<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job\Warehouse\Bundling;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobPacking.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundling extends BaseJobOrder
{

    /**
     * JobPacking constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhBundling', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        # Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'jb_warehouse', $this->getStringParameter('jb_warehouse'));
        $whField->setHiddenField('jb_wh_id', $this->getIntParameter('jb_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');
        $statusField->addOption(Trans::getWord('hold'), '6');


        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('orderDateFrom'), $this->Field->getCalendar('order_date_from', $this->getStringParameter('order_date_from')));
        $this->ListingForm->addField(Trans::getWord('orderDateUntil'), $this->Field->getCalendar('order_date_until', $this->getStringParameter('order_date_until')));
        $this->ListingForm->addField(Trans::getWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getWord('completeDateFrom'), $this->Field->getCalendar('complete_date_from', $this->getStringParameter('complete_date_from')));
        $this->ListingForm->addField(Trans::getWord('completeDateUntil'), $this->Field->getCalendar('complete_date_until', $this->getStringParameter('complete_date_until')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->ListingForm->addField(Trans::getWord('status'), $statusField);
        $this->ListingForm->setGridDimension(4);
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
            'jo_number' => Trans::getWord('jobNumber'),
            'jo_customer' => Trans::getWord('customer'),
            'jo_customer_ref' => Trans::getWord('customerRef'),
            'jo_order_date' => Trans::getWord('orderDate'),
            'jb_warehouse' => Trans::getWord('warehouse'),
            'jb_gd_sku' => Trans::getWord('sku'),
            'jb_goods' => Trans::getWord('goods'),
            'jb_quantity' => Trans::getWord('quantity'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        # Load the data for JobPacking.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('jo_customer', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('jb_quantity', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('jo_order_date', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
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
        $query = 'SELECT count(DISTINCT (jb_id)) AS total_rows
                   FROM job_bundling as jb
                INNER JOIN job_order as jo ON jo.jo_id = jb.jb_jo_id
                INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                INNER JOIN warehouse as wh ON jb.jb_wh_id = wh.wh_id
                INNER JOIN job_goods as jog ON jog.jog_id = jb.jb_jog_id
                INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                INNER JOIN brand as br ON gd.gd_br_id = br.br_id
                INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                INNER JOIN unit as uom on gdu.gdu_uom_id = uom.uom_id
                LEFT OUTER JOIN sales_order as so ON jo.jo_so_id = so.so_id ';
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
        $query = 'SELECT jb.jb_id, jb.jb_jo_id, jo.jo_id, jo.jo_number, rel.rel_name, rel.rel_short_name as jo_customer, jo.jo_order_date,
                        jo.jo_publish_on, jo.jo_deleted_on, jo.jo_deleted_reason, jo.jo_start_on, jo.jo_finish_on, jo.jo_created_on,
                        gd.gd_sku as jb_gd_sku, gd.gd_name,wh.wh_name as jb_warehouse,
                        jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style as jae_style, so.so_id, so.so_number,
                        (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                        joh.joh_id, joh.joh_reason, joh.joh_created_on, jo.jo_srt_id, jo.jo_srv_id, br.br_name as gd_brand, gdc.gdc_name as gd_category,
                        jog.jog_quantity, uom.uom_code as jog_unit
                FROM job_bundling as jb
                INNER JOIN job_order as jo ON jo.jo_id = jb.jb_jo_id
                INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                INNER JOIN warehouse as wh ON jb.jb_wh_id = wh.wh_id
                INNER JOIN job_goods as jog ON jog.jog_id = jb.jb_jog_id
                INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                INNER JOIN brand as br ON gd.gd_br_id = br.br_id
                INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                INNER JOIN unit as uom on gdu.gdu_uom_id = uom.uom_id
                LEFT OUTER JOIN sales_order as so ON jo.jo_so_id = so.so_id
                LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                LEFT OUTER JOIN (SELECT joh_jo_id, max(joh_id) last_joh
                                 FROM job_order_hold
                                 WHERE (joh_deleted_on IS NULL)
                                 GROUP BY joh_jo_id) as joh1 ON jo.jo_id = joh1.joh_jo_id
                LEFT OUTER JOIN job_order_hold as joh ON joh1.last_joh = joh.joh_id ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY jb.jb_id,jo.jo_id, jb.jb_jo_id, jo.jo_number, rel.rel_name, jo.jo_order_date,
                        jo.jo_publish_on, jo.jo_deleted_on, jo.jo_deleted_reason, jo.jo_start_on, jo.jo_finish_on, jo.jo_created_on,
                        gd.gd_sku, gd.gd_name,wh.wh_name,
                        jac.jac_id, ac.ac_code, jae.jae_description, ac.ac_style, so.so_id, so.so_number,
                        so.so_bl_ref, so.so_aju_ref, so.so_packing_ref, jo.jo_bl_ref, jo.jo_aju_ref, jo.jo_packing_ref,
                         so.so_sppb_ref, jo.jo_sppb_ref, so.so_customer_ref, jo.jo_customer_ref,
                        joh.joh_id, joh.joh_reason, joh.joh_created_on, jo.jo_srt_id, jo.jo_srv_id, br.br_name,
                        gdc.gdc_name, rel.rel_short_name, jog.jog_quantity, uom.uom_code';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->doPrepareData($this->loadDatabaseRow($query));
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
        $result = [];
        $joDao = new JobOrderDao();
        $gdDao = new GoodsDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            if (empty($row['jo_order_date']) === false) {
                $row['jo_order_date'] = DateTimeParser::format($row['jo_order_date'], 'Y-m-d', 'd.M.Y');
            }
            $row['jo_customer_ref'] = $joDao->concatReference($row);
            $row['jb_goods'] = $gdDao->formatFullName($row['gd_category'], $row['gd_brand'], $row['gd_name']);
            $row['jb_quantity'] = $number->doFormatFloat($row['jog_quantity']) . ' ' . $row['jog_unit'];
            $row['jo_status'] = $joDao->generateStatus([
                'is_hold' => !empty($row['joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jac_id'],
                'jae_style' => $row['jae_style'],
                'jac_action' => $row['jac_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $result[] = $row;
        }

        return $result;

    }


    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = $this->getJoConditions();
        $srt = ServiceTermDao::getByRoute('joWhBundling');
        $wheres[] = '(jo.jo_srt_id = ' . $srt['srt_id'] . ')';
        if ($this->isValidParameter('jb_wh_id') === true) {
            $wheres[] = '(jb.jb_wh_id = ' . $this->getIntParameter('jb_wh_id') . ')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
