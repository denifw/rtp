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

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Mvc\AbstractBaseDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table users.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserLogDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ul_id',
        'ul_media',
        'ul_route',
        'ul_action',
        'ul_ref_id',
        'ul_token',
        'ul_param',
    ];

    /**
     * Base dao constructor for users.
     *
     */
    public function __construct()
    {
        parent::__construct('user_log', 'ul', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table users.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ul_action',
            'ul_param',
            'ul_token',
            'ul_route',
            'ul_media'
        ]);
    }

    /**
     * Abstract function to load the seeder query for table users.
     *
     * 
     * @param string $route To Store the action route.
     * @param string $action To Store the action log.
     * @param string $token To store the action token.
     * 
     * @return bool
     */
    public function isLogRegistered($usId, $token): bool
    {
        $results = false;
        // $wheres = [];
        // $wheres[] = "(ul_route = '" . $route . "')";
        // $wheres[] = "(ul_action = '" . $action . "')";
        // $wheres[] = "(ul_token = '" . $token . "')";
        // $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        // $query = 'SELECT ul_id 
        //             FROM user_log ' . $strWhere;
        // $sqlResults = DB::select($query);
        // if (empty($sqlResults) === false) {
        //     $results = true;
        // }
        return $results;
    }

    /**
     * Abstract function to load the seeder query for table users.
     *
     * 
     * @param string $route To Store the action route.
     * @param int $refId To Store the action route.
     * 
     * 
     * @return array
     */
    public static function loadDataByPage($route, $refId): array
    {
        $results = [];
        $action = $route;
        $wheres = [];
        $wheres[] = "(ul_route = '" . $action . "')";
        $wheres[] = '(ul_ref_id = ' . $refId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ul.ul_id, ul.ul_route, ul.ul_action, ul.ul_param, ul.ul_ref_id, 
                        us.us_name as ul_created_by, ul.ul_created_on, ul.ul_media
                    FROM user_log as ul INNER JOIN
                    users as us ON us.us_id = ul.ul_created_by' . $strWhere;
        $query .= ' ORDER BY ul.ul_created_on DESC, ul.ul_id';
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                $row['ul_data'] = $row['ul_param'];
                $row['ul_created_on'] = DateTimeParser::format($row['ul_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                $results[] = $row;
            }
        }

        return $results;
    }
}
