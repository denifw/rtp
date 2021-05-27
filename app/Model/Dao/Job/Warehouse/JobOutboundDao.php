<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_outbound.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutboundDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'job_id',
        'job_jo_id',
        'job_wh_id',
        'job_eta_date',
        'job_eta_time',
        'job_ata_date',
        'job_ata_time',
        'job_rel_id',
        'job_of_id',
        'job_cp_id',
        'job_vendor_id',
        'job_pic_vendor',
        'job_truck_number',
        'job_container_number',
        'job_start_load_on',
        'job_end_load_on',
        'job_start_start_on',
        'job_end_start_on',
        'job_driver',
        'job_driver_phone',
        'job_so_id',
        'job_soc_id',
    ];

    /**
     * Base dao constructor for job_outbound.
     *
     */
    public function __construct()
    {
        parent::__construct('job_outbound', 'job', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_outbound.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'job_eta_date',
            'job_eta_time',
            'job_ata_date',
            'job_ata_time',
            'job_truck_number',
            'job_container_number',
            'job_start_load_on',
            'job_end_load_on',
            'job_start_start_on',
            'job_end_start_on',
            'job_driver',
            'job_driver_phone',
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
        $wheres[] = SqlHelper::generateNumericCondition('job.job_id', $referenceValue);
        $result = self::loadData($wheres);
        if (count($result) === 1) {
            return $result[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $joId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJoId(int $joId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $joId);
        $result = self::loadData($wheres);
        if (count($result) === 1) {
            return $result[0];
        }
        return [];
    }

    /**
     * Function to get data by job order and system setting
     *
     * @param int $joId To store the reference value of the table.
     * @param int $ssId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJoIdAndSystem(int $joId, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $joId);
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $ssId);
        $result = self::loadData($wheres);
        if (count($result) === 1) {
            return $result[0];
        }
        return [];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(job_active = 'Y')";
        $where[] = '(job_deleted_on IS NULL)';

        return self::loadData($where);

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
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_rel_id, jo.jo_ss_id, jo.jo_srv_id, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                           jo.jo_srt_id, srt.srt_route as jo_srt_route, srt.srt_name as jo_service_term, srt.srt_container as jo_srt_container,
                           srt.srt_load as jo_srt_load, srt.srt_unload as jo_srt_unload, srt.srt_pol as jo_srt_pol, srt.srt_pod as jo_srt_pod,
                           jo.jo_created_on, uc.us_name as jo_created_by, jo.jo_publish_on, up.us_name as jo_publish_by,
                           jo.jo_start_on, jo.jo_document_on, udc.us_name as jo_document_by, jo.jo_finish_on, uf.us_name as jo_finish_by,
                           rel.rel_name as jo_customer, jo.jo_pic_id, pic.cp_name as jo_pic_customer, jo.jo_order_of_id, oo.of_name as jo_order_office,
                           jo.jo_manager_id, um.us_name as jo_manager, jo.jo_vendor_id, jo.jo_order_date,
                           jo.jo_deleted_on, ud.us_name as jo_deleted_by, jo.jo_deleted_reason, jo.jo_vendor_pic_id, jo.jo_vendor_ref, jo.jo_joh_id,
                           joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on, uh.us_name as jo_hold_by,
                           jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                           so.so_id, so.so_number, so.so_start_on, soc.soc_container_number, soc.soc_seal_number, so.so_soh_id, soh.soh_deleted_on as so_soh_deleted_on,
                           (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as jo_customer_ref,
                           (CASE WHEN so.so_id IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as jo_aju_ref,
                           (CASE WHEN so.so_id IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as jo_bl_ref,
                           (CASE WHEN so.so_id IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as jo_packing_ref,
                           (CASE WHEN so.so_id IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as jo_sppb_ref,
                           job.job_id, job.job_jo_id, job.job_wh_id, wh.wh_name as job_warehouse, job.job_eta_date, job.job_eta_time,
                           job.job_ata_date, job.job_ata_time, job.job_rel_id, cons.rel_name as job_consignee, job.job_of_id,
                           o.of_name as job_consignee_address, job.job_cp_id, pic2.cp_name as job_pic_consignee, job.job_vendor_id,
                           transporter.rel_name AS job_vendor, transporter.rel_short_name AS job_vendor_alias, job.job_truck_number,
                           (CASE WHEN job.job_soc_id IS NULL THEN job.job_container_number ELSE soc.soc_container_number END) as job_container_number,
                           (CASE WHEN job.job_soc_id IS NULL THEN job.job_seal_number ELSE soc.soc_seal_number END) as job_seal_number,
                           job.job_start_load_on, job.job_end_load_on, job.job_start_store_on, job.job_end_store_on,
                           pic3.cp_name as job_vendor_pic, job.job_driver, job.job_driver_phone, jtr.jtr_id as jo_jtr_id, jo.jo_invoice_of_id, oo.of_name as jo_invoice_of
                    FROM job_outbound as job
                             INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                             INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                             INNER JOIN warehouse as wh ON job.job_wh_id = wh.wh_id
                             LEFT OUTER JOIN sales_order as so ON job.job_so_id = so.so_id
                             LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id
                             LEFT OUTER JOIN sales_order_container as soc ON job.job_soc_id = soc.soc_id
                             LEFT OUTER JOIN relation as cons ON job.job_rel_id = cons.rel_id
                             LEFT OUTER JOIN relation as transporter ON job.job_vendor_id = transporter.rel_id
                             LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                             LEFT OUTER JOIN office as o ON job.job_of_id = o.of_id
                             LEFT OUTER JOIN contact_person as pic2 ON job.job_cp_id = pic2.cp_id
                             LEFT OUTER JOIN contact_person as pic3 ON job.job_pic_vendor = pic3.cp_id
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
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                             LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_job_jo_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_start_on DESC, jo.jo_publish_on DESC, jo.jo_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResult = DB::select($query);

        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult);
        }

        return $result;

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
                        FROM job_outbound as job
                             INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                             INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                             INNER JOIN warehouse as wh ON job.job_wh_id = wh.wh_id
                             LEFT OUTER JOIN sales_order as so ON job.job_so_id = so.so_id
                             LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id
                             LEFT OUTER JOIN sales_order_container as soc ON job.job_soc_id = soc.soc_id
                             LEFT OUTER JOIN relation as cons ON job.job_rel_id = cons.rel_id
                             LEFT OUTER JOIN relation as transporter ON job.job_vendor_id = transporter.rel_id
                             LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                             LEFT OUTER JOIN office as o ON job.job_of_id = o.of_id
                             LEFT OUTER JOIN contact_person as pic2 ON job.job_cp_id = pic2.cp_id
                             LEFT OUTER JOIN contact_person as pic3 ON job.job_pic_vendor = pic3.cp_id
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
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                             LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_job_jo_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


}
