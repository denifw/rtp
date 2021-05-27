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
 * Class to handle data access object for table job_movement_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobMovementDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jmd_id',
        'jmd_jm_id',
        'jmd_jid_id',
        'jmd_quantity',
        'jmd_gdu_id',
        'jmd_jis_id',
        'jmd_jid_new_id',
        'jmd_jis_new_id',
        'jmd_gdt_id',
        'jmd_gdt_remark',
        'jmd_gcd_id',
        'jmd_gcd_remark',
        'jmd_length',
        'jmd_width',
        'jmd_height',
        'jmd_volume',
        'jmd_weight',
    ];

    /**
     * Base dao constructor for job_movement_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_movement_detail', 'jmd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_movement_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jmd_gdt_remark',
            'jmd_gcd_remark',
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
        $where[] = '(jmd.jmd_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $where = [];
        $where[] = '(jmd_id = ' . $referenceValue . ')';
        $where[] = '(jmd_ss_id = ' . $systemSettingValue . ')';
        $data  = self::loadData($where);
        if (count($data) === 1) {
            return $data[0];
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
        $where[] = "(jmd_active = 'Y')";
        $where[] = '(jmd_deleted_on IS NULL)';

        return self::loadData($where);
    }


    /**
     * Function to get data by jmId.
     *
     * @param int $jmId To store the job movement reference id.
     *
     * @return array
     */
    public static function loadDataByJmId($jmId): array
    {
        $where = [];
        $where[] = '(jmd.jmd_jm_id = ' . $jmId . ')';
        $where[] = '(jmd.jmd_deleted_on IS NULL)';

        return self::loadData($where);
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
        $query = 'SELECT jmd.jmd_id, jmd.jmd_jm_id, jmd.jmd_jid_id, gd.gd_id as jmd_gd_id, gd.gd_sku as jmd_gd_sku, gd.gd_barcode as jmd_gd_barcode, 
                        gd.gd_name as jmd_gd_name, gdc.gdc_id as jmd_gdc_id, gdc.gdc_name as jmd_gdc_name, br.br_id as jmd_br_id,
                        br.br_name as jmd_br_name, jis.jis_stock as jmd_jid_stock, jmd.jmd_gdu_id, uom.uom_code as jmd_gdu_uom, 
                        jmd.jmd_quantity, jmd.jmd_jis_id, jmd.jmd_jid_new_id, jmd.jmd_jis_new_id, 
                        jid.jid_gdt_id as jmd_jid_gdt_id, jid.jid_gdt_remark as jmd_jid_gdt_remark, gdt1.gdt_code  as jmd_jid_gdt_code, gdt1.gdt_description as jmd_jid_gdt_description,
                        jid.jid_gcd_id as jmd_jid_gcd_id, jid.jid_gcd_remark as jmd_jid_gcd_remark, jmd.jmd_gdt_id, gdt.gdt_code as jmd_gdt_code, gdt.gdt_description as jmd_gdt_description,
                        jmd.jmd_gdt_remark, jmd.jmd_gcd_id, gcd.gcd_code as jmd_gcd_code, gcd.gcd_description as jmd_gcd_description, jmd.jmd_gcd_remark, 
                        jmd.jmd_length, jmd.jmd_height, jmd.jmd_width, jmd.jmd_volume, jmd.jmd_weight, 
                        (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as jmd_jid_volume,
                        (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as jmd_jid_weight,
                        jid.jid_serial_number as jmd_jid_serial_number, jid.jid_lot_number as jmd_jid_lot_number, jid.jid_packing_number as jmd_jid_packing_number, 
                        jid.jid_ji_id as jmd_jid_ji_id, jid.jid_jir_id as jmd_jid_jir_id, 
                        jm.jm_new_whs_id as jmd_whs_id, whs.whs_name as jmd_whs_name, jid.jid_expired_date as jmd_jid_expired_date
                    FROM job_movement_detail as jmd INNER JOIN 
                        job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                        job_inbound_detail as jid ON jmd.jmd_jid_id = jid.jid_id INNER JOIN
                        goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                        goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                        brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = jmd.jmd_gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                        warehouse_storage as whs ON jm.jm_new_whs_id = whs.whs_id LEFT OUTER JOIN
                        goods_damage_type as gdt1 ON jid.jid_gdt_id = gdt1.gdt_id LEFT OUTER JOIN
                        goods_damage_type as gdt ON jmd.jmd_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                        goods_cause_damage as gcd ON jmd.jmd_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                        (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                          from job_inbound_stock 
                          where (jis_deleted_on IS NULL)
                          GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record.
     *
     * @param int $jmId To store the job movement reference id.
     *
     * @return bool
     */
    public static function isDataValidToCompleteMovement($jmId): bool
    {
        $wheres = [];
        $wheres[] = '(jmd_jm_id = ' . $jmId . ')';
        $wheres[] = '(jmd_deleted_on IS NULL)';
        $wheres[] = '(jmd_gdt_id IS NOT NULL)';
        $wheres[] = '(jmd_volume IS NULL)';
        $wheres[] = '(jmd_weight IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jmd_id  
                    FROM job_movement_detail ' . $strWhere;
        $result = DB::select($query);

        return empty($result);
    }


    /**
     * Function to get all record.
     *
     * @param int $jmId To store the job movement reference id.
     *
     * @return bool
     */
    public static function isExistData($jmId): bool
    {
        $results = false;
        $wheres = [];
        $wheres[] = '(jmd_jm_id = ' . $jmId . ')';
        $wheres[] = '(jmd_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jmd_id  
                    FROM job_movement_detail ' . $strWhere;
        $result = DB::select($query);
        if (empty($result) === false) {
            $results = true;
        }

        return $results;
    }
}
