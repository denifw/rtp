<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Master\Warehouse;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of StockAdjustmentType.
 *
 * @package    app
 * @subpackage Model\Listing\System\Service
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockAdjustmentType extends AbstractListingModel
{

    /**
     * StockAdjustmentType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockAdjustmentType');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('sat_code', $this->getStringParameter('sat_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('sat_description', $this->getStringParameter('sat_description')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sat_active', $this->getStringParameter('sat_active')));
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
            'sat_code' => Trans::getWord('code'),
            'sat_description' => Trans::getWord('description'),
            'sat_active' => Trans::getWord('active'),
        ]);
        # Load the data for StockAdjustmentType.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['sat_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sat_id']);
        }
        $this->ListingTable->setColumnType('sat_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (sat_id)) AS total_rows
                   FROM stock_adjustment_type';
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
        $query = 'SELECT sat_id, sat_code, sat_description, sat_ss_id, sat_description, sat_active
                   FROM stock_adjustment_type';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY sat_id, sat_code, sat_description, sat_ss_id, sat_description, sat_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY sat_active DESC, sat_code';
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

        $wheres[] = '(sat_ss_id = ' . $this->User->getSsId() . ')';

        if ($this->isValidParameter('sat_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('sat_code', $this->getStringParameter('sat_code'));
        }
        if ($this->isValidParameter('sat_description') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('sat_description', $this->getStringParameter('sat_description'));
        }
        if ($this->isValidParameter('sat_active') === true) {
            $wheres[] = "(sat_active = '" . $this->getStringParameter('sat_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
