<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job\Delivery;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table job_delivery.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Delivery
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobDeliveryDao extends AbstractBaseDao
{
    /**
     * Base dao constructor for job_delivery.
     *
     */
    public function __construct()
    {
        parent::__construct('job_delivery', 'jdl', self::$Fields);
    }

    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jdl_id',
        'jdl_jo_id',
        'jdl_consolidate',
        'jdl_tm_id',
        'jdl_eg_id',
        'jdl_eq_id',
        'jdl_transport_number',
        'jdl_first_driver',
        'jdl_second_driver',
        'jdl_departure_date',
        'jdl_departure_time',
        'jdl_arrival_date',
        'jdl_arrival_time',
        'jdl_pol_id',
        'jdl_pod_id',
        'jdl_ct_id',
        'jdl_container_number',
        'jdl_seal_number',
        'jdl_dp_id',
        'jdl_dp_date',
        'jdl_dp_time',
        'jdl_dp_start',
        'jdl_dp_end',
        'jdl_dp_ata',
        'jdl_dp_atd',
        'jdl_dr_id',
        'jdl_dr_date',
        'jdl_dr_time',
        'jdl_dr_start',
        'jdl_dr_end',
        'jdl_dr_ata',
        'jdl_dr_atd',
    ];

    /**
     * Abstract function to load the seeder query for table job_delivery.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jdl_consolidate',
            'jdl_transport_number',
            'jdl_departure_date',
            'jdl_departure_time',
            'jdl_arrival_date',
            'jdl_arrival_time',
            'jdl_container_number',
            'jdl_seal_number',
            'jdl_dp_date',
            'jdl_dp_time',
            'jdl_dp_start',
            'jdl_dp_end',
            'jdl_dp_ata',
            'jdl_dp_atd',
            'jdl_dr_date',
            'jdl_dr_time',
            'jdl_dr_start',
            'jdl_dr_end',
            'jdl_dr_ata',
            'jdl_dr_atd',
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
    public static function getByJobId(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jodId To store the reference value of the table.
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByJobIdAndSystem(int $jodId, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $jodId);
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $ssId);
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
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_rel_id, jo.jo_ss_id, jo.jo_srv_id, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                        jo.jo_srt_id, srt.srt_route as jo_srt_route, srt.srt_name as jo_service_term, srt.srt_container as jo_srt_container,
                        srt.srt_load as jo_srt_load, srt.srt_unload as jo_srt_unload, srt.srt_pol as jo_srt_pol, srt.srt_pod as jo_srt_pod,
                        jo.jo_created_on, uc.us_name as jo_created_by, jo.jo_publish_on, up.us_name as jo_publish_by,  cust.rel_name as jo_customer,
                        jo.jo_start_on, jo.jo_document_on, udc.us_name as jo_document_by, jo.jo_finish_on, uf.us_name as jo_finish_by,
                        jo.jo_manager_id, um.us_name as jo_manager, jo.jo_vendor_id, relven.rel_name as jo_vendor,
                        jo.jo_deleted_on, ud.us_name as jo_deleted_by, jo.jo_deleted_reason,
                        jo.jo_vendor_pic_id, cpven.cp_name as jo_pic_vendor, jo.jo_vendor_ref, jo.jo_joh_id,
                        joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on, uh.us_name as jo_hold_by,
                        jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                         so.so_id, so.so_number, so.so_start_on, so.so_soh_id, soh.soh_deleted_on as so_soh_deleted_on,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as jo_customer_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as jo_aju_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as jo_bl_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as jo_packing_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as jo_sppb_ref,
                        jdl.jdl_id, jdl.jdl_jo_id, jdl.jdl_tm_id, tm.tm_code as jdl_tm_code, tm.tm_name as jdl_transport_module, jdl.jdl_eg_id, eg.eg_name as jdl_equipment_group,
                        jdl.jdl_consolidate, jdl.jdl_eq_id, eq.eq_description as jdl_equipment, eq.eq_license_plate as jdl_equipment_plate, eq.eq_doc_id as jdl_eq_doc_id,
                        jdl.jdl_transport_number, jdl.jdl_first_cp_id, fdr.cp_name as jdl_first_driver, jdl.jdl_second_cp_id,
                        sdr.cp_name as jdl_second_driver, jdl.jdl_departure_date, jdl.jdl_departure_time, jdl.jdl_arrival_date,
                        jdl.jdl_arrival_time, jdl.jdl_pol_id, pol.po_name as jdl_pol, jdl.jdl_pod_id, pod.po_name as jdl_pod,
                        cpol.cnt_name as jdl_pol_country, cpod.cnt_name as jdl_pod_country, jdl.jdl_so_id, so.so_number as jdl_so_number,
                        cust.rel_name as jdl_so_customer, so.so_container as jdl_container,
                        jdl.jdl_ct_id, ct.ct_name as jdl_container_type,
                        jdl.jdl_container_number, jdl.jdl_seal_number, dpo.of_rel_id as jdl_dp_rel_id, dpr.rel_name as jdl_dp_owner,
                        jdl.jdl_dp_id, dpo.of_name as jdl_dp_name, jdl.jdl_dp_date, jdl.jdl_dp_time, jdl.jdl_dp_start, jdl.jdl_dp_end,
                        jdl.jdl_dp_ata, jdl.jdl_dp_atd, jdl.jdl_dr_id, dr.of_name as jdl_dr_name, dr.of_rel_id as jdl_dr_rel_id, drr.rel_name as jdl_dr_owner,
                        jdl.jdl_dr_date, jdl.jdl_dr_time, jdl.jdl_dr_start, jdl.jdl_dr_end, jdl.jdl_dr_ata, jdl.jdl_dr_atd,
                        pic.cp_name as jdl_so_pic_customer
                        FROM job_delivery as jdl
                            INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                            INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                            INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                            INNER JOIN transport_module as tm ON jdl.jdl_tm_id = tm.tm_id
                            LEFT OUTER JOIN equipment_group as eg ON jdl.jdl_eg_id = eg.eg_id
                            LEFT OUTER JOIN sales_order as so ON jdl.jdl_so_id = so.so_id
                            LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id
                            LEFT OUTER JOIN relation as cust ON so.so_rel_id = cust.rel_id
                            LEFT OUTER JOIN contact_person as pic ON so.so_pic_id = pic.cp_id
                            LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                            LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                            LEFT OUTER JOIN equipment as eq ON jdl.jdl_eq_id = eq.eq_id
                            LEFT OUTER JOIN contact_person as fdr ON jdl.jdl_first_cp_id = fdr.cp_id
                            LEFT OUTER JOIN contact_person as sdr ON jdl.jdl_second_cp_id = sdr.cp_id
                            LEFT OUTER JOIN port as pol ON jdl.jdl_pol_id = pol.po_id
                            LEFT OUTER JOIN port as pod ON jdl.jdl_pod_id  = pod.po_id
                            LEFT OUTER JOIN country as cpol ON pol.po_cnt_id = cpol.cnt_id
                            LEFT OUTER JOIN country as cpod ON pod.po_cnt_id = cpod.cnt_id
                            LEFT OUTER JOIN container as ct ON jdl.jdl_ct_id = ct.ct_id
                            LEFT OUTER JOIN office as dpo ON jdl.jdl_dp_id = dpo.of_id
                            LEFT OUTER JOIN relation as dpr ON dpo.of_rel_id = dpr.rel_id
                            LEFT OUTER JOIN office as dr ON jdl.jdl_dr_id = dr.of_id
                            LEFT OUTER JOIN relation as drr ON dr.of_rel_id = drr.rel_id
                            LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                            LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
                            LEFT OUTER JOIN users as uc ON jo.jo_created_by = uc.us_id
                            LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                            LEFT OUTER JOIN users as uf ON jo.jo_finish_by = uf.us_id
                            LEFT OUTER JOIN users as udc ON jo.jo_document_by = udc.us_id
                            LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                            LEFT OUTER JOIN users as uh ON joh.joh_created_by = uh.us_id
                            LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                            LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                            LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_start_on DESC, jo.jo_publish_on DESC, jo.jo_id DESC';
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
        $query = 'SELECT count(DISTINCT (jo.jo_id)) AS total_rows
                        FROM job_delivery as jdl
                            INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                            INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                            INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                            INNER JOIN transport_module as tm ON jdl.jdl_tm_id = tm.tm_id
                            INNER JOIN equipment_group as eg ON jdl.jdl_eg_id = eg.eg_id
                            LEFT OUTER JOIN sales_order as so ON jdl.jdl_so_id = so.so_id
                            LEFT OUTER JOIN relation as cust ON so.so_rel_id = cust.rel_id
                            LEFT OUTER JOIN contact_person as pic ON so.so_pic_id = pic.cp_id
                            LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                            LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                            LEFT OUTER JOIN equipment as eq ON jdl.jdl_eq_id = eq.eq_id
                            LEFT OUTER JOIN contact_person as fdr ON jdl.jdl_first_cp_id = fdr.cp_id
                            LEFT OUTER JOIN contact_person as sdr ON jdl.jdl_second_cp_id = sdr.cp_id
                            LEFT OUTER JOIN port as pol ON jdl.jdl_pol_id = pol.po_id
                            LEFT OUTER JOIN port as pod ON jdl.jdl_pod_id  = pod.po_id
                            LEFT OUTER JOIN country as cpol ON pol.po_cnt_id = cpol.cnt_id
                            LEFT OUTER JOIN country as cpod ON pod.po_cnt_id = cpod.cnt_id
                            LEFT OUTER JOIN container as ct ON jdl.jdl_ct_id = ct.ct_id
                            LEFT OUTER JOIN office as dpo ON jdl.jdl_dp_id = dpo.of_id
                            LEFT OUTER JOIN relation as dpr ON dpo.of_rel_id = dpr.rel_id
                            LEFT OUTER JOIN office as dr ON jdl.jdl_dr_id = dpo.of_id
                            LEFT OUTER JOIN relation as drr ON dr.of_rel_id = drr.rel_id
                            LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                            LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
                            LEFT OUTER JOIN users as uc ON jo.jo_created_by = uc.us_id
                            LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                            LEFT OUTER JOIN users as uf ON jo.jo_finish_by = uf.us_id
                            LEFT OUTER JOIN users as udc ON jo.jo_document_by = udc.us_id
                            LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                            LEFT OUTER JOIN users as uh ON joh.joh_created_by = uh.us_id
                            LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                            LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                            LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 30): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'jo_number', 'jo_id');
    }


    /**
     * Function to load job delivery for updating container number.
     *
     * @param int $socId To store the sales order container id.
     *
     * @return array
     */
    public static function loadJobDeliveryRoadBySocId(int $socId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_soc_id', $socId);
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jdld.jdld_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('tm.tm_code', 'road');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jdl.jdl_id
                FROM job_delivery as jdl
                    INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                    INNER JOIN transport_module as tm ON jdl.jdl_tm_id = tm.tm_id
                    INNER JOIN job_delivery_detail as jdld ON jdl.jdl_id = jdld.jdld_jdl_id' . $strWhere;

        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

}
