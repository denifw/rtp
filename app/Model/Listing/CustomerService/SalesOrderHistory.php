<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CustomerService\SalesOrderDao;

/**
 * Class to control the system of SalesOrder.
 *
 * @package    app
 * @subpackage Model\Listing\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrderHistory extends AbstractListingModel
{

    /**
     * SalesOrder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'soHistory');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Order Office Field
        $ofOrderField = $this->Field->getSingleSelect('office', 'so_order_office', $this->getStringParameter('so_order_office'));
        $ofOrderField->setHiddenField('so_order_of_id', $this->getIntParameter('so_order_of_id'));
        $ofOrderField->addParameter('of_rel_id', $this->User->getRelId());
        if ($this->PageSetting->checkPageRight('AllowSeeAllOrderOffice') === false) {
            $ofOrderField->addParameter('of_id', $this->User->Relation->getOfficeId());
        }
        $ofOrderField->setEnableDetailButton(false);
        $ofOrderField->setEnableNewButton(false);
        $ofOrderField->addClearField('so_sales_manager');
        $ofOrderField->addClearField('so_sales_id');

        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'so_customer', $this->getStringParameter('so_customer'));
        $relField->setHiddenField('so_rel_id', $this->getIntParameter('so_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $relField->addParameter('rel_id', $this->User->getRelId());
        }
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('soNumber'), $this->Field->getText('so_number', $this->getStringParameter('so_number')));
        $this->ListingForm->addField(Trans::getWord('orderOffice'), $ofOrderField);
        $this->ListingForm->addField(Trans::getWord('orderDateFrom'), $this->Field->getCalendar('order_date_from', $this->getStringParameter('order_date_from')));
        $this->ListingForm->addField(Trans::getWord('orderDateUntil'), $this->Field->getCalendar('order_date_until', $this->getStringParameter('order_date_until')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('so_reference', $this->getStringParameter('so_reference')));
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
            'so_number' => Trans::getWord('soNumber'),
            'so_order_date' => Trans::getWord('orderDate'),
            'so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('customerRef'),
            'so_container' => Trans::getWord('container'),
            'so_inklaring' => Trans::getWord('inklaring'),
            'so_delivery' => Trans::getWord('delivery'),
            'so_ict_code' => Trans::getWord('incoTerms'),
            'so_warehouse' => Trans::getWord('warehouse'),
            'so_status' => Trans::getWord('lastStatus'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('so_order_date', 'date');
        $this->ListingTable->setColumnType('so_container', 'yesno');
        $this->ListingTable->setColumnType('so_inklaring', 'yesno');
        $this->ListingTable->setColumnType('so_delivery', 'yesno');
        $this->ListingTable->setColumnType('so_warehouse', 'yesno');
        $this->ListingTable->addColumnAttribute('so_status', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('so_ict_code', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink('so/view', ['so_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SalesOrderDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = SalesOrderDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        return $this->doPrepareData($data);
    }

    /**
     * Function to do prepare data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $result = [];
        $soDao = new SalesOrderDao();
        foreach ($data as $row) {
            # Customer
            $customers = [$row['so_customer']];
            if (empty($row['so_pic_customer']) === false) {
                $customers[] = Trans::getWord('pic') . ' : ' . $row['so_pic_customer'];
            }
            $row['so_customer'] = StringFormatter::generateTableView($customers, 'text-align:left;');
            # Status
            $row['so_status'] = $soDao->generateStatus([
                'is_deleted' => !empty($row['so_deleted_on']),
                'is_hold' => !empty($row['soh_id']),
                'is_finish' => !empty($row['so_finish_on']),
                'is_in_progress' => !empty($row['so_start_on']),
                'is_publish' => !empty($row['so_publish_on']),
            ]);
            $row['so_customer_ref'] = $soDao->concatReference($row);
            $result[] = $row;
        }

        return $result;
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
        $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->User->getSsId());
        $deleted = SqlHelper::generateNullCondition('so.so_deleted_on', false);
        $archive = SqlHelper::generateNullCondition('so.so_soa_id', false);
        $finish = SqlHelper::generateNullCondition('so.so_finish_on', false);
        $wheres[] = '(' . $deleted . ' OR ' . $archive . ' OR ' . $finish . ')';

        # So Number
        if ($this->isValidParameter('so_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('so_number'));
        }
        # order office
        if ($this->isValidParameter('so_order_of_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('so.so_order_of_id', $this->getIntParameter('so_order_of_id'));
        }
        # Order date filter
        if ($this->isValidParameter('order_date_from') === true) {
            if ($this->isValidParameter('order_date_until') === true) {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_from'), '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_from'));
            }
        }
        if ($this->isValidParameter('order_date_until') === true) {
            if ($this->isValidParameter('order_date_from') === true) {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_until'), '<=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_until'));
            }
        }
        # Relation
        if ($this->isValidParameter('so_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('so.so_rel_id', $this->getIntParameter('so_rel_id'));
        }
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(so.so_rel_id = ' . $this->User->getRelId() . ')';
        }
        # so reference filter
        if ($this->isValidParameter('so_reference') === true) {
            $orWheres = [];
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_bl_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_packing_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_aju_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_sppb_ref', $this->getStringParameter('so_reference'));
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        # Sales Manager
        if ($this->isValidParameter('so_sales_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('so.so_sales_id', $this->getIntParameter('so_sales_id'));
        }
        # Status
        if ($this->isValidParameter('so_status') === true) {
            $status = $this->getIntParameter('so_status');
            if ($status === 1) {
                # Draft
                $wheres[] = '(so.so_publish_on IS NULL)';
            } else if ($status === 2) {
                # In Progress
                $wheres[] = '(so.so_publish_on IS NOT NULL)';
                $wheres[] = '(so.so_start_on IS NULL)';
            } else if ($status === 3) {
                # In Progress
                $wheres[] = '(so.so_start_on IS NOT NULL)';
                $wheres[] = '(so.so_finish_on IS NULL)';
            } else {
                $wheres[] = '(so.so_finish_on IS NOT NULL)';
            }
        }


        # return the where query.
        return $wheres;
    }
}
