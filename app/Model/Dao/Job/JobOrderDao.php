<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_order.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOrderDao extends AbstractBaseDao
{
    /**
     * Base dao constructor for job_order.
     *
     */
    public function __construct()
    {
        parent::__construct('job_order', 'jo', self::$Fields);
    }

    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jo_id',
        'jo_ss_id',
        'jo_ref_id',
        'jo_number',
        'jo_srv_id',
        'jo_srt_id',
        'jo_order_date',
        'jo_rel_id',
        'jo_customer_ref',
        'jo_pic_id',
        'jo_order_of_id',
        'jo_invoice_of_id',
        'jo_manager_id',
        'jo_vendor_id',
        'jo_vendor_pic_id',
        'jo_vendor_ref',
        'jo_aju_ref',
        'jo_bl_ref',
        'jo_sppb_ref',
        'jo_packing_ref',
        'jo_publish_by',
        'jo_publish_on',
        'jo_start_by',
        'jo_start_on',
        'jo_document_by',
        'jo_document_on',
        'jo_finish_by',
        'jo_finish_on',
        'jo_deleted_reason',
        'jo_joh_id',
        'jo_jae_id',
        'jo_joa_id',
    ];

    /**
     * Abstract function to load the seeder query for table job_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jo_number',
            'jo_order_date',
            'jo_customer_ref',
            'jo_aju_ref',
            'jo_bl_ref',
            'jo_sppb_ref',
            'jo_packing_ref',
            'jo_publish_on',
            'jo_start_on',
            'jo_document_on',
            'jo_finish_on',
            'jo_deleted_reason',
            'jo_vendor_ref',
        ]);
    }


    /**
     * function to get all available fields
     *
     * @return array
     */
    public static function getFields(): array
    {
        return self::$Fields;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_id = ' . $referenceValue . ')';


        return self::loadData($wheres)[0];
    }


    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $systemId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $systemId);
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_srv_id, srv.srv_code as jo_srv_code, srv.srv_name as jo_service,
                            jo.jo_srt_id, srt.srt_route as jo_srt_route, srt.srt_name as jo_service_term
                        FROM job_order as jo
                            INNER JOIN service as srv ON srv.srv_id = jo.jo_srv_id
                            INNER JOIN service_term as srt ON srt.srt_id = jo.jo_srt_id ' . $strWheres;
        $result = DB::select($query);
        if (count($result) === 1) {
            return DataParser::objectToArray($result[0]);
        }

        return [];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = '(jo.jo_deleted_on IS NULL)';

        return self::loadData($where);
    }

    /**
     * Function to get all active record.
     *
     * @param string $customerReff
     *
     * @return bool
     */
    public static function isCustomerRefExist(string $customerReff): bool
    {
        $result = false;
        $query = "SELECT jo_id
                  FROM job_order
                  WHERE jo_customer_ref = '" . $customerReff . "' AND jo_deleted_on IS NULL";
        $results = DB::select($query);
        if (empty($results) === false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jo.jo_id, jo.jo_number, jo.jo_srv_id, srv.srv_name as jo_srv_name, jo.jo_srt_id, srt.srt_name as jo_srt_name, us.us_name as jo_manager,
                           jo.jo_publish_on, jo.jo_deleted_on, jo.jo_deleted_reason, jo.jo_document_on, jo.jo_finish_on, srt.srt_route,
                           jod.warehouse, jod.container_number, jod.seal_number, jac.jac_id, ac.ac_code as jac_action, jae.jae_description,  ac.ac_style as jae_style,
                           jod.truck_number, jod.driver, jod.driver_phone, jod.eta_date, jod.eta_time, jod.shipper, jod.transporter,
                           jo.jo_start_on, jo.jo_finish_on, joh.joh_id, joh.joh_reason, joh.joh_created_on, srt.srt_route as jo_route
                    FROM job_order as jo INNER JOIN
                    service as srv ON jo.jo_srv_id = srv.srv_id INNER JOIN
                    service_term as srt ON jo.jo_srt_id = srt.srt_id LEFT OUTER JOIN
                    users as us ON jo.jo_manager_id = us.us_id LEFT OUTER JOIN
                    (SELECT ji.ji_id as id, ji.ji_jo_id as jo_id, ji.ji_wh_id as wh_id, wh.wh_name as warehouse, ji_container_number as container_number, ji.ji_seal_number as seal_number,
                            ji.ji_truck_number as truck_number, ji.ji_driver as driver, ji.ji_driver_phone as driver_phone,
                            ji.ji_eta_date as eta_date, ji.ji_eta_time as eta_time, ship.rel_name as shipper, ven.rel_name as transporter
                        FROM job_inbound as ji INNER JOIN
                            warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                            relation as ship ON ship.rel_id = ji.ji_rel_id LEFT OUTER JOIN
                            relation as ven ON ji.ji_vendor_id = ven.rel_id
                        UNION ALL
                        SELECT job.job_id as id, job.job_jo_id as jo_id, job.job_wh_id as wh_id, wh.wh_name as warehouse, job.job_container_number as container_number, job.job_seal_number as seal_number,
                                job.job_truck_number as truck_number, job.job_driver as driver, job.job_driver_phone as driver_phone,
                            job.job_eta_date as eta_date, job.job_eta_time as eta_time, cons.rel_name as shipper, ven.rel_name as transporter
                        FROM job_outbound as job INNER JOIN
                            warehouse as wh ON job.job_wh_id = wh.wh_id INNER JOIN
                            relation as cons ON cons.rel_id = job.job_rel_id LEFT OUTER JOIN
                            relation as ven ON job.job_vendor_id = ven.rel_id
                        UNION ALL
                        SELECT jm.jm_id as id, jm.jm_jo_id as jo_id, jm.jm_wh_id as wh_id, wh.wh_name as warehouse, '' as container_number, '' as seal_number,
                                '' as truck_number, '' as driver, '' as driver_phone,
                            jm.jm_date as eta_date, jm.jm_time as eta_time, '' as shipper, '' as transporter
                        FROM job_movement as jm INNER JOIN
                            warehouse as wh ON jm.jm_wh_id = wh.wh_id
                        UNION ALL
                        SELECT sop.sop_id as id, sop.sop_jo_id as jo_id, sop.sop_wh_id as wh_id, wh.wh_name as warehouse, '' as container_number, '' as seal_number,
                                '' as truck_number, '' as driver, '' as driver_phone,
                            sop.sop_date as eta_date, sop.sop_time as eta_time, '' as shipper, '' as transporter
                        FROM stock_opname as sop INNER JOIN
                            warehouse as wh ON sop.sop_wh_id = wh.wh_id
                        UNION ALL
                        SELECT ja.ja_id as id, ja.ja_jo_id as jo_id, ja.ja_wh_id as wh_id, wh.wh_name as warehouse, '' as container_number, '' as seal_number,
                                '' as truck_number, '' as driver, '' as driver_phone,
                            null as eta_date, null as eta_time, '' as shipper, '' as transporter
                        FROM job_adjustment as ja INNER JOIN
                            warehouse as wh ON ja.ja_wh_id = wh.wh_id
                        UNION ALL
                        SELECT jik.jik_id as id, jik.jik_jo_id as jo_id, jik.jik_wh_id as wh_id, wh.wh_name as warehouse, '' as container_number, '' as seal_number,
                                '' as truck_number, '' as driver, '' as driver_phone,
                                 jik.jik_eta_date as eta_date, jik.jik_eta_time as eta_time, ship.rel_name as shipper, '' as transporter
                        FROM job_inklaring as jik LEFT OUTER JOIN
                             warehouse as wh ON wh.wh_id = jik.jik_wh_id LEFT OUTER JOIN
                             relation as cons ON cons.rel_id = jik.jik_consignee_id LEFT OUTER JOIN
                             relation as ship ON ship.rel_id = jik.jik_shipper_id) as jod ON jo.jo_id = jod.jo_id LEFT OUTER JOIN
                         job_action_event as jae ON jo.jo_jae_id = jae.jae_id LEFT OUTER JOIN
                         job_action as jac ON jae.jae_jac_id = jac.jac_id LEFT OUTER JOIN
                         action as ac ON jac.jac_ac_id = ac.ac_id LEFT OUTER JOIN
                         job_order_hold as joh ON jo.jo_joh_id = joh.joh_id " . $strWhere;
        $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record.
     *
     * @param int $soId To store the limit of the data.
     * @param int $ssId To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadDataBySoIdAndSystem(int $soId, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $ssId);
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');

        # Inklaring Query
        $inklaringWheres = [];
        $inklaringWheres[] = SqlHelper::generateNumericCondition('jik_so_id', $soId);
        $strWhereJik = ' WHERE ' . implode(' AND ', array_merge($wheres, $inklaringWheres));
        $query = "SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                           jo.jo_srt_id, 'jik' as jo_srt_route, srt.srt_name as jo_service_term,
                           jo.jo_created_on, jo.jo_publish_on,
                           jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                           um.us_name as jo_manager, relven.rel_name as jo_vendor,
                           jo.jo_deleted_on, jo.jo_deleted_reason,
                           cpven.cp_name as jo_pic_vendor, jo.jo_vendor_ref, jo.jo_joh_id,
                           joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on,
                           jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style
                    FROM job_inklaring AS jik
                             INNER JOIN job_order as jo ON jo.jo_id = jik.jik_jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                             LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                             LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                             LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                             LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                             LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereJik;
        $query .= ' UNION ALL ';
        $warehouseWheres = [];
        $inbound = '(jo_id IN (SELECT ji_jo_id
                                FROM job_inbound
                                WHERE (ji_so_id = ' . $soId . ')))';
        $outbound = '(jo_id IN (SELECT job_jo_id
                                FROM job_outbound
                                WHERE (job_so_id = ' . $soId . ')))';
        $warehouseWheres[] = '(' . $inbound . ' OR ' . $outbound . ')';
        $warehouseWheres[] = SqlHelper::generateStringCondition('srv.srv_code', 'warehouse');
        $strWhereWarehouse = ' WHERE ' . implode(' AND ', array_merge($wheres, $warehouseWheres));

        $query .= 'SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                       jo.jo_srt_id, srt.srt_route as jo_srt_route, srt.srt_name as jo_service_term,
                       jo.jo_created_on, jo.jo_publish_on,
                       jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                       um.us_name as jo_manager, relven.rel_name as jo_vendor,
                       jo.jo_deleted_on, jo.jo_deleted_reason,
                       cpven.cp_name as jo_pic_vendor, jo.jo_vendor_ref, jo.jo_joh_id,
                       joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style
                FROM  job_order as jo
                         INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                         LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                         LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                         LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                         LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                         LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                         LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                         LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id ' . $strWhereWarehouse;
        $query .= ' UNION ALL ';
        $doWheres = [];
        $doWheres[] = SqlHelper::generateNumericCondition('jdl.jdl_so_id', $soId);
        $strWhereDo = ' WHERE ' . implode(' AND ', array_merge($wheres, $doWheres));

        $query .= "SELECT jo.jo_id, jo.jo_number, srv.srv_name as jo_service, srv.srv_code as jo_srv_code,
                           jo.jo_srt_id, 'jdl' as jo_srt_route, srt.srt_name as jo_service_term,
                           jo.jo_created_on, jo.jo_publish_on,
                           jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on,
                           um.us_name as jo_manager, relven.rel_name as jo_vendor,
                           jo.jo_deleted_on, jo.jo_deleted_reason,
                           cpven.cp_name as jo_pic_vendor, jo.jo_vendor_ref, jo.jo_joh_id,
                           joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on,
                           jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style
                    FROM job_delivery AS jdl
                             INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id
                             INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             LEFT OUTER JOIN relation as relven ON jo.jo_vendor_id = relven.rel_id
                             LEFT OUTER JOIN contact_person as cpven ON jo.jo_vendor_pic_id = cpven.cp_id
                             LEFT OUTER JOIN users as um ON jo.jo_manager_id = um.us_id
                             LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                             LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                             LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                             LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id" . $strWhereDo;
        $query .= ' ORDER BY jo_deleted_on DESC, jo_finish_on DESC, jo_start_on DESC, jo_publish_on DESC, jo_id DESC';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to job id by so id.
     *
     * @param int $soId To store the limit of the data.
     * @param array $wheres To store jo conditions.
     *
     * @return array
     */
    public static function loadJoIdBySoId(int $soId, array $wheres = []): array
    {
        $wheres[] = SqlHelper::generateNullCondition('jo_deleted_on');
        $inklaring = '(jo_id IN (SELECT jik_jo_id
                                FROM job_inklaring
                                WHERE (jik_so_id = ' . $soId . ')))';
        $delivery = '(jo_id IN (SELECT jdl_jo_id
                                FROM job_delivery
                                WHERE (jdl_so_id = ' . $soId . ')))';
        $inbound = '(jo_id IN (SELECT ji_jo_id
                                FROM job_inbound
                                WHERE (ji_so_id = ' . $soId . ')))';
        $outbound = '(jo_id IN (SELECT job_jo_id
                                FROM job_outbound
                                WHERE (job_so_id = ' . $soId . ')))';
        $wheres[] = '(' . $inklaring . ' OR ' . $delivery . ' OR ' . $inbound . ' OR ' . $outbound . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jo_id, jo_joh_id, jo_finish_on
                    FROM job_order " . $strWhere;
        $query .= ' GROUP BY jo_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to generate the status
     *
     * @param array $data To store the status data.
     *
     * @return string
     */
    public function generateStatus(array $data): string
    {
        /*
         $data = [
            'is_deleted' => '',
            'is_finish' => '',
            'is_start' => '',
            'is_document' => '',
            'jac_id' => '',
            'jae_style' => '',
            'jac_action' => '',
            'jae_description' => '',
            'jo_srt_id' => '',
            'is_published' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDark(Trans::getWord('canceled'));
        } elseif ($data['is_hold'] === true) {
            $result = new LabelDark(Trans::getWord('hold'));
        } elseif ($data['is_finish'] === true) {
            $result = new LabelSuccess(Trans::getWord('finish'));
        } elseif (array_key_exists('is_document', $data) === true && $data['is_document'] === true) {
            $result = new LabelWarning(Trans::getWord('documentComplete'));
        } elseif ($data['is_start'] === true && empty($data['jac_id']) === false) {
            $result = new Label(Trans::getWord($data['jac_action'] . '' . $data['jo_srt_id'] . '.description', 'action'), $data['jae_style']);
            if (array_key_exists('jae_description', $data) === true && empty($data['jae_description']) === false) {
                $result .= ' | ' . $data['jae_description'];
            }
        } elseif ($data['is_publish'] === true) {
            $result = new LabelDanger(Trans::getWord('published'));
        } else {
            $result = new LabelGray(Trans::getWord('draft'));
        }

        return $result;
    }


    /**
     * Function to get all record.
     *
     * @param int $joId To store the limit of the data.
     *
     * @return array
     */
    public static function loadSimpleJobOrderById($joId): array
    {
        $query = 'SELECT jo.jo_id, jo.jo_srt_id, jo.jo_created_on, u1.us_name as jo_creator, jo.jo_publish_on,
                          u2.us_name as jo_publisher, jo.jo_finish_on, u3.us_name as jo_finisher
                        FROM job_order as jo LEFT OUTER JOIN
                              users as u1 ON jo.jo_created_by = u1.us_id LEFT OUTER JOIN
                              users as u2 ON jo.jo_publish_by = u2.us_id LEFT OUTER JOIN
                              users as u3 ON jo.jo_finish_by = u3.us_id
                         WHERE (jo.jo_id = ' . $joId . ')';
        $result = DB::select($query);
        if (count($result) === 1) {
            return DataParser::objectToArray($result[0], array_merge(self::$Fields, [
                'jo_created_on',
                'jo_creator',
                'jo_publisher',
                'jo_finisher',
            ]));
        }

        return [];
    }

    /**
     * Function to get the where condition.
     *
     * @param string $pageCategory To store the service term id.
     * @param int $srtId To store the service term id.
     * @param int $joId To store the service term id.
     * @param boolean $isPopup To store the service term id.
     * @param boolean $isBackAllow To store the service term id.
     *
     * @return string
     */
    public function getJobUrl($pageCategory, $srtId, $joId, $isPopup = false, $isBackAllow = false): string
    {
        $url = '';
        $popup = '';
        $home = '';
        if ($isPopup) {
            $popup = '&pv=1';
        }
        if ($srtId === 1) {
            $url = 'joWhInbound/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 2) {
            $url = 'joWhOutbound/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 3) {
            $url = 'joWhOpname/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 4) {
            $url = 'joWhStockAdjustment/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 5) {
            $url = 'joWhStockMovement/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 6) {
            $url = 'jik/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'inklaringHome';
        } elseif ($srtId === 7) {
            $url = 'jik/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'inklaringHome';
        } elseif ($srtId === 8) {
            $url = 'jik/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'inklaringHome';
        } elseif ($srtId === 9) {
            $url = 'jik/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'inklaringHome';
        } elseif ($srtId === 10) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 11) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 12) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 13) {
            $url = 'joWhBundling/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 14) {
            $url = 'joWhUnBundling/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'warehouseHome';
        } elseif ($srtId === 15) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 16) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 17) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 18) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        } elseif ($srtId === 19) {
            $url = 'jdl/' . $pageCategory . '?jo_id=' . $joId . $popup;
            $home = 'jdl';
        }
        if ($isBackAllow) {
            $url .= '&back_url=' . $home;
        }

        return url($url);
    }


    /**
     * Function to get the stock card table.
     *
     * @param array $row To store the data.
     * @param string $prefix To store the data.
     * @param array $additionals To store the data.
     *
     * @return string
     */
    public function concatReference(array $row, string $prefix = 'jo', array $additionals = []): string
    {
        if (empty($prefix) === false) {
            $prefix .= '_';
        }
        $data = [
            [
                'label' => 'SO',
                'value' => $row['so_number'],
            ],
            [
                'label' => 'REF',
                'value' => $row[$prefix . 'customer_ref'],
            ],
            [
                'label' => 'BL',
                'value' => $row[$prefix . 'bl_ref'],
            ],
            [
                'label' => 'AJU',
                'value' => $row[$prefix . 'aju_ref'],
            ],
            [
                'label' => 'SPPB',
                'value' => $row[$prefix . 'sppb_ref'],
            ],
            [
                'label' => 'Packing',
                'value' => $row[$prefix . 'packing_ref'],
            ],
        ];
        if (empty($additionals) === false) {
            $data = array_merge($data, $additionals);
        }
        return StringFormatter::generateKeyValueTableView($data);

    }

    /**
     * Function to get notification module by service code.
     *
     * @param string $srvCode
     *
     * @return string
     */
    public function getJobNotificationModule(string $srvCode): string
    {
        $result = '';
        if ($srvCode === 'warehouse') {
            $result = 'warehouse';
        } elseif ($srvCode === 'inklaring') {
            $result = 'inklaring';
        } elseif ($srvCode === 'delivery') {
            $result = 'delivery';
        }

        return $result;
    }

    /**
     * Function to load financial margin data.
     *
     * @param int $joId To store the reference of job order.
     *
     * @return array
     */
    public static function loadFinanceMarginData(int $joId): array
    {
        $results = [];
        $sales = [
            'sales_planning' => 0,
            'sales_invoiced' => 0,
            'sales_paid' => 0,
            'reimburse_planning' => 0,
            'reimburse_invoiced' => 0,
            'reimburse_paid' => 0,
        ];
        $purchase = [
            'purchase_planning' => 0,
            'purchase_invoiced' => 0,
            'purchase_paid' => 0,
            'reimburse_planning' => 0,
            'reimburse_invoiced' => 0,
            'reimburse_paid' => 0,
        ];
        $wheresSales = [];
        $wheresSales[] = '(jos_jo_id = ' . $joId . ')';
        $wheresSales[] = '(jos_deleted_on IS NULL)';
        $strWhereSales = ' WHERE ' . implode(' AND ', $wheresSales);
        $querySales = "SELECT 'S' as fn_type, ccg.ccg_type as fn_category, jos.jos_id as fn_id, jos.jos_total as fn_total,
                                'N' as fn_ca,
                                (CASE WHEN sid.sid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                (CASE WHEN si.si_pay_time IS NULL THEN 'N' ELSE 'Y' END) as fn_paid
                        FROM job_sales as jos
                            INNER JOIN cost_code as cc ON jos.jos_cc_id = cc.cc_id
                            INNER JOIN cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id
                            LEFT OUTER JOIN sales_invoice_detail as sid ON jos.jos_sid_id = sid.sid_id
                            LEFT OUTER JOIN sales_invoice as si ON sid.sid_si_id = si.si_id " . $strWhereSales;
        $wheresPurchase = [];
        $wheresPurchase[] = '(jop_jo_id = ' . $joId . ')';
        $wheresPurchase[] = '(jop_deleted_on IS NULL)';
        $strWherePurchase = ' WHERE ' . implode(' AND ', $wheresPurchase);
        $queryPurchase = "SELECT 'P' as fn_type, ccg.ccg_type as fn_category, jop_id as fn_id, jop_total as fn_total,
                                (CASE WHEN jop.jop_cad_id IS NULL THEN 'N' ELSE 'Y' END) as fn_ca,
                                (CASE WHEN pid.pid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                (CASE WHEN pi.pi_paid_on IS NULL THEN 'N' ELSE 'Y' END) as fn_paid
                        FROM job_purchase as jop
                            INNER JOIN cost_code as cc ON jop.jop_cc_id = cc.cc_id
                            INNER JOIN cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id
                            LEFT OUTER JOIN purchase_invoice_detail as pid ON jop.jop_pid_id = pid.pid_id
                            LEFT OUTER JOIN purchase_invoice as pi ON pid.pid_pi_id = pi.pi_id " . $strWherePurchase;

        $query = $querySales . ' UNION ALL ' . $queryPurchase;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $totalCa = 0.0;
            $ca = CashAdvanceDao::getByJobId($joId);
            if (empty($ca) === false && empty($ca['ca_receive_on']) === false) {
                if (empty($ca['ca_settlement_on']) === true) {
                    $totalCa = (float)$ca['ca_amount'] + (float)$ca['ca_reserve_amount'];
                } else {
                    $totalCa = (float)$ca['ca_actual_amount'] + (float)$ca['ca_ea_amount'];
                }
            }
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                $total = (float)$row['fn_total'];
                if ($row['fn_type'] === 'S') {
                    # udpate sales
                    if ($row['fn_category'] === 'S') {
                        $sales['sales_planning'] += $total;
                        if ($row['fn_invoiced'] === 'Y') {
                            $sales['sales_invoiced'] += $total;
                        }
                        if ($row['fn_paid'] === 'Y') {
                            $sales['sales_paid'] += $total;
                        }
                    } else {
                        # Update sales reimbursement
                        $sales['reimburse_planning'] += $total;
                        if ($row['fn_invoiced'] === 'Y') {
                            $sales['reimburse_invoiced'] += $total;
                        }
                        if ($row['fn_paid'] === 'Y') {
                            $sales['reimburse_paid'] += $total;
                        }
                    }
                } else {
                    if ($row['fn_category'] === 'P') {
                        # Update Purchase
                        $purchase['purchase_planning'] += $total;
                        if ($row['fn_ca'] === 'N') {
                            if ($row['fn_invoiced'] === 'Y') {
                                $purchase['purchase_invoiced'] += $total;
                            }
                            if ($row['fn_paid'] === 'Y') {
                                $purchase['purchase_paid'] += $total;
                            }
                        } else {
                            $purchase['purchase_invoiced'] += $total;
                            $caPayment = 0.0;
                            if ($totalCa > 0) {
                                if ($totalCa >= $total) {
                                    $caPayment = $total;
                                    $totalCa -= $total;
                                } else {
                                    $caPayment = $totalCa;
                                    $totalCa = 0.0;
                                }
                            }
                            $purchase['purchase_paid'] += $caPayment;
                        }
                    } else {
                        # Update Purchase reimbursement
                        $purchase['reimburse_planning'] += $total;
                        if ($row['fn_ca'] === 'N') {
                            if ($row['fn_invoiced'] === 'Y') {
                                $purchase['reimburse_invoiced'] += $total;
                            }
                            if ($row['fn_paid'] === 'Y') {
                                $purchase['reimburse_paid'] += $total;
                            }
                        } else {
                            $purchase['reimburse_invoiced'] += $total;
                            $caPayment = 0.0;
                            if ($totalCa > 0) {
                                if ($totalCa >= $total) {
                                    $caPayment = $total;
                                    $totalCa -= $total;
                                } else {
                                    $caPayment = $totalCa;
                                    $totalCa = 0.0;
                                }
                            }
                            $purchase['reimburse_paid'] += $caPayment;
                        }

                    }
                }
            }
        }
        $results[] = [
            'fn_description' => Trans::getFinanceWord('revenue'),
            'fn_planning' => $sales['sales_planning'],
            'fn_invoice' => $sales['sales_invoiced'],
            'fn_pay' => $sales['sales_paid'],
        ];
        $results[] = [
            'fn_description' => Trans::getFinanceWord('reimburse'),
            'fn_planning' => $sales['reimburse_planning'],
            'fn_invoice' => $sales['reimburse_invoiced'],
            'fn_pay' => $sales['reimburse_paid'],
        ];
        $results[] = [
            'fn_description' => Trans::getFinanceWord('cogs'),
            'fn_planning' => $purchase['purchase_planning'],
            'fn_invoice' => $purchase['purchase_invoiced'],
            'fn_pay' => $purchase['purchase_paid'],
        ];
        $results[] = [
            'fn_description' => Trans::getFinanceWord('cogsReimburse'),
            'fn_planning' => $purchase['reimburse_planning'],
            'fn_invoice' => $purchase['reimburse_invoiced'],
            'fn_pay' => $purchase['reimburse_paid'],
        ];
        $results[] = [
            'fn_description' => Trans::getFinanceWord('margin'),
            'fn_planning' => ($sales['sales_planning'] + $sales['reimburse_planning']) - ($purchase['purchase_planning'] + $purchase['reimburse_planning']),
            'fn_invoice' => ($sales['sales_invoiced'] + $sales['reimburse_invoiced']) - ($purchase['purchase_invoiced'] + $purchase['reimburse_invoiced']),
            'fn_pay' => ($sales['sales_paid'] + $sales['reimburse_paid']) - ($purchase['purchase_paid'] + $purchase['reimburse_paid']),
        ];

        return $results;
    }

    /**
     * Function to get all record.
     *
     * @param int $joId To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function getTotalPurchase($joId): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_id = ' . $joId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT
                    FROM job_order as jo LEFT OUTER JOIN
                    () as jop" . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = []): array
    {
        $wheres[] = SqlHelper::generateNullCondition('jo_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jo_id, jo_number
                    FROM job_order " . $strWhere;
        $query .= ' ORDER BY jo_id DESC';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResults = DB::select($query);

        return parent::doPrepareSingleSelectData(DataParser::arrayObjectToArray($sqlResults), 'jo_number', 'jo_id');
    }


}
