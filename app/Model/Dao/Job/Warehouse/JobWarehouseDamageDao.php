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
 * Class to handle data access object for table job_warehouse_damage.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobWarehouseDamageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jwld_id',
        'jwld_jwl_id',
        'jwld_quantity',
        'jwld_length',
        'jwld_width',
        'jwld_height',
        'jwld_volume',
        'jwld_net_weight',
        'jwld_gross_weight',
        'jwld_gdt_id',
        'jwld_jog_id',
        'jwld_stored',
    ];

    /**
     * Base dao constructor for job_warehouse_damage.
     *
     */
    public function __construct()
    {
        parent::__construct('job_warehouse_damage', 'jwld', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_warehouse_damage.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jwld_stored',
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
        $where[] = '(jwld.jwld_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
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
        $query = 'SELECT jwld.jwld_id, jwld.jwld_jwl_id, jwl.jwl_jog_id, jog.jog_gd_id, gd.gd_name as jwld_goods, gd.gd_sku as jwld_sku,
                        br.br_name as jwld_goods_brand, gdc.gdc_name as jwld_goods_category, jog.jog_uom_id, uom.uom_code as jwld_unit,
                        jwld.jwld_quantity, jwld.jwld_length, jwld.jwld_width, jwld.jwld_height, jwld.jwld_volume, jwld.jwld_net_weight,
                        jwld.jwld_gross_weight, jwld.jwld_stored, 
                        jwld.jwld_gdt_id, gdt.gdt_description as jwld_damage_type, jog.jog_quantity as jwld_quantity_planning,
                        jog.jog_production_number, jog.jog_production_date, jog.jog_available_date
                        FROM job_warehouse_damage as jwld INNER JOIN 
                        job_warehouse_load as jwl ON jwld.jwld_jwl_id = jwl.jwl_id INNER JOIN
                         job_goods as jog ON jwl.jwl_jog_id = jog.jog_id INNER JOIN
                          unit as uom ON jog.jog_uom_id = uom.uom_id INNER JOIN
                           goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                            brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                              goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                              goods_damage_type as gdt ON jwld.jwld_gdt_id = gdt.gdt_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'jwld_id', 'jwld_jwl_id', 'jwl_jog_id', 'jog_gd_id', 'jwld_goods', 'jwld_sku', 'jwld_goods_brand', 'jwld_goods_category',
            'jog_uom_id', 'jwld_unit', 'jwld_quantity', 'jwld_length', 'jwld_width', 'jwld_height', 'jwld_volume', 'jwld_net_weight',
            'jwld_gross_weight', 'jwld_stored', 'jwld_gdt_id', 'jwld_damage_type',
            'jwld_quantity_planning', 'jog_production_number', 'jog_production_date', 'jog_available_date']);

    }


    /**
     * Function to get data by job warehouse id.
     *
     * @param int $jowId To store the reference of job warehouse.
     *
     * @return array
     */
    public static function loadDataByJobWarehouseId($jowId): array
    {
        $wheres = [];
        $wheres[] = '(jwl.jwl_jow_id = ' . $jowId . ')';
        $wheres[] = '(jwld.jwld_deleted_on IS NULL)';

        return self::loadData($wheres);
    }


    /**
     * Function to get data by job warehouse id.
     *
     * @param int $jowId To store the reference of job warehouse.
     *
     * @return array
     */
    public static function loadSimpleDataByJobWarehouse($jowId): array
    {
        $wheres = [];
        $wheres[] = '(jwl.jwl_jow_id = ' . $jowId . ')';
        $wheres[] = '(jwld.jwld_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jwld.jwld_id, jwld.jwld_jwl_id, jwld.jwld_quantity,
                        jwld.jwld_gross_weight, jwld.jwld_stored
                        FROM job_warehouse_damage as jwld INNER JOIN 
                        job_warehouse_load as jwl ON jwld.jwld_jwl_id = jwl.jwl_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);
    }


}
