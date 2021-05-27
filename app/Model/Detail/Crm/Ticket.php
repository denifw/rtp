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
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\TicketDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\TicketDiscussionDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\SystemTypeDao;
use App\Model\Dao\User\UsersDao;

/**
 * Class to handle the creation of detail Ticket page
 *
 * @package    app
 * @subpackage Model\Detail\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Ticket extends AbstractFormModel
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
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('Ticket');
        $status = SystemTypeDao::getByGroupAndName('taskstatus', 'Open');
        $colVal = [
            'tc_ss_id' => $this->User->getSsId(),
            'tc_number' => $number,
            'tc_subject' => $this->getStringParameter('tc_subject'),
            'tc_report_date' => $this->getStringParameter('tc_report_date'),
            'tc_report_time' => $this->getStringParameter('tc_report_time'),
            'tc_rel_id' => $this->getIntParameter('tc_rel_id'),
            'tc_pic_id' => $this->getIntParameter('tc_pic_id'),
            'tc_priority_id' => $this->getIntParameter('tc_priority_id'),
            'tc_status_id' => $status['sty_id'],
            'tc_assign_id' => $this->getIntParameter('tc_assign_id'),
            'tc_description' => $this->getStringParameter('tc_description'),
        ];
        $tcDao = new TicketDao();
        $tcDao->doInsertTransaction($colVal);

        return $tcDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateDiscussion') {
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
        } else {
            $colVal = [
                'tc_subject' => $this->getStringParameter('tc_subject'),
                'tc_report_date' => $this->getStringParameter('tc_report_date'),
                'tc_report_time' => $this->getStringParameter('tc_report_time'),
                'tc_rel_id' => $this->getIntParameter('tc_rel_id'),
                'tc_pic_id' => $this->getIntParameter('tc_pic_id'),
                'tc_priority_id' => $this->getIntParameter('tc_priority_id'),
                'tc_assign_id' => $this->getIntParameter('tc_assign_id'),
                'tc_description' => $this->getStringParameter('tc_description'),
            ];
            $tcDao = new TicketDao();
            $tcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
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
        if ($this->isValidParameter('tc_rel_id') === true && $this->isInsert() === true) {
            $relData = RelationDao::getByReferenceAndSystem($this->getIntParameter('tc_rel_id'), $this->User->getSsId());
            $this->setParameter('tc_rel_name', $relData['rel_name']);
            $this->setParameter('tc_rel_id', $relData['rel_id']);
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->isUpdate()) {
            $this->overridePageTitle();
            $this->Tab->addPortlet('general', $this->getDiscussionPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('ticket', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('tc_subject', 3, 256);
            $this->Validation->checkRequire('tc_rel_id');
            $this->Validation->checkRequire('tc_priority_id');
            $this->Validation->checkRequire('tc_assign_id');
            $this->Validation->checkRequire('tc_report_date');
            $this->Validation->checkRequire('tc_report_time');
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
        if ($this->isUpdate() === true) {
            $portlet->setGridDimension(6, 6);
            $fieldSet->setGridDimension(6, 6);
        }
        $relationField = $this->Field->getSingleSelect('relation', 'tc_rel_name', $this->getStringParameter('tc_rel_name'));
        $relationField->setHiddenField('tc_rel_id', $this->getIntParameter('tc_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addClearField('tc_pic_name');
        $relationField->addClearField('tc_pic_id');
        $picField = $this->Field->getSingleSelect('contactPerson', 'tc_pic_name', $this->getStringParameter('tc_pic_name'));
        $picField->setHiddenField('tc_pic_id', $this->getIntParameter('tc_pic_id'));
        $picField->addParameterById('cp_rel_id', 'tc_rel_id', Trans::getCrmWord('relation'));
        $picField->setDetailReferenceCode('cp_id');
        $priorityField = $this->Field->getSingleSelect('sty', 'tc_priority_name', $this->getStringParameter('tc_priority_name'));
        $priorityField->setHiddenField('tc_priority_id', $this->getIntParameter('tc_priority_id'));
        $priorityField->addParameter('sty_group', 'taskpriority');
        $priorityField->setEnableNewButton(false);
        $priorityField->setEnableDetailButton(false);
        $assignField = $this->Field->getSingleSelect('user', 'tc_assign_name', $this->getStringParameter('tc_assign_name'));
        $assignField->setHiddenField('tc_assign_id', $this->getIntParameter('tc_assign_id'));
        $assignField->addParameter('ss_id', $this->User->getSsId());
        $assignField->addParameter('rel_id', $this->User->getRelId());
        $assignField->setEnableNewButton(false);
        $assignField->setEnableDetailButton(false);
        # Add field to field set
        $fieldSet->addField(Trans::getCrmWord('subject'), $this->Field->getText('tc_subject', $this->getStringParameter('tc_subject')), true);
        $fieldSet->addField(Trans::getCrmWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getCrmWord('pic'), $picField);
        $fieldSet->addField(Trans::getCrmWord('reportDate'), $this->Field->getCalendar('tc_report_date', $this->getStringParameter('tc_report_date')), true);
        $fieldSet->addField(Trans::getCrmWord('reportTime'), $this->Field->getTime('tc_report_time', $this->getStringParameter('tc_report_time')), true);
        $fieldSet->addField(Trans::getCrmWord('priority'), $priorityField, true);
        $fieldSet->addField(Trans::getCrmWord('assignTo'), $assignField, true);
        $fieldSet->addField(Trans::getCrmWord('description'), $this->Field->getTextArea('tc_description', $this->getStringParameter('tc_description')));
        $portlet->addFieldSet($fieldSet);

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
