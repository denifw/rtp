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
 * Class to handle data access object for table user_group_detail.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugd_id',
        'ugd_usg_id',
        'ugd_ump_id',
    ];

    /**
     * Base dao constructor for user_group_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_detail', 'ugd', self::$Fields);
    }

    /**
     * Function to get all data based on user group id.
     *
     * @param string $usgId To store the id of user group.
     * @param ?string $ssId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupFormData(string $usgId, ?string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');
        if (empty($ssId) === false) {
            $wheres[] = SqlHelper::generateStringCondition('ump.ump_ss_id', $ssId);
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
                          WHERE " . SqlHelper::generateStringCondition('ugd_usg_id', $usgId) . ') AS ugd ON ump.ump_id = ugd.ugd_ump_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugd_active DESC, ss.ss_relation, rel.rel_name, us.us_name, ump.ump_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
