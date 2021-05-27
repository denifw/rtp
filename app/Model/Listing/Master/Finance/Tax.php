<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Tax page.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Tax extends AbstractListingModel
{

    /**
     * Tax constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'tax');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('tax_name', $this->getStringParameter('tax_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('tax_active', $this->getStringParameter('tax_active')));
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
                'tax_name' => Trans::getWord('description'),
                'tax_percent' => Trans::getFinanceWord('percentage'),
                'tax_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Tax.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('tax_active', 'yesno');
        $this->ListingTable->setColumnType('tax_percent', 'currency');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['tax_id'], false);
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
        $query = "SELECT count(DISTINCT (t.tax_id)) AS total_rows
        FROM tax as t LEFT OUTER JOIN
        (SELECT td_tax_id, SUM(td_percent) as total from tax_detail
          WHERE td_active = 'Y' and td_deleted_on is null
          GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id";
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
        $query = "SELECT t.tax_id, t.tax_name, t.tax_active, (CASE WHEN td.total IS NULL THEN 0 ELSE td.total END) as tax_percent
        FROM tax as t LEFT OUTER JOIN
        (SELECT td_tax_id, SUM(td_percent) as total from tax_detail
          WHERE td_active = 'Y' and td_deleted_on is null
          GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY t.tax_id, t.tax_name, td.total, t.tax_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY t.tax_name, t.tax_id';
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
        if ($this->isValidParameter('tax_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('t.tax_name', $this->getStringParameter('tax_name'));
        }
        if ($this->isValidParameter('tax_active')) {
            $wheres[] = '(t.tax_active = \'' . $this->getStringParameter('tax_active') . '\')';
        }
        $wheres[] = '(t.tax_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # return the where query.
        return $strWhere;
    }
}
