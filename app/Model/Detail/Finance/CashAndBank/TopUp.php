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
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Finance\CashAndBank\BankAccountBalanceDao;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;
use App\Model\Dao\Finance\CashAndBank\BankTransactionApprovalDao;
use App\Model\Dao\Finance\CashAndBank\BankTransactionDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;

/**
 * Class to handle the creation of detail TopUp page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class TopUp extends AbstractFormModel
{
    /**
     * Property to store total on going cash.
     *
     * @var float $TotalOnGoingCash .
     */
    private $TotalOnGoingCash = 0.0;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'topUp', 'bt_id');
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
        $number = $sn->loadNumber('BT', $this->User->Relation->getOfficeId());
        $baPayer = null;
        $baReceiver = null;
        if ($this->getStringParameter('bt_type') === 'request') {
            $baReceiver = $this->getIntParameter('bt_ba_id');
        } else {
            $baPayer = $this->getIntParameter('bt_ba_id');
        }
        $colVal = [
            'bt_ss_id' => $this->User->getSsId(),
            'bt_number' => $number,
            'bt_type' => $this->getStringParameter('bt_type'),
            'bt_payer_ba_id' => $baPayer,
            'bt_receiver_ba_id' => $baReceiver,
            'bt_amount' => $this->getFloatParameter('bt_amount'),
            'bt_currency_exchange' => 1,
            'bt_notes' => $this->getStringParameter('bt_notes'),
        ];
        $btDao = new BankTransactionDao();
        $btDao->doInsertTransaction($colVal);
        return $btDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $baPayer = null;
            $baReceiver = null;
            if ($this->getStringParameter('bt_type') === 'request') {
                $baReceiver = $this->getIntParameter('bt_ba_id');
            } else {
                $baPayer = $this->getIntParameter('bt_ba_id');
            }
            $colVal = [
                'bt_type' => $this->getStringParameter('bt_type'),
                'bt_payer_ba_id' => $baPayer,
                'bt_receiver_ba_id' => $baReceiver,
                'bt_amount' => $this->getFloatParameter('bt_amount'),
                'bt_notes' => $this->getStringParameter('bt_notes'),
            ];
            $btDao = new BankTransactionDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $btDao = new BankTransactionDao();
            $btDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        } elseif ($this->getFormAction() === 'doRequest') {
            $btaColVal = [
                'bta_bt_id' => $this->getDetailReferenceValue()
            ];
            $btaDao = new BankTransactionApprovalDao();
            $btaDao->doInsertTransaction($btaColVal);

            # Update bank transaction
            $btDao = new BankTransactionDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'bt_bta_id' => $btaDao->getLastInsertId()
            ]);
        } elseif ($this->getFormAction() === 'doReject') {
            $btaDao = new BankTransactionApprovalDao();
            $btaDao->doDeleteTransaction($this->getIntParameter('bt_bta_id'), $this->getStringParameter('bta_deleted_reason'));
        } elseif ($this->getFormAction() === 'doApprove') {
            # Update bank transaction
            $btDao = new BankTransactionDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'bt_approve_by' => $this->User->getId(),
                'bt_approve_on' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($this->getFormAction() === 'doPayment') {
            $amount = $this->getFloatParameter('bt_amount');
            $payerBalance = $amount * -1;
            $receiverBalance = $amount;
            if ($this->getStringParameter('bt_type') === 'request') {
                $baReceiver = $this->getIntParameter('bt_ba_id');
                $baPayer = $this->getIntParameter('bt_pay_ba_id');
            } else {
                $baPayer = $this->getIntParameter('bt_ba_id');
                $baReceiver = $this->getIntParameter('bt_pay_ba_id');
            }
            # Update balance payer
            $babDao = new BankAccountBalanceDao();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $baPayer,
                'bab_amount' => $payerBalance,
            ]);
            # Update balance receiver
            $idBabPayer = $babDao->getLastInsertId();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $baReceiver,
                'bab_amount' => $receiverBalance,
            ]);
            $idBabReceiver = $babDao->getLastInsertId();
            # upload receipt
            $docId = null;
            $file = $this->getFileParameter('bt_receipt');
            if ($file !== null) {
                $colValDoc = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('bt_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('paymentReceipt'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colValDoc);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docId);
                $upload->upload($file);
            }
            $colVal = [
                'bt_payer_ba_id' => $baPayer,
                'bt_payer_bab_id' => $idBabPayer,
                'bt_receiver_ba_id' => $baReceiver,
                'bt_receiver_bab_id' => $idBabReceiver,
                'bt_paid_by' => $this->User->getId(),
                'bt_paid_on' => date('Y-m-d H:i:s'),
                'bt_paid_ref' => $this->getStringParameter('bt_paid_ref'),
                'bt_doc_id' => $docId,
                'bt_receive_by' => $this->User->getId(),
                'bt_receive_on' => date('Y-m-d H:i:s'),
            ];
            # Update bank transaction
            $btDao = new BankTransactionDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doReturnCash') {
            $amount = $this->getFloatParameter('bt_amount');
            $payerBalance = $amount * -1;
            $receiverBalance = $amount;
            $baPayer = $this->getIntParameter('bt_ba_id');
            $baReceiver = $this->getIntParameter('bt_pay_ba_id');
            # Update balance payer
            $babDao = new BankAccountBalanceDao();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $baPayer,
                'bab_amount' => $payerBalance,
            ]);
            $idBabPayer = $babDao->getLastInsertId();
            # Update balance receiver
            $babDao->doInsertTransaction([
                'bab_ba_id' => $baReceiver,
                'bab_amount' => $receiverBalance,
            ]);
            $idBabReceiver = $babDao->getLastInsertId();
            # Insert request approval
            $btaDao = new BankTransactionApprovalDao();
            $btaDao->doInsertTransaction([
                'bta_bt_id' => $this->getDetailReferenceValue()
            ]);
            # upload receipt
            $docId = null;
            $file = $this->getFileParameter('bt_receipt');
            if ($file !== null) {
                $colValDoc = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('bt_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('paymentReceipt'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colValDoc);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docId);
                $upload->upload($file);
            }
            $colVal = [
                'bt_bta_id' => $btaDao->getLastInsertId(),
                'bt_approve_by' => $this->User->getId(),
                'bt_approve_on' => date('Y-m-d H:i:s'),
                'bt_payer_ba_id' => $baPayer,
                'bt_payer_bab_id' => $idBabPayer,
                'bt_receiver_ba_id' => $baReceiver,
                'bt_receiver_bab_id' => $idBabReceiver,
                'bt_paid_by' => $this->User->getId(),
                'bt_paid_on' => date('Y-m-d H:i:s'),
                'bt_paid_ref' => $this->getStringParameter('bt_paid_ref'),
                'bt_doc_id' => $docId,
                'bt_receive_by' => $this->User->getId(),
                'bt_receive_on' => date('Y-m-d H:i:s'),
            ];
            # Update bank transaction
            $btDao = new BankTransactionDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_id', $this->getDetailReferenceValue());
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_ss_id', $this->User->getSsId());
        $wheres[] = "(bt.bt_type IN ('request', 'return'))";
        $data = BankTransactionDao::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
                Message::throwMessage(Trans::getMessageWord('noDataFound'));
            }
            $this->setHiddenField($account);
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        } else {
            # Check if Deleted
            if ($this->isDeleted() === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('bt_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('bt_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('bt_deleted_reason')
                ]));
            }
            # Check if rejected
            if ($this->isRejected() === true) {
                $this->View->addErrorMessage(Trans::getWord('rejectRequest', 'message', '', [
                    'user' => $this->getStringParameter('bt_reject_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('bt_reject_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('bt_reject_reason')
                ]));
            }

            # Set Parameter
            $this->overridePageTitle();
            if ($this->getStringParameter('bt_type') === 'request') {
                $account = BankAccountDao::getByReferenceAndSystem($this->getIntParameter('bt_receiver_ba_id'), $this->User->getSsId());
                $this->setHiddenField($account);
                $this->TotalOnGoingCash = CashAdvanceDao::getTotalUnSettlementCashByBankAccount($account['ba_id']);
                $this->Tab->addContent('general', $this->getWidget());
            } else {
                $account = BankAccountDao::getByReferenceAndSystem($this->getIntParameter('bt_payer_ba_id'), $this->User->getSsId());
                $this->setHiddenField($account);
            }
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            if ($this->isApproved() === true) {
                $this->Tab->addPortlet('general', $this->getDetailPortlet());
            }
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
            $this->Validation->checkRequire('bt_ba_id');
            $this->Validation->checkRequire('bt_type');
            $this->Validation->checkRequire('bt_amount');
            $this->Validation->checkFloat('bt_amount', 1);
            $this->Validation->checkMaxLength('bt_notes', 256);
        } elseif ($this->getFormAction() === 'doReject') {
            $this->Validation->checkRequire('bt_bta_id');
            $this->Validation->checkRequire('bta_deleted_reason', 2, 256);
        } elseif ($this->getFormAction() === 'doPayment') {
            $this->Validation->checkRequire('bt_ba_id');
            $this->Validation->checkRequire('bt_pay_ba_id');
            $this->Validation->checkRequire('bt_dct_id');
            $this->Validation->checkRequire('bt_receipt');
            $this->Validation->checkFile('bt_receipt');
            $this->Validation->checkRequire('bt_type');
            $this->Validation->checkRequire('bt_amount');
            $this->Validation->checkFloat('bt_amount', 1);
        } elseif ($this->getFormAction() === 'doReturnCash') {
            $this->Validation->checkRequire('bt_ba_id');
            $this->Validation->checkRequire('bt_pay_ba_id');
            $this->Validation->checkRequire('bt_dct_id');
            $this->Validation->checkRequire('bt_receipt');
            $this->Validation->checkFile('bt_receipt');
            $this->Validation->checkRequire('bt_amount');
            $this->Validation->checkFloat('bt_amount', 1);
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
        $baCodeField = $this->Field->getText('bt_ba_code', $this->getStringParameter('bt_ba_code'));
        $baCodeField->setReadOnly();
        $baDescriptionField = $this->Field->getText('bt_ba_description', $this->getStringParameter('bt_ba_description'));
        $baDescriptionField->setReadOnly();
        # Type
        $typeField = $this->Field->getSelect('bt_type', $this->getStringParameter('bt_type'));
        $typeField->addOption(Trans::getFinanceWord('request'), 'request');
        $typeField->addOption(Trans::getFinanceWord('return'), 'return');
        # Amount
        $amountField = $this->Field->getNumber('bt_amount', $this->getFloatParameter('bt_amount'));
        # notes field
        $notesField = $this->Field->getTextArea('bt_notes', $this->getStringParameter('bt_notes'));
        if ($this->isUpdate() === true && $this->isAllowUpdate() === false) {
            $typeField->setReadOnly();
            $amountField->setReadOnly();
            $notesField->setReadOnly();
        }


        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getFinanceWord('accountCode'), $baCodeField);
        $fieldSet->addField(Trans::getFinanceWord('accountDescription'), $baDescriptionField);
        $fieldSet->addField(Trans::getFinanceWord('type'), $typeField, true);
        $fieldSet->addField(Trans::getFinanceWord('amount'), $amountField, true);
        $fieldSet->addField(Trans::getFinanceWord('notes'), $notesField);
        # Create a portlet box.
        $portlet = new Portlet('BaPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $number = new NumberFormatter();
        $results = '';
        # Limit
        $plan = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('ceiling'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark-blue',
            'amount' => $this->getStringParameter('bt_ba_currency') . ' ' . $number->doFormatFloat($this->getFloatParameter('bt_ba_limit')),
            'uom' => '',
            'url' => '',
        ];
        $plan->setData($data);
        $plan->setGridDimension(4);
        $results .= $plan->createView();
        # Balance
        $balance = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('currentBalance'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-success',
            'amount' => $this->getStringParameter('bt_ba_currency') . ' ' . $number->doFormatFloat($this->getFloatParameter('bt_ba_balance')),
            'uom' => '',
            'url' => '',
        ];
        $balance->setData($data);
        $balance->setGridDimension(4);
        $results .= $balance->createView();

        # Cash Advance
        $cashAdvance = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('onGoingCash'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-warning',
            'amount' => $this->getStringParameter('bt_ba_currency') . ' ' . $number->doFormatFloat($this->TotalOnGoingCash),
            'uom' => '',
            'url' => '',
        ];
        $cashAdvance->setData($data);
        $cashAdvance->setGridDimension(4);
        $results .= $cashAdvance->createView();
        return $results;
    }


    /**
     * Function to get the general Field Set.
     *
     * @param array $account To store the bank account.
     * @return void
     */
    private function setHiddenField(array $account): void
    {
        # Set parameter
        $this->setParameter('bt_ba_id', $account['ba_id']);
        $this->setParameter('bt_ba_code', $account['ba_code']);
        $this->setParameter('bt_ba_description', $account['ba_description']);
        $this->setParameter('bt_ba_cur_id', $account['ba_cur_id']);
        $this->setParameter('bt_ba_currency', $account['ba_currency']);
        $this->setParameter('bt_ba_us_id', $account['ba_us_id']);
        $this->setParameter('bt_ba_limit', $account['ba_limit']);
        $this->setParameter('bt_ba_balance', $account['ba_balance']);

        $hidden = '';
        $hidden .= $this->Field->getHidden('bt_ba_id', $this->getIntParameter('bt_ba_id'));
        $hidden .= $this->Field->getHidden('bt_ba_cur_id', $this->getIntParameter('bt_ba_cur_id'));
        $hidden .= $this->Field->getHidden('bt_ba_us_id', $this->getIntParameter('bt_ba_us_id'));
        $hidden .= $this->Field->getHidden('bt_bta_id', $this->getIntParameter('bt_bta_id'));
        $this->View->addContent('BtHdFields', $hidden);
    }


    /**
     * Function to get the time sheet field set
     *
     * @return Portlet
     */
    protected function getTimeSheetPortlet(): Portlet
    {
        $table = new Table('BtTimeTbl');
        $table->setHeaderRow([
            'bt_ts_action' => Trans::getWord('action'),
            'bt_ts_creator' => Trans::getWord('user'),
            'bt_ts_time' => Trans::getWord('time'),
            'bt_ts_remark' => Trans::getWord('remark'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->setColumnType('bt_ts_time', 'datetime');
        # Create a portlet box.
        $portlet = new Portlet('BtTimePtl', Trans::getWord('timeSheet'));
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
        $request = BankTransactionApprovalDao::getByTransactionId($this->getDetailReferenceValue());
        if ($this->isDeleted() === true) {
            $result[] = [
                'bt_ts_action' => Trans::getFinanceWord('deleted'),
                'bt_ts_creator' => $this->getStringParameter('bt_deleted_by'),
                'bt_ts_time' => $this->getStringParameter('bt_deleted_on'),
                'bt_ts_remark' => $this->getStringParameter('bt_deleted_reason'),
            ];
        }
//        if ($this->isReceive() === true) {
//            $result[] = [
//                'bt_ts_action' => Trans::getFinanceWord('paymentReceived'),
//                'bt_ts_creator' => $this->getStringParameter('bt_receive_by'),
//                'bt_ts_time' => $this->getStringParameter('bt_receive_on'),
//                'bt_ts_remark' => '',
//            ];
//        }
        if ($this->isPaid() === true) {
            $result[] = [
                'bt_ts_action' => Trans::getFinanceWord('paid'),
                'bt_ts_creator' => $this->getStringParameter('bt_paid_by'),
                'bt_ts_time' => $this->getStringParameter('bt_paid_on'),
                'bt_ts_remark' => $this->getStringParameter('bt_paid_ref'),
            ];
        }
        if ($this->isApproved() === true) {
            $result[] = [
                'bt_ts_action' => Trans::getFinanceWord('approved'),
                'bt_ts_creator' => $this->getStringParameter('bt_approve_by'),
                'bt_ts_time' => $this->getStringParameter('bt_approve_on'),
                'bt_ts_remark' => '',
            ];
        }
        foreach ($request as $row) {
            if (empty($row['bta_deleted_on']) === false) {
                $result[] = [
                    'bt_ts_action' => Trans::getFinanceWord('rejected'),
                    'bt_ts_creator' => $row['bta_deleted_by'],
                    'bt_ts_time' => $row['bta_deleted_on'],
                    'bt_ts_remark' => $row['bta_deleted_reason'],
                ];
            }
            $result[] = [
                'bt_ts_action' => Trans::getFinanceWord('requested'),
                'bt_ts_creator' => $row['bta_created_by'],
                'bt_ts_time' => $row['bta_created_on'],
                'bt_ts_remark' => '',
            ];
        }
        $result[] = [
            'bt_ts_action' => Trans::getFinanceWord('draft'),
            'bt_ts_creator' => $this->getStringParameter('bt_created_by'),
            'bt_ts_time' => $this->getStringParameter('bt_created_on'),
            'bt_ts_remark' => '',
        ];


        return $result;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $dt = new DateTimeParser();
        $receipt = '';
        if ($this->isValidParameter('bt_doc_id') === true) {
            $receipt = new Button('btnRecBt', Trans::getWord('download'));
            $receipt->setIcon(Icon::Download)->btnPrimary();
            $receipt->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $this->getIntParameter('bt_doc_id')) . "')");

        }
        $data = [
            [
                'label' => Trans::getFinanceWord('requestedOn'),
                'value' => $dt->formatDateTime($this->getStringParameter('bt_request_on')),
            ],
            [
                'label' => Trans::getFinanceWord('approvedOn'),
                'value' => $dt->formatDateTime($this->getStringParameter('bt_approve_on')),
            ],
            [
                'label' => Trans::getFinanceWord('approvedBy'),
                'value' => $this->getStringParameter('bt_approve_by'),
            ],
            [
                'label' => Trans::getFinanceWord('paidOn'),
                'value' => $dt->formatDateTime($this->getStringParameter('bt_paid_on')),
            ],
            [
                'label' => Trans::getFinanceWord('paidBy'),
                'value' => $this->getStringParameter('bt_paid_by'),
            ],
            [
                'label' => Trans::getFinanceWord('paymentRef'),
                'value' => $this->getStringParameter('bt_paid_ref'),
            ],
            [
                'label' => Trans::getFinanceWord('paymentReceipt'),
                'value' => $receipt,
            ],
//            [
//                'label' => Trans::getFinanceWord('receivedOn'),
//                'value' => $dt->formatDateTime($this->getStringParameter('bt_receive_on')),
//            ],
//            [
//                'label' => Trans::getFinanceWord('receivedBy'),
//                'value' => $this->getStringParameter('bt_receive_by'),
//            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);

        # Create a portlet box.
        $portlet = new Portlet('BtDetailPtl', Trans::getFinanceWord('details'));
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
        if ($this->isUpdate() === true) {
            if ($this->isAllowUpdate()) {
                # enable delete button
                $ca = CashAdvanceDao::loadData([
                    SqlHelper::generateNumericCondition('ca.ca_bt_id', $this->getDetailReferenceValue()),
                    SqlHelper::generateNullCondition('ca.ca_deleted_on')
                ]);
                if (empty($ca) === true) {
                    $this->setEnableDeleteButton();
                }
                if ($this->getStringParameter('bt_type') === 'request') {
                    # Create button Request
                    $modal = $this->getRequestModal();
                    $this->View->addModal($modal);
                    $btnReq = new ModalButton('btnReqBt', Trans::getFinanceWord('requestApproval'), $modal->getModalId());
                    $btnReq->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                    $this->View->addButton($btnReq);
                } else {
                    # Create button Complete
                    $dct = DocumentTypeDao::getByCode('BT', 'PaymentReceipt');
                    if (empty($dct) === false) {
                        $this->setParameter('bt_dct_id', $dct['dct_id']);
                    }
                    $modal = $this->getCompleteReturnModal();
                    $this->View->addModal($modal);
                    $btnReq = new ModalButton('btnCplBt', Trans::getFinanceWord('confirmReturn'), $modal->getModalId());
                    $btnReq->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                    $this->View->addButton($btnReq);
                }
            } else {
                # Disable update
                $this->setDisableUpdate();
            }
            if ($this->isRequested() === true && $this->isApproved() === false && $this->PageSetting->checkPageRight('AllowApproveRejectRequest') === true) {
                # Create button Approve
                $approveMdl = $this->getApproveModal();
                $this->View->addModal($approveMdl);
                $btnApp = new ModalButton('btnAppBt', Trans::getFinanceWord('approve'), $approveMdl->getModalId());
                $btnApp->setIcon(Icon::Check)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnApp);
                # Create button Reject
                $rejectMdl = $this->getRejectModal();
                $this->View->addModal($rejectMdl);
                $btnRej = new ModalButton('btnRejBt', Trans::getFinanceWord('reject'), $rejectMdl->getModalId());
                $btnRej->setIcon(Icon::Times)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnRej);

            }
            if ($this->isAllowPaid() === true) {

                $dct = DocumentTypeDao::getByCode('BT', 'PaymentReceipt');
                if (empty($dct) === false) {
                    $this->setParameter('bt_dct_id', $dct['dct_id']);
                }
                # Create button Reject
                $paidMdl = $this->getPaidModal();
                $this->View->addModal($paidMdl);
                $btnPaid = new ModalButton('btnPaidPc', Trans::getFinanceWord('paid'), $paidMdl->getModalId());
                $btnPaid->setIcon(Icon::Money)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnPaid);
            }

        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get request modal
     *
     * @return Modal
     */
    protected function getRequestModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtReqMdl', Trans::getFinanceWord('requestApproval'));
        $balance = $this->getFloatParameter('bt_ba_balance', 0.0);
        $limit = $this->getFloatParameter('bt_ba_limit', 0.0);
        $totalCash = $balance + $this->TotalOnGoingCash + $this->getFloatParameter('bt_amount', 0.0);
        $type = $this->getStringParameter('bt_type');
        if ($type === 'request' && $limit > 0.0 && $totalCash > $limit) {
            $p = new Paragraph(Trans::getMessageWord('topUpExceedCeiling'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getFinanceWord('cashRequestMessage');
            $modal->setFormSubmit($this->getMainFormId(), 'doRequest');
            $modal->setBtnOkName(Trans::getFinanceWord('yesRequest'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        }

        return $modal;
    }

    /**
     * Function to get approve modal
     *
     * @return Modal
     */
    protected function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtAppMdl', Trans::getFinanceWord('approvalConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doApprove');
        $modal->setBtnOkName(Trans::getFinanceWord('yesApprove'));
        $text = Trans::getMessageWord('bankTransactionApproveConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get reject modal
     *
     * @return Modal
     */
    protected function getRejectModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtRejMdl', Trans::getFinanceWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReject');
        $modal->setBtnOkName(Trans::getFinanceWord('yesReject'));
        $showModal = false;
        if ($this->getFormAction() === 'doReject' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('reason'), $this->Field->getTextArea('bta_deleted_reason', $this->getParameterForModal('bta_deleted_reason', $showModal)), true);
        $text = Trans::getMessageWord('bankTransactionRejectConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get paid modal
     *
     * @return Modal
     */
    protected function getPaidModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtPaidMdl', Trans::getFinanceWord('paymentConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doPayment');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $showModal = false;
        if ($this->getFormAction() === 'doPayment' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $baField = $this->Field->getSingleSelect('ba', 'bt_pay_description', $this->getParameterForModal('bt_pay_description', $showModal));
        $baField->setHiddenField('bt_pay_ba_id', $this->getParameterForModal('bt_pay_ba_id', $showModal));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_main', 'Y');
        $baField->addParameter('ba_payable', 'Y');
        $baField->addParameter('ba_active', 'Y');
        $baField->addParameter('ba_cur_id', $this->getParameterForModal('bt_ba_cur_id', true));
        $baField->setEnableNewButton(false);
        $baField->setEnableDetailButton(false);


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $label = Trans::getFinanceWord('returnToAccount');
        if ($this->getStringParameter('bt_type') === 'request') {
            $label = Trans::getFinanceWord('paidFromAccount');
        }
        $fieldSet->addField($label, $baField, true);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('bt_receipt', ''), true);
        $fieldSet->addField(Trans::getFinanceWord('reference'), $this->Field->getTextArea('bt_paid_ref', $this->getParameterForModal('bt_paid_ref', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('bt_dct_id', $this->getIntParameter('bt_dct_id')));

        $text = Trans::getMessageWord('bankTransactionPaymentConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get paid modal
     *
     * @return Modal
     */
    protected function getCompleteReturnModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtCplMdl', Trans::getFinanceWord('returnConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReturnCash');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $showModal = false;
        if ($this->getFormAction() === 'doReturnCash' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $baField = $this->Field->getSingleSelect('ba', 'bt_pay_description', $this->getParameterForModal('bt_pay_description', $showModal));
        $baField->setHiddenField('bt_pay_ba_id', $this->getParameterForModal('bt_pay_ba_id', $showModal));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_main', 'Y');
        $baField->addParameter('ba_payable', 'Y');
        $baField->addParameter('ba_active', 'Y');
        $baField->addParameter('ba_cur_id', $this->getParameterForModal('bt_ba_cur_id', true));
        $baField->setEnableNewButton(false);
        $baField->setEnableDetailButton(false);


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('returnToAccount'), $baField, true);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('bt_receipt', ''), true);
        $fieldSet->addField(Trans::getFinanceWord('reference'), $this->Field->getTextArea('bt_paid_ref', $this->getParameterForModal('bt_paid_ref', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('bt_dct_id', $this->getIntParameter('bt_dct_id')));

        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to check if user has access to paid request.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $btDao = new BankTransactionDao();
        $status = $btDao->generateStatus([
            'is_deleted' => $this->isDeleted(),
            'is_receive' => $this->isReceive(),
            'is_paid' => $this->isPaid(),
            'is_approved' => $this->isApproved(),
            'is_requested' => $this->isValidParameter('bt_bta_id'),
            'is_rejected' => $this->isRejected(),
        ]);
        $this->View->setDescription($this->getStringParameter('bt_number') . ' - ' . $status);
    }

    /**
     * Function to check if user has access to update request.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->isDeleted() === false && $this->isRequested() === false && ($this->getIntParameter('bt_ba_us_id') === $this->User->getId());
    }

    /**
     * Function to check if user has access to paid request.
     *
     * @return bool
     */
    private function isAllowPaid(): bool
    {
        $allow = $this->isApproved() && $this->isPaid() === false;
        if ($this->getStringParameter('bt_type') === 'request') {
            return $allow && $this->PageSetting->checkPageRight('AllowPaidRequest');
        }
        if ($this->getStringParameter('bt_type') === 'return') {
            return $allow && $this->getIntParameter('bt_ba_us_id') === $this->User->getId();
        }
        return false;
    }

    /**
     * Function to check if user has access to update request.
     *
     * @return bool
     */
    private function isRequested(): bool
    {
        return ($this->isValidParameter('bt_bta_id') === true && $this->isValidParameter('bt_reject_on') === false);
    }

    /**
     * Function to check if user has access to update request.
     *
     * @return bool
     */
    private function isRejected(): bool
    {
        return ($this->isValidParameter('bt_bta_id') === true && $this->isValidParameter('bt_reject_on') === true);
    }

    /**
     * Function to check if user has access to update request.
     *
     * @return bool
     */
    private function isApproved(): bool
    {
        return $this->isValidParameter('bt_approve_on');
    }

    /**
     * Function to check if data has paid.
     *
     * @return bool
     */
    private function isPaid(): bool
    {
        return $this->isValidParameter('bt_paid_on');
    }

    /**
     * Function to check if data has receive.
     *
     * @return bool
     */
    private function isReceive(): bool
    {
        return $this->isValidParameter('bt_receive_on');
    }

    /**
     * Function to check if data deleted.
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('bt_deleted_on');
    }

}
