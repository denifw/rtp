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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_mapping.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserMappingDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ump_id',
        'ump_us_id',
        'ump_ss_id',
        'ump_rel_id',
        'ump_cp_id',
        'ump_confirm',
        'ump_default',
        'ump_active',
    ];

    /**
     * Base dao constructor for user_mapping.
     *
     */
    public function __construct()
    {
        parent::__construct('user_mapping', 'ump', self::$Fields);
    }

    /**
     * function to get all available fields
     *
     * @param string $userId To Store the user data.
     * @param string $ssId To Store the id of the system setting.
     *
     * @return array
     */
    public static function loadUserMappingData(string $userId, string $ssId = ''): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_us_id', $userId);
        if (empty($ssId) === false) {
            $wheres[] = SqlHelper::generateStringCondition('ump.ump_ss_id', $ssId);
        }
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_confirm', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('rel.rel_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('rel.rel_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ofc.of_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ofc.of_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('cp.cp_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('cp.cp_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ss.ss_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ss.ss_deleted_on');

        $data = self::loadData($wheres, [], 1);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * function to get all available fields
     *
     * @param string $ssId To Store the id of the system setting.
     *
     * @return array
     */
    public static function loadSystemMappingData(string $ssId = ''): array
    {
        $result = [];
        $wheres = [];
        if (empty($ssId) === false) {
            $wheres[] = SqlHelper::generateStringCondition('ss.ss_ss_id', $ssId);
        } else {
            $wheres[] = SqlHelper::generateStringCondition('ss.ss_system', 'Y');
        }
        $wheres[] = SqlHelper::generateStringCondition('rel.rel_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('rel.rel_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ss.ss_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ss.ss_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ss.ss_id, lg.lg_locale as ss_lg_locale, lg.lg_iso as ss_lg_iso, ss.ss_decimal_number,
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, ss.ss_cur_id, cur.cur_iso as ss_currency_iso, cur.cur_name as ss_currency,
                          ss.ss_rel_id, ss.ss_relation
					FROM system_setting as ss
					    INNER JOIN relation as rel ON rel.rel_ss_id = ss.ss_id
					    INNER JOIN languages as lg ON ss.ss_lg_id = lg.lg_id
					    INNER JOIN currency as cur ON ss.ss_cur_id = cur.cur_id ' . $strWhere;
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::objectToArray($sqlResult[0]);
        }

        return $result;
    }

    /**
     * function to get all available fields
     *
     * @param string $userId To Store the user data.
     * @param string $ssId To Store the user data.
     *
     * @return array
     */
    public static function loadAllUserMappingData(string $userId, string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_ss_id', $ssId, '<>');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_us_id', $userId);

        $wheres[] = SqlHelper::generateStringCondition('ump.ump_confirm', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ss.ss_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ss.ss_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * function to get all available fields
     *
     * @param string $ssId To Store the user data.
     *
     * @return array
     */
    public static function loadAllUserMappingDataForSystem(string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ss_id', $ssId, '<>');
        $wheres[] = SqlHelper::generateStringCondition('ss_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ss_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ss_id, ss_logo, ss_relation, ss_system
					FROM system_setting ' . $strWhere;
        $query .= ' GROUP BY ss_id, ss_logo, ss_relation, ss_system';
        $query .= ' ORDER BY ss_system DESC, ss_relation, ss_id';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            return DataParser::arrayObjectToArray($sqlResult);
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_id', $referenceValue);
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * Function to get all record by user id.
     *
     * @param string $userId To store the id of the user.
     * @param string $ssId To store the id of the system.
     *
     * @return array
     */
    public static function getByUserIdAndSystemId(string $userId, string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_ss_id', $ssId);
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_us_id', $userId);

        $wheres[] = SqlHelper::generateStringCondition('ump.ump_confirm', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('ump.ump_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ump.ump_deleted_on');
        $result = self::loadData($wheres);
        if (count($result) === 1) {
            return $result[0];
        }
        return [];

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ump.ump_id, ss.ss_id , lg.lg_locale as ss_lg_locale, lg.lg_iso as ss_lg_iso, ss.ss_decimal_number,
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, cp.cp_id, cp.cp_name, ofc.of_id, ofc.of_name,
                          ss.ss_cur_id, cur.cur_iso as ss_currency_iso, cur.cur_name as ss_currency, ss.ss_relation,
                            ss.ss_rel_id
					FROM user_mapping as ump
					    INNER JOIN system_setting as ss ON ump.ump_ss_id = ss.ss_id
					    INNER JOIN relation as rel ON ump.ump_rel_id = rel.rel_id
					    INNER JOIN contact_person as cp ON ump.ump_cp_id = cp.cp_id
					    INNER JOIN office as ofc ON  cp.cp_of_id = ofc.of_id
					    INNER JOIN languages as lg ON ss.ss_lg_id = lg.lg_id
					    INNER JOIN currency as cur ON ss.ss_cur_id = cur.cur_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY ump.ump_default DESC, ump.ump_created_on, ump.ump_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
