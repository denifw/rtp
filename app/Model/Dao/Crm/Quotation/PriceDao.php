<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm\Quotation;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table price.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PriceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'prc_id',
        'prc_qt_id',
        'prc_ss_id',
        'prc_code',
        'prc_type',
        'prc_rel_of_id',
        'prc_srv_id',
        'prc_srt_id',
        'prc_lead_time',
        'prc_ct_id',
        'prc_eg_id',
        'prc_dtc_origin',
        'prc_dtc_destination',
        'prc_wh_id',
        'prc_tm_id',
        'prc_cct_id',
        'prc_pol_id',
        'prc_pod_id',
        'prc_origin_address',
        'prc_destination_address',
    ];

    /**
     * Base dao constructor for price.
     *
     */
    public function __construct()
    {
        parent::__construct('price', 'prc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table price.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'prc_code',
            'prc_type',
            'prc_origin_address',
            'prc_destination_address',
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
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(prc.prc_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $ssId): array
    {
        $wheres = [];
        $wheres[] = '(prc.prc_id = ' . $referenceValue . ')';
        $wheres[] = '(prc.prc_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by quotation id
     *
     * @param int $qtId To store the reference value of quotation table.
     *
     * @return array
     */
    public static function getByQuotationIdForDelete(int $qtId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('prc_qt_id', $qtId);
        $wheres[] = '(prc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT prc_id
                    FROM price ' . $strWhere;
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT prc.prc_id, prc.prc_qt_id, prc.prc_code, prc.prc_type, prc.prc_rel_id, rel.rel_name as prc_relation,
                           prc.prc_srv_id, srv.srv_code as prc_srv_code, srv.srv_name as prc_srv_name,
                           prc.prc_srt_id, srt.srt_name as prc_srt_name, srt.srt_container as prc_srt_container, prc.prc_lead_time,
                           prc.prc_ct_id, ct.ct_name as prc_container_type, prc.prc_eg_id, eg.eg_name as prc_eg_name, eg.eg_code as prc_eg_code,
                           prc.prc_dtc_origin, dtc_or.dtc_name as prc_origin_district, cty_or.cty_name as prc_origin_city, stt_or.stt_name as prc_origin_state,
                           prc.prc_dtc_destination, dtc_des.dtc_name as prc_destination_district, cty_des.cty_name as prc_destination_city, stt_des.stt_name as prc_destination_state,
                           prc.prc_wh_id, wh.wh_name as prc_warehouse, prc.prc_tm_id, tm.tm_name as prc_transport_module, tm.tm_code as prc_tm_code,
                           prc.prc_cct_id, cct.cct_name as prc_custom_clearance_type, cct.cct_code as prc_cct_code, ct.ct_code as prc_ct_code,
                           uc.us_name as prc_created_by, prc.prc_created_on, ud.us_name as prc_deleted_by, prc.prc_deleted_on, prc.prc_deleted_reason,
                           prc.prc_qt_id, qt.qt_number as prc_qt_number, qt.qt_approve_on as prc_qt_approve_on, qt.qt_qts_id as prc_qt_qts_id,
                           qts.qts_deleted_on as prc_qt_qts_deleted_on, qt.qt_start_date as prc_qt_start_date, qt.qt_end_date as prc_qt_end_date,
                           prd.prc_total as prc_total, prc.prc_origin_address, prc.prc_destination_address,
                            prc.prc_pol_id, pol.po_name as prc_pol_name, pol.po_code as prc_pol_code, polc.cnt_name as prc_pol_country,
                            prc.prc_pod_id, pod.po_name as prc_pod_name, pod.po_code as prc_pod_code, podc.cnt_name as prc_pod_country,
                            srt.srt_load as prc_srt_load, srt.srt_unload as prc_srt_unload, srt.srt_pol as prc_srt_pol, srt.srt_pod as prc_srt_pod
                    FROM price as prc
                         INNER JOIN relation as rel on prc.prc_rel_id = rel.rel_id
                         INNER JOIN service as srv on prc.prc_srv_id = srv.srv_id
                         INNER JOIN quotation as qt on prc.prc_qt_id = qt.qt_id
                         LEFT OUTER JOIN quotation_submit as qts on qt.qt_qts_id = qts.qts_id
                         LEFT OUTER JOIN service_term as srt on srt.srt_id = prc.prc_srt_id
                         LEFT OUTER JOIN container as ct on ct.ct_id = prc.prc_ct_id
                         LEFT OUTER JOIN port as pol on prc.prc_pol_id = pol.po_id
                         LEFT OUTER JOIN country as polc on pol.po_cnt_id = polc.cnt_id
                         LEFT OUTER JOIN port as pod on prc.prc_pod_id = pod.po_id
                         LEFT OUTER JOIN country as podc on pod.po_cnt_id = podc.cnt_id
                         LEFT OUTER JOIN district as dtc_or on prc.prc_dtc_origin = dtc_or.dtc_id
                         LEFT OUTER JOIN city as cty_or on dtc_or.dtc_cty_id = cty_or.cty_id
                         LEFT OUTER JOIN state as stt_or on dtc_or.dtc_stt_id = stt_or.stt_id
                         LEFT OUTER JOIN district as dtc_des on prc.prc_dtc_destination = dtc_des.dtc_id
                         LEFT OUTER JOIN city as cty_des on dtc_des.dtc_cty_id = cty_des.cty_id
                         LEFT OUTER JOIN state as stt_des on dtc_des.dtc_stt_id = stt_des.stt_id
                         LEFT OUTER JOIN transport_module as tm on  prc.prc_tm_id = tm.tm_id
                         LEFT OUTER JOIN warehouse as wh on wh.wh_id = prc.prc_wh_id
                         LEFT OUTER JOIN equipment_group as eg on eg.eg_id = prc.prc_eg_id
                         LEFT OUTER JOIN customs_clearance_type as cct on prc.prc_cct_id = cct.cct_id
                         LEFT OUTER JOIN users as uc on prc.prc_created_by = uc.us_id
                         LEFT OUTER JOIN users as ud on prc.prc_deleted_by = ud.us_id
                         LEFT OUTER JOIN (
                            SELECT prd_prc_id, SUM(prd_total) as prc_total
                            FROM price_detail
                            WHERE (prd_deleted_on IS NULL)
                            GROUP BY prd_prc_id
                         ) as prd ON prc.prc_id = prd.prd_prc_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY prc.prc_deleted_on DESC, qt.qt_approve_on DESC, prc.prc_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (prc_id)) AS total_rows
                       FROM price as prc
                         INNER JOIN relation as rel on prc.prc_rel_id = rel.rel_id
                         INNER JOIN service as srv on prc.prc_srv_id = srv.srv_id
                         INNER JOIN quotation as qt on prc.prc_qt_id = qt.qt_id
                         LEFT OUTER JOIN quotation_submit as qts on qt.qt_qts_id = qts.qts_id
                         LEFT OUTER JOIN service_term as srt on srt.srt_id = prc.prc_srt_id
                         LEFT OUTER JOIN container as ct on ct.ct_id = prc.prc_ct_id
                         LEFT OUTER JOIN port as pol on prc.prc_pol_id = pol.po_id
                         LEFT OUTER JOIN country as polc on pol.po_cnt_id = polc.cnt_id
                         LEFT OUTER JOIN port as pod on prc.prc_pod_id = pod.po_id
                         LEFT OUTER JOIN country as podc on pod.po_cnt_id = podc.cnt_id
                         LEFT OUTER JOIN district as dtc_or on prc.prc_dtc_origin = dtc_or.dtc_id
                         LEFT OUTER JOIN city as cty_or on dtc_or.dtc_cty_id = cty_or.cty_id
                         LEFT OUTER JOIN state as stt_or on dtc_or.dtc_stt_id = stt_or.stt_id
                         LEFT OUTER JOIN district as dtc_des on prc.prc_dtc_destination = dtc_des.dtc_id
                         LEFT OUTER JOIN city as cty_des on dtc_des.dtc_cty_id = cty_des.cty_id
                         LEFT OUTER JOIN state as stt_des on dtc_des.dtc_stt_id = stt_des.stt_id
                         LEFT OUTER JOIN transport_module as tm on  prc.prc_tm_id = tm.tm_id
                         LEFT OUTER JOIN warehouse as wh on wh.wh_id = prc.prc_wh_id
                         LEFT OUTER JOIN equipment_group as eg on eg.eg_id = prc.prc_eg_id
                         LEFT OUTER JOIN customs_clearance_type as cct on prc.prc_cct_id = cct.cct_id
                         LEFT OUTER JOIN users as uc on prc.prc_created_by = uc.us_id
                         LEFT OUTER JOIN users as ud on prc.prc_deleted_by = ud.us_id' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'prc_code', 'prc_id');
    }


    /**
     * Function to get record for single select field.
     *
     * @param array $data To store the list condition query.
     *
     * @return string
     */
    public function getStatus(array $data): string
    {
        if (empty($data) === true) {
            return '';
        }
        if (array_key_exists('prc_deleted_on', $data) === true && empty($data['prc_deleted_on']) === false) {
            return new LabelDanger(Trans::getWord('deleted'));
        }
        $expired = false;
        if (array_key_exists('prc_qt_end_date', $data) === true && empty($data['prc_qt_end_date']) === false) {
            $today = DateTimeParser::createFromFormat(date('Y-m-d') . ' 23:50:00');
            $endDate = DateTimeParser::createFromFormat($data['prc_qt_end_date'] . ' 23:50:00');
            $expired = ($today > $endDate);
        }
        if ($expired === false) {
            if (array_key_exists('prc_qt_approve_on', $data) === true && empty($data['prc_qt_approve_on']) === false) {
                return new LabelSuccess(Trans::getWord('approved'));
            }
            if (array_key_exists('prc_qt_qts_id', $data) === true && empty($data['prc_qt_qts_id']) === false) {
                if (array_key_exists('prc_qt_qts_deleted_on', $data) === true && empty($data['prc_qt_qts_deleted_on']) === false) {
                    return new LabelDark(Trans::getWord('rejected'));
                }
                return new LabelPrimary(Trans::getWord('submitted'));
            }
            return new LabelGray(Trans::getWord('draft'));
        }
        return new LabelDark(Trans::getWord('expired'));
    }

    /**
     * Function to get route for detail page
     *
     * @param string $type To store the type of price
     * @param string $serviceCode To store the code of service
     *
     * @return string
     */
    public static function getDetailRoute(string $type, string $serviceCode): string
    {
        $route = 'prcSls';
        if ($type === 'P') {
            $route = 'prcPrc';
        }
        if ($serviceCode === 'warehouse') {
            $route .= 'Wh';
        } elseif ($serviceCode === 'inklaring') {
            $route .= 'Ink';
        } elseif ($serviceCode === 'delivery') {
            $route .= 'Dl';
        }
        return '/' . $route . '/detail';
    }

    /**
     * Function to get route for detail page
     *
     * @param array $data To store the type of price
     *
     * @return array
     */
    public static function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            if ($row['prc_srv_code'] === 'inklaring') {
                $row['prc_port'] = $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                if ($row['prc_srt_pod'] === 'Y') {
                    $row['prc_port'] = $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                }
            } elseif ($row['prc_srv_code'] === 'delivery') {
                $origin = $row['prc_origin_district'] . ', ' . $row['prc_origin_city'] . ', ' . $row['prc_origin_state'];
                if (empty($row['prc_origin_address']) === false) {
                    $origin = $row['prc_origin_address'] . ', ' . $origin;
                }
                $destination = $row['prc_destination_district'] . ', ' . $row['prc_destination_city'] . ', ' . $row['prc_destination_state'];
                if (empty($row['prc_destination_address']) === false) {
                    $destination = $row['prc_destination_address'] . ', ' . $destination;
                }
                if ($row['prc_srt_pol'] === 'Y') {
                    $row['prc_origin'] = $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                } else {
                    $row['prc_origin'] = $origin;
                }

                if ($row['prc_srt_pod'] === 'Y') {
                    $row['prc_destination'] = $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                } else {
                    $row['prc_destination'] = $destination;
                }
            }
            $results[] = $row;
        }
        return $results;
    }
}
