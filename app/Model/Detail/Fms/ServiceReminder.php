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
use App\Model\Dao\Fms\ServiceOrderDao;
use App\Model\Dao\Fms\ServiceReminderDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail ServiceReminder page
 *
 * @package    app
 * @subpackage Model\Detail\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class ServiceReminder extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'serviceReminder', 'svrm_id');
        if ($this->isValidParameter('svrm_time_interval_period') === false) {
            $this->setParameter('svrm_time_interval_period', 'Months');
        }
        if ($this->isValidParameter('svrm_time_threshold_period') === false) {
            $this->setParameter('svrm_time_threshold_period', 'Weeks');
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
        $nextDue = null;
        $nextThreshold = null;
        if ($this->isValidParameter('svrm_time_interval')) {
            $wheres[] = '(eq.eq_id = ' . $this->getIntParameter('svrm_eq_id') . ')';
            $wheres[] = '(svt.svt_id = ' . $this->getIntParameter('svrm_svt_id') . ')';
            $wheres[] = '(svo.svo_start_service_date IS NOT NULL)';
            $svoData = ServiceOrderDao::getLastServiceOrder($wheres);
            if (empty($svoData) === false) {
                # Next due date
                $nextDueDateModify = DateTimeParser::createDateTime($svoData['svo_start_service_date']);
                $nextDueDateModify->modify('+' . $this->getIntParameter('svrm_time_interval') . ' ' . $this->getStringParameter('svrm_time_interval_period'));
                $nextDue = $nextDueDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextDue);
                $nextThresholdDateModify->modify('-' . $this->getIntParameter('svrm_time_threshold') . ' ' . $this->getStringParameter('svrm_time_threshold_period'));
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
            } else {
                $nextDueDateModify = DateTimeParser::createDateTime(date('Y-m-d'));
                $nextDueDateModify->modify('+' . $this->getIntParameter('svrm_time_interval') . ' ' . $this->getStringParameter('svrm_time_interval_period'));
                $nextDue = $nextDueDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextDue);
                $nextThresholdDateModify->modify('-' . $this->getIntParameter('svrm_time_threshold') . ' ' . $this->getStringParameter('svrm_time_threshold_period'));
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
            }
        }
        $colVal = [
            'svrm_ss_id' => $this->User->getSsId(),
            'svrm_eq_id' => $this->getIntParameter('svrm_eq_id'),
            'svrm_svt_id' => $this->getIntParameter('svrm_svt_id'),
            'svrm_meter_interval' => $this->getIntParameter('svrm_meter_interval'),
            'svrm_time_interval' => $this->getIntParameter('svrm_time_interval'),
            'svrm_time_interval_period' => $this->getStringParameter('svrm_time_interval_period'),
            'svrm_meter_threshold' => $this->getIntParameter('svrm_meter_threshold'),
            'svrm_time_threshold' => $this->getIntParameter('svrm_time_threshold'),
            'svrm_time_threshold_period' => $this->getStringParameter('svrm_time_threshold_period'),
            'svrm_next_due_date' => $nextDue,
            'svrm_next_due_date_threshold' => $nextThreshold,
            'svrm_remark' => $this->getStringParameter('svrm_remark'),
        ];
        $svrmDao = new ServiceReminderDao();
        $svrmDao->doInsertTransaction($colVal);

        return $svrmDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $nextDue = null;
        $nextThreshold = null;
        if ($this->isValidParameter('svrm_time_interval')) {
            $wheres[] = '(eq.eq_id = ' . $this->getIntParameter('svrm_eq_id') . ')';
            $wheres[] = '(svt.svt_id = ' . $this->getIntParameter('svrm_svt_id') . ')';
            $wheres[] = '(svo.svo_start_service_date IS NOT NULL)';
            $svoData = ServiceOrderDao::getLastServiceOrder($wheres);
            if (empty($svoData) === false) {
                # Next due date
                $nextDueDateModify = DateTimeParser::createDateTime($svoData['svo_start_service_date']);
                $nextDueDateModify->modify('+' . $this->getIntParameter('svrm_time_interval') . ' ' . $this->getStringParameter('svrm_time_interval_period'));
                $nextDue = $nextDueDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextDue);
                $nextThresholdDateModify->modify('-' . $this->getIntParameter('svrm_time_threshold') . ' ' . $this->getStringParameter('svrm_time_threshold_period'));
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
            } else {
                $nextDueDateModify = DateTimeParser::createDateTime(date('Y-m-d'));
                $nextDueDateModify->modify('+' . $this->getIntParameter('svrm_time_interval') . ' ' . $this->getStringParameter('svrm_time_interval_period'));
                $nextDue = $nextDueDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextDue);
                $nextThresholdDateModify->modify('-' . $this->getIntParameter('svrm_time_threshold') . ' ' . $this->getStringParameter('svrm_time_threshold_period'));
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
            }
        }
        $colVal = [
            'svrm_eq_id' => $this->getIntParameter('svrm_eq_id'),
            'svrm_svt_id' => $this->getIntParameter('svrm_svt_id'),
            'svrm_meter_interval' => $this->getIntParameter('svrm_meter_interval'),
            'svrm_time_interval' => $this->getIntParameter('svrm_time_interval'),
            'svrm_time_interval_period' => $this->getStringParameter('svrm_time_interval_period'),
            'svrm_meter_threshold' => $this->getIntParameter('svrm_meter_threshold'),
            'svrm_time_threshold' => $this->getIntParameter('svrm_time_threshold'),
            'svrm_time_threshold_period' => $this->getStringParameter('svrm_time_threshold_period'),
            'svrm_next_due_date' => $nextDue,
            'svrm_next_due_date_threshold' => $nextThreshold,
            'svrm_remark' => $this->getStringParameter('svrm_remark'),
        ];
        $svrmDao = new ServiceReminderDao();

        $svrmDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ServiceReminderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('svrm_eq_id');
        $this->Validation->checkRequire('svrm_svt_id');
        if ($this->isValidParameter('svrm_meter_interval') === false &&
            $this->isValidParameter('svrm_meter_threshold') === false &&
            $this->isValidParameter('svrm_time_interval') === false &&
            $this->isValidParameter('svrm_time_threshold') === false) {
            $this->Validation->checkInt('svrm_meter_interval', 1);
            $this->Validation->checkInt('svrm_meter_threshold', 1);
            $this->Validation->checkInt('svrm_time_interval', 1);
            $this->Validation->checkInt('svrm_time_threshold', 1);
        }
        if ($this->isValidParameter('svrm_meter_interval')) {
            $this->Validation->checkInt('svrm_meter_interval', 1);
            $this->Validation->checkInt('svrm_meter_threshold', 1);
        }
        if ($this->isValidParameter('svrm_time_interval')) {
            $this->Validation->checkInt('svrm_time_interval', 1);
            $this->Validation->checkInt('svrm_time_threshold', 1);
            $this->Validation->checkRequire('svrm_time_interval_period');
            $this->Validation->checkRequire('svrm_time_threshold_period');
        }
        $this->Validation->checkUnique('svrm_svt_id', 'service_reminder', [
            'svrm_id' => $this->getDetailReferenceValue()
        ], [
            'svrm_ss_id' => $this->User->getSsId(),
            'svrm_eq_id' => $this->getIntParameter('svrm_eq_id')
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
        $eqField = $this->Field->getSingleSelect('equipment', 'svrm_eq_name', $this->getStringParameter('svrm_eq_name'), 'loadAutoCompleteData');
        $eqField->setHiddenField('svrm_eq_id', $this->getIntParameter('svrm_eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setDetailReferenceCode('eq_id');
        $eqField->setAutoCompleteFields([
            'svrm_meter_info' => 'eq_meter_text',
        ]);
        $svtField = $this->Field->getSingleSelect('serviceTask', 'svrm_svt_name', $this->getStringParameter('svrm_svt_name'), 'loadAutoCompleteDataForServiceReminder');
        $svtField->setHiddenField('svrm_svt_id', $this->getIntParameter('svrm_svt_id'));
        $svtField->addParameter('svt_ss_id', $this->User->getSsId());
        $svtField->setDetailReferenceCode('svt_id');
        $svtField->setAutoCompleteFields([
            'svrm_last_service' => 'svt_service',
        ]);
        $svtField->addParameterById('svt_eq_id', 'svrm_eq_id', Trans::getFmsWord('equipment'));
        $lastMeterField = $this->Field->getText('svrm_meter_info', $this->getStringParameter('svrm_meter_info'));
        $lastMeterField->setReadOnly();
        $lastServiceField = $this->Field->getText('svrm_last_service', $this->getStringParameter('svrm_last_service'));
        $lastServiceField->setReadOnly();
        $meterIntervalField = $this->Field->getNumber('svrm_meter_interval', $this->getIntParameter('svrm_meter_interval'));
        $meterThresholdField = $this->Field->getNumber('svrm_meter_threshold', $this->getIntParameter('svrm_meter_threshold'));
        $timeIntervalField = $this->Field->getNumber('svrm_time_interval', $this->getIntParameter('svrm_time_interval'));
        $timeThresholdField = $this->Field->getNumber('svrm_time_threshold', $this->getIntParameter('svrm_time_threshold'));
        $timeIntervalPeriod = $this->Field->getSelect('svrm_time_interval_period', $this->getStringParameter('svrm_time_interval_period'));
        $timeIntervalPeriod->setPleaseSelect(false);
        $timeIntervalPeriod->addOptions($timePeriodeData);
        $timeThresholdPeriod = $this->Field->getSelect('svrm_time_threshold_period', $this->getStringParameter('svrm_time_threshold_period'));
        $timeThresholdPeriod->setPleaseSelect(false);
        $timeThresholdPeriod->addOptions($timePeriodeData);
        # Set read only fields
        if ($this->isUpdate()) {
            $eqField->setReadOnly();
        }
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('equipment'), $eqField, true);
        $fieldSet->addField(Trans::getFmsWord('meterInfo'), $lastMeterField);
        $fieldSet->addField(Trans::getFmsWord('serviceTask'), $svtField, true);
        $fieldSet->addField(Trans::getFmsWord('lastService'), $lastServiceField);
        $fieldSet->addField(Trans::getFmsWord('meterInterval'), $meterIntervalField);
        $fieldSet->addField(Trans::getFmsWord('meterThreshold'), $meterThresholdField);
        $fieldSet->addField(Trans::getFmsWord('timeInterval'), $timeIntervalField);
        $fieldSet->addField('-', $timeIntervalPeriod);
        $fieldSet->addField(Trans::getFmsWord('timeThreshold'), $timeThresholdField);
        $fieldSet->addField('-', $timeThresholdPeriod);
        $fieldSet->addField(Trans::getFmsWord('remark'), $this->Field->getTextArea('svrm_remark', $this->getStringParameter('svrm_remark')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 12, 12);

        return $portlet;
    }

}
