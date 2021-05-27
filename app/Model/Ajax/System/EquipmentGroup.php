<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\EquipmentGroupDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo EquipmentGroup.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class EquipmentGroup extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('eg_name', $this->getStringParameter('search_key'));
        $wheres[] = '(eg_deleted_on IS NULL)';
        $wheres[] = "(eg_active = 'Y')";
        if ($this->isValidParameter('eg_tm_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eg_tm_id', $this->getIntParameter('eg_tm_id'));
        }
        if ($this->isValidParameter('eg_tm_code') === true) {
            $wheres[] = SqlHelper::generateStringCondition('tm.tm_code', $this->getStringParameter('eg_tm_code'));
        }
        if ($this->getStringParameter('eg_container', 'N') === 'Y') {
            $wheres[] = SqlHelper::generateStringCondition('eg.eg_container', $this->getStringParameter('eg_container'));
        }

        $orders = [
            'eg_name', 'eg_id'
        ];

        return EquipmentGroupDao::loadSingleSelectData($wheres, $orders, 20);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectAutoComplete(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('eg_name', $this->getStringParameter('search_key'));
        $wheres[] = '(eg_deleted_on IS NULL)';
        $wheres[] = "(eg_active = 'Y')";
        if ($this->isValidParameter('eg_tm_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eg_tm_id', $this->getIntParameter('eg_tm_id'));
        }
        if ($this->isValidParameter('eg_tm_code') === true) {
            $wheres[] = SqlHelper::generateStringCondition('tm.tm_code', $this->getStringParameter('eg_tm_code'));
        }
        if ($this->isValidParameter('eg_container') === true) {
            $wheres[] = SqlHelper::generateStringCondition('eg.eg_container', $this->getStringParameter('eg_container'));
        }

        $orders = [
            'eg_name', 'eg_id'
        ];

        return EquipmentGroupDao::loadSingleSelectData($wheres, $orders, 20);
    }

    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('eg_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('eg_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('eg_name', $this->getStringParameter('eg_name'));
        }
        if ($this->isValidParameter('eg_sty_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('sty.sty_name', $this->getStringParameter('eg_sty_name'));
        }
        $wheres[] = '(eg_deleted_on IS NULL)';
        $wheres[] = "(eg_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT eg_id, eg_name, eg_tm_id, sty.sty_name as eg_sty_name, sty.sty_group as eg_sty_group
                    FROM equipment_group as eg
                    INNER JOIN system_type as sty on sty.sty_id = eg.eg_sty_id' . $strWhere;
        $query .= ' ORDER BY eg_name';
        $query .= ' LIMIT 30 OFFSET 0';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }
}
