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
 * Class to handle data access object for table sales_order_delivery.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderDeliveryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sdl_id',
        'sdl_so_id',
        'sdl_rel_id',
        'sdl_of_id',
        'sdl_pic_id',
        'sdl_sog_id',
        'sdl_reference',
        'sdl_quantity',
        'sdl_type',
    ];

    /**
     * Base dao constructor for sales_order_delivery.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_delivery', 'sdl', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order_delivery.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sdl_reference',
            'sdl_type',
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
        $wheres[] = SqlHelper::generateNumericCondition('sdl.sdl_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $soId To store the reference value of sales order delivery table.
     * @param string $type To store the type of location.
     *
     * @return array
     */
    public static function getBySoIdAndType(int $soId, string $type): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sdl.sdl_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('sdl.sdl_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('sdl.sdl_type', $type);
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
        $query = 'SELECT sdl.sdl_id, sdl.sdl_so_id, sdl.sdl_rel_id, rel.rel_name as sdl_relation,
                           sdl.sdl_of_id, oo.of_name as sdl_office, sdl.sdl_pic_id, cp.cp_name as sdl_pic,
                           sdl.sdl_sog_id, sog.sog_name as sdl_sog_name, uom.uom_code as sdl_uom,
                           sdl.sdl_quantity, sdl.sdl_type,sdl.sdl_reference, sog.sog_hs_code as sdl_sog_hs_code,
                           oo.of_address as sdl_address, oo.of_postal_code as sdl_postalCode, oo.of_longitude as sdl_longitude,
                            oo.of_latitude as sdl_latitude,
                           cnt.cnt_name as sdl_country, stt.stt_name as sdl_state, cty.cty_name as sdl_city, dtc.dtc_name as sdl_district
                    FROM sales_order_delivery as sdl INNER JOIN
                         relation as rel ON sdl.sdl_rel_id = rel.rel_id INNER JOIN
                         office as oo ON sdl.sdl_of_id = oo.of_id LEFT OUTER JOIN
                         country as cnt ON oo.of_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                         state as stt ON oo.of_stt_id = stt.stt_id LEFT OUTER JOIN
                         city as cty ON oo.of_cty_id = cty.cty_id LEFT OUTER JOIN
                         district as dtc ON oo.of_dtc_id = dtc.dtc_id LEFT OUTER JOIN
                         contact_person as cp ON sdl.sdl_pic_id = cp.cp_id LEFT OUTER JOIN
                         sales_order_goods as sog ON sdl.sdl_sog_id = sog.sog_id LEFT OUTER JOIN
                         unit as uom ON sog.sog_uom_id = uom.uom_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY sdl.sdl_deleted_on DESC, sdl.sdl_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
