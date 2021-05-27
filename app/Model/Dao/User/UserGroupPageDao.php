<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\User;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group_page.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupPageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugp_id',
        'ugp_usg_id',
        'ugp_pg_id',
    ];

    /**
     * Base dao constructor for user_group_page.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_page', 'ugp', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group_page.
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
        $query = 'SELECT ugp_id
                        FROM user_group_page
                        WHERE (ugp_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], self::$Fields);
        }

        return $result;
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
        $wheres[] = SqlHelper::generateNumericCondition('ugp.ugp_usg_id', $userGroupId);
        $wheres[] = SqlHelper::generateNullCondition('ugp.ugp_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('pg.pg_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_default', 'N');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_system', 'N');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_active', 'Y');
        return self::loadAllData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadAllData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ugp_id, ugp_usg_id, ugp_pg_id
                        FROM user_group_page as ugp
                        INNER JOIN page as pg ON pg.pg_id = ugp.ugp_pg_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all data based on user group id.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupPage($userGroupId): array
    {
        $wheres = [];
        $wheres[] = "(pg.pg_system = 'N')";
        $wheres[] = "(pg.pg_active = 'Y')";
        $wheres[] = "(pg.pg_default = 'N')";
        $wheres[] = '(pg.pg_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # Set Select query;
        $query = "SELECT pg.pg_id, pg.pg_title, pg.pg_description, pc.pc_name as pg_category, pg.pg_default, ugp.ugp_id, (CASE WHEN (ugp.active IS NULL) THEN 'N' ELSE ugp.active END) AS ugp_active
                FROM page AS pg INNER JOIN
                    page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                     (SELECT ugp_id, ugp_pg_id, (CASE WHEN (ugp_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_page
                          WHERE (ugp_usg_id = " . $userGroupId . ')) AS ugp ON pg.pg_id = ugp.ugp_pg_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugp_active DESC, pg.pg_default, pg.pg_title, pc.pc_name';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'pg_id',
            'pg_title',
            'pg_description',
            'pg_category',
            'pg_default',
            'ugp_id',
            'ugp_active',
        ]);

    }

    /**
     * Function to get all data based on user group id.
     *
     * @param int $usgId To store the id of user group.
     * @param int $ssId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupDetail(int $usgId, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');
        if ($ssId > 0) {
            $wheres[] = SqlHelper::generateNumericCondition('ump.ump_ss_id', $ssId);
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # Set Select query;
        $query = "SELECT ump.ump_id, ss.ss_relation as ump_ss_name, rel.rel_name as ump_rel_name, us.us_name as ump_us_name, us.us_username as ump_us_username, ugd.ugd_id, (CASE WHEN (ugd.active IS NULL) THEN 'N' ELSE ugd.active END) AS ugd_active
                FROM user_mapping AS ump
                    INNER JOIN users AS us on ump.ump_us_id = us.us_id
                    INNER JOIN relation as rel ON ump.ump_rel_id = rel.rel_id
                    INNER JOIN system_setting as ss ON ump.ump_ss_id = ss.ss_id
                    LEFT OUTER JOIN (SELECT ugd_id, ugd_ump_id, (CASE WHEN (ugd_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_detail
                          WHERE (ugd_usg_id = " . $usgId . ')) AS ugd ON ump.ump_id = ugd.ugd_ump_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugd_active DESC, ss.ss_relation, rel.rel_name, us.us_name, ump.ump_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
