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
use App\Model\Dao\Relation\ContactPersonDao;

/**
 * Class to handle the ajax request fo ContactPerson.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ContactPerson extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('cp.cp_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('cp_of_id') === true) {
            $wheres[] = '(cp.cp_of_id = ' . $this->getIntParameter('cp_of_id') . ')';
        }
        if ($this->isValidParameter('cp_rel_id') === true) {
            $wheres[] = '(o.of_rel_id = ' . $this->getIntParameter('cp_rel_id') . ')';
        }
        if ($this->isValidParameter('cp_rel_ids') === true) {
            $wheres[] = '(o.of_rel_id IN (' . $this->getStringParameter('cp_rel_ids') . '))';
        }
        $wheres[] = "(cp.cp_active = 'Y')";
        $wheres[] = '(cp.cp_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cp.cp_id, cp.cp_name
                    FROM contact_person as cp INNER JOIN
                    office as o ON cp.cp_of_id = o.of_id ' . $strWhere;
        $query .= ' ORDER BY cp.cp_name, cp.cp_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cp_name', 'cp_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadNotUserData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('cp.cp_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('cp_of_id') === true) {
            $wheres[] = '(cp.cp_of_id = ' . $this->getIntParameter('cp_of_id') . ')';
        }
        if ($this->isValidParameter('cp_rel_id') === true) {
            $wheres[] = '(o.of_rel_id = ' . $this->getIntParameter('cp_rel_id') . ')';
        }
        $wheres[] = '(cp.cp_id NOT IN (SELECT ump_cp_id
                                        FROM user_mapping
                                        WHERE (ump_deleted_on IS NULL) AND (ump_ss_id = ' . $this->getIntParameter('ump_ss_id') . ')
                                        GROUP BY ump_cp_id))';
        $wheres[] = "(cp.cp_active = 'Y')";
        $wheres[] = '(cp.cp_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cp.cp_id, cp.cp_name
                    FROM contact_person as cp INNER JOIN
                    office as o ON cp.cp_of_id = o.of_id ' . $strWhere;
        $query .= ' ORDER BY cp.cp_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cp_name', 'cp_id');
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('cp_id') === true) {
            return ContactPersonDao::getByReference($this->getIntParameter('cp_id'));
        }

        return [];
    }

}
