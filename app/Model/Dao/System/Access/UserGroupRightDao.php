<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
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
     * Function to get all record.
     *
     * @param string $usgId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupFormData(string $usgId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_system', 'N');
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('pg.pg_deleted_on');

        $wheres[] = SqlHelper::generateStringCondition('pr.pr_active', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('pr.pr_default', 'N');
        $wheres[] = SqlHelper::generateNullCondition('pr.pr_deleted_on');
        $subWhere = '(pg.pg_id IN (SELECT ugp_pg_id
                                    FROM user_group_page
                                           WHERE ' . SqlHelper::generateNullCondition('ugp_deleted_on') .
            ' AND ' . SqlHelper::generateStringCondition('ugp_usg_id', $usgId) . '))';
        $wheres[] = '(' . SqlHelper::generateNullCondition('pg.pg_default') . ' OR ' . $subWhere . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = "SELECT pg.pg_id, pg.pg_description as pr_page, pr.pr_id, pr.pr_name, pr.pr_default, ugr.ugr_id, (CASE WHEN (ugr.active IS NULL) THEN 'N' ELSE ugr.active END) AS ugr_active
                FROM page_right AS pr INNER JOIN
                 page AS pg ON pr.pr_pg_id = pg.pg_id INNER JOIN
                 page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                 (SELECT ugr_id, ugr_pr_id, (CASE WHEN (ugr_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                      FROM user_group_right
                      WHERE " . SqlHelper::generateStringCondition('ugr_usg_id', $usgId) . ') AS ugr ON pr.pr_id = ugr.ugr_pr_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugr_active DESC, pr.pr_default, pg.pg_description, pr.pr_name, pr.pr_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
