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
 * Class to control the system of SystemSetting.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemSetting extends AbstractListingModel
{

    /**
     * SystemSetting constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemSetting');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('relation'), $this->Field->getText('ss_relation', $this->getStringParameter('ss_relation')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getText('ss_active', $this->getStringParameter('ss_active')));
    }

    /**
     * Abstract function to load sorting field.
     *
     * @return void
     */
    public function loadSortingOptions(): void
    {
        $this->ListingSort->addOption('ss_relation', Trans::getWord('relation'));
        parent::loadSortingOptions();
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
            'ss_relation' => Trans::getWord('relation'),
            'lg_locale' => Trans::getWord('language'),
            'ss_name_space' => Trans::getWord('nameSpace'),
            'ss_decimal_number' => Trans::getWord('decimalNumber'),
            'ss_decimal_separator' => Trans::getWord('decimalSeparator'),
            'ss_thousand_separator' => Trans::getWord('thousandSeparator'),
            'ss_system' => Trans::getWord('system'),
            'ss_active' => Trans::getWord('active'),

        ]);
        # Load the data for SystemSetting.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['ss_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ss_id']);
        $this->ListingTable->setColumnType('ss_decimal_number', 'integer');
        $this->ListingTable->setColumnType('ss_system', 'yesno');
        $this->ListingTable->setColumnType('ss_active', 'yesno');
        $this->ListingTable->addColumnAttribute('ss_decimal_separator', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('ss_thousand_separator', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (ss_id)) AS total_rows
                   FROM system_setting as ss INNER JOIN 
                   languages as lg ON ss.ss_lg_id = lg.lg_id ';
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
        $query = 'SELECT ss.ss_id, ss.ss_relation, ss.ss_lg_id, ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator,
                      lg.lg_iso, lg.lg_locale, ss.ss_logo, ss.ss_name_space, ss.ss_system, ss.ss_active
                   FROM system_setting as ss INNER JOIN 
                   languages as lg ON ss.ss_lg_id = lg.lg_id ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY ss.ss_id, ss.ss_relation, ss.ss_lg_id, ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator,
                      lg.lg_iso, lg.lg_locale, ss.ss_logo, ss.ss_name_space, ss.ss_system, ss.ss_active';
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

        if ($this->isValidParameter('ss_relation') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('ss.ss_relation', $this->getStringParameter('ss_relation'));
        }

        if ($this->isValidParameter('ss_active') === true) {
            $wheres[] = "(ss.ss_active = '" . $this->getStringParameter('ss_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
