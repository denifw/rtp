<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System\Service;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Service page.
 *
 * @package    app
 * @subpackage Model\Listing\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Service extends AbstractListingModel
{

    /**
     * Service constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'service');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('srv_name', $this->getStringParameter('srv_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('srv_active', $this->getStringParameter('srv_active')));
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
                'srv_code' => Trans::getWord('code'),
                'srv_name' => Trans::getWord('name'),
                'srv_active' => Trans::getWord('active')
            ]
        );
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('srv_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['srv_id'], true);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (srv_id)) AS total_rows
                   FROM service';
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
        $query = 'SELECT srv_id, srv_name, srv_active, srv_code
                  FROM service';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY  srv_id, srv_name, srv_active, srv_code';
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
        if ($this->isValidParameter('srv_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('srv_name', $this->getStringParameter('srv_name'));
        }
        if ($this->isValidParameter('srv_active')) {
            $wheres[] = '(srv_active = \'' . $this->getStringParameter('srv_active') . '\')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
