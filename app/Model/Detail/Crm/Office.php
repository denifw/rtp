<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Crm;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\OfficeDao;
use App\Model\Dao\Crm\RelationDao;

/**
 * Class to handle the creation of detail Office page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Office extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'of', 'of_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
//        $mainOffice = 'N';
//        if (OfficeDao::isRelationHasMainOffice($this->getIntParameter('of_rel_id')) === false) {
//            $mainOffice = 'Y';
//        }
//        $districtData = DistrictDao::getByReference($this->getIntParameter('of_dtc_id'));
//        $colVal = [
//            'of_rel_id' => $this->getIntParameter('of_rel_id'),
//            'of_name' => $this->getStringParameter('of_name'),
//            'of_main' => $mainOffice,
//            'of_invoice' => $this->getStringParameter('of_invoice', 'N'),
//            'of_address' => $this->getStringParameter('of_address'),
//            'of_cnt_id' => $districtData['dtc_cnt_id'],
//            'of_stt_id' => $districtData['dtc_stt_id'],
//            'of_cty_id' => $districtData['dtc_cty_id'],
//            'of_dtc_id' => $this->getIntParameter('of_dtc_id'),
//            'of_postal_code' => $this->getStringParameter('of_postal_code'),
//            'of_longitude' => $this->getFloatParameter('of_longitude'),
//            'of_latitude' => $this->getFloatParameter('of_latitude'),
//            'of_active' => $this->getStringParameter('of_active', 'Y'),
//        ];
        $ofDao = new OfficeDao();
//        $ofDao->doInsertTransaction($colVal);

        return $ofDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateContact') {
            $sn = new SerialNumber($this->User->getSsId());
            $cpNumber = $sn->loadNumber('ContactPerson', $this->getDetailReferenceValue(), $this->getIntParameter('of_rel_id'));
            $cpColVal = [
                'cp_number' => $cpNumber,
                'cp_of_id' => $this->getDetailReferenceValue(),
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_office_manager' => $this->getStringParameter('cp_office_manager', 'N'),
                'cp_active' => $this->getStringParameter('cp_active', 'Y'),
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doInsertTransaction($cpColVal);
        } else {
            $districtData = DistrictDao::getByReference($this->getIntParameter('of_dtc_id'));
            $colVal = [
                'of_rel_id' => $this->getIntParameter('of_rel_id'),
                'of_name' => $this->getStringParameter('of_name'),
                'of_invoice' => $this->getStringParameter('of_invoice'),
                'of_address' => $this->getStringParameter('of_address'),
                'of_cnt_id' => $districtData['dtc_cnt_id'],
                'of_stt_id' => $districtData['dtc_stt_id'],
                'of_cty_id' => $districtData['dtc_cty_id'],
                'of_dtc_id' => $this->getIntParameter('of_dtc_id'),
                'of_postal_code' => $this->getStringParameter('of_postal_code'),
                'of_longitude' => $this->getFloatParameter('of_longitude'),
                'of_latitude' => $this->getFloatParameter('of_latitude'),
                'of_active' => $this->getStringParameter('of_active'),
            ];
            $ofDao = new OfficeDao();
            $ofDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return OfficeDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('of_rel_id') === true) {
                $relation = RelationDao::getByReference($this->getStringParameter('of_rel_id'));
                if (empty($relation) === false) {
                    $this->setParameter('of_relation', $relation['rel_name']);
                } else {
                    $this->setParameter('of_rel_id', '');
                }
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('contactPerson', $this->getContactFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateContact') {
            $this->Validation->checkRequire('cp_name', 2, 125);
            if ($this->isValidParameter('cp_email') === true) {
                $this->Validation->checkMaxLength('cp_email', 125);
                $this->Validation->checkEmail('cp_email');
            }
            $this->Validation->checkMaxLength('cp_phone', 25);
        } else {
            $this->Validation->checkRequire('of_rel_id');
            $this->Validation->checkRequire('of_dtc_id');
            $this->Validation->checkRequire('of_name', 2, 125);
            $this->Validation->checkRequire('of_address', 2, 255);
            if ($this->isValidParameter('of_longitude') === true) {
                $this->Validation->checkFloat('of_longitude');
            }
            if ($this->isValidParameter('of_latitude') === true) {
                $this->Validation->checkFloat('of_latitude');
            }
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'of_relation', $this->getStringParameter('of_relation'));
        $relField->setHiddenField('of_rel_id', $this->getIntParameter('of_rel_id'));
        $relField->setDetailReferenceCode('rel_id');
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->isInsert() === true && $this->isValidParameter('of_rel_id') === true) {
            $relField->setReadOnly();
        }
        # Create custom field.
        $districtField = $this->Field->getSingleSelect('district', 'of_full_district', $this->getStringParameter('of_full_district'), 'loadCompleteSingleSelectData');
        $districtField->setHiddenField('of_dtc_id', $this->getIntParameter('of_dtc_id'));
        $districtField->setDetailReferenceCode('dtc_id');
        $districtField->setEnableDetailButton(false);
        $districtField->setEnableNewButton(false);
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('of_name', $this->getStringParameter('of_name')), true);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('of_address', $this->getStringParameter('of_address')), true);
        $fieldSet->addField(Trans::getWord('city') . '/' . Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('postalCode'), $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code')));
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_longitude', $this->getFloatParameter('of_longitude')));
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_latitude', $this->getFloatParameter('of_latitude')));
        $fieldSet->addField(Trans::getWord('invoiceOffice'), $this->Field->getYesNo('of_invoice', $this->getStringParameter('of_invoice')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('of_active', $this->getStringParameter('of_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('OfGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getContactFieldSet(): Portlet
    {
        $modal = $this->getContactModal();
        $this->View->addModal($modal);
        $table = new Table('RelCpTbl');
        $table->setHeaderRow([
            'cp_name' => Trans::getWord('name'),
            'cp_email' => Trans::getWord('email'),
            'cp_phone' => Trans::getWord('phone'),
            'cp_office_manager' => Trans::getWord('mainPic'),
            'cp_active' => Trans::getWord('active'),
        ]);
        $data = ContactPersonDao::getDataByOffice($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('cp_active', 'yesno');
        $table->setColumnType('cp_office_manager', 'yesno');
        $table->setUpdateActionByHyperlink('contactPerson/detail', ['cp_id'], true);
        # Create a portlet box.
        $portlet = new Portlet('RelCPPtl', Trans::getWord('contactPerson'));
        $btnCpMdl = new ModalButton('btnCpMdl', Trans::getWord('addContact'), $modal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getContactModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('RelCpMdl', Trans::getWord('contactPerson'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateContact');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateContact' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getParameterForModal('cp_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getParameterForModal('cp_email', $showModal)));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getParameterForModal('cp_phone', $showModal)));
        $fieldSet->addField(Trans::getWord('mainPic'), $this->Field->getYesNo('cp_office_manager', $this->getParameterForModal('cp_office_manager', $showModal)));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getParameterForModal('cp_active', $showModal)));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('cp_id', $this->getParameterForModal('cp_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
}
