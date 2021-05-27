<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 11/04/2019
 * Time: 12:16
 */

namespace App\Frame\Mvc;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;

abstract class AbstractFormModel extends AbstractDetailModel
{
    /**
     * Base detail model constructor.
     *
     * @param string $nameSpace To store the name space of the page.
     * @param string $route To store the name space of the page.
     * @param string $detailReferenceCode To store the detail reference code.
     */
    public function __construct(string $nameSpace, string $route, string $detailReferenceCode)
    {
        parent::__construct('Detail', $nameSpace, $route);
        $this->setDetailReferenceCode($detailReferenceCode);
    }

    /**
     * Function to get the the view route.
     *
     * @return string
     */
    protected function getViewRoute(): string
    {
        return $this->PageSetting->getPageRoute() . '/view';
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {

        if ($this->isInsertButtonEnabled() === true && $this->isInsert() === true && $this->PageSetting->checkPageRight('AllowInsert') === true) {
            $btnInsert = new Button('btnInsert', Trans::getWord('insert'), 'button');
            $btnInsert->addAttribute('onclick', "App.submitForm('" . $this->getMainFormId() . "')");
            $btnInsert->setIcon(Icon::Save)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnInsert);
        }
        if ($this->isUpdate() === true) {
            if ($this->isUpdateButtonEnabled() === true && $this->PageSetting->checkPageRight('AllowUpdate') === true) {
                $btnUpdate = new Button('btnUpdate', Trans::getWord('save'), 'button');
                $btnUpdate->setIcon(Icon::Save)->btnSuccess()->pullRight()->btnMedium();
                $btnUpdate->addAttribute('onclick', "App.submitForm('" . $this->getMainFormId() . "')");
                $this->View->addButtonAtTheBeginning($btnUpdate);
            }
            if ($this->isViewButtonEnabled() === true) {
                $btnView = new HyperLink('hplView', Trans::getWord('view'), url($this->getViewRoute() . '?' . $this->getDetailReferenceCode() . '=' . $this->getDetailReferenceValue()));
                $btnView->viewAsButton();
                $btnView->setIcon(Icon::Eye)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnView);
            }
            if ($this->isCopyButtonEnabled() === true) {
                $modalCopy = $this->getBaseCopyModal();
                $this->View->addModal($modalCopy);
                $btnCp = new ModalButton('btnCopy', Trans::getWord('copy'), $modalCopy->getModalId());
                $btnCp->setIcon(Icon::Copy)->btnDark()->pullRight()->btnMedium();
                $this->View->addButton($btnCp);
            }
            if ($this->isHoldButtonEnabled() === true) {
                $modalHold = $this->getBaseHoldModal();
                $this->View->addModal($modalHold);
                $btnHold = new ModalButton('btnHold', Trans::getWord('hold'), $modalHold->getModalId());
                $btnHold->setIcon(Icon::Stop)->btnWarning()->pullRight()->btnMedium();
                $this->View->addButton($btnHold);
            }
            if ($this->isUnHoldButtonEnabled() === true) {
                $modalUnHold = $this->getBaseUnHoldModal();
                $this->View->addModal($modalUnHold);
                $btnUnHold = new ModalButton('btnUnHold', Trans::getWord('unHold'), $modalUnHold->getModalId());
                $btnUnHold->setIcon(Icon::CheckSquare)->btnWarning()->pullRight()->btnMedium();
                $this->View->addButton($btnUnHold);
            }
            if ($this->isDeleteButtonEnabled() === true && $this->PageSetting->checkPageRight('AllowDelete') === true) {
                $modal = $this->getBaseDeleteModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnDelete', Trans::getWord('delete'), $modal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            }
        }

        if ($this->isCloseButtonEnabled() === true) {
            if ($this->isPopupLayout() === true) {
                $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
                $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight()->btnMedium();
                $btnClose->addAttribute('onclick', 'App.closeWindow()');
                $this->View->addButton($btnClose);
            } else {
                $backUrl = $this->getStringParameter('back_url', $this->getDefaultRoute());
                $btnClose = new HyperLink('hplClose', Trans::getWord('close'), url($backUrl));
                $btnClose->viewAsButton();
                $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnClose);
            }
        }
    }

    /**
     * Function to get default title for portlet.
     *
     * @return string
     */
    protected function getDefaultPortletTitle(): string
    {
        $result = 'formInsert';
        if ($this->isUpdate() === true) {
            $result = 'formUpdate';
        }

        return Trans::getWord($result);
    }
//
//    /**
//     * Function to load all the view data and convert it to array.
//     *
//     * @return array
//     */
//    public function createView(): array
//    {
//        if ($this->isUpdate()) {
//            $this->Tab->addPortlet('userLog', $this->getUserLogFieldSet());
//        }
//        return parent::createView();
//    }

//
//    /**
//     * Function to get the Office Field Set.
//     *
//     * @return Portlet
//     */
//    private function getUserLogFieldSet(): Portlet
//    {
//        $table = new Table('UsLogTbl');
//        $table->setHeaderRow([
//            'ul_created_on' => Trans::getWord('createdOn'),
//            'ul_action' => Trans::getWord('action'),
//            'ul_created_by' => Trans::getWord('user'),
//            'ul_media' => Trans::getWord('media'),
//            // 'ul_data' => Trans::getWord('data')
//        ]);
//        $data = UserLogDao::loadDataByPage($this->PageSetting->getPageRoute(), $this->getDetailReferenceValue());
//        $table->addRows($data);
//        $table->addColumnAttribute('ul_created_on', 'style', 'text-align: center;');
//        $table->addColumnAttribute('ul_media', 'style', 'text-align: center;');
//        # Create a portlet box.
//        $portlet = new Portlet('UsLogPtl', Trans::getWord('logs'));
//        $portlet->addTable($table);
//        return $portlet;
//    }
//

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getBaseDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaseDelMdl', Trans::getWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDelete');
        $showModal = false;
        if ($this->getFormAction() === 'doDelete' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('reason'), $this->Field->getTextArea('base_delete_reason', $this->getParameterForModal('base_delete_reason', $showModal)), true);
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to check is delete action.
     *
     * @return bool
     */
    protected function isDeleteAction(): bool
    {
        return $this->getFormAction() === 'doDelete';
    }


    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->isDeleteAction()) {
            $this->Validation->checkRequire('base_delete_reason', 2, 255);
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to check is delete action.
     *
     * @return string
     */
    protected function getReasonDeleteAction(): string
    {
        return $this->getStringParameter('base_delete_reason');
    }


    /**
     * Function to check is delete action.
     *
     * @return string
     */
    protected function getReasonHoldAction(): string
    {
        return $this->getStringParameter('base_hold_reason');
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getBaseCopyModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaseCopyMdl', Trans::getWord('copyData'));
        $modal->setFormSubmit($this->getMainFormId(), 'doCopyData');
        $showModal = false;
        if ($this->getFormAction() === 'doCopyData' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('copyAmount'), $this->Field->getNumber('base_copy_amount', $this->getParameterForModal('copy_amount', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getBaseHoldModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaseHoldMdl', Trans::getWord('holdConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doHold');
        $showModal = false;
        if ($this->getFormAction() === 'doHold' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('reason'), $this->Field->getTextArea('base_hold_reason', $this->getParameterForModal('base_hold_reason', $showModal)), true);
        $p = new Paragraph(Trans::getMessageWord('holdConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesHold'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getBaseUnHoldModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PageUnHoldMdl', Trans::getWord('unHoldConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUnHold');
        $p = new Paragraph(Trans::getMessageWord('unHoldConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesUnHold'));

        return $modal;
    }
}
