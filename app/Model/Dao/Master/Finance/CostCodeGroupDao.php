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
use App\Frame\Formatter\Trans;
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
class CostCodeGroupDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ccg_id',
        'ccg_ss_id',
        'ccg_code',
        'ccg_name',
        'ccg_type',
        'ccg_active',
    ];

    /**
     * Base dao constructor for cost_code.
     *
     */
    public function __construct()
    {
        parent::__construct('cost_code_group', 'ccg', self::$Fields);
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
        $helper->addStringWhere('ccg_id', $referenceValue);
        $helper->addStringWhere('ccg_ss_id', $systemSettingValue);
        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
            $helper->addOrderByString('ccg_code, ccg_id');
        }
        $query = "SELECT ccg_id, ccg_ss_id, ccg_code, ccg_name, ccg_active, ccg_type
                FROM cost_code_group " . $helper;
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all the active record.
     *
     * @param string $type To store the type
     *
     * @return string
     */
    public static function getTypeName(string $type): string
    {
        if ($type === 'S') {
            return Trans::getWord('sales');
        }
        return Trans::getWord('purchase');
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
        $query = 'SELECT count(DISTINCT (ccg_id)) AS total_rows
                   FROM cost_code_group ' . $helper->getConditionForCountData();
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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ccg_id');
    }

}
