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
use App\Frame\Gui\Html\Buttons\Button;
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
use App\Model\Dao\Crm\DealDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\DealDiscussionDao;
use App\Model\Dao\Crm\TaskDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\SystemTypeDao;
use App\Model\Dao\User\UsersDao;

/**
 * Class to handle the creation of detail Deal page
 *
 * @package    app
 * @subpackage Model\Detail\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Deal extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'deal', 'dl_id');
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
        $number = $sn->loadNumber('Deal');
        $colVal = [
            'dl_number' => $number,
            'dl_ss_id' => $this->User->getSsId(),
            'dl_name' => $this->getStringParameter('dl_name'),
            'dl_manager_id' => $this->getIntParameter('dl_manager_id'),
            'dl_rel_id' => $this->getIntParameter('dl_rel_id'),
            'dl_pic_id' => $this->getIntParameter('dl_pic_id'),
            'dl_source_id' => $this->getIntParameter('dl_source_id'),
            'dl_amount' => $this->getFloatParameter('dl_amount'),
            'dl_close_date' => $this->getStringParameter('dl_close_date'),
            'dl_stage_id' => $this->getIntParameter('dl_stage_id'),
            'dl_description' => $this->getStringParameter('dl_description'),
            'dl_sty_id' => $this->getIntParameter('dl_sty_id'),
        ];
        $dealDao = new DealDao();
        $dealDao->doInsertTransaction($colVal);

        return $dealDao->getLastInsertId();
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
        } elseif ($this->getFormAction() === 'doUpdateDiscussion') {
            $colVal = [
                'dld_dl_id' => $this->getDetailReferenceValue(),
                'dld_discussion' => $this->getStringParameter('dld_discussion'),
            ];
            $dldDao = new DealDiscussionDao();
            if ($this->isValidParameter('dld_id') === true) {
                $dldDao->doUpdateTransaction($this->getIntParameter('dld_id'), $colVal);
            } else {
                $dldDao->doInsertTransaction($colVal);
            }
        } else {
            $colVal = [
                'dl_name' => $this->getStringParameter('dl_name'),
                'dl_manager_id' => $this->getIntParameter('dl_manager_id'),
                'dl_rel_id' => $this->getIntParameter('dl_rel_id'),
                'dl_pic_id' => $this->getIntParameter('dl_pic_id'),
                'dl_source_id' => $this->getIntParameter('dl_source_id'),
                'dl_amount' => $this->getFloatParameter('dl_amount'),
                'dl_close_date' => $this->getStringParameter('dl_close_date'),
                'dl_stage_id' => $this->getIntParameter('dl_stage_id'),
                'dl_description' => $this->getStringParameter('dl_description'),
                'dl_sty_id' => $this->getIntParameter('dl_sty_id'),
            ];
            $dealDao = new DealDao();
            $dealDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DealDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isValidParameter('dl_rel_id') === true && $this->isInsert() === true) {
            $relData = RelationDao::getByReferenceAndSystem($this->getIntParameter('dl_rel_id'), $this->User->getSsId());
            $this->setParameter('dl_rel_name', $relData['rel_name']);
            $this->setParameter('dl_rel_id', $relData['rel_id']);
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->isUpdate()) {
            $this->overridePageTitle();
            $this->Tab->addPortlet('general', $this->getDiscussionPortlet());
            $this->Tab->addPortlet('task', $this->getTaskPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('dealcrm', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('dl_name', 3, 256);
            $this->Validation->checkRequire('dl_rel_id');
            $this->Validation->checkRequire('dl_manager_id');
            $this->Validation->checkRequire('dl_stage_id');
            $this->Validation->checkFloat('dl_amount');
            if ($this->isValidParameter('dl_close_date')) {
                $this->Validation->checkDate('dl_close_date');
            }
            $this->Validation->checkRequire('dl_sty_id');
        } elseif ($this->getFormAction() === 'doUploadDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
            $this->Validation->checkRequire('doc_description');
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        } elseif ($this->getFormAction() === 'doUpdateDiscussion') {
            $this->Validation->checkRequire('dld_discussion', 3, 256);
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
        $portletTitle = $this->getDefaultPortletTitle();
        if ($this->isUpdate() === true) {
            $portletTitle = Trans::getCrmWord('deal');
        }
        $portlet = new Portlet('gnrlPtl', $portletTitle);
        $portlet->setGridDimension(12, 12, 12);

        $relationField = $this->Field->getSingleSelect('relation', 'dl_rel_name', $this->getStringParameter('dl_rel_name'));
        $relationField->setHiddenField('dl_rel_id', $this->getIntParameter('dl_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addClearField('dl_pic_name');
        $relationField->addClearField('dl_pic_id');

        $picField = $this->Field->getSingleSelect('contactPerson', 'dl_pic_name', $this->getStringParameter('dl_pic_name'));
        $picField->setHiddenField('dl_pic_id', $this->getIntParameter('dl_pic_id'));
        $picField->addParameterById('cp_rel_id', 'dl_rel_id', Trans::getWord('relation'));
        $picField->setDetailReferenceCode('cp_id');

        $managerField = $this->Field->getSingleSelect('user', 'dl_manager_name', $this->getStringParameter('dl_manager_name'));
        $managerField->setHiddenField('dl_manager_id', $this->getIntParameter('dl_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->addParameter('rel_id', $this->User->getRelId());
        $managerField->setEnableNewButton(false);
        $managerField->setEnableDetailButton(false);

        $sourceField = $this->Field->getSingleSelect('sty', 'dl_source_name', $this->getStringParameter('dl_source_name'));
        $sourceField->setHiddenField('dl_source_id', $this->getIntParameter('dl_source_id'));
        $sourceField->addParameter('sty_group', 'relationsource');
        $sourceField->setEnableNewButton(false);
        $sourceField->setEnableDetailButton(false);

        $stageField = $this->Field->getSingleSelect('sty', 'dl_stage_name', $this->getStringParameter('dl_stage_name'));
        $stageField->setHiddenField('dl_stage_id', $this->getIntParameter('dl_stage_id'));
        $stageField->addParameter('sty_group', 'salesstage');
        $stageField->setEnableNewButton(false);
        $stageField->setEnableDetailButton(false);

        $wheres[] = '(sty.sty_group = \'relationtype\')';
        $wheres[] = '(sty.sty_name IN (\'Customer\', \'Vendor\'))';
        $styData = SystemTypeDao::loadData($wheres);
        $dealTypeField = $this->Field->getRadioGroup('dl_sty_id', $this->getIntParameter('dl_sty_id'));
        foreach ($styData as $data) {
            $dealTypeField->addRadio($data['sty_name'], $data['sty_id']);
        }
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension();
        $fieldSet->addField(Trans::getCrmWord('dealName'), $this->Field->getText('dl_name', $this->getStringParameter('dl_name')), true);
        $fieldSet->addField(Trans::getCrmWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getCrmWord('pic'), $picField);
        $fieldSet->addField(Trans::getCrmWord('manager'), $managerField, true);
        $fieldSet->addField(Trans::getCrmWord('amount'), $this->Field->getNumber('dl_amount', $this->getFloatParameter('dl_amount')), true);
        $fieldSet->addField(Trans::getCrmWord('source'), $sourceField);
        $fieldSet->addField(Trans::getCrmWord('expectedCloseDate'), $this->Field->getCalendar('dl_close_date', $this->getStringParameter('dl_close_date')));
        $fieldSet->addField(Trans::getCrmWord('salesStage'), $stageField, true);
        $fieldSet->addField(Trans::getCrmWord('description'), $this->Field->getTextArea('dl_description', $this->getStringParameter('dl_description')));
        $fieldSet->addField(Trans::getCrmWord('type'), $dealTypeField);
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
        $portlet->setGridDimension(12, 12, 12);
        $table = new TableDatas('discTbl');
        $table->setHeaderRow([
            'dld_discussion' => Trans::getCrmWord('post'),
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('dld_dl_id', $this->getDetailReferenceValue());
        $wheres[] = '(dld.dld_deleted_on IS NULL)';
        $orders[] = 'dld_created_on DESC';
        $tempData = DealDiscussionDao::loadData($wheres, $orders);
        $dldData = [];
        foreach ($tempData as $row) {
            $createdBy = UsersDao::getByReference($row['dld_created_by']);
            $createdDate = DateTimeParser::format($row['dld_created_on'], 'Y-m-d H:i:s', 'd M Y H:i');
            $labelCreator = new LabelDanger($createdBy['us_name'] . ' ' . $createdDate);
            if ($row['dld_created_by'] === $this->User->getId()) {
                $labelCreator = new LabelSuccess($createdBy['us_name'] . ' ' . $createdDate);
            }
            $labelCreator->addAttribute('style', 'font-size: 9pt;');
            $discussionAgg = Trans::getCrmWord('postedBy');
            $discussionAgg .= ' ' . $labelCreator . '<br><br>' . $row['dld_discussion'];
            $row['dld_discussion'] = $discussionAgg;
            $dldData[] = $row;
        }
        $table->addRows($dldData);
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
        $fieldSet->addField(Trans::getCrmWord('discussion'), $this->Field->getTextArea('dld_discussion', $this->getParameterForModal('dld_discussion', $showModal)), true);
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
            'tsk_status_name' => Trans::getCrmWord('status'),
        ]);
        $table->setDisableLineNumber();
        $table->setDisableOrdering();
        $wheres[] = SqlHelper::generateNumericCondition('tsk_dl_id', $this->getDetailReferenceValue());
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
        $btnTsk->setPopup('task/detail', ['tsk_dl_id' => $this->getDetailReferenceValue()]);
        $btnTsk->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addTable($table);
        $portlet->addButton($btnTsk);

        return $portlet;
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('dl_number');
        $status = $this->getStringParameter('dl_stage_name');
        $this->View->setDescription($title . ' | ' . $status);
    }
}
