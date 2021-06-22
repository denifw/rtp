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
use App\Model\Dao\Master\Employee\EmployeeDao;

/**
 * Class to control the system of Employee.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Employee extends AbstractListingModel
{

    /**
     * Employee constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'em');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $jtField = $this->Field->getSingleSelect('jt', 'em_job_title', $this->getStringParameter('em_job_title'));
        $jtField->setHiddenField('em_jt_id', $this->getStringParameter('em_jt_id'));
        $jtField->addParameter('jt_ss_id', $this->User->getSsId());
        $jtField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('number'), $this->Field->getText('em_number', $this->getStringParameter('em_number')));
        $this->ListingForm->addField(Trans::getWord('identityNumber'), $this->Field->getText('em_identity_number', $this->getStringParameter('em_identity_number')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('em_name', $this->getStringParameter('em_name')));
        $this->ListingForm->addField(Trans::getWord('jobTitle'), $jtField);
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
            'em_number' => Trans::getWord('number'),
            'em_identity_number' => Trans::getWord('identityNumber'),
            'em_name' => Trans::getWord('name'),
            'em_phone' => Trans::getWord('phone'),
            'em_birthday' => Trans::getWord('birthday'),
            'em_active' => Trans::getWord('active'),
        ]);
        # Load the data for Employee.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('em_birthday', 'date');
        $this->ListingTable->setColumnType('em_active', 'yesno');
        $this->ListingTable->addColumnAttribute('em_number', 'style', 'text-align: center;');
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['em_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return EmployeeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return EmployeeDao::loadData(
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
        $wheres[] = SqlHelper::generateStringCondition('em.em_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('em_jt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('em.em_jt_id', $this->getStringParameter('em_jt_id'));
        }
        if ($this->isValidParameter('em_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('em.em_number', $this->getStringParameter('em_number'));
        }
        if ($this->isValidParameter('em_identity_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('em.em_identity_number', $this->getStringParameter('em_identity_number'));
        }
        if ($this->isValidParameter('em_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('em.em_name', $this->getStringParameter('em_name'));
        }

        # return the list where condition.
        return $wheres;
    }
}
