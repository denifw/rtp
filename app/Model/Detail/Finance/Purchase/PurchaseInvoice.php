<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Detail\Finance\Purchase;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceApprovalDao;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceDao;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceDetailDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail Invoice page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PurchaseInvoice extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'purchaseInvoice', 'pi_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'pi_ss_id' => $this->User->getSsId(),
            'pi_cur_id' => $this->User->Settings->getCurrencyId(),
            'pi_exchange_rate' => 1,
            'pi_of_id' => $this->getIntParameter('pi_of_id'),
            'pi_srv_id' => $this->getIntParameter('pi_srv_id'),
            'pi_reference' => $this->getStringParameter('pi_reference'),
            'pi_rel_id' => $this->getIntParameter('pi_rel_id'),
            'pi_rb_id' => $this->getIntParameter('pi_rb_id'),
            'pi_rel_of_id' => $this->getIntParameter('pi_rel_of_id'),
            'pi_cp_id' => $this->getIntParameter('pi_cp_id'),
            'pi_rel_reference' => $this->getStringParameter('pi_rel_reference'),
            'pi_date' => $this->getStringParameter('pi_date'),
            'pi_due_date' => $this->getStringParameter('pi_due_date'),
            'pi_ca_id' => $this->getIntParameter('pi_ca_id'),
        ];
        $piDao = new PurchaseInvoiceDao();
        $piDao->doInsertTransaction($colVal);
        $this->doUploadDocument($piDao->getLastInsertId());
        return $piDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doRequest') {
            $piaColVal = [
                'pia_pi_id' => $this->getDetailReferenceValue(),
            ];
            $piaDao = new PurchaseInvoiceApprovalDao();
            $piaDao->doInsertTransaction($piaColVal);
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'pi_pia_id' => $piaDao->getLastInsertId(),
            ]);
        } else if ($this->getFormAction() === 'doReject') {
            $piaColVal = [
                'pia_reject_reason' => $this->getStringParameter('pia_reject_reason'),
                'pia_deleted_by' => $this->User->getId(),
                'pia_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $piaDao = new PurchaseInvoiceApprovalDao();
            $piaDao->doUpdateTransaction($this->getIntParameter('pia_id'), $piaColVal);
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'pi_pia_id' => null,
            ]);
        } else if ($this->getFormAction() === 'doApprove') {
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('PurchaseInvoice', $this->getIntParameter('pi_of_id'), $this->getIntParameter('pi_rel_id'), $this->getIntParameter('pi_srv_id'));
            $piColVal = [
                'pi_number' => $number,
                'pi_approve_by' => $this->User->getId(),
                'pi_approve_on' => date('Y-m-d H:i:s'),
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), $piColVal);
        } else if ($this->isDeleteAction() === true) {
            $piColVal = [
                'pi_deleted_reason' => $this->getReasonDeleteAction(),
                'pi_deleted_by' => $this->User->getId(),
                'pi_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), $piColVal);
            $data = PurchaseInvoiceDetailDao::getByJopIdByPiId($this->getDetailReferenceValue());
            if (empty($data) === false) {
                $jopDao = new JobPurchaseDao();
                foreach ($data as $row) {
                    $jopDao->doUpdateTransaction($row['pid_jop_id'], [
                        'jop_pid_id' => null
                    ]);
                }
            }
        } else if ($this->getFormAction() === 'doPayment') {
            $piColVal = [
                'pi_pay_date' => $this->getStringParameter('pi_pay_date'),
                'pi_paid_ref' => $this->getStringParameter('pi_paid_ref'),
                'pi_paid_rb_id' => $this->getIntParameter('pi_paid_rb_id'),
                'pi_paid_by' => $this->User->getId(),
                'pi_paid_on' => date('Y-m-d H:i:s'),
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), $piColVal);
            # Upload Document.
            $file = $this->getFileParameter('pi_payment_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => 68,
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('paymentReceipt'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } else {
            $colVal = [
                'pi_of_id' => $this->getIntParameter('pi_of_id'),
                'pi_srv_id' => $this->getIntParameter('pi_srv_id'),
                'pi_reference' => $this->getStringParameter('pi_reference'),
                'pi_rel_id' => $this->getIntParameter('pi_rel_id'),
                'pi_rb_id' => $this->getIntParameter('pi_rb_id'),
                'pi_rel_of_id' => $this->getIntParameter('pi_rel_of_id'),
                'pi_cp_id' => $this->getIntParameter('pi_cp_id'),
                'pi_rel_reference' => $this->getStringParameter('pi_rel_reference'),
                'pi_date' => $this->getStringParameter('pi_date'),
                'pi_due_date' => $this->getStringParameter('pi_due_date'),
                'pi_ca_id' => $this->getIntParameter('pi_ca_id'),
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

            # Start Update Invoice Detail
            $pidIds = $this->getArrayParameter('pid_id');
            $jopIds = $this->getArrayParameter('pid_jop_id');
            $pidActives = $this->getArrayParameter('pid_active');
            if (empty($pidIds) === false) {
                $pidDao = new PurchaseInvoiceDetailDao();
                $jopDao = new JobPurchaseDao();
                foreach ($pidIds as $key => $value) {
                    if (array_key_exists($key, $pidActives) === true && $pidActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $pidColVal = [
                                'pid_pi_id' => $this->getDetailReferenceValue(),
                                'pid_jop_id' => $jopIds[$key],
                            ];
                            $pidDao->doInsertTransaction($pidColVal);
                            # Update Job Purchase Dao
                            $jopDao->doUpdateTransaction($jopIds[$key], [
                                'jop_pid_id' => $pidDao->getLastInsertId()
                            ]);
                        } else {
                            $pidDao->doUndoDeleteTransaction($value);
                            $jopDao->doUpdateTransaction($jopIds[$key], [
                                'jop_pid_id' => $value
                            ]);
                        }
                    } else {
                        if (empty($value) === false) {
                            $pidDao->doDeleteTransaction($value);
                            $jopDao->doUpdateTransaction($jopIds[$key], [
                                'jop_pid_id' => null
                            ]);
                        }
                    }
                }
            }
            # End Update Pid Update
            # Do Upload Document
            $this->doUploadDocument($this->getDetailReferenceValue());

        }
    }

    /**
     * Abstract function to load the data.
     *
     * @param int $docGroupReference To store the document group reference
     *
     * @return void
     */
    private function doUploadDocument(int $docGroupReference): void
    {
        $docDao = new DocumentDao();
        $invoiceFile = $this->getFileParameter('pi_invoice_file');
        $piDocId = null;
        $piDocTaxId = null;
        if ($invoiceFile !== null) {
            $colVal = [
                'doc_ss_id' => $this->User->getSsId(),
                'doc_dct_id' => 66,
                'doc_group_reference' => $docGroupReference,
                'doc_type_reference' => null,
                'doc_file_name' => time() . '.' . $invoiceFile->getClientOriginalExtension(),
                'doc_description' => Trans::getFinanceWord('purchaseInvoice'),
                'doc_file_size' => $invoiceFile->getSize(),
                'doc_file_type' => $invoiceFile->getClientOriginalExtension(),
                'doc_public' => 'Y',
            ];
            $docDao->doInsertTransaction($colVal);
            $piDocId = $docDao->getLastInsertId();
            $upload = new FileUpload($docDao->getLastInsertId());
            $upload->upload($invoiceFile);
            if ($this->isValidParameter('pi_doc_id')) {
                $docDao->doDeleteTransaction($this->getIntParameter('pi_doc_id'));
            }
        }
        $taxFile = $this->getFileParameter('pi_tax_file');
        if ($taxFile !== null) {
            $colVal = [
                'doc_ss_id' => $this->User->getSsId(),
                'doc_dct_id' => 67,
                'doc_group_reference' => $docGroupReference,
                'doc_type_reference' => null,
                'doc_file_name' => time() . '.' . $taxFile->getClientOriginalExtension(),
                'doc_description' => Trans::getFinanceWord('taxInvoice'),
                'doc_file_size' => $taxFile->getSize(),
                'doc_file_type' => $taxFile->getClientOriginalExtension(),
                'doc_public' => 'Y',
            ];
            $docDao->doInsertTransaction($colVal);
            $piDocTaxId = $docDao->getLastInsertId();
            $upload = new FileUpload($docDao->getLastInsertId());
            $upload->upload($taxFile);
            if ($this->isValidParameter('pi_doc_tax_id')) {
                $docDao->doDeleteTransaction($this->getIntParameter('pi_doc_tax_id'));
            }
        }
        if ($piDocId !== null || $piDocTaxId !== null) {
            $piColVal = [
                'pi_doc_id' => $piDocId,
                'pi_doc_tax_id' => $piDocTaxId,
            ];
            $piDao = new PurchaseInvoiceDao();
            $piDao->doUpdateTransaction($docGroupReference, $piColVal);
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
        if ($this->isInsert()) {
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        } else {
            $this->overridePageTitle();
            $dtParser = new DateTimeParser();
            if ($this->isValidParameter('pia_reject_reason')) {
                $this->View->addErrorMessage(Trans::getWord('invoiceRejected', 'message', '', [
                    'user' => $this->getStringParameter('pia_deleted_by'),
                    'time' => $dtParser->formatDateTime($this->getStringParameter('pia_deleted_on')),
                    'reason' => $this->getStringParameter('pia_reject_reason'),
                ]));
            }
            if ($this->isValidParameter('pi_deleted_on')) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('pi_deleted_by'),
                    'time' => $dtParser->formatDateTime($this->getStringParameter('pi_deleted_on')),
                    'reason' => $this->getStringParameter('pi_deleted_reason'),
                ]));
            }
            $this->Tab->addContent('general', $this->getWidget());
            if ($this->isAllowUpdate()) {
                # Show Update Form
                $this->Tab->addPortlet('general', $this->getGeneralPortlet());
                $this->Tab->addPortlet('general', $this->getDetailFormPortlet());
            } else {
                # Show View
                $this->setDisableUpdate();
                $this->View->addContent('hide1', $this->Field->getHidden('pi_of_id', $this->getIntParameter('pi_of_id')));
                $this->View->addContent('hide2', $this->Field->getHidden('pi_rel_id', $this->getIntParameter('pi_rel_id')));
                $this->View->addContent('hide3', $this->Field->getHidden('pi_srv_id', $this->getIntParameter('pi_srv_id')));
                $this->View->addContent('hide4', $this->Field->getHidden('pi_ca_id', $this->getIntParameter('pi_ca_id')));

                $this->Tab->addPortlet('general', $this->getInvoicePortlet());
                $this->Tab->addPortlet('general', $this->getVendorPortlet());
                if ($this->isValidParameter('pi_paid_on') === true) {
                    $this->Tab->addPortlet('general', $this->getPaymentPortlet());
                }
                $this->Tab->addPortlet('general', $this->getDetailPortlet());
            }
            $this->Tab->addPortlet('document', $this->getDocumentPortlet());
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetPortlet());
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
            $this->Validation->checkRequire('pi_of_id');
            $this->Validation->checkRequire('pi_srv_id');
            $this->Validation->checkRequire('pi_rel_id');
            $this->Validation->checkRequire('pi_rel_id');
            if ($this->isValidParameter('pi_rel_id')) {
                if ($this->getIntParameter('pi_rel_id') !== $this->User->getId()) {
                    $this->Validation->checkRequire('pi_rb_id');
                    $this->Validation->checkRequire('pi_rel_reference');
                    if ($this->isInsert()) {
                        $this->Validation->checkRequire('pi_invoice_file');
                    }
                } else {
                    $this->Validation->checkRequire('pi_ca_id');
                }
            }
            if ($this->isValidParameter('pi_tax_file')) {
                $this->Validation->checkFile('pi_tax_file');
            }
            if ($this->isValidParameter('pi_invoice_file')) {
                $this->Validation->checkFile('pi_invoice_file');
            }
            $this->Validation->checkMaxLength('pi_reference', 255);
            $this->Validation->checkMaxLength('pi_rel_reference', 255);
            $this->Validation->checkRequire('pi_rel_of_id');
            $this->Validation->checkRequire('pi_date');
            $this->Validation->checkDate('pi_date');
            $this->Validation->checkRequire('pi_due_date');
            $this->Validation->checkDate('pi_due_date');
        } else if ($this->getFormAction() === 'doApprove') {
            $this->Validation->checkRequire('pi_of_id');
            $this->Validation->checkRequire('pi_rel_id');
            $this->Validation->checkRequire('pi_srv_id');
        } else if ($this->getFormAction() === 'doReject') {
            $this->Validation->checkRequire('pia_id');
            $this->Validation->checkRequire('pia_reject_reason', 2, 255);
//        } else if ($this->getFormAction() === 'doDelete') {
//            $this->Validation->checkRequire('base_delete_reason', 3, 255);
        } else if ($this->getFormAction() === 'doPayment') {
            $this->Validation->checkRequire('pi_pay_date');
            $this->Validation->checkDate('pi_pay_date');
            $this->Validation->checkRequire('pi_paid_rb_id');
            $this->Validation->checkMaxLength('pi_paid_ref', 255);
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
        # Create Fields.
        $invOfField = $this->Field->getSelect('pi_of_id', $this->getIntParameter('pi_of_id'));
        $wheres = [];
        $wheres[] = '(of_deleted_on is null)';
        $wheres[] = '(of_rel_id = ' . $this->User->getRelId() . ')';
        $wheres[] = "(of_active = 'Y')";
        $wheres[] = "(of_invoice = 'Y')";
        $invOfField->addOptions(OfficeDao::loadSimpleData($wheres), 'of_name', 'of_id');

        $srvField = $this->Field->getSingleSelect('service', 'pi_service', $this->getStringParameter('pi_service'));
        $srvField->setHiddenField('pi_srv_id', $this->getIntParameter('pi_srv_id'));
        $srvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srvField->setEnableDetailButton(false);
        $srvField->setEnableNewButton(false);
        $srvField->addClearField('pi_ca_number');
        $srvField->addClearField('pi_ca_id');

        $vendorField = $this->Field->getSingleSelect('relation', 'pi_vendor', $this->getStringParameter('pi_vendor'));
        $vendorField->setHiddenField('pi_rel_id', $this->getIntParameter('pi_rel_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setEnableNewButton(false);
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('pi_rb_number');
        $vendorField->addClearField('pi_rb_id');
        $vendorField->addClearField('pi_vendor_office');
        $vendorField->addClearField('pi_rel_of_id');
        $vendorField->addClearField('pi_contact_person');
        $vendorField->addClearField('pi_cp_id');
        $vendorField->addClearField('pi_ca_number');
        $vendorField->addClearField('pi_ca_id');

        $rbField = $this->Field->getSingleSelect('relationBank', 'pi_rb_number', $this->getStringParameter('pi_rb_number'));
        $rbField->setHiddenField('pi_rb_id', $this->getIntParameter('pi_rb_id'));
        $rbField->addParameterById('rb_rel_id', 'pi_rel_id', Trans::getFinanceWord('vendor'));
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->setDetailReferenceCode('rb_id');

        $relOfField = $this->Field->getSingleSelect('office', 'pi_vendor_office', $this->getStringParameter('pi_vendor_office'));
        $relOfField->setHiddenField('pi_rel_of_id', $this->getIntParameter('pi_rel_of_id'));
        $relOfField->addParameterById('of_rel_id', 'pi_rel_id', Trans::getFinanceWord('vendor'));
        $relOfField->setDetailReferenceCode('of_id');

        $cpField = $this->Field->getSingleSelect('contactPerson', 'pi_contact_person', $this->getStringParameter('pi_contact_person'));
        $cpField->setHiddenField('pi_cp_id', $this->getIntParameter('pi_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'pi_rel_id', Trans::getFinanceWord('vendor'));
        $cpField->setDetailReferenceCode('cp_id');

        $caField = $this->Field->getText('pi_ca_number', $this->getStringParameter('pi_ca_number'));
        $caField->setReadOnly();

        if ($this->isUpdate()) {
            $srvField->setReadOnly(true);
            $vendorField->setReadOnly(true);
            $caField->setReadOnly(true);
        }

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getFinanceWord('service'), $srvField, true);
        $fieldSet->addField(Trans::getFinanceWord('vendor'), $vendorField, true);
        $fieldSet->addField(Trans::getFinanceWord('invoiceOffice'), $invOfField, true);
        $fieldSet->addField(Trans::getFinanceWord('bankAccount'), $rbField);
        $fieldSet->addField(Trans::getFinanceWord('poRef'), $this->Field->getText('pi_reference', $this->getStringParameter('pi_reference')));
        $fieldSet->addField(Trans::getFinanceWord('vendorRef'), $this->Field->getText('pi_rel_reference', $this->getStringParameter('pi_rel_reference')));
        $fieldSet->addField(Trans::getFinanceWord('vendorOffice'), $relOfField, true);
        $fieldSet->addField(Trans::getFinanceWord('picVendor'), $cpField);
        $fieldSet->addField(Trans::getFinanceWord('invoiceDate'), $this->Field->getCalendar('pi_date', $this->getStringParameter('pi_date')), true);
        $fieldSet->addField(Trans::getFinanceWord('dueDate'), $this->Field->getCalendar('pi_due_date', $this->getStringParameter('pi_due_date')), true);
        $fieldSet->addField(Trans::getFinanceWord('invoice'), $this->Field->getFile('pi_invoice_file', ''));
        $fieldSet->addField(Trans::getFinanceWord('taxInvoice'), $this->Field->getFile('pi_tax_file', ''));
        if ($this->isUpdate() === true && $this->isValidParameter('pi_ca_id') === true) {
            $fieldSet->addField(Trans::getFinanceWord('cashAdvance'), $caField);
        }
        $fieldSet->addHiddenField($this->Field->getHidden('pi_ca_id', $this->getIntParameter('pi_ca_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('pi_doc_id', $this->getIntParameter('pi_doc_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('pi_doc_tax_id', $this->getIntParameter('pi_doc_tax_id')));

        # Create a portlet box.
        $portlet = new Portlet('PiGnPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        if ($this->isValidParameter('pi_doc_tax_id')) {
            $btnTaxDoc = new Button('btnDocTax', Trans::getFinanceWord('taxInvoice'));
            $btnTaxDoc->setIcon(Icon::Download);
            $btnTaxDoc->addAttribute('class', 'tbn btn-primary pull-right btn-sm');
            $btnTaxDoc->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $this->getIntParameter('pi_doc_tax_id')) . "')");
            $portlet->addButton($btnTaxDoc);
        }
        if ($this->isValidParameter('pi_doc_id')) {
            $btnInvDoc = new Button('btnDocInv', Trans::getFinanceWord('invoice'));
            $btnInvDoc->setIcon(Icon::Download);
            $btnInvDoc->addAttribute('class', 'tbn btn-success pull-right btn-sm');
            $btnInvDoc->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $this->getIntParameter('pi_doc_id')) . "')");
            $portlet->addButton($btnInvDoc);
        }

        return $portlet;
    }


    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $results = '';
        $number = new NumberFormatter();
        $large = 12;
        $medium = 12;
        $small = 12;
        $extraSmall = 12;
        if ($this->isValidParameter('pi_ca_id')) {
            $large = 6;
            $medium = 6;
            $small = 6;
            $extraSmall = 12;
            $caField = new NumberGeneral();
            $data = [
                'title' => Trans::getFinanceWord('cashAdvance'),
                'icon' => '',
                'tile_style' => 'tile-stats tile-success',
                'amount' => 'IDR ' . $number->doFormatFloat($this->getFloatParameter('pi_ca_settlement')),
                'uom' => '',
                'url' => '',
            ];
            $caField->setData($data);
            $caField->setGridDimension($large, $medium, $small, $extraSmall);
            $results .= $caField->createView();
        }
        # damage Stock
        $invoice = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('invoiceAmount'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-primary',
            'amount' => 'IDR ' . $number->doFormatFloat($this->getFloatParameter('pi_total_amount')),
            'uom' => '',
            'url' => '',
        ];
        $invoice->setData($data);
        $invoice->setGridDimension($large, $medium, $small, $extraSmall);
        $results .= $invoice->createView();


        return $results;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getDetailFormPortlet(): Portlet
    {
        # Create a table.
        $table = new TableDatas('PiPidTbl');
        $table->setHeaderRow([
            'pid_id' => '',
            'pid_jop_id' => '',
            'pid_jo_number' => Trans::getFinanceWord('joNumber'),
            'pid_description' => Trans::getWord('description'),
            'pid_quantity' => Trans::getWord('qty'),
            'pid_rate' => Trans::getWord('rate'),
            'pid_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'pid_tax_name' => Trans::getWord('tax'),
            'pid_total' => Trans::getWord('total'),
            'pid_active' => Trans::getWord('select'),
        ]);
        $table->setRowsPerPage(30);
        $data = $this->loadInvoiceDetailData();
        $results = [];
        $i = 0;
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['pid_id'] = $this->Field->getHidden('pid_id[' . $i . ']', $row['pid_id']);
            $row['pid_jop_id'] = $this->Field->getHidden('pid_jop_id[' . $i . ']', $row['pid_jop_id']);

            $checked = false;
            if ($row['pid_active'] === 'Y') {
                $checked = true;
                $table->addCellAttribute('pid_active', $i, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('pid_active[' . $i . ']', 'Y', $checked);
            if (empty($row['pid_quantity']) === true || empty($row['pid_exchange_rate']) === true || empty($row['jop_tax_id']) === true) {
                $check->setReadOnly();
            }
            $row['pid_active'] = $check;
            $row['pid_description'] = $row['pid_cc_code'] . ' - ' . $row['pid_description'];
            $row['pid_quantity'] = $number->doFormatFloat($row['pid_quantity']) . ' - ' . $row['pid_uom_code'];
            $row['pid_rate'] = $row['pid_cur_iso'] . ' ' . $number->doFormatFloat($row['pid_rate']);
            if (empty($row['pid_exchange_rate']) === false) {
                $row['pid_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['pid_exchange_rate']);
                $row['pid_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['pid_total']);
            } else {
                $row['pid_total'] = $row['pid_cur_iso'] . ' ' . $number->doFormatFloat($row['pid_total']);
                $table->addCellAttribute('pid_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['pid_tax_name']) === true) {
                $table->addCellAttribute('pid_tax_name', $i, 'style', 'background-color: red;');
            }
            $results[] = $row;
            $i++;
        }
        $table->addRows($results);
        # Add special settings to the table
        $table->addColumnAttribute('pid_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('pid_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('pid_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('pid_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('pid_active', 'style', 'text-align: center;');
        $table->addColumnAttribute('pid_tax_name', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('PiPidPtl', Trans::getFinanceWord('invoiceDetail'));
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to get the page Field Set.
     *
     * @return array
     */
    private function loadInvoiceDetailData(): array
    {
        $wheres = [];
        $wheres[] = '(jop.jop_deleted_on IS NULL)';
        $wheres[] = '(jop.jop_rel_id = ' . $this->getIntParameter('pi_rel_id') . ')';
        $wheres[] = '(jo.jo_srv_id = ' . $this->getIntParameter('pi_srv_id') . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jop.jop_id NOT IN (SELECT (CASE WHEN p1.pid_jop_id IS NULL THEN 0 ELSE p1.pid_jop_id END)
                                            FROM purchase_invoice_detail as p1 INNER JOIN
                                            purchase_invoice as p2 ON p1.pid_pi_id = p2.pi_id
                                            WHERE (p2.pi_id <> ' . $this->getDetailReferenceValue() . ')
                                            AND (p2.pi_deleted_on IS NULL) AND (p1.pid_deleted_on IS NULL)
                                            GROUP BY p1.pid_jop_id))';
        if ($this->isValidParameter('pi_ca_id') === true) {
            $wheres[] = '(jo.jo_id IN (SELECT (CASE WHEN ca_jo_id IS NULL THEN 0 ELSE ca_jo_id END)
                                        FROM cash_advance
                                        WHERE ca_id = ' . $this->getIntParameter('pi_ca_id') . '))';
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jop.jop_id as pid_jop_id, jop.jop_jo_id, jo.jo_number as pid_jo_number, jop.jop_cc_id, jop.jop_rel_id,
                        jop.jop_description as pid_description, jop.jop_rate as pid_rate, jop.jop_quantity as pid_quantity,
                        jop.jop_uom_id, jop.jop_cur_id, jop.jop_exchange_rate as pid_exchange_rate, jop.jop_tax_id,
                       cc.cc_code AS pid_cc_code, uom.uom_code AS pid_uom_code, cur.cur_iso AS pid_cur_iso,
                       tax.tax_name AS pid_tax_name, rel.rel_name AS pid_relation, (CASE WHEN tax.tax_percent is null then 0 else tax.tax_percent END) as tax_percent,
                       jop.jop_total as pid_total, pid.pid_id, (CASE WHEN (pid.pid_active IS NULL) THEN 'N' ELSE pid.pid_active END) AS pid_active
                FROM job_purchase AS jop INNER JOIN
                     job_order as jo ON jo.jo_id = jop.jop_jo_id INNER JOIN
                     relation AS rel ON rel.rel_id = jop.jop_rel_id INNER JOIN
                     cost_code AS cc ON cc.cc_id = jop.jop_cc_id INNER JOIN
                     unit AS uom ON uom.uom_id = jop.jop_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = jop.jop_cur_id LEFT OUTER JOIN
                     (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                      from tax as t left OUTER join
                           (select td_tax_id, SUM(td_percent) as tax_percent
                            from tax_detail
                            where td_active = 'Y' and td_deleted_on is null
                            group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jop.jop_tax_id = tax.tax_id LEFT OUTER JOIN
                     (SELECT pid_id, pid_jop_id, (CASE WHEN (pid_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS pid_active
                          FROM purchase_invoice_detail
                          WHERE (pid_pi_id = " . $this->getDetailReferenceValue() . ")) AS pid ON jop.jop_id = pid.pid_jop_id ";
        $query .= $strWheres;
        $query .= ' ORDER BY pid.pid_active, jop.jop_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);

    }


    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getInvoicePortlet(): Portlet
    {
        $dtParser = new DateTimeParser();
        $data = [
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $this->getStringParameter('pi_service'),
            ],
            [
                'label' => Trans::getFinanceWord('invoiceOffice'),
                'value' => $this->getStringParameter('pi_invoice_office'),
            ],
            [
                'label' => Trans::getFinanceWord('poRef'),
                'value' => $this->getStringParameter('pi_reference'),
            ],
            [
                'label' => Trans::getFinanceWord('invoiceDate'),
                'value' => $dtParser->formatDate($this->getStringParameter('pi_date')),
            ],
            [
                'label' => Trans::getFinanceWord('dueDate'),
                'value' => $dtParser->formatDate($this->getStringParameter('pi_due_date')),
            ],
            [
                'label' => Trans::getFinanceWord('cashAdvance'),
                'value' => $this->getStringParameter('pi_ca_number'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('PiInvPtl', Trans::getFinanceWord('invoice'));
        $portlet->addText($content);
        if ($this->isValidParameter('pi_paid_on') === true) {
            $portlet->setGridDimension(4, 4, 12);
        } else {
            $portlet->setGridDimension(6, 6);
        }


        return $portlet;
    }


    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getVendorPortlet(): Portlet
    {
        $btnInvoice = '';
        $btnTax = '';
        if ($this->isValidParameter('pi_doc_tax_id')) {
            $btnTaxDoc = new Button('btnDocTax', Trans::getFinanceWord('taxInvoice'));
            $btnTaxDoc->setIcon(Icon::Download);
            $btnTaxDoc->addAttribute('class', 'tbn btn-primary pull-right btn-sm');
            $btnTaxDoc->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $this->getIntParameter('pi_doc_tax_id')) . "')");
            $btnTax = $btnTaxDoc;
        }
        if ($this->isValidParameter('pi_doc_id')) {
            $btnInvDoc = new Button('btnDocInv', Trans::getFinanceWord('invoice'));
            $btnInvDoc->setIcon(Icon::Download);
            $btnInvDoc->addAttribute('class', 'tbn btn-success pull-right btn-sm');
            $btnInvDoc->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $this->getIntParameter('pi_doc_id')) . "')");
            $btnInvoice = $btnInvDoc;
        }
        $data = [
            [
                'label' => Trans::getFinanceWord('vendor'),
                'value' => $this->getStringParameter('pi_vendor'),
            ],
            [
                'label' => Trans::getFinanceWord('vendorRef'),
                'value' => $this->getStringParameter('pi_rel_reference'),
            ],
            [
                'label' => Trans::getFinanceWord('bankAccount'),
                'value' => $this->getStringParameter('pi_rb_number'),
            ],
            [
                'label' => Trans::getFinanceWord('vendorOffice'),
                'value' => $this->getStringParameter('pi_rel_office'),
            ],
            [
                'label' => Trans::getFinanceWord('picVendor'),
                'value' => $this->getStringParameter('pi_contact_person'),
            ],
            [
                'label' => Trans::getFinanceWord('invoice'),
                'value' => $btnInvoice,
            ],
            [
                'label' => Trans::getFinanceWord('taxInvoice'),
                'value' => $btnTax,
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('PiRelPtl', Trans::getFinanceWord('vendor'));
        $portlet->addText($content);
        if ($this->isValidParameter('pi_paid_on') === true) {
            $portlet->setGridDimension(4, 4, 12);
        } else {
            $portlet->setGridDimension(6, 6);
        }

        return $portlet;
    }

    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getPaymentPortlet(): Portlet
    {
        $dt = new DateTimeParser();
        $data = [
            [
                'label' => Trans::getFinanceWord('paidOn'),
                'value' => $dt->formatDate($this->getStringParameter('pi_pay_date')),
            ],
            [
                'label' => Trans::getFinanceWord('paidBy'),
                'value' => $this->getStringParameter('pi_paid_by'),
            ],
            [
                'label' => Trans::getFinanceWord('apAccount'),
                'value' => $this->getStringParameter('pi_rbp_number'),
            ],
            [
                'label' => Trans::getFinanceWord('apName'),
                'value' => $this->getStringParameter('pi_rbp_name'),
            ],
            [
                'label' => Trans::getFinanceWord('apBank'),
                'value' => $this->getStringParameter('pi_rbp_bank'),
            ],
            [
                'label' => Trans::getFinanceWord('apBranch'),
                'value' => $this->getStringParameter('pi_rbp_branch'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('PiPayPtl', Trans::getFinanceWord('payment'));
        $portlet->addText($content);
        $portlet->setGridDimension(4, 4, 12);

        return $portlet;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        # Create a table.
        $table = new TableDatas('PiPidTbl');
        $table->setHeaderRow([
            'pid_cc_code' => Trans::getFinanceWord('costCode'),
            'pid_description' => Trans::getWord('description'),
            'pid_quantity' => Trans::getWord('qty'),
            'pid_uom_code' => Trans::getWord('uom'),
            'pid_rate' => Trans::getWord('rate'),
            'pid_tax_name' => Trans::getWord('tax'),
            'pid_total' => Trans::getWord('total'),
        ]);
        $table->setRowsPerPage(30);
        $wheres = [];
        $wheres[] = '(pid.pid_pi_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(pid.pid_deleted_on IS NULL)';
        if ($this->isValidParameter('pi_srv_id')) {
            $data = PurchaseInvoiceDetailDao::loadDataByJop($wheres);
            $table->addColumnAtTheBeginning('pid_jo_number', Trans::getFinanceWord('joNumber'));
        } else {
            $data = PurchaseInvoiceDetailDao::loadData($wheres);
        }
        $table->addRows($data);
        # Add special settings to the table
        $table->setColumnType('pid_quantity', 'float');
        $table->setColumnType('pid_rate', 'float');
        $table->setColumnType('pid_total', 'float');
        $table->setFooterType('pid_total', 'SUM');
        $table->addColumnAttribute('pid_uom_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('pid_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('pid_cc_code', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('PiPidPtl', Trans::getFinanceWord('invoiceDetail'));
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $piDao = new PurchaseInvoiceDao();
        $status = $piDao->generateStatus([
            'is_deleted' => $this->isValidParameter('pi_deleted_on'),
            'is_paid' => $this->isValidParameter('pi_paid_on'),
            'is_approved' => $this->isValidParameter('pi_approve_on'),
            'is_rejected' => $this->isValidParameter('pia_created_on') && $this->isValidParameter('pia_deleted_on'),
            'is_requested' => $this->isValidParameter('pia_created_on') && !$this->isValidParameter('pia_deleted_on'),
        ]);
        $title = Trans::getFinanceWord('purchaseInvoice');
        if ($this->isValidParameter('pi_number')) {
            $title = $this->getStringParameter('pi_number');
        }
        $title .= ' - ' . $status;
        $this->View->setDescription($title);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {

        return $this->PageSetting->checkPageRight('AllowUpdate')
            && !$this->isValidParameter('pi_deleted_on')
            && !$this->isValidParameter('pi_approve_on')
            && (!$this->isValidParameter('pia_created_on') || ($this->isValidParameter('pia_created_on') && $this->isValidParameter('pia_deleted_on')));
    }

    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowApprove(): bool
    {
        return $this->PageSetting->checkPageRight('AllowApproveReject')
            && !$this->isValidParameter('pi_deleted_on')
            && !$this->isValidParameter('pi_approve_on')
            && $this->isValidParameter('pia_created_on')
            && !$this->isValidParameter('pia_deleted_on');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowPaid(): bool
    {
        return $this->PageSetting->checkPageRight('AllowPaid')
            && $this->isValidParameter('pi_approve_on')
            && !$this->isValidParameter('pi_paid_on');
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate()) {
            if ($this->isAllowUpdate()) {
                # Create btn request
                $modal = $this->getRequestModal();
                $this->View->addModal($modal);
                $btnRequest = new ModalButton('PiRcBtn', Trans::getFinanceWord('requestApproval'), $modal->getModalId());
                $btnRequest->setIcon(Icon::Share)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnRequest);

                # Enable Btn Delete
                $this->setEnableDeleteButton();
            }
            if ($this->isAllowApprove()) {
                $modalApprove = $this->getApproveModal();
                $this->View->addModal($modalApprove);
                $btnApprove = new ModalButton('PiRcBtn', Trans::getFinanceWord('approve'), $modalApprove->getModalId());
                $btnApprove->setIcon(Icon::Check)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnApprove);
                $modalReject = $this->getRejectModal();
                $this->View->addModal($modalReject);
                $btnReject = new ModalButton('PiRjBtn', Trans::getFinanceWord('reject'), $modalReject->getModalId());
                $btnReject->setIcon(Icon::Remove)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnReject);
            }
            if ($this->isAllowPaid()) {
                # Button Paid
                $modalPayment = $this->getPaymentModal();
                $this->View->addModal($modalPayment);
                $btnPayment = new ModalButton('PiPayBtn', Trans::getFinanceWord('paid'), $modalPayment->getModalId());
                $btnPayment->setIcon(Icon::Money)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnPayment);
            }
            if ($this->isValidParameter('pi_ca_id')) {
                $url = url('/cashAdvance/detail?ca_id=' . $this->getIntParameter('pi_ca_id'));
                $btnView = new HyperLink('BtnCaView', $this->getStringParameter('pi_ca_number'), $url);
                $btnView->viewAsButton();
                $btnView->setIcon(Icon::Eye)->btnWarning()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnView);
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getRequestModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PiRcMdl', Trans::getFinanceWord('requestConfirmation'));
        $valid = true;
        $text = '';
        if ($this->getFloatParameter('pi_total_amount', 0.0) === 0.0) {
            $text = Trans::getWord('invoiceRequestWarningAmount', 'message');
            $valid = false;
        }
        if ($this->isValidParameter('pi_ca_id') && $this->getFloatParameter('pi_ca_settlement') !== $this->getFloatParameter('pi_total_amount')) {
            $text = Trans::getWord('invoiceRequestWarningCa', 'message');
            $valid = false;
        }
        if ($valid) {
            $text = Trans::getWord('invoiceRequestConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doRequest');
        } else {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $modal->setDisableBtnOk();
        }
        $modal->setBtnOkName(Trans::getFinanceWord('yesRequest'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PiAppMdl', Trans::getFinanceWord('approveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doApprove');
        $text = Trans::getWord('invoiceApproveConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesApprove'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getRejectModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('CaRjMdl', Trans::getFinanceWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReject');
        $showModal = false;
        if ($this->getFormAction() === 'doReject' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('rejectReason'), $this->Field->getTextArea('pia_reject_reason', $this->getParameterForModal('pia_reject_reason', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('pia_id', $this->getIntParameter('pia_id')));

        $text = Trans::getWord('invoiceRejectConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesReject'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    private function getDocumentPortlet(): Portlet
    {
        # Create table.
        $docTable = new Table('PiJoDocTbl');
        $docTable->setHeaderRow([
            'doc_group_text' => Trans::getWord('group'),
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
        ]);
        # load data
        $data = $this->loadDocumentData();
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->setColumnType('doc_created_on', 'datetime');
        $portlet = new Portlet('PiJoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return array
     */
    protected function loadDocumentData(): array
    {
        $piWheres = [];
        $piWheres[] = "(dcg.dcg_code = 'purchaseinvoice')";
        $piWheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $piWheres[] = '(doc.doc_deleted_on IS NULL)';
        $piWheres[] = '(doc.doc_ss_id = ' . $this->User->getSsId() . ')';
        $strPiWheres = ' WHERE ' . implode(' AND ', $piWheres);
        $siQuery = "SELECT 1 as doc_order, doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, '" . $this->getStringParameter('pi_number', '') . "' as doc_group_text
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                    users AS us ON us.us_id = doc.doc_created_by " . $strPiWheres;

        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'joborder')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $wheres[] = '(doc.doc_ss_id = ' . $this->User->getSsId() . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $joQuery = 'SELECT 2 as doc_order, doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, j.jo_number as doc_group_text
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                    users AS us ON us.us_id = doc.doc_created_by INNER JOIN
                    (SELECT jo.jo_id, jo.jo_number
                        FROM purchase_invoice_detail as pid INNER JOIN
                        job_purchase as jop ON pid.pid_jop_id = jop.jop_id INNER JOIN
                        job_order as jo ON jo.jo_id = jop.jop_jo_id
                    WHERE (jo.jo_deleted_on IS NULL) AND (jo.jo_ss_id = ' . $this->User->getSsId() . ')
                            AND (pid.pid_pi_id = ' . $this->getDetailReferenceValue() . ')
                            AND (jop.jop_deleted_on IS NULL) AND (pid.pid_deleted_on IS NULL)
                    GROUP BY jo.jo_id, jo.jo_number) as j ON doc.doc_group_reference = j.jo_id ' . $strWheres;
        $query = 'SELECT doc_order, doc_id, doc_dct_id, dct_code, dct_description, dct_dcg_id, dcg_code, dcg_description, doc_group_reference,
                    doc_type_reference, doc_file_name, doc_file_size, doc_file_type, doc_public,
                    doc_created_by, doc_creator, doc_created_on, doc_description, doc_group_text
                    FROM (' . $siQuery . ' UNION ALL ' . $joQuery . ') as j
                   ORDER BY doc_order';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get the time sheet field set
     *
     * @return Portlet
     */
    protected function getTimeSheetPortlet(): Portlet
    {
        $table = new Table('PtcTimeTbl');
        $table->setHeaderRow([
            'ts_action' => Trans::getWord('action'),
            'ts_creator' => Trans::getWord('user'),
            'ts_time' => Trans::getWord('time'),
            'ts_remark' => Trans::getWord('remark'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->setColumnType('ts_time', 'datetime');
        # Create a portlet box.
        $portlet = new Portlet('PiTimePtl', Trans::getWord('timeSheet'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $request = PurchaseInvoiceApprovalDao::getByPiId($this->getDetailReferenceValue());
        if ($this->isValidParameter('pi_deleted_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('canceled'),
                'ts_creator' => $this->getStringParameter('pi_deleted_by'),
                'ts_time' => $this->getStringParameter('pi_deleted_on'),
                'ts_remark' => $this->getStringParameter('pi_delete_reason'),
            ];
        }
        if ($this->isValidParameter('pi_paid_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('paid'),
                'ts_creator' => $this->getStringParameter('pi_paid_by'),
                'ts_time' => $this->getStringParameter('pi_paid_on'),
                'ts_remark' => $this->getStringParameter('pi_ca_number', $this->getStringParameter('pi_paid_ref')),
            ];
        }
        if ($this->isValidParameter('pi_approve_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('approve'),
                'ts_creator' => $this->getStringParameter('pi_approve_by'),
                'ts_time' => $this->getStringParameter('pi_approve_on'),
                'ts_remark' => '',
            ];
        }
        foreach ($request as $row) {
            if (empty($row['pia_deleted_on']) === false) {
                $result[] = [
                    'ts_action' => Trans::getFinanceWord('reject'),
                    'ts_creator' => $row['pia_deleted_by'],
                    'ts_time' => $row['pia_deleted_on'],
                    'ts_remark' => $row['pia_reject_reason'],
                ];
            }
            $result[] = [
                'ts_action' => Trans::getFinanceWord('request'),
                'ts_creator' => $row['pia_created_by'],
                'ts_time' => $row['pia_created_on'],
                'ts_remark' => '',
            ];
        }
        $result[] = [
            'ts_action' => Trans::getFinanceWord('draft'),
            'ts_creator' => $this->getStringParameter('pi_created_by'),
            'ts_time' => $this->getStringParameter('pi_created_on'),
            'ts_remark' => '',
        ];


        return $result;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getPaymentModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PiPayMdl', Trans::getFinanceWord('paymentConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doPayment');
        $showModal = false;
        if ($this->getFormAction() === 'doPayment' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('pi_pay_date') === false) {
            $this->setParameter('pi_pay_date', date('Y-m-d'));
        }
        # Relation bank Field
        $rbField = $this->Field->getSingleSelect('relationBank', 'pi_rbp_number', $this->getParameterForModal('pi_rbp_number', $showModal));
        $rbField->setHiddenField('pi_paid_rb_id', $this->getParameterForModal('pi_paid_rb_id', $showModal));
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->addParameter('rb_rel_id', $this->User->getRelId());
        $rbField->setEnableNewButton(false);
        $rbField->setEnableDetailButton(false);

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('pi_pay_date', $this->getParameterForModal('pi_pay_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('bankAccount'), $rbField, true);
        $fieldSet->addField(Trans::getFinanceWord('paymentRef'), $this->Field->getText('pi_paid_ref', $this->getParameterForModal('pi_paid_ref', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('pi_payment_file', ''));

        $text = Trans::getWord('invoicePaymentConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
