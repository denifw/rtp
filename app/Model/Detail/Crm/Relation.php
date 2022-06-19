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

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\ContactPersonDao;
use App\Model\Dao\Crm\OfficeDao;
use App\Model\Dao\Crm\RelationBankDao;
use App\Model\Dao\Crm\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail Relation page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Relation extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'rel', 'rel_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $sn = new SerialNumber($this->User->getSsId());
        $relNumber = $sn->loadNumber('REL', $this->User->Relation->getOfficeId());
        $colVal = [
            'rel_ss_id' => $this->User->getSsId(),
            'rel_number' => $relNumber,
            'rel_name' => $this->getStringParameter('rel_name'),
            'rel_short_name' => $this->getStringParameter('rel_short_name'),
            'rel_website' => $this->getStringParameter('rel_website'),
            'rel_email' => $this->getStringParameter('rel_email'),
            'rel_phone' => $this->getStringParameter('rel_phone'),
            'rel_vat' => $this->getStringParameter('rel_vat'),
            'rel_remark' => $this->getStringParameter('rel_remark'),
            'rel_active' => 'Y',
        ];
        $relDao = new RelationDao();
        $relDao->doInsertTransaction($colVal);
        $relId = $relDao->getLastInsertId();
        # Insert Office
        $ofColVal = [
            'of_rel_id' => $relId,
            'of_name' => $this->getStringParameter('rel_short_name'),
            'of_invoice' => 'Y',
            'of_address' => $this->getStringParameter('of_address'),
            'of_dtc_id' => $this->getStringParameter('of_dtc_id'),
            'of_cty_id' => $this->getStringParameter('of_cty_id'),
            'of_stt_id' => $this->getStringParameter('of_stt_id'),
            'of_cnt_id' => $this->getStringParameter('of_cnt_id'),
            'of_postal_code' => $this->getStringParameter('of_postal_code'),
            'of_longitude' => $this->getFloatParameter('of_longitude'),
            'of_latitude' => $this->getFloatParameter('of_latitude'),
            'of_active' => 'Y',
        ];
        $ofDao = new OfficeDao();
        $ofDao->doInsertTransaction($ofColVal);
        $ofId = $ofDao->getLastInsertId();
        $cpId = null;
        if ($this->isValidParameter('cp_name') === true) {
            # Insert Contact Person
            $cpNumber = $sn->loadNumber('CP', $ofId);
            $cpColVal = [
                'cp_number' => $cpNumber,
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_of_id' => $ofId,
                'cp_active' => 'Y',
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doInsertTransaction($cpColVal);
            $cpId = $cpDao->getLastInsertId();

            # Update office pic
            $ofDao->doUpdateTransaction($ofId, [
                'of_cp_id' => $cpId
            ]);
        }
        # Update Relation Data
        $relDao->doUpdateTransaction($relId, [
            'rel_of_id' => $ofId,
            'rel_cp_id' => $cpId
        ]);

        return $relId;
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
                'rel_ss_id' => $this->User->getSsId(),
                'rel_name' => $this->getStringParameter('rel_name'),
                'rel_short_name' => $this->getStringParameter('rel_short_name'),
                'rel_website' => $this->getStringParameter('rel_website'),
                'rel_email' => $this->getStringParameter('rel_email'),
                'rel_phone' => $this->getStringParameter('rel_phone'),
                'rel_vat' => $this->getStringParameter('rel_vat'),
                'rel_remark' => $this->getStringParameter('rel_remark'),
                'rel_of_id' => $this->getStringParameter('rel_of_id'),
                'rel_cp_id' => $this->getStringParameter('rel_cp_id'),
                'rel_active' => $this->getStringParameter('rel_active'),
            ];
            $relDao = new RelationDao();
            $relDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

        } elseif ($this->getFormAction() === 'doUpdateOffice') {
            $ofColVal = [
                'of_rel_id' => $this->getDetailReferenceValue(),
                'of_name' => $this->getStringParameter('of_name'),
                'of_invoice' => $this->getStringParameter('of_invoice', 'N'),
                'of_address' => $this->getStringParameter('of_address'),
                'of_dtc_id' => $this->getStringParameter('of_dtc_id'),
                'of_cty_id' => $this->getStringParameter('of_cty_id'),
                'of_stt_id' => $this->getStringParameter('of_stt_id'),
                'of_cnt_id' => $this->getStringParameter('of_cnt_id'),
                'of_postal_code' => $this->getStringParameter('of_postal_code'),
                'of_longitude' => $this->getFloatParameter('of_longitude'),
                'of_latitude' => $this->getFloatParameter('of_latitude'),
                'of_active' => $this->getStringParameter('of_active', 'Y'),
            ];
            $ofDao = new OfficeDao();
            if ($this->isValidParameter('of_id') === true) {
                $ofDao->doUpdateTransaction($this->getStringParameter('of_id'), $ofColVal);
            } else {
                $ofDao->doInsertTransaction($ofColVal);
            }
        } elseif ($this->getFormAction() === 'doUpdateContact') {
            $cpColVal = [
                'cp_of_id' => $this->getStringParameter('cp_of_id'),
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_active' => $this->getStringParameter('cp_active', 'Y'),
            ];
            $cpDao = new ContactPersonDao();
            if ($this->isValidParameter('cp_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $cpNumber = $sn->loadNumber('CP', $this->getStringParameter('cp_of_id'), $this->getDetailReferenceValue());
                $cpColVal['cp_number'] = $cpNumber;
                $cpDao->doInsertTransaction($cpColVal);
            } else {
                $cpDao->doUpdateTransaction($this->getStringParameter('cp_id'), $cpColVal);
            }
        } elseif ($this->getFormAction() === 'doUploadDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getStringParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getStringParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateBank') {
            $rbColVal = [
                'rb_rel_id' => $this->getDetailReferenceValue(),
                'rb_bn_id' => $this->getStringParameter('rb_bn_id'),
                'rb_cur_id' => $this->getStringParameter('rb_cur_id'),
                'rb_number' => $this->getStringParameter('rb_number'),
                'rb_branch' => $this->getStringParameter('rb_branch'),
                'rb_name' => $this->getStringParameter('rb_name'),
                'rb_active' => $this->getStringParameter('rb_active', 'Y'),
            ];
            $rbDao = new RelationBankDao();
            if ($this->isValidParameter('rb_id') === true) {
                $rbDao->doUpdateTransaction($this->getStringParameter('rb_id'), $rbColVal);
            } else {
                $rbDao->doInsertTransaction($rbColVal);
            }
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return RelationDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getAddressPortlet());
            $this->Tab->addPortlet('general', $this->getPicPortlet());
        } else {
            $this->Tab->addPortlet('general', $this->getOfficePortlet());
            $this->Tab->addPortlet('general', $this->getRemarkPortlet());
            $this->Tab->addPortlet('contactPerson', $this->getContactPortlet());
//            $this->Tab->addPortlet('finance', $this->getRelationBankPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('rel', $this->getDetailReferenceValue(), '', '', true));
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('rel_name', 2, 255);
            $this->Validation->checkRequire('rel_short_name', 2, 25);
            if ($this->isValidParameter('rel_email') === true) {
                $this->Validation->checkEmail('rel_email');
            }
            $this->Validation->checkUnique('rel_short_name', 'relation', [
                'rel_id' => $this->getDetailReferenceValue(),
            ], [
                'rel_ss_id' => $this->User->getSsId(),
            ]);
            if ($this->isInsert() === true) {
                $this->Validation->checkMaxLength('of_postal_code', '10');
                if ($this->isValidParameter('cp_email') === true) {
                    $this->Validation->checkEmail('cp_email');
                }
                $this->Validation->checkMaxLength('cp_phone', '25');
            }
        } elseif ($this->getFormAction() === 'doUpdateOffice') {
            $this->Validation->checkRequire('of_dtc_id');
            $this->Validation->checkRequire('of_cnt_id');
            $this->Validation->checkRequire('of_cty_id');
            $this->Validation->checkRequire('of_stt_id');
            $this->Validation->checkRequire('of_name', 2, 125);
            $this->Validation->checkRequire('of_address', 2, 255);
            if ($this->isValidParameter('of_longitude') === true) {
                $this->Validation->checkFloat('of_longitude');
            }
            if ($this->isValidParameter('of_latitude') === true) {
                $this->Validation->checkFloat('of_latitude');
            }
        } elseif ($this->getFormAction() === 'doUpdateContact') {
            $this->Validation->checkRequire('cp_of_id');
            $this->Validation->checkRequire('cp_name', 2, 125);
            if ($this->isValidParameter('cp_email') === true) {
                $this->Validation->checkMaxLength('cp_email', 125);
                $this->Validation->checkEmail('cp_email');
            }
            $this->Validation->checkMaxLength('cp_phone', 25);
        } elseif ($this->getFormAction() === 'doUpdateBank') {
            $this->Validation->checkRequire('rb_bn_id');
            $this->Validation->checkRequire('rb_number', 1, 255);
            if ($this->isValidParameter('rb_branch') === true) {
                $this->Validation->checkRequire('rb_branch', 3, 255);
            }
            $this->Validation->checkRequire('rb_name', 1, 255);
            $this->Validation->checkUnique('rb_number', 'relation_bank', [
                'rb_id' => $this->getIntParameter('rb_id'),
            ], [
                'rb_rel_id' => $this->getDetailReferenceValue(),
            ]);
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $this->setEnableViewButton();
        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Create Fields.
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        # Create a portlet box.
        $portlet = new Portlet('RelGeneralPtl', Trans::getWord('relation'));
        $portlet->setGridDimension(12, 12, 12);

        $shortNameField = $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name'));
        # Add field
        if ($this->isUpdate() === true) {
            $relNumber = $this->Field->getText('rel_number', $this->getStringParameter('rel_number'));
            $relNumber->setReadOnly();
            $fieldSet->addField(Trans::getWord('number'), $relNumber);
            $shortNameField->setReadOnly();
        }
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')), true);
        $fieldSet->addField(Trans::getWord('shortName'), $shortNameField, true);
        $fieldSet->addField(Trans::getWord('website'), $this->Field->getText('rel_website', $this->getStringParameter('rel_website')));
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('rel_email', $this->getStringParameter('rel_email')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('rel_phone', $this->getStringParameter('rel_phone')));
        $fieldSet->addField(Trans::getWord('vatNumber'), $this->Field->getText('rel_vat', $this->getStringParameter('rel_vat')));
        if ($this->isUpdate() === true) {
            # Office
            $ofField = $this->Field->getSingleSelect('of', 'rel_office', $this->getStringParameter('rel_office'));
            $ofField->setHiddenField('rel_of_id', $this->getStringParameter('rel_of_id'));
            $ofField->addParameter('of_rel_id', $this->getDetailReferenceValue());
            $ofField->setEnableNewButton(false);
            # Contact Person
            $cpField = $this->Field->getSingleSelect('cp', 'rel_pic', $this->getStringParameter('rel_pic'));
            $cpField->setHiddenField('rel_cp_id', $this->getStringParameter('rel_cp_id'));
            $cpField->addParameter('cp_rel_id', $this->getDetailReferenceValue());
            $cpField->addParameterById('cp_of_id', 'rel_of_id', Trans::getWord('mainOffice'));
            $cpField->setEnableNewButton(false);
            $fieldSet->addField(Trans::getWord('mainOffice'), $ofField);
            $fieldSet->addField(Trans::getWord('pic'), $cpField);
            if ($this->isSystemOwner() === true) {
                $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('rel_active', $this->getStringParameter('rel_active')));
            } else {
                $fieldSet->addHiddenField($this->Field->getHidden('rel_active', $this->getStringParameter('rel_active')));
            }
        }
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the address Field Set.
     *
     * @return Portlet
     */
    private function getAddressPortlet(): Portlet
    {
        $districtField = $this->Field->getSingleSelectTable('dtc', 'of_district', $this->getStringParameter('of_district'), 'loadSingleSelectTableData');
        $districtField->setHiddenField('of_dtc_id', $this->getStringParameter('of_dtc_id'));

        $districtField->setFilters([
            'dtc_country' => Trans::getWord('country'),
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);

        $districtField->setTableColumns([
            'dtc_country' => Trans::getWord('country'),
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);

        $districtField->setAutoCompleteFields([
            'of_country' => 'dtc_country',
            'of_state' => 'dtc_state',
            'of_city' => 'dtc_city',
            'of_cnt_id' => 'dtc_cnt_id',
            'of_stt_id' => 'dtc_stt_id',
            'of_cty_id' => 'dtc_cty_id',

        ]);
        $districtField->setLabelCode('dtc_name');
        $districtField->setValueCode('dtc_id');
        $this->View->addModal($districtField->getModal());

        $cityField = $this->Field->getText('of_city', $this->getStringParameter('of_city'));
        $cityField->setReadOnly();
        $stateField = $this->Field->getText('of_state', $this->getStringParameter('of_state'));
        $stateField->setReadOnly();
        $countryField = $this->Field->getText('of_country', $this->getStringParameter('of_country'));
        $countryField->setReadOnly();
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('of_address', $this->getStringParameter('of_address')));
        $fieldSet->addField(Trans::getWord('district'), $districtField);
        $fieldSet->addField(Trans::getWord('city'), $cityField);
        $fieldSet->addField(Trans::getWord('state'), $stateField);
        $fieldSet->addField(Trans::getWord('country'), $countryField);
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_longitude', $this->getStringParameter('of_longitude')));
        $fieldSet->addField(Trans::getWord('latitude'), $this->Field->getText('of_latitude', $this->getStringParameter('of_latitude')));
        $fieldSet->addField(Trans::getWord('postalCode'), $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('of_cnt_id', $this->getStringParameter('of_cnt_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('of_stt_id', $this->getStringParameter('of_stt_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('of_cty_id', $this->getStringParameter('of_cty_id')));
        # Create a portlet box.
        $portlet = new Portlet('RelOfPtl', Trans::getWord('address'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the PIC Field Set.
     *
     * @return Portlet
     */
    private function getPicPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')));
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getStringParameter('cp_email')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone')));
        # Create a portlet box.
        $portlet = new Portlet('RelCpPtl', Trans::getWord('pic'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the Office Field Set.
     *
     * @return Portlet
     */
    private function getOfficePortlet(): Portlet
    {
        $modal = $this->getOfficeModal();
        $this->View->addModal($modal);

        $table = new Table('RelOfTbl');
        $table->setHeaderRow([
            'of_name' => Trans::getWord('name'),
            'of_full_address' => Trans::getWord('address'),
            'of_invoice' => Trans::getWord('invoiceOffice'),
            'of_active' => Trans::getWord('active'),
        ]);
        $data = OfficeDao::getDataByRelation($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('of_active', 'yesno');
        $table->setColumnType('of_invoice', 'yesno');
        $table->setColumnType('of_main', 'yesno');
        $table->setUpdateActionByModal($modal, 'of', 'getByid', ['of_id']);
        # Create a portlet box.
        $portlet = new Portlet('RelOfPtl', Trans::getWord('office'));
        $btnOfMdl = new ModalButton('btnOfMdl', Trans::getWord('addOffice'), $modal->getModalId());
        $btnOfMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnOfMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the Office Field Set.
     *
     * @return Portlet
     */
    private function getRemarkPortlet(): Portlet
    {

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('rel_remark', $this->getStringParameter('rel_remark')));
        # Create a portlet box.
        $portlet = new Portlet('RelRemarkPtl', Trans::getWord('notes'));
        $portlet->setGridDimension(12, 12, 12);
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getContactPortlet(): Portlet
    {
        $modal = $this->getContactModal();
        $this->View->addModal($modal);
        $table = new Table('RelCpTbl');
        $table->setHeaderRow([
            'cp_office' => Trans::getWord('office'),
            'cp_name' => Trans::getWord('name'),
            'cp_email' => Trans::getWord('email'),
            'cp_phone' => Trans::getWord('phone'),
            'cp_active' => Trans::getWord('active'),
        ]);
        $data = ContactPersonDao::getDataByRelation($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('cp_active', 'yesno');
        $table->setUpdateActionByModal($modal, 'cp', 'getByid', ['cp_id']);
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
     * @return Modal
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
        # Create Office Field
        $officeField = $this->Field->getSingleSelect('of', 'cp_office', $this->getParameterForModal('cp_office', $showModal));
        $officeField->setHiddenField('cp_of_id', $this->getParameterForModal('cp_of_id', $showModal));
        $officeField->addParameter('of_rel_id', $this->getDetailReferenceValue());
        $officeField->setEnableNewButton(false);
        $officeField->setEnableDetailButton(false);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getParameterForModal('cp_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('office'), $officeField, true);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getParameterForModal('cp_email', $showModal)));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getParameterForModal('cp_phone', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getParameterForModal('cp_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cp_id', $this->getParameterForModal('cp_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getOfficeModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('RelOfMdl', Trans::getWord('office'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateOffice');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateOffice' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create custom field.
        $districtField = $this->Field->getSingleSelectTable('dtc', 'of_district', $this->getParameterForModal('of_district', $showModal), 'loadSingleSelectTableData');
        $districtField->setHiddenField('of_dtc_id', $this->getParameterForModal('of_dtc_id', $showModal));

        $districtField->setFilters([
            'dtc_country' => Trans::getWord('country'),
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);

        $districtField->setTableColumns([
            'dtc_country' => Trans::getWord('country'),
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);

        $districtField->setAutoCompleteFields([
            'of_country' => 'dtc_country',
            'of_state' => 'dtc_state',
            'of_city' => 'dtc_city',
            'of_cnt_id' => 'dtc_cnt_id',
            'of_stt_id' => 'dtc_stt_id',
            'of_cty_id' => 'dtc_cty_id',
        ]);
        $districtField->setLabelCode('dtc_name');
        $districtField->setValueCode('dtc_id');
        $districtField->setParentModal($modal->getModalId());
        $this->View->addModal($districtField->getModal());

        $cityField = $this->Field->getText('of_city', $this->getParameterForModal('of_city', $showModal));
        $cityField->setReadOnly();
        $stateField = $this->Field->getText('of_state', $this->getParameterForModal('of_state', $showModal));
        $stateField->setReadOnly();
        $countryField = $this->Field->getText('of_country', $this->getParameterForModal('of_country', $showModal));
        $countryField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('of_name', $this->getParameterForModal('of_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('of_address', $this->getParameterForModal('of_address', $showModal)), true);
        $fieldSet->addField(Trans::getWord('city'), $cityField);
        $fieldSet->addField(Trans::getWord('state'), $stateField);
        $fieldSet->addField(Trans::getWord('country'), $countryField);
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_longitude', $this->getParameterForModal('of_longitude', $showModal)));
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_latitude', $this->getParameterForModal('of_latitude', $showModal)));
        $fieldSet->addField(Trans::getWord('postalCode'), $this->Field->getText('of_postal_code', $this->getParameterForModal('of_postal_code', $showModal)));
        $fieldSet->addField(Trans::getWord('invoiceOffice'), $this->Field->getYesNo('of_invoice', $this->getParameterForModal('of_invoice', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('of_active', $this->getParameterForModal('of_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('of_cnt_id', $this->getParameterForModal('of_cnt_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('of_stt_id', $this->getParameterForModal('of_stt_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('of_cty_id', $this->getParameterForModal('of_cty_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('of_id', $this->getParameterForModal('of_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getRelationBankPortlet(): Portlet
    {
        $modal = $this->getRelationBankModal();
        $this->View->addModal($modal);
        $table = new Table('RelRbTbl');
        $table->setHeaderRow([
            'rb_number' => Trans::getWord('accountNumber'),
            'rb_name' => Trans::getWord('accountName'),
            'rb_bank' => Trans::getWord('bank'),
            'rb_branch' => Trans::getWord('branch'),
            'rb_active' => Trans::getWord('active'),
        ]);
        $data = RelationBankDao::getByRelId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('rb_active', 'yesno');
        $table->setUpdateActionByModal($modal, 'relationBank', 'getByReference', ['rb_id']);
        # Create a portlet box.
        $portlet = new Portlet('RelRbPtl', Trans::getWord('relationBank'));
        $btnCpMdl = new ModalButton('btnRbMdl', Trans::getWord('addBank'), $modal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getRelationBankModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('RelRbMdl', Trans::getWord('relationBank'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateBank');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateBank' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Office Field
        $bankField = $this->Field->getSingleSelect('bank', 'rb_bank', $this->getParameterForModal('rb_bank', $showModal));
        $bankField->setHiddenField('rb_bn_id', $this->getParameterForModal('rb_bn_id', $showModal));
        $bankField->setEnableNewButton(false);
        $bankField->setEnableDetailButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('accountNumber'), $this->Field->getText('rb_number', $this->getParameterForModal('rb_number', $showModal)), true);
        $fieldSet->addField(Trans::getWord('bank'), $bankField, true);
        $fieldSet->addField(Trans::getWord('accountName'), $this->Field->getText('rb_name', $this->getParameterForModal('rb_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('branch'), $this->Field->getText('rb_branch', $this->getParameterForModal('rb_branch', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('rb_active', $this->getParameterForModal('rb_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('rb_id', $this->getParameterForModal('rb_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Bool
     */
    private function isSystemOwner(): bool
    {
        return $this->getDetailReferenceValue() === $this->User->Settings->getOwnerId();
    }
}
