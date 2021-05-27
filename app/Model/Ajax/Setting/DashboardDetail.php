<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Setting;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Setting\DashboardDetailDao;

/**
 * Class to handle the ajax request fo DashboardItem.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class DashboardDetail extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('dsd_id') === true) {
            $number = new NumberFormatter();
            $tempResult = DashboardDetailDao::getByReference($this->getIntParameter('dsd_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['dsd_order_number'] = $number->doFormatCurrency($result['dsd_order']);

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
        if ($this->isValidParameter('dsd_id') === true) {
            $number = new NumberFormatter();
            $dsdData = DashboardDetailDao::getByReference($this->getIntParameter('dsd_id'));
            if (empty($dsdData) === false) {
                $keys = array_keys($dsdData);
                foreach ($keys as $key) {
                    if ($key === 'dsd_order') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($dsdData[$key]);
                    }
                    $result[$key . '_del'] = $dsdData[$key];
                }
            }
        }

        return $result;
    }

}
