<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Dashboard page.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class Dashboard extends AbstractListingModel
{

    /**
     * Dashboard constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dashboard');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {

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
                'dsh_name' => Trans::getWord('name'),
                'dsh_description' => Trans::getWord('description')
            ]
        );
        # Load the data for Dashboard.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['dsh_id']);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dsh_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (dsh.dsh_id)) AS total_rows
                  FROM dashboard AS dsh';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT dsh.dsh_id, dsh.dsh_name, dsh.dsh_description
                  FROM dashboard AS dsh';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY dsh.dsh_id, dsh.dsh_name, dsh.dsh_description';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->loadDatabaseRow($query);
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
        $wheres[] = '(dsh.dsh_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(dsh.dsh_us_id = ' . $this->User->getId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        # return the where query.
        return $strWhere;
    }
}
