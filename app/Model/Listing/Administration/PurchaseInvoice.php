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

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Administration\PurchaseInvoiceDao;

/**
 * Class to control the system of PurchaseInvoice.
 *
 * @package    app
 * @subpackage Model\Listing\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PurchaseInvoice extends AbstractListingModel
{

    /**
     * PurchaseInvoice constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pi');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relField = $this->Field->getSingleSelect('rel', 'pi_vendor', $this->getStringParameter('pi_vendor'));
        $relField->setHiddenField('pi_rel_id', $this->getStringParameter('pi_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('pi_number', $this->getStringParameter('pi_number')));
        $this->ListingForm->addField(Trans::getWord('relation'), $relField);
        $this->ListingForm->addField(Trans::getWord('date'), $this->Field->getCalendar('pi_date', $this->getStringParameter('pi_date')));
        $this->ListingForm->addField(Trans::getWord('paid'), $this->Field->getYesNo('pi_paid', $this->getStringParameter('pi_paid')));
        $this->ListingForm->addField(Trans::getWord('verified'), $this->Field->getYesNo('pi_verified', $this->getStringParameter('pi_verified')));
        $this->ListingForm->addField(Trans::getWord('deleted'), $this->Field->getYesNo('pi_deleted', $this->getStringParameter('pi_deleted')));
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
            'pi_number' => Trans::getWord('number'),
            'pi_vendor' => Trans::getWord('vendor'),
            'pi_reference' => Trans::getWord('reference'),
            'pi_date' => Trans::getWord('date'),
            'pi_total' => Trans::getWord('amount'),
            'pi_due_date' => Trans::getWord('due'),
            'pi_status' => Trans::getWord('status'),
        ]);
        # Load the data for PurchaseInvoice.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('pi_date', 'date');
        $this->ListingTable->setColumnType('pi_due_date', 'date');
        $this->ListingTable->addColumnAttribute('pi_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('pi_total', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('pi_status', 'style', 'text-align: center;');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pi_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PurchaseInvoiceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = PurchaseInvoiceDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $total = (float)$row['pi_total'];
            $row['pi_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($total);
            $row['pi_status'] = PurchaseInvoiceDao::generateStatus($row);
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
        $wheres[] = SqlHelper::generateStringCondition('pi.pi_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('pi_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pi.pi_number', $this->getStringParameter('pi_number'));
        }
        if ($this->isValidParameter('pi_rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pi.pi_rel_id', $this->getStringParameter('pi_rel_id'));
        }
        if ($this->isValidParameter('pi_date') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pi.pi_date', $this->getStringParameter('pi_date'));
        }
        if ($this->isValidParameter('pi_paid') === true) {
            if ($this->getStringParameter('pi_paid', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_paid_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_paid_on');
            }
        }
        if ($this->isValidParameter('pi_verified') === true) {
            if ($this->getStringParameter('pi_verified', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_verified_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_verified_on');
            }
        }
        if ($this->isValidParameter('pi_deleted') === true) {
            if ($this->getStringParameter('pi_deleted', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_deleted_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('pi.pi_deleted_on');
            }
        }
        # return the list where condition.
        return $wheres;
    }
}
