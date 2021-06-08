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

/**
 * Class to handle the ajax request fo User.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class User extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('us.us_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('us_confirm') === true) {
            $wheres[] = SqlHelper::generateStringCondition('us.us_confirm', $this->getStringParameter('us_confirm'));
        } else {
            $wheres[] = SqlHelper::generateStringCondition('us.us_confirm', 'Y');
        }
        if ($this->isValidParameter('ss_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ump.ump_ss_id', $this->getStringParameter('ss_id'));
        }
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ump.ump_rel_id', $this->getStringParameter('rel_id'));
        }
        if ($this->isValidParameter('us_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('us.us_id', $this->getStringParameter('us_id'));
        }
        if ($this->isValidParameter('of_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('o.of_id', $this->getStringParameter('of_id'));
        }
        $wheres[] = SqlHelper::generateStringCondition('us.us_active', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('us.us_system', 'N');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_confirm', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('us.us_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us.us_id, us.us_name, us.us_username
                    FROM users as us INNER JOIN
                    user_mapping as ump ON ump.ump_us_id = us.us_id INNER JOIN
                         contact_person as cp on ump.ump_cp_id = cp.cp_id INNER JOIN
                         office as o ON cp.cp_of_id = o.of_id' . $strWhere;
        $query .= ' ORDER BY us.us_name, us.us_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'us_name', 'us_id', true);
    }

}
