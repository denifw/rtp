<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\EquipmentGroupDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail EquipmentGroup page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class EquipmentGroup extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'eg', 'eg_id');
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
            'eg_name' => $this->getStringParameter('eg_name'),
            'eg_code' => $this->getStringParameter('eg_code'),
            'eg_tm_id' => $this->getIntParameter('eg_tm_id'),
            'eg_sty_id' => $this->getIntParameter('eg_sty_id'),
            'eg_container' => $this->getStringParameter('eg_container', 'N'),
            'eg_active' => $this->getStringParameter('eg_active', 'Y'),
        ];
        $egDao = new EquipmentGroupDao();
        $egDao->doInsertTransaction($colVal);
        return $egDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $colVal = [
                'eg_code' => $this->getStringParameter('eg_code'),
                'eg_name' => $this->getStringParameter('eg_name'),
                'eg_tm_id' => $this->getIntParameter('eg_tm_id'),
                'eg_sty_id' => $this->getIntParameter('eg_sty_id'),
                'eg_container' => $this->getStringParameter('eg_container', 'N'),
                'eg_active' => $this->getStringParameter('eg_active'),
            ];
            $egDao = new EquipmentGroupDao();
            $egDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction()) {
            $colVal = [
                'eg_deleted_on' => date('Y-m-d H:i:s'),
                'eg_deleted_by' => $this->User->getId(),
                'eg_deleted_reason' => $this->getReasonDeleteAction()
            ];
            $delDao = new EquipmentGroupDao();
            $delDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EquipmentGroupDao::getByReference($this->getDetailReferenceValue());
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
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('eg_name', 2, 255);
            $this->Validation->checkRequire('eg_code', 2, 25);
            $this->Validation->checkUnique('eg_code', 'equipment_group', [
                'eg_id' => $this->getDetailReferenceValue()
            ]);
            $this->Validation->checkRequire('eg_tm_id');
            $this->Validation->checkRequire('eg_tm_code');
            $this->Validation->checkRequire('eg_container');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('EgGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);

        # Initiate Field Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Fields.
        # Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'eg_module', $this->getStringParameter('eg_module'));
        $tmField->setHiddenField('eg_tm_id', $this->getIntParameter('eg_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setEnableDetailButton(false);
        $tmField->setAutoCompleteFields([
            'eg_tm_code' => 'tm_code'
        ]);

        # System Type
        $styField = $this->Field->getSingleSelect('sty', 'eg_sty_name', $this->getStringParameter('eg_sty_name'));
        $styField->setHiddenField('eg_sty_id', $this->getIntParameter('eg_sty_id'));
        $styField->addParameter('sty_group', 'vehicleclass');
        $styField->setEnableNewButton(false);
        $styField->setEnableDetailButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('eg_code', $this->getStringParameter('eg_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('eg_name', $this->getStringParameter('eg_name')), true);
        $fieldSet->addField(Trans::getWord('module'), $tmField, true);
        $fieldSet->addField(Trans::getWord('class'), $styField);
        $fieldSet->addField(Trans::getWord('container'), $this->Field->getYesNo('eg_container', $this->getStringParameter('eg_container')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('eg_active', $this->getStringParameter('eg_active')));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('eg_tm_code', $this->getStringParameter('eg_tm_code')));

        # Create a portlet box.
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
