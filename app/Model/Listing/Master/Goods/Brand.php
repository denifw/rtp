<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\Master\Goods;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Brand page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Brand extends AbstractListingModel
{

    /**
     * Brand constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'brand');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('br_name', $this->getStringParameter('br_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('br_active', $this->getStringParameter('br_active')));
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
                'br_name' => Trans::getWord('name'),
                'br_active' => Trans::getWord('active')
            ]
        );
        # Load the data for Brand.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['br_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('br_active', 'yesno');
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['br_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['br_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (br_id)) AS total_rows
                   FROM brand';
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
        $query = 'SELECT br_id, br_name, br_active
                  FROM brand';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY br_id, br_name, br_active ';
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
        if ($this->isValidParameter('br_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('br_name', $this->getStringParameter('br_name'));
        }
        if ($this->isValidParameter('br_active')) {
            $wheres[] = '(br_active = \'' . $this->getStringParameter('br_active') . '\')';
        }
        $wheres[] = '(br_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
