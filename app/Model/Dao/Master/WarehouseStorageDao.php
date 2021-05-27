<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Master;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table warehouse_storage.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class WarehouseStorageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'whs_id',
        'whs_wh_id',
        'whs_name',
        'whs_length',
        'whs_width',
        'whs_height',
        'whs_volume',
        'whs_active',
    ];

    /**
     * Base dao constructor for warehouse_storage.
     *
     */
    public function __construct()
    {
        parent::__construct('warehouse_storage', 'whs', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table warehouse_storage.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'whs_name',
            'whs_active',
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
     * @param int $whId To store the id of the warehouse.
     *
     * @return array
     */
    public static function getByWarehouseId($whId): array
    {
        $where = [];
        $where [] = '(whs_wh_id = ' . $whId . ')';

        return self::loadData($where);
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
        $where [] = '(whs_id = ' . $referenceValue . ')';
        $results = self::loadData($where);
        if (\count($results) === 1) {
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
        $where[] = "(whs_active = 'Y')";
        $where[] = '(whs_deleted_on IS NULL)';

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
        $query = 'SELECT whs.whs_id, whs.whs_wh_id, whs.whs_name, whs.whs_width, whs.whs_height, whs.whs_length, whs.whs_volume, whs.whs_active, 
                      wh.wh_id, wh.wh_name
                        FROM warehouse_storage as whs INNER JOIN
                        warehouse as wh ON whs.whs_wh_id = wh.wh_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
