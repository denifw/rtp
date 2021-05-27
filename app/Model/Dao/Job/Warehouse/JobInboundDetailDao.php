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

use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inbound_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jid_id',
        'jid_ji_id',
        'jid_jir_id',
        'jid_whs_id',
        'jid_gd_id',
        'jid_quantity',
        'jid_gdu_id',
        'jid_lot_number',
        'jid_serial_number',
        'jid_packing_number',
        'jid_adjustment',
        'jid_gdt_id',
        'jid_gdt_remark',
        'jid_gcd_id',
        'jid_gcd_remark',
        'jid_length',
        'jid_width',
        'jid_height',
        'jid_volume',
        'jid_weight',
    ];

    /**
     * Base dao constructor for job_inbound_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inbound_detail', 'jid', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inbound_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jid_adjustment',
            'jid_gdt_remark',
            'jid_gcd_remark',
            'jid_lot_number',
            'jid_serial_number',
            'jid_packing_number',
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
        $where[] = '(jid_id = ' . $referenceValue . ')';

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
        $where[] = '(jid_id = ' . $referenceValue . ')';
        $where[] = '(jid_ss_id = ' . $systemSettingValue . ')';

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
        $where[] = "(jid_active = 'Y')";
        $where[] = '(jid_deleted_on IS NULL)';

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
        $query = 'SELECT jid.jid_id, jid.jid_ji_id, jid.jid_gd_id, jid.jid_gdu_id, jid.jid_whs_id, whs.whs_name as jid_whs_name,
                      jid.jid_quantity, jid.jid_gdt_id, gdt.gdt_code as jid_gdt_code, gdt.gdt_description as jid_gdt_description, jid.jid_gdt_remark,
                      jid.jid_gcd_id, gcd.gcd_code as jid_gcd_code, gcd.gcd_description as jid_gcd_description, jid.jid_gcd_remark, jid.jid_jir_id,
                      jid.jid_gdu_id, uom.uom_code as jid_uom, jid.jid_adjustment,
                      jid.jid_jir_id, jid.jid_lot_number, jid.jid_expired_date, jid.jid_length, jid.jid_width, jid.jid_height,
                      gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, gdc.gdc_name as jid_gdc_name,
                      (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as jid_weight,
                      (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as jid_volume,
                      jid.jid_serial_number, jid.jid_packing_number
                        FROM job_inbound_detail as jid  INNER JOIN
                            job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                         warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                         goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                         goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                         goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                         goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id ' . $strWhere;
        $query .= ' ORDER BY jid.jid_gdt_id DESC, jid.jid_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record.
     *
     * @param array $data To store the list condition query.
     *
     * @return array
     */
    public static function doPrepareInboundDetailData(array $data = []): array
    {
        $results = [];
        if (empty($data) === false) {
            $gdDao = new GoodsDao();
            foreach ($data as $row) {
                $row['jid_goods'] = $gdDao->formatFullName($row['jid_gdc_name'], $row['jid_br_name'], $row['jid_gd_name']);
                $volume = (float)$row['jid_volume'];
                $weight = (float)$row['jid_weight'];
                $remarks = '';
                if (empty($row['jid_gdt_id']) === false) {
                    $row['jid_condition'] = new LabelDanger(Trans::getWord('damage'));
                    $remarks = $row['jid_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jid_gcd_description'];
                } else {
                    $row['jid_condition'] = new LabelSuccess(Trans::getWord('good'));
                }
                $row['jid_total_volume'] = $volume * (float)$row['jid_quantity'];
                $row['jid_total_weight'] = $weight * (float)$row['jid_quantity'];
                $row['jid_gd_name'] = $row['jid_gdc_name'] . ' ' . $row['jid_br_name'] . ' ' . $row['jid_gd_name'];
                $row['jid_remarks'] = $remarks;
                $results[] = $row;
            }
        }
        return $results;
    }


    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param int $jiId To store the job warehouse reference
     *
     * @return array
     */
    public static function getTotalDifferentQuantityLoadWithStoredByJobInboundId($jiId): array
    {
        $jirWheres = [];
        $jirWheres[] = '(jir_deleted_on IS NULL)';
        $jirWheres[] = "(jir_stored = 'Y')";
        $jirWheres[] = '(jir_ji_id = ' . $jiId . ')';
        $strJirWhere = ' WHERE ' . implode(' AND ', $jirWheres);
        $jidWheres = [];
        $jidWheres[] = '(jid_deleted_on IS NULL)';
        $jidWheres[] = '(jid_ji_id = ' . $jiId . ')';
        $strJidWhere = ' WHERE ' . implode(' AND ', $jidWheres);
        $query = 'SELECT jir.jir_ji_id, jir.qty_actual, jid.jid_ji_id, jid.qty_stored, (jir.qty_actual - jid.qty_stored) as diff_qty
                FROM (SELECT jir_ji_id, sum(jir_quantity) as qty_actual
                      FROM job_inbound_receive ' . $strJirWhere . ' GROUP BY jir_ji_id) as jir
                       INNER JOIN
                     (SELECT jid_ji_id, sum(jid_quantity) as qty_stored
                      FROM job_inbound_detail ' . $strJidWhere . ' GROUP BY jid_ji_id) as jid ON jid.jid_ji_id = jir.jir_ji_id
                GROUP BY jir.jir_ji_id, jid.jid_ji_id, jir.qty_actual, jid.qty_stored ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {

            return DataParser::objectToArray($sqlResult[0], [
                'jir_ji_id',
                'jid_ji_id',
                'qty_actual',
                'qty_stored',
                'diff_qty',
            ]);
        }

        return [];
    }

    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param array $wheres to store the condition.
     *
     * @return array
     */
    public static function loadAvailableStock(array $wheres = []): array
    {
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jid.jid_uom_id, uom.uom_code as jid_unit, jo.jo_number as jid_inbound_number,
                        gd.gd_sku as jid_sku, jog.jog_name as jid_goods, br.br_name as jid_goods_brand, gdc.gdc_name as jid_goods_category,
                        whs.whs_name as jid_storage, jis.jis_stock as jid_available_stock
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                job_order as jo ON jog.jog_jo_id = jo.jo_id INNER JOIN
                unit as uom ON jid.jid_uom_id = uom.uom_id INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock
                  from job_inbound_stock
                  where (jis_deleted_on IS NULL)
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id ' . $strWhere;
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {

            return DataParser::arrayObjectToArray($sqlResult, [
                'jid_id',
                'jid_uom_id',
                'jid_unit',
                'jid_inbound_number',
                'jid_sku',
                'jid_goods',
                'jid_goods_brand',
                'jid_goods_category',
                'jid_storage',
                'jid_available_stock',
            ]);
        }

        return [];
    }


    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param int $jiId To store the job warehouse reference
     *
     * @return bool
     */
    public static function isValidAllSerialNumberByJiId($jiId): bool
    {
        $valid = true;
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_serial_number IS NULL)';
        $wheres[] = '(jid.jid_serial_number IS NULL)';
        $wheres[] = '(jid.jid_ji_id = ' . $jiId . ')';
        $wheres[] = "(gd.gd_sn = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT COUNT(jid_id) as jid_total
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jir.jir_id = jid.jid_jir_id INNER JOIN
                job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
                goods as gd ON gd.gd_id = jog.jog_gd_id ' . $strWhere;

        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $rows = (int)DataParser::objectToArray($sqlResult[0])['jid_total'];
            if ($rows > 0) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Function to get the current stock by goods and unit id
     *
     * @param int $gdId  To store the id of goods
     * @param int $gduId To store the id of goods unit.
     *
     * @return float
     */
    public static function getStockByGoodsAndUnitId($gdId, $gduId): float
    {

        $result = 0.0;
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = '(jis.jis_total > 0)';
        $wheres[] = '(jid.jid_gd_id = ' . $gdId . ')';
        $wheres[] = '(jid.jid_gdu_id = ' . $gduId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_gd_id, jid.jid_gdu_id, SUM(jis.jis_total) as jid_stock
                FROM job_inbound_detail as jid INNER JOIN
                (SELECT jis_jid_id, SUM(jis_quantity) as jis_total
                    FROM job_inbound_stock
                    WHERE jis_deleted_on IS NULL
                    group by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWhere;
        $query .= ' GROUP BY jid.jid_gd_id, jid.jid_gdu_id';

        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $result = (float)DataParser::objectToArray($sqlResult[0])['jid_stock'];
        }

        return $result;
    }
}
