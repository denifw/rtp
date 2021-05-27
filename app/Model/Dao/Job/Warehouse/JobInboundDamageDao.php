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
 * Class to handle data access object for table job_inbound_damage.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundDamageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jidm_id',
        'jidm_jir_id',
        'jidm_quantity',
        'jidm_length',
        'jidm_width',
        'jidm_height',
        'jidm_volume',
        'jidm_weight',
        'jidm_gdt_id',
        'jidm_gdt_remark',
        'jidm_gcd_id',
        'jidm_gcd_remark',
        'jidm_stored',
    ];

    /**
     * Base dao constructor for job_inbound_damage.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inbound_damage', 'jidm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inbound_damage.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jidm_gdt_remark',
            'jidm_gcd_remark',
            'jidm_stored',
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
        $where[] = '(jidm.jidm_id = ' . $referenceValue . ')';
        $data = self::loadData($where);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jiId To store the reference of job inbound.
     *
     * @return array
     */
    public static function loadDataByJobInboundId($jiId): array
    {
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $jiId . ')';
        $wheres[] = '(jidm.jidm_deleted_on IS NULL)';

        return self::loadData($wheres);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jirId To store the reference of job inbound.
     *
     * @return array
     */
    public static function loadDataByJobInboundReceiveId($jirId): array
    {
        $wheres = [];
        $wheres[] = '(jidm.jidm_jir_id = ' . $jirId . ')';
        $wheres[] = '(jidm.jidm_deleted_on IS NULL)';

        return self::loadData($wheres);
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
        $where[] = '(jidm.jidm_id = ' . $referenceValue . ')';
        $where[] = '(jidm.jidm_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(jidm.jidm_active = 'Y')";
        $where[] = '(jidm.jidm_deleted_on IS NULL)';

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
        $query = 'SELECT jidm.jidm_id, jidm.jidm_jir_id, jog.jog_serial_number as jidm_jog_number, jog.jog_gd_id, gd.gd_name as jidm_gd_name, gd.gd_sku as jidm_gd_sku,
                        br.br_name as jidm_br_name, gdc.gdc_name as jidm_gdc_name, jog.jog_gdu_id as jidm_jog_gdu_id, uom.uom_code as jidm_jog_uom,
                        jidm.jidm_quantity, jidm.jidm_length, jidm.jidm_width, jidm.jidm_height, jidm.jidm_volume, jidm.jidm_weight, jidm.jidm_stored, 
                        jidm.jidm_gdt_id, gdt.gdt_code as jidm_gdt_code, gdt.gdt_description as jidm_gdt_description,
                        jog.jog_production_number as jidm_jog_production_number,
                        jidm.jidm_gcd_id, gcd.gcd_code as jidm_gcd_code, gcd.gcd_description as jidm_gcd_description, jidm.jidm_gdt_remark, jidm.jidm_gcd_remark, 
                        jir.jir_qty_damage as jidm_jir_qty_damage, (j.jir_damage_used - jidm.jidm_quantity) as jidm_jir_damage_used, 
                        gd.gd_id as jidm_gd_id, jog.jog_uom_id as jidm_jog_uom_id, jog.jog_production_date as jidm_jog_production_date, 
                        jog.jog_available_date as jidm_jog_available_date, jog.jog_id, gd.gd_barcode as jidm_gd_barcode, gd.gd_tonnage as jidm_gd_tonnage, 
                        gd.gd_cbm as jidm_gd_cbm
                    FROM job_inbound_damage as jidm INNER JOIN 
                        job_inbound_receive as jir ON jidm.jidm_jir_id = jir.jir_id INNER JOIN
                        job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                        goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                        brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                        goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                        goods_damage_type as gdt ON jidm.jidm_gdt_id = gdt.gdt_id INNER JOIN
                        goods_cause_damage as gcd ON jidm.jidm_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                        (SELECT jidm_jir_id, SUM(jidm_quantity) as jir_damage_used 
                        FROM job_inbound_damage 
                        WHERE (jidm_deleted_on IS NULL) 
                        GROUP BY jidm_jir_id) as j ON jir.jir_id = j.jidm_jir_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get data by job warehouse id.
     *
     * @param int $jowId To store the reference of job warehouse.
     *
     * @return array
     */
    public static function loadSimpleDataByJobWarehouseId($jowId): array
    {
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $jowId . ')';
        $wheres[] = '(jidm.jidm_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jidm.jidm_id, jidm.jidm_jir_id, jidm.jidm_quantity,
                        jidm.jidm_weight, jidm.jidm_stored
                        FROM job_inbound_damage as jidm INNER JOIN 
                        job_inbound_receive as jir ON jidm.jidm_jir_id = jir.jir_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

}
