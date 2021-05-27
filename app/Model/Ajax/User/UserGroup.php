<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\User;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\User\UserGroupDetailDao;

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
        $wheres[] = StringFormatter::generateLikeQuery('usg_name', $this->getStringParameter('search_key'));

        $wheres[] = '((usg_ss_id IS NULL) OR (usg_ss_id = ' . $this->getIntParameter('usg_ss_id') . '))';
        $wheres[] = '(usg_deleted_on IS NULL)';
        $wheres[] = "(usg_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT usg_id, usg_name
                    FROM user_group' . $strWhere;
        $query .= ' ORDER BY usg_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'usg_name', 'usg_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getUserGroupDetailByIdForDelete(): array
    {
        if ($this->isValidParameter('ugd_id') === true) {
            $temp = UserGroupDetailDao::getByReference($this->getIntParameter('ugd_id'));
            if (empty($temp) === false) {
                $result = [];
                $keys = array_keys($temp);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[$key];
                }

                return $result;
            }
        }

        return [];
    }
}
