<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Fms;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Model\Dao\Fms\RenewalFulfillmentDao;
use App\Model\Dao\Fms\RenewalReminderDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail RenewalReminder page
 *
 * @package    app
 * @subpackage Model\Detail\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class RenewalReminder extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'renewalReminder', 'rnrm_id');
        if ($this->isValidParameter('rnrm_interval_period') === false) {
            $this->setParameter('rnrm_interval_period', 'Years');
        }
        if ($this->isValidParameter('rnrm_threshold_period') === false) {
            $this->setParameter('rnrm_threshold_period', 'Weeks');
        }
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $expiryThresholdDateMod = DateTimeParser::createDateTime($this->getStringParameter('rnrm_expiry_date'));
        $expiryThresholdDateMod->modify('-' . $this->getIntParameter('rnrm_threshold') . ' ' . $this->getStringParameter('rnrm_threshold_period'));
        $expiryThresholdDate = $expiryThresholdDateMod->format('Y-m-d');
        $colVal = [
            'rnrm_ss_id' => $this->User->getSsId(),
            'rnrm_eq_id' => $this->getIntParameter('rnrm_eq_id'),
            'rnrm_rnt_id' => $this->getIntParameter('rnrm_rnt_id'),
            'rnrm_interval' => $this->getIntParameter('rnrm_interval'),
            'rnrm_interval_period' => $this->getStringParameter('rnrm_interval_period'),
            'rnrm_threshold' => $this->getIntParameter('rnrm_threshold'),
            'rnrm_threshold_period' => $this->getStringParameter('rnrm_threshold_period'),
            'rnrm_expiry_date' => $this->getStringParameter('rnrm_expiry_date'),
            'rnrm_expiry_threshold_date' => $expiryThresholdDate,
            'rnrm_remark' => $this->getStringParameter('rnrm_remark')
        ];
        $rnrmDao = new RenewalReminderDao();
        $rnrmDao->doInsertTransaction($colVal);

        return $rnrmDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $expiryThresholdDateMod = DateTimeParser::createDateTime($this->getStringParameter('rnrm_expiry_date'));
        $expiryThresholdDateMod->modify('-' . $this->getIntParameter('rnrm_threshold') . ' ' . $this->getStringParameter('rnrm_threshold_period'));
        $expiryThresholdDate = $expiryThresholdDateMod->format('Y-m-d');
        $colVal = [
            'rnrm_eq_id' => $this->getIntParameter('rnrm_eq_id'),
            'rnrm_rnt_id' => $this->getIntParameter('rnrm_rnt_id'),
            'rnrm_interval' => $this->getIntParameter('rnrm_interval'),
            'rnrm_interval_period' => $this->getStringParameter('rnrm_interval_period'),
            'rnrm_threshold' => $this->getIntParameter('rnrm_threshold'),
            'rnrm_threshold_period' => $this->getStringParameter('rnrm_threshold_period'),
            'rnrm_expiry_date' => $this->getStringParameter('rnrm_expiry_date'),
            'rnrm_expiry_threshold_date' => $expiryThresholdDate,
            'rnrm_remark' => $this->getStringParameter('rnrm_remark')
        ];
        $rnrmDao = new RenewalReminderDao();
        $rnrmDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return RenewalReminderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->Tab->addPortlet('general', $this->getRenewalFulfillmentFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('rnrm_eq_id');
        $this->Validation->checkRequire('rnrm_rnt_id');
        $this->Validation->checkRequire('rnrm_interval');
        $this->Validation->checkRequire('rnrm_interval_period');
        $this->Validation->checkRequire('rnrm_threshold');
        $this->Validation->checkRequire('rnrm_threshold_period');
        $this->Validation->checkRequire('rnrm_expiry_date');
        if ($this->isValidParameter('rnrm_remark')) {
            $this->Validation->checkRequire('rnrm_remark', 3, 255);
        }
        $this->Validation->checkUnique('rnrm_rnt_id', 'renewal_reminder', [
            'rnrm_id' => $this->getDetailReferenceValue()
        ], [
            'rnrm_ss_id' => $this->User->getSsId(),
            'rnrm_eq_id' => $this->getIntParameter('rnrm_eq_id')
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Period data
        $timePeriodeData = [
            [
                'text' => 'Days',
                'value' => 'Days'
            ],
            [
                'text' => 'Weeks',
                'value' => 'Weeks'
            ],
            [
                'text' => 'Months',
                'value' => 'Months'
            ],
            [
                'text' => 'Years',
                'value' => 'Years'
            ]
        ];
        # Create field
        $eqField = $this->Field->getSingleSelect('equipment', 'rnrm_eq_name', $this->getStringParameter('rnrm_eq_name'), 'loadAutoCompleteData');
        $eqField->setHiddenField('rnrm_eq_id', $this->getIntParameter('rnrm_eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setDetailReferenceCode('eq_id');
        $eqField->setAutoCompleteFields([
            'rnrm_meter_info' => 'eq_meter_text',
        ]);
        $rntField = $this->Field->getSingleSelect('renewalType', 'rnrm_rnt_name', $this->getStringParameter('rnrm_rnt_name'));
        $rntField->setHiddenField('rnrm_rnt_id', $this->getIntParameter('rnrm_rnt_id'));
        $rntField->addParameter('rnt_ss_id', $this->User->getSsId());
        $rntField->setDetailReferenceCode('rnt_id');
        $intervalField = $this->Field->getNumber('rnrm_interval', $this->getIntParameter('rnrm_interval'));
        $thresholdField = $this->Field->getNumber('rnrm_threshold', $this->getIntParameter('rnrm_threshold'));
        $intervalPeriodField = $this->Field->getSelect('rnrm_interval_period', $this->getStringParameter('rnrm_interval_period'));
        $intervalPeriodField->setPleaseSelect(false);
        $intervalPeriodField->addOptions($timePeriodeData);
        $thresholdPeriod = $this->Field->getSelect('rnrm_threshold_period', $this->getStringParameter('rnrm_threshold_period'));
        $thresholdPeriod->setPleaseSelect(false);
        $thresholdPeriod->addOptions($timePeriodeData);
        $expiryField = $this->Field->getCalendar('rnrm_expiry_date', $this->getStringParameter('rnrm_expiry_date'));
        # Set read only fields
        if ($this->isUpdate()) {
            $eqField->setReadOnly();
            if ($this->isValidParameter('rnrm_rnf_id')) {
                $rntField->setReadOnly();
            }
        }
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('equipment'), $eqField, true);
        $fieldSet->addField(Trans::getFmsWord('renewalType'), $rntField, true);
        $fieldSet->addField(Trans::getFmsWord('interval'), $intervalField, true);
        $fieldSet->addField('-', $intervalPeriodField, true);
        $fieldSet->addField(Trans::getFmsWord('threshold'), $thresholdField, true);
        $fieldSet->addField('-', $thresholdPeriod, true);
        $fieldSet->addField(Trans::getFmsWord('expiryDate'), $expiryField, true);
        $fieldSet->addField(Trans::getFmsWord('remark'), $this->Field->getTextArea('rnrm_remark', $this->getStringParameter('rnrm_remark')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', Trans::getFmsWord('renewalReminder'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 12, 12);

        return $portlet;
    }

    /**
     * Function to get the renewal implementation Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRenewalFulfillmentFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $table = new Table('rnfTbl');
        $table->setHeaderRow([
            'rnf_rno_number' => Trans::getFmsWord('renewalOrder'),
            'rnf_expiry_date' => Trans::getFmsWord('expiryDate'),
            'rnf_fulfillment_date' => Trans::getFmsWord('processDate'),
        ]);
        $wheres[] = '(rnf.rnf_rnrm_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnf.rnf_deleted_on IS NULL)';
        $orderList[] = 'rnf.rnf_created_on DESC';
        $tempData = RenewalFulfillmentDao::loadData($wheres, $orderList);
        $rniData = [];
        foreach ($tempData AS $row) {
            $row['rnf_expiry_date'] = DateTimeParser::format($row['rnf_expiry_date'], 'Y-m-d', 'd M Y');
            $row['rnf_fulfillment_date'] = DateTimeParser::format($row['rnf_fulfillment_date'], 'Y-m-d', 'd M Y');
            $row['rno_id'] = $row['rnf_rno_id'];
            $rniData[] = $row;
        }
        $table->addRows($rniData);
        $table->addColumnAttribute('rnf_expiry_date', 'style', 'text-align: center');
        $table->addColumnAttribute('rnf_fulfillment_date', 'style', 'text-align: center');
        $table->setViewActionByHyperlink('renewalOrder/view', ['rno_id']);
        # Create a portlet box.
        $portlet = new Portlet('rnfPtl', Trans::getFmsWord('renewalFulfillment'));
        $portlet->addTable($table);
        $portlet->setGridDimension(6, 12, 12);

        return $portlet;
    }


}
