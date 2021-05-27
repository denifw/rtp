<?php

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\System;

use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to load all the settings of the system.
 *
 * @package    app
 * @subpackage Frame\System
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class SystemSettings
{


    /**
     * Property to store the user data.
     *
     * @var array
     */
    private $User = [];

    /**
     * Property to store the settings system
     *
     * @var array
     */
    private $Settings = [];

    /**
     * Function to load the relation settings.
     *
     * @param array $user to Store the user data.
     *
     * @return void
     */
    public function registerSystemSetting(array $user): void
    {
        $this->User = $user;
        if (session()->exists('user') === false) {
            session()->put('user', $user);
        }
        if (session()->exists('app_set') === false) {
            $this->Settings['menus'] = $this->loadMenu();
            $this->Settings['pages'] = $this->loadPage();
            $this->Settings['pageRights'] = $this->loadPageRight();
            session()->put('app_set', $this->Settings);
        }
    }


    /**
     * Function to load the settings.
     *
     * @param string $key To store the key session of the data.
     *
     * @return null|array
     */
    public static function loadSettings(string $key): ?array
    {
        $result = null;
        if (session()->exists('app_set') === true) {
            $settings = session('app_set');
            if (array_key_exists($key, $settings) === true) {
                $result = $settings[$key];
            }
        }

        return $result;
    }

    /**
     * Function to load menu from database.
     *
     * @return array
     */
    private function loadMenu(): array
    {
        $wheres = [];
        $wheres[] = '(mn_deleted_on IS NULL)';
        $wheres[] = "(mn_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT mn_id, mn_name, mn_parent, mn_icon, mn_order
				FROM menu ' . $strWhere;
        $query .= ' ORDER BY mn_parent, mn_order';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, [
                'mn_id',
                'mn_name',
                'mn_parent',
                'mn_icon',
                'mn_order'
            ]);
        }

        return $result;
    }

    /**
     * Function to load the page.
     *
     * @return array
     */
    private function loadPage(): array
    {
        $wheres = [];
        if ($this->User['ss_system'] !== 'Y' || $this->User['us_system'] !== 'Y') {
            $wheres[] = "(pg.pg_system = 'N')";
        }
        if ($this->User['us_system'] !== 'Y') {
            $subWhere = '(pg.pg_id IN (SELECT ugp.ugp_pg_id
                                FROM user_group_page AS ugp INNER JOIN
                                     (SELECT ug.usg_id
                                      FROM user_group_detail as ugd INNER JOIN
                                        user_group as ug ON ug.usg_id = ugd.ugd_usg_id
                                      WHERE (ugd.ugd_deleted_on IS NULL) AND ((ug.usg_ss_id = ' . $this->User['ss_id'] . ') OR (ug.usg_ss_id IS NULL))
                                        AND (ugd.ugd_ump_id = ' . $this->User['ump_id'] . ") AND (ug.usg_deleted_on IS NULL) AND (ug.usg_active = 'Y') 
                                        GROUP BY ug.usg_id) AS usg ON usg.usg_id = ugp.ugp_usg_id
                                WHERE (ugp.ugp_deleted_on IS NULL)))";
            $wheres[] = "((pg.pg_default = 'Y') OR " . $subWhere . ')';
        }
        $wheres[] = '(pg.pg_deleted_on IS NULL)';
        $wheres[] = "(pg.pg_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT pg.pg_id, pg.pg_title, pg.pg_route, pg.pg_icon, pg.pg_description, pg.pg_mn_id, pg.pg_order, 
                      pc.pc_name, pg.pg_default, pg.pg_system, pg.pg_pc_id, pc.pc_route
				FROM page AS pg INNER JOIN 
				page_category AS pc on pg.pg_pc_id = pc.pc_id ' . $strWhere;
        $query .= ' ORDER BY pg.pg_mn_id, pg.pg_order';
        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $results = DataParser::arrayObjectToArray($sqlResult, [
                'pg_id',
                'pg_mn_id',
                'pg_title',
                'pg_description',
                'pg_route',
                'pg_icon',
                'pg_order',
                'pg_default',
                'pg_system',
                'pg_pc_id',
                'pc_name',
                'pc_route',
            ]);
        }

        return $results;
    }

    /**
     * Function to load the page right.
     *
     * @return array
     */
    private function loadPageRight(): array
    {
        $wheres = [];
        $pageIds = $this->loadPageIds();
        $wheres[] = '(pr_pg_id IN (' . implode(',', $pageIds) . '))';
        $wheres[] = "(pr_active = 'Y')";
        $wheres[] = '(pr_deleted_on IS NULL)';
        if ($this->User['us_system'] !== 'Y') {
            $subWhere = '(pr_id IN (SELECT ugr.ugr_pr_id
                                    FROM user_group_right AS ugr INNER JOIN
                                     (SELECT ug.usg_id
                                      FROM user_group_detail as ugd INNER JOIN
                                        user_group as ug ON ug.usg_id = ugd.ugd_usg_id
                                      WHERE (ugd.ugd_deleted_on IS NULL) AND ((ug.usg_ss_id = ' . $this->User['ss_id'] . ') OR (ug.usg_ss_id IS NULL))
                                        AND (ugd.ugd_ump_id = ' . $this->User['ump_id'] . ") AND (ug.usg_deleted_on IS NULL) AND (ug.usg_active = 'Y') 
                                        GROUP BY ug.usg_id) AS usg ON usg.usg_id = ugr.ugr_usg_id
                                    WHERE (ugr.ugr_deleted_on IS NULL)))";
            $wheres[] = "((pr_default = 'Y') OR " . $subWhere . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT pr_id, pr_pg_id, pr_name
                        FROM page_right' . $strWhere;
        $query .= ' GROUP BY pr_id, pr_pg_id, pr_name';
        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $temp = DataParser::arrayObjectToArray($sqlResult, [
                'pr_id',
                'pr_pg_id',
                'pr_name'
            ]);
            foreach ($temp as $row) {
                if (array_key_exists($row['pr_pg_id'], $results) === false) {
                    $results[$row['pr_pg_id']] = [];
                }
                $results[$row['pr_pg_id']][] = $row;
            }
        }

        return $results;
    }


    /**
     * Function to load the page right.
     *
     * @return array
     */
    private function loadPageIds(): array
    {
        $results = [];
        $pages = $this->Settings['pages'];
        foreach ($pages as $page) {
            $results[] = $page['pg_id'];
        }

        return $results;
    }
}
