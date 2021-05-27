<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Fms;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing ServiceReminder page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class ServiceReminder extends AbstractListingModel
{

    /**
     * ServiceReminder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceReminder');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create equiment single select
        $eqField = $this->Field->getSingleSelect('equipment', 'eq_name', $this->getStringParameter('eq_name'), 'loadSingleSelectDataForFms');
        $eqField->setHiddenField('eq_id', $this->getIntParameter('eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setEnableNewButton(false);
        $eqField->setEnableDetailButton(false);
        # Create service task single select
        $svtField = $this->Field->getSingleSelect('serviceTask', 'svt_name', $this->getStringParameter('svt_name'));
        $svtField->setHiddenField('svt_id', $this->getIntParameter('svt_id'));
        $svtField->addParameter('svt_ss_id', $this->User->getSsId());
        $svtField->setEnableNewButton(false);
        $svtField->setEnableDetailButton(false);
        $statusData = [
            [
                'text' => Trans::getFmsWord('allStatus'),
                'value' => Trans::getFmsWord('allStatus')
            ],
            [
                'text' => Trans::getFmsWord('comingSoon'),
                'value' => Trans::getFmsWord('comingSoon')
            ],
            [
                'text' => Trans::getFmsWord('overDue'),
                'value' => Trans::getFmsWord('overDue')
            ],
        ];
        $statusField = $this->Field->getSelect('svrm_status', $this->getStringParameter('svrm_status'));
        $statusField->addOptions($statusData);
        $this->ListingForm->addField(Trans::getFmsWord('equipment'), $eqField);
        $this->ListingForm->addField(Trans::getFmsWord('task'), $svtField);
        $this->ListingForm->addField(Trans::getFmsWord('status'), $statusField);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'svrm_eq_name' => Trans::getFmsWord('equipment'),
                'svrm_svt_name' => Trans::getFmsWord('task'),
                'svrm_interval' => Trans::getFmsWord('schedule'),
                'svrm_next_due_date' => Trans::getFmsWord('nextService'),
                'svrm_status' => Trans::getFmsWord('status'),
                'svrm_last_completed' => Trans::getFmsWord('lastCompleted')
            ]
        );
        # Load the data for ServiceReminder.
        $listingData = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('svrm_last_completed', 'style', 'text-align:center');
        $this->ListingTable->addColumnAttribute('svrm_status', 'style', 'text-align:center');
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['svrm_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['svrm_id']);
        }
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareData(array $data): array
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
            $lastCompleted = '';
            if (empty($row['svo_start_service_date']) === false) {
                $lastCompleted = DateTimeParser::format($row['svo_start_service_date'], 'Y-m-d', 'd M Y') . ' <br> ' . $numberFormatter->doFormatFloat($row['svo_meter']) . ' ' . $row['eq_primary_meter'];
            }
            $row['svrm_last_completed'] = $lastCompleted;
            $meterDueText = '';
            $timesDueText = '';
            $svrmNextDueDate = '';
            $meterStatus = '';
            $timesStatus = '';
            $svrmStatus = '';
            # Calculate meter remaining
            if (empty($row['svrm_meter_remaining']) === false && empty($row['eqm_meter']) === false) {
                $nextMeter = $row['svrm_meter_interval'];
                if (empty($row['svo_meter']) === false) {
                    $nextMeter = $row['svrm_meter_interval'] + $row['svo_meter'];
                }
                if ($row['svrm_meter_remaining'] > 0) {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'] . ' From now';
                } elseif ($row['svrm_meter_remaining'] < 0) {
                    $meterDueText = $numberFormatter->doFormatFloat(abs($row['svrm_meter_remaining'])) . ' ' . $row['eq_primary_meter'] . ' Ago';
                } else {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'];
                }
                $meterDueText .= ' On ' . $numberFormatter->doFormatFloat($nextMeter) . ' ' . $row['eq_primary_meter'];
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
            # Status
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
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (svrm.svrm_id)) AS total_rows
                  FROM  service_reminder AS svrm INNER JOIN
                         equipment AS eq ON eq.eq_id = svrm.svrm_eq_id INNER JOIN
                         equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                         service_task AS svt ON svt.svt_id = svrm.svrm_svt_id LEFT OUTER JOIN
                         (SELECT   eqm_eq_id, MAX(eqm_meter) AS eqm_meter
						  FROM     equipment_meter
						  WHERE    eqm_deleted_on IS NULL
						  GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id LEFT OUTER JOIN
						  (SELECT  MAX(svo_meter) AS svo_meter, MAX(svo_start_service_date) AS svo_start_service_date, svo_eq_id, svd_svt_id
						   FROM    service_order INNER JOIN
								   service_order_detail ON svd_svo_id = svo_id
						   WHERE  svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
						   GROUP BY svo_eq_id, svd_svt_id) AS svo ON svo.svo_eq_id = eq.eq_id AND svo.svd_svt_id = svt.svt_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT svrm.svrm_id, svrm.svrm_ss_id, svrm.svrm_eq_id, svrm.svrm_svt_id,
                         svrm.svrm_meter_interval, svrm.svrm_time_interval, svrm.svrm_time_interval_period,
                         svrm.svrm_meter_threshold, svrm.svrm_time_threshold, svrm.svrm_time_threshold_period,
                         svrm.svrm_next_due_date, svrm.svrm_next_due_date_threshold, svrm.svrm_remark,
                         eg.eg_name || \' \' || eq.eq_description AS svrm_eq_name,
                         svt.svt_name AS svrm_svt_name, eq.eq_primary_meter,
                         eqm.eqm_meter, svo.svo_start_service_date, svo.svo_meter,
                         (svrm.svrm_meter_interval - (coalesce(eqm.eqm_meter, 0) - coalesce(svo.svo_meter, 0))) AS svrm_meter_remaining,
                         DATE_PART(\'day\', svrm.svrm_next_due_date - now()) AS svrm_times_remaining
                   FROM  service_reminder AS svrm INNER JOIN
                         equipment AS eq ON eq.eq_id = svrm.svrm_eq_id INNER JOIN
                         equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                         service_task AS svt ON svt.svt_id = svrm.svrm_svt_id LEFT OUTER JOIN
                         (SELECT   eqm_eq_id, MAX(eqm_meter) AS eqm_meter
						  FROM     equipment_meter
						  WHERE    eqm_deleted_on IS NULL
						  GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id LEFT OUTER JOIN
						  (SELECT  MAX(svo_meter) AS svo_meter, MAX(svo_start_service_date) AS svo_start_service_date, svo_eq_id, svd_svt_id
						   FROM    service_order INNER JOIN
								   service_order_detail ON svd_svo_id = svo_id
						   WHERE  svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
						   GROUP BY svo_eq_id, svd_svt_id) AS svo ON svo.svo_eq_id = eq.eq_id AND svo.svd_svt_id = svt.svt_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY svrm.svrm_id, svrm.svrm_ss_id, svrm.svrm_eq_id, svrm.svrm_svt_id,
                         svrm.svrm_meter_interval, svrm.svrm_time_interval, svrm_time_interval_period, svrm.svrm_meter_threshold,
                         svrm.svrm_time_threshold, svrm.svrm_time_threshold_period, svrm.svrm_next_due_date, svrm.svrm_remark, eg.eg_name, eq.eq_description,
                         svt.svt_name, eq.eq_primary_meter, eqm.eqm_meter, svo.svo_start_service_date, svo.svo_meter';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(svrm.svrm_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id') === true) {
            $wheres[] = '(svrm.svrm_eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        if ($this->isValidParameter('svt_id') === true) {
            $wheres[] = '(svrm.svrm_svt_id = ' . $this->getIntParameter('svt_id') . ')';
        }
        if ($this->isValidParameter('svrm_status') === true) {
            $status = $this->getStringParameter('svrm_status');
            if ($status === 'Coming Soon') {
                $wheresMeter[] = '(subSvrm.svrm_meter_remaining >= 0)';
                $wheresMeter[] = '(svrm.svrm_meter_threshold >= subSvrm.svrm_meter_remaining)';
                $wheresTimes[] = '(svrm.svrm_next_due_date >= NOW())';
                $wheresTimes[] = '(NOW() >= svrm.svrm_next_due_date_threshold)';
                $wheres[] = '(' . implode(' AND ', $wheresMeter) . ' OR ' . implode(' AND ', $wheresTimes) . ')';
            }
            if ($status === 'Over Due') {
                $wheres[] = '((subSvrm.svrm_meter_remaining < 0)
                              OR (NOW() > svrm.svrm_next_due_date) )';
            }
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
