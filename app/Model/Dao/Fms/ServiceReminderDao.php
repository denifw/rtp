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
 * Class to handle data access object for table service_reminder.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceReminderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'svrm_id', 'svrm_ss_id', 'svrm_eq_id', 'svrm_svt_id',
        'svrm_meter_interval', 'svrm_time_interval', 'svrm_time_interval_period',
        'svrm_meter_threshold', 'svrm_time_threshold', 'svrm_time_threshold_period',
        'svrm_next_due_date', 'svrm_next_due_date_threshold', 'svrm_remark'
    ];

    /**
     * Base dao constructor for service_reminder.
     *
     */
    public function __construct()
    {
        parent::__construct('service_reminder', 'svrm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_reminder.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
          'svrm_remark', 'svrm_time_interval_period',
          'svrm_time_threshold_period', 'svrm_next_due_date', 'svrm_next_due_date_threshold',
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
        $wheres[] = '(svrm.svrm_id = ' . $referenceValue . ')';
        $wheres[] = '(svrm.svrm_ss_id = ' . $systemSettingValue . ')';
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
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT   svrm.svrm_id, svrm.svrm_ss_id, svrm.svrm_eq_id, svrm.svrm_svt_id,
                           svrm.svrm_meter_interval, svrm.svrm_time_interval, svrm.svrm_time_interval_period,
                           svrm.svrm_meter_threshold, svrm.svrm_time_threshold, svrm.svrm_time_threshold_period,
                           svrm.svrm_next_due_date, svrm.svrm_next_due_date_threshold,
                           svrm.svrm_remark, eq.eq_description AS svrm_equipment,
                           svt.svt_name AS svrm_svt_name, eg.eg_name || \' - \' || eq.eq_description AS svrm_eq_name
                  FROM     service_reminder AS svrm INNER JOIN
                           equipment AS eq ON eq.eq_id = svrm.svrm_eq_id INNER JOIN
                           equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                           service_task AS svt ON svt.svt_id = svrm.svrm_svt_id' . $strWhere;
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
     * @param array $orderList To store the list for sortir.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadCompleteData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT svrm.svrm_id, svrm.svrm_ss_id, svrm.svrm_eq_id, svrm.svrm_svt_id,
                         svrm.svrm_meter_interval, svrm.svrm_time_interval, svrm.svrm_time_interval_period,
                         svrm.svrm_meter_threshold, svrm.svrm_time_threshold, svrm.svrm_time_threshold_period,
                         svrm.svrm_next_due_date, svrm.svrm_next_due_date_threshold, svrm.svrm_remark,
                         eg.eg_name || \' \' || eq.eq_description AS svrm_eq_name,
                         svt.svt_name AS svrm_svt_name, eq.eq_primary_meter,
                         eqm.eqm_meter, svo.svo_start_service_date, svo.svo_meter,
                         (svrm.svrm_meter_interval - (coalesce(eqm.eqm_meter, 0) - coalesce(svo.svo_meter, 0))) AS svrm_meter_remaining,
                         DATE_PART(\'day\', svrm.svrm_next_due_date - now()) AS svrm_times_remaining
                   FROM  service_reminder AS svrm INNER JOIN
                         equipment AS eq ON eq.eq_id = svrm.svrm_eq_id INNER JOIN
                         equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                         service_task AS svt ON svt.svt_id = svrm.svrm_svt_id LEFT OUTER JOIN
                         (SELECT   eqm_eq_id, MAX(eqm_meter) AS eqm_meter
						  FROM     equipment_meter
						  WHERE    eqm_deleted_on IS NULL
						  GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id LEFT OUTER JOIN
						  (SELECT  MAX(svo_meter) AS svo_meter, MAX(svo_start_service_date) AS svo_start_service_date, svo_eq_id, svd_svt_id
						   FROM    service_order INNER JOIN
								   service_order_detail ON svd_svo_id = svo_id
						   WHERE  svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
						   GROUP BY svo_eq_id, svd_svt_id) AS svo ON svo.svo_eq_id = eq.eq_id AND svo.svd_svt_id = svt.svt_id' . $strWhere;
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
     * @param int   $eqId  To store equipment id.
     * @param int   $svtId To store service task id.
     *
     * @return array
     */
    public static function getByEqIdSvtId(int $eqId, int $svtId): array
    {
        $wheres = [];
        $wheres[] = '(svrm.svrm_eq_id = ' . $eqId . ')';
        $wheres[] = '(svrm.svrm_svt_id = ' . $svtId. ')';
        $wheres[] = '(svrm.svrm_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT   svrm.svrm_id, svrm.svrm_ss_id, svrm.svrm_eq_id, svrm.svrm_svt_id,
                           svrm.svrm_meter_interval, svrm.svrm_time_interval, svrm.svrm_time_interval_period,
                           svrm.svrm_meter_threshold, svrm.svrm_time_threshold, svrm.svrm_time_threshold_period,
                           svrm.svrm_next_due_date, svrm.svrm_next_due_date_threshold,
                           svrm.svrm_remark, eq.eq_description AS svrm_equipment,
                           svt.svt_name AS svrm_svt_name, eg.eg_name || \' - \' || eq.eq_description AS svrm_eq_name
                  FROM     service_reminder AS svrm INNER JOIN
                           equipment AS eq ON eq.eq_id = svrm.svrm_eq_id INNER JOIN
                           equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                           service_task AS svt ON svt.svt_id = svrm.svrm_svt_id' . $strWhere;
        $sqlResult = DB::select($query);
        $results = DataParser::arrayObjectToArray($sqlResult);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;

    }


}
