<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
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
     * Function to get all record.
     *
     * @param string $usgId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupFormData(string $usgId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('aa.aa_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('aa.aa_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        # Set Select query;
        $query = "SELECT aa.aa_id, aa.aa_name, aa.aa_description, aa.aa_default, uga.uga_id, (CASE WHEN (uga.active IS NULL) THEN 'N' ELSE uga.active END) AS uga_active
                FROM api_access AS aa LEFT OUTER JOIN
                     (SELECT uga_id, uga_aa_id, (CASE WHEN (uga_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_api_access
                          WHERE " . SqlHelper::generateStringCondition('uga_usg_id', $usgId) . ') AS uga ON aa.aa_id = uga.uga_aa_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY uga_active DESC, aa.aa_default, aa.aa_name, aa.aa_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
