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
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\DealDao;
use App\Model\Dao\Crm\TaskDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\TaskParticipantDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to handle the creation of detail Task page
 *
 * @package    app
 * @subpackage Model\Detail\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Task extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'task', 'tsk_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('TaskCrm');
        $status = SystemTypeDao::getByGroupAndName('taskstatus', 'Open');
        $colVal = [
            'tsk_ss_id' => $this->User->getSsId(),
            'tsk_number' => $number,
            'tsk_subject' => $this->getStringParameter('tsk_subject'),
            'tsk_rel_id' => $this->getIntParameter('tsk_rel_id'),
            'tsk_pic_id' => $this->getIntParameter('tsk_pic_id'),
            'tsk_type_id' => $this->getIntParameter('tsk_type_id'),
            'tsk_priority_id' => $this->getIntParameter('tsk_priority_id'),
            'tsk_status_id' => $status['sty_id'],
            'tsk_assign_id' => $this->getIntParameter('tsk_assign_id'),
            'tsk_location' => $this->getStringParameter('tsk_location'),
            'tsk_dl_id' => $this->getIntParameter('tsk_dl_id'),
            'tsk_start_date' => $this->getStringParameter('tsk_start_date'),
            'tsk_start_time' => $this->getStringParameter('tsk_start_time'),
            'tsk_end_date' => $this->getStringParameter('tsk_end_date'),
            'tsk_end_time' => $this->getStringParameter('tsk_end_time'),
            'tsk_description' => $this->getStringParameter('tsk_description'),
            'tsk_result' => $this->getStringParameter('tsk_result'),
            'tsk_next_step' => $this->getStringParameter('tsk_next_step'),
        ];
        $tskDao = new TaskDao();
        $tskDao->doInsertTransaction($colVal);

        return $tskDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUploadDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
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
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateParticipant') {
            $colVal = [
                'tp_tsk_id' => $this->getDetailReferenceValue(),
                'tp_rel_id' => $this->getIntParameter('tp_rel_id'),
                'tp_cp_id' => $this->getIntParameter('tp_cp_id'),
            ];
            $tpDao = new TaskParticipantDao();
            if ($this->isValidParameter('tp_id') === true) {
                $tpDao->doUpdateTransaction($this->getIntParameter('tp_id'), $colVal);
            } else {
                $tpDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteParticipant') {
            $tpDao = new TaskParticipantDao();
            $tpDao->doDeleteTransaction($this->getIntParameter('tp_id_del'));
        } else {
            $colVal = [
                'tsk_subject' => $this->getStringParameter('tsk_subject'),
                'tsk_rel_id' => $this->getIntParameter('tsk_rel_id'),
                'tsk_pic_id' => $this->getIntParameter('tsk_pic_id'),
                'tsk_type_id' => $this->getIntParameter('tsk_type_id'),
                'tsk_priority_id' => $this->getIntParameter('tsk_priority_id'),
                'tsk_assign_id' => $this->getIntParameter('tsk_assign_id'),
                'tsk_location' => $this->getStringParameter('tsk_location'),
                'tsk_dl_id' => $this->getIntParameter('tsk_dl_id'),
                'tsk_start_date' => $this->getStringParameter('tsk_start_date'),
                'tsk_start_time' => $this->getStringParameter('tsk_start_time'),
                'tsk_end_date' => $this->getStringParameter('tsk_end_date'),
                'tsk_end_time' => $this->getStringParameter('tsk_end_time'),
                'tsk_description' => $this->getStringParameter('tsk_description'),
                'tsk_result' => $this->getStringParameter('tsk_result'),
                'tsk_next_step' => $this->getStringParameter('tsk_next_step'),
            ];
            $tskDao = new TaskDao();
            $tskDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return TaskDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isValidParameter('tsk_dl_id') === true && $this->isInsert() === true) {
            $dealData = DealDao::getByReferenceAndSystem($this->getIntParameter('tsk_dl_id'), $this->User->getSsId());
            $this->setParameter('tsk_dl_id', $dealData['dl_id']);
            $this->setParameter('tsk_dl_name', $dealData['dl_name']);
            $this->setParameter('tsk_rel_name', $dealData['dl_rel_name']);
            $this->setParameter('tsk_rel_id', $dealData['dl_rel_id']);
        }
        if ($this->isValidParameter('tsk_rel_id') === true && $this->isInsert() === true) {
            $relData = RelationDao::getByReferenceAndSystem($this->getIntParameter('tsk_rel_id'), $this->User->getSsId());
            $this->setParameter('tsk_rel_name', $relData['rel_name']);
            $this->setParameter('tsk_rel_id', $relData['rel_id']);
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getInformationPortlet());
        if ($this->isUpdate()) {
            $this->overridePageTitle();
            $this->Tab->addPortlet('general', $this->getParticipantPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('taskcrm', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('tsk_subject', 3, 256);
            $this->Validation->checkRequire('tsk_rel_id');
            $this->Validation->checkRequire('tsk_type_id');
            $this->Validation->checkRequire('tsk_priority_id');
            $this->Validation->checkRequire('tsk_assign_id');
            if ($this->isValidParameter('tsk_location') === true) {
                $this->Validation->checkMaxLength('tsk_location', 256);
            }
            if ($this->isValidParameter('tsk_start_date') === true) {
                $this->Validation->checkDate('tsk_start_date');
            }
            if ($this->isValidParameter('tsk_start_time') === true) {
                $this->Validation->checkTime('tsk_start_time');
            }
            if ($this->isValidParameter('tsk_end_date') === true) {
                $this->Validation->checkDate('tsk_end_date');
            }
            if ($this->isValidParameter('tsk_end_time') === true) {
                $this->Validation->checkTime('tsk_end_time');
            }
        } elseif ($this->getFormAction() === 'doUploadDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
            $this->Validation->checkRequire('doc_description');
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        } elseif ($this->getFormAction() === 'doUpdateParticipant') {
            $this->Validation->checkRequire('tp_rel_id');
            $this->Validation->checkRequire('tp_cp_id');
            $this->Validation->checkUnique('tp_cp_id', 'task_participant',
                [
                    'tp_id' => $this->getIntParameter('tp_id'),
                ], [
                    'tp_tsk_id' => $this->getDetailReferenceValue(),
                    'tp_deleted_on' => null,
                ]);
        } elseif ($this->getFormAction() === 'doDeleteParticipant') {
            $this->Validation->checkRequire('tp_id_del');
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
        # Instantiate Portlet Object
        $portlet = new Portlet('GnrlPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension();
        $relationField = $this->Field->getSingleSelect('relation', 'tsk_rel_name', $this->getStringParameter('tsk_rel_name'));
        $relationField->setHiddenField('tsk_rel_id', $this->getIntParameter('tsk_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addClearField('tsk_pic_name');
        $relationField->addClearField('tsk_pic_id');
        $relationField->addClearField('tsk_dl_name');
        $relationField->addClearField('tsk_dl_id');
        $picField = $this->Field->getSingleSelect('contactPerson', 'tsk_pic_name', $this->getStringParameter('tsk_pic_name'));
        $picField->setHiddenField('tsk_pic_id', $this->getIntParameter('tsk_pic_id'));
        $picField->addParameterById('cp_rel_id', 'tsk_rel_id', Trans::getCrmWord('relation'));
        $picField->setDetailReferenceCode('cp_id');
        $typeField = $this->Field->getSingleSelect('sty', 'tsk_type_name', $this->getStringParameter('tsk_type_name'));
        $typeField->setHiddenField('tsk_type_id', $this->getIntParameter('tsk_type_id'));
        $typeField->addParameter('sty_group', 'tasktype');
        $typeField->setEnableNewButton(false);
        $typeField->setEnableDetailButton(false);
        $priorityField = $this->Field->getSingleSelect('sty', 'tsk_priority_name', $this->getStringParameter('tsk_priority_name'));
        $priorityField->setHiddenField('tsk_priority_id', $this->getIntParameter('tsk_priority_id'));
        $priorityField->addParameter('sty_group', 'taskpriority');
        $priorityField->setEnableNewButton(false);
        $priorityField->setEnableDetailButton(false);
        $assignField = $this->Field->getSingleSelect('user', 'tsk_assign_name', $this->getStringParameter('tsk_assign_name'));
        $assignField->setHiddenField('tsk_assign_id', $this->getIntParameter('tsk_assign_id'));
        $assignField->addParameter('ss_id', $this->User->getSsId());
        $assignField->addParameter('rel_id', $this->User->getRelId());
        $assignField->setEnableNewButton(false);
        $assignField->setEnableDetailButton(false);
        $dealField = $this->Field->getSingleSelect('deal', 'tsk_dl_name', $this->getStringParameter('tsk_dl_name'));
        $dealField->setHiddenField('tsk_dl_id', $this->getIntParameter('tsk_dl_id'));
        $dealField->addParameter('dl_ss_id', $this->User->getSsId());
        $dealField->addParameterById('dl_rel_id', 'tsk_rel_id', Trans::getCrmWord('relation'));
        $dealField->setDetailReferenceCode('dl_id');
        # Add field to field set
        $fieldSet->addField(Trans::getCrmWord('subject'), $this->Field->getText('tsk_subject', $this->getStringParameter('tsk_subject')), true);
        $fieldSet->addField(Trans::getCrmWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getCrmWord('pic'), $picField);
        $fieldSet->addField(Trans::getCrmWord('taskType'), $typeField, true);
        $fieldSet->addField(Trans::getCrmWord('priority'), $priorityField, true);
        $fieldSet->addField(Trans::getCrmWord('assignTo'), $assignField, true);
        $fieldSet->addField(Trans::getCrmWord('location'), $this->Field->getText('tsk_location', $this->getStringParameter('tsk_location')));
        $fieldSet->addField(Trans::getCrmWord('relatedDeal'), $dealField);
        $fieldSet->addField(Trans::getCrmWord('startDate'), $this->Field->getCalendar('tsk_start_date', $this->getStringParameter('tsk_start_date')));
        $fieldSet->addField(Trans::getCrmWord('startTime'), $this->Field->getTime('tsk_start_time', $this->getStringParameter('tsk_start_time')));
        $fieldSet->addField(Trans::getCrmWord('endDate'), $this->Field->getCalendar('tsk_end_date', $this->getStringParameter('tsk_end_date')));
        $fieldSet->addField(Trans::getCrmWord('endTime'), $this->Field->getTime('tsk_end_time', $this->getStringParameter('tsk_end_time')));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the information Field Set.
     *
     * @return Portlet
     */
    private function getInformationPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('InfoPtl', Trans::getCrmWord('information'));
        $portlet->setGridDimension(12, 12, 12);
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('agenda'), $this->Field->getTextArea('tsk_description', $this->getStringParameter('tsk_description')));
        if ($this->isUpdate() === true) {
            $portlet->setGridDimension(6, 6, 12);
            $fieldSet->setGridDimension(12, 12, 12);
            $fieldSet->addField(Trans::getCrmWord('result'), $this->Field->getTextArea('tsk_result', $this->getStringParameter('tsk_result')));
            $fieldSet->addField(Trans::getCrmWord('nextStep'), $this->Field->getTextArea('tsk_next_step', $this->getStringParameter('tsk_next_step')));
        }
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the participant Field Set.
     *
     * @return Portlet
     */
    private function getParticipantPortlet(): Portlet
    {
        $modal = $this->getParticipantModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getParticipantDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('tskPrtTbl');
        $table->setHeaderRow([
            'tp_cp_name' => Trans::getCrmWord('name'),
            'tp_rel_name' => Trans::getCrmWord('relation'),
            'tp_cp_email' => Trans::getCrmWord('email'),
        ]);
        $wheres[] = SqlHelper::generateNumericCondition('tp_tsk_id', $this->getDetailReferenceValue());
        $wheres[] = '(tp_deleted_on IS NULL)';
        $data = TaskParticipantDao::loadData($wheres);
        $table->addRows($data);
        $table->setUpdateActionByModal($modal, 'tp', 'getByReference', ['tp_id']);
        $table->setDeleteActionByModal($modalDelete, 'tp', 'getByReferenceForDelete', ['tp_id']);
        $btnTskPrtMdl = new ModalButton('btnTskPrtMdl', Trans::getCrmWord('participant'), $modal->getModalId());
        $btnTskPrtMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        # Instantiate Portlet Object
        $portlet = new Portlet('TskPrtPtl', Trans::getCrmWord('participant'));
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addTable($table);
        $portlet->addButton($btnTskPrtMdl);

        return $portlet;
    }

    /**
     * Function to get participant modal
     *
     * @return Modal
     */
    private function getParticipantModal(): Modal
    {
        $modal = new Modal('tskPrtMdl', Trans::getCrmWord('participant'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateParticipant');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateParticipant' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $relationField = $this->Field->getSingleSelect('relation', 'tp_rel_name', $this->getParameterForModal('tp_rel_name', $showModal));
        $relationField->setHiddenField('tp_rel_id', $this->getParameterForModal('tp_rel_id', $showModal));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addClearField('tp_cp_name');
        $relationField->addClearField('tp_cp_id');
        $picField = $this->Field->getSingleSelect('contactPerson', 'tp_cp_name', $this->getParameterForModal('tp_cp_name', $showModal));
        $picField->setHiddenField('tp_cp_id', $this->getParameterForModal('tp_cp_id', $showModal));
        $picField->addParameterById('cp_rel_id', 'tp_rel_id', Trans::getCrmWord('relation'));
        $picField->setDetailReferenceCode('cp_id');
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getCrmWord('name'), $picField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('tp_id', $this->getParameterForModal('tp_id')));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get participant delete modal
     *
     * @return Modal
     */
    private function getParticipantDeleteModal(): Modal
    {
        $modal = new Modal('tskPrtDelMdl', Trans::getCrmWord('participant'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteParticipant');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteParticipant' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $relationField = $this->Field->getText('tp_rel_name_del', $this->getParameterForModal('tp_rel_name_del', $showModal));
        $relationField->setReadOnly();
        $picField = $this->Field->getText('tp_cp_name_del', $this->getParameterForModal('tp_cp_name_del', $showModal));
        $picField->setReadOnly();
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('relation'), $relationField);
        $fieldSet->addField(Trans::getCrmWord('name'), $picField);
        $fieldSet->addHiddenField($this->Field->getHidden('tp_id_del', $this->getParameterForModal('tp_id_del')));

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
        $title = $this->getStringParameter('tsk_number');
        $status = new LabelGray(Trans::getCrmWord('open'));
        if ($this->isValidParameter('tsk_start_on') === true && $this->isValidParameter('tsk_finish_on') === false) {
            $status = new LabelWarning(Trans::getCrmWord('inProgress'));
        } elseif ($this->isValidParameter('tsk_finish_on') === true) {
            $status = new LabelSuccess(Trans::getCrmWord('finish'));
        }
        $this->View->setDescription($title . ' | ' . $status);
    }
}
