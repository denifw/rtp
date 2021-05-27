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
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo Container.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Container extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('ct_name', $this->getStringParameter('search_key'));

        $wheres[] = '(ct_deleted_on IS NULL)';
        $wheres[] = "(ct_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ct_id, ct_name, ct_code
                    FROM container' . $strWhere;
        $query .= ' ORDER BY ct_name, ct_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'ct_name', 'ct_id', true);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('ct_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('ct_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ct.ct_name', $this->getStringParameter('ct_name'));
        }
        $wheres[] = '(ct_deleted_on IS NULL)';
        $wheres[] = "(ct_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ct.ct_id, ct.ct_name
                    FROM container as ct' . $strWhere;
        $query .= ' ORDER BY ct_name';
        $query .= ' LIMIT 30 OFFSET 0';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }
}
