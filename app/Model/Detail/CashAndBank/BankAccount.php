<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\CashAndBank;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\CashAndBank\BankAccountBalanceDao;
use App\Model\Dao\CashAndBank\BankAccountDao;

/**
 * Class to handle the creation of detail BankAccount page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccount extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ba', 'ba_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $initialBalance = $this->getFloatParameter('ba_initial_balance');
        $receivable = $this->getStringParameter('ba_receivable');
        $payable = $this->getStringParameter('ba_payable');
        if ($this->getStringParameter('ba_main', 'N') === 'Y') {
            $receivable = 'N';
            $payable = 'N';
            $initialBalance = 0.0;
        }
        $colVal = [
            'ba_ss_id' => $this->User->getSsId(),
            'ba_code' => $this->getStringParameter('ba_code'),
            'ba_description' => $this->getStringParameter('ba_description'),
            'ba_initial_balance' => $initialBalance,
            'ba_bn_id' => $this->getStringParameter('ba_bn_id'),
            'ba_cur_id' => $this->getStringParameter('ba_cur_id'),
            'ba_account_number' => $this->getStringParameter('ba_account_number'),
            'ba_account_name' => $this->getStringParameter('ba_account_name'),
            'ba_bank_branch' => $this->getStringParameter('ba_bank_branch'),
            'ba_main' => $this->getStringParameter('ba_main'),
            'ba_receivable' => $receivable,
            'ba_payable' => $payable,
            'ba_us_id' => $this->getStringParameter('ba_us_id'),
        ];
        $baDao = new BankAccountDao();
        $baDao->doInsertTransaction($colVal);
        $baId = $baDao->getLastInsertId();
        # Insert bank account balance
        if ($initialBalance !== 0.0) {
            $babDao = new BankAccountBalanceDao();
            $babDao->doInsertTransaction([
                'bab_ba_id' => $baId,
                'bab_amount' => $initialBalance
            ]);

            # Update ba_bab_id
            $baDao->doUpdateTransaction($baId, [
                'ba_bab_id' => $babDao->getLastInsertId()
            ]);
        }
        return $baId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $initialBalanceOld = $this->getFloatParameter('ba_initial_balance_old');
            $initialBalance = $this->getFloatParameter('ba_initial_balance');
            $receivable = $this->getStringParameter('ba_receivable');
            $payable = $this->getStringParameter('ba_payable');
            if ($this->getStringParameter('ba_main', 'N') === 'Y') {
                $receivable = 'N';
                $payable = 'N';
                $initialBalance = 0.0;
            }
            $colVal = [
                'ba_ss_id' => $this->User->getSsId(),
                'ba_code' => $this->getStringParameter('ba_code'),
                'ba_description' => $this->getStringParameter('ba_description'),
                'ba_initial_balance' => $initialBalance,
                'ba_bn_id' => $this->getStringParameter('ba_bn_id'),
                'ba_cur_id' => $this->getStringParameter('ba_cur_id'),
                'ba_account_number' => $this->getStringParameter('ba_account_number'),
                'ba_account_name' => $this->getStringParameter('ba_account_name'),
                'ba_bank_branch' => $this->getStringParameter('ba_bank_branch'),
                'ba_main' => $this->getStringParameter('ba_main'),
                'ba_receivable' => $receivable,
                'ba_payable' => $payable,
                'ba_us_id' => $this->getStringParameter('ba_us_id'),
            ];
            $baDao = new BankAccountDao();
            $baDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            if ($initialBalance !== $initialBalanceOld) {
                $babDao = new BankAccountBalanceDao();
                if ($this->isValidParameter('ba_bab_id') === false) {
                    if ($initialBalance !== 0.0) {
                        $babDao->doInsertTransaction([
                            'bab_ba_id' => $this->getDetailReferenceValue(),
                            'bab_amount' => $initialBalance
                        ]);

                        # Update ba_bab_id
                        $baDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                            'ba_bab_id' => $babDao->getLastInsertId()
                        ]);
                    }
                } else {
                    $babDao->doUpdateTransaction($this->getStringParameter('ba_bab_id'), [
                        'bab_amount' => $initialBalance
                    ]);
                }
            }
        } elseif ($this->getFormAction() === 'doBlockAccount') {
            $baDao = new BankAccountDao();
            $baDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'ba_block_by' => $this->User->getId(),
                'ba_block_on' => date('Y-m-d H:i:s'),
                'ba_block_reason' => $this->getStringParameter('ba_block_reason')
            ]);
        } elseif ($this->getFormAction() === 'doUnBlockAccount') {
            $baDao = new BankAccountDao();
            $baDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'ba_block_by' => null,
                'ba_block_on' => null,
                'ba_block_reason' => null
            ]);
        } elseif ($this->isDeleteAction() === true) {
            $baDao = new BankAccountDao();
            $baDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return BankAccountDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getBankPortlet());
        } else {
            $this->setParameter('ba_initial_balance_old', $this->getFloatParameter('ba_initial_balance'));
            if ($this->isValidParameter('ba_deleted_on') === true) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('ba_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ba_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ba_deleted_reason')
                ]));
            }
            if ($this->isValidParameter('ba_block_on') === true) {
                $this->View->addErrorMessage(Trans::getWord('frozenAccount', 'message', '', [
                    'user' => $this->getStringParameter('ba_block_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ba_block_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ba_block_reason')
                ]));
            }
            if ($this->getStringParameter('ba_main') === 'N') {
                $this->Tab->addContent('general', $this->getWidget());
            }
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getBankPortlet());
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
            $this->Validation->checkRequire('ba_code', 1, 64);
            $this->Validation->checkUnique('ba_code', 'bank_account', [
                'ba_id' => $this->getDetailReferenceValue()
            ], [
                'ba_ss_id' => $this->User->getSsId()
            ]);
            $this->Validation->checkRequire('ba_description', 2, 256);
            $this->Validation->checkRequire('ba_main');
            $this->Validation->checkRequire('ba_cur_id');
            $this->Validation->checkRequire('ba_receivable');
            $this->Validation->checkRequire('ba_payable');
            $this->Validation->checkMaxLength('ba_account_number', 256);
            $this->Validation->checkMaxLength('ba_account_name', 256);
            $this->Validation->checkMaxLength('ba_bank_branch', 256);
            if ($this->getStringParameter('ba_main', 'Y') === 'N') {
                $this->Validation->checkRequire('ba_initial_balance');
                $this->Validation->checkFloat('ba_initial_balance');
                $this->Validation->checkRequire('ba_us_id');
            }
        } elseif ($this->getFormAction() === 'doBlockAccount') {
            $this->Validation->checkRequire('ba_block_reason', 2, 256);
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
        $portlet = new Portlet('ba', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # User Field
        $usField = $this->Field->getSingleSelect('us', 'ba_user', $this->getStringParameter('ba_user'));
        $usField->setHiddenField('ba_us_id', $this->getStringParameter('ba_us_id'));
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->addParameter('rel_id', $this->User->getRelId());
        $usField->setEnableDetailButton(false);
        $usField->setEnableNewButton(false);

        # Currency Field
        $curField = $this->Field->getSingleSelect('cur', 'ba_currency', $this->getStringParameter('ba_currency'));
        $curField->setHiddenField('ba_cur_id', $this->getStringParameter('ba_cur_id'));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);

        $mainField = $this->Field->getYesNo('ba_main', $this->getStringParameter('ba_main'));
        $arField = $this->Field->getYesNo('ba_receivable', $this->getStringParameter('ba_receivable'));
        $apField = $this->Field->getYesNo('ba_payable', $this->getStringParameter('ba_payable'));
        $initialBalanceField = $this->Field->getNumber('ba_initial_balance', $this->getFloatParameter('ba_initial_balance'));
        if ($this->isUpdate() === true) {
            $mainField->setReadOnly();
            $arField->setReadOnly();
            $apField->setReadOnly();
            $initialBalanceField->setReadOnly();
            $usField->setReadOnly();
        }

        # Add field to field set
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('ba_code', $this->getStringParameter('ba_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('ba_description', $this->getStringParameter('ba_description')), true);
        $fieldSet->addField(Trans::getWord('investorAccount'), $mainField, true);
        $fieldSet->addField(Trans::getWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getWord('ar'), $arField, true);
        $fieldSet->addField(Trans::getWord('ap'), $apField, true);
        $fieldSet->addField(Trans::getWord('initialBalance'), $initialBalanceField);
        $fieldSet->addField(Trans::getWord('owner'), $usField);
        $fieldSet->addHiddenField($this->Field->getHidden('ba_bab_id', $this->getStringParameter('ba_bab_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('ba_initial_balance_old', $this->getStringParameter('ba_initial_balance_old')));

        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 6);
        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getBankPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('BaBn', Trans::getWord('detailBank'));

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Bank Field
        $bankField = $this->Field->getSingleSelect('bn', 'ba_bank_name', $this->getStringParameter('ba_bank_name'));
        $bankField->setHiddenField('ba_bn_id', $this->getStringParameter('ba_bn_id'));
        $bankField->setEnableNewButton(false);
        $bankField->setEnableDetailButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('bankName'), $bankField);
        $fieldSet->addField(Trans::getWord('accountNumber'), $this->Field->getText('ba_account_number', $this->getStringParameter('ba_account_number')));
        $fieldSet->addField(Trans::getWord('accountName'), $this->Field->getText('ba_account_name', $this->getStringParameter('ba_account_name')));
        $fieldSet->addField(Trans::getWord('bankBranch'), $this->Field->getText('ba_bank_branch', $this->getStringParameter('ba_bank_branch')));

        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 6);
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
            if ($this->isDeleted('ba') === true || $this->isBlocked() === true) {
                $this->setDisableUpdate();
            }
            if ($this->isBlocked() === false) {
                $blockModal = $this->getBlockModal();
                $this->View->addModal($blockModal);
                $blockBtn = new ModalButton('BaBlockBtn', Trans::getWord('freeze'), $blockModal->getModalId());
                $blockBtn->btnDark()->pullRight()->setIcon(Icon::Lock);
                $this->View->addButton($blockBtn);
            } else {
                $unBlockModal = $this->getUnBlockModal();
                $this->View->addModal($unBlockModal);
                $blockBtn = new ModalButton('BaUnBlockBtn', Trans::getWord('openFreeze'), $unBlockModal->getModalId());
                $blockBtn->btnDark()->pullRight()->setIcon(Icon::Unlock);
                $this->View->addButton($blockBtn);
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getBlockModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaBlockMdl', Trans::getWord('freezeAccount'));
        $modal->setFormSubmit($this->getMainFormId(), 'doBlockAccount');
        $showModal = false;
        if ($this->getFormAction() === 'doBlockAccount' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('reason'), $this->Field->getTextArea('ba_block_reason', $this->getParameterForModal('ba_block_reason', $showModal)), true);
        $p = new Paragraph(Trans::getMessageWord('freezeAccountConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    private function getUnBlockModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaUnBlockMdl', Trans::getWord('openFreezeAccount'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUnBlockAccount');
        if ($this->getFormAction() === 'doUnBlockAccount' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $p = new Paragraph(Trans::getMessageWord('unFreezeAccountConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesConfirm'));

        return $modal;
    }

    /**
     * Function to check is data block
     *
     * @return bool
     */
    private function isBlocked(): bool
    {
        return $this->isValidParameter('ba_block_on');
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
        # Balance
        $balance = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('balance'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-success',
            'amount' => $this->getStringParameter('ba_currency') . ' ' . $number->doFormatFloat($this->getFloatParameter('ba_current_balance')),
            'uom' => '',
            'url' => '',
        ];
        $balance->setData($data);
        $balance->setGridDimension(6, 6);
        $results .= $balance->createView();
//
//        # Cash Advance
//        $totalCashAdvance = CashAdvanceDao::getTotalUnSettlementCashByBankAccount($this->getDetailReferenceValue());
//        $cashAdvance = new NumberGeneral();
//        $data = [
//            'title' => Trans::getFinanceWord('onGoingCash'),
//            'icon' => '',
//            'tile_style' => 'tile-stats tile-warning',
//            'amount' => $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($totalCashAdvance),
//            'uom' => '',
//            'url' => '',
//        ];
//        $cashAdvance->setData($data);
//        $cashAdvance->setGridDimension(6, 6);
//        $results .= $cashAdvance->createView();
        return $results;
    }

}
