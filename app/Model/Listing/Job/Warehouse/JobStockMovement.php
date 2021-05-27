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
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobStockMovement.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockMovement extends BaseJobOrder
{

    /**
     * JobStockMovement constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockMovement', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'jm_warehouse', $this->getStringParameter('jm_warehouse'));
        $whField->setHiddenField('jm_wh_id', $this->getIntParameter('jm_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->addClearField('jm_whs_name');
        $whField->addClearField('jm_whs_id');
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        # Warehouse Field
        $whsField = $this->Field->getSingleSelect('warehouse', 'jm_whs_name', $this->getStringParameter('jm_whs_name'));
        $whsField->setHiddenField('jm_whs_id', $this->getIntParameter('jm_whs_id'));
        $whsField->addParameterById('whs_wh_id', 'jm_wh_id', Trans::getWord('warehouse'));
        $whsField->setEnableDetailButton(false);
        $whsField->setEnableNewButton(false);
        # Warehouse Field
        $destinationField = $this->Field->getSingleSelect('warehouse', 'jm_destination_storage', $this->getStringParameter('jm_destination_storage'));
        $destinationField->setHiddenField('jm_new_whs_id', $this->getIntParameter('jm_new_whs_id'));
        $destinationField->addParameterById('whs_wh_id', 'jm_wh_id', Trans::getWord('warehouse'));
        $destinationField->setEnableDetailButton(false);
        $destinationField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');

        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('planningDateFrom'), $this->Field->getCalendar('planning_date_from', $this->getStringParameter('planning_date_from')));
        $this->ListingForm->addField(Trans::getWord('startDateFrom'), $this->Field->getCalendar('start_date_from', $this->getStringParameter('start_date_from')));
        $this->ListingForm->addField(Trans::getWord('completeDateFrom'), $this->Field->getCalendar('complete_date_from', $this->getStringParameter('complete_date_from')));
        $this->ListingForm->addField(Trans::getWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getWord('planningDateUntil'), $this->Field->getCalendar('planning_date_until', $this->getStringParameter('planning_date_until')));
        $this->ListingForm->addField(Trans::getWord('startDateUntil'), $this->Field->getCalendar('start_date_until', $this->getStringParameter('start_date_until')));
        $this->ListingForm->addField(Trans::getWord('completeDateUntil'), $this->Field->getCalendar('complete_date_until', $this->getStringParameter('complete_date_until')));
        $this->ListingForm->addField(Trans::getWord('originStorage'), $whsField);
        $this->ListingForm->addField(Trans::getWord('destinationStorage'), $destinationField);
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
            'jm_wh_name' => Trans::getWord('warehouse'),
            'jm_whs_name' => Trans::getWord('originStorage'),
            'jm_destination_storage' => Trans::getWord('destinationStorage'),
            'jm_date' => Trans::getWord('planningDate'),
            'jm_remark' => Trans::getWord('remark'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('jm_whs_name', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('jm_destination_storage', 'style', 'text-align: center');

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
        return JobMovementDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $sqlResult = JobMovementDao::loadData(
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
            $date = '';
            if (empty($row['jm_date']) === false) {
                if (empty($row['jm_time']) === false) {
                    $date = DateTimeParser::format($row['jm_date'] . ' ' . $row['jm_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $date = DateTimeParser::format($row['jm_date'], 'Y-m-d', 'd M Y');
                }
            }
            $row['jm_date'] = $date;

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
        if ($this->isValidParameter('jm_wh_id') === true) {
            $wheres[] = '(jm.jm_wh_id = ' . $this->getIntParameter('jm_wh_id') . ')';
        }
        if ($this->isValidParameter('jm_whs_id') === true) {
            $wheres[] = '(jm.jm_whs_id = ' . $this->getIntParameter('jm_whs_id') . ')';
        }
        if ($this->isValidParameter('jm_new_whs_id') === true) {
            $wheres[] = '(jm.jm_new_whs_id = ' . $this->getIntParameter('jm_new_whs_id') . ')';
        }
        if ($this->isValidParameter('planning_date_from') === true) {
            if ($this->isValidParameter('planning_date_until') === true) {
                $wheres[] = "(jm.jm_date >= '" . $this->getStringParameter('planning_date_from') . "')";
            } else {
                $wheres[] = "(jm.jm_date = '" . $this->getStringParameter('planning_date_from') . "')";
            }
        }
        if ($this->isValidParameter('planning_date_until') === true) {
            if ($this->isValidParameter('planning_date_from') === true) {
                $wheres[] = "(jm.jm_date <= '" . $this->getStringParameter('planning_date_until') . "')";
            } else {
                $wheres[] = "(jm.jm_date = '" . $this->getStringParameter('planning_date_until') . "')";
            }
        }
        return $wheres;
    }

}
