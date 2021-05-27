<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Fms;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Fms\EquipmentMeterDao;

/**
 * Class to handle the ajax request fo EquipmentMeter.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class EquipmentMeter extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('eqm_id') === true) {
            $number = new NumberFormatter();
            $tempResult = EquipmentMeterDao::getByReference($this->getIntParameter('eqm_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['eqm_meter_number'] = $number->doFormatCurrency($result['eqm_meter']);
                return $result;
            }
            return [];
        }

        return [];
    }

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('eqm_id') === true) {
            $equipmentMeter = EquipmentMeterDao::getByReference($this->getIntParameter('eqm_id'));
            if (empty($equipmentMeter) === false) {
                $keys = array_keys($equipmentMeter);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $equipmentMeter[$key];
                }
            }
        }

        return $result;
    }
}
