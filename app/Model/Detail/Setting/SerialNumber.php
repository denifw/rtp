<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Setting;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Setting\SerialNumberDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

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
        parent::__construct(get_class($this), 'serialNumber', 'sn_id');
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
            'sn_ss_id' => $this->User->getSsId(),
            'sn_sc_id' => $this->getIntParameter('sn_sc_id'),
            'sn_of_id' => $this->getIntParameter('sn_of_id'),
            'sn_relation' => $this->getStringParameter('sn_relation', 'N'),
            'sn_srv_id' => $this->getIntParameter('sn_srv_id'),
            'sn_srt_id' => $this->getIntParameter('sn_srt_id'),
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
                'sn_sc_id' => $this->getIntParameter('sn_sc_id'),
                'sn_of_id' => $this->getIntParameter('sn_of_id'),
                'sn_relation' => $this->getStringParameter('sn_relation'),
                'sn_srv_id' => $this->getIntParameter('sn_srv_id'),
                'sn_srt_id' => $this->getIntParameter('sn_srt_id'),
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
        return SerialNumberDao::getByReferenceAndSystemSetting($this->getDetailReferenceValue(), $this->User->getSsId());

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
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', [
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
                'sn_ss_id' => $this->User->getSsId(),
                'sn_of_id' => $this->getIntParameter('sn_of_id'),
                'sn_srv_id' => $this->getIntParameter('sn_srv_id'),
                'sn_srt_id' => $this->getIntParameter('sn_srt_id'),
                'sn_active' => 'Y',
            ]);
            $this->Validation->checkUnique('sn_prefix', 'serial_number', [
                'sn_id' => $this->getDetailReferenceValue(),
            ], [
                'sn_ss_id' => $this->User->getSsId(),
                'sn_sc_id' => $this->getIntParameter('sn_sc_id'),
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
        # Serial Code field
        $serialCodeField = $this->Field->getSingleSelect('serialCode', 'sn_serial_code', $this->getStringParameter('sn_serial_code'));
        $serialCodeField->setHiddenField('sn_sc_id', $this->getIntParameter('sn_sc_id'));
        $serialCodeField->setEnableDetailButton(false);
        $serialCodeField->setEnableNewButton(false);

        $officeField = $this->Field->getSelect('sn_of_id', $this->getIntParameter('sn_of_id'));
        $officeField->addOptions(OfficeDao::loadActiveDataByRelation($this->User->getRelId()), 'of_name', 'of_id');
        $officeField->setPleaseSelect();

        # Service Field
        $systemServiceField = $this->Field->getSingleSelect('service', 'sn_service', $this->getStringParameter('sn_service'));
        $systemServiceField->setHiddenField('sn_srv_id', $this->getIntParameter('sn_srv_id'));
        $systemServiceField->addParameter('ssr_ss_id', $this->User->getSsId());
        $systemServiceField->setEnableNewButton(false);
        $systemServiceField->setEnableDetailButton(false);

        # Service Field
        $serviceTermField = $this->Field->getSingleSelect('serviceTerm', 'sn_service_term', $this->getStringParameter('sn_service_term'));
        $serviceTermField->setHiddenField('sn_srt_id', $this->getIntParameter('sn_srt_id'));
        $serviceTermField->addParameterById('srt_srv_id', 'sn_srv_id', Trans::getWord('service'));
        $serviceTermField->setEnableNewButton(false);
        $serviceTermField->setEnableDetailButton(false);

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
        $fieldSet->addField(Trans::getWord('serialCode'), $serialCodeField, true);
        $fieldSet->addField(Trans::getWord('service'), $systemServiceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);
        $fieldSet->addField(Trans::getWord('office'), $officeField);
        $fieldSet->addField(Trans::getWord('prefix'), $prefixField);
        $fieldSet->addField(Trans::getWord('postfix'), $postfixField);
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getYesNo('sn_relation', $this->getStringParameter('sn_relation')));
        $fieldSet->addField(Trans::getWord('yearly'), $yearlyField);
        $fieldSet->addField(Trans::getWord('monthly'), $monthlyField);
        $fieldSet->addField(Trans::getWord('length'), $lengthField);
        $fieldSet->addField(Trans::getWord('separator'), $separatorField);
        $fieldSet->addField(Trans::getWord('format'), $formatField, true);

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
