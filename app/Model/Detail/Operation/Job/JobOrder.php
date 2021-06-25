<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Operation\Job;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Operation\Job\JobEmployeeDao;
use App\Model\Dao\Operation\Job\jobOrderDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Operation\Job\JobOrderTaskDao;

/**
 * Class to handle the creation of detail jobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrder extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jo', 'jo_id');
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
        $joNumber = $sn->loadNumber('JO', $this->User->Relation->getOfficeId(), $this->getStringParameter('jo_rel_id', ''));
        $fee = null;
        $value = null;
        if ($this->getStringParameter('jo_srv_code') === 'caf') {
            $fee = $this->getFloatParameter('jo_fee');
        } else {
            $value = $this->getFloatParameter('jo_value');
        }
        $colVal = [
            'jo_ss_id' => $this->User->getSsId(),
            'jo_number' => $joNumber,
            'jo_name' => $this->getStringParameter('jo_name'),
            'jo_rel_id' => $this->getStringParameter('jo_rel_id'),
            'jo_cp_id' => $this->getStringParameter('jo_cp_id'),
            'jo_srv_id' => $this->getStringParameter('jo_srv_id'),
            'jo_fee' => $fee,
            'jo_value' => $value,
            'jo_estimation_start' => $this->getStringParameter('jo_estimation_start'),
            'jo_estimation_end' => $this->getStringParameter('jo_estimation_end'),
            'jo_address' => $this->getStringParameter('jo_address'),
            'jo_dtc_id' => $this->getStringParameter('jo_dtc_id'),
            'jo_reference' => $this->getStringParameter('jo_reference'),
            'jo_us_id' => $this->getStringParameter('jo_us_id'),
        ];
        $joDao = new JobOrderDao();
        $joDao->doInsertTransaction($colVal);
        return $joDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $fee = null;
            $value = null;
            if ($this->getStringParameter('jo_srv_code') === 'caf') {
                $fee = $this->getFloatParameter('jo_fee');
            } else {
                $value = $this->getFloatParameter('jo_value');
            }
            $colVal = [
                'jo_name' => $this->getStringParameter('jo_name'),
                'jo_rel_id' => $this->getStringParameter('jo_rel_id'),
                'jo_cp_id' => $this->getStringParameter('jo_cp_id'),
                'jo_srv_id' => $this->getStringParameter('jo_srv_id'),
                'jo_fee' => $fee,
                'jo_value' => $value,
                'jo_estimation_start' => $this->getStringParameter('jo_estimation_start'),
                'jo_estimation_end' => $this->getStringParameter('jo_estimation_end'),
                'jo_address' => $this->getStringParameter('jo_address'),
                'jo_dtc_id' => $this->getStringParameter('jo_dtc_id'),
                'jo_reference' => $this->getStringParameter('jo_reference'),
                'jo_us_id' => $this->getStringParameter('jo_us_id'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $joDao = new JobOrderDao();
            $joDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        } elseif ($this->getFormAction() === 'doUpdateTask') {
            $jotColVal = [
                'jot_jo_id' => $this->getDetailReferenceValue(),
                'jot_description' => $this->getStringParameter('jot_description'),
                'jot_notes' => $this->getStringParameter('jot_notes'),
                'jot_portion' => $this->getFloatParameter('jot_portion'),
            ];
            $jotDao = new JobOrderTaskDao();
            if ($this->isValidParameter('jot_id') === true) {
                $jotColVal['jot_progress'] = 0;
                $jotDao->doUpdateTransaction($this->getStringParameter('jot_id'), $jotColVal);
            } else {
                $jotColVal['jot_progress'] = 0;
                $jotDao->doInsertTransaction($jotColVal);
            }

        } elseif ($this->getFormAction() === 'doDeleteTask') {
            $jotDao = new JobOrderTaskDao();
            $jotDao->doDeleteTransaction($this->getStringParameter('jot_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateWorker') {
            $jemColVal = [
                'jem_jo_id' => $this->getDetailReferenceValue(),
                'jem_em_id' => $this->getStringParameter('jem_em_id'),
                'jem_shift_one' => $this->getFloatParameter('jem_shift_one'),
                'jem_shift_two' => $this->getFloatParameter('jem_shift_two'),
                'jem_shift_three' => $this->getFloatParameter('jem_shift_three'),
                'jem_type' => $this->getStringParameter('jem_type'),
            ];
            $jemDao = new JobEmployeeDao();
            if ($this->isValidParameter('jem_id') === true) {
                $jemDao->doUpdateTransaction($this->getStringParameter('jem_id'), $jemColVal);
            } else {
                $jemDao->doInsertTransaction($jemColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteWorker') {
            $jemDao = new JobEmployeeDao();
            $jemDao->doDeleteTransaction($this->getStringParameter('jem_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return jobOrderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->overrideTitle();
            $this->Tab->addPortlet('general', $this->getTaskPortlet());
            $this->Tab->addPortlet('workers', $this->getWorkersPortlet());

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
            $this->Validation->checkRequire('jo_name', 2, 256);
            $this->Validation->checkRequire('jo_srv_id');
            $this->Validation->checkRequire('jo_rel_id');
            $this->Validation->checkRequire('jo_address', 2, 256);
            $this->Validation->checkRequire('jo_dtc_id');
            $this->Validation->checkRequire('jo_estimation_start');
            $this->Validation->checkRequire('jo_estimation_end');
            $this->Validation->checkDate('jo_estimation_start');
            $this->Validation->checkDate('jo_estimation_end');
            if ($this->isValidParameter('jo_srv_code') === true) {
                if ($this->getStringParameter('jo_srv_code') === 'caf') {
                    $this->Validation->checkRequire('jo_fee');
                    $this->Validation->checkFloat('jo_fee', 0);
                } else {
                    $this->Validation->checkRequire('jo_value');
                    $this->Validation->checkFloat('jo_value');

                }
            }
        } elseif ($this->getFormAction() === 'doUpdateTask') {
            $this->Validation->checkRequire('jot_description', 2, 256);
            $this->Validation->checkMaxLength('jot_notes', 256);
            if ($this->isValidParameter('jot_portion') === true) {
                $this->Validation->checkFloat('jot_portion', 0, 100);
            }
        } elseif ($this->getFormAction() === 'doDeleteTask') {
            $this->Validation->checkRequire('jot_id_del');
        } elseif ($this->getFormAction() === 'doUpdateWorker') {
            $this->Validation->checkRequire('jem_em_id');
            $this->Validation->checkRequire('jem_type');
            $this->Validation->checkRequire('jem_shift_one');
            $this->Validation->checkFloat('jem_shift_one', 1);
            if ($this->isValidParameter('jem_shift_two') === true) {
                $this->Validation->checkFloat('jem_shift_two', 1);
            }
            if ($this->isValidParameter('jem_shift_three') === true) {
                $this->Validation->checkFloat('jem_shift_three', 1);
            }
            $this->Validation->checkUnique('jem_em_id', 'job_employee', [
                'jem_id' => $this->getStringParameter('jem_id')
            ], [
                'jem_jo_id' => $this->getDetailReferenceValue(),
                'jem_deleted_on' => null
            ]);
        } elseif ($this->getFormAction() === 'doDeleteWorker') {
            $this->Validation->checkRequire('jem_id_del');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to override title
     *
     * @return void
     */
    private function overrideTitle(): void
    {
        # Override Title
        $status = JobOrderDao::getStatus($this->getAllParameters());
        $this->View->setDescription($this->getStringParameter('jo_number') . ' - ' . $status);

        # Show Delete Reason
        $this->addDeletedMessage('jo');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('JoPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension();

        # Relation
        $relField = $this->Field->getSingleSelect('rel', 'jo_relation', $this->getStringParameter('jo_relation'));
        $relField->setHiddenField('jo_rel_id', $this->getStringParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('jo_contact_person');
        $relField->addClearField('jo_cp_id');

        # Contact Person
        $cpField = $this->Field->getSingleSelect('cp', 'jo_contact_person', $this->getStringParameter('jo_contact_person'));
        $cpField->setHiddenField('jo_cp_id', $this->getStringParameter('jo_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $cpField->setDetailReferenceCode('cp_id');

        # Service
        $srvField = $this->Field->getSingleSelect('srv', 'jo_service', $this->getStringParameter('jo_service'));
        $srvField->setHiddenField('jo_srv_id', $this->getStringParameter('jo_srv_id'));
        $srvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srvField->setEnableNewButton(false);
        $srvField->setAutoCompleteFields([
            'jo_srv_code' => 'srv_code'
        ]);
        # District
        $districtField = $this->Field->getSingleSelectTable('dtc', 'jo_district', $this->getStringParameter('jo_district'), 'loadSingleSelectTableData');
        $districtField->setHiddenField('jo_dtc_id', $this->getStringParameter('jo_dtc_id'));
        $districtField->setTableColumns([
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);
        $districtField->setFilters([
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('district'),
        ]);
        $districtField->setAutoCompleteFields([
            'jo_state' => 'dtc_state',
            'jo_city' => 'dtc_city',
        ]);
        $districtField->setValueCode('dtc_id');
        $districtField->setLabelCode('dtc_name');
        $districtField->addParameter('ssr_ss_id', $this->User->getSsId());
        $this->View->addModal($districtField->getModal());

        $ctyField = $this->Field->getText('jo_city', $this->getStringParameter('jo_city'));
        $ctyField->setReadOnly();
        $sttField = $this->Field->getText('jo_state', $this->getStringParameter('jo_state'));
        $sttField->setReadOnly();


        # Users
        $usField = $this->Field->getSingleSelect('us', 'jo_manager', $this->getStringParameter('jo_manager'));
        $usField->setHiddenField('jo_us_id', $this->getStringParameter('jo_us_id'));
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->setEnableNewButton(false);


        # Add field to field set
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('jo_name', $this->getStringParameter('jo_name')), true);
        $fieldSet->addField(Trans::getWord('service'), $srvField, true);
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('pic'), $cpField);
        $fieldSet->addField(Trans::getWord('marginOfCost') . ' (%)', $this->Field->getNumber('jo_fee', $this->getFloatParameter('jo_fee')));
        $fieldSet->addField(Trans::getWord('projectValue') . ' (' . $this->User->Settings->getCurrencyIso() . ')', $this->Field->getNumber('jo_value', $this->getFloatParameter('jo_value')));
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('jo_address', $this->getStringParameter('jo_address')), true);
        $fieldSet->addField(Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('city'), $ctyField);
        $fieldSet->addField(Trans::getWord('state'), $sttField);
        $fieldSet->addField(Trans::getWord('estStartDate'), $this->Field->getCalendar('jo_estimation_start', $this->getStringParameter('jo_estimation_start')), true);
        $fieldSet->addField(Trans::getWord('estFinishDate'), $this->Field->getCalendar('jo_estimation_end', $this->getStringParameter('jo_estimation_end')));
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $fieldSet->addField(Trans::getWord('manager'), $usField);
        $fieldSet->addHiddenField($this->Field->getHidden('jo_srv_code', $this->getStringParameter('jo_srv_code')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
            if ($this->isAllowUpdate() === true) {
                if ($this->isStarted() === false) {
                    $this->setEnableDeleteButton(true);
                } else {
                    $this->setEnableDeleteButton(false);
                }
                if ($this->isFinished() === true) {
                    # Show Archive Button
                }
            } else {
                $this->setDisableUpdate(true);
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to check is allow update
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->isDeleted() === false && $this->isArchived() === false;
    }

    /**
     * Function to check is deleted
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('jo_deleted_on');
    }

    /**
     * Function to check is archive
     *
     * @return bool
     */
    private function isArchived(): bool
    {
        return $this->isValidParameter('jo_joa_id');
    }

    /**
     * Function to check is archive
     *
     * @return bool
     */
    private function isFinished(): bool
    {
        return $this->isValidParameter('jo_finish_on');
    }

    /**
     * Function to check is archive
     *
     * @return bool
     */
    private function isStarted(): bool
    {
        return $this->isValidParameter('jo_start_on');
    }

    /**
     * Function to check is archive
     *
     * @return bool
     */
    private function isPublished(): bool
    {
        return $this->isValidParameter('jo_publish_on');
    }


    /**
     * Function to get the Task Portlet.
     *
     * @return Portlet
     */
    private function getTaskPortlet(): Portlet
    {
        $modalUpdate = $this->getTaskModal();
        $this->View->addModal($modalUpdate);
        $modalDelete = $this->getTaskDeleteModal();
        $this->View->addModal($modalDelete);

        $tbl = new Table('JotTbl');
        $tbl->setHeaderRow([
            'jot_description' => Trans::getWord('description'),
            'jot_notes' => Trans::getWord('notes'),
            'jot_portion' => Trans::getWord('portion') . ' (%)',
            'jot_progress' => Trans::getWord('progress') . ' (%)',
        ]);
        $tbl->addRows(JobOrderTaskDao::getByJobId($this->getDetailReferenceValue()));
        $tbl->setColumnType('jot_portion', 'float');
        $tbl->setColumnType('jot_progress', 'float');

        # Instantiate Portlet Object
        $portlet = new Portlet('JoJotPtl', Trans::getWord('jobDetail'));
        $portlet->setGridDimension(12, 12, 12);
        # Set Action
        if ($this->isAllowUpdate() === true) {
            $tbl->setUpdateActionByModal($modalUpdate, 'jot', 'getById', ['jot_id']);
            $tbl->setDeleteActionByModal($modalDelete, 'jot', 'getByIdForDelete', ['jot_id']);
            $btn = new ModalButton('JotBtn', Trans::getWord('add'), $modalUpdate->getModalId());
            $btn->btnPrimary()->pullRight()->btnMedium()->setIcon(Icon::Plus);
            $portlet->addButton($btn);
        }
        $portlet->addTable($tbl);
        return $portlet;
    }

    /**
     * Function to get the Task Modal.
     *
     * @return Modal
     */
    private function getTaskModal(): Modal
    {
        $mdl = new Modal('JotMdl', Trans::getWord('jobDetail'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doUpdateTask');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateTask' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('jot_description', $this->getParameterForModal('jot_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getText('jot_notes', $this->getParameterForModal('jot_notes', $showModal)));
        $fieldSet->addField(Trans::getWord('portion') . ' (%)', $this->Field->getNumber('jot_portion', $this->getParameterForModal('jot_portion', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jot_id', $this->getParameterForModal('jot_id', $showModal)));

        $mdl->addFieldSet($fieldSet);
        return $mdl;
    }

    /**
     * Function to get the Task Delete Modal.
     *
     * @return Modal
     */
    private function getTaskDeleteModal(): Modal
    {
        $mdl = new Modal('JotDelMdl', Trans::getWord('deleteJobDetail'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doDeleteTask');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteTask' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('jot_description_del', $this->getParameterForModal('jot_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getText('jot_notes_del', $this->getParameterForModal('jot_notes_del', $showModal)));
        $fieldSet->addField(Trans::getWord('portion'), $this->Field->getNumber('jot_portion_del', $this->getParameterForModal('jot_portion_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jot_id_del', $this->getParameterForModal('jot_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $mdl->addText($p);
        $mdl->setBtnOkName(Trans::getWord('yesDelete'));
        $mdl->addFieldSet($fieldSet);

        return $mdl;
    }

    /**
     * Function to get the Task Portlet.
     *
     * @return Portlet
     */
    private function getWorkersPortlet(): Portlet
    {
        $modalUpdate = $this->getWorkerModal();
        $this->View->addModal($modalUpdate);
        $modalDelete = $this->getWorkerDeleteModal();
        $this->View->addModal($modalDelete);

        $tbl = new Table('JemTbl');
        $tbl->setHeaderRow([
            'jem_number' => Trans::getWord('id'),
            'jem_name' => Trans::getWord('name'),
            'jem_title' => Trans::getWord('title'),
            'jem_type' => Trans::getWord('costType'),
            'jem_shift_one' => Trans::getWord('shift1'),
            'jem_shift_two' => Trans::getWord('shift2'),
            'jem_shift_three' => Trans::getWord('shift3'),
        ]);
        $data = JobEmployeeDao::getByJoId($this->getDetailReferenceValue());
        $rows = [];
        foreach ($data as $row) {
            if ($row['jem_type'] === 'H') {
                $row['jem_type'] = new LabelInfo(Trans::getWord('hour'));
            } else {
                $row['jem_type'] = new LabelPrimary(Trans::getWord('shift'));
            }
            $rows[] = $row;
        }
        $tbl->addRows($rows);
        $tbl->setColumnType('jem_shift_one', 'float');
        $tbl->setColumnType('jem_shift_two', 'float');
        $tbl->setColumnType('jem_shift_three', 'float');
        $tbl->addColumnAttribute('jem_number', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('jem_type', 'style', 'text-align: center;');

        # Instantiate Portlet Object
        $portlet = new Portlet('JoJemPtl', Trans::getWord('workers'));
        $portlet->setGridDimension(12, 12, 12);
        # Set Action
        if ($this->isAllowUpdate() === true) {
            $tbl->setUpdateActionByModal($modalUpdate, 'jem', 'getById', ['jem_id']);
            $tbl->setDeleteActionByModal($modalDelete, 'jem', 'getByIdForDelete', ['jem_id']);
            $btn = new ModalButton('JemBtn', Trans::getWord('add'), $modalUpdate->getModalId());
            $btn->btnPrimary()->pullRight()->btnMedium()->setIcon(Icon::Plus);
            $portlet->addButton($btn);
        }
        $portlet->addTable($tbl);
        return $portlet;
    }

    /**
     * Function to get the Task Modal.
     *
     * @return Modal
     */
    private function getWorkerModal(): Modal
    {
        $mdl = new Modal('JemMdl', Trans::getWord('workers'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doUpdateWorker');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateWorker' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $emField = $this->Field->getSingleSelect('em', 'jem_name', $this->getParameterForModal('jem_name', $showModal));
        $emField->setHiddenField('jem_em_id', $this->getParameterForModal('jem_em_id', $showModal));
        $emField->setEnableNewButton('false');
        $emField->addParameter('em_ss_id', $this->User->getSsId());

        # Type
        $typeField = $this->Field->getRadioGroup('jem_type', $this->getParameterForModal('jem_type', true));
        $typeField->addRadio(Trans::getWord('hour'), 'H');
        $typeField->addRadio(Trans::getWord('shift'), 'S');

        $fieldSet->addField(Trans::getWord('name'), $emField, true);
        $fieldSet->addField(Trans::getWord('costType'), $typeField, true);
        $fieldSet->addField(Trans::getWord('shift1'), $this->Field->getNumber('jem_shift_one', $this->getParameterForModal('jm_shift_one', $showModal)), true);
        $fieldSet->addField(Trans::getWord('shift2'), $this->Field->getNumber('jem_shift_two', $this->getParameterForModal('jm_shift_two', $showModal)), true);
        $fieldSet->addField(Trans::getWord('shift3'), $this->Field->getNumber('jem_shift_three', $this->getParameterForModal('jm_shift_three', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('jem_id', $this->getParameterForModal('jem_id', $showModal)));

        $mdl->addFieldSet($fieldSet);
        return $mdl;
    }

    /**
     * Function to get the Task Delete Modal.
     *
     * @return Modal
     */
    private function getWorkerDeleteModal(): Modal
    {
        $mdl = new Modal('JemDelMdl', Trans::getWord('deleteWorker'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doDeleteWorker');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteWorker' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('jem_name_del', $this->getParameterForModal('jem_name_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('costType'), $this->Field->getText('jem_type_del', $this->getParameterForModal('jem_type_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('shift1'), $this->Field->getNumber('jem_shift_one_del', $this->getParameterForModal('jm_shift_one_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('shift2'), $this->Field->getNumber('jem_shift_two_del', $this->getParameterForModal('jm_shift_two_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('shift3'), $this->Field->getNumber('jem_shift_three_del', $this->getParameterForModal('jm_shift_three_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('jem_id_del', $this->getParameterForModal('jem_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $mdl->addText($p);
        $mdl->setBtnOkName(Trans::getWord('yesDelete'));
        $mdl->addFieldSet($fieldSet);

        return $mdl;
    }

}
