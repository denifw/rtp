<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_goods.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobGoodsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jog_id',
        'jog_serial_number',
        'jog_jo_id',
        'jog_gd_id',
        'jog_name',
        'jog_quantity',
        'jog_uom_id',
        'jog_gdu_id',
        'jog_production_number',
        'jog_production_date',
        'jog_available_date',
        'jog_length',
        'jog_width',
        'jog_height',
        'jog_weight',
        'jog_volume',
        'jog_ji_jo_id',
    ];
    /**
     * The field for the table.
     *
     * @var array
     */
    protected static $ImportExcelHeader = [
        'sku',
        'qty',
        'uom',
    ];

    /**
     * Base dao constructor for job_goods.
     *
     */
    public function __construct()
    {
        parent::__construct('job_goods', 'jog', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_goods.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jog_name',
            'jog_production_number',
            'jog_production_date',
            'jog_available_date',
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
        $wheres[] = '(jog.jog_id = ' . $referenceValue . ')';

        return self::loadData($wheres)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $joId To store the job id of the table.
     *
     * @return array
     */
    public static function getByJobId($joId): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $joId . ')';

        return self::loadData($wheres);
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = '(jog.jog_deleted_on IS NULL)';

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
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code as jog_unit, jog.jog_production_date, jog.jog_production_number, jog.jog_production_number as jog_production_batch,
                      jog.jog_available_date, jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_weight,
                      jog.jog_volume, gd.gd_br_id, br.br_name as jog_br_name, gd.gd_gdc_id, gdc.gdc_name as jog_gdc_name
                        FROM job_goods as jog INNER JOIN
                        job_order as jo  ON jog.jog_jo_id = jo.jo_id LEFT OUTER JOIN
                         unit as uom ON jog.jog_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
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
    public static function loadWarehouseData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_gdu_id, uom.uom_code as jog_uom_code, br.br_name as jog_gd_brand, gdc.gdc_name as jog_gd_category,
                      gd.gd_sn as jog_gd_sn, gd.gd_tonnage as jog_gd_tonnage, gd.gd_cbm as jog_gd_cbm, gd.gd_multi_sn as jog_gd_multi_sn, gd.gd_receive_sn as jog_gd_receive_sn, gd.gd_generate_sn as jog_gd_generate_sn,
                            gd.gd_packing as jog_gd_packing, gd.gd_expired as jog_gd_expired, gd.gd_min_tonnage as jog_gd_min_tonnage, gd.gd_max_tonnage as jog_gd_max_tonnage,
                            gd.gd_min_cbm as jog_gd_min_cbm, gd.gd_max_cbm as jog_gd_max_cbm, gd.gd_tonnage_dm as jog_gd_tonnage_dm, gd.gd_cbm_dm as jog_gd_cbm_dm
                        FROM job_goods as jog
                            INNER JOIN job_order as jo  ON jog.jog_jo_id = jo.jo_id
                            INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                            INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                            INNER JOIN brand as br ON gd.gd_br_id = br.br_id
                            INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                            INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
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
    public static function loadDataForOutbound(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_gdu_id, uom.uom_code as jog_unit, jog.jog_production_date, jog.jog_production_number, jog.jog_production_number as jog_production_batch,
                      jog.jog_available_date, jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_weight,
                      jog.jog_volume, gd.gd_br_id, br.br_name as jog_br_name, gd.gd_gdc_id, gdc.gdc_name as jog_gdc_name,
                      gdu.gdu_volume as jog_gd_volume, gdu.gdu_weight as jog_gd_weight, (CASE WHEN jod.picking_qty IS NULL THEN 0 ELSE jod.picking_qty END) as jog_qty_picking,
                      loaded_qty as jog_qty_loaded, jog.jog_sog_id, sog.sog_name as jog_sog_name, sog.sog_uom_id as jog_sog_uom_id, jog.jog_ji_jo_id,
                        jo2.jo_number as jog_ji_jo_number
                        FROM job_goods as jog INNER JOIN
                        job_order as jo  ON jog.jog_jo_id = jo.jo_id LEFT OUTER JOIN
                        goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id LEFT OUTER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
                           (SELECT jod_jog_id, SUM(jod_quantity) as picking_qty, SUM(jod_qty_loaded) as loaded_qty
                            FROM job_outbound_detail
                            WHERE (jod_deleted_on IS NULL)
                            GROUP BY jod_jog_id) as jod ON jog.jog_id = jod.jod_jog_id LEFT OUTER JOIN
                            sales_order_goods as sog ON jog.jog_sog_id = sog.sog_id LEFT OUTER JOIN
                            job_order as jo2 ON jog.jog_ji_jo_id = jo2.jo_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadDataForInbound(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_sku, gd.gd_name as jog_goods, jog.jog_name, jog.jog_quantity,
                      jog.jog_gdu_id, gdu.gdu_uom_id, uom.uom_code as jog_unit, jog.jog_production_date, jog.jog_production_number,
                      jog.jog_available_date, gd.gd_barcode as jog_gd_barcode,
                      (CASE WHEN jir.jir_length IS NULL THEN gdu.gdu_length ELSE jir.jir_length END) as jog_length,
                      (CASE WHEN jir.jir_height IS NULL THEN gdu.gdu_height ELSE jir.jir_height END) as jog_height,
                      (CASE WHEN jir.jir_width IS NULL THEN gdu.gdu_width ELSE jir.jir_width END) as jog_width,
                      (CASE WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight ELSE jir.jir_weight END) as jog_weight,
                      (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as jog_volume,
                      gd.gd_br_id as jog_br_id, br.br_name as jog_br_name, gd.gd_gdc_id as jog_gdc_id, gdc.gdc_name as jog_gdc_name,
                      jir.jir_quantity as jog_quantity_actual, jir.jir_gdt_id as jog_damage_id,
                      gdt.gdt_description as jog_gdt_description, gdt.gdt_code as jog_gdt_code,
                      gcd.gcd_description as jog_gcd_description, gcd.gcd_code as jog_gcd_code, jir.jir_id, jir.jir_gdt_id, jir.jir_gcd_id,
                      jir.jir_gdt_remark, jir.jir_gcd_remark, jog.jog_sog_id, sog.sog_name as jog_sog_name, sog.sog_uom_id as jog_sog_uom_id
                        FROM job_goods as jog INNER JOIN
                        job_order as jo  ON jog.jog_jo_id = jo.jo_id LEFT OUTER JOIN
                        goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN
                          goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id LEFT OUTER JOIN
                          unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
                           job_inbound_receive as jir ON jog.jog_id = jir.jir_jog_id LEFT OUTER JOIN
                            goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                            goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                            sales_order_goods as sog ON jog.jog_sog_id = sog.sog_id' . $strWhere;
        $query .= ' ORDER BY gd.gd_sku, jog.jog_id, jir.jir_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $data To store the list condition query.
     *
     * @return array
     */
    public static function doPrepareDataForInbound(array $data = []): array
    {
        $results = [];
        if (empty($data) === false) {
            $tempJogIds = [];
            $number = new NumberFormatter();
            $gdDao = new GoodsDao();
            foreach ($data as $row) {
                $volume = (float)$row['jog_volume'];
                $weight = (float)$row['jog_weight'];
                $qtyReceive = (float)$row['jog_quantity_actual'];
                $qtyPlanning = (float)$row['jog_quantity'];
                $remarks = '';
                if (empty($row['jog_damage_id']) === false) {
                    $qtyGood = 0;
                    $qtyDamage = (float)$row['jog_quantity_actual'];
                    $remarks = $number->doFormatFloat($qtyDamage) . ' ' . $row['jog_unit'] . ' ' . $row['jog_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jog_gcd_description'] . ';';
                } else {
                    $qtyGood = (float)$row['jog_quantity_actual'];
                    $qtyDamage = 0;
                }
                if (empty($row['jir_id']) === false) {
                    $totalVolume = $qtyReceive * $volume;
                    $totalWeight = $qtyReceive * $weight;
                } else {
                    $totalVolume = $qtyPlanning * $volume;
                    $totalWeight = $qtyPlanning * $weight;
                }

                if (in_array($row['jog_id'], $tempJogIds, true) === false) {
                    $row['jog_gd_name'] = $row['jog_goods'];
                    $row['jog_goods'] = $gdDao->formatFullName($row['jog_gdc_name'], $row['jog_br_name'], $row['jog_goods']);
                    $row['jog_total_volume'] = $totalVolume;
                    $row['jog_total_weight'] = $totalWeight;
                    $row['jog_qty_received'] = $qtyReceive;
                    $row['jog_qty_good'] = $qtyGood;
                    $row['jog_qty_damage'] = $qtyDamage;
                    $row['jog_remarks'] = $remarks;

                    $results[] = $row;
                    $tempJogIds[] = $row['jog_id'];
                } else {
                    $index = array_search($row['jog_id'], $tempJogIds, true);
                    $results[$index]['jog_total_volume'] += $totalVolume;
                    $results[$index]['jog_total_weight'] += $totalWeight;
                    $results[$index]['jog_qty_received'] += $qtyReceive;
                    $results[$index]['jog_qty_good'] += $qtyGood;
                    $results[$index]['jog_qty_damage'] += $qtyDamage;
                    if (empty($remarks) === false) {
                        if (empty($results[$index]['jog_remarks']) === false) {
                            $results[$index]['jog_remarks'] .= '<br />' . $remarks;
                        } else {
                            $results[$index]['jog_remarks'] = $remarks;
                        }
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadSimpleData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog_id, jog_serial_number, jog_jo_id, jog_gd_id, jog_name, jog_quantity, jog_uom_id, jog_gdu_id,
                        jog_production_number, jog_production_date, jog_available_date, jog_width, jog_height, jog_length,
                        jog_volume, jog_weight, jog_total_tonnage, jog_total_cbm
                        FROM job_goods ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param int $joId To store the list condition query.
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadSimpleDataByJoId(int $joId, array $wheres = []): array
    {
        $wheres[] = SqlHelper::generateNumericCondition('jog.jog_jo_id', $joId);
        $wheres[] = SqlHelper::generateNullCondition('jog.jog_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name as gd_brand, gdc.gdc_name as gd_category
                        FROM job_goods as jog
                            INNER JOIN goods as gd ON gd.gd_id  = jog.jog_gd_id
                            INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                            INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id ' . $strWhere;
        $query .= ' GROUP BY gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record for warehouse.
     *
     * @param array $header To store the list condition query.
     *
     * @return array
     */
    public function lowerCaseExcelImportData(array $header): array
    {
        $results = [];

        foreach ($header as $row) {
            $results[] = mb_strtolower(trim($row));
        }

        return $results;
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $headers To store the list condition query.
     *
     * @return bool
     */
    public function isValidExcelImportHeader(array $headers): bool
    {
        $valid = true;
        foreach (self::$ImportExcelHeader as $col) {

            if (in_array($col, $headers, true) === false) {
                $valid = false;
            }
        }

        return $valid;
    }


    /**
     * Function to get all record for warehouse.
     *
     * @param array $data To store the list condition query.
     * @param array $header To store the list condition query.
     *
     * @return array
     */
    public function doFormatExcelImportData(array $data, array $header): array
    {
        $results = [];

        foreach ($data as $row) {
            $newRow = [];
            foreach (self::$ImportExcelHeader as $col) {
                $index = array_search($col, $header, true);
                $newRow[$col] = $row[$index];
            }
            $results[] = $newRow;
        }

        return $results;
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $data To store the list condition query.
     * @param array $header To store the list condition query.
     *
     * @return array
     */
    public function loadGoodsIdExcelImportData(array $data, array $header): array
    {
        $results = [];
//        $data = $this->doFormatExcelImportData($data, $header);
        if (empty($data) === false) {
            $skus = [];
            foreach ($data as $row) {
                if (empty($row['sku']) === false) {
                    $skus[] = $row['sku'];
                }
            }
            $wheres = [];
            $wheres[] = "(gd.gd_active = 'Y')";
            $wheres[] = '(gd.gd_deleted_on IS NULL)';
            $wheres[] = "(gdu.gdu_active = 'Y')";
            $wheres[] = '(gdu.gdu_deleted_on IS NULL)';
            $wheres[] = "(gd.gd_sku IN ('" . implode("','", $skus) . "'))";
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT gd.gd_id, gd.gd_sku, gdu.gdu_id, uom.uom_code
                        FROM goods as gd INNER JOIN
                            goods_unit as gdu ON gdu.gdu_gd_id = gd.gd_id INNER JOIN
                            unit as uom ON uom.uom_id = gdu.gdu_uom_id ' . $strWhere;
            $sqlResults = DB::select($query);
            $temp = DataParser::arrayObjectToArray($sqlResults);
            $goods = [];
            foreach ($temp as $row) {
                if (array_key_exists($row['gd_sku'], $goods) === false) {
                    $goods[$row['gd_sku']] = [];
                    $unit = [];
                    $unit[$row['uom_code']] = $row['gdu_id'];
                    $goods[$row['gd_sku']] = [
                        'gd_id' => $row['gd_id'],
                        'gd_unit' => $unit,
                    ];
                } else {
                    $goods[$row['gd_sku']]['gd_unit'][$row['uom_code']] = $row['gdu_id'];
                }
            }
            foreach ($data as $row) {
                $row['qty'] = (float)$row['qty'];
                if (array_key_exists($row['sku'], $goods) === true) {
                    $gd = $goods[$row['sku']];
                    $row['gd_id'] = $gd['gd_id'];
                    if (array_key_exists($row['uom'], $gd['gd_unit']) === true) {
                        $row['gdu_id'] = $gd['gd_unit'][$row['uom']];
                    } else {
                        $row['gdu_id'] = '';
                    }
                } else {
                    $row['gd_id'] = '';
                    $row['gdu_id'] = '';
                }
                $results[] = $row;
            }
        }
        return $results;
    }


    /**
     * Function to get all record for warehouse.
     *
     * @param int $joId To store the jo id
     * @param array $data To store the list condition query.
     *
     * @return array
     */
    public function doValidateExcelImportData(int $joId, array $data): array
    {
        $errors = [];
        $tempId = [];
        foreach ($data as $row) {
            if (in_array($row['gd_id'], $tempId, true) === false) {
                $tempId[] = $row['gd_id'];
            } else {
                $errors[] = Trans::getWord('duplicateGoodsImport', 'message', '', [
                    'sku' => $row['sku'],
                ]);
            }
            if (empty($row['gd_id']) === true || empty($row['gdu_id']) === true) {
                $errors[] = Trans::getWord('invalidGoodsImport', 'message', '', [
                    'sku' => $row['sku'],
                    'uom' => $row['uom'],
                ]);
            }
        }
        if (empty($errors) === true && empty($tempId) === false) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('jog.jog_jo_id', $joId);
            $wheres[] = SqlHelper::generateNullCondition('jog.jog_deleted_on');
            $wheres[] = '(jog.jog_gd_id IN (' . implode(',', $tempId) . '))';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jog.jog_id, jog.jog_gd_id, gd.gd_sku
                        FROM job_goods as jog
                        INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id ' . $strWhere;
            $sqlResults = DB::select($query);
            if (empty($sqlResults) === false) {
                $temp = DataParser::arrayObjectToArray($sqlResults);
                foreach ($temp as $row) {
                    $errors[] = Trans::getWord('goodsImportAlreadyRegistered', 'message', '', [
                        'sku' => $row['gd_sku'],
                    ]);
                }
            }
        }

        return $errors;
    }

    /*
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadDataForTrucking(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code as jog_unit, jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_weight,
                      jog.jog_volume, jog.jog_total_cbm, jog.jog_total_tonnage
                        FROM job_goods as jog INNER JOIN
                        job_order as jo  ON jog.jog_jo_id = jo.jo_id LEFT OUTER JOIN
                         unit as uom ON jog.jog_uom_id = uom.uom_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record for warehouse.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadSimpleDataForInbound(array $wheres = []): array
    {
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $wheres[] = '(so.so_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, jog.jog_name, jog.jog_quantity,
                      jog.jog_gdu_id, jog.jog_uom_id, jo.jo_id, jo.jo_number,
                      (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as jo_customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as jo_aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as jo_bl_ref
                        FROM job_goods as jog
                            INNER JOIN job_order as jo  ON jog.jog_jo_id = jo.jo_id
                            INNER JOIN job_inbound as ji ON ji.ji_jo_id = jo.jo_id
                            LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
