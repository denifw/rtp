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
 * Class to handle data access object for table user_group_right.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupRightDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugr_id',
        'ugr_usg_id',
        'ugr_pr_id',
    ];

    /**
     * Base dao constructor for user_group_right.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_right', 'ugr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group_right.
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
        $query = 'SELECT ugr_id
                        FROM user_group_right
                        WHERE (ugr_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
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
        $wheres[] = SqlHelper::generateNumericCondition('ugr.ugr_usg_id', $userGroupId);
        $wheres[] = SqlHelper::generateNullCondition('ugr.ugr_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('pg.pg_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_system', 'N');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('pr.pr_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('pr.pr_default', 'N');
        $wheres[] = SqlHelper::generateStringCondition('pr.pr_active', 'Y');
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
        $query = 'SELECT ugr.ugr_id, ugr.ugr_usg_id, ugr.ugr_pr_id
                        FROM user_group_right as ugr
                        INNER JOIN page_right as pr ON pr.pr_id = ugr.ugr_pr_id
                        INNER JOIN page as pg ON pr.pr_pg_id = pg.pg_id' . $strWhere;
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
    public static function loadUserGroupRight($userGroupId): array
    {
        $wheres = [];
        $wheres[] = "(pg.pg_system = 'N')";
        $wheres[] = "(pg.pg_active = 'Y')";
        $wheres[] = '(pg.pg_deleted_on IS NULL)';
        $wheres[] = "(pr.pr_active = 'Y')";
        $wheres[] = "(pr.pr_default = 'N')";
        $wheres[] = '(pr.pr_deleted_on IS NULL)';
        $subWhere = '(pg.pg_id IN (SELECT ugp_pg_id
                                    FROM user_group_page
                                           WHERE (ugp_deleted_on IS NULL) AND (ugp_usg_id = ' . $userGroupId . ')))';
        $wheres[] = "((pg.pg_default = 'Y') OR " . $subWhere . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = "SELECT pg.pg_id, pg.pg_description as pr_page, pr.pr_id, pr.pr_name, pr.pr_default, ugr.ugr_id, (CASE WHEN (ugr.active IS NULL) THEN 'N' ELSE ugr.active END) AS ugr_active
                FROM page_right AS pr INNER JOIN
                 page AS pg ON pr.pr_pg_id = pg.pg_id INNER JOIN
                 page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                 (SELECT ugr_id, ugr_pr_id, (CASE WHEN (ugr_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                      FROM user_group_right
                      WHERE (ugr_usg_id = " . $userGroupId . ')) AS ugr ON pr.pr_id = ugr.ugr_pr_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugr_active DESC, pr.pr_default, pg.pg_description, pr.pr_name';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'pg_id',
            'pr_page',
            'pr_name',
            'pr_id',
            'ugr_id',
            'pr_default',
            'ugr_active',
        ]);

    }


}
