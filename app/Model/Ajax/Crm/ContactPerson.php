<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\ContactPersonDao;

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
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cp.cp_name', $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('cp_of_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cp.cp_of_id', $this->getStringParameter('cp_of_id'));
        }
        if ($this->isValidParameter('cp_rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('o.of_rel_id', $this->getStringParameter('cp_rel_id'));
        }
        $wheres[] = SqlHelper::generateStringCondition('cp.cp_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('cp.cp_deleted_on');
        return ContactPersonDao::loadSingleSelectData('cp_name', $wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadNotUserData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cp.cp_name', $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('cp_of_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cp.cp_of_id', $this->getStringParameter('cp_of_id'));
        }
        if ($this->isValidParameter('cp_rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('o.of_rel_id', $this->getStringParameter('cp_rel_id'));
        }
        $wheres[] = SqlHelper::generateStringCondition('cp.cp_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('cp.cp_deleted_on');
        $wheres[] = '(cp.cp_id NOT IN (SELECT ump_cp_id
                                        FROM user_mapping
                                        WHERE ' . SqlHelper::generateNullCondition('ump_deleted_on') .
            ' AND ' . SqlHelper::generateStringCondition('ump_ss_id', $this->getStringParameter('ump_ss_id')) .
            ' GROUP BY ump_cp_id))';
        return ContactPersonDao::loadSingleSelectData('cp_name', $wheres);
    }


    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('cp_id') === true) {
            return ContactPersonDao::getByReference($this->getStringParameter('cp_id'));
        }
        return [];
    }

}
