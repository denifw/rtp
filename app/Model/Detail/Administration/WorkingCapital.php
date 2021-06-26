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
use App\Model\Dao\Administration\WorkingCapitalDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Master\Finance\BankAccountBalanceDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail WorkingCapital page
 *
 * @package    app
 * @subpackage Model\Detail\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class WorkingCapital extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'wc', 'wc_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $amount = $this->getFloatParameter('wc_amount');
        if ($this->getStringParameter('wc_type') === 'W') {
            $amount *= -1;
        }
        $babDao = new BankAccountBalanceDao();
        $babDao->doInsertTransaction([
            'bab_ba_id' => $this->getStringParameter('wc_ba_id'),
            'bab_amount' => $amount,
        ]);
        $colVal = [
            'wc_ss_id' => $this->User->getSsId(),
            'wc_ba_id' => $this->getStringParameter('wc_ba_id'),
            'wc_bab_id' => $babDao->getLastInsertId(),
            'wc_type' => $this->getStringParameter('wc_type'),
            'wc_date' => $this->getStringParameter('wc_date'),
            'wc_time' => $this->getStringParameter('wc_time'),
            'wc_transaction_on' => $this->getStringParameter('wc_date') . ' ' . $this->getStringParameter('wc_time') . ':00',
            'wc_amount' => $amount,
            'wc_reference' => $this->getStringParameter('wc_reference'),
        ];
        $wcDao = new WorkingCapitalDao();
        $wcDao->doInsertTransaction($colVal);
        return $wcDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $amount = $this->getFloatParameter('wc_amount');
            if ($this->getStringParameter('wc_type') === 'W') {
                $amount *= -1;
            }
            $babDao = new BankAccountBalanceDao();
            $babDao->doUpdateTransaction($this->getStringParameter('wc_bab_id'), [
                'bab_ba_id' => $this->getStringParameter('wc_ba_id'),
                'bab_amount' => $amount,
            ]);
            $colVal = [
                'wc_ba_id' => $this->getStringParameter('wc_ba_id'),
                'wc_type' => $this->getStringParameter('wc_type'),
                'wc_date' => $this->getStringParameter('wc_date'),
                'wc_time' => $this->getStringParameter('wc_time'),
                'wc_transaction_on' => $this->getStringParameter('wc_date') . ' ' . $this->getStringParameter('wc_time') . ':00',
                'wc_amount' => $amount,
                'wc_reference' => $this->getStringParameter('wc_reference'),
            ];
            $wcDao = new WorkingCapitalDao();
            $wcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $wcDao = new WorkingCapitalDao();
            $wcDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        } elseif ($this->isUploadDocumentAction() === true) {
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getStringParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => $this->getStringParameter('doc_public', 'Y'),
                ];
                $docDao = new DocumentDao();
                $docDao->doUploadDocument($colVal, $file);
            }
        } elseif ($this->isDeleteDocumentAction() === true) {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getStringParameter('doc_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return WorkingCapitalDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->addDeletedMessage('wc');
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('wc', $this->getDetailReferenceValue()));
            if($this->isValidParameter('wc_deleted_on') === false) {
                $this->setEnableDeleteButton(true);
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
            $this->Validation->checkRequire('wc_type');
            $this->Validation->checkRequire('wc_ba_id');
            $this->Validation->checkRequire('wc_date');
            $this->Validation->checkDate('wc_date');
            $this->Validation->checkRequire('wc_time');
            $this->Validation->checkTime('wc_time');
            $this->Validation->checkRequire('wc_amount');
            $this->Validation->checkFloat('wc_amount', 1);
            $this->Validation->checkMaxLength('wc_reference', 256);
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('wc_bab_id');
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
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('WcPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Bank Account
        $baField = $this->Field->getSingleSelect('ba', 'wc_bank_account', $this->getStringParameter('wc_bank_account'));
        $baField->setHiddenField('wc_ba_id', $this->getStringParameter('wc_ba_id'));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_main', 'Y');
        $baField->setEnableNewButton(false);
        $baField->setAutoCompleteFields([
            'wc_currency' => 'ba_currency'
        ]);
        # Type
        $typeField = $this->Field->getSelect('wc_type', $this->getStringParameter('wc_type'));
        $typeField->addOption(Trans::getWord('deposit'), 'D');
        $typeField->addOption(Trans::getWord('withdrawal'), 'W');

        $curField = $this->Field->getText('wc_currency', $this->getStringParameter('wc_currency'));
        $curField->setReadOnly(true);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('type'), $typeField, true);
        $fieldSet->addField(Trans::getWord('account'), $baField, true);
        $fieldSet->addField(Trans::getWord('date'), $this->Field->getCalendar('wc_date', $this->getStringParameter('wc_date')), true);
        $fieldSet->addField(Trans::getWord('time'), $this->Field->getTime('wc_time', $this->getStringParameter('wc_time')), true);
        $fieldSet->addField(Trans::getWord('amount'), $this->Field->getNumber('wc_amount', $this->getFloatParameter('wc_amount')), true);
        $fieldSet->addField(Trans::getWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('wc_reference', $this->getStringParameter('wc_reference')));
        if ($this->isUpdate() === true) {
            $fieldSet->addHiddenField($this->Field->getHidden('wc_bab_id', $this->getStringParameter('wc_bab_id')));
        }
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
