<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Relation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Relation.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Relation extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('rel_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateOrLikeCondition(['rel_short_name', 'rel_name'], $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('rel_ss_id', $this->getIntParameter('rel_ss_id'));
            if ($this->isValidParameter('rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('rel_id', $this->getIntParameter('rel_id'));
            }
            $wheres[] = SqlHelper::generateNullCondition('rel_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('rel_active', 'Y');
            $ids = [];
            if ($this->isValidParameter('default_id') === true) {
                $ids[] = $this->getIntParameter('default_id');
            }
            if ($this->isValidParameter('vendor_id') === true) {
                $ids[] = $this->getIntParameter('vendor_id');
            }
            if (empty($ids) === false) {
                $wheres[] = '(rel_id IN (' . implode(', ', $ids) . '))';
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT rel_id, rel_number, rel_name
                        FROM relation ' . $strWhere;
            $query .= ' ORDER BY rel_name, rel_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'rel_name', 'rel_id');
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadGoodsOwnerData(): array
    {
        if ($this->isValidParameter('rel_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateOrLikeCondition(['rel_short_name', 'rel_name'], $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('rel_ss_id', $this->getIntParameter('rel_ss_id'));
            if ($this->isValidParameter('rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('rel_id', $this->getIntParameter('rel_id'));
            }
            $wheres[] = '(rel_id IN (SELECT gd_rel_id
                                        FROM goods
                                        WHERE (gd_deleted_on IS NULL) AND (gd_ss_id = ' . $this->getIntParameter('rel_ss_id') . ')
                                        GROUP BY gd_rel_id))';
            $wheres[] = SqlHelper::generateNullCondition('rel_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('rel_active', 'Y');
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT rel_id, rel_name
                        FROM relation ' . $strWhere;
            $query .= ' ORDER BY rel_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'rel_name', 'rel_id');
        }

        return [];
    }
}
