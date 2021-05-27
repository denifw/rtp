<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Finance\CashAndBank;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDetailDao;

/**
 * Class to handle the ajax request fo CashAdvanceDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvanceDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('cad_id') === true) {
            $data = CashAdvanceDetailDao::getByReference($this->getIntParameter('cad_id'), $this->isValidParameter('ca_jo_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['cad_quantity_number'] = $number->doFormatFloat($data['cad_quantity']);
                $data['cad_rate_number'] = $number->doFormatFloat($data['cad_rate']);
            }
            return $data;

        }
        return [];
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('cad_id') === true) {
            $data = CashAdvanceDetailDao::getByReference($this->getIntParameter('cad_id'), $this->isValidParameter('ca_jo_id'));
            if (empty($data) === false) {
                $data['cad_id'] = '';
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
        if ($this->isValidParameter('cad_id') === true) {
            $data = CashAdvanceDetailDao::getByReference($this->getIntParameter('cad_id'), $this->isValidParameter('ca_jo_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['cad_quantity_del_number'] = $number->doFormatFloat($result['cad_quantity_del']);
                $result['cad_rate_del_number'] = $number->doFormatFloat($result['cad_rate_del']);
            }
        }

        return $result;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForUploadReceipt(): array
    {
        $result = [];
        if ($this->isValidParameter('cad_id') === true) {
            $data = CashAdvanceDetailDao::getByReference($this->getIntParameter('cad_id'), $this->isValidParameter('ca_jo_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_doc'] = $data[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDeleteReceipt(): array
    {
        $result = [];
        if ($this->isValidParameter('cad_id') === true) {
            $data = CashAdvanceDetailDao::getByReference($this->getIntParameter('cad_id'), $this->isValidParameter('ca_jo_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_doc_del'] = $data[$key];
                }
            }
        }

        return $result;
    }
}
