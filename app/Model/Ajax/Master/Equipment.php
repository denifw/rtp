<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\EquipmentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo Equipment.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Equipment extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('eq_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateOrLikeCondition(['eq.eq_description', 'eq.eq_license_plate'], $this->getStringParameter('search_key'));
            if ($this->isValidParameter('eq_eg_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('eq.eq_eg_id', $this->getIntParameter('eq_eg_id'));
            }
            if ($this->isValidParameter('eq_rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('eq.eq_rel_id', $this->getIntParameter('eq_rel_id'));
            }
            if ($this->isValidParameter('eq_tm_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('eg.eg_tm_id', $this->getIntParameter('eq_tm_id'));
            }
            if ($this->isValidParameter('eq_manage_by_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('eq.eq_manage_by_id', $this->getIntParameter('eq_manage_by_id'));
            }
            $wheres[] = '(eq_deleted_on IS NULL)';
            $wheres[] = SqlHelper::generateNullCondition('eq_deleted_on');
            $wheres[] = SqlHelper::generateNumericCondition('eq.eq_ss_id', $this->getIntParameter('eq_ss_id'));
            $wheres[] = SqlHelper::generateStringCondition('eq.eq_active', 'Y');
            return EquipmentDao::loadSingleSelectData($wheres);
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectDataForFms(): array
    {
        if ($this->isValidParameter('eq_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(eq_ss_id = ' . $this->getIntParameter('eq_ss_id') . ')';
            $wheres[] = StringFormatter::generateLikeQuery('eq_description', $this->getStringParameter('search_key'));
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT eq.eq_id, eg.eg_name || \' - \' || eq.eq_description AS eq_name
                      FROM equipment AS eq INNER JOIN
                           equipment_group AS eg ON eg.eg_id = eq.eq_eg_id' . $strWhere;
            $query .= ' ORDER BY eq.eq_description, eq.eq_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'eq_name', 'eq_id');
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadAutoCompleteData(): array
    {
        if ($this->isValidParameter('eq_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(eq_ss_id = ' . $this->getIntParameter('eq_ss_id') . ')';
            $wheres[] = StringFormatter::generateLikeQuery('eq_description', $this->getStringParameter('search_key'));
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT eq.eq_id, eg.eg_name || \' - \' || eq.eq_description AS eq_name,
                             eq.eq_primary_meter, eqm.eqm_meter, eqm.eqm_date
                      FROM equipment AS eq INNER JOIN
                           equipment_group AS eg ON eg.eg_id = eq.eq_eg_id LEFT OUTER JOIN
                           (SELECT eqm_eq_id, MAX(eqm_meter) AS eqm_meter, MAX(eqm_date) AS eqm_date
                            FROM equipment_meter
                            WHERE eqm_deleted_on IS NULL
                            GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id' . $strWhere;
            $query .= ' ORDER BY eq.eq_description, eq.eq_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $results = [];
            $data = DB::select($query);
            if (empty($data) === false) {
                $numberFormatter = new NumberFormatter();
                $tempResult = DataParser::arrayObjectToArray($data);
                foreach ($tempResult as $row) {
                    $meter = 0;
                    $indicator = '';
                    if ($row['eq_primary_meter'] === 'km') {
                        $indicator = 'Odometer';
                    } elseif ($row['eq_primary_meter'] === 'hours') {
                        $indicator = 'Hours Meter';
                    }
                    if (empty($row['eqm_meter']) === false) {
                        $meter = $numberFormatter->doFormatFloat($row['eqm_meter']);
                    }
                    $eqMeterText = 'Last ' . $indicator . ' update: ' . $meter . ' ' . $row['eq_primary_meter'];
                    if (empty($row['eqm_date']) === false) {
                        $eqmDate = DateTimeParser::format($row['eqm_date'], 'Y-m-d', 'd M Y');
                        $eqMeterText .= ' On ' . $eqmDate;
                    }
                    $row['eq_meter_number'] = $numberFormatter->doFormatFloat($row['eqm_meter']);
                    $row['eq_meter_text'] = $eqMeterText;
                    foreach ($row as $key => $value) {
                        $result[$key] = $value;
                    }
                    $result['text'] = $row['eq_name'];
                    $result['value'] = $row['eq_id'];
                    $results[] = $result;
                }
            }

            # return the data.
            return $results;
        }

        return [];

    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {

        $wheres = [];

        if ($this->isValidParameter('eq_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eq.eq_number', $this->getStringParameter('eq_number'));
        }
        if ($this->isValidParameter('eq_eg_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eg.eg_name', $this->getStringParameter('eq_eg_name'));
        }
        if ($this->isValidParameter('eq_sty_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sty.sty_name', $this->getStringParameter('eq_sty_name'));
        }
        if ($this->isValidParameter('eq_license_plate') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eq.eq_license_plate', $this->getStringParameter('eq_license_plate'));
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT eq.eq_id, eq.eq_ss_id, eq.eq_number, eq.eq_eg_id, eq.eq_fuel_consume,
                            eq.eq_license_plate, eg.eg_name as eq_eg_name, sty.sty_name as eq_sty_name
                    FROM equipment as eq
                    INNER JOIN equipment_group as eg on eg.eg_id = eq.eq_id
                    LEFT JOIN system_type as sty on sty.sty_id = eq.eq_sty_id' . $strWhere;
        $query .= ' ORDER BY eq.eq_number, eq.eq_id';
        $query .= ' LIMIT 50 OFFSET 0';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
