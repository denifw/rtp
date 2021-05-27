<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_outbound_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutboundDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jod_id',
        'jod_job_id',
        'jod_jog_id',
        'jod_jid_id',
        'jod_quantity',
        'jod_qty_loaded',
        'jod_gd_id',
        'jod_gdu_id',
        'jod_whs_id',
        'jod_lot_number',
        'jod_jis_id',
    ];

    /**
     * Base dao constructor for job_outbound_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_outbound_detail', 'jod', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_outbound_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jod_lot_number',
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
        $where = [];
        $where[] = '(jod_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
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
        $where = [];
        $where[] = '(jod_id = ' . $referenceValue . ')';
        $where[] = '(jod_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all active record.
     *
     * @param int $jobId TO store the reference of job outbound
     *
     * @return array
     */
    public static function loadSimpleDataByJobOutboundId($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jod_job_id = ' . $jobId . ')';
        $wheres[] = '(jod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jod_id, jod_jid_id, jod_quantity, jod_qty_loaded, jod_jis_id, jod_jid_id
                        FROM job_outbound_detail ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all active record.
     *
     * @param int $jobId TO store the reference of job outbound
     *
     * @return array
     */
    public static function loadJobOutboundForPos($jobId): array
    {
        $wheres = [];
        $wheres[] = '(jod_job_id = ' . $jobId . ')';
        $wheres[] = '(jod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT SUM(jod_quantity) AS jod_quantity,  gd.gd_name as jod_gd_name, uom.uom_name AS jod_unit
                  FROM   job_outbound_detail AS jod INNER JOIN
                         job_goods as jog ON jod.jod_jog_id = jog.jog_id INNER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                         goods_unit as gdu ON jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id' . $strWhere .
                 'GROUP BY  gd.gd_name, uom_name';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get loaded data.
     *
     * @param array $wheres     To store the reference value of the table.
     *
     * @return array
     */
    public static function getDataForLoading(array $wheres = []): array
    {
        $wheres[] = '(jod.jod_deleted_on IS NULL)';

        $data = self::loadData($wheres);
        $results = [];
        if (empty($data) === false) {
            $keys = array_keys($data[0]);
            $keyVal = [];
            foreach ($keys as $key) {
                $newKey = str_replace('jod_', 'jodl_', $key);
                $keyVal[$key] = $newKey;
            }
            foreach ($data as $row) {
                $newRow = [];
                foreach ($keyVal as $key => $val) {
                    $newRow[$val] = $row[$key];
                }
                $results[] = $newRow;
            }
        }
        return $results;
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
        $query = 'SELECT jod.jod_id, jod.jod_jog_id, jod.jod_jis_id, jog.jog_serial_number as jod_jog_number,
                        jod.jod_quantity, jod.jod_gdu_id, uom.uom_code as jod_unit, jod.jod_jid_id, whs.whs_name as jod_storage,
                        br.br_name as jod_br_name, gdc.gdc_name as jod_gdc_name, gd.gd_sku as jod_gd_sku, gd.gd_name as jod_gd_name,
                        (CASE WHEN jod.jod_qty_loaded IS NULL THEN 0 ELSE jod.jod_qty_loaded END) as jod_qty_loaded,
                        (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as jod_weight,
                        (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as jod_volume,
                        jid.jid_gdt_id, gdt.gdt_code as jod_gdt_code, gdt.gdt_description as jod_gdt_description,
                        jod.jod_whs_id, jid.jid_serial_number as jod_jid_serial_number, jod.jod_gd_id,
                        jid.jid_lot_number as jod_lot_number, gd.gd_sn as jod_gd_sn, gcd.gcd_code as jod_gcd_code,
                        gcd.gcd_description as jod_gcd_description, jid.jid_packing_number as jod_packing_number
                        FROM job_outbound_detail as jod INNER JOIN
                        job_goods as jog ON jod.jod_jog_id = jog.jog_id INNER JOIN
                         goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                         warehouse_storage as whs ON jod.jod_whs_id = whs.whs_id INNER JOIN
                        goods_unit as gdu ON jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                        unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                        brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                        goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                          job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id LEFT OUTER JOIN
                              goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                              goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record.
     *
     * @param array $data To store the list condition query.
     *
     * @return array
     */
    public static function doPrepareOutboundDetailData(array $data = []): array
    {
        $results = [];
        if (empty($data) === false) {
            foreach ($data as $row) {
                if (empty($row['jid_gdt_id']) === true) {
                    $row['jod_condition'] = new LabelSuccess(Trans::getWord('good'));
                } else {
                    $row['jod_condition'] = $row['jod_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jod_gcd_description'];
                }
                $qtyLoaded = (float) $row['jod_qty_loaded'];
                if ($qtyLoaded > 0) {
                    $row['jod_qty_return'] = (float) $row['jod_quantity'] - $qtyLoaded;
                }
                $row['jod_goods'] = $row['jod_gdc_name'] . ' ' . $row['jod_br_name'] . ' ' . $row['jod_gd_name'];
                $results[] = $row;
            }
        }
        return $results;
    }

    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param int $joId To store the job order reference
     *
     * @return array
     */
    public static function getTotalDifferentQuantityUnloadWithPickingByJobOrderId($joId): array
    {
        $jogWheres = [];
        $jogWheres[] = '(jog_deleted_on IS NULL)';
        $jogWheres[] = '(jog_jo_id = ' . $joId . ')';
        $strJogWhere = ' WHERE ' . implode(' AND ', $jogWheres);
        $jodWheres = [];
        $jodWheres[] = '(j.jod_deleted_on IS NULL)';
        $jodWheres[] = '(job.job_jo_id = ' . $joId . ')';
        $strJodWhere = ' WHERE ' . implode(' AND ', $jodWheres);
        $query = 'SELECT jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick, (jog.qty_outbound - jod.qty_pick) as diff_qty
                FROM (SELECT jog_jo_id, sum(jog_quantity) as qty_outbound
                      FROM job_goods ' . $strJogWhere . ' GROUP BY jog_jo_id) as jog
                       INNER JOIN
                     (SELECT job.job_jo_id, sum(j.jod_quantity) as qty_pick
                      FROM job_outbound_detail as j INNER JOIN
                       job_outbound as job ON j.jod_job_id = job.job_id ' . $strJodWhere . ' GROUP BY job.job_jo_id) as jod ON jod.job_jo_id = jog.jog_jo_id
                GROUP BY jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {

            return DataParser::objectToArray($sqlResult[0], [
                'jog_jo_id',
                'job_jo_id',
                'qty_outbound',
                'qty_pick',
                'diff_qty',
            ]);
        }

        return [];
    }

    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param int $joId To store the job order reference
     *
     * @return array
     */
    public static function getTotalDifferentQuantityLoadingWithJobGoodsByJobOrderId($joId): array
    {
        $jogWheres = [];
        $jogWheres[] = '(jog_deleted_on IS NULL)';
        $jogWheres[] = '(jog_jo_id = ' . $joId . ')';
        $strJogWhere = ' WHERE ' . implode(' AND ', $jogWheres);
        $jodWheres = [];
        $jodWheres[] = '(j.jod_deleted_on IS NULL)';
        $jodWheres[] = '(job.job_jo_id = ' . $joId . ')';
        $strJodWhere = ' WHERE ' . implode(' AND ', $jodWheres);
        $query = 'SELECT jog.jog_jo_id, jog.qty_planning, jod.job_jo_id, jod.qty_loaded, (jog.qty_planning - jod.qty_loaded) as diff_qty
                FROM (SELECT jog_jo_id, sum(jog_quantity) as qty_planning
                      FROM job_goods ' . $strJogWhere . ' GROUP BY jog_jo_id) as jog
                       LEFT OUTER JOIN
                     (SELECT job.job_jo_id, sum((CASE WHEN j.jod_qty_loaded IS NULL THEN 0 ELSE j.jod_qty_loaded END)) as qty_loaded
                      FROM job_outbound_detail as j INNER JOIN
                       job_outbound as job ON j.jod_job_id = job.job_id ' . $strJodWhere . ' GROUP BY job.job_jo_id) as jod ON jog.jog_jo_id = jod.job_jo_id
                GROUP BY jog.jog_jo_id, jog.qty_planning, jod.job_jo_id, jod.qty_loaded ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {

            return DataParser::objectToArray($sqlResult[0], [
                'jog_jo_id',
                'job_jo_id',
                'qty_planning',
                'qty_loaded',
                'diff_qty',
            ]);
        }

        return [];
    }



    /**
     * Function to get the difference quantity load and quantity stored
     *
     * @param int $jobId To store the job order reference
     *
     * @return bool
     */
    public static function isValidAllInboundDetailIdByJobId($jobId): bool
    {
        $valid = true;
        $wheres = [];
        $wheres[] = '(jod.jod_jid_id IS NULL)';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $wheres[] = '(jod.jod_job_id = ' . $jobId . ')';
        $wheres[] = "(gd.gd_sn = 'Y')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT COUNT(jod.jod_id) as jod_total
                FROM job_outbound_detail as jod INNER JOIN
                job_goods as jog ON jog.jog_id = jod.jod_jog_id INNER JOIN
                goods as gd ON gd.gd_id = jog.jog_gd_id ' . $strWheres;
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $rows = (int) DataParser::objectToArray($sqlResult[0])['jod_total'];
            if ($rows > 0) {
                $valid = false;
            }
        }

        return $valid;
    }
}
