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
use App\Model\Dao\Finance\CashAndBank\ElectronicAccountDao;

/**
 * Class to handle the ajax request fo ElectronicAccount.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicAccount extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for ElectronicAccount
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('ea_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('ea.ea_ss_id', $this->getIntParameter('ea_ss_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition(['ea.ea_code', 'ea.ea_description'], $this->getStringParameter('search_key'));
            }
            if ($this->isValidParameter('ea_us_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('ea.ea_us_id', $this->getIntParameter('ea_us_id'));
            }

            $data = ElectronicAccountDao::loadSingleSelectData(['ea_code', 'ea_description'], $wheres);
            $results = [];
            $number = new NumberFormatter();
            foreach ($data as $row) {
                $row['ea_balance_number'] = $number->doFormatFloat($row['ea_balance']);
                $results[] = $row;
            }
            return $results;
        }
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('ea_id') === true) {
            return ElectronicAccountDao::getByReference($this->getIntParameter('ea_id'));
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
        if ($this->isValidParameter('ea_id') === true) {
            $data = ElectronicAccountDao::getByReference($this->getIntParameter('ea_id'));
            if (empty($data) === false) {
                $data['ea_id'] = '';
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
        if ($this->isValidParameter('ea_id') === true) {
            $data = ElectronicAccountDao::getByReference($this->getIntParameter('ea_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }
}
