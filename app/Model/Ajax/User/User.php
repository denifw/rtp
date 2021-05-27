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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
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
            $wheres[] = "(us.us_confirm = '" . $this->getStringParameter('us_confirm') . "')";
        } else {
            $wheres[] = "(us.us_confirm = 'Y')";
        }
        if ($this->isValidParameter('ss_id') === true) {
            $wheres[] = '(ump.ump_ss_id = ' . $this->getIntParameter('ss_id') . ')';
        }
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(ump.ump_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('us_id') === true) {
            $wheres[] = '(us.us_id = ' . $this->getIntParameter('us_id') . ')';
        }
        if ($this->isValidParameter('of_id') === true) {
            $wheres[] = '(o.of_id = ' . $this->getIntParameter('of_id') . ')';
        }
        $wheres[] = "(us.us_active = 'Y')";
        $wheres[] = "(us.us_system = 'N')";
        $wheres[] = '(us.us_deleted_on IS NULL)';
        $wheres[] = "(ump.ump_active = 'Y')";
        $wheres[] = "(ump.ump_confirm = 'Y')";
        $wheres[] = '(ump.ump_deleted_on IS NULL)';

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

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadBankAccountManager(): array
    {
        if ($this->isValidParameter('ba_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(ba.ba_ss_id = ' . $this->getIntParameter('ba_ss_id') . ')';
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('us.us_name', $this->getStringParameter('search_key'));
            }
            if ($this->isValidParameter('ba_cur_id') === true) {
                $wheres[] = '(ba.ba_cur_id = ' . $this->getIntParameter('ba_cur_id') . ')';
            }
            if ($this->isValidParameter('ba_us_id') === true) {
                $wheres[] = '(ba.ba_us_id = ' . $this->getIntParameter('ba_us_id') . ')';
            }
            $wheres[] = '(us.us_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT us.us_id, us.us_name, us.us_username
                    FROM users as us INNER JOIN
                    bank_account as ba ON ba.ba_us_id = us.us_id ' . $strWhere;
            $query .= ' GROUP BY us.us_id, us.us_name, us.us_username';
            $query .= ' ORDER BY us.us_name, us.us_id';
            $query .= ' LIMIT 20 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'us_name', 'us_id', true);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadElectronicAccountUser(): array
    {
        if ($this->isValidParameter('ea_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(ea.ea_ss_id = ' . $this->getIntParameter('ea_ss_id') . ')';
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('us.us_name', $this->getStringParameter('search_key'));
            }
            if ($this->isValidParameter('ea_us_id') === true) {
                $wheres[] = '(ea.ea_us_id = ' . $this->getIntParameter('ea_us_id') . ')';
            }
            $wheres[] = '(us.us_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT us.us_id, us.us_name, us.us_username
                    FROM users as us INNER JOIN
                    electronic_account as ea ON ea.ea_us_id = us.us_id ' . $strWhere;
            $query .= ' GROUP BY us.us_id, us.us_name, us.us_username';
            $query .= ' ORDER BY us.us_name, us.us_id';
            $query .= ' LIMIT 20 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'us_name', 'us_id', true);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadOfficerJob(): array
    {
        if ($this->isValidParameter('joo_jo_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('us_name', $this->getStringParameter('search_key'));
            $officer = '(us_id IN (SELECT joo_us_id
                                    FROM job_officer
                                    WHERE joo_jo_id = ' . $this->getIntParameter('joo_jo_id') . ' AND joo_deleted_on IS NULL
                                    GROUP by joo_us_id))';
            $manager = '(us_id IN (SELECT jo_manager_id
                                    FROM job_order
                                    WHERE jo_id = ' . $this->getIntParameter('joo_jo_id') . '))';
            $wheres[] = '(' . $officer . ' OR ' . $manager . ')';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT us_id, us_name
                    FROM users ' . $strWhere;
            $query .= ' GROUP BY us_name, us_id';
            $query .= ' ORDER BY us_name, us_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'us_name', 'us_id');
        }
        return [];
    }

}
