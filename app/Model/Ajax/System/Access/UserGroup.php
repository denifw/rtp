<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Access\UserGroupDao;

/**
 * Class to handle the ajax request fo UserGroup.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserGroup extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateLikeCondition('usg_name', $this->getStringParameter('search_key'));
        }

        $wheres[] = '(' . SqlHelper::generateNullCondition('usg_ss_id') . ' OR ' . SqlHelper::generateStringCondition('usg_ss_id', $this->getStringParameter('usg_ss_id')) . ')';
        $wheres[] = SqlHelper::generateNullCondition('usg_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('usg_active', 'Y');
        return UserGroupDao::loadSingleSelectData('usg_name', $wheres);
    }
}
