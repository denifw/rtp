<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Finance\PaymentMethodDao;
use App\Model\Dao\Master\Finance\PaymentTermsDao;

/**
 * Class to manage the creation of the listing CostCode page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */
class PaymentTerms extends AbstractListingModel
{

    /**
     * CostCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('pt_name', $this->getStringParameter('pt_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('pt_active', $this->getStringParameter('pt_active')));
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
                'pt_name' => Trans::getWord('description'),
                'pt_days' => Trans::getWord('days'),
                'pt_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for CostCode.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('pt_active', 'yesno');
        $this->ListingTable->setColumnType('pt_days', 'integer');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pt_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PaymentTermsDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return PaymentTermsDao::loadData($this->getWhereCondition());
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
        $helper->addStringWhere('pt_ss_id', $this->User->getSsId());
        $helper->addLikeWhere('pt_name', $this->getStringParameter('pt_name'));
        $helper->addStringWhere('pt_active', $this->getStringParameter('pt_active'));
        return $helper;
    }
}
