<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of SystemTable.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemTable extends AbstractListingModel
{

    /**
     * SystemTable constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemTable');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('st_name', $this->getStringParameter('st_name')));
        $this->ListingForm->addField(Trans::getWord('prefix'), $this->Field->getText('st_prefix', $this->getStringParameter('st_prefix')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('st_active', $this->getStringParameter('st_active')));
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'st_name' => Trans::getWord('name'),
            'st_prefix' => Trans::getWord('prefix'),
            'st_path' => Trans::getWord('path'),
            'st_active' => Trans::getWord('active'),
            'seeder' => Trans::getWord('seeder'),
        ]);
        # Load the data for SystemTable.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['st_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['st_id']);
        $this->ListingTable->setColumnType('st_active', 'yesno');
        $this->ListingTable->addColumnAttribute('seeder', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (st_id)) AS total_rows
                   FROM system_table';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @param array $outFields To store the out field from selection data.
     *
     * @return array
     */
    private function loadData(array $outFields): array
    {
        # Set Select query;
        $query = 'SELECT st_id, st_name, st_prefix, st_path, st_active
                    FROM system_table ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY st_id, st_name, st_prefix, st_path, st_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY st_name';
        }

        $data = $this->loadDatabaseRow($query, $outFields);
        $lengthData = count($data);
        for ($i = 0; $i < $lengthData; $i++) {
            $btn = new Button('btnSeed' . $data[$i]['st_id'], '');
            $btn->setIcon(Icon::Eye)->btnDanger()->viewIconOnly();
            $fileName = str_replace(' ', '', $data[$i]['st_name']) . 'Dao';
            $path = str_replace(['\\', ' '], ['/', ''], $data[$i]['st_path']) . '/' . $fileName;
            $btn->setPopup('seed', ['page' => $path]);
            $data[$i]['seeder'] = $btn;
        }

        return $data;
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

        if ($this->isValidParameter('st_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('st_name', $this->getStringParameter('st_name'));
        }
        if ($this->isValidParameter('st_prefix') === true) {
            $wheres[] = SqlHelper::generateStringCondition('st_prefix', $this->getStringParameter('st_prefix'));
        }
        if ($this->isValidParameter('st_active') === true) {
            $wheres[] = "(st_active = '" . $this->getStringParameter('st_active') . "')";
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
