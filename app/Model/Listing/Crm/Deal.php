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
use App\Model\Dao\Crm\DealDao;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to control the system of Deal.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Deal extends AbstractListingModel
{

    /**
     * Deal constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'deal');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relationField = $this->Field->getSingleSelect('relation', 'dl_rel_name', $this->getStringParameter('dl_rel_name'));
        $relationField->setHiddenField('dl_rel_id', $this->getIntParameter('dl_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $relationField->setEnableDetailButton(false);
        $sourceField = $this->Field->getSingleSelect('sty', 'dl_source_name', $this->getStringParameter('dl_source_name'));
        $sourceField->setHiddenField('dl_source_id', $this->getIntParameter('dl_source_id'));
        $sourceField->addParameter('sty_group', 'relationsource');
        $sourceField->setEnableNewButton(false);
        $sourceField->setEnableDetailButton(false);
        $stageField = $this->Field->getSingleSelect('sty', 'dl_stage_name', $this->getStringParameter('dl_stage_name'));
        $stageField->setHiddenField('dl_stage_id', $this->getIntParameter('dl_stage_id'));
        $stageField->addParameter('sty_group', 'salesstage');
        $stageField->setEnableNewButton(false);
        $stageField->setEnableDetailButton(false);
        $wheres[] = '(sty.sty_group = \'relationtype\')';
        $wheres[] = '(sty.sty_name IN (\'Customer\', \'Vendor\'))';
        $styData = SystemTypeDao::loadData($wheres);
        $dealTypeField = $this->Field->getRadioGroup('dl_sty_id', $this->getIntParameter('dl_sty_id'));
        foreach ($styData as $data) {
            $dealTypeField->addRadio($data['sty_name'], $data['sty_id']);
        }
        $this->ListingForm->addField(Trans::getCrmWord('number'), $this->Field->getText('dl_number', $this->getStringParameter('dl_number')));
        $this->ListingForm->addField(Trans::getCrmWord('dealName'), $this->Field->getText('dl_name', $this->getStringParameter('dl_name')));
        $this->ListingForm->addField(Trans::getCrmWord('relation'), $relationField);
        $this->ListingForm->addField(Trans::getCrmWord('source'), $sourceField);
        $this->ListingForm->addField(Trans::getCrmWord('salesStage'), $stageField);
        $this->ListingForm->addField(Trans::getCrmWord('type'), $dealTypeField);

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
            'dl_number' => Trans::getCrmWord('number'),
            'dl_name' => Trans::getCrmWord('deal'),
            'dl_rel_name' => Trans::getCrmWord('relation'),
            'dl_sty_name' => Trans::getCrmWord('type'),
            'dl_amount' => Trans::getCrmWord('amount'),
            'dl_close_date' => Trans::getCrmWord('expectedCloseDate'),
            'dl_stage_name' => Trans::getCrmWord('salesStage'),
        ]);
        # Load the data for Deal.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['dl_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dl_id']);
        }
        $this->ListingTable->setColumnType('dl_amount', 'currency');
        $this->ListingTable->setColumnType('dl_close_date', 'date');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DealDao::loadTotalData($this->getWhereCondition());
    }

    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DealDao::loadData(
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
        $wheres[] = '(dl.dl_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('dl_number') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dl_number', $this->getStringParameter('dl_number'));
        }
        if ($this->isValidParameter('dl_name') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dl_name', $this->getStringParameter('dl_name'));
        }
        if ($this->isValidParameter('dl_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dl_rel_id', $this->getIntParameter('dl_rel_id'));
        }
        if ($this->isValidParameter('dl_source_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dl_source_id', $this->getIntParameter('dl_source_id'));
        }
        if ($this->isValidParameter('dl_stage_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dl_stage_id', $this->getIntParameter('dl_stage_id'));
        }
        if ($this->isValidParameter('dl_sty_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('dl_sty_id', $this->getIntParameter('dl_sty_id'));
        }
        # return the list where condition.
        return $wheres;
    }
}
