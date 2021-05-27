<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use Illuminate\Support\Facades\DB;

/**
 * Class to manage query for job warehouse
 *
 * @package    app
 * @subpackage Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobWarehouseDao
{

    /**
     * Function to get all record.
     *
     * @param int $soId To store the sales order reference.
     * @param int $joId To store the job order reference.
     *
     * @return array
     */
    public static function loadInboundOutboundDataForSo(int $soId = 0, int $joId = 0): array
    {
        $joWheres = [];
        $jiWheres = [];
        $jobWheres = [];
        if ($joId > 0) {
            $joWheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $joId);
        }
        $joWheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');

        if ($soId > 0) {
            $jiWheres[] = SqlHelper::generateNumericCondition('ji.ji_so_id', $soId);
            $jobWheres[] = SqlHelper::generateNumericCondition('job.job_so_id', $soId);
        }
        $strJiWheres = ' WHERE ' . implode(' AND ', array_merge($joWheres, $jiWheres));
        $strJobWheres = ' WHERE ' . implode(' AND ', array_merge($joWheres, $jobWheres));
        $query = 'SELECT jo.jo_id, jo.jo_number as jw_number, jo.jo_srv_id as jw_srv_id, srv.srv_name as jw_service, srt.srt_route as jw_srt_route,
                        jo.jo_srt_id as jw_srt_id, srt.srt_name as jw_service_term, ji.ji_id as jw_id,
                       ji.ji_rel_id as jw_rel_id, rel.rel_name as jw_relation, ji.ji_of_id as jw_of_id,
                       o.of_name as jw_office_relation, ji.ji_wh_id as jw_wh_id, wh.wh_name as jw_warehouse,
                       ji.ji_cp_id as jw_cp_id, cp.cp_name as jw_pic_relation, jo.jo_joh_id as jw_joh_id, jo.jo_jae_id as jw_jae_id,
                       jae.jae_jac_id as jw_jac_id, jac.jac_ac_id as jw_ac_id, ac.ac_code as jw_action,
                       ac.ac_style as jw_action_style, jo.jo_publish_on as jw_publish_on, jo.jo_start_on as jw_start_on,
                       jo.jo_finish_on as jw_finish_on, jo.jo_deleted_on as jw_deleted_on,
                       ji.ji_vendor_id as jw_transport_id, ven.rel_name as jw_transporter, ji.ji_driver as jw_driver,
                       ji.ji_truck_number as jw_truck_number, ji.ji_so_id as jw_so_id, ji.ji_soc_id as jw_soc_id,
                       (CASE WHEN ji.ji_soc_id IS NULL THEN ji.ji_container_number ELSE soc.soc_container_number END) as jw_container_number,
                       (CASE WHEN ji.ji_soc_id IS NULL THEN ji.ji_seal_number ELSE soc.soc_seal_number END) as jw_seal_number,
                       ji.ji_eta_date as jw_eta_date, ji.ji_eta_time as jw_eta_time, soc.soc_number as jw_soc_number,
                       ct.ct_name as jw_container_type, soc.soc_number as jw_container_id
                FROM job_inbound as ji
                INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                INNER JOIN relation as rel ON ji.ji_rel_id = rel.rel_id
                INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                LEFT OUTER JOIN relation as ven ON ji.ji_vendor_id = ven.rel_id
                LEFT OUTER JOIN office as o ON ji.ji_of_id = o.of_id
                LEFT OUTER JOIN contact_person as cp ON ji.ji_cp_id = cp.cp_id
                LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                LEFT OUTER JOIN sales_order_container as soc ON ji.ji_soc_id = soc.soc_id
                LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id' . $strJiWheres;
        $query .= ' UNION ALL ';
        $query .= 'SELECT jo.jo_id, jo.jo_number as jw_number, jo.jo_srv_id as jw_srv_id, srv.srv_name as jw_service, srt.srt_route as jw_srt_route,
                        jo.jo_srt_id as jw_srt_id, srt.srt_name as jw_service_term, job.job_id as jw_id,
                       job.job_rel_id as jw_rel_id, rel.rel_name as jw_relation, job.job_of_id as jw_of_id,
                       o.of_name as jw_office_relation, job.job_wh_id as jw_wh_id, wh.wh_name as jw_warehouse,
                       job.job_cp_id as jw_cp_id, cp.cp_name as jw_pic_relation, jo.jo_joh_id as jw_joh_id, jo.jo_jae_id as jw_jae_id,
                       jae.jae_jac_id as jw_jac_id, jac.jac_ac_id as jw_ac_id, ac.ac_code as jw_action,
                       ac.ac_style as jw_action_style, jo.jo_publish_on as jw_publish_on, jo.jo_start_on as jw_start_on,
                       jo.jo_finish_on as jw_finish_on, jo.jo_deleted_on as jw_deleted_on,
                       job.job_vendor_id as jw_transport_id, ven.rel_name as jw_transporter, job.job_driver as jw_driver,
                       job.job_truck_number as jw_truck_number, job.job_so_id as jw_so_id, job.job_soc_id as jw_soc_id,
                       (CASE WHEN job.job_soc_id IS NULL THEN job.job_container_number ELSE soc.soc_container_number END) as jw_container_number,
                       (CASE WHEN job.job_soc_id IS NULL THEN job.job_seal_number ELSE soc.soc_seal_number END) as jw_seal_number,
                       job.job_eta_date as jw_eta_date, job.job_eta_time as jw_eta_time, soc.soc_number as jw_soc_number,
                       ct.ct_name as jw_container_type, soc.soc_number as jw_container_id
                FROM job_outbound as job
                INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
                INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                INNER JOIN relation as rel ON job.job_rel_id = rel.rel_id
                INNER JOIN warehouse as wh ON job.job_wh_id = wh.wh_id
                LEFT OUTER JOIN relation as ven ON job.job_vendor_id = ven.rel_id
                LEFT OUTER JOIN office as o ON job.job_of_id = o.of_id
                LEFT OUTER JOIN contact_person as cp ON job.job_cp_id = cp.cp_id
                LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                LEFT OUTER JOIN sales_order_container as soc ON job.job_soc_id = soc.soc_id
                LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id' . $strJobWheres;
        $query .= ' ORDER BY jw_deleted_on DESC, jw_finish_on DESC, jw_start_on DESC, jw_publish_on DESC, jo_id DESC';
        $sqlResult = DB::select($query);

        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult);
        }

        return $result;

    }


}
