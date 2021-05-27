<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;

/**
 * Class to control the system of SalesOrderDelivery.
 *
 * @package    app
 * @subpackage Model\Listing\CustomerServicec
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderDelivery extends AbstractListingModel
{

    /**
     * SalesOrderDelivery constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sdl');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'so_customer', $this->getStringParameter('so_customer'));
        $relField->setHiddenField('so_rel_id', $this->getIntParameter('so_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $relField->addParameter('rel_id', $this->User->getRelId());
        }
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('soNumber'), $this->Field->getText('so_number', $this->getStringParameter('so_number')));
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('so_reference', $this->getStringParameter('so_reference')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('consolidate'), $this->Field->getYesNo('so_consolidate', $this->getStringParameter('so_consolidate')));
        $this->ListingForm->addField(Trans::getWord('container'), $this->Field->getYesNo('so_container', $this->getStringParameter('so_container')));
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
        $this->ListingTable->setHeaderRow([
            'so_number' => Trans::getWord('soNumber'),
            'so_customer' => Trans::getWord('customer'),
            'sdl_service_term' => Trans::getWord('serviceTerm'),
        ]);
        # Load the data for SalesOrderDelivery.
        $this->ListingTable->addRows($this->loadData());
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['sdl_id']);
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sdl_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SalesOrderDeliveryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return SalesOrderDeliveryDao::loadData(
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
        $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNullCondition('so.so_finish_on');
        $wheres[] = SqlHelper::generateNullCondition('so.so_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('sdl.sdl_deleted_on');
        # So Number
        if ($this->isValidParameter('so_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('so_number'));
        }
        # so reference filter
        if ($this->isValidParameter('so_reference') === true) {
            $orWheres = [];
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_bl_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_packing_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_aju_ref', $this->getStringParameter('so_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('so.so_sppb_ref', $this->getStringParameter('so_reference'));
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        # Relation
        if ($this->isValidParameter('so_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('so.so_rel_id', $this->getIntParameter('so_rel_id'));
        }
        if ($this->isValidParameter('so_consolidate') === true) {
            $wheres[] = SqlHelper::generateStringCondition('so.so_consolidate', $this->getIntParameter('so_consolidate'));
        }
        if ($this->isValidParameter('so_container') === true) {
            $wheres[] = SqlHelper::generateStringCondition('so.so_container', $this->getIntParameter('so_container'));
        }

        # return the list where condition.
        return $wheres;
    }
}
