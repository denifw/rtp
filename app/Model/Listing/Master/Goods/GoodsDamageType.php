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
 * Class to control the system of GoodsDamageType.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsDamageType extends AbstractListingModel
{

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goodsDamageType');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('gdt_code', $this->getStringParameter('gdt_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('gdt_description', $this->getStringParameter('gdt_description')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('gdt_active', $this->getStringParameter('gdt_active')));
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
            'gdt_code' => Trans::getWord('code'),
            'gdt_description' => Trans::getWord('description'),
            'gdt_active' => Trans::getWord('active'),
        ]);
        # Load the data for GoodsDamageType.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['gdt_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['gdt_id']);
        }
        $this->ListingTable->setColumnType('gdt_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (gdt_id)) AS total_rows
                   FROM goods_damage_type';
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
        $query = 'SELECT gdt_id, gdt_code, gdt_ss_id, gdt_description, gdt_active
                FROM goods_damage_type';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY gdt_id, gdt_code, gdt_ss_id, gdt_description, gdt_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY gdt_active DESC, gdt_code';
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
        $wheres[] = '(gdt_ss_id = ' . $this->User->getSsId() . ')';

        if ($this->isValidParameter('gdt_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gdt_code', $this->getStringParameter('gdt_code'));
        }

        if ($this->isValidParameter('gdt_description') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gdt_description', $this->getStringParameter('gdt_description'));
        }
        if ($this->isValidParameter('gdt_active') === true) {
            $wheres[] = "(gdt_active = '" . $this->getStringParameter('gdt_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
