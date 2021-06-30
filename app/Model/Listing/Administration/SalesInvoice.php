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
use App\Model\Dao\Administration\SalesInvoiceDao;

/**
 * Class to control the system of SalesInvoice.
 *
 * @package    app
 * @subpackage Model\Listing\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoice extends AbstractListingModel
{

    /**
     * SalesInvoice constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'si');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relField = $this->Field->getSingleSelect('rel', 'si_customer', $this->getStringParameter('si_customer'));
        $relField->setHiddenField('si_rel_id', $this->getStringParameter('si_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('si_number', $this->getStringParameter('si_number')));
        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('si_jo_number', $this->getStringParameter('si_jo_number')));
        $this->ListingForm->addField(Trans::getWord('relation'), $relField);
        $this->ListingForm->addField(Trans::getWord('overDue'), $this->Field->getYesNo('si_over_due', $this->getStringParameter('si_over_due')));
        $this->ListingForm->addField(Trans::getWord('paid'), $this->Field->getYesNo('si_paid', $this->getStringParameter('si_paid')));
        $this->ListingForm->addField(Trans::getWord('deleted'), $this->Field->getYesNo('si_deleted', $this->getStringParameter('si_deleted')));
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
            'si_number' => Trans::getWord('number'),
            'si_vendor' => Trans::getWord('customer'),
            'si_job_order' => Trans::getWord('jobNumber'),
            'si_total' => Trans::getWord('amount'),
            'pi_due_date' => Trans::getWord('due'),
            'pi_status' => Trans::getWord('status'),
        ]);
        # Load the data for SalesInvoice.
        $this->ListingTable->addRows($this->loadData());
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['si_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SalesInvoiceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = SalesInvoiceDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $total = (float)$row['si_total'];
            $row['si_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($total);
            if (empty($row['si_jo_id']) === false) {
                $row['si_job_order'] = $row['si_jo_number'] . ' - ' . $row['si_jo_name'];
            }
            $row['si_status'] = SalesInvoiceDao::generateStatus($row);
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
        $wheres[] = SqlHelper::generateStringCondition('si.si_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('si_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('si.si_number', $this->getStringParameter('si_number'));
        }
        if ($this->isValidParameter('si_jo_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('si_jo_number'));
        }
        if ($this->isValidParameter('si_rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('si.si_rel_id', $this->getStringParameter('si_rel_id'));
        }
        if ($this->isValidParameter('si_paid') === true) {
            if ($this->getStringParameter('si_paid', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('si.si_paid_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('si.si_paid_on');
                $wheres[] = SqlHelper::generateNullCondition('si.si_deleted_on');
            }
        }
        if ($this->isValidParameter('si_over_due') === true) {
            if ($this->getStringParameter('si_over_due', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateStringCondition('si.si_due_date', date('Y-m-d'), '<');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('si.si_due_date', date('Y-m-d'), '>=');
            }
            $wheres[] = SqlHelper::generateNullCondition('si.si_paid_on');
            $wheres[] = SqlHelper::generateNullCondition('si.si_deleted_on');
        }
        if ($this->isValidParameter('si_deleted') === true) {
            if ($this->getStringParameter('si_deleted', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('si.si_deleted_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('si.si_deleted_on');
            }
        }

        # return the list where condition.
        return $wheres;
    }
}
