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

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Administration\CashTransferDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Master\Finance\BankAccountBalanceDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;

/**
 * Class to handle the creation of detail CashTransfer page
 *
 * @package    app
 * @subpackage Model\Detail\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class CashTransfer extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ct', 'ct_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $amount = $this->getFloatParameter('ct_amount');
        $babDao = new BankAccountBalanceDao();
        # Insert Bab Payer
        $babDao->doInsertTransaction([
            'bab_ba_id' => $this->getStringParameter('ct_payer_ba_id'),
            'bab_amount' => $amount * -1,
        ]);
        $babPayer = $babDao->getLastInsertId();
        # Insert Bab Receiver
        $babDao->doInsertTransaction([
            'bab_ba_id' => $this->getStringParameter('ct_receiver_ba_id'),
            'bab_amount' => $amount,
        ]);
        $babReceiver = $babDao->getLastInsertId();

        # Insert Cash transfer
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('CT');
        $colVal = [
            'ct_ss_id' => $this->User->getSsId(),
            'ct_number' => $number,
            'ct_payer_ba_id' => $this->getStringParameter('ct_payer_ba_id'),
            'ct_payer_bab_id' => $babPayer,
            'ct_receiver_ba_id' => $this->getStringParameter('ct_receiver_ba_id'),
            'ct_receiver_bab_id' => $babReceiver,
            'ct_date' => $this->getStringParameter('ct_date'),
            'ct_amount' => $amount,
            'ct_currency_exchange' => 1,
            'ct_notes' => $this->getStringParameter('ct_notes'),
        ];
        $ctDao = new CashTransferDao();
        $ctDao->doInsertTransaction($colVal);

        # Insert Document
        $docId = null;
        $file = $this->getFileParameter('ct_file');
        if ($file !== null) {
            $colVal = [
                'doc_ss_id' => $this->User->getSsId(),
                'doc_dct_id' => $this->getStringParameter('ct_dct_id'),
                'doc_group_reference' => $ctDao->getLastInsertId(),
                'doc_type_reference' => null,
                'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                'doc_description' => $this->getStringParameter('ct_dct_description'),
                'doc_file_size' => $file->getSize(),
                'doc_file_type' => $file->getClientOriginalExtension(),
                'doc_public' => 'Y',
            ];
            $docDao = new DocumentDao();
            $docDao->doUploadDocument($colVal, $file);
        }
        return $ctDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $amount = $this->getFloatParameter('ct_amount');
            $babDao = new BankAccountBalanceDao();
            # Update Bab Payer
            $babDao->doUpdateTransaction($this->getStringParameter('ct_payer_bab_id'), [
                'bab_ba_id' => $this->getStringParameter('ct_payer_ba_id'),
                'bab_amount' => $amount * -1,
            ]);
            # Update Bab Receiver
            $babDao->doUpdateTransaction($this->getStringParameter('ct_receiver_bab_id'), [
                'bab_ba_id' => $this->getStringParameter('ct_receiver_ba_id'),
                'bab_amount' => $amount,
            ]);

            # Update Cash transfer
            $colVal = [
                'ct_payer_ba_id' => $this->getStringParameter('ct_payer_ba_id'),
                'ct_receiver_ba_id' => $this->getStringParameter('ct_receiver_ba_id'),
                'ct_date' => $this->getStringParameter('ct_date'),
                'ct_amount' => $amount,
                'ct_currency_exchange' => 1,
                'ct_notes' => $this->getStringParameter('ct_notes'),
            ];
            $ctDao = new CashTransferDao();
            $ctDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $ctDao = new CashTransferDao();
            $ctDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
            # Delete Bab Payer
            $babDao = new BankAccountBalanceDao();
            $babDao->doDeleteTransaction($this->getStringParameter('ct_payer_bab_id'));
            # Update Bab Receiver
            $babDao->doDeleteTransaction($this->getStringParameter('ct_receiver_bab_id'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CashTransferDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isUpdate() === true) {
            $this->View->setDescription($this->getStringParameter('ct_number'));
            $this->addDeletedMessage('ct');
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getBaseDocumentPortlet('ct', $this->getDetailReferenceValue(), '', '', false));
            if ($this->isDeleted('ct') === false) {
                $this->setEnableDeleteButton(true);
            }
        } else {
            $dct = DocumentTypeDao::getByCode('ct', 'receipt');
            if (empty($dct) === false) {
                $this->setParameter('ct_dct_id', $dct['dct_id']);
                $this->setParameter('ct_dct_description', $dct['dct_description']);
            }
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
            $this->Validation->checkRequire('ct_payer_ba_id');
            $this->Validation->checkRequire('ct_receiver_ba_id');
            $this->Validation->checkRequire('ct_amount');
            $this->Validation->checkFloat('ct_amount', 1);
            $this->Validation->checkRequire('ct_date');
            $this->Validation->checkDate('ct_date');
            $this->Validation->checkDifferent('ct_payer_ba_id', 'ct_receiver_ba_id');
            if ($this->isInsert() === true) {
                $this->Validation->checkRequire('ct_dct_id');
                $this->Validation->checkRequire('ct_dct_description');
                $this->Validation->checkRequire('ct_file');
                $this->Validation->checkFile('ct_file');
            } else {
                $this->Validation->checkRequire('ct_payer_bab_id');
                $this->Validation->checkRequire('ct_receiver_bab_id');
            }
        } elseif ($this->isDeleteAction() === true) {
            $this->Validation->checkRequire('ct_payer_bab_id');
            $this->Validation->checkRequire('ct_receiver_bab_id');
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
        $portlet = new Portlet('CtPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $payerField = $this->Field->getSingleSelect('ba', 'ct_payer', $this->getStringParameter('ct_payer'));
        $payerField->setHiddenField('ct_payer_ba_id', $this->getStringParameter('ct_payer_ba_id'));
        $payerField->setEnableNewButton(false);
        $payerField->addParameter('ba_ss_id', $this->User->getSsId());

        $receiverField = $this->Field->getSingleSelect('ba', 'ct_receiver', $this->getStringParameter('ct_receiver'));
        $receiverField->setHiddenField('ct_receiver_ba_id', $this->getStringParameter('ct_receiver_ba_id'));
        $receiverField->setEnableNewButton(false);
        $receiverField->addParameter('ba_ss_id', $this->User->getSsId());

        # Add field to field set
        $fieldSet->addField(Trans::getWord('sender'), $payerField, true);
        $fieldSet->addField(Trans::getWord('receiver'), $receiverField, true);
        $fieldSet->addField(Trans::getWord('amount'), $this->Field->getNumber('ct_amount', $this->getFloatParameter('ct_amount')), true);
        $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('ct_date', $this->getStringParameter('ct_date')), true);
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('ct_notes', $this->getStringParameter('ct_notes')));
        if ($this->isInsert() === true) {
            $fieldSet->addField(Trans::getWord('receipt'), $this->Field->getFile('ct_file', ''), true);
            $fieldSet->addHiddenField($this->Field->getHidden('ct_dct_id', $this->getStringParameter('ct_dct_id')));
            $fieldSet->addHiddenField($this->Field->getHidden('ct_dct_description', $this->getStringParameter('ct_dct_description')));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('ct_payer_bab_id', $this->getStringParameter('ct_payer_bab_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('ct_receiver_bab_id', $this->getStringParameter('ct_receiver_bab_id')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
