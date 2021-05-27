<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\User;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group_api_access.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserGroupApiAccessDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'uga_id',
        'uga_usg_id',
        'uga_aa_id'
    ];

    /**
     * Base dao constructor for user_group_api_access.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_api_access', 'uga', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group_api_access.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder();
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
        $where[] = '(uga_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
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
        $wheres[] = SqlHelper::generateNumericCondition('uga_usg_id', $userGroupId);
        $wheres[] = SqlHelper::generateNullCondition('uga_deleted_on');
        return self::loadData($wheres);
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
        $query = 'SELECT uga_id, uga_aa_id, uga_usg_id
                        FROM user_group_api_access' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }

    /**
     * Function to get all record.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupApiAccess($userGroupId): array
    {
        $wheres = [];
        $wheres[] = "(aa.aa_active = 'Y')";
        $wheres[] = '(aa.aa_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # Set Select query;
        $query = "SELECT aa.aa_id, aa.aa_name, aa.aa_description, aa.aa_default, uga.uga_id, (CASE WHEN (uga.active IS NULL) THEN 'N' ELSE uga.active END) AS uga_active
                FROM api_access AS aa LEFT OUTER JOIN
                     (SELECT uga_id, uga_aa_id, (CASE WHEN (uga_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_api_access
                          WHERE (uga_usg_id = " . $userGroupId . ')) AS uga ON aa.aa_id = uga.uga_aa_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY uga_active DESC, aa.aa_default, aa.aa_name';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
