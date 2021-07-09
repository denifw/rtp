<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\Administration;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Class to handle data access object for table employee_loan_balance.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class EmployeeLoanBalanceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'elb_id',
        'elb_em_id',
        'elb_amount',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'elb_amount',
    ];

    /**
     * Base dao constructor for employee_loan_balance.
     *
     */
    public function __construct()
    {
        parent::__construct('employee_loan_balance', 'elb', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('elb_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $emId To store the system setting value.
     *
     * @return float
     */
    public static function getBalaceByEmployee(string $emId): float
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('elb_em_id', $emId);
        $wheres[] = SqlHelper::generateNullCondition('el_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT elb_em_id, SUM(el_amount) as total
                FROM employee_loan_balance ' . $strWheres . '
                GROUP BY elb_em_id';
        $sqlResults = DB::select($query);
        $results = 0.0;
        if (count($sqlResults) === 1) {
            $results = (float)DataParser::objectToArray($sqlResults[0])['total'];
        }
        return $results;
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
        $query = 'SELECT elb_id, elb_em_id, elb_amount
                        FROM employee_loan_balance ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY elb_id';
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
        $query = 'SELECT count(DISTINCT (elb_id)) AS total_rows
                        FROM employee_loan_balance' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'elb_id');
    }


}
