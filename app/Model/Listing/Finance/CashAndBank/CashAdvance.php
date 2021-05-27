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
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;

/**
 * Class to control the system of CashAdvance.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvance extends AbstractListingModel
{
    /**
     * Property to store the user bank account
     *
     * @var array $Accounts
     */
    protected $Accounts = [];


    /**
     * CashAdvance constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ca');
        $this->setParameters($parameters);
        $this->Accounts = BankAccountDao::getByUser($this->User);

    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Bank Account
        $baField = $this->Field->getSingleSelect('ba', 'ca_ba_description', $this->getStringParameter('ca_ba_description'));
        $baField->setHiddenField('ca_ba_id', $this->getIntParameter('ca_ba_id'));
        $baField->setEnableNewButton(false);
        $baField->setEnableDetailButton(false);
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('AllowSeeAllAccount') === false) {
            $baField->addParameter('ba_us_id', $this->User->getId());
        }
        # Contact Person
        $cpField = $this->Field->getSingleSelect('contactPerson', 'ca_cp_name', $this->getStringParameter('ca_cp_name'));
        $cpField->setHiddenField('ca_cp_id', $this->getIntParameter('ca_cp_id'));
        $cpField->setEnableDetailButton(false);
        $cpField->setEnableNewButton(false);
        $cpField->addParameter('cp_rel_id', $this->User->getRelId());

        $statusField = $this->Field->getSelect('ca_status', $this->getStringParameter('ca_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('waitingSettlement'), '2');
        $statusField->addOption(Trans::getFinanceWord('completed'), '3');
        $statusField->addOption(Trans::getFinanceWord('canceled'), '4');


        $this->ListingForm->addField(Trans::getFinanceWord('number'), $this->Field->getText('ca_number', $this->getStringParameter('ca_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('reference'), $this->Field->getText('ca_reference', $this->getStringParameter('ca_reference')));
        $this->ListingForm->addField(Trans::getFinanceWord('dateFrom'), $this->Field->getCalendar('ca_date_from', $this->getStringParameter('ca_date_from')));
        $this->ListingForm->addField(Trans::getFinanceWord('dateUntil'), $this->Field->getCalendar('ca_date_until', $this->getStringParameter('ca_date_until')));
        $this->ListingForm->addField(Trans::getFinanceWord('cashAccount'), $baField);
        $this->ListingForm->addField(Trans::getFinanceWord('receiver'), $cpField);
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
            'ca_number' => Trans::getFinanceWord('number'),
            'ca_ba_description' => Trans::getFinanceWord('account'),
            'ca_cp_name' => Trans::getFinanceWord('receiver'),
            'ca_reference' => Trans::getFinanceWord('reference'),
            'ca_date' => Trans::getFinanceWord('date'),
            'ca_amount' => Trans::getFinanceWord('amount'),
            'ca_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for CashAdvance.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['ca_id']);
        $this->ListingTable->setColumnType('ca_date', 'date');
        $this->ListingTable->addColumnAttribute('ca_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('ca_amount', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('ca_status', 'style', 'text-align: center;');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CashAdvanceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = CashAdvanceDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $caDao = new CashAdvanceDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            if (empty($row['ca_settlement_on']) === true) {
                $row['ca_amount'] = $row['ca_currency'] . ' ' . $number->doFormatFloat((float)$row['ca_amount'] + (float)$row['ca_reserve_amount']);
            } else {
                $row['ca_amount'] = $row['ca_currency'] . ' ' . $number->doFormatFloat((float)$row['ca_actual_amount'] + (float)$row['ca_ea_amount']);
            }
            # Status
            $row['ca_status'] = $caDao->generateStatus([
                'is_deleted' => !empty($row['ca_deleted_on']),
                'is_completed' => !empty($row['ca_settlement_on']),
                'is_settlement_rejected' => !empty($row['ca_crt_id']) && !empty($row['ca_crt_reject_on']),
                'is_waiting_settlement_confirm' => !empty($row['ca_crt_id']) && empty($row['ca_crt_reject_on']) && empty($row['ca_settlement_on']),
                'is_waiting_settlement' => !empty($row['ca_receive_on']),
                'is_receive_rejected' => !empty($row['ca_crc_id']) && !empty($row['ca_crc_reject_on']),
                'is_waiting_receive_confirm' => !empty($row['ca_crc_id']) && empty($row['ca_crc_reject_on']) && empty($row['ca_receive_on']),
                'is_top_up_exist' => !empty($row['ca_bt_id']),
                'is_top_up_paid' => !empty($row['ca_bt_paid_on']),
                'is_top_up_approved' => !empty($row['ca_bt_approve_on']),
                'is_top_up_requested' => !empty($row['ca_bta_id']),
                'is_top_up_rejected' => !empty($row['ca_bta_reject_on']),
            ]);
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
        $wheres[] = SqlHelper::generateNumericCondition('ca.ca_ss_id', $this->User->getSsId());
        $allowSeeAllAccount = $this->PageSetting->checkPageRight('AllowSeeAllAccount');
        if ($allowSeeAllAccount === false) {
            $wheres[] = SqlHelper::generateNumericCondition('ba.ba_us_id', $this->User->getId());
        }
        if ($allowSeeAllAccount === true && $this->isValidParameter('ca_ba_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ca.ca_ba_id', $this->getIntParameter('ca_ba_id'));
        }
        if ($this->isValidParameter('ca_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ca.ca_number', $this->getStringParameter('ca_number'));
        }
        if ($this->isValidParameter('ca_reference') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ca.ca_reference', $this->getStringParameter('ca_reference'));
        }
        if ($this->isValidParameter('ca_cp_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ca.ca_cp_id', $this->getIntParameter('ca_cp_id'));
        }
        if ($this->isValidParameter('ca_date_from') === true) {
            if ($this->isValidParameter('ca_date_until') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ca.ca_date', $this->getStringParameter('ca_date_from'), '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('ca.ca_date', $this->getStringParameter('ca_date_from'));
            }
        }
        if ($this->isValidParameter('ca_date_until') === true) {
            if ($this->isValidParameter('ca_date_from') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ca.ca_date', $this->getStringParameter('ca_date_until'), '<=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('ca.ca_date', $this->getStringParameter('ca_date_until'));
            }
        }
        if ($this->isValidParameter('ca_status') === true) {
            $status = $this->getIntParameter('ca_status');
            if ($status === 1) {
                # Draft
                $wheres[] = '(ca.ca_crc_id IS NULL)';
                $wheres[] = '(ca.ca_deleted_on IS NULL)';
            } else if ($status === 2) {
                # Waiting Cash Return
                $wheres[] = '(ca.ca_receive_on IS NOT NULL)';
                $wheres[] = '(ca.ca_crt_id IS NULL)';
                $wheres[] = '(ca.ca_deleted_on IS NULL)';
            } else if ($status === 3) {
                # Completed
                $wheres[] = '(ca.ca_settlement_on IS NOT NULL)';
                $wheres[] = '(ca.ca_deleted_on IS NULL)';
            } else {
                $wheres[] = '(ca.ca_deleted_on IS NOT NULL)';
            }
        }

        return $wheres;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if (empty($this->Accounts) === true) {
            $this->disableNewButton();
        }
        parent::loadDefaultButton();
    }
}
