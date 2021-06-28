<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\Administration;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Administration\CashTransferDao;

/**
 * Class to control the system of CashTransfer.
 *
 * @package    app
 * @subpackage Model\Listing\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class CashTransfer extends AbstractListingModel
{

    /**
     * CashTransfer constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ct');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $payerField = $this->Field->getSingleSelect('ba', 'ct_payer', $this->getStringParameter('ct_payer'));
        $payerField->setHiddenField('ct_payer_ba_id', $this->getStringParameter('ct_payer_ba_id'));
        $payerField->setEnableNewButton(false);
        $payerField->addParameter('ba_ss_id', $this->User->getSsId());

        $receiverField = $this->Field->getSingleSelect('ba', 'ct_receiver', $this->getStringParameter('ct_receiver'));
        $receiverField->setHiddenField('ct_receiver_ba_id', $this->getStringParameter('ct_receiver_ba_id'));
        $receiverField->setEnableNewButton(false);
        $receiverField->addParameter('ba_ss_id', $this->User->getSsId());

        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('ct_number', $this->getStringParameter('ct_number')));
        $this->ListingForm->addField(Trans::getWord('sender'), $payerField);
        $this->ListingForm->addField(Trans::getWord('receiver'), $receiverField);
        $this->ListingForm->addField(Trans::getWord('date'), $this->Field->getCalendar('ct_date', $this->getStringParameter('ct_date')));
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
            'ct_number' => Trans::getWord('number'),
            'ct_date' => Trans::getWord('date'),
            'ct_payer' => Trans::getWord('sender'),
            'ct_receiver' => Trans::getWord('receiver'),
            'ct_amount' => Trans::getWord('amount'),
            'ct_notes' => Trans::getWord('notes'),
            'ct_deleted' => Trans::getWord('deleted'),
        ]);
        # Load the data for CashTransfer.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('ct_date', 'date');
        $this->ListingTable->setColumnType('ct_amount', 'float');
        $this->ListingTable->addColumnAttribute('ct_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('ct_deleted', 'style', 'text-align: center;');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ct_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CashTransferDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = CashTransferDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        foreach ($data as $row) {
            $row['ct_deleted'] = '';
            if (empty($row['ct_deleted_on']) === false) {
                $row['ct_deleted'] = new LabelDanger(Trans::getWord('deleted'));
            }
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

        $wheres[] = SqlHelper::generateStringCondition('ct.ct_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('ct_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ct.ct_number', $this->getStringParameter('ct_number'));
        }
        if ($this->isValidParameter('ct_payer_ba_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ct.ct_payer_ba_id', $this->getStringParameter('ct_payer_ba_id'));
        }
        if ($this->isValidParameter('ct_receiver_ba_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ct.ct_receiver_ba_id', $this->getStringParameter('ct_receiver_ba_id'));
        }
        if ($this->isValidParameter('ct_date') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ct.ct_date', $this->getStringParameter('ct_date'));
        }


        # return the list where condition.
        return $wheres;
    }
}
