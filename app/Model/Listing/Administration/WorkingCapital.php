<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\Administration;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Administration\WorkingCapitalDao;

/**
 * Class to control the system of WorkingCapital.
 *
 * @package    app
 * @subpackage Model\Listing\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class WorkingCapital extends AbstractListingModel
{

    /**
     * WorkingCapital constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'wc');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $typeField = $this->Field->getSelect('wc_type', $this->getStringParameter('wc_type'));
        $typeField->addOption(Trans::getWord('deposit'), 'D');
        $typeField->addOption(Trans::getWord('withdrawal'), 'W');

        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('wc_reference', $this->getStringParameter('wc_reference')));
        $this->ListingForm->addField(Trans::getWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getWord('date'), $this->Field->getCalendar('wc_date', $this->getStringParameter('wc_date')));
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
            'wc_date' => Trans::getWord('date'),
            'wc_type' => Trans::getWord('type'),
            'wc_amount' => Trans::getWord('amount'),
            'wc_bank_account' => Trans::getWord('account'),
            'wc_reference' => Trans::getWord('reference'),
            'wc_deleted' => Trans::getWord('deleted'),
        ]);
        # Load the data for WorkingCapital.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('wc_date', 'date');
        $this->ListingTable->setColumnType('wc_amount', 'currency');
        $this->ListingTable->addColumnAttribute('wc_type', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('wc_deleted', 'style', 'text-align: center;');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['wc_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return WorkingCapitalDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = WorkingCapitalDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        foreach ($data as $row) {
            if ($row['wc_type'] === 'D') {
                $row['wc_type'] = new LabelSuccess(Trans::getWord('deposit'));
            } else {
                $row['wc_type'] = new LabelWarning(Trans::getWord('withdrawal'));
            }
            $row['wc_deleted'] = '';
            if (empty($row['wc_deleted_on']) === false) {
                $row['wc_deleted'] = new LabelDanger(Trans::getWord('deleted'));
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
        $wheres[] = SqlHelper::generateStringCondition('wc.wc_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('wc_reference') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('wc.wc_reference', $this->getStringParameter('wc_reference'));
        }
        if ($this->isValidParameter('wc_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('wc.wc_type', $this->getStringParameter('wc_type'));
        }
        if ($this->isValidParameter('wc_date') === true) {
            $wheres[] = SqlHelper::generateStringCondition('wc.wc_date', $this->getStringParameter('wc_date'));
        }
        # return the list where condition.
        return $wheres;
    }
}
