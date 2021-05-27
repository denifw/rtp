<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Fms;

use App\Frame\Formatter\Trans;
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\Fms\EquipmentUsageDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail EquipmentUsage page
 *
 * @package    app
 * @subpackage Model\Detail\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class EquipmentUsage extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'equipmentUsage', 'equ_id');
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
            'equ_ss_id' => $this->User->getSsId(),
            'equ_eq_id' => $this->getIntParameter('equ_eq_id'),
            'equ_date' => $this->getStringParameter('equ_date'),
            'equ_meter' => $this->getFloatParameter('equ_meter'),
            'equ_remark' => $this->getStringParameter('equ_remark'),
        ];
        $equDao = new EquipmentUsageDao();
        $equDao->doInsertTransaction($colVal);

        $colValMeter = [
            'eqm_eq_id' => $this->getIntParameter('equ_eq_id'),
            'eqm_date' => $this->getStringParameter('equ_date'),
            'eqm_meter' => $this->getFloatParameter('equ_meter'),
            'eqm_source' => Trans::getFmsWord('usage')
        ];

        $eqmDao = new EquipmentMeterDao();
        $eqmDao->doInsertTransaction($colValMeter);

        return $equDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'equ_eq_id' => $this->getIntParameter('equ_eq_id'),
            'equ_date' => $this->getStringParameter('equ_date'),
            'equ_meter' => $this->getFloatParameter('equ_meter'),
            'equ_remark' => $this->getStringParameter('equ_remark'),
        ];
        $equDao = new EquipmentUsageDao();

        $equDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EquipmentUsageDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('equ_eq_id');
        $this->Validation->checkRequire('equ_date');
        $this->Validation->checkFloat('equ_meter');
        $this->Validation->checkFloat('equ_fuel');
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
        $fieldSet->setGridDimension(6, 6);
        # Create field
        $eqField = $this->Field->getSingleSelect('equipment', 'equ_eq_name', $this->getStringParameter('equ_eq_name'), 'loadSingleSelectDataForFms');
        $eqField->setHiddenField('equ_eq_id', $this->getIntParameter('equ_eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('equipment'), $eqField, true);
        $fieldSet->addField(Trans::getFmsWord('usageDate'), $this->Field->getCalendar('equ_date', $this->getStringParameter('equ_date')), true);
        $fieldSet->addField(Trans::getFmsWord('meter'), $this->Field->getNumber('equ_meter', $this->getFloatParameter('equ_meter')), true);
        $fieldSet->addField(Trans::getFmsWord('remark'), $this->Field->getTextArea('equ_remark', $this->getStringParameter('equ_remark')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 12, 12);

        return $portlet;
    }


}
