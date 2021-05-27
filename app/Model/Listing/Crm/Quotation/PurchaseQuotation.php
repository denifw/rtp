<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Crm\Quotation;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\Quotation\QuotationDao;

/**
 * Class to control the system of SalesQuotation.
 *
 * @package    app
 * @subpackage Model\Listing\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PurchaseQuotation extends AbstractListingModel
{

    /**
     * SalesQuotation constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'prcQt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Relation Field
        $relationField = $this->Field->getSingleSelect('relation', 'qt_relation', $this->getStringParameter('qt_relation'));
        $relationField->setHiddenField('qt_rel_id', $this->getIntParameter('qt_rel_id'));
        $relationField->setEnableNewButton(false);
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());

        # ORDER Office
        $officeField = $this->Field->getSingleSelect('office', 'qt_order_office', $this->getStringParameter('qt_order_office'));
        $officeField->setHiddenField('qt_order_of_id', $this->getIntParameter('qt_order_of_id'));
        $officeField->setEnableNewButton(false);
        $officeField->addParameter('of_rel_id', $this->User->getRelId());

        # Manager Field
        $usField = $this->Field->getSingleSelect('user', 'qt_manager', $this->getStringParameter('qt_manager'));
        $usField->setHiddenField('qt_us_id', $this->getIntParameter('qt_us_id'));
        $usField->setEnableNewButton(false);
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->addParameter('rel_id', $this->User->getRelId());
        # Status field
        $statusField = $this->Field->getSelect('qt_status', $this->getStringParameter('qt_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('submitted'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('approved'), '4');
        $statusField->addOption(Trans::getFinanceWord('expired'), '5');
        $statusField->addOption(Trans::getFinanceWord('deleted'), '6');

        $this->ListingForm->addField(Trans::getFinanceWord('quotationNumber'), $this->Field->getText('qt_number', $this->getStringParameter('qt_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('dealNumber'), $this->Field->getText('dl_number', $this->getStringParameter('dl_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
        $this->ListingForm->addField(Trans::getFinanceWord('vendor'), $relationField);
        $this->ListingForm->addField(Trans::getFinanceWord('orderOffice'), $officeField);
        $this->ListingForm->addField(Trans::getFinanceWord('quotationManager'), $usField);
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
            'qt_number' => Trans::getFinanceWord('number'),
            'qt_relation' => Trans::getFinanceWord('vendor'),
            'qt_order_office' => Trans::getFinanceWord('orderOffice'),
            'qt_dl_number' => Trans::getFinanceWord('dealNumber'),
            'qt_period' => Trans::getFinanceWord('period'),
            'qt_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for SalesQuotation.
        $this->ListingTable->addRows($this->loadData());
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['qt_id']);
        }
        $this->ListingTable->addColumnAttribute('qt_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('qt_order_office', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('qt_dl_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('qt_period', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('qt_status', 'style', 'text-align: center;');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return QuotationDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = QuotationDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        $results = [];
        $qtDao = new QuotationDao();
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $row['qt_period'] = $dt->formatDate($row['qt_start_date']) . ' - ' . $dt->formatDate($row['qt_end_date']);
            $row['qt_status'] = $qtDao->getStatus($row);
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
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', 'P');
        if ($this->isValidParameter('qt_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('qt_number'));
        }
        if ($this->isValidParameter('dl_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dl.dl_number', $this->getStringParameter('dl_number'));
        }
        if ($this->isValidParameter('qt_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getStringParameter('qt_rel_id'));
        }
        if ($this->isValidParameter('qt_order_of_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_order_of_id', $this->getStringParameter('qt_order_of_id'));
        }
        if ($this->isValidParameter('qt_us_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_us_id', $this->getStringParameter('qt_us_id'));
        }
        # Filter Status
        if ($this->isValidParameter('qt_status') === true) {
            if ($this->getIntParameter('qt_status') === 1) {
                # Draft
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_qts_id IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('qt_status') === 2) {
                # Submitted
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('qt_status') === 3) {
                # Rejected
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NOT NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('qt_status') === 4) {
                # Approved
                $wheres[] = '(qt.qt_approve_on IS NOT NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('qt_status') === 5) {
                # Expired
                $wheres[] = "(qt.qt_end_date < '" . date('Y-m-d') . "')";
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
            } else {
                # Deleted
                $wheres[] = '(qt.qt_deleted_on IS NOT NULL)';
            }
        } else {
            $wheres[] = '(qt.qt_deleted_on IS NULL)';
        }

        # return the list where condition.
        return $wheres;
    }
}
