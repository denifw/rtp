<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Master\Finance;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Master\Finance\BankAccountBalanceDao;
use App\Model\Dao\Master\Finance\BankAccountDao;

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
     * Property to store property of transaction.
     *
     * @var  bool $TransactionExist
     */
    private $TransactionExist = false;

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
        $usId = null;
        $limit = 0.0;
        $receivable = $this->getStringParameter('ba_receivable');
        $payable = $this->getStringParameter('ba_payable');
        if ($this->getStringParameter('ba_main', 'N') === 'N') {
            $receivable = 'Y';
            $payable = 'Y';
            $usId = $this->getStringParameter('ba_us_id');
            $limit = $this->getFloatParameter('ba_limit');
        }
        $colVal = [
            'ba_ss_id' => $this->User->getSsId(),
            'ba_code' => $this->getStringParameter('ba_code'),
            'ba_description' => $this->getStringParameter('ba_description'),
            'ba_bn_id' => $this->getStringParameter('ba_bn_id'),
            'ba_cur_id' => $this->getStringParameter('ba_cur_id'),
            'ba_account_number' => $this->getStringParameter('ba_account_number'),
            'ba_account_name' => $this->getStringParameter('ba_account_name'),
            'ba_bank_branch' => $this->getStringParameter('ba_bank_branch'),
            'ba_main' => $this->getStringParameter('ba_main'),
            'ba_receivable' => $receivable,
            'ba_payable' => $payable,
            'ba_us_id' => $usId,
            'ba_limit' => $limit,
        ];
        var_dump($colVal);
        exit;
        $baDao = new BankAccountDao();
        $baDao->doInsertTransaction($colVal);
        return $baDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $usId = null;
            $limit = 0.0;
            $receivable = $this->getStringParameter('ba_receivable');
            $payable = $this->getStringParameter('ba_payable');
            if ($this->getStringParameter('ba_main', 'N') === 'N') {
                $receivable = 'Y';
                $payable = 'Y';
                $usId = $this->getStringParameter('ba_us_id');
                $limit = $this->getFloatParameter('ba_limit');
            }
            $colVal = [
                'ba_ss_id' => $this->User->getSsId(),
                'ba_code' => $this->getStringParameter('ba_code'),
                'ba_description' => $this->getStringParameter('ba_description'),
                'ba_bn_id' => $this->getStringParameter('ba_bn_id'),
                'ba_cur_id' => $this->getStringParameter('ba_cur_id'),
                'ba_account_number' => $this->getStringParameter('ba_account_number'),
                'ba_account_name' => $this->getStringParameter('ba_account_name'),
                'ba_bank_branch' => $this->getStringParameter('ba_bank_branch'),
                'ba_main' => $this->getStringParameter('ba_main'),
                'ba_receivable' => $receivable,
                'ba_payable' => $payable,
                'ba_us_id' => $usId,
                'ba_limit' => $limit,
            ];
            $baDao = new BankAccountDao();
            $baDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doBlockAccount') {
            $baDao = new BankAccountDao();
            $baDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'ba_block_by' => $this->User->getId(),
                'ba_block_on' => date('Y-m-d H:i:s'),
                'ba_block_reason' => $this->getStringParameter('ba_block_reason')
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
        } else {
            if ($this->isValidParameter('ba_deleted_on') === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('ba_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ba_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ba_deleted_reason')
                ]));
            }
            if ($this->isValidParameter('ba_blocked_on') === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('blockedAccount', 'message', '', [
                    'user' => $this->getStringParameter('ba_block_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ba_block_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ba_block_reason')
                ]));
            }
            $this->TransactionExist = BankAccountBalanceDao::isBankAccountHasBalance($this->getDetailReferenceValue());
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
            $this->Validation->checkRequire('ba_code', 1, 50);
            $this->Validation->checkUnique('ba_code', 'bank_account', [
                'ba_id' => $this->getDetailReferenceValue()
            ], [
                'ba_ss_id' => $this->User->getSsId()
            ]);
            $this->Validation->checkRequire('ba_description', 2, 256);
            $this->Validation->checkRequire('ba_bn_id');
            $this->Validation->checkRequire('ba_cur_id');
            $this->Validation->checkRequire('ba_account_number', 2, 256);
            $this->Validation->checkRequire('ba_account_name', 2, 256);
            $this->Validation->checkMaxLength('ba_bank_branch', 256);
            $this->Validation->checkRequire('ba_main');
            if ($this->isValidParameter('ba_main') === true) {
                if ($this->getStringParameter('ba_main', 'Y') === 'N') {
                    $this->Validation->checkRequire('ba_us_id');
                    $this->Validation->checkUnique('ba_us_id', 'bank_account', [
                        'ba_id' => $this->getDetailReferenceValue()
                    ], [
                        'ba_ss_id' => $this->User->getSsId()
                    ]);
                    $this->Validation->checkRequire('ba_limit');
                    $this->Validation->checkFloat('ba_limit');
                } else {
                    $this->Validation->checkRequire('ba_receivable');
                    $this->Validation->checkRequire('ba_payable');
                }
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

        # Bank Field
        $bankField = $this->Field->getSingleSelect('bn', 'ba_bank_name', $this->getStringParameter('ba_bank_name'));
        $bankField->setHiddenField('ba_bn_id', $this->getStringParameter('ba_bn_id'));
        $bankField->setEnableNewButton(false);
        $bankField->setEnableDetailButton(false);

        # Currency Field
        $curField = $this->Field->getSingleSelect('cur', 'ba_currency', $this->getStringParameter('ba_currency'));
        $curField->setHiddenField('ba_cur_id', $this->getStringParameter('ba_cur_id'));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);

        $mainField = $this->Field->getYesNo('ba_main', $this->getStringParameter('ba_main'));
        if ($this->TransactionExist === true) {
            $usField->setReadOnly();
            $curField->setReadOnly();
            $mainField->setReadOnly();
        }

        # Add field to field set
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('ba_code', $this->getStringParameter('ba_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('ba_description', $this->getStringParameter('ba_description')), true);
        $fieldSet->addField(Trans::getWord('accountNumber'), $this->Field->getText('ba_account_number', $this->getStringParameter('ba_account_number')), true);
        $fieldSet->addField(Trans::getWord('bankName'), $bankField, true);
        $fieldSet->addField(Trans::getWord('accountName'), $this->Field->getText('ba_account_name', $this->getStringParameter('ba_account_name')), true);
        $fieldSet->addField(Trans::getWord('bankBranch'), $this->Field->getText('ba_bank_branch', $this->getStringParameter('ba_bank_branch')));
        $fieldSet->addField(Trans::getWord('mainAccount'), $mainField, true);
        $fieldSet->addField(Trans::getWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getWord('ar'), $this->Field->getYesNo('ba_receivable', $this->getStringParameter('ba_receivable')));
        $fieldSet->addField(Trans::getWord('ap'), $this->Field->getYesNo('ba_payable', $this->getStringParameter('ba_payable')));
        $fieldSet->addField(Trans::getWord('ceiling'), $this->Field->getNumber('ba_limit', $this->getFloatParameter('ba_limit')));
        $fieldSet->addField(Trans::getWord('accountManager'), $usField);

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
        if ($this->isUpdate() === true) {
            if ($this->isDeleted() === true || $this->isBlocked() === true) {
                $this->setDisableUpdate();
            }
            if ($this->TransactionExist === false) {
                $this->setEnableDeleteButton();
            }
            if ($this->TransactionExist === true && $this->getFloatParameter('ba_balance') === 0.0) {
                $blockModal = $this->getBlockModal();
                $this->View->addModal($blockModal);
                $blockBtn = new ModalButton('BaBlockBtn', Trans::getWord('block'), $blockModal->getModalId());
                $blockBtn->btnDark()->pullRight()->setIcon(Icon::Lock);
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
        $modal = new Modal('BaBlockMdl', Trans::getWord('blockAccount'));
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
        $p = new Paragraph(Trans::getMessageWord('blockAccountConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesBlock'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to check is data deleted
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('ba_deleted_on');
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
}
