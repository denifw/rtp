<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\Operation\Job;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table job_order_task.
 *
 * @package    app
 * @subpackage Model\Dao\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrderTaskDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jot_id',
        'jot_jo_id',
        'jot_description',
        'jot_rel_id',
        'jot_cp_id',
        'jot_notes',
        'jot_portion',
        'jot_progress',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'jot_portion',
        'jot_progress',
    ];

    /**
     * Base dao constructor for job_order_task.
     *
     */
    public function __construct()
    {
        parent::__construct('job_order_task', 'jot', self::$Fields);
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('jot.jot_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $joId To store the job id.
     *
     * @return array
     */
    public static function getByJobId(string $joId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('jot.jot_jo_id', $joId);
        $wheres[] = SqlHelper::generateNullCondition('jot.jot_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jot.jot_id, jot.jot_jo_id, jot.jot_description, jot.jot_rel_id, rel.rel_name as jot_vendor,
                        jot.jot_cp_id, cp.cp_name as jot_pic_vendor, jot.jot_notes, jot.jot_portion, jot.jot_progress
                    FROM job_order_task as jot
                        LEFT OUTER JOIN relation as rel ON jot.jot_rel_id = rel.rel_id
                        LEFT OUTER JOIN contact_person as cp ON jot.jot_cp_id = cp.cp_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jot.jot_created_on, jot.jot_id';
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
        $query = 'SELECT count(DISTINCT (jot.jot_id)) AS total_rows
                        FROM job_order_task as jot
                        LEFT OUTER JOIN relation as rel ON jor.jot_rel_id = rel.rel_id
                        LEFT OUTER JOIN contact_person as cp ON jot.jot_cp_id = cp.cp_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array|String $textColumn To store the text value of single select.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'jot_id');
    }


}
