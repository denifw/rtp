<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CustomerService\SalesOrderIssueDao;
use Mpdf\Tag\S;

/**
 * Class to control the system of SalesOrderIssue.
 *
 * @package    app
 * @subpackage Model\Listing\CustomerService
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderIssue extends AbstractListingModel
{

    /**
     * SalesOrderIssue constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'soi');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        #Create Relation Field
        $soiRelField = $this->Field->getSingleSelect('relation', 'soi_rel_name', $this->getStringParameter('soi_rel_name'));
        $soiRelField->setHiddenField('soi_rel_id', $this->getIntParameter('soi_rel_id'));
        $soiRelField->addParameter('rel_ss_id', $this->User->getSsId());
        $soiRelField->setEnableNewButton(false);
        $soiRelField->addClearField('soi_so_number');
        $soiRelField->addClearField('soi_so_id');

        #Create Priority Field
        $soiPrtField = $this->Field->getSingleSelect('sty', 'soi_sty_name', $this->getStringParameter('soi_sty_name'));
        $soiPrtField->setHiddenField('soi_priority_id', $this->getIntParameter('soi_priority_id'));
        $soiPrtField->addParameter('sty_group', 'priorityissue');
        $soiPrtField->setEnableNewButton(false);

        #Create Sales Order Field
        $soiSoField = $this->Field->getSingleSelect('so', 'soi_so_number', $this->getStringParameter('soi_so_number'));
        $soiSoField->setHiddenField('soi_so_id', $this->getIntParameter('soi_so_id'));
        $soiSoField->addOptionalParameterById('so_rel_id', 'soi_rel_id');
        $soiSoField->addParameter('so_ss_id', $this->User->getSsId());
        $soiSoField->setEnableNewButton(false);

        #Create Status Field
        $conditionField = $this->Field->getRadioGroup('soi_status', $this->getStringParameter('soi_status'));
        $conditionField->addRadio(Trans::getWord('open'), 'O');
        $conditionField->addRadio(Trans::getWord('closed'), 'C');

        #create type field
        $this->ListingForm->addField(Trans::getWord('issueNumber'), $this->Field->getText('soi_number', $this->getStringParameter('soi_number')));
        $this->ListingForm->addField(Trans::getWord('customer'), $soiRelField);
        $this->ListingForm->addField(Trans::getWord('salesOrder'), $soiSoField);
        $this->ListingForm->addField(Trans::getWord('priority'), $soiPrtField);
        $this->ListingForm->addField(Trans::getWord('reportDateForm'), $this->Field->getCalendar('soi_report_date_form', $this->getStringParameter('soi_report_date_form')));
        $this->ListingForm->addField(Trans::getWord('reportDateUntil'), $this->Field->getCalendar('soi_report_date_until', $this->getStringParameter('soi_report_date_until')));
        $this->ListingForm->addField(Trans::getWord('soReference'), $this->Field->getText('soi_so_reference', $this->getStringParameter('soi_so_reference')));
        $this->ListingForm->addField(Trans::getWord('status'), $conditionField);

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
            'soi_number' => Trans::getWord('number'),
            'soi_customer' => Trans::getWord('customer'),
            'soi_so' => Trans::getWord('salesOrder'),
            'soi_subject' => Trans::getWord('subject'),
            'soi_report_date' => Trans::getWord('reportDate'),
            'soi_assign_name' => Trans::getWord('assignedTo'),
            'soi_sty_name' => Trans::getWord('priority'),
            'soi_status' => Trans::getWord('status'),
        ]);
        # Load the data for SalesOrderIssue.
        $this->ListingTable->addRows($this->doPrepareData($this->loadData()));
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['soi_id']);
        }
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['soi_id']);
        $this->ListingTable->setColumnType('soi_report_date', 'date');
        $this->ListingTable->addColumnAttribute('soi_sty_name', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('soi_status', 'style', 'text-align: center;');

    }

    /**
     * Function to do prepare data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];

        foreach ($data as $row) {
            $row['soi_sty_name'] = new Label($row['soi_sty_name'], $row['soi_sty_label']);
            if (empty($row['soi_finish_on']) === false) {
                $row['soi_status'] = new LabelSuccess(Trans::getWord('closed'));
            } else {
                $row['soi_status'] = new LabelPrimary(Trans::getWord('open'));
            }
            $row['soi_customer'] = StringFormatter::generateTableView([
                $row['soi_rel_name'],
                Trans::getWord('pic') . ' : ' . $row['soi_pic_name'],
            ], 'text-align: left;');
            $row['soi_so'] = StringFormatter::generateTableView([
                $row['soi_so_number'],
                $row['soi_srv_name'],
                $row['soi_jo_number'],
                Trans::getWord('pic') . ' : ' . $row['soi_pic_field_name'],
            ], 'text-align: left;');

            $results[] = $row;
        }


        return $results;
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SalesOrderIssueDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $orderBys = $this->ListingSort->getOrderByFields();
        if (empty($orderBys) === true) {
            $orderBys = [
                'soi.soi_finish_on DESC',
                'soi.soi_id'
            ];
        }
        return SalesOrderIssueDao::loadData(
            $this->getWhereCondition(),
            $orderBys,
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

        $wheres[] = SqlHelper::generateNumericCondition('soi.soi_ss_id', $this->User->getSsId());
        $wheres[] = '(soi.soi_deleted_on IS NULL)';

        #Filter Sales Order Issue Number
        if ($this->isValidParameter('soi_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('soi_number', $this->getStringParameter('soi_number'));
        }
        #Filter Sales Order Number
        if ($this->isValidParameter('soi_so_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('soi.soi_so_id', $this->getIntParameter('soi_so_id'));
        }
        #Filter Relation
        if ($this->isValidParameter('soi_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('soi.soi_rel_id', $this->getIntParameter('soi_rel_id'));
        }
        #Filter Sales Order Issue Date
        if ($this->isValidParameter('soi_report_date_form') === true) {
            if ($this->isValidParameter('soi_report_date_until') === true) {
                $wheres[] = "(soi.soi_report_date >= '" . $this->getStringParameter('soi_report_date_form') . "')";
            } else {
                $wheres[] = "(soi.soi_report_date = '" . $this->getStringParameter('soi_report_date_form') . "')";
            }
        }
        if ($this->isValidParameter('soi_report_date_until') === true) {
            if ($this->isValidParameter('soi_report_date_form') === true) {
                $wheres[] = "(soi.soi_report_date <= '" . $this->getStringParameter('soi_report_date_until') . "')";
            } else {
                $wheres[] = "(soi.soi_report_date = '" . $this->getStringParameter('soi_report_date_until') . "')";
            }
        }
        #Filter Priority
        if ($this->isValidParameter('soi_sty_id')) {
            $wheres[] = SqlHelper::generateNumericCondition('soi.soi_sty_id', $this->getIntParameter('soi_sty_id'));
        }

        #Filter Status
        if ($this->isValidParameter('soi_status') === true) {
            $status = $this->getStringParameter('soi_status');
            if ($status === 'O') {
                $wheres[] = '(soi.soi_finish_on IS NULL)';
            } else {
                $wheres[] = '(soi.soi_finish_on IS NOT NULL)';
            }
        }
        # Filter for so reference
        if ($this->isValidParameter('soi_so_reference') === true) {
            $wheres[] = SqlHelper::generateOrLikeCondition(['so.so_customer_ref', 'so.so_bl_ref', 'so.so_packing_ref', 'so.so_aju_ref', 'so.so_sppb_ref'], $this->getStringParameter('soi_so_reference'));
        }

        # return the list where condition.
        return $wheres;
    }

}
