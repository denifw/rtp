<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\Administration;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Administration\PurchaseInvoiceDetailDao;

/**
 * Class to handle the ajax request fo PurchaseInvoiceDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PurchaseInvoiceDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for PurchaseInvoiceDetail
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        }
        # TODO Add additional wheres here.

        return PurchaseInvoiceDetailDao::loadSingleSelectData('', $wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('pid_id') === true) {
            $data = PurchaseInvoiceDetailDao::getByReference($this->getStringParameter('pid_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['pid_quantity_number'] = $number->doFormatFloat($data['pid_quantity']);
                $data['pid_rate_number'] = $number->doFormatFloat($data['pid_rate']);
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
        if ($this->isValidParameter('pid_id') === true) {
            $data = PurchaseInvoiceDetailDao::getByReference($this->getStringParameter('pid_id'));
            if (empty($data) === false) {
                $data['pid_id'] = '';
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
        if ($this->isValidParameter('pid_id') === true) {
            $data = PurchaseInvoiceDetailDao::getByReference($this->getStringParameter('pid_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['pid_quantity_del_number'] = $number->doFormatFloat($result['pid_quantity_del']);
                $result['pid_rate_del_number'] = $number->doFormatFloat($result['pid_rate_del']);
            }
        }

        return $result;
    }
}
