<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System\Location;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Location\PortDao;

/**
 * Class to manage the creation of the listing Port page.
 *
 * @package    app
 * @subpackage Model\Listing\System\Location
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Port extends AbstractListingModel
{

    /**
     * Port constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'port');
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
        $tmField = $this->Field->getSingleSelect('transportModule', 'po_module', $this->getStringParameter('po_module'));
        $tmField->setHiddenField('po_tm_id', $this->getIntParameter('po_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setEnableDetailButton(false);
        $countryField = $this->Field->getSingleSelect('country', 'po_country', $this->getStringParameter('po_country'));
        $countryField->setHiddenField('po_cnt_id', $this->getIntParameter('po_cnt_id'));
        $countryField->setEnableDetailButton(false);
        $countryField->setEnableNewButton(false);
        # Add field to form.
        $this->ListingForm->addField(Trans::getWord('portName'), $this->Field->getText('po_name', $this->getStringParameter('po_name')));
        $this->ListingForm->addField(Trans::getWord('portCode'), $this->Field->getText('po_code', $this->getStringParameter('po_code')));
        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('module'), $tmField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('po_active', $this->getStringParameter('po_active')));
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
        $this->ListingTable->setHeaderRow(
            [
                'po_name' => Trans::getWord('portName'),
                'po_code' => Trans::getWord('portCode'),
                'po_module' => Trans::getWord('module'),
                'po_country' => Trans::getWord('country'),
                'po_city' => Trans::getWord('city'),
                'po_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Port.
        $this->ListingTable->addRows($this->loadData());
        # Add special settings to the table
        $this->ListingTable->setColumnType('po_active', 'yesno');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['po_id'], true);
        }

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PortDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return PortDao::loadData(
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
        if ($this->isValidParameter('po_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('po.po_name', $this->getStringParameter('po_name'));
        }
        if ($this->isValidParameter('po_code')) {
            $wheres[] = SqlHelper::generateLikeCondition('po.po_code', $this->getStringParameter('po_code'));
        }
        if ($this->isValidParameter('po_cnt_id')) {
            $wheres[] = '(po.po_cnt_id = ' . $this->getIntParameter('po_cnt_id') . ')';
        }
        if ($this->isValidParameter('po_tm_id')) {
            $wheres[] = '(po.po_tm_id = ' . $this->getIntParameter('po_tm_id') . ')';
        }
        if ($this->isValidParameter('po_active')) {
            $wheres[] = "(po.po_active = '" . $this->getStringParameter('po_active') . "')";
        }
        return $wheres;
    }
}
