<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job\Trucking;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table route_delivery.
 *
 * @package    app
 * @subpackage Model\Dao\Trucking
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class RouteDeliveryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rd_id',
        'rd_ss_id',
        'rd_code',
        'rd_dtc_or_id',
        'rd_dtc_des_id',
        'rd_distance',
        'rd_drive_time',
        'rd_toll_1',
        'rd_toll_2',
        'rd_toll_3',
        'rd_toll_4',
        'rd_toll_5',
        'rd_toll_6',
        'rd_active',
        'rd_deleted_reason'
    ];

    /**
     * Base dao constructor for route_delivery.
     *
     */
    public function __construct()
    {
        parent::__construct('route_delivery', 'rd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table route_delivery.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rd_code',
            'rd_deleted_reason',
            'rd_active'
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
        $wheres[] = '(rd_id = ' . $referenceValue . ')';
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
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(rd_id = ' . $referenceValue . ')';
        $wheres[] = '(rd_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
        $query = 'SELECT rd.rd_id, rd.rd_code, rd.rd_dtc_or_id, rd.rd_dtc_des_id, rd_distance,
                        rd.rd_drive_time, rd.rd_toll_1, rd.rd_toll_2, rd.rd_toll_3, rd.rd_toll_4, rd.rd_toll_5, rd.rd_toll_6, rd.rd_active, rd.rd_deleted_reason, rd.rd_deleted_on,
                        dtc_or.dtc_name as rd_dtc_or_name, cty_or.cty_name as rd_cty_or_name, stt_or.stt_name as rd_stt_or_name, cnt_or.cnt_name as rd_cnt_or_name,
                        dtc_des.dtc_name as rd_dtc_des_name, cty_des.cty_name as rd_cty_des_name, stt_des.stt_name as rd_stt_des_name, us.us_name as rd_us_name, cnt_des.cnt_name as rd_cnt_des_name
                        FROM route_delivery rd
                        INNER JOIN district dtc_or on rd.rd_dtc_or_id = dtc_or.dtc_id
                        INNER JOIN city cty_or on dtc_or.dtc_cty_id = cty_or.cty_id
                        INNER JOIN country cnt_or on dtc_or.dtc_cnt_id = cnt_or.cnt_id
                        INNER JOIN state stt_or on dtc_or.dtc_stt_id = stt_or.stt_id
                        INNER JOIN district dtc_des on rd.rd_dtc_des_id = dtc_des.dtc_id
                        INNER JOIN city cty_des on dtc_des.dtc_cty_id = cty_des.cty_id
                        INNER JOIN country cnt_des on dtc_des.dtc_cnt_id = cnt_des.cnt_id
                        INNER JOIN state stt_des on dtc_des.dtc_stt_id = stt_des.stt_id
                        LEFT JOIN users us on rd.rd_deleted_by = us.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
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
        $query = 'SELECT count(DISTINCT (rd.rd_id)) AS total_rows
                         FROM route_delivery rd
                        INNER JOIN district dtc_or on rd.rd_dtc_or_id = dtc_or.dtc_id
                        INNER JOIN city cty_or on dtc_or.dtc_cty_id = cty_or.cty_id
                        INNER JOIN country cnt_or on dtc_or.dtc_cnt_id = cnt_or.cnt_id
                        INNER JOIN state stt_or on dtc_or.dtc_stt_id = stt_or.stt_id
                        INNER JOIN district dtc_des on rd.rd_dtc_des_id = dtc_des.dtc_id
                        INNER JOIN city cty_des on dtc_des.dtc_cty_id = cty_des.cty_id
                        INNER JOIN country cnt_des on dtc_des.dtc_cnt_id = cnt_des.cnt_id
                        INNER JOIN state stt_des on dtc_des.dtc_stt_id = stt_des.stt_id
                        LEFT JOIN users us on rd.rd_deleted_by = us.us_id' . $strWhere;
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

        return parent::doPrepareSingleSelectData($data, 'rd_code', 'rd_id');
    }


}
