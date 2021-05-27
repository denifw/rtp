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
 * Class to manage the creation of the listing ServiceOrder page.
 *
 * @package    app
 * @subpackage Model\Listing\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class ServiceOrder extends AbstractListingModel
{

    /**
     * ServiceOrder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceOrder');
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
                'text' => Trans::getFmsWord('onService'),
                'value' => Trans::getFmsWord('onService')
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
        $statusField = $this->Field->getSelect('svo_status', $this->getStringParameter('svo_status'));
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
                'svo_number' => Trans::getFmsWord('number'),
                'svo_eq_name' => Trans::getFmsWord('equipment'),
                'svo_order_date' => Trans::getFmsWord('orderDate'),
                'svo_planning_date' => Trans::getFmsWord('planningDate'),
                'svo_manager_name' => Trans::getFmsWord('manager'),
                'svo_request_by_name' => Trans::getFmsWord('requestBy'),
                'svo_status' => Trans::getFmsWord('status')
            ]
        );
        # Load the data for ServiceOrder.
        $listingData = [];
        $tempData = $this->loadData();
        foreach ($tempData as $row) {
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['svo_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['svo_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['svo_finish_on']) === true && empty($row['svo_start_service_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onService'));
            } elseif (empty($row['svo_start_service_date']) === true && empty($row['svo_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['svo_approved_on']) === true && empty($row['svr_id']) === false) {
                if (empty($row['svr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['svo_order_date'] = DateTimeParser::format($row['svo_order_date'], 'Y-m-d', 'd M Y');
            $row['svo_planning_date'] = DateTimeParser::format($row['svo_planning_date'], 'Y-m-d', 'd M Y');
            $row['svo_status'] = $status;
            $listingData[] = $row;
        }
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('svo_order_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('svo_planning_date', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('svo_meter', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('svo_status', 'style', 'text-align: center');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['svo_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['svo_id']);
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
        $query = 'SELECT count(DISTINCT (svo_id)) AS total_rows
                  FROM service_order AS svo
                       INNER JOIN equipment AS eq ON eq.eq_id = svo.svo_eq_id
                       INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                       INNER JOIN users AS manager ON manager.us_id = svo.svo_manager_id
                       INNER JOIN users AS requestBy ON requestBy.us_id = svo.svo_request_by_id
                       LEFT OUTER JOIN service_order_request AS svr ON svr.svr_id = svo.svo_svr_id';
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
        $query = 'SELECT svo.svo_id, svo.svo_ss_id, svo.svo_number, svo.svo_eq_id, svo.svo_svr_id, svo.svo_meter, svo.svo_order_date, svo.svo_planning_date,
                         svo.svo_manager_id, svo.svo_request_by_id, svo.svo_approved_by, svo.svo_approved_on, svo.svo_start_service_by, svo.svo_start_service_date,
                         svo.svo_finish_by, svo.svo_finish_on, svo.svo_deleted_on, eg.eg_name || \' - \' || eq.eq_description as svo_eq_name, manager.us_name as svo_manager_name,
                         requestBy.us_name as svo_request_by_name, eq.eq_primary_meter, svr.svr_id, svr.svr_reject_reason
                  FROM service_order AS svo
                       INNER JOIN equipment AS eq ON eq.eq_id = svo.svo_eq_id
                       INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                       INNER JOIN users AS manager ON manager.us_id = svo.svo_manager_id
                       INNER JOIN users AS requestBy ON requestBy.us_id = svo.svo_request_by_id
                       LEFT OUTER JOIN service_order_request AS svr ON svr.svr_id = svo.svo_svr_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY svo.svo_id, svo.svo_ss_id, svo.svo_number, svo.svo_eq_id, svo.svo_svr_id, svo.svo_meter, svo.svo_order_date, svo.svo_planning_date,
                         svo.svo_manager_id, svo.svo_request_by_id, svo.svo_approved_by, svo.svo_approved_on, svo.svo_start_service_by, svo.svo_start_service_date,
                         svo.svo_finish_by, svo.svo_finish_on, eg.eg_name, eq.eq_description, manager.us_name, requestBy.us_name, eq.eq_primary_meter, svr.svr_id, svr.svr_reject_reason';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY svo.svo_deleted_on DESC, svo.svo_finish_on DESC, svo.svo_order_date DESC';
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
        $wheres[] = '(svo.svo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('eq_id')) {
            $wheres[] = '(svo.svo_eq_id = ' . $this->getIntParameter('eq_id') . ')';
        }
        if ($this->isValidParameter('order_date_from') === true) {
            if ($this->isValidParameter('order_date_until') === true) {
                $wheres[] = "(svo.svo_order_date >= '" . $this->getStringParameter('order_date_from') . "')";
            } else {
                $wheres[] = "(svo.svo_order_date = '" . $this->getStringParameter('order_date_from') . "')";
            }
        }
        if ($this->isValidParameter('order_date_until') === true) {
            if ($this->isValidParameter('order_date_from') === true) {
                $wheres[] = "(svo.svo_order_date <= '" . $this->getStringParameter('order_date_until') . "')";
            } else {
                $wheres[] = "(svo.svo_order_date = '" . $this->getStringParameter('order_date_until') . "')";
            }
        }
        if ($this->isValidParameter('svo_status')) {
            $status = $this->getStringParameter('svo_status');
            if ($status === 'Draft') {
                $wheres[] = '(svr.svr_id IS NULL)';
            }
            if ($status === 'Request') {
                $wheres[] = '(svr.svr_id IS NOT NULL) AND (svr.svr_reject_reason IS NULL) AND (svo.svo_approved_on IS NULL) AND (svo.svo_deleted_on IS NULL)';
            }
            if ($status === 'Reject') {
                $wheres[] = '(svr.svr_id IS NOT NULL) AND (svr.svr_reject_reason IS NOT NULL) AND (svo.svo_deleted_on IS NULL)';
            }
            if ($status === 'Approve') {
                $wheres[] = '(svo.svo_approved_on IS NOT NULL) AND (svo.svo_start_service_date IS NULL)';
            }
            if ($status === 'On Service') {
                $wheres[] = '(svo.svo_start_service_date IS NOT NULL) AND (svo.svo_finish_on IS NULL)';
            }
            if ($status === 'Finish') {
                $wheres[] = '(svo.svo_finish_on IS NOT NULL)';
            }
            if ($status === 'Deleted') {
                $wheres[] = '(svo.svo_deleted_on IS NOT NULL)';
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
