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
 * Class to handle data access object for table sales_order_container.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderContainerDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'soc_id',
        'soc_number',
        'soc_eg_id',
        'soc_so_id',
        'soc_ct_id',
        'soc_container_number',
        'soc_seal_number',
    ];

    /**
     * Base dao constructor for sales_order_container.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_container', 'soc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order_container.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'soc_number',
            'soc_container_number',
            'soc_seal_number',
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
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by sales order id value
     *
     * @param int $soId To store the reference value of Sales order.
     *
     * @return array
     */
    public static function getBySoId(int $soId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
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
        $query = 'SELECT soc.soc_id, soc.soc_number, soc.soc_so_id, soc.soc_ct_id, ct.ct_name as soc_container_type,
                        soc.soc_container_number, soc.soc_seal_number, soc.soc_eg_id, eg.eg_name as soc_equipment_group,
                        jdl.total_delivery
                        FROM sales_order_container as soc
                            LEFT OUTER JOIN container as ct ON soc.soc_ct_id = ct.ct_id
                            LEFT OUTER JOIN equipment_group as eg On soc.soc_eg_id = eg.eg_id
                            LEFT OUTER JOIN (SELECT jdld.jdld_soc_id, COUNT(jdld.jdld_id) as total_delivery
                                FROM job_delivery_detail as jdld
                                INNER JOIN job_delivery as jdl ON jdl.jdl_id = jdld.jdld_jdl_id
                                INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                                WHERE (jo.jo_deleted_on IS NULL) AND (jdld.jdld_deleted_on IS NULL)
                                    AND (jdld.jdld_soc_id IS NOT NULL)
                                GROUP BY jdld.jdld_soc_id) as jdl ON soc.soc_id = jdl.jdld_soc_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ct.ct_name,soc.soc_number, soc.soc_container_number, soc.soc_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
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

        return parent::doPrepareSingleSelectData($data, ['soc_number', 'soc_container_type', 'soc_container_number'], 'soc_id');
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
    public static function loadSingleSelectTruckData(array $wheres = [], array $orders = [], int $limit = 30): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, ['soc_number', 'soc_equipment_group'], 'soc_id');
    }


}
