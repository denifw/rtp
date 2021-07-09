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

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Employee\EmployeeItemSalaryDao;

/**
 * Class to handle the ajax request fo EmployeeItemSalary.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class EmployeeItemSalary extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for EmployeeItemSalary
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        }
        # TODO Add additional wheres here.

        return EmployeeItemSalaryDao::loadSingleSelectData('', $wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('eis_id') === true) {
            $data = EmployeeItemSalaryDao::getByReference($this->getStringParameter('eis_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['eis_amount_number'] = $number->doFormatFloat($data['eis_amount']);
            }
            return $data;
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
        if ($this->isValidParameter('eis_id') === true) {
            $data = EmployeeItemSalaryDao::getByReference($this->getStringParameter('eis_id'));
            if (empty($data) === false) {
                $data['eis_id'] = '';
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
        if ($this->isValidParameter('eis_id') === true) {
            $data = EmployeeItemSalaryDao::getByReference($this->getStringParameter('eis_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['eis_amount_del_number'] = $number->doFormatFloat($result['eis_amount_del']);
            }
        }

        return $result;
    }
}
