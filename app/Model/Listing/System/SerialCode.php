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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of SerialCode.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialCode extends AbstractListingModel
{

    /**
     * SerialCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serialCode');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('sc_code', $this->getStringParameter('sc_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sc_active', $this->getStringParameter('sc_active')));
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
            'sc_code' => Trans::getWord('code'),
            'sc_description' => Trans::getWord('description'),
            'sc_active' => Trans::getWord('active'),
        ]);
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['sc_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sc_id']);
        $this->ListingTable->setColumnType('sc_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (sc_id)) AS total_rows
                   FROM serial_code';
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
        $query = 'SELECT sc_id, sc_code, sc_description, sc_active
                    FROM serial_code';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY sc_id, sc_code, sc_description, sc_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->loadDatabaseRow($query, $outFields);
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

        if ($this->isValidParameter('sc_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('sc_code', $this->getStringParameter('sc_code'));
        }
        if ($this->isValidParameter('sc_active') === true) {
            $wheres[] = "(sc_active = '" . $this->getStringParameter('sc_active') . "')";
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
