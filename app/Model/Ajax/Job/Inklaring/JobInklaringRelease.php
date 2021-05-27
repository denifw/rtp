<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Inklaring;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Inklaring\JobInklaringReleaseDao;

/**
 * Class to handle the ajax request fo JobInklaringRelease.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Inklaring
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobInklaringRelease extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('jikr_id') === true) {
            $data = JobInklaringReleaseDao::getByReference($this->getIntParameter('jikr_id'));
            if (empty($data) === false) {
                $dt = new DateTimeParser();
                $number = new NumberFormatter();
                $data['jikr_quantity_number'] = $number->doFormatCurrency($data['jikr_quantity']);
                $data['jikr_goods_quantity_number'] = $number->doFormatCurrency($data['jikr_goods_quantity']);
                $data['jikr_gross_weight_number'] = $number->doFormatCurrency($data['jikr_gross_weight']);
                $data['jikr_net_weight_number'] = $number->doFormatCurrency($data['jikr_net_weight']);
                $data['jikr_cbm_number'] = $number->doFormatCurrency($data['jikr_cbm']);
                $data['jikr_load_time'] = $dt->formatTime($data['jikr_load_time']);
                if (empty($data['jikr_gate_in_time']) === true) {
                    $data['jikr_gate_in_time'] = date('H:i');
                } else {
                    $data['jikr_gate_in_time'] = $dt->formatTime($data['jikr_gate_in_time']);
                }
                if (empty($data['jikr_gate_in_date']) === true) {
                    $data['jikr_gate_in_date'] = date('Y-m-d');
                }

                return $data;
            }
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jikr_id') === true) {
            $temp = JobInklaringReleaseDao::getByReference($this->getIntParameter('jikr_id'));
            if (empty($temp) === false) {
                $number = new NumberFormatter();
                $dt = new DateTimeParser();
                $result = [];
                $keys = array_keys($temp);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[$key];
                }
                $result['jikr_quantity_del_number'] = $number->doFormatCurrency($result['jikr_quantity_del']);
                $result['jikr_goods_quantity_del_number'] = $number->doFormatCurrency($result['jikr_goods_quantity_del']);
                $result['jikr_gross_weight_del_number'] = $number->doFormatCurrency($result['jikr_gross_weight_del']);
                $result['jikr_net_weight_del_number'] = $number->doFormatCurrency($result['jikr_net_weight_del']);
                $result['jikr_cbm_del_number'] = $number->doFormatCurrency($result['jikr_cbm_del']);
                $result['jikr_load_time_del'] = $dt->formatTime($result['jikr_load_time_del']);
            }
        }
        return $result;
    }

}
