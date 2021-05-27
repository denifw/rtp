<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Finance\CashAndBank;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;

/**
 * Class to control the system of BankAccount.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccount extends AbstractListingModel
{

    /**
     * BankAccount constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ba');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # User
        $usField = $this->Field->getSingleSelect('user', 'ba_user', $this->getStringParameter('ba_user'), 'loadBankAccountManager');
        $usField->setHiddenField('ba_us_id', $this->getIntParameter('ba_us_id'));
        $usField->addParameter('ba_ss_id', $this->User->getSsId());
        $usField->setEnableDetailButton(false);
        $usField->setEnableNewButton(false);
        if ($this->PageSetting->checkPageRight('AllowSeeAllAccount') === false) {
            $usField->addParameter('ba_us_id', $this->User->getId());
        }

        $this->ListingForm->addField(Trans::getFinanceWord('code'), $this->Field->getText('ba_code', $this->getStringParameter('ba_code')));
        $this->ListingForm->addField(Trans::getFinanceWord('description'), $this->Field->getText('ba_description', $this->getStringParameter('ba_description')));
        $this->ListingForm->addField(Trans::getFinanceWord('accountManager'), $usField);
        $this->ListingForm->addField(Trans::getFinanceWord('mainAccount'), $this->Field->getYesNo('ba_main', $this->getStringParameter('ba_main')));
        $this->ListingForm->addField(Trans::getFinanceWord('blocked'), $this->Field->getYesNo('ba_blocked', $this->getStringParameter('ba_blocked')));
        $this->ListingForm->addField(Trans::getFinanceWord('deleted'), $this->Field->getYesNo('ba_deleted', $this->getStringParameter('ba_deleted')));

        $this->ListingForm->setGridDimension(4);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'ba_code' => Trans::getFinanceWord('code'),
            'ba_description' => Trans::getFinanceWord('description'),
            'ba_bank' => Trans::getFinanceWord('bank'),
            'ba_account' => Trans::getFinanceWord('account'),
            'ba_user' => Trans::getFinanceWord('manager'),
            'ba_balance' => Trans::getFinanceWord('balance'),
            'ba_main' => Trans::getFinanceWord('mainAccount'),
            'ba_receivable' => Trans::getFinanceWord('receivable'),
            'ba_payable' => Trans::getFinanceWord('payable'),
            'ba_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for BankAccount.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('ba_main', 'YesNo');
        $this->ListingTable->setColumnType('ba_receivable', 'YesNo');
        $this->ListingTable->setColumnType('ba_payable', 'YesNo');
        $this->ListingTable->addColumnAttribute('ba_balance', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('ba_status', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['ba_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ba_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return BankAccountDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = BankAccountDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $bank = [
                $row['ba_bank_name']
            ];
            if (empty($row['ba_bank_branch']) === false) {
                $bank[] = $row['ba_bank_branch'];
            }
            $row['ba_bank'] = StringFormatter::generateTableView($bank);
            $row['ba_account'] = StringFormatter::generateTableView([
                $row['ba_account_number'],
                $row['ba_account_name'],
            ]);
            if ($row['ba_main'] === 'Y') {
                $row['ba_balance'] = '';
            } else {
                $row['ba_balance'] = $row['ba_currency'] . ' ' . $number->doFormatFloat($row['ba_balance']);
            }
            if (empty($row['ba_deleted_on']) === false) {
                $status = new LabelDanger(Trans::getFinanceWord('deleted'));
            } elseif (empty($row['ba_block_on']) === false) {
                $status = new LabelDark(Trans::getFinanceWord('blocked'));
            } else {
                $status = new LabelSuccess(Trans::getFinanceWord('active'));
            }
            $row['ba_status'] = $status;
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ba.ba_ss_id', $this->User->getSsId());
        $allowSeeAllAccount = $this->PageSetting->checkPageRight('AllowSeeAllAccount');
        if ($allowSeeAllAccount === false) {
            $wheres[] = SqlHelper::generateNumericCondition('ba.ba_us_id', $this->User->getId());
        }
        if ($allowSeeAllAccount === true && $this->isValidParameter('ba_us_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ba.ba_us_id', $this->getIntParameter('ba_us_id'));
        }
        if ($this->isValidParameter('ba_code') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ba.ba_code', $this->getStringParameter('ba_code'));
        }
        if ($this->isValidParameter('ba_description') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ba.ba_description', $this->getStringParameter('ba_description'));
        }
        if ($this->isValidParameter('ba_main') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ba.ba_main', $this->getStringParameter('ba_main'));
        }
        if ($this->isValidParameter('ba_blocked') === true) {
            if ($this->getStringParameter('ba_blocked', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_block_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_block_on');
            }
        }
        if ($this->isValidParameter('ba_deleted') === true) {
            if ($this->getStringParameter('ba_deleted', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_deleted_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('ba.ba_deleted_on');
            }
        }
        # return the list where condition.
        return $wheres;
    }
}
