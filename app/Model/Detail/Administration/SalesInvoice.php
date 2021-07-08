<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Administration;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Administration\SalesInvoiceDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Administration\SalesInvoiceDetailDao;
use App\Model\Dao\Master\Finance\BankAccountBalanceDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail SalesInvoice page
 *
 * @package    app
 * @subpackage Model\Detail\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoice extends AbstractFormModel
{
    /**
     * Property to store the detail data.
     *
     * @var array $Details
     */
    private $Details = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'si', 'si_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'si_ss_id' => $this->User->getSsId(),
            'si_rel_id' => $this->getStringParameter('si_rel_id'),
            'si_of_id' => $this->getStringParameter('si_of_id'),
            'si_cp_id' => $this->getStringParameter('si_cp_id'),
            'si_jo_id' => $this->getStringParameter('si_jo_id'),
            'si_pt_id' => $this->getStringParameter('si_pt_id'),
            'si_ba_id' => $this->getStringParameter('si_ba_id'),
        ];
        $siDao = new SalesInvoiceDao();
        $siDao->doInsertTransaction($colVal);
        return $siDao->getLastInsertId();
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
                'si_ss_id' => $this->User->getSsId(),
                'si_rel_id' => $this->getStringParameter('si_rel_id'),
                'si_of_id' => $this->getStringParameter('si_of_id'),
                'si_cp_id' => $this->getStringParameter('si_cp_id'),
                'si_jo_id' => $this->getStringParameter('si_jo_id'),
                'si_pt_id' => $this->getStringParameter('si_pt_id'),
                'si_ba_id' => $this->getStringParameter('si_ba_id'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('sid_rate') * $this->getFloatParameter('sid_quantity');
            if ($this->isValidParameter('sid_tax_percent')) {
                $taxPercent = $this->getFloatParameter('sid_tax_percent', 0.0);
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $sidColVal = [
                'sid_si_id' => $this->getDetailReferenceValue(),
                'sid_cc_id' => $this->getStringParameter('sid_cc_id'),
                'sid_description' => $this->getStringParameter('sid_description'),
                'sid_quantity' => $this->getFloatParameter('sid_quantity'),
                'sid_uom_id' => $this->getStringParameter('sid_uom_id'),
                'sid_rate' => $this->getFloatParameter('sid_rate'),
                'sid_cur_id' => $this->User->Settings->getCurrencyId(),
                'sid_exchange_rate' => 1,
                'sid_tax_id' => $this->getStringParameter('sid_tax_id'),
                'sid_total' => $total,
            ];
            $sidDao = new SalesInvoiceDetailDao();
            if ($this->isValidParameter('sid_id') === false) {
                $sidDao->doInsertTransaction($sidColVal);
            } else {
                $sidDao->doUpdateTransaction($this->getStringParameter('sid_id'), $sidColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $sidDao = new SalesInvoiceDetailDao();
            $sidDao->doDeleteTransaction($this->getStringParameter('sid_id_del'));
        } elseif ($this->getFormAction() === 'doSubmit') {
            $ptDays = $this->getIntParameter('si_pt_days', 0);
            $today = DateTimeParser::createDateTime();
            $today->modify('+' . $ptDays . ' days');
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('SI', $this->User->Relation->getOfficeId(), $this->getStringParameter('si_rel_id', ''));
            $colVal = [
                'si_number' => $number,
                'si_date' => date('Y-m-d'),
                'si_due_date' => $today->format('Y-m-d'),
                'si_submit_on' => date('Y-m-d H:i:s'),
                'si_submit_by' => $this->User->getId(),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doPaid') {
            $total = SalesInvoiceDetailDao::loadTotalAmountBySoid($this->getDetailReferenceValue());
            # Insert Bank Account Balance
            $babDao = new BankAccountBalanceDao();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $this->getStringParameter('si_ba_id'),
                'bab_amount' => $total
            ]);
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'si_bab_id' => $babDao->getLastInsertId(),
                'si_pm_id' => $this->getStringParameter('si_pm_id'),
                'si_pay_date' => $this->getStringParameter('si_pay_date'),
                'si_paid_on' => date('Y-m-d H:i:s'),
                'si_paid_by' => $this->User->getId(),
            ]);
        } elseif ($this->isDeleteAction() === true) {
            $siDao = new SalesInvoiceDao();
            $siDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
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
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SalesInvoiceDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isUpdate() === true) {
            $this->doPrepareDetailData();
            $this->overrideTitle();
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getDetailPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('si', $this->getDetailReferenceValue()));
        } else {
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
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
            $this->Validation->checkRequire('si_rel_id');
            $this->Validation->checkRequire('si_of_id');
            $this->Validation->checkRequire('si_cp_id');
            $this->Validation->checkRequire('si_ba_id');
            $this->Validation->checkRequire('si_pt_id');
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('sid_cc_id');
            $this->Validation->checkRequire('sid_description', 2, 256);
            $this->Validation->checkRequire('sid_quantity');
            $this->Validation->checkFloat('sid_quantity');
            $this->Validation->checkRequire('sid_uom_id');
            $this->Validation->checkRequire('sid_rate');
            $this->Validation->checkRequire('sid_tax_id');
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('sid_id_del');
        } elseif ($this->getFormAction() === 'doSubmit') {
            $this->Validation->checkRequire('si_rel_id');
            $this->Validation->checkRequire('si_pt_id');
            $this->Validation->checkRequire('si_pt_days');
            $this->Validation->checkInt('si_pt_days');
        } elseif ($this->getFormAction() === 'doPaid') {
            $this->Validation->checkRequire('si_ba_id');
            $this->Validation->checkRequire('si_pm_id');
            $this->Validation->checkRequire('si_pay_date');
            $this->Validation->checkDate('si_pay_date');

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
        $portlet = new Portlet('SiPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Relation
        $relField = $this->Field->getSingleSelect('rel', 'si_customer', $this->getStringParameter('si_customer'));
        $relField->setHiddenField('si_rel_id', $this->getStringParameter('si_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('si_of_customer');
        $relField->addClearField('si_of_id');
        $relField->addClearField('si_pic_customer');
        $relField->addClearField('si_cp_id');
        # Office
        $ofField = $this->Field->getSingleSelect('of', 'si_of_customer', $this->getStringParameter('si_of_customer'));
        $ofField->setHiddenField('si_of_id', $this->getStringParameter('si_of_id'));
        $ofField->addParameterById('of_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $ofField->setDetailReferenceCode('of_id');
        $ofField->addClearField('si_pic_customer');
        $ofField->addClearField('si_cp_id');
        # Contact Person
        $cpField = $this->Field->getSingleSelect('cp', 'si_pic_customer', $this->getStringParameter('si_pic_customer'));
        $cpField->setHiddenField('si_cp_id', $this->getStringParameter('si_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $cpField->addParameterById('cp_of_id', 'si_of_id', Trans::getWord('customerOffice'));
        $cpField->setDetailReferenceCode('cp_id');

        # Job Order
        $joField = $this->Field->getSingleSelect('jo', 'si_jo_number', $this->getStringParameter('si_jo_number'));
        $joField->setHiddenField('si_jo_id', $this->getStringParameter('si_jo_id'));
        $joField->addParameter('jo_ss_id', $this->User->getSsId());
        $joField->addParameterById('jo_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $joField->addParameter('jo_active', 'Y');
        $joField->setDetailReferenceCode('jo_id');
        $joField->setEnableNewButton(false);

        # Payment Terms
        $ptField = $this->Field->getSingleSelect('pt', 'si_payment_terms', $this->getStringParameter('si_payment_terms'));
        $ptField->setHiddenField('si_pt_id', $this->getStringParameter('si_pt_id'));
        $ptField->addParameter('pt_ss_id', $this->User->getSsId());
        $ptField->setEnableNewButton(false);
        $ptField->setAutoCompleteFields([
            'si_pt_days' => 'pt_days'
        ]);


        # Payment Terms
        $baField = $this->Field->getSingleSelect('ba', 'si_bank_account', $this->getStringParameter('si_bank_account'));
        $baField->setHiddenField('si_ba_id', $this->getStringParameter('si_ba_id'));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_main', 'Y');
        $baField->addParameter('ba_receivable', 'Y');
        $baField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('jobNumber'), $joField);
        $fieldSet->addField(Trans::getWord('customerOffice'), $ofField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $cpField, true);
        $fieldSet->addField(Trans::getWord('ar'), $baField, true);
        $fieldSet->addField(Trans::getWord('paymentTerms'), $ptField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('si_pt_days', $this->getFloatParameter('si_pt_days')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to do prepare detail data.
     *
     * @return void
     */
    private function overrideTitle(): void
    {
        $status = SalesInvoiceDao::generateStatus($this->getAllParameters());
        $title = $this->PageSetting->getPageDescription();
        if ($this->isValidParameter('si_number') === true) {
            $title .= ' #' . $this->getStringParameter('si_number');
        }
        $title .= ' - ' . $status;
        $this->View->setDescription($title);

        $this->addDeletedMessage('si');
    }


    /**
     * Function to do prepare detail data.
     *
     * @return void
     */
    private function doPrepareDetailData(): void
    {
        $data = SalesInvoiceDetailDao::getBySiId($this->getDetailReferenceValue());
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['sid_quantity'] = $number->doFormatFloat($row['sid_quantity']) . ' ' . $row['sid_uom_code'];
            $row['sid_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_rate']);
            $row['sid_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_total']);
            $row['sid_cc_name'] = $row['sid_cc_code'] . ' - ' . $row['sid_cc_name'];
            $this->Details[] = $row;
        }
    }


    /**
     * Function to get the Task Portlet.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $modalUpdate = $this->getDetailModal();
        $this->View->addModal($modalUpdate);
        $modalDelete = $this->getDetailDeleteModal();
        $this->View->addModal($modalDelete);

        $tbl = new Table('SidTbl');
        $tbl->setHeaderRow([
            'sid_cc_name' => Trans::getWord('account'),
            'sid_description' => Trans::getWord('description'),
            'sid_quantity' => Trans::getWord('quantity'),
            'sid_rate' => Trans::getWord('unitPrice'),
            'sid_tax_name' => Trans::getWord('tax'),
            'sid_total' => Trans::getWord('total'),
        ]);
        $tbl->addRows($this->Details);
        $tbl->addColumnAttribute('sid_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_rate', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_total', 'style', 'text-align: right;');

        # Instantiate Portlet Object
        $portlet = new Portlet('PiPidPtl', Trans::getWord('salesDetail'));
        $portlet->setGridDimension(12, 12, 12);
        # Set Action
        if ($this->isAllowUpdate() === true) {
            $tbl->setUpdateActionByModal($modalUpdate, 'sid', 'getById', ['sid_id']);
            $tbl->setDeleteActionByModal($modalDelete, 'sid', 'getByIdForDelete', ['sid_id']);
            $btn = new ModalButton('PidBtn', Trans::getWord('add'), $modalUpdate->getModalId());
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
    private function getDetailModal(): Modal
    {
        $mdl = new Modal('SidMdl', Trans::getWord('details'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelect('cc', 'sid_cc_name', $this->getParameterForModal('sid_cc_name', $showModal));
        $ccField->setHiddenField('sid_cc_id', $this->getParameterForModal('sid_cc_id', $showModal));
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameter('ccg_type', 'S');
        $ccField->setEnableDetailButton(false);
        $ccField->setEnableNewButton(false);
        $ccField->setAutoCompleteFields([
            'sid_description' => 'cc_name',
        ]);

        $uomField = $this->Field->getSingleSelect('uom', 'sid_uom_code', $this->getParameterForModal('sid_uom_code', $showModal));
        $uomField->setHiddenField('sid_uom_id', $this->getParameterForModal('sid_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'sid_tax_name', $this->getParameterForModal('sid_tax_name', $showModal));
        $taxField->setHiddenField('sid_tax_id', $this->getParameterForModal('sid_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);
        $taxField->setAutoCompleteFields([
            'sid_tax_percent' => 'tax_percent'
        ]);

        $quantityField = $this->Field->getNumber('sid_quantity', $this->getParameterForModal('sid_quantity', $showModal));
        $rate = $this->Field->getNumber('sid_rate', $this->getParameterForModal('sid_rate', $showModal));
        if ($this->isPaid() === true) {
            $ccField->setReadOnly();
            $quantityField->setReadOnly();
            $uomField->setReadOnly();
            $rate->setReadOnly();
            $taxField->setReadOnly();
        }

        $fieldSet->addField(Trans::getWord('account'), $ccField, true);
        $fieldSet->addField(Trans::getWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sid_description', $this->getParameterForModal('sid_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('quantity'), $quantityField, true);
        $fieldSet->addField(Trans::getWord('unitPrice'), $rate, true);
        $fieldSet->addField(Trans::getWord('tax'), $taxField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('sid_tax_percent', $this->getParameterForModal('sid_tax_percent', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sid_id', $this->getParameterForModal('sid_id', $showModal)));

        $mdl->addFieldSet($fieldSet);
        return $mdl;
    }

    /**
     * Function to get the Task Delete Modal.
     *
     * @return Modal
     */
    private function getDetailDeleteModal(): Modal
    {
        $mdl = new Modal('SidDelMdl', Trans::getWord('deleteDetail'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('account'), $this->Field->getText('sid_cc_name_del', $this->getParameterForModal('sid_cc_name_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sid_description_del', $this->getParameterForModal('sid_description_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('sid_quantity_del', $this->getParameterForModal('sid_quantity_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('unitPrice'), $this->Field->getNumber('sid_rate_del', $this->getParameterForModal('sid_rate_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('sid_uom_code_del', $this->getParameterForModal('sid_uom_code_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('tax'), $this->Field->getText('sid_tax_name_del', $this->getParameterForModal('sid_tax_name_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('sid_id_del', $this->getParameterForModal('sid_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $mdl->addText($p);
        $mdl->setBtnOkName(Trans::getWord('yesDelete'));
        $mdl->addFieldSet($fieldSet);

        return $mdl;
    }

    /**
     * Function to get the Task Delete Modal.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->isDeleted('si') === false && $this->isPaid() === false;
    }

    /**
     * Function to check is data has been verified or not.
     *
     * @return bool
     */
    private function isSubmitted(): bool
    {
        return $this->isValidParameter('si_submit_on');
    }

    /**
     * Function to check is data has been verified or not.
     *
     * @return bool
     */
    private function isPaid(): bool
    {
        return $this->isValidParameter('si_paid_on');
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true && $this->isAllowUpdate() === true) {
            $this->setEnableDeleteButton();
            if ($this->isSubmitted() === false) {
                # Show Button verify
                $modal = $this->getSubmitModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnPiSubmitMdl', Trans::getWord('submitInvoice'), $modal->getModalId());
                $btnDel->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            } else {
                # Show Button paid
                $modal = $this->getPaidModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnPiPaidMdl', Trans::getWord('pay'), $modal->getModalId());
                $btnDel->setIcon(Icon::Money)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            }
        } else {
            $this->setDisableUpdate();
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getSubmitModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SiSubmitMdl', Trans::getWord('submitConfirmation'));
        if (empty($this->Details) === true) {
            $p = new Paragraph(Trans::getMessageWord('unableSubmitInvoice'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getMessageWord('invoiceSubmitConfirmation');
            $modal->setFormSubmit($this->getMainFormId(), 'doSubmit');
            $modal->setBtnOkName(Trans::getWord('yesConfirm'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        }

        return $modal;
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getPaidModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PiPaidMdl', Trans::getWord('paidConfirmation'));
        if (empty($this->Details) === true) {
            $p = new Paragraph(Trans::getMessageWord('unablePaidInvoice'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getMessageWord('paidConfirmation');
            $modal->setFormSubmit($this->getMainFormId(), 'doPaid');
            $modal->setBtnOkName(Trans::getWord('yesConfirm'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $showModal = false;
            if ($this->getFormAction() === 'doPaid' && $this->isValidPostValues() === false) {
                $modal->setShowOnLoad();
                $showModal = true;
            }


            $fieldSet = new FieldSet($this->Validation);
            $fieldSet->setGridDimension(6, 6);
            # Payment Method Field
            $pmField = $this->Field->getSingleSelect('pm', 'si_payment_method', $this->getParameterForModal('si_payment_method', $showModal));
            $pmField->setHiddenField('si_pm_id', $this->getParameterForModal('si_pm_id', $showModal));
            $pmField->addParameter('pm_ss_id', $this->User->getSsId());
            $pmField->setEnableNewButton(false);

            $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('si_pay_date', $this->getParameterForModal('si_pay_date', $showModal)), true);
            $fieldSet->addField(Trans::getWord('paymentMethod'), $pmField, true);

            $modal->addFieldSet($fieldSet);
        }

        return $modal;
    }

}
