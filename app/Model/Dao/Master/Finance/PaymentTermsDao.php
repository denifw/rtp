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
 * Class to handle data access object for table tax.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class PaymentTermsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pt_id',
        'pt_ss_id',
        'pt_days',
        'pt_name',
        'pt_active',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'pt_days'
    ];


    /**
     * Base dao constructor for tax.
     *
     */
    public function __construct()
    {
        parent::__construct('payment_terms', 'pt', self::$Fields);
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     * @param string $ssId To store the System Settings ID.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $referenceValue, string $ssId): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('pt_id', $referenceValue);
        $helper->addStringWhere('pt_ss_id', $ssId);
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
            $helper->addOrderByString('pt_days, pt_id');
        }
        $query = 'SELECT pt_id, pt_name, pt_days, pt_active, pt_ss_id
                FROM payment_terms ' . $helper;
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

        $query = 'SELECT count(DISTINCT (pt_id)) AS total_rows
                   FROM payment_terms ' . $helper->getConditionForCountData();
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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'pt_id', ['pt_days']);
    }
}
