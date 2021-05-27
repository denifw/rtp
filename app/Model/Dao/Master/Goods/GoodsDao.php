<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Master\Goods;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Frame\System\SerialNumber\SerialNumber;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table goods.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Goods
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gd_id',
        'gd_ss_id',
        'gd_uom_id',
        'gd_rel_id',
        'gd_gdc_id',
        'gd_br_id',
        'gd_sku',
        'gd_name',
        'gd_description',
        'gd_sn',
        'gd_generate_sn',
        'gd_receive_sn',
        'gd_multi_sn',
        'gd_warranty',
        'gd_bundling',
        'gd_active',
        'gd_remark',
        'gd_doc_id',
        'gd_tonnage',
        'gd_min_tonnage',
        'gd_max_tonnage',
        'gd_cbm',
        'gd_min_cbm',
        'gd_max_cbm',
        'gd_packing',
        'gd_expired',
        'gd_tonnage_dm',
        'gd_cbm_dm',
    ];

    /**
     * Base dao constructor for goods.
     *
     */
    public function __construct()
    {
        parent::__construct('goods', 'gd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table goods.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gd_sku',
            'gd_name',
            'gd_description',
            'gd_sn',
            'gd_generate_sn',
            'gd_receive_sn',
            'gd_multi_sn',
            'gd_warranty',
            'gd_bundling',
            'gd_active',
            'gd_remark',
            'gd_tonnage',
            'gd_cbm',
            'gd_packing',
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
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_id = ' . $referenceValue . ')';
        $wheres[] = '(gd.gd_ss_id = ' . $systemSettingValue . ')';
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
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
        $wheres[] = '(gd.gd_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $skuName            To store the sku name value.
     * @param int    $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getBySkuAndSsId(string $skuName, int $systemSettingValue): array
    {
        $query = "SELECT gd.gd_id, gd.gd_name, gdu.gdu_id
                  FROM  goods AS gd LEFT OUTER JOIN
                        goods_unit AS gdu ON gdu.gdu_gd_id = gd.gd_id AND gdu.gdu_uom_id = gd.gd_uom_id
                  WHERE gd.gd_sku = '$skuName' AND gd.gd_ss_id = $systemSettingValue";
        $sqlResult = DB::select($query);
        $results = DataParser::arrayObjectToArray($sqlResult);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $barcode            To store the reference value of the table.
     * @param int    $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByBarcodeAndSystem($barcode, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = "(LOWER(gd.gd_barcode) = '" . mb_strtolower($barcode) . "')";
        $wheres[] = '(gd.gd_ss_id = ' . $systemSettingValue . ')';
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            return $results[0];
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
        $where[] = "(gd_active = 'Y')";
        $where[] = '(gd_deleted_on IS NULL)';

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
        $query = 'SELECT gd.gd_id, gd.gd_ss_id, gd.gd_rel_id, gd.gd_gdc_id, gd.gd_br_id, gd.gd_sku, gd.gd_name, gd.gd_active,
                         gdc.gdc_name as gd_category, br.br_name as gd_brand, rel.rel_name as gd_relation, 
                         rel.rel_short_name as gd_rel_short, gd.gd_uom_id, uom.uom_code as gd_uom_code, uom.uom_name as gd_uom_name, gd.gd_sn,
                         gd.gd_multi_sn, gd.gd_barcode, gd.gd_warranty, gd.gd_bundling, gd.gd_packing, gd.gd_generate_sn, gd.gd_receive_sn,
                         gd.gd_tonnage, gd.gd_min_tonnage, gd.gd_max_tonnage, gd.gd_cbm, gd.gd_min_cbm, gd.gd_max_cbm, gd.gd_remark, gd.gd_doc_id,
                         gd.gd_expired, gdu.gdu_id as gd_gdu_id, gdu.gdu_length as gd_length, gdu.gdu_width as gd_width, 
                         gdu.gdu_height as gd_height, gdu.gdu_volume as gd_volume, gdu.gdu_weight as gd_weight,
                         gd.gd_tonnage_dm, gd.gd_cbm_dm
                         FROM goods AS gd 
                            INNER JOIN goods_category AS gdc ON gdc.gdc_id = gd.gd_gdc_id 
                            INNER JOIN brand AS br ON br.br_id = gd.gd_br_id 
                            INNER JOIN relation AS rel ON rel.rel_id = gd.gd_rel_id 
                            INNER JOIN unit As uom ON gd.gd_uom_id = uom.uom_id
                            LEFT OUTER JOIN goods_unit as gdu ON gd.gd_id = gdu.gdu_gd_id AND gd.gd_uom_id = gdu.gdu_uom_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY gd.gd_active DESC, rel.rel_name, br.br_name, gdc.gdc_name, gd.gd_sku, gd.gd_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to format a full name goods.
     *
     * @param string $category To store the category goods.
     * @param string $brand    To store the brand goods.
     * @param string $goods    To store the name goods.
     * @param string $sku      To store the sku goods.
     *
     * @return string
     */
    public function formatFullName($category = '', $brand = '', $goods = '', $sku = ''): string
    {
        $temp = [];
        if (empty($brand) === false) {
            $temp[] = $brand;
        }
        if (empty($category) === false) {
            $temp[] = $category;
        }
        if (empty($goods) === false) {
            $temp[] = $goods;
        }
        if (empty($sku) === false) {
            $temp[] = $sku;
        }


        return implode(' ', $temp);
    }

    /**
     * Function to validate serial number prefix.
     *
     * @param int   $gdId   To store the id of goods.
     * @param array $inputs To store the input.
     *
     * @return string
     */
    public static function isValidSnPrefix($gdId, array $inputs): string
    {
        $query = 'SELECT gpf_id, gpf_prefix
                FROM goods_prefix 
                WHERE (gpf_deleted_on IS NULL) and (gpf_gd_id = ' . $gdId . ')';
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === true) {
            return '';
        }
        $data = DataParser::arrayObjectToArray($sqlResults);
        $valid = false;
        foreach ($data as $row) {
            $prefix = $row['gpf_prefix'];
            foreach ($inputs as $input) {
                $input = trim($input);
                if ($valid === false) {
                    $valid = (mb_strlen($prefix) < mb_strlen($input)) && (mb_strpos($input, $prefix) === 0);
                }
            }
        }
        if ($valid === false) {
            return Trans::getWord('invalidSerialNumberPrefix', 'validation');
        }

        return '';
    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (gd.gd_id)) AS total_rows
                   FROM goods AS gd INNER JOIN
                        goods_category AS gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                        brand AS br ON br.br_id = gd.gd_br_id INNER JOIN
                        relation AS rel ON rel.rel_id = gd.gd_rel_id INNER JOIN
                        unit as uom ON gd.gd_uom_id = uom.uom_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Get query movement
     *
     * @param int $gdId        To store the id of goods
     * @param int $qtyRequired To store the id of goods
     *
     * @return array
     */
    public static function generateSnGoodsData($gdId, $qtyRequired): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_id = ' . $gdId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT gd.gd_id, gd.gd_sku, gd.gd_name, rel.rel_short_name as gd_relation
                FROM goods as gd 
                INNER JOIN relation as rel ON gd.gd_rel_id = rel.rel_id  " . $strWhere;
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $row['total_quantity'] = $qtyRequired;
            $listSn = GoodsPrefixDao::doGenerateSn($row);
            foreach ($listSn as $sn) {
                $results[] = $sn;
            }
        }

        return $results;
    }

    /**
     * Get query movement
     *
     * @param int $gdId        To store the id of goods
     * @param int $qtyRequired To store the id of goods
     *
     * @return array
     */
    public static function generatePnGoodsData($gdId, $qtyRequired): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_id = ' . $gdId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT gd.gd_id, gd.gd_ss_id, gd.gd_sku, gd.gd_name, rel.rel_short_name as gd_relation
                FROM goods as gd 
                INNER JOIN relation as rel ON gd.gd_rel_id = rel.rel_id  " . $strWhere;
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $sn = new SerialNumber($row['gd_ss_id']);
            for ($i = 0; $i < $qtyRequired; $i++) {
                $number = $sn->loadNumber( 'WhPackingNumber');
                $row['gd_packing_number'] = $number;
                $results[] = $row;
            }
        }

        return $results;
    }

}
