<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Master\Goods;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of GoodsCauseDamage.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCauseDamage extends AbstractListingModel
{

    /**
     * GoodsCauseDamage constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goodsCauseDamage');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('gcd_code', $this->getStringParameter('gcd_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('gcd_description', $this->getStringParameter('gcd_description')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('gcd_active', $this->getStringParameter('gcd_active')));
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
            'gcd_code' => Trans::getWord('code'),
            'gcd_description' => Trans::getWord('description'),
            'gcd_active' => Trans::getWord('active'),
        ]);
        # Load the data for GoodsCauseDamage.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['gcd_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['gcd_id']);
        }
        $this->ListingTable->setColumnType('gcd_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (gcd_id)) AS total_rows
                   FROM goods_cause_damage';
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
        $query = 'SELECT gcd_id, gcd_code, gcd_active, gcd_description, gcd_ss_id
                    FROM goods_cause_damage';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY gcd_id, gcd_code, gcd_active, gcd_description, gcd_ss_id';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY gcd_active DESC, gcd_code';
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

        $wheres[] = '(gcd_ss_id = ' . $this->User->getSsId() . ')';

        if ($this->isValidParameter('gcd_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gcd_code', $this->getStringParameter('gcd_code'));
        }
        if ($this->isValidParameter('gcd_description') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gcd_description', $this->getStringParameter('gcd_description'));
        }
        if ($this->isValidParameter('gcd_active') === true) {
            $wheres[] = "(gcd_active = '" . $this->getStringParameter('gcd_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
