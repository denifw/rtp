<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm\Quotation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table price_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PriceDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'prd_id',
        'prd_prc_id',
        'prd_cc_id',
        'prd_description',
        'prd_quantity',
        'prd_uom_id',
        'prd_rate',
        'prd_minimum_rate',
        'prd_cur_id',
        'prd_exchange_rate',
        'prd_tax_id',
        'prd_total',
        'prd_remark',
    ];

    /**
     * Base dao constructor for price_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('price_detail', 'prd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table price_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'prd_description',
            'prd_remark',
        ]);
    }


    /**
     * Function to get all available fields
     *
     * @return array
     */
    public static function getFields(): array
    {
        return self::$Fields;
    }

    /**
     * Function filter array PriceDetail by prc_id
     *
     * @param int $prcId
     *
     * @return array
     */
    public static function getByPriceId(int $prcId): array
    {
        $wheres = [];
        $wheres [] = '(prd.prd_prc_id = ' . $prcId . ')';
        $wheres [] = '(prd.prd_deleted_on IS NULL)';
        return self::loadData($wheres);
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
        $wheres[] = '(prd.prd_id = ' . $referenceValue . ')';
        return self::loadData($wheres)[0];
    }


    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId           To store the system setting value.pr
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(prd_id = ' . $referenceValue . ')';
        $wheres[] = '(prd_ss_id = ' . $ssId . ')';
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
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        $wheres[] = '(prd.prd_deleted_on IS NULL)';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT prd.prd_id, prd.prd_prc_id, prd.prd_cc_id, cc.cc_code as prd_cost_code, prd.prd_description,
                        prd.prd_quantity, prd.prd_uom_id, uom.uom_name as prd_unit, prd.prd_rate, prd.prd_minimum_rate,
                        prd.prd_cur_id, cur.cur_iso as prd_currency, prd.prd_exchange_rate, prd.prd_tax_id, tax.tax_name as prd_tax,
                        (CASE WHEN tax.tax_percent IS NULL THEN 0 ELSE tax.tax_percent END) as prd_tax_percent, prd.prd_total,
                        prd.prd_remark, prc.prc_rel_id as prd_rel_id
                        FROM price_detail as prd
                        INNER JOIN price as prc on prd.prd_prc_id = prc.prc_id
                        INNER JOIN currency as cur on prd.prd_cur_id = cur.cur_id
                        INNER JOIN unit as uom on prd.prd_uom_id = uom.uom_id
                        LEFT OUTER JOIN cost_code as cc on prd.prd_cc_id = cc.cc_id
                        LEFT OUTER JOIN
                             (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t left OUTER join
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON prd.prd_tax_id = tax.tax_id" . $strWhere;
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
        $query = "SELECT count(DISTINCT (prd_id)) AS total_rows
                        FROM price_detail as prd
                        INNER JOIN price as prc on prc.prc_id = prd.prd_prc_id
                        INNER JOIN unit as uom on prd.prd_uom_id = uom.uom_id
                        INNER JOIN tax as tx on prd.prd_tax_id = tx.tax_id
                        LEFT OUTER JOIN cost_code as cc on prd.prd_cc_id = cc.cc_id
                        LEFT OUTER JOIN (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                         from tax as t
                         LEFT OUTER JOIN (select td_tax_id, SUM(td_percent) as tax_percent from tax_detail WHERE td_active = 'Y' AND td_deleted_on IS NULL
                                GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON prd.prd_tax_id = tax.tax_id" . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function get price total detail by PrcId
     *
     * @param int $prcId
     *
     * @return array
     */
    public static function totalDetailByPrcId(int $prcId): array
    {
        $strWhere = '';
        $wheres [] = '(prd.prd_prc_id = ' . $prcId . ')';
        $wheres [] = '(prd.prd_deleted_on IS NULL)';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT prc.prc_id, tax_percent as prd_tax_percent, prd.prd_quantity, prd.prd_rate , prd.prd_exchange_rate
                    FROM price_detail as prd
                     INNER JOIN price as prc on prc.prc_id = prd.prd_prc_id
                     INNER JOIN unit as uom on prd.prd_uom_id = uom.uom_id
                     INNER JOIN tax as tx on prd.prd_tax_id = tx.tax_id
                     LEFT OUTER JOIN cost_code as cc on prd.prd_cc_id = cc.cc_id
                     LEFT OUTER JOIN (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                          from tax as t
                                   LEFT OUTER JOIN (select td_tax_id, SUM(td_percent) as tax_percent from tax_detail WHERE td_active = 'Y' AND td_deleted_on IS NULL
                                                    GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON prd.prd_tax_id = tax.tax_id" . $strWhere;
        $query .= ' ORDER BY prc.prc_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'prd_cc_name', 'prd_id');
    }


    /**
     * Function to get data by quotation id
     *
     * @param int $qtId To store the reference value of quotation table.
     *
     * @return array
     */
    public static function getByQuotationId($qtId): array
    {
        $wheres = [];
        $wheres[] = '(prc.prc_qt_id = ' . $qtId . ')';
        $wheres[] = '(prc.prc_deleted_on IS NULL)';
        $wheres[] = '(prd.prd_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT prc.prc_id, prc.prc_qt_id, prc.prc_code, prc.prc_type, prc.prc_rel_id, rel.rel_name as prc_relation,
                           prc.prc_srv_id, srv.srv_code as prc_srv_code, srv.srv_name as prc_srv_name,
                           prc.prc_srt_id, srt.srt_name as prc_srt_name, srt.srt_container as prc_srt_container, prc.prc_lead_time,
                           prc.prc_ct_id, ct.ct_name as prc_container_type, prc.prc_eg_id, eg.eg_name as prc_eg_name, eg.eg_code as prc_eg_code,
                           prc.prc_dtc_origin, dtc_or.dtc_name as prc_origin_district, cty_or.cty_name as prc_origin_city, stt_or.stt_name as prc_origin_state,
                           prc.prc_dtc_destination, dtc_des.dtc_name as prc_destination_district, cty_des.cty_name as prc_destination_city, stt_des.stt_name as prc_destination_state,
                           prc.prc_wh_id, wh.wh_name as prc_warehouse, prc.prc_tm_id, tm.tm_name as prc_transport_module,
                           prc.prc_cct_id, cct.cct_name as prc_custom_clearance_type,
                           uc.us_name as prc_created_by, prc.prc_created_on, ud.us_name as prc_deleted_by, prc.prc_deleted_on, prc.prc_deleted_reason,
                           cct.cct_code as prc_cct_code, ct.ct_code as prc_ct_code,
                           prc.prc_qt_id, qt.qt_number as prc_qt_number, qt.qt_approve_on as prc_qt_approve_on, qt.qt_qts_id as prc_qt_qts_id,
                           qts.qts_deleted_on as prc_qt_qts_deleted_on, qt.qt_start_date as prc_qt_start_date, qt.qt_end_date as prc_qt_end_date,
                           prd.prd_id, prd.prd_prc_id, prd.prd_cc_id, cc.cc_code as prd_cost_code, prd.prd_description,
                           prd.prd_quantity, prd.prd_uom_id, uom.uom_name as prd_unit, uom.uom_code as prd_uom_code, prd.prd_rate, prd.prd_minimum_rate,
                           prd.prd_cur_id, cur.cur_iso as prd_currency, prd.prd_exchange_rate, prd.prd_tax_id, tax.tax_name as prd_tax,
                           (CASE WHEN tax.tax_percent IS NULL THEN 0 ELSE tax.tax_percent END) as prd_tax_percent, prd.prd_total,
                           prd.prd_remark, prc.prc_pol_id, pol.po_name as prc_pol_name, pol.po_code as prc_pol_code, polc.cnt_name as prc_pol_country,
                           prc.prc_pod_id, pod.po_name as prc_pod_name, pod.po_code as prc_pod_code, podc.cnt_name as prc_pod_country,
                            srt.srt_load as prc_srt_load, srt.srt_unload as prc_srt_unload, srt.srt_pol as prc_srt_pol, srt.srt_pod as prc_srt_pod,
                            prc.prc_origin_address, prc.prc_destination_address
                    FROM price as prc
                         INNER JOIN relation as rel on prc.prc_rel_id = rel.rel_id
                         INNER JOIN service as srv on prc.prc_srv_id = srv.srv_id
                         INNER JOIN quotation as qt on prc.prc_qt_id = qt.qt_id
                         INNER JOIN price_detail as prd ON prd.prd_prc_id = prc.prc_id
                         INNER JOIN currency as cur on prd.prd_cur_id = cur.cur_id
                         INNER JOIN unit as uom on prd.prd_uom_id = uom.uom_id
                         LEFT OUTER JOIN cost_code as cc on prd.prd_cc_id = cc.cc_id
                         LEFT OUTER JOIN
                             (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t left OUTER join
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON prd.prd_tax_id = tax.tax_id
                         LEFT OUTER JOIN quotation_submit as qts on qt.qt_qts_id = qts.qts_id
                         LEFT OUTER JOIN service_term as srt on srt.srt_id = prc.prc_srt_id
                         LEFT OUTER JOIN container as ct on ct.ct_id = prc.prc_ct_id
                         LEFT OUTER JOIN port as pol on prc.prc_pol_id = pol.po_id
                         LEFT OUTER JOIN country as polc on pol.po_cnt_id = polc.cnt_id
                         LEFT OUTER JOIN port as pod on prc.prc_pod_id = pod.po_id
                         LEFT OUTER JOIN country as podc on pod.po_cnt_id = podc.cnt_id
                         LEFT OUTER JOIN district as dtc_or on prc.prc_dtc_origin = dtc_or.dtc_id
                         LEFT OUTER JOIN city as cty_or on dtc_or.dtc_cty_id = cty_or.cty_id
                         LEFT OUTER JOIN state as stt_or on dtc_or.dtc_stt_id = stt_or.stt_id
                         LEFT OUTER JOIN district as dtc_des on prc.prc_dtc_destination = dtc_des.dtc_id
                         LEFT OUTER JOIN city as cty_des on dtc_des.dtc_cty_id = cty_des.cty_id
                         LEFT OUTER JOIN state as stt_des on dtc_des.dtc_stt_id = stt_des.stt_id
                         LEFT OUTER JOIN transport_module as tm on  prc.prc_tm_id = tm.tm_id
                         LEFT OUTER JOIN warehouse as wh on wh.wh_id = prc.prc_wh_id
                         LEFT OUTER JOIN equipment_group as eg on eg.eg_id = prc.prc_eg_id
                         LEFT OUTER JOIN customs_clearance_type as cct on prc.prc_cct_id = cct.cct_id
                         LEFT OUTER JOIN users as uc on prc.prc_created_by = uc.us_id
                         LEFT OUTER JOIN users as ud on prc.prc_deleted_by = ud.us_id " . $strWhere;
        $query .= ' ORDER BY prc.prc_srv_id, prc.prc_srt_id, dtc_or.dtc_name,dtc_des.dtc_name, prc.prc_id,prd.prd_id';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to check is there any empty cost code for quotation.
     *
     * @param int $qtId To store the reference value of quotation table.
     *
     * @return bool
     */
    public static function checkEmptyCostCodeForQuotation($qtId): bool
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_id', $qtId);
        $wheres[] = '(prc.prc_deleted_on IS NULL)';
        $wheres[] = '(prd.prd_deleted_on IS NULL)';
        $wheres[] = '(prd.prd_cc_id IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT prd.prd_id, prc.prc_id, qt.qt_id
                    FROM price_detail as prd
                        INNER JOIN price as prc ON prc.prc_id = prd.prd_prc_id
                        INNER JOIN quotation as qt ON qt.qt_id = prc.prc_qt_id ' . $strWheres;
        $sqlResults = DB::select($query);
        # return true if there is no data with empty cc_id
        return empty($sqlResults);
    }

    /**
     * Function to load default data by quotation.
     *
     * @param int $qtId To store the reference value of quotation table.
     *
     * @return array
     */
    public static function loadDefaultDataByQuotation($qtId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('prc.prc_qt_id', $qtId);
        $wheres[] = '(prc.prc_deleted_on IS NULL)';
        $wheres[] = '(prd.prd_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT prc.prc_id, prc.prc_ss_id, prc.prc_qt_id, prc.prc_rel_id, prc.prc_code,
                        prc.prc_type, prc.prc_srv_id, prc.prc_srt_id, prc.prc_lead_time, prc.prc_ct_id,
                        prc.prc_eg_id, prc.prc_dtc_origin, prc.prc_dtc_destination, prc.prc_wh_id,
                        prc.prc_tm_id, prc.prc_cct_id, prc.prc_po_id,
                        prd.prd_id, prd.prd_cc_id, prd.prd_description, prd.prd_quantity, prd.prd_uom_id,
                        prd.prd_rate, prd.prd_minimum_rate, prd.prd_cur_id, prd.prd_exchange_rate, prd.prd_tax_id,
                        prd.prd_total,prd.prd_remark
                    FROM price_detail as prd
                        INNER JOIN price as prc ON prc.prc_id = prd.prd_prc_id ' . $strWheres;
        $query .= ' ORDER BY prc.prc_id, prd.prd_id';
        $sqlResults = DB::select($query);
        # return true if there is no data with empty cc_id
        return DataParser::arrayObjectToArray($sqlResults);
    }

}
