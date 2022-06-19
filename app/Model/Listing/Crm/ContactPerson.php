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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\ContactPersonDao;

/**
 * Class to control the system of Office.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */
class ContactPerson extends AbstractListingModel
{

    /**
     * Office constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cp');
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
        $relField->setHiddenField('cp_rel_id', $this->getStringParameter('cp_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $ofField = $this->Field->getSingleSelect('rel', 'office', $this->getStringParameter('office'));
        $ofField->setHiddenField('cp_of_id', $this->getStringParameter('cp_of_id'));
        $ofField->addParameterById('of_rel_id', 'cp_rel_id', Trans::getWord('relation'));
        $ofField->setEnableDetailButton(false);
        $ofField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('relation'), $relField);
        $this->ListingForm->addField(Trans::getWord('office'), $ofField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getStringParameter('cp_active')));
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
            'cp_name' => Trans::getWord('name'),
            'cp_relation' => Trans::getWord('relation'),
            'cp_office' => Trans::getWord('office'),
            'cp_phone' => Trans::getWord('phone'),
            'cp_email' => Trans::getWord('email'),
            'cp_active' => Trans::getWord('active'),
        ]);
        # Load the data for Office.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('cp_active', 'yesno');
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['of_id']);
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cp_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return ContactPersonDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = ContactPersonDao::loadData($this->getWhereCondition());
        $results = [];
        foreach ($data as $row) {
//            $row['of_full_address'] = DataParser::doFormatAddress($row, 'of');
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
        $helper->addStringWhere('rel.rel_id', $this->getStringParameter('cp_rel_id'));
        $helper->addStringWhere('cp.cp_of_id', $this->getStringParameter('cp_of_id'));
        $helper->addLikeWhere('cp.cp_name', $this->getStringParameter('of_name'));
        $helper->addStringWhere('cp.cp_active', $this->getStringParameter('cp_active'));

        # return the list where condition.
        return $helper;
    }
}
