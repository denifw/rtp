<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse\Bundling;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_bundling.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundlingDao extends AbstractBaseDao
{

    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jb_id',
        'jb_jo_id',
        'jb_wh_id',
        'jb_jog_id',
        'jb_et_date',
        'jb_et_time',
        'jb_start_pick_on',
        'jb_end_pick_on',
        'jb_start_pack_on',
        'jb_end_pack_on',
        'jb_start_store_on',
        'jb_end_store_on',
    ];

    /**
     * Base dao constructor for job_bundling.
     *
     */
    public function __construct()
    {
        parent::__construct('job_bundling', 'jb', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_bundling.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jb_et_date',
            'jb_et_time',
            'jb_start_pick_on',
            'jb_end_pick_on',
            'jb_start_pack_on',
            'jb_end_pack_on',
            'jb_start_store_on',
            'jb_end_store_on',
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
        $wheres[] = '(jb_id = ' . $referenceValue . ')';
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
     *
     * @return array
     */
    public static function getByJobOrder($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(jb.jb_jo_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     * @param int $srtId              To store the service terms id.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue, $srtId): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_id = ' . $referenceValue . ')';
        $wheres[] = '(jo.jo_srv_id = 1)';
        $wheres[] = '(jo.jo_srt_id = ' . $srtId . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $systemSettingValue . ')';
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
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jb.jb_id, jb.jb_jo_id, jb.jb_wh_id, jb.jb_et_date, jb.jb_et_time, jb.jb_jog_id,
                        jb.jb_start_pick_on, jb.jb_end_pick_on, jb.jb_start_pack_on, jb.jb_end_pack_on, jb.jb_start_store_on, jb.jb_end_store_on,
                        jo.jo_id, jo.jo_number, jo.jo_srv_id, jo.jo_srt_id, jo.jo_rel_id, rel.rel_name as jo_customer, jo.jo_order_date, jo.jo_pic_id,
                         pic.cp_name as jo_pic, jo.jo_order_of_id, oo.of_name as jo_order_of, jo.jo_invoice_of_id, oi.of_name as jo_invoice_of,
                         jo.jo_manager_id, manager.us_name as jo_manager, jo.jo_created_on, u1.us_name as created_by,
                         jo.jo_publish_on, u2.us_name as published_by, jo.jo_deleted_on, us.us_name as jo_deleted_by, jo.jo_deleted_reason,
                         jo.jo_start_on, jo.jo_finish_on, u3.us_name as finished_by, wh.wh_name as jb_warehouse,
                        jog.jog_id, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_goods, jog.jog_quantity,
                        jog.jog_gdu_id, uom.uom_code as jog_unit, jo.jo_customer_ref as jo_customer_ref,
                        joh.joh_id, joh.joh_reason, joh.joh_created_on, br.br_name as jog_gd_brand, gdc.gdc_name as jog_gd_category,
                        job.job_id as jb_outbound_id, gd.gd_sn as jog_gd_sn, ji.ji_id as jb_inbound_id, jo.jo_document_on,
                        srt.srt_route as jo_route
                FROM  job_bundling as jb
                    INNER JOIN job_order as jo ON jo.jo_id = jb.jb_jo_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                    INNER JOIN job_goods as jog ON jb.jb_jog_id = jog.jog_id
                    INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                    INNER JOIN brand as br ON gd.gd_br_id = br.br_id
                    INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                    INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                    INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
                    INNER JOIN warehouse as wh ON jb.jb_wh_id = wh.wh_id
                    LEFT OUTER JOIN office as oi ON oi.of_id = jo.jo_invoice_of_id
                    LEFT OUTER JOIN users as manager ON jo.jo_manager_id = manager.us_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN users as us ON jo.jo_deleted_by = us.us_id
                    LEFT OUTER JOIN users as u1 ON jo.jo_created_by = u1.us_id
                    LEFT OUTER JOIN users as u2 ON jo.jo_publish_by = u2.us_id
                    LEFT OUTER JOIN users as u3 ON jo.jo_finish_by = u3.us_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                    LEFT OUTER JOIN (select job_jo_id, job_id
                                        FROM job_outbound
                                        where job_deleted_on is null) as job ON jo.jo_id = job.job_jo_id
                    LEFT OUTER JOIN (select ji_jo_id, ji_id
                                        FROM job_inbound
                                        where ji_deleted_on is null) as ji ON jo.jo_id = ji.ji_jo_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get document action modal.
     *
     * @param int $joId    To store the job order id.
     * @param int $jbJogId To store the job packing Goods id.
     *
     * @return array
     */
    public static function doValidateCompletePicking($joId, $jbJogId): array
    {
        $result = [];
        $jogWheres = [];
        $jogWheres[] = '(jog_deleted_on IS NULL)';
        $jogWheres[] = '(jog_jo_id = ' . $joId . ')';
        $jogWheres[] = '(jog_id <> ' . $jbJogId . ')';
        $strJogWhere = ' WHERE ' . implode(' AND ', $jogWheres);
        $jodWheres = [];
        $jodWheres[] = '(j.jod_deleted_on IS NULL)';
        $jodWheres[] = '(job.job_jo_id = ' . $joId . ')';
        $strJodWhere = ' WHERE ' . implode(' AND ', $jodWheres);
        $query = 'SELECT jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick, (jog.qty_outbound - jod.qty_pick) as diff_qty
                FROM (SELECT jog_jo_id, sum(jog_quantity) as qty_outbound
                      FROM job_goods ' . $strJogWhere . ' GROUP BY jog_jo_id) as jog
                       INNER JOIN
                     (SELECT job.job_jo_id, sum(j.jod_quantity) as qty_pick
                      FROM job_outbound_detail as j INNER JOIN
                       job_outbound as job ON j.jod_job_id = job.job_id ' . $strJodWhere . ' GROUP BY job.job_jo_id) as jod ON jod.job_jo_id = jog.jog_jo_id
                GROUP BY jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick ';
        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $data = DataParser::objectToArray($sqlResult[0], [
                'jog_jo_id',
                'job_jo_id',
                'qty_outbound',
                'qty_pick',
                'diff_qty',
            ]);
            if ((float)$data['diff_qty'] > 0) {
                $result[] = Trans::getWord('outboundStorageNotMatch', 'message', '', [
                    'outbound' => $data['qty_outbound'],
                    'taken' => $data['qty_pick'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('outboundPickingEmpty', 'message');
        }
        return $result;
    }


}
