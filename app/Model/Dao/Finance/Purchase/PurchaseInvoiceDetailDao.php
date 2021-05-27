<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Purchase;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table purchase_invoice_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
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
        'pid_jop_id',
        'pid_cc_id',
        'pid_description',
        'pid_rate',
        'pid_minimum_rate',
        'pid_quantity',
        'pid_uom_id',
        'pid_cur_id',
        'pid_exchange_rate',
        'pid_tax_rate',
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
     * Abstract function to load the seeder query for table purchase_invoice_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pid_description',
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
     *
     * @return array
     */
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(pid_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(pid_id = ' . $referenceValue . ')';
        $wheres[] = '(pid_ss_id = ' . $systemSettingValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $piId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJopIdByPiId(int $piId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('pid_pi_id', $piId);
        $wheres[] = SqlHelper::generateNullCondition('pid_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('pid_jop_id', false);
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = ' SELECT pid_jop_id
                        FROM purchase_invoice_detail ' . $strWhere;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT pid.pid_id, pid.pid_pi_id, pid.pid_cc_id, pid.pid_description, pid.pid_rate, pid.pid_quantity,
                        pid.pid_uom_id, pid.pid_cur_id, pid.pid_exchange_rate, pid.pid_tax_id,
                       cc.cc_code AS pid_cc_code, uom.uom_code AS pid_uom_code, cur.cur_iso AS pid_cur_iso,
                       tax.tax_name AS pid_tax_name, (CASE WHEN tax.tax_percent is null then 0 else tax.tax_percent END) as tax_percent,
                       pid.pid_total, pid.pid_jop_id
                FROM purchase_invoice_detail as pid INNER JOIN
                     cost_code AS cc ON cc.cc_id = pid.pid_cc_id INNER JOIN
                     unit AS uom ON uom.uom_id = pid.pid_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = pid.pid_cur_id LEFT OUTER JOIN
                     (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                      from tax as t left OUTER join
                           (select td_tax_id, SUM(td_percent) as tax_percent
                            from tax_detail
                            where td_active = \'Y\' and td_deleted_on is null
                            group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON pid.pid_tax_id  = tax.tax_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadDataByJop(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT pid.pid_id, pid.pid_pi_id, pid.pid_jop_id, jo.jo_number as pid_jo_number, jop.jop_cc_id,
                        jop.jop_description as pid_description, jop.jop_rate as pid_rate, jop.jop_quantity as pid_quantity,
                        jop.jop_uom_id, jop.jop_cur_id, jop.jop_exchange_rate as pid_exchange_rate, jop.jop_tax_id,
                       cc.cc_code AS pid_cc_code, uom.uom_code AS pid_uom_code, cur.cur_iso AS pid_cur_iso,
                       tax.tax_name AS pid_tax_name, (CASE WHEN tax.tax_percent is null then 0 else tax.tax_percent END) as tax_percent,
                       jop.jop_total as pid_total
                FROM purchase_invoice_detail as pid INNER JOIN
                    job_purchase AS jop ON pid.pid_jop_id = jop.jop_id INNER JOIN
                     job_order as jo ON jo.jo_id = jop.jop_jo_id INNER JOIN
                     relation AS rel ON rel.rel_id = jop.jop_rel_id INNER JOIN
                     cost_code AS cc ON cc.cc_id = jop.jop_cc_id INNER JOIN
                     unit AS uom ON uom.uom_id = jop.jop_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = jop.jop_cur_id LEFT OUTER JOIN
                     (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                      from tax as t left OUTER join
                           (select td_tax_id, SUM(td_percent) as tax_percent
                            from tax_detail
                            where td_active = 'Y' and td_deleted_on is null
                            group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jop.jop_tax_id  = tax.tax_id" . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


}
