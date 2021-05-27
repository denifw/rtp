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
 * Class to handle data access object for table user_group.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'usg_id',
        'usg_ss_id',
        'usg_name',
        'usg_active',
    ];

    /**
     * Base dao constructor for user_group.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group', 'usg', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'usg_name',
            'usg_active',
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
        $query = 'SELECT usg.usg_id, usg.usg_ss_id, usg.usg_name, usg.usg_active, ss.ss_relation as usg_system_setting
                        FROM user_group as usg LEFT OUTER JOIN
                        system_setting as ss ON usg.usg_ss_id = ss.ss_id
                        WHERE (usg.usg_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], self::$Fields);
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
    public static function loadAllData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT usg_id, usg_ss_id, usg_name, usg_active
                        FROM user_group' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);
    }


    /**
     * Function to get all record.
     *
     * @param int $ssId To store the user id.
     * @param int $umpId To store the user id.
     *
     * @return array
     */
    public static function loadApiAccess($ssId, $umpId): array
    {
        $usgWhere = '(aa_id IN (SELECT uga.uga_aa_id
                                    FROM user_group_api_access AS uga INNER JOIN
                                        (SELECT ug.usg_id
                                        FROM user_group_detail as ugd INNER JOIN
                                            user_group as ug ON ug.usg_id = ugd.ugd_usg_id
                                        WHERE (ugd.ugd_deleted_on IS NULL) AND ((ug.usg_ss_id = ' . $ssId . ') OR (ug.usg_ss_id IS NULL))
                                            AND (ugd.ugd_ump_id = ' . $umpId . ") AND (ug.usg_deleted_on IS NULL) AND (ug.usg_active = 'Y') 
                                            GROUP BY ug.usg_id) AS usg ON usg.usg_id = uga.uga_usg_id
                                    WHERE (uga.uga_deleted_on IS NULL)))";
        $wheres = [];
        $wheres[] = "((aa_default = 'Y') OR " . $usgWhere . ')';
        $wheres[] = '(aa_deleted_on IS NULL)';
        $wheres[] = "(aa_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT aa_id, aa_name
				FROM api_access ' . $strWhere;
        $query .= ' ORDER BY aa_name, aa_id';
        $sqlResult = DB::select($query);
        $results = [];
        $temp = DataParser::arrayObjectToArray($sqlResult);
        foreach ($temp as $row) {
            $results[] = $row['aa_name'];
        }
        return $results;
    }
}
