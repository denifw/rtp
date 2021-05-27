<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Detail\Finance\Sales;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Finance\Sales\SalesInvoiceApprovalDao;
use App\Model\Dao\Finance\Sales\SalesInvoiceDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Finance\Sales\SalesInvoiceDetailDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Master\Finance\PaymentTermsDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail SalesInvoice page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesInvoice extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'salesInvoice', 'si_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $siColVal = [
            'si_ss_id' => $this->User->getSsId(),
            'si_cur_id' => $this->User->Settings->getCurrencyId(),
            'si_exchange_rate' => 1,
            'si_of_id' => $this->getIntParameter('si_of_id'),
            'si_so_id' => $this->getIntParameter('si_so_id'),
            'si_rb_id' => $this->getIntParameter('si_rb_id'),
            'si_rel_id' => $this->getIntParameter('si_rel_id'),
            'si_rel_of_id' => $this->getIntParameter('si_rel_of_id'),
            'si_cp_id' => $this->getIntParameter('si_cp_id'),
            'si_rel_reference' => $this->getStringParameter('si_rel_reference'),
            'si_pt_id' => $this->getIntParameter('si_pt_id'),
            'si_manual' => $this->getStringParameter('si_manual', 'N'),
        ];
        $siDao = new SalesInvoiceDao();
        $siDao->doInsertTransaction($siColVal);
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
            $siColVal = [
                'si_of_id' => $this->getIntParameter('si_of_id'),
                'si_so_id' => $this->getIntParameter('si_so_id'),
                'si_rb_id' => $this->getIntParameter('si_rb_id'),
                'si_rel_id' => $this->getIntParameter('si_rel_id'),
                'si_rel_of_id' => $this->getIntParameter('si_rel_of_id'),
                'si_cp_id' => $this->getIntParameter('si_cp_id'),
                'si_rel_reference' => $this->getStringParameter('si_rel_reference'),
                'si_pt_id' => $this->getIntParameter('si_pt_id'),
                'si_manual' => $this->getStringParameter('si_manual', 'N'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $siColVal);
            # Start Update Invoice Detail
            $sidIds = $this->getArrayParameter('sid_id');
            $josIds = $this->getArrayParameter('sid_jos_id');
            $sidActives = $this->getArrayParameter('sid_active');
            $josDao = new JobSalesDao();
            if (empty($sidIds) === false) {
                $sidDao = new SalesInvoiceDetailDao();
                foreach ($sidIds as $key => $value) {
                    if (array_key_exists($key, $sidActives) === true && $sidActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $sidColVal = [
                                'sid_si_id' => $this->getDetailReferenceValue(),
                                'sid_jos_id' => $josIds[$key],
                            ];
                            $sidDao->doInsertTransaction($sidColVal);
                            $josDao->doUpdateTransaction($josIds[$key], [
                                'jos_sid_id' => $sidDao->getLastInsertId()
                            ]);
                        } else {
                            $sidDao->doUndoDeleteTransaction($value);
                            $josDao->doUpdateTransaction($josIds[$key], [
                                'jos_sid_id' => $value
                            ]);
                        }
                    } else {
                        if (empty($value) === false) {
                            $sidDao->doDeleteTransaction($value);
                            $josDao->doUpdateTransaction($josIds[$key], [
                                'jos_sid_id' => null
                            ]);
                        }
                    }
                }
            }
            # End Update Pid Update
        } else if ($this->getFormAction() === 'doRequest') {
            $siaColVal = [
                'sia_si_id' => $this->getDetailReferenceValue(),
            ];
            $siaDao = new SalesInvoiceApprovalDao();
            $siaDao->doInsertTransaction($siaColVal);
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'si_sia_id' => $siaDao->getLastInsertId(),
            ]);
        } else if ($this->getFormAction() === 'doApprove') {
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('SalesInvoice', $this->getIntParameter('si_of_id'), $this->getIntParameter('si_rel_id'));
            $today = DateTimeParser::createDateTime();
            $dueDate = DateTimeParser::createDateTime();
            $dueDate->modify('+' . $this->getIntParameter('si_pt_days') . ' days');
            $siColVal = [
                'si_number' => $number,
                'si_date' => $today->format('Y-m-d'),
                'si_due_date' => $dueDate->format('Y-m-d'),
                'si_approve_by' => $this->User->getId(),
                'si_approve_on' => date('Y-m-d H:i:s'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $siColVal);
        } else if ($this->getFormAction() === 'doReject') {
            $siaColVal = [
                'sia_reject_reason' => $this->getStringParameter('sia_reject_reason'),
                'sia_deleted_by' => $this->User->getId(),
                'sia_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $siaDao = new SalesInvoiceApprovalDao();
            $siaDao->doUpdateTransaction($this->getIntParameter('sia_id'), $siaColVal);
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'si_sia_id' => null,
            ]);
        } else if ($this->getFormAction() === 'doPayment') {
            $dateTime = $this->getStringParameter('si_date_pay') . ' ' . $this->getStringParameter('si_time_pay') . ':00';
            $siColVal = [
                'si_pay_time' => $dateTime,
                'si_paid_ref' => $this->getStringParameter('si_paid_ref'),
                'si_paid_by' => $this->User->getId(),
                'si_paid_on' => date('Y-m-d H:i:s'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $siColVal);
            # Upload Document.
            $file = $this->getFileParameter('si_payment_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => 72,
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
        } else if ($this->isDeleteAction() === true) {
            $siColVal = [
                'si_deleted_reason' => $this->getReasonDeleteAction(),
                'si_deleted_by' => $this->User->getId(),
                'si_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $siColVal);
            # Update Job Sales Data
            $data = SalesInvoiceDetailDao::getBySiId($this->getDetailReferenceValue());
            if (empty($data) === false) {
                $josDao = new JobSalesDao();
                foreach ($data as $row) {
                    if (empty($row['sid_jos_id']) === false) {
                        $josDao->doUpdateTransaction($row['sid_jos_id'], [
                            'jos_sid_id' => null
                        ]);
                    }
                }
            }
        } else if ($this->getFormAction() === 'doUploadDocument') {
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
        } else if ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } else if ($this->getFormAction() === 'doUpdateDetail') {
            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('sid_rate') * $this->getFloatParameter('sid_quantity');
            if ($this->isValidParameter('sid_tax_id')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('sid_tax_id'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $sidDao = new SalesInvoiceDetailDao();
            $colVal = [
                'sid_si_id' => $this->getDetailReferenceValue(),
                'sid_cc_id' => $this->getIntParameter('sid_cc_id'),
                'sid_description' => $this->getStringParameter('sid_description'),
                'sid_quantity' => $this->getFloatParameter('sid_quantity'),
                'sid_rate' => $this->getFloatParameter('sid_rate'),
                'sid_uom_id' => $this->getIntParameter('sid_uom_id'),
                'sid_exchange_rate' => 1,
                'sid_cur_id' => 1,
                'sid_tax_id' => $this->getIntParameter('sid_tax_id'),
                'sid_total' => $total,
            ];
            if ($this->isValidParameter('sid_id')) {
                $sidDao->doUpdateTransaction($this->getIntParameter('sid_id'), $colVal);
            } else {
                $sidDao->doInsertTransaction($colVal);
            }
        } else if ($this->getFormAction() === 'doDeleteDetail') {
            $sidDao = new SalesInvoiceDetailDao();
            $sidDao->doDeleteTransaction($this->getIntParameter('sid_id_del'));
        } else if ($this->getFormAction() === 'doReceive') {
            $dateTime = $this->getStringParameter('si_receive_date') . ' ' . $this->getStringParameter('si_receive_time') . ':00';
            $siColVal = [
                'si_receive_id' => $this->getIntParameter('si_receive_id'),
                'si_receive_by' => $this->User->getId(),
                'si_receive_on' => $dateTime,
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $siColVal);
            # Upload Document.
            $file = $this->getFileParameter('si_receive_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => 73,
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('proofOfHandover'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
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
        if ($this->isInsert()) {
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        } else {
            $this->overridePageTitle();
            $dtParser = new DateTimeParser();
            if ($this->isValidParameter('sia_reject_reason')) {
                $this->View->addErrorMessage(Trans::getWord('invoiceRejected', 'message', '', [
                    'user' => $this->getStringParameter('sia_deleted_by'),
                    'time' => $dtParser->formatDateTime($this->getStringParameter('sia_deleted_on')),
                    'reason' => $this->getStringParameter('sia_reject_reason'),
                ]));
            }
            if ($this->isValidParameter('si_deleted_on')) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('si_deleted_by'),
                    'time' => $dtParser->formatDateTime($this->getStringParameter('si_deleted_on')),
                    'reason' => $this->getStringParameter('si_deleted_reason'),
                ]));
            }
            $this->Tab->addContent('general', $this->getWidget());
            if ($this->isAllowUpdate()) {
                # Show Update Form
                $this->Tab->addPortlet('general', $this->getGeneralPortlet());
                if ($this->getStringParameter('si_manual', 'N') === 'Y') {
                    $this->Tab->addPortlet('general', $this->getManualDetailPortlet());
                } else {
                    $this->Tab->addPortlet('general', $this->getDetailFormPortlet());
                }
            } else {
                # Show View
                $this->setDisableUpdate();
                $this->View->addContent('hide1', $this->Field->getHidden('si_of_id', $this->getIntParameter('si_of_id')));
                $this->View->addContent('hide2', $this->Field->getHidden('si_rel_id', $this->getIntParameter('si_rel_id')));
                $this->View->addContent('hide4', $this->Field->getHidden('si_pt_days', $this->getIntParameter('si_pt_days')));

                $this->Tab->addPortlet('general', $this->getInvoicePortlet());
                $this->Tab->addPortlet('general', $this->getCustomerPortlet());
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
            $this->Validation->checkRequire('si_of_id');
            $this->Validation->checkRequire('si_rb_id');
            $this->Validation->checkRequire('si_pt_id');
            $this->Validation->checkRequire('si_rel_id');
            $this->Validation->checkRequire('si_rel_of_id');
            $this->Validation->checkRequire('si_cp_id');
            if ($this->getStringParameter('si_manual', 'N') === 'Y') {
                $this->Validation->checkRequire('si_rel_reference', 1, 255);
            } else {
                $this->Validation->checkMaxLength('si_rel_reference', 255);
            }
        } else if ($this->getFormAction() === 'doApprove') {
            $this->Validation->checkRequire('si_of_id');
            $this->Validation->checkRequire('si_rel_id');
            $this->Validation->checkRequire('si_pt_days');
        } else if ($this->getFormAction() === 'doReject') {
            $this->Validation->checkRequire('sia_id');
            $this->Validation->checkRequire('sia_reject_reason', 2, 255);
        } else if ($this->getFormAction() === 'doPayment') {
            $this->Validation->checkRequire('si_date_pay');
            $this->Validation->checkDate('si_date_pay');
            $this->Validation->checkRequire('si_time_pay');
            $this->Validation->checkTime('si_time_pay');
            $this->Validation->checkMaxLength('si_paid_ref', 255);
        } else if ($this->getFormAction() === 'doUploadDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
            $this->Validation->checkRequire('doc_description');
        } else if ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        } else if ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('sid_cc_id');
            $this->Validation->checkRequire('sid_description', 1, 150);
            $this->Validation->checkRequire('sid_rate');
            $this->Validation->checkFloat('sid_rate');
            $this->Validation->checkRequire('sid_quantity');
            $this->Validation->checkFloat('sid_quantity');
            $this->Validation->checkRequire('sid_uom_id');
        } else if ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('sid_id_del');
        } else if ($this->getFormAction() === 'doReceive') {
            $this->Validation->checkRequire('si_receive_date');
            $this->Validation->checkDate('si_receive_date');
            $this->Validation->checkRequire('si_receive_time');
            $this->Validation->checkTime('si_receive_time');
            $this->Validation->checkRequire('si_receive_id');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {

        return $this->PageSetting->checkPageRight('AllowUpdate')
            && !$this->isValidParameter('si_deleted_on')
            && !$this->isValidParameter('si_approve_on')
            && (!$this->isValidParameter('sia_created_on') || ($this->isValidParameter('sia_created_on') && $this->isValidParameter('sia_deleted_on')));
    }

    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowApprove(): bool
    {
        return $this->PageSetting->checkPageRight('AllowApproveReject')
            && !$this->isValidParameter('si_deleted_on')
            && !$this->isValidParameter('si_approve_on')
            && $this->isValidParameter('sia_created_on')
            && !$this->isValidParameter('sia_deleted_on');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowDocumentReceive(): bool
    {
        return $this->PageSetting->checkPageRight('AllowDocumentReceive')
            && $this->isValidParameter('si_approve_on')
            && !$this->isValidParameter('si_receive_on');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return bool
     */
    private function isAllowPaid(): bool
    {
        return $this->PageSetting->checkPageRight('AllowPaid')
            && $this->isValidParameter('si_receive_on')
            && !$this->isValidParameter('si_pay_time');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Create Fields.
        $invOfField = $this->Field->getSelect('si_of_id', $this->getIntParameter('si_of_id'));
        $wheres = [];
        $wheres[] = '(of_deleted_on is null)';
        $wheres[] = '(of_rel_id = ' . $this->User->getRelId() . ')';
        $wheres[] = "(of_active = 'Y')";
        $wheres[] = "(of_invoice = 'Y')";
        $invOfField->addOptions(OfficeDao::loadSimpleData($wheres), 'of_name', 'of_id');

        $rbField = $this->Field->getSingleSelect('relationBank', 'si_rb_number', $this->getStringParameter('si_rb_number'));
        $rbField->setHiddenField('si_rb_id', $this->getIntParameter('si_rb_id'));
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->addParameter('rb_rel_id', $this->User->getRelId());
        $rbField->setDetailReferenceCode('rb_id');

        $customerField = $this->Field->getSingleSelect('relation', 'si_customer', $this->getStringParameter('si_customer'));
        $customerField->setHiddenField('si_rel_id', $this->getIntParameter('si_rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableNewButton(false);
        $customerField->setDetailReferenceCode('rel_id');
        $customerField->addClearField('si_cust_office');
        $customerField->addClearField('si_rel_of_id');
        $customerField->addClearField('si_pic_cust');
        $customerField->addClearField('si_cp_id');

        $relOfField = $this->Field->getSingleSelect('office', 'si_cust_office', $this->getStringParameter('si_cust_office'));
        $relOfField->setHiddenField('si_rel_of_id', $this->getIntParameter('si_rel_of_id'));
        $relOfField->addParameterById('of_rel_id', 'si_rel_id', Trans::getFinanceWord('customer'));
        $relOfField->setDetailReferenceCode('of_id');

        $cpField = $this->Field->getSingleSelect('contactPerson', 'si_pic_cust', $this->getStringParameter('si_pic_cust'));
        $cpField->setHiddenField('si_cp_id', $this->getIntParameter('si_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'si_rel_id', Trans::getFinanceWord('customer'));
        $cpField->setDetailReferenceCode('cp_id');


        # Create SO Field.
        $soField = $this->Field->getSingleSelectTable('so', 'si_so_number', $this->getStringParameter('si_so_number'), 'loadUnInvoiceData');
        $soField->setHiddenField('si_so_id', $this->getIntParameter('si_so_id'));
        $soField->setTableColumns([
            'so_number' => Trans::getFinanceWord('number'),
            'so_container' => Trans::getFinanceWord('container'),
            'so_party_number' => Trans::getFinanceWord('party'),
            'so_reference' => Trans::getFinanceWord('reference'),
            'so_order_date' => Trans::getFinanceWord('orderDate'),
        ]);
        $soField->setFilters([
            'so_number' => Trans::getFinanceWord('number'),
            'so_reference' => Trans::getFinanceWord('reference'),
        ]);
        $soField->setAutoCompleteFields([
            'si_rel_reference' => 'so_customer_ref',
        ]);
        $soField->setValueCode('so_id');
        $soField->setLabelCode('so_number');
        $soField->addParameter('so_ss_id', $this->User->getSsId());
        $soField->addParameterById('so_rel_id', 'si_rel_id', Trans::getFinanceWord('customer'));
        $soField->addParameterById('so_invoice_of_id', 'si_of_id', Trans::getFinanceWord('invoiceOffice'));
        $this->View->addModal($soField->getModal());

        # Create Payment Terms
        $ptField = $this->Field->getSelect('si_pt_id', $this->getIntParameter('si_pt_id'));
        $wheres = [];
        $wheres[] = '(pt_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = "(pt_active = 'Y')";
        $wheres[] = '(pt_deleted_on IS NULL)';
        $ptField->addOptions(PaymentTermsDao::loadData($wheres), 'pt_name', 'pt_id');

        $manualField = $this->Field->getYesNo('si_manual', $this->getStringParameter('si_manual'));
        if ($this->isUpdate()) {
            $invOfField->setReadOnly(true);
            $customerField->setReadOnly(true);
            $manualField->setReadOnly();
            $soField->setReadOnly();
        }
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getFinanceWord('invoiceOffice'), $invOfField, true);
        $fieldSet->addField(Trans::getFinanceWord('customer'), $customerField, true);
        $fieldSet->addField(Trans::getFinanceWord('paymentTerms'), $ptField, true);
        $fieldSet->addField(Trans::getFinanceWord('customerOffice'), $relOfField, true);
        $fieldSet->addField(Trans::getFinanceWord('bankAccount'), $rbField, true);
        $fieldSet->addField(Trans::getFinanceWord('picCustomer'), $cpField, true);
        $fieldSet->addField(Trans::getFinanceWord('soNumber'), $soField);
        $fieldSet->addField(Trans::getFinanceWord('customerRef'), $this->Field->getText('si_rel_reference', $this->getStringParameter('si_rel_reference')));
//        $fieldSet->addField(Trans::getFinanceWord('manualProcess'), $manualField, true);

        # Create a portlet box.
        $portlet = new Portlet('SiFormPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $siDao = new SalesInvoiceDao();
        $status = $siDao->generateStatus([
            'is_deleted' => $this->isValidParameter('si_deleted_on'),
            'is_paid' => $this->isValidParameter('si_paid_on'),
            'is_received' => $this->isValidParameter('si_receive_on'),
            'is_approved' => $this->isValidParameter('si_approve_on'),
            'is_rejected' => $this->isValidParameter('sia_created_on') && $this->isValidParameter('sia_deleted_on'),
            'is_requested' => $this->isValidParameter('sia_created_on') && !$this->isValidParameter('sia_deleted_on'),
        ]);
        $title = Trans::getFinanceWord('salesInvoice');
        if ($this->isValidParameter('si_number')) {
            $title = $this->getStringParameter('si_number');
        }
        $title .= ' - ' . $status;
        $this->View->setDescription($title);
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
        if ($this->isValidParameter('si_due_date')) {
            $large = 6;
            $medium = 6;
            $small = 6;
            $extraSmall = 12;
            $aging = 0;
            $class = 'tile-stats tile-warning';
            $dueDate = DateTimeParser::createFromFormat($this->getStringParameter('si_due_date') . ' 01:00:00');
            $today = DateTimeParser::createFromFormat(date('Y-m-d') . ' 01:00:00');
            $isPaid = false;
            if ($this->isValidParameter('si_pay_time')) {
                $class = 'tile-stats tile-success';
                $isPaid = true;
                $today = DateTimeParser::createFromFormat(mb_substr($this->getStringParameter('si_pay_time'), 0, 10) . ' 01:00:00');
            }
            if ($dueDate !== null && $today !== null) {
                $diff = DateTimeParser::different($dueDate, $today);
                $diffDays = (int)$diff['days'];
                if ($dueDate > $today) {
                    if ($isPaid === false && $diffDays > 2) {
                        $class = 'tile-stats tile-success';
                    }
                }
                if ($dueDate < $today) {
                    $class = 'tile-stats tile-danger';
                    $aging = $diffDays;
                }
            }

            $caField = new NumberGeneral();
            $data = [
                'title' => Trans::getFinanceWord('overDueDays'),
                'icon' => '',
                'tile_style' => $class,
                'amount' => $number->doFormatInteger($aging),
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
            'amount' => 'IDR ' . $number->doFormatFloat($this->getFloatParameter('si_total_amount')),
            'uom' => '',
            'url' => '',
        ];
        $invoice->setData($data);
        $invoice->setGridDimension($large, $medium, $small, $extraSmall);
        $results .= $invoice->createView();


        return $results;
    }


    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    private function getDocumentPortlet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('SiJoDocTbl');
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

            if ($row['dcg_code'] === 'salesinvoice') {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['doc_delete'] = $btnDel;
            }

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->setColumnType('doc_created_on', 'datetime');
        $portlet = new Portlet('PiJoDocPtl', Trans::getWord('document'));
        if ($this->PageSetting->checkPageRight('AllowUpdate')) {
            $docTable->addColumnAtTheEnd('doc_delete', Trans::getWord('delete'));
            $docTable->addColumnAttribute('doc_delete', 'style', 'text-align: center');
            # create modal.
            $docModal = $this->getDocumentModal();
            $this->View->addModal($docModal);
            $btnDocMdl = new ModalButton('btnSiDocMdl', Trans::getWord('upload'), $docModal->getModalId());
            $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnDocMdl);
        }
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
        $siWheres = [];
        $siWheres[] = "(dcg.dcg_code = 'salesinvoice')";
        $siWheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $siWheres[] = '(doc.doc_deleted_on IS NULL)';
        $siWheres[] = '(doc.doc_ss_id = ' . $this->User->getSsId() . ')';
        $strSiWheres = ' WHERE ' . implode(' AND ', $siWheres);
        $siQuery = "SELECT 1 as doc_order, doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, '" . $this->getStringParameter('si_number', '') . "' as doc_group_text
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                    users AS us ON us.us_id = doc.doc_created_by " . $strSiWheres;
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
                        FROM sales_invoice_detail as sid INNER JOIN
                        job_sales as jos ON sid.sid_jos_id = jos.jos_id INNER JOIN
                        job_order as jo ON jo.jo_id = jos.jos_jo_id
                    WHERE (jo.jo_deleted_on IS NULL) AND (jo.jo_ss_id = ' . $this->User->getSsId() . ')
                            AND (sid.sid_si_id = ' . $this->getDetailReferenceValue() . ')
                            AND (jos.jos_deleted_on IS NULL) AND (sid.sid_deleted_on IS NULL)
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
        $request = SalesInvoiceApprovalDao::getBySalesInvoice($this->getDetailReferenceValue());
        if ($this->isValidParameter('si_deleted_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('canceled'),
                'ts_creator' => $this->getStringParameter('si_deleted_by'),
                'ts_time' => $this->getStringParameter('si_deleted_on'),
                'ts_remark' => $this->getStringParameter('si_delete_reason'),
            ];
        }
        if ($this->isValidParameter('si_paid_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('paid'),
                'ts_creator' => $this->getStringParameter('si_paid_by'),
                'ts_time' => $this->getStringParameter('si_paid_on'),
                'ts_remark' => $this->getStringParameter('si_ca_number', $this->getStringParameter('si_paid_ref')),
            ];
        }
        if ($this->isValidParameter('si_receive_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('invoiceReceived'),
                'ts_creator' => $this->getStringParameter('si_receive_by'),
                'ts_time' => $this->getStringParameter('si_receive_on'),
                'ts_remark' => $this->getStringParameter('si_receiver'),
            ];
        }
        if ($this->isValidParameter('si_approve_on') === true) {
            $result[] = [
                'ts_action' => Trans::getFinanceWord('approve'),
                'ts_creator' => $this->getStringParameter('si_approve_by'),
                'ts_time' => $this->getStringParameter('si_approve_on'),
                'ts_remark' => '',
            ];
        }
        foreach ($request as $row) {
            if (empty($row['sia_deleted_on']) === false) {
                $result[] = [
                    'ts_action' => Trans::getFinanceWord('reject'),
                    'ts_creator' => $row['sia_deleted_by'],
                    'ts_time' => $row['sia_deleted_on'],
                    'ts_remark' => $row['sia_reject_reason'],
                ];
            }
            $result[] = [
                'ts_action' => Trans::getFinanceWord('request'),
                'ts_creator' => $row['sia_created_by'],
                'ts_time' => $row['sia_created_on'],
                'ts_remark' => '',
            ];
        }
        $result[] = [
            'ts_action' => Trans::getFinanceWord('draft'),
            'ts_creator' => $this->getStringParameter('si_created_by'),
            'ts_time' => $this->getStringParameter('si_created_on'),
            'ts_remark' => '',
        ];


        return $result;
    }


    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getInvoicePortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $this->getStringParameter('si_service'),
            ],
            [
                'label' => Trans::getFinanceWord('invoiceOffice'),
                'value' => $this->getStringParameter('si_invoice_office'),
            ],
            [
                'label' => Trans::getFinanceWord('bankAccount'),
                'value' => $this->getStringParameter('si_rb_number'),
            ],
            [
                'label' => Trans::getFinanceWord('paymentTerms'),
                'value' => $this->getStringParameter('si_payment_terms'),
            ],
        ];
        if ($this->isValidParameter('si_approve_on')) {
            $dtParser = new DateTimeParser();
            $data[] = [
                'label' => Trans::getFinanceWord('invoiceDate'),
                'value' => $dtParser->formatDate($this->getStringParameter('si_date')),
            ];
            $data[] = [
                'label' => Trans::getFinanceWord('dueDate'),
                'value' => $dtParser->formatDate($this->getStringParameter('si_due_date')),
            ];
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('SiInvPtl', Trans::getFinanceWord('invoice'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getCustomerPortlet(): Portlet
    {
        $dtParser = new DateTimeParser();
        $data = [
            [
                'label' => Trans::getFinanceWord('customer'),
                'value' => $this->getStringParameter('si_customer'),
            ],
            [
                'label' => Trans::getFinanceWord('customerRef'),
                'value' => $this->getStringParameter('si_rel_reference'),
            ],
            [
                'label' => Trans::getFinanceWord('customerOffice'),
                'value' => $this->getStringParameter('si_cust_office'),
            ],
            [
                'label' => Trans::getFinanceWord('picCustomer'),
                'value' => $this->getStringParameter('si_pic_cust'),
            ],
        ];
        if ($this->isValidParameter('si_so_id')) {
            $data[] = [
                'label' => Trans::getFinanceWord('soNumber'),
                'value' => $this->getStringParameter('si_so_number'),
            ];
        }
        if ($this->isValidParameter('si_receive_on')) {
            $data[] = [
                'label' => Trans::getFinanceWord('invoiceReceivedOn'),
                'value' => $dtParser->formatDateTime($this->getStringParameter('si_receive_on')),
            ];
            $data[] = [
                'label' => Trans::getFinanceWord('invoiceReceivedBy'),
                'value' => $this->getStringParameter('si_receiver'),
            ];
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('SiRelPtl', Trans::getFinanceWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

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
        $table = new TableDatas('SiSidTbl');
        $table->setHeaderRow([
            'sid_jo_number' => Trans::getFinanceWord('joNumber'),
            'sid_description' => Trans::getFinanceWord('description'),
            'sid_quantity' => Trans::getFinanceWord('quantity'),
            'sid_rate' => Trans::getFinanceWord('rate'),
            'sid_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'sid_tax_name' => Trans::getFinanceWord('tax'),
            'sid_type' => Trans::getFinanceWord('type'),
            'sid_total' => Trans::getFinanceWord('total'),
        ]);
        $table->setRowsPerPage(30);
        $wheres = [];
        $wheres[] = '(sid.sid_si_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(sid.sid_deleted_on IS NULL)';
        $data = SalesInvoiceDetailDao::loadData($wheres);
        $number = new NumberFormatter($this->User);
        $rows = [];
        foreach ($data as $row) {
            if ($row['sid_type'] === 'S') {
                $row['sid_type'] = new LabelPrimary(Trans::getFinanceWord('revenue'));
            } else {
                $row['sid_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $row['sid_description'] = $row['sid_cc_code'] . ' - ' . $row['sid_description'];
            $row['sid_quantity'] = $number->doFormatFloat($row['sid_quantity']) . ' ' . $row['sid_uom_code'];
            $row['sid_rate'] = $row['sid_cur_iso'] . ' ' . $number->doFormatFloat($row['sid_rate']);
            $row['sid_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_exchange_rate']);
            $row['sid_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_total']);

            $rows[] = $row;
        }
        $table->addRows($rows);
        # Add special settings to the table
        $table->addColumnAttribute('sid_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('sid_type', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SiSidPtl', Trans::getFinanceWord('invoiceDetail'));
        $portlet->addTable($table);
        return $portlet;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getDetailFormPortlet(): Portlet
    {
        # Create a table.
        $table = new TableDatas('SiSidTbl');
        $table->setHeaderRow([
            'sid_id' => '',
            'sid_jos_id' => '',
            'sid_jo_number' => Trans::getFinanceWord('joNumber'),
            'sid_description' => Trans::getFinanceWord('description'),
            'sid_quantity' => Trans::getFinanceWord('quantity'),
            'sid_rate' => Trans::getFinanceWord('rate'),
            'sid_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'sid_tax_name' => Trans::getFinanceWord('tax'),
            'sid_type' => Trans::getFinanceWord('type'),
            'sid_total' => Trans::getFinanceWord('total'),
            'sid_active' => Trans::getFinanceWord('select'),
        ]);
        $table->setRowsPerPage(30);
        $data = $this->loadInvoiceDetailData();
        $results = [];
        $i = 0;
        $number = new NumberFormatter($this->User);
        $showSo = true;
        if ($this->isValidParameter('si_so_id') === true) {
            $showSo = false;
        }
        foreach ($data as $row) {
            if ($showSo === true) {
                $row['sid_jo_number'] = StringFormatter::generateTableView([
                    $row['sid_jo_number'], $row['sid_so_number']
                ]);
            }
            if ($row['sid_type'] === 'S') {
                $row['sid_type'] = new LabelPrimary(Trans::getFinanceWord('revenue'));
            } else {
                $row['sid_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $row['sid_id'] = $this->Field->getHidden('sid_id[' . $i . ']', $row['sid_id']);
            $row['sid_jos_id'] = $this->Field->getHidden('sid_jos_id[' . $i . ']', $row['sid_jos_id']);

            $checked = false;
            if ($row['sid_active'] === 'Y') {
                $checked = true;
                $table->addCellAttribute('sid_active', $i, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('sid_active[' . $i . ']', 'Y', $checked);
            if (empty($row['sid_quantity']) === true || empty($row['sid_exchange_rate']) === true || empty($row['sid_tax_name']) === true) {
                $check->setReadOnly();
            }
            $row['sid_active'] = $check;

            $row['sid_description'] = $row['sid_cc_code'] . ' - ' . $row['sid_description'];
            $row['sid_quantity'] = $number->doFormatFloat($row['sid_quantity']) . ' ' . $row['sid_uom_code'];
            $row['sid_rate'] = $row['sid_cur_iso'] . ' ' . $number->doFormatFloat($row['sid_rate']);
            if (empty($row['sid_exchange_rate']) === false) {
                $row['sid_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_exchange_rate']);
                $row['sid_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['sid_total']);
            } else {
                $table->addCellAttribute('sid_exchange_rate', $i, 'style', 'background-color: red;');
                $row['sid_total'] = $row['sid_cur_iso'] . ' ' . $number->doFormatFloat($row['sid_total']);
            }
            if (empty($row['sid_tax_name']) === true) {
                $table->addCellAttribute('sid_tax_name', $i, 'style', 'background-color: red;');
            }
            $results[] = $row;
            $i++;
        }
        $table->addRows($results);
        # Add special settings to the table
        $table->addColumnAttribute('sid_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('sid_active', 'style', 'text-align: center;');
        $table->addColumnAttribute('sid_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('sid_type', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SiSidPtl', Trans::getFinanceWord('invoiceDetail'));
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
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jos.jos_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('jos.jos_rel_id', $this->getIntParameter('si_rel_id'));
        $wheres[] = '(jos.jos_id NOT IN (SELECT (CASE WHEN s1.sid_jos_id IS NULL THEN 0 ELSE s1.sid_jos_id END)
                                            FROM sales_invoice_detail as s1 INNER JOIN
                                            sales_invoice as s2 ON s1.sid_si_id = s2.si_id
                                            WHERE (s2.si_id <> ' . $this->getDetailReferenceValue() . ')
                                            AND (s2.si_deleted_on IS NULL) AND (s1.sid_deleted_on IS NULL)
                                            GROUP BY s1.sid_jos_id))';
        if ($this->isValidParameter('si_so_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('so.so_id', $this->getIntParameter('si_so_id'));
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jos.jos_id as sid_jos_id, jos.jos_jo_id, jo.jo_number as sid_jo_number, jos.jos_cc_id, jos.jos_rel_id,
                        jos.jos_description as sid_description, jos.jos_rate as sid_rate, jos.jos_quantity as sid_quantity,
                        jos.jos_uom_id, jos.jos_cur_id as sid_cur_id, jos.jos_exchange_rate as sid_exchange_rate, jos.jos_tax_id,
                       cc.cc_code AS sid_cc_code, uom.uom_code AS sid_uom_code, cur.cur_iso AS sid_cur_iso,
                       tax.tax_name AS sid_tax_name, rel.rel_name AS sid_relation, so.so_number as sid_so_number,
                       jos.jos_total as sid_total, sid.sid_id, (CASE WHEN (sid.sid_id IS NULL) THEN 'N' ELSE sid.sid_active END) AS sid_active,
                       jo.jo_srv_id as sid_jo_srv_id, srv.srv_name as sid_jo_service, jo.jo_srt_id as sid_jo_srt_id, srt.srt_name as sid_jo_service_term,
                        ccg.ccg_type as sid_type
                FROM job_sales AS jos INNER JOIN
                     job_order as jo ON jo.jo_id = jos.jos_jo_id INNER JOIN
                     service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                    service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                     job_inklaring as jik ON jo.jo_id = jik.jik_jo_id INNER JOIN
                     sales_order as so ON so.so_id = jik.jik_so_id INNER JOIN
                     relation AS rel ON rel.rel_id = jos.jos_rel_id INNER JOIN
                     cost_code AS cc ON cc.cc_id = jos.jos_cc_id INNER JOIN
                    cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                     unit AS uom ON uom.uom_id = jos.jos_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = jos.jos_cur_id LEFT OUTER JOIN
                     tax as tax ON jos.jos_tax_id = tax.tax_id LEFT OUTER JOIN
                     (SELECT sid_id, sid_jos_id, (CASE WHEN (sid_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS sid_active
                          FROM sales_invoice_detail
                          WHERE (sid_si_id = " . $this->getDetailReferenceValue() . ")) AS sid ON jos.jos_id = sid.sid_jos_id " . $strWheres;
        $query .= ' UNION ALL ';
        $query .= "SELECT jos.jos_id as sid_jos_id, jos.jos_jo_id, jo.jo_number as sid_jo_number, jos.jos_cc_id, jos.jos_rel_id,
                        jos.jos_description as sid_description, jos.jos_rate as sid_rate, jos.jos_quantity as sid_quantity,
                        jos.jos_uom_id, jos.jos_cur_id as sid_cur_id, jos.jos_exchange_rate as sid_exchange_rate, jos.jos_tax_id,
                       cc.cc_code AS sid_cc_code, uom.uom_code AS sid_uom_code, cur.cur_iso AS sid_cur_iso,
                       tax.tax_name AS sid_tax_name, rel.rel_name AS sid_relation, so.so_number as sid_so_number,
                       jos.jos_total as sid_total, sid.sid_id, (CASE WHEN (sid.sid_id IS NULL) THEN 'N' ELSE sid.sid_active END) AS sid_active,
                       jo.jo_srv_id as sid_jo_srv_id, srv.srv_name as sid_jo_service, jo.jo_srt_id as sid_jo_srt_id, srt.srt_name as sid_jo_service_term,
                        ccg.ccg_type as sid_type
                FROM job_sales AS jos INNER JOIN
                     job_order as jo ON jo.jo_id = jos.jos_jo_id INNER JOIN
                     service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                    service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                     job_delivery as jdl ON jo.jo_id = jdl.jdl_jo_id INNER JOIN
                     relation AS rel ON rel.rel_id = jos.jos_rel_id INNER JOIN
                     cost_code AS cc ON cc.cc_id = jos.jos_cc_id INNER JOIN
                    cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                     unit AS uom ON uom.uom_id = jos.jos_uom_id INNER JOIN
                     currency AS cur ON cur.cur_id = jos.jos_cur_id LEFT OUTER JOIN
                     sales_order as so ON so.so_id = jdl.jdl_so_id LEFT OUTER JOIN
                     tax as tax ON jos.jos_tax_id = tax.tax_id LEFT OUTER JOIN
                     (SELECT sid_id, sid_jos_id, (CASE WHEN (sid_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS sid_active
                          FROM sales_invoice_detail
                          WHERE (sid_si_id = " . $this->getDetailReferenceValue() . ")) AS sid ON jos.jos_id = sid.sid_jos_id " . $strWheres;
        $query .= ' ORDER BY sid_active DESC, sid_type DESC, sid_jo_srv_id, sid_jos_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);

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
                $btnRequest = new ModalButton('SiRcBtn', Trans::getFinanceWord('requestApproval'), $modal->getModalId());
                $btnRequest->setIcon(Icon::Share)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnRequest);

                # Enable Btn Delete
                $this->setEnableDeleteButton();
            }
            if ($this->isAllowApprove()) {
                # Create Pro forma Invoice
//                $pdfButton = new PdfButton('SiPfPrt', Trans::getFinanceWord('proFormaInvoice'), 'siproforma');
//                $pdfButton->setIcon(Icon::FilePdfO)->btnDark()->pullRight()->btnMedium();
//                $pdfButton->addParameter('si_id', $this->getDetailReferenceValue());
//                $this->View->addButton($pdfButton);

                $modalApprove = $this->getApproveModal();
                $this->View->addModal($modalApprove);
                $btnApprove = new ModalButton('SiRcBtn', Trans::getFinanceWord('approve'), $modalApprove->getModalId());
                $btnApprove->setIcon(Icon::Check)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnApprove);
                $modalReject = $this->getRejectModal();
                $this->View->addModal($modalReject);
                $btnReject = new ModalButton('SiRjBtn', Trans::getFinanceWord('reject'), $modalReject->getModalId());
                $btnReject->setIcon(Icon::Remove)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnReject);
            }
            if ($this->isAllowDocumentReceive()) {
                # Button Paid
                $modalReceive = $this->getReceiveModal();
                $this->View->addModal($modalReceive);
                $btnPayment = new ModalButton('SiRcBtn', Trans::getFinanceWord('invoiceReceived'), $modalReceive->getModalId());
                $btnPayment->setIcon(Icon::CheckSquare)->btnAqua()->pullRight()->btnMedium();
                $this->View->addButton($btnPayment);
                # Create Pro forma Invoice
//                $invBtn = new PdfButton('SiInvPrt', Trans::getFinanceWord('invoice'), 'siinvoice');
//                $invBtn->setIcon(Icon::FilePdfO)->btnPrimary()->pullRight()->btnMedium();
//                $invBtn->addParameter('si_id', $this->getDetailReferenceValue());
//                $this->View->addButton($invBtn);

            }
            if ($this->isAllowPaid()) {
                # Button Paid
                $modalPayment = $this->getPaymentModal();
                $this->View->addModal($modalPayment);
                $btnPayment = new ModalButton('SiPayBtn', Trans::getFinanceWord('paid'), $modalPayment->getModalId());
                $btnPayment->setIcon(Icon::Money)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnPayment);
            }
            if ($this->isValidParameter('si_so_number')) {
                $url = url('/so/view?so_id=' . $this->getIntParameter('si_so_id'));
                $btnView = new HyperLink('BtnSoView', $this->getStringParameter('si_so_number'), $url);
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
        $modal = new Modal('SiRcMdl', Trans::getFinanceWord('requestConfirmation'));
        if ($this->getFloatParameter('si_total_amount', 0.0) === 0.0) {
            $modal->setTitle(Trans::getWord('warning'));
            $text = Trans::getWord('invoiceRequestWarningAmount', 'message');
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getWord('invoiceRequestConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doRequest');
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
        $modal = new Modal('SiAppMdl', Trans::getFinanceWord('approveConfirmation'));
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
        $modal = new Modal('SiRjMdl', Trans::getFinanceWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReject');
        $showModal = false;
        if ($this->getFormAction() === 'doReject' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('rejectReason'), $this->Field->getTextArea('sia_reject_reason', $this->getParameterForModal('sia_reject_reason', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('sia_id', $this->getIntParameter('sia_id')));

        $text = Trans::getWord('invoiceRejectConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesReject'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getPaymentModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SiPayMdl', Trans::getFinanceWord('paymentConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doPayment');
        $showModal = false;
        if ($this->getFormAction() === 'doPayment' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('si_date_pay') === false) {
            $this->setParameter('si_date_pay', date('Y-m-d'));
        }
        if ($this->isValidParameter('si_time_pay') === false) {
            $this->setParameter('si_time_pay', date('H:i'));
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('si_date_pay', $this->getParameterForModal('si_date_pay', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('si_time_pay', $this->getParameterForModal('si_time_pay', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('paymentRef'), $this->Field->getText('si_paid_ref', $this->getParameterForModal('si_paid_ref', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('si_payment_file', ''));

        $text = Trans::getWord('invoicePaymentConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getReceiveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SiRcMdl', Trans::getFinanceWord('invoiceReceiveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReceive');
        $showModal = false;
        if ($this->getFormAction() === 'doReceive' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('si_receive_date') === false) {
            $this->setParameter('si_receive_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('si_receive_time') === false) {
            $this->setParameter('si_receive_time', date('H:i'));
        }
        # CP Field
        $cpField = $this->Field->getSingleSelect('contactPerson', 'si_receiver', $this->getParameterForModal('si_receiver', $showModal));
        $cpField->setHiddenField('si_receive_id', $this->getParameterForModal('si_receive_id', $showModal));
        $cpField->addParameterById('cp_rel_id', 'si_rel_id', Trans::getFinanceWord('vendor'));
        $cpField->setDetailReferenceCode('cp_id');


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('si_receive_date', $this->getParameterForModal('si_receive_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('si_receive_time', $this->getParameterForModal('si_receive_time', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('receiver'), $cpField, true);
        $fieldSet->addField(Trans::getFinanceWord('proofOfHandover'), $this->Field->getFile('si_receive_file', ''));

        $text = Trans::getWord('invoicePaymentConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('SiDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', 'salesinvoice');
        $dctFields->addParameter('dct_master', 'Y');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('SiDocDelMdl', Trans::getWord('deleteDocument'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the sales Field Set.
     *
     * @return Portlet
     */
    protected function getManualDetailPortlet(): Portlet
    {
        # insert modal
        $modal = $this->getDetailModal();
        $this->View->addModal($modal);
        # delete Modal
        $modalDelete = $this->getDetailDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new Table('SiSidTbl');
        $table->setHeaderRow([
            'sid_cc_code' => Trans::getFinanceWord('costCode'),
            'sid_description' => Trans::getWord('description'),
            'sid_quantity' => Trans::getWord('qty'),
            'sid_uom_code' => Trans::getWord('uom'),
            'sid_rate' => Trans::getWord('rate'),
            'sid_tax_name' => Trans::getWord('tax'),
            'sid_total' => Trans::getWord('total'),
        ]);
        $wheres = [];
        $wheres[] = '(sid.sid_si_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(sid.sid_deleted_on IS NULL)';
        $data = SalesInvoiceDetailDao::loadData($wheres);
        $table->addRows($data);
        $table->setColumnType('sid_rate', 'float');
        $table->setColumnType('sid_quantity', 'float');
        $table->setColumnType('sid_total', 'float');
        $table->addColumnAttribute('sid_cc_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('sid_uom_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('sid_tax_name', 'style', 'text-align: center;');

        $portlet = new Portlet('SiSidPtl', Trans::getFinanceWord('invoiceDetail'));

        $table->setUpdateActionByModal($modal, 'sid', 'getByIdForUpdate', ['sid_id']);
        $table->setDeleteActionByModal($modalDelete, 'sid', 'getByIdForDelete', ['sid_id']);

        # Create btn add Sales.
        $btnSalesMdl = new ModalButton('btnSiSidMdl', Trans::getWord('addSales'), $modal->getModalId());
        $btnSalesMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnSalesMdl);

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get sales modal.
     *
     * @return Modal
     */
    protected function getDetailModal(): Modal
    {
        $modal = new Modal('SiSidMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelectTable('costCode', 'sid_cc_code', $this->getParameterForModal('sid_cc_code', $showModal), 'loadSingleSelectTable');
        $ccField->setHiddenField('sid_cc_id', $this->getParameterForModal('sid_cc_id', $showModal));
        $ccField->setTableColumns([
            'cc_code' => Trans::getWord('code'),
            'cc_group' => Trans::getWord('group'),
            'cc_service' => Trans::getWord('service'),
            'cc_name' => Trans::getWord('name'),
        ]);
        $ccField->setFilters([
            'cc_code' => Trans::getWord('code'),
            'cc_group' => Trans::getWord('group'),
            'cc_service' => Trans::getWord('service'),
            'cc_name' => Trans::getWord('name'),
        ]);
        $ccField->setAutoCompleteFields([
            'sid_description' => 'cc_name',
        ]);
        $ccField->setValueCode('cc_id');
        $ccField->setLabelCode('cc_name');
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameter('ccg_type', 'S');
        $ccField->setParentModal($modal->getModalId());
        $this->View->addModal($ccField->getModal());

        $uomField = $this->Field->getSingleSelect('unit', 'sid_uom_code', $this->getParameterForModal('sid_uom_code', $showModal));
        $uomField->setHiddenField('sid_uom_id', $this->getParameterForModal('sid_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'sid_tax_name', $this->getParameterForModal('sid_tax_name', $showModal));
        $taxField->setHiddenField('sid_tax_id', $this->getParameterForModal('sid_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);


        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sid_description', $this->getParameterForModal('sid_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('qty'), $this->Field->getNumber('sid_quantity', $this->getParameterForModal('sid_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('rate'), $this->Field->getNumber('sid_rate', $this->getParameterForModal('sid_rate', $showModal)), true);
        $fieldSet->addField(Trans::getWord('tax'), $taxField);
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('sid_id', $this->getParameterForModal('sid_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get sales delete modal.
     *
     * @return Modal
     */
    protected function getDetailDeleteModal(): Modal
    {
        $modal = new Modal('SiSidDelMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('sid_cc_code_del', $this->getParameterForModal('sid_cc_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sid_description_del', $this->getParameterForModal('sid_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('qty'), $this->Field->getNumber('sid_quantity_del', $this->getParameterForModal('sid_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('rate'), $this->Field->getNumber('sid_rate_del', $this->getParameterForModal('sid_rate_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('sid_uom_code_del', $this->getParameterForModal('sid_uom_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('tax'), $this->Field->getText('sid_tax_name_del', $this->getParameterForModal('sid_tax_name_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sid_id_del', $this->getParameterForModal('sid_id_del', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }
}

