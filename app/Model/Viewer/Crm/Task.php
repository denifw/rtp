<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Viewer\Crm;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Crm\TaskDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Crm\TaskParticipantDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to handle the creation of detail Task page
 *
 * @package    app
 * @subpackage Model\Viewer\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Task extends AbstractViewerModel
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
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'startTask') {
            $status = SystemTypeDao::getByGroupAndName('taskstatus', 'In Progress');
            $colVal = [
                'tsk_status_id' => $status['sty_id'],
                'tsk_start_by' => $this->User->getId(),
                'tsk_start_on' => $this->getStringParameter('tsk_start_date_on') . ' ' . $this->getStringParameter('tsk_start_time_on'),
            ];
            $tskDao = new TaskDao();
            $tskDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'finishTask') {
            $status = SystemTypeDao::getByGroupAndName('taskstatus', 'Finish');
            $colVal = [
                'tsk_status_id' => $status['sty_id'],
                'tsk_finish_by' => $this->User->getId(),
                'tsk_finish_on' => $this->getStringParameter('tsk_finish_date_on') . ' ' . $this->getStringParameter('tsk_finish_time_on'),
            ];
            $tskDao = new TaskDao();
            $tskDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doUploadDocument') {
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
        $this->overridePageTitle();
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getDetailPortlet());
        $this->Tab->addPortlet('general', $this->getInformationPortlet());
        $this->Tab->addPortlet('general', $this->getParticipantPortlet());
        $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('taskcrm', $this->getDetailReferenceValue()));
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'startTask') {
            $this->Validation->checkRequire('tsk_start_date_on');
            $this->Validation->checkRequire('tsk_start_time_on');
        } elseif ($this->getFormAction() === 'finishTask') {
            $this->Validation->checkRequire('tsk_finish_date_on');
            $this->Validation->checkRequire('tsk_finish_time_on');
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
        parent::loadDefaultButton();
        if ($this->isValidParameter('tsk_start_on') === false) {
            $modal = $this->getStartModal();
            $this->View->addModal($modal);
            $btnStart = new ModalButton('btnStart', Trans::getCrmWord('startTask'), $modal->getModalId());
            $btnStart->setIcon(Icon::YoutubePlay)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnStart);
        } elseif ($this->isValidParameter('tsk_start_on') === true) {
            # Create button finish task
            if ($this->isValidParameter('tsk_finish_on') === false) {
                $modal = $this->getFinishModal();
                $this->View->addModal($modal);
                $btnFinish = new ModalButton('btnFinish', Trans::getCrmWord('finishTask'), $modal->getModalId());
                $btnFinish->btnSuccess();
                $btnFinish->pullRight();
                $btnFinish->btnMedium();
                $this->View->addButtonAtTheBeginning($btnFinish);
            }
            # Create button print MOM
            $pdfButton = new PdfButton('TaskPrint', Trans::getCrmWord('minuteofMeeting'), 'taskmom');
            $pdfButton->setIcon(Icon::FilePdfO)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('tsk_id', $this->getDetailReferenceValue());
            $this->View->addButtonAtTheBeginning($pdfButton);
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getCrmWord('subject'),
                'value' => $this->getStringParameter('tsk_subject'),
            ],
            [
                'label' => Trans::getCrmWord('relation'),
                'value' => $this->getStringParameter('tsk_rel_name'),
            ],
            [
                'label' => Trans::getCrmWord('pic'),
                'value' => $this->getStringParameter('tsk_pic_name'),
            ],
            [
                'label' => Trans::getCrmWord('taskType'),
                'value' => $this->getStringParameter('tsk_type_name'),
            ],
            [
                'label' => Trans::getCrmWord('priority'),
                'value' => $this->getStringParameter('tsk_priority_name'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Instantiate Portlet Object
        $portlet = new Portlet('GnrlPtl', Trans::getCrmWord('general'));
        $portlet->setGridDimension(6, 6);
        $portlet->addText($content);

        return $portlet;
    }

    /**
     * Function to get the detail Field Set.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $startDate = '';
        if ($this->isValidParameter('tsk_start_date') === true) {
            if ($this->isValidParameter('tsk_start_time') === true) {
                $startDate = DateTimeParser::format($this->getStringParameter('tsk_start_date') . ' ' . $this->getStringParameter('tsk_start_time'), 'Y-m-d H:i:s', 'd M Y H:i');
            } else {
                $startDate = DateTimeParser::format($this->getStringParameter('tsk_start_date'), 'Y-m-d', 'd M Y');
            }
        }
        $endDate = '';
        if ($this->isValidParameter('tsk_end_date') === true) {
            if ($this->isValidParameter('tsk_end_time') === true) {
                $endDate = DateTimeParser::format($this->getStringParameter('tsk_end_date') . ' ' . $this->getStringParameter('tsk_end_time'), 'Y-m-d H:i:s', 'd M Y H:i');
            } else {
                $endDate = DateTimeParser::format($this->getStringParameter('tsk_end_date'), 'Y-m-d', 'd M Y');
            }
        }
        $data = [
            [
                'label' => Trans::getCrmWord('assignTo'),
                'value' => $this->getStringParameter('tsk_assign_name'),
            ],
            [
                'label' => Trans::getCrmWord('location'),
                'value' => $this->getStringParameter('tsk_location'),
            ],
            [
                'label' => Trans::getCrmWord('relatedDeal'),
                'value' => $this->getStringParameter('tsk_dl_name'),
            ],
            [
                'label' => Trans::getCrmWord('startDate'),
                'value' => $startDate,
            ],
            [
                'label' => Trans::getCrmWord('endDate'),
                'value' => $endDate,
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Instantiate Portlet Object
        $portlet = new Portlet('DtlPtl', Trans::getCrmWord('detail'));
        $portlet->setGridDimension(6, 6);
        $portlet->addText($content);

        return $portlet;
    }

    /**
     * Function to get the description Field Set.
     *
     * @return Portlet
     */
    private function getInformationPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getCrmWord('agenda'),
                'value' => StringFormatter::replaceNewLineToBr($this->getStringParameter('tsk_description')),
            ]
        ];
        $content = StringFormatter::generateCustomTableView($data);
        $dataResult = [
            [
                'label' => Trans::getCrmWord('result'),
                'value' => StringFormatter::replaceNewLineToBr($this->getStringParameter('tsk_result')),
            ],
        ];
        $contentResult = StringFormatter::generateCustomTableView($dataResult);
        $dataNextStep = [
            [
                'label' => Trans::getCrmWord('nextStep'),
                'value' => StringFormatter::replaceNewLineToBr($this->getStringParameter('tsk_next_step')),
            ],
        ];
        $contentNextStep = StringFormatter::generateCustomTableView($dataNextStep);
        # Instantiate Portlet Object
        $portlet = new Portlet('DescPtl', Trans::getCrmWord('information'));
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addText($content);
        $portlet->addText($contentResult);
        $portlet->addText($contentNextStep);

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
     * Function to get start confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getStartModal(): Modal
    {
        if ($this->isValidParameter('tsk_start_date_on') === false) {
            $this->setParameter('tsk_start_date_on', date('Y-m-d'));
        }
        if ($this->isValidParameter('tsk_start_time_on') === false) {
            $this->setParameter('tsk_start_time_on', time());
        }
        # Create Fields.
        $modal = new Modal('StartTskMdl', Trans::getCrmWord('startTaskConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'startTask');
        if ($this->getFormAction() === 'startTask' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        # Add field into field set.
        $dateField = $this->Field->getCalendar('tsk_start_date_on', $this->getParameterForModal('tsk_start_date_on', true));
        $timeField = $this->Field->getTime('tsk_start_time_on', $this->getParameterForModal('tsk_start_time_on', true));
        $fieldSet->addField(Trans::getCrmWord('startDate'), $dateField, true);
        $fieldSet->addField(Trans::getCrmWord('startTime'), $timeField, true);
        # Add field into field set.
        $modal->setBtnOkName(Trans::getCrmWord('yesStart'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get finish confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getFinishModal(): Modal
    {
        if ($this->isValidParameter('tsk_finish_date_on') === false) {
            $this->setParameter('tsk_finish_date_on', date('Y-m-d'));
        }
        if ($this->isValidParameter('tsk_finish_time_on') === false) {
            $this->setParameter('tsk_finish_time_on', time());
        }
        # Create Fields.
        $modal = new Modal('FinishTskMdl', Trans::getCrmWord('finishTaskConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'finishTask');
        if ($this->getFormAction() === 'finishTask' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        # Add field into field set.
        $dateField = $this->Field->getCalendar('tsk_finish_date_on', $this->getParameterForModal('tsk_finish_date_on', true));
        $timeField = $this->Field->getTime('tsk_finish_time_on', $this->getParameterForModal('tsk_finish_time_on', true));
        $fieldSet->addField(Trans::getCrmWord('finishDate'), $dateField, true);
        $fieldSet->addField(Trans::getCrmWord('finishTime'), $timeField, true);
        # Add field into field set.
        $modal->setBtnOkName(Trans::getCrmWord('yesFinish'));
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
