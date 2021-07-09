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
use App\Model\Dao\Master\Employee\ItemSalaryDao;

/**
 * Class to handle the ajax request fo ItemSalary.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class ItemSalary extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for ItemSalary
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('isl_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('isl_ss_id', $this->getStringParameter('isl_ss_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('isl_name', $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateStringCondition('isl_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('isl_deleted_on');
            return ItemSalaryDao::loadSingleSelectData('isl_name', $wheres);
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
        if ($this->isValidParameter('isl_id') === true) {
            return ItemSalaryDao::getByReference($this->getStringParameter('isl_id'));
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
        if ($this->isValidParameter('isl_id') === true) {
            $data = ItemSalaryDao::getByReference($this->getStringParameter('isl_id'));
            if (empty($data) === false) {
                $data['isl_id'] = '';
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
        if ($this->isValidParameter('isl_id') === true) {
            $data = ItemSalaryDao::getByReference($this->getStringParameter('isl_id'));
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
