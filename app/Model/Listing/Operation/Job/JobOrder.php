<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\Operation\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Operation\Job\JobOrderDao;

/**
 * Class to control the system of JobOrder.
 *
 * @package    app
 * @subpackage Model\Listing\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrder extends AbstractListingModel
{

    /**
     * JobOrder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jo');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        // TODO: Implement loadSearchForm() method.
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
            'jo_number' => Trans::getWord('number'),
            'jo_name' => Trans::getWord('name'),
        ]);
        # Load the data for JobOrder.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobOrderDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return JobOrderDao::loadData(
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

        $wheres[] = SqlHelper::generateStringCondition('jo.jo_ss_id', $this->User->getSsId());
        # return the list where condition.
        return $wheres;
    }
}
