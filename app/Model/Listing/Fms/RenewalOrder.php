<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Fms;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing RenewalOrder page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class RenewalOrder extends AbstractListingModel
{

    /**
     * RenewalOrder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'renewalOrder');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        #Create equiment single select
        $eqField = $this->Field->getSingleSelect('equipment', 'eq_name', $this->getStringParameter('eq_name'), 'loadSingleSelectDataForFms');
        $eqField->setHiddenField('eq_id', $this->getIntParameter('eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setEnableNewButton(false);
        $eqField->setEnableDetailButton(false);
        $statusData = [
            [
                'text' => Trans::getFmsWord('draft'),
                'value' => Trans::getFmsWord('draft')
            ],
            [
                'text' => Trans::getFmsWord('request'),
                'value' => Trans::getFmsWord('request')
            ],
            [
                'text' => Trans::getFmsWord('reject'),
                'value' => Trans::getFmsWord('reject')
            ],
            [
                'text' => Trans::getFmsWord('approve'),
                'value' => Trans::getFmsWord('approve')
            ],
            [
                'text' => Trans::getFmsWord('onProgress'),
                'value' => Trans::getFmsWord('onProgress')
            ],
            [
                'text' => Trans::getFmsWord('finish'),
                'value' => Trans::getFmsWord('finish')
            ],
            [
                'text' => Trans::getFmsWord('deleted'),
                'value' => Trans::getFmsWord('deleted')
            ],
        ];
        $statusField = $this->Field->getSelect('rno_status', $this->getStringParameter('rno_status'));
        $statusField->addOptions($statusData);
        $this->ListingForm->addField(Trans::getFmsWord('equipment'), $eqField);
        $this->ListingForm->addField(Trans::getFmsWord('orderDateFrom'), $this->Field->getCalendar('order_date_from', $this->getStringParameter('order_date_from')));
        $this->ListingForm->addField(Trans::getFmsWord('orderDateUntil'), $this->Field->getCalendar('order_date_until', $this->getStringParameter('order_date_until')));
        $this->ListingForm->addField(Trans::getFmsWord('status'), $statusField);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'rno_number' => Trans::getFmsWord('number'),
                'rno_eq_name' => Trans::getFmsWord('equipment'),
                'rno_order_date' => Trans::getFmsWord('orderDate'),
                'rno_planning_date' => Trans::getFmsWord('planningDate'),
                'rno_manager_name' => Trans::getFmsWord('manager'),
                'rno_request_by_name' => Trans::getFmsWord('requestBy'),
                'rno_status' => Trans::getFmsWord('status')
            ]
        );
        # Load the data for ServiceOrder.
        $listingData = [];
        $tempData = $this->loadData();
        foreach ($tempData as $row) {
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['rno_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['rno_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['rno_finish_on']) === true && empty($row['rno_start_renewal_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onProgress'));
            } elseif (empty($row['rno_start_renewal_date']) === true && empty($row['rno_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['rno_approved_on']) === true && empty($row['rnr_id']) === false) {
                if (empty($row['rnr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['rno_order_date'] = DateTimeParser::format($row['rno_order_date'], 'Y-m-d', 'd M Y');
            $row['rno_planning_date'] = DateTimeParser::format($row['rno_planning_date'], 'Y-m-d', 'd M Y');
            $row['rno_status'] = $status;
            $listingData[] = $row;
        }
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('rno_order_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('rno_planning_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('rno_status', 'style', 'text-align: center');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['rno_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['rno_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (rno_id)) AS total_rows
                  FROM renewal_order AS rno
                       INNER JOIN equipment AS eq ON eq.eq_id = rno.rno_eq_id
                       INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                       INNER JOIN users AS manager ON manager.us_id = rno.rno_manager_id
                       INNER JOIN users AS requestBy ON requestBy.us_id = rno.rno_request_by_id
                       LEFT OUTER JOIN renewal_order_request AS rnr ON rnr.rnr_id = rno.rno_rnr_id ';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT rno.rno_id, rno.rno_ss_id, rno.rno_eq_id, rno.rno_rnr_id, rno.rno_number, rno.rno_order_date, rno.rno_planning_date,
                         rno.rno_vendor_id, rno.rno_manager_id, rno.rno_request_by_id, rno.rno_remark, rno.rno_deleted_reason,
                         rno.rno_approved_on, rno.rno_start_renewal_date, rno.rno_finish_on,
                         eg.eg_name || \' - \' || eq.eq_description as rno_eq_name, manager.us_name as rno_manager_name,
                         requestBy.us_name as rno_request_by_name, rnr.rnr_id, rnr.rnr_reject_reason
                  FROM renewal_order AS rno
                       INNER JOIN equipment AS eq ON eq.eq_id = rno.rno_eq_id
                       INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                       INNER JOIN users AS manager ON manager.us_id = rno.rno_manager_id
                       INNER JOIN users AS requestBy ON requestBy.us_id = rno.rno_request_by_id
                       LEFT OUTER JOIN renewal_order_request AS rnr ON rnr.rnr_id = rno.rno_rnr_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY rno.rno_id, rno.rno_ss_id, rno.rno_eq_id, rno.rno_rnr_id, rno.rno_number, rno.rno_order_date, rno.rno_planning_date,
                             rno.rno_vendor_id, rno.rno_manager_id, rno.rno_request_by_id, rno.rno_remark, rno.rno_deleted_reason,
                             rno.rno_approved_on, rno.rno_start_renewal_date, rno.rno_finish_on,
                             eg.eg_name, eq.eq_description, manager.us_name, requestBy.us_name, rnr.rnr_id, rnr.rnr_reject_reason';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(rno.rno_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id')) {
            $wheres[] = '(rno.rno_eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        if ($this->isValidParameter('order_date_from') === true) {
            if ($this->isValidParameter('order_date_until') === true) {
                $wheres[] = "(rno.rno_order_date >= '" . $this->getStringParameter('order_date_from') . "')";
            } else {
                $wheres[] = "(rno.rno_order_date = '" . $this->getStringParameter('order_date_from') . "')";
            }
        }
        if ($this->isValidParameter('order_date_until') === true) {
            if ($this->isValidParameter('order_date_from') === true) {
                $wheres[] = "(rno.rno_order_date <= '" . $this->getStringParameter('order_date_until') . "')";
            } else {
                $wheres[] = "(rno.rno_order_date = '" . $this->getStringParameter('order_date_until') . "')";
            }
        }
        if ($this->isValidParameter('rno_status')) {
            $status = $this->getStringParameter('rno_status');
            if ($status === 'Draft') {
                $wheres[] = '(rnr.rnr_id IS NULL)';
            }
            if ($status === 'Request') {
                $wheres[] = '(rnr.rnr_id IS NOT NULL) AND (rnr.rnr_reject_reason IS NULL) AND (rno.rno_approved_on IS NULL) AND (rno.rno_deleted_on IS NULL)';
            }
            if ($status === 'Reject') {
                $wheres[] = '(rnr.rnr_id IS NOT NULL) AND (rnr.rnr_reject_reason IS NOT NULL) AND (rno.rno_deleted_on IS NULL)';
            }
            if ($status === 'Approve') {
                $wheres[] = '(rno.rno_approved_on IS NOT NULL) AND (rno.rno_start_renewal_date IS NULL)';
            }
            if ($status === 'On Progress') {
                $wheres[] = '(rno.rno_start_renewal_date IS NOT NULL) AND (rno.rno_finish_on IS NULL)';
            }
            if ($status === 'Finish') {
                $wheres[] = '(rno.rno_finish_on IS NOT NULL)';
            }
            if ($status === 'Deleted') {
                $wheres[] = '(rno.rno_deleted_on IS NOT NULL)';
            }
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }


        # return the where query.
        return $strWhere;
    }
}
