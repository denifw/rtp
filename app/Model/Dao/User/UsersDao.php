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
        'us_api_token',
        'us_picture',
        'us_allow_mail',
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
    public function getByUsername($username): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = "(us_username = '" . $username . "')";
        $wheres[] = "(us_confirm = 'Y')";
        $wheres[] = "(us_active = 'Y')";
        $wheres[] = '(us_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us_id, us_name, us_username
					FROM users ' . $strWhere;
        $data = DB::select($query);
        if (\count($data) === 1) {
            $result = DataParser::objectToArray($data[0], [
                'us_id',
                'us_name',
                'us_username'
            ]);
        }

        return $result;
    }


    /**
     * Function to get all the data for the login information.
     *
     * @param string $email    To set the email of the user.
     * @param string $password To set the password of the user.
     *
     * @return array
     */
    public function getLoginData($email, $password): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = "(us.us_username = '" . $email . "')";
        $wheres[] = "(us.us_confirm = 'Y')";
        $wheres[] = "(us.us_active = 'Y')";
        $wheres[] = '(us.us_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us.us_id, us.us_name, us.us_username, us.us_password, us.us_system, us.us_picture, us.us_allow_mail, 
                        us.us_lg_id, us.us_menu_style, lg.lg_locale as us_lg_locale, lg.lg_iso as us_lg_iso, us.us_api_token
					FROM users as us INNER JOIN
					languages as lg ON us.us_lg_id = lg.lg_id ' . $strWhere;
        $query .= ' GROUP BY us.us_id, us.us_name, us.us_username, us.us_password, us.us_system, us.us_picture, us.us_allow_mail, 
                        us.us_lg_id, us.us_menu_style, lg.lg_locale, lg.lg_iso, us.us_api_token';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $arrData = DataParser::objectToArray($sqlResult[0], array_merge(self::$Fields, ['us_lg_locale', 'us_lg_iso']));
            if (Hash::check($password, $arrData['us_password']) === true) {
                $result = [
                    'us_id' => $arrData['us_id'],
                    'us_name' => $arrData['us_name'],
                    'us_username' => $arrData['us_username'],
                    'us_api_token' => $arrData['us_api_token'],
                    'us_picture' => $arrData['us_picture'],
                    'us_system' => $arrData['us_system'],
                    'us_allow_mail' => $arrData['us_allow_mail'],
                    'us_lg_id' => $arrData['us_lg_id'],
                    'us_lg_locale' => $arrData['us_lg_locale'],
                    'us_lg_iso' => $arrData['us_lg_iso'],
                    'us_menu_style' => $arrData['us_menu_style']
                ];
            }
        }

        return $result;
    }


    /**
     * Function to get all the data for the login information.
     *
     * @param string $token To set the email of the user.
     *
     * @return array
     */
    public function getLoginDataByToken($token): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = "(umt.umt_api_token= '" . $token . "')";
        $wheres[] = "(us.us_confirm = 'Y')";
        $wheres[] = "(us.us_active = 'Y')";
        $wheres[] = '(us.us_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us.us_id, us.us_name, us.us_username, us.us_password, us.us_system, us.us_picture, us.us_allow_mail, 
                        us.us_lg_id, us.us_menu_style, lg.lg_locale as us_lg_locale, lg.lg_iso as us_lg_iso, 
                        umt.umt_api_token as us_api_token, umt.umt_deleted_on
					FROM users as us INNER JOIN
					user_mobile_token as umt ON us.us_id = umt.umt_us_id INNER JOIN
					languages as lg ON us.us_lg_id = lg.lg_id ' . $strWhere;
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $arrData = DataParser::objectToArray($sqlResult[0]);
            $result = [
                'us_id' => $arrData['us_id'],
                'us_name' => $arrData['us_name'],
                'us_username' => $arrData['us_username'],
                'us_api_token' => $arrData['us_api_token'],
                'us_picture' => $arrData['us_picture'],
                'us_system' => $arrData['us_system'],
                'us_allow_mail' => $arrData['us_allow_mail'],
                'us_lg_id' => $arrData['us_lg_id'],
                'us_lg_locale' => $arrData['us_lg_locale'],
                'us_lg_iso' => $arrData['us_lg_iso'],
                'us_menu_style' => $arrData['us_menu_style'],
                'umt_deleted_on' => $arrData['umt_deleted_on']
            ];
        }
        return $result;
    }


    /**
     * Abstract function to load the seeder query for table users.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'us_name',
            'us_username',
            'us_password',
            'us_api_token',
            'us_system',
            'us_picture',
            'us_allow_mail',
            'us_menu_style',
            'us_confirm',
            'us_active',
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
        $result = [];
        $wheres = [];
        $wheres[] = "(us.us_active = 'Y')";
        $wheres[] = '(us.us_id = ' . $referenceValue . ')';
        $wheres[] = '(us.us_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us.us_id, us.us_name, us.us_username, us.us_system, us.us_picture, us.us_allow_mail, 
                        us.us_lg_id, us.us_menu_style, lg.lg_locale as us_lg_locale, lg.lg_iso as us_lg_iso
					FROM users as us INNER JOIN
					languages as lg ON us.us_lg_id = lg.lg_id ' . $strWhere;
        $query .= ' GROUP BY us.us_id, us.us_name, us.us_username, us.us_system, us.us_picture, us.us_allow_mail, 
                        us.us_lg_id, us.us_menu_style, lg.lg_locale, lg.lg_iso';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $arrData = DataParser::objectToArray($sqlResult[0], array_merge(self::$Fields, ['us_lg_locale', 'us_lg_iso']));
            $result = [
                'us_id' => $arrData['us_id'],
                'us_name' => $arrData['us_name'],
                'us_username' => $arrData['us_username'],
                'us_picture' => $arrData['us_picture'],
                'us_system' => $arrData['us_system'],
                'us_allow_mail' => $arrData['us_allow_mail'],
                'us_lg_id' => $arrData['us_lg_id'],
                'us_lg_locale' => $arrData['us_lg_locale'],
                'us_lg_iso' => $arrData['us_lg_iso'],
                'us_menu_style' => $arrData['us_menu_style']
            ];
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
        $query = 'SELECT us_id
                        FROM users' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


}
