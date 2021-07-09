<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\Master\Employee;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Employee\ItemSalaryDao;

/**
 * Class to control the system of ItemSalary.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class ItemSalary extends AbstractListingModel
{

    /**
     * ItemSalary constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'isl');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('isl_name', $this->getStringParameter('isl_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('isl_active', $this->getStringParameter('isl_active')));
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
            'isl_name' => Trans::getWord('name'),
            'isl_active' => Trans::getWord('active'),
        ]);
        # Load the data for ItemSalary.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('isl_active', 'yesno');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['isl_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return ItemSalaryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return ItemSalaryDao::loadData(
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
        $wheres[] = SqlHelper::generateStringCondition('isl_ss_id', $this->User->getSsId());

        if ($this->isValidParameter('isl_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('isl_name', $this->getStringParameter('isl_name'));
        }
        if ($this->isValidParameter('isl_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('isl_active', $this->getStringParameter('isl_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
