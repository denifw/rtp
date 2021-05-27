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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table goods_category.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Goods
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCategoryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'gdc_id',
        'gdc_ss_id',
        'gdc_name',
        'gdc_active',
    ];

    /**
     * Base dao constructor for goods_category.
     *
     */
    public function __construct()
    {
        parent::__construct('goods_category', 'gdc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table goods_category.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'gdc_name',
            'gdc_active',
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
        $query = 'SELECT gdc_id, gdc_ss_id, gdc_name, gdc_active
                         FROM goods_category
                         WHERE (gdc_id = ' . $referenceValue . ') AND (gdc_ss_id = ' . $systemSettingValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], self::$Fields);
        }

        return $result;
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(gdc_active = 'Y')";
        $where[] = '(gdc_deleted_on IS NULL)';

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
        $query = 'SELECT gdc_id, gdc_ss_id, gdc_name, gdc_active
                        FROM goods_category' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


}
