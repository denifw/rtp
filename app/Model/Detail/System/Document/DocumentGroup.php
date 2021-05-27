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
use App\Model\Dao\System\Document\DocumentGroupDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail DocumentGroup page
 *
 * @package    app
 * @subpackage Model\Detail\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentGroup extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentGroup', 'dcg_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $code = $this->getStringParameter('dcg_code');
        $code = mb_strtolower(StringFormatter::replaceSpecialCharacter($code));
        $colVal = [
            'dcg_code' => $code,
            'dcg_description' => $this->getStringParameter('dcg_description'),
            'dcg_table' => $this->getStringParameter('dcg_table'),
            'dcg_value_field' => $this->getStringParameter('dcg_value_field'),
            'dcg_text_field' => $this->getStringParameter('dcg_text_field'),
            'dcg_active' => $this->getStringParameter('dcg_active', 'Y'),
        ];
        $dcgDao = new DocumentGroupDao();
        $dcgDao->doInsertTransaction($colVal);

        return $dcgDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dcg_code' => $this->getStringParameter('dcg_code'),
            'dcg_description' => $this->getStringParameter('dcg_description'),
            'dcg_table' => $this->getStringParameter('dcg_table'),
            'dcg_value_field' => $this->getStringParameter('dcg_value_field'),
            'dcg_text_field' => $this->getStringParameter('dcg_text_field'),
            'dcg_active' => $this->getStringParameter('dcg_active', 'Y'),
        ];
        $dcgDao = new DocumentGroupDao();
        $dcgDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DocumentGroupDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('dcg_code', 2, 125);
        $this->Validation->checkRequire('dcg_description', 2, 255);
        $this->Validation->checkUnique('dcg_code', 'document_group', [
            'dcg_id' => $this->getDetailReferenceValue()
        ]);
        $this->Validation->checkRequire('dcg_table');
        $this->Validation->checkRequire('dcg_value_field');
        $this->Validation->checkRequire('dcg_text_field');
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        if ($this->isValidParameter('dcg_table') === true) {
            $this->setParameter('dcg_table_text', $this->getStringParameter('dcg_table'));
        }
        if ($this->isValidParameter('dcg_value_field') === true) {
            $this->setParameter('dcg_value_text', $this->getStringParameter('dcg_value_field'));
        }
        if ($this->isValidParameter('dcg_text_field') === true) {
            $this->setParameter('dcg_text_text', $this->getStringParameter('dcg_text_field'));
        }
        # Create Fields.
        $tblFields = $this->Field->getSingleSelect('systemTable', 'dcg_table_text', $this->getStringParameter('dcg_table_text'));
        $tblFields->setHiddenField('dcg_table', $this->getStringParameter('dcg_table'));
        $tblFields->setEnableNewButton(false);
        $tblFields->setEnableNewButton(false);
        $tblFields->addClearField('dcg_value_text');
        $tblFields->addClearField('dcg_value_field');
        $tblFields->addClearField('dcg_text_text');
        $tblFields->addClearField('dcg_text_field');
        # Value Field
        $valFields = $this->Field->getSingleSelect('systemTable', 'dcg_value_text', $this->getStringParameter('dcg_value_text'), 'loadFieldsTable');
        $valFields->setHiddenField('dcg_value_field', $this->getStringParameter('dcg_value_field'));
        $valFields->addParameterById('table_name', 'dcg_table', Trans::getWord('tableReference'));
        $valFields->setEnableNewButton(false);
        $valFields->setEnableDetailButton(false);

        $textFields = $this->Field->getSingleSelect('systemTable', 'dcg_text_text', $this->getStringParameter('dcg_text_text'), 'loadFieldsTable');
        $textFields->setHiddenField('dcg_text_field', $this->getStringParameter('dcg_text_field'));
        $textFields->addParameterById('table_name', 'dcg_table', Trans::getWord('tableReference'));
        $textFields->setEnableNewButton(false);
        $textFields->setEnableDetailButton(false);

        $codeField = $this->Field->getText('dcg_code', $this->getStringParameter('dcg_code'));
        if ($this->isUpdate()) {
            $codeField->setReadOnly();
        }

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('code'), $codeField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('dcg_description', $this->getStringParameter('dcg_description')), true);
        $fieldSet->addField(Trans::getWord('tableReference'), $tblFields, true);
        $fieldSet->addField(Trans::getWord('valueField'), $valFields, true);
        $fieldSet->addField(Trans::getWord('textField'), $textFields, true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('dcg_active', $this->getStringParameter('dcg_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('dcgGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
