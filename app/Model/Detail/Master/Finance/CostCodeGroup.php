<?php

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Master\Finance;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Model\Dao\Master\Finance\CostCodeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Finance\CostCodeGroupDao;

/**
 * Class to handle the creation of detail CostCode page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */
class CostCodeGroup extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'ccg', 'ccg_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'ccg_ss_id' => $this->User->getSsId(),
            'ccg_code' => $this->getStringParameter('ccg_code'),
            'ccg_name' => $this->getStringParameter('ccg_name'),
            'ccg_type' => $this->getStringParameter('ccg_type'),
            'ccg_active' => 'Y',
        ];
        $ccgDao = new CostCodeGroupDao();
        $ccgDao->doInsertTransaction($colVal);

        return $ccgDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'ccg_code' => $this->getStringParameter('ccg_code'),
            'ccg_name' => $this->getStringParameter('ccg_name'),
            'ccg_type' => $this->getStringParameter('ccg_type'),
            'ccg_active' => $this->getStringParameter('ccg_active'),
        ];
        $ccgDao = new CostCodeGroupDao();
        $ccgDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CostCodeGroupDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->Tab->addPortlet('general', $this->getCostCodeFieldset());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('ccg_code', 1, 50);
        $this->Validation->checkRequire('ccg_name', 2, 150);
        $this->Validation->checkRequire('ccg_type');
        $this->Validation->checkUnique('ccg_code', 'cost_code_group', [
            'ccg_id' => $this->getDetailReferenceValue()
        ], [
            'ccg_ss_id' => $this->User->getSsId(),
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        #Type
        $typeField = $this->Field->getSelect('ccg_type', $this->getStringParameter('ccg_type'));
        $typeField->addOption(Trans::getWord('sales'), 'S');
        $typeField->addOption(Trans::getWord('purchase'), 'P');
        $typeField->addOption(Trans::getWord('reimburse'), 'R');
        $typeField->addOption(Trans::getWord('deposit'), 'D');


        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('ccg_code', $this->getStringParameter('ccg_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('ccg_name', $this->getStringParameter('ccg_name')), true);
        $fieldSet->addField(Trans::getWord('type'), $typeField, true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ccg_active', $this->getStringParameter('ccg_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('costCodeGeneralPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }

    /*
     * Function to get service fieldset.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getCostCodeFieldset(): Portlet
    {
        $table = new Table('ServiceTbl');
        $table->setHeaderRow(
            [
                'cc_code' => Trans::getWord('code'),
                'cc_name' => Trans::getWord('name'),
                'cc_active' => Trans::getWord('active'),
            ]
        );
        # Load data from dao
        $table->addRows(CostCodeDao::getByGroupId($this->getDetailReferenceValue()));
        $table->setUpdateActionByHyperlink('/costCode/detail', ['cc_id']);
        $table->setColumnType('cc_active', 'yesno');
        $table->addColumnAttribute('cc_code', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('CcPtl', Trans::getWord('costCode'));
        $portlet->addTable($table);
        return $portlet;
    }
}
