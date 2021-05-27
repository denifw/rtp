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
use App\Model\Dao\Fms\RenewalOrderCostDao;

/**
 * Class to handle the ajax request fo RenewalOrderCost.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrderCost extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('rnc_id') === true) {
            $number = new NumberFormatter();
            $tempResult = RenewalOrderCostDao::getByReference($this->getIntParameter('rnc_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['rnc_rate_number'] = $number->doFormatCurrency($result['rnc_rate']);
                $result['rnc_quantity_number'] = $number->doFormatCurrency($result['rnc_quantity']);
                $result['rnc_est_cost_number'] = $number->doFormatCurrency($result['rnc_est_cost']);

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
        if ($this->isValidParameter('rnc_id') === true) {
            $number = new NumberFormatter();
            $serviceCost = RenewalOrderCostDao::getByReference($this->getIntParameter('rnc_id'));
            if (empty($serviceCost) === false) {
                $keys = array_keys($serviceCost);
                foreach ($keys as $key) {
                    if ($key === 'rnc_est_cost' || $key === 'rnc_rate' || $key === 'rnc_quantity') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($serviceCost[$key]);
                    }
                    $result[$key . '_del'] = $serviceCost[$key];
                }
            }
        }

        return $result;
    }
}
