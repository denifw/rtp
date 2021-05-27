<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\TicketDao;

/**
 * Class to control the system of Ticket.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Ticket extends AbstractListingModel
{

    /**
     * Ticket constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ticket');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relationField = $this->Field->getSingleSelect('relation', 'tc_rel_name', $this->getStringParameter('tc_rel_name'));
        $relationField->setHiddenField('tc_rel_id', $this->getIntParameter('tc_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $priorityField = $this->Field->getSingleSelect('sty', 'tc_priority_name', $this->getStringParameter('tc_priority_name'));
        $priorityField->setHiddenField('tc_priority_id', $this->getIntParameter('tc_priority_id'));
        $priorityField->addParameter('sty_group', 'taskpriority');
        $priorityField->setEnableNewButton(false);
        $priorityField->setEnableDetailButton(false);
        $statusField = $this->Field->getSingleSelect('sty', 'tc_status_name', $this->getStringParameter('tc_status_name'));
        $statusField->setHiddenField('tc_status_id', $this->getIntParameter('tc_status_id'));
        $statusField->addParameter('sty_group', 'taskstatus');
        $statusField->setEnableNewButton(false);
        $statusField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getCrmWord('number'), $this->Field->getText('tc_number', $this->getStringParameter('tc_number')));
        $this->ListingForm->addField(Trans::getCrmWord('subject'), $this->Field->getText('tc_subject', $this->getStringParameter('tc_subject')));
        $this->ListingForm->addField(Trans::getCrmWord('relation'), $relationField);
        $this->ListingForm->addField(Trans::getCrmWord('priority'), $priorityField);
        $this->ListingForm->addField(Trans::getCrmWord('status'), $statusField);
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
            'tc_number' => Trans::getCrmWord('number'),
            'tc_rel_name' => Trans::getCrmWord('relation'),
            'tc_subject' => Trans::getCrmWord('subject'),
            'tc_report_date' => Trans::getCrmWord('reportDate'),
            'tc_priority_name' => Trans::getCrmWord('priority'),
            'tc_status_name' => Trans::getCrmWord('status')
        ]);
        # Load the data for Ticket.
        $data = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($data);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['tc_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['tc_id']);
        }
        $this->ListingTable->setColumnType('tc_report_date', 'date');
        $this->ListingTable->addColumnAttribute('tc_priority_name', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('tc_status_name', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return TicketDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $orders = [];
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $orders = $this->ListingSort->getOrderByFields();
        } else {
            $orders[] = 'tc_finish_on DESC';
            $orders[] = 'tc_report_date DESC';
        }

        return TicketDao::loadData(
            $this->getWhereCondition(),
            $orders,
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
        $wheres[] = SqlHelper::generateNumericCondition('tc_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('tc_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tc_number', $this->getStringParameter('tc_number'));
        }
        if ($this->isValidParameter('tc_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tc_rel_id', $this->getIntParameter('tc_rel_id'));
        }
        if ($this->isValidParameter('tc_priority_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tc_priority_id', $this->getIntParameter('tc_priority_id'));
        }
        if ($this->isValidParameter('tc_status_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tc_status_id', $this->getIntParameter('tc_status_id'));
        }
        if ($this->isValidParameter('tc_subject') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tc_subject', $this->getStringParameter('tc_subject'));
        }

        # return the list where condition.
        return $wheres;
    }

    /**
     * Do prepare data
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $status = new LabelGray(Trans::getCrmWord('open'));
            if (empty($row['tc_start_on']) === false && empty($row['tc_finish_on']) === true) {
                $status = new LabelWarning(Trans::getCrmWord('inProgress'));
            } elseif (empty($row['tc_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getCrmWord('finish'));
            }
            $row['tc_status_name'] = $status;
            $priority = '';
            if ($row['tc_priority_name'] === 'Low') {
                $priority = new LabelSuccess($row['tc_priority_name']);
            }
            if ($row['tc_priority_name'] === 'Medium') {
                $priority = new LabelWarning($row['tc_priority_name']);
            }
            if ($row['tc_priority_name'] === 'High') {
                $priority = new LabelDanger($row['tc_priority_name']);
            }
            $row['tc_priority_name'] = $priority;
            $results[] = $row;
        }

        return $results;
    }
}
