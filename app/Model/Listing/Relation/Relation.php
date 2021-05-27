<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Relation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Relation\RelationDao;

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
        parent::__construct(get_class($this), 'relation');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $industryField = $this->Field->getSingleSelect('ids', 'rel_ids_name', $this->getStringParameter('rel_ids_name'));
        $industryField->setHiddenField('rel_ids_id', $this->getIntParameter('rel_ids_id'));
        $industryField->addParameter('ids_ss_id', $this->User->getSsId());
        $industryField->setEnableNewButton(false);
        $industryField->setEnableDetailButton(false);
        $sourceField = $this->Field->getSingleSelect('sty', 'rel_source_name', $this->getStringParameter('rel_source_name'));
        $sourceField->setHiddenField('rel_source_id', $this->getIntParameter('rel_source_id'));
        $sourceField->addParameter('sty_group', 'relationsource');
        $sourceField->setEnableNewButton(false);
        $sourceField->setEnableDetailButton(false);
        $typeField = $this->Field->getSingleSelect('sty', 'rel_type_name', $this->getStringParameter('rel_type_name'));
        $typeField->setHiddenField('rel_type_id', $this->getIntParameter('rel_type_id'));
        $typeField->addParameter('sty_group', 'relationtype');
        $typeField->setEnableNewButton(false);
        $typeField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getCrmWord('number'), $this->Field->getText('rel_number', $this->getStringParameter('rel_number')));
        $this->ListingForm->addField(Trans::getCrmWord('name'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')));
        $this->ListingForm->addField(Trans::getCrmWord('shortName'), $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name')));
        $this->ListingForm->addField(Trans::getCrmWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getCrmWord('industry'), $industryField);
        $this->ListingForm->addField(Trans::getCrmWord('source'), $sourceField);
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
            'rel_name' => Trans::getCrmWord('name'),
            'rel_short_name' => Trans::getCrmWord('shortName'),
            'rel_main_contact_name' => Trans::getCrmWord('mainContact'),
            'rel_ids_name' => Trans::getCrmWord('industry'),
            'rel_source_name' => Trans::getCrmWord('source'),
            'rel_email' => Trans::getCrmWord('email'),
            'rel_phone' => Trans::getCrmWord('phone'),
            'rel_active' => Trans::getCrmWord('active'),
        ]);
        # Load the data for Relation.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['rel_id']);
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
        return RelationDao::loadData(
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
        $wheres[] = '(rel_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('rel_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rel_number', $this->getStringParameter('rel_number'));
        }
        if ($this->isValidParameter('rel_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rel_name', $this->getStringParameter('rel_name'));
        }
        if ($this->isValidParameter('rel_short_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rel_short_name', $this->getStringParameter('rel_short_name'));
        }
        if ($this->isValidParameter('rel_type_id')) {
            $wheres[] = '(rel.rel_id IN (SELECT rty_rel_id FROM relation_type WHERE rty_sty_id = ' . $this->getIntParameter('rel_type_id') . '))';
        }
        if ($this->isValidParameter('rel_ids_id')) {
            $wheres[] = SqlHelper::generateNumericCondition('rel_ids_id', $this->getIntParameter('rel_ids_id'));
        }
        if ($this->isValidParameter('rel_source_id')) {
            $wheres[] = SqlHelper::generateNumericCondition('rel_source_id', $this->getIntParameter('rel_source_id'));
        }

        # return the where query.
        return $wheres;
    }
}
