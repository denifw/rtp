<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Currency page.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Currency extends AbstractListingModel
{

    /**
     * Currency constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'currency');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $countryField = $this->Field->getSingleSelect('country', 'cnt_name', $this->getStringParameter('cnt_name'));
        $countryField->setHiddenField('cur_cnt_id', $this->getIntParameter('cur_cnt_id'));
        $countryField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('currency'), $this->Field->getText('cur_name', $this->getStringParameter('cur_name')));
        $this->ListingForm->addField(Trans::getWord('isoCode'), $this->Field->getText('cur_iso', $this->getStringParameter('cur_iso')));
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
                'cur_name' => Trans::getWord('currency'),
                'cur_iso' => Trans::getWord('isoCode'),
                'cnt_name' => Trans::getWord('country'),
                'cur_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Currency.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['cur_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('cur_active', 'yesno');
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['cur_id']);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cur_id'], true);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (cur.cur_id)) AS total_rows
                   FROM currency AS cur';
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
        $query = 'SELECT cur.cur_id, cur.cur_name, cur.cur_iso, cur.cur_cnt_id, cur.cur_active, cnt.cnt_name
                  FROM   currency AS cur INNER JOIN 
                         country AS cnt ON cur.cur_cnt_id = cnt.cnt_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY cur.cur_id, cur.cur_name, cur.cur_iso, cur.cur_cnt_id, cur.cur_active, cnt.cnt_name';

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
        if ($this->isValidParameter('cur_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('cur_name', $this->getStringParameter('cur_name'));
        }
        if ($this->isValidParameter('cur_iso')) {
            $wheres[] = '(LOWER(cur.cur_iso) = \'' . mb_strtolower($this->getStringParameter('cur_iso')) . '\')';
        }
        if ($this->isValidParameter('cur_cnt_id')) {
            $wheres[] = '(cur.cur_cnt_id = ' . $this->getIntParameter('cur_cnt_id') . ')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
