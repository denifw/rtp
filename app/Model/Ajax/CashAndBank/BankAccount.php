<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\CashAndBank;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CashAndBank\BankAccountDao;

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
            $helper = new SqlHelper();
            $helper->addStringWhere('ba.ba_ss_id', $this->getStringParameter('ba_ss_id'));
            $helper->addOrLikeWhere(['ba.ba_code', 'ba.ba_description'], $this->getStringParameter('search_key'));
            $helper->addStringWhere('ba.ba_main', $this->getStringParameter('ba_main'));
            $helper->addStringWhere('ba.ba_payable', $this->getStringParameter('ba_payable'));
            $helper->addStringWhere('ba.ba_receivable', $this->getStringParameter('ba_receivable'));
            $helper->addStringWhere('ba.ba_us_id', $this->getStringParameter('ba_us_id'));
            $helper->addStringWhere('ba.ba_active', $this->getStringParameter('ba_active'));
            $helper->addNullWhere('ba.ba_deleted_on');
            $helper->addNullWhere('ba.ba_block_on');
            return BankAccountDao::loadSingleSelectData(['ba_code', 'ba_description'], $helper, ['ba_current_balance']);
        }
        return [];
    }
}
