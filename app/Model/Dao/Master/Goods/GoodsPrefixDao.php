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

use App\Frame\Formatter\DateTimeParser;
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
class GoodsPrefixDao extends AbstractBaseDao
{
    /**
     * Base dao constructor for goods_unit.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_prefix', 'gpf', self::$Fields);
    }

    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gpf_id',
        'gpf_gd_id',
        'gpf_yearly',
        'gpf_monthly',
        'gpf_length',
        'gpf_prefix',
    ];

    /**
     * Abstract function to load the seeder query for table goods_unit.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gpf_yearly',
            'gpf_monthly',
            'gpf_prefix',
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
        $wheres[] = '(gpf_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
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
        $wheres[] = '(gpf_gd_id = ' . $gdId . ')';
        $wheres[] = '(gpf_deleted_on IS NULL)';

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
        $query = 'SELECT gpf_id, gpf_gd_id, gpf_prefix, gpf_yearly, gpf_monthly, gpf_length
                        FROM goods_prefix ' . $strWhere;
        $query .= ' ORDER BY gpf_prefix, gpf_id ';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);

    }


    /**
     * Function to get all record.
     *
     * @param array $goods To store the goods data.
     *
     * @return array
     */
    public static function doGenerateSn(array $goods): array
    {
        $results = [];
        $configs = self::getByGoodsId($goods['gd_id']);
        if (empty($configs) === false) {
            $config = $configs[0];
            $date = DateTimeParser::createDateTime();
            $year = '';
            $month = '';
            if ($config['gpf_yearly'] === 'Y') {
                $year = $date->format('y');
            }
            if ($config['gpf_monthly'] === 'Y') {
                $month = $date->format('m');
            }
            $config['year'] = $year;
            $config['month'] = $month;
            $numbers = self::loadNextNumbers($config, (int)$goods['total_quantity']);
            $maxLength = (int)$config['gpf_length'];
            foreach ($numbers as $num) {
                $sn = $config['gpf_prefix'] . $year . $month;
                $lengthNum = (int)mb_strlen($num);
                if ($lengthNum < $maxLength) {
                    $sn .= str_repeat('0', ($maxLength - $lengthNum));
                }
                $sn .= $num;
                $results[] = [
                    'gd_id' => $goods['gd_id'],
                    'gd_relation' => $goods['gd_relation'],
                    'gd_sku' => $goods['gd_sku'],
                    'gd_name' => $goods['gd_name'],
                    'gd_serial_number' => $sn,
                ];
            }
        }
        return $results;
    }

    /**
     * Function to load the next number for serial number.
     *
     * @param array $config To set the next number.
     * @param int   $range  to store the range of serial number.
     *
     * @return array
     */
    private static function loadNextNumbers(array $config, int $range): array
    {
        $wheres = [];
        $wheres[] = '(gnh_gpf_id = ' . $config['gpf_id'] . ')';
        if ($config['year'] === null || $config['year'] === '') {
            $wheres[] = '(gnh_year IS NULL)';
        } else {
            $wheres[] = "(gnh_year = '" . $config['year'] . "')";
        }
        if ($config['month'] === null || $config['month'] === '') {
            $wheres[] = '(gnh_month IS NULL)';
        } else {
            $wheres[] = "(gnh_month = '" . $config['month'] . "')";
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gnh_id, gnh_number
                        FROM goods_number_history ' . $strWhere;
        $query .= ' ORDER BY gnh_number DESC, gnh_id';
        $query .= '  LIMIT 1 OFFSET 0';

        $sqlResult = DB::select($query);
        $lastNumber = 0;
        $lastGnhId = null;
        if (count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0]);
            $lastNumber = (int)$result['gnh_number'];
            $lastGnhId = $result['gnh_id'];
        }
        $number = $lastNumber + $range;
        $colVal = [
            'gnh_gpf_id' => $config['gpf_id'],
            'gnh_year' => $config['year'],
            'gnh_month' => $config['month'],
            'gnh_number' => $number,
        ];
        $gnhDao = new GoodsNumberHistoryDao();
        if ($lastGnhId === null) {
            $gnhDao->doInsertTransaction($colVal);
        } else {
            $gnhDao->doUpdateTransaction($lastGnhId, $colVal);
        }
        return range($lastNumber + 1, $number, 1);
    }


}
