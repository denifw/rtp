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
 * Class to handle data access object for table job_adjustment.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobAdjustmentDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ja_id',
        'ja_jo_id',
        'ja_wh_id',
        'ja_gd_id',
        'ja_complete_on',
    ];

    /**
     * Base dao constructor for job_adjustment.
     *
     */
    public function __construct()
    {
        parent::__construct('job_adjustment', 'ja', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_adjustment.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ja_complete_on',
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
     * @param int $joId To store the reference value of the table.
     * @param int $ssId To store the system setting value.
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
                       rel.rel_name as jo_customer, jo.jo_pic_id, pic.cp_name as jo_pic_customer, jo.jo_order_of_id,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_vendor_id, jo.jo_order_date,
                       jo.jo_deleted_on, ud.us_name as jo_deleted_by, jo.jo_deleted_reason, jo.jo_vendor_pic_id, jo.jo_vendor_ref, jo.jo_joh_id,
                       joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on, uh.us_name as jo_hold_by,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       ja.ja_id, ja.ja_complete_on, ja.ja_wh_id, wh.wh_name,
                       ja.ja_gd_id, gd.gd_sku as ja_goods
                FROM job_order AS jo
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN job_adjustment as ja ON jo.jo_id = ja.ja_jo_id
                    INNER JOIN warehouse as wh ON ja.ja_wh_id = wh.wh_id
                    INNER JOIN goods as gd ON gd.gd_id = ja.ja_gd_id
                    LEFT OUTER JOIN office as o ON jo.jo_invoice_of_id = o.of_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
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
                    LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_start_on DESC, jo.jo_publish_on DESC, jo.jo_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
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
                        FROM job_order AS jo
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN job_adjustment as ja ON jo.jo_id = ja.ja_jo_id
                    INNER JOIN warehouse as wh ON ja.ja_wh_id = wh.wh_id
                    INNER JOIN goods as gd ON gd.gd_id = ja.ja_gd_id
                    LEFT OUTER JOIN office as o ON jo.jo_invoice_of_id = o.of_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
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

}
