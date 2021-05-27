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
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Crm\TicketDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Crm\TicketDiscussionDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\SystemTypeDao;
use App\Model\Dao\User\UsersDao;

/**
 * Class to handle the creation of detail Ticket page
 *
 * @package    app
 * @subpackage Model\Viewer\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Ticket extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ticket', 'tc_id');
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
                'tc_status_id' => $status['sty_id'],
                'tc_start_by' => $this->User->getId(),
                'tc_start_on' => $this->getStringParameter('tc_start_date_on') . ' ' . $this->getStringParameter('tc_start_time_on'),
            ];
            $tcDao = new TicketDao();
            $tcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'finishTask') {
            $status = SystemTypeDao::getByGroupAndName('taskstatus', 'Finish');
            $colVal = [
                'tc_status_id' => $status['sty_id'],
                'tc_finish_by' => $this->User->getId(),
                'tc_finish_on' => $this->getStringParameter('tc_finish_date_on') . ' ' . $this->getStringParameter('tc_finish_time_on'),
            ];
            $tcDao = new TicketDao();
            $tcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doUpdateDiscussion') {
            $colVal = [
                'tcd_tc_id' => $this->getDetailReferenceValue(),
                'tcd_discussion' => $this->getStringParameter('tcd_discussion'),
            ];
            $tcdDao = new TicketDiscussionDao();
            if ($this->isValidParameter('tcd_id') === true) {
                $tcdDao->doUpdateTransaction($this->getIntParameter('tcd_id'), $colVal);
            } else {
                $tcdDao->doInsertTransaction($colVal);
            }
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
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return TicketDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->overridePageTitle();
        $this->Tab->addPortlet('general', $this->getDiscussionPortlet());
        $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('ticket', $this->getDetailReferenceValue()));
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'startTask') {
            $this->Validation->checkRequire('tc_start_date_on');
            $this->Validation->checkRequire('tc_start_time_on');
        } elseif ($this->getFormAction() === 'finishTask') {
            $this->Validation->checkRequire('tc_finish_date_on');
            $this->Validation->checkRequire('tc_finish_time_on');
        } elseif ($this->getFormAction() === 'doUpdateDiscussion') {
            $this->Validation->checkRequire('tcd_discussion', 3, 256);
        } elseif ($this->getFormAction() === 'doUploadDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
            $this->Validation->checkRequire('doc_description');
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
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
        if ($this->isValidParameter('tc_start_on') === false) {
            $modal = $this->getStartModal();
            $this->View->addModal($modal);
            $btnStart = new ModalButton('btnStart', Trans::getCrmWord('startTask'), $modal->getModalId());
            $btnStart->setIcon(Icon::YoutubePlay)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnStart);
        } elseif ($this->isValidParameter('tc_start_on') === true) {
            # Create button print MOM
            # Create button finish task
            if ($this->isValidParameter('tc_finish_on') === false) {
                $modal = $this->getFinishModal();
                $this->View->addModal($modal);
                $btnFinish = new ModalButton('btnFinish', Trans::getCrmWord('finishTask'), $modal->getModalId());
                $btnFinish->btnSuccess();
                $btnFinish->pullRight();
                $btnFinish->btnMedium();
                $this->View->addButtonAtTheBeginning($btnFinish);
            }
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        $reportDateTime = DateTimeParser::format($this->getStringParameter('tc_report_date') . ' ' . $this->getStringParameter('tc_report_time'), 'Y-m-d H:i:s', 'd M Y H:i');
        $data = [
            [
                'label' => Trans::getCrmWord('number'),
                'value' => $this->getStringParameter('tc_number'),
            ],
            [
                'label' => Trans::getCrmWord('subject'),
                'value' => $this->getStringParameter('tc_subject'),
            ],
            [
                'label' => Trans::getCrmWord('reportDate'),
                'value' => $reportDateTime,
            ],
            [
                'label' => Trans::getCrmWord('relation'),
                'value' => $this->getStringParameter('tc_rel_name'),
            ],
            [
                'label' => Trans::getCrmWord('pic'),
                'value' => $this->getStringParameter('tc_pic_name'),
            ],
            [
                'label' => Trans::getCrmWord('assignTo'),
                'value' => $this->getStringParameter('tc_assign_name'),
            ],
            [
                'label' => Trans::getCrmWord('priority'),
                'value' => $this->getStringParameter('tc_priority_name'),
            ],
            [
                'label' => Trans::getCrmWord('status'),
                'value' => $this->getStringParameter('tc_status_name'),
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
     * Function to get discussion portlet
     *
     * @return Portlet
     */
    private function getDiscussionPortlet(): Portlet
    {
        $modal = $this->getDiscussionModal();
        $this->View->addModal($modal);
        $portlet = new Portlet('discPtl', Trans::getCrmWord('discussion'));
        $portlet->setGridDimension(6, 6);
        $table = new TableDatas('discTbl');
        $table->setHeaderRow([
            'tcd_discussion' => Trans::getCrmWord('post'),
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('tcd_tc_id', $this->getDetailReferenceValue());
        $wheres[] = '(tcd.tcd_deleted_on IS NULL)';
        $orders[] = 'tcd_created_on DESC';
        $tempData = TicketDiscussionDao::loadData($wheres, $orders);
        $tcdData = [];
        foreach ($tempData as $row) {
            $createdBy = UsersDao::getByReference($row['tcd_created_by']);
            $createdDate = DateTimeParser::format($row['tcd_created_on'], 'Y-m-d H:i:s', 'd M Y H:i');
            $labelCreator = new LabelDanger($createdBy['us_name'] . ' ' . $createdDate);
            if ($row['tcd_created_by'] === $this->User->getId()) {
                $labelCreator = new LabelSuccess($createdBy['us_name'] . ' ' . $createdDate);
            }
            $labelCreator->addAttribute('style', 'font-size: 9pt;');
            $discussionAgg = Trans::getCrmWord('postedBy');
            $discussionAgg .= ' ' . $labelCreator . '<br><br>' . $row['tcd_discussion'];
            $row['tcd_discussion'] = $discussionAgg;
            $tcdData[] = $row;
        }
        $table->addRows($tcdData);
        $btnDiscMdl = new ModalButton('btnDiscMdl', Trans::getCrmWord('post'), $modal->getModalId());
        $btnDiscMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addTable($table);
        $portlet->addButton($btnDiscMdl);

        return $portlet;
    }

    /**
     * Function to get discussion modal
     *
     * @return Modal
     */
    private function getDiscussionModal(): Modal
    {
        $modal = new Modal('discMdl', Trans::getCrmWord('discussion'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDiscussion');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDiscussion' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('discussion'), $this->Field->getTextArea('tcd_discussion', $this->getParameterForModal('tcd_discussion', $showModal)), true);
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
        if ($this->isValidParameter('tc_start_date_on') === false) {
            $this->setParameter('tc_start_date_on', date('Y-m-d'));
        }
        if ($this->isValidParameter('tc_start_time_on') === false) {
            $this->setParameter('tc_start_time_on', time());
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
        $dateField = $this->Field->getCalendar('tc_start_date_on', $this->getParameterForModal('tc_start_date_on', true));
        $timeField = $this->Field->getTime('tc_start_time_on', $this->getParameterForModal('tc_start_time_on', true));
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
        if ($this->isValidParameter('tc_finish_date_on') === false) {
            $this->setParameter('tc_finish_date_on', date('Y-m-d'));
        }
        if ($this->isValidParameter('tc_finish_time_on') === false) {
            $this->setParameter('tc_finish_time_on', time());
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
        $dateField = $this->Field->getCalendar('tc_finish_date_on', $this->getParameterForModal('tc_finish_date_on', true));
        $timeField = $this->Field->getTime('tc_finish_time_on', $this->getParameterForModal('tc_finish_time_on', true));
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
        $title = $this->getStringParameter('tc_number');
        $status = new LabelGray(Trans::getCrmWord('open'));
        if ($this->isValidParameter('tc_start_on') === true && $this->isValidParameter('tc_finish_on') === false) {
            $status = new LabelWarning(Trans::getCrmWord('inProgress'));
        } elseif ($this->isValidParameter('tc_finish_on') === true) {
            $status = new LabelSuccess(Trans::getCrmWord('finish'));
        }
        $this->View->setDescription($title . ' | ' . $status);
    }
}
