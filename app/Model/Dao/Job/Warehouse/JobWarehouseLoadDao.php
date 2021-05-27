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
 * Class to handle data access object for table job_warehouse_load.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobWarehouseLoadDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jwl_id',
        'jwl_jow_id',
        'jwl_jog_id',
        'jwl_quantity',
        'jwl_qty_damage',
    ];

    /**
     * Base dao constructor for job_warehouse_load.
     *
     */
    public function __construct()
    {
        parent::__construct('job_warehouse_load', 'jwl', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_warehouse_load.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder();
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
        $where[] = '(jwl_id = ' . $referenceValue . ')';

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
        $where[] = '(jwl_deleted_on IS NULL)';

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
        $query = 'SELECT jwl_id
                        FROM job_warehouse_load' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


    /**
     * Function to get all record for warehouse.
     *
     * @param int $jobId To store the job order reference.
     *
     * @return array
     */
    public static function loadDataByJobId($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $jobId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code as jog_unit, gd.gd_br_id, br.br_name as jog_goods_brand, gd.gd_gdc_id, 
                      gdc.gdc_name as jog_goods_category, jwl.jwl_id, jwl_quantity, jwl.jwl_qty_damage 
                        FROM job_goods as jog LEFT OUTER JOIN
                         unit as uom ON jog.jog_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN 
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
                           job_warehouse_load as jwl ON jog.jog_id = jwl.jwl_jog_id ' . $strWhere;
        $query .= ' GROUP BY jog.jog_id, jog.jog_gd_id, gd.gd_sku, gd.gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code, gd.gd_br_id, br.br_name, gd.gd_gdc_id, 
                      gdc.gdc_name, jwl.jwl_id, jwl_quantity, jwl.jwl_qty_damage';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'jog_id', 'jog_gd_id', 'jog_sku', 'jog_goods', 'jog_name', 'jog_quantity', 'jog_uom_id', 'jog_unit',
            'gd_br_id', 'jog_goods_brand', 'gd_gdc_id', 'jog_goods_category', 'jwl_id', 'jwl_quantity', 'jwl_qty_damage',
        ]);

    }


    /**
     * Function to get all record for warehouse.
     *
     * @param int $jobId To store the job order reference.
     *
     * @return array
     */
    public static function loadAllJobGoodsLoadByJoId($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $jobId . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_gd_id, jwl.jwl_id, jwl_quantity, jwl.jwl_qty_damage, jog.jog_name 
                        FROM job_goods as jog LEFT OUTER JOIN
                           job_warehouse_load as jwl ON jog.jog_id = jwl.jwl_jog_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'jog_id', 'jog_gd_id', 'jwl_id', 'jwl_quantity', 'jwl_qty_damage', 'jog_name'
        ]);
    }


}
