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
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing EquipmentUsage page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class EquipmentUsage extends AbstractListingModel
{

    /**
     * EquipmentUsage constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipmentUsage');
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
        $this->ListingForm->addField(Trans::getFmsWord('usageDateFrom'), $this->Field->getCalendar('usage_date_from', $this->getStringParameter('usage_date_from')));
        $this->ListingForm->addField(Trans::getFmsWord('usageDateUntil'), $this->Field->getCalendar('usage_date_until', $this->getStringParameter('usage_date_until')));
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
                'equ_eq_name' => Trans::getFmsWord('equipment'),
                'equ_date' => Trans::getFmsWord('usageDate'),
                'equ_meter' => Trans::getFmsWord('meter')
            ]
        );
        # Load the data for EquipmentUsage.
        $tempData = $this->loadData();
        $listingData = [];
        foreach ($tempData AS $row) {
            $row['equ_date'] = DateTimeParser::format($row['equ_date'], 'Y-m-d', 'd M Y');
            $listingData[] = $row;
        }
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('equ_meter', 'float');
        $this->ListingTable->addColumnAttribute('equ_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('equ_meter', 'style', 'text-align: center');
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['equ_id']);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['equ_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (equ.equ_id)) AS total_rows
                  FROM equipment_usage AS equ INNER JOIN
                       equipment AS eq ON eq.eq_id = equ.equ_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id';
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
        $query = 'SELECT equ.equ_id, equ.equ_eq_id, equ.equ_date, equ.equ_meter,
                         eg.eg_name || \' \' || eq.eq_description AS equ_eq_name
                  FROM equipment_usage AS equ INNER JOIN
                       equipment AS eq ON eq.eq_id = equ.equ_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY equ.equ_id, equ.equ_eq_id, equ.equ_date, equ.equ_meter, eg.eg_name, eq.eq_description';
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
        $wheres[] = '(equ.equ_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id') == true) {
            $wheres[] = '(eq.eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
