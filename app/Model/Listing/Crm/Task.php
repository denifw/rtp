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
use App\Model\Dao\Crm\TaskDao;

/**
 * Class to control the system of Task.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Task extends AbstractListingModel
{

    /**
     * Task constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'task');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relationField = $this->Field->getSingleSelect('relation', 'tsk_rel_name', $this->getStringParameter('tsk_rel_name'));
        $relationField->setHiddenField('tsk_rel_id', $this->getIntParameter('tsk_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $relationField->setEnableDetailButton(false);
        $typeField = $this->Field->getSingleSelect('sty', 'tsk_type_name', $this->getStringParameter('tsk_type_name'));
        $typeField->setHiddenField('tsk_type_id', $this->getIntParameter('tsk_type_id'));
        $typeField->addParameter('sty_group', 'tasktype');
        $typeField->setEnableNewButton(false);
        $typeField->setEnableDetailButton(false);
        $priorityField = $this->Field->getSingleSelect('sty', 'tsk_priority_name', $this->getStringParameter('tsk_priority_name'));
        $priorityField->setHiddenField('tsk_priority_id', $this->getIntParameter('tsk_priority_id'));
        $priorityField->addParameter('sty_group', 'taskpriority');
        $priorityField->setEnableNewButton(false);
        $priorityField->setEnableDetailButton(false);
        $statusField = $this->Field->getSingleSelect('sty', 'tsk_status_name', $this->getStringParameter('tsk_status_name'));
        $statusField->setHiddenField('tsk_status_id', $this->getIntParameter('tsk_status_id'));
        $statusField->addParameter('sty_group', 'taskstatus');
        $statusField->setEnableNewButton(false);
        $statusField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getCrmWord('number'), $this->Field->getText('tsk_number', $this->getStringParameter('tsk_number')));
        $this->ListingForm->addField(Trans::getCrmWord('subject'), $this->Field->getText('tsk_subject', $this->getStringParameter('tsk_subject')));
        $this->ListingForm->addField(Trans::getCrmWord('relation'), $relationField);
        $this->ListingForm->addField(Trans::getCrmWord('taskType'), $typeField);
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
            'tsk_number' => Trans::getCrmWord('number'),
            'tsk_rel_name' => Trans::getCrmWord('relation'),
            'tsk_subject' => Trans::getCrmWord('subject'),
            'tsk_type_name' => Trans::getCrmWord('taskType'),
            'tsk_priority_name' => Trans::getCrmWord('priority'),
            'tsk_start_date' => Trans::getCrmWord('startDate'),
            'tsk_status_name' => Trans::getCrmWord('status')
        ]);
        # Load the data for Task.
        $data = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($data);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['tsk_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['tsk_id']);
        }
        $this->ListingTable->setColumnType('tsk_start_date', 'date');
        $this->ListingTable->addColumnAttribute('tsk_priority_name', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('tsk_status_name', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return TaskDao::loadTotalData($this->getWhereCondition());
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
            $orders[] = 'tsk_finish_on DESC';
            $orders[] = 'tsk_start_date DESC';
        }

        return TaskDao::loadData(
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
        $wheres[] = SqlHelper::generateNumericCondition('tsk_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('tsk_') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tsk_number', $this->getStringParameter('tsk_number'));
        }
        if ($this->isValidParameter('tsk_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tsk_rel_id', $this->getIntParameter('tsk_rel_id'));
        }
        if ($this->isValidParameter('tsk_type_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tsk_type_id', $this->getIntParameter('tsk_type_id'));
        }
        if ($this->isValidParameter('tsk_priority_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tsk_priority_id', $this->getIntParameter('tsk_priority_id'));
        }
        if ($this->isValidParameter('tsk_status_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('tsk_status_id', $this->getIntParameter('tsk_status_id'));
        }
        if ($this->isValidParameter('tsk_subject') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('tsk_subject', $this->getStringParameter('tsk_subject'));
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
            if (empty($row['tsk_start_on']) === false && empty($row['tsk_finish_on']) === true) {
                $status = new LabelWarning(Trans::getCrmWord('inProgress'));
            } elseif (empty($row['tsk_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getCrmWord('finish'));
            }
            $row['tsk_status_name'] = $status;
            $priority = '';
            if ($row['tsk_priority_name'] === 'Low') {
                $priority = new LabelSuccess($row['tsk_priority_name']);
            }
            if ($row['tsk_priority_name'] === 'Medium') {
                $priority = new LabelWarning($row['tsk_priority_name']);
            }
            if ($row['tsk_priority_name'] === 'High') {
                $priority = new LabelDanger($row['tsk_priority_name']);
            }
            $row['tsk_priority_name'] = $priority;
            $results[] = $row;
        }

        return $results;
    }
}
