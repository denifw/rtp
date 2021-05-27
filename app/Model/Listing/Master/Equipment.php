<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\EquipmentDao;
use App\Model\Dao\System\EquipmentStatusDao;

/**
 * Class to control the system of Equipment.
 *
 * @package    app
 * @subpackage Model\Listing\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Equipment extends AbstractListingModel
{

    /**
     * Equipment constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipment');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Transport Module
        $eqField = $this->Field->getSingleSelect('eg', 'eq_group', $this->getStringParameter('eq_group'));
        $eqField->setHiddenField('eq_eg_id', $this->getIntParameter('eq_eg_id'));
        $eqField->setEnableNewButton(false);
        $eqField->setEnableDetailButton(false);
        # Owner
        $ownerField = $this->Field->getSingleSelect('relation', 'eq_owner', $this->getStringParameter('eq_owner'));
        $ownerField->setHiddenField('eq_rel_id', $this->getIntParameter('eq_rel_id'));
        $ownerField->addParameter('rel_ss_id', $this->User->getSsId());
        $ownerField->setEnableNewButton(false);
        $ownerField->setEnableDetailButton(false);
        # Manage By
        $manageField = $this->Field->getSingleSelect('relation', 'eq_manage_by_name', $this->getStringParameter('eq_manage_by_name'));
        $manageField->setHiddenField('eq_manage_by_id', $this->getIntParameter('eq_manage_by_id'));
        $manageField->addParameter('rel_ss_id', $this->User->getSsId());
        $manageField->setEnableNewButton(false);
        $manageField->setEnableDetailButton(false);

        # Status Field
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('eqs_active', 'Y');
        $statusData = EquipmentStatusDao::loadData($wheres);
        $statusField = $this->Field->getSelect('eq_eqs_id', $this->getIntParameter('eq_eqs_id'));
        $statusField->addOptions($statusData, 'eqs_name', 'eqs_id');

        # Add Field into field set.
        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('eq_number', $this->getStringParameter('eq_number')));
        $this->ListingForm->addField(Trans::getWord('transportType'), $eqField);
        $this->ListingForm->addField(Trans::getWord('owner'), $ownerField);
        $this->ListingForm->addField(Trans::getFmsWord('manageBy'), $manageField);
        $this->ListingForm->addField(Trans::getFmsWord('licensePlate'), $this->Field->getText('eq_license_plate', $this->getStringParameter('eq_license_plate')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('eq_description', $this->getStringParameter('eq_description')));
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
            'eq_number' => Trans::getFmsWord('number'),
            'eg_transport_module' => Trans::getFmsWord('transportModule'),
            'eq_group' => Trans::getFmsWord('transportType'),
            'eq_description' => Trans::getFmsWord('description'),
            'eq_owner' => Trans::getFmsWord('owner'),
            'eq_manage_by_name' => Trans::getFmsWord('manageBy'),
            'eq_license_plate' => Trans::getFmsWord('licensePlate'),
            'eq_eqs_name' => Trans::getFmsWord('status'),
        ]);
        # Load the data for Equipment.
        $listingData = [];
        $tempData = $this->loadData();
        foreach ($tempData as $row) {
            $status = '';
            if ($row['eq_eqs_name'] === 'Available') {
                $status = new LabelSuccess($row['eq_eqs_name']);
            } elseif ($row['eq_eqs_name'] === 'Not Available') {
                $status = new LabelDark($row['eq_eqs_name']);
            } elseif ($row['eq_eqs_name'] === 'On Service') {
                $status = new LabelWarning($row['eq_eqs_name']);
            }
            $row['eq_eqs_name'] = $status;
            $listingData[] = $row;
        }
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('eqm_meter', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('eqf_total_fuel', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('eq_eqs_name', 'style', 'text-align: center;');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['eq_id']);
        }
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['eq_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return EquipmentDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        return EquipmentDao::loadData(
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
        $wheres[] = SqlHelper::generateNumericCondition('eq.eq_ss_id', $this->User->getSsId());

        if ($this->isValidParameter('eq_eg_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eq.eq_eg_id', $this->getIntParameter('eq_eg_id'));
        }
        if ($this->isValidParameter('eq_manage_by_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eq.eq_manage_by_id', $this->getIntParameter('eq_manage_by_id'));
        }
        if ($this->isValidParameter('eq_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eq.eq_rel_id', $this->getIntParameter('eq_rel_id'));
        }
        if ($this->isValidParameter('eq_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eq.eq_number', $this->getStringParameter('eq_number'));
        }

        if ($this->isValidParameter('eq_license_plate') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eq.eq_license_plate', $this->getStringParameter('eq_license_plate'));
        }
        if ($this->isValidParameter('eq_description') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eq.eq_description', $this->getStringParameter('eq_description'));
        }

        if ($this->isValidParameter('eq_eqs_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('eq.eq_eqs_id', $this->getIntParameter('eq_eqs_id'));
        }

        # return the where query.
        return $wheres;
    }
}
