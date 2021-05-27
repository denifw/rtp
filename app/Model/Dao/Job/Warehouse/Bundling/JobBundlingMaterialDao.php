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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_bundling_material.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse\Bundling
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundlingMaterialDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jbm_id',
        'jbm_jbd_id',
        'jbm_jog_id',
        'jbm_lot_number',
        'jbm_serial_number',
        'jbm_quantity',
        'jbm_jid_id',
        'jbm_gd_id',
        'jbm_gdu_id',
        'jbm_gdt_id',
        'jbm_gdt_remark',
        'jbm_gcd_id',
        'jbm_gcd_remark',
        'jbm_packing_number',
        'jbm_expired_date',
        'jbm_length',
        'jbm_width',
        'jbm_height',
        'jbm_volume',
        'jbm_weight',
        'jbm_stored'
    ];

    /**
     * Base dao constructor for job_bundling_material.
     *
     */
    public function __construct()
    {
        parent::__construct('job_bundling_material', 'jbm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_bundling_material.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jbm_lot_number',
            'jbm_serial_number',
            'jbm_expired_date',
            'jbm_gcd_remark',
            'jbm_gdt_remark',
            'jbm_stored'
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
        $wheres[] = '(jbm.jbm_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jbdId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobBundlingDetail($jbdId): array
    {
        $wheres = [];
        $wheres[] = '(jbm.jbm_jbd_id = ' . $jbdId . ')';
        $wheres[] = '(jbm.jbm_deleted_on IS NULL)';
        return self::loadData($wheres);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jbId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobBundling($jbId): array
    {
        $wheres = [];
        $wheres[] = '(jbd.jbd_jb_id = ' . $jbId . ')';
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
        $wheres[] = '(jbm.jbm_deleted_on IS NULL)';
        return self::loadData($wheres);
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
        $query = 'SELECT jbm.jbm_id, jbm.jbm_jbd_id, jbm.jbm_jid_id, jbm.jbm_gd_id, jbm.jbm_gdu_id, jbm.jbm_jog_id, jog.jog_gd_id as jbm_gd_id,
                        gd.gd_sku as jbm_gd_sku, gd.gd_name as jbm_gd_name, br.br_name as jbm_gd_brand,
                        gdc.gdc_name as jbm_gd_category, jog.jog_gdu_id as jbm_gdu_id, uom.uom_code as jbm_uom_code,
                        jbm.jbm_lot_number, jbm.jbm_serial_number, jbm.jbm_quantity, gd.gd_sn as jbm_gd_sn,
                        gd.gd_tonnage as jbm_gd_tonnage, gd.gd_cbm as jbm_gd_cbm,
                        gd.gd_multi_sn as jbm_gd_multi_sn, gd.gd_receive_sn as jbm_gd_receive_sn, gd.gd_generate_sn as jbm_gd_generate_sn,
                        gd.gd_packing as jbm_gd_packing, gd.gd_expired as jbm_gd_expired, gd.gd_min_tonnage as jbm_gd_min_tonnage, gd.gd_max_tonnage as jbm_gd_max_tonnage,
                        gd.gd_min_cbm as jbm_gd_min_cbm, gd.gd_max_cbm as jbm_gd_max_cbm, gd.gd_tonnage_dm as jbm_gd_tonnage_dm, gd.gd_cbm_dm as jbm_gd_cbm_dm,
                        jbm.jbm_expired_date, jbm.jbm_packing_number, jbm.jbm_gcd_id, jbm.jbm_gcd_remark, jbm.jbm_gdt_id, jbm.jbm_gdt_remark,
                        jbm.jbm_length, jbm.jbm_height, jbm.jbm_width, jbm.jbm_volume, jbm.jbm_weight, jbm.jbm_stored
                        FROM job_bundling_material as jbm INNER JOIN
                             job_bundling_detail AS jbd ON jbd.jbd_id = jbm.jbm_jbd_id INNER JOIN
                        job_goods as jog ON jbm.jbm_jog_id = jog.jog_id INNER JOIN
                        goods as gd ON gd.gd_id = jog.jog_gd_id INNER JOIN
                        brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                        goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                        unit as uom ON uom.uom_id = gdu.gdu_uom_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
