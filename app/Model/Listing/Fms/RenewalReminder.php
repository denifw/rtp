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
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing RenewalReminder page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class RenewalReminder extends AbstractListingModel
{

    /**
     * RenewalReminder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'renewalReminder');
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
        $rntField = $this->Field->getSingleSelect('renewalType', 'rnt_name', $this->getStringParameter('rnt_name'));
        $rntField->setHiddenField('rnt_id', $this->getIntParameter('rnt_id'));
        $rntField->addParameter('rnt_ss_id', $this->User->getSsId());
        $rntField->setEnableNewButton(false);
        $rntField->setEnableDetailButton(false);
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
        $statusField = $this->Field->getSelect('rnrm_status', $this->getStringParameter('rnrm_status'));
        $statusField->addOptions($statusData);
        $this->ListingForm->addField(Trans::getFmsWord('equipment'), $eqField);
        $this->ListingForm->addField(Trans::getFmsWord('renewalType'), $rntField);
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
                'rnrm_eq_name' => Trans::getFmsWord('equipment'),
                'rnrm_rnt_name' => Trans::getFmsWord('renewalType'),
                'rnrm_expiry_date' => Trans::getFmsWord('expiryDate'),
                'rnrm_status' => Trans::getFmsWord('status'),
            ]
        );
        # Load the data for ServiceReminder.
        $listingData = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('rnrm_status', 'style', 'text-align:center');
//        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['rnrm_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['rnrm_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (rnrm_id)) AS total_rows
                  FROM   renewal_reminder AS rnrm INNER JOIN
                         equipment AS eq ON eq.eq_id = rnrm.rnrm_eq_id INNER JOIN
                         equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                         renewal_type AS rnt ON rnt.rnt_id = rnrm.rnrm_rnt_id ';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
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
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT rnrm.rnrm_id, rnrm.rnrm_ss_id, rnrm.rnrm_eq_id, rnrm.rnrm_rnt_id,
                         rnrm.rnrm_expiry_date, rnrm.rnrm_expiry_threshold_date,
                         rnrm.rnrm_interval, rnrm.rnrm_interval_period,
                         rnrm.rnrm_threshold, rnrm.rnrm_threshold_period, rnrm.rnrm_remark,
                         eg.eg_name || \' \' || eq.eq_description AS rnrm_eq_name,
                         rnt.rnt_name AS rnrm_rnt_name
                  FROM   renewal_reminder AS rnrm INNER JOIN
                         equipment AS eq ON eq.eq_id = rnrm.rnrm_eq_id INNER JOIN
                         equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                         renewal_type AS rnt ON rnt.rnt_id = rnrm.rnrm_rnt_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY rnrm.rnrm_id, rnrm.rnrm_ss_id, rnrm.rnrm_eq_id, rnrm.rnrm_rnt_id, rnrm.rnrm_expiry_date,
                         rnrm.rnrm_interval, rnrm.rnrm_interval_period,
                         rnrm.rnrm_threshold, rnrm.rnrm_threshold_period, rnrm.rnrm_remark,
                         eg.eg_name, eq.eq_description, rnt.rnt_name';
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
        $wheres[] = '(rnrm.rnrm_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id') === true) {
            $wheres[] = '(rnrm.rnrm_eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        if ($this->isValidParameter('rnt_id') === true) {
            $wheres[] = '(rnrm.rnrm_rnt_id = ' . $this->getIntParameter('rnt_id') . ')';
        }
        if ($this->isValidParameter('rnrm_status') === true) {
            $status = $this->getStringParameter('rnrm_status');
            if ($status === 'Coming Soon') {
                $wheres[] = '(rnrm.rnrm_expiry_date >= NOW())';
                $wheres[] = '(NOW() >= rnrm.rnrm_expiry_threshold_date)';
            }
            if ($status === 'Over Due') {
                $wheres[] = '(NOW() > rnrm.rnrm_expiry_date)';
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
