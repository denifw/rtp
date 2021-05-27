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
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Finance\CashAndBank\BankAccountBalanceDao;
use App\Model\Dao\Finance\CashAndBank\ElectronicAccountDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Finance\CashAndBank\ElectronicBalanceDao;
use App\Model\Dao\Finance\CashAndBank\ElectronicTopUpDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;

/**
 * Class to handle the creation of detail ElectronicAccount page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicAccount extends AbstractFormModel
{
    /**
     * Property to store trigger is transaction exist or not.
     *
     * @var bool $TransactionExist .
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
        parent::__construct(get_class($this), 'ea', 'ea_id');
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
            'ea_ss_id' => $this->User->getSsId(),
            'ea_code' => $this->getStringParameter('ea_code'),
            'ea_description' => $this->getStringParameter('ea_description'),
            'ea_cur_id' => $this->getIntParameter('ea_cur_id'),
            'ea_us_id' => $this->getIntParameter('ea_us_id'),
        ];
        $eaDao = new ElectronicAccountDao();
        $eaDao->doInsertTransaction($colVal);
        return $eaDao->getLastInsertId();
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
                'ea_code' => $this->getStringParameter('ea_code'),
                'ea_description' => $this->getStringParameter('ea_description'),
                'ea_cur_id' => $this->getIntParameter('ea_cur_id'),
                'ea_us_id' => $this->getIntParameter('ea_us_id'),
            ];
            $eaDao = new ElectronicAccountDao();
            $eaDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $eaDao = new ElectronicAccountDao();
            $eaDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        } elseif ($this->getFormAction() === 'doTopUp') {
            $babDao = new BankAccountBalanceDao();
            $ebDao = new ElectronicBalanceDao();
            $etDao = new ElectronicTopUpDao();
            $docId = $this->getIntParameter('et_doc_id');
            $file = $this->getFileParameter('et_receipt');
            if ($file !== null) {
                $docDao = new DocumentDao();
                # Delete old document
                if ($this->isValidParameter('et_doc_id') === true) {
                    $docDao->doDeleteTransaction($docId);
                }
                # Insert new Document
                $colValDoc = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('et_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => Trans::getFinanceWord('topUpReceipt'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'N',
                ];
                $docDao->doInsertTransaction($colValDoc);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docId);
                $upload->upload($file);
            }
            # Check Bank account has change
            $amount = $this->getFloatParameter('et_amount');
            # Update bank account balance
            if ($this->getIntParameter('et_ba_id') === $this->getIntParameter('et_ba_id_old')) {
                $babId = $this->getIntParameter('et_bab_id');
                $babDao->doUpdateTransaction($babId, [
                    'bab_amount' => $amount * -1
                ]);
            } else {
                # Delete old bank account balance
                if ($this->isValidParameter('et_bab_id') === true) {
                    $babDao->doDeleteTransaction($this->getIntParameter('et_bab_id'));
                }
                # Insert new Bab data
                $babDao->doInsertTransaction([
                    'bab_ba_id' => $this->getIntParameter('et_ba_id'),
                    'bab_amount' => $amount * -1
                ]);
                $babId = $babDao->getLastInsertId();
            }
            # Update Electronic Account Balance
            if ($this->isValidParameter('et_eb_id') === false) {
                $ebDao->doInsertTransaction([
                    'eb_ea_id' => $this->getDetailReferenceValue(),
                    'eb_amount' => $amount
                ]);
                $ebId = $ebDao->getLastInsertId();
            } else {
                # Update Electronic Account Balance
                $ebId = $this->getIntParameter('et_eb_id');
                $ebDao->doUpdateTransaction($ebId, [
                    'eb_amount' => $amount
                ]);
            }
            # Insert Electronic Top Up
            $colVal = [
                'et_ea_id' => $this->getDetailReferenceValue(),
                'et_ba_id' => $this->getIntParameter('et_ba_id'),
                'et_date' => $this->getStringParameter('et_date'),
                'et_amount' => $amount,
                'et_notes' => $this->getStringParameter('et_notes'),
                'et_doc_id' => $docId,
                'et_eb_id' => $ebId,
                'et_bab_id' => $babId,
            ];
            if ($this->isValidParameter('et_id') === false) {
                $etDao->doInsertTransaction($colVal);
            } else {
                $etDao->doUpdateTransaction($this->getIntParameter('et_id'), $colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteTopUp') {
            # Delete Bab Data
            $babDao = new BankAccountBalanceDao();
            $babDao->doDeleteTransaction($this->getIntParameter('et_bab_id_del'));

            # Delete Eb Data
            $ebDao = new ElectronicBalanceDao();
            $ebDao->doDeleteTransaction($this->getIntParameter('et_eb_id_del'));

            # Delete Doc data
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('et_doc_id_del'));

            # Delete Top Up data
            $etDao = new ElectronicTopUpDao();
            $etDao->doDeleteTransaction($this->getIntParameter('et_id_del'));
        } elseif ($this->getFormAction() === 'doBlockAccount') {
            $colVal = [
                'ea_block_by' => $this->User->getId(),
                'ea_block_on' => date('Y-m-d H:i:s'),
                'ea_block_reason' => $this->getStringParameter('ea_block_reason'),
            ];
            $eaDao = new ElectronicAccountDao();
            $eaDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ElectronicAccountDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isUpdate() === true) {
            if ($this->isDeleted() === true) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('ea_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ea_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ea_deleted_reason')
                ]));
            }
            if ($this->isBlocked() === true) {
                $this->View->addErrorMessage(Trans::getWord('blockedAccount', 'message', '', [
                    'user' => $this->getStringParameter('ea_block_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('ea_block_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('ea_block_reason')
                ]));
            }
            # Load Data
            $this->TransactionExist = ElectronicBalanceDao::isAccountHasBalance($this->getDetailReferenceValue());

            # Add Widget
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addContent('general', $this->getWidget());
            $this->Tab->addPortlet('general', $this->getTopUpPortlet());
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
            $this->Validation->checkRequire('ea_code', 1, 50);
            $this->Validation->checkRequire('ea_description', 1, 256);
            $this->Validation->checkRequire('ea_cur_id');
            $this->Validation->checkRequire('ea_us_id');
            $this->Validation->checkUnique('ea_code', 'electronic_account', [
                'ea_id' => $this->getDetailReferenceValue()
            ], [
                'ea_ss_id' => $this->User->getSsId()
            ]);
        } elseif ($this->getFormAction() === 'doTopUp') {
            $this->Validation->checkRequire('et_date');
            $this->Validation->checkRequire('et_ba_id');
            $this->Validation->checkRequire('et_amount');
            $this->Validation->checkRequire('et_dct_id');
            $this->Validation->checkDate('et_date');
            $this->Validation->checkFloat('et_amount', 10000);
            $this->Validation->checkMaxLength('et_notes', 256);
            if ($this->isValidParameter('et_id') === true) {
                $this->Validation->checkRequire('et_ba_id_old');
                $this->Validation->checkRequire('et_bab_id');
                $this->Validation->checkRequire('et_doc_id');
                $this->Validation->checkRequire('et_eb_id');
            } else {
                $this->Validation->checkRequire('et_receipt');
            }
            if ($this->isValidParameter('et_receipt') === true) {
                $this->Validation->checkFile('et_receipt');
            }
        } elseif ($this->getFormAction() === 'doDeleteTopUp') {
            $this->Validation->checkRequire('et_id_del');
            $this->Validation->checkRequire('et_bab_id_del');
            $this->Validation->checkRequire('et_doc_id_del');
            $this->Validation->checkRequire('et_eb_id_del');
        } elseif ($this->getFormAction() === 'doBlockAccount') {
            $this->Validation->checkRequire('ea_block_reason', 2, 256);
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
        $portlet = new Portlet('EaPtl', $this->getDefaultPortletTitle());
        if ($this->isInsert() === true) {
            $portlet->setGridDimension(12, 12, 12);
        } else {
            $portlet->setGridDimension(8, 8, 12);
        }

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Currency Field
        $curField = $this->Field->getSingleSelect('currency', 'ea_currency', $this->getStringParameter('ea_currency'));
        $curField->setHiddenField('ea_cur_id', $this->getIntParameter('ea_cur_id'));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);
        # User Field
        $usField = $this->Field->getSingleSelect('user', 'ea_user', $this->getStringParameter('ea_user'));
        $usField->setHiddenField('ea_us_id', $this->getIntParameter('ea_us_id'));
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->addParameter('rel_id', $this->User->getRelId());
        if ($this->isAllowInsertAccountForOtherUser() === false) {
            $usField->addParameter('us_id', $this->User->getId());
        }
        $usField->setEnableDetailButton(false);
        $usField->setEnableNewButton(false);

        if ($this->isUpdate() === true && $this->isAllowInsertAccountForOtherUser() === false && $this->isUserOwnAccount() === false) {
            $usField->setReadOnly();
        }
        if ($this->TransactionExist === true) {
            $curField->setReadOnly();
        }

        $fieldSet->addField(Trans::getFinanceWord('code'), $this->Field->getText('ea_code', $this->getStringParameter('ea_code')), true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('ea_description', $this->getStringParameter('ea_description')), true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('user'), $usField, true);
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
        $number = new NumberFormatter();
        $results = '';
        # Limit
        $plan = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('currentBalance'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-success',
            'amount' => $this->getStringParameter('ea_currency') . ' ' . $number->doFormatFloat($this->getFloatParameter('ea_balance')),
            'uom' => '',
            'url' => '',
        ];
        $plan->setData($data);
        $plan->setGridDimension(4, 4, 12);
        $results .= $plan->createView();
        return $results;
    }


    /**
     * Function to get the top up portlet.
     *
     * @return Portlet
     */
    private function getTopUpPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('EaEtPtl', Trans::getFinanceWord('recentTopUp'));
        $allowUpdate = $this->isAllowUpdate();
        $topUpModal = $this->getTopUpModal();
        $this->View->addModal($topUpModal);
        $topUpDelModal = $this->getTopUpDeleteModal();
        $this->View->addModal($topUpDelModal);

        # Create Table
        $table = new Table('EtTbl');
        $table->setHeaderRow([
            'et_date' => Trans::getFinanceWord('date'),
            'et_ba_description' => Trans::getFinanceWord('cashAccount'),
            'et_amount' => Trans::getFinanceWord('amount'),
            'et_receipt' => Trans::getFinanceWord('receipt'),
            'et_registered_by' => Trans::getFinanceWord('registeredBy'),
        ]);
        $data = ElectronicTopUpDao::getByAccount($this->getDetailReferenceValue());
        $rows = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['et_amount'] = $this->getStringParameter('ea_currency') . ' ' . $number->doFormatFloat($row['et_amount']);
            if (empty($row['et_doc_id']) === false) {
                $receipt = new Button('btnEtRecBt' . $row['et_id'], '');
                $receipt->setIcon(Icon::Download)->btnPrimary()->viewIconOnly();
                $receipt->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['et_doc_id']) . "')");
                $row['et_receipt'] = $receipt;
            }
            if ((int)$row['et_created_by'] === $this->User->getId()) {
                $btnUpdate = new ModalButton('btnEtUpBt' . $row['et_id'], '', $topUpModal->getModalId());
                $btnUpdate->setIcon(Icon::Pencil)->btnSuccess()->viewIconOnly();
                $btnUpdate->setEnableCallBack('et', 'getById');
                $btnUpdate->addParameter('et_id', $row['et_id']);
                # Btn Delete
                $btnDelete = new ModalButton('btnEtDelBt' . $row['et_id'], '', $topUpDelModal->getModalId());
                $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDelete->setEnableCallBack('et', 'getByIdForDelete');
                $btnDelete->addParameter('et_id', $row['et_id']);
                $row['et_action'] = $btnUpdate . ' ' . $btnDelete;
            }
            $rows[] = $row;
        }
        $table->addRows($rows);

        if ($allowUpdate) {
            $table->addColumnAtTheEnd('et_action', Trans::getFinanceWord('action'));
            $table->addColumnAttribute('et_action', 'style', 'text-align: center;');
            $btn = new ModalButton('newEtBtn', Trans::getFinanceWord('topUp'), $topUpModal->getModalId());
            $btn->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btn);
        }
        $table->setColumnType('et_date', 'date');
        $table->addColumnAttribute('et_receipt', 'style', 'text-align: center;');
        $table->addColumnAttribute('et_amount', 'style', 'text-align: right;');

        # Add table into portlet
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to get paid modal
     *
     * @return Modal
     */
    private function getTopUpModal(): Modal
    {
        # Set Dct v
        # Create Fields.
        $modal = new Modal('EtMdl', Trans::getFinanceWord('topUpAccount'));
        $modal->setFormSubmit($this->getMainFormId(), 'doTopUp');
        $showModal = false;
        if ($this->getFormAction() === 'doTopUp' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        } else {
            $dct = DocumentTypeDao::getByCode('EA', 'TopUpReceipt');
            if (empty($dct) === false) {
                $this->setParameter('et_dct_id', $dct['dct_id']);
            }
        }
        $baField = $this->Field->getSingleSelect('ba', 'et_ba_description', $this->getParameterForModal('et_ba_description', $showModal));
        $baField->setHiddenField('et_ba_id', $this->getParameterForModal('et_ba_id', $showModal));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_payable', 'Y');
        $baField->addParameter('ba_active', 'Y');
        $baField->addParameter('ba_cur_id', $this->getIntParameter('ea_cur_id'));
        if ($this->isAllowTopUpFromMainAccount() === false) {
            $baField->addParameter('ba_main', 'N');
            $baField->addParameter('ba_us_id', $this->User->getId());
        }
        $baField->setEnableNewButton(false);
        $baField->setEnableDetailButton(false);


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('et_date', $this->getParameterForModal('et_date', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('bankAccount'), $baField, true);
        $fieldSet->addField(Trans::getFinanceWord('amount'), $this->Field->getNumber('et_amount', $this->getParameterForModal('et_amount', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('receipt'), $this->Field->getFile('et_receipt', ''), true);
        $fieldSet->addField(Trans::getFinanceWord('notes'), $this->Field->getTextArea('et_notes', $this->getParameterForModal('et_notes', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_dct_id', $this->getParameterForModal('et_dct_id', true)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_id', $this->getParameterForModal('et_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_doc_id', $this->getParameterForModal('et_doc_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_bab_id', $this->getParameterForModal('et_bab_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_eb_id', $this->getParameterForModal('et_eb_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_ba_id_old', $this->getParameterForModal('et_ba_id_old', $showModal)));

        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get paid modal
     *
     * @return Modal
     */
    private function getTopUpDeleteModal(): Modal
    {
        # Set Dct v
        # Create Fields.
        $modal = new Modal('EtDelMdl', Trans::getFinanceWord('deleteTopUp'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteTopUp');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteTopUp' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getText('et_date_del', $this->getParameterForModal('et_date_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('bankAccount'), $this->Field->getText('et_ba_description_del', $this->getParameterForModal('et_ba_description_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('amount'), $this->Field->getNumber('et_amount_del', $this->getParameterForModal('et_amount_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('notes'), $this->Field->getTextArea('et_notes_del', $this->getParameterForModal('et_notes_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_id_del', $this->getParameterForModal('et_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_doc_id_del', $this->getParameterForModal('et_doc_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_bab_id_del', $this->getParameterForModal('et_bab_id_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('et_eb_id_del', $this->getParameterForModal('et_eb_id_del', $showModal)));

        $text = Trans::getMessageWord('deleteConfirmation');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));

        return $modal;
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
                if ($this->TransactionExist === false) {
                    $this->setEnableDeleteButton();
                } else {
                    $blockModal = $this->getBlockModal();
                    $this->View->addModal($blockModal);
                    $blockBtn = new ModalButton('BaBlockBtn', Trans::getFinanceWord('block'), $blockModal->getModalId());
                    $blockBtn->btnDark()->pullRight()->setIcon(Icon::Lock);
                    $this->View->addButton($blockBtn);
                }
            } else {
                $this->setDisableUpdate();
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
        $modal = new Modal('EaBlockMdl', Trans::getFinanceWord('blockAccount'));
        $modal->setFormSubmit($this->getMainFormId(), 'doBlockAccount');
        $showModal = false;
        if ($this->getFormAction() === 'doBlockAccount' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('reason'), $this->Field->getTextArea('ea_block_reason', $this->getParameterForModal('ea_block_reason', $showModal)), true);
        $p = new Paragraph(Trans::getMessageWord('blockAccountConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFinanceWord('yesBlock'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to check access right.
     *
     * @return bool
     */
    private function isAllowInsertAccountForOtherUser(): bool
    {
        return $this->PageSetting->checkPageRight('AllowInsertAccountForOtherUser');
    }

    /**
     * Function to check access right.
     *
     * @return bool
     */
    private function isAllowTopUpFromMainAccount(): bool
    {
        return $this->PageSetting->checkPageRight('AllowTopUpFromMainAccount');
    }

    /**
     * Function to check if user is own account.
     *
     * @return bool
     */
    private function isUserOwnAccount(): bool
    {
        return $this->User->getId() === $this->getIntParameter('ea_us_id');
    }

    /**
     * Function to check if data blocked.
     *
     * @return bool
     */
    private function isBlocked(): bool
    {
        return $this->isValidParameter('ea_block_on');
    }

    /**
     * Function to check if data deleted.
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('ea_deleted_on');
    }

    /**
     * Function to check if allow update.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->isDeleted() === false && $this->isBlocked() === false && $this->isAllowUpdateByUser() === true;
    }

    /**
     * Function to check if allow update.
     *
     * @return bool
     */
    private function isAllowUpdateByUser(): bool
    {
        return $this->isUserOwnAccount() || $this->isAllowInsertAccountForOtherUser();
    }
}
