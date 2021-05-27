<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job\Delivery;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Delivery\JobDeliveryDetailDao;

/**
 * Class to handle the ajax request fo JobDeliveryDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Delivery
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobDeliveryDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobDeliveryDetail
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        # TODO Add additional wheres here.

        return JobDeliveryDetailDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('jdld_id') === true) {
            return JobDeliveryDetailDao::getByReference($this->getIntParameter('jdld_id'));
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
        if ($this->isValidParameter('jdld_id') === true) {
            $data = JobDeliveryDetailDao::getByReference($this->getIntParameter('jdld_id'));
            if (empty($data) === false) {
                $data['jdld_id'] = '';
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
        if ($this->isValidParameter('jdld_id') === true) {
            $data = JobDeliveryDetailDao::getByReference($this->getIntParameter('jdld_id'));
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
