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

/**
 * Class to handle data access object for table sales_invoice_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoiceDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sid_id',
        'sid_si_id',
        'sid_cc_id',
        'sid_description',
        'sid_quantity',
        'sid_uom_id',
        'sid_rate',
        'sid_cur_id',
        'sid_exchange_rate',
        'sid_tax_id',
        'sid_total',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'sid_quantity',
        'sid_rate',
        'sid_exchange_rate',
        'sid_total',
    ];

    /**
     * Base dao constructor for sales_invoice_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_invoice_detail', 'sid', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('sid.sid_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $siId To store the reference value of the table.
     *
     * @return array
     */
    public static function getBySiId(string $siId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('sid.sid_si_id', $siId);
        $wheres[] = SqlHelper::generateNullCondition('sid.sid_deleted_on');
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
        $query = 'SELECT sid.sid_id, sid.sid_si_id, si.si_number as sid_si_number, sid.sid_cc_id, cc.cc_name as sid_cost_code, sid.sid_description,
                        sid.sid_quantity, sid.sid_uom_id, uom.uom_code as sid_uom_code, sid.sid_rate, sid.sid_cur_id,
                        cur.cur_iso as sid_currency, sid.sid_exchange_rate, sid.sid_tax_id, tax.tax_name as sid_tax_name,
                        tax.tax_percent as sid_tax_percent, sid.sid_total
                    FROM sales_invoice_detail as sid
                        INNER JOIN sales_invoice as si ON sid.sid_si_id = si.si_id
                        INNER JOIN cost_code as cc ON sid.sid_cc_id = cc.cc_id
                        INNER JOIN currency as cur ON sid.sid_cur_id = cur.cur_id
                        INNER JOIN unit as uom ON sid.sid_uon_id = uom.uom_id
                        INNER JOIN tax as tax ON sid.sid_tax_id = tax.tax_id ' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (sid.sid_id)) AS total_rows
                        FROM sales_invoice_detail as sid
                        INNER JOIN sales_invoice as si ON sid.sid_si_id = si.si_id
                        INNER JOIN cost_code as cc ON sid.sid_cc_id = cc.cc_id
                        INNER JOIN currency as cur ON sid.sid_cur_id = cur.cur_id
                        INNER JOIN unit as uom ON sid.sid_uon_id = uom.uom_id
                        INNER JOIN tax as tax ON sid.sid_tax_id = tax.tax_id ' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'sid_id');
    }


}
