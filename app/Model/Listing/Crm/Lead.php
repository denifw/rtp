<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\LeadDao;

/**
 * Class to control the system of Lead.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Lead extends AbstractListingModel
{

    /**
     * Lead constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'lead');
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
        $statusField = $this->Field->getSingleSelect('sty', 'ld_sty_name', $this->getStringParameter('ld_sty_name'));
        $statusField->setHiddenField('ld_sty_id', $this->getIntParameter('ld_sty_id'));
        $statusField->addParameter('sty_group', 'leadstatus');
        $statusField->setEnableNewButton(false);
        $statusField->setEnableDetailButton(false);
        $typeField = $this->Field->getSingleSelect('sty', 'rel_type_name', $this->getStringParameter('rel_type_name'));
        $typeField->setHiddenField('rel_type_id', $this->getIntParameter('rel_type_id'));
        $typeField->addParameter('sty_group', 'relationtype');
        $typeField->setEnableNewButton(false);
        $typeField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getCrmWord('number'), $this->Field->getText('ld_number', $this->getStringParameter('ld_number')));
        $this->ListingForm->addField(Trans::getCrmWord('name'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')));
        $this->ListingForm->addField(Trans::getCrmWord('shortName'), $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name')));
        $this->ListingForm->addField(Trans::getCrmWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getCrmWord('industry'), $industryField);
        $this->ListingForm->addField(Trans::getCrmWord('source'), $sourceField);
        $this->ListingForm->addField(Trans::getCrmWord('leadStatus'), $statusField);
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
            'ld_number' => Trans::getCrmWord('number'),
            'rel_name' => Trans::getCrmWord('name'),
            'rel_short_name' => Trans::getCrmWord('shortName'),
            'rel_main_contact_name' => Trans::getCrmWord('mainContact'),
            'rel_ids_name' => Trans::getCrmWord('industry'),
            'rel_source_name' => Trans::getCrmWord('source'),
            'rel_email' => Trans::getCrmWord('email'),
            'rel_phone' => Trans::getCrmWord('phone'),
            'ld_sty_name' => Trans::getCrmWord('leadStatus'),
        ]);
        # Load the data for Lead.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['ld_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ld_id']);
        }
        $this->ListingTable->addColumnAttribute('ld_sty_name', 'style', 'text-align:center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return LeadDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = LeadDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        return $this->doPrepareData($data);
    }

    /**
     * Function do prepare data.
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            if (empty($row['ld_sty_label_type']) === false) {
                $row['ld_sty_name'] = '<span class="' . $row['ld_sty_label_type'] . '">' . $row['ld_sty_name'] . '</span>';
            }
            $results[] = $row;
        }

        return $results;
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
        $wheres[] = '(ld_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('ld_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ld_number', $this->getStringParameter('ld_number'));
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
        if ($this->isValidParameter('ld_sty_id')) {
            $wheres[] = SqlHelper::generateNumericCondition('ld_sty_id', $this->getIntParameter('ld_sty_id'));
        }

        # return the where query.
        return $wheres;
    }
}
