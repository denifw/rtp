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
     * @param int $userId To Store the user data.
     * @param int $ssId   To Store the id of the system setting.
     *
     * @return array
     */
    public static function loadUserMappingData(int $userId, int $ssId = 0): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(ump.ump_us_id = ' . $userId . ')';
        if (is_numeric($ssId) === true && $ssId !== 0 && $ssId > 0) {
            $wheres[] = '(ss.ss_id = ' . $ssId . ')';
        }
        $wheres[] = "(ump.ump_confirm = 'Y')";
        $wheres[] = "(ump.ump_active = 'Y')";
        $wheres[] = '(ump.ump_deleted_on IS NULL)';
        $wheres[] = "(rel.rel_active = 'Y')";
        $wheres[] = '(rel.rel_deleted_on IS NULL)';
        $wheres[] = "(cp.cp_active = 'Y')";
        $wheres[] = '(cp.cp_deleted_on IS NULL)';
        $wheres[] = "(ofc.of_active = 'Y')";
        $wheres[] = '(ofc.of_deleted_on IS NULL)';
        $wheres[] = "(ss.ss_active = 'Y')";
        $wheres[] = '(ss.ss_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ump.ump_id, ss.ss_id, lg.lg_locale as ss_lg_locale, lg.lg_iso as ss_lg_iso, ss.ss_decimal_number, 
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, cp.cp_id, cp.cp_name, ofc.of_id, ofc.of_name,
                          ss.ss_cur_id, cur.cur_iso as ss_currency_iso, cur.cur_name as ss_currency
					FROM user_mapping as ump INNER JOIN
					  system_setting as ss ON ump.ump_ss_id = ss.ss_id INNER JOIN
					  relation as rel ON ump.ump_rel_id = rel.rel_id INNER JOIN
					   contact_person as cp ON ump.ump_cp_id = cp.cp_id INNER JOIN
					    office as ofc ON  cp.cp_of_id = ofc.of_id INNER JOIN
					     languages as lg ON ss.ss_lg_id = lg.lg_id INNER JOIN
					      currency as cur ON ss.ss_cur_id = cur.cur_id ' . $strWhere;
        $query .= ' GROUP BY ump.ump_id, ss.ss_id, lg.lg_locale, lg.lg_iso, ss.ss_decimal_number, 
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, cp.cp_id, cp.cp_name, ofc.of_id, ofc.of_name,
                          ss.ss_cur_id, cur.cur_iso, cur.cur_name';
        $query .= ' ORDER BY ump.ump_default DESC';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0], [
                'ump_id',
                'ss_id',
                'ss_lg_locale',
                'ss_lg_iso',
                'ss_decimal_number',
                'ss_decimal_separator',
                'ss_thousand_separator',
                'ss_logo',
                'ss_name_space',
                'ss_system',
                'rel_id',
                'rel_name',
                'rel_short_name',
                'cp_id',
                'cp_name',
                'of_id',
                'of_name',
                'ss_cur_id',
                'ss_currency_iso',
                'ss_currency',
            ]);
        }

        return $result;
    }

    /**
     * function to get all available fields
     *
     * @param int $ssId To Store the id of the system setting.
     *
     * @return array
     */
    public static function loadSystemMappingData(int $ssId = 0): array
    {
        $result = [];
        $wheres = [];
        if (is_numeric($ssId) === true && $ssId !== 0 && $ssId > 0) {
            $wheres[] = '(ss.ss_id = ' . $ssId . ')';
        } else {
            $wheres[] = "(ss.ss_system = 'Y')";
        }
        $wheres[] = "(rel.rel_active = 'Y')";
        $wheres[] = '(rel.rel_deleted_on IS NULL)';
        $wheres[] = "(ss.ss_active = 'Y')";
        $wheres[] = '(ss.ss_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ss.ss_id, lg.lg_locale as ss_lg_locale, lg.lg_iso as ss_lg_iso, ss.ss_decimal_number, 
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, ss.ss_cur_id, cur.cur_iso as ss_currency_iso, cur.cur_name as ss_currency
					FROM system_setting as ss INNER JOIN
					  relation as rel ON rel.rel_ss_id = ss.ss_id  INNER JOIN
					     languages as lg ON ss.ss_lg_id = lg.lg_id INNER JOIN
					      currency as cur ON ss.ss_cur_id = cur.cur_id ' . $strWhere;
        $query .= ' GROUP BY ss.ss_id, lg.lg_locale, lg.lg_iso, ss.ss_decimal_number, 
                          ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo, ss.ss_name_space, ss.ss_system,
                          rel.rel_id, rel.rel_name, rel.rel_short_name, ss.ss_cur_id, cur.cur_iso, cur.cur_name';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::objectToArray($sqlResult[0], [
                'ump_id',
                'ss_id',
                'ss_lg_locale',
                'ss_lg_iso',
                'ss_decimal_number',
                'ss_decimal_separator',
                'ss_thousand_separator',
                'ss_logo',
                'ss_name_space',
                'ss_system',
                'rel_id',
                'rel_name',
                'rel_short_name',
                'cp_id',
                'cp_name',
                'of_id',
                'of_name',
                'ss_cur_id',
                'ss_currency_iso',
                'ss_currency',
            ]);
        }

        return $result;
    }

    /**
     * function to get all available fields
     *
     * @param int $userId To Store the user data.
     * @param int $ssId   To Store the user data.
     *
     * @return array
     */
    public static function loadAllUserMappingData(int $userId, $ssId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(ump.ump_ss_id <> ' . $ssId . ')';
        $wheres[] = '(ump.ump_us_id = ' . $userId . ')';
        $wheres[] = "(ump.ump_confirm = 'Y')";
        $wheres[] = "(ump.ump_active = 'Y')";
        $wheres[] = '(ump.ump_deleted_on IS NULL)';
        $wheres[] = "(ss.ss_active = 'Y')";
        $wheres[] = '(ss.ss_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ump.ump_id, ump.ump_us_id, ss.ss_id, ss.ss_logo, ss.ss_relation, ss.ss_system
					FROM user_mapping as ump INNER JOIN
					  system_setting as ss ON ump.ump_ss_id = ss.ss_id ' . $strWhere;
        $query .= ' GROUP BY ump.ump_id, ump.ump_us_id, ss.ss_id, ss.ss_logo, ss.ss_relation, ss.ss_system';
        $query .= ' ORDER BY ss.ss_system, ss.ss_relation';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, [
                'ump_id',
                'ump_us_id',
                'ss_id',
                'ss_logo',
                'ss_relation',
                'ss_system',
            ]);
        }

        return $result;
    }

    /**
     * function to get all available fields
     *
     * @param int $ssId To Store the user data.
     *
     * @return array
     */
    public static function loadAllUserMappingDataForSystem($ssId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(ss_id <> ' . $ssId . ')';
        $wheres[] = "(ss_active = 'Y')";
        $wheres[] = '(ss_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT  ss_id, ss_logo, ss_relation, ss_system
					FROM system_setting ' . $strWhere;
        $query .= ' GROUP BY ss_id, ss_logo, ss_relation, ss_system';
        $query .= ' ORDER BY ss_system, ss_relation';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, [
                'ss_id',
                'ss_logo',
                'ss_relation',
                'ss_system',
            ]);
        }

        return $result;
    }


    /**
     * Abstract function to load the seeder query for table user_mapping.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ump_confirm',
            'ump_default',
            'ump_active',
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
        $results = self::loadData(['(ump.ump_id = ' . $referenceValue . ')']);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
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
        $query = 'SELECT  ump.ump_id, ump.ump_ss_id, ss.ss_relation as ump_system, ump.ump_rel_id, rel.rel_name as ump_relation, 
                ump.ump_cp_id, cp.cp_name as ump_contact, ump.ump_confirm, cp.cp_of_id, o.of_name as ump_office,
                ump.ump_default, ump.ump_active, ump.ump_us_id, us.us_name as ump_user
					FROM user_mapping as ump INNER JOIN
					  system_setting as ss ON ump.ump_ss_id = ss.ss_id INNER JOIN
					  relation as rel ON ump.ump_rel_id = rel.rel_id INNER JOIN
					   contact_person as cp ON ump.ump_cp_id = cp.cp_id INNER JOIN
					   office as o ON cp.cp_of_id = o.of_id INNER JOIN 
					   users as us ON ump.ump_us_id = us.us_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, [
            'ump_id',
            'ump_ss_id',
            'ump_system',
            'ump_rel_id',
            'ump_relation',
            'ump_cp_id',
            'ump_contact',
            'ump_confirm',
            'cp_of_id',
            'ump_office',
            'ump_default',
            'ump_us_id',
            'ump_user',
            'ump_active',
        ]);

    }

    /**
     * Function to get all record by user id.
     *
     * @param int $userId   To store the id of the user.
     * @param int $systemId To store the id of the system.
     *
     * @return array
     */
    public static function getByUserIdAndSystemId($userId, $systemId): array
    {
        $wheres = [];
        $wheres[] = '(ump_us_id = ' . $userId . ')';
        $wheres[] = '(ump_ss_id = ' . $systemId . ')';
        $result = self::loadData($wheres);
        if (\count($result) === 1) {
            return $result[0];
        }

        return [];

    }


}
