<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Master\Goods;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table goods_material.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class GoodsMaterialDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gm_id',
        'gm_gd_id',
        'gm_goods_id',
        'gm_quantity',
        'gm_gdu_id',
    ];

    /**
     * Base dao constructor for goods_material.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_material', 'gm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table goods_material.
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
        $wheres = [];
        $wheres[] = '(gm.gm_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $gdId To store the reference value of goods data.
     *
     * @return array
     */
    public static function getByGdId($gdId): array
    {
        $wheres = [];
        $wheres[] = '(gm.gm_gd_id = ' . $gdId . ')';
        $wheres[] = '(gm.gm_deleted_on IS NULL)';

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
        $query = 'SELECT gm.gm_id, gm.gm_gd_id, gm.gm_goods_id, gd.gd_sku as gm_gd_sku, gd.gd_name as gm_gd_name, 
                            gd.gd_gdc_id as gm_gdc_id, gdc.gdc_name as gm_gdc_name, gd.gd_br_id as gm_gd_br_id, 
                            br.br_name as gm_br_name, gm.gm_quantity, gm.gm_gdu_id, uom.uom_code as gm_uom_code 
                        FROM goods_material as gm INNER JOIN
                        goods as gd ON gm.gm_goods_id = gd.gd_id INNER JOIN
                        goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                        brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                        goods_unit as gdu ON gm.gm_gdu_id = gdu.gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param int $gdId To store the limit of the data.
     * @param int $whId The warehouse reference.
     *
     * @return array
     */
    public static function loadDataWithStock(int $gdId, int $whId): array
    {
        $wheres = [];
        $wheres[] = '(gm.gm_gd_id = ' . $gdId . ')';
        $wheres[] = '(gm.gm_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT gm.gm_id, gm.gm_gd_id, gm.gm_goods_id, gd.gd_sku as gm_gd_sku, gd.gd_name as gm_gd_name, 
                            gd.gd_gdc_id as gm_gdc_id, gdc.gdc_name as gm_gdc_name, gd.gd_br_id as gm_gd_br_id, 
                            br.br_name as gm_br_name, gm.gm_quantity, gm.gm_gdu_id, uom.uom_code as gm_uom_code,
                            j.jid_stock as gm_available_stock
                        FROM goods_material as gm INNER JOIN
                        goods as gd ON gm.gm_goods_id = gd.gd_id INNER JOIN
                        goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                        brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                        goods_unit as gdu ON gm.gm_gdu_id = gdu.gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                        (SELECT jid.jid_gd_id, jid.jid_gdu_id, SUM(jis.jis_total) as jid_stock
                            FROM job_inbound_detail as jid INNER JOIN
                                 warehouse_storage AS whs ON whs.whs_id = jid.jid_whs_id INNER JOIN
                            (SELECT jis_jid_id, SUM(jis_quantity) as jis_total
                                FROM job_inbound_stock
                                WHERE jis_deleted_on IS NULL
                                group by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                        WHERE (whs.whs_wh_id = ' . $whId . ') AND (jid.jid_deleted_on IS NULL) AND (jid.jid_gdt_id IS NULL) AND (jis.jis_total > 0)
                        GROUP BY jid.jid_gd_id, jid.jid_gdu_id) as j ON gm.gm_goods_id = j.jid_gd_id AND gm.gm_gdu_id = j.jid_gdu_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
