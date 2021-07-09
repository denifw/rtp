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
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Administration\EmployeeLoanDao;

/**
 * Class to control the system of EmployeeLoan.
 *
 * @package    app
 * @subpackage Model\Listing\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class EmployeeLoan extends AbstractListingModel
{

    /**
     * EmployeeLoan constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'el');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $emField = $this->Field->getSingleSelect('em', 'el_employee', $this->getStringParameter('el_employee'));
        $emField->setHiddenField('el_em_id', $this->getStringParameter('el_em_id'));
        $emField->addParameter('em_ss_id', $this->User->getSsId());
        $emField->setEnableNewButton(false);

        $typeField = $this->Field->getSelect('el_type', $this->getStringParameter('el_type'));
        $typeField->addOption(Trans::getWord('loan'), 'L');
        $typeField->addOption(Trans::getWord('payment'), 'P');

        $statusField = $this->Field->getSelect('el_status', $this->getStringParameter('el_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('requested'), '2');
        $statusField->addOption(Trans::getWord('waitingPayment'), '3');
        $statusField->addOption(Trans::getWord('paid'), '4');

        $this->ListingForm->addField(Trans::getWord('employee'), $emField);
        $this->ListingForm->addField(Trans::getWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getWord('status'), $statusField);
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
            'el_employee' => Trans::getWord('employee'),
            'el_type' => Trans::getWord('type'),
            'el_amount' => Trans::getWord('amount'),
            'el_date' => Trans::getWord('date'),
            'el_notes' => Trans::getWord('notes'),
            'el_pic' => Trans::getWord('pic'),
        ]);
        # Load the data for EmployeeLoan.
        $this->ListingTable->addRows($this->loadData());
        if ($this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['el_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return EmployeeLoanDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return EmployeeLoanDao::loadData(
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
        var_dump('To do');
        exit;
        # Set where conditions
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('el_ss_id', $this->User->getSsId());

        if ($this->isValidParameter('el_em_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('el.el_em_id', $this->getStringParameter('el_em_id'));
        }
        if ($this->isValidParameter('el_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('el.el_type', $this->getStringParameter('el_type'));
        }
        # return the list where condition.
        return $wheres;
    }
}
