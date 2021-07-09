<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Master\Employee;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\ContactPersonDao;
use App\Model\Dao\Master\Employee\EmployeeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Master\Employee\EmployeeItemSalaryDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail Employee page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Employee extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'em', 'em_id');
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
        $cpNumber = $sn->loadNumber('CP', $this->User->Relation->getOfficeId(), $this->User->Settings->getOwnerId());
        $cpColVal = [
            'cp_number' => $cpNumber,
            'cp_of_id' => $this->User->Relation->getOfficeId(),
            'cp_name' => $this->getStringParameter('em_name'),
            'cp_email' => $this->getStringParameter('em_email'),
            'cp_phone' => $this->getStringParameter('em_phone'),
            'cp_active' => 'Y',
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($cpColVal);

        $emNumber = $sn->loadNumber('EM', $this->User->Relation->getOfficeId(), $this->User->Settings->getOwnerId());

        $colVal = [
            'em_ss_id' => $this->User->getSsId(),
            'em_cp_id' => $cpDao->getLastInsertId(),
            'em_jt_id' => $this->getStringParameter('em_jt_id'),
            'em_number' => $emNumber,
            'em_name' => $this->getStringParameter('em_name'),
            'em_identity_number' => $this->getStringParameter('em_identity_number'),
            'em_gender' => $this->getStringParameter('em_gender'),
            'em_birthday' => $this->getStringParameter('em_birthday'),
            'em_join_date' => $this->getStringParameter('em_join_date'),
            'em_phone' => $this->getStringParameter('em_phone'),
            'em_email' => $this->getStringParameter('em_email'),
            'em_active' => 'Y',
        ];
        $emDao = new EmployeeDao();
        $emDao->doInsertTransaction($colVal);
        return $emDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $cpColVal = [
                'cp_name' => $this->getStringParameter('em_name'),
                'cp_email' => $this->getStringParameter('em_email'),
                'cp_phone' => $this->getStringParameter('em_phone'),
                'cp_active' => $this->getStringParameter('em_active'),
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doUpdateTransaction($this->getStringParameter('em_cp_id'), $cpColVal);

            $colVal = [
                'em_jt_id' => $this->getStringParameter('em_jt_id'),
                'em_name' => $this->getStringParameter('em_name'),
                'em_identity_number' => $this->getStringParameter('em_identity_number'),
                'em_gender' => $this->getStringParameter('em_gender'),
                'em_birthday' => $this->getStringParameter('em_birthday'),
                'em_join_date' => $this->getStringParameter('em_join_date'),
                'em_phone' => $this->getStringParameter('em_phone'),
                'em_email' => $this->getStringParameter('em_email'),
                'em_active' => $this->getStringParameter('em_active'),
            ];
            $emDao = new EmployeeDao();
            $emDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isUploadDocumentAction() === true) {
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
                    'doc_public' => $this->getStringParameter('doc_public', 'Y'),
                ];
                $docDao = new DocumentDao();
                $docDao->doUploadDocument($colVal, $file);
            }
        } elseif ($this->isDeleteDocumentAction() === true) {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getStringParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateItemSalary') {
            $colVal = [
                'eis_em_id' => $this->getDetailReferenceValue(),
                'eis_isl_id' => $this->getStringParameter('eis_isl_id'),
                'eis_sty_id' => $this->getStringParameter('eis_sty_id'),
                'eis_amount' => $this->getFloatParameter('eis_amount'),
            ];
            $eisDao = new EmployeeItemSalaryDao();
            if ($this->isValidParameter('eis_id') === false) {
                $eisDao->doInsertTransaction($colVal);
            } else {
                $eisDao->doUpdateTransaction($this->getStringParameter('eis_id'), $colVal);
            }

        } elseif ($this->getFormAction() === 'doDeleteItemSalary') {
            $eisDao = new EmployeeItemSalaryDao();
            $eisDao->doDeleteTransaction($this->getStringParameter('eis_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EmployeeDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->isUpdate() === true) {
            $this->View->setDescription($this->getStringParameter('em_number'));
            $this->Tab->addPortlet('general', $this->getSalaryPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('em', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('em_name', 2, 256);
            $this->Validation->checkRequire('em_identity_number', 2, 256);
            $this->Validation->checkRequire('em_jt_id');
            $this->Validation->checkRequire('em_gender');
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('em_cp_id');
            }
        } elseif ($this->getFormAction() === 'doUpdateItemSalary') {
            $this->Validation->checkRequire('eis_isl_id');
            $this->Validation->checkRequire('eis_sty_id');
            $this->Validation->checkRequire('eis_amount');
            $this->Validation->checkFloat('eis_amount');
            $this->Validation->checkUnique('eis_isl_id', 'employee_item_salary', [
                'eis_id' => $this->getStringParameter('eis_id')
            ], [
                'eis_em_id' => $this->getDetailReferenceValue(),
                'eis_deleted_on' => null
            ]);
        } elseif ($this->getFormAction() === 'doDeleteItemSalary') {
            $this->Validation->checkRequire('eis_id_del');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('EmPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $jtField = $this->Field->getSingleSelect('jt', 'em_job_title', $this->getStringParameter('em_job_title'));
        $jtField->setHiddenField('em_jt_id', $this->getStringParameter('em_jt_id'));
        $jtField->addParameter('jt_ss_id', $this->User->getSsId());
        $jtField->setDetailReferenceCode('jt_id');

        $genderField = $this->Field->getRadioGroup('em_gender', $this->getStringParameter('em_gender'));
        $genderField->addRadios([
            'M' => Trans::getWord('man'),
            'W' => Trans::getWord('woman'),
        ]);


        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('em_name', $this->getStringParameter('em_name')), true);
        $fieldSet->addField(Trans::getWord('identityNumber'), $this->Field->getText('em_identity_number', $this->getStringParameter('em_identity_number')), true);
        $fieldSet->addField(Trans::getWord('gender'), $genderField, true);
        $fieldSet->addField(Trans::getWord('jobTitle'), $jtField, true);
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('em_phone', $this->getStringParameter('em_phone')));
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('em_email', $this->getStringParameter('em_email')));
        $fieldSet->addField(Trans::getWord('birthday'), $this->Field->getCalendar('em_birthday', $this->getStringParameter('em_birthday')));
        $fieldSet->addField(Trans::getWord('joinDate'), $this->Field->getCalendar('em_join_date', $this->getStringParameter('em_join_date')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('em_active', $this->getStringParameter('em_active')));
            $fieldSet->addHiddenField($this->Field->getHidden('em_cp_id', $this->getStringParameter('em_cp_id')));
        }

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }


    /**
     * Function to get salary portlet
     *
     * @return Portlet
     */
    private function getSalaryPortlet(): Portlet
    {
        $modal = $this->getSalaryModal();
        $modalDelete = $this->getSalaryDeleteModal();
        $this->View->addModal($modal);
        $this->View->addModal($modalDelete);

        $table = new Table('EmEisTbl');
        $table->setHeaderRow([
            'eis_item_salary' => Trans::getWord('description'),
            'eis_salary_type' => Trans::getWord('type'),
            'eis_amount' => Trans::getWord('amount'),
        ]);
        $table->addRows(EmployeeItemSalaryDao::getByEmId($this->getDetailReferenceValue()));
        $table->setColumnType('eis_amount', 'float');
        $table->setUpdateActionByModal($modal, 'eis', 'getById', ['eis_id']);
        $table->setDeleteActionByModal($modalDelete, 'eis', 'getByIdForDelete', ['eis_id']);
        # Instantiate Portlet Object
        $portlet = new Portlet('EmEisPtl', Trans::getWord('itemSalary'));
        $btn = new ModalButton('EmeEisBtn', Trans::getWord('add'), $modal->getModalId());
        $btn->btnPrimary()->pullRight()->btnMedium()->setIcon(Icon::Plus);
        $portlet->addButton($btn);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the Task Modal.
     *
     * @return Modal
     */
    private function getSalaryModal(): Modal
    {
        $mdl = new Modal('EmEisMdl', Trans::getWord('itemSalary'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doUpdateItemSalary');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateItemSalary' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Description
        $islField = $this->Field->getSingleSelect('isl', 'eis_item_salary', $this->getParameterForModal('isl_item_salary', $showModal));
        $islField->setHiddenField('eis_isl_id', $this->getParameterForModal('eis_isl_id', $showModal));
        $islField->addParameter('isl_ss_id', $this->User->getSsId());
        $islField->setDetailReferenceCode('isl_id');

        # Salary Type
        $styField = $this->Field->getSingleSelect('sty', 'eis_salary_type', $this->getParameterForModal('eis_salary_type', $showModal));
        $styField->setHiddenField('eis_sty_id', $this->getParameterForModal('eis_sty_id', $showModal));
        $styField->addParameter('sty_group', 'salarytype');
        $styField->setEnableNewButton(false);

        $fieldSet->addField(Trans::getWord('description'), $islField, true);
        $fieldSet->addField(Trans::getWord('type'), $styField);
        $fieldSet->addField(Trans::getWord('amount'), $this->Field->getNumber('eis_amount', $this->getParameterForModal('eis_amount', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('eis_id', $this->getParameterForModal('eis_id', $showModal)));

        $mdl->addFieldSet($fieldSet);
        return $mdl;
    }

    /**
     * Function to get the Task Delete Modal.
     *
     * @return Modal
     */
    private function getSalaryDeleteModal(): Modal
    {
        $mdl = new Modal('EisDelMdl', Trans::getWord('deleteItemSalary'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doDeleteItemSalary');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteItemSalary' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('eis_item_salary_del', $this->getParameterForModal('eis_item_salary_del', $showModal)));
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getText('eis_salary_type_del', $this->getParameterForModal('eis_salary_type_del', $showModal)));
        $fieldSet->addField(Trans::getWord('amount'), $this->Field->getNumber('eis_amount_del', $this->getParameterForModal('eis_amount_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('eis_id_del', $this->getParameterForModal('eis_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $mdl->addText($p);
        $mdl->setBtnOkName(Trans::getWord('yesDelete'));
        $mdl->addFieldSet($fieldSet);

        return $mdl;
    }


}
