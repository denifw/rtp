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
 * Class to handle data access object for table purchase_invoice_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PurchaseInvoiceDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pid_id',
        'pid_pi_id',
        'pid_jo_id',
        'pid_cc_id',
        'pid_description',
        'pid_quantity',
        'pid_uom_id',
        'pid_rate',
        'pid_cur_id',
        'pid_exchange_rate',
        'pid_tax_id',
        'pid_total',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'pid_quantity',
        'pid_rate',
        'pid_exchange_rate',
        'pid_total',
    ];

    /**
     * Base dao constructor for purchase_invoice_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('purchase_invoice_detail', 'pid', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('pid.pid_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }


    /**
     * Function to get data by reference value
     *
     * @param string $piId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByPiId(string $piId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('pid.pid_pi_id', $piId);
        $wheres[] = SqlHelper::generateNullCondition('pid.pid_deleted_on');
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
        $query = 'SELECT pid.pid_id, pid.pid_pi_id, pi.pi_number as pid_pi_number, rel.rel_name as pid_pi_vendor,
                        pi.pi_paid_on as pid_pi_paid_on, pi.pi_verified_on as pid_pi_verified_on, pi.pi_date as pid_pi_date,
                        pid.pid_cc_id, cc.cc_code as pid_cc_code, cc.cc_name as pid_cc_name, pid.pid_description, pid.pid_quantity,
                        pid.pid_uom_id, uom.uom_code as pid_uom_code, pid.pid_rate, pid.pid_cur_id, cur.cur_iso as pid_currency,
                        pid.pid_exchange_rate, pid.pid_tax_id, tax.tax_name as pid_tax_name, tax.tax_percent as pid_tax_percent,
                        pid.pid_total, pid.pid_jo_id, jo.jo_number as pid_jo_number, jo.jo_name as pid_jo_name
                    FROM purchase_invoice_detail as pid
                    INNER JOIN purchase_invoice as pi ON pid.pid_pi_id = pi.pi_id
                    INNER JOIN cost_code as cc ON pid.pid_cc_id = cc.cc_id
                    INNER JOIN unit as uom ON pid.pid_uom_id = uom.uom_id
                    INNER JOIN currency as cur ON pid.pid_cur_id = cur.cur_id
                    INNER JOIN tax as tax ON pid.pid_tax_id = tax.tax_id
                    LEFT OUTER JOIN job_order as jo ON pid.pid_jo_id = jo.jo_id
                    LEFT OUTER JOIN relation as rel ON pi.pi_rel_id = rel.rel_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY pid.pid_created_on, pid.pid_id';
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
        $query = 'SELECT count(DISTINCT (pid_id)) AS total_rows
                        FROM purchase_invoice_detail' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'pid_id');
    }


}
