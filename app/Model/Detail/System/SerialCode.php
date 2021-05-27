<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Setting\SerialNumberDao;
use App\Model\Dao\System\SerialCodeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\SystemSettingDao;

/**
 * Class to handle the creation of detail SerialCode page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialCode extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serialCode', 'sc_id');
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
            'sc_code' => $this->getStringParameter('sc_code'),
            'sc_description' => $this->getStringParameter('sc_description'),
            'sc_active' => $this->getStringParameter('sc_active', 'Y'),
        ];
        $scDao = new SerialCodeDao();
        $scDao->doInsertTransaction($colVal);
        $scId = $scDao->getLastInsertId();
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('ss_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ss_active', 'Y');
        $data = SystemSettingDao::loadAllData($wheres);
        $snDao = new SerialNumberDao();
        foreach ($data as $row) {
            $snColVal = [
                'sn_ss_id' => $row['ss_id'],
                'sn_sc_id' => $scId,
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
            $snDao->doInsertTransaction($snColVal);
        }
        return $scId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'sc_code' => $this->getStringParameter('sc_code'),
            'sc_description' => $this->getStringParameter('sc_description'),
            'sc_active' => $this->getStringParameter('sc_active', 'Y'),
        ];
        $scDao = new SerialCodeDao();
        $scDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SerialCodeDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getSerialNumberPortlet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('sc_code', 2, 125);
        $this->Validation->checkRequire('sc_description', 2, 255);
        $this->Validation->checkUnique('sc_code', 'serial_code', [
            'sc_id' => $this->getDetailReferenceValue()
        ]);
        if ($this->isInsert() === true) {
            $this->Validation->checkRequire('sn_format');
            $this->Validation->checkMaxLength('sn_separator', 1);
            $this->Validation->checkMaxLength('sn_prefix', 10);
            $this->Validation->checkMaxLength('sn_postfix', 10);
            if ($this->isValidParameter('sn_length') === true) {
                $this->Validation->checkInt('sn_length');
            }
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

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);

        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('sc_code', $this->getStringParameter('sc_code')));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sc_description', $this->getStringParameter('sc_description')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('sc_active', $this->getStringParameter('sc_active')));
        }

        # Create a portlet box.
        $portlet = new Portlet('ScGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getSerialNumberPortlet(): Portlet
    {
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
        $fieldSet->addField(Trans::getWord('office'), $officeField);
        $fieldSet->addField(Trans::getWord('service'), $systemServiceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);
        $fieldSet->addField(Trans::getWord('prefix'), $prefixField);
        $fieldSet->addField(Trans::getWord('separator'), $separatorField);
        $fieldSet->addField(Trans::getWord('postfix'), $postfixField);
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getYesNo('sn_relation', $this->getStringParameter('sn_relation')));
        $fieldSet->addField(Trans::getWord('yearly'), $yearlyField);
        $fieldSet->addField(Trans::getWord('monthly'), $monthlyField);
        $fieldSet->addField(Trans::getWord('length'), $lengthField);
        $fieldSet->addField(Trans::getWord('format'), $formatField, true);

        # Create a portlet box.
        $portlet = new Portlet('SnGeneralPtl', Trans::getWord('serialNumber'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

}
