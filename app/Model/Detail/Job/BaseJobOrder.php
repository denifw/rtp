<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job;

use App\Frame\Document\FileUpload;
use App\Frame\Document\ParseExcel;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelTrueFalse;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Portlet;
use App\Frame\System\Notification\JobNotificationBuilder;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\Quotation\PriceDetailDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobNotificationReceiverDao;
use App\Model\Dao\Job\JobOfficerDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobOrderHoldDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use App\Model\Dao\User\UserMappingDao;

/**
 * Class to handle the creation of detail AbstractJobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class BaseJobOrder extends AbstractFormModel
{
    /**
     * Property to store the actions of the job.
     *
     * @var array $CurrentAction
     */
    protected $CurrentAction = [];

    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * Property to store the cash advance data.
     *
     * @var array $CashAdvance
     */
    protected $CashAdvance = [];

    /**
     * Property to store trigger to enable delete button.
     *
     * @var bool $EnableDelete
     */
    protected $EnableDelete = true;


    /**
     * Property to store the cash advance data.
     *
     * @var array $JobSales
     */
    protected $JobSales = [];


    /**
     * Property to store the cash advance data.
     *
     * @var array $JobPurchase
     */
    protected $JobPurchase = [];

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return $this->doInsertJobOrder();
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsertJobOrder(): int
    {
        $officeId = $this->getIntParameter('jo_order_of_id', $this->User->Relation->getOfficeId());
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('JobOrder', $officeId, $this->getIntParameter('jo_rel_id', 0), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
        $joColVal = [
            'jo_number' => $number,
            'jo_ss_id' => $this->User->getSsId(),
            'jo_ref_id' => $this->getIntParameter('jo_ref_id'),
            'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
            'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
            'jo_order_date' => date('Y-m-d'),
            'jo_rel_id' => $this->getIntParameter('jo_rel_id'),
            'jo_customer_ref' => $this->getStringParameter('jo_customer_ref'),
            'jo_pic_id' => $this->getIntParameter('jo_pic_id'),
            'jo_order_of_id' => $officeId,
            'jo_invoice_of_id' => $this->getIntParameter('jo_invoice_of_id'),
            'jo_manager_id' => $this->getIntParameter('jo_manager_id'),
            'jo_aju_ref' => $this->getStringParameter('jo_aju_ref'),
            'jo_bl_ref' => $this->getStringParameter('jo_bl_ref'),
            'jo_sppb_ref' => $this->getStringParameter('jo_sppb_ref'),
            'jo_packing_ref' => $this->getStringParameter('jo_packing_ref')
        ];
        $jobDao = new JobOrderDao();
        $jobDao->doInsertTransaction($joColVal);

        $joId = $jobDao->getLastInsertId();
        $actions = SystemActionDao::getByServiceTermIdAndSystemId($this->getIntParameter('jo_srt_id'), $this->User->getSsId());
        $jacDao = new JobActionDao();
        $i = 1;
        foreach ($actions as $row) {
            $jacColVal = [
                'jac_jo_id' => $joId,
                'jac_ac_id' => $row['sac_ac_id'],
                'jac_order' => $i,
                'jac_active' => 'Y',
            ];
            $jacDao->doInsertTransaction($jacColVal);
            $i++;
        }

        return $joId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $joColVal = [
                'jo_customer_ref' => $this->getStringParameter('jo_customer_ref'),
                'jo_pic_id' => $this->getIntParameter('jo_pic_id'),
                'jo_order_of_id' => $this->getIntParameter('jo_order_of_id'),
                'jo_invoice_of_id' => $this->getIntParameter('jo_invoice_of_id'),
                'jo_manager_id' => $this->getIntParameter('jo_manager_id'),
                'jo_aju_ref' => $this->getStringParameter('jo_aju_ref'),
                'jo_bl_ref' => $this->getStringParameter('jo_bl_ref'),
                'jo_sppb_ref' => $this->getStringParameter('jo_sppb_ref'),
                'jo_packing_ref' => $this->getStringParameter('jo_packing_ref'),
                'jo_vendor_id' => $this->getIntParameter('jo_vendor_id'),
                'jo_vendor_pic_id' => $this->getIntParameter('jo_vendor_pic_id'),
                'jo_vendor_ref' => $this->getStringParameter('jo_vendor_ref'),
            ];
            $jobDao = new JobOrderDao();
            $jobDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
        } elseif ($this->getFormAction() === 'doInsertOfficer') {
            $jooColVal = [
                'joo_jo_id' => $this->getDetailReferenceValue(),
                'joo_us_id' => $this->getIntParameter('joo_us_id'),
            ];
            $jooDao = new JobOfficerDao();
            $jooDao->doInsertTransaction($jooColVal);
        } elseif ($this->getFormAction() === 'doDeleteOfficer') {
            $jooDao = new JobOfficerDao();
            $jooDao->doDeleteTransaction($this->getIntParameter('joo_id_del'));
        } elseif ($this->getFormAction() === 'doPublishJob') {
            $joColVal = [
                'jo_publish_by' => $this->User->getId(),
                'jo_publish_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            $this->doGenerateNotificationReceiver('jobpublish');
        } elseif ($this->getFormAction() === 'doUpdateSales') {
            $exchangeRate = $this->getFloatParameter('jos_exchange_rate');
            if ($this->getIntParameter('jos_cur_id') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }

            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('jos_rate') * $this->getFloatParameter('jos_quantity') * $exchangeRate;
            if ($this->isValidParameter('jos_tax_id')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jos_tax_id'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $joSalesDao = new JobSalesDao();
            $colVal = [
                'jos_jo_id' => $this->getDetailReferenceValue(),
                'jos_rel_id' => $this->getIntParameter('jos_rel_id'),
                'jos_cc_id' => $this->getIntParameter('jos_cc_id'),
                'jos_description' => $this->getStringParameter('jos_description'),
                'jos_quantity' => $this->getFloatParameter('jos_quantity'),
                'jos_rate' => $this->getFloatParameter('jos_rate'),
                'jos_uom_id' => $this->getIntParameter('jos_uom_id'),
                'jos_exchange_rate' => $exchangeRate,
                'jos_cur_id' => $this->getIntParameter('jos_cur_id'),
                'jos_tax_id' => $this->getIntParameter('jos_tax_id'),
                'jos_total' => $total,
            ];
            if ($this->isValidParameter('jos_id')) {
                $joSalesDao->doUpdateTransaction($this->getIntParameter('jos_id'), $colVal);
            } else {
                $joSalesDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doUpdateSalesReimbursement') {
            # Update purchase remove link to sales
            $jopDao = new JobPurchaseDao();
            $jopDao->doUpdateTransaction($this->getIntParameter('jos_jop_id_r'), [
                'jop_jos_id' => null,
            ]);

            # Update sales
            $exchangeRate = $this->getFloatParameter('jos_exchange_rate_r');
            if ($this->getIntParameter('jos_cur_id_r') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }

            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('jos_rate_r') * $this->getFloatParameter('jos_quantity_r') * $exchangeRate;
            if ($this->isValidParameter('jos_tax_id_r')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jos_tax_id_r'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $joSalesDao = new JobSalesDao();
            $colVal = [
                'jos_jo_id' => $this->getDetailReferenceValue(),
                'jos_rel_id' => $this->getIntParameter('jos_rel_id_r'),
                'jos_cc_id' => $this->getIntParameter('jos_cc_id_r'),
                'jos_description' => $this->getStringParameter('jos_description_r'),
                'jos_quantity' => $this->getFloatParameter('jos_quantity_r'),
                'jos_rate' => $this->getFloatParameter('jos_rate_r'),
                'jos_uom_id' => $this->getIntParameter('jos_uom_id_r'),
                'jos_exchange_rate' => $exchangeRate,
                'jos_cur_id' => $this->getIntParameter('jos_cur_id_r'),
                'jos_tax_id' => $this->getIntParameter('jos_tax_id_r'),
                'jos_total' => $total,
            ];
            if ($this->isValidParameter('jos_id_r')) {
                $joSalesDao->doUpdateTransaction($this->getIntParameter('jos_id_r'), $colVal);
            } else {
                $joSalesDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteSales') {
            $joSalesDao = new JobSalesDao();
            $joSalesDao->doDeleteTransaction($this->getIntParameter('jos_id_del'));
        } elseif ($this->getFormAction() === 'doDeleteSalesReimbursement') {
            $jopDao = new JobPurchaseDao();
            $jopDao->doUpdateTransaction($this->getIntParameter('jos_jop_id_rdel'), [
                'jop_jos_id' => null,
                'jop_cc_id' => $this->getIntParameter('jos_jop_cc_id')
            ]);
            $joSalesDao = new JobSalesDao();
            $joSalesDao->doDeleteTransaction($this->getIntParameter('jos_id_rdel'));
        } elseif ($this->getFormAction() === 'doUpdatePurchase') {
            $exchangeRate = $this->getFloatParameter('jop_exchange_rate');
            if ($this->getIntParameter('jop_cur_id') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }
            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('jop_rate') * $this->getFloatParameter('jop_quantity') * $exchangeRate;
            if ($this->isValidParameter('jop_tax_id')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jop_tax_id'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $josId = null;
            if ($this->getStringParameter('jop_type', 'P') === 'R' || $this->isValidParameter('jop_jos_id')) {
                $josDao = new JobSalesDao();
                $josColVal = [
                    'jos_jo_id' => $this->getDetailReferenceValue(),
                    'jos_rel_id' => $this->getIntParameter('jo_rel_id'),
                    'jos_cc_id' => $this->getIntParameter('jop_cc_id'),
                    'jos_description' => $this->getStringParameter('jop_description'),
                    'jos_quantity' => $this->getFloatParameter('jop_quantity'),
                    'jos_rate' => $this->getFloatParameter('jop_rate'),
                    'jos_uom_id' => $this->getIntParameter('jop_uom_id'),
                    'jos_exchange_rate' => $exchangeRate,
                    'jos_cur_id' => $this->getIntParameter('jop_cur_id'),
                    'jos_tax_id' => $this->getIntParameter('jop_tax_id'),
                    'jos_total' => $total,
                ];
                if ($this->isValidParameter('jop_jos_id') === true) {
                    $josId = $this->getIntParameter('jop_jos_id');
                    $josDao->doUpdateTransaction($this->getIntParameter('jop_jos_id'), $josColVal);
                } else {
                    $josDao->doInsertTransaction($josColVal);
                    $josId = $josDao->getLastInsertId();
                }
            }
            $joPurchaseDao = new JobPurchaseDao();
            $colVal = [
                'jop_jo_id' => $this->getDetailReferenceValue(),
                'jop_rel_id' => $this->getIntParameter('jop_rel_id'),
                'jop_cc_id' => $this->getIntParameter('jop_cc_id'),
                'jop_description' => $this->getStringParameter('jop_description'),
                'jop_quantity' => $this->getFloatParameter('jop_quantity'),
                'jop_rate' => $this->getFloatParameter('jop_rate'),
                'jop_uom_id' => $this->getIntParameter('jop_uom_id'),
                'jop_exchange_rate' => $exchangeRate,
                'jop_cur_id' => $this->getIntParameter('jop_cur_id'),
                'jop_tax_id' => $this->getIntParameter('jop_tax_id'),
                'jop_jos_id' => $josId,
                'jop_total' => $total,
            ];
            if ($this->isValidParameter('jop_id')) {
                $joPurchaseDao->doUpdateTransaction($this->getIntParameter('jop_id'), $colVal);
            } else {
                $joPurchaseDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeletePurchase') {
            $joPurchaseDao = new JobPurchaseDao();
            $joPurchaseDao->doDeleteTransaction($this->getIntParameter('jop_id_del'));
            if ($this->isValidParameter('jop_jos_id_del')) {
                $josDao = new JobSalesDao();
                $josDao->doDeleteTransaction($this->getIntParameter('jop_jos_id_del'));
            }
        } elseif ($this->isUploadDocumentAction() === true) {
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
        } elseif ($this->isDeleteDocumentAction() === true) {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doInsertNotificationReceiver') {
            $colVal = [
                'jnr_jo_id' => $this->getDetailReferenceValue(),
                'jnr_cp_id' => $this->getIntParameter('jnr_cp_id')
            ];
            $jnrDao = new JobNotificationReceiverDao();
            $jnrDao->doInsertTransaction($colVal);
        } elseif ($this->getFormAction() === 'deleteNotificationReceiver') {
            $jnrDao = new JobNotificationReceiverDao();
            $jnrDao->doDeleteTransaction($this->getIntParameter('jnr_id_del'));
        } elseif ($this->isDeleteAction() === true) {
            $joDelColVal = [
                'jo_deleted_reason' => $this->getStringParameter('base_delete_reason'),
                'jo_deleted_by' => $this->User->getId(),
                'jo_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joDelColVal);
        } elseif ($this->getFormAction() === 'doHold') {
            $johDao = new JobOrderHoldDao();
            $johColVal = [
                'joh_jo_id' => $this->getDetailReferenceValue(),
                'joh_reason' => $this->getStringParameter('base_hold_reason'),
            ];
            $johDao->doInsertTransaction($johColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_joh_id' => $johDao->getLastInsertId(),
            ]);
        } elseif ($this->getFormAction() === 'doUnHold') {
            $johDao = new JobOrderHoldDao();
            $johDao->doDeleteTransaction($this->getIntParameter('jo_joh_id'));
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_joh_id' => null,
            ]);
        } elseif ($this->getFormAction() === 'doUploadJogWarehouse') {
            $file = $this->getFileParameter('jog_file');
            if ($file !== null) {
                $fileName = 'jog_' . $this->getDetailReferenceValue() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $parseExcel = new ParseExcel($file, $fileName, 'goods');
                $parseExcel->setHeaderRow([
                    'sku' => 'A',
                    'qty' => 'B',
                    'uom' => 'C',
                ]);
                if ($parseExcel->IsSuccessStored !== false) {
                    $headers = $parseExcel->getSheetHeader('1');
                    $jogDao = new JobGoodsDao();
                    $headers = $jogDao->lowerCaseExcelImportData($headers);
                    if ($jogDao->isValidExcelImportHeader($headers) === true) {
                        $rows = $parseExcel->getAllSheetCells('2');
                        $data = $jogDao->loadGoodsIdExcelImportData($rows, $headers);
                        $errors = $jogDao->doValidateExcelImportData($this->getDetailReferenceValue(), $data);
                        if (empty($errors) === true) {
                            $sn = new SerialNumber($this->User->getSsId());
                            foreach ($data as $row) {
                                $snGoods = $sn->loadNumber('JobOrderGoods', $this->getIntParameter('jo_order_of_id'), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
                                $jogColVal = [
                                    'jog_jo_id' => $this->getDetailReferenceValue(),
                                    'jog_serial_number' => $snGoods,
                                    'jog_gd_id' => $row['gd_id'],
                                    'jog_name' => $row['sku'],
                                    'jog_quantity' => $row['qty'],
                                    'jog_gdu_id' => $row['gdu_id'],
                                ];
                                $jogDao->doInsertTransaction($jogColVal);
                            }
                        } else {
                            Message::throwMessage(implode('<br />', $errors), 'ERROR');
                        }
                    } else {
                        Message::throwMessage(Trans::getWord('invalidExcelHeaderData'), 'ERROR');
                    }
                } else {
                    Message::throwMessage(Trans::getWord('unableUploadJogExcel'), 'ERROR');
                }
            } else {
                Message::throwMessage(Trans::getWord('unableUploadJogExcel'), 'ERROR');
            }
        } elseif ($this->getFormAction() === 'doUploadPurchaseReceipt') {
            # Upload Document.
            $file = $this->getFileParameter('jop_file_doc');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => 69,
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => $this->getIntParameter('jop_id_doc'),
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('jop_description_doc'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
                $jopDao = new JobPurchaseDao();
                $jopDao->doUpdateTransaction($this->getIntParameter('jop_id_doc'), [
                    'jop_doc_id' => $docDao->getLastInsertId()
                ]);
            }
        } elseif ($this->getFormAction() === 'doDeletePurchaseReceipt') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('jop_doc_id_del'));
            $jopDao = new JobPurchaseDao();
            $jopDao->doUpdateTransaction($this->getIntParameter('jop_id_doc_del'), [
                'jop_doc_id' => null
            ]);
        } elseif ($this->getFormAction() === 'doInsertSalesByQuotation') {
            $data = PriceDetailDao::getByPriceId($this->getIntParameter('jos_prc_id'));
            $joSalesDao = new JobSalesDao();
            if (empty($data) === false) {
                foreach ($data as $row) {
                    $colVal = [
                        'jos_jo_id' => $this->getDetailReferenceValue(),
                        'jos_prd_id' => $row['prd_id'],
                        'jos_rel_id' => $row['prd_rel_id'],
                        'jos_cc_id' => $row['prd_cc_id'],
                        'jos_description' => $row['prd_description'],
                        'jos_quantity' => $row['prd_quantity'],
                        'jos_rate' => $row['prd_rate'],
                        'jos_uom_id' => $row['prd_uom_id'],
                        'jos_exchange_rate' => $row['prd_exchange_rate'],
                        'jos_cur_id' => $row['prd_cur_id'],
                        'jos_tax_id' => $row['prd_tax_id'],
                        'jos_total' => $row['prd_total'],
                    ];
                    $joSalesDao->doInsertTransaction($colVal);
                }
            }
        } elseif ($this->getFormAction() === 'doUpdateSalesByQuotation') {
            $exchangeRate = $this->getFloatParameter('jos_exchange_rate_qt');
            if ($this->getIntParameter('jos_cur_id_qt') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }

            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('jos_rate_qt') * $this->getFloatParameter('jos_quantity_qt') * $exchangeRate;
            if ($this->isValidParameter('jos_tax_id_qt')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jos_tax_id_qt'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $joSalesDao = new JobSalesDao();
            $colVal = [
                'jos_description' => $this->getStringParameter('jos_description_qt'),
                'jos_quantity' => $this->getFloatParameter('jos_quantity_qt'),
                'jos_exchange_rate' => $exchangeRate,
                'jos_tax_id' => $this->getIntParameter('jos_tax_id_qt'),
                'jos_total' => $total,
            ];
            $joSalesDao->doUpdateTransaction($this->getIntParameter('jos_id_qt'), $colVal);
        } elseif ($this->getFormAction() === 'doInsertPurchaseByQuotation') {
            $data = PriceDetailDao::getByPriceId($this->getIntParameter('jop_prc_id'));
            if (empty($data) === false) {
                $jopDao = new JobPurchaseDao();
                foreach ($data as $row) {
                    $jopDao->doInsertTransaction([
                        'jop_jo_id' => $this->getDetailReferenceValue(),
                        'jop_prd_id' => $row['prd_id'],
                        'jop_rel_id' => $row['prd_rel_id'],
                        'jop_cc_id' => $row['prd_cc_id'],
                        'jop_description' => $row['prd_description'],
                        'jop_quantity' => $row['prd_quantity'],
                        'jop_rate' => $row['prd_rate'],
                        'jop_uom_id' => $row['prd_uom_id'],
                        'jop_exchange_rate' => $row['prd_exchange_rate'],
                        'jop_cur_id' => $row['prd_cur_id'],
                        'jop_tax_id' => $row['prd_tax_id'],
                        'jop_total' => $row['prd_total'],
                    ]);
                }
            }
        } elseif ($this->getFormAction() === 'doUpdatePurchaseByQuotation') {
            $exchangeRate = $this->getFloatParameter('jop_exchange_rate_qt');
            if ($this->getIntParameter('jop_cur_id_qt') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }
            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('jop_rate_qt') * $this->getFloatParameter('jop_quantity_qt') * $exchangeRate;
            if ($this->isValidParameter('jop_tax_id_qt')) {
                $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jop_tax_id_qt'));
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            if ($this->isValidParameter('jop_jos_id_qt') === true) {
                $josDao = new JobSalesDao();
                $josColVal = [
                    'jos_description' => $this->getStringParameter('jop_description_qt'),
                    'jos_quantity' => $this->getFloatParameter('jop_quantity_qt'),
                    'jos_exchange_rate' => $exchangeRate,
                    'jos_rate' => $this->getFloatParameter('jop_rate_qt'),
                    'jos_tax_id' => $this->getIntParameter('jop_tax_id_qt'),
                    'jos_total' => $total,
                ];
                $josDao->doUpdateTransaction($this->getIntParameter('jop_jos_id_qt'), $josColVal);
            }
            $joPurchaseDao = new JobPurchaseDao();
            $colVal = [
                'jop_description' => $this->getStringParameter('jop_description_qt'),
                'jop_quantity' => $this->getFloatParameter('jop_quantity_qt'),
                'jop_exchange_rate' => $exchangeRate,
                'jop_rate' => $this->getFloatParameter('jop_rate_qt'),
                'jop_tax_id' => $this->getIntParameter('jop_tax_id_qt'),
                'jop_total' => $total,
            ];
            $joPurchaseDao->doUpdateTransaction($this->getIntParameter('jop_id_qt'), $colVal);

        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return [];
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert()) {
            $this->setServiceIntoParameter($this->getDefaultRoute());
        }
        # Set Hidden Data
        $this->setJoHiddenData();
        $this->setSoHiddenData();
        if ($this->isUpdate() === true) {
            $this->JobSales = JobSalesDao::getByJobId($this->getDetailReferenceValue());
            $this->JobPurchase = JobPurchaseDao::getByJobId($this->getDetailReferenceValue());
            $this->CashAdvance = CashAdvanceDao::getByJobId($this->getDetailReferenceValue());

            # Load Current Action
            $this->CurrentAction = JobActionDao::getLastActiveActionByJobId($this->getDetailReferenceValue());

            # Override title page
            $this->overridePageTitle();


            # Load goods data.
            $this->loadGoodsData();

            # Show delete reason
            if ($this->isJobDeleted() === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('jobCanceledReason', 'message', '', [
                    'user' => $this->getStringParameter('jo_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('jo_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('jo_deleted_reason')
                ]));
            }
            # Show delete reason
            if ($this->isJobHold() === true) {
                $this->setDisableUpdate();
                $date = DateTimeParser::format($this->getStringParameter('jo_hold_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
                $this->View->addWarningMessage(Trans::getWord('joHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('jo_hold_reason')]));
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
            $this->Validation->checkRequire('jo_srv_id');
            $this->Validation->checkRequire('jo_srt_id');
//            $this->Validation->checkRequire('jo_rel_id');
//            if ($this->isUpdate()) {
//                $this->Validation->checkRequire('jo_order_of_id');
//            }
            $this->Validation->checkMaxLength('jo_customer_ref', 255);
            $this->Validation->checkMaxLength('jo_contract_ref', 255);
            $this->Validation->checkMaxLength('jo_bl_ref', 255);
            $this->Validation->checkMaxLength('jo_sppb_ref', 255);
        } elseif ($this->getFormAction() === 'doHold') {
            $this->Validation->checkRequire('base_hold_reason', 3);
        } elseif ($this->getFormAction() === 'doUnHold') {
            $this->Validation->checkRequire('jo_joh_id');
        } elseif ($this->getFormAction() === 'doInsertOfficer') {
            $this->Validation->checkRequire('joo_us_id');
            $this->Validation->checkUnique('joo_us_id', 'job_officer', [
                'joo_id' => $this->getIntParameter('joo_id', 0),
            ], [
                'joo_deleted_on' => null,
                'joo_jo_id' => $this->getDetailReferenceValue(),
            ]);
        } elseif ($this->getFormAction() === 'doDeleteOfficer') {
            $this->Validation->checkRequire('joo_id_del');
        } elseif ($this->getFormAction() === 'doUpdateSales') {
            $this->Validation->checkRequire('jos_cc_id');
            $this->Validation->checkRequire('jos_rel_id');
            $this->Validation->checkRequire('jos_description', 1, 150);
            $this->Validation->checkRequire('jos_rate');
            $this->Validation->checkFloat('jos_rate');
            $this->Validation->checkRequire('jos_quantity');
            $this->Validation->checkFloat('jos_quantity');
            $this->Validation->checkRequire('jos_uom_id');
            $this->Validation->checkRequire('jos_tax_id');
            $this->Validation->checkRequire('jos_cur_id');
            if ($this->isValidParameter('jos_cur_id') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('jos_cur_id')) {
                $this->Validation->checkRequire('jos_exchange_rate');
                $this->Validation->checkFloat('jos_exchange_rate');
            }
        } elseif ($this->getFormAction() === 'doUpdateSalesReimbursement') {
            $this->Validation->checkRequire('jos_cc_id_r');
            $this->Validation->checkRequire('jos_rel_id_r');
            $this->Validation->checkRequire('jos_description_r', 1, 150);
            $this->Validation->checkRequire('jos_rate_r');
            $this->Validation->checkFloat('jos_rate_r');
            $this->Validation->checkRequire('jos_quantity_r');
            $this->Validation->checkFloat('jos_quantity_r');
            $this->Validation->checkRequire('jos_uom_id_r');
            $this->Validation->checkRequire('jos_tax_id_r');
            $this->Validation->checkRequire('jos_cur_id_r');
            $this->Validation->checkRequire('jos_jop_id_r');
            if ($this->isValidParameter('jos_cur_id_r') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('jos_cur_id_r')) {
                $this->Validation->checkRequire('jos_exchange_rate_r');
                $this->Validation->checkFloat('jos_exchange_rate_r');
            }
        } elseif ($this->getFormAction() === 'doDeleteSales') {
            $this->Validation->checkRequire('jos_id_del');
        } elseif ($this->getFormAction() === 'doDeleteSalesReimbursement') {
            $this->Validation->checkRequire('jos_id_rdel');
            $this->Validation->checkRequire('jos_jop_id_rdel');
            $this->Validation->checkRequire('jos_jop_cc_id');
        } elseif ($this->getFormAction() === 'doUpdatePurchase') {
            $this->Validation->checkRequire('jop_cc_id');
            $this->Validation->checkRequire('jop_rel_id');
            $this->Validation->checkRequire('jop_description', 1, 150);
            $this->Validation->checkRequire('jop_rate');
            $this->Validation->checkFloat('jop_rate');
            $this->Validation->checkRequire('jop_quantity');
            $this->Validation->checkFloat('jop_quantity');
            $this->Validation->checkRequire('jop_uom_id');
            $this->Validation->checkRequire('jop_tax_id');
            $this->Validation->checkRequire('jop_cur_id');
            if ($this->isValidParameter('jop_cur_id') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('jop_cur_id')) {
                $this->Validation->checkRequire('jop_exchange_rate');
                $this->Validation->checkFloat('jop_exchange_rate');
            }
        } elseif ($this->getFormAction() === 'doDeletePurchase') {
            $this->Validation->checkRequire('jop_id_del');
        } elseif ($this->getFormAction() === 'doInsertNotificationReceiver') {
            $this->Validation->checkRequire('jnr_cp_id');
            $this->Validation->checkUnique('jnr_cp_id', 'job_notification_receiver', [
                'jnr_id' => $this->getIntParameter('jnr_id', 0)
            ], [
                    'jnr_deleted_on' => null,
                    'jnr_jo_id' => $this->getDetailReferenceValue()
                ]
            );
        } elseif ($this->getFormAction() === 'deleteNotificationReceiver') {
            $this->Validation->checkRequire('jnr_id_del');
        } elseif ($this->getFormAction() === 'doCopyData') {
            $this->Validation->checkRequire('base_copy_amount');
            $this->Validation->checkInt('base_copy_amount');
        } elseif ($this->getFormAction() === 'doUploadJogWarehouse') {
            $this->Validation->checkRequire('jog_file');
            $this->Validation->checkFile('jog_file');
            $this->Validation->checkMimes('jog_file', ['xlsx']);
        } elseif ($this->getFormAction() === 'doUploadPurchaseReceipt') {
            $this->Validation->checkRequire('jop_id_doc');
            $this->Validation->checkRequire('jop_file_doc');
            $this->Validation->checkFile('jop_file_doc');
        } elseif ($this->getFormAction() === 'doDeletePurchaseReceipt') {
            $this->Validation->checkRequire('jop_doc_id_del');
            $this->Validation->checkRequire('jop_id_doc_del');
        } elseif ($this->getFormAction() === 'doInsertQuotation') {
            $this->Validation->checkUnique('joq_qt_id', 'job_order_quotation', [
                'joq_id' => $this->getIntParameter('joq_id')
            ], [
                'joq_jo_id' => $this->getDetailReferenceValue(),
                'joq_deleted_on' => null
            ]);
        } elseif ($this->getFormAction() === 'doInsertSalesByQuotation') {
            $this->Validation->checkRequire('jos_prc_id');
        } elseif ($this->getFormAction() === 'doUpdateSalesByQuotation') {
            $this->Validation->checkRequire('jos_id_qt');
            $this->Validation->checkRequire('jos_cc_id_qt');
            $this->Validation->checkRequire('jos_rel_id_qt');
            $this->Validation->checkRequire('jos_description_qt', 1, 150);
            $this->Validation->checkRequire('jos_rate_qt');
            $this->Validation->checkFloat('jos_rate_qt');
            $this->Validation->checkRequire('jos_quantity_qt');
            $this->Validation->checkFloat('jos_quantity_qt');
            $this->Validation->checkRequire('jos_uom_id_qt');
            $this->Validation->checkRequire('jos_tax_id_qt');
            $this->Validation->checkRequire('jos_cur_id_qt');
            if ($this->isValidParameter('jos_cur_id_qt') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('jos_cur_id_qt')) {
                $this->Validation->checkRequire('jos_exchange_rate_qt');
                $this->Validation->checkFloat('jos_exchange_rate_qt');
            }
        } elseif ($this->getFormAction() === 'doInsertPurchaseByQuotation') {
            $this->Validation->checkRequire('jop_prc_id');
        } elseif ($this->getFormAction() === 'doUpdatePurchaseByQuotation') {
            $this->Validation->checkRequire('jop_id_qt');
            $this->Validation->checkRequire('jop_cc_id_qt');
            $this->Validation->checkRequire('jop_rel_id_qt');
            $this->Validation->checkRequire('jop_description_qt', 1, 150);
            $this->Validation->checkRequire('jop_rate_qt');
            $this->Validation->checkFloat('jop_rate_qt');
            $this->Validation->checkRequire('jop_quantity_qt');
            $this->Validation->checkFloat('jop_quantity_qt');
            $this->Validation->checkRequire('jop_uom_id_qt');
            $this->Validation->checkRequire('jop_tax_id_qt');
            $this->Validation->checkRequire('jop_cur_id_qt');
            if ($this->isValidParameter('jop_cur_id_qt') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('jop_cur_id_qt')) {
                $this->Validation->checkRequire('jop_exchange_rate_qt');
                $this->Validation->checkFloat('jop_exchange_rate_qt');
            }
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getVendorFieldSet(): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('JoVendorPtl', Trans::getWord('vendor'));
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);
        # Create Relation Field
        $vendorField = $this->Field->getSingleSelect('relation', 'jo_vendor', $this->getStringParameter('jo_vendor'));
        $vendorField->setHiddenField('jo_vendor_id', $this->getIntParameter('jo_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('jo_pic_vendor');
        $vendorField->addClearField('jo_vendor_pic_id');
        # Create Contact Field
        $picVendorField = $this->Field->getSingleSelect('contactPerson', 'jo_pic_vendor', $this->getStringParameter('jo_pic_vendor'));
        $picVendorField->setHiddenField('jo_vendor_pic_id', $this->getIntParameter('jo_vendor_pic_id'));
        $picVendorField->addParameterById('cp_rel_id', 'jo_vendor_id', Trans::getWord('vendor'));
        $picVendorField->setDetailReferenceCode('cp_id');

        if ($this->isJobPublished() === true) {
            $managerField->setReadOnly();
            $vendorField->setReadOnly();
        }
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField);
        $fieldSet->addField(Trans::getWord('vendor'), $vendorField);
        $fieldSet->addField(Trans::getWord('picVendor'), $picVendorField);
        $fieldSet->addField(Trans::getWord('vendorReference'), $this->Field->getText('jo_vendor_ref', $this->getStringParameter('jo_vendor_ref')));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJoHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jo_joh_id', $this->getIntParameter('jo_joh_id'));
        $content .= $this->Field->getHidden('jo_publish_on', $this->getStringParameter('jo_publish_on'));
        $content .= $this->Field->getHidden('jo_finish_on', $this->getStringParameter('jo_finish_on'));
        $content .= $this->Field->getHidden('jo_order_of_id', $this->getIntParameter('jo_order_of_id', $this->User->Relation->getOfficeId()));
        $content .= $this->Field->getHidden('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $content .= $this->Field->getHidden('jo_srv_code', $this->getStringParameter('jo_srv_code'));
        $content .= $this->Field->getHidden('jo_srt_pol', $this->getStringParameter('jo_srt_pol'));
        $content .= $this->Field->getHidden('jo_srt_pod', $this->getStringParameter('jo_srt_pod'));
        $content .= $this->Field->getHidden('jo_srt_container', $this->getStringParameter('jo_srt_container'));
        $content .= $this->Field->getHidden('jo_srt_load', $this->getStringParameter('jo_srt_load'));
        $content .= $this->Field->getHidden('jo_srt_unload', $this->getStringParameter('jo_srt_unload'));
        $content .= $this->Field->getHidden('jo_jtr_id', $this->getIntParameter('jo_jtr_id'));
        $content .= $this->Field->getHidden('jo_srt_route', $this->getStringParameter('jo_srt_route'));
        $content .= $this->Field->getHidden('jo_number', $this->getStringParameter('jo_number'));
        $this->View->addContent('JoHdFld', $content);

    }

    /**
     * Function to set so hidden data.
     *
     * @return void
     */
    private function setSoHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('so_id', $this->getIntParameter('so_id'));
        $content .= $this->Field->getHidden('so_soh_id', $this->getIntParameter('so_soh_id'));
        $content .= $this->Field->getHidden('so_start_on', $this->getStringParameter('so_start_on'));
        $content .= $this->Field->getHidden('so_soh_deleted_on', $this->getStringParameter('so_soh_deleted_on'));
        $this->View->addContent('SoHdFld', $content);

    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        if ($this->isUpdate() === true) {
            $relField->setReadOnly();
        }

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'jo_pic_customer', $this->getStringParameter('jo_pic_customer'));
        $picField->setHiddenField('jo_pic_id', $this->getIntParameter('jo_pic_id'));
        $picField->addParameterById('cp_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $picField->setDetailReferenceCode('cp_id');

        # Create order Office Field
        $ofOrderField = $this->Field->getSingleSelect('office', 'jo_order_office', $this->getStringParameter('jo_order_office'));
        $ofOrderField->setHiddenField('jo_order_of_id', $this->getIntParameter('jo_order_of_id'));
        $ofOrderField->addParameter('of_rel_id', $this->User->getRelId());
        $ofOrderField->setEnableDetailButton(false);
        $ofOrderField->setEnableNewButton(false);

        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $picField);
        if ($this->isUpdate()) {
            $fieldSet->addField(Trans::getWord('orderOffice'), $ofOrderField, true);
        }
        $fieldSet->addField(Trans::getWord('orderDate'), $this->Field->getCalendar('jo_order_date', $this->getStringParameter('jo_order_date')), true);
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('jo_srv_id', $this->getIntParameter('jo_srv_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_so_id', $this->getIntParameter('jo_so_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('joh_id', $this->getIntParameter('joh_id')));
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 8, 8);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return FieldSet
     */
    protected function getReferenceField(): FieldSet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('customerRef'), $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref')));
        $fieldSet->addField(Trans::getWord('blRef'), $this->Field->getText('jo_bl_ref', $this->getStringParameter('jo_bl_ref')));
        $fieldSet->addField(Trans::getWord('packingListRef'), $this->Field->getText('jo_packing_ref', $this->getStringParameter('jo_packing_ref')));
        $fieldSet->addField(Trans::getWord('ajuRef'), $this->Field->getText('jo_aju_ref', $this->getStringParameter('jo_aju_ref')));
        $fieldSet->addField(Trans::getWord('sppbRef'), $this->Field->getText('jo_sppb_ref', $this->getStringParameter('jo_sppb_ref')));

        return $fieldSet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getReferenceFieldSet(): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('JoRefPtl', Trans::getWord('reference'));
        $portlet->addFieldSet($this->getReferenceField());
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }

    /**
     * Function to get so reference Field Set.
     *
     * @return Portlet
     */
    protected function getSoFieldSet(): Portlet
    {
        # Create Fields.
        # So Number
        $soNumber = $this->Field->getText('so_number', $this->getStringParameter('so_number'));
        $soNumber->setReadOnly();
        # Customer Reference Field
        $custField = $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref'));
        $custField->setReadOnly();
        # Bl Reference
        $blNumber = $this->Field->getText('jo_bl_ref', $this->getStringParameter('jo_bl_ref'));
        $blNumber->setReadOnly();
        # Aju Reference
        $ajuNumber = $this->Field->getText('jo_aju_ref', $this->getStringParameter('jo_aju_ref'));
        $ajuNumber->setReadOnly();
        # sppb reference
        $sppbNumber = $this->Field->getText('jo_sppb_ref', $this->getStringParameter('jo_sppb_ref'));
        $sppbNumber->setReadOnly();
        # Packing Reference
        $packingNumber = $this->Field->getText('jo_packing_ref', $this->getStringParameter('jo_packing_ref'));
        $packingNumber->setReadOnly();

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('soNumber'), $soNumber);
        if ($this->isValidParameter('jo_customer_ref') === true) {
            $fieldSet->addField(Trans::getWord('customerRef'), $custField);
        }
        if ($this->isValidParameter('jo_bl_ref') === true) {
            $fieldSet->addField(Trans::getWord('blRef'), $blNumber);
        }
        if ($this->isValidParameter('jo_packing_ref') === true) {
            $fieldSet->addField(Trans::getWord('packingListRef'), $packingNumber);
        }
        if ($this->isValidParameter('jo_aju_ref') === true) {
            $fieldSet->addField(Trans::getWord('ajuRef'), $ajuNumber);
        }
        $fieldSet->addField(Trans::getWord('sppbRef'), $sppbNumber);
        # Create a portlet box.
        $portlet = new Portlet('JoSoRefPtl', Trans::getWord('soReference'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $this->Goods = JobGoodsDao::loadData($wheres);
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return void
     */
    protected function overridePageTitle(): void
    {
        $title = $this->getStringParameter('jo_number');
        $data = [
            'is_deleted' => $this->isValidParameter('jo_deleted_on'),
            'is_hold' => $this->isValidParameter('jo_joh_id'),
            'is_finish' => $this->isValidParameter('jo_finish_on'),
            'is_document' => $this->isValidParameter('jo_document_on'),
            'is_start' => $this->isValidParameter('jo_start_on'),
            'is_publish' => $this->isValidParameter('jo_publish_on'),
        ];
        if (empty($this->CurrentAction) === false) {
            $data = array_merge($data, [
                'jac_id' => $this->CurrentAction['jac_id'],
                'jae_style' => $this->CurrentAction['jac_style'],
                'jac_action' => $this->CurrentAction['jac_action'],
                'jae_description' => $this->CurrentAction['jae_description'],
                'jo_srt_id' => $this->CurrentAction['ac_srt_id'],
            ]);
        }
        $jobDao = new JobOrderDao();
        $title .= ' | ' . $jobDao->generateStatus($data);
        $this->View->setDescription($title);
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
            if ($this->isJobDeleted() === false) {
                # Show Hold Button
                if ($this->isJobFinish() === false && $this->isJobHold() === false) {
                    $this->setEnableHoldButton();
                }
                # Show Deleted Button
                if ($this->PageSetting->checkPageRight('AllowDelete') === true && $this->EnableDelete === true && ($this->isJobFinish() === false || $this->PageSetting->checkPageRight('AllowDeleteFinishJob') === true)) {
                    $this->setEnableDeleteButton();
                }
            }
            # Show Hold/Un-Hold Button
            if ($this->isJobHold()) {
                $this->setDisableUpdate();
                if ($this->isSoHold() === false) {
                    $this->setEnableUnHoldButton();
                }
            }
            # Show Publish Button
            if ($this->isAllowUpdate() === true && $this->isValidParameter('jo_publish_on') === false && $this->PageSetting->checkPageRight('AllowPublish') === true) {
                $modal = $this->getJoPublishModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnPubJo', Trans::getWord('publish'), $modal->getModalId());
                $btnDel->setIcon(Icon::PaperPlane)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            }
            # Enable Copy Button
//            $this->setEnableCopyButton();
            # Show SO Button
            if ($this->isValidSoId() === true) {
                $btnSo = new HyperLink('hplSo', $this->getStringParameter('so_number', 'SO'), url('so/detail?so_id=' . $this->getSoId()));
                $btnSo->viewAsButton();
                $btnSo->setIcon(Icon::Eye)->btnInfo()->pullRight()->btnMedium();
                $this->View->addButton($btnSo);
            }
            $this->setEnableViewButton();
        }

        parent::loadDefaultButton();
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getJoPublishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoPubMdl', Trans::getWord('publishConfirmation'));
        if (empty($this->Goods) === true || $this->isValidParameter('jo_manager_id') === false) {
            $p = new Paragraph(Trans::getMessageWord('unablePublishJobOrder'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } elseif ($this->isAllowPublishWithoutFinanceData() === false && (empty($this->JobSales) === true || empty($this->JobPurchase) === true)) {
            $p = new Paragraph(Trans::getMessageWord('emptyJobFinanceData'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getWord('publishJobConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
            $modal->setBtnOkName(Trans::getWord('yesPublish'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
        }
        $modal->addText($p);

        return $modal;
    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobDeleted(): bool
    {

        return $this->isValidParameter('jo_deleted_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobFinish(): bool
    {

        return $this->isValidParameter('jo_finish_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowUpdate(): bool
    {
        return $this->isUpdate() && $this->isJobDeleted() === false && $this->isJobHold() === false;
    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobPublished(): bool
    {

        return $this->isValidParameter('jo_publish_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobHold(): bool
    {

        return $this->isValidParameter('jo_joh_id');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isSoHold(): bool
    {
        return $this->isValidParameter('so_soh_id');
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getOfficerFieldSet(): Portlet
    {
        $table = new Table('JoJooTbl');
        $table->setHeaderRow([
            'joo_relation' => Trans::getWord('relation'),
            'joo_user' => Trans::getWord('officer'),
            'joo_username' => Trans::getWord('email'),
        ]);
        $table->addRows(JobOfficerDao::loadByJobOrderIdAndSystemSettings($this->getDetailReferenceValue(), $this->User->getSsId()));
        # Create a portlet box.
        $portlet = new Portlet('JoJooPtl', Trans::getWord('officer'));
        if ($this->isJobDeleted() === false) {
            $modal = $this->getOfficerModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getOfficerDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jobOfficer', 'getByReferenceForDelete', ['joo_id']);
            if ($this->isAllowUpdate()) {
                $btnCpMdl = new ModalButton('btnJoJooMdl', Trans::getWord('addOfficer'), $modal->getModalId());
                $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
                $portlet->addButton($btnCpMdl);
            }
        }
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get officer modal.
     *
     * @return Modal
     */
    protected function getOfficerModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJooMdl', Trans::getWord('officer'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertOfficer');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertOfficer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        # Create Contact Field
        $relationField = $this->Field->getSingleSelect('relation', 'joo_relation', $this->getParameterForModal('joo_relation', $showModal));
        $relationField->setHiddenField('joo_rel_id', $this->getParameterForModal('joo_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->addParameter('default_id', $this->User->getRelId());
        $relationField->addParameter('vendor_id', $this->getIntParameter('jo_vendor_id'));
        $relationField->addClearField('joo_user');
        $relationField->addClearField('joo_us_id');
        $relationField->addClearField('joo_username');
        $relationField->setEnableDetailButton(false);
        $relationField->setEnableNewButton(false);
        # Create Contact Field
        $officerField = $this->Field->getSingleSelect('user', 'joo_user', $this->getParameterForModal('joo_user', $showModal));
        $officerField->setHiddenField('joo_us_id', $this->getParameterForModal('joo_us_id'));
        $officerField->addParameter('ss_id', $this->User->getSsId());
        $officerField->addParameterById('rel_id', 'joo_rel_id', Trans::getWord('relation'));
        $officerField->setEnableDetailButton(false);
        $officerField->setEnableNewButton(false);
        $officerField->setAutoCompleteFields([
            'joo_username' => 'us_username'
        ]);

        $emailFied = $this->Field->getText('joo_username', $this->getParameterForModal('joo_username', $showModal));
        $emailFied->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getWord('officer'), $officerField, true);
        $fieldSet->addField(Trans::getWord('email'), $emailFied, true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get office delete modal.
     *
     * @return Modal
     */
    protected function getOfficerDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJooDelMdl', Trans::getWord('deleteOfficer'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteOfficer');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteOfficer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        $officerField = $this->Field->getText('joo_user_del', $this->getParameterForModal('joo_user_del', $showModal));
        $officerField->setReadOnly();
        $emailFied = $this->Field->getText('joo_username_del', $this->getParameterForModal('joo_username_del', $showModal));
        $emailFied->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('officer'), $officerField);
        $fieldSet->addField(Trans::getWord('email'), $emailFied);
        $fieldSet->addHiddenField($this->Field->getHidden('joo_id_del', $this->getParameterForModal('joo_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getTimeSheetFieldSet(): Portlet
    {
        $table = new Table('JoJaeTbl');
        $table->setHeaderRow([
            'jae_action' => Trans::getWord('action'),
            'jae_event' => Trans::getWord('event'),
            'jae_remark' => Trans::getWord('remark'),
            'jae_time' => Trans::getWord('time'),
            'jae_creator' => Trans::getWord('reportedBy'),
            'jae_created_on' => Trans::getWord('reportedOn'),
            'image' => Trans::getWord('image'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->addColumnAttribute('image', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJaePtl', Trans::getWord('timeSheet'));
        $btnXls = new SubmitButton('btnExportTimeShtXls', Trans::getWord('exportXls'), 'doExportTimeShtXls', $this->getMainFormId());
        $btnXls->setIcon(Icon::FileExcelO)->btnSuccess()->pullRight()->btnMedium();
        $btnXls->setEnableLoading(false);
        $portlet->addButton($btnXls);

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the notification receiver portlet.
     *
     * @return Portlet
     */
    protected function getJobNotificationReceiver(): Portlet
    {
        $table = new Table('JoJnrTBl');
        $table->setHeaderRow([
            'jnr_rel_name' => Trans::getWord('relation'),
            'jnr_cp_name' => Trans::getWord('receiver'),
            'jnr_cp_email' => Trans::getWord('email'),
        ]);
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jnr_jo_id', $this->getDetailReferenceValue());
        $wheres[] = '(jnr.jnr_deleted_on IS NULL)';
        $jnrData = JobNotificationReceiverDao::loadData($wheres);
        $table->addRows($jnrData);
        # Create a portlet box.
        $portlet = new Portlet('JoJnrPtl', Trans::getWord('notification'));
        if ($this->isJobDeleted() === false) {
            $modal = $this->getNotificationModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getNotificationDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jnr', 'getByReferenceForDelete', ['jnr_id']);
            $btnCpMdl = new ModalButton('btnJoJnrMdl', Trans::getWord('addReceiver'), $modal->getModalId());
            $btnCpMdl->addAttribute('class', 'btn-primary pull-right');
            $btnCpMdl->setIcon('fa fa-plus');
            $portlet->addButton($btnCpMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get officer modal.
     *
     * @return Modal
     */
    protected function getNotificationModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJnrMdl', Trans::getWord('notificationReceiver'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertNotificationReceiver');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertNotificationReceiver' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        # Create Contact Field
        $cpField = $this->Field->getSingleSelect('contactPerson', 'jnr_cp_name', $this->getParameterForModal('jnr_cp_name', $showModal));
        $cpField->setHiddenField('jnr_cp_id', $this->getParameterForModal('jnr_cp_id', $showModal));
        $relIds[] = $this->User->Relation->getId();
        $relIds[] = $this->getIntParameter('jo_rel_id');
        $cpField->addParameter('cp_rel_ids', implode(',', $relIds));
        $cpField->setDetailReferenceCode('cp_id');

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('receiver'), $cpField, true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get office delete modal.
     *
     * @return Modal
     */
    protected function getNotificationDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJnrDelMdl', Trans::getWord('deleteNotificationReceiver'));
        $modal->setFormSubmit($this->getMainFormId(), 'deleteNotificationReceiver');
        $showModal = false;
        if ($this->getFormAction() === 'deleteNotificationReceiver' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        $cpField = $this->Field->getText('jnr_cp_name_del', $this->getParameterForModal('jnr_cp_name_del', $showModal));
        $cpField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('notification'), $cpField);
        $fieldSet->addHiddenField($this->Field->getHidden('jnr_id_del', $this->getParameterForModal('jnr_id_del', $showModal)));
        $modal->addText('<p class="label-large" style="text-align: center">' . Trans::getWord('deleteConfirmation', 'message') . '<p>');
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $imageNotFoundPath = asset('images/image-not-found.jpg');
        $events = JobActionEventDao::loadEventByJobId($this->getDetailReferenceValue());
        if ($this->isValidParameter('jo_finish_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_finish_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('finish'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_creator' => $this->getStringParameter('jo_finish_by'),
                'jae_time' => $time,
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('finish') . '"/>',
            ];
        }
        if ($this->isValidParameter('jo_document_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_document_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('documentComplete'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_creator' => $this->getStringParameter('jo_document_by'),
                'jae_time' => $time,
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('documentComplete') . '"/>',
            ];
        }
        $docDao = new DocumentDao();
        foreach ($events as $row) {
            $image = '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            if (empty($row['doc_id']) === false) {
                $path = $docDao->getDocumentPath($row);
                $image = '<img onclick="App.popup(\'' . url('/download?doc_id=' . $row['doc_id']) . '\')" style="text-align: center" class="img-responsive avatar-view" src="' . $path . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            }
            $time = '';
            if (empty($row['jae_date']) === false) {
                if (empty($row['jae_time']) === false) {
                    $time = DateTimeParser::format($row['jae_date'] . ' ' . $row['jae_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $time = DateTimeParser::format($row['jae_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result[] = [
                'jae_action' => Trans::getWord($row['jae_action'] . $this->getIntParameter('jo_srt_id') . '.description', 'action'),
                'jae_event' => $row['jae_description'],
                'jae_remark' => $row['remark'],
                'jae_creator' => $row['jae_created_by'],
                'jae_time' => $time,
                'jae_created_on' => DateTimeParser::format($row['jae_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                'image' => $image,
            ];
        }
        if ($this->isValidParameter('jo_publish_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_publish_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('published'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_creator' => $this->getStringParameter('jo_publish_by'),
                'jae_time' => $time,
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('published') . '"/>',
            ];
        }
        $time = DateTimeParser::format($this->getStringParameter('jo_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
        $result[] = [
            'jae_action' => Trans::getWord('created'),
            'jae_event' => '',
            'jae_remark' => '',
            'jae_creator' => $this->getStringParameter('jo_created_by'),
            'jae_time' => $time,
            'jae_created_on' => $time,
            'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('created') . '"/>',
        ];


        return $result;
    }

    /**
     * Function to get the sales Field Set.
     *
     * @return Portlet
     */
    protected function getSalesFieldSet(): Portlet
    {
        # insert modal
        $modal = $this->getSalesModal();
        $this->View->addModal($modal);
        # delete Modal
        $modalDelete = $this->getSalesDeleteModal();
        $this->View->addModal($modalDelete);
        # insert By quotation modal
        $modalInsertQuotation = $this->getSalesQuotationInsertModal();
        $this->View->addModal($modalInsertQuotation);
        # insert By quotation modal
        $modalUpdateQuotation = $this->getSalesQuotationUpdateModal();
        $this->View->addModal($modalUpdateQuotation);
        # insert modal
        $salesReimburseMdl = $this->getSalesReimburseUpdateModal();
        $this->View->addModal($salesReimburseMdl);
        # delete Modal
        $deleteSalesReimburseMdl = $this->getSalesReimburseDeleteModal();
        $this->View->addModal($deleteSalesReimburseMdl);

        $table = new Table('JoSalesTbl');
        $table->setHeaderRow([
            'jos_relation' => Trans::getFinanceWord('billTo'),
            'jos_description' => Trans::getFinanceWord('description'),
            'jos_quantity' => Trans::getFinanceWord('quantity'),
            'jos_rate' => Trans::getFinanceWord('rate'),
            'jos_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jos_tax_name' => Trans::getFinanceWord('tax'),
            'jos_total' => Trans::getFinanceWord('total'),
            'jos_type' => Trans::getFinanceWord('type'),
            'jos_quotation_number' => Trans::getFinanceWord('quotation'),
        ]);
        $rows = [];
        $number = new NumberFormatter($this->User);
        $i = 0;
        $isAllowUpdateReimburse = $this->isAllowUpdateSalesReimbursement();
        foreach ($this->JobSales as $row) {
            if ($row['jos_type'] === 'S') {
                $row['jos_type'] = new LabelPrimary(Trans::getFinanceWord('revenue'));
            } else {
                $row['jos_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $action = '';
            if (empty($row['jos_si_id']) === false) {
                $url = url('/salesInvoice/detail?si_id=' . $row['jos_si_id']);
                $siButton = new HyperLink('JoSiBtn' . $row['jos_id'], '', $url);
                $siButton->viewAsButton();
                $siButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $action .= $siButton . ' ';
            }
            if (empty($row['jos_sid_id']) === true) {
                if (empty($row['jos_prd_id']) === false) {
                    $btnUpdate = new ModalButton('btnJosUp' . $row['jos_id'], '', $modalUpdateQuotation->getModalId());
                    $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
                    $btnUpdate->setEnableCallBack('jobSales', 'getByIdForUpdateFromQuotation');
                    $btnUpdate->addParameter('jos_id', $row['jos_id']);
                    $action .= $btnUpdate;
                } elseif (empty($row['jos_jop_id']) === false && $isAllowUpdateReimburse === true) {
                    $btnUpdate = new ModalButton('btnJosUp' . $row['jos_id'], '', $salesReimburseMdl->getModalId());
                    $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
                    $btnUpdate->setEnableCallBack('jobSales', 'getByIdForUpdateReimburse');
                    $btnUpdate->addParameter('jos_id', $row['jos_id']);
                    $action .= $btnUpdate;
                } elseif (empty($row['jos_jop_id']) === true) {
                    $btnUpdate = new ModalButton('btnJosUp' . $row['jos_id'], '', $modal->getModalId());
                    $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
                    $btnUpdate->setEnableCallBack('jobSales', 'getByIdForUpdate');
                    $btnUpdate->addParameter('jos_id', $row['jos_id']);
                    $action .= $btnUpdate;
                }
                if (empty($row['jos_jop_id']) === false && $isAllowUpdateReimburse === true) {
                    $btnDelete = new ModalButton('btnJosDel' . $row['jos_id'], '', $deleteSalesReimburseMdl->getModalId());
                    $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                    $btnDelete->setEnableCallBack('jobSales', 'getByIdForDeleteReimburse');
                    $btnDelete->addParameter('jos_id', $row['jos_id']);
                    $action .= ' ' . $btnDelete;
                } elseif (empty($row['jos_jop_id']) === true) {
                    $btnDelete = new ModalButton('btnJosDel' . $row['jos_id'], '', $modalDelete->getModalId());
                    $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                    $btnDelete->setEnableCallBack('jobSales', 'getByIdForDelete');
                    $btnDelete->addParameter('jos_id', $row['jos_id']);
                    $action .= ' ' . $btnDelete;
                }
            }
            $row['jos_action'] = $action;
            $row['jos_description'] = $row['jos_cc_code'] . ' - ' . $row['jos_description'];
            $row['jos_quantity'] = $number->doFormatFloat($row['jos_quantity']) . ' ' . $row['jos_uom_code'];
            $row['jos_rate'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_rate']);
            if (empty($row['jos_exchange_rate']) === false) {
                $row['jos_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_exchange_rate']);
                $row['jos_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_total']);
            } else {
                $row['jos_total'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_total']);
                $table->addCellAttribute('jos_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jos_tax_id']) === true) {
                $table->addCellAttribute('jos_tax_name', $i, 'style', 'background-color: red;');
            }
            $rows[] = $row;
            $i++;
        }
        if ($this->isAllowToUpdateSalesInformation()) {
            $table->addColumnAtTheEnd('jos_action', Trans::getWord('action'));
            $table->addColumnAttribute('jos_action', 'style', 'text-align: center;');
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jos_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_cur_iso', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_quotation_number', 'style', 'text-align: center;');

        $portlet = new Portlet('JoSalesPtl', Trans::getWord('sales'));

        if ($this->isAllowToUpdateSalesInformation()) {
            if ($this->isAllowInsertSalesWithoutQuotation()) {
                # Create btn add Sales.
                $btnSalesMdl = new ModalButton('btnJoSalesMdl', Trans::getWord('sales'), $modal->getModalId());
                $btnSalesMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
                $portlet->addButton($btnSalesMdl);
            }
            if ($this->isValidSoId() === true) {
                $btnSalesQtMdl = new ModalButton('btnJosQtMdl', Trans::getWord('quotation'), $modalInsertQuotation->getModalId());
                $btnSalesQtMdl->setIcon(Icon::Plus)->btnInfo()->pullRight();
                $portlet->addButton($btnSalesQtMdl);
            }
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get sales modal.
     *
     * @return Modal
     */
    protected function getSalesModal(): Modal
    {
        $modal = new Modal('SalesMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateSales');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateSales' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $costCodeField = $this->Field->getSingleSelect('costCode', 'jos_cc_code', $this->getParameterForModal('jos_cc_code', $showModal));
        $costCodeField->setHiddenField('jos_cc_id', $this->getParameterForModal('jos_cc_id', $showModal));
        $costCodeField->addParameter('cc_ss_id', $this->User->getSsId());
        $costCodeField->addParameterById('ccg_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $costCodeField->addParameter('ccg_type', 'S');
        $costCodeField->setEnableDetailButton(false);
        $costCodeField->setEnableNewButton(false);
        $costCodeField->setAutoCompleteFields([
            'jos_description' => 'cc_name'
        ]);

        $relationField = $this->Field->getSingleSelect('relation', 'jos_relation', $this->getParameterForModal('jos_relation', $showModal));
        $relationField->setHiddenField('jos_rel_id', $this->getIntParameter('jos_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');

        $uomField = $this->Field->getSingleSelect('unit', 'jos_uom_code', $this->getParameterForModal('jos_uom_code', $showModal));
        $uomField->setHiddenField('jos_uom_id', $this->getParameterForModal('jos_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'jos_tax_name', $this->getParameterForModal('jos_tax_name', $showModal));
        $taxField->setHiddenField('jos_tax_id', $this->getParameterForModal('jos_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);

        $curField = $this->Field->getSingleSelect('currency', 'jos_cur_iso', $this->getParameterForModal('jos_cur_iso', $showModal));
        $curField->setHiddenField('jos_cur_id', $this->getParameterForModal('jos_cur_id', $showModal));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);


        $fieldSet->addField(Trans::getFinanceWord('billTo'), $relationField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jos_description', $this->getParameterForModal('jos_description', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jos_quantity', $this->getParameterForModal('jos_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jos_rate', $this->getParameterForModal('jos_rate', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jos_exchange_rate', $this->getParameterForModal('jos_exchange_rate', $showModal)));
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jos_id', $this->getParameterForModal('jos_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get sales modal.
     *
     * @return Modal
     */
    protected function getSalesReimburseUpdateModal(): Modal
    {
        $modal = new Modal('SalesRmbUpMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateSalesReimbursement');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateSalesReimbursement' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $costCodeField = $this->Field->getSingleSelect('costCode', 'jos_cc_code_r', $this->getParameterForModal('jos_cc_code_r', $showModal));
        $costCodeField->setHiddenField('jos_cc_id_r', $this->getParameterForModal('jos_cc_id_r', $showModal));
        $costCodeField->setEnableDetailButton(false);
        $costCodeField->setReadOnly();

        $relationField = $this->Field->getSingleSelect('relation', 'jos_relation_r', $this->getParameterForModal('jos_relation_r', $showModal));
        $relationField->setHiddenField('jos_rel_id_r', $this->getIntParameter('jos_rel_id_r'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');

        $uomField = $this->Field->getSingleSelect('unit', 'jos_uom_code_r', $this->getParameterForModal('jos_uom_code_r', $showModal));
        $uomField->setHiddenField('jos_uom_id_r', $this->getParameterForModal('jos_uom_id_r', $showModal));
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);
        $uomField->setReadOnly();

        $taxField = $this->Field->getSingleSelect('tax', 'jos_tax_name_r', $this->getParameterForModal('jos_tax_name_r', $showModal));
        $taxField->setHiddenField('jos_tax_id_r', $this->getParameterForModal('jos_tax_id_r', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);
        $taxField->setReadOnly();

        $curField = $this->Field->getSingleSelect('currency', 'jos_cur_iso_r', $this->getParameterForModal('jos_cur_iso_r', $showModal));
        $curField->setHiddenField('jos_cur_id_r', $this->getParameterForModal('jos_cur_id_r', $showModal));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);
        $curField->setReadOnly();

        $descriptionField = $this->Field->getText('jos_description_r', $this->getParameterForModal('jos_description_r', $showModal));
        $descriptionField->setReadOnly();

        $exchangeRate = $this->Field->getNumber('jos_exchange_rate_r', $this->getParameterForModal('jos_exchange_rate_r', $showModal));
        $exchangeRate->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('billTo'), $relationField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $descriptionField, true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jos_quantity_r', $this->getParameterForModal('jos_quantity_r', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jos_rate_r', $this->getParameterForModal('jos_rate_r', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $exchangeRate);
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jos_id_r', $this->getParameterForModal('jos_id_r', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_jop_id_r', $this->getParameterForModal('jos_jop_id_r', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get sales delete modal.
     *
     * @return Modal
     */
    protected function getSalesReimburseDeleteModal(): Modal
    {
        $modal = new Modal('SalesRmbDltMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteSalesReimbursement');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteSalesReimbursement' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        # Cost Code Field
        $ccField = $this->Field->getSingleSelect('costCode', 'jos_jop_cc_code', $this->getParameterForModal('jos_jop_cc_code', $showModal));
        $ccField->setHiddenField('jos_jop_cc_id', $this->getParameterForModal('jos_jop_cc_id', $showModal));
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameterById('ccg_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $ccField->addParameter('ccg_type', 'P');
        $ccField->setEnableDetailButton(false);
        $ccField->setEnableNewButton(false);

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addField(Trans::getFinanceWord('oldCostCode'), $this->Field->getText('jos_cc_code_rdel', $this->getParameterForModal('jos_cc_code_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('newCostCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jos_description_rdel', $this->getParameterForModal('jos_description_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('uom'), $this->Field->getText('jos_uom_code_rdel', $this->getParameterForModal('jos_uom_code_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jos_quantity_rdel', $this->getParameterForModal('jos_quantity_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jos_rate_rdel', $this->getParameterForModal('jos_rate_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('currency'), $this->Field->getText('jos_cur_iso_rdel', $this->getParameterForModal('jos_cur_iso_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('tax'), $this->Field->getText('jos_tax_name_rdel', $this->getParameterForModal('jos_tax_name_rdel', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jos_exchange_rate_rdel', $this->getParameterForModal('jos_exchange_rate_rdel', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_id_rdel', $this->getParameterForModal('jos_id_rdel', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_jop_id_rdel', $this->getParameterForModal('jos_jop_id_rdel', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }

    /**
     * Function to get sales modal.
     *
     * @return Modal
     */
    protected function getSalesQuotationInsertModal(): Modal
    {
        $modal = new Modal('JosQtInsMdl', Trans::getFinanceWord('selectPrice'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertSalesByQuotation');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertSalesByQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $priceField = $this->Field->getSingleSelectTable('prc', 'jos_prc_code', $this->getParameterForModal('jos_prc_code', $showModal), 'loadSingleSelectTable');
        $priceField->setHiddenField('jos_prc_id', $this->getParameterForModal('jos_prc_id', $showModal));
        $priceField->setValueCode('prc_id');
        $priceField->setLabelCode('prc_code');

        $priceField->setFilters([
            'prc_qt_number' => Trans::getFinanceWord('quotationNumber'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
        ]);
        $priceField->setAutoCompleteFields([
            'jos_prc_relation' => 'prc_relation',
            'jos_prc_qt_number' => 'prc_qt_number',
            'jos_prc_srt_name' => 'prc_srt_name',
        ]);
        $priceField->addParameter('prc_ss_id', $this->User->getSsId());
        $priceField->addParameter('prc_type', 'S');
        $priceField->addParameter('prc_srv_id', $this->getIntParameter('jo_srv_id'));
        $priceField->addParameter('prc_srt_id', $this->getIntParameter('jo_srt_id'));
        $srvCode = $this->getStringParameter('jo_srv_code');
        $header = [
            'prc_qt_number' => Trans::getFinanceWord('quotationNumber'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
        ];
        if ($srvCode === 'inklaring') {
            $priceField->addParameter('prc_tm_id', $this->getIntParameter('so_tm_id'));
            $header['prc_transport_module'] = Trans::getFinanceWord('transportModule');
            $header['prc_port'] = Trans::getFinanceWord('operationPort');
        } elseif ($srvCode === 'delivery') {
            $priceField->addParameter('prc_eg_id', $this->getIntParameter('jdl_eg_id'));
            if ($this->getStringParameter('jo_srt_route') === 'ptp' || $this->getStringParameter('jo_srt_route') === 'ptpc') {
                $header['prc_transport_module'] = Trans::getFinanceWord('transportModule');
            }
            $header['prc_eg_name'] = Trans::getFinanceWord('transportType');
            $header['prc_origin'] = Trans::getFinanceWord('origin');
            $header['prc_destination'] = Trans::getFinanceWord('destination');
        } else {
            $header['prc_warehouse'] = Trans::getFinanceWord('warehouse');
        }
        $priceField->setTableColumns($header);
        $priceField->setParentModal($modal->getModalId());
        $this->View->addModal($priceField->getModal());

        $relation = $this->Field->getText('jos_prc_relation', $this->getParameterForModal('jos_prc_relation', $showModal));
        $relation->setReadOnly();
        # Currency
        $quotation = $this->Field->getText('jos_prc_qt_number', $this->getParameterForModal('jos_prc_qt_number', $showModal));
        $quotation->setReadOnly();

        $srtField = $this->Field->getText('jos_prc_srt_name', $this->getParameterForModal('jos_prc_srt_name', $showModal));
        $srtField->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('quotation'), $quotation);
        $fieldSet->addField(Trans::getFinanceWord('code'), $priceField, true);
        $fieldSet->addField(Trans::getFinanceWord('customer'), $relation);
        $fieldSet->addField(Trans::getFinanceWord('serviceTerm'), $srtField);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get sales modal.
     *
     * @return Modal
     */
    protected function getSalesQuotationUpdateModal(): Modal
    {
        $modal = new Modal('JosQtUpMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateSalesByQuotation');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateSalesByQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $costCodeField = $this->Field->getText('jos_cc_code_qt', $this->getParameterForModal('jos_cc_code_qt', $showModal));
        $costCodeField->setReadOnly();
        $relationField = $this->Field->getText('jos_relation_qt', $this->getParameterForModal('jos_relation_qt', $showModal));
        $relationField->setReadOnly();
        $uomField = $this->Field->getText('jos_uom_code_qt', $this->getParameterForModal('jos_uom_code_qt', $showModal));
        $uomField->setReadOnly();
        $curField = $this->Field->getText('jos_cur_iso_qt', $this->getParameterForModal('jos_cur_iso_qt', $showModal));
        $curField->setReadOnly();
        $rateField = $this->Field->getNumber('jos_rate_qt', $this->getParameterForModal('jos_rate_qt', $showModal));
        $rateField->setReadOnly();

        $taxField = $this->Field->getSingleSelect('tax', 'jos_tax_name_qt', $this->getParameterForModal('jos_tax_name_qt', $showModal));
        $taxField->setHiddenField('jos_tax_id_qt', $this->getParameterForModal('jos_tax_id_qt', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);

        $fieldSet->addField(Trans::getFinanceWord('billTo'), $relationField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jos_description_qt', $this->getParameterForModal('jos_description_qt', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jos_quantity_qt', $this->getParameterForModal('jos_quantity_qt', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $rateField, true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jos_exchange_rate_qt', $this->getParameterForModal('jos_exchange_rate_qt', $showModal)));
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jos_id_qt', $this->getParameterForModal('jos_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_cc_id_qt', $this->getParameterForModal('jos_cc_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_rel_id_qt', $this->getParameterForModal('jos_rel_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_uom_id_qt', $this->getParameterForModal('jos_uom_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_cur_id_qt', $this->getParameterForModal('jos_cur_id_qt', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get sales delete modal.
     *
     * @return Modal
     */
    protected function getSalesDeleteModal(): Modal
    {
        $modal = new Modal('SalesDltMdl', Trans::getWord('sales'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteSales');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteSales' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addField(Trans::getFinanceWord('billTo'), $this->Field->getText('jos_relation_del', $this->getParameterForModal('jos_relation_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('jos_cc_code_del', $this->getParameterForModal('jos_cc_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jos_description_del', $this->getParameterForModal('jos_description_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('uom'), $this->Field->getText('jos_uom_code_del', $this->getParameterForModal('jos_uom_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jos_quantity_del', $this->getParameterForModal('jos_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jos_rate_del', $this->getParameterForModal('jos_rate_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('currency'), $this->Field->getText('jos_cur_iso_del', $this->getParameterForModal('jos_cur_iso_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('tax'), $this->Field->getText('jos_tax_name_del', $this->getParameterForModal('jos_tax_name_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jos_exchange_rate_del', $this->getParameterForModal('jos_exchange_rate_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jos_id_del', $this->getParameterForModal('jos_id_del', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getPurchaseFieldSet(): Portlet
    {
        # insert modal
        $modal = $this->getPurchaseModal();
        $this->View->addModal($modal);
        # insert Quotation modal
        $jopQtInsertModal = $this->getPurchaseQuotationInsertModal();
        $this->View->addModal($jopQtInsertModal);
        # insert modal
        $jopQtUpdateModal = $this->getPurchaseQuotationUpdateModal();
        $this->View->addModal($jopQtUpdateModal);
        # delete modal
        $modalDelete = $this->getPurchaseDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('JoPurchaseTbl');
        # Upload Receipt Modal
        $uploadReceiptMdl = $this->getPurchaseReceiptModal();
        $this->View->addModal($uploadReceiptMdl);
        # Delete Receipt Modal
        $deleteReceiptMdl = $this->getPurchaseReceiptDeleteModal();
        $this->View->addModal($deleteReceiptMdl);

        $table->setHeaderRow([
            'jop_relation' => Trans::getFinanceWord('billTo'),
            'jop_description' => Trans::getFinanceWord('description'),
            'jop_quantity' => Trans::getFinanceWord('quantity'),
            'jop_rate' => Trans::getFinanceWord('rate'),
            'jop_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jop_tax_name' => Trans::getFinanceWord('tax'),
            'jop_total' => Trans::getFinanceWord('total'),
            'jop_type' => Trans::getFinanceWord('type'),
            'jop_quotation_number' => Trans::getFinanceWord('quotation'),
            'jop_receipt' => Trans::getFinanceWord('receipt'),
        ]);
        $rows = [];
        $showBtnInvoice = false;
        $number = new NumberFormatter($this->User);
        $i = 0;
        foreach ($this->JobPurchase as $row) {
            $row['jop_description'] = $row['jop_cc_code'] . ' - ' . $row['jop_description'];
            $row['jop_quantity'] = $number->doFormatFloat($row['jop_quantity']) . ' ' . $row['jop_uom_code'];
            $row['jop_rate'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_rate']);
            if (empty($row['jop_exchange_rate']) === false) {
                $row['jop_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_exchange_rate']);
                $row['jop_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_total']);
            } else {
                $row['jop_total'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_total']);
                $table->addCellAttribute('jop_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jop_tax_id']) === true) {
                $table->addCellAttribute('jop_tax_name', $i, 'style', 'background-color: red;');
            }
            if ($row['jop_type'] === 'P') {
                $row['jop_type'] = new LabelPrimary(Trans::getFinanceWord('cogs'));
            } else {
                $row['jop_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $btnReceipt = '';
            if (empty($row['jop_doc_id']) === true && empty($row['jop_cad_id']) === true) {
                $btnUpload = new ModalButton('btnRecUp' . $row['jop_id'], '', $uploadReceiptMdl->getModalId());
                $btnUpload->setIcon(Icon::Upload)->btnWarning()->viewIconOnly();
                $btnUpload->setEnableCallBack('jobPurchase', 'getByIdForUploadReceipt');
                $btnUpload->addParameter('jop_id', $row['jop_id']);
                $btnReceipt = $btnUpload;
            }
            if (empty($row['jop_doc_id']) === false) {
                $btnDown = new Button('btnRecDown' . $row['jop_id'], '');
                $btnDown->setIcon(Icon::Download)->btnPrimary()->viewIconOnly();
                $btnDown->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['jop_doc_id']) . "')");
                $btnReceipt = $btnDown;
                if (empty($row['jop_cad_id']) === true) {
                    $btnDelete = new ModalButton('btnRecDel' . $row['jop_id'], '', $deleteReceiptMdl->getModalId());
                    $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                    $btnDelete->setEnableCallBack('jobPurchase', 'getByIdForDeleteReceipt');
                    $btnDelete->addParameter('jop_id', $row['jop_id']);
                    $btnReceipt .= ' ' . $btnDelete;
                }

            }
            $row['jop_receipt'] = $btnReceipt;
            $action = '';
            if (empty($row['jop_pi_id']) === false) {
                $url = url('/purchaseInvoice/detail?pi_id=' . $row['jop_pi_id']);
                $piButton = new HyperLink('JoPiBtn' . $row['jop_id'], '', $url);
                $piButton->viewAsButton();
                $piButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $action .= $piButton . ' ';
            } else {
                $showBtnInvoice = true;
            }
            if (empty($row['jop_pid_id']) === true && empty($row['jop_cad_id']) === true) {
                if (empty($row['jop_prd_id']) === false) {
                    $btnUpdate = new ModalButton('btnJopUp' . $row['jop_id'], '', $jopQtUpdateModal->getModalId());
                    $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
                    $btnUpdate->setEnableCallBack('jobPurchase', 'getByIdForUpdateFromQuotation');
                    $btnUpdate->addParameter('jop_id', $row['jop_id']);
                } else {
                    $btnUpdate = new ModalButton('btnJopUp' . $row['jop_id'], '', $modal->getModalId());
                    $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
                    $btnUpdate->setEnableCallBack('jobPurchase', 'getByIdForUpdate');
                    $btnUpdate->addParameter('jop_id', $row['jop_id']);
                }

                $btnDelete = new ModalButton('btnJopDel' . $row['jop_id'], '', $modalDelete->getModalId());
                $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDelete->setEnableCallBack('jobPurchase', 'getByIdForDelete');
                $btnDelete->addParameter('jop_id', $row['jop_id']);
                $action .= $btnUpdate . ' ' . $btnDelete;
            }
            $row['jop_action'] = $action;

            $rows[] = $row;
            $i++;
        }
        if ($this->isAllowToUpdatePurchaseInformation()) {
            $table->addColumnAtTheEnd('jop_action', Trans::getWord('action'));
            $table->addColumnAttribute('jop_action', 'style', 'text-align: center;');
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jop_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_receipt', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_quotation_number', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoPurchasePtl', Trans::getWord('purchase'));
        if ($this->isAllowToUpdatePurchaseInformation()) {
            if ($this->isAllowInsertPurchaseWithoutQuotation() === true) {
                # create new purchase button
                $btnPurchaseMdl = new ModalButton('btnJoPurchaseMdl', Trans::getWord('purchase'), $modal->getModalId());
                $btnPurchaseMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight()->btnMedium();
                $portlet->addButton($btnPurchaseMdl);
            }
            # create new quotation purchase button
            $btnQtnPurchaseMdl = new ModalButton('btnJopQtInMdl', Trans::getWord('quotation'), $jopQtInsertModal->getModalId());
            $btnQtnPurchaseMdl->setIcon(Icon::Plus)->btnInfo()->pullRight()->btnMedium();
            $portlet->addButton($btnQtnPurchaseMdl);

            # Create CA Button
            if ($this->isJobPublished() === true) {
                if (empty($this->CashAdvance) === true && $this->isOwnVendor() === true && $this->isUserJobManager() === true) {
                    $url = url('/ca/detail?ca_jo_id=' . $this->getDetailReferenceValue());
                    $caButton = new HyperLink('BtnJoCa', Trans::getFinanceWord('cashAdvance'), $url);
                    $caButton->viewAsButton();
                    $caButton->setIcon(Icon::Plus)->btnSuccess()->pullRight()->btnMedium();
                    $portlet->addButton($caButton);
                }
                if ($showBtnInvoice) {
                    $url = url('/purchaseInvoice/detail');
                    $caButton = new HyperLink('BtnJoPi', Trans::getFinanceWord('registerInvoice'), $url);
                    $caButton->viewAsButton();
                    $caButton->setIcon(Icon::Plus)->btnAqua()->pullRight()->btnMedium();
                    $portlet->addButton($caButton);
                }
            }
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getPurchaseModal(): Modal
    {
        $modal = new Modal('PurchaseMdl', Trans::getWord('purchase'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdatePurchase');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdatePurchase' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelect('costCode', 'jop_cc_code', $this->getParameterForModal('jop_cc_code', $showModal), 'loadPurchaseData');
        $ccField->setHiddenField('jop_cc_id', $this->getParameterForModal('jop_cc_id', $showModal));
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameterById('ccg_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $ccField->setEnableDetailButton(false);
        $ccField->setEnableNewButton(false);
        $ccField->setAutoCompleteFields([
            'jop_description' => 'cc_name',
            'jop_type_name' => 'cc_type_name',
            'jop_type' => 'cc_type',
        ]);

        $relationField = $this->Field->getSingleSelect('relation', 'jop_relation', $this->getParameterForModal('jop_relation', $showModal));
        $relationField->setHiddenField('jop_rel_id', $this->getParameterForModal('jop_rel_id', $showModal));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');

        $uomField = $this->Field->getSingleSelect('unit', 'jop_uom_code', $this->getParameterForModal('jop_uom_code', $showModal));
        $uomField->setHiddenField('jop_uom_id', $this->getParameterForModal('jop_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'jop_tax_name', $this->getParameterForModal('jop_tax_name', $showModal));
        $taxField->setHiddenField('jop_tax_id', $this->getParameterForModal('jop_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);

        $type = $this->Field->getText('jop_type_name', $this->getParameterForModal('jop_type_name', $showModal));
        $type->setReadOnly();
        # Currency
        $curField = $this->Field->getSingleSelect('currency', 'jop_cur_iso', $this->getParameterForModal('jop_cur_iso', $showModal));
        $curField->setHiddenField('jop_cur_id', $this->getParameterForModal('jop_cur_id', $showModal));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);

        $fieldSet->addField(Trans::getFinanceWord('billTo'), $relationField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('type'), $type);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jop_description', $this->getParameterForModal('jop_description', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jop_quantity', $this->getParameterForModal('jop_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jop_rate', $this->getParameterForModal('jop_rate', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jop_exchange_rate', $this->getParameterForModal('jop_exchange_rate', $showModal)));
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jop_id', $this->getParameterForModal('jop_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_jos_id', $this->getParameterForModal('jop_jos_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_type', $this->getParameterForModal('jop_type', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getPurchaseQuotationUpdateModal(): Modal
    {
        $modal = new Modal('JopQtUpMdl', Trans::getWord('purchase'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdatePurchaseByQuotation');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdatePurchaseByQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getText('jop_cc_code_qt', $this->getParameterForModal('jop_cc_code_qt', $showModal));
        $ccField->setReadOnly();
        $relationField = $this->Field->getText('jop_relation_qt', $this->getParameterForModal('jop_relation_qt', $showModal));
        $relationField->setReadOnly();

        $uomField = $this->Field->getText('jop_uom_code_qt', $this->getParameterForModal('jop_uom_code_qt', $showModal));
        $uomField->setReadOnly();

        $taxField = $this->Field->getSingleSelect('tax', 'jop_tax_name_qt', $this->getParameterForModal('jop_tax_name_qt', $showModal));
        $taxField->setHiddenField('jop_tax_id_qt', $this->getParameterForModal('jop_tax_id_qt', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);

        $type = $this->Field->getText('jop_type_name_qt', $this->getParameterForModal('jop_type_name_qt', $showModal));
        $type->setReadOnly();
        # Currency
        $curField = $this->Field->getText('jop_cur_iso_qt', $this->getParameterForModal('jop_cur_iso_qt', $showModal));
        $curField->setReadOnly();

        $rateField = $this->Field->getNumber('jop_rate_qt', $this->getParameterForModal('jop_rate_qt', $showModal));

        $fieldSet->addField(Trans::getFinanceWord('billTo'), $relationField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('type'), $type);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jop_description_qt', $this->getParameterForModal('jop_description_qt', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jop_quantity_qt', $this->getParameterForModal('jop_quantity_qt', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $rateField, true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jop_exchange_rate_qt', $this->getParameterForModal('jop_exchange_rate_qt', $showModal)));
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jop_id_qt', $this->getParameterForModal('jop_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_cc_id_qt', $this->getParameterForModal('jop_cc_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_cur_id_qt', $this->getParameterForModal('jop_cur_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_jos_id_qt', $this->getParameterForModal('jop_jos_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_type_qt', $this->getParameterForModal('jop_type_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_uom_id_qt', $this->getParameterForModal('jop_uom_id_qt', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_rel_id_qt', $this->getParameterForModal('jop_rel_id_qt', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getPurchaseQuotationInsertModal(): Modal
    {
        $modal = new Modal('JopQtInsMdl', Trans::getFinanceWord('selectPurchaseQuotation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertPurchaseByQuotation');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertPurchaseByQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $priceField = $this->Field->getSingleSelectTable('prc', 'jop_prc_code', $this->getParameterForModal('jop_prc_code', $showModal), 'loadSingleSelectTable');
        $priceField->setHiddenField('jop_prc_id', $this->getParameterForModal('jop_prc_id', $showModal));
        $priceField->setValueCode('prc_id');
        $priceField->setLabelCode('prc_code');

        $priceField->setFilters([
            'prc_qt_number' => Trans::getFinanceWord('quotationNumber'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
        ]);
        $priceField->setAutoCompleteFields([
            'jop_prc_relation' => 'prc_relation',
            'jop_prc_qt_number' => 'prc_qt_number',
            'jop_prc_srt_name' => 'prc_srt_name',
        ]);
        $priceField->addParameter('prc_ss_id', $this->User->getSsId());
        $priceField->addParameter('prc_type', 'P');
        $priceField->addParameter('prc_srv_id', $this->getIntParameter('jo_srv_id'));
        $priceField->addParameter('prc_srt_id', $this->getIntParameter('jo_srt_id'));
        $srvCode = $this->getStringParameter('jo_srv_code');
        $header = [
            'prc_qt_number' => Trans::getFinanceWord('quotationNumber'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
        ];
        if ($srvCode === 'inklaring') {
            $priceField->addParameter('prc_tm_id', $this->getIntParameter('so_tm_id'));
            $header['prc_transport_module'] = Trans::getFinanceWord('transportModule');
            $header['prc_port'] = Trans::getFinanceWord('operationPort');
        } elseif ($srvCode === 'delivery') {
            $priceField->addParameter('prc_eg_id', $this->getIntParameter('jdl_eg_id'));
            if ($this->getStringParameter('jo_srt_route') === 'ptp' || $this->getStringParameter('jo_srt_route') === 'ptpc') {
                $header['prc_transport_module'] = Trans::getFinanceWord('transportModule');
            }
            $header['prc_eg_name'] = Trans::getFinanceWord('transportType');
            $header['prc_origin'] = Trans::getFinanceWord('origin');
            $header['prc_destination'] = Trans::getFinanceWord('destination');
        } else {
            $header['prc_warehouse'] = Trans::getFinanceWord('warehouse');
        }
        $priceField->setTableColumns($header);
        $priceField->setParentModal($modal->getModalId());
        $this->View->addModal($priceField->getModal());

        $relation = $this->Field->getText('jop_prc_relation', $this->getParameterForModal('jop_prc_relation', $showModal));
        $relation->setReadOnly();
        # Currency
        $quotation = $this->Field->getText('jop_prc_qt_number', $this->getParameterForModal('jop_prc_qt_number', $showModal));
        $quotation->setReadOnly();

        $srtField = $this->Field->getText('jop_prc_srt_name', $this->getParameterForModal('jop_prc_srt_name', $showModal));
        $srtField->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('quotation'), $quotation);
        $fieldSet->addField(Trans::getFinanceWord('code'), $priceField, true);
        $fieldSet->addField(Trans::getFinanceWord('vendor'), $relation);
        $fieldSet->addField(Trans::getFinanceWord('serviceTerm'), $srtField);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getPurchaseReceiptModal(): Modal
    {
        $modal = new Modal('JoPuRcMdl', Trans::getFinanceWord('uploadReceipt'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadPurchaseReceipt');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadPurchaseReceipt' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $descField = $this->Field->getText('jop_description_doc', $this->getParameterForModal('jop_description_doc', $showModal));
        $descField->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('description'), $descField);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('jop_file_doc', ''), true);
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jop_id_doc', $this->getParameterForModal('jop_id_doc', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getPurchaseReceiptDeleteModal(): Modal
    {
        $modal = new Modal('JoPuRcDelMdl', Trans::getFinanceWord('deleteReceipt'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeletePurchaseReceipt');
        $text = Trans::getWord('deleteConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $descField = $this->Field->getText('jop_description_doc_del', $this->getParameterForModal('jop_description_doc_del', true));
        $descField->setReadOnly();
        $fieldSet->addField(Trans::getFinanceWord('description'), $descField);
        $fieldSet->addHiddenField($this->Field->getHidden('jop_doc_id_del', $this->getParameterForModal('jop_doc_id_del', true)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_id_doc_del', $this->getParameterForModal('jop_id_doc_del', true)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase delete modal.
     *
     * @return Modal
     */
    protected function getPurchaseDeleteModal(): Modal
    {
        $modal = new Modal('PurchaseDltMdl', Trans::getWord('purchase'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeletePurchase');
        $showModal = false;
        if ($this->getFormAction() === 'doDeletePurchase' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addField(Trans::getFinanceWord('billTo'), $this->Field->getText('jop_relation_del', $this->getParameterForModal('jop_relation_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('jop_cc_code_del', $this->getParameterForModal('jop_cc_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('type'), $this->Field->getText('jop_type_name_del', $this->getParameterForModal('jop_type_name_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jop_description_del', $this->getParameterForModal('jop_description_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jop_quantity_del', $this->getParameterForModal('jop_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('uom'), $this->Field->getText('jop_uom_code_del', $this->getParameterForModal('jop_uom_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jop_rate_del', $this->getParameterForModal('jop_rate_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('currency'), $this->Field->getText('jop_cur_iso_del', $this->getParameterForModal('jop_cur_iso_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('tax'), $this->Field->getText('jop_tax_name_del', $this->getParameterForModal('jop_tax_name_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getText('jop_exchange_rate_del', $this->getParameterForModal('jop_exchange_rate_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_jos_id_del', $this->getParameterForModal('jop_jos_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jop_id_del', $this->getParameterForModal('jop_id_del', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getFinanceMarginFieldSet(): Portlet
    {
        $table = new Table('JoFinMgnTbl');
        $table->setHeaderRow([
            'fn_description' => Trans::getFinanceWord('description'),
            'fn_planning' => Trans::getFinanceWord('planning'),
            'fn_invoice' => Trans::getFinanceWord('invoiced'),
            'fn_pay' => Trans::getFinanceWord('paid'),
        ]);
        $table->setDisableLineNumber();
        $data = JobOrderDao::loadFinanceMarginData($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('fn_planning', 'float');
        $table->setColumnType('fn_invoice', 'float');
        $table->setColumnType('fn_pay', 'float');
        # Create a portlet box.
        $portlet = new Portlet('JoFinMgnPtl', Trans::getFinanceWord('grossMargin'));
        $portlet->addTable($table);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getBaseDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('JoDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete'),
        ]);
        // $docTable->setDeleteActionByModal($docDeleteModal, 'document', 'getByReferenceForDelete', ['doc_id']);
        # load data
        $wheres = [];
        if ($this->getStringParameter('jo_srv_code') === 'inklaring') {
            $joWhere = "((dcg.dcg_code = 'joborder') AND (doc.doc_group_reference = " . $this->getDetailReferenceValue() . '))';
            $soWhere = "((dcg.dcg_code = 'salesorder') AND (doc.doc_group_reference = " . $this->getIntParameter('jik_so_id') . '))';
            $wheres[] = '(' . $joWhere . ' OR ' . $soWhere . ')';
        } else {
            $wheres[] = "(dcg.dcg_code = 'joborder')";
            $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        }
        if ($this->isThirdPartyUser() === true) {
            $wheres[] = "(dct.dct_master = 'Y')";
        }
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            if ((int)$row['doc_group_reference'] === $this->getDetailReferenceValue()) {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('JoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        if ($this->isAllowUpdate()) {
            # create modal.
            $docModal = $this->getBaseDocumentModal('joborder');
            $this->View->addModal($docModal);
            $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
            $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnDocMdl);
        }

        return $portlet;
    }
//
//    /**
//     * Function to get the relation bank modal.
//     *
//     * @return Modal
//     */
//    private function getDocumentModal(): Modal
//    {
//        $modal = new Modal('JoDocMdl', Trans::getWord('documents'));
//        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDocument');
//        $showModal = false;
//        if ($this->getFormAction() === 'doUpdateDocument' && $this->isValidPostValues() === false) {
//            $modal->setShowOnLoad();
//            $showModal = true;
//        }
//
//        $fieldSet = new FieldSet($this->Validation);
//        $fieldSet->setGridDimension(12, 12, 12);
//        # Create document type field.
//        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
//        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
//        $dctFields->addParameter('dcg_code', 'joborder');
//        $dctFields->addParameter('dct_master', 'Y');
//        $dctFields->setEnableDetailButton(false);
//        $dctFields->setEnableNewButton(false);
//
//        # Add field into field set.
//        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
//        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
//        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
//        $modal->addFieldSet($fieldSet);
//
//        return $modal;
//    }
//
//    /**
//     * Function to get the relation bank modal.
//     *
//     * @return Modal
//     */
//    private function getDocumentDeleteModal(): Modal
//    {
//        $modal = new Modal('JoDocDelMdl', Trans::getWord('deleteDocument'));
//        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
//        $showModal = false;
//        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
//            $modal->setShowOnLoad();
//            $showModal = true;
//        }
//
//        $fieldSet = new FieldSet($this->Validation);
//        $fieldSet->setGridDimension(6, 6);
//        # Create document type field.
//        # Add field into field set.
//        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
//        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
//        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));
//        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
//        $p->setAsLabelLarge()->setAlignCenter();
//        $modal->addText($p);
//        $modal->setBtnOkName(Trans::getWord('yesDelete'));
//        $modal->addFieldSet($fieldSet);
//
//        return $modal;
//    }
//

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getGoodsUploadModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JogUpMdl', Trans::getWord('uploadGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadJogWarehouse');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadJogWarehouse' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('jog_file', $this->getParameterForModal('jog_file', $showModal)), true);

        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeSalesInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeSales');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowUpdateSalesReimbursement(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdateSalesReimbursement');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToUpdateSalesInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdateSales');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeDepositInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeDeposit');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToUpdateDepositInformation(): bool
    {
//        return true;
        return $this->PageSetting->checkPageRight('AllowUpdateDeposit');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowInsertSalesWithoutQuotation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowInsertSalesWithoutQuotation');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeePurchaseInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeePurchase')
            && $this->getIntParameter('jo_vendor_id', 0) !== 0;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToUpdatePurchaseInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdatePurchase');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowInsertPurchaseWithoutQuotation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowInsertPurchaseWithoutQuotation');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowPublishWithoutFinanceData(): bool
    {
        return $this->PageSetting->checkPageRight('AllowPublishWithoutFinanceData');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeMarginInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeFinanceMargin')
            && $this->getIntParameter('jo_vendor_id', 0) !== 0;
    }

    /**
     * Function to get cash advance portlet.
     *
     * @return Portlet
     */
    protected function getCashAdvancePortlet(): Portlet
    {
        $totalCa = 0.0;
        if (empty($this->CashAdvance['ca_receive_on']) === false) {
            if (empty($this->CashAdvance['ca_settlement_on']) === true) {
                $totalCa = (float)$this->CashAdvance['ca_amount'] + (float)$this->CashAdvance['ca_reserve_amount'];
            } else {
                $totalCa = (float)$this->CashAdvance['ca_actual_amount'] + (float)$this->CashAdvance['ca_ea_amount'];
            }
        }
        $dtParser = new DateTimeParser();
        $number = new NumberFormatter($this->User);
        $data = [
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->CashAdvance['ca_ba_code'] . ' - ' . $this->CashAdvance['ca_ba_description'],
            ],
            [
                'label' => Trans::getFinanceWord('eCardAccount'),
                'value' => $this->CashAdvance['ca_ea_code'] . ' - ' . $this->CashAdvance['ca_ea_description'],
            ],
            [
                'label' => Trans::getFinanceWord('date'),
                'value' => $dtParser->formatDate($this->CashAdvance['ca_date']),
            ],
            [
                'label' => Trans::getFinanceWord('receiver'),
                'value' => $this->CashAdvance['ca_cp_name'],
            ],
            [
                'label' => Trans::getFinanceWord('receiveOn'),
                'value' => $dtParser->formatDateTime($this->CashAdvance['ca_receive_on']),
            ],
            [
                'label' => Trans::getFinanceWord('settlementDate'),
                'value' => $dtParser->formatDateTime($this->CashAdvance['ca_settlement_on']),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        $title = $this->CashAdvance['ca_number'];
        $title .= ' ( ' . $this->CashAdvance['ca_currency'] . ' ' . $number->doFormatFloat($totalCa) . ' )';
        # Create a portlet box.
        $portlet = new Portlet('JoCaPtl', $title);
        if ($this->isUserJobManager() === true) {
            $url = url('/ca/detail?ca_id=' . $this->CashAdvance['ca_id']);
            $caButton = new HyperLink('BtnJoCa', Trans::getFinanceWord('cashPayment'), $url);
            $caButton->viewAsButton();
            $caButton->setIcon(Icon::Eye)->btnSuccess()->pullRight()->btnMedium();
            $portlet->addButton($caButton);
        }

        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to add all default portlet
     *
     * @return bool
     */
    protected function isOwnVendor(): bool
    {
        return $this->getIntParameter('jo_vendor_id', 0) === $this->User->getRelId();
    }

    /**
     * Function to add all default portlet
     *
     * @return bool
     */
    protected function isUserJobManager(): bool
    {
        return $this->getIntParameter('jo_manager_id') === $this->User->getId();
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getDepositPortlet(): Portlet
    {
        $table = new Table('JoJdTbl');
        $table->setHeaderRow([
            'jd_number' => Trans::getFinanceWord('number'),
            'jd_relation' => Trans::getFinanceWord('relation'),
            'jd_cc_name' => Trans::getFinanceWord('description'),
            'jd_ref' => Trans::getFinanceWord('reference'),
            'jd_amount' => Trans::getFinanceWord('amount'),
            'jd_date' => Trans::getFinanceWord('date'),
            'jd_status' => Trans::getFinanceWord('status'),
        ]);
        $rows = [];
        $wheres = [];
        $wheres[] = '(jd.jd_deleted_on IS NULL)';
        $wheres[] = '(jd.jd_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $data = JobDepositDao::loadData($wheres);
        $dtParser = new DateTimeParser();
        $jdDao = new JobDepositDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $url = url('/jd/detail?jd_id=' . $row['jd_id']);
            $jdBtn = new HyperLink('JoJdBtn' . $row['jd_id'], '', $url);
            $jdBtn->viewAsButton();
            $jdBtn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['jd_action'] = $jdBtn;
            $row['jd_ref'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('relation'),
                    'value' => $row['jd_rel_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('payment'),
                    'value' => $row['jd_paid_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('settlement'),
                    'value' => $row['jd_settle_ref'],
                ],
            ]);
            $row['jd_date'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $dtParser->formatDate($row['jd_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $dtParser->formatDate($row['jd_return_date']),
                ],
            ]);
            $amounts = [
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $number->doFormatFloat((float)$row['jd_amount']),
                ],
                [
                    'label' => Trans::getFinanceWord('claim'),
                    'value' => $number->doFormatFloat((float)$row['jd_claim_amount']),
                ],
            ];
            if (empty($row['jd_settle_on']) === false) {
                $refund = (float)$row['jd_amount'] - (float)$row['jd_claim_amount'];
                $amounts[] = [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $number->doFormatFloat($refund),
                ];
            }
            $row['jd_amount'] = StringFormatter::generateKeyValueTableView($amounts);

            $row['jd_status'] = $jdDao->generateStatus([
                'is_deleted' => !empty($row['jd_deleted_on']),
                'is_return' => !empty($row['jd_return_on']),
                'is_settle' => !empty($row['jd_settle_on']),
                'is_paid' => !empty($row['jd_paid_on']),
                'is_approved' => !empty($row['jd_approved_on']),
                'is_requested' => !empty($row['jda_id']),
                'is_rejected' => !empty($row['jda_deleted_on']),
            ]);
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jd_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jd_status', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJdPtl', Trans::getFinanceWord('deposit'));
        if ($this->isAllowToUpdateDepositInformation()) {
            $url = url('/jd/detail?jd_jo_id=' . $this->getDetailReferenceValue());
            $caButton = new HyperLink('BtnJoJd', Trans::getFinanceWord('registerDeposit'), $url);
            $caButton->viewAsButton();
            $caButton->setIcon(Icon::Plus)->btnPrimary()->pullRight()->btnMedium();
            $portlet->addButton($caButton);
            $table->addColumnAtTheEnd('jd_action', Trans::getFinanceWord('view'));
            $table->addColumnAttribute('jd_action', 'style', 'text-align: center;');
        }
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to add all default portlet
     *
     * @param string $srtRoute To store the service term route.
     *
     * @return void
     */
    protected function setServiceIntoParameter(string $srtRoute): void
    {
        $srt = ServiceTermDao::getByRoute($srtRoute);
        if (empty($srt) === false) {
            $this->setParameter('jo_srv_id', $srt['srt_srv_id']);
            $this->setParameter('jo_srt_id', $srt['srt_id']);

        }
    }

    /**
     * Function to add all default portlet
     *
     * @return void
     */
    protected function includeAllDefaultPortlet(): void
    {
        if ($this->isAllowToSeeSalesInformation()) {
            $this->Tab->addPortlet('finance', $this->getSalesFieldSet());
        }
        if ($this->isAllowToSeePurchaseInformation()) {
            $this->Tab->addPortlet('finance', $this->getPurchaseFieldSet());
            if ($this->isAllowToSeeDepositInformation()) {
                $this->Tab->addPortlet('finance', $this->getDepositPortlet());
            }
            if (empty($this->CashAdvance) === false) {
                $this->Tab->addPortlet('finance', $this->getCashAdvancePortlet());
            }
        }
        if ($this->isAllowToSeeMarginInformation()) {
            $this->Tab->addPortlet('finance', $this->getFinanceMarginFieldSet());
        }
        $this->Tab->addPortlet('officer', $this->getOfficerFieldSet());
        $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
        if ($this->isValidParameter('jo_publish_on') === true) {
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetFieldSet());
        }
        $this->Tab->addPortlet('notificationReceiver', $this->getJobNotificationReceiver());
    }

    /**
     * Function do generate notification receiver
     *
     * @param string $notificationCode The notification code.
     *
     * @return void
     */
    protected function doGenerateNotificationReceiver(string $notificationCode = ''): void
    {
        # The job manager
        $mainReceiver = [];
        $manager = UserMappingDao::getByUserIdAndSystemId($this->getIntParameter('jo_manager_id'), $this->User->getSsId());
        if (empty($manager) === false) {
            $mainReceiver[] = $manager['ump_cp_id'];
        }
        # The job creator
//        $mainReceiver[] = $this->User->Relation->getPersonId();
        # Get officer user
        $officers = JobOfficerDao::loadByJobOrderIdAndSystemSettings($this->getDetailReferenceValue(), $this->User->getSsId());
        foreach ($officers as $officer) {
            $mainReceiver[] = $officer['joo_cp_id'];
        }
        # Get user group notification
        $jobDao = new JobOrderDao();
        $moduleNotification = $jobDao->getJobNotificationModule($this->getStringParameter('jo_srv_code'));
        $jnrData = JobNotificationReceiverDao::loadDataByUserGroupNotification($this->getDetailReferenceValue(), $notificationCode, $moduleNotification, $this->User->getSsId());
        $receiverExist = [];
        foreach ($jnrData as $row) {
            $receiverExist[] = $row['jnr_cp_id'];
        }
        # Merge receiver
        $receivers = array_unique(array_merge($mainReceiver, $receiverExist));
        # Process notification
        if (empty($notificationCode) === false) {
            $nf = new JobNotificationBuilder($this, $notificationCode, $moduleNotification, $receivers);
            $nf->doNotify();
        }
    }

    /**
     * Function to validate fields require before publish.
     *
     * @param array $fields To store required fields
     *
     * @return string
     */
    protected function checkRequiredPublishFields(array $fields): string
    {

        $valid = true;
        $message = [];
        foreach ($fields as $key => $label) {
            if ($this->isValidParameter($key) === false) {
                $valid = false;
                $message[] = [
                    'label' => Trans::getWord($label),
                    'value' => new LabelTrueFalse($valid),
                ];
            }
        }
        if ($valid === true) {
            return '';
        }

        return StringFormatter::generateCustomTableView($message, 8, 8);
    }


    /**
     * Function to check if user is customer
     *
     * @return bool
     */
    protected function isCustomerUser(): bool
    {
        return $this->User->getRelId() === $this->getIntParameter('jo_rel_id');
    }

    /**
     * Function to check if user is third party user.
     *
     * @return bool
     */
    protected function isThirdPartyUser(): bool
    {
        return $this->PageSetting->checkPageRight('ThirdPartyAccess');
    }

    /**
     * Function to check is this container job or not
     *
     * @return bool
     */
    protected function isContainerJob(): bool
    {
        return $this->getStringParameter('jo_srt_container', 'N') === 'Y';
    }

    /**
     * Function to check is valid SO Id
     *
     * @return bool
     */
    public function isValidSoId(): bool
    {
        return $this->isValidParameter('so_id');
    }

    /**
     * Function to get so Id
     *
     * @return int
     */
    public function getSoId(): ?int
    {
        return $this->getIntParameter('so_id');
    }

}
