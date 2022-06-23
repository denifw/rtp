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
        $helper = new SqlHelper();
        $helper->addStringWhere('cc.cc_id', $referenceValue);
        $helper->addStringWhere('cc.cc_ss_id', $systemSettingValue);
        $data = self::loadData($helper);
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
        $helper = new SqlHelper();
        $helper->addStringWhere('cc.cc_ccg_id', $ccgId);
        $helper->addNullWhere('cc.cc_deleted_on');
        return self::loadData($helper);
    }

    /**
     * Function to get all record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadData(SqlHelper $helper): array
    {
        if ($helper->hasOrderBy() === false) {
            $helper->addOrderByString('ccg.ccg_code, cc.cc_code, cc.cc_id');
        }
        $query = "SELECT cc.cc_id, cc.cc_ss_id, cc.cc_code, cc.cc_ccg_id, cc.cc_name, cc.cc_active,
                        ccg.ccg_code as cc_group_code, ccg.ccg_name as cc_group_name, ccg.ccg_type as cc_type,
                        (CASE WHEN ccg.ccg_type = 'S' THEN 'sales' ELSE 'purchase' END) AS cc_type_name
                        FROM cost_code AS cc INNER JOIN
                       cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id " . $helper;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get total record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(SqlHelper $helper): int
    {
        $result = 0;
        $query = 'SELECT count(DISTINCT (cc.cc_id)) AS total_rows
                       FROM cost_code AS cc
                           INNER JOIN cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id ' . $helper->getConditionForCountData();
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
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper): array
    {
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'cc_id');
    }

}
