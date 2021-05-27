<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_stock_transfer.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobStockTransferDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jtr_id', 'jtr_number', 'jtr_ss_id', 'jtr_rel_id', 'jtr_customer_ref', 'jtr_pic_id', 'jtr_who_id', 'jtr_who_us_id', 'jtr_who_date', 'jtr_who_time',
        'jtr_whd_id', 'jtr_whd_us_id', 'jtr_whd_date', 'jtr_whd_time', 'jtr_transporter_id', 'jtr_truck_plate',
        'jtr_container_number', 'jtr_seal_number', 'jtr_driver', 'jtr_driver_phone', 'jtr_ji_jo_id', 'jtr_job_jo_id',
        'jtr_publish_by', 'jtr_publish_on', 'jtr_start_out_on', 'jtr_end_out_on', 'jtr_start_in_on', 'jtr_end_in_on', 'jtr_deleted_reason'
    ];

    /**
     * Base dao constructor for job_stock_transfer.
     *
     */
    public function __construct()
    {
        parent::__construct('job_stock_transfer', 'jtr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_stock_transfer.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
             'jtr_number', 'jtr_customer_ref', 'jtr_who_date', 'jtr_who_time',
             'jtr_whd_date', 'jtr_whd_time', 'jtr_truck_plate',
             'jtr_container_number', 'jtr_seal_number', 'jtr_driver', 'jtr_driver_phone',
             'jtr_publish_on', 'jtr_start_out_on', 'jtr_end_out_on', 'jtr_start_in_on', 'jtr_end_in_on', 'jtr_deleted_reason'
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
        $wheres[] = '(jtr.jtr_id = ' . $referenceValue . ')';
        $wheres[] = '(jtr.jtr_ss_id = ' . $systemSettingValue . ')';
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
        $query = 'SELECT jtr.jtr_id, jtr.jtr_ss_id, jtr.jtr_number, jtr.jtr_rel_id, jtr.jtr_customer_ref, jtr.jtr_pic_id, jtr.jtr_who_id, jtr.jtr_who_us_id, jtr.jtr_who_date, jtr.jtr_who_time,
                         jtr.jtr_whd_id, jtr.jtr_whd_us_id, jtr.jtr_whd_date, jtr.jtr_whd_time, jtr.jtr_transporter_id, jtr.jtr_truck_plate,
                         jtr.jtr_container_number, jtr.jtr_seal_number, jtr.jtr_driver, jtr.jtr_driver_phone, jtr.jtr_ji_jo_id, jtr.jtr_job_jo_id,
                         jtr.jtr_publish_by, jtr.jtr_publish_on, jtr.jtr_start_out_on, jtr.jtr_end_out_on, jtr.jtr_start_in_on, jtr.jtr_end_in_on, 
                         jtr.jtr_deleted_on, jtr.jtr_deleted_reason, who.wh_name AS jtr_who_name, whous.us_name AS jtr_who_us_name, 
                         whd.wh_name AS jtr_whd_name, whdus.us_name AS jtr_whd_us_name, transporter.rel_name AS jtr_transporter_name,
                         customer.rel_name AS jtr_rel_name, pic.cp_name AS jtr_pic_name
                  FROM   job_stock_transfer AS jtr INNER JOIN
                         warehouse AS who ON who.wh_id = jtr.jtr_who_id INNER JOIN
                         users AS whous ON whous.us_id = jtr.jtr_who_us_id  INNER JOIN
                         warehouse AS whd ON whd.wh_id = jtr.jtr_whd_id INNER JOIN
                         users AS whdus ON whdus.us_id = jtr.jtr_whd_us_id INNER JOIN
                         relation AS transporter ON transporter.rel_id = jtr.jtr_transporter_id INNER JOIN
                         relation AS customer ON customer.rel_id = jtr.jtr_rel_id LEFT OUTER JOIN
                         contact_person AS pic ON pic.cp_id = jtr.jtr_pic_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
