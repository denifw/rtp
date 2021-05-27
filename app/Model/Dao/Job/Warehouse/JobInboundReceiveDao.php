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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inbound_receive.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundReceiveDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jir_id',
        'jir_ji_id',
        'jir_jog_id',
        'jir_quantity',
        'jir_qty_damage',
        'jir_gdt_id',
        'jir_gdt_remark',
        'jir_gcd_id',
        'jir_gcd_remark',
        'jir_stored',
        'jir_lot_number',
        'jir_serial_number',
        'jir_packing_number',
        'jir_expired_date',
    ];

    /**
     * Base dao constructor for job_inbound_receive.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inbound_receive', 'jir', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inbound_receive.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jir_gdt_remark',
            'jir_gcd_remark',
            'jir_packing_number',
            'jir_expired_date',
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
        $where = [];
        $where[] = '(jir_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all goods inbound receive.
     *
     * @param int $jobId To store the job order reference.
     *
     * @return array
     */
    public static function loadDataByJobOrderId($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $jobId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code as jog_unit, gd.gd_br_id, br.br_name as jog_goods_brand, gd.gd_gdc_id, 
                      gdc.gdc_name as jog_goods_category, jir.jir_id, jir.jir_quantity, jir.jir_qty_damage,
                      jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_weight, jog.jog_production_number, jog.jog_production_date,
                      gd.gd_sn as jog_gd_sn,jir.jir_lot_number, jir.jir_serial_number, jir.jir_stored
                        FROM job_goods as jog 
                            LEFT OUTER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id 
                            LEFT OUTER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id 
                            LEFT OUTER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id 
                            LEFT OUTER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id 
                            LEFT OUTER JOIN brand as br ON gd.gd_br_id = br.br_id 
                            LEFT OUTER JOIN job_inbound_receive as jir ON jog.jog_id = jir.jir_jog_id ' . $strWhere;
        $query .= ' GROUP BY jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku, gd.gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code, gd.gd_br_id, br.br_name, gd.gd_gdc_id, 
                      gdc.gdc_name, jir.jir_id, jir.jir_quantity, jir.jir_qty_damage, jog.jog_length, jog.jog_width, jog.jog_height, 
                      jog.jog_weight, jog.jog_production_number, jog.jog_production_date, gd.gd_sn';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all goods inbound receive.
     *
     * @param int $jobId To store the job order reference.
     *
     * @return array
     */
    public static function loadDataForStorageByJobOrderId($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $jobId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_gdu_id, uom.uom_code as jog_unit, gd.gd_br_id, br.br_name as jog_goods_brand, gd.gd_gdc_id, 
                      gdc.gdc_name as jog_goods_category, jir.jir_id, jir.jir_quantity, jir.jir_qty_damage,
                      (CASE WHEN (jid.total_stored IS NULL) THEN 0 ELSE jid.total_stored END) as jir_stored, 
                      jir.jir_gdt_id, gdt.gdt_description, gdt.gdt_code, jir.jir_gdt_remark, jir.jir_gcd_id, gcd.gcd_description, gcd.gcd_code, jir.jir_gcd_remark,
                      jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_weight, jog.jog_production_number, jog.jog_production_date, 
                      gd.gd_sn as jog_gd_sn
                        FROM job_goods as jog INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN 
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           job_inbound_receive as jir ON jog.jog_id = jir.jir_jog_id LEFT OUTER JOIN
                            goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                             goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                            (SELECT jid_jir_id, SUM(jid_quantity) as total_stored
                                FROM job_inbound_detail WHERE (jid_deleted_on IS NULL)
                                GROUP BY jid_jir_id) jid ON jir.jir_id = jid.jid_jir_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(jir_active = 'Y')";
        $where[] = '(jir_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jir.jir_id, jir.jir_ji_id, jir.jir_quantity, jir.jir_lot_number, jir.jir_serial_number, jir.jir_packing_number, 
                            jir.jir_expired_date, jir.jir_jog_id,
                            jog.jog_serial_number as jir_jog_number, jog.jog_gd_id as jir_gd_id, gd.gd_sku as jir_gd_sku,
                            gd.gd_name as jir_gd_name, br.br_name as jir_gd_brand, gdc.gdc_name as jir_gd_category, jog.jog_gdu_id as jir_gdu_id,
                            uom.uom_code as jir_uom_code, jir.jir_gdt_id, gdt.gdt_code as jir_gdt_code, gdt.gdt_description as jir_gdt_description,
                            jir.jir_gdt_remark, jir.jir_gcd_id, gcd.gcd_code as jir_gcd_code, gcd.gcd_description as jir_gcd_description,
                            jir.jir_gcd_remark, jir.jir_stored, jir.jir_weight, jir.jir_height, jir.jir_volume, jir.jir_width, jir.jir_length,
                            gd.gd_sn as jir_gd_sn, gd.gd_tonnage as jir_gd_tonnage, gd.gd_cbm as jir_gd_cbm,
                            jir.jir_created_on, gd.gd_multi_sn as jir_gd_multi_sn, gd.gd_receive_sn as jir_gd_receive_sn, gd.gd_generate_sn as jir_gd_generate_sn,
                            gd.gd_packing as jir_gd_packing, gd.gd_expired as jir_gd_expired, gd.gd_min_tonnage as jir_gd_min_tonnage, gd.gd_max_tonnage as jir_gd_max_tonnage,
                            gd.gd_min_cbm as jir_gd_min_cbm, gd.gd_max_cbm as jir_gd_max_cbm, gd.gd_tonnage_dm as jir_gd_tonnage_dm, gd.gd_cbm_dm as jir_gd_cbm_dm
                        FROM job_inbound_receive as jir
                            INNER JOIN job_goods as jog ON jir.jir_jog_id = jog.jog_id
                            INNER JOIN goods as gd ON gd.gd_id = jog.jog_gd_id
                            INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                            INNER JOIN goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id
                            INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                            INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
                            LEFT OUTER JOIN goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id
                            LEFT OUTER JOIN goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all record for warehouse.
     *
     * @param int $joId To store the job order reference.
     *
     * @return array
     */
    public static function loadAllByJoOrderId($joId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $joId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_gd_id, jir.jir_id, jog.jog_name 
                        FROM job_goods as jog 
                            LEFT OUTER JOIN (SELECT jir_id, jir_jog_id
                                                FROM job_inbound_receive
                                                WHERE jir_deleted_on IS NULL) as jir ON jog.jog_id = jir.jir_jog_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record for warehouse.
     *
     * @param int $joId  To store the job order reference.
     * @param int $jiId  To store the job order reference.
     * @param int $jogId To store the job order reference.
     *
     * @return array
     */
    public static function loadJobGoodsReceive($joId, $jiId, $jogId = 0): array
    {
        $wheres = [];
        if ($jogId !== 0) {
            $wheres[] = '(jog.jog_id = ' . $jogId . ')';
        }
        $wheres[] = '(jog.jog_jo_id = ' . $joId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_gd_name, br.br_name as jog_gd_brand,
                            gdc.gdc_name as jog_gd_category, jog.jog_gdu_id, uom.uom_code as jog_uom, (CASE WHEN js.qty_received IS NULL THEN 0 ELSE js.qty_received END) as qty_received,
                            (CASE WHEN jr.qty_returned IS NULL THEN 0 ELSE jr.qty_returned END) as qty_returned,
                            count(jp.jir_packing_number) as total_package,
                            jog.jog_quantity, gd.gd_generate_sn as jog_gd_generate_sn
                        FROM job_goods as jog
                            INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                            INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                            INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                            INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                            INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id 
                            LEFT OUTER JOIN (SELECT jir_jog_id, SUM(jir_quantity) as qty_received
                                                FROM job_inbound_receive
                                                WHERE (jir_deleted_on IS NULL) AND (jir_stored = 'Y') AND (jir_ji_id = " . $jiId . ")
                                                GROUP BY jir_jog_id) as js ON jog.jog_id = js.jir_jog_id 
                            LEFT OUTER JOIN (SELECT jir_jog_id, jir_packing_number
                                                FROM job_inbound_receive
                                                WHERE (jir_deleted_on IS NULL) AND (jir_packing_number IS NOT NULL) 
                                                    AND (jir_stored = 'Y') AND (jir_ji_id = " . $jiId . ")
                                                GROUP BY jir_jog_id, jir_packing_number) as jp ON jog.jog_id = jp.jir_jog_id 
                            LEFT OUTER JOIN (SELECT jir_jog_id, SUM(jir_quantity) as qty_returned
                                                FROM job_inbound_receive
                                                WHERE (jir_deleted_on IS NULL) AND (jir_stored = 'N') AND (jir_ji_id = " . $jiId . ")
                                                GROUP BY jir_jog_id) as jr ON jog.jog_id = jr.jir_jog_id " . $strWhere;
        $query .= ' GROUP BY jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku, gd.gd_name, br.br_name,
                            gdc.gdc_name, jog.jog_gdu_id, uom.uom_code, js.qty_received,
                            jr.qty_returned,
                            jog.jog_quantity, gd.gd_generate_sn';
        $query .= ' ORDER BY gd.gd_sku, jog.jog_id';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record for warehouse.
     *
     * @param array $wheres To store the job order reference.
     *
     * @return array
     */
    public static function loadPackingNumber(array $wheres = []): array
    {
        $wheres[] = '(jir_packing_number IS NOT NULL)';
        $wheres[] = '(jir_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jir_packing_number
                        FROM job_inbound_receive " . $strWhere;
        $query .= ' GROUP BY jir_packing_number';
        $query .= ' ORDER BY jir_packing_number';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


}
