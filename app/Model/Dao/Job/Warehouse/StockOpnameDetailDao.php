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
 * Class to handle data access object for table stock_opname_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockOpnameDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sod_id',
        'sod_sop_id',
        'sod_whs_id',
        'sod_gd_id',
        'sod_production_number',
        'sod_serial_number',
        'sod_gdt_id',
        'sod_quantity',
        'sod_qty_figure',
        'sod_gdu_id',
        'sod_remark',
    ];

    /**
     * Base dao constructor for stock_opname_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('stock_opname_detail', 'sod', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table stock_opname_detail.
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
        $where[] = '(sod_id = ' . $referenceValue . ')';
        $data = self::loadData($where);
        if (\count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $sopId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByStockOpnameId($sopId, $limit = 0): array
    {
        $where = [];
        $where[] = '(sod.sod_sop_id = ' . $sopId . ')';
        $where[] = '(sod.sod_deleted_on IS NULL)';

        return self::loadData($where, $limit);
    }
    /**
     * Function to get data by reference value
     *
     * @param int $sopId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByStockOpnameDetailId($sopId, $limit = 0): array
    {
        $where = [];
        $where[] = '(sod.sod_sop_id = ' . $sopId . ')';
        $where[] = '(sod.sod_deleted_on IS NULL)';
        $where[] = '(sod.sod_qty_figure > 0)';

        return self::loadData($where, $limit);
    }

    /**
     * Function to get data summary of opname stock
     *
     * @param int $sopId To store the reference value of the table.
     *
     * @return array
     */
    public static function getSummaryByStockOpnameId($sopId, $limit = 0): array
    {
        $where = [];
        $where[] = '(sod.sod_sop_id = ' . $sopId . ')';
        $where[] = '(sod.sod_deleted_on IS NULL)';
        return self::loadSummaryData($where, $limit);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $sopId To store the reference value of the table.
     *
     * @return array
     */
    public static function getUncompleteFigureDataBySopId($sopId): array
    {
        $where = [];
        $where[] = '(sod.sod_sop_id = ' . $sopId . ')';
        $where[] = '(sod.sod_deleted_on IS NULL)';
        $where[] = '(sod.sod_qty_figure IS NULL)';

        return self::loadData($where);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $where = [];
        $where[] = '(sod_id = ' . $referenceValue . ')';
        $where[] = '(sod_ss_id = ' . $systemSettingValue . ')';

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
        $where[] = "(sod_active = 'Y')";
        $where[] = '(sod_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT sod.sod_id, sod.sod_sop_id, sod.sod_gd_id, sod.sod_production_number, sod.sod_gdt_id, sod.sod_quantity, sod.sod_qty_figure,
                            sod.sod_gdu_id, sod.sod_remark, gd.gd_sku as sod_gd_sku, gd.gd_name as sod_gd_name, br.br_name as sod_gd_brand,
                            gdc.gdc_name as sod_gd_category, gdt.gdt_code as sod_gdt_code, whs.whs_name as sod_whs_name, uom.uom_code as sod_gdu_uom,
                            gdt.gdt_description as sod_gdt_description, (CASE WHEN sod_qty_figure IS NULL THEN 1 ELSE 2 END) as sod_sort,
                            sod.sod_whs_id, sod.sod_serial_number
                    FROM stock_opname_detail as sod INNER JOIN
                        goods as gd ON sod.sod_gd_id = gd.gd_id INNER JOIN
                        goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                        brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                        warehouse_storage as whs ON sod.sod_whs_id = whs.whs_id INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = sod.sod_gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                        goods_damage_type as gdt ON sod.sod_gdt_id = gdt.gdt_id ' . $strWhere;
        $query .= ' ORDER BY sod_sort, whs.whs_name, sod.sod_gdt_id, gdc.gdc_name, br.br_name, gd.gd_name, sod.sod_gdu_id, sod.sod_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * @param array $wheres
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function loadSummaryData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'select whs.whs_name,
                        sod.sod_production_number,
                        sum(sod_quantity) as sod_quantity,
                        sum(sod.sod_qty_figure) as sod_qty_figure,
                        uom.uom_code as sod_gdu_uom
                from stock_opname_detail sod
                join goods_unit gdu on gdu.gdu_id = sod.sod_gdu_id
                join unit as uom ON gdu.gdu_uom_id = uom.uom_id
                join warehouse_storage whs on whs.whs_id = sod.sod_whs_id ' . $strWhere;
        $query .= ' group by whs.whs_id,sod.sod_production_number,uom.uom_code';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }

}

