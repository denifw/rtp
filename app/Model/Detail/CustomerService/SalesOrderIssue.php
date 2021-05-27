<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\CustomerService;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderIssueDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Job\JobOrderDao;

/**
 * Class to handle the creation of detail SalesOrderIssue page
 *
 * @package    app
 * @subpackage Model\Detail\CustomerService
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderIssue extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'soi', 'soi_id');
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
        $number = $sn->loadNumber('SoIssue', $this->User->Relation->getOfficeId());
        $colVal = [
            'soi_ss_id' => $this->User->getSsId(),
            'soi_number' => $number,
            'soi_rel_id' => $this->getIntParameter('soi_rel_id'),
            'soi_pic_id' => $this->getIntParameter('soi_pic_id'),
            'soi_priority_id' => $this->getIntParameter('soi_priority_id'),
            'soi_srv_id' => $this->getIntParameter('soi_srv_id'),
            'soi_subject' => $this->getStringParameter('soi_subject'),
            'soi_pic_field_id' => $this->getIntParameter('soi_pic_field_id'),
            'soi_so_id' => $this->getIntParameter('soi_so_id'),
            'soi_report_date' => $this->getStringParameter('soi_report_date'),
            'soi_description' => $this->getStringParameter('soi_description'),
            'soi_jo_id' => $this->getIntParameter('soi_jo_id'),
            'soi_assign_id' => $this->getIntParameter('soi_assign_id'),
        ];

        $soiDao = new SalesOrderIssueDao();
        $soiDao->doInsertTransaction($colVal);
        return $soiDao->getLastInsertId();
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
                'soi_rel_id' => $this->getIntParameter('soi_rel_id'),
                'soi_pic_id' => $this->getIntParameter('soi_pic_id'),
                'soi_priority_id' => $this->getIntParameter('soi_priority_id'),
                'soi_srv_id' => $this->getIntParameter('soi_srv_id'),
                'soi_subject' => $this->getStringParameter('soi_subject'),
                'soi_pic_field_id' => $this->getIntParameter('soi_pic_field_id'),
                'soi_so_id' => $this->getIntParameter('soi_so_id'),
                'soi_report_date' => $this->getStringParameter('soi_report_date'),
                'soi_description' => $this->getStringParameter('soi_description'),
                'soi_jo_id' => $this->getIntParameter('soi_jo_id'),
                'soi_assign_id' => $this->getIntParameter('soi_assign_id'),
                'soi_solution' => $this->getStringParameter('soi_solution'),
                'soi_note' => $this->getStringParameter('soi_note'),
            ];
            $soiDao = new SalesOrderIssueDao();
            $soiDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === "doReopen") {
            $colVal = [
                'soi_finish_by' => null,
                'soi_finish_on' => null,
            ];
            $soiDao = new SalesOrderIssueDao();
            $soiDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction()) {
            $soiDao = new SalesOrderIssueDao();
            $soiDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SalesOrderIssueDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true && $this->isValidParameter('so_id') === true) {
            $salesOrder = SalesOrderDao::getByReference($this->getIntParameter('so_id'));
            if (empty($salesOrder) === false) {
                $this->setParameter('soi_so_number', $salesOrder['so_number']);
                $this->setParameter('soi_so_id', $salesOrder['so_id']);
                $this->setParameter('soi_rel_name', $salesOrder['so_customer']);
                $this->setParameter('soi_rel_id', $salesOrder['so_rel_id']);
                $this->setParameter('soi_pic_name', $salesOrder['so_pic_customer']);
                $this->setParameter('soi_pic_id', $salesOrder['so_pic_id']);
            }
        }

        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getIssuePortlet());

        if ($this->isUpdate()) {
            $this->overrideTitle();
            $this->Tab->addPortlet('general', $this->getSolutionPortlet());
            if ($this->isValidParameter('soi_finish_on') === true || $this->isValidParameter('soi_deleted_on') === true) {
                $this->setDisableUpdate();
            }
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
            $this->Validation->checkRequire('soi_rel_id');
            $this->Validation->checkRequire('soi_priority_id');
            $this->Validation->checkRequire('soi_srv_id');
            $this->Validation->checkRequire('soi_subject', '2', '256');
            $this->Validation->checkRequire('soi_so_id');
            $this->Validation->checkRequire('soi_report_date');
            $this->Validation->checkRequire('soi_description');
            $this->Validation->checkRequire('soi_assign_id');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to override title page
     *
     * @return void
     */
    private function overrideTitle(): void
    {
        if ($this->isValidParameter('soi_deleted_on') === true) {
            $status = new LabelDanger(Trans::getWord('deleted'));
            $date = new DateTimeParser();
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', '', [
                'user' => $this->getStringParameter('soi_deleted_by'),
                'time' => $date->formatDateTime($this->getStringParameter('soi_deleted_on')),
                'reason' => $this->getStringParameter('soi_deleted_reason'),
            ]));

        } elseif ($this->isValidParameter('soi_finish_on') === true) {
            $status = new LabelSuccess(Trans::getWord('closed'));
        } else {
            $status = new LabelPrimary(Trans::getWord('open'));
        }
        $this->View->setDescription($this->getStringParameter('soi_number') . ' | ' . $status);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('SoiGenPtl', Trans::getWord('general'));
        $portlet->setGridDimension(8, 12, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        #Create Relation Field
        $soiRelField = $this->Field->getSingleSelect('relation', 'soi_rel_name', $this->getStringParameter('soi_rel_name'));
        $soiRelField->setHiddenField('soi_rel_id', $this->getIntParameter('soi_rel_id'));
        $soiRelField->addParameter('rel_ss_id', $this->User->getSsId());
        $soiRelField->setEnableNewButton(false);
        $soiRelField->addClearField('soi_pic_name');
        $soiRelField->addClearField('soi_pic_id');
        $soiRelField->addClearField('soi_so_number');
        $soiRelField->addClearField('soi_so_id');
        $soiRelField->addClearField('soi_srv_name');
        $soiRelField->addClearField('soi_srv_id');
        $soiRelField->addClearField('soi_jo_number');
        $soiRelField->addClearField('soi_jo_id');

        #Create Contact Field
        $soiPicField = $this->Field->getSingleSelect('contactPerson', 'soi_pic_name', $this->getStringParameter('soi_pic_name'));
        $soiPicField->setHiddenField('soi_pic_id', $this->getIntParameter('soi_pic_id'));
        $soiPicField->addParameterById('cp_rel_id', 'soi_rel_id', Trans::getWord('customer'));
        $soiPicField->setDetailReferenceCode('cp_id');

        #Create Sales Order Field
        $soiSoField = $this->Field->getSingleSelect('so', 'soi_so_number', $this->getStringParameter('soi_so_number'));
        $soiSoField->setHiddenField('soi_so_id', $this->getIntParameter('soi_so_id'));
        $soiSoField->addParameter('so_ss_id', $this->User->getSsId());
        $soiSoField->addParameterById('so_rel_id', 'soi_rel_id', Trans::getWord('relation'));
        $soiSoField->setEnableNewButton(false);
        $soiSoField->addClearField('soi_srv_name');
        $soiSoField->addClearField('soi_srv_id');
        $soiSoField->addClearField('soi_jo_number');
        $soiSoField->addClearField('soi_jo_id');

        #Create Service Field
        $soiSrvField = $this->Field->getSingleSelect('service', 'soi_srv_name', $this->getStringParameter('soi_srv_name'));
        $soiSrvField->setHiddenField('soi_srv_id', $this->getIntParameter('soi_srv_id'));
        $soiSrvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $soiSrvField->setEnableNewButton(false);
        $soiSrvField->addClearField('soi_jo_number');
        $soiSrvField->addClearField('soi_jo_id');

        #Create Job Order Field
        $soiJoFlField = $this->Field->getSingleSelect('jobOrder', 'soi_jo_number', $this->getStringParameter('soi_jo_number'));
        $soiJoFlField->setHiddenField('soi_jo_id', $this->getIntParameter('soi_jo_id'));
        $soiJoFlField->addParameter('jo_ss_id', $this->User->getSsId());
        $soiJoFlField->addParameterById('so_id', 'soi_so_id', Trans::getWord('salesOrder'));
        $soiJoFlField->addParameterById('jo_srv_id', 'soi_srv_id', Trans::getWord('service'));
        $soiJoFlField->setEnableNewButton(false);


        #Create PIC In Field
        $soiPifField = $this->Field->getSingleSelect('contactPerson', 'soi_pic_field_name', $this->getStringParameter('soi_pic_field_name'));
        $soiPifField->setHiddenField('soi_pic_field_id', $this->getIntParameter('soi_pic_field_id'));
        $soiPifField->addParameter('cp_rel_id', $this->User->getRelId());
        $soiPifField->setDetailReferenceCode('cp_id');

        $soiAssFlField = $this->Field->getSingleSelect('user', 'soi_assign_name', $this->getStringParameter('soi_assign_name'));
        $soiAssFlField->setHiddenField('soi_assign_id', $this->getIntParameter('soi_assign_id'));
        $soiAssFlField->addParameter('rel_id', $this->User->getRelId());
        $soiAssFlField->addParameter('ss_id', $this->User->getSsId());
        $soiAssFlField->setEnableDetailButton(false);
        $soiAssFlField->setEnableNewButton(false);


        # Add field to field set
        $fieldSet->addField(Trans::getWord('customer'), $soiRelField, true);
        $fieldSet->addField(Trans::getWord('salesOrder'), $soiSoField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $soiPicField);
        $fieldSet->addField(Trans::getWord('service'), $soiSrvField, true);
        $fieldSet->addField(Trans::getWord('reportDate'), $this->Field->getCalendar('soi_report_date', $this->getStringParameter('soi_report_date', date('Y-m-d'))), true);
        $fieldSet->addField(Trans::getWord('jobOrder'), $soiJoFlField);
        $fieldSet->addField(Trans::getWord('assignedTo'), $soiAssFlField, true);
        $fieldSet->addField(Trans::getWord('picInField'), $soiPifField);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getIssuePortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('SoiIssPtl', Trans::getWord('issue'));
        $portlet->setGridDimension(4, 6, 6, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        #Create Priority Field
        $soiPrtField = $this->Field->getSingleSelect('sty', 'soi_sty_name', $this->getStringParameter('soi_sty_name'));
        $soiPrtField->setHiddenField('soi_priority_id', $this->getIntParameter('soi_priority_id'));
        $soiPrtField->addParameter('sty_group', 'priorityissue');
        $soiPrtField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('subject'), $this->Field->getText('soi_subject', $this->getStringParameter('soi_subject')), true);
        $fieldSet->addField(Trans::getWord('priority'), $soiPrtField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getTextArea('soi_description', $this->getStringParameter('soi_description')), true);


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getSolutionPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('SoiSolPtl', Trans::getWord('solution'));
        $portlet->setGridDimension(6, 6, 6, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $solutionField = $this->Field->getTextArea('soi_solution', $this->getStringParameter('soi_solution'));
        $notesField = $this->Field->getTextArea('soi_note', $this->getStringParameter('soi_note'));

        if ($this->isAssignedUser() === false) {
            $solutionField->setReadOnly();
            $notesField->setReadOnly();
        }

        $fieldSet->addField(Trans::getWord('solution'), $solutionField);
        $fieldSet->addField(Trans::getWord('noteForFuture'), $notesField);

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
        if ($this->isUpdate()) {
            $this->setEnableViewButton();
            if ($this->isValidParameter('soi_deleted_on') === false) {
                if ($this->isValidParameter('soi_finish_on') === true) {
                    $mdlReopen = $this->getSoiReopenModal();
                    $this->View->addModal($mdlReopen);
                    $btnReopen = new ModalButton('btnReopen', Trans::getWord('reOpen'), $mdlReopen->getModalId());
                    $btnReopen->btnPrimary();
                    $btnReopen->pullRight();
                    $btnReopen->btnMedium();
                    $this->View->addButton($btnReopen);
                } else {
                    $this->setEnableDeleteButton();
                }
            }
            # Add so button
            if ($this->isValidParameter('soi_so_id') === true) {
                $btnSo = new HyperLink('hplSo', $this->getStringParameter('soi_so_number'), url('so/view?so_id=' . $this->getIntParameter('soi_so_id')));
                $btnSo->viewAsButton();
                $btnSo->setIcon(Icon::Eye)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnSo);
            }
            # Add Jo button
            if ($this->isValidParameter('soi_jo_id') === true) {
                $joDao = new JobOrderDao();
                $url = $joDao->getJobUrl('view', $this->getIntParameter('soi_jo_srt_id'), $this->getIntParameter('soi_jo_id'));
                $btnJo = new HyperLink('hplJo', $this->getStringParameter('soi_jo_number'), $url);
                $btnJo->viewAsButton();
                $btnJo->setIcon(Icon::Eye)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnJo);
            }
        }
        # load parent.
        parent::loadDefaultButton();
    }

    /**
     * Function to get request modal reopen sales order issue
     *
     * @return Modal
     */
    protected function getSoiReopenModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('rpnSoiMdl', Trans::getWord('actionConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReopen');
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));
        $text = Trans::getMessageWord('reopenIssueConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to check if user is assigned user ord not
     *
     * @return bool
     */
    private function isAssignedUser(): bool
    {
        return $this->getIntParameter('soi_assign_id') === $this->User->getId();
    }
}
