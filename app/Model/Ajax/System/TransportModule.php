<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\TransportModuleDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo TransportType.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class TransportModule extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('tm_name', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNullCondition('tm_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('tm_active', 'Y');
        return TransportModuleDao::loadSingleSelectData($wheres, ['tm_name', 'tm_id'], 20);
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadNonRoadData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('tm_name', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNullCondition('tm_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('tm_active', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('tm_code', 'road', '<>');
        return TransportModuleDao::loadSingleSelectData($wheres, ['tm_name', 'tm_id'], 20);
    }

    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('tm.tm_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('tm_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tm.tm_code', $this->getStringParameter('tm_code'));
        }
        if ($this->isValidParameter('tm_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tm.tm_name', $this->getStringParameter('tm_name'));
        }
        $wheres[] = '(tm_deleted_on IS NULL)';
        $wheres[] = "(tm_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  tm.tm_code,tm.tm_id, tm.tm_name
                        FROM transport_module tm' . $strWhere;
        $query .= ' ORDER BY tm_name';
        $query .= ' LIMIT 30 OFFSET 0';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }
}
