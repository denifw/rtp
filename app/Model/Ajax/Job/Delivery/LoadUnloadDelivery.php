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

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Delivery\LoadUnloadDeliveryDao;
use App\Model\Dao\Job\Trucking\JobTruckingDetailDao;

/**
 * Class to handle the ajax request fo LoadUnloadDelivery.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Delivery
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class LoadUnloadDelivery extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for LoadUnloadDelivery
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        # TODO Add additional wheres here.

        return LoadUnloadDeliveryDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadAddress(): array
    {
        if ($this->isValidParameter('lud_jdl_id') === true) {
            $data = LoadUnloadDeliveryDao::getByJobDeliveryIdAndType($this->getIntParameter('lud_jdl_id'), $this->getStringParameter('lud_type', 'NULL'));
            $result = [];
            if (empty($data) === false) {
                $formatter = new StringFormatter();
                foreach ($data as $row) {
                    $temp = [];
                    $temp['lud_relation'] = $row['lud_relation'];
                    $temp['lud_rel_id'] = $row['lud_rel_id'];
                    $temp['lud_of_id'] = $row['lud_of_id'];
                    $temp['lud_address'] = $formatter->doFormatAddress($row, 'lud');
                    $result[] = $temp;
                }
            }

            return $result;
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadUnArriveAddress(): array
    {
        if ($this->isValidParameter('lud_jdl_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('lud.lud_jdl_id', $this->getIntParameter('lud_jdl_id'));
            $wheres[] = SqlHelper::generateStringCondition('lud.lud_type', $this->getStringParameter('lud_type', 'NULL'));
            $wheres[] = SqlHelper::generateNullCondition('lud.lud_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('lud.lud_ata_on');
            $data = LoadUnloadDeliveryDao::loadData($wheres);
            $result = [];
            $tempOfId = [];
            if (empty($data) === false) {
                $formatter = new StringFormatter();
                foreach ($data as $row) {
                    if (in_array($row['lud_of_id'], $tempOfId, true) === false) {
                        $temp = [];
                        $temp['lud_relation'] = $row['lud_relation'];
                        $temp['lud_rel_id'] = $row['lud_rel_id'];
                        $temp['lud_of_id'] = $row['lud_of_id'];
                        $temp['lud_address'] = $formatter->doFormatAddress($row, 'lud');
                        $result[] = $temp;
                        $tempOfId[] = $row['lud_of_id'];
                    }
                }
            }

            return $result;
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
        $results = [];
        if ($this->isValidParameter('lud_id') === true) {
            $results = LoadUnloadDeliveryDao::getByReference($this->getIntParameter('lud_id'));
            if (empty($results) === false) {
                $formatter = new StringFormatter();
                $number = new NumberFormatter();
                $results['lud_address'] = $formatter->doFormatAddress($results, 'lud');
                $results['lud_quantity_number'] = $number->doFormatFloat($results['lud_quantity']);
                $results['lud_qty_good_number'] = $number->doFormatFloat($results['lud_qty_good']);
                $results['lud_qty_damage_number'] = $number->doFormatFloat($results['lud_qty_damage']);
            }
        }
        return $results;
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('lud_id') === true) {
            $data = LoadUnloadDeliveryDao::getByReference($this->getIntParameter('lud_id'));
            if (empty($data) === false) {
                $data['lud_id'] = '';
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
        if ($this->isValidParameter('lud_id') === true) {
            $data = LoadUnloadDeliveryDao::getByReference($this->getIntParameter('lud_id'));
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
