<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Master\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Crm\JobTitleDao;

/**
 * Class to control the system of JobTitle.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class JobTitle extends AbstractListingModel
{

    /**
     * JobTitle constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jbt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getCrmWord('name'), $this->Field->getText('jbt_name', $this->getStringParameter('jbt_name')));
        $this->ListingForm->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('jbt_active', $this->getStringParameter('jbt_active')));
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
            'jbt_name' => Trans::getCrmWord('name'),
            'jbt_active' => Trans::getCrmWord('active'),
        ]);
        # Load the data for JobTitle.
        $this->ListingTable->addRows($this->loadData());
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jbt_id']);
        }
        $this->ListingTable->setColumnType('jbt_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobTitleDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return JobTitleDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(jbt.jbt_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('jbt_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jbt_name', $this->getStringParameter('jbt_name'));
        }
        if ($this->isValidParameter('jbt_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('jbt_active', $this->getStringParameter('jbt_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
