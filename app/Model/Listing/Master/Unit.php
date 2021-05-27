<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing UnitOfMeasure page.
 *
 * @package    app
 * @subpackage Model\Listing\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Unit extends AbstractListingModel
{

    /**
     * UnitOfMeasure constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'unit');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('uom_name', $this->getStringParameter('uom_name')));
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('uom_code', $this->getStringParameter('uom_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('uom_active', $this->getStringParameter('uom_active')));
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
                'uom_name' => Trans::getWord('name'),
                'uom_code' => Trans::getWord('code'),
                'uom_active' => Trans::getWord('active'),
            ]
        );
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['uom_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('uom_active', 'yesno');
        # Add special settings to the table
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['uom_id'], true);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (uom_id)) AS total_rows
                   FROM unit ';
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
        $query = 'SELECT uom_id, uom_code, uom_name, uom_active
                  FROM unit ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY uom_id, uom_code, uom_name, uom_active';
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
        if ($this->isValidParameter('uom_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('uom_name', $this->getStringParameter('uom_name'));
        }
        if ($this->isValidParameter('uom_code')) {
            $wheres[] = StringFormatter::generateLikeQuery('uom_code', $this->getStringParameter('uom_code'));
        }
        if ($this->isValidParameter('uom_active')) {
            $wheres[] = '(uom_active = \'' . $this->getStringParameter('uom_active') . '\')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
