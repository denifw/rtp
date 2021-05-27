<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Finance\CashAndBank;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table cash_advance_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvanceDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'cad_id',
        'cad_ca_id',
        'cad_jop_id',
        'cad_cc_id',
        'cad_description',
        'cad_quantity',
        'cad_uom_id',
        'cad_rate',
        'cad_cur_id',
        'cad_exchange_rate',
        'cad_tax_id',
        'cad_total',
        'cad_doc_id',
        'cad_ea_payment',
    ];

    /**
     * Base dao constructor for cash_advance_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('cash_advance_detail', 'cad', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cash_advance_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'cad_description',
            'cad_ea_payment',
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
     * @param bool $isJoExist To trigger is this ca link with job order or not.
     *
     * @return array
     */
    public static function getByReference(int $referenceValue, bool $isJoExist = false): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('cad.cad_id', $referenceValue);
        if ($isJoExist === true) {
            $data = self::loadDataWithJopPurchase($wheres);
        } else {
            $data = self::loadData($wheres);
        }
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $caId To store the reference value of the table.
     * @param bool $isJoExist To trigger is this ca link with job order or not.
     *
     * @return array
     */
    public static function getByCaId(int $caId, bool $isJoExist = false): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('cad.cad_ca_id', $caId);
        $wheres[] = SqlHelper::generateNullCondition('cad.cad_deleted_on');
        if ($isJoExist === true) {
            return self::loadDataWithJopPurchase($wheres);
        }
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
        $query = "SELECT cad.cad_id, cad.cad_ca_id, cad.cad_jop_id, cad.cad_cc_id, cc.cc_code as cad_cost_code, cad.cad_description,
                        cad.cad_quantity, cad.cad_rate, cad.cad_cur_id, cur.cur_iso as cad_currency, cad.cad_exchange_rate,
                        cad.cad_tax_id, tax.tax_name as cad_tax_name, tax.tax_percent as cad_tax_percent, cad.cad_total,
                        cad.cad_doc_id, ccg.ccg_type as cad_type, (CASE WHEN ccg.ccg_type = 'P' THEN 'Purchase' ELSE 'Reimburse' END) AS cad_type_name,
                        cad.cad_uom_id, uom.uom_code as cad_uom_code, cad.cad_ea_payment
                    FROM cash_advance_detail as cad
                        INNER JOIN cost_code as cc ON cad.cad_cc_id = cc.cc_id
                         INNER JOIN cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id
                       INNER JOIN currency as cur ON cad.cad_cur_id = cur.cur_id
                       INNER JOIN unit as uom ON cad.cad_uom_id = uom.uom_id
                        INNER JOIN (SELECT t.tax_id, t.tax_name, (CASE WHEN tax_percent IS NULL THEN 0 ELSE tax_percent END) as tax_percent
                                        FROM tax as t LEFT OUTER JOIN
                                            (SELECT td_tax_id, SUM(td_percent) as tax_percent
                                            FROM tax_detail
                                            WHERE td_active = 'Y' AND td_deleted_on IS NULL
                                            GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON cad.cad_tax_id = tax.tax_id " . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY cad.cad_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
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
    public static function loadDataWithJopPurchase(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT cad.cad_id, cad.cad_ca_id, cad.cad_jop_id, jop.jop_cc_id as cad_cc_id,
                        cc.cc_code as cad_cost_code, jop.jop_description as cad_description, jop.jop_rate as cad_rate,
                        jop.jop_quantity as cad_quantity, jop.jop_uom_id as cad_uom_id, uom.uom_code as cad_uom_code,
                        jop.jop_cur_id as cad_cur_id, jop.jop_exchange_rate as cad_exchange_rate, jop.jop_tax_id as cad_tax_id,
                        cur.cur_iso AS cad_currency, tax.tax_name AS cad_tax_name, tax.tax_percent as cad_tax_percent,
                        jop.jop_total as cad_total, jop.jop_jos_id as cad_jos_id, cad.cad_doc_id, cad.cad_ea_payment,
                        ccg.ccg_type as cad_type, (CASE WHEN ccg.ccg_type = 'P' THEN 'Purchase' ELSE 'Reimburse' END) AS cad_type_name
                        FROM cash_advance_detail as cad
                            INNER JOIN job_purchase AS jop ON jop.jop_id = cad.cad_jop_id
                            INNER JOIN cost_code AS cc ON cc.cc_id = jop.jop_cc_id
                            INNER JOIN cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id
                            INNER JOIN unit AS uom ON uom.uom_id = jop.jop_uom_id
                            INNER JOIN currency AS cur ON cur.cur_id = jop.jop_cur_id
                            LEFT OUTER JOIN (SELECT t.tax_id, t.tax_name, (CASE WHEN tax_percent IS NULL THEN 0 ELSE tax_percent END) as tax_percent
                                            FROM tax as t LEFT OUTER JOIN
                                                (SELECT td_tax_id, SUM(td_percent) as tax_percent
                                                FROM tax_detail
                                                WHERE td_active = 'Y' AND td_deleted_on IS NULL
                                                GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jop.jop_tax_id = tax.tax_id" . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY cad.cad_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get all record.
     *
     * @param int $caId To store cash advance id.
     * @param bool $isEaPayment To store the trigger for payment type.
     *
     * @return float
     */
    public static function getTotalDetailByCa(int $caId, bool $isEaPayment): float
    {
        $result = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('cad.cad_ca_id', $caId);
        $wheres[] = SqlHelper::generateNullCondition('cad.cad_deleted_on');
        if ($isEaPayment) {
            $wheres[] = SqlHelper::generateStringCondition('cad.cad_ea_payment', 'Y');
        } else {
            $wheres[] = '(' . SqlHelper::generateStringCondition('cad.cad_ea_payment', 'N') . ' OR ' . SqlHelper::generateNullCondition('cad.cad_ea_payment') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cad.cad_id, (CASE WHEN cad_jop_id IS NULL THEN cad.cad_total ELSE jop.jop_total END) as cad_total
                        FROM cash_advance_detail as cad
                            LEFT OUTER JOIN job_purchase AS jop ON cad.cad_jop_id = jop.jop_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                $result += (float)$row['cad_total'];
            }
        }

        return $result;
    }

}
