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

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing CostCode page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class CostCode extends AbstractListingModel
{

    /**
     * CostCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'costCode');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Cost Code Group
        $ccgField = $this->Field->getSingleSelect('costCodeGroup', 'cc_group', $this->getStringParameter('cc_group'));
        $ccgField->setHiddenField('cc_ccg_id', $this->getIntParameter('cc_ccg_id'));
        $ccgField->setEnableDetailButton(false);
        $ccgField->addParameter('ccg_ss_id', $this->User->getSsId());
        $ccgField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('group'), $ccgField);
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('cc_code', $this->getStringParameter('cc_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cc_active', $this->getStringParameter('cc_active')));
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
                'cc_group' => Trans::getWord('group'),
                'cc_code' => Trans::getWord('code'),
                'cc_name' => Trans::getWord('name'),
                'cc_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for CostCode.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('cc_active', 'yesno');
        $this->ListingTable->addColumnAttribute('cc_code', 'style', 'text-align: center;');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cc_id']);
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
        $query = 'SELECT count(DISTINCT (cc.cc_id)) AS total_rows
                   FROM cost_code AS cc INNER JOIN
                       cost_code_group as ccg ON ccg.ccg_id = cc.cc_ccg_id';
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
        $query = "SELECT cc.cc_id, cc.cc_code, cc.cc_name, cc.cc_ccg_id, ccg.ccg_code,ccg.ccg_name,
                        cc.cc_active
                  FROM cost_code AS cc INNER JOIN
                       cost_code_group as ccg ON ccg.ccg_id = cc.cc_ccg_id";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY cc.cc_id, cc.cc_code, cc.cc_name, cc.cc_ccg_id, ccg.ccg_code,ccg.ccg_name, cc.cc_active';
        # Set order by query.
        $query .= ' ORDER BY ccg.ccg_code, cc.cc_code, cc.cc_id';

        $data = $this->loadDatabaseRow($query);
        return $this->doPrepareData($data);
    }

    /**
     * Function to do prepare data
     *
     * @param array $data To store the listing data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];

        foreach ($data as $row) {
            $row['cc_group'] = $row['ccg_code'] . ' - ' . $row['ccg_name'];
            $results[] = $row;
        }

        return $results;

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
        if ($this->isValidParameter('cc_code')) {
            $wheres[] = '(CAST(cc.cc_code AS TEXT) like \'%' . mb_strtolower($this->getStringParameter('cc_code')) . '%\')';
        }
        if ($this->isValidParameter('cc_active')) {
            $wheres[] = '(cc.cc_active = \'' . $this->getStringParameter('cc_active') . '\')';
        }
        if ($this->isValidParameter('cc_ccg_id')) {
            $wheres[] = '(cc.cc_ccg_id = ' . $this->getIntParameter('cc_ccg_id') . ')';
        }
        $wheres[] = '(cc.cc_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
