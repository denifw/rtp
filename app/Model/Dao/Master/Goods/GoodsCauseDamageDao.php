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
 * Class to handle data access object for table goods_cause_damage.
 *
 * @package    app
 * @subpackage Model\Dao\Mater\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCauseDamageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gcd_id',
        'gcd_ss_id',
        'gcd_code',
        'gcd_description',
        'gcd_active',
    ];

    /**
     * Base dao constructor for goods_cause_damage.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_cause_damage', 'gcd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table goods_cause_damage.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gcd_code',
            'gcd_description',
            'gcd_active',
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
        $where[] = '(gcd_id = ' . $referenceValue . ')';

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
        $where[] = '(gcd_id = ' . $referenceValue . ')';
        $where[] = '(gcd_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getBySystemId($systemSettingValue): array
    {
        $where = [];
        $where[] = '(gcd_ss_id = ' . $systemSettingValue . ')';
        $where[] = "(gcd_active = 'Y')";
        $where[] = '(gcd_deleted_on IS NULL)';

        return self::loadData($where);
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(gcd_active = 'Y')";
        $where[] = '(gcd_deleted_on IS NULL)';

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
        $query = 'SELECT gcd_id, gcd_code, gcd_description, gcd_ss_id, gcd_active
                        FROM goods_cause_damage' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


}
