<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Inklaring;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inklaring.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Inklaring
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobInklaringDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jik_id',
        'jik_jo_id',
        'jik_register_number',
        'jik_register_date',
        'jik_drafting_on',
        'jik_drafting_by',
        'jik_approve_on',
        'jik_approve_by',
        'jik_register_on',
        'jik_register_by',
        'jik_approve_pabean_on',
        'jik_approve_pabean_by',
        'jik_port_release_on',
        'jik_port_release_by',
        'jik_port_complete_on',
        'jik_port_complete_by',
        'jik_release_on',
        'jik_release_by',
        'jik_complete_release_on',
        'jik_complete_release_by',
        'jik_gate_pass_on',
        'jik_gate_pass_by',
        'jik_so_id',
    ];

    /**
     * Base dao constructor for job_inklaring.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inklaring', 'jik', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inklaring.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jik_register_number',
            'jik_register_date',
            'jik_drafting_on',
            'jik_approve_on',
            'jik_register_on',
            'jik_approve_pabean_on',
            'jik_port_release_on',
            'jik_port_complete_on',
            'jik_release_on',
            'jik_complete_release_on',
            'jik_gate_pass_on',
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
        $wheres[] = '(jo.jo_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId To store the reference value of system setting.
     *
     * @return array
     */
    public static function getByReferenceAndSystemSetting($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_id = ' . $referenceValue . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $ssId . ')';
        $result = self::loadData($wheres);
        if (count($result) === 1) {
            return $result[0];
        }

        return [];
    }

    public static function getByJobId(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }


    public static function getBySoId(int $soId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jik.jik_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_rel_id, jo.jo_ss_id, jo.jo_srv_id, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                           jo.jo_srt_id, srt.srt_route as jo_srt_route, srt.srt_name as jo_service_term, srt.srt_container as jo_srt_container,
                           srt.srt_load as jo_srt_load, srt.srt_unload as jo_srt_unload, srt.srt_pol as jo_srt_pol, srt.srt_pod as jo_srt_pod,
                           jo.jo_created_on, uc.us_name as jo_created_by, jo.jo_publish_on, up.us_name as jo_publish_by, rel.rel_name as jo_customer,
                           jo.jo_start_on, jo.jo_document_on, udc.us_name as jo_document_by, jo.jo_finish_on, uf.us_name as jo_finish_by,
                           jo.jo_manager_id, um.us_name as jo_manager, jo.jo_vendor_id, relven.rel_name as jo_vendor,
                            (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as jo_customer_ref,
                           jo.jo_deleted_on, ud.us_name as jo_deleted_by, jo.jo_deleted_reason,
                           jo.jo_vendor_pic_id, cpven.cp_name as jo_pic_vendor, jo.jo_vendor_ref, jo.jo_joh_id,
                           joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on, uh.us_name as jo_hold_by,
                           jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                           jik.jik_id, jik.jik_jo_id, jik.jik_register_number, jik.jik_register_date, jik.jik_drafting_on, jik.jik_approve_on,
                           jik.jik_approve_pabean_on, jik.jik_register_on, jik.jik_release_on, jik.jik_port_complete_on, jik.jik_complete_release_on,
                           jik.jik_gate_pass_on, jik.jik_port_complete_on, jik.jik_so_id, so.so_number, rel.rel_name as so_customer, so.so_customer_ref,
                           so.so_bl_ref, so.so_sppb_ref, so.so_packing_ref, so.so_aju_ref, pol.po_name as so_pol, polc.cnt_name as so_pol_country,
                           so.so_departure_date, so.so_departure_time, pod.po_name as so_pod, podc.cnt_name as so_pod_country,
                           so.so_arrival_date, so.so_arrival_time, jik.jik_closing_date, jik.jik_closing_time, cdt.cdt_name as so_document_type,
                            so.so_soh_id
                    FROM job_inklaring AS jik
                             INNER JOIN sales_order as so ON jik.jik_so_id = so.so_id
                             INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                             INNER JOIN job_order as jo ON jo.jo_id = jik.jik_jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             LEFT OUTER JOIN customs_document_type as cdt ON so.so_cdt_id = cdt.cdt_id
                             LEFT OUTER JOIN port as pol ON so.so_pol_id = pol.po_id
                             LEFT OUTER JOIN country as polc ON pol.po_cnt_id = polc.cnt_id
                             LEFT OUTER JOIN port as pod ON so.so_pod_id = pod.po_id
                             LEFT OUTER JOIN country as podc ON pod.po_cnt_id = podc.cnt_id
                             LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                             LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                             LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                             LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                             LEFT OUTER JOIN users as uc ON jo.jo_created_by = uc.us_id
                             LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
                             LEFT OUTER JOIN users as uf ON jo.jo_finish_by = uf.us_id
                             LEFT OUTER JOIN users as udc ON jo.jo_document_by = udc.us_id
                             LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                             LEFT OUTER JOIN users as uh ON joh.joh_created_by = uh.us_id
                             LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                             LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_start_on DESC, jo.jo_publish_on DESC, jik.jik_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param int $joId To store the limit of the data.
     *
     * @return array
     */
    public static function loadDataForCashAdvance($joId): array
    {
        $query = 'SELECT jo.jo_id, jo.jo_number, rel.rel_name as jo_customer,
                        so.so_customer_ref as jo_customer_ref, so.so_aju_ref as jo_aju_ref, so.so_bl_ref as jo_bl_ref, so.so_packing_ref as jo_packing_ref,
                        so.so_sppb_ref as jo_sppb_ref,
                        jik.jik_id, consignee.rel_name as jik_consignee, shipper.rel_name as jik_shipper,notify.rel_name as jik_notify,
                        pol.po_name as jik_pol_name, pod.po_name as jik_pod_name, cdt.cdt_name as jik_cdt, cct.cct_name as jik_cct,
                        srt.srt_name as jo_service_term
                        FROM job_inklaring AS jik INNER JOIN
                      job_order as jo ON jo.jo_id = jik.jik_jo_id  INNER JOIN
                      service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                      relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                      sales_order as so ON jik.jik_so_id = so.so_id INNER JOIN
                      customs_document_type as cdt ON cdt.cdt_id = jik.jik_cdt_id LEFT OUTER JOIN
                      relation as consignee ON consignee.rel_id = jik.jik_consignee_id LEFT OUTER JOIN
                      relation as shipper ON shipper.rel_id = jik.jik_shipper_id LEFT OUTER JOIN
                      relation as notify ON notify.rel_id = jik.jik_notify_id LEFT OUTER JOIN
                      customs_clearance_type as cct ON cct.cct_id = jik.jik_cct_id LEFT OUTER JOIN
                      port as pol ON pol.po_id = jik.jik_pol_id LEFT OUTER JOIN
                      port as pod ON pod.po_id = jik.jik_pod_id
                         WHERE (jo.jo_id = ' . $joId . ')';
        $result = DB::select($query);
        if (count($result) === 1) {
            return DataParser::objectToArray($result[0]);
        }

        return [];
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
        $query = 'SELECT count(DISTINCT (jik.jik_id)) AS total_rows
                       FROM job_inklaring AS jik
                             INNER JOIN sales_order as so ON jik.jik_so_id = so.so_id
                             INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                             INNER JOIN job_order as jo ON jo.jo_id = jik.jik_jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             LEFT OUTER JOIN customs_document_type as cdt ON so.so_cdt_id = cdt.cdt_id
                             LEFT OUTER JOIN port as pol ON so.so_pol_id = pol.po_id
                             LEFT OUTER JOIN country as polc ON pol.po_cnt_id = polc.cnt_id
                             LEFT OUTER JOIN port as pod ON so.so_pod_id = pod.po_id
                             LEFT OUTER JOIN country as podc ON pod.po_cnt_id = podc.cnt_id
                             LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                             LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                             LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                             LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                             LEFT OUTER JOIN users as uc ON jo.jo_created_by = uc.us_id
                             LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
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

}
