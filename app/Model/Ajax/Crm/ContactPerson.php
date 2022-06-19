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
        $helper = new SqlHelper();
        $helper->addLikeWhere('cp.cp_name', $this->getStringParameter('search_key'));
        $helper->addStringWhere('cp.cp_of_id', $this->getStringParameter('cp_of_id'));
        $helper->addStringWhere('cp.cp_rel_id', $this->getStringParameter('cp_rel_id'));
        $helper->addStringWhere('cp.cp_active', 'Y');
        $helper->addNullWhere('cp_deleted_on');
        return ContactPersonDao::loadSingleSelectData('cp_name', $helper);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadNotUserData(): array
    {
        $helper = new SqlHelper();
        $helper->addLikeWhere('cp.cp_name', $this->getStringParameter('search_key'));
        $helper->addStringWhere('cp.cp_of_id', $this->getStringParameter('cp_of_id'));
        $helper->addStringWhere('cp.cp_rel_id', $this->getStringParameter('cp_rel_id'));
        $helper->addStringWhere('cp.cp_active', 'Y');
        $helper->addNullWhere('cp_deleted_on');
        $subHelper = new SqlHelper();
        $subHelper->addNullWhere('ump_deleted_on');
        $subHelper->addStringWhere('ump_ss_id', $this->getStringParameter('ump_ss_id'));
        $subHelper->addGroupBy('ump_cp_id');
        $helper->addWhere('(cp.cp_id NOT IN (SELECT ump_cp_id
                                        FROM user_mapping ' . $subHelper . '))');
        return ContactPersonDao::loadSingleSelectData('cp_name', $helper);
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
