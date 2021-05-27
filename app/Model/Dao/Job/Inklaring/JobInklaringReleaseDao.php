<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Inklaring;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inklaring_release.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Inklaring
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobInklaringReleaseDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jikr_id',
        'jikr_jik_id',
        'jikr_soc_id',
        'jikr_sog_id',
        'jikr_quantity',
        'jikr_transporter_id',
        'jikr_truck_number',
        'jikr_driver',
        'jikr_driver_phone',
        'jikr_load_date',
        'jikr_load_time',
        'jikr_gate_in_date',
        'jikr_gate_in_time',
        'jikr_gate_in_by'
    ];

    /**
     * Base dao constructor for job_inklaring_release.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inklaring_release', 'jikr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inklaring_release.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jikr_truck_number', 'jikr_driver', 'jikr_driver_phone',
            'jikr_load_date', 'jikr_load_time', 'jikr_gate_in_date', 'jikr_gate_in_time'
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
        $wheres[] = '(jikr.jikr_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data by job inklaring
     *
     * @param int $jikId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobInklring(int $jikId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jikr.jikr_jik_id', $jikId);
        $wheres[] = SqlHelper::generateNullCondition('jikr.jikr_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jikr.jikr_id, jikr.jikr_jik_id, jikr.jikr_soc_id, jikr.jikr_sog_id,
                         jikr.jikr_quantity, jikr.jikr_transporter_id, jikr.jikr_truck_number, jikr.jikr_driver, jikr.jikr_driver_phone,
                         jikr.jikr_load_date, jikr.jikr_load_time, jikr.jikr_gate_in_date, jikr.jikr_gate_in_time,
                        transporter.rel_name as jikr_transporter, soc.soc_container_number as jikr_container_number,
                         soc.soc_seal_number as jikr_seal_number, ct.ct_name as jikr_container_type, soc.soc_number as jikr_container_id,
                         sog.sog_name as jikr_goods, sog.sog_quantity as jikr_goods_quantity, sog.sog_gross_weight as jikr_gross_weight,
                        sog.sog_net_weight as jikr_net_weight, sog.sog_cbm as jikr_cbm, sog.sog_packing_ref as jikr_packing_ref,
                         sog.sog_hs_code as jikr_hs_code, uc.us_name as jikr_created_by, uom.uom_code as jikr_uom_code
                  FROM   job_inklaring_release as jikr
                      INNER JOIN relation as transporter ON transporter.rel_id = jikr.jikr_transporter_id
                      INNER JOIN users as uc ON jikr.jikr_created_by = uc.us_id
                      LEFT OUTER JOIN sales_order_container as soc ON jikr_soc_id = soc.soc_id
                      LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id
                      LEFT OUTER JOIN sales_order_goods as sog ON jikr.jikr_sog_id = sog.sog_id
                      LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY jikr.jikr_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get the all un released container
     *
     * @param int $soId To store the sales order reference
     * @param int $jikId To store the job inklaring reference
     *
     * @return array
     */
    public static function getUnReleaseContainer($soId, $jikId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $wheres[] = '(soc.soc_id NOT IN (SELECT jikr_soc_id
                                                FROM job_inklaring_release
                                                WHERE jikr_jik_id = ' . $jikId . '
                                                    AND jikr_deleted_on IS NULL AND jikr_soc_id IS NOT NULL))';
        return SalesOrderContainerDao::loadData($wheres);

    }

    /**
     * Function to get the all un released goods
     *
     * @param int $soId To store the sales order reference
     * @param int $jikId To store the job inklaring reference
     *
     * @return array
     */
    public static function getUnReleaseGoods($soId, $jikId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sog.sog_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sog.sog_id, sog.sog_hs_code, sog.sog_name, uom.uom_code as sog_uom, sog.sog_quantity, jikr.qty_release as sog_qty_release
                FROM sales_order_goods AS sog
                    LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id
                    LEFT OUTER JOIN (SELECT jikr_sog_id, SUM(jikr_quantity) as qty_release
                        FROM job_inklaring_release
                        WHERE jikr_jik_id = ' . $jikId . ' AND jikr_deleted_on IS NULL
                        GROUP BY jikr_sog_id) as jikr ON sog.sog_id = jikr.jikr_sog_id '. $strWheres;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            return DataParser::arrayObjectToArray($sqlResults);
        }
        return [];

    }
}
