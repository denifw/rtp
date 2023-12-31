<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Access;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Access\SerialNumberDao;

/**
 * Class to handle the creation of detail SerialNumber page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialNumber extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sn', 'sn_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'sn_ss_id' => $this->getStringParameter('sn_ss_id'),
            'sn_sc_id' => $this->getStringParameter('sn_sc_id'),
            'sn_of_id' => $this->getStringParameter('sn_of_id'),
            'sn_relation' => $this->getStringParameter('sn_relation', 'N'),
            'sn_prefix' => $this->getStringParameter('sn_prefix'),
            'sn_separator' => $this->getStringParameter('sn_separator', '-'),
            'sn_postfix' => $this->getStringParameter('sn_postfix'),
            'sn_yearly' => $this->getStringParameter('sn_yearly', 'N'),
            'sn_monthly' => $this->getStringParameter('sn_monthly', 'N'),
            'sn_length' => $this->getIntParameter('sn_length', 3),
            'sn_increment' => $this->getIntParameter('sn_increment', 1),
            'sn_format' => $this->getStringParameter('sn_format'),
            'sn_active' => $this->getStringParameter('sn_active', 'Y'),
        ];
        $snDao = new SerialNumberDao();
        $snDao->doInsertTransaction($colVal);

        return $snDao->getLastInsertId();
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
                'sn_sc_id' => $this->getStringParameter('sn_sc_id'),
                'sn_of_id' => $this->getStringParameter('sn_of_id'),
                'sn_relation' => $this->getStringParameter('sn_relation'),
                'sn_prefix' => $this->getStringParameter('sn_prefix'),
                'sn_separator' => $this->getStringParameter('sn_separator', '-'),
                'sn_postfix' => $this->getStringParameter('sn_postfix'),
                'sn_yearly' => $this->getStringParameter('sn_yearly'),
                'sn_monthly' => $this->getStringParameter('sn_monthly'),
                'sn_length' => $this->getIntParameter('sn_length', 3),
                'sn_increment' => $this->getIntParameter('sn_increment', 1),
                'sn_format' => $this->getStringParameter('sn_format'),
                'sn_active' => $this->getStringParameter('sn_active', 'Y'),
            ];
            $snDao = new SerialNumberDao();
            $snDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->isDeleteAction() === true) {
            $snDao = new SerialNumberDao();
            $snDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SerialNumberDao::getByReference($this->getDetailReferenceValue());

    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isValidParameter('sn_deleted_on') === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', '', [
                'user' => $this->getStringParameter('sn_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('sn_deleted_on')),
                'reason' => $this->getStringParameter('sn_deleted_reason')
            ]));
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
            $this->Validation->checkRequire('sn_ss_id');
            $this->Validation->checkRequire('sn_sc_id');
            $this->Validation->checkRequire('sn_format');
            $this->Validation->checkMaxLength('sn_separator', 1);
            $this->Validation->checkMaxLength('sn_prefix', 10);
            $this->Validation->checkMaxLength('sn_postfix', 10);
            if ($this->isValidParameter('sn_length') === true) {
                $this->Validation->checkInt('sn_length');
            }
            $this->Validation->checkUnique('sn_sc_id', 'serial_number', [
                'sn_id' => $this->getDetailReferenceValue(),
            ], [
                'sn_ss_id' => $this->getStringParameter('sn_ss_id'),
                'sn_of_id' => $this->getStringParameter('sn_of_id'),
                'sn_active' => 'Y',
            ]);
            $this->Validation->checkUnique('sn_prefix', 'serial_number', [
                'sn_id' => $this->getDetailReferenceValue(),
            ], [
                'sn_ss_id' => $this->getStringParameter('sn_ss_id'),
                'sn_sc_id' => $this->getStringParameter('sn_sc_id'),
                'sn_postfix' => $this->getStringParameter('sn_postfix'),
                'sn_active' => 'Y',
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
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        # System Settings
        $ssField = $this->Field->getSingleSelect('ss', 'sn_system', $this->getStringParameter('sn_system'));
        $ssField->setHiddenField('sn_ss_id', $this->getStringParameter('sn_ss_id'));
        $ssField->setEnableDetailButton(false);
        $ssField->setEnableNewButton(false);
        $ssField->setAutoCompleteFields([
            'sn_rel_id' => 'ss_rel_id'
        ]);
        # Serial Code field
        $serialCodeField = $this->Field->getSingleSelect('sc', 'sn_serial_code', $this->getStringParameter('sn_serial_code'));
        $serialCodeField->setHiddenField('sn_sc_id', $this->getStringParameter('sn_sc_id'));
        $serialCodeField->setEnableDetailButton(false);
        $serialCodeField->setEnableNewButton(false);
        # Office field
        $officeField = $this->Field->getSingleSelect('of', 'sn_office', $this->getStringParameter('sn_office'));
        $officeField->setHiddenField('sn_of_id', $this->getStringParameter('sn_of_id'));
        $officeField->addParameterById('ss_id', 'sn_ss_id', Trans::getWord('systemName'));
        $officeField->addOptionalParameterById('of_rel_id', 'sn_rel_id');
        $officeField->setEnableDetailButton(false);
        $officeField->setEnableNewButton(false);
        # Prefix Field
        $prefixField = $this->Field->getText('sn_prefix', $this->getStringParameter('sn_prefix'));
        # Separator Field
        $separatorField = $this->Field->getText('sn_separator', $this->getStringParameter('sn_separator', '-'));
        # Postfix Field
        $postfixField = $this->Field->getText('sn_postfix', $this->getStringParameter('sn_postfix'));
        # Length Field
        $lengthField = $this->Field->getText('sn_length', $this->getIntParameter('sn_length', 3));
        # yearly Field
        $yearlyField = $this->Field->getYesNo('sn_yearly', $this->getStringParameter('sn_yearly'));
        # monthly Field
        $monthlyField = $this->Field->getYesNo('sn_monthly', $this->getStringParameter('sn_monthly'));

        $formatField = $this->Field->getSelect('sn_format', $this->getStringParameter('sn_format'));
        $formatField->addOptions([
            ['value' => 'A', 'text' => 'PRE-REL-NUMBER-POST'],
            ['value' => 'B', 'text' => 'PRE-NUMBER-POST-REL-MONTH-YEAR'],
        ]);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $fieldSet->addField(Trans::getWord('systemName'), $ssField, true);
        $fieldSet->addField(Trans::getWord('serialCode'), $serialCodeField, true);
        $fieldSet->addField(Trans::getWord('office'), $officeField);
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getYesNo('sn_relation', $this->getStringParameter('sn_relation')));
        $fieldSet->addField(Trans::getWord('yearly'), $yearlyField);
        $fieldSet->addField(Trans::getWord('monthly'), $monthlyField);
        $fieldSet->addField(Trans::getWord('prefix'), $prefixField);
        $fieldSet->addField(Trans::getWord('postfix'), $postfixField);
        $fieldSet->addField(Trans::getWord('length'), $lengthField);
        $fieldSet->addField(Trans::getWord('separator'), $separatorField);
        $fieldSet->addField(Trans::getWord('format'), $formatField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('sn_rel_id', $this->getStringParameter('sn_rel_id')));

        # Create a portlet box.
        $portlet = new Portlet('SnGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $isDeleted = $this->isValidParameter('sn_deleted_on');
        if ($isDeleted === true) {
            $this->setDisableUpdate();
        } else {
            $this->setEnableDeleteButton();
        }
        parent::loadDefaultButton();
    }
}
