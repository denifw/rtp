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
 * Class to handle data access object for table renewal_order.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rno_id', 'rno_ss_id', 'rno_number', 'rno_eq_id', 'rno_rnr_id', 'rno_order_date', 'rno_planning_date',
        'rno_vendor_id', 'rno_manager_id', 'rno_request_by_id', 'rno_remark', 'rno_deleted_reason',
        'rno_approved_by', 'rno_approved_on', 'rno_start_renewal_date', 'rno_start_renewal_time', 'rno_start_renewal_by',
        'rno_finish_by', 'rno_finish_on'
    ];

    /**
     * Base dao constructor for renewal_order.
     *
     */
    public function __construct()
    {
        parent::__construct('renewal_order', 'rno', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table renewal_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rno_order_date', 'rno_planning_date', 'rno_remark', 'rno_deleted_reason',
            'rno_approved_on', 'rno_start_renewal_date', 'rno_start_renewal_time',  'rno_finish_on'
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
        $wheres[] = '(rno.rno_id = ' . $referenceValue . ')';
        $wheres[] = '(rno.rno_ss_id = ' . $systemSettingValue . ')';
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
        $query = 'SELECT rno.rno_id, rno.rno_ss_id, rno.rno_number, rno.rno_eq_id, rno.rno_vendor_id,
                         rno.rno_order_date, rno.rno_planning_date, rno.rno_manager_id, rno.rno_request_by_id,
                         rno.rno_remark, rno.rno_deleted_reason, rno.rno_approved_by, rno.rno_approved_on,
                         rno.rno_start_renewal_date, rno.rno_start_renewal_time, rno.rno_start_renewal_by,
                         rno.rno_finish_by, rno.rno_finish_on, eg.eg_name || \' - \' || eq.eq_description as rno_eq_name,
                         vendor.rel_name as rno_vendor_name, manager.us_name as rno_manager_name,
                         requestBy.us_name as rno_request_by_name, rnr.rnr_id, rnr.rnr_reject_reason
                  FROM   renewal_order AS rno
                         INNER JOIN equipment AS eq ON eq.eq_id = rno.rno_eq_id
                         INNER JOIN equipment_group AS eg ON eg.eg_id = eq.eq_eg_id
                         INNER JOIN relation AS vendor ON vendor.rel_id = rno.rno_vendor_id
                         INNER JOIN users AS manager ON manager.us_id = rno.rno_manager_id
                         INNER JOIN users AS requestBy ON requestBy.us_id = rno.rno_request_by_id
                         LEFT OUTER JOIN renewal_order_request AS rnr ON rnr.rnr_id = rno.rno_rnr_id' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
