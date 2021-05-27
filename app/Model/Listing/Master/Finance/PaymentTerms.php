<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing CostCode page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */
class PaymentTerms extends AbstractListingModel
{

    /**
     * CostCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'paymentTerms');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('pt_name', $this->getStringParameter('pt_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('pt_active', $this->getStringParameter('pt_active')));
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
                'pt_name' => Trans::getWord('description'),
                'pt_days' => Trans::getWord('days'),
                'pt_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for CostCode.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('pt_active', 'yesno');
        $this->ListingTable->setColumnType('pt_days', 'integer');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pt_id']);
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
        $query = 'SELECT count(DISTINCT (pt_id)) AS total_rows
                   FROM payment_terms ';
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
        $query = "SELECT pt_id, pt_name, pt_days, pt_active
                  FROM payment_terms ";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY pt_id, pt_name, pt_days, pt_active';
        # Set order by query.
        $query .= ' ORDER BY pt_days, pt_id';

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
        if ($this->isValidParameter('pt_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('pt_name', $this->getStringParameter('pt_name'));
        }
        if ($this->isValidParameter('pt_active')) {
            $wheres[] = '(pt_active = \'' . $this->getStringParameter('pt_active') . '\')';
        }
        $wheres[] = '(pt_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # return the where query.
        return $strWhere;
    }
}
