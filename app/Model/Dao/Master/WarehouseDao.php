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
 * Class to handle data access object for table warehouse.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class WarehouseDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'wh_id',
        'wh_ss_id',
        'wh_of_id',
        'wh_name',
        'wh_length',
        'wh_height',
        'wh_width',
        'wh_volume',
        'wh_active',
    ];

    /**
     * Base dao constructor for warehouse.
     *
     */
    public function __construct()
    {
        parent::__construct('warehouse', 'wh', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table warehouse.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'wh_name',
            'wh_active',
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
     * @param int $systemId       To store the id of the system setting.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemId): array
    {
        $where = [];
        $where [] = '(wh.wh_id = ' . $referenceValue . ')';
        $where [] = '(wh.wh_ss_id = ' . $systemId . ')';
        $results = self::loadData($where);
        if (\count($results) === 1) {
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
        $where = [];
        $where [] = '(wh.wh_id = ' . $referenceValue . ')';
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
        $where[] = "(wh.wh_active = 'Y')";
        $where[] = '(wh.wh_deleted_on IS NULL)';

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
        $query = 'SELECT wh.wh_id, wh.wh_name, wh.wh_length, wh.wh_width, wh.wh_height, wh.wh_volume, wh.wh_active, wh.wh_ss_id, 
                          wh.wh_of_id, o.of_name as wh_office, o.of_address as wh_address, o.of_postal_code as wh_postal_code,
                          cnt.cnt_name as wh_country, cty.cty_name as wh_city, stt.stt_name as wh_state, dtc.dtc_name as wh_district, 
                          ss.ss_relation
                        FROM warehouse as wh INNER JOIN 
                        system_setting as ss ON wh.wh_ss_id = ss.ss_id INNER JOIN
                        office as o ON wh.wh_of_id = o.of_id LEFT OUTER JOIN
                        country as cnt ON o.of_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                         state as stt ON o.of_stt_id = stt.stt_id LEFT OUTER JOIN
                          city as cty ON o.of_cty_id = cty.cty_id LEFT OUTER JOIN
                           district as dtc ON o.of_dtc_id = dtc.dtc_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, [
            'ss_relation',
            'wh_office',
            'wh_address',
            'wh_postal_code',
            'wh_country',
            'wh_city',
            'wh_state',
            'wh_district',
        ]));

    }


    /**
     * Function to get all active record.
     *
     * @param int $whId To store the warehouse id
     *
     * @return array
     */
    public static function getWarehouseAddress($whId): array
    {
        $wheres = [];
        $wheres[] = '(wh.wh_id = ' . $whId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT wh.wh_id, wh.wh_name, o.of_name as wh_office, o.of_address as wh_address, o.of_postal_code as wh_postal_code,
                          cnt.cnt_name as wh_country, cty.cty_name as wh_city, stt.stt_name as wh_state, dtc.dtc_name as wh_district
                        FROM warehouse as wh INNER JOIN 
                        office as o ON wh.wh_of_id = o.of_id LEFT OUTER JOIN
                        country as cnt ON o.of_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                         state as stt ON o.of_stt_id = stt.stt_id LEFT OUTER JOIN
                          city as cty ON o.of_cty_id = cty.cty_id LEFT OUTER JOIN
                           district as dtc ON o.of_dtc_id = dtc.dtc_id ' . $strWhere;
        $result = DB::select($query);
        if (\count($result) === 1) {
            return DataParser::objectToArray($result[0], [
                'wh_id',
                'wh_name',
                'wh_office',
                'wh_address',
                'wh_postal_code',
                'wh_country',
                'wh_city',
                'wh_state',
                'wh_district',
            ]);
        }

        return [];
    }

}
