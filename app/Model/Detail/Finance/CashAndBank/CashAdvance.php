<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Finance\CashAndBank;

use App\Frame\Document\FileUpload;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
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
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Finance\CashAndBank\BankAccountBalanceDao;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;
use App\Model\Dao\Finance\CashAndBank\BankTransactionApprovalDao;
use App\Model\Dao\Finance\CashAndBank\BankTransactionDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDetailDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceReceivedDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceReturnedDao;
use App\Model\Dao\Finance\CashAndBank\ElectronicBalanceDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;

/**
 * Class to handle the creation of detail CashAdvance page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvance extends AbstractFormModel
{

    /**
     * Property to store number object
     *
     * @var NumberFormatter $Number
     */
    protected $Number;
    /**
     * Property to store date time parser object
     *
     * @var DateTimeParser $DtParser
     */
    protected $DtParser;

    /**
     * Property to store cash advance detail data
     *
     * @var array $DetailData
     */
    private $DetailData = [];

    /**
     * Property to store amount
     *
     * @var float $Amount
     */
    private $Amount = 0.0;

    /**
     * Property to store card amount
     *
     * @var float $CardAmount
     */
    private $CardAmount = 0.0;


    /**
     * Property to store settlement amount
     *
     * @var float $SettlementAmount
     */
    private $SettlementAmount = 0.0;

    /**
     * Property to store return amount
     *
     * @var float $ReturnAmount
     */
    private $ReturnAmount = 0.0;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ca', 'ca_id');
        $this->setParameters($parameters);
        $this->Number = new NumberFormatter($this->User);
        $this->DtParser = new DateTimeParser();

    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('CashAdvance', 0, 0, $this->getIntParameter('ca_srv_id', 0));
        $colVal = [
            'ca_ss_id' => $this->User->getSsId(),
            'ca_number' => $number,
            'ca_reference' => $this->getStringParameter('ca_reference'),
            'ca_ba_id' => $this->getIntParameter('ca_ba_id'),
            'ca_ea_id' => $this->getIntParameter('ca_ea_id'),
            'ca_cp_id' => $this->getIntParameter('ca_cp_id'),
            'ca_jo_id' => $this->getIntParameter('ca_jo_id'),
            'ca_date' => $this->getStringParameter('ca_date'),
            'ca_reserve_amount' => $this->getFloatParameter('ca_reserve_amount'),
            'ca_notes' => $this->getStringParameter('ca_notes'),
        ];
        $caDao = new CashAdvanceDao();
        $caDao->doInsertTransaction($colVal);
        return $caDao->getLastInsertId();
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
                'ca_reference' => $this->getStringParameter('ca_reference'),
                'ca_ba_id' => $this->getIntParameter('ca_ba_id'),
                'ca_ea_id' => $this->getIntParameter('ca_ea_id'),
                'ca_cp_id' => $this->getIntParameter('ca_cp_id'),
                'ca_jo_id' => $this->getIntParameter('ca_jo_id'),
                'ca_date' => $this->getStringParameter('ca_date'),
                'ca_reserve_amount' => $this->getFloatParameter('ca_reserve_amount'),
                'ca_notes' => $this->getStringParameter('ca_notes'),
            ];
            $caDao = new CashAdvanceDao();
            $caDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $caDao = new CashAdvanceDao();
            $caDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
            if ($this->isJoExist() === true) {
                # delete jop data
                $data = CashAdvanceDetailDao::getByCaId($this->getDetailReferenceValue(), true);
                $jopDao = new JobPurchaseDao();
                $josDao = new JobSalesDao();
                foreach ($data as $row) {
                    if (empty($row['cad_jop_id']) === false) {
                        $jopDao->doDeleteTransaction($row['cad_jop_id']);
                    }
                    if (empty($row['cad_jos_id']) === false) {
                        $josDao->doDeleteTransaction($row['cad_jos_id']);
                    }
                }
            }
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $exchangeRate = 1;
            $rate = $this->getFloatParameter('cad_quantity') * $this->getFloatParameter('cad_rate');
            $taxAmount = ($rate * $this->getFloatParameter('cad_tax_percent')) / 100;
            $total = $rate + $taxAmount;
            $cadDao = new CashAdvanceDetailDao();
            $josDao = new JobSalesDao();
            $joPurchaseDao = new JobPurchaseDao();
            if ($this->isJoExist() === false) {
                # Upsert Cash Advance Detail
                $cadColVal = [
                    'cad_ca_id' => $this->getDetailReferenceValue(),
                    'cad_cc_id' => $this->getIntParameter('cad_cc_id'),
                    'cad_description' => $this->getStringParameter('cad_description'),
                    'cad_quantity' => $this->getFloatParameter('cad_quantity'),
                    'cad_uom_id' => $this->getIntParameter('cad_uom_id'),
                    'cad_rate' => $this->getFloatParameter('cad_rate'),
                    'cad_cur_id' => $this->getIntParameter('ca_cur_id'),
                    'cad_exchange_rate' => $exchangeRate,
                    'cad_tax_id' => $this->getIntParameter('cad_tax_id'),
                    'cad_total' => $total,
                    'cad_ea_payment' => $this->getStringParameter('cad_ea_payment', 'N'),
                ];
                if ($this->isValidParameter('cad_id') === true) {
                    $cadDao->doUpdateTransaction($this->getIntParameter('cad_id'), $cadColVal);
                } else {
                    $cadDao->doInsertTransaction($cadColVal);
                }
            } else {
                $docId = $this->getIntParameter('cad_doc_id');
                $file = $this->getFileParameter('cad_file');
                if ($file !== null) {
                    $docDao = new DocumentDao();
                    if ($this->isValidParameter('cad_doc_id') === true) {
                        $docDao->doDeleteTransaction($docId);
                    }
                    $colVal = [
                        'doc_ss_id' => $this->User->getSsId(),
                        'doc_dct_id' => $this->getIntParameter('cad_dct_id'),
                        'doc_group_reference' => $this->getDetailReferenceValue(),
                        'doc_type_reference' => null,
                        'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                        'doc_description' => Trans::getFinanceWord('paymentReceipt'),
                        'doc_file_size' => $file->getSize(),
                        'doc_file_type' => $file->getClientOriginalExtension(),
                        'doc_public' => 'N',
                    ];
                    $docDao->doInsertTransaction($colVal);
                    $docId = $docDao->getLastInsertId();
                    $upload = new FileUpload($docId);
                    $upload->upload($file);
                }
                # Upsert Job Sales
                $josId = null;
                if ($this->getStringParameter('cad_type', 'P') === 'R') {
                    $josColVal = [
                        'jos_jo_id' => $this->getJoId(),
                        'jos_rel_id' => $this->User->getRelId(),
                        'jos_cc_id' => $this->getIntParameter('cad_cc_id'),
                        'jos_description' => $this->getStringParameter('cad_description'),
                        'jos_quantity' => $this->getFloatParameter('cad_quantity'),
                        'jos_rate' => $this->getFloatParameter('cad_rate'),
                        'jos_uom_id' => $this->getIntParameter('cad_uom_id'),
                        'jos_exchange_rate' => $exchangeRate,
                        'jos_cur_id' => $this->getIntParameter('ca_cur_id'),
                        'jos_tax_id' => $this->getIntParameter('cad_tax_id'),
                        'jos_total' => $total,
                    ];
                    if ($this->isValidParameter('cad_jos_id') === true) {
                        $josId = $this->getIntParameter('cad_jos_id');
                        $josDao->doUpdateTransaction($josId, $josColVal);
                    } else {
                        $josDao->doInsertTransaction($josColVal);
                        $josId = $josDao->getLastInsertId();
                    }
                }

                # Upsert Job Purchase
                $colVal = [
                    'jop_jo_id' => $this->getJoId(),
                    'jop_rel_id' => $this->User->getRelId(),
                    'jop_cc_id' => $this->getIntParameter('cad_cc_id'),
                    'jop_description' => $this->getStringParameter('cad_description'),
                    'jop_quantity' => $this->getFloatParameter('cad_quantity'),
                    'jop_rate' => $this->getFloatParameter('cad_rate'),
                    'jop_uom_id' => $this->getIntParameter('cad_uom_id'),
                    'jop_exchange_rate' => $exchangeRate,
                    'jop_cur_id' => $this->getIntParameter('ca_cur_id'),
                    'jop_tax_id' => $this->getIntParameter('cad_tax_id'),
                    'jop_jos_id' => $josId,
                    'jop_total' => $total,
                    'jop_doc_id' => $docId,
                ];
                $jopId = null;
                if ($this->isValidParameter('cad_jop_id')) {
                    $jopId = $this->getIntParameter('cad_jop_id');
                    $joPurchaseDao->doUpdateTransaction($jopId, $colVal);
                } else {
                    $joPurchaseDao->doInsertTransaction($colVal);
                    $jopId = $joPurchaseDao->getLastInsertId();
                }
                # Upsert Cash Advance Detail
                $cadColVal = [
                    'cad_ca_id' => $this->getDetailReferenceValue(),
                    'cad_jop_id' => $jopId,
                    'cad_doc_id' => $docId,
                    'cad_ea_payment' => $this->getStringParameter('cad_ea_payment', 'N'),
                ];
                if ($this->isValidParameter('cad_id') === true) {
                    $cadDao->doUpdateTransaction($this->getIntParameter('cad_id'), $cadColVal);
                } else {
                    $cadDao->doInsertTransaction($cadColVal);
                    # Update cad in job purchase
                    $joPurchaseDao->doUpdateTransaction($jopId, [
                        'jop_cad_id' => $cadDao->getLastInsertId()
                    ]);
                }
            }
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            # Delete document receipt
            if ($this->isValidParameter('cad_doc_id_del') === true) {
                $docDao = new DocumentDao();
                $docDao->doDeleteTransaction($this->getIntParameter('cad_doc_id_del'));
            }
            $cadDao = new CashAdvanceDetailDao();
            $cadDao->doDeleteTransaction($this->getIntParameter('cad_id_del'));
            # Delete job purchase
            if ($this->isValidParameter('cad_jop_id_del') === true) {
                $jopDao = new JobPurchaseDao();
                $jopDao->doDeleteTransaction($this->getIntParameter('cad_jop_id_del'));
            }
            # Delete sales purchase
            if ($this->isValidParameter('cad_jos_id_del') === true) {
                $josDao = new JobSalesDao();
                $josDao->doDeleteTransaction($this->getIntParameter('cad_jos_id_del'));
            }
        } elseif ($this->getFormAction() === 'doComplete') {
            $file = $this->getFileParameter('ca_cpl_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('ca_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('ca_dct_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docId);
                $upload->upload($file);
                # insert cash advance received.
                $crcId = $this->doInsertCashAdvanceReceive();
                # Insert cash advance returned.
                $crtId = $this->doInsertCashAdvanceReturn();
                # Update Bank Account balance
                $caAmount = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), false);
                $babId = $this->doInsertBankAccountBalance($this->getIntParameter('ca_ba_id'), $caAmount, false);
                # Update Cash Advance
                $dateTime = date('Y-m-d H:i:s');
                $caColVal = [
                    'ca_amount' => $caAmount,
                    'ca_reserve_amount' => 0.0,
                    'ca_actual_amount' => $caAmount,
                    'ca_return_amount' => 0.0,
                    'ca_receive_on' => $dateTime,
                    'ca_receive_by' => $this->User->getId(),
                    'ca_receive_bab_id' => $babId,
                    'ca_settlement_by' => $this->User->getId(),
                    'ca_settlement_on' => $dateTime,
                    'ca_crc_id' => $crcId,
                    'ca_crt_id' => $crtId,
                ];
                $caDao = new CashAdvanceDao();
                $caDao->doUpdateTransaction($this->getDetailReferenceValue(), $caColVal);
            }
        } elseif ($this->getFormAction() === 'doRequestTopUp') {
            $baBalance = BankAccountBalanceDao::getTotalBalanceAccount($this->getIntParameter('ca_ba_id'));
            $baReceiver = $this->getIntParameter('ca_ba_id');
            if ($this->isReceived() === false) {
                $totalDetailAmount = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), false);
                $totalDetailAmount += $this->getFloatParameter('ca_reserve_amount', 0);
                $notes = $this->getStringParameter('ca_number');
                $requestAmount = $baBalance - $totalDetailAmount;
            } else {
                $totalDetailAmount = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), false);
                $caAmount = $this->getFloatParameter('ca_amount');
                $notes = Trans::getFinanceWord('repayment') . ' ' . $this->getStringParameter('ca_number');
                $requestAmount = ($caAmount + $baBalance) - $totalDetailAmount;
            }
            $btDao = new BankTransactionDao();
            if ($this->isValidParameter('ca_bt_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $number = $sn->loadNumber('BT', $this->User->Relation->getOfficeId());
                $colVal = [
                    'bt_ss_id' => $this->User->getSsId(),
                    'bt_number' => $number,
                    'bt_type' => 'request',
                    'bt_payer_ba_id' => null,
                    'bt_receiver_ba_id' => $baReceiver,
                    'bt_amount' => abs($requestAmount),
                    'bt_currency_exchange' => 1,
                    'bt_notes' => $notes,
                ];
                $btDao->doInsertTransaction($colVal);
                $btId = $btDao->getLastInsertId();
                # Update Cash Advance
                $caDao = new CashAdvanceDao();
                $caDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                    'ca_bt_id' => $btId
                ]);
            } else {
                $btId = $this->getIntParameter('ca_bt_id');
            }
            # Insert top up Request
            $btaDao = new BankTransactionApprovalDao();
            $btaDao->doInsertTransaction([
                'bta_bt_id' => $btId,
            ]);

            $btDao->doUpdateTransaction($btId, [
                'bt_amount' => abs($requestAmount),
                'bt_bta_id' => $btaDao->getLastInsertId(),
            ]);
        } else if ($this->getFormAction() === 'doUpdateReceive') {
            # Add cash advance receive
            $crcDao = new CashAdvanceReceivedDao();
            $crcDao->doInsertTransaction([
                'crc_ca_id' => $this->getDetailReferenceValue()
            ]);
            # Register Cash balance
            $caAmount = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), false);
            $reserveAmount = $this->getFloatParameter('ca_reserve_amount', 0.0);
            $babId = $this->doInsertBankAccountBalance($this->getIntParameter('ca_ba_id'), $caAmount + $reserveAmount, false);
            # Update Cash Advance
            $caColVal = [
                'ca_receive_on' => $this->getStringParameter('ca_receive_date') . ' ' . $this->getStringParameter('ca_receive_time') . ':00',
                'ca_receive_by' => $this->User->getId(),
                'ca_receive_bab_id' => $babId,
                'ca_amount' => $caAmount,
                'ca_crc_id' => $crcDao->getLastInsertId(),
                'ca_bt_id' => null
            ];
            $caDao = new CashAdvanceDao();
            $caDao->doUpdateTransaction($this->getDetailReferenceValue(), $caColVal);
            # Upload Document.
            $file = $this->getFileParameter('ca_receive_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('ca_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('receiveConfirmation'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } else if ($this->getFormAction() === 'doUploadReceipt') {
            # Upload Document.
            $file = $this->getFileParameter('cad_file_doc');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('cad_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('paymentReceipt'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docId);
                $upload->upload($file);

                # Update Cad Document
                $cadDao = new CashAdvanceDetailDao();
                $cadDao->doUpdateTransaction($this->getIntParameter('cad_id_doc'), [
                    'cad_doc_id' => $docId
                ]);

                # Update Job Purchase Document
                if ($this->isValidParameter('cad_jop_id_doc') === true) {
                    $jopDao = new JobPurchaseDao();
                    $jopDao->doUpdateTransaction($this->getIntParameter('cad_jop_id_doc'), [
                        'jop_doc_id' => $docId
                    ]);
                }
            }
        } else if ($this->getFormAction() === 'doDeleteReceipt') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('cad_doc_id_doc_del'));

            # Update Cad Document
            $cadDao = new CashAdvanceDetailDao();
            $cadDao->doUpdateTransaction($this->getIntParameter('cad_id_doc_del'), [
                'cad_doc_id' => null
            ]);

            # Update Job Purchase Document
            if ($this->isValidParameter('cad_jop_id_doc_del') === true) {
                $jopDao = new JobPurchaseDao();
                $jopDao->doUpdateTransaction($this->getIntParameter('cad_jop_id_doc_del'), [
                    'jop_doc_id' => null
                ]);
            }
        } else if ($this->getFormAction() === 'doConfirmSettlement') {
            $date = $this->getStringParameter('ca_settle_date') . ' ' . $this->getStringParameter('ca_settle_time') . ':00';
            # Calculate Amount
            $caAmount = $this->getFloatParameter('ca_amount');
            $caAmount += $this->getFloatParameter('ca_reserve_amount', 0.0);
            $payCash = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), false);
            $payCard = CashAdvanceDetailDao::getTotalDetailByCa($this->getDetailReferenceValue(), true);
            $returnAmount = $caAmount - $payCash;
            # Add Return Confirmation
            $crtDao = new CashAdvanceReturnedDao();
            $crtDao->doInsertTransaction([
                'crt_ca_id' => $this->getDetailReferenceValue()
            ]);
            # Update electronic account balance
            $ebId = null;
            if ($payCard > 0.0) {
                $ebDao = new ElectronicBalanceDao();
                $ebDao->doInsertTransaction([
                    'eb_ea_id' => $this->getIntParameter('ca_ea_id'),
                    'eb_amount' => $payCard * -1
                ]);
                $ebId = $ebDao->getLastInsertId();
            }
            # Update bank account balance
            $babId = null;
            if ($returnAmount !== 0.0) {
                $babId = $this->doInsertBankAccountBalance($this->getIntParameter('ca_ba_id'), $returnAmount, true);
            }
            # Update Cash Advance
            $caColVal = [
                'ca_ea_amount' => $payCard,
                'ca_eb_id' => $ebId,
                'ca_actual_amount' => $payCash,
                'ca_return_amount' => $returnAmount,
                'ca_settlement_on' => $date,
                'ca_settlement_by' => $this->User->getId(),
                'ca_settlement_bab_id' => $babId,
                'ca_crt_id' => $crtDao->getLastInsertId(),
                'ca_bt_id' => null,
            ];
            $caDao = new CashAdvanceDao();
            $caDao->doUpdateTransaction($this->getDetailReferenceValue(), $caColVal);
            # Upload Document.
            $file = $this->getFileParameter('ca_settle_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('ca_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('settlementConfirmation'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        }
    }

    /**
     * function to insert cash advance receive.
     *
     * @return int
     */
    private function doInsertCashAdvanceReceive(): int
    {
        $carcDao = new CashAdvanceReceivedDao();
        $carcDao->doInsertTransaction([
            'crc_ca_id' => $this->getDetailReferenceValue()
        ]);
        return $carcDao->getLastInsertId();
    }


    /**
     * function to insert cash advance return.
     *
     * @return int
     */
    private function doInsertCashAdvanceReturn(): int
    {
        $crtDao = new CashAdvanceReturnedDao();
        $crtDao->doInsertTransaction([
            'crt_ca_id' => $this->getDetailReferenceValue()
        ]);
        return $crtDao->getLastInsertId();
    }

    /**
     * function to insert bank account balance.
     * @param int $baId To store the bank account id
     * @param float $amount To store the amount
     * @param bool $increase To store trigger is it negative or positive amount.
     *
     * @return int
     */
    private function doInsertBankAccountBalance(int $baId, float $amount, bool $increase): int
    {
        if ($increase === false) {
            $amount *= -1;
        }
        $babDao = new BankAccountBalanceDao();
        $babDao->doInsertTransaction([
            'bab_ba_id' => $baId,
            'bab_amount' => $amount,
        ]);
        return $babDao->getLastInsertId();
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CashAdvanceDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            $account = BankAccountDao::getByUser($this->User);
            if (empty($account) === true) {
                Message::throwMessage(Trans::getMessageWord('doNotHavePermission'));
            }
            $jobOrder = [];
            if ($this->isValidParameter('ca_jo_id') === true) {
                $jobOrder = JobOrderDao::getByReferenceAndSystem($this->getIntParameter('ca_jo_id'), $this->User->getSsId());
            }
            $this->setHiddenFields($account, $jobOrder);
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        } else {
            # Add modal for insert detail data.
            $modalDetailInsert = $this->getDetailModal();
            $this->View->addModal($modalDetailInsert);
            $this->doPrepareDetailData($modalDetailInsert);
            $this->setHiddenFields();
            $this->overridePageTitle();
            # Add view
            if ($this->isAllowUpdate() === false || $this->isReceived() === true) {
                if ($this->isJoExist() === true && $this->isReceived() === true) {
                    $this->Tab->addContent('general', $this->getWidget());
                }
                $this->Tab->addPortlet('general', $this->getGeneralViewPortlet());
                if ($this->isJoExist() === true) {
                    $this->Tab->addPortlet('general', $this->getJobViewPortlet());
                }
            } else {
                $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            }
            $this->Tab->addPortlet('general', $this->getDetailPortlet($modalDetailInsert));
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('ca', $this->getDetailReferenceValue(), '', 0, $this->isAllowUpdate()));
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
            $this->Validation->checkRequire('ca_ba_id');
            $this->Validation->checkRequire('ca_date');
            $this->Validation->checkDate('ca_date');
            $this->Validation->checkRequire('ca_reference', 2, 256);
            if ($this->isJoExist() === true) {
                $this->Validation->checkRequire('ca_cp_id');
            }
            if ($this->isValidParameter('ca_reserve_amount') === true) {
                $this->Validation->checkFloat('ca_reserve_amount');
            }
            $this->Validation->checkMaxLength('ca_notes', 256);
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('cad_cc_id');
            $this->Validation->checkRequire('cad_description', 2, 256);
            $this->Validation->checkRequire('cad_uom_id');
            $this->Validation->checkRequire('cad_quantity');
            $this->Validation->checkFloat('cad_quantity');
            $this->Validation->checkRequire('cad_rate');
            $this->Validation->checkFloat('cad_rate');
            $this->Validation->checkRequire('cad_tax_id');
            $this->Validation->checkRequire('cad_tax_percent');
            $this->Validation->checkFloat('cad_tax_percent');
            $this->Validation->checkRequire('ca_cur_id');
            if ($this->isReceived() === true) {
                if ($this->isCardExist() === true) {
                    $this->Validation->checkRequire('cad_ea_payment');
                }
                $this->Validation->checkRequire('cad_file');
            }
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('cad_id_del');
        } elseif ($this->getFormAction() === 'doComplete') {
            $this->Validation->checkRequire('ca_amount');
            $this->Validation->checkFloat('ca_amount');
            $this->Validation->checkRequire('ca_date');
            $this->Validation->checkRequire('ca_ba_id');
            $this->Validation->checkRequire('ca_dct_id');
            $this->Validation->checkRequire('ca_dct_description');
            $this->Validation->checkRequire('ca_cpl_file');
            $this->Validation->checkFile('ca_cpl_file');
        } elseif ($this->getFormAction() === 'doRequestTopUp') {
            $this->Validation->checkRequire('ca_ba_id');
        } else if ($this->getFormAction() === 'doUpdateReceive') {
            $this->Validation->checkRequire('ca_ba_id');
            $this->Validation->checkRequire('ca_receive_date');
            $this->Validation->checkDate('ca_receive_date');
            $this->Validation->checkRequire('ca_receive_time');
            $this->Validation->checkTime('ca_receive_time');
            $this->Validation->checkRequire('ca_receive_file');
            $this->Validation->checkFile('ca_receive_file');
            $this->Validation->checkRequire('ca_dct_id');
        } else if ($this->getFormAction() === 'doUploadReceipt') {
            $this->Validation->checkRequire('cad_id_doc');
            $this->Validation->checkRequire('cad_jop_id_doc');
            $this->Validation->checkRequire('cad_dct_id');
            $this->Validation->checkRequire('cad_file_doc');
            $this->Validation->checkFile('cad_file_doc');
        } else if ($this->getFormAction() === 'doDeleteReceipt') {
            $this->Validation->checkRequire('cad_doc_id_doc_del');
            $this->Validation->checkRequire('cad_id_doc_del');
            $this->Validation->checkRequire('cad_jop_id_doc_del');
        } else if ($this->getFormAction() === 'doConfirmSettlement') {
            $this->Validation->checkRequire('ca_ba_id');
            $this->Validation->checkRequire('ca_dct_id');
            $this->Validation->checkRequire('ca_settle_date');
            $this->Validation->checkDate('ca_settle_date');
            $this->Validation->checkRequire('ca_settle_time');
            $this->Validation->checkTime('ca_settle_time');
            $this->Validation->checkRequire('ca_settle_file');
            $this->Validation->checkFile('ca_settle_file');
            $this->Validation->checkRequire('ca_amount');
            $this->Validation->checkFloat('ca_amount');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $caDao = new CashAdvanceDao();
        $status = $caDao->generateStatus([
            'is_deleted' => $this->isDeleted(),
            'is_completed' => $this->isSettle(),
            'is_settlement_rejected' => $this->isSettlementRejected(),
            'is_waiting_settlement_confirm' => $this->isSettlementRequested(),
            'is_waiting_settlement' => $this->isReceived(),
            'is_receive_rejected' => $this->isReceiveRejected(),
            'is_waiting_receive_confirm' => $this->isReceiveRequested(),
            'is_top_up_exist' => $this->isValidParameter('ca_bt_id'),
            'is_top_up_paid' => $this->isValidParameter('ca_bt_paid_on'),
            'is_top_up_approved' => $this->isValidParameter('ca_bt_approve_on'),
            'is_top_up_requested' => $this->isValidParameter('ca_bta_id'),
            'is_top_up_rejected' => $this->isValidParameter('ca_bta_reject_on'),
        ]);
        $this->View->setDescription($this->getStringParameter('ca_number') . ' - ' . $status);
        # Show message
        if ($this->isReceiveRejected() === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('caReceiveRejected', '', [
                'user' => $this->getStringParameter('ca_crc_reject_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ca_crc_reject_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('ca_crc_reject_reason')
            ]));
        }
        if ($this->isSettlementRejected() === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('caReturnRejected', '', [
                'user' => $this->getStringParameter('ca_crt_reject_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ca_crt_reject_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('ca_crt_reject_reason')
            ]));
        }
        if ($this->isDeleted() === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', '', [
                'user' => $this->getStringParameter('ca_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ca_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('ca_deleted_reason')
            ]));
        }
        # Check if enough balance to pay cash
        if ($this->isRequireTopUpBalance() === true && $this->isTopUpExist() === false) {
            $this->View->addWarningMessage(Trans::getMessageWord('notEnoughBalanceToPayCa'));
        }
        # Check if top up request is rejected
        if ($this->isTopUpRejected() === true) {
            $this->View->addErrorMessage(Trans::getWord('rejectRequest', 'message', '', [
                'user' => $this->getStringParameter('ca_bta_reject_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ca_bta_reject_on')),
                'reason' => $this->getStringParameter('ca_bta_reject_reason'),
            ]));
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
            $this->setDisableUpdate($this->isAllowUpdate() === false || $this->isReceived() === true);
            if ($this->isAllowUpdate() === true) {
                # Set Deleted Button
                $this->setEnableDeleteButton($this->isAllowDelete());

                # Show complete button for payment without job order
                if ($this->isJoExist() === false && $this->isSettle() === false) {
                    # Show complete button
                    $modal = $this->getCompleteModal();
                    $this->View->addModal($modal);
                    $btnDoc = new ModalButton('CaCplBtn', Trans::getFinanceWord('complete'), $modal->getModalId());
                    $btnDoc->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                    $this->View->addButton($btnDoc);
                }
                if ($this->isJoExist() === true) {
                    if ($this->isReceived() === false) {
                        if ($this->isRequireTopUpBalance() === false || $this->isTopUpApproved() === true) {
                            $pdfButton = new PdfButton('CaRcPrt', Trans::getFinanceWord('cashReceive'), 'cpreceive');
                            $pdfButton->setIcon(Icon::Download)->btnDark()->pullRight()->btnMedium();
                            $pdfButton->addParameter('ca_id', $this->getDetailReferenceValue());
                            $this->View->addButton($pdfButton);
                            # Create
                            $modal = $this->getConfirmationReceiveModal();
                            $this->View->addModal($modal);
                            $btnDoc = new ModalButton('CaRcBtn', Trans::getFinanceWord('confirmReceive'), $modal->getModalId());
                            $btnDoc->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                            $this->View->addButton($btnDoc);
                        }
                        if ($this->isRequireTopUpBalance() === true && ($this->isTopUpExist() === false || $this->isTopUpRejected() === true)) {
                            $modal = $this->getConfirmationTopUpModal();
                            $this->View->addModal($modal);
                            $btnDoc = new ModalButton('CaPTcBtn', Trans::getFinanceWord('requestTopUp'), $modal->getModalId());
                            $btnDoc->setIcon(Icon::Plane)->btnPrimary()->pullRight()->btnMedium();
                            $this->View->addButton($btnDoc);
                        }
                    }
                    # Settlement Action
                    if ($this->isReceived() === true && $this->isSettle() === false) {
                        if ($this->isRequireTopUpBalance() === false || $this->isTopUpApproved() === true) {
                            $pdfButton = new PdfButton('CaRtPrt', Trans::getFinanceWord('cashSettlement'), 'cpsettlement');
                            $pdfButton->setIcon(Icon::Download)->btnDark()->pullRight()->btnMedium();
                            $pdfButton->addParameter('ca_id', $this->getDetailReferenceValue());
                            $this->View->addButton($pdfButton);

                            $modal = $this->getSettlementModal();
                            $this->View->addModal($modal);
                            $btnSettlement = new ModalButton('CaRtBtn', Trans::getFinanceWord('confirmSettlement'), $modal->getModalId());
                            $btnSettlement->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                            $this->View->addButton($btnSettlement);

                        }
                        if ($this->isRequireTopUpBalance() === true && ($this->isTopUpExist() === false || $this->isTopUpRejected() === true)) {
                            # Top Up Cash
                            $modal = $this->getConfirmationTopUpModal();
                            $this->View->addModal($modal);
                            $btnDoc = new ModalButton('CaPTcBtn', Trans::getFinanceWord('requestTopUp'), $modal->getModalId());
                            $btnDoc->setIcon(Icon::Plane)->btnPrimary()->pullRight()->btnMedium();
                            $this->View->addButton($btnDoc);
                        }
                    }
                }

            }
            if ($this->isJoExist() === true) {
                $joDao = new JobOrderDao();
                $btnView = new HyperLink('BtnJobView', $this->getStringParameter('ca_jo_number'), $joDao->getJobUrl('view', $this->getIntParameter('ca_srt_id'), $this->getJoId()));
                $btnView->viewAsButton();
                $btnView->setIcon(Icon::Eye)->btnWarning()->pullRight()->btnMedium();
                $this->View->addButton($btnView);
            }
        }

        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Create Fields.
        $baField = $this->Field->getText('ca_ba_description', $this->getStringParameter('ca_ba_description'));
        $baField->setReadOnly();
        $baBalanceField = $this->Field->getNumber('ca_ba_balance', $this->getFloatParameter('ca_ba_balance'));
        $baBalanceField->setReadOnly();
        # Electronic Field
        $eaField = $this->Field->getSingleSelect('ea', 'ca_ea_description', $this->getStringParameter('ca_ea_description'));
        $eaField->setHiddenField('ca_ea_id', $this->getIntParameter('ca_ea_id'));
        $eaField->setAutoCompleteFields([
            'ca_ea_balance' => 'ea_balance',
            'ca_ea_balance_number' => 'ea_balance_number',
        ]);
        $eaField->addParameter('ea_ss_id', $this->User->getSsId());
        $eaField->addParameter('ea_us_id', $this->User->getId());
        $eaField->setDetailReferenceCode('ea_id');
        $eaField->setEnableNewButton(false);

        # Electronic Balance
        $eaBalanceField = $this->Field->getNumber('ca_ea_balance', $this->getFloatParameter('ca_ea_balance'));
        $eaBalanceField->setReadOnly();

        # Reference
        $referenceField = $this->Field->getText('ca_reference', $this->getStringParameter('ca_reference'));
        if ($this->isJoExist() === true) {
            $referenceField->setReadOnly();
        }
        $srvField = $this->Field->getText('ca_srv_name', $this->getStringParameter('ca_srv_name'));
        $srvField->setReadOnly();
        $srtField = $this->Field->getText('ca_srt_name', $this->getStringParameter('ca_srt_name'));
        $srtField->setReadOnly();
        $joAmount = $this->Field->getNumber('ca_required_amount', $this->getStringParameter('ca_required_amount'));
        $joAmount->setReadOnly();
        # Contact Person Field
        $cpField = $this->Field->getSingleSelect('contactPerson', 'ca_cp_name', $this->getStringParameter('ca_cp_name'));
        $cpField->setHiddenField('ca_cp_id', $this->getIntParameter('ca_cp_id'));
        $cpField->addParameter('cp_rel_id', $this->User->getRelId());
        $cpField->setDetailReferenceCode('cp_id');


        # Create Field Set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field to field set
        if ($this->isJoExist() === false) {
            $fieldSet->addField(Trans::getFinanceWord('accountName'), $baField);
            $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('ca_date', $this->getStringParameter('ca_date')), true);
            $fieldSet->addField(Trans::getFinanceWord('accountBalance'), $baBalanceField);
            $fieldSet->addField(Trans::getFinanceWord('pic'), $cpField, $this->isJoExist());
            $fieldSet->addField(Trans::getFinanceWord('reference'), $referenceField, true);
        } else {
            $fieldSet->addField(Trans::getFinanceWord('accountName'), $baField);
            $fieldSet->addField(Trans::getFinanceWord('jobNumber'), $referenceField);
            $fieldSet->addField(Trans::getFinanceWord('accountBalance'), $baBalanceField);
            $fieldSet->addField(Trans::getFinanceWord('service'), $srvField);
            $fieldSet->addField(Trans::getFinanceWord('eCard'), $eaField);
            $fieldSet->addField(Trans::getFinanceWord('dateRequired'), $this->Field->getCalendar('ca_date', $this->getStringParameter('ca_date')), true);
            $fieldSet->addField(Trans::getFinanceWord('eCardBalance'), $eaBalanceField);
            $fieldSet->addField(Trans::getFinanceWord('receiver'), $cpField, $this->isJoExist());
            $fieldSet->addField(Trans::getFinanceWord('reserveCash'), $this->Field->getNumber('ca_reserve_amount', $this->getFloatParameter('ca_reserve_amount')));
        }
        $fieldSet->addField(Trans::getFinanceWord('notes'), $this->Field->getTextArea('ca_notes', $this->getStringParameter('ca_notes')));
        # Create a portlet box.
        $portlet = new Portlet('CaInPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

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
        # Cash Advance
        $advance = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('cashPayment'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-primary',
            'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat($this->Amount),
            'uom' => '',
            'url' => '',
        ];
        $advance->setData($data);
        $advance->setGridDimension(6, 6);
        $results .= $advance->createView();
        # Reserve Cash
        $plan = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('actualAmount'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark-blue',
            'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat($this->SettlementAmount + $this->CardAmount),
            'uom' => '',
            'url' => '',
        ];
        $plan->setData($data);
        $plan->setGridDimension(6, 6);
        $results .= $plan->createView();

        $large = 6;
        $medium = 6;
        $small = 6;
        if ($this->isCardExist() === true) {
            $large = 4;
            $medium = 4;
            $small = 4;
        }
        # Settlement amount
        $settlement = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('paidWithCash'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-success',
            'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat($this->SettlementAmount),
            'uom' => '',
            'url' => '',
        ];
        $settlement->setData($data);
        $settlement->setGridDimension($large, $medium, $small);
        $results .= $settlement->createView();
        # Card Payment
        if ($this->isCardExist() === true) {
            $eCard = new NumberGeneral();
            $data = [
                'title' => Trans::getFinanceWord('paidWithECard'),
                'icon' => '',
                'tile_style' => 'tile-stats tile-success',
                'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat($this->CardAmount),
                'uom' => '',
                'url' => '',
            ];
            $eCard->setData($data);
            $eCard->setGridDimension($large, $medium, $small);
            $results .= $eCard->createView();
        }
        # Return Amount
        $return = new NumberGeneral();
        if ($this->ReturnAmount >= 0) {
            $data = [
                'title' => Trans::getFinanceWord('cashRefund'),
                'icon' => '',
                'tile_style' => 'tile-stats tile-warning',
                'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat($this->ReturnAmount),
                'uom' => '',
                'url' => '',
            ];
        } else {
            $data = [
                'title' => Trans::getFinanceWord('repayment'),
                'icon' => '',
                'tile_style' => 'tile-stats tile-warning',
                'amount' => $this->getStringParameter('ca_currency') . ' ' . $this->Number->doFormatFloat(abs($this->ReturnAmount)),
                'uom' => '',
                'url' => '',
            ];
        }
        $return->setData($data);
        $return->setGridDimension($large, $medium, $small);
        $results .= $return->createView();
        return $results;
    }


    /**
     * Function to get the purchase Field Set.
     *
     * @param Modal $modal To store the modal for insert data.
     * @return Portlet
     */
    private function getDetailPortlet(Modal $modal): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('CaCadPtl', Trans::getFinanceWord('items'));

        # Add Table
        $table = new Table('CaCadTbl');
        $table->setHeaderRow([
            'cad_description' => Trans::getFinanceWord('description'),
            'cad_quantity' => Trans::getFinanceWord('quantity'),
            'cad_rate' => Trans::getFinanceWord('rate'),
            'cad_tax_name' => Trans::getFinanceWord('tax'),
            'cad_total' => Trans::getFinanceWord('total')
        ]);
        if ($this->isJoExist() === true) {
            $table->addColumnAfter('cad_tax_name', 'cad_type', Trans::getFinanceWord('type'));
            $table->addColumnAttribute('cad_type', 'style', 'text-align: center;');
            if ($this->isReceived() === true) {
                $table->addColumnAfter('cad_total', 'cad_receipt', Trans::getFinanceWord('receipt'));
                $table->addColumnAttribute('cad_receipt', 'style', 'text-align: center;');
                if ($this->isCardExist() === true) {
                    $table->addColumnAfter('cad_total', 'cad_ea_payment', Trans::getFinanceWord('paidWithECard'));
                    $table->setColumnType('cad_ea_payment', 'yesno');
                }
            }
        }
        $table->addRows($this->DetailData);
        $table->addColumnAttribute('cad_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('cad_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('cad_tax_name', 'style', 'text-align: center;');
        $table->setColumnType('cad_total', 'float');
        $table->setFooterType('cad_total', 'SUM');

        if ($this->isAllowUpdate() === true && $this->isSettle() === false && ($this->isTopUpExist() === false || $this->isTopUpRejected() === true)) {
            # Button Insert
            $btnInsert = new ModalButton('CadInsBtn', Trans::getWord('add'), $modal->getModalId());
            $btnInsert->setIcon(Icon::Plus)->pullRight()->btnPrimary();
            $portlet->addButton($btnInsert);

            # Add Update Action
            $table->addColumnAtTheEnd('cad_action', Trans::getFinanceWord('action'));
            $table->addColumnAttribute('cad_action', 'style', 'text-align: center;');

        }

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get detail modal.
     *
     * @return Modal
     */
    protected function getDetailModal(): Modal
    {
        $modal = new Modal('CaCadMdl', Trans::getWord('items'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelectTable('costCode', 'cad_cost_code', $this->getParameterForModal('cad_cost_code', $showModal), 'loadPurchaseData');
        $ccField->setHiddenField('cad_cc_id', $this->getParameterForModal('cad_cc_id', $showModal));
        $ccField->setTableColumns([
            'cc_group_name' => Trans::getFinanceWord('groupName'),
            'cc_name' => Trans::getFinanceWord('description'),
            'cc_type_name' => Trans::getFinanceWord('type'),
        ]);
        $ccField->setFilters([
            'cc_group_name' => Trans::getFinanceWord('groupName'),
            'cc_name' => Trans::getFinanceWord('description'),
        ]);
        $ccField->setAutoCompleteFields([
            'cad_description' => 'cc_name',
            'cad_type' => 'cc_type',
        ]);
        $ccField->setLabelCode('cc_code');
        $ccField->setValueCode('cc_id');
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        if ($this->isJoExist() === false) {
            $ccField->addParameter('cc_service', 'N');
        } else {
            $ccField->addParameter('ccg_srv_id', $this->getIntParameter('ca_srv_id'));
        }
        $ccField->setParentModal($modal->getModalId());
        $this->View->addModal($ccField->getModal());

        $uomField = $this->Field->getSingleSelect('unit', 'cad_uom_code', $this->getParameterForModal('cad_uom_code', $showModal));
        $uomField->setHiddenField('cad_uom_id', $this->getParameterForModal('cad_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'cad_tax_name', $this->getParameterForModal('cad_tax_name', $showModal));
        $taxField->setHiddenField('cad_tax_id', $this->getParameterForModal('cad_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);
        $taxField->setAutoCompleteFields([
            'cad_tax_percent' => 'tax_percent'
        ]);

        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('cad_description', $this->getParameterForModal('cad_description', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('cad_quantity', $this->getParameterForModal('cad_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('cad_rate', $this->getParameterForModal('cad_rate', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        if ($this->isReceived() === true) {
            if ($this->isCardExist() === true) {
                $fieldSet->addField(Trans::getFinanceWord('paidWithECard'), $this->Field->getYesNo('cad_ea_payment', $this->getParameterForModal('cad_ea_payment', $showModal)), true);
            }
            $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('cad_file', ''), true);
        }
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('cad_id', $this->getParameterForModal('cad_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_doc_id', $this->getParameterForModal('cad_doc_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jop_id', $this->getParameterForModal('cad_jop_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jos_id', $this->getParameterForModal('cad_jos_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_type', $this->getParameterForModal('cad_type', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_tax_percent', $this->getParameterForModal('cad_tax_percent', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get detail modal.
     *
     * @return Modal
     */
    protected function getDetailDeleteModal(): Modal
    {
        $modal = new Modal('CaCadDelMdl', Trans::getFinanceWord('deleteItems'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('cad_cost_code_del', $this->getParameterForModal('cad_cost_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('uom'), $this->Field->getText('cad_uom_code_del', $this->getParameterForModal('cad_uom_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('cad_description_del', $this->getParameterForModal('cad_description_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('cad_quantity_del', $this->getParameterForModal('cad_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('cad_rate_del', $this->getParameterForModal('cad_rate_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('tax'), $this->Field->getText('cad_tax_name_del', $this->getParameterForModal('cad_tax_name_del', $showModal)));
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('cad_id_del', $this->getParameterForModal('cad_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_doc_id_del', $this->getParameterForModal('cad_doc_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jop_id_del', $this->getParameterForModal('cad_jop_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jos_id_del', $this->getParameterForModal('cad_jos_id_del', $showModal)));

        $text = Trans::getWord('deleteConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getCompleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('CaCplMdl', Trans::getFinanceWord('completeConfirmation'));
        if (empty($this->DetailData) === true) {
            $p = new Paragraph(Trans::getMessageWord('cashPaymentNoDetailFound'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $modal->setFormSubmit($this->getMainFormId(), 'doComplete');
            if ($this->getFormAction() === 'doComplete' && $this->isValidPostValues() === false) {
                $modal->setShowOnLoad();
            }
            $fieldSet = new FieldSet($this->Validation);
            $fieldSet->setGridDimension(6, 6);

            $amount = $this->Field->getText('ca_cpl_amount', $this->Number->doFormatFloat($this->Amount));
            $amount->setReadOnly();

            $fieldSet->addField(Trans::getFinanceWord('cashPayment'), $amount, true);
            $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('ca_cpl_file', ''), true);
            $modal->addFieldSet($fieldSet);
        }

        return $modal;
    }

    /**
     * Function to set hidden fields.
     *
     * @param array $account To store the bank account data.
     * @param array $jobOrder To store the bank account data.
     * @return void
     */
    private function setHiddenFields(array $account = [], array $jobOrder = []): void
    {
        if (empty($account) === false) {
            $this->setParameter('ca_ba_id', $account['ba_id']);
            $this->setParameter('ca_ba_code', $account['ba_code']);
            $this->setParameter('ca_ba_description', $account['ba_description']);
            $this->setParameter('ca_ba_us_id', $account['ba_us_id']);
            $this->setParameter('ca_ba_user', $account['ba_user']);
            $this->setParameter('ca_ba_balance', $account['ba_balance']);
            $this->setParameter('ca_cur_id', $account['ba_cur_id']);
            $this->setParameter('ca_currency', $account['ba_currency']);
            $this->setParameter('ca_ba_limit', $account['ba_limit']);
        }
        if (empty($jobOrder) === false) {
            $this->setParameter('ca_jo_id', $jobOrder['jo_id']);
            $this->setParameter('ca_jo_number', $jobOrder['jo_number']);
            $this->setParameter('ca_reference', $jobOrder['jo_number']);
            $this->setParameter('ca_srv_id', $jobOrder['jo_srv_id']);
            $this->setParameter('ca_srv_code', $jobOrder['jo_srv_code']);
            $this->setParameter('ca_srv_name', $jobOrder['jo_service']);
            $this->setParameter('ca_srt_id', $jobOrder['jo_srt_id']);
            $this->setParameter('ca_srt_route', $jobOrder['jo_srt_route']);
            $this->setParameter('ca_srt_name', $jobOrder['jo_service_term']);
        }
        if ($this->isUpdate() === true) {
            $this->doCalculateAmount();
            $dctCad = [];
            if ($this->isJoExist() === true && $this->isReceived() === false) {
                $dct = DocumentTypeDao::getByCode('ca', 'cpreceive');
            } elseif ($this->isJoExist() === true && $this->isReceived() === true) {
                $dct = DocumentTypeDao::getByCode('ca', 'cpsettlement');
                $dctCad = DocumentTypeDao::getByCode('joborder', 'purchasereceipt');
            } else {
                $dct = DocumentTypeDao::getByCode('ca', 'cpreceipt');
            }
            if (empty($dct) === false) {
                $this->setParameter('ca_dct_id', $dct['dct_id']);
                $this->setParameter('ca_dct_description', $dct['dct_description']);
            }
            if (empty($dctCad) === false) {
                $this->setParameter('cad_dct_id', $dctCad['dct_id']);
            }
        }

        # Set Hidden Data
        $hd = '';
        # BAnk Account
        $hd .= $this->Field->getHidden('ca_ba_id', $this->getIntParameter('ca_ba_id'));
        $hd .= $this->Field->getHidden('ca_ba_us_id', $this->getIntParameter('ca_ba_us_id'));
        $hd .= $this->Field->getHidden('ca_ba_limit', $this->getFloatParameter('ca_ba_limit'));
        $hd .= $this->Field->getHidden('ca_cur_id', $this->getIntParameter('ca_cur_id'));
        $hd .= $this->Field->getHidden('ca_currency', $this->getStringParameter('ca_currency'));
        # Job Order
        $hd .= $this->Field->getHidden('ca_jo_id', $this->getIntParameter('ca_jo_id'));
        $hd .= $this->Field->getHidden('ca_srv_id', $this->getIntParameter('ca_srv_id'));
        $hd .= $this->Field->getHidden('ca_srv_code', $this->getStringParameter('ca_srv_code'));
        $hd .= $this->Field->getHidden('ca_srt_id', $this->getIntParameter('ca_srt_id'));
        $hd .= $this->Field->getHidden('ca_srt_route', $this->getStringParameter('ca_srt_route'));

        # Amount
        $hd .= $this->Field->getHidden('ca_number', $this->getStringParameter('ca_number'));

        # Top Up
        $hd .= $this->Field->getHidden('ca_bt_id', $this->getIntParameter('ca_bt_id'));

        # Document
        $hd .= $this->Field->getHidden('ca_dct_id', $this->getIntParameter('ca_dct_id'));
        $hd .= $this->Field->getHidden('ca_dct_description', $this->getStringParameter('ca_dct_description'));
        $hd .= $this->Field->getHidden('cad_dct_id', $this->getIntParameter('cad_dct_id'));

        if ($this->isReceived() === true) {
            $hd .= $this->Field->getHidden('ca_ea_id', $this->getIntParameter('ca_ea_id'));
            $hd .= $this->Field->getHidden('ca_amount', $this->getFloatParameter('ca_amount'));
            $hd .= $this->Field->getHidden('ca_reserve_amount', $this->getFloatParameter('ca_reserve_amount'));
            $hd .= $this->Field->getHidden('ca_receive_on', $this->getStringParameter('ca_receive_on'));
        }

        $this->View->addContent('CaHdFields', $hd);
    }

    /**
     * Function to do prepare detail data.
     *
     * @param Modal $modal To Store object modal
     * @return void
     */
    private function doPrepareDetailData(Modal $modal): void
    {
        $data = CashAdvanceDetailDao::getByCaId($this->getDetailReferenceValue(), $this->isJoExist());
        # delete modal
        $modalDelete = $this->getDetailDeleteModal();

        # Upload Receipt Modal
        $uploadReceiptMdl = $this->getCadReceiptModal();
        # Delete Receipt Modal
        $deleteReceiptMdl = $this->getCadReceiptDeleteModal();
        $allowUpdate = false;
        if ($this->isAllowUpdate() === true) {
            $allowUpdate = true;
            $this->View->addModal($modalDelete);
            $this->View->addModal($uploadReceiptMdl);
            $this->View->addModal($deleteReceiptMdl);
        }


        $totalAmount = 0.0;
        $totalCard = 0.0;
        foreach ($data as $row) {
            if ($row['cad_type'] === 'P') {
                $row['cad_type'] = new LabelPrimary(Trans::getFinanceWord('cogs'));
            } else {
                $row['cad_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            # Update action
            $btnUpdate = new ModalButton('btnCadUp' . $row['cad_id'], '', $modal->getModalId());
            $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
            $btnUpdate->setEnableCallBack('cad', 'getById');
            $btnUpdate->addParameter('cad_id', $row['cad_id']);
            $btnUpdate->addParameter('ca_jo_id', $this->getJoId());
            # Delete Action
            $btnDelete = new ModalButton('btnCadDel' . $row['cad_id'], '', $modalDelete->getModalId());
            $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
            $btnDelete->setEnableCallBack('cad', 'getByIdForDelete');
            $btnDelete->addParameter('cad_id', $row['cad_id']);
            $btnDelete->addParameter('ca_jo_id', $this->getJoId());
            $row['cad_action'] = $btnUpdate . ' ' . $btnDelete;
            if ($allowUpdate === true && empty($row['cad_doc_id']) === true) {
                $btnUpload = new ModalButton('btnCadDocUp' . $row['cad_id'], '', $uploadReceiptMdl->getModalId());
                $btnUpload->setIcon(Icon::Upload)->btnWarning()->viewIconOnly();
                $btnUpload->setEnableCallBack('cad', 'getByIdForUploadReceipt');
                $btnUpload->addParameter('cad_id', $row['cad_id']);
                $btnUpload->addParameter('ca_jo_id', $this->getJoId());
                $row['cad_receipt'] = $btnUpload;
            }
            if (empty($row['cad_doc_id']) === false) {
                $btnDown = new Button('btnCadDocDown' . $row['cad_id'], '');
                $btnDown->setIcon(Icon::Download)->btnPrimary()->viewIconOnly();
                $btnDown->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['cad_doc_id']) . "')");
                if ($allowUpdate === true) {
                    $btnDelete = new ModalButton('btnCadDocDel' . $row['cad_id'], '', $deleteReceiptMdl->getModalId());
                    $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                    $btnDelete->setEnableCallBack('cad', 'getByIdForDeleteReceipt');
                    $btnDelete->addParameter('cad_id', $row['cad_id']);
                    $btnDelete->addParameter('ca_jo_id', $this->getJoId());
                    $row['cad_receipt'] = $btnDown . ' ' . $btnDelete;
                } else {
                    $row['cad_receipt'] = $btnDown;
                }

            }
            $row['cad_quantity'] = $this->Number->doFormatFloat($row['cad_quantity']) . ' ' . $row['cad_uom_code'];
            $row['cad_rate'] = $row['cad_currency'] . ' ' . $this->Number->doFormatFloat($row['cad_rate']);
            if ($row['cad_ea_payment'] === 'Y') {
                $totalCard += (float)$row['cad_total'];
            } else {
                $totalAmount += (float)$row['cad_total'];
            }
            $this->DetailData[] = $row;
        }
        $this->CardAmount = $totalCard;
        $this->Amount = $totalAmount;
        $this->SettlementAmount = $totalAmount;
    }

    /**
     * Function to do calculate amount.
     * @return void
     */
    private function doCalculateAmount(): void
    {
        if ($this->isSettle() === true) {
            $this->SettlementAmount = $this->getFloatParameter('ca_actual_amount', 0.0);
            $this->CardAmount = $this->getFloatParameter('ca_ea_amount', 0.0);
            $this->Amount = $this->getFloatParameter('ca_amount', 0.0) + $this->getFloatParameter('ca_reserve_amount', 0.0);
            $this->ReturnAmount = $this->getFloatParameter('ca_return_amount', 0.0);
        } elseif ($this->isReceived() === true) {
            $this->Amount = $this->getFloatParameter('ca_amount', 0.0) + $this->getFloatParameter('ca_reserve_amount', 0.0);
            $this->ReturnAmount = $this->Amount - $this->SettlementAmount;
        } else {
            $this->SettlementAmount = 0.0;
            $this->CardAmount = 0.0;
            $this->ReturnAmount = 0.0;
        }
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isJoExist(): bool
    {
        return $this->isValidParameter('ca_jo_id');
    }


    /**
     * Function to check is job order exist or not
     *
     * @return ?int
     */
    private function getJoId(): ?int
    {
        return $this->getIntParameter('ca_jo_id');
    }

    /**
     * Function to check is electronic card exist or not
     *
     * @return bool
     */
    private function isCardExist(): bool
    {
        return $this->isValidParameter('ca_ea_id');
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isReceived(): bool
    {
        return $this->isValidParameter('ca_receive_on');
    }

    /**
     * Function to check is receive rejected
     *
     * @return bool
     */
    private function isReceiveRequested(): bool
    {
        return $this->isValidParameter('ca_crc_id') && $this->isValidParameter('ca_crc_reject_on') === false;
    }

    /**
     * Function to check is receive rejected
     *
     * @return bool
     */
    private function isReceiveRejected(): bool
    {
        return $this->isValidParameter('ca_crc_id') && $this->isValidParameter('ca_crc_reject_on');
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isSettle(): bool
    {
        return $this->isValidParameter('ca_settlement_on');
    }
//
//    /**
//     * Function to check is job order exist or not
//     *
//     * @return bool
//     */
//    private function isCompleted(): bool
//    {
//        return $this->isSettle() && ($this->isRequireTopUpBalance() === false || ($this->isTopUpExist() && $this->isTopUpApproved()));
//    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isSettlementRequested(): bool
    {
        return $this->isValidParameter('ca_crt_id') && $this->isValidParameter('ca_crt_reject_on') === false;
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isSettlementRejected(): bool
    {
        return $this->isValidParameter('ca_crt_id') && $this->isValidParameter('ca_crt_reject_on');
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('ca_deleted_on');
    }

    /**
     * Function to check is job order exist or not
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->getIntParameter('ca_ba_us_id') === $this->User->getId() && $this->isDeleted() === false;
    }

    /**
     * Function to check is allow delete
     *
     * @return bool
     */
    private function isAllowDelete(): bool
    {
        return $this->isAllowUpdate() && $this->isReceived() === false;
    }


    /**
     * Function to check is balance user enough to pay cash.
     *
     * @return bool
     */
    private function isRequireTopUpBalance(): bool
    {
        if ($this->getFloatParameter('ca_ba_limit', 0.0) > 0.0) {
            return false;
        }
        $balance = $this->getFloatParameter('ca_ba_balance', 0.0);
        $reserve = $this->getFloatParameter('ca_reserve_amount', 0.0);
        if ($this->isReceived() === false) {
            return $balance !== ($this->Amount + $reserve);
        }
        if ($this->isSettle() === false) {
            return $this->ReturnAmount < 0;
        }
        return false;
    }

    /**
     * Function to check is there is a top up request.
     *
     * @return bool
     */
    private function isTopUpExist(): bool
    {
        return $this->isValidParameter('ca_bt_id');
    }

    /**
     * Function to check is there is a top up request.
     *
     * @return bool
     */
    private function isTopUpApproved(): bool
    {
        return $this->isTopUpExist() && $this->isValidParameter('ca_bt_approve_on');
    }

    /**
     * Function to check is there is a top up request.
     *
     * @return bool
     */
    private function isTopUpRejected(): bool
    {
        return $this->isTopUpExist() && $this->isValidParameter('ca_bta_reject_on');
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getConfirmationReceiveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('CaRcMdl', Trans::getFinanceWord('receiveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateReceive');
        if ($this->getFormAction() === 'doUpdateReceive' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        if ($this->isValidParameter('ca_receive_date') === false) {
            $this->setParameter('ca_receive_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('ca_receive_time') === false) {
            $this->setParameter('ca_receive_time', date('H:i'));
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('ca_receive_date', $this->getParameterForModal('ca_receive_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('ca_receive_time', $this->getParameterForModal('ca_receive_time', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('ca_receive_file', ''), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getConfirmationTopUpModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('CaRcMdl', Trans::getFinanceWord('requestTopUp'));
        $modal->setFormSubmit($this->getMainFormId(), 'doRequestTopUp');
        $number = new NumberFormatter($this->User);
        $balance = $this->getFloatParameter('ca_ba_balance', 0.0);
        if ($this->isValidParameter('ca_receive_on') === false) {
            $totalAmount = $this->Amount + $this->getFloatParameter('ca_reserve_amount', 0.0);
            $text = StringFormatter::generateCustomTableView([
                [
                    'label' => Trans::getFinanceWord('accountBalance'),
                    'value' => $number->doFormatFloat($balance),
                ],
                [
                    'label' => Trans::getFinanceWord('cashPayment'),
                    'value' => $number->doFormatFloat($totalAmount),
                ],
                [
                    'label' => Trans::getFinanceWord('requestAmount'),
                    'value' => $number->doFormatFloat($totalAmount - $balance),
                ],
            ]);
        } else {
            $text = StringFormatter::generateCustomTableView([
                [
                    'label' => Trans::getFinanceWord('cashPayment'),
                    'value' => $number->doFormatFloat($this->Amount),
                ],
                [
                    'label' => Trans::getFinanceWord('actualAmount'),
                    'value' => $number->doFormatFloat($this->SettlementAmount),
                ],
                [
                    'label' => Trans::getFinanceWord('repayment'),
                    'value' => $number->doFormatFloat(abs($this->ReturnAmount)),
                ],
                [
                    'label' => Trans::getFinanceWord('accountBalance'),
                    'value' => $number->doFormatFloat($balance),
                ],
                [
                    'label' => Trans::getFinanceWord('requestAmount'),
                    'value' => $number->doFormatFloat(abs($this->ReturnAmount - $balance)),
                ],
            ]);
        }
        $modal->addText($text);
        $modal->setBtnOkName(Trans::getFinanceWord('request'));

        return $modal;
    }

    /**
     * Function to get general view portlet.
     *
     * @return Portlet
     */
    private function getGeneralViewPortlet(): Portlet
    {
        $receiverLabel = Trans::getWord('pic');
        $eAccount = '';
        if ($this->isJoExist() === true) {
            $receiverLabel = Trans::getFinanceWord('receiver');
            $eAccount = $this->getStringParameter('ca_ea_code') . ' - ' . $this->getStringParameter('ca_ea_description');
        }
        $data = [
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->getStringParameter('ca_ba_code') . ' - ' . $this->getStringParameter('ca_ba_description'),
            ],
            [
                'label' => Trans::getFinanceWord('eCardAccount'),
                'value' => $eAccount,
            ],
            [
                'label' => Trans::getFinanceWord('date'),
                'value' => $this->DtParser->formatDate($this->getStringParameter('ca_date')),
            ],
            [
                'label' => $receiverLabel,
                'value' => $this->getStringParameter('ca_cp_name'),
            ],
        ];
        if ($this->isJoExist() === true) {
            if ($this->isReceived() === true) {
                $data[] = [
                    'label' => Trans::getFinanceWord('receiveOn'),
                    'value' => $this->DtParser->formatDateTime($this->getStringParameter('ca_receive_on')),
                ];
            }
            if ($this->isSettle() === true) {
                $data[] = [
                    'label' => Trans::getFinanceWord('settlementOn'),
                    'value' => $this->DtParser->formatDateTime($this->getStringParameter('ca_return_on')),
                ];
            }
        }
        $data[] = [
            'label' => Trans::getFinanceWord('notes'),
            'value' => $this->getStringParameter('ca_notes'),
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('CaViewPtl', Trans::getFinanceWord('cashPayment'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get job view portlet.
     *
     *
     * @return Portlet
     */
    private function getJobViewPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('jo'),
                'value' => $this->getStringParameter('ca_jo_number'),
            ],
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $this->getStringParameter('ca_srv_name') . ' - ' . $this->getStringParameter('ca_srt_name'),
            ],
        ];
        if ($this->getStringParameter('ca_srv_code') === 'delivery') {
            $data = array_merge($data, $this->getDeliveryData());
        } else if ($this->getStringParameter('ca_srv_code') === 'inklaring') {
            $data = array_merge($data, $this->getInklaringData());
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('CaJoPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get job view portlet.
     *
     * @return array
     */
    private function getDeliveryData(): array
    {
        $data = [];
        $job = JobDeliveryDao::getByJobIdAndSystem($this->getIntParameter('ca_jo_id'), $this->User->getSsId());
        if (empty($job) === false) {
            $data = [
                [
                    'label' => Trans::getTruckingWord('transportModule'),
                    'value' => $job['jdl_transport_module'],
                ],
                [
                    'label' => Trans::getTruckingWord('transportType'),
                    'value' => $job['jdl_equipment_group'],
                ],
            ];
            if ($job['jdl_tm_code'] === 'road') {
                $data[] = [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $job['jdl_equipment_plate'],
                ];
            } else {
                $data[] = [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $job['jdl_equipment'] . ' ' . $job['jdl_transport_number'],
                ];
            }
        }
        return $data;
    }

    /**
     * Function to get job view portlet.
     *
     * @return array
     */
    private function getInklaringData(): array
    {
        $data = [];
        $job = JobInklaringDao::getByReferenceAndSystemSetting($this->getIntParameter('ca_jo_id'), $this->User->getSsId());
        if (empty($job) === false) {
            $so = SalesOrderDao::getByReferenceAndSystem($job['jik_so_id'], $this->User->getSsId());
            $data = [
                [
                    'label' => Trans::getTruckingWord('transportModule'),
                    'value' => $so['so_transport_module'],
                ],
                [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $so['so_transport_name'] . ' - ' . $so['so_transport_number'],
                ],
                [
                    'label' => Trans::getWord('documentType'),
                    'value' => $so['so_document_type'],
                ],
                [
                    'label' => Trans::getWord('pol'),
                    'value' => $so['so_pol'] . ' - ' . $so['so_pol_country'],
                ],
                [
                    'label' => Trans::getWord('pod'),
                    'value' => $so['so_pod'] . ' - ' . $so['so_pod_country'],
                ],
            ];
        }
        return $data;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    private function getCadReceiptModal(): Modal
    {
        $modal = new Modal('CaCadDocMdl', Trans::getFinanceWord('uploadReceipt'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadReceipt');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadReceipt' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $descField = $this->Field->getText('cad_description_doc', $this->getParameterForModal('cad_description_doc', $showModal));
        $descField->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('description'), $descField);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('cad_file_doc', ''), true);
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('cad_id_doc', $this->getParameterForModal('cad_id_doc', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jop_id_doc', $this->getParameterForModal('cad_jop_id_doc', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    private function getCadReceiptDeleteModal(): Modal
    {
        $modal = new Modal('JoPuRcDelMdl', Trans::getFinanceWord('deleteReceipt'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteReceipt');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteReceipt' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $text = Trans::getWord('deleteConfirmation', 'message');
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $descField = $this->Field->getText('cad_description_doc_del', $this->getParameterForModal('cad_description_doc_del', $showModal));
        $descField->setReadOnly();
        $fieldSet->addField(Trans::getFinanceWord('description'), $descField);
        $fieldSet->addHiddenField($this->Field->getHidden('cad_id_doc_del', $this->getParameterForModal('cad_id_doc_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_jop_id_doc_del', $this->getParameterForModal('cad_jop_id_doc_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('cad_doc_id_doc_del', $this->getParameterForModal('cad_doc_id_doc_del', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getSettlementModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('CaRtMdl', Trans::getFinanceWord('settlementConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doConfirmSettlement');
        if ($this->getFormAction() === 'doConfirmSettlement' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        if ($this->isValidParameter('ca_settle_date') === false) {
            $this->setParameter('ca_settle_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('ca_settle_time') === false) {
            $this->setParameter('ca_settle_time', date('H:i'));
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $number = new NumberFormatter($this->User);
        $settlement = $this->Field->getText('ca_sett_amount', $number->doFormatFloat($this->SettlementAmount + $this->CardAmount));
        $settlement->setReadOnly();
        $payCash = $this->Field->getText('ca_card_amount', $number->doFormatFloat($this->SettlementAmount));
        $payCash->setReadOnly();
        $caPaid = $this->Field->getText('ca_paid_amount', $number->doFormatFloat($this->Amount));
        $caPaid->setReadOnly();
        $payCard = $this->Field->getText('ca_cash_amount', $number->doFormatFloat($this->CardAmount));
        $payCard->setReadOnly();
        $return = $this->Field->getText('ca_ret_amount', $number->doFormatFloat(abs($this->ReturnAmount)));
        $return->setReadOnly();

        $fieldSet->addField(Trans::getFinanceWord('cashPayment'), $caPaid, true);
        $fieldSet->addField(Trans::getFinanceWord('actualAmount'), $settlement, true);
        if ($this->isCardExist() === true) {
            $fieldSet->addField(Trans::getFinanceWord('paidWithCash'), $payCash, true);
            $fieldSet->addField(Trans::getFinanceWord('paidWithECard'), $payCard, true);
        }
        if ($this->ReturnAmount >= 0) {
            $fieldSet->addField(Trans::getFinanceWord('cashRefund'), $return, true);
        } else {
            $fieldSet->addField(Trans::getFinanceWord('repayment'), $return, true);
        }
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('ca_settle_file', ''), true);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('ca_settle_date', $this->getParameterForModal('ca_settle_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('ca_settle_time', $this->getParameterForModal('ca_settle_time', true)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
