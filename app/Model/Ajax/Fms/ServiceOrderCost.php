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
use App\Model\Dao\Fms\ServiceOrderCostDao;

/**
 * Class to handle the ajax request fo ServiceOrderCost.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrderCost extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('svc_id') === true) {
            $number = new NumberFormatter();
            $tempResult = ServiceOrderCostDao::getByReference($this->getIntParameter('svc_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['svc_rate_number'] = $number->doFormatCurrency($result['svc_rate']);
                $result['svc_quantity_number'] = $number->doFormatCurrency($result['svc_quantity']);
                $result['svc_est_cost_number'] = $number->doFormatCurrency($result['svc_est_cost']);

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
        if ($this->isValidParameter('svc_id') === true) {
            $number = new NumberFormatter();
            $serviceCost = ServiceOrderCostDao::getByReference($this->getIntParameter('svc_id'));
            if (empty($serviceCost) === false) {
                $keys = array_keys($serviceCost);
                foreach ($keys as $key) {
                    if ($key === 'svc_est_cost' || $key === 'svc_rate' || $key === 'svc_quantity') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($serviceCost[$key]);
                    }
                    $result[$key . '_del'] = $serviceCost[$key];
                }
            }
        }

        return $result;
    }
}
