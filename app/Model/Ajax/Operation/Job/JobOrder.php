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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Operation\Job\JobOrderDao;

/**
 * Class to handle the ajax request fo JobOrder.
 *
 * @package    app
 * @subpackage Model\Ajax\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrder extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobOrder
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jo_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('jo.jo_ss_id', $this->getStringParameter('jo_ss_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition(['jo.jo_number', 'jo.jo_name'], $this->getStringParameter('search_key'));
            }
            return JobOrderDao::loadSingleSelectData(['jo_number', 'jo_name'], $wheres);
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
        if ($this->isValidParameter('jo_id') === true) {
            return JobOrderDao::getByReference($this->getStringParameter('jo_id'));
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
        if ($this->isValidParameter('jo_id') === true) {
            $data = JobOrderDao::getByReference($this->getStringParameter('jo_id'));
            if (empty($data) === false) {
                $data['jo_id'] = '';
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
        if ($this->isValidParameter('jo_id') === true) {
            $data = JobOrderDao::getByReference($this->getStringParameter('jo_id'));
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
