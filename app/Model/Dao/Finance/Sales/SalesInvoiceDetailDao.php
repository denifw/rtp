<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Sales;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table sales_invoice_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
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
        'sid_jos_id',
        'sid_cc_id',
        'sid_description',
        'sid_rate',
        'sid_quantity',
        'sid_uom_id',
        'sid_cur_id',
        'sid_exchange_rate',
        'sid_tax_id',
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
     * Abstract function to load the seeder query for table sales_invoice_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sid_description',
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
        $wheres[] = '(sid.sid_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $siId To store the reference value of the table.
     *
     * @return array
     */
    public static function getBySiId(int $siId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sid.sid_si_id', $siId);
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
        $query = "SELECT sid.sid_id, sid.sid_si_id, jos.jos_id as sid_jos_id, jos.jos_jo_id, jo.jo_number as sid_jo_number, jos.jos_cc_id, jos.jos_rel_id,
                        jos.jos_description as sid_description, jos.jos_rate as sid_rate, jos.jos_quantity as sid_quantity,
                        jos.jos_uom_id, jos.jos_cur_id as sid_cur_id, jos.jos_exchange_rate as sid_exchange_rate, jos.jos_tax_id,
                       cc.cc_code AS sid_cc_code, uom.uom_code AS sid_uom_code, cur.cur_iso AS sid_cur_iso,
                       tax.tax_name AS sid_tax_name, rel.rel_name AS sid_relation, jos.jos_total as sid_total,
                       jo.jo_srv_id as sid_jo_srv_id, srv.srv_name as sid_jo_service, jo.jo_srt_id as sid_jo_srt_id, srt.srt_name as sid_jo_service_term,
                        ccg.ccg_type as sid_type
                FROM sales_invoice_detail as sid INNER JOIN
                     job_sales AS jos ON sid.sid_jos_id = jos.jos_id INNER JOIN
                     job_order as jo ON jo.jo_id = jos.jos_jo_id INNER JOIN
                     service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                    service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                     relation AS rel ON rel.rel_id = jos.jos_rel_id INNER JOIN
                     cost_code AS cc ON cc.cc_id = jos.jos_cc_id INNER JOIN
                    cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                     unit AS uom ON uom.uom_id = jos.jos_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = jos.jos_cur_id LEFT OUTER JOIN
                     tax as tax ON jos.jos_tax_id = tax.tax_id " . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jo.jo_srv_id, sid.sid_id';
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
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadManualData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT sid.sid_id, sid.sid_si_id, sid.sid_cc_id, sid.sid_description, sid.sid_rate, sid.sid_quantity,
                        sid.sid_uom_id, sid.sid_cur_id, sid.sid_exchange_rate, sid.sid_tax_id,
                       cc.cc_code AS sid_cc_code, uom.uom_code AS sid_uom_code, cur.cur_iso AS sid_cur_iso,
                       tax.tax_name AS sid_tax_name, tax.tax_percent as sid_tax_percent,
                       sid.sid_total, ccg.ccg_type as sid_cc_type
                FROM sales_invoice_detail as sid INNER JOIN
                     cost_code AS cc ON sid.sid_cc_id = cc.cc_id INNER JOIN
                     cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                     unit AS uom ON sid.sid_uom_id = uom.uom_id INNER JOIN
                     currency AS cur ON sid.sid_cur_id = cur.cur_id LEFT OUTER JOIN
                     (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                      from tax as t left OUTER join
                           (select td_tax_id, SUM(td_percent) as tax_percent
                            from tax_detail
                            where td_active = 'Y' and td_deleted_on is null
                            group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON sid.sid_tax_id = tax.tax_id" . $strWhere;
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
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadJosData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT sid.sid_si_id, jos.jos_cc_id as sid_cc_id,  jos.jos_description as sid_description,
                        jos.jos_rate as sid_rate, SUM(jos.jos_quantity) as sid_quantity, jos.jos_uom_id as sid_uom_id, jos.jos_cur_id as sid_cur_id,
                        jos.jos_exchange_rate as sid_exchange_rate, jos.jos_tax_id as sid_tax_id,
                       cc.cc_code AS sid_cc_code, uom.uom_code AS sid_uom_code, cur.cur_iso AS sid_cur_iso,
                       tax.tax_name AS sid_tax_name, tax.tax_percent as sid_tax_percent, ccg.ccg_type as sid_cc_type
                FROM sales_invoice_detail as sid INNER JOIN
                     job_sales as jos ON sid.sid_jos_id = jos.jos_id INNER JOIN
                        job_order as jo ON jos.jos_jo_id = jo.jo_id INNER JOIN
                     cost_code AS cc ON jos.jos_cc_id = cc.cc_id INNER JOIN
                     cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                     unit AS uom ON jos.jos_uom_id = uom.uom_id INNER JOIN
                     currency AS cur ON jos.jos_cur_id = cur.cur_id LEFT OUTER JOIN
                     (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                      from tax as t left OUTER join
                           (select td_tax_id, SUM(td_percent) as tax_percent
                            from tax_detail
                            where td_active = 'Y' and td_deleted_on is null
                            group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jos.jos_tax_id = tax.tax_id " . $strWhere;
        $query .= ' GROUP BY sid.sid_si_id, jos.jos_cc_id, jos.jos_description, jos.jos_rate,
                        jos.jos_uom_id, jos.jos_cur_id,jos.jos_exchange_rate, jos.jos_tax_id,
                       cc.cc_code, uom.uom_code, cur.cur_iso, tax.tax_name, tax.tax_percent, ccg.ccg_type';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);;

    }


}
