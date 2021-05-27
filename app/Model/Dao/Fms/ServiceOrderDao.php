<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Fms;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table service_order.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'svo_id', 'svo_ss_id', 'svo_number', 'svo_eq_id', 'svo_svr_id', 'svo_vendor_id', 'svo_meter', 'svo_order_date', 'svo_planning_date',
        'svo_manager_id', 'svo_request_by_id', 'svo_remark', 'svo_deleted_reason', 'svo_approved_by', 'svo_approved_on',
        'svo_start_service_date', 'svo_start_service_time', 'svo_start_service_by', 'svo_finish_by', 'svo_finish_on'
    ];

    /**
     * Base dao constructor for service_order.
     *
     */
    public function __construct()
    {
        parent::__construct('service_order', 'svo', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'svo_number', 'svo_order_date', 'svo_planning_date', 'svo_remark', 'svo_deleted_reason',
            'svo_approved_on', 'svo_start_service_date', 'svo_start_service_by', 'svo_finish_on'
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
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(svo.svo_id = ' . $referenceValue . ')';
        $wheres[] = '(svo.svo_ss_id = ' . $systemSettingValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderList To store the list for sortir.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT svo.svo_id, svo.svo_ss_id, svo.svo_number, svo.svo_eq_id, svo.svo_svr_id, svo.svo_vendor_id, svo.svo_meter,
                         svo.svo_order_date, svo.svo_planning_date, svo.svo_manager_id,
                         svo.svo_request_by_id, svo.svo_remark, svo.svo_deleted_reason, svo.svo_approved_by, svo.svo_approved_on,
                         svo.svo_start_service_date, svo.svo_start_service_time, svo.svo_start_service_by,
                         svo.svo_finish_by, svo.svo_finish_on, svo.svo_deleted_by, svo.svo_deleted_on,
                         eg.eg_name || \' - \' || eq.eq_description as svo_eq_name, manager.us_name as svo_manager_name,
                         vendor.rel_name as svo_vendor_name, requestBy.us_name as svo_request_by_name, eq.eq_primary_meter,
                         svr.svr_id, svr.svr_reject_reason
                  FROM   service_order AS svo
                         INNER JOIN equipment AS eq ON eq.eq_id = svo.svo_eq_id
                         INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                         INNER JOIN relation AS vendor ON vendor.rel_id = svo.svo_vendor_id
                         INNER JOIN users AS manager ON manager.us_id = svo.svo_manager_id
                         INNER JOIN users AS requestBy ON requestBy.us_id = svo.svo_request_by_id
                         LEFT OUTER JOIN service_order_request AS svr ON svr.svr_id = svo.svo_svr_id' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function getLastServiceOrder(array $wheres = [], int $limit = 1, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT svo.svo_start_service_date
                  FROM   service_order AS svo INNER JOIN
                         service_order_detail AS svd ON svd.svd_svo_id = svo.svo_id INNER JOIN
                         equipment AS eq ON eq.eq_id = svo.svo_eq_id INNER JOIN
                         service_task AS svt ON svt.svt_id = svd.svd_svt_id' . $strWhere . ' ORDER BY svo.svo_start_service_date DESC ';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResult = DB::select($query);
        $results = DataParser::arrayObjectToArray($sqlResult);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;

    }


}
