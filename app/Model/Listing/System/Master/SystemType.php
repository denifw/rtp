<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Master\SystemTypeDao;

/**
 * Class to control the system of SystemType.
 *
 * @package    app
 * @subpackage Model\Listing\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SystemType extends AbstractListingModel
{

    /**
     * SystemType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sty');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('group'), $this->Field->getText('sty_group', $this->getStringParameter('sty_group')));
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('sty_code', $this->getStringParameter('sty_code')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('sty_name', $this->getStringParameter('sty_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sty_active', $this->getStringParameter('sty_active')));
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
            'sty_group' => Trans::getWord('group'),
            'sty_code' => Trans::getWord('code'),
            'sty_name' => Trans::getWord('name'),
            'sty_active' => Trans::getWord('active'),
        ]);
        # Load the data for SystemType.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('sty_active', 'yesno');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sty_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SystemTypeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return SystemTypeDao::loadData(
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

        if ($this->isValidParameter('sty_group') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_group', $this->getStringParameter('sty_group'));
        }
        if ($this->isValidParameter('sty_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_code', $this->getStringParameter('sty_code'));
        }
        if ($this->isValidParameter('sty_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_name', $this->getStringParameter('sty_name'));
        }
        if ($this->isValidParameter('sty_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('sty_active', $this->getStringParameter('sty_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
