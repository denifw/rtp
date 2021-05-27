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
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Finance\CashAndBank\ElectronicTopUpDao;

/**
 * Class to handle the ajax request fo ElectronicTopUp.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicTopUp extends AbstractBaseAjaxModel
{
//
//    /**
//     * Function to load the data for single select for ElectronicTopUp
//     *
//     * @return array
//     */
//    public function loadSingleSelectData(): array
//    {
//        $wheres = [];
//        if ($this->isValidParameter('search_key') === true) {
//            $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
//        }
//        # TODO Add additional wheres here.
//
//        return ElectronicTopUpDao::loadSingleSelectData($wheres);
//    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('et_id') === true) {
            $data = ElectronicTopUpDao::getByReference($this->getIntParameter('et_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['et_amount_number'] = $number->doFormatFloat($data['et_amount']);
                $data['et_ba_id_old'] = $data['et_ba_id'];
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
        if ($this->isValidParameter('et_id') === true) {
            $data = ElectronicTopUpDao::getByReference($this->getIntParameter('et_id'));
            if (empty($data) === false) {
                $data['et_id'] = '';
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
        if ($this->isValidParameter('et_id') === true) {
            $data = ElectronicTopUpDao::getByReference($this->getIntParameter('et_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['et_amount_del_number'] = $number->doFormatFloat($result['et_amount_del']);
            }
        }

        return $result;
    }
}
