<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Crm;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\DealDao;
use App\Model\Dao\Crm\LeadDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\RelationTypeDao;
use App\Model\Dao\Crm\TaskDao;
use App\Model\Dao\Crm\TicketDao;
use App\Model\Dao\Relation\ContactPersonDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationBankDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Location\DistrictDao;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to handle the creation of detail Lead page
 *
 * @package    app
 * @subpackage Model\Detail\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Lead extends AbstractFormModel
{
    /**
     * Property to store relation id
     *
     * @var $RelationId
     */
    private $RelationId;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'lead', 'ld_id');
        $this->setParameters($parameters);
        $this->RelationId = $this->getIntParameter('ld_rel_id');
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $relNumber = $sn->loadNumber('Relation', $this->User->Relation->getOfficeId());
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
            'rel_manager_id' => $this->getIntParameter('rel_manager_id'),
            'rel_main_contact_id' => $this->getIntParameter('rel_main_contact_id'),
            'rel_ids_id' => $this->getIntParameter('rel_ids_id'),
            'rel_source_id' => $this->getIntParameter('rel_source_id'),
            'rel_established' => $this->getIntParameter('rel_established'),
            'rel_size_id' => $this->getIntParameter('rel_size_id'),
            'rel_employee' => $this->getIntParameter('rel_employee'),
            'rel_revenue' => $this->getFloatParameter('rel_revenue'),
            'rel_owner' => 'N',
            'rel_active' => 'Y',
        ];
        $relDao = new RelationDao();
        $relDao->doInsertTransaction($colVal);
        # Insert Office
        $ofColVal = [
            'of_rel_id' => $relDao->getLastInsertId(),
            'of_name' => $this->getStringParameter('rel_short_name'),
            'of_main' => 'Y',
            'of_invoice' => 'Y',
            'of_dtc_id' => $this->getIntParameter('of_dtc_id'),
            'of_address' => $this->getStringParameter('of_address'),
            'of_postal_code' => $this->getStringParameter('of_postal_code'),
            'of_longitude' => $this->getFloatParameter('of_longitude'),
            'of_latitude' => $this->getFloatParameter('of_latitude'),
            'of_active' => 'Y',
        ];
        if ($this->isValidParameter('of_dtc_id') === true) {
            $districtData = DistrictDao::getByReference($this->getIntParameter('of_dtc_id'));
            $ofColVal['of_cnt_id'] = $districtData['dtc_cnt_id'];
            $ofColVal['of_stt_id'] = $districtData['dtc_stt_id'];
            $ofColVal['of_cty_id'] = $districtData['dtc_cty_id'];
        }
        $ofDao = new OfficeDao();
        $ofDao->doInsertTransaction($ofColVal);
        if ($this->isValidParameter('cp_name') === true) {
            # Insert Contact Person
            $cpNumber = $sn->loadNumber( 'ContactPerson', $this->User->Relation->getOfficeId());
            $cpColVal = [
                'cp_number' => $cpNumber,
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_of_id' => $ofDao->getLastInsertId(),
                'cp_salutation_id' => $this->getIntParameter('cp_salutation_id'),
                'cp_jbt_id' => $this->getIntParameter('cp_jbt_id'),
                'cp_dpt_id' => $this->getIntParameter('cp_dpt_id'),
                'cp_birthday' => $this->getStringParameter('cp_birthday'),
                'cp_office_manager' => 'Y',
                'cp_active' => 'Y',
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doInsertTransaction($cpColVal);
            if ($this->getStringParameter('rel_set_main_contact') === 'Y') {
                $relCol = ['rel_main_contact_id' => $cpDao->getLastInsertId()];
                $relUpDao = new RelationDao();
                $relUpDao->doUpdateTransaction($relDao->getLastInsertId(), $relCol);
            }
        }
        # Insert Lead
        $leadNumber = $sn->loadNumber( 'Lead');
        $leadColVal = [
            'ld_ss_id' => $this->User->getSsId(),
            'ld_number' => $leadNumber,
            'ld_rel_id' => $relDao->getLastInsertId(),
            'ld_sty_id' => $this->getIntParameter('ld_sty_id'),
        ];
        $leadDao = new LeadDao();
        $leadDao->doInsertTransaction($leadColVal);

        return $leadDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateOffice') {
            $districtData = DistrictDao::getByReference($this->getIntParameter('of_dtc_id'));
            $ofColVal = [
                'of_rel_id' => $this->RelationId,
                'of_name' => $this->getStringParameter('of_name'),
                'of_main' => 'N',
                'of_invoice' => $this->getStringParameter('of_invoice', 'N'),
                'of_address' => $this->getStringParameter('of_address'),
                'of_cnt_id' => $districtData['dtc_cnt_id'],
                'of_stt_id' => $districtData['dtc_stt_id'],
                'of_cty_id' => $districtData['dtc_cty_id'],
                'of_dtc_id' => $this->getIntParameter('of_dtc_id'),
                'of_postal_code' => $this->getStringParameter('of_postal_code'),
                'of_longitude' => $this->getFloatParameter('of_longitude'),
                'of_latitude' => $this->getFloatParameter('of_latitude'),
                'of_active' => $this->getStringParameter('of_active', 'Y'),
            ];
            $ofDao = new OfficeDao();
            if ($this->isValidParameter('of_id') === true) {
                $ofDao->doUpdateTransaction($this->getIntParameter('of_id'), $ofColVal);
            } else {
                $ofDao->doInsertTransaction($ofColVal);
            }
        } elseif ($this->getFormAction() === 'doUpdateContact') {
            $cpColVal = [
                'cp_of_id' => $this->getIntParameter('cp_of_id'),
                'cp_salutation_id' => $this->getIntParameter('cp_salutation_id'),
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_dpt_id' => $this->getIntParameter('cp_dpt_id'),
                'cp_jbt_id' => $this->getIntParameter('cp_jbt_id'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_birthday' => $this->getStringParameter('cp_birthday'),
                'cp_office_manager' => $this->getStringParameter('cp_office_manager', 'N'),
                'cp_active' => $this->getStringParameter('cp_active', 'Y'),
            ];
            $cpDao = new ContactPersonDao();
            if ($this->isValidParameter('cp_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $cpNumber = $sn->loadNumber('ContactPerson', $this->getIntParameter('cp_of_id'), $this->RelationId);
                $cpColVal['cp_number'] = $cpNumber;
                $cpDao->doInsertTransaction($cpColVal);
            } else {
                $cpDao->doUpdateTransaction($this->getIntParameter('cp_id'), $cpColVal);
            }
        } elseif ($this->getFormAction() === 'doUploadDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->RelationId,
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
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateBank') {
            $rbColVal = [
                'rb_rel_id' => $this->RelationId,
                'rb_bn_id' => $this->getIntParameter('rb_bn_id'),
                'rb_number' => $this->getStringParameter('rb_number'),
                'rb_branch' => $this->getStringParameter('rb_branch'),
                'rb_name' => $this->getStringParameter('rb_name'),
                'rb_active' => $this->getStringParameter('rb_active', 'Y'),
            ];
            $rbDao = new RelationBankDao();
            if ($this->isValidParameter('rb_id') === true) {
                $rbDao->doUpdateTransaction($this->getIntParameter('rb_id'), $rbColVal);
            } else {
                $rbDao->doInsertTransaction($rbColVal);
            }
        } elseif ($this->getFormAction() === 'doUpdateRelationType') {
            $colVal = [
                'rty_rel_id' => $this->RelationId,
                'rty_sty_id' => $this->getIntParameter('rty_sty_id')
            ];
            $rtyDao = new RelationTypeDao();
            if ($this->isValidParameter('rty_id') === true) {
                $rtyDao->doUpdateTransaction($this->getIntParameter('rty_id'), $colVal);
            } else {
                $rtyDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteRelationType') {
            $rtyDao = new RelationTypeDao();
            $rtyDao->doDeleteTransaction($this->getIntParameter('rty_id_del'));
        } else {
            $colVal = [
                'rel_name' => $this->getStringParameter('rel_name'),
                'rel_short_name' => $this->getStringParameter('rel_short_name'),
                'rel_website' => $this->getStringParameter('rel_website'),
                'rel_email' => $this->getStringParameter('rel_email'),
                'rel_phone' => $this->getStringParameter('rel_phone'),
                'rel_vat' => $this->getStringParameter('rel_vat'),
                'rel_remark' => $this->getStringParameter('rel_remark'),
                'rel_active' => $this->getStringParameter('rel_active', 'Y'),
                'rel_manager_id' => $this->getIntParameter('rel_manager_id'),
                'rel_main_contact_id' => $this->getIntParameter('rel_main_contact_id'),
                'rel_ids_id' => $this->getIntParameter('rel_ids_id'),
                'rel_source_id' => $this->getIntParameter('rel_source_id'),
                'rel_established' => $this->getIntParameter('rel_established'),
                'rel_size_id' => $this->getIntParameter('rel_size_id'),
                'rel_employee' => $this->getIntParameter('rel_employee'),
                'rel_revenue' => $this->getFloatParameter('rel_revenue'),
            ];
            $relDao = new RelationDao();
            $relDao->doUpdateTransaction($this->RelationId, $colVal);

            $leadColVal = [
                'ld_sty_id' => $this->getIntParameter('ld_sty_id'),
            ];
            $leadDao = new LeadDao();
            $leadDao->doUpdateTransaction($this->getDetailReferenceValue(), $leadColVal);

        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return LeadDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->RelationId = $this->getIntParameter('ld_rel_id');
        if ($this->isValidParameter('rel_set_main_contact') === false) {
            $this->setParameter('rel_set_main_contact', 'Y');
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getCompanyProfileFieldSet());
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getAddressFieldSet());
            $this->Tab->addPortlet('general', $this->getPicFieldSet());
        } else {
            $this->Tab->addPortlet('general', $this->getAdditionalFieldSet());
            $this->Tab->addPortlet('general', $this->getRelationTypeFieldSet());
            $this->Tab->addPortlet('general', $this->getOfficeFieldSet());
            $this->Tab->addPortlet('contactPerson', $this->getContactFieldSet());
            $this->Tab->addPortlet('finance', $this->getRelationBankPortlet());
            $this->Tab->addPortlet('task', $this->getTaskPortlet());
            $this->Tab->addPortlet('deal', $this->getDealPortlet());
            $this->Tab->addPortlet('ticket', $this->getTicketPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('relation', $this->RelationId));
            $this->overridePageTitle();
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
                'rel_id' => $this->RelationId,
            ], [
                'rel_ss_id' => $this->User->getSsId(),
            ]);
            if ($this->isValidParameter('rel_established') === true) {
                $this->Validation->checkInt('rel_established', '1800');
            }
            if ($this->isValidParameter('rel_employee') === true) {
                $this->Validation->checkInt('rel_employee', '1');
            }
            if ($this->isValidParameter('rel_revenue') === true) {
                $this->Validation->checkFloat('rel_revenue');
            }
            if ($this->isInsert() === true) {
                $this->Validation->checkMaxLength('of_postal_code', '10');
                if ($this->isValidParameter('cp_email') === true) {
                    $this->Validation->checkEmail('cp_email');
                }
                if ($this->isValidParameter('cp_birthday') === true) {
                    $this->Validation->checkDate('cp_birthday');
                }
                $this->Validation->checkMaxLength('cp_phone', '25');
            }
            $this->Validation->checkRequire('ld_sty_id');
        } elseif ($this->getFormAction() === 'doUpdateOffice') {
            $this->Validation->checkRequire('of_dtc_id');
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
            if ($this->isValidParameter('cp_birthday') === true) {
                $this->Validation->checkDate('cp_birthday');
            }
            $this->Validation->checkMaxLength('cp_phone', 25);
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
        } elseif ($this->getFormAction() === 'doGenerateSerialJob') {
            $this->Validation->checkRequire('rel_short_name');
            $this->Validation->checkRequire('srv_id');
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
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
                'rb_rel_id' => $this->RelationId,
            ]);
        } elseif ($this->getFormAction() === 'doUpdateRelationType') {
            $this->Validation->checkRequire('rty_sty_id');
            $this->Validation->checkUnique('rty_sty_id', 'relation_type', [
                'rty_id' => $this->getIntParameter('rty_id')
            ], [
                'rty_rel_id' => $this->RelationId,
                'rty_deleted_on' => null,
            ]);
        } elseif ($this->getFormAction() === 'doDeleteRelationType') {
            $this->Validation->checkRequire('rty_id_del');
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
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Create a portlet box.
        $portlet = new Portlet('RelGeneralPtl', Trans::getCrmWord('relation'));
        $portlet->setGridDimension(6, 6, 12);
        $shortNameField = $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name'));
        # Add field
        $managerField = $this->Field->getSingleSelect('user', 'rel_manager_name', $this->getStringParameter('rel_manager_name'));
        $managerField->setHiddenField('rel_manager_id', $this->getIntParameter('rel_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->addParameter('rel_id', $this->User->getRelId());
        $managerField->setEnableNewButton(false);
        $managerField->setEnableDetailButton(false);

        $statusField = $this->Field->getSingleSelect('sty', 'ld_sty_name', $this->getStringParameter('ld_sty_name'));
        $statusField->setHiddenField('ld_sty_id', $this->getIntParameter('ld_sty_id'));
        $statusField->addParameter('sty_group', 'leadstatus');

        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')), true);
        $fieldSet->addField(Trans::getCrmWord('shortName'), $shortNameField, true);
        $fieldSet->addField(Trans::getCrmWord('phone'), $this->Field->getText('rel_phone', $this->getStringParameter('rel_phone')));
        $fieldSet->addField(Trans::getCrmWord('email'), $this->Field->getText('rel_email', $this->getStringParameter('rel_email')));
        $fieldSet->addField(Trans::getCrmWord('website'), $this->Field->getText('rel_website', $this->getStringParameter('rel_website')));
        $fieldSet->addField(Trans::getCrmWord('relationManager'), $managerField);
        $fieldSet->addField(Trans::getCrmWord('leadStatus'), $statusField, true);
//        if ($this->isUpdate() === true && $this->getStringParameter('rel_owner') === 'N') {
//            $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('rel_active', $this->getStringParameter('rel_active')));
//        }
        $fieldSet->addHiddenField($this->Field->getHidden('ld_rel_id', $this->getIntParameter('ld_rel_id')));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the company profile Field Set.
     *
     * @return Portlet
     */
    private function getCompanyProfileFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $mainContactField = $this->Field->getSingleSelect('contactPerson', 'rel_main_contact_name', $this->getStringParameter('rel_main_contact_name'));
        $mainContactField->setHiddenField('rel_main_contact_id', $this->getIntParameter('rel_main_contact_id'));
        $mainContactField->addParameter('cp_rel_id', $this->RelationId);
        $mainContactField->setDetailReferenceCode('cp_id');
        $industryField = $this->Field->getSingleSelect('ids', 'rel_ids_name', $this->getStringParameter('rel_ids_name'));
        $industryField->setHiddenField('rel_ids_id', $this->getIntParameter('rel_ids_id'));
        $industryField->addParameter('ids_ss_id', $this->User->getSsId());
        $industryField->setDetailReferenceCode('ids_id');
        $sizeField = $this->Field->getSingleSelect('sty', 'rel_size', $this->getStringParameter('rel_size'));
        $sizeField->setHiddenField('rel_size_id', $this->getIntParameter('rel_size_id'));
        $sizeField->addParameter('sty_group', 'relationsize');
        $sizeField->setEnableNewButton(false);
        $sizeField->setEnableDetailButton(false);
        $sourceField = $this->Field->getSingleSelect('sty', 'rel_source_name', $this->getStringParameter('rel_source_name'));
        $sourceField->setHiddenField('rel_source_id', $this->getIntParameter('rel_source_id'));
        $sourceField->addParameter('sty_group', 'relationsource');
        $sourceField->setEnableNewButton(false);
        $sourceField->setEnableDetailButton(false);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getCrmWord('mainContact'), $mainContactField);
        }
        $fieldSet->addField(Trans::getCrmWord('industry'), $industryField);
        $fieldSet->addField(Trans::getCrmWord('establishedSince'), $this->Field->getText('rel_established', $this->getIntParameter('rel_established')));
        $fieldSet->addField(Trans::getCrmWord('size'), $sizeField);
        $fieldSet->addField(Trans::getCrmWord('employee'), $this->Field->getText('rel_employee', $this->getIntParameter('rel_employee')));
        $fieldSet->addField(Trans::getCrmWord('source'), $sourceField);
        $fieldSet->addField(Trans::getCrmWord('annualRevenue'), $this->Field->getNumber('rel_revenue', $this->getIntParameter('rel_revenue')));
        $fieldSet->addField(Trans::getCrmWord('taxNumber'), $this->Field->getText('rel_vat', $this->getStringParameter('rel_vat')));
        if ($this->isInsert() === true) {
            $fieldSet->addField(Trans::getCrmWord('description'), $this->Field->getTextArea('rel_remark', $this->getStringParameter('rel_remark')));
        }
        # Create a portlet box.
        $portlet = new Portlet('RelComproPtl', Trans::getCrmWord('companyProfile'));
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the Relation Type Field Set.
     *
     * @return Portlet
     */
    private function getRelationTypeFieldSet(): Portlet
    {
        $modal = $this->getRelationTypeModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getRelationTypeDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('RelTypTbl');
        $table->setHeaderRow([
            'rty_sty_name' => Trans::getCrmWord('type')
        ]);
        $wheres[] = SqlHelper::generateNumericCondition('rty_rel_id', $this->RelationId);
        $wheres[] = '(rty.rty_deleted_on IS NULL)';
        $data = RelationTypeDao::loadData($wheres);
        $table->addRows($data);
        $table->setUpdateActionByModal($modal, 'rty', 'getByReference', ['rty_id']);
        $table->setDeleteActionByModal($modalDelete, 'rty', 'getByReferenceForDelete', ['rty_id']);
        $btnRelTypMdl = new ModalButton('btnRelTypMdl', Trans::getCrmWord('relationType'), $modal->getModalId());
        $btnRelTypMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        # Create a portlet box.
        $portlet = new Portlet('RelTypePtl', Trans::getCrmWord('relationType'));
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addTable($table);
        $portlet->addButton($btnRelTypMdl);

        return $portlet;
    }

    /**
     * Function to get the additional Field Set.
     *
     * @return Portlet
     */
    private function getAdditionalFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('description'), $this->Field->getTextArea('rel_remark', $this->getStringParameter('rel_remark')));
        # Create a portlet box.
        $portlet = new Portlet('RelAddtionalPtl', Trans::getCrmWord('additionalInformation'));
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the address Field Set.
     *
     * @return Portlet
     */
    private function getAddressFieldSet(): Portlet
    {
        $districtField = $this->Field->getSingleSelect('district', 'of_district', $this->getStringParameter('of_district'), 'loadCompleteSingleSelectData');
        $districtField->setHiddenField('of_dtc_id', $this->getIntParameter('of_dtc_id'));
        $districtField->setDetailReferenceCode('dtc_id');
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $fieldSet->addField(Trans::getCrmWord('district'), $districtField);
        $fieldSet->addField(Trans::getCrmWord('address'), $this->Field->getText('of_address', $this->getStringParameter('of_address')));
        $fieldSet->addField(Trans::getCrmWord('postalCode'), $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code')));
        $fieldSet->addField(Trans::getCrmWord('longitude'), $this->Field->getText('of_longitude', $this->getStringParameter('of_longitude')));
        $fieldSet->addField(Trans::getCrmWord('latitude'), $this->Field->getText('of_latitude', $this->getStringParameter('of_latitude')));
        # Create a portlet box.
        $portlet = new Portlet('RelOfPtl', Trans::getCrmWord('address'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the PIC Field Set.
     *
     * @return Portlet
     */
    private function getPicFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $salutationField = $this->Field->getSingleSelect('sty', 'cp_salutation_name', $this->getStringParameter('cp_salutation_name'));
        $salutationField->setHiddenField('cp_salutation_id', $this->getIntParameter('cp_salutation_id'));
        $salutationField->addParameter('sty_group', 'relationsalutation');
        $salutationField->setEnableNewButton(false);
        $salutationField->setEnableDetailButton(false);
        $titleField = $this->Field->getSingleSelect('jbt', 'cp_jbt_name', $this->getStringParameter('cp_jbt_name'));
        $titleField->setHiddenField('cp_jbt_id', $this->getIntParameter('cp_jbt_id'));
        $titleField->addParameter('jbt_ss_id', $this->User->getSsId());
        $titleField->setDetailReferenceCode('jbt_id');
        $departmentField = $this->Field->getSingleSelect('dpt', 'cp_dpt_name', $this->getStringParameter('cp_dpt_name'));
        $departmentField->setHiddenField('cp_dpt_id', $this->getIntParameter('cp_dpt_id'));
        $departmentField->addParameter('dpt_ss_id', $this->User->getSsId());
        $departmentField->setDetailReferenceCode('dpt_id');
        $fieldSet->addField(Trans::getCrmWord('salutation'), $salutationField);
        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')));
        $fieldSet->addField(Trans::getCrmWord('department'), $departmentField);
        $fieldSet->addField(Trans::getCrmWord('jobTitle'), $titleField);
        $fieldSet->addField(Trans::getCrmWord('email'), $this->Field->getText('cp_email', $this->getStringParameter('cp_email')));
        $fieldSet->addField(Trans::getCrmWord('phone'), $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone')));
        $fieldSet->addField(Trans::getCrmWord('birthday'), $this->Field->getCalendar('cp_birthday', $this->getStringParameter('cp_birthday')));
        $fieldSet->addField(Trans::getCrmWord('setAsMainContact'), $this->Field->getYesNo('rel_set_main_contact', $this->getStringParameter('rel_set_main_contact')));
        # Create a portlet box.
        $portlet = new Portlet('RelCpPtl', Trans::getCrmWord('personalInCharge'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the Office Field Set.
     *
     * @return Portlet
     */
    private function getOfficeFieldSet(): Portlet
    {
        $modal = $this->getOfficeModal();
        $this->View->addModal($modal);

        $table = new Table('RelOfTbl');
        $table->setHeaderRow([
            'of_name' => Trans::getCrmWord('name'),
            'of_full_address' => Trans::getCrmWord('address'),
            'of_main' => Trans::getCrmWord('mainOffice'),
            'of_invoice' => Trans::getCrmWord('invoiceOffice'),
            'of_active' => Trans::getCrmWord('active'),
        ]);
        $data = OfficeDao::getDataByRelation($this->RelationId);
        $table->addRows($data);
        $table->setColumnType('of_active', 'yesno');
        $table->setColumnType('of_invoice', 'yesno');
        $table->setColumnType('of_main', 'yesno');
        $table->setUpdateActionByHyperlink('office/detail', ['of_id'], true);
        # Create a portlet box.
        $portlet = new Portlet('RelOfPtl', Trans::getCrmWord('office'));
        $btnOfMdl = new ModalButton('btnOfMdl', Trans::getCrmWord('addOffice'), $modal->getModalId());
        $btnOfMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnOfMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getContactFieldSet(): Portlet
    {
        $modal = $this->getContactModal();
        $this->View->addModal($modal);
        $table = new Table('RelCpTbl');
        $table->setHeaderRow([
            'cp_office' => Trans::getCrmWord('office'),
            'cp_salutation_name' => Trans::getCrmWord('salutation'),
            'cp_name' => Trans::getCrmWord('name'),
            'cp_dpt_name' => Trans::getCrmWord('department'),
            'cp_jbt_name' => Trans::getCrmWord('jobTitle'),
            'cp_email' => Trans::getCrmWord('email'),
            'cp_phone' => Trans::getCrmWord('phone'),
            'cp_office_manager' => Trans::getCrmWord('mainPic'),
            'cp_active' => Trans::getCrmWord('active'),
        ]);
        $data = ContactPersonDao::getDataByRelation($this->RelationId);
        $table->addRows($data);
        $table->setColumnType('cp_active', 'yesno');
        $table->setColumnType('cp_office_manager', 'yesno');
        $table->setUpdateActionByHyperlink('contactPerson/detail', ['cp_id'], true);
        # Create a portlet box.
        $portlet = new Portlet('RelCPPtl', Trans::getCrmWord('contactPerson'));
        $btnCpMdl = new ModalButton('btnCpMdl', Trans::getCrmWord('addContact'), $modal->getModalId());
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
        $modal = new Modal('RelCpMdl', Trans::getCrmWord('contactPerson'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateContact');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateContact' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Office Field
        $officeField = $this->Field->getSingleSelect('office', 'cp_office', $this->getParameterForModal('cp_office', $showModal));
        $officeField->setHiddenField('cp_of_id', $this->getParameterForModal('cp_of_id', $showModal));
        $officeField->setEnableNewButton(false);
        $officeField->setEnableDetailButton(false);
        $officeField->addParameter('of_rel_id', $this->RelationId);
        $salutationField = $this->Field->getSingleSelect('sty', 'cp_salutation_name', $this->getParameterForModal('cp_salutation_name', $showModal));
        $salutationField->setHiddenField('cp_salutation_id', $this->getParameterForModal('cp_salutation_id', $showModal));
        $salutationField->addParameter('sty_group', 'relationsalutation');
        $salutationField->setEnableNewButton(false);
        $salutationField->setEnableDetailButton(false);
        $titleField = $this->Field->getSingleSelect('jbt', 'cp_jbt_name', $this->getParameterForModal('cp_jbt_name'));
        $titleField->setHiddenField('cp_jbt_id', $this->getParameterForModal('cp_jbt_id'));
        $titleField->addParameter('jbt_ss_id', $this->User->getSsId());
        $titleField->setDetailReferenceCode('jbt_id');
        $departmentField = $this->Field->getSingleSelect('dpt', 'cp_dpt_name', $this->getParameterForModal('cp_dpt_name'));
        $departmentField->setHiddenField('cp_dpt_id', $this->getParameterForModal('cp_dpt_id'));
        $departmentField->addParameter('dpt_ss_id', $this->User->getSsId());
        $departmentField->setDetailReferenceCode('dpt_id');
        # Add field into field set.
        $fieldSet->addField(Trans::getCrmWord('office'), $officeField, true);
        $fieldSet->addField(Trans::getCrmWord('salutation'), $salutationField);
        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('cp_name', $this->getParameterForModal('cp_name', $showModal)), true);
        $fieldSet->addField(Trans::getCrmWord('department'), $departmentField);
        $fieldSet->addField(Trans::getCrmWord('jobTitle'), $titleField);
        $fieldSet->addField(Trans::getCrmWord('email'), $this->Field->getText('cp_email', $this->getParameterForModal('cp_email', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('phone'), $this->Field->getText('cp_phone', $this->getParameterForModal('cp_phone', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('birthday'), $this->Field->getCalendar('cp_birthday', $this->getParameterForModal('cp_birthday', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('mainPic'), $this->Field->getYesNo('cp_office_manager', $this->getParameterForModal('cp_office_manager', $showModal)));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('cp_active', $this->getParameterForModal('cp_active', $showModal)));
        }
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

        $modal = new Modal('RelOfMdl', Trans::getCrmWord('office'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateOffice');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateOffice' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create custom field.
        $districtField = $this->Field->getSingleSelect('district', 'of_district', $this->getParameterForModal('of_district', $showModal), 'loadCompleteSingleSelectData');
        $districtField->setHiddenField('of_dtc_id', $this->getParameterForModal('of_dtc_id', $showModal));
        $districtField->setDetailReferenceCode('dtc_id');
        $districtField->setEnableDetailButton(false);
        $districtField->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('of_name', $this->getParameterForModal('of_name', $showModal)), true);
        $fieldSet->addField(Trans::getCrmWord('city') . '/' . Trans::getCrmWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getCrmWord('address'), $this->Field->getText('of_address', $this->getParameterForModal('of_address', $showModal)), true);
        $fieldSet->addField(Trans::getCrmWord('postalCode'), $this->Field->getText('of_postal_code', $this->getParameterForModal('of_postal_code', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('longitude'), $this->Field->getText('of_longitude', $this->getParameterForModal('of_longitude', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('longitude'), $this->Field->getText('of_latitude', $this->getParameterForModal('of_latitude', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('invoiceOffice'), $this->Field->getYesNo('of_invoice', $this->getParameterForModal('of_invoice', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('of_active', $this->getParameterForModal('of_active', $showModal)));
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
            'rb_number' => Trans::getCrmWord('accountNumber'),
            'rb_name' => Trans::getCrmWord('accountName'),
            'rb_bank' => Trans::getCrmWord('bank'),
            'rb_branch' => Trans::getCrmWord('branch'),
            'rb_active' => Trans::getCrmWord('active'),
        ]);
        $data = RelationBankDao::getByRelId($this->RelationId);
        $table->addRows($data);
        $table->setColumnType('rb_active', 'yesno');
        $table->setUpdateActionByModal($modal, 'relationBank', 'getByReference', ['rb_id']);
        # Create a portlet box.
        $portlet = new Portlet('RelRbPtl', Trans::getCrmWord('relationBank'));
        $btnCpMdl = new ModalButton('btnRbMdl', Trans::getCrmWord('addBank'), $modal->getModalId());
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

        $modal = new Modal('RelRbMdl', Trans::getCrmWord('relationBank'));
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
        $fieldSet->addField(Trans::getCrmWord('accountNumber'), $this->Field->getText('rb_number', $this->getParameterForModal('rb_number', $showModal)), true);
        $fieldSet->addField(Trans::getCrmWord('bank'), $bankField, true);
        $fieldSet->addField(Trans::getCrmWord('accountName'), $this->Field->getText('rb_name', $this->getParameterForModal('rb_name', $showModal)), true);
        $fieldSet->addField(Trans::getCrmWord('branch'), $this->Field->getText('rb_branch', $this->getParameterForModal('rb_branch', $showModal)));
        $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('rb_active', $this->getParameterForModal('rb_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('rb_id', $this->getParameterForModal('rb_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get task portlet
     *
     * @return Portlet
     */
    private function getTaskPortlet(): Portlet
    {
        $portlet = new Portlet('tskPtl', Trans::getCrmWord('task'));
        $portlet->setGridDimension(12, 12, 12);
        $table = new TableDatas('tskTbl');
        $table->setHeaderRow([
            'tsk_number' => Trans::getCrmWord('number'),
            'tsk_rel_name' => Trans::getCrmWord('relation'),
            'tsk_subject' => Trans::getCrmWord('subject'),
            'tsk_type_name' => Trans::getCrmWord('taskType'),
            'tsk_priority_name' => Trans::getCrmWord('priority'),
            'tsk_start_date' => Trans::getCrmWord('startDate'),
            'tsk_status_name' => Trans::getCrmWord('status')
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('tsk_rel_id', $this->RelationId);
        $wheres[] = '(tsk.tsk_deleted_on IS NULL)';
        $orders[] = 'tsk_finish_on DESC';
        $orders[] = 'tsk_start_date DESC';
        $data = TaskDao::loadData($wheres, $orders);
        $tskData = [];
        foreach ($data as $row) {
            $status = new LabelGray(Trans::getCrmWord('open'));
            if (empty($row['tsk_start_on']) === false && empty($row['tsk_finish_on']) === true) {
                $status = new LabelWarning(Trans::getCrmWord('inProgress'));
            } elseif (empty($row['tsk_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getCrmWord('finish'));
            }
            $row['tsk_status_name'] = $status;
            $priority = '';
            if ($row['tsk_priority_name'] === 'Low') {
                $priority = new LabelSuccess($row['tsk_priority_name']);
            }
            if ($row['tsk_priority_name'] === 'Medium') {
                $priority = new LabelWarning($row['tsk_priority_name']);
            }
            if ($row['tsk_priority_name'] === 'High') {
                $priority = new LabelDanger($row['tsk_priority_name']);
            }
            $row['tsk_priority_name'] = $priority;
            $tskData[] = $row;
        }
        $table->addRows($tskData);
        $table->setUpdateActionByHyperlink('task/detail', ['tsk_id']);
        $table->setColumnType('tsk_start_date', 'date');
        $table->addColumnAttribute('tsk_priority_name', 'style', 'text-align: center');
        $table->addColumnAttribute('tsk_status_name', 'style', 'text-align: center');
        $btnTsk = new Button('btnTsk', Trans::getCrmWord('task'));
        $btnTsk->setPopup('task/detail', ['tsk_rel_id' => $this->RelationId]);
        $btnTsk->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addTable($table);
        $portlet->addButton($btnTsk);

        return $portlet;
    }

    /**
     * Function to get deal portlet
     *
     * @return Portlet
     */
    private function getDealPortlet(): Portlet
    {
        $portlet = new Portlet('dlPtl', Trans::getCrmWord('deal'));
        $portlet->setGridDimension(12, 12, 12);
        $table = new TableDatas('dlTbl');
        $table->setHeaderRow([
            'dl_number' => Trans::getCrmWord('number'),
            'dl_name' => Trans::getCrmWord('deal'),
            'dl_rel_name' => Trans::getCrmWord('relation'),
            'dl_amount' => Trans::getCrmWord('amount'),
            'dl_close_date' => Trans::getCrmWord('expectedCloseDate'),
            'dl_stage_name' => Trans::getCrmWord('salesStage'),
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('dl_rel_id', $this->RelationId);
        $wheres[] = '(dl.dl_deleted_on IS NULL)';
        $data = DealDao::loadData($wheres);
        $table->addRows($data);
        $table->setUpdateActionByHyperlink('deal/detail', ['dl_id']);
        $table->setColumnType('dl_amount', 'currency');
        $table->setColumnType('dl_close_date', 'date');
        $btnDl = new Button('btnDl', Trans::getCrmWord('deal'));
        $btnDl->setPopup('deal/detail', ['dl_rel_id' => $this->RelationId]);
        $btnDl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addTable($table);
        $portlet->addButton($btnDl);

        return $portlet;
    }

    /**
     * Function to get ticket portlet
     *
     * @return Portlet
     */
    private function getTicketPortlet(): Portlet
    {
        $portlet = new Portlet('tckPtl', Trans::getCrmWord('ticket'));
        $portlet->setGridDimension(12, 12, 12);
        $table = new TableDatas('tckTbl');
        $table->setHeaderRow([
            'tc_number' => Trans::getCrmWord('number'),
            'tc_rel_name' => Trans::getCrmWord('relation'),
            'tc_subject' => Trans::getCrmWord('subject'),
            'tc_report_date' => Trans::getCrmWord('reportDate'),
            'tc_priority_name' => Trans::getCrmWord('priority'),
            'tc_status_name' => Trans::getCrmWord('status')
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('tc_rel_id', $this->RelationId);
        $wheres[] = '(tc.tc_deleted_on IS NULL)';
        $orders[] = 'tc_finish_on DESC';
        $orders[] = 'tc_report_date DESC';
        $data = TicketDao::loadData($wheres, $orders);
        $results = [];
        foreach ($data as $row) {
            $status = new LabelGray(Trans::getCrmWord('open'));
            if (empty($row['tc_start_on']) === false && empty($row['tc_finish_on']) === true) {
                $status = new LabelWarning(Trans::getCrmWord('inProgress'));
            } elseif (empty($row['tc_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getCrmWord('finish'));
            }
            $row['tc_status_name'] = $status;
            $priority = '';
            if ($row['tc_priority_name'] === 'Low') {
                $priority = new LabelSuccess($row['tc_priority_name']);
            }
            if ($row['tc_priority_name'] === 'Medium') {
                $priority = new LabelWarning($row['tc_priority_name']);
            }
            if ($row['tc_priority_name'] === 'High') {
                $priority = new LabelDanger($row['tc_priority_name']);
            }
            $row['tc_priority_name'] = $priority;
            $results[] = $row;
        }
        $table->addRows($results);
        $table->setUpdateActionByHyperlink('ticket/detail', ['tc_id']);
        $table->setColumnType('tc_report_date', 'date');
        $table->addColumnAttribute('tc_priority_name', 'style', 'text-align: center');
        $table->addColumnAttribute('tc_status_name', 'style', 'text-align: center');
        $btnDl = new Button('btnTck', Trans::getCrmWord('ticket'));
        $btnDl->setPopup('ticket/detail', ['tc_rel_id' => $this->RelationId]);
        $btnDl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addTable($table);
        $portlet->addButton($btnDl);

        return $portlet;
    }

    /**
     * Function to get relation type modal
     *
     * @return Modal
     */
    private function getRelationTypeModal(): Modal
    {
        $modal = new Modal('relTypMdl', Trans::getCrmWord('relationType'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateRelationType');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateRelationType' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $typeField = $this->Field->getSingleSelect('sty', 'rty_sty_name', $this->getParameterForModal('rty_sty_name', $showModal));
        $typeField->setHiddenField('rty_sty_id', $this->getParameterForModal('rty_sty_id'));
        $typeField->addParameter('sty_group', 'relationtype');
        $typeField->setEnableNewButton(false);
        $typeField->setEnableDetailButton(false);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('type'), $typeField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('rty_id', $this->getParameterForModal('rty_id')));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get relation type delete modal
     *
     * @return Modal
     */
    private function getRelationTypeDeleteModal(): Modal
    {
        $modal = new Modal('RelTypDelMdl', Trans::getCrmWord('relationType'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteRelationType');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteRelationType' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $typeField = $this->Field->getText('rty_sty_name_del', $this->getParameterForModal('rty_sty_name_del', $showModal));
        $typeField->setReadOnly();
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('type'), $typeField);
        $fieldSet->addHiddenField($this->Field->getHidden('rty_id_del', $this->getParameterForModal('rty_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getCrmWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('ld_number');
        $wheres[] = SqlHelper::generateStringCondition('sty_group', 'leadstatus');
        $orders[] = 'sty.sty_order ASC';
        $statusData = SystemTypeDao::loadData($wheres, $orders);
        $statusLabel = '';
        foreach ($statusData as $data) {
            if ($this->getIntParameter('ld_sty_id') === $data['sty_id']) {
                $statusLabel = '<span class="' . $data['sty_label_type'] . '">' . $data['sty_name'] . '</span>';
            }
        }
        $this->View->setDescription($title . ' | ' . $statusLabel);
    }
}
