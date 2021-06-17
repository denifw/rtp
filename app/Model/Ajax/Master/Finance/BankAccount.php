<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\BankAccountDao;

/**
 * Class to handle the ajax request fo BankAccount.
 *
 * @package    app
 * @subpackage Model\Ajax\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccount extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for BankAccount
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('ba_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('ba.ba_ss_id', $this->getStringParameter('ba_ss_id'));
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition([
                    'ba.ba_code', 'ba.ba_description'
                ], $this->getStringParameter('search_key'));
            }
            if ($this->isValidParameter('ba_main') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ba.ba_main', $this->getStringParameter('ba_main'));
            }
            if ($this->isValidParameter('ba_payable') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ba.ba_payable', $this->getStringParameter('ba_payable'));
            }
            if ($this->isValidParameter('ba_receivable') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ba.ba_receivable', $this->getStringParameter('ba_receivable'));
            }
            if ($this->isValidParameter('ba_cur_id') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ba.ba_cur_id', $this->getStringParameter('ba_cur_id'));
            }
            if ($this->isValidParameter('ba_us_id') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ba.ba_us_id', $this->getStringParameter('ba_us_id'));
            }
            if ($this->isValidParameter('ba_active') === true) {
                $null = $this->getStringParameter('ba_active') === 'Y';
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_block_on', $null);
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_deleted_on', $null);
            }
            return BankAccountDao::loadSingleSelectData(['ba_code', 'ba_description'], $wheres);
        }
        return [];
    }
}
