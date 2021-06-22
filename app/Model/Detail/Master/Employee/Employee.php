<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Master\Employee;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\ContactPersonDao;
use App\Model\Dao\Master\Employee\EmployeeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail Employee page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Employee extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'em', 'em_id');
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
        $cpNumber = $sn->loadNumber('CP', $this->User->Relation->getOfficeId(), $this->User->Settings->getOwnerId());
        $cpColVal = [
            'cp_number' => $cpNumber,
            'cp_of_id' => $this->User->Relation->getOfficeId(),
            'cp_name' => $this->getStringParameter('em_name'),
            'cp_email' => $this->getStringParameter('em_email'),
            'cp_phone' => $this->getStringParameter('em_phone'),
            'cp_active' => 'Y',
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($cpColVal);

        $emNumber = $sn->loadNumber('EM', $this->User->Relation->getOfficeId(), $this->User->Settings->getOwnerId());

        $colVal = [
            'em_ss_id' => $this->User->getSsId(),
            'em_cp_id' => $cpDao->getLastInsertId(),
            'em_jt_id' => $this->getStringParameter('em_jt_id'),
            'em_number' => $emNumber,
            'em_name' => $this->getStringParameter('em_name'),
            'em_identity_number' => $this->getStringParameter('em_identity_number'),
            'em_gender' => $this->getStringParameter('em_gender'),
            'em_birthday' => $this->getStringParameter('em_birthday'),
            'em_join_date' => $this->getStringParameter('em_join_date'),
            'em_phone' => $this->getStringParameter('em_phone'),
            'em_email' => $this->getStringParameter('em_email'),
            'em_active' => 'Y',
        ];
        $emDao = new EmployeeDao();
        $emDao->doInsertTransaction($colVal);
        return $emDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $cpColVal = [
                'cp_name' => $this->getStringParameter('em_name'),
                'cp_email' => $this->getStringParameter('em_email'),
                'cp_phone' => $this->getStringParameter('em_phone'),
                'cp_active' => $this->getStringParameter('em_active'),
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doUpdateTransaction($this->getStringParameter('em_cp_id'), $cpColVal);

            $colVal = [
                'em_jt_id' => $this->getStringParameter('em_jt_id'),
                'em_name' => $this->getStringParameter('em_name'),
                'em_identity_number' => $this->getStringParameter('em_identity_number'),
                'em_gender' => $this->getStringParameter('em_gender'),
                'em_birthday' => $this->getStringParameter('em_birthday'),
                'em_join_date' => $this->getStringParameter('em_join_date'),
                'em_phone' => $this->getStringParameter('em_phone'),
                'em_email' => $this->getStringParameter('em_email'),
                'em_active' => $this->getStringParameter('em_active'),
            ];
            $emDao = new EmployeeDao();
            $emDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isUploadDocumentAction()) {
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
        } elseif ($this->isDeleteDocumentAction()) {
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
        return EmployeeDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->View->setDescription($this->getStringParameter('em_number'));
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('em', $this->getDetailReferenceValue()));
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
            $this->Validation->checkRequire('em_name', 2, 256);
            $this->Validation->checkRequire('em_identity_number', 2, 256);
            $this->Validation->checkRequire('em_jt_id');
            $this->Validation->checkRequire('em_gender');
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('em_cp_id');
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
        $portlet = new Portlet('EmPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $jtField = $this->Field->getSingleSelect('jt', 'em_job_title', $this->getStringParameter('em_job_title'));
        $jtField->setHiddenField('em_jt_id', $this->getStringParameter('em_jt_id'));
        $jtField->addParameter('jt_ss_id', $this->User->getSsId());
        $jtField->setDetailReferenceCode('jt_id');

        $genderField = $this->Field->getRadioGroup('em_gender', $this->getStringParameter('em_gender'));
        $genderField->addRadios([
            'M' => Trans::getWord('man'),
            'W' => Trans::getWord('woman'),
        ]);


        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('em_name', $this->getStringParameter('em_name')), true);
        $fieldSet->addField(Trans::getWord('identityNumber'), $this->Field->getText('em_identity_number', $this->getStringParameter('em_identity_number')), true);
        $fieldSet->addField(Trans::getWord('gender'), $genderField, true);
        $fieldSet->addField(Trans::getWord('jobTitle'), $jtField, true);
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('em_phone', $this->getStringParameter('em_phone')));
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('em_email', $this->getStringParameter('em_email')));
        $fieldSet->addField(Trans::getWord('birthday'), $this->Field->getCalendar('em_birthday', $this->getStringParameter('em_birthday')));
        $fieldSet->addField(Trans::getWord('joinDate'), $this->Field->getCalendar('em_join_date', $this->getStringParameter('em_join_date')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('em_active', $this->getStringParameter('em_active')));
            $fieldSet->addHiddenField($this->Field->getHidden('em_cp_id', $this->getStringParameter('em_cp_id')));
        }

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
