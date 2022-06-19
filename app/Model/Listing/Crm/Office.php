<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\Crm;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\OfficeDao;

/**
 * Class to control the system of Office.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */
class Office extends AbstractListingModel
{

    /**
     * Office constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'of');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relField = $this->Field->getSingleSelect('rel', 'relation', $this->getStringParameter('relation'));
        $relField->setHiddenField('of_rel_id', $this->getStringParameter('of_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('relation'), $relField);
        $this->ListingForm->addField(Trans::getWord('officeName'), $this->Field->getText('of_name', $this->getStringParameter('of_name')));
        $this->ListingForm->addField(Trans::getWord('invoiceOffice'), $this->Field->getYesNo('of_invoice', $this->getStringParameter('of_invoice')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('of_active', $this->getStringParameter('of_active')));
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
            'of_relation' => Trans::getWord('relation'),
            'of_name' => Trans::getWord('name'),
            'of_full_address' => Trans::getWord('address'),
            'of_invoice' => Trans::getWord('invoiceOffice'),
            'of_active' => Trans::getWord('active'),
        ]);
        # Load the data for Office.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('of_invoice', 'yesno');
        $this->ListingTable->setColumnType('of_active', 'yesno');
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['of_id']);
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['of_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return OfficeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = OfficeDao::loadData($this->getWhereCondition());
        $results = [];
        foreach ($data as $row) {
            $row['of_full_address'] = DataParser::doFormatAddress($row, 'of');
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return SqlHelper
     */
    private function getWhereCondition(): SqlHelper
    {
        # Set where conditions
        $helper = new SqlHelper();
        $helper->setLimit($this->getLimitTable(), $this->getLimitOffsetTable());
        $helper->addOrderByString($this->ListingSort->getOrderByFieldsString());

        # Check the filter value here.
        $helper->addStringWhere('rel.rel_ss_id', $this->User->getSsId());
        $helper->addStringWhere('ofc.of_rel_id', $this->getStringParameter('of_rel_id'));
        $helper->addLikeWhere('ofc.of_name', $this->getStringParameter('of_name'));
        $helper->addStringWhere('ofc.of_invoice', $this->getStringParameter('of_invoice'));
        $helper->addStringWhere('ofc.of_active', $this->getStringParameter('of_active'));

        # return the list where condition.
        return $helper;
    }
}
