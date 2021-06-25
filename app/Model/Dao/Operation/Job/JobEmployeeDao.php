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
 * Class to handle data access object for table job_employee.
 *
 * @package    app
 * @subpackage Model\Dao\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobEmployeeDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jem_id',
        'jem_jo_id',
        'jem_em_id',
        'jem_shift_one',
        'jem_shift_two',
        'jem_shift_three',
        'jem_type',
    ];

    /**
     * Base dao constructor for job_employee.
     *
     */
    public function __construct()
    {
        parent::__construct('job_employee', 'jem', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('jem.jem_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $joId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJoId(string $joId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('jem.jem_jo_id', $joId);
        $wheres[] = SqlHelper::generateNullCondition('jem.jem_deleted_on');
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
        $query = 'SELECT jem.jem_id, jem.jem_em_id, em.em_number as jem_number, em.em_name as jem_name,
                        jt.jt_description as jem_title, jem.jem_shift_one, jem.jem_shift_two, jem.jem_shift_three,
                        jem.jem_type
                    FROM job_employee as jem
                        INNER JOIN employee as em ON jem.jem_em_id = em.em_id
                        LEFT OUTER JOIN job_title as jt ON em.em_jt_id = jt.jt_id ' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (jem.jem_id)) AS total_rows
                        FROM job_employee as jem
                        INNER JOIN employee as em ON jem.jem_em_id = em.em_id
                        LEFT OUTER JOIN job_title as jt ON em.em_jt_id = jt.jt_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'jem_id');
    }


}
