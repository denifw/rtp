<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Job\Warehouse;

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
 * Class to manage the creation of the listing JobTransfer page.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class JobStockTransfer extends AbstractListingModel
{

    /**
     * JobTransfer constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockTransfer');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Customer
        $customerField = $this->Field->getSingleSelect('relation', 'jtr_rel_name', $this->getStringParameter('jtr_rel_name'), 'loadGoodsOwnerData');
        $customerField->setHiddenField('jtr_rel_id', $this->getIntParameter('jtr_rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableNewButton(false);
        $customerField->setEnableDetailButton(false);
        # Warehouse Origin
        $whoField = $this->Field->getSingleSelect('warehouse', 'jtr_who_name', $this->getStringParameter('jtr_who_name'));
        $whoField->setHiddenField('jtr_who_id', $this->getIntParameter('jtr_who_id'));
        $whoField->addParameter('wh_ss_id', $this->User->getSsId());
        $whoField->setEnableDetailButton(false);
        $whoField->setEnableNewButton(false);
        # Warehouse Destination
        $whdField = $this->Field->getSingleSelect('warehouse', 'jtr_whd_name', $this->getStringParameter('jtr_whd_name'));
        $whdField->setHiddenField('jtr_whd_id', $this->getIntParameter('jtr_whd_id'));
        $whdField->addParameter('wh_ss_id', $this->User->getSsId());
        $whdField->setEnableDetailButton(false);
        $whdField->setEnableNewButton(false);
        $statusData = [
            [
                'text' => Trans::getWhsWord('draft'),
                'value' => Trans::getWhsWord('draft')
            ],
            [
                'text' => Trans::getWhsWord('publish'),
                'value' => Trans::getWhsWord('publish')
            ],
            [
                'text' => Trans::getWhsWord('outboundProcess'),
                'value' => Trans::getWhsWord('outboundProcess')
            ],
            [
                'text' => Trans::getWhsWord('delivery'),
                'value' => Trans::getWhsWord('delivery')
            ],
            [
                'text' => Trans::getWhsWord('inboundProcess'),
                'value' => Trans::getWhsWord('inboundProcess')
            ],
            [
                'text' => Trans::getWhsWord('finish'),
                'value' => Trans::getWhsWord('finish')
            ],
            [
                'text' => Trans::getWhsWord('deleted'),
                'value' => Trans::getWhsWord('deleted')
            ],
        ];
        $statusField = $this->Field->getSelect('jtr_status', $this->getStringParameter('jtr_status'));
        $statusField->addOptions($statusData);
        $this->ListingForm->addField(Trans::getWhsWord('customer'), $customerField);
        $this->ListingForm->addField(Trans::getWhsWord('warehouseOrigin'), $whoField);
        $this->ListingForm->addField(Trans::getWhsWord('warehouseDestination'), $whdField);
        $this->ListingForm->addField(Trans::getWhsWord('status'), $statusField);
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
                'jtr_number' => Trans::getWhsWord('jobNumber'),
                'jtr_rel_name' => Trans::getWhsWord('customer'),
                'jtr_who_name' => Trans::getWhsWord('warehouseOrigin'),
                'jtr_whd_name' => Trans::getWhsWord('warehouseDestination'),
                'jtr_transporter_name' => Trans::getWhsWord('transporter'),
                'jtr_status' => Trans::getWhsWord('status'),
            ]
        );
        # Load the data for JobTransfer.
        $listingData = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->addColumnAttribute('jtr_status', 'style', 'text-align:center');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jtr_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jtr_id']);
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
        $query = 'SELECT count(DISTINCT (jtr_id)) AS total_rows
                  FROM   job_stock_transfer AS jtr INNER JOIN
                         warehouse AS who ON who.wh_id = jtr.jtr_who_id INNER JOIN
                         users AS whous ON whous.us_id = jtr.jtr_who_us_id  INNER JOIN
                         warehouse AS whd ON whd.wh_id = jtr.jtr_whd_id INNER JOIN
                         users AS whdus ON whdus.us_id = jtr.jtr_whd_us_id INNER JOIN
                         relation AS transporter ON transporter.rel_id = jtr.jtr_transporter_id INNER JOIN
                         relation AS customer ON customer.rel_id = jtr.jtr_rel_id LEFT OUTER JOIN
                         contact_person AS pic ON pic.cp_id = jtr.jtr_pic_id';
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
        $query = 'SELECT jtr.jtr_id, jtr.jtr_ss_id, jtr.jtr_number, jtr.jtr_rel_id, jtr.jtr_customer_ref, jtr.jtr_pic_id, jtr.jtr_who_id, jtr.jtr_who_us_id, jtr.jtr_who_date, jtr.jtr_who_time,
                         jtr.jtr_who_id, jtr.jtr_whd_us_id, jtr.jtr_whd_date, jtr.jtr_whd_time, jtr.jtr_transporter_id, jtr.jtr_truck_plate,
                         jtr.jtr_container_number, jtr.jtr_seal_number, jtr.jtr_driver, jtr.jtr_driver_phone, jtr.jtr_ji_jo_id, jtr.jtr_job_jo_id,
                         jtr.jtr_publish_by, jtr.jtr_publish_on, jtr.jtr_start_out_on, jtr.jtr_end_out_on, jtr.jtr_start_in_on, jtr.jtr_end_in_on, jtr.jtr_deleted_on, jtr.jtr_deleted_reason,
                         who.wh_name AS jtr_who_name, whous.us_name AS jtr_who_us_name, whd.wh_name AS jtr_whd_name, whdus.us_name AS jtr_whd_us_name,
                         transporter.rel_name AS jtr_transporter_name, customer.rel_name AS jtr_rel_name, pic.cp_name AS jtr_pic_name
                  FROM   job_stock_transfer AS jtr INNER JOIN
                         warehouse AS who ON who.wh_id = jtr.jtr_who_id INNER JOIN
                         users AS whous ON whous.us_id = jtr.jtr_who_us_id  INNER JOIN
                         warehouse AS whd ON whd.wh_id = jtr.jtr_whd_id INNER JOIN
                         users AS whdus ON whdus.us_id = jtr.jtr_whd_us_id INNER JOIN
                         relation AS transporter ON transporter.rel_id = jtr.jtr_transporter_id INNER JOIN
                         relation AS customer ON customer.rel_id = jtr.jtr_rel_id LEFT OUTER JOIN
                         contact_person AS pic ON pic.cp_id = jtr.jtr_pic_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY jtr.jtr_id, jtr.jtr_ss_id, jtr.jtr_number,  jtr.jtr_rel_id, jtr.jtr_customer_ref, jtr.jtr_pic_id, jtr.jtr_who_id, jtr.jtr_who_us_id, jtr.jtr_who_date, jtr.jtr_who_time,
                         jtr.jtr_who_id, jtr.jtr_whd_us_id, jtr.jtr_whd_date, jtr.jtr_whd_time, jtr.jtr_transporter_id, jtr.jtr_truck_plate,
                         jtr.jtr_container_number, jtr.jtr_seal_number, jtr.jtr_driver, jtr.jtr_driver_phone, jtr.jtr_ji_jo_id, jtr.jtr_job_jo_id,
                         jtr.jtr_publish_by, jtr.jtr_publish_on, jtr.jtr_start_out_on, jtr.jtr_end_out_on, jtr.jtr_start_in_on, jtr.jtr_end_in_on, jtr.jtr_deleted_on, jtr.jtr_deleted_reason,
                         who.wh_name, whous.us_name, whd.wh_name, whdus.us_name,
                         transporter.rel_name, customer.rel_name, pic.cp_name';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY jtr.jtr_id DESC';
        }

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data AS $row) {
            $status = new LabelGray(Trans::getWhsWord('draft'));
            if (empty($row['jtr_deleted_on']) === false) {
                $status = new LabelDark(Trans::getWhsWord('deleted'));
            } elseif (empty($row['jtr_end_in_on']) === false) {
                $status = new LabelSuccess(Trans::getWhsWord('finish'));
            } elseif (empty($row['jtr_end_in_on']) === true && empty($row['jtr_start_in_on']) === false) {
                $status = new LabelPrimary(Trans::getWhsWord('inboundProcess'));
            } elseif (empty($row['jtr_start_in_on']) === true && empty($row['jtr_end_out_on']) === false) {
                $status = new LabelInfo(Trans::getWhsWord('delivery'));
            } elseif (empty($row['jtr_end_out_on']) === true && empty($row['jtr_start_out_on']) === false) {
                $status = new LabelWarning(Trans::getWhsWord('outboundProcess'));
            } elseif (empty($row['jtr_start_out_on']) === true && empty($row['jtr_publish_on']) === false) {
                $status = new LabelDanger(Trans::getWhsWord('publish'));
            }
            $row['jtr_status'] = $status;
            $results[] = $row;
        }

        return $results;
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
        $wheres[] = '(jtr.jtr_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('jtr_rel_id') === true) {
            $wheres[] = '(jtr.jtr_rel_id = ' . $this->getIntParameter('jtr_rel_id') . ')';
        }
        if ($this->isValidParameter('jtr_who_id') === true) {
            $wheres[] = '(jtr.jtr_who_id = ' . $this->getIntParameter('jtr_who_id') . ')';
        }
        if ($this->isValidParameter('jtr_whd_id') === true) {
            $wheres[] = '(jtr.jtr_whd_id = ' . $this->getIntParameter('jtr_whd_id') . ')';
        }
        if ($this->isValidParameter('jtr_status')) {
            $status = $this->getStringParameter('jtr_status');
            if ($status === 'Draft') {
                $wheres[] = '(jtr.jtr_publish_on IS NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Publish') {
                $wheres[] = '(jtr.jtr_publish_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Outbound Process') {
                $wheres[] = '(jtr.jtr_end_out_on IS NULL)';
                $wheres[] = '(jtr.jtr_start_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_publish_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Delivery') {
                $wheres[] = '(jtr.jtr_start_in_on IS NULL)';
                $wheres[] = '(jtr.jtr_end_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_start_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_publish_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Inbound Process') {
                $wheres[] = '(jtr.jtr_end_in_on IS NULL)';
                $wheres[] = '(jtr.jtr_start_in_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_end_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_start_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_publish_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Finish') {
                $wheres[] = '(jtr.jtr_end_in_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_start_in_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_end_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_start_out_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_publish_on IS NOT NULL)';
                $wheres[] = '(jtr.jtr_deleted_on IS NULL)';
            }
            if ($status === 'Deleted') {
                $wheres[] = '(jtr.jtr_deleted_on IS NOT NULL)';
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
