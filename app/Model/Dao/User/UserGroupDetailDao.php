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
     * Abstract function to load the seeder query for table user_group_detail.
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
        $wheres = [];
        $wheres[] = '(ugd.ugd_id = ' . $referenceValue . ')';
        $data = self::loadAllData($wheres);
        if (empty($data) === false) {
            return $data[0];
        }

        return [];
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
        $query = 'SELECT ugd.ugd_id, ugd.ugd_usg_id, usg.usg_name as ugd_group, ugd.ugd_ump_id 
                        FROM user_group_detail as ugd INNER JOIN
                        user_group as usg ON ugd.ugd_usg_id = usg.usg_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, ['ugd_group']));

    }


}
