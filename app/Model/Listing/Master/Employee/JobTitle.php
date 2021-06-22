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
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Employee\JobTitleDao;

/**
 * Class to control the system of JobTitle.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
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
        parent::__construct(get_class($this), 'jt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('jt_description', $this->getStringParameter('jt_description')));
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
            'jt_description' => Trans::getWord('description'),
            'jt_status' => Trans::getWord('status'),
        ]);
        # Load the data for JobTitle.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->addColumnAttribute('jt_status', 'style', 'text-align: center;');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jt_id']);
        }
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
        $data = JobTitleDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        foreach ($data as $row) {
            if (empty($row['jt_deleted_on']) === false) {
                $row['jt_status'] = new LabelDanger(Trans::getWord('deleted'));
            } else {
                $row['jt_status'] = new LabelSuccess(Trans::getWord('active'));
            }
            $results[] = $row;
        }
        return $results;
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
        $wheres[] = SqlHelper::generateStringCondition('jt_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('jt_description') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jt_description', $this->getStringParameter('jt_description'));
        }

        # return the list where condition.
        return $wheres;
    }
}
