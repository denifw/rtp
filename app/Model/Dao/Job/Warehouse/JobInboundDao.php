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
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inbound.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ji_id',
        'ji_jo_id',
        'ji_wh_id',
        'ji_eta_date',
        'ji_eta_time',
        'ji_ata_date',
        'ji_ata_time',
        'ji_rel_id',
        'ji_of_id',
        'ji_cp_id',
        'ji_vendor_id',
        'ji_pic_vendor',
        'ji_truck_number',
        'ji_container_number',
        'ji_seal_number',
        'ji_driver',
        'ji_driver_phone',
        'ji_so_id',
        'ji_soc_id',
    ];

    /**
     * Base dao constructor for job_inbound.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inbound', 'ji', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inbound.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ji_eta_date',
            'ji_eta_time',
            'ji_ata_date',
            'ji_ata_time',
            'ji_rel_id',
            'ji_of_id',
            'ji_cp_id',
            'ji_vendor_id',
            'ji_pic_vendor',
            'ji_truck_number',
            'ji_container_number',
            'ji_seal_number',
            'ji_driver',
            'ji_driver_phone',
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
     * @param int $ssId To store the reference value of system setting.
     *
     * @return array
     */
    public static function getByJobOrderAndSystemSetting(int $referenceValue, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $referenceValue);
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
                       ji.ji_id, ji.ji_jo_id, ji.ji_wh_id, wh.wh_name as ji_warehouse, ji.ji_eta_date, ji.ji_eta_time,
                       ji.ji_ata_date, ji.ji_ata_time, ji.ji_rel_id, shipper.rel_name as ji_shipper, ji.ji_of_id,
                       o.of_name as ji_shipper_address, ji.ji_cp_id, pic2.cp_name as ji_pic_shipper, ji.ji_vendor_id,
                       transporter.rel_name AS ji_vendor, ji.ji_truck_number,
                       (CASE WHEN ji.ji_soc_id IS NULL THEN ji.ji_container_number ELSE soc.soc_container_number END) as ji_container_number,
                       (CASE WHEN ji.ji_soc_id IS NULL THEN ji.ji_seal_number ELSE soc.soc_seal_number END) as ji_seal_number,
                       ji.ji_start_load_on, ji.ji_end_load_on, ji.ji_start_store_on, ji.ji_end_store_on,
                       pic3.cp_name as ji_vendor_pic, ji.ji_driver, ji.ji_driver_phone, jtr.jtr_id as jo_jtr_id, jo.jo_invoice_of_id, oo.of_name as jo_invoice_of
                FROM job_inbound as ji
                         INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                         INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                         INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                         LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id
                         LEFT OUTER JOIN sales_order_hold as soh ON so.so_soh_id = soh.soh_id
                         LEFT OUTER JOIN sales_order_container as soc ON ji.ji_soc_id = soc.soc_id
                         LEFT OUTER JOIN relation as shipper ON ji.ji_rel_id = shipper.rel_id
                         LEFT OUTER JOIN relation as transporter ON ji.ji_vendor_id = transporter.rel_id
                         LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                         LEFT OUTER JOIN office as o ON ji.ji_of_id = o.of_id
                         LEFT OUTER JOIN contact_person as pic2 ON ji.ji_cp_id = pic2.cp_id
                         LEFT OUTER JOIN contact_person as pic3 ON ji.ji_pic_vendor = pic3.cp_id
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
                         LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_ji_jo_id ' . $strWhere;
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
                        FROM job_inbound as ji
                         INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                         INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                         INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                         LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id
                         LEFT OUTER JOIN sales_order_container as soc ON ji.ji_soc_id = soc.soc_id
                         LEFT OUTER JOIN relation as shipper ON ji.ji_rel_id = shipper.rel_id
                         LEFT OUTER JOIN relation as transporter ON ji.ji_vendor_id = transporter.rel_id
                         LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                         LEFT OUTER JOIN office as o ON ji.ji_of_id = o.of_id
                         LEFT OUTER JOIN contact_person as pic2 ON ji.ji_cp_id = pic2.cp_id
                         LEFT OUTER JOIN contact_person as pic3 ON ji.ji_pic_vendor = pic3.cp_id
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
                         LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_ji_jo_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


    /**
     * Function to get document action modal.
     *
     * @param int $joId To store the job order id.
     *
     * @return array
     */
    public static function doValidateCompleteLoading(int $joId): array
    {
        $result = [];
        $goodsData = JobInboundReceiveDao::loadAllByJoOrderId($joId);
        foreach ($goodsData as $goods) {
            if (empty($goods['jir_id']) === true) {
                $result[] = Trans::getWord('pleaseUpdateQuantityActual', 'message', '', ['goods' => $goods['jog_name']]);
            }
        }


        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @param int $jiId To store the job inbound id.
     * @param array $user TO store the user.
     *
     * @return array
     */
    public static function doValidateCompleteStorage($jiId, array $user): array
    {
        $result = [];
        $diffQty = JobInboundDetailDao::getTotalDifferentQuantityLoadWithStoredByJobInboundId($jiId);
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getWord('inboundStorageNotMatch', 'message', '', [
                    'inbound' => $diffQty['qty_actual'],
                    'stored' => $diffQty['qty_stored'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('inboundStorageEmpty', 'message');
        }
        if (mb_strtolower($user['ss_name_space']) === 'mol' && empty($result) === true) {
            $valid = JobInboundDetailDao::isValidAllSerialNumberByJiId($jiId);
            if ($valid === false) {
                $result[] = Trans::getWord('invalidSerialNumberInbound', 'message');
            }
        }

        return $result;
    }
}
