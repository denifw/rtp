<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\User;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group_notification.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class UserGroupNotificationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugn_id', 'ugn_usg_id', 'ugn_nt_id'
    ];

    /**
     * Base dao constructor for user_group_notification.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_notification', 'ugn', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group_notification.
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
        $wheres[] = '(ugn_id = ' . $referenceValue . ')';
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
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(ugn_id = ' . $referenceValue . ')';
        $wheres[] = '(ugn_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all data based on user group id.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function getByUserGroup(int $userGroupId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ugn.ugn_usg_id', $userGroupId);
        $wheres[] = SqlHelper::generateNullCondition('ugn.ugn_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('nt.nt_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('nt.nt_active', 'Y');
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
        $query = 'SELECT ugn.ugn_id, ugn.ugn_id, ugn_usg_id, ugn_nt_id,
                         nt.nt_code AS ugn_nt_code, nt.nt_module AS ugn_nt_module
                  FROM user_group_notification AS ugn
                  INNER JOIN notification_template AS nt ON nt.nt_id = ugn.ugn_nt_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (ugn_id)) AS total_rows
                        FROM user_group_notification' . $strWhere;

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
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'ugn_', 'ugn_id');
    }


    /**
     * Function to get all record.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupNotification($userGroupId): array
    {
        $wheres = [];
        $wheres[] = '(nt.nt_active = \'Y\')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = "SELECT nt.nt_id, nt.nt_code, nt.nt_module, nt.nt_description, ugn.ugn_id,
                  (CASE WHEN (ugn.active IS NULL) THEN 'N' ELSE ugn.active END) AS ugn_active
                  FROM notification_template AS nt
                       LEFT OUTER JOIN (SELECT ugn_id, ugn_nt_id, (CASE WHEN (ugn_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_notification
                          WHERE (ugn_usg_id = " . $userGroupId . ')) AS ugn ON ugn.ugn_nt_id = nt.nt_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugn_active DESC';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get user group notification by code.
     *
     * @param string $notificationCode The notification code.
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function loadByCodeAndSsId($notificationCode, $ssId): array
    {
        # Set Select query;
        $query = "SELECT us.us_id, cp.cp_id
                  FROM notification_template AS nt
                  INNER JOIN user_group_notification AS ugn ON ugn.ugn_nt_id = nt.nt_id
                  INNER JOIN user_group AS usg ON usg.usg_id = ugn.ugn_usg_id
                  INNER JOIN user_group_detail AS ugd ON ugd.ugd_usg_id = usg.usg_id
                  INNER JOIN user_mapping AS ump ON ump.ump_id = ugd.ugd_ump_id
                  INNER JOIN users AS us ON us.us_id = ump.ump_us_id
                  INNER JOIN contact_person AS cp ON cp.cp_id = ump.ump_cp_id
                  WHERE nt.nt_code = '" . $notificationCode . "'  AND ump.ump_ss_id = '" . $ssId . "'
                  AND usg.usg_deleted_on IS NULL AND ugn.ugn_deleted_on IS NULL AND ugd.ugd_deleted_on IS NULL
                  AND us.us_active = 'Y' AND us.us_deleted_on IS NULL
                  AND cp.cp_active = 'Y' AND cp.cp_deleted_on IS NULL";
        # Set Where condition.
        $query .= ' ORDER BY ugn_active DESC';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
