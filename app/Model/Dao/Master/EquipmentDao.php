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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table equipment.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class EquipmentDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'eq_id',
        'eq_ss_id',
        'eq_rel_id',
        'eq_number',
        'eq_description',
        'eq_eg_id',
        'eq_length',
        'eq_width',
        'eq_height',
        'eq_volume',
        'eq_weight',
        'eq_lgh_capacity',
        'eq_wdh_capacity',
        'eq_hgh_capacity',
        'eq_cbm_capacity',
        'eq_wgh_capacity',
        'eq_owt_id',
        'eq_manage_by_id',
        'eq_manager_id',
        'eq_sty_id',
        'eq_built_year',
        'eq_color',
        'eq_engine_capacity',
        'eq_fty_id',
        'eq_max_speed',
        'eq_license_plate', '
        eq_machine_number',
        'eq_chassis_number',
        'eq_bpkb',
        'eq_stnk',
        'eq_keur',
        'eq_picture',
        'eq_primary_meter',
        'eq_fuel_consume',
        'eq_eqs_id',
        'eq_active'
    ];

    /**
     * Base dao constructor for equipment.
     *
     */
    public function __construct()
    {
        parent::__construct('equipment', 'eq', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table equipment.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'eq_number', 'eq_description',
            'eq_color', 'eq_license_plate', 'eq_machine_number',
            'eq_chassis_number', 'eq_bpkb', 'eq_stnk', 'eq_keur', 'eq_primary_meter', 'eq_picture'
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
     * @param int $systemId To store the id of the system setting.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $systemId): array
    {
        $wheres = [];
        $wheres[] = '(eq.eq_id = ' . $referenceValue . ')';
        $wheres[] = SqlHelper::generateNumericCondition('eq.eq_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('eq.eq_ss_id', $systemId);
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
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('eq.eq_id', $referenceValue);
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT eq.eq_id, eq.eq_ss_id, eq.eq_number, eq.eq_description, eq.eq_eg_id, eg.eg_name as eq_group,
                        eq.eq_active, eq.eq_rel_id, rel.rel_name as eq_owner, eq.eq_fty_id,
                        eq.eq_length, eq.eq_width, eq.eq_height, eq.eq_volume, eq.eq_weight, eq.eq_lgh_capacity,
                        eq.eq_wdh_capacity, eq.eq_hgh_capacity, eq.eq_cbm_capacity, eq.eq_wgh_capacity,
                        eq.eq_owt_id, eq.eq_manage_by_id, eq.eq_manager_id, eq.eq_sty_id, eq.eq_built_year, eq.eq_color,
                        eq.eq_engine_capacity, eq.eq_max_speed, eq.eq_license_plate, eq.eq_machine_number, eq.eq_fuel_consume,
                        eq.eq_chassis_number, eq.eq_bpkb, eq.eq_stnk, eq.eq_keur, eq.eq_picture, eq.eq_primary_meter, eq.eq_eqs_id,
                        owt.owt_name as eq_owt_name, manageby.rel_name as eq_manage_by_name, manager.us_name as eq_manager_name,
                        sty1.sty_name AS eq_sty_name, sty2.sty_name as eq_fty_name,
                        eqs.eqs_name AS eq_eqs_name,
                        eg.eg_tm_id as eq_tm_id, tm.tm_code as eq_tm_code, tm.tm_name as eq_transport_module, eq.eq_driver_id, cp.cp_name as eq_driver
                 FROM equipment as eq
                     INNER JOIN equipment_group as eg ON eq.eq_eg_id = eg.eg_id
                     INNER JOIN transport_module as tm ON eg.eg_tm_id = tm.tm_id
                     INNER JOIN relation as rel ON rel.rel_id = eq.eq_rel_id
                     LEFT OUTER JOIN relation AS manageby ON manageby.rel_id = eq.eq_manage_by_id
                     LEFT OUTER JOIN ownership_type AS owt ON owt.owt_id = eq.eq_owt_id
                     LEFT OUTER JOIN users AS manager ON manager.us_id = eq.eq_manager_id
                     LEFT OUTER JOIN system_type AS sty1 ON sty1.sty_id = eq.eq_sty_id
                     LEFT OUTER JOIN system_type AS sty2 ON sty2.sty_id = eq.eq_fty_id
                     LEFT OUTER JOIN equipment_status AS eqs ON eqs.eqs_id = eq.eq_eqs_id
                     LEFT OUTER JOIN contact_person as cp on cp.cp_id = eq.eq_driver_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY eq.eq_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get total record.
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
        $query = 'SELECT count(DISTINCT (eq.eq_id)) AS total_rows
                        FROM equipment as eq
                     INNER JOIN equipment_group as eg ON eq.eq_eg_id = eg.eg_id
                     INNER JOIN transport_module as tm ON eg.eg_tm_id = tm.tm_id
                     INNER JOIN relation as rel ON rel.rel_id = eq.eq_rel_id
                     LEFT OUTER JOIN relation AS manageby ON manageby.rel_id = eq.eq_manage_by_id
                     LEFT OUTER JOIN ownership_type AS owt ON owt.owt_id = eq.eq_owt_id
                     LEFT OUTER JOIN users AS manager ON manager.us_id = eq.eq_manager_id
                     LEFT OUTER JOIN system_type AS sty1 ON sty1.sty_id = eq.eq_sty_id
                     LEFT OUTER JOIN system_type AS sty2 ON sty2.sty_id = eq.eq_fty_id
                     LEFT OUTER JOIN equipment_status AS eqs ON eqs.eqs_id = eq.eq_eqs_id' . $strWhere;

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
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 30): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'eq_description', 'eq_id');
    }

}
