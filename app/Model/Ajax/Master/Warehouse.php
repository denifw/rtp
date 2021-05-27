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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Warehouse.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Warehouse extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('wh_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('wh_name', $this->getStringParameter('search_key'));

            $wheres[] = '(wh_deleted_on IS NULL)';
            $wheres[] = '(wh_ss_id = ' . $this->getIntParameter('wh_ss_id') . ')';
            $wheres[] = "(wh_active = 'Y')";

            if ($this->isValidParameter('wh_of_id') === true) {
                $wheres[] = '(wh_of_id = ' . $this->getIntParameter('wh_of_id') . ')';
            }

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT wh_id, wh_name
                    FROM warehouse' . $strWhere;
            $query .= ' ORDER BY wh_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'wh_name', 'wh_id');
        }

        return [];
    }

    public function loadSingleSelectPrice(): array
    {
        $wheres = [];
        if($this->isValidParameter('wh_name')){
            $wheres[] = SqlHelper::generateLikeCondition('wh.wh_name', $this->getStringParameter('search_key'));
        }
        if($this->isValidParameter('wh_ss_id')){
            $wheres[] = '(wh.wh_ss_id = ' . $this->getIntParameter('wh_ss_id') . ')';
        }
        if ($this->isValidParameter('wh_of_id') === true) {
            $wheres[] = '(wh.wh_of_id = ' . $this->getIntParameter('wh_of_id') . ')';
        }
        $wheres[] = '(wh.wh_deleted_on IS NULL)';
        $wheres[] = "(wh_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT wh.wh_id, wh.wh_name, wh.wh_deleted_on, wh.wh_ss_id
                FROM warehouse as wh' . $strWhere;
        $query .= ' ORDER BY wh_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'wh_name', 'wh_id');
    }
}
