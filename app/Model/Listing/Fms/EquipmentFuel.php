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
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing EquipmentFuel page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class EquipmentFuel extends AbstractListingModel
{

    /**
     * EquipmentFuel constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipmentFuel');
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
        $this->ListingForm->addField(Trans::getFmsWord('equipment'), $eqField);
        $this->ListingForm->addField(Trans::getFmsWord('recordDateFrom'), $this->Field->getCalendar('record_date_from', $this->getStringParameter('record_date_from')));
        $this->ListingForm->addField(Trans::getFmsWord('recordDateUntil'), $this->Field->getCalendar('record_date_until', $this->getStringParameter('record_date_until')));
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
                'eqf_eq_name' => Trans::getFmsWord('equipment'),
                'eqf_date' => Trans::getFmsWord('recordDate'),
                'eqf_meter_convert' => Trans::getFmsWord('meter'),
                'eqf_qty_fuel' => Trans::getFmsWord('fuel') . ' (Liter)',
                'eqf_cost' => Trans::getFmsWord('cost'),
                'eqf_total' => Trans::getFmsWord('total'),
                'eqf_status' => Trans::getFmsWord('status')
            ]
        );
        # Load the data for EquipmentFuel.
        $listingData = [];
        $tempData = $this->loadData();
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['eqf_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['eqf_confirm_on']) === false) {
                $status = $status = new LabelSuccess(Trans::getFmsWord('confirm'));
            }
            $row['eqf_status'] = $status;
            $row['eqf_date'] = DateTimeParser::format($row['eqf_date'], 'Y-m-d', 'd M Y');
            $row['eqf_meter_convert'] = $numberFormat->doFormatFloat($row['eqf_meter']) . ' ' . $row['eq_primary_meter'];
            $row['eqf_total'] = ($row['eqf_qty_fuel'] * $row['eqf_cost']);
            $listingData[] = $row;
        }
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('eqf_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('eqf_meter_convert', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('eqf_status', 'style', 'text-align: center');
        $this->ListingTable->setColumnType('eqf_qty_fuel', 'float');
        $this->ListingTable->setColumnType('eqf_cost', 'currency');
        $this->ListingTable->setColumnType('eqf_total', 'currency');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['eqf_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['eqf_id']);
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
        $query = 'SELECT count(DISTINCT (eqf.eqf_id)) AS total_rows
                  FROM equipment_fuel AS eqf INNER JOIN
                       equipment AS eq ON eq.eq_id = eqf_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id ';
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
        $query = 'SELECT eqf.eqf_id, eqf.eqf_ss_id, eqf.eqf_eq_id, eqf.eqf_date, eqf.eqf_meter, eqf.eqf_qty_fuel, eqf.eqf_cost,
                         eqf.eqf_deleted_on, eqf.eqf_deleted_reason, eqf.eqf_confirm_by, eqf.eqf_confirm_on,
                         eqf.eqf_remark, eg.eg_name || \' - \' || eq.eq_description AS eqf_eq_name, eq.eq_primary_meter
                  FROM equipment_fuel AS eqf INNER JOIN
                       equipment AS eq ON eq.eq_id = eqf_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY eqf.eqf_id, eqf.eqf_ss_id, eqf.eqf_eq_id, eqf.eqf_date, eqf.eqf_meter,
                             eqf.eqf_qty_fuel, eqf.eqf_cost, eqf.eqf_remark, eqf.eqf_deleted_on, eqf.eqf_deleted_reason, eqf.eqf_confirm_by, eqf.eqf_confirm_on,
                             eg.eg_name, eq.eq_description, eq.eq_primary_meter';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY eqf.eqf_date DESC';
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
        $wheres[] = '(eqf.eqf_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id') === true) {
            $wheres[] = '(eq.eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        if ($this->isValidParameter('record_date_from') === true) {
            if ($this->isValidParameter('record_date_until') === true) {
                $wheres[] = "(eqf.eqf_date >= '" . $this->getStringParameter('record_date_from') . "')";
            } else {
                $wheres[] = "(eqf.eqf_date = '" . $this->getStringParameter('record_date_from') . "')";
            }
        }
        if ($this->isValidParameter('record_date_until') === true) {
            if ($this->isValidParameter('record_date_from') === true) {
                $wheres[] = "(eqf.eqf_date <= '" . $this->getStringParameter('record_date_until') . "')";
            } else {
                $wheres[] = "(eqf.eqf_date = '" . $this->getStringParameter('record_date_until') . "')";
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
