<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Finance\TaxDao;

/**
 * Class to manage the creation of the listing Tax page.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Tax extends AbstractListingModel
{

    /**
     * Tax constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'tax');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('tax_name', $this->getStringParameter('tax_name')));
        $this->ListingForm->addField(Trans::getWord('group'), $this->Field->getYesNo('tax_group', $this->getStringParameter('tax_group')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('tax_active', $this->getStringParameter('tax_active')));
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
                'tax_name' => Trans::getWord('description'),
                'tax_group' => Trans::getWord('group'),
                'tax_percent' => Trans::getWord('percentage'),
                'tax_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Tax.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('tax_group', 'yesno');
        $this->ListingTable->setColumnType('tax_active', 'yesno');
        $this->ListingTable->setColumnType('tax_percent', 'currency');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['tax_id'], false);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return TaxDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return TaxDao::loadData($this->getWhereCondition());
    }

    /**
     * Function to get the where condition.
     *
     * @return SqlHelper
     */
    private function getWhereCondition(): SqlHelper
    {
        $helper = new SqlHelper();
        $helper->setLimit($this->getLimitTable(), $this->getLimitOffsetTable());
        $helper->addOrderByString($this->ListingSort->getOrderByFieldsString());
        # Set where conditions
        $helper->addStringWhere('tax_ss_id', $this->User->getSsId());
        $helper->addLikeWhere('tax_name', $this->getStringParameter('tax_name'));
        $helper->addStringWhere('tax_group', $this->getStringParameter('tax_group'));
        $helper->addStringWhere('tax_active', $this->getStringParameter('tax_active'));
        return $helper;
    }
}
