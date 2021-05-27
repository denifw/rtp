<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table sales_order_goods.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderGoodsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sog_id',
        'sog_number',
        'sog_so_id',
        'sog_soc_id',
        'sog_hs_code',
        'sog_name',
        'sog_packing_ref',
        'sog_quantity',
        'sog_uom_id',
        'sog_length',
        'sog_width',
        'sog_height',
        'sog_cbm',
        'sog_gross_weight',
        'sog_net_weight',
        'sog_dimension_unit',
        'sog_notes',
        'sog_sgp_id',
    ];

    /**
     * Base dao constructor for sales_order_goods.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_goods', 'sog', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order_goods.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sog_number',
            'sog_hs_code',
            'sog_name',
            'sog_packing_ref',
            'sog_notes',
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
        $wheres[] = SqlHelper::generateNumericCondition('sog.sog_id', $referenceValue);
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
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $systemSettingValue): array
    {
        $result = [];
        $where = [];
        $where[] = SqlHelper::generateNumericCondition('sog.sog_id', $referenceValue);
        $where[] = SqlHelper::generateNumericCondition('so.so_ss_id', $systemSettingValue);
        $data = self::loadData($where);
        if (count($data) === 1) {
            $result = $data[0];
        }

        return $result;
    }

    /**
     * Function to get data by sales order id
     *
     * @param int $soId To store the reference value of sales order.
     *
     * @return array
     */
    public static function getBySoId(int $soId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sog.sog_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get data by sales order container id
     *
     * @param int $socId To store the reference value of sales order container.
     *
     * @return array
     */
    public static function getBySocId(int $socId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sog.sog_soc_id', $socId);
        $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
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
        $query = 'SELECT sog.sog_id, sog.sog_number, sog.sog_so_id, sog.sog_soc_id, ct.ct_name as sog_container_type,
                        soc.soc_number as sog_container_id, soc.soc_container_number as sog_container_number,
                        soc.soc_seal_number as sog_seal_number, sog.sog_hs_code, sog.sog_name, sog.sog_quantity, sog.sog_uom_id, uom.uom_code as sog_uom,
                        sog.sog_length, sog.sog_height, sog.sog_width, sog.sog_cbm, sog.sog_gross_weight, sog.sog_net_weight, sog.sog_dimension_unit, sog.sog_notes,
                        so.so_number, so.so_consolidate, so.so_container, so.so_inklaring, so.so_warehouse, so.so_delivery,
                        ict.ict_code as so_inco_terms, rel.rel_name as so_customer, so.so_multi_load, so.so_multi_unload,
                        so.so_customer_ref, so.so_bl_ref, so.so_sppb_ref, so.so_aju_ref, so.so_packing_ref,
                        soc.soc_eg_id as sog_eg_id, eg.eg_name as sog_equipment_group, sog.sog_sgp_id,
                        jo.jo_id, jo.jo_srt_id, jo.jo_number, srv.srv_name as jo_service, jo.jo_joh_id,jo.jo_created_on, jo.jo_publish_on,
                        jo.jo_start_on, jo.jo_document_on, jo.jo_finish_on, jo.jo_deleted_on, jo.jo_deleted_reason,
                        joh.joh_reason as jo_hold_reason, joh.joh_created_on as jo_hold_on, soc.soc_ct_id as sog_ct_id,
                       jac.jac_id as jo_action_id, ac.ac_code as jo_action, jae.jae_description as jo_event, ac.ac_style as jo_action_style,
                        sog.sog_packing_ref, sog.sog_notes
                        FROM sales_order_goods as sog
                            INNER JOIN sales_order as so ON sog.sog_so_id = so.so_id
                            INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id
                            LEFT OUTER JOIN inco_terms as ict ON so.so_ict_id = ict.ict_id
                            LEFT OUTER JOIN sales_order_container as soc ON sog.sog_soc_id = soc.soc_id
                            LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id
                            LEFT OUTER JOIN equipment_group as eg ON soc.soc_eg_id = eg.eg_id
                            LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id
                            LEFT OUTER JOIN sales_goods_position as sgp ON sog.sog_sgp_id = sgp.sgp_id
                            LEFT OUTER JOIN job_order as jo ON sgp.sgp_jo_id = jo.jo_id
                            LEFT OUTER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                            LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                            LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                            LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                            LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ct.ct_name, soc.soc_number, sog.sog_name, sog.sog_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to load total row data.
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
        $query = 'SELECT count(DISTINCT (sog.sog_id)) AS total_rows
                FROM sales_order_goods as sog
                            INNER JOIN sales_order as so ON sog.sog_so_id = so.so_id
                            LEFT OUTER JOIN sales_order_container as soc ON sog.sog_soc_id = soc.soc_id
                            LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id
                            LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id ' . $strWhere;
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

        return parent::doPrepareSingleSelectData($data, 'sog_number', 'sog_id');
    }
}
