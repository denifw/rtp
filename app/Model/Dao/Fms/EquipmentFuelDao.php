<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Fms;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table equipment_fuel.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class EquipmentFuelDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'eqf_id', 'eqf_ss_id', 'eqf_eq_id', 'eqf_date', 'eqf_meter',
        'eqf_qty_fuel', 'eqf_cost', 'eqf_remark', 'eqf_confirm_by', 'eqf_confirm_on'
    ];

    /**
     * Base dao constructor for equipment_fuel.
     *
     */
    public function __construct()
    {
        parent::__construct('equipment_fuel', 'eqf', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table equipment_fuel.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'eqf_date', 'eqf_remark', 'eqf_confirm_on'
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
        $wheres[] = '(eqf.eqf_id = ' . $referenceValue . ')';
        $wheres[] = '(eqf.eqf_ss_id = ' . $systemSettingValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderList To store the list for sortir.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT eqf.eqf_id, eqf.eqf_ss_id, eqf.eqf_eq_id, eqf.eqf_date, eqf.eqf_meter,
                         eqf.eqf_qty_fuel, eqf.eqf_cost, eqf.eqf_remark, eqf.eqf_deleted_on,
                         eqf.eqf_deleted_reason, eqf.eqf_confirm_by, eqf.eqf_confirm_on,
                         eg.eg_name || \' - \' || eq.eq_description AS eqf_eq_name
                  FROM equipment_fuel AS eqf INNER JOIN
                       equipment AS eq ON eq.eq_id = eqf.eqf_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
