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
 * Class to handle data access object for table goods_number_history.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class GoodsNumberHistoryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gnh_id',
        'gnh_gpf_id',
        'gnh_year',
        'gnh_month',
        'gnh_number',
    ];

    /**
     * Base dao constructor for goods_number_history.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_number_history', 'gnh', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table goods_number_history.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gnh_year',
            'gnh_month',
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
        $wheres[] = '(gnh_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
        $wheres[] = '(gnh_id = ' . $referenceValue . ')';
        $wheres[] = '(gnh_ss_id = ' . $systemSettingValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $gdId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByGoodsId($gdId): array
    {
        $wheres = [];
        $wheres[] = '(gpf.gpf_gd_id = ' . $gdId . ')';
        $wheres[] = '(gpf.gpf_deleted_on IS NULL)';
        return self::loadData($wheres, [
            'gpf.gpf_prefix',
            'gnh.gnh_year DESC',
            'gnh.gnh_month DESC',
            'gnh.gnh_id',
        ]);
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
        $query = 'SELECT gnh.gnh_id, gnh.gnh_gpf_id, gpf.gpf_prefix as gnh_prefix, gnh.gnh_year, gnh.gnh_month, gnh.gnh_number
                        FROM goods_number_history as gnh 
                        INNER JOIN goods_prefix as gpf ON gpf.gpf_id = gnh.gnh_gpf_id' . $strWhere;

        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
