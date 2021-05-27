<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Document;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail DocumentType page
 *
 * @package    app
 * @subpackage Model\Detail\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentType', 'dct_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $code = $this->getStringParameter('dct_code');
        $code = mb_strtolower(StringFormatter::replaceSpecialCharacter($code));
        $colVal = [
            'dct_code' => $code,
            'dct_description' => $this->getStringParameter('dct_description'),
            'dct_dcg_id' => $this->getStringParameter('dct_dcg_id'),
            'dct_table' => $this->getStringParameter('dct_table'),
            'dct_value_field' => $this->getStringParameter('dct_value_field'),
            'dct_text_field' => $this->getStringParameter('dct_text_field'),
            'dct_master' => $this->getStringParameter('dct_master', 'Y'),
            'dct_active' => $this->getStringParameter('dct_active', 'Y'),
        ];
        $dctDao = new DocumentTypeDao();
        $dctDao->doInsertTransaction($colVal);

        return $dctDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dct_code' => $this->getStringParameter('dct_code'),
            'dct_description' => $this->getStringParameter('dct_description'),
            'dct_dcg_id' => $this->getStringParameter('dct_dcg_id'),
            'dct_table' => $this->getStringParameter('dct_table'),
            'dct_value_field' => $this->getStringParameter('dct_value_field'),
            'dct_text_field' => $this->getStringParameter('dct_text_field'),
            'dct_master' => $this->getStringParameter('dct_master', 'Y'),
            'dct_active' => $this->getStringParameter('dct_active', 'Y'),
        ];
        $dctDao = new DocumentTypeDao();
        $dctDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DocumentTypeDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('dct_code', 2, 125);
        $this->Validation->checkRequire('dct_description', 2, 255);
        $this->Validation->checkRequire('dct_dcg_id');
        $this->Validation->checkUnique('dct_code', 'document_type', [
            'dct_id' => $this->getDetailReferenceValue()
        ], [
            'dct_dcg_id' => $this->getIntParameter('dct_dcg_id')
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        if ($this->isValidParameter('dct_table') === true) {
            $this->setParameter('dct_table_text', $this->getStringParameter('dct_table'));
        }
        if ($this->isValidParameter('dct_value_field') === true) {
            $this->setParameter('dct_value_text', $this->getStringParameter('dct_value_field'));
        }
        if ($this->isValidParameter('dct_text_field') === true) {
            $this->setParameter('dct_text_text', $this->getStringParameter('dct_text_field'));
        }
        # Create Fields.
        $dcgFields = $this->Field->getSingleSelect('documentGroup', 'dct_group', $this->getStringParameter('dct_group'));
        $dcgFields->setHiddenField('dct_dcg_id', $this->getStringParameter('dct_dcg_id'));
        $dcgFields->setDetailReferenceCode('dcg_id');
        # Table Field
        $tblFields = $this->Field->getSingleSelect('systemTable', 'dct_table_text', $this->getStringParameter('dct_table_text'));
        $tblFields->setHiddenField('dct_table', $this->getStringParameter('dct_table'));
        $tblFields->setEnableNewButton(false);
        $tblFields->setEnableNewButton(false);
        $tblFields->addClearField('dct_value_text');
        $tblFields->addClearField('dct_value_field');
        $tblFields->addClearField('dct_text_text');
        $tblFields->addClearField('dct_text_field');
        # Value Field
        $valFields = $this->Field->getSingleSelect('systemTable', 'dct_value_text', $this->getStringParameter('dct_value_text'), 'loadFieldsTable');
        $valFields->setHiddenField('dct_value_field', $this->getStringParameter('dct_value_field'));
        $valFields->addParameterById('table_name', 'dct_table', Trans::getWord('tableReference'));
        $valFields->setEnableNewButton(false);
        $valFields->setEnableDetailButton(false);

        $textFields = $this->Field->getSingleSelect('systemTable', 'dct_text_text', $this->getStringParameter('dct_text_text'), 'loadFieldsTable');
        $textFields->setHiddenField('dct_text_field', $this->getStringParameter('dct_text_field'));
        $textFields->addParameterById('table_name', 'dct_table', Trans::getWord('tableReference'));
        $textFields->setEnableNewButton(false);
        $textFields->setEnableDetailButton(false);

        $codeField = $this->Field->getText('dct_code', $this->getStringParameter('dct_code'));
        if ($this->isUpdate()) {
            $codeField->setReadOnly();
        }

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('group'), $dcgFields, true);
        $fieldSet->addField(Trans::getWord('code'), $codeField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('dct_description', $this->getStringParameter('dct_description')), true);
        $fieldSet->addField(Trans::getWord('tableReference'), $tblFields);
        $fieldSet->addField(Trans::getWord('valueField'), $valFields);
        $fieldSet->addField(Trans::getWord('textField'), $textFields);
        $fieldSet->addField(Trans::getWord('master'), $this->Field->getYesNo('dct_master', $this->getStringParameter('dct_master')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('dct_active', $this->getStringParameter('dct_active')));
        }

        # Create a portlet box.
        $portlet = new Portlet('DctGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
