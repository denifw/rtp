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
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Operation\Job\JobOrderTaskDao;

/**
 * Class to handle the ajax request fo JobOrderTask.
 *
 * @package    app
 * @subpackage Model\Ajax\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrderTask extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobOrderTask
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jot.jot_description', $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('jot_jo_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('jot.jot_jo_id', $this->getStringParameter('jot_jo_id'));
        }
        $wheres[] = SqlHelper::generateNullCondition('jot.jot_deleted_on');

        return JobOrderTaskDao::loadSingleSelectData('jot_description', $wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('jot_id') === true) {
            $data = JobOrderTaskDao::getByReference($this->getStringParameter('jot_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['jot_portion_number'] = $number->doFormatFloat($data['jot_portion']);
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
        if ($this->isValidParameter('jot_id') === true) {
            $data = JobOrderTaskDao::getByReference($this->getStringParameter('jot_id'));
            if (empty($data) === false) {
                $data['jot_id'] = '';
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
        if ($this->isValidParameter('jot_id') === true) {
            $data = JobOrderTaskDao::getByReference($this->getStringParameter('jot_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['jot_portion_del_number'] = $number->doFormatFloat($result['jot_portion_del']);
            }
        }

        return $result;
    }
}
