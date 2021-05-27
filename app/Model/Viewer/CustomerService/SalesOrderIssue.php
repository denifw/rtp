<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Viewer\CustomerService;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\CustomerService\SalesOrderIssueDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;

/**
 * Class to handle the creation of detail SalesOrderIssue page
 *
 * @package    app
 * @subpackage Model\Viewer\CustomerService
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderIssue extends AbstractViewerModel
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
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === "doClose") {
            $colVal = [
                'soi_finish_by' => $this->User->getId(),
                'soi_finish_on' => date('Y-m-d H:i:s'),
                'soi_solution' => $this->getStringParameter('soi_solution'),
                'soi_note' => $this->getStringParameter('soi_note'),
            ];
            $soiDao = new SalesOrderIssueDao();
            $soiDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
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
        $this->overrideTitle();
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getIssuePortlet());
        $this->Tab->addPortlet('general', $this->getSolutionPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === "doClose") {
            $this->Validation->checkRequire('soi_solution', 2, 256);
            $this->Validation->checkRequire('soi_note', 2, 256);
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
        $date = new DateTimeParser();
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('soi_rel_name'),
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->getStringParameter('soi_pic_name'),
            ],
            [
                'label' => Trans::getWord('salesOrder'),
                'value' => $this->getStringParameter('soi_so_number'),
            ],
            [
                'label' => Trans::getWord('service'),
                'value' => $this->getStringParameter('soi_srv_name'),
            ],
            [
                'label' => Trans::getWord('jobOrder'),
                'value' => $this->getStringParameter('soi_jo_number'),
            ],
            [
                'label' => Trans::getWord('picInField'),
                'value' => $this->getStringParameter('soi_pic_field_name'),
            ],
            [
                'label' => Trans::getWord('assignedTo'),
                'value' => $this->getStringParameter('soi_assign_name'),
            ],
            [
                'label' => Trans::getWord('reportDate'),
                'value' => $date->formatDate($this->getStringParameter('soi_report_date'))
            ],
        ]);

        # Instantiate Portlet Object
        $portlet = new Portlet('SoiPtl', Trans::getWord('general'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the issue Field Set.
     *
     * @return Portlet
     */
    private function getIssuePortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('subject'),
                'value' => $this->getStringParameter('soi_subject'),
            ],
            [
                'label' => Trans::getWord('priority'),
                'value' => $this->getStringParameter('soi_sty_name'),
            ],
            [
                'label' => Trans::getWord('description'),
                'value' => $this->getStringParameter('soi_description'),
            ],
        ]);

        # Instantiate Portlet Object
        $portlet = new Portlet('SoiIssPtl', Trans::getWord('issue'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the solution Field Set.
     *
     * @return Portlet
     */
    private function getSolutionPortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('solution'),
                'value' => $this->getStringParameter('soi_solution'),
            ],
            [
                'label' => Trans::getWord('noteForFuture'),
                'value' => $this->getStringParameter('soi_note'),
            ],
        ]);

        # Instantiate Portlet Object
        $portlet = new Portlet('SoiSolutionPtl', Trans::getWord('solution'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isValidParameter('soi_finish_on') === false && $this->isValidParameter('soi_deleted_on') === false && $this->isAssignedUser() === true) {
            $mdlFinish = $this->getSoiFinishModal();
            $this->View->addModal($mdlFinish);
            $btnFinish = new ModalButton('btnClose', Trans::getWord('closeIssue'), $mdlFinish->getModalId());
            $btnFinish->setIcon(Icon::Check)->btnSuccess()->pullRight()->btnMedium();
            $this->View->addButton($btnFinish);
        }
        parent::loadDefaultButton();
        if ($this->isValidParameter('soi_so_id') === true) {
            $btnSo = new HyperLink('hplSo', $this->getStringParameter('soi_so_number'), url('so/view?so_id=' . $this->getIntParameter('soi_so_id')));
            $btnSo->viewAsButton();
            $btnSo->setIcon(Icon::Eye)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnSo);
        }
        if ($this->isValidParameter('soi_jo_id') === true) {
            $joDao = new JobOrderDao();
            $url = $joDao->getJobUrl('view', $this->getIntParameter('soi_jo_srt_id'), $this->getIntParameter('soi_jo_id'));
            $btnJo = new HyperLink('hplJo', $this->getStringParameter('soi_jo_number'), $url);
            $btnJo->viewAsButton();
            $btnJo->setIcon(Icon::Eye)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnJo);
        }
    }


    /**
     * Function to get request modal finish sales order issue
     *
     * @return Modal
     */
    protected function getSoiFinishModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('fnsSoiMdl', Trans::getWord('closeConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doClose');
        if ($this->getFormAction() === 'doClose' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('solution'), $this->Field->getTextArea('soi_solution', $this->getParameterForModal('soi_solution', true)), true);
        $fieldSet->addField(Trans::getWord('noteForFuture'), $this->Field->getTextArea('soi_note', $this->getParameterForModal('soi_note', true)), true);

        $text = Trans::getMessageWord('closeIssueConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet );
        $modal->setBtnOkName(Trans::getWord('yesClose'));

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
