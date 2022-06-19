<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\RelationDao;

/**
 * Class to control the system of Relation.
 *
 * @package    app
 * @subpackage Model\Listing\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Relation extends AbstractListingModel
{

    /**
     * Relation constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'rel');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('rel_number', $this->getStringParameter('rel_number')));
        $this->ListingForm->addField(Trans::getWord('shortName'), $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('rel_active', $this->getStringParameter('rel_active')));
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
            'rel_name' => Trans::getWord('name'),
            'rel_short_name' => Trans::getWord('shortName'),
            'rel_email' => Trans::getWord('email'),
            'rel_phone' => Trans::getWord('phone'),
            'rel_pic' => Trans::getWord('pic'),
            'rel_active' => Trans::getWord('active'),
        ]);
        # Load the data for Relation.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['rel_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['rel_id']);
        }
        $this->ListingTable->setColumnType('rel_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return RelationDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return RelationDao::loadData($this->getWhereCondition());
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
        $helper->addStringWhere('rel.rel_ss_id', $this->User->getSsId());
        $helper->addLikeWhere('rel.rel_number', $this->getStringParameter('rel_number'));
        $helper->addLikeWhere('rel.rel_name', $this->getStringParameter('rel_name'));
        $helper->addLikeWhere('rel.rel_short_name', $this->getStringParameter('rel_short_name'));
        $helper->addStringWhere('rel.rel_active', $this->getStringParameter('rel_active'));

        # return the where query.
        return $helper;
    }
}
