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
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Finance\CostCodeDao;

/**
 * Class to handle the creation of detail CostCode page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */
class CostCode extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'costCode', 'cc_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'cc_ss_id' => $this->User->getSsId(),
            'cc_ccg_id' => $this->getIntParameter('cc_ccg_id'),
            'cc_code' => $this->getStringParameter('cc_code'),
            'cc_name' => $this->getStringParameter('cc_name'),
            'cc_active' => $this->getStringParameter('cc_active', 'Y')
        ];
        $costCodeDao = new CostCodeDao();
        $costCodeDao->doInsertTransaction($colVal);

        return $costCodeDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cc_ccg_id' => $this->getIntParameter('cc_ccg_id'),
            'cc_code' => $this->getStringParameter('cc_code'),
            'cc_name' => $this->getStringParameter('cc_name'),
            'cc_active' => $this->getStringParameter('cc_active', 'Y')
        ];
        $costCodeDao = new CostCodeDao();
        $costCodeDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CostCodeDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('cc_code', 1, 50);
        $this->Validation->checkRequire('cc_name', 2, 150);
        $this->Validation->checkRequire('cc_ccg_id');
        $this->Validation->checkUnique('cc_code', 'cost_code', [
            'cc_id' => $this->getDetailReferenceValue()
        ], [
            'cc_ss_id' => $this->User->getSsId(),
            'cc_ccg_id' => $this->getIntParameter('cc_ccg_id')
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Add field to field set
        $ccgField = $this->Field->getSingleSelect('costCodeGroup', 'cc_group_name', $this->getStringParameter('cc_group_name'));
        $ccgField->setHiddenField('cc_ccg_id', $this->getIntParameter('cc_ccg_id'));
        $ccgField->addParameter('ccg_ss_id', $this->User->getSsId());
        $ccgField->setDetailReferenceCode('ccg_id');

        $fieldSet->addField(Trans::getWord('group'), $ccgField, true);
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('cc_code', $this->getStringParameter('cc_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cc_name', $this->getStringParameter('cc_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cc_active', $this->getStringParameter('cc_active')));
        # Create a portlet box.
        $portlet = new Portlet('costCodeGeneralPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
