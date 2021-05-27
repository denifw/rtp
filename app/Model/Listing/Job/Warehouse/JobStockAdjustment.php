<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobStockAdjustment.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockAdjustment extends BaseJobOrder
{

    /**
     * JobStockAdjustment constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockAdjustment', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        # Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'ja_wh_name', $this->getStringParameter('ja_wh_name'));
        $whField->setHiddenField('ja_wh_id', $this->getIntParameter('ja_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('ja_gd_id', $this->getIntParameter('ja_gd_id'));
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addOptionalParameterById('gd_rel_id', 'jo_rel_id');
        $goodsField->setEnableNewButton(false);


        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('planningDateFrom'), $this->Field->getCalendar('order_date_from', $this->getStringParameter('order_date_from')));
        $this->ListingForm->addField(Trans::getWord('startDateFrom'), $this->Field->getCalendar('start_date_from', $this->getStringParameter('start_date_from')));
        $this->ListingForm->addField(Trans::getWord('completeDateFrom'), $this->Field->getCalendar('complete_date_from', $this->getStringParameter('complete_date_from')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('planningDateUntil'), $this->Field->getCalendar('order_date_until', $this->getStringParameter('order_date_until')));
        $this->ListingForm->addField(Trans::getWord('startDateUntil'), $this->Field->getCalendar('start_date_until', $this->getStringParameter('start_date_until')));
        $this->ListingForm->addField(Trans::getWord('completeDateUntil'), $this->Field->getCalendar('complete_date_until', $this->getStringParameter('complete_date_until')));
        $this->ListingForm->addField(Trans::getWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getWord('goods'), $goodsField);
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
            'jo_number' => Trans::getWord('jobNumber'),
            'wh_name' => Trans::getWord('warehouse'),
            'jo_customer' => Trans::getWord('customer'),
            'ja_goods' => Trans::getWord('goods'),
            'jo_order_date' => Trans::getWord('planningDate'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        # Load the data for JobStockAdjustment.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobAdjustmentDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $sqlResult = JobAdjustmentDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        return $this->doPrepareData($sqlResult);
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
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            if (empty($row['jo_order_date']) === false) {
                $row['jo_order_date'] = DateTimeParser::format($row['jo_order_date'], 'Y-m-d', 'd M Y');
            }
            $row['jo_status'] = $joDao->generateStatus([
                'is_hold' => !empty($row['joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
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
        $wheres = $this->getJoConditions(false);
        if ($this->isValidParameter('ja_wh_id') === true) {
            $wheres[] = '(ja.ja_wh_id = ' . $this->getIntParameter('ja_wh_id') . ')';
        }
        if ($this->isValidParameter('ja_gd_id') === true) {
            $wheres[] = '(ja.ja_gd_id = ' . $this->getIntParameter('ja_gd_id') . ')';
        }
        if ($this->isValidParameter('jo_number_ref') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jo2.jo_number', $this->getStringParameter('jo_number_ref'));
        }
        return $wheres;
    }
}
