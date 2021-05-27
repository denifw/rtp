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
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of Container.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Container extends AbstractListingModel
{

    /**
     * Container constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'container');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('ct_name', $this->getStringParameter('ct_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('ct_active', $this->getStringParameter('ct_active')));
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
            'ct_code' => Trans::getWord('code'),
            'ct_name' => Trans::getWord('name'),
            'ct_length' => Trans::getWord('length'),
            'ct_height' => Trans::getWord('height'),
            'ct_width' => Trans::getWord('width'),
            'ct_volume' => Trans::getWord('volume'),
            'ct_max_weight' => Trans::getWord('maxWeight'),
            'ct_active' => Trans::getWord('active'),
        ]);
        # Load the data for Container.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['ct_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ct_id']);
        $this->ListingTable->setColumnType('ct_length', 'float');
        $this->ListingTable->setColumnType('ct_height', 'float');
        $this->ListingTable->setColumnType('ct_width', 'float');
        $this->ListingTable->setColumnType('ct_active', 'yesno');
        $this->ListingTable->setColumnType('ct_volume', 'float');
        $this->ListingTable->setColumnType('ct_max_weight', 'float');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (ct_id)) AS total_rows
                   FROM container';
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
        $query = 'SELECT ct_id, ct_code, ct_name, ct_length, ct_width, ct_height, ct_volume, ct_max_weight, ct_volume, ct_active
                FROM container ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY ct_id, ct_code, ct_name, ct_length, ct_width, ct_height, ct_volume, ct_max_weight, ct_volume, ct_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY ct_name, ct_id';
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

        if ($this->isValidParameter('ct_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ct_name', $this->getStringParameter('ct_name'));
        }
        if ($this->isValidParameter('ct_active') === true) {
            $wheres[] = "(ct_active = '" . $this->getStringParameter('ct_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
