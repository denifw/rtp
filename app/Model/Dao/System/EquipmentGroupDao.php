<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table equipment_group.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class EquipmentGroupDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'eg_id',
        'eg_name',
        'eg_code',
        'eg_sty_id',
        'eg_tm_id',
        'eg_container',
        'eg_active',
    ];

    /**
     * Base dao constructor for equipment_group.
     *
     */
    public function __construct()
    {
        parent::__construct('equipment_group', 'eg', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table equipment_group.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'eg_name',
            'eg_code',
            'eg_container',
            'eg_active',
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
        $where [] = '(eg.eg_id = ' . $referenceValue . ')';
        $results = self::loadData($where);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * @param int $referenceValue To store the reference value of the table
     * @param int $ssId To store the system setting value
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(eg_id = ' . $referenceValue . ')';
        $wheres[] = '(eg_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
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
        $where[] = "(eg_active = 'Y')";
        $where[] = '(eg_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all record.
     * @param array $orders To Store the List Condition Query
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT eg.eg_id, eg.eg_name, eg.eg_code, eg.eg_tm_id, tm.tm_name as eg_module,
                    eg.eg_active, eg.eg_sty_id, sty.sty_name as eg_sty_name, tm.tm_code as eg_tm_code,
                    eg.eg_container
                FROM equipment_group as eg
                LEFT JOIN transport_module as tm on eg.eg_tm_id = tm.tm_id
                LEFT JOIN system_type sty on eg.eg_sty_id = sty.sty_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * @param array $wheres
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (eg.eg_id)) AS total_rows
                    FROM equipment_group as eg
                    LEFT JOIN transport_module as tm on eg.eg_tm_id = tm.tm_id
                    LEFT JOIN system_type sty on eg.eg_sty_id = sty.sty_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'eg_name', 'eg_id');
    }

}
