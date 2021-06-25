<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\Operation\Job;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Operation\Job\JobEmployeeDao;

/**
 * Class to handle the ajax request fo JobEmployee.
 *
 * @package    app
 * @subpackage Model\Ajax\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobEmployee extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobEmployee
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jem_jo_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('jem.jem_jo_id', $this->getStringParameter('jem_jo_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition(['em.em_number', 'em.em_name'], $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateNullCondition('jem.jem_deleted_on');

            return JobEmployeeDao::loadSingleSelectData(['em_number', 'em_name'], $wheres);
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
        if ($this->isValidParameter('jem_id') === true) {
            $data = JobEmployeeDao::getByReference($this->getStringParameter('jem_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['jem_shift_one_number'] = $number->doFormatFloat($data['jem_shift_one']);
                $data['jem_shift_two_number'] = $number->doFormatFloat($data['jem_shift_two']);
                $data['jem_shift_three_number'] = $number->doFormatFloat($data['jem_shift_three']);
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
        if ($this->isValidParameter('jem_id') === true) {
            $data = JobEmployeeDao::getByReference($this->getStringParameter('jem_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['jem_shift_one_number'] = $number->doFormatFloat($data['jem_shift_one']);
                $data['jem_shift_two_number'] = $number->doFormatFloat($data['jem_shift_two']);
                $data['jem_shift_three_number'] = $number->doFormatFloat($data['jem_shift_three']);
                $data['jem_id'] = '';
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
        if ($this->isValidParameter('jem_id') === true) {
            $data = JobEmployeeDao::getByReference($this->getStringParameter('jem_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['jem_shift_one_del_number'] = $number->doFormatFloat($result['jem_shift_one_del']);
                $result['jem_shift_two_del_number'] = $number->doFormatFloat($result['jem_shift_two_del']);
                $result['jem_shift_three_del_number'] = $number->doFormatFloat($result['jem_shift_three_del']);
                if ($result['jem_type_del'] === 'H') {
                    $result['jem_type_del'] = Trans::getWord('hour');
                } else {
                    $result['jem_type_del'] = Trans::getWord('shift');
                }
            }
        }

        return $result;
    }
}
