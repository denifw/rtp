<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Job\Trucking;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Job\Trucking\RouteDeliveryDao;

/**
 * Class to control the system of RouteDelivery.
 *
 * @package    app
 * @subpackage Model\Listing\Trucking
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class RouteDelivery extends AbstractListingModel
{

    /**
     * RouteDelivery constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'rd');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Custom Fields
        $stateOrField = $this->Field->getSingleSelect('state', 'rd_stt_or_name', $this->getStringParameter('rd_stt_or_name'), 'loadSingleSelectData');
        $stateOrField->setHiddenField('rd_stt_or_id', $this->getIntParameter('rd_stt_or_id'));
        $stateOrField->setEnableNewButton(false);
        $stateOrField->addClearField('rd_cty_or_name');
        $stateOrField->addClearField('rd_cty_or_id');
        $stateOrField->addClearField('rd_dtc_or_name');
        $stateOrField->addClearField('rd_dtc_or_id');


        $cityOrField = $this->Field->getSingleSelect('city', 'rd_cty_or_name', $this->getStringParameter('rd_cty_or_name'), 'loadSingleSelectData');
        $cityOrField->setHiddenField('rd_cty_or_id', $this->getIntParameter('rd_cty_or_id'));
        $cityOrField->setEnableNewButton(false);
        $cityOrField->addClearField('rd_dtc_or_name');
        $cityOrField->addClearField('rd_dtc_or_id');
        # filter city base on state
        $cityOrField->addOptionalParameterById('cty_stt_id', 'rd_stt_or_id');

        $distOrField = $this->Field->getSingleSelect('district', 'rd_dtc_or_name', $this->getStringParameter('rd_dtc_or_name'), 'loadSingleSelectData');
        $distOrField->setHiddenField('rd_dtc_or_id', $this->getIntParameter('rd_dtc_or_id'));
        $distOrField->setEnableNewButton(false);
        # add option parId using on ajax
        $distOrField->addOptionalParameterById('dtc_cty_id', 'rd_cty_or_id');
        $distOrField->addOptionalParameterById('dtc_stt_id', 'rd_stt_or_id');

        $stateDesField = $this->Field->getSingleSelect('state', 'rd_stt_des_name', $this->getStringParameter('rd_stt_des_name'), 'loadSingleSelectData');
        $stateDesField->setHiddenField('rd_stt_des_id', $this->getIntParameter('rd_stt_des_id'));
        $stateDesField->setEnableNewButton(false);
        $stateDesField->addClearField('rd_cty_des_name');
        $stateDesField->addClearField('rd_cty_des_id');
        $stateDesField->addClearField('rd_dtc_des_name');
        $stateDesField->addClearField('rd_dtc_des_id');

        $cityDesField = $this->Field->getSingleSelect('city', 'rd_cty_des_name', $this->getStringParameter('rd_cty_des_name'), 'loadSingleSelectData');
        $cityDesField->setHiddenField('rd_cty_des_id', $this->getIntParameter('rd_cty_des_id'));
        $cityDesField->setEnableNewButton(false);
        $cityDesField->addClearField('rd_dtc_des_name');
        $cityDesField->addClearField('rd_dtc_des_id');

        # filter city base on state id on district
        $cityDesField->addOptionalParameterById('cty_stt_id', 'rd_stt_des_id');

        $distDesField = $this->Field->getSingleSelect('district', 'rd_dtc_des_name', $this->getStringParameter('rd_dtc_des_name'), 'loadSingleSelectData');
        $distDesField->setHiddenField('rd_dtc_des_id', $this->getIntParameter('rd_dtc_des_id'));
        $distDesField->setEnableNewButton(false);
        # filter city and district base on state
        $distDesField->addOptionalParameterById('dtc_cty_id', 'rd_cty_des_id');
        $distDesField->addOptionalParameterById('dtc_stt_id', 'rd_stt_des_id');

        # add field into field set.
        $this->ListingForm->addField(Trans::getTruckingWord('originState'), $stateOrField);
        $this->ListingForm->addField(Trans::getTruckingWord('originCity'), $cityOrField);
        $this->ListingForm->addField(Trans::getTruckingWord('originDistrict'), $distOrField);
        $this->ListingForm->addField(Trans::getTruckingWord('destinationState'), $stateDesField);
        $this->ListingForm->addField(Trans::getTruckingWord('destinationCity'), $cityDesField);
        $this->ListingForm->addField(Trans::getTruckingWord('destinationDistrict'), $distDesField);
        $this->ListingForm->addField(Trans::getTruckingWord('code'), $this->Field->getText('rd_code', $this->getStringParameter('rd_code')));
        $this->ListingForm->addField(Trans::getTruckingWord('active'), $this->Field->getYesNo('rd_active', $this->getStringParameter('rd_active')));
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
            'rd_code' => Trans::getTruckingWord('code'),
            'rd_dtc_or_id' => Trans::getTruckingWord('origin'),
            'rd_dtc_des_id' => Trans::getTruckingWord('destination'),
            'rd_distance' => Trans::getTruckingWord('distance'),
            'rd_drive_time' => Trans::getTruckingWord('driveTime'),
            'rd_toll_1' => Trans::getTruckingWord('toll1'),
            'rd_toll_2' => Trans::getTruckingWord('toll2'),
            'rd_toll_3' => Trans::getTruckingWord('toll3'),
            'rd_toll_4' => Trans::getTruckingWord('toll4'),
            'rd_toll_5' => Trans::getTruckingWord('toll5'),
            'rd_toll_6' => Trans::getTruckingWord('toll6'),
            'rd_active' => Trans::getTruckingWord('active'),
        ]);
        # Load the data for RouteDelivery.
        $this->ListingTable->addRows($this->doPrepareData($this->loadData()));
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['rd_id']);
        }
        $this->ListingTable->setColumnType('rd_distance', 'float');
        $this->ListingTable->setColumnType('rd_drive_time', 'float');
        $this->ListingTable->setColumnType('rd_toll_1', 'float');
        $this->ListingTable->setColumnType('rd_toll_2', 'float');
        $this->ListingTable->setColumnType('rd_toll_3', 'float');
        $this->ListingTable->setColumnType('rd_toll_4', 'float');
        $this->ListingTable->setColumnType('rd_toll_5', 'float');
        $this->ListingTable->setColumnType('rd_toll_6', 'float');
        $this->ListingTable->setColumnType('rd_active', 'yesno');
    }

    protected function doPrepareData(array $data): array
    {
        $result = [];
        foreach ($data as $row) {
            $row['rd_dtc_or_id'] = $row['rd_dtc_or_name'] . ', ' . $row['rd_cty_or_name'] . ', ' . $row['rd_stt_or_name'];
            $row['rd_dtc_des_id'] = $row['rd_dtc_des_name'] . ', ' . $row['rd_cty_des_name'] . ', ' . $row['rd_stt_des_name'];
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return RouteDeliveryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return RouteDeliveryDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
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
        $wheres[] = SqlHelper::generateNumericCondition('rd.rd_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('rd_stt_or_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('stt_or.stt_id', $this->getIntParameter('rd_stt_or_id'));
        }
        if ($this->isValidParameter('rd_cty_or_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('cty_or.cty_id', $this->getIntParameter('rd_cty_or_id'));
        }
        if ($this->isValidParameter('rd_dtc_or_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dtc_or.dtc_id', $this->getIntParameter('rd_dtc_or_id'));
        }
        if ($this->isValidParameter('rd_stt_des_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('stt_des.stt_id', $this->getIntParameter('rd_stt_des_id'));
        }
        if ($this->isValidParameter('rd_cty_des_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('cty_des.cty_id', $this->getIntParameter('rd_cty_des_id'));
        }
        if ($this->isValidParameter('rd_dtc_des_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dtc_des.dtc_id', $this->getIntParameter('rd_dtc_des_id'));
        }
        if ($this->isValidParameter('rd_active') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rd.rd_active', $this->getStringParameter('rd_active'));
        }
        if ($this->isValidParameter('rd_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rd.rd_code', $this->getStringParameter('rd_code'));
        }
        $wheres[] = '(rd.rd_deleted_on is NULL)';
        # return the list where condition.
        return $wheres;
    }
}
