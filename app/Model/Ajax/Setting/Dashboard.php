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
use App\Model\Dao\Setting\DashboardDao;

/**
 * Class to handle the ajax request fo Dashboard.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class Dashboard extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('dsh_id') === true) {
            $number = new NumberFormatter();
            $tempResult = DashboardDao::getByReference($this->getIntParameter('dsh_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['dsh_order_number'] = $number->doFormatInteger($result['dsh_order']);
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
        if ($this->isValidParameter('dsh_id') === true) {
            $dshData = DashboardDao::getByReference($this->getIntParameter('dsh_id'));
            if (empty($dshData) === false) {
                $keys = array_keys($dshData);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $dshData[$key];
                }
            }
        }

        return $result;
    }
}
