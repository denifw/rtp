<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job\Delivery;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table job_delivery_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Delivery
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobDeliveryDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jdld_id',
        'jdld_jdl_id',
        'jdld_soc_id',
        'jdld_final_destination',
    ];

    /**
     * Base dao constructor for job_delivery_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_delivery_detail', 'jdld', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_delivery_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jdld_final_destination',
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
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by job detail reference value
     *
     * @param int $jdlId To store the reference value of job delivery table.
     *
     * @return array
     */
    public static function getByJobDeliveryId(int $jdlId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_jdl_id', $jdlId);
        $wheres[] = SqlHelper::generateNullCondition('jdld.jdld_deleted_on');
        return self::loadData($wheres);
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
        $query = 'SELECT jdld.jdld_id, jdld.jdld_jdl_id, jdld.jdld_soc_id, so.so_id as jdld_so_id, so.so_number as jdld_so_number,
                        so.so_rel_id as jdld_rel_id, rel.rel_name as jdld_so_customer, soc.soc_eg_id as jdld_eg_id,
                        eg.eg_name as jdld_equipment_group, soc.soc_ct_id as jdld_ct_id,
                        ct.ct_name as jdld_container_type, soc.soc_container_number as jdld_container_number,
                        soc.soc_seal_number as jdld_seal_number, soc.soc_number as jdld_soc_number
                        FROM job_delivery_detail as jdld
                            INNER JOIN sales_order_container as soc ON jdld.jdld_soc_id = soc.soc_id
                            INNER JOIN sales_order as so ON soc.soc_so_id = so.so_id
                            INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                            LEFT OUTER JOIN equipment_group as eg ON soc.soc_eg_id = eg.eg_id
                            LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jdld.jdld_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to load goods data for container
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadGoodsDataContainer(array $wheres = []): array
    {
        $wheres[] = SqlHelper::generateNullCondition('jdld.jdld_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jdld.jdld_id, jdld.jdld_soc_id, so.so_id as jdld_so_id, so.so_number as jdld_so_number,
                        so.so_rel_id as jdld_rel_id, rel.rel_name as jdld_so_customer, soc.soc_ct_id as jdld_ct_id,
                        ct.ct_name as jdld_container_type, soc.soc_container_number as jdld_container_number, soc.soc_number as jdld_soc_number,
                        soc.soc_seal_number as jdld_seal_number, sog.sog_id as jdld_sog_id, sog.sog_hs_code as jdld_hs_code, sog.sog_name as jdld_goods,
                        sog.sog_quantity as jdld_goods_quantity, uom.uom_code as jdld_goods_uom, sog.sog_cbm as jdld_goods_cbm,
                        sog.sog_gross_weight as jdld_goods_gross_weight, sog.sog_net_weight as jdld_goods_net_weight, sog.sog_dimension_unit as jdld_goods_dimension_unit,
                        eg.eg_name as jdld_equipment_group
                        FROM job_delivery_detail as jdld
                            INNER JOIN sales_order_container as soc ON jdld.jdld_soc_id = soc.soc_id
                            INNER JOIN sales_order as so ON soc.soc_so_id = so.so_id
                            INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                            LEFT OUTER JOIN equipment_group as eg ON soc.soc_eg_id = eg.eg_id
                            LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id
                            LEFT OUTER JOIN sales_order_goods as sog ON soc.soc_id = sog.sog_id
                            LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id ' . $strWhere;
        $query .= ' ORDER BY jdld.jdld_id';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to load goods data for non container
     *
     * @param int $id To store the reference value of job order table.
     * @param int $joId To store the reference value of job order table.
     *
     * @return array
     */
    public static function loadGoodsPositionByIdAndJoId(int $id, int $joId): array
    {
        $wheres[] = SqlHelper::generateNumericCondition('sgp.sgp_jo_id', $joId);
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_id', $id);
        $wheres[] = SqlHelper::generateNullCondition('sgp.sgp_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sgp.sgp_id
                        FROM sales_goods_position as sgp
                            INNER JOIN sales_order_goods as sog ON sgp.sgp_sog_id = sog.sog_id
                            INNER JOIN job_delivery_detail as jdld ON jdld.jdld_soc_id = sog.sog_soc_id' . $strWhere;
        $query .= ' GROUP BY sgp.sgp_id';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to check is job delivery has details or not
     *
     * @param int $jdlId To store job delivery reference.
     *
     * @return bool
     */
    public static function isJobDeliveryHasDetail(int $jdlId): bool
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jdld_jdl_id', $jdlId);
        $wheres[] = SqlHelper::generateNullCondition('jdld_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jdld_id
                        FROM job_delivery_detail ' . $strWhere;
        $sqlResults = DB::select($query);
        return !empty($sqlResults);
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
        $query = 'SELECT count(DISTINCT (jdld_id)) AS total_rows
                        FROM job_delivery_detail' . $strWhere;

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
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 30): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'jdld_', 'jdld_id');
    }


}
