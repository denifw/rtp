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
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Administration\PurchaseInvoiceDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Administration\PurchaseInvoiceDetailDao;
use App\Model\Dao\Master\Finance\BankAccountBalanceDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail PurchaseInvoice page
 *
 * @package    app
 * @subpackage Model\Detail\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PurchaseInvoice extends AbstractFormModel
{
    /**
     * Property to store the detail data.
     *
     * @var array $Details
     */
    private $Details = [];

    /**
     * Property to store the total amount.
     *
     * @var float $TotalAmount
     */
    private $TotalAmount = 0.0;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pi', 'pi_id');
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
        $number = $sn->loadNumber('PI', $this->User->Relation->getOfficeId(), $this->getStringParameter('pi_rel_id', ''));
        $colVal = [
            'pi_ss_id' => $this->User->getSsId(),
            'pi_number' => $number,
            'pi_reference' => $this->getStringParameter('pi_reference'),
            'pi_rel_id' => $this->getStringParameter('pi_rel_id'),
            'pi_cp_id' => $this->getStringParameter('pi_cp_id'),
            'pi_date' => $this->getStringParameter('pi_date'),
            'pi_due_date' => $this->getStringParameter('pi_due_date', $this->getStringParameter('pi_date')),
            'pi_notes' => $this->getStringParameter('pi_notes'),
        ];
        $piDao = new PurchaseInvoiceDao();
        $piDao->doInsertTransaction($colVal);
        return $piDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === true) {
            $colVal = [
                'pi_reference' => $this->getStringParameter('pi_reference'),
                'pi_rel_id' => $this->getStringParameter('pi_rel_id'),
                'pi_cp_id' => $this->getStringParameter('pi_cp_id'),
                'pi_date' => $this->getStringParameter('pi_date'),
                'pi_due_date' => $this->getStringParameter('pi_due_date'),
                'pi_notes' => $this->getStringParameter('pi_notes'),
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->isDeleteAction() === true) {
            $piDao = new PurchaseInvoiceDao();
            $piDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
            if ($this->isValidParameter('pi_bab_id') === true) {
                $babDao = new BankAccountBalanceDao();
                $babDao->doDeleteTransaction($this->getStringParameter('pi_bab_id'));
            }
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
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('pid_rate') * $this->getFloatParameter('pid_quantity');
            if ($this->isValidParameter('pid_tax_percent')) {
                $taxPercent = $this->getFloatParameter('pid_tax_percent', 0.0);
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $pidColVal = [
                'pid_pi_id' => $this->getDetailReferenceValue(),
                'pid_jo_id' => $this->getStringParameter('pid_jo_id'),
                'pid_cc_id' => $this->getStringParameter('pid_cc_id'),
                'pid_description' => $this->getStringParameter('pid_description'),
                'pid_quantity' => $this->getFloatParameter('pid_quantity'),
                'pid_uom_id' => $this->getStringParameter('pid_uom_id'),
                'pid_rate' => $this->getFloatParameter('pid_rate'),
                'pid_cur_id' => $this->User->Settings->getCurrencyId(),
                'pid_exchange_rate' => 1,
                'pid_tax_id' => $this->getStringParameter('pid_tax_id'),
                'pid_total' => $total,
            ];
            $pidDao = new PurchaseInvoiceDetailDao();
            if ($this->isValidParameter('pid_id') === true) {
                $pidDao->doUpdateTransaction($this->getStringParameter('pid_id'), $pidColVal);
            } else {
                $pidDao->doInsertTransaction($pidColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $pidDao = new PurchaseInvoiceDetailDao();
            $pidDao->doDeleteTransaction($this->getStringParameter('pid_id_del'));
        } elseif ($this->getFormAction() === 'doPaid') {
            # Insert Bank Account Balance
            $babDao = new BankAccountBalanceDao();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $this->getStringParameter('pi_ba_id'),
                'bab_amount' => $this->getFloatParameter('pi_total', 0.0) * -1
            ]);
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'pi_pay_date' => $this->getStringParameter('pi_pay_date'),
                'pi_paid_on' => date('Y-m-d H:i:s'),
                'pi_paid_by' => $this->User->getId(),
                'pi_pm_id' => $this->getStringParameter('pi_pm_id'),
                'pi_ba_id' => $this->getStringParameter('pi_ba_id'),
                'pi_bab_id' => $babDao->getLastInsertId()
            ]);
        } elseif ($this->getFormAction() === 'doVerified') {
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'pi_verified_on' => date('Y-m-d H:i:s'),
                'pi_verified_by' => $this->User->getId()
            ]);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PurchaseInvoiceDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->doPrepareDetailData();
            $this->overrideTitle();
            $this->Tab->addContent('general', $this->getWidget());
            $this->Tab->addPortlet('general', $this->getPaymentPortlet());
            $this->Tab->addPortlet('general', $this->getDetailPortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('pi', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('pi_date');
            $this->Validation->checkDate('pi_date');
            $this->Validation->checkMaxLength('pi_reference', 256);
            $this->Validation->checkMaxLength('pi_notes', 256);
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('pid_cc_id');
            $this->Validation->checkRequire('pid_description', 2, 256);
            $this->Validation->checkRequire('pid_quantity');
            $this->Validation->checkRequire('pid_uom_id');
            $this->Validation->checkRequire('pid_rate');
            $this->Validation->checkRequire('pid_tax_id');
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('pid_id_del');
        } elseif ($this->getFormAction() === 'doPaid') {
            $this->Validation->checkRequire('pi_ba_id');
            $this->Validation->checkRequire('pi_pay_date');
            $this->Validation->checkRequire('pi_pm_id');
            $this->Validation->checkDate('pi_pay_date');
            $this->Validation->checkRequire('pi_total');
            $this->Validation->checkFloat('pi_total');
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
        $portlet = new Portlet('PiPtl', $this->getDefaultPortletTitle());
        if ($this->isUpdate() === true) {
            $portlet->setGridDimension(8, 8);
        }

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Relation Field
        $relField = $this->Field->getSingleSelect('rel', 'pi_vendor', $this->getStringParameter('pi_vendor'));
        $relField->setHiddenField('pi_rel_id', $this->getStringParameter('pi_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('pi_pic_vendor');
        $relField->addClearField('pi_cp_id');
        # Contact Person
        $cpField = $this->Field->getSingleSelect('cp', 'pi_pic_vendor', $this->getStringParameter('pi_pic_vendor'));
        $cpField->setHiddenField('pi_cp_id', $this->getStringParameter('pi_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'pi_rel_id', Trans::getWord('vendor'));
        $cpField->setDetailReferenceCode('cp_id');

        # Add field to field set
        $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('pi_date', $this->getStringParameter('pi_date')), true);
        $fieldSet->addField(Trans::getWord('dueDate'), $this->Field->getCalendar('pi_due_date', $this->getStringParameter('pi_due_date')));
        $fieldSet->addField(Trans::getWord('vendor'), $relField);
        $fieldSet->addField(Trans::getWord('picVendor'), $cpField);
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('pi_reference', $this->getStringParameter('pi_reference')));
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('pi_notes', $this->getStringParameter('pi_notes')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to do prepare detail data.
     *
     * @return void
     */
    private function doPrepareDetailData(): void
    {
        $data = PurchaseInvoiceDetailDao::getByPiId($this->getDetailReferenceValue());
        $number = new NumberFormatter($this->User);
        $this->TotalAmount = 0.0;
        foreach ($data as $row) {
            $this->TotalAmount += (float)$row['pid_total'];
            $row['pid_quantity'] = $number->doFormatFloat($row['pid_quantity']) . ' ' . $row['pid_uom_code'];
            $row['pid_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['pid_rate']);
            $row['pid_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['pid_total']);
            if (empty($row['pid_jo_id']) === false) {
                $row['pid_job_order'] = $row['pid_jo_number'] . ' - ' . $row['pid_jo_name'];
            }
            $row['pid_cc_name'] = $row['pid_cc_code'] . ' - ' . $row['pid_cc_name'];
            $this->Details[] = $row;
        }
    }

    /**
     * Function to do prepare detail data.
     *
     * @return void
     */
    private function overrideTitle(): void
    {
        $status = PurchaseInvoiceDao::generateStatus($this->getAllParameters());
        $title = $this->PageSetting->getPageDescription();
        if ($this->isValidParameter('pi_number') === true) {
            $title .= ' #' . $this->getStringParameter('pi_number');
        }
        $title .= ' - ' . $status;
        $this->View->setDescription($title);

        $this->addDeletedMessage('pi');

        $content = '';
        $content .= $this->Field->getHidden('pi_bab_id', $this->getStringParameter('pi_bab_id'));
        $content .= $this->Field->getHidden('pi_total', $this->TotalAmount);
        $this->View->addContent('PiHdFld', $content);
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

        $tbl = new Table('JotTbl');
        $tbl->setHeaderRow([
            'pid_job_order' => Trans::getWord('jobNumber'),
            'pid_cc_name' => Trans::getWord('account'),
            'pid_description' => Trans::getWord('description'),
            'pid_quantity' => Trans::getWord('quantity'),
            'pid_rate' => Trans::getWord('unitPrice'),
            'pid_tax_name' => Trans::getWord('tax'),
            'pid_total' => Trans::getWord('total'),
        ]);
        $tbl->addRows($this->Details);
        $tbl->addColumnAttribute('pid_jo_number', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('pid_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('pid_rate', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('pid_total', 'style', 'text-align: right;');

        # Instantiate Portlet Object
        $portlet = new Portlet('PiPidPtl', Trans::getWord('purchaseDetail'));
        $portlet->setGridDimension(12, 12, 12);
        # Set Action
        if ($this->isAllowUpdate() === true) {
            $tbl->setUpdateActionByModal($modalUpdate, 'pid', 'getById', ['pid_id']);
            if ($this->isPaid() === false) {
                $tbl->setDeleteActionByModal($modalDelete, 'pid', 'getByIdForDelete', ['pid_id']);
                $btn = new ModalButton('PidBtn', Trans::getWord('add'), $modalUpdate->getModalId());
                $btn->btnPrimary()->pullRight()->btnMedium()->setIcon(Icon::Plus);
                $portlet->addButton($btn);
            }
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
        $mdl = new Modal('PidMdl', Trans::getWord('details'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelect('cc', 'pid_cc_name', $this->getParameterForModal('pid_cc_name', $showModal));
        $ccField->setHiddenField('pid_cc_id', $this->getParameterForModal('pid_cc_id', $showModal));
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameter('ccg_type', 'P');
        $ccField->setEnableDetailButton(false);
        $ccField->setEnableNewButton(false);
        $ccField->setAutoCompleteFields([
            'pid_description' => 'cc_name',
        ]);

        $joField = $this->Field->getSingleSelect('jo', 'pid_jo_number', $this->getParameterForModal('pid_jo_number', $showModal));
        $joField->setHiddenField('pid_jo_id', $this->getParameterForModal('pid_jo_id', $showModal));
        $joField->addParameter('jo_ss_id', $this->User->getSsId());
        $joField->addParameter('jo_active', 'Y');
        $joField->setDetailReferenceCode('jo_id');
        $joField->setEnableNewButton(false);
        $joField->setEnableDetailButton(false);

        $uomField = $this->Field->getSingleSelect('uom', 'pid_uom_code', $this->getParameterForModal('pid_uom_code', $showModal));
        $uomField->setHiddenField('pid_uom_id', $this->getParameterForModal('pid_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'pid_tax_name', $this->getParameterForModal('pid_tax_name', $showModal));
        $taxField->setHiddenField('pid_tax_id', $this->getParameterForModal('pid_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);
        $taxField->setAutoCompleteFields([
            'pid_tax_percent' => 'tax_percent'
        ]);

        $quantityField = $this->Field->getNumber('pid_quantity', $this->getParameterForModal('pid_quantity', $showModal));
        $rate = $this->Field->getNumber('pid_rate', $this->getParameterForModal('pid_rate', $showModal));
        if ($this->isPaid() === true) {
            $ccField->setReadOnly();
            $quantityField->setReadOnly();
            $uomField->setReadOnly();
            $rate->setReadOnly();
            $taxField->setReadOnly();
        }

        $fieldSet->addField(Trans::getWord('account'), $ccField, true);
        $fieldSet->addField(Trans::getWord('jobNumber'), $joField);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pid_description', $this->getParameterForModal('pid_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('quantity'), $quantityField, true);
        $fieldSet->addField(Trans::getWord('unitPrice'), $rate, true);
        $fieldSet->addField(Trans::getWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getWord('tax'), $taxField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('pid_tax_percent', $this->getParameterForModal('pid_tax_percent', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('pid_id', $this->getParameterForModal('pid_id', $showModal)));

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
        $mdl = new Modal('PidDelMdl', Trans::getWord('deleteDetail'));
        $mdl->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $mdl->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('account'), $this->Field->getText('pid_cc_name_del', $this->getParameterForModal('pid_cc_name_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('jobNumber'), $this->Field->getText('pid_jo_number_del', $this->getParameterForModal('pid_jo_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pid_description_del', $this->getParameterForModal('pid_description_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('pid_quantity_del', $this->getParameterForModal('pid_quantity_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('unitPrice'), $this->Field->getNumber('pid_rate_del', $this->getParameterForModal('pid_rate_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('pid_uom_code_del', $this->getParameterForModal('pid_uom_code_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('tax'), $this->Field->getText('pid_tax_name_del', $this->getParameterForModal('pid_tax_name_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('pid_id_del', $this->getParameterForModal('pid_id_del', $showModal)));

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
        return $this->isDeleted('pi') === false && $this->isVerified() === false;
    }

    /**
     * Function to check is data has been verified or not.
     *
     * @return bool
     */
    private function isVerified(): bool
    {
        return $this->isValidParameter('pi_verified_on');
    }

    /**
     * Function to check is data has been verified or not.
     *
     * @return bool
     */
    private function isPaid(): bool
    {
        return $this->isValidParameter('pi_paid_on');
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isAllowUpdate() === true) {
            if ($this->isPaid() === true) {
                $this->setEnableDeleteButton(false);
                # Show Button verify
                $modal = $this->getVerifiedModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnPiVerifiedMdl', Trans::getWord('verified'), $modal->getModalId());
                $btnDel->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            } else {
                $this->setEnableDeleteButton(true);
                # Show Button paid
                $modal = $this->getPaidModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnPiPaidMdl', Trans::getWord('pay'), $modal->getModalId());
                $btnDel->setIcon(Icon::Money)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            }
        } else {
            $this->setDisableUpdate(true);
        }
        parent::loadDefaultButton();
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

            # Bank Account Field
            $baField = $this->Field->getSingleSelect('ba', 'pi_bank_account', $this->getParameterForModal('pi_bank_account', $showModal));
            $baField->setHiddenField('pi_ba_id', $this->getParameterForModal('pi_ba_id', $showModal));
            $baField->addParameter('ba_ss_id', $this->User->getSsId());
            $baField->addParameter('ba_us_id', $this->User->getId());
            $baField->addParameter('ba_payable', 'Y');
            $baField->setEnableNewButton(false);
            # Payment Method Field
            $pmField = $this->Field->getSingleSelect('pm', 'pi_payment_method', $this->getParameterForModal('pi_payment_method', $showModal));
            $pmField->setHiddenField('pi_pm_id', $this->getParameterForModal('pi_pm_id', $showModal));
            $pmField->addParameter('pm_ss_id', $this->User->getSsId());
            $pmField->setEnableNewButton(false);

            $fieldSet->addField(Trans::getWord('cashAccount'), $baField, true);
            $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('pi_pay_date', $this->getParameterForModal('pi_pay_date', $showModal)), true);
            $fieldSet->addField(Trans::getWord('paymentMethod'), $pmField, true);

            $modal->addFieldSet($fieldSet);
        }

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getVerifiedModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PidVerifiedMdl', Trans::getWord('verifiedConfirmation'));
        $text = Trans::getMessageWord('verifiedConfirmation');
        $modal->setFormSubmit($this->getMainFormId(), 'doVerified');
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }


    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $results = '';
        # Cash Advance
        $number = new NumberFormatter($this->User);
        $advance = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('total'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-primary',
            'amount' => $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($this->TotalAmount),
            'uom' => '',
            'url' => '',
        ];
        $advance->setData($data);
        $advance->setGridDimension(4);
        $results .= $advance->createView();
        return $results;
    }


    /**
     * Function to get general view portlet.
     *
     * @return Portlet
     */
    private function getPaymentPortlet(): Portlet
    {
        $dt = new DateTimeParser();
        $data = [
            [
                'label' => Trans::getWord('paidOn'),
                'value' => $dt->formatDate($this->getStringParameter('pi_pay_date')),
            ],
            [
                'label' => Trans::getWord('cashAccount'),
                'value' => $this->getStringParameter('pi_bank_account'),
            ],
            [
                'label' => Trans::getWord('paymentMethod'),
                'value' => $this->getStringParameter('pi_payment_method'),
            ],
            [
                'label' => Trans::getWord('paidBy'),
                'value' => $this->getStringParameter('pi_paid_by')
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('PiPayPtl', Trans::getWord('paymentDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(4);

        return $portlet;
    }


}
