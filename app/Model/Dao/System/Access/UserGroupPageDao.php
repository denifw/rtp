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
use App\Frame\Mvc\AbstractBaseDao;
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
}
