<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Master\Goods;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table goods_unit.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsUnitDao extends AbstractBaseDao
{
    /**
     * Base dao constructor for goods_unit.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_unit', 'gdu', self::$Fields);
    }

    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gdu_id',
        'gdu_gd_id',
        'gdu_quantity',
        'gdu_uom_id',
        'gdu_qty_conversion',
        'gdu_length',
        'gdu_width',
        'gdu_height',
        'gdu_volume',
        'gdu_weight',
        'gdu_active',
    ];

    /**
     * Abstract function to load the seeder query for table goods_unit.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gdu_active',
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
        $wheres[] = '(gdu.gdu_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (\count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by goods id
     *
     * @param int $gdId To store the goods id.
     *
     * @return array
     */
    public static function getByGoodsId($gdId): array
    {
        $wheres = [];
        $wheres[] = '(gdu.gdu_gd_id = ' . $gdId . ')';

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
        $where[] = "(gdu.gdu_active = 'Y')";
        $where[] = '(gdu.gdu_deleted_on IS NULL)';

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
        $query = 'SELECT gdu.gdu_id, gdu.gdu_gd_id, gdu.gdu_quantity, gdu.gdu_uom_id, uom.uom_code as gdu_uom, uom.uom_name as gdu_uom_name,
                        gdu.gdu_qty_conversion, gdu.gdu_length, gdu.gdu_width, gdu.gdu_height, gdu.gdu_volume, gdu.gdu_weight, gdu.gdu_active
                        FROM goods_unit as gdu INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id' . $strWhere;
        $query .= ' ORDER BY gdu_id ';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $row['gdu_full_uom'] = $row['gdu_uom_name'] . ' (' . $row['gdu_uom'] . ')';
            $results[] = $row;
        }

        return $results;

    }


}
