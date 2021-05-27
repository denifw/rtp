<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Finance\Sales;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Finance\Sales\SalesInvoiceDetailDao;

/**
 * Class to handle the ajax request fo SalesInvoiceDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesInvoiceDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('sid_id') === true) {
            $number = new NumberFormatter();
            $result = SalesInvoiceDetailDao::getByReference($this->getIntParameter('sid_id'));
            $result['sid_rate_number'] = $number->doFormatFloat((float)$result['sid_rate']);
            $result['sid_quantity_number'] = $number->doFormatFloat((float)$result['sid_quantity']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('sid_id') === true) {
            $number = new NumberFormatter();
            $data = SalesInvoiceDetailDao::getByReference($this->getIntParameter('sid_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key.'_del'] = $data[$key];
            }
            $result['sid_rate_del_number'] = $number->doFormatFloat((float)$result['sid_rate_del']);
            $result['sid_quantity_del_number'] = $number->doFormatFloat((float)$result['sid_quantity_del']);
        }

        return $result;
    }
}
