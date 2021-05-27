<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of Warehouse.
 *
 * @package    app
 * @subpackage Model\Listing\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Warehouse extends AbstractListingModel
{

    /**
     * Warehouse constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'warehouse');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ofField = $this->Field->getSingleSelect('office', 'wh_office', $this->getStringParameter('wh_office'));
        $ofField->setHiddenField('wh_of_id', $this->getIntParameter('wh_of_id'));
        $ofField->addParameter('of_ss_id', $this->User->getSsId());
        $ofField->setEnableNewButton(false);
        $ofField->setEnableDetailButton(false);

        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('wh_name', $this->getStringParameter('wh_name')));
        $this->ListingForm->addField(Trans::getWord('office'), $ofField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('wh_active', $this->getStringParameter('wh_active')));
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
            'wh_name' => Trans::getWord('name'),
            'wh_office' => Trans::getWord('office'),
            'wh_state' => Trans::getWord('state'),
            'wh_city' => Trans::getWord('city'),
            'wh_length' => Trans::getWord('length') . ' (M)',
            'wh_height' => Trans::getWord('height') . ' (M)',
            'wh_width' => Trans::getWord('width') . ' (M)',
            'wh_volume' => Trans::getWord('volume') . ' (M3)',
            'wh_active' => Trans::getWord('active'),
        ]);
        # Load the data for Warehouse.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['wh_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['wh_id']);
        }
        $this->ListingTable->setColumnType('wh_length', 'float');
        $this->ListingTable->setColumnType('wh_height', 'float');
        $this->ListingTable->setColumnType('wh_width', 'float');
        $this->ListingTable->setColumnType('wh_volume', 'float');
        $this->ListingTable->setColumnType('wh_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (wh_id)) AS total_rows
                   FROM warehouse as wh INNER JOIN
                   office as o on wh.wh_of_id = o.of_id INNER JOIN
                   country as cnt ON o.of_cnt_id = cnt.cnt_id INNER JOIN
                   state as stt ON o.of_stt_id = stt.stt_id INNER JOIN
                   city as cty ON o.of_cty_id = cty.cty_id ';
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
        $query = 'SELECT wh.wh_id, wh.wh_name, wh.wh_of_id, o.of_name as wh_office, cnt.cnt_name as wh_country, cty.cty_name as wh_city,
                        wh.wh_length, wh.wh_height, wh.wh_width, wh.wh_volume, wh.wh_active
                   FROM warehouse as wh INNER JOIN
                   office as o on wh.wh_of_id = o.of_id INNER JOIN
                   country as cnt ON o.of_cnt_id = cnt.cnt_id INNER JOIN
                   state as stt ON o.of_stt_id = stt.stt_id INNER JOIN
                   city as cty ON o.of_cty_id = cty.cty_id ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY wh.wh_id, wh.wh_name, wh.wh_of_id, o.of_name, cnt.cnt_name, cty.cty_name,
                        wh.wh_length, wh.wh_height, wh.wh_width, wh.wh_volume, wh.wh_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY wh.wh_name';
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

        if ($this->isValidParameter('wh_of_id') === true) {
            $wheres[] = '(wh.wh_of_id = ' . $this->getIntParameter('wh_of_id') . ')';
        }
        if ($this->isValidParameter('wh_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('wh.wh_name', $this->getStringParameter('wh_name'));
        }
        if ($this->isValidParameter('wh_active') === true) {
            $wheres[] = "(wh.wh_active = '" . $this->getStringParameter('wh_active') . "')";
        }
        $wheres[] = '(wh.wh_ss_id = ' . $this->User->getSsId() . ')';

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
