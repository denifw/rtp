<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Fms\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing RenewalType page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class RenewalType extends AbstractListingModel
{

    /**
     * RenewalType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'renewalType');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getFmsWord('name'), $this->Field->getText('rnt_name', $this->getStringParameter('rnt_name')));
        $this->ListingForm->addField(Trans::getFmsWord('active'), $this->Field->getYesNo('rnt_active', $this->getStringParameter('rnt_active')));
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
                'rnt_name' => Trans::getFmsWord('name'),
                'rnt_active' => Trans::getFmsWord('active')
            ]
        );
        # Load the data for RenewalType.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('rnt_active', 'yesno');
        //$this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['rnt_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['rnt_id']);
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
        $query = 'SELECT count(DISTINCT (rnt.rnt_id)) AS total_rows
                   FROM renewal_type AS rnt ';
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
        $query = 'SELECT rnt.rnt_id, rnt.rnt_name, rnt.rnt_active
                  FROM renewal_type AS rnt';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY rnt.rnt_id, rnt.rnt_name, rnt.rnt_active';
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
        $wheres[] = '(rnt.rnt_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('rnt_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('rnt_name', $this->getStringParameter('rnt_name'));
        }
        if ($this->isValidParameter('rnt_active')) {
            $wheres[] = '(rnt.rnt_active = \'' . $this->getStringParameter('rnt_active') . '\')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
