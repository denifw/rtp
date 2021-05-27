<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use function count;

/**
 * Class to handle data access object for table sales_order.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'so_id', 'so_ss_id', 'so_number', 'so_rel_id', 'so_customer_ref', 'so_pic_id', 'so_order_of_id', 'so_invoice_of_id', 'so_order_date', 'so_container',
        'so_contract_ref', 'so_bl_ref', 'so_aju_ref', 'so_sppb_ref', 'so_packing_ref', 'so_publish_by', 'so_publish_on', 'so_start_by', 'so_start_on', 'so_finish_by',
        'so_finish_on', 'so_deleted_reason', 'so_notes', 'so_soh_id', 'so_soa_id', 'so_consolidate', 'so_sales_id', 'so_inklaring', 'so_delivery', 'so_warehouse',
        'so_ict_id', 'so_cct_id', 'so_cdt_id', 'so_pol_id', 'so_departure_date', 'so_departure_time', 'so_pod_id', 'so_arrival_date', 'so_arrival_time', 'so_tm_id',
        'so_consignee_id', 'so_consignee_of_id', 'so_shipper_id', 'so_shipper_of_id', 'so_notify_id', 'so_notify_of_id', 'so_carrier_id', 'so_carrier_of_id', 'so_transport_name',
        'so_transport_number', 'so_sppd_ref', 'so_sppd_date', 'so_do_ref', 'so_do_expired', 'so_manifest_ref', 'so_manifest_date', 'so_manifest_pos', 'so_manifest_sub_pos', 'so_plb',
        'so_wh_id', 'so_dp_id', 'so_pick_date', 'so_pick_time', 'so_dr_id', 'so_return_date', 'so_return_time',
        'so_consignee_cp_id', 'so_shipper_cp_id', 'so_notify_cp_id', 'so_carrier_cp_id', 'so_multi_load', 'so_multi_unload',
        'so_atd_date', 'so_atd_time', 'so_ata_date', 'so_ata_time',
        'sp_yp_id', 'so_yp_date', 'so_yp_time', 'sp_yr_id', 'so_yr_date', 'so_yr_time',
    ];

    /**
     * Base dao constructor for sales_order.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order', 'so', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'so_number', 'so_customer_ref', 'so_order_date', 'so_container', 'so_contract_ref', 'so_bl_ref', 'so_aju_ref', 'so_sppb_ref', 'so_packing_ref', 'so_publish_on',
            'so_start_on', 'so_finish_on', 'so_deleted_reason', 'so_notes', 'so_consolidate', 'so_inklaring', 'so_delivery', 'so_warehouse', 'so_departure_date', 'so_departure_time',
            'so_arrival_date', 'so_arrival_time', 'so_transport_name', 'so_transport_number', 'so_sppd_ref', 'so_sppd_date', 'so_do_ref', 'so_do_expired', 'so_manifest_ref', 'so_manifest_date',
            'so_manifest_pos', 'so_manifest_sub_pos', 'so_plb', 'so_pick_date', 'so_pick_time', 'so_return_date', 'so_return_time',
            'so_multi_load', 'so_multi_unload', 'so_atd_date', 'so_atd_time', 'so_ata_date', 'so_ata_time',
            'so_yp_date', 'so_yp_time', 'so_yr_date', 'so_yr_time',
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
    public static function getByReference(int $referenceValue): array
    {
        $result = [];
        $where = [];
        $where[] = SqlHelper::generateNumericCondition('so.so_id', $referenceValue);
        $data = self::loadData($where);
        if (count($data) === 1) {
            $result = $data[0];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $systemSettingValue): array
    {
        $result = [];
        $where = [];
        $where[] = SqlHelper::generateNumericCondition('so.so_id', $referenceValue);
        $where[] = SqlHelper::generateNumericCondition('so.so_ss_id', $systemSettingValue);
        $data = self::loadData($where);
        if (count($data) === 1) {
            $result = $data[0];
        }

        return $result;
    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list of order by.
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
        $query = 'SELECT so.so_id, so.so_number, so.so_ss_id, so.so_rel_id, rel.rel_name as so_customer, so.so_customer_ref,
                        so.so_pic_id, cp.cp_name as so_pic_customer, so.so_order_date, so.so_order_of_id, oo.of_name as so_order_office,
                        so.so_invoice_of_id, oi.of_name as so_invoice_office, so.so_container, so.so_bl_ref,
                        so.so_packing_ref, so.so_aju_ref, so.so_publish_on, so.so_consolidate,
                        so.so_finish_on, so.so_deleted_on, so.so_start_on, so.so_deleted_reason,
                        u1.us_name as so_created_by, u2.us_name as so_publish_by, u3.us_name as so_finish_by, u4.us_name as so_deleted_by,
                        so.so_notes, so.so_sppb_ref, soh.soh_id, soh.soh_reason, soh.soh_created_on, so.so_soa_id,
                        so.so_sales_id, sm.cp_name as so_sales_manager,
                        so.so_inklaring, so.so_delivery, so.so_warehouse, so.so_ict_id, so.so_cct_id, so.so_cdt_id, so.so_pol_id, so.so_departure_date, so.so_departure_time, so.so_pod_id,
                        so.so_arrival_date, so.so_arrival_time, so.so_tm_id, so.so_consignee_id, so.so_consignee_of_id, so.so_shipper_id, so.so_shipper_of_id, so.so_notify_id, so.so_notify_of_id, so.so_carrier_id,
                        so.so_carrier_of_id, so.so_transport_name, so.so_transport_number, so.so_sppd_ref, so.so_sppd_date, so.so_do_ref, so.so_do_expired, so.so_manifest_ref, so.so_manifest_date,
                        so.so_manifest_pos, so.so_manifest_sub_pos, so.so_plb, so.so_wh_id, so.so_dp_id, so.so_pick_date, so.so_pick_time, so.so_dr_id, so.so_return_date,
                        so.so_return_time, so_consignee_cp_id, so_shipper_cp_id, so_notify_cp_id, so_carrier_cp_id,
                        ict.ict_code as so_ict_code, ict.ict_name as so_inco_terms, ict.ict_pol as so_ict_pol, so.so_multi_load, so.so_multi_unload,
                        ict.ict_pod as so_ict_pod, ict.ict_load as so_ict_load, ict.ict_unload as so_ict_unload,
                        shp.rel_name as so_shipper, shpOf.of_name as so_shipper_address, shpCp.cp_name as shp_pic_shipper,
                        cons.rel_name as so_consignee, consOf.of_name as so_consignee_address, consCp.cp_name as shp_pic_consignee,
                        ntf.rel_name as so_notify, ntfOf.of_name as so_notify_address, ntfCp.cp_name as so_pic_notify,
                        crr.rel_name as so_carrier, crrOf.of_name as so_carrier_address, crrCp.cp_name as so_pic_carrier,
                        cdt.cdt_name as so_document_type, cct.cct_name as so_custom_type, tm.tm_name as so_transport_module,
                        wh.wh_name as so_warehouse_name,
                        pol.po_name as so_pol, polc.cnt_name as so_pol_country, pod.po_name as so_pod, podc.cnt_name as so_pod_country,
                        dp.of_name as so_dp_name, dp.of_rel_id as so_dp_rel_id, dpr.rel_name as so_dp_owner, dr.of_name as so_dr_name,
                        dr.of_rel_id as so_dr_rel_id, drr.rel_name as so_dr_owner,
                        so.so_atd_date, so.so_atd_time, so.so_ata_date, so.so_ata_time,
                        so.so_yp_id, yp.of_name as so_yp_name, ypr.rel_id as so_yp_rel_id, ypr.rel_name as so_yp_owner, so.so_yp_date, so.so_yp_time,
                        so.so_yr_id, yr.of_name as so_yr_name, yrr.rel_id as so_yr_rel_id, yrr.rel_name as so_yr_owner, so.so_yr_date, so.so_yr_time
                FROM sales_order as so
                    INNER JOIN relation as rel on so.so_rel_id = rel.rel_id
                    INNER JOIN office as oo ON so.so_order_of_id = oo.of_id
                    LEFT OUTER JOIN contact_person as sm ON so.so_sales_id = sm.cp_id
                    LEFT OUTER JOIN office as oi ON so.so_invoice_of_id = oi.of_id
                    LEFT OUTER JOIN contact_person as cp ON so.so_pic_id = cp.cp_id
                    LEFT OUTER JOIN inco_terms as ict ON so.so_ict_id = ict.ict_id
                    LEFT OUTER JOIN relation as shp ON so.so_shipper_id = shp.rel_id
                    LEFT OUTER JOIN relation as cons ON so.so_consignee_id = cons.rel_id
                    LEFT OUTER JOIN relation as ntf ON so.so_notify_id = ntf.rel_id
                    LEFT OUTER JOIN relation as crr ON so.so_carrier_id = crr.rel_id
                    LEFT OUTER JOIN office as shpOf ON so.so_shipper_of_id = shpOf.of_id
                    LEFT OUTER JOIN office as consOf ON so.so_consignee_of_id = consOf.of_id
                    LEFT OUTER JOIN office as ntfOf ON so.so_notify_of_id = ntfOf.of_id
                    LEFT OUTER JOIN office as crrOf ON so.so_carrier_of_id = crrOf.of_id
                    LEFT OUTER JOIN contact_person as shpCp ON so.so_shipper_cp_id = shpCp.cp_id
                    LEFT OUTER JOIN contact_person as consCp ON so.so_consignee_cp_id = consCp.cp_id
                    LEFT OUTER JOIN contact_person as ntfCp ON so.so_notify_cp_id = ntfCp.cp_id
                    LEFT OUTER JOIN contact_person as crrCp ON so.so_carrier_cp_id = crrCp.cp_id
                    LEFT OUTER JOIN customs_document_type as cdt ON so.so_cdt_id = cdt.cdt_id
                    LEFT OUTER JOIN customs_clearance_type as cct ON so.so_cct_id = cct.cct_id
                    LEFT OUTER JOIN transport_module as tm ON so.so_tm_id = tm.tm_id
                    LEFT OUTER JOIN warehouse as wh ON so.so_wh_id = wh.wh_id
                    LEFT OUTER JOIN port as pol ON so.so_pol_id = pol.po_id
                    LEFT OUTER JOIN country as polc ON pol.po_cnt_id = polc.cnt_id
                    LEFT OUTER JOIN port as pod ON so.so_pod_id = pod.po_id
                    LEFT OUTER JOIN country as podc ON pod.po_cnt_id = podc.cnt_id
                    LEFT OUTER JOIN office as dp ON so.so_dp_id = dp.of_id
                    LEFT OUTER JOIN relation as dpr ON dp.of_rel_id = dpr.rel_id
                    LEFT OUTER JOIN office as dr ON so.so_dr_id = dr.of_id
                    LEFT OUTER JOIN relation as drr ON dr.of_rel_id = drr.rel_id
                    LEFT OUTER JOIN office as yp ON so.so_yp_id = yp.of_id
                    LEFT OUTER JOIN relation as ypr ON yp.of_rel_id = ypr.rel_id
                    LEFT OUTER JOIN office as yr ON so.so_yr_id = yr.of_id
                    LEFT OUTER JOIN relation as yrr ON yr.of_rel_id = yrr.rel_id
                    LEFT OUTER JOIN users as u1 ON so.so_created_by = u1.us_id
                    LEFT OUTER JOIN users as u2 ON so.so_publish_by = u2.us_id
                    LEFT OUTER JOIN users as u3 ON so.so_finish_by = u3.us_id
                    LEFT OUTER JOIN users as u4 ON so.so_deleted_by = u4.us_id
                    LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY so.so_deleted_on DESC, so.so_finish_on DESC, so.so_start_on DESC, so.so_publish_on DESC, so.so_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to load total row data.
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
        $query = 'SELECT count(DISTINCT (so.so_id)) AS total_rows
                FROM sales_order as so
                    INNER JOIN relation as rel on so.so_rel_id = rel.rel_id
                    INNER JOIN office as oo ON so.so_order_of_id = oo.of_id
                    LEFT OUTER JOIN contact_person as sm ON so.so_sales_id = sm.cp_id
                    LEFT OUTER JOIN office as oi ON so.so_invoice_of_id = oi.of_id
                    LEFT OUTER JOIN contact_person as cp ON so.so_pic_id = cp.cp_id
                    LEFT OUTER JOIN inco_terms as ict ON so.so_ict_id = ict.ict_id
                    LEFT OUTER JOIN relation as shp ON so.so_shipper_id = shp.rel_id
                    LEFT OUTER JOIN relation as cons ON so.so_consignee_id = cons.rel_id
                    LEFT OUTER JOIN relation as ntf ON so.so_notify_id = ntf.rel_id
                    LEFT OUTER JOIN relation as crr ON so.so_carrier_id = crr.rel_id
                    LEFT OUTER JOIN office as shpOf ON so.so_shipper_of_id = shpOf.of_id
                    LEFT OUTER JOIN office as consOf ON so.so_consignee_of_id = consOf.of_id
                    LEFT OUTER JOIN office as ntfOf ON so.so_notify_of_id = ntfOf.of_id
                    LEFT OUTER JOIN office as crrOf ON so.so_carrier_of_id = crrOf.of_id
                    LEFT OUTER JOIN contact_person as shpCp ON so.so_shipper_cp_id = shpCp.cp_id
                    LEFT OUTER JOIN contact_person as consCp ON so.so_consignee_cp_id = consCp.cp_id
                    LEFT OUTER JOIN contact_person as ntfCp ON so.so_notify_cp_id = ntfCp.cp_id
                    LEFT OUTER JOIN contact_person as crrCp ON so.so_carrier_cp_id = crrCp.cp_id
                    LEFT OUTER JOIN customs_document_type as cdt ON so.so_cdt_id = cdt.cdt_id
                    LEFT OUTER JOIN customs_clearance_type as cct ON so.so_cct_id = cct.cct_id
                    LEFT OUTER JOIN transport_module as tm ON so.so_tm_id = tm.tm_id
                    LEFT OUTER JOIN warehouse as wh ON so.so_wh_id = wh.wh_id
                    LEFT OUTER JOIN port as pol ON so.so_pol_id = pol.po_id
                    LEFT OUTER JOIN country as polc ON pol.po_cnt_id = polc.cnt_id
                    LEFT OUTER JOIN port as pod ON so.so_pod_id = pod.po_id
                    LEFT OUTER JOIN country as podc ON pod.po_cnt_id = podc.cnt_id
                    LEFT OUTER JOIN office as dp ON so.so_dp_id = dp.of_id
                    LEFT OUTER JOIN relation as dpr ON dp.of_rel_id = dpr.rel_id
                    LEFT OUTER JOIN office as dr ON so.so_dr_id = dr.of_id
                    LEFT OUTER JOIN relation as drr ON dr.of_rel_id = drr.rel_id
                    LEFT OUTER JOIN office as yp ON so.so_yp_id = yp.of_id
                    LEFT OUTER JOIN relation as ypr ON yp.of_rel_id = ypr.rel_id
                    LEFT OUTER JOIN office as yr ON so.so_yr_id = yr.of_id
                    LEFT OUTER JOIN relation as yrr ON yr.of_rel_id = yrr.rel_id
                    LEFT OUTER JOIN users as u1 ON so.so_created_by = u1.us_id
                    LEFT OUTER JOIN users as u2 ON so.so_publish_by = u2.us_id
                    LEFT OUTER JOIN users as u3 ON so.so_finish_by = u3.us_id
                    LEFT OUTER JOIN users as u4 ON so.so_deleted_by = u4.us_id
                    LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get the stock card table.
     *
     * @param array $row To store the data.
     * @param bool $showSoNumber To trigger if need to show so number or not
     * @param string $prefix To store the prefix
     *
     * @return string
     */
    public function concatReference(array $row, bool $showSoNumber = false, string $prefix = 'so'): string
    {
        $refs = [];
        if ($showSoNumber === true && empty($row[$prefix . '_number']) === false) {
            $refs[] = [
                'label' => 'SO',
                'value' => $row[$prefix . '_number'],
            ];
        }
        if (empty($row[$prefix . '_customer_ref']) === false) {
            $refs[] = [
                'label' => 'PO',
                'value' => $row[$prefix . '_customer_ref'],
            ];
        }
        if (empty($row[$prefix . '_aju_ref']) === false) {
            $refs[] = [
                'label' => 'AJU',
                'value' => $row[$prefix . '_aju_ref'],
            ];
        }
        if (empty($row[$prefix . '_bl_ref']) === false) {
            $refs[] = [
                'label' => 'BL',
                'value' => $row[$prefix . '_bl_ref'],
            ];
        }
        if (empty($row[$prefix . '_sppb_ref']) === false) {
            $refs[] = [
                'label' => 'SPPB',
                'value' => $row[$prefix . '_sppb_ref'],
            ];
        }
        if (empty($row[$prefix . '_packing_ref']) === false) {
            $refs[] = [
                'label' => Trans::getWord('packing'),
                'value' => $row[$prefix . '_packing_ref'],
            ];
        }

        return StringFormatter::generateKeyValueTableView($refs);
    }

    /**
     * Function to generate the status
     *
     * @param array $data To store the status data.
     *
     * @return string
     */
    public function generateStatus(array $data): string
    {
        /*
         $data = [
            'is_deleted' => '',
            'is_finish' => '',
            'is_in_progress' => '',
            'is_publish' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDark(Trans::getWord('canceled'));
        } elseif ($data['is_hold'] === true) {
            $result = new LabelDark(Trans::getWord('hold'));
        } elseif ($data['is_finish'] === true) {
            $result = new LabelSuccess(Trans::getWord('finish'));
        } elseif ($data['is_in_progress'] === true) {
            $result = new LabelWarning(Trans::getWord('inProgress'));
        } elseif ($data['is_publish'] === true) {
            $result = new LabelDanger(Trans::getWord('published'));
        } else {
            $result = new LabelGray(Trans::getWord('draft'));
        }

        return $result;
    }

    /**
     * Function to load financial margin data.
     *
     * @param int $soId To store the reference of job order.
     *
     * @return array
     */
    public static function loadFinanceMarginData(int $soId): array
    {
        $results = [];
        $jik = '(jo.jo_id IN (SELECT jik_jo_id
                                    FROM job_inklaring
                                    WHERE jik_so_id = ' . $soId . '))';
        $jdl = '(jo.jo_id IN (SELECT jdl_jo_id
                                    FROM job_delivery
                                    WHERE jdl_so_id = ' . $soId . '))';
        $wheresSales = [];
        $wheresSales[] = '(' . $jik . ' OR ' . $jdl . ')';
        $wheresSales[] = '(jo.jo_deleted_on IS NULL)';
        $wheresSales[] = '(jos.jos_deleted_on IS NULL)';
        $strWhereSales = ' WHERE ' . implode(' AND ', $wheresSales);
        $querySales = "SELECT 'S' as fn_type, jos.jos_id as fn_id, jos.jos_total as fn_total,
                                (CASE WHEN sid.sid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                (CASE WHEN si.si_pay_time IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                                ccg.ccg_type as fn_category, srv.srv_id as fn_srv_id, srv.srv_name as fn_service,
                                jos.jos_jo_id as fn_jo_id, 'N' as fn_ca, 0 as ca_amount, 0 as ca_reserve_amount, null as ca_receive_on,
                                0 as ca_actual_amount, 0 as ca_ea_amount, null as ca_settlement_on
                        FROM job_sales as jos INNER JOIN
                            cost_code as cc ON cc.cc_id = jos.jos_cc_id INNER JOIN
                            cost_code_group as ccg ON ccg.ccg_id = cc.cc_ccg_id INNER JOIN
                            service as srv ON ccg.ccg_srv_id = srv.srv_id INNER JOIN
                            job_order as jo ON jos.jos_jo_id = jo.jo_id LEFT OUTER JOIN
                            sales_invoice_detail as sid ON jos.jos_sid_id = sid.sid_id LEFT OUTER JOIN
                            sales_invoice as si ON sid.sid_si_id = si.si_id " . $strWhereSales;
        $wheresPurchase = [];
        $wheresPurchase[] = '(' . $jik . ' OR ' . $jdl . ')';
        $wheresPurchase[] = '(jo.jo_deleted_on IS NULL)';
        $wheresPurchase[] = '(jop.jop_deleted_on IS NULL)';
        $strWherePurchase = ' WHERE ' . implode(' AND ', $wheresPurchase);
        $queryPurchase = "SELECT 'P' as fn_type, jop_id as fn_id, jop_total as fn_total,
                                (CASE WHEN pid.pid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                (CASE WHEN pi.pi_paid_on IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                                ccg.ccg_type as fn_category, srv.srv_id as fn_srv_id, srv.srv_name as fn_service,
                                jop.jop_jo_id as fn_jo_id, (CASE WHEN jop.jop_cad_id IS NULL THEN 'N' ELSE 'Y' END) as fn_ca, ca.ca_amount, ca.ca_reserve_amount, ca.ca_receive_on,
                                ca.ca_actual_amount, ca.ca_ea_amount, ca.ca_settlement_on
                        FROM job_purchase as jop INNER JOIN cost_code as cc ON cc.cc_id = jop.jop_cc_id
                            INNER JOIN cost_code_group as ccg ON ccg.ccg_id = cc.cc_ccg_id
                            INNER JOIN service as srv ON ccg.ccg_srv_id = srv.srv_id
                            INNER JOIN job_order as jo ON jop.jop_jo_id = jo.jo_id
                            LEFT OUTER JOIN purchase_invoice_detail as pid ON jop.jop_pid_id = pid.pid_id
                            LEFT OUTER JOIN purchase_invoice as pi ON pid.pid_pi_id = pi.pi_id
                            LEFT OUTER JOIN cash_advance_detail as cad ON jop.jop_cad_id = cad.cad_id
                            LEFT OUTER JOIN cash_advance as ca ON cad.cad_ca_id = ca.ca_id " . $strWherePurchase;
        $query = $querySales . ' UNION ALL ' . $queryPurchase;
        $query .= ' ORDER BY fn_srv_id';
        $sqlResults = DB::select($query);
        $header = [
            'fn_description' => Trans::getFinanceWord('description'),
        ];
        $temp = [
            'SS' => [
                'fn_description' => Trans::getFinanceWord('revenue'),
                'fn_total' => 0.0,
                'fn_invoiced' => 0.0,
                'fn_paid' => 0.0,
            ],
            'PP' => [
                'fn_description' => Trans::getFinanceWord('cogs'),
                'fn_total' => 0.0,
                'fn_invoiced' => 0.0,
                'fn_paid' => 0.0,
            ],
            'SR' => [
                'fn_description' => Trans::getFinanceWord('reimburse'),
                'fn_total' => 0.0,
                'fn_invoiced' => 0.0,
                'fn_paid' => 0.0,
            ],
            'PR' => [
                'fn_description' => Trans::getFinanceWord('cogsReimburse'),
                'fn_total' => 0.0,
                'fn_invoiced' => 0.0,
                'fn_paid' => 0.0,
            ],
            'M' => [
                'fn_description' => Trans::getFinanceWord('margin'),
                'fn_total' => 0.0,
                'fn_invoiced' => 0.0,
                'fn_paid' => 0.0,
            ],
        ];
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            $tempCaJoAmount = [];
            foreach ($data as $row) {
                # Calculate Ca Payment
                if ($row['fn_ca'] === 'Y' && array_key_exists($row['fn_jo_id'], $tempCaJoAmount) === false) {
                    $totalCa = 0.0;
                    if (empty($row['ca_receive_on']) === false) {
                        if (empty($row['ca_settlement_on']) === true) {
                            $totalCa = (float)$row['ca_amount'] + (float)$row['ca_reserve_amount'];
                        } else {
                            $totalCa = (float)$row['ca_actual_amount'] + (float)$row['ca_ea_amount'];
                        }
                    }
                    $tempCaJoAmount[$row['fn_jo_id']] = $totalCa;
                }
                $srvKey = 'srv' . $row['fn_srv_id'];
                # Adding Header
                if (array_key_exists($srvKey, $header) === false) {
                    $header[$srvKey] = $row['fn_service'];
                }
                $total = (float)$row['fn_total'];
                $fnType = $row['fn_type'] . $row['fn_category'];
                # Add amount per service
                if (array_key_exists($srvKey, $temp[$fnType]) === false) {
                    $temp[$fnType][$srvKey] = $total;
                } else {
                    $temp[$fnType][$srvKey] += $total;
                }
                # Add default field
                $invoiced = 0.0;
                $paid = 0.0;
                if ($row['fn_ca'] === 'N') {
                    if ($row['fn_invoiced'] === 'Y') {
                        $invoiced = $total;
                    }
                    if ($row['fn_paid'] === 'Y') {
                        $paid = $total;
                    }
                } else {
                    $invoiced = $total;
                    if (array_key_exists($row['fn_jo_id'], $tempCaJoAmount) === true && $tempCaJoAmount[$row['fn_jo_id']] > 0) {
                        if ($tempCaJoAmount[$row['fn_jo_id']] >= $total) {
                            $paid = $total;
                            $tempCaJoAmount[$row['fn_jo_id']] -= $total;
                        } else {
                            $paid = $tempCaJoAmount[$row['fn_jo_id']];
                            $tempCaJoAmount[$row['fn_jo_id']] = 0.0;
                        }
                    }
                }
                $temp[$fnType]['fn_total'] += $total;
                $temp[$fnType]['fn_invoiced'] += $invoiced;
                $temp[$fnType]['fn_paid'] += $paid;
                # Calculate Margin
                $aggregator = 1;
                if ($row['fn_type'] === 'P') {
                    $aggregator = -1;
                }
                if (array_key_exists($srvKey, $temp['M']) === false) {
                    $temp['M'][$srvKey] = ($total * $aggregator);
                } else {
                    $temp['M'][$srvKey] += ($total * $aggregator);
                }
                $temp['M']['fn_total'] += ($total * $aggregator);
                $temp['M']['fn_invoiced'] += ($invoiced * $aggregator);
                $temp['M']['fn_paid'] += ($paid * $aggregator);

            }
        }
        $header['fn_total'] = Trans::getFinanceWord('subTotal');
        $header['fn_invoiced'] = Trans::getFinanceWord('invoiced');
        $header['fn_paid'] = Trans::getFinanceWord('paid');
        $keyRows = ['SS', 'SR', 'PP', 'PR', 'M'];
        $rows = [];
        foreach ($keyRows as $key) {
            $rows[] = $temp[$key];
        }
        $results['header'] = $header;
        $results['rows'] = $rows;
        return $results;
    }


    /**
     * Function to load financial margin data.
     *
     * @param int $ssId To store the reference of job order.
     * @param int $soId To store the reference of job order.
     * @param string $soNumber To store the reference of job order.
     *
     * @return array
     */
    public static function loadDocumentData(int $ssId, int $soId, string $soNumber = ''): array
    {
        $soWheres = [];
        $soWheres[] = "(dcg.dcg_code = 'salesorder')";
        $soWheres[] = '(doc.doc_group_reference = ' . $soId . ')';
        $soWheres[] = '(doc.doc_deleted_on IS NULL)';
        $soWheres[] = '(doc.doc_ss_id = ' . $ssId . ')';
        $strSoWheres = ' WHERE ' . implode(' AND ', $soWheres);
        $siQuery = "SELECT 1 as doc_order, doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, '" . $soNumber . "' as doc_group_text
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                    users AS us ON us.us_id = doc.doc_created_by " . $strSoWheres;
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'joborder')";
        $wheres[] = "(dct.dct_code <> 'actionevent')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $wheres[] = '(doc.doc_ss_id = ' . $ssId . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $inklaring = '(jo_id IN (SELECT jik_jo_id
                                FROM job_inklaring
                                WHERE (jik_so_id = ' . $soId . ')))';
        $delivery = '(jo_id IN (SELECT jdl_jo_id
                                FROM job_delivery
                                WHERE (jdl_so_id = ' . $soId . ')))';
        $inbound = '(jo_id IN (SELECT ji_jo_id
                                FROM job_inbound
                                WHERE (ji_so_id = ' . $soId . ')))';
        $outbound = '(jo_id IN (SELECT job_jo_id
                                FROM job_outbound
                                WHERE (job_so_id = ' . $soId . ')))';
        $wheres[] = '(' . $inklaring . ' OR ' . $delivery . ' OR ' . $inbound . ' OR ' . $outbound . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $ssId . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $joQuery = 'SELECT 2 as doc_order, doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, jo.jo_number as doc_group_text
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                    users AS us ON us.us_id = doc.doc_created_by INNER JOIN
                    job_order as jo ON doc.doc_group_reference = jo.jo_id ' . $strWheres;
        $query = 'SELECT doc_order, doc_id, doc_dct_id, dct_code, dct_description, dct_dcg_id, dcg_code, dcg_description, doc_group_reference,
                    doc_type_reference, doc_file_name, doc_file_size, doc_file_type, doc_public,
                    doc_created_by, doc_creator, doc_created_on, doc_description, doc_group_text
                    FROM (' . $siQuery . ' UNION ALL ' . $joQuery . ') as j
                   ORDER BY doc_order, doc_group_text, doc_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list order by.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 20): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'so_number', 'so_id');
    }


    /**
     * Function to do the update of the transaction.;
     *
     * @param int $soId To store the so id
     *
     * @return array
     */
    static function loadAllJobGoodsInbound(int $soId): array
    {
        $query = 'SELECT jog_id
                    FROM  job_goods
                    WHERE (jog_jo_id IN (SELECT ji_jo_id
                                            FROM job_inbound
                                            WHERE (ji_so_id = ' . $soId . ')))';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
