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
use Illuminate\Support\Facades\Hash;

/**
 * Class to handle data access object for table users.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UsersDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'us_id',
        'us_name',
        'us_username',
        'us_password',
        'us_system',
        'us_picture',
        'us_lg_id',
        'us_menu_style',
        'us_confirm',
        'us_active',
    ];

    /**
     * Base dao constructor for users.
     *
     */
    public function __construct()
    {
        parent::__construct('users', 'us', self::$Fields);
    }

    /**
     * Function to get the user by email.
     *
     * @param string $username To set the username of the user.
     *
     * @return array
     */
    public static function getByUsername(string $username): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('us.us_username', $username);
        $wheres[] = SqlHelper::generateStringCondition('us.us_confirm', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('us.us_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('us.us_deleted_on');
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }


    /**
     * Function to get all the data for the login information.
     *
     * @param string $email To set the email of the user.
     * @param string $password To set the password of the user.
     *
     * @return array
     */
    public function getLoginData(string $email, string $password): array
    {
        $data = self::getByUsername($email);
        if (empty($data) === false && Hash::check($password, $data['us_password']) === true) {
            return $data;
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
        $wheres[] = SqlHelper::generateStringCondition('us.us_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
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
        $query = 'SELECT us.us_id, us.us_name, us.us_username, us.us_password, us.us_system, us.us_picture,
                        us.us_lg_id, us.us_menu_style, lg.lg_locale as us_language, lg.lg_iso as us_lg_iso,
                        us.us_confirm, us.us_active
					FROM users as us
					    INNER JOIN languages as lg ON us.us_lg_id = lg.lg_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY us.us_created_on, us.us_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (us.us_id)) AS total_rows
                   FROM users as us
					    INNER JOIN languages as lg ON us.us_lg_id = lg.lg_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'us_id');
    }

}
