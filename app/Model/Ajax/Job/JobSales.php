<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobSalesDao;

/**
 * Class to handle the ajax request fo JobSales.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobSales extends AbstractBaseAjaxModel
{

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('jos_id') === true) {
            $number = new NumberFormatter();
            $result = JobSalesDao::getByReference($this->getIntParameter('jos_id'));
            $result['jos_rate_number'] = $number->doFormatFloat((float)$result['jos_rate']);
            $result['jos_quantity_number'] = $number->doFormatFloat((float)$result['jos_quantity']);
            $result['jos_exchange_rate_number'] = $number->doFormatFloat((float)$result['jos_exchange_rate']);
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
        if ($this->isValidParameter('jos_id') === true) {
            $number = new NumberFormatter();
            $data = JobSalesDao::getByReference($this->getIntParameter('jos_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_del'] = $data[$key];
            }
            $result['jos_rate_del_number'] = $number->doFormatFloat((float)$result['jos_rate_del']);
            $result['jos_quantity_del_number'] = $number->doFormatFloat((float)$result['jos_quantity_del']);
            $result['jos_exchange_rate_del_number'] = $number->doFormatFloat((float)$result['jos_exchange_rate_del']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUpdateFromQuotation(): array
    {
        $result = [];
        if ($this->isValidParameter('jos_id') === true) {
            $number = new NumberFormatter();
            $data = JobSalesDao::getByReference($this->getIntParameter('jos_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_qt'] = $data[$key];
            }
            $result['jos_rate_qt_number'] = $number->doFormatFloat((float)$result['jos_rate_qt']);
            $result['jos_quantity_qt_number'] = $number->doFormatFloat((float)$result['jos_quantity_qt']);
            $result['jos_exchange_rate_qt_number'] = $number->doFormatFloat((float)$result['jos_exchange_rate_qt']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUpdateReimburse(): array
    {
        $result = [];
        if ($this->isValidParameter('jos_id') === true) {
            $number = new NumberFormatter();
            $data = JobSalesDao::getByReference($this->getIntParameter('jos_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_r'] = $data[$key];
            }
            $result['jos_rate_r_number'] = $number->doFormatFloat((float)$result['jos_rate_r']);
            $result['jos_quantity_r_number'] = $number->doFormatFloat((float)$result['jos_quantity_r']);
            $result['jos_exchange_rate_r_number'] = $number->doFormatFloat((float)$result['jos_exchange_rate_r']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForDeleteReimburse(): array
    {
        $result = [];
        if ($this->isValidParameter('jos_id') === true) {
            $number = new NumberFormatter();
            $data = JobSalesDao::getByReference($this->getIntParameter('jos_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_rdel'] = $data[$key];
            }
            $result['jos_rate_rdel_number'] = $number->doFormatFloat((float)$result['jos_rate_rdel']);
            $result['jos_quantity_rdel_number'] = $number->doFormatFloat((float)$result['jos_quantity_rdel']);
            $result['jos_exchange_rate_rdel_number'] = $number->doFormatFloat((float)$result['jos_exchange_rate_rdel']);
        }

        return $result;
    }

}
