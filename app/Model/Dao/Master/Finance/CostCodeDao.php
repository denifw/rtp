<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table cost_code.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class CostCodeDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'cc_id',
        'cc_ss_id',
        'cc_code',
        'cc_name',
        'cc_ccg_id',
        'cc_active',
    ];

    /**
     * Base dao constructor for cost_code.
     *
     */
    public function __construct()
    {
        parent::__construct('cost_code', 'cc', self::$Fields);
    }


    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     * @param string $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $referenceValue, string $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('cc.cc_ss_id', $systemSettingValue);
        $wheres[] = SqlHelper::generateStringCondition('cc.cc_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get all the active record.
     *
     * @param string $ccgId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByGroupId(string $ccgId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('cc.cc_ccg_id', $ccgId);
        $wheres[] = '(cc.cc_ccg_id = ' . $ccgId . ')';

        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
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
        $query = "SELECT cc.cc_id, cc.cc_ss_id, cc.cc_code, cc.cc_ccg_id, cc.cc_name, cc.cc_active,
                        ccg.ccg_code as cc_group_code, ccg.ccg_name as cc_group_name, ccg.ccg_type as cc_type,
                        (CASE WHEN ccg.ccg_type = 'S' THEN 'Sales' WHEN ccg.ccg_type = 'P' THEN 'Purchase' WHEN ccg.ccg_type = 'D' THEN 'Deposit' ELSE 'Reimburse' END) AS cc_type_name
                        FROM cost_code AS cc INNER JOIN
                       cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id " . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(' AND ', $orders);
        } else {
            $query .= ' ORDER BY ccg.ccg_code, cc.cc_code, cc.cc_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
        $query = 'SELECT count(DISTINCT (cc.cc_id)) AS total_rows
                       FROM cost_code AS cc INNER JOIN
                       cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'cc_id');
    }


}
