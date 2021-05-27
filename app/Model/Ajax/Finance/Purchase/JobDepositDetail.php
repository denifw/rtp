<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Finance\Purchase;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Finance\Purchase\JobDepositDetailDao;
use App\Model\Dao\Master\Finance\CostCodeGroupDao;

/**
 * Class to handle the ajax request fo JobDepositDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDepositDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        $result = [];
        if ($this->isValidParameter('jdd_id')) {
            $number = new NumberFormatter();
            $result = JobDepositDetailDao::getByReference($this->getIntParameter('jdd_id'));
            $result['jdd_rate_number'] = $number->doFormatFloat((float)$result['jdd_rate']);
            $result['jdd_quantity_number'] = $number->doFormatFloat((float)$result['jdd_quantity']);
            $result['jdd_type_name'] = CostCodeGroupDao::getTypeName($result['jdd_type']);
        }
        return $result;
    }


    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jdd_id') === true) {
            $number = new NumberFormatter();
            $data = JobDepositDetailDao::getByReference($this->getIntParameter('jdd_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_del'] = $data[$key];
            }
            $result['jdd_rate_del_number'] = $number->doFormatFloat((float)$result['jdd_rate_del']);
            $result['jdd_quantity_del_number'] = $number->doFormatFloat((float)$result['jdd_quantity_del']);
            $result['jdd_type_name_del'] = CostCodeGroupDao::getTypeName($result['jdd_type_del']);
        }

        return $result;
    }
}
