<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Setting\DocumentSignatureDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail DocumentSignature page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentSignature extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentSignature', 'ds_id');
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
            'ds_ss_id' => $this->User->getSsId(),
            'ds_dt_id' => $this->getIntParameter('ds_dt_id'),
            'ds_cp_id' => $this->getIntParameter('ds_cp_id'),
        ];

        $dsDao = new DocumentSignatureDao();
        $dsDao->doInsertTransaction($colVal);
        return $dsDao->getLastInsertId();
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
                'ds_ss_id' => $this->User->getSsId(),
                'ds_dt_id' => $this->getIntParameter('ds_dt_id'),
                'ds_cp_id' => $this->getIntParameter('ds_cp_id'),
            ];

            $dsDao = new DocumentSignatureDao();
            $dsDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction()) {
            $colVal = [
                'ds_deleted_reason' => $this->getReasonDeleteAction(),
                'ds_deleted_by' => $this->User->getId(),
                'ds_deleted_on' => date('Y-m-d H:i:s')
            ];
            $dsDao = new DocumentSignatureDao();
            $dsDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DocumentSignatureDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());

        if ($this->isUpdate()) {
            $this->setEnableDeleteButton();
            if ($this->isValidParameter('ds_deleted_reason')) {
                $this->setEnableDeleteButton(false);
                $this->setDisableUpdate();

                $massage = "Deleted by " . $this->getStringParameter('ds_us_name') . " With Reason : ";
                $massage .= $this->getStringParameter('ds_deleted_reason');
                $this->View->addErrorMessage($massage);
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
            $this->Validation->checkRequire('ds_dt_id');
            $this->Validation->checkRequire('ds_cp_id');
            $this->Validation->checkUnique('ds_dt_id', 'document_signature', [
                'ds_id' => $this->getDetailReferenceValue()
            ], [
                'ds_ss_id' => $this->User->getSsId(),
                'ds_deleted_on' => null
            ]);
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
        $portlet = new Portlet('DsPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);

        $dsDttField = $this->Field->getSingleSelect('documentTemplateType', 'ds_dtt_description', $this->getStringParameter('ds_dtt_description'));
        $dsDttField->setHiddenField('ds_dtt_id', $this->getIntParameter('ds_dtt_id'));
        $dsDttField->setEnableNewButton(false);

        $dsDtField = $this->Field->getSingleSelect('documentTemplate', 'ds_dt_description', $this->getStringParameter('ds_dt_description'));
        $dsDtField->setHiddenField('ds_dt_id', $this->getIntParameter('ds_dt_id'));
        $dsDtField->addParameterById('dt_dtt_id', 'ds_dtt_id', Trans::getWord('templateType'));
        $dsDtField->setEnableNewButton(false);

        $dsCpField = $this->Field->getSingleSelect('contactPerson', 'ds_cp_name', $this->getStringParameter('ds_cp_name'));
        $dsCpField->setHiddenField('ds_cp_id', $this->getIntParameter('ds_cp_id'));
        $dsCpField->addParameter('cp_rel_id', $this->User->getRelId());
        $dsCpField->setDetailReferenceCode('cp_id');

        # Add field to field set

        $fieldSet->addField(Trans::getWord('templateType'), $dsDttField, true);
        $fieldSet->addField(Trans::getWord('template'), $dsDtField, true);
        $fieldSet->addField(Trans::getWord('person'), $dsCpField, true);

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
