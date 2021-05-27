<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Crm\Quotation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\Quotation\PriceDao;
use App\Model\Dao\System\Service\ServiceDao;


/**
 * Class to control the system of Price.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class CogsWarehouse extends AbstractListingModel
{

    /*
     * Property to store service id.
     *
     * @var int $SrvId
     * */
    private $SrvId;

    /**
     * Price constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'prcPrcWh');
        $this->setParameters($parameters);
        $this->loadServiceId();
    }

    /**
     * Function to load service id.
     *
     * @return void
     */
    private function loadServiceId(): void
    {
        $this->SrvId = ServiceDao::getIdByCode('warehouse');
    }


    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Add Customer
        $customerField = $this->Field->getSingleSelect('relation', 'prc_relation', $this->getStringParameter('prc_relation'));
        $customerField->setHiddenField('prc_rel_id', $this->getIntParameter('prc_rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableNewButton(false);
        # Status field
        $statusField = $this->Field->getSelect('prc_status', $this->getStringParameter('prc_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('submitted'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('approved'), '4');
        $statusField->addOption(Trans::getFinanceWord('expired'), '5');
        $statusField->addOption(Trans::getFinanceWord('deleted'), '6');

        # Warehouse
        $whField = $this->Field->getSingleSelect('warehouse', 'prc_wh_name', $this->getStringParameter('prc_wh_name'), 'loadSingleSelectPrice');
        $whField->setHiddenField('prc_wh_id', $this->getIntParameter('prc_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getFinanceWord('code'), $this->Field->getText('prc_code', $this->getStringParameter('prc_code')));
        $this->ListingForm->addField(Trans::getFinanceWord('quotation'), $this->Field->getText('qt_number', $this->getStringParameter('qt_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('vendor'), $customerField);
        $this->ListingForm->addField(Trans::getFinanceWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
        $this->ListingForm->setGridDimension(4);


    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        $this->loadResultTableByCode();
    }

    /**
     * Function to load result table for code view.
     *
     * @return void
     */
    private function loadResultTableByCode(): void
    {
        # Set header column table
        $this->ListingTable->setHeaderRow([
            'prc_qt_number' => Trans::getFinanceWord('quotation'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
            'prc_warehouse' => Trans::getFinanceWord('warehouse'),
            'prc_total' => Trans::getFinanceWord('totalPrice'),
            'prc_status' => Trans::getFinanceWord('status'),
        ]);

        # Load the data for Price.
        $this->ListingTable->addRows($this->loadDataByCode());

        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['prc_id']);
        }
        $this->ListingTable->setColumnType('prc_total', 'float');
        $this->ListingTable->addColumnAttribute('prc_qt_number', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('prc_code', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('prc_status', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PriceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data by code.
     *
     * @return array
     */
    private function loadDataByCode(): array
    {
        $data = PriceDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $prcDao = new PriceDao();
        foreach ($data as $row) {
            # Status
            $row['prc_status'] = $prcDao->getStatus($row);
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
        $wheres[] = SqlHelper::generateNumericCondition('prc.prc_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNumericCondition('prc.prc_srv_id', $this->SrvId);
        $wheres[] = SqlHelper::generateStringCondition('prc.prc_type', 'P');
        if ($this->isValidParameter('prc_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('prc.prc_code', $this->getStringParameter('prc_code'));
        }
        if ($this->isValidParameter('qt_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('qt_number'));
        }
        if ($this->isValidParameter('prc_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('rel.rel_id', $this->getIntParameter('prc_rel_id'));
        }
        if ($this->isValidParameter('prc_wh_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_wh_id', $this->getIntParameter('prc_wh_id'));
        }
        # Filter Status
        if ($this->isValidParameter('prc_status') === true) {
            if ($this->getIntParameter('prc_status') === 1) {
                # Draft
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_qts_id IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 2) {
                # Submitted
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 3) {
                # Rejected
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NOT NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 4) {
                # Approved
                $wheres[] = '(qt.qt_approve_on IS NOT NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 5) {
                # Expired
                $wheres[] = "(qt.qt_end_date < '" . date('Y-m-d') . "')";
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } else {
                # Deleted
                $wheres[] = '(prc.prc_deleted_on IS NOT NULL)';
            }
        } else {
            $wheres[] = '(prc.prc_deleted_on IS NULL)';
        }
        # return the list where condition.
        return $wheres;
    }
}
