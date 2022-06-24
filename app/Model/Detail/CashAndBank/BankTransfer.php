<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\CashAndBank;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CashAndBank\BankAccountBalanceDao;
use App\Model\Dao\CashAndBank\BankTransferDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail BankTransfer page
 *
 * @package    app
 * @subpackage Model\Detail\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */
class BankTransfer extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'bt', 'bt_id');
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
        $number = $sn->loadNumber('BT');
        $colVal = [
            'bt_ss_id' => $this->User->getSsId(),
            'bt_number' => $number,
            'bt_payer_ba_id' => $this->getStringParameter('bt_payer_ba_id'),
            'bt_receiver_ba_id' => $this->getStringParameter('bt_receiver_ba_id'),
            'bt_date' => $this->getStringParameter('bt_date'),
            'bt_time' => $this->getStringParameter('bt_time'),
            'bt_datetime' => $this->getStringParameter('bt_date') . ' ' . $this->getStringParameter('bt_time'),
            'bt_amount' => $this->getFloatParameter('bt_amount'),
            'bt_exchange_rate' => $this->getFloatParameter('bt_exchange_rate', 1),
            'bt_notes' => $this->getStringParameter('bt_notes'),
        ];
        $btDao = new BankTransferDao();
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
            $colVal = [
                'bt_payer_ba_id' => $this->getStringParameter('bt_payer_ba_id'),
                'bt_receiver_ba_id' => $this->getStringParameter('bt_receiver_ba_id'),
                'bt_date' => $this->getStringParameter('bt_date'),
                'bt_time' => $this->getStringParameter('bt_time'),
                'bt_datetime' => $this->getStringParameter('bt_date') . ' ' . $this->getStringParameter('bt_time'),
                'bt_amount' => $this->getFloatParameter('bt_amount'),
                'bt_exchange_rate' => $this->getFloatParameter('bt_exchange_rate', 1),
                'bt_notes' => $this->getStringParameter('bt_notes'),
            ];
            $btDao = new BankTransferDao();
            $btDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doPaid') {
            $btDao = new BankTransferDao();
            if ($btDao->isPaid($this->getDetailReferenceValue()) === false) {
                $payerAmount = $this->getFloatParameter('bt_amount');
                $exchangeRate = $this->getFloatParameter('bt_exchange_rate', 1);
                $receiverAmount = $payerAmount;
                if ($this->getStringParameter('bt_payer_cur_id') !== $this->getStringParameter('bt_receiver_cur_id')) {
                    $receiverAmount = $payerAmount * $exchangeRate;
                }
                # Insert Bank Balance
                $babDao = new BankAccountBalanceDao();
                # BAB Payer
                $babDao->doInsertTransaction([
                    'bab_ba_id' => $this->getStringParameter('bt_payer_ba_id'),
                    'bab_amount' => $payerAmount * -1,
                ]);
                $payerBabId = $babDao->getLastInsertId();

                # BAB Receiver
                $babDao->doInsertTransaction([
                    'bab_ba_id' => $this->getStringParameter('bt_receiver_ba_id'),
                    'bab_amount' => $receiverAmount,
                ]);
                $receiverBabId = $babDao->getLastInsertId();

                # Update Bank Transfer
                $colVal = [
                    'bt_payer_ba_id' => $this->getStringParameter('bt_payer_ba_id'),
                    'bt_payer_bab_id' => $payerBabId,
                    'bt_receiver_ba_id' => $this->getStringParameter('bt_receiver_ba_id'),
                    'bt_receiver_bab_id' => $receiverBabId,
                    'bt_date' => $this->getStringParameter('bt_date'),
                    'bt_time' => $this->getStringParameter('bt_time'),
                    'bt_datetime' => $this->getStringParameter('bt_date') . ' ' . $this->getStringParameter('bt_time'),
                    'bt_amount' => $payerAmount,
                    'bt_exchange_rate' => $exchangeRate,
                    'bt_notes' => $this->getStringParameter('bt_notes'),
                    'bt_paid_on' => date('Y-m-d H:i:s'),
                    'bt_paid_by' => $this->User->getId(),
                ];
                $btDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            }
        } elseif ($this->getFormAction() === 'doReOpen') {
            $btDao = new BankTransferDao();
            if ($btDao->isPaid($this->getDetailReferenceValue()) === true) {
                $babDao = new BankAccountBalanceDao();
                $babDao->doDeleteTransaction($this->getStringParameter('bt_payer_bab_id'));
                $babDao->doDeleteTransaction($this->getStringParameter('bt_receiver_bab_id'));
                $btDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                    'bt_payer_bab_id' => null,
                    'bt_receiver_bab_id' => null,
                    'bt_paid_on' => null,
                    'bt_paid_by' => null,
                ]);
            }
        } elseif ($this->isDeleteAction() === true) {
            $btDao = new BankTransferDao();
            $btDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return BankTransferDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->setPageDescription();
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $action = $this->getFormAction();
        if ($action === null || $action === 'doPaid') {
            $this->Validation->checkRequire('bt_payer_ba_id');
            $this->Validation->checkRequire('bt_receiver_ba_id');
            $this->Validation->checkDifferent('bt_receiver_ba_id', 'bt_payer_ba_id');
            $this->Validation->checkRequire('bt_payer_cur_id');
            $this->Validation->checkRequire('bt_receiver_cur_id');
            $this->Validation->checkRequire('bt_date');
            $this->Validation->checkDate('bt_date');
            $this->Validation->checkRequire('bt_time');
            $this->Validation->checkTime('bt_time');
            $this->Validation->checkRequire('bt_amount');
            $this->Validation->checkFloat('bt_amount', 1);
            if ($this->isValidParameter('bt_payer_cur_id') === true && $this->isValidParameter('bt_receiver_cur_id') === true) {
                $payerCur = $this->getStringParameter('bt_payer_cur_id');
                $receiverCur = $this->getStringParameter('bt_receiver_cur_id');
                if ($payerCur !== $receiverCur) {
                    $this->Validation->checkRequire('bt_exchange_rate');
                    $this->Validation->checkFloat('bt_exchange_rate');
                }
                if ($payerCur === $receiverCur && $this->isValidParameter('bt_exchange_rate') === true) {
                    $this->Validation->checkFloat('bt_exchange_rate', 1, 1);
                }
            }
            $this->Validation->checkMaxLength('bt_notes', 256);
        } elseif ($action === 'doReOpen') {
            $this->Validation->checkRequire('bt_payer_bab_id');
            $this->Validation->checkRequire('bt_receiver_bab_id');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setPageDescription(): void
    {

        if ($this->isDeleted('bt') === true) {
            $this->addDeletedMessage('bt');
        }

        # SET TITLE
        $btDao = new BankTransferDao();
        $title = $this->PageSetting->getPageDescription();
        $title .= ' #' . $this->getStringParameter('bt_number');
        $title .= ' | ' . $btDao->getStatus($this->getAllParameters());

        $this->View->setDescription($title);
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('BtGeneralPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $callback = 'loadSingleSelectData';
        if ($this->isAllowTransferFromOtherAccount() === true) {
            $callback = 'loadDataWithMainAccount';
        }
        $payerField = $this->Field->getSingleSelect('ba', 'bt_payer_ba', $this->getStringParameter('bt_payer_ba'), $callback);
        $payerField->setHiddenField('bt_payer_ba_id', $this->getStringParameter('bt_payer_ba_id'));
        $payerField->addParameter('ba_ss_id', $this->User->getSsId());
        $payerField->addParameter('ba_us_id', $this->User->getId());
        $payerField->addParameter('ba_blocked', 'N');
        $payerField->setDetailReferenceCode('ba_id');
        $payerField->setEnableNewButton(false);
        $payerField->setAutoCompleteFields([
            'bt_payer_cur_id' => 'ba_cur_id',
            'bt_payer_currency' => 'ba_currency'
        ]);

        $receiverField = $this->Field->getSingleSelect('ba', 'bt_receiver_ba', $this->getStringParameter('bt_receiver_ba'));
        $receiverField->setHiddenField('bt_receiver_ba_id', $this->getStringParameter('bt_receiver_ba_id'));
        $receiverField->addParameter('ba_ss_id', $this->User->getSsId());
        $receiverField->addParameter('ba_blocked', 'N');
        $receiverField->setDetailReferenceCode('ba_id');
        $receiverField->setEnableNewButton(false);
        $receiverField->setAutoCompleteFields([
            'bt_receiver_cur_id' => 'ba_cur_id',
            'bt_receiver_currency' => 'ba_currency'
        ]);

        $payerCur = $this->Field->getText('bt_payer_currency', $this->getStringParameter('bt_payer_currency'));
        $payerCur->setReadOnly();
        $receiverCur = $this->Field->getText('bt_receiver_currency', $this->getStringParameter('bt_receiver_currency'));
        $receiverCur->setReadOnly();

        # Add field to field set
        $fieldSet->addField(Trans::getWord('sender'), $payerField, true);
        $fieldSet->addField(Trans::getWord('receiver'), $receiverField, true);
        $fieldSet->addField(Trans::getWord('currencySender'), $payerCur, true);
        $fieldSet->addField(Trans::getWord('currencyReceiver'), $receiverCur, true);
        $fieldSet->addField(Trans::getWord('amount'), $this->Field->getNumber('bt_amount', $this->getFloatParameter('bt_amount')), true);
        $fieldSet->addField(Trans::getWord('exchangeRate'), $this->Field->getNumber('bt_exchange_rate', $this->getFloatParameter('bt_exchange_rate')));
        $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('bt_date', $this->getStringParameter('bt_date')), true);
        $fieldSet->addField(Trans::getWord('time'), $this->Field->getTime('bt_time', $this->getStringParameter('bt_time')), true);
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('bt_notes', $this->getStringParameter('bt_notes')));
        # Hidden
        $fieldSet->addHiddenField($this->Field->getHidden('bt_payer_cur_id', $this->getStringParameter('bt_payer_cur_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('bt_payer_bab_id', $this->getStringParameter('bt_payer_bab_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('bt_receiver_cur_id', $this->getStringParameter('bt_receiver_cur_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('bt_receiver_bab_id', $this->getStringParameter('bt_receiver_bab_id')));


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
        if ($this->isAllowUpdate() === false || $this->isPaid() === true) {
            $this->setDisableUpdate();
        }
        if ($this->isAllowUpdate() === true && $this->isPaid() === false) {
            # Enable Delete
            $this->setEnableDeleteButton(!$this->isDeleted('bt'));

            # Show paid confirmation
            $paidModal = $this->getPaidModal();
            $this->View->addModal($paidModal);
            $paidBtn = new ModalButton('BtPaidBtn', Trans::getWord('pay'), $paidModal->getModalId());
            $paidBtn->btnDark()->pullRight()->setIcon(Icon::Money);
            $this->View->addButton($paidBtn);
        }
        if ($this->isPaid() === true && $this->isAllowReOpenPayment() === true) {
            # Show Re open Confirmation
            $reOpenModal = $this->getReOpenModal();
            $this->View->addModal($reOpenModal);
            $reOpenBtn = new ModalButton('BtPaidBtn', Trans::getWord('reOpen'), $reOpenModal->getModalId());
            $reOpenBtn->btnDark()->pullRight()->setIcon(Icon::ArrowCircleOLeft);
            $this->View->addButton($reOpenBtn);
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getPaidModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtPaidMdl', Trans::getWord('paidTransferBalance'));
        $modal->setFormSubmit($this->getMainFormId(), 'doPaid');
        if ($this->getFormAction() === 'doPaid' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $p = new Paragraph(Trans::getMessageWord('paidConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getReOpenModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BtReOpenMdl', Trans::getWord('reOpen'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReOpen');
        if ($this->getFormAction() === 'doReOpen' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $p = new Paragraph(Trans::getMessageWord('reOpenDataConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));

        return $modal;
    }

    /**
     * Function to check is data deleted
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        $valid = $this->User->getId() === $this->getStringParameter('bt_payer_us_id') && $this->isDeleted('bt') === false;
        if ($this->isAllowTransferFromOtherAccount() === true) {
            return $valid || $this->getStringParameter('bt_payer_us_id') === null;
        }
        return $valid;
    }

    /**
     * Function to check is data deleted
     *
     * @return bool
     */
    private function isAllowTransferFromOtherAccount(): bool
    {
        return $this->PageSetting->checkPageRight('AllowTransferFromOtherAccount');
    }

    /**
     * Function to check is data deleted
     *
     * @return bool
     */
    private function isAllowReOpenPayment(): bool
    {
        return $this->PageSetting->checkPageRight('AllowReOpenPayment');
    }

    /**
     * Function to check is data deleted
     *
     * @return bool
     */
    private function isPaid(): bool
    {
        return $this->isValidParameter('bt_paid_on');
    }
}
