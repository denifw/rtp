<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\JobWarehouseDao;

/**
 * Class to handle the ajax request fo JobWarehouse.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobWarehouse extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobWarehouse
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
//        if ($this->isValidParameter('jo_id') === true) {
//            return JobWarehouseDao::getByReference($this->getIntParameter('jo_id'));
//        }
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
            $list = JobWarehouseDao::loadInboundOutboundDataForSo(0, $this->getIntParameter('jo_id'));
            $dt = new DateTimeParser();
            if (count($list) === 1) {
                $data = $list[0];
                $data['jw_eta_time'] = $dt->formatTime($data['jw_eta_time']);
                $data['jw_id'] = '';
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
//        if ($this->isValidParameter('jo_id') === true) {
//            $data = JobWarehouseDao::getByReference($this->getIntParameter('jo_id'));
//            if (empty($data) === false) {
//                $keys = array_keys($data);
//                foreach ($keys as $key) {
//                    $result[$key . '_del'] = $data[$key];
//                }
//            }
//        }

        return $result;
    }
}
