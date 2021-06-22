<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\Master\Employee;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Employee\EmployeeDao;

/**
 * Class to handle the ajax request fo Employee.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Employee extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Employee
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('em_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('em.em_ss_id', $this->getStringParameter('em_ss_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition(['em.em_number', 'em.em_name'], $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateNullCondition('em.em_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('em.em_active', 'Y');

            return EmployeeDao::loadSingleSelectData(['em_number', 'em_name'], $wheres);
        }
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('em_id') === true) {
            return EmployeeDao::getByReference($this->getIntParameter('em_id'));
        }
        return [];
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('em_id') === true) {
            $data = EmployeeDao::getByReference($this->getIntParameter('em_id'));
            if (empty($data) === false) {
                $data['em_id'] = '';
            }
        }

        return $data;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('em_id') === true) {
            $data = EmployeeDao::getByReference($this->getIntParameter('em_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }
}
