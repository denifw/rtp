<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\DashboardItemDao;

/**
 * Class to manage the creation of the listing DashboardItem page.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class DashboardItem extends AbstractListingModel
{

    /**
     * DashboardItem constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dashboardItem');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $moduleField = $this->Field->getSingleSelect('sty', 'dsi_module_name', $this->getStringParameter('dsi_module_name'));
        $moduleField->setHiddenField('dsi_module_id', $this->getIntParameter('dsi_module_id'));
        $moduleField->addParameter('sty_group', 'dashboardmodule');
        $moduleField->setEnableNewButton(false);
        $moduleField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getWord('title'), $this->Field->getText('dsi_title', $this->getStringParameter('dsi_title')));
        $this->ListingForm->addField(Trans::getWord('route'), $this->Field->getText('dsi_route', $this->getStringParameter('dsi_route')));
        $this->ListingForm->addField(Trans::getWord('module'), $moduleField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dsi_active', $this->getStringParameter('dsi_active')));
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'dsi_title' => Trans::getWord('title'),
                'dsi_code' => Trans::getWord('code'),
                'dsi_route' => Trans::getWord('route'),
                'dsi_module_name' => Trans::getWord('module'),
                'dsi_path' => Trans::getWord('path'),
                'dsi_description' => Trans::getWord('description'),
                'dsi_order' => Trans::getWord('order'),
                'dsi_parameter' => 'parameter',
                'dsi_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for DashboardItem.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['dsi_id']);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dsi_id']);
        $this->ListingTable->setColumnType('dsi_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DashboardItemDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $orders[] = 'sty.sty_id ASC';
        $orders[] = 'dsi.dsi_order ASC';
        return DashboardItemDao::loadData(
            $this->getWhereCondition(),
            $orders,
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
        if ($this->isValidParameter('dsi_title') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dsi_title', $this->getStringParameter('dsi_title'));
        }
        if ($this->isValidParameter('dsi_route') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dsi_route', $this->getStringParameter('dsi_route'));
        }
        if ($this->isValidParameter('dsi_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dsi_active', $this->getStringParameter('dsi_active'));
        }
        if ($this->isValidParameter('dsi_module_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dsi_module_id', $this->getIntParameter('dsi_module_id'));
        }
        # return the where query.
        return $wheres;
    }
}
