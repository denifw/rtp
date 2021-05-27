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
 * Class to handle data access object for table load_unload_delivery.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class LoadUnloadDeliveryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'lud_id',
        'lud_sdl_id',
        'lud_sog_id',
        'lud_jdl_id',
        'lud_quantity',
        'lud_qty_good',
        'lud_qty_damage',
        'lud_ata_on',
        'lud_atd_on',
        'lud_start_on',
        'lud_end_on',
        'lud_rel_id',
        'lud_of_id',
        'lud_pic_id',
        'lud_reference',
        'lud_type',
    ];

    /**
     * Base dao constructor for load_unload_delivery.
     *
     */
    public function __construct()
    {
        parent::__construct('load_unload_delivery', 'lud', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table load_unload_delivery.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'lud_ata_on',
            'lud_atd_on',
            'lud_start_on',
            'lud_end_on',
            'lud_reference',
            'lud_type',
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
        $wheres[] = SqlHelper::generateNumericCondition('lud.lud_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jdlId To store the reference value of job delivery table.
     * @param string $type To store the type of location
     *
     * @return array
     */
    public static function getByJobDeliveryIdAndType(int $jdlId, string $type): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('lud.lud_jdl_id', $jdlId);
        $wheres[] = SqlHelper::generateNullCondition('lud.lud_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('lud.lud_type', $type);
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
        $query = 'SELECT lud.lud_id, lud.lud_sdl_id, lud.lud_rel_id as lud_rel_id, rel.rel_name as lud_relation,
                           lud.lud_of_id as lud_of_id, oo.of_name as lud_office, lud.lud_pic_id as lud_pic_id, cp.cp_name as lud_pic,
                           sog.sog_id as lud_sog_id, sog.sog_name as lud_sog_name, uom.uom_code as lud_uom_code,
                           lud.lud_quantity as lud_quantity, lud.lud_type, lud.lud_reference, sog.sog_hs_code as lud_sog_hs_code,
                           oo.of_address as lud_address, oo.of_postal_code as lud_postalCode, oo.of_longitude as lud_longitude,
                            oo.of_latitude as lud_latitude, cnt.cnt_name as lud_country, stt.stt_name as lud_state,
                            cty.cty_name as lud_city, dtc.dtc_name as lud_district, lud.lud_qty_good, lud.lud_qty_damage, lud.lud_start_on,
                            lud.lud_end_on, lud.lud_ata_on, lud.lud_atd_on
                    FROM load_unload_delivery as lud
                        INNER JOIN sales_order_goods as sog ON lud.lud_sog_id = sog.sog_id
                        LEFT OUTER JOIN relation as rel ON lud.lud_rel_id = rel.rel_id
                        LEFT OUTER JOIN office as oo ON lud.lud_of_id = oo.of_id
                        LEFT OUTER JOIN sales_order_delivery as sdl ON lud.lud_sdl_id = sdl.sdl_id
                        LEFT OUTER JOIN country as cnt ON oo.of_cnt_id = cnt.cnt_id
                        LEFT OUTER JOIN state as stt ON oo.of_stt_id = stt.stt_id
                        LEFT OUTER JOIN city as cty ON oo.of_cty_id = cty.cty_id
                        LEFT OUTER JOIN district as dtc ON oo.of_dtc_id = dtc.dtc_id
                        LEFT OUTER JOIN contact_person as cp ON lud.lud_pic_id = cp.cp_id
                        LEFT OUTER JOIN unit as uom ON sog.sog_uom_id = uom.uom_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY lud.lud_id';
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

        return parent::doPrepareSingleSelectData($data, 'lud_office', 'lud_id');
    }


}
