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
 * Class to handle data access object for table ticket.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class TicketDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'tc_id', 'tc_ss_id', 'tc_number', 'tc_subject', 'tc_rel_id', 'tc_pic_id',
        'tc_report_date', 'tc_report_time', 'tc_priority_id', 'tc_status_id', 'tc_assign_id',
        'tc_description', 'tc_start_by', 'tc_start_on', 'tc_finish_by', 'tc_finish_on',
        'tc_deleted_reason'
    ];

    /**
     * Base dao constructor for ticket.
     *
     */
    public function __construct()
    {
        parent::__construct('ticket', 'tc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table ticket.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'tc_number', 'tc_subject', 'tc_report_date', 'tc_report_time',
            'tc_description', 'tc_start_on', 'tc_finish_on',
            'tc_deleted_reason'
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
        $wheres[] = '(tc_id = ' . $referenceValue . ')';
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
        $wheres[] = '(tc_id = ' . $referenceValue . ')';
        $wheres[] = '(tc_ss_id = ' . $ssId . ')';
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
        $query = 'SELECT tc.tc_id, tc.tc_ss_id, tc.tc_number, tc.tc_subject, tc.tc_rel_id, tc.tc_pic_id, tc.tc_report_date, tc.tc_report_time,
                         tc.tc_priority_id, tc.tc_status_id, tc.tc_assign_id, tc.tc_description, 
                         tc.tc_start_by, tc.tc_start_on, tc.tc_finish_by, tc.tc_finish_on, tc.tc_deleted_reason,
                         rel.rel_name as tc_rel_name, pri.sty_name as tc_priority_name,
                         stt.sty_name as tc_status_name, us.us_name as tc_assign_name, pic.cp_name as tc_pic_name
                  FROM ticket as tc
                  INNER JOIN relation as rel on rel.rel_id = tc.tc_rel_id
                  INNER JOIN system_type as pri on pri.sty_id = tc.tc_priority_id
                  INNER JOIN system_type as stt on stt.sty_id = tc.tc_status_id
                  INNER JOIN users as us on us.us_id = tc.tc_assign_id
                  INNER JOIN contact_person as pic on pic.cp_id = tc.tc_pic_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (tc_id)) AS total_rows
                  FROM ticket as tc
                  INNER JOIN relation as rel on rel.rel_id = tc.tc_rel_id
                  INNER JOIN system_type as pri on pri.sty_id = tc.tc_priority_id
                  INNER JOIN system_type as stt on stt.sty_id = tc.tc_status_id
                  INNER JOIN users as us on us.us_id = tc.tc_assign_id
                  INNER JOIN contact_person as pic on pic.cp_id = tc.tc_pic_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, 'tc_subject', 'tc_id');
    }


}
