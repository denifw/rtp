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
use App\Model\Dao\Administration\SalesInvoiceDetailDao;

/**
 * Class to handle the ajax request fo SalesInvoiceDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoiceDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for SalesInvoiceDetail
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

        return SalesInvoiceDetailDao::loadSingleSelectData('', $wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('sid_id') === true) {
            $data = SalesInvoiceDetailDao::getByReference($this->getStringParameter('sid_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['sid_quantity_number'] = $number->doFormatFloat($data['sid_quantity']);
                $data['sid_rate_number'] = $number->doFormatFloat($data['sid_rate']);
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
        if ($this->isValidParameter('sid_id') === true) {
            $data = SalesInvoiceDetailDao::getByReference($this->getStringParameter('sid_id'));
            if (empty($data) === false) {
                $data['sid_id'] = '';
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
        if ($this->isValidParameter('sid_id') === true) {
            $data = SalesInvoiceDetailDao::getByReference($this->getStringParameter('sid_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['sid_quantity_del_number'] = $number->doFormatFloat($result['sid_quantity_del']);
                $result['sid_rate_del_number'] = $number->doFormatFloat($result['sid_rate_del']);
            }
        }

        return $result;
    }
}
