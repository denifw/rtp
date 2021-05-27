<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Master;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneralEquipment;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Fms\EquipmentFuelDao;
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\Fms\RenewalOrderDao;
use App\Model\Dao\Fms\RenewalReminderDao;
use App\Model\Dao\Fms\ServiceOrderDao;
use App\Model\Dao\Fms\ServiceReminderDao;
use App\Model\Dao\Master\EquipmentDao;
use App\Frame\Formatter\Trans;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail Equipment page
 *
 * @package    app
 * @subpackage Model\Viewer\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class Equipment extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipment', 'eq_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateMeter') {
            $colVal = [
                'eqm_eq_id' => $this->getDetailReferenceValue(),
                'eqm_date' => $this->getStringParameter('eqm_date'),
                'eqm_meter' => $this->getFloatParameter('eqm_meter'),
                'eqm_source' => Trans::getFmsWord('manuallyEntered'),
            ];
            $eqmDao = new EquipmentMeterDao();
            if ($this->isValidParameter('eqm_id') === true) {
                $eqmDao->doUpdateTransaction($this->getIntParameter('eqm_id'), $colVal);
            } else {
                $eqmDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteMeter') {
            $eqmDao = new EquipmentMeterDao();
            $eqmDao->doDeleteTransaction($this->getIntParameter('eqm_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EquipmentDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addContent('general', $this->getWidget());
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getIdentityFieldSet());
        $this->Tab->addPortlet('general', $this->getCapacityFieldSet());
        $this->Tab->addPortlet('general', $this->getSpecificationFieldSet());
        $this->Tab->addPortlet('reminder', $this->getServiceRemindersFieldSet());
        $this->Tab->addPortlet('reminder', $this->getRenewalRemindersFieldSet());
        $this->Tab->addPortlet('serviceHistory', $this->getServiceHistoryFieldSet());
        $this->Tab->addPortlet('renewalHistory', $this->getRenewalHistoryFieldSet());
        $this->Tab->addPortlet('meterHistory', $this->getMeterHistoryFieldSet());
        $this->Tab->addPortlet('fuelHistory', $this->getFuelHistoryFieldSet());
        $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
        $this->overridePageTitle();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateMeter') {
            $this->Validation->checkRequire('eqm_date');
            $this->Validation->checkFloat('eqm_meter');
            if ($this->isValidParameter('eqm_date') && $this->isValidParameter('eqm_meter')) {
                $minMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($this->getDetailReferenceValue(), $this->getStringParameter('eqm_date'), 'min');
                $maxMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($this->getDetailReferenceValue(), $this->getStringParameter('eqm_date'), 'max');
                $this->Validation->checkFloat('eqm_meter', $minMeterData['eqm_meter'], $maxMeterData['eqm_meter']);
            }
        } elseif ($this->getFormAction() === 'doDeleteMeter') {
            $this->Validation->checkRequire('eqm_id_del');
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkRequire('doc_description', 3, 255);
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        }
    }

    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $fuelCost = $this->getFuelCost();
        $number = new NumberFormatter();
        $fuelCostWidget = new NumberGeneralEquipment();
        $data = [
            'title' => Trans::getFmsWord('fuel'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark-blue',
            'data' => [
                Trans::getFmsWord('fuelCost') => $number->doFormatCurrency($fuelCost['fuel_cost']),
                Trans::getFmsWord('totalFuel') => $fuelCost['fuel_consume'] . ' L'
            ],
        ];
        $fuelCostWidget->setData($data);
        $fuelCostWidget->setGridDimension(4);

        $serviceCost = $this->getServiceCost();
        $number = new NumberFormatter();
        $serviceCostWidget = new NumberGeneralEquipment();
        $data = [
            'title' => Trans::getFmsWord('service'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-teal-second',
            'data' => [
                Trans::getFmsWord('serviceCost') => $number->doFormatCurrency($serviceCost['svc_cost']),
                Trans::getFmsWord('totalService') => $serviceCost['svc_total_service'] . ' Times'
            ],
        ];
        $serviceCostWidget->setData($data);
        $serviceCostWidget->setGridDimension(4);

        $number = new NumberFormatter();
        $totalCostWidget = new NumberGeneralEquipment();
        $data = [
            'title' => Trans::getFmsWord('totalCost'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-second',
            'data' => [
                Trans::getFmsWord('totalCost') => $number->doFormatCurrency($fuelCost['fuel_cost'] + $serviceCost['svc_cost']),
            ],
        ];
        $totalCostWidget->setData($data);
        $totalCostWidget->setGridDimension(4);

        return $fuelCostWidget->createView() . $serviceCostWidget->createView() . $totalCostWidget->createView();
    }

    /**
     * Function to get fuel cost.
     *
     * @return array
     */
    private function getFuelCost(): array
    {
        $strWhere = 'WHERE eq.eq_id = ' . $this->getDetailReferenceValue();

        $query = 'SELECT eq.eq_id, eqf.eqf_cost, eqf.eqf_qty_fuel
                  FROM equipment AS eq LEFT OUTER JOIN
                       (SELECT eqf_eq_id, SUM(eqf_qty_fuel * eqf_cost) AS eqf_cost, SUM(eqf_qty_fuel) AS eqf_qty_fuel
                        FROM equipment_fuel
                        WHERE eqf_deleted_on IS NULL AND eqf_confirm_on IS NOT NULL
                        GROUP BY eqf_eq_id) AS eqf ON eqf.eqf_eq_id = eq.eq_id ' . $strWhere;
        $sqlResults = DB::select($query);
        $result = [];
        if (empty($sqlResults) === false) {
            $data = $this->loadDatabaseRow($query);
            if (empty($data[0]['eqf_cost']) === false) {
                $result['fuel_cost'] = $data[0]['eqf_cost'];
                $result['fuel_consume'] = $data[0]['eqf_qty_fuel'];
            } else {
                $result['fuel_cost'] = 0;
                $result['fuel_consume'] = 0;
            }
        } else {
            $result['fuel_cost'] = 0;
            $result['fuel_consume'] = 0;
        }

        return $result;
    }

    /**
     * Function to get service cost.
     *
     * @return array
     */
    private function getServiceCost(): array
    {
        $strWhere = 'WHERE eq.eq_id = ' . $this->getDetailReferenceValue();

        $query = 'SELECT eq.eq_id, SUM(svc.svc_total) AS svc_cost, COUNT(svo.svo_id) AS svc_total_service
                  FROM equipment AS eq INNER JOIN
                       service_order AS svo ON svo.svo_eq_id = eq.eq_id LEFT OUTER JOIN
                       service_order_cost AS svc ON svc.svc_svo_id = svo.svo_id AND svc.svc_deleted_on IS NULL '
            . $strWhere .
            'GROUP BY eq.eq_id';
        $sqlResults = DB::select($query);
        $result = [];
        if (empty($sqlResults) === false) {
            $data = $this->loadDatabaseRow($query);
            if (empty($data[0]['svc_cost']) === false) {
                $result['svc_cost'] = $data[0]['svc_cost'];
            } else {
                $result['svc_cost'] = 0;
            }
            if (empty($data[0]['svc_total_service']) === false) {
                $result['svc_total_service'] = $data[0]['svc_total_service'];
            } else {
                $result['svc_total_service'] = 0;
            }
        } else {
            $result['svc_cost'] = 0;
            $result['svc_total_service'] = 0;
        }

        return $result;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getFmsWord('equipment'),
                'value' => $this->getStringParameter('eq_name'),
            ],
            [
                'label' => Trans::getFmsWord('owner'),
                'value' => $this->getStringParameter('eq_owner'),
            ],
            [
                'label' => Trans::getFmsWord('ownershipType'),
                'value' => $this->getStringParameter('eq_owt_name'),
            ],
            [
                'label' => Trans::getFmsWord('manageBy'),
                'value' => $this->getStringParameter('eq_manage_by_name'),
            ],
            [
                'label' => Trans::getFmsWord('manager'),
                'value' => $this->getStringParameter('eq_manager_name'),
            ],
            [
                'label' => Trans::getFmsWord('status'),
                'value' => $this->getStringParameter('eq_eqs_name'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('EqGeneralPtl', Trans::getWord('general'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the identity Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getIdentityFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getFmsWord('licensePlate'),
                'value' => $this->getStringParameter('eq_license_plate'),
            ],
            [
                'label' => Trans::getFmsWord('machineNumber'),
                'value' => $this->getStringParameter('eq_machine_number'),
            ],
            [
                'label' => Trans::getFmsWord('chassisNumber'),
                'value' => $this->getStringParameter('eq_chassis_number'),
            ],
            [
                'label' => Trans::getFmsWord('bpkb'),
                'value' => $this->getStringParameter('eq_bpkb'),
            ],
            [
                'label' => Trans::getFmsWord('stnk'),
                'value' => $this->getStringParameter('eq_stnk'),
            ],
            [
                'label' => Trans::getFmsWord('keur'),
                'value' => $this->getStringParameter('eq_keur'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('EqIdentityPtl', Trans::getFmsWord('identity'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the capacity Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getCapacityFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getFmsWord('length'),
                'value' => $this->getFloatParameter('eq_lgh_capacity') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('width'),
                'value' => $this->getFloatParameter('eq_wdh_capacity') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('height'),
                'value' => $this->getFloatParameter('eq_hgh_capacity') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('weight'),
                'value' => $this->getFloatParameter('eq_wgh_capacity') . ' M',
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('EqCapacityPtl', Trans::getFmsWord('capacity'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the capacity Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getSpecificationFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getFmsWord('brand'),
                'value' => $this->getStringParameter('eq_br_name'),
            ],
            [
                'label' => Trans::getFmsWord('color'),
                'value' => $this->getStringParameter('eq_color'),
            ],
            [
                'label' => Trans::getFmsWord('builtYear'),
                'value' => $this->getIntParameter('eq_built_year'),
            ],
            [
                'label' => Trans::getFmsWord('engineCapacity'),
                'value' => $this->getIntParameter('eq_engine_capacity') . ' CC',
            ],
            [
                'label' => Trans::getFmsWord('fuelType'),
                'value' => $this->getStringParameter('eq_fuel_type'),
            ],
            [
                'label' => Trans::getFmsWord('fuelConsume') . ' (KM) Per Liter',
                'value' => $this->getFloatParameter('eq_fuel_consume') . ' KM',
            ],
            [
                'label' => Trans::getFmsWord('maxSpeed'),
                'value' => $this->getIntParameter('eq_max_speed') . ' KM',
            ],
            [
                'label' => Trans::getFmsWord('length'),
                'value' => $this->getFloatParameter('eq_length') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('width'),
                'value' => $this->getFloatParameter('eq_width') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('height'),
                'value' => $this->getFloatParameter('eq_height') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('weight'),
                'value' => $this->getFloatParameter('eq_weight') . ' M',
            ],
            [
                'label' => Trans::getFmsWord('volume'),
                'value' => $this->getFloatParameter('eq_volume') . ' M3',
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('EqSpecificationyPtl', Trans::getFmsWord('specification'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the service reminders Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getServiceRemindersFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqSrvRemindersPtl', Trans::getFmsWord('reminders'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvRemindersTbl');
        $table->setHeaderRow([
            'svrm_svt_name' => Trans::getFmsWord('task'),
            'svrm_interval' => Trans::getFmsWord('schedule'),
            'svrm_next_due_date' => Trans::getFmsWord('nextDueDate'),
            'svrm_status' => Trans::getFmsWord('status'),
            'svrm_last_completed' => Trans::getFmsWord('lastCompleted')
        ]);
        $wheres[] = '(svrm.svrm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svrm.svrm_deleted_on IS NULL )';
        $serviceData = $this->doPrepareServiceRemindersData(ServiceReminderDao::loadCompleteData($wheres));
        $table->addRows($serviceData);
        # Add special table attribute
        $table->addColumnAttribute('svrm_last_completed', 'style', 'text-align: center');
        $table->addColumnAttribute('svrm_status', 'style', 'text-align: center');
        $table->setUpdateActionByHyperlink('serviceReminder/detail', ['svrm_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareServiceRemindersData(array $data): array
    {
        $results = [];
        $numberFormatter = new NumberFormatter();
        foreach ($data as $row) {
            $interval = 'Every ';
            if (empty($row['svrm_time_interval']) === false) {
                $interval .= '<i style="color: #1a3a95" class="' . Icon::Calendar . '" ></i> ' . $row['svrm_time_interval'] . ' ' . $row['svrm_time_interval_period'];
                if (empty($row['svrm_meter_interval']) === false) {
                    $interval .= ' or ';
                }
            }
            if (empty($row['svrm_meter_interval']) === false) {
                $interval .= '<i style="color: #1a3a95" class="' . Icon::Tachometer . '" ></i> ' . $numberFormatter->doFormatFloat($row['svrm_meter_interval']) . ' ' . $row['eq_primary_meter'];
            }
            $row['svrm_interval'] = $interval;
            $row['svrm_last_completed'] = DateTimeParser::format($row['svo_start_service_date'], 'Y-m-d', 'd M Y') . ' <br> ' . $numberFormatter->doFormatFloat($row['svo_meter']) . ' ' . $row['eq_primary_meter'];
            $meterDueText = '';
            $timesDueText = '';
            $svrmNextDueDate = '';
            $meterStatus = '';
            $timesStatus = '';
            $svrmStatus = '';
            # Calculate meter remaining
            if (empty($row['svrm_meter_remaining']) === false && empty($row['eqm_meter']) === false) {
                if ($row['svrm_meter_remaining'] > 0) {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'] . ' From now';
                } elseif ($row['svrm_meter_remaining'] < 0) {
                    $meterDueText = $numberFormatter->doFormatFloat(abs($row['svrm_meter_remaining'])) . ' ' . $row['eq_primary_meter'] . ' Ago';
                } else {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'];
                }
                # Set service reminder status compare by meter remaining and threshold
                if ($row['svrm_meter_remaining'] >= 0) {
                    if ($row['svrm_meter_threshold'] >= $row['svrm_meter_remaining']) {
                        $meterStatus = Trans::getFmsWord('comingSoon');
                    }
                } elseif ($row['svrm_meter_remaining'] < 0) {
                    $meterStatus = Trans::getFmsWord('overDue');
                }
            }
            # Calculate times remaining
            if (empty($row['svrm_time_interval']) === false) {
                $now = DateTimeParser::createDateTime(date('Y-m-d'));
                $nextDueDate = DateTimeParser::createDateTime($row['svrm_next_due_date']);
                $dateDiff = DateTimeParser::different($now, $nextDueDate);
                $timesDiffAgg = '';
                if (empty($dateDiff['y']) === false) {
                    $timesDiffAgg .= $dateDiff['y'] . ' Years ';
                }
                if (empty($dateDiff['m']) === false) {
                    $timesDiffAgg .= $dateDiff['m'] . ' Months ';
                }
                if (empty($dateDiff['d']) === false) {
                    $timesDiffAgg .= $dateDiff['d'] . ' Days ';
                }
                if ($now > $nextDueDate) {
                    $timesDueText .= $timesDiffAgg . ' Ago<br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                } elseif ($now < $nextDueDate) {
                    $timesDueText .= $timesDiffAgg . ' From Now <br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                } else {
                    $timesDueText .= $timesDiffAgg . ' <br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                }
                # Set service reminder status compare by time remaining and threshold
                $dateThreshold = DateTimeParser::createDateTime($row['svrm_next_due_date_threshold']);
                if ($nextDueDate >= $now) {
                    if ($now >= $dateThreshold) {
                        $timesStatus = Trans::getFmsWord('comingSoon');
                    }
                } else {
                    $timesStatus = Trans::getFmsWord('overDue');
                }
            }
            # Aggerate meter and times due date
            if (empty($meterDueText) === false) {
                $svrmNextDueDate .= $meterDueText;
                if (empty($timesDueText) === false) {
                    $svrmNextDueDate .= '<br>';
                }
            } elseif (empty($timesDueText) === true) {
                if ($row['eq_primary_meter'] === 'km') {
                    $svrmNextDueDate .= 'Odometer not set';
                } elseif ($row['eq_primary_meter'] === 'hours') {
                    $svrmNextDueDate .= 'Hours meter not set';
                }

            }
            if (empty($timesDueText) === false) {
                $svrmNextDueDate .= $timesDueText;
            }
            if (empty($meterStatus) === false || empty($timesStatus) === false) {
                if ($meterStatus === 'Coming Soon' || $timesStatus === 'Coming Soon') {
                    $svrmStatus = new LabelWarning(Trans::getFmsWord('comingSoon'));
                } else {
                    $svrmStatus = new LabelDanger(Trans::getFmsWord('overDue'));
                }
            }
            $row['svrm_next_due_date'] = $svrmNextDueDate;
            $row['svrm_status'] = $svrmStatus;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to get the renewal reminders Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRenewalRemindersFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqRnRemindersPtl', Trans::getFmsWord('renewalReminder'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rnRemindersTbl');
        $table->setHeaderRow([
            'rnrm_rnt_name' => Trans::getFmsWord('renewalType'),
            'rnrm_expiry_date' => Trans::getFmsWord('expiryDate'),
            'rnrm_status' => Trans::getFmsWord('status'),
        ]);
        $wheres[] = '(rnrm.rnrm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnrm.rnrm_deleted_on IS NULL )';
        $renewalData = $this->doPrepareRenewalRemindersData(RenewalReminderDao::loadData($wheres));
        $table->addRows($renewalData);
        # Add special table attribute
        $table->addColumnAttribute('rnrm_status', 'style', 'text-align: center');
        $table->setUpdateActionByHyperlink('renewalReminder/detail', ['rnrm_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareRenewalRemindersData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $now = DateTimeParser::createDateTime(date('Y-m-d'));
            $expiryDate = DateTimeParser::createDateTime($row['rnrm_expiry_date']);
            $dateDiff = DateTimeParser::different($now, $expiryDate);
            $timesDiffAgg = '';
            $timesDueText = '';
            $timesStatus = '';
            if (empty($dateDiff['y']) === false) {
                $timesDiffAgg .= $dateDiff['y'] . ' Years ';
            }
            if (empty($dateDiff['m']) === false) {
                $timesDiffAgg .= $dateDiff['m'] . ' Months ';
            }
            if (empty($dateDiff['d']) === false) {
                $timesDiffAgg .= $dateDiff['d'] . ' Days ';
            }
            if ($now > $expiryDate) {
                $timesDueText .= $timesDiffAgg . ' Ago';
            } elseif ($now < $expiryDate) {
                $timesDueText .= $timesDiffAgg . ' From Now';
            } else {
                $timesDueText .= $timesDiffAgg . 'Today';
            }

            $row['rnrm_expiry_date'] = DateTimeParser::format($row['rnrm_expiry_date'], 'Y-m-d', 'd M Y') . '<br>' . $timesDueText;
            # Set service reminder status compare by time remaining and threshold
            $dateThreshold = DateTimeParser::createDateTime($row['rnrm_expiry_threshold_date']);
            if ($expiryDate >= $now) {
                if ($now >= $dateThreshold) {
                    $timesStatus = new LabelWarning(Trans::getFmsWord('comingSoon'));
                }
            } else {
                $timesStatus = new LabelDanger(Trans::getFmsWord('overDue'));
            }
            $row['rnrm_status'] = $timesStatus;
            $results[] = $row;
        }

        return $results;
    }


    /**
     * Function to get the service history Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getServiceHistoryFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqSrvHistoryPtl', Trans::getFmsWord('serviceHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvHistoryTbl');
        $table->setHeaderRow([
            'svo_number' => Trans::getFmsWord('number'),
            'svo_eq_name' => Trans::getFmsWord('equipment'),
            'svo_order_date' => Trans::getFmsWord('orderDate'),
            'svo_planning_date' => Trans::getFmsWord('planningDate'),
            'svo_meter' => Trans::getFmsWord('meter'),
            'svo_status' => Trans::getFmsWord('status')
        ]);
        $wheres[] = '(svo_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svo_deleted_on IS NULL )';
        $orderList[] = 'svo.svo_finish_on DESC';
        $orderList[] = 'svo.svo_order_date DESC';
        $serviceData = [];
        $tempData = ServiceOrderDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $convertMeter = $numberFormat->doFormatFloat($row['svo_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['svo_order_date'] = DateTimeParser::format($row['svo_order_date'], 'Y-m-d', 'd M Y');
            $row['svo_planning_date'] = DateTimeParser::format($row['svo_planning_date'], 'Y-m-d', 'd M Y');
            $row['svo_meter'] = $convertMeter;
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['svo_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['svo_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['svo_finish_on']) === true && empty($row['svo_start_service_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onService'));
            } elseif (empty($row['svo_start_service_date']) === true && empty($row['svo_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['svo_approved_on']) === true && empty($row['svr_id']) === false) {
                if (empty($row['svr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['svo_status'] = $status;
            $serviceData[] = $row;
        }
        $table->addRows($serviceData);
        # Add special table attribute
        $table->addColumnAttribute('svo_meter', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_order_date', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_planning_date', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_status', 'style', 'text-align: center');
        $table->setViewActionByHyperlink(url('serviceOrder/view'), ['svo_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the renewal history Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRenewalHistoryFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqRnHistoryPtl', Trans::getFmsWord('renewalHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rnHistoryTbl');
        $table->setHeaderRow([
            'rno_number' => Trans::getFmsWord('number'),
            'rno_eq_name' => Trans::getFmsWord('equipment'),
            'rno_order_date' => Trans::getFmsWord('orderDate'),
            'rno_planning_date' => Trans::getFmsWord('planningDate'),
            'rno_status' => Trans::getFmsWord('status')
        ]);
        $wheres[] = '(rno_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rno_deleted_on IS NULL )';
        $orderList[] = 'rno.rno_finish_on DESC';
        $orderList[] = 'rno.rno_order_date DESC';
        $renewalData = [];
        $tempData = RenewalOrderDao::loadData($wheres, $orderList);
        foreach ($tempData as $row) {
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['rno_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['rno_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['rno_finish_on']) === true && empty($row['rno_start_renewal_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onProgress'));
            } elseif (empty($row['rno_start_renewal_date']) === true && empty($row['rno_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['rno_approved_on']) === true && empty($row['rnr_id']) === false) {
                if (empty($row['rnr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['rno_order_date'] = DateTimeParser::format($row['rno_order_date'], 'Y-m-d', 'd M Y');
            $row['rno_planning_date'] = DateTimeParser::format($row['rno_planning_date'], 'Y-m-d', 'd M Y');
            $row['rno_status'] = $status;
            $renewalData[] = $row;
        }
        $table->addRows($renewalData);
        # Add special settings to the table
        $table->addColumnAttribute('rno_order_date', 'style', 'text-align: center');
        $table->addColumnAttribute('rno_planning_date', 'style', 'text-align: center');
        $table->addColumnAttribute('rno_status', 'style', 'text-align: center');
        $table->setViewActionByHyperlink(url('renewalOrder/view'), ['rno_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the meter history Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getMeterHistoryFieldSet(): Portlet
    {
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        # Create portlet box.
        $portlet = new Portlet('EqMtrHistoryyPtl', $textMeter . ' ' . Trans::getFmsWord('history'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('mtrHistoryTbl');
        $table->setHeaderRow([
            'eqm_date' => Trans::getFmsWord('date'),
            'eqm_meter_convert' => $textMeter,
            'eqm_source' => Trans::getFmsWord('source'),
        ]);
        $wheres[] = '(eqm.eqm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(eqm.eqm_deleted_on IS NULL )';
        $orderList[] = 'eqm.eqm_date DESC';
        $orderList[] = 'eqm.eqm_meter DESC';
        $meterData = [];
        $tempData = EquipmentMeterDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $convertMeter = $numberFormat->doFormatFloat($row['eqm_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['eqm_date'] = DateTimeParser::format($row['eqm_date'], 'Y-m-d', 'd M Y');
            $row['eqm_meter_convert'] = $convertMeter;
            $meterData[] = $row;
        }
        $table->addRows($meterData);
        # Add special table attribute
        $table->addColumnAttribute('eqm_meter_convert', 'style', 'text-align: center');
        $table->addColumnAttribute('eqm_date', 'style', 'text-align: center');
        $table->addColumnAttribute('eqm_source', 'style', 'text-align: center');
        # add new modal button
        $modal = $this->getMeterHistoryModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getMeterHistoryDeleteModal();
        $this->View->addModal($modalDelete);
        $table->setUpdateActionByModal($modal, 'equipmentMeter', 'getByReference', ['eqm_id']);
        $table->setDeleteActionByModal($modalDelete, 'equipmentMeter', 'getByReferenceForDelete', ['eqm_id']);
        $btnMtrHisMdl = new ModalButton('btnMtrHisMdl', Trans::getFmsWord('update') . ' ' . $textMeter, $modal->getModalId());
        $btnMtrHisMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnMtrHisMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Meter History modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getMeterHistoryModal(): Modal
    {
        # Create Fields.
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        $modal = new Modal('MtrHisMdl', Trans::getFmsWord('update') . ' ' . $textMeter);
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateMeter');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateMeter' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField($textMeter, $this->Field->getNumber('eqm_meter', $this->getParameterForModal('eqm_meter', $showModal)), true);
        $fieldSet->addField(Trans::getFmsWord('date'), $this->Field->getCalendar('eqm_date', $this->getParameterForModal('eqm_date', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('eqm_id', $this->getParameterForModal('eqm_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Meter History delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getMeterHistoryDeleteModal(): Modal
    {
        # Create Fields.
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        $modal = new Modal('MtrHisDelMdl', Trans::getFmsWord('delete') . ' ' . $textMeter);
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteMeter');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteMeter' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField($textMeter, $this->Field->getText('eqm_meter_del', $this->getParameterForModal('eqm_meter_del', $showModal)), true);
        $fieldSet->addField(Trans::getFmsWord('date'), $this->Field->getCalendar('eqm_date_del', $this->getParameterForModal('eqm_date_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('eqm_id_del', $this->getParameterForModal('eqm_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the fuel history Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getFuelHistoryFieldSet(): Portlet
    {
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        # Create portlet box.
        $portlet = new Portlet('EqFuelHistoryyPtl', Trans::getFmsWord('fuelHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('fuelHistoryTbl');
        $table->setHeaderRow([
            'eqf_date' => Trans::getFmsWord('recordDate'),
            'eqf_meter_convert' => $textMeter,
            'eqf_qty_fuel_text' => Trans::getFmsWord('fuel'),
            'eqf_cost' => Trans::getFmsWord('cost'),
            'eqf_total' => Trans::getFmsWord('total'),
        ]);
        $wheres[] = '(eqf.eqf_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(eqf.eqf_deleted_on IS NULL )';
        $orderList[] = 'eqf.eqf_date DESC';
        $orderList[] = 'eqf.eqf_meter DESC';
        $meterData = [];
        $tempData = EquipmentFuelDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $convertMeter = $numberFormat->doFormatFloat($row['eqf_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['eqf_date'] = DateTimeParser::format($row['eqf_date'], 'Y-m-d', 'd M Y');
            $row['eqf_meter_convert'] = $convertMeter;
            $row['eqf_qty_fuel_text'] = $numberFormat->doFormatFloat($row['eqf_qty_fuel']) . ' L';
            $row['eqf_total'] = ($row['eqf_qty_fuel'] * $row['eqf_cost']);
            $meterData[] = $row;
        }
        $table->addRows($meterData);
        # Add special table attribute
        $table->addColumnAttribute('eqf_meter_convert', 'style', 'text-align: center');
        $table->addColumnAttribute('eqf_qty_fuel_text', 'style', 'text-align: center');
        $table->addColumnAttribute('eqf_date', 'style', 'text-align: center');
        $table->setColumnType('eqf_cost', 'currency');
        $table->setColumnType('eqf_total', 'currency');
        # add button edit
        $table->setUpdateActionByHyperlink('equipmentFuel/detail', ['eqf_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    protected function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('EqDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete')
        ]);
        // $docTable->setDeleteActionByModal($docDeleteModal, 'document', 'getByReferenceForDelete', ['doc_id']);
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'equipment')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = "(dct.dct_master = 'Y')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            if ((int)$row['doc_group_reference'] === $this->getDetailReferenceValue()) {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('EqFotoPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        # create modal.
        $docModal = $this->getDocumentModal();
        $this->View->addModal($docModal);
        $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
        $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnDocMdl);

        return $portlet;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('EqDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', 'equipment');
        $dctFields->addParameter('dct_master', 'Y');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('EqDocDelMdl', Trans::getWord('deleteDocument'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('eq_number');
        $status = $this->getStringParameter('eq_eqs_name');
        if ($status === 'Not Available') {
            $status = new LabelDark($status);
        } elseif ($status === 'Available') {
            $status = new LabelSuccess($status);
        } elseif ($status === 'On Service') {
            $status = new LabelWarning($status);
        }
        $this->View->setDescription($title . ' | ' . $status);

    }

}
