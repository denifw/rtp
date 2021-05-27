<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Model\Dao\Job\JobOrderDao;

/**
 * Class to control the system of JobOrder.
 *
 * @package    app
 * @subpackage Model\Listing\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JoHistory extends BaseJobOrder
{

    /**
     * JobOrder constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joHistory', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'));
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $srvField = $this->Field->getSingleSelect('service', 'jo_service', $this->getStringParameter('jo_service'));
        $srvField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $srvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srvField->setEnableDetailButton(false);
        $srvField->setEnableNewButton(false);
        $srvField->setAutoCompleteFields([
            'jo_srv_code' => 'srv_code'
        ]);

        $srtField = $this->Field->getSingleSelect('serviceTerm', 'jo_service_term', $this->getStringParameter('jo_service_term'));
        $srtField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srtField->addParameterById('srt_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $srtField->setEnableDetailButton(false);
        $srtField->setEnableNewButton(false);
        $srtField->setAutoCompleteFields([
            'jo_srt_route' => 'srt_route'
        ]);


        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->ListingForm->addField(Trans::getWord('service'), $srvField);
        $this->ListingForm->addField(Trans::getWord('serviceTerm'), $srtField);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        }
        $this->ListingForm->addField(Trans::getWord('canceled'), $this->Field->getYesNo('jo_cancel', $this->getStringParameter('jo_cancel')));
        $this->ListingForm->addHiddenField($this->Field->getHidden('jo_srv_code', $this->getStringParameter('jo_srv_code')));
        $this->ListingForm->addHiddenField($this->Field->getHidden('jo_srt_route', $this->getStringParameter('jo_srt_route')));
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
            'jo_service_term' => Trans::getWord('serviceTerm'),
            'customer' => Trans::getWord('customer'),
            'reference' => Trans::getWord('reference'),
            'jo_manager' => Trans::getWord('jobManager'),
            'jo_status' => Trans::getWord('lastStatus'),
            'jo_action' => Trans::getWord('action'),
        ]);
        # Load the data for JobOrder.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('jo_number', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('jo_action', 'style', 'text-align: center');
        $this->disableNewButton(true);

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        $unions = $this->getUnionQuery();
        if (empty($unions) === true) {
            return 0;
        }
        # Set Select query;
        $query = 'SELECT count(DISTINCT (jo_id)) AS total_rows
                   FROM (' . implode(' UNION ALL ', $unions) . ') as j';
        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        $unions = $this->getUnionQuery();
        if (empty($unions) === true) {
            return [];
        }
        # Set Select query;
        $query = 'SELECT jo_id, jo_number, jo_service, jo_srt_id, jo_service_term,
                       jo_created_on, jo_publish_on, jo_start_on, jo_document_on, jo_finish_on,
                       jo_manager_id, jo_manager, jo_deleted_on, jo_joh_id,
                       jo_action_id, jo_action, jo_event, jo_action_style,
                       customer, so_number, customer_ref, bl_ref, sppb_ref, packing_ref, aju_ref
                   FROM (' . implode(' UNION ALL ', $unions) . ') as j';
        $query .= ' ORDER BY jo_deleted_on DESC, jo_finish_on DESC, jo_start_on DESC, jo_publish_on DESC, jo_id DESC';

        $sqlResult = $this->loadDatabaseRow($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = $this->doPrepareData($sqlResult);
        }
        return $result;
    }

    /**
     * Function load union sql.
     *
     * @return array
     */
    private function getUnionQuery(): array
    {
        $unions = [];
        $strWheres = $this->getWhereCondition();
        $strWheresNoSo = $this->getWhereCondition(false);
        if ($this->isValidParameter('jo_srv_code') === false || $this->getStringParameter('jo_srv_code') === 'inklaring') {
            $unions[] = $this->getInklaringQuery($strWheres);
        }
        if ($this->isValidParameter('jo_srv_code') === false || $this->getStringParameter('jo_srv_code') === 'delivery') {
            $unions[] = $this->getDeliveryQuery($strWheres);
        }
        if ($this->isValidParameter('jo_srv_code') === false || $this->getStringParameter('jo_srv_code') === 'warehouse') {
            if ($this->isValidParameter('jo_srt_route') === false || $this->getStringParameter('jo_srt_route') === 'joWhInbound') {
                $unions[] = $this->getInboundQuery($strWheres);
            }
            if ($this->isValidParameter('jo_srt_route') === false || $this->getStringParameter('jo_srt_route') === 'joWhOutbound') {
                $unions[] = $this->getOutboundQuery($strWheres);
            }
            if ($this->isValidParameter('jo_srt_route') === false || $this->getStringParameter('jo_srt_route') === 'joWhStockMovement') {
                $unions[] = $this->getMovementQuery($strWheresNoSo);
            }
            if ($this->isValidParameter('jo_srt_route') === false || $this->getStringParameter('jo_srt_route') === 'joWhStockAdjustment') {
                $unions[] = $this->getAdjustmentQuery($strWheresNoSo);
            }
            if ($this->isValidParameter('jo_srt_route') === false || $this->getStringParameter('jo_srt_route') === 'joWhOpname') {
                $unions[] = $this->getOpnameQuery($strWheresNoSo);
            }
        }
        return $unions;
    }

    /**
     * Function load sql for inklaring.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getInklaringQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       rel.rel_name as customer, so.so_number, so.so_customer_ref as customer_ref,
                       so.so_bl_ref as bl_ref, so.so_sppb_ref as sppb_ref, so.so_packing_ref as packing_ref, so.so_aju_ref as aju_ref
                FROM job_inklaring AS jik
                         INNER JOIN sales_order as so ON jik.jik_so_id = so.so_id
                         INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                         INNER JOIN job_order as jo ON jo.jo_id = jik.jik_jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for delivery.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getDeliveryQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       rel.rel_name as customer, so.so_number,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref
                FROM job_delivery as jdl
                         INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         LEFT OUTER JOIN sales_order as so ON jdl.jdl_so_id = so.so_id
                         LEFT OUTER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for Inbound.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getInboundQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       rel.rel_name as customer, so.so_number,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref
                FROM job_inbound as ji
                         INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                         INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                         LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for Outbound.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getOutboundQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       rel.rel_name as customer, so.so_number,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                       (CASE WHEN so.so_id IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref
                FROM job_outbound as job
                         INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                         LEFT OUTER JOIN sales_order as so ON job.job_so_id = so.so_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for Movement.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getMovementQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       null as customer, null as so_number, jo.jo_customer_ref as customer_ref,
                       jo.jo_bl_ref as bl_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_packing_ref as packing_ref, jo.jo_aju_ref as aju_ref
                FROM job_movement as jm
                         INNER JOIN job_order as jo ON jm.jm_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for Adjustment.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getAdjustmentQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                           jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                           jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                           jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                           null as customer, null as so_number, jo.jo_customer_ref as customer_ref,
                           jo.jo_bl_ref as bl_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_packing_ref as packing_ref, jo.jo_aju_ref as aju_ref
                    FROM job_order AS jo
                             INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             INNER JOIN job_adjustment as ja ON jo.jo_id = ja.ja_jo_id
                             LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                             LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                             LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
    }

    /**
     * Function load sql for Opname.
     *
     * @param string $strWhere To store the data.
     *
     * @return string
     */
    private function getOpnameQuery(string $strWhere): string
    {
        return 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_srt_id, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on, jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       jo.jo_manager_id, um.us_name as jo_manager, jo.jo_deleted_on, jo.jo_joh_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                       null as customer, null as so_number, jo.jo_customer_ref as customer_ref,
                       jo.jo_bl_ref as bl_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_packing_ref as packing_ref, jo.jo_aju_ref as aju_ref
                FROM stock_opname as sop
                         INNER JOIN job_order as jo ON sop.sop_jo_id = jo.jo_id
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
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
        $jobDao = new JobOrderDao();
        foreach ($data as $row) {
            $row['reference'] = $jobDao->concatReference($row, '');

            $row['jo_status'] = $jobDao->generateStatus([
                'is_hold' => !empty($row['joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $row['jo_service_term'] = $row['jo_service'] . ' - ' . $row['jo_service_term'];
            $btn = new HyperLink('BtnView' . $row['jo_id'], '', $jobDao->getJobUrl('view', (int)$row['jo_srt_id'], $row['jo_id']), true);
            $btn->viewAsButton();
            $btn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['jo_action'] = $btn;
            $result[] = $row;
        }

        return $result;

    }


    /**
     * Function to get the where condition.
     *
    /**
     * Function to get the where condition.
     *
     * @param bool $soExists To trigger if so exist or not.
     *
     * @return string
     */
    private function getWhereCondition(bool $soExists = true): string
    {
        # Set where conditions
        $wheres = $this->getJoConditions($soExists);
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_srv_id', $this->getIntParameter('jo_srv_id'));
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_srt_id', $this->getIntParameter('jo_srt_id'));
        }
        if ($this->isValidParameter('jo_cancel') === true) {
            $status = $this->getStringParameter('jo_cancel');
            if ($status === 'Y') {
                $wheres[] = '(jo.jo_deleted_on IS NOT NULL)';
            } else {
                $wheres[] = '(jo.jo_finish_on IS NOT NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            }
        } else {

            $wheres[] = '((jo.jo_finish_on IS NOT NULL) OR (jo.jo_deleted_on IS NOT NULL))';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
