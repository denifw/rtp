<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\EquipmentGroupDao;

/**
 * Class to control the system of EquipmentGroup.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class EquipmentGroup extends AbstractListingModel
{

    /**
     * EquipmentGroup constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'eg');
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
        $tmField = $this->Field->getSingleSelect('transportModule', 'eg_module', $this->getStringParameter('eg_module'));
        $tmField->setHiddenField('eg_tm_id', $this->getIntParameter('eg_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setEnableDetailButton(false);

        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('eg_name', $this->getStringParameter('eg_name')));
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('eg_code', $this->getStringParameter('eg_code')));
        $this->ListingForm->addField(Trans::getWord('module'), $tmField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('eg_active', $this->getStringParameter('eg_active')));
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
            'eg_name' => Trans::getWord('name'),
            'eg_code' => Trans::getWord('code'),
            'eg_module' => Trans::getWord('module'),
            'eg_container' => Trans::getWord('container'),
            'eg_sty_name' => Trans::getWord('class'),
            'eg_active' => Trans::getWord('active'),
        ]);
        # Load the data for EquipmentGroup.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['eg_id']);
        $this->ListingTable->setColumnType('eg_container', 'yesno');
        $this->ListingTable->setColumnType('eg_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        return EquipmentGroupDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        return EquipmentGroupDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable()
        );
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
        if ($this->isValidParameter('eg_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eg_name', $this->getStringParameter('eg_name'));
        }
        if ($this->isValidParameter('eg_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('eg_code', $this->getStringParameter('eg_code'));
        }
        if ($this->isValidParameter('eg_tm_id') === true) {
            $wheres[] = '(eg.eg_tm_id = ' . $this->getIntParameter('eg_tm_id') . ')';
        }
        if ($this->isValidParameter('eg_active') === true) {
            $wheres[] = "(eg.eg_active = '" . $this->getStringParameter('eg_active') . "')";
        }
        # return the where query.
        return $wheres;
    }
}
