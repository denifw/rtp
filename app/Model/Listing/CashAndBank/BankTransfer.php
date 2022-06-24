<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\CashAndBank;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CashAndBank\BankTransferDao;

/**
 * Class to control the system of BankTransfer.
 *
 * @package    app
 * @subpackage Model\Listing\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */
class BankTransfer extends AbstractListingModel
{

    /**
     * BankTransfer constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'bt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $payerField = $this->Field->getSingleSelect('ba', 'bt_payer', $this->getStringParameter('bt_payer'));
        $payerField->setHiddenField('bt_payer_ba_id', $this->getStringParameter('bt_payer_ba_id'));
        $payerField->addParameter('ba_ss_id', $this->User->getSsId());
        $payerField->setEnableNewButton(false);

        $receiverField = $this->Field->getSingleSelect('ba', 'bt_receiver', $this->getStringParameter('bt_receiver'));
        $receiverField->setHiddenField('bt_receiver_ba_id', $this->getStringParameter('bt_receiver_ba_id'));
        $receiverField->addParameter('ba_ss_id', $this->User->getSsId());
        $receiverField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('bt_status', $this->getStringParameter('bt_status'));
        $statusField->addOption(Trans::getWord('draft'), 'draft');
        $statusField->addOption(Trans::getWord('paid'), 'paid');
        $statusField->addOption(Trans::getWord('deleted'), 'deleted');

        $this->ListingForm->addField(Trans::getWord('sender'), $payerField);
        $this->ListingForm->addField(Trans::getWord('receiver'), $receiverField);
        $this->ListingForm->addField(Trans::getWord('fromDate'), $this->Field->getCalendar('bt_from_date', $this->getStringParameter('bt_from_date')));
        $this->ListingForm->addField(Trans::getWord('untilDate'), $this->Field->getCalendar('bt_until_date', $this->getStringParameter('bt_until_date')));

        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('bt_number', $this->getStringParameter('bt_number')));
        $this->ListingForm->addField(Trans::getWord('notes'), $this->Field->getText('bt_notes', $this->getStringParameter('bt_notes')));
        $this->ListingForm->addField(Trans::getWord('status'), $statusField);
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
            'bt_number' => Trans::getWord('number'),
            'bt_datetime' => Trans::getWord('time'),
            'bt_payer' => Trans::getWord('sender'),
            'bt_amount' => Trans::getWord('amount'),
            'bt_exchange_rate' => Trans::getWord('exchangeRate'),
            'bt_receiver' => Trans::getWord('receiver'),
            'bt_status' => Trans::getWord('status'),
        ]);
        # Load the data for BankTransfer.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('bt_datetime', 'datetime');
        $this->ListingTable->setColumnType('bt_amount', 'float');
        $this->ListingTable->setColumnType('bt_exchange_rate', 'float');
        $this->ListingTable->addColumnAttribute('bt_status', 'style', 'text-align: center');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['bt_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return BankTransferDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = BankTransferDao::loadData($this->getWhereCondition());
        $results = [];
        $btDao = new BankTransferDao();
        foreach ($data as $row) {
            $row['bt_payer'] = StringFormatter::generateTableView([
                $row['bt_payer_code'] . ' - ' . $row['bt_payer_ba'], $row['bt_payer_currency']
            ]);
            $row['bt_receiver'] = StringFormatter::generateTableView([
                $row['bt_receiver_code'] . ' - ' . $row['bt_receiver_ba'], $row['bt_receiver_currency']
            ]);
            $row['bt_status'] = $btDao->getStatus($row);
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return SqlHelper
     */
    private function getWhereCondition(): SqlHelper
    {
        # Set where conditions
        $helper = new SqlHelper();
        $helper->setLimit($this->getLimitTable(), $this->getLimitOffsetTable());
        $helper->addOrderByString($this->ListingSort->getOrderByFieldsString());

        # Check the filter value here.
        $helper->addStringWhere('bt.bt_ss_id', $this->User->getSsId());
        $helper->addStringWhere('bt.bt_payer_ba_id', $this->getStringParameter('bt_payer_ba_id'));
        $helper->addStringWhere('bt.bt_receiver_ba_id', $this->getStringParameter('bt_receiver_ba_id'));
        $helper->addLikeWhere('bt.bt_number', $this->getStringParameter('bt_number'));
        $helper->addLikeWhere('bt.bt_notes', $this->getStringParameter('bt_notes'));
        if ($this->PageSetting->checkPageRight('AllowSeeAllTransaction') === false) {
            $helper->addStringWhere('bap.ba_us_id', $this->User->getId(), '=', '', 'us');
            $helper->addStringWhere('bar.ba_us_id', $this->User->getId(), '=', '', 'us');
        }
        $helper->addRangeDateWhere('bt.bt_date', $this->getStringParameter('bt_from_date'), $this->getStringParameter('bt_until_date'));
        if ($this->isValidParameter('bt_status') === true) {
            $status = $this->getStringParameter('bt_status');
            if ($status === 'draft') {
                $helper->addNullWhere('bt.bt_paid_on');
                $helper->addNullWhere('bt.bt_deleted_on');
            } elseif ($status === 'paid') {
                $helper->addNullWhere('bt.bt_paid_on', false);
                $helper->addNullWhere('bt.bt_deleted_on');
            } else {
                $helper->addNullWhere('bt.bt_deleted_on', false);
            }

        }
        # return the list where condition.
        return $helper;
    }
}
