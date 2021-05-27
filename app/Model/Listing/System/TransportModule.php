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
use App\Model\Dao\System\TransportModuleDao;

/**
 * Class to control the system of TransportModule.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class TransportModule extends AbstractListingModel
{

    /**
     * TransportModule constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'transportModule');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('tm_name', $this->getStringParameter('tm_name')));
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('tm_code', $this->getStringParameter('tm_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('tm_active', $this->getStringParameter('tm_active')));
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
            'tm_name' => Trans::getWord('name'),
            'tm_code' => Trans::getWord('code'),
            'tm_active' => Trans::getWord('active'),
        ]);
        # Load the data for TransportModule.
        $dataLoad = $this->loadData();
        $this->ListingTable->addRows($dataLoad);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['tm_id']);
        $this->ListingTable->setColumnType('tm_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        return TransportModuleDao::loadTotalData($this->getWhereCondition());
    }

    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        return TransportModuleDao::loadData(
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
        if ($this->isValidParameter('tm_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tm_name', $this->getStringParameter('tm_name'));
        }
        if ($this->isValidParameter('tm_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tm_code', $this->getStringParameter('tm_code'));
        }
        if ($this->isValidParameter('tm_active') === true) {
            $wheres[] = "(tm_active = '" . $this->getStringParameter('tm_active') . "')";
        }

        $wheres[] = '(tm.tm_deleted_on is NULL)';
        # return the where query.
        return $wheres;
    }
}
