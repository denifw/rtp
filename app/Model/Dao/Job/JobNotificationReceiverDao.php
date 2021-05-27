<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_notification_receiver.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobNotificationReceiverDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jnr_id', 'jnr_jo_id', 'jnt_cp_id'
    ];

    /**
     * Base dao constructor for job_notification_receiver.
     *
     */
    public function __construct()
    {
        parent::__construct('job_notification_receiver', 'jnr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_notification_receiver.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
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
        $wheres[] = '(jnr_id = ' . $referenceValue . ')';
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
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jnr.jnr_id, jnr.jnr_jo_id, jnr.jnr_cp_id,
                         cp.cp_name AS jnr_cp_name, cp.cp_email AS jnr_cp_email, rel.rel_name AS jnr_rel_name
                  FROM job_notification_receiver AS jnr
                  INNER JOIN job_order AS jo ON jo.jo_id = jnr.jnr_jo_id
                  INNER JOIN  contact_person AS cp ON cp.cp_id = jnr.jnr_cp_id
                  INNER JOIN office AS of ON of.of_id = cp.cp_of_id
                  INNER JOIN relation AS rel ON rel.rel_id = of.of_rel_id' . $strWhere;
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
     * Function to get all record.
     *
     * @param int    $joId             To store job order reference.
     * @param string $notificationCode The notification code.
     * @param string $module           The module code.
     * @param int    $ssId             To store the system setting value.
     *
     * @return array
     */
    public static function loadDataByUserGroupNotification(int $joId, string $notificationCode, string $module, int $ssId): array
    {
        $query = "SELECT jnr.jnr_cp_id
                  FROM job_notification_receiver AS jnr
                  INNER JOIN job_order AS jo ON jo.jo_id = jnr.jnr_jo_id
                  INNER JOIN  contact_person AS cp ON cp.cp_id = jnr.jnr_cp_id
                  WHERE (jnr.jnr_jo_id = $joId) AND (jnr.jnr_deleted_on IS NULL) UNION ";
        $query .= "SELECT cp.cp_id AS jnr_cp_id
                  FROM notification_template AS nt 
                  INNER JOIN user_group_notification AS ugn ON ugn.ugn_nt_id = nt.nt_id
                  INNER JOIN user_group AS usg ON usg.usg_id = ugn.ugn_usg_id
                  INNER JOIN user_group_detail AS ugd ON ugd.ugd_usg_id = usg.usg_id
                  INNER JOIN user_mapping AS ump ON ump.ump_id = ugd.ugd_ump_id
                  INNER JOIN users AS us ON us.us_id = ump.ump_us_id
                  INNER JOIN contact_person AS cp ON cp.cp_id = ump.ump_cp_id
                  WHERE (nt.nt_code = '" . $notificationCode . "')  AND (nt.nt_module = '" . $module . "') AND (ump.ump_ss_id = '" . $ssId . "') 
                  AND (usg.usg_deleted_on IS NULL) AND (ugn.ugn_deleted_on IS NULL) AND (ugd.ugd_deleted_on IS NULL) 
                  AND (us.us_active = 'Y') AND (us.us_deleted_on IS NULL)
                  AND (cp.cp_active = 'Y') AND (cp.cp_deleted_on IS NULL)";
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
        $query = 'SELECT count(DISTINCT (jnr.jnr_id)) AS total_rows
                  FROM job_notification_receiver AS jnr
                  INNER JOIN job_order AS jo ON jo.jo_id = jnr.jnr_jo_id
                  INNER JOIN  contact_person AS cp ON cp.cp_id = jnr.jnr_cp_id' . $strWhere;

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
     * @param int   $limit  To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'jnr_cp_name', 'jnr_id');
    }


}
