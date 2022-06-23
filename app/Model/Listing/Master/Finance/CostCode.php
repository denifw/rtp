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
use App\Model\Dao\Master\Finance\CostCodeDao;

/**
 * Class to manage the creation of the listing CostCode page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class CostCode extends AbstractListingModel
{

    /**
     * CostCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cc');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Cost Code Group
        $ccgField = $this->Field->getSingleSelect('ccg', 'cc_group', $this->getStringParameter('cc_group'));
        $ccgField->setHiddenField('cc_ccg_id', $this->getStringParameter('cc_ccg_id'));
        $ccgField->setEnableDetailButton(false);
        $ccgField->addParameter('ccg_ss_id', $this->User->getSsId());
        $ccgField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('group'), $ccgField);
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('cc_code', $this->getStringParameter('cc_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('cc_name', $this->getStringParameter('cc_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cc_active', $this->getStringParameter('cc_active')));
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
                'cc_group' => Trans::getWord('group'),
                'cc_code' => Trans::getWord('code'),
                'cc_name' => Trans::getWord('name'),
                'cc_type_name' => Trans::getWord('type'),
                'cc_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for CostCode.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('cc_active', 'yesno');
        $this->ListingTable->addColumnAttribute('cc_code', 'style', 'text-align: center;');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cc_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CostCodeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = CostCodeDao::loadData($this->getWhereCondition());
        $results = [];
        foreach ($data as $row) {
            $row['cc_group'] = $row['cc_group_code'] . ' - ' . $row['cc_group_name'];
            $row['cc_type_name'] = Trans::getWord($row['cc_type_name']);
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
        $helper = new SqlHelper();
        $helper->setLimit($this->getLimitTable(), $this->getLimitOffsetTable());
        $helper->addOrderByString($this->ListingSort->getOrderByFieldsString());
        # Set where conditions
        $helper->addStringWhere('ccg_ss_id', $this->User->getSsId());
        $helper->addStringWhere('cc_ccg_id', $this->getStringParameter('cc_ccg_id'));
        $helper->addLikeWhere('cc_name', $this->getStringParameter('cc_name'));
        $helper->addLikeWhere('cc_code', $this->getStringParameter('cc_code'));
        $helper->addStringWhere('cc_active', $this->getStringParameter('cc_active'));
        return $helper;
    }
}
