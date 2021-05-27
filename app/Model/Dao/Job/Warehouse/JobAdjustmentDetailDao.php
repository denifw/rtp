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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_adjustment_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobAdjustmentDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jad_id',
        'jad_ja_id',
        'jad_jid_id',
        'jad_quantity',
        'jad_gdu_id',
        'jad_sat_id',
        'jad_remark',
        'jad_jis_id',
    ];

    /**
     * Base dao constructor for job_adjustment_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_adjustment_detail', 'jad', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_adjustment_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jad_remark',
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
        $where[] = '(jad_id = ' . $referenceValue . ')';

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
        $where[] = '(jad_id = ' . $referenceValue . ')';
        $where[] = '(jad_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(jad_active = 'Y')";
        $where[] = '(jad_deleted_on IS NULL)';

        return self::loadData($where);
    }

    /**
     * Function to get all active record.
     *
     * @param int $jaId To store the job adjustment reference
     *
     * @return array
     */
    public static function loadDataByJaId($jaId): array
    {
        $where = [];
        $where[] = '(jad.jad_ja_id = ' . $jaId . ')';
        $where[] = '(jad.jad_deleted_on IS NULL)';

        return self::loadData($where);
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
        $query = 'SELECT jad.jad_id, jad.jad_ja_id, jad.jad_jid_id, jad.jad_quantity, jad.jad_sat_id, jad.jad_remark, jad.jad_jis_id,
                          jid.jid_whs_id, whs.whs_name as jad_whs_name, jo.jo_number as jad_jo_number, ji.ji_start_load_on as jad_inbound_on, 
                          jid.jid_lot_number as jad_lot_number, jad.jad_gdu_id, uom.uom_code as jad_uom,
                          sat.sat_description as jad_sat_description, jid.jid_serial_number as jad_serial_number,
                          jid.jid_quantity as jad_jid_quantity, jis.stock as jad_jid_stock
                        FROM job_adjustment_detail as jad INNER JOIN
                        job_inbound_detail as jid ON jad.jad_jid_id = jid.jid_id INNER JOIN
                        warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                         job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                           job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN 
                             stock_adjustment_type as sat on jad.jad_sat_id = sat.sat_id INNER JOIN
                             goods_unit as gdu ON gdu.gdu_id = jad.jad_gdu_id INNER JOIN
                              unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                              (select jis_jid_id, SUM(jis_quantity) as stock
                                from job_inbound_stock 
                                where jis_deleted_on is null
                                group by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWhere;

        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
