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

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;
use App\Model\Dao\Finance\CashAndBank\BankTransactionDao;

/**
 * Class to control the system of TopUp.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class TopUp extends AbstractListingModel
{

    /**
     * TopUp constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'topUp');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getFinanceWord('number'), $this->Field->getText('bt_number', $this->getStringParameter('bt_number')));
        if ($this->getPageSetting()->checkPageRight('AllowSeeAllAccount') === true) {
            $baField = $this->Field->getSingleSelect('ba', 'bt_ba_description', $this->getStringParameter('bt_ba_description'));
            $baField->setHiddenField('bt_ba_id', $this->getIntParameter('bt_ba_id'));
            $baField->addParameter('ba_ss_id', $this->User->getSsId());
            $baField->addParameter('ba_main', 'N');
            $baField->setEnableDetailButton(false);
            $baField->setEnableNewButton(false);
            $this->ListingForm->addField(Trans::getFinanceWord('bankAccount'), $baField);
        }
        # Status
        $typeField = $this->Field->getSelect('bt_type', $this->getStringParameter('bt_type'));
        $typeField->addOption(Trans::getFinanceWord('request'), 'request');
        $typeField->addOption(Trans::getFinanceWord('return'), 'return');
        $this->ListingForm->addField(Trans::getFinanceWord('type'), $typeField);
        # Status
        $statusField = $this->Field->getSelect('bt_status', $this->getStringParameter('bt_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('waitingApproval'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('waitingPayment'), '4');
        $statusField->addOption(Trans::getFinanceWord('waitingReceive'), '5');
        $statusField->addOption(Trans::getFinanceWord('complete'), '6');
        $statusField->addOption(Trans::getFinanceWord('deleted'), '7');
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
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
            'bt_number' => Trans::getFinanceWord('number'),
            'bt_type' => Trans::getFinanceWord('type'),
            'bt_account' => Trans::getFinanceWord('account'),
            'bt_amount' => Trans::getFinanceWord('amount'),
            'bt_notes' => Trans::getFinanceWord('notes'),
            'bt_time' => Trans::getFinanceWord('time'),
            'bt_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for TopUp.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->addColumnAttribute('bt_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('bt_type', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('bt_amount', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('bt_status', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['bt_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return BankTransactionDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = BankTransactionDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        if (empty($data) === false) {
            $btDao = new BankTransactionDao();
            $number = new NumberFormatter($this->User);
            $dt = new DateTimeParser();
            foreach ($data as $row) {
                $currency = $row['bt_payer_currency'];
                $accountCode = $row['bt_payer_code'];
                $accountDescription = $row['bt_payer'];
                if ($row['bt_type'] === 'request') {
                    $currency = $row['bt_receiver_currency'];
                    $accountCode = $row['bt_receiver_code'];
                    $accountDescription = $row['bt_receiver'];
                }
                $row['bt_type'] = Trans::getFinanceWord($row['bt_type']);
                $row['bt_account'] = $accountCode . ' - ' . $accountDescription;
                $row['bt_amount'] = $currency . ' ' . $number->doFormatFloat($row['bt_amount']);
                $row['bt_time'] = StringFormatter::generateKeyValueTableView([
                    [
                        'label' => Trans::getFinanceWord('requested'),
                        'value' => $dt->formatDateTime($row['bt_request_on']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('approved'),
                        'value' => $dt->formatDateTime($row['bt_approve_on']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('paid'),
                        'value' => $dt->formatDateTime($row['bt_paid_on']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('received'),
                        'value' => $dt->formatDateTime($row['bt_receive_on']),
                    ],
                ]);
                $row['bt_status'] = $btDao->generateStatus([
                    'is_deleted' => !empty($row['bt_deleted_on']),
                    'is_receive' => !empty($row['bt_receive_on']),
                    'is_paid' => !empty($row['bt_paid_on']),
                    'is_approved' => !empty($row['bt_approve_on']),
                    'is_requested' => !empty($row['bt_bta_id']),
                    'is_rejected' => !empty($row['bt_reject_on']),
                ]);
                $results[] = $row;
            }
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
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('bt_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('bt.bt_number', $this->getStringParameter('bt_number'));
        }
        if ($this->getPageSetting()->checkPageRight('AllowSeeAllAccount') === false) {
            $wheres[] = '(' . SqlHelper::generateNumericCondition('py.ba_us_id', $this->User->getId()) .
                ' OR ' .
                SqlHelper::generateNumericCondition('rc.ba_us_id', $this->User->getId()) . ')';
        }
        if ($this->isValidParameter('bt_ba_id') === true) {
            $wheres[] = '(' . SqlHelper::generateNumericCondition('bt.bt_payer_ba_id', $this->getIntParameter('bt_ba_id')) .
                ' OR ' .
                SqlHelper::generateNumericCondition('bt.bt_receiver_ba_id', $this->getIntParameter('bt_ba_id')) . ')';
        }
        if ($this->isValidParameter('bt_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('bt.bt_type', $this->getStringParameter('bt_type'));
        } else {
            $wheres[] = "(bt.bt_type IN ('request', 'return'))";
        }
        if ($this->isValidParameter('bt_status') === true) {
            $status = $this->getIntParameter('bt_status');
            switch ($status) {
                case 1:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_bta_id');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 2:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_bta_id', false);
                    $wheres[] = SqlHelper::generateNullCondition('bta.bta_deleted_on');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_approve_on');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 3:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_bta_id', false);
                    $wheres[] = SqlHelper::generateNullCondition('bta.bta_deleted_on', false);
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_approve_on');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 4:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_approve_on', false);
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_paid_on');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 5:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_paid_on', false);
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_receive_on');
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 6:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_receive_on', false);
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on');
                    break;
                case 7:
                    $wheres[] = SqlHelper::generateNullCondition('bt.bt_deleted_on', false);
                    break;
            }
        }

        # return the list where condition.
        return $wheres;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $data = BankAccountDao::getByUser($this->User);
        if (empty($data) === true) {
            $this->disableNewButton();
        }
        parent::loadDefaultButton();
    }
}
