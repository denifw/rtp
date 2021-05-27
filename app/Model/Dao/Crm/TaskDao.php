<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table task.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class TaskDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'tsk_id', 'tsk_ss_id', 'tsk_number', 'tsk_subject', 'tsk_rel_id', 'tsk_pic_id',
        'tsk_type_id', 'tsk_priority_id', 'tsk_status_id', 'tsk_assign_id', 'tsk_location', 'tsk_dl_id',
        'tsk_start_date', 'tsk_start_time', 'tsk_end_date', 'tsk_end_time', 'tsk_description', 'tsk_result', 'tsk_deleted_reason',
        'tsk_start_by', 'tsk_start_on', 'tsk_finish_by', 'tsk_finish_on'
    ];

    /**
     * Base dao constructor for task.
     *
     */
    public function __construct()
    {
        parent::__construct('task', 'tsk', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table task.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'tsk_number', 'tsk_subject', 'tsk_location', 'tsk_start_date', 'tsk_start_time',
            'tsk_end_date', 'tsk_end_time', 'tsk_description', 'tsk_result', 'tsk_deleted_reason',
            'tsk_start_on', 'tsk_finish_on'
        ]);
    }


    /**
     * function to get all available fields
     *
     * @return array
     */
    public static function getFields(): array
    {
        return self::$Fields;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(tsk_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId           To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(tsk_id = ' . $referenceValue . ')';
        $wheres[] = '(tsk_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT tsk.tsk_id, tsk.tsk_ss_id, tsk.tsk_number, tsk.tsk_subject, tsk.tsk_rel_id, tsk.tsk_pic_id,
                         tsk.tsk_type_id, tsk.tsk_priority_id, tsk.tsk_status_id, tsk.tsk_assign_id, tsk.tsk_location, tsk.tsk_dl_id,
                         tsk.tsk_start_date, tsk.tsk_start_time, tsk.tsk_end_date, tsk.tsk_end_time, tsk.tsk_description, tsk.tsk_next_step,
                         tsk.tsk_result, tsk.tsk_start_by, tsk.tsk_start_on, tsk.tsk_finish_by, tsk.tsk_finish_on, us.us_name as tsk_created_by,
                         rel.rel_name as tsk_rel_name, typ.sty_name as tsk_type_name, pr.sty_name as tsk_priority_name,
                         pic.cp_name as tsk_pic_name, us.us_name as tsk_assign_name, dl.dl_name as tsk_dl_name, status.sty_name as tsk_status_name
                 FROM    task as tsk
                         INNER JOIN relation as rel on rel.rel_id = tsk.tsk_rel_id
                         INNER JOIN system_type as typ on typ.sty_id = tsk.tsk_type_id
                         INNER JOIN system_type as pr on pr.sty_id = tsk.tsk_priority_id
                         INNER JOIN users as us on us.us_id = tsk.tsk_assign_id
                         LEFT OUTER JOIN contact_person as pic on pic.cp_id = tsk.tsk_pic_id
                         LEFT OUTER JOIN deal as dl on dl.dl_id = tsk.tsk_dl_id
                         LEFT OUTER JOIN  system_type as status on status.sty_id = tsk.tsk_status_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (tsk.tsk_id)) AS total_rows
                  FROM task as tsk
                  INNER JOIN relation as rel on rel.rel_id = tsk.tsk_rel_id
                  INNER JOIN system_type as typ on typ.sty_id = tsk.tsk_type_id
                  INNER JOIN system_type as pr on pr.sty_id = tsk.tsk_priority_id
                  INNER JOIN users as us on us.us_id = tsk.tsk_assign_id
                  LEFT OUTER JOIN contact_person as pic on pic.cp_id = tsk.tsk_pic_id
                  LEFT OUTER JOIN deal as dl on dl.dl_id = tsk.tsk_dl_id
                  LEFT OUTER JOIN  system_type as status on status.sty_id = tsk.tsk_status_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'tsk_subject', 'tsk_id');
    }


}
