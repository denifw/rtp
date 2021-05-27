<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\System;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table dashboard_ite,.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class DashboardItemDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dsi_id', 'dsi_title', 'dsi_code', 'dsi_route', 'dsi_path',
        'dsi_height', 'dsi_grid_large', 'dsi_grid_medium', 'dsi_grid_small',
        'dsi_grid_xsmall','dsi_description', 'dsi_color', 'dsi_order', 'dsi_module_id', 'dsi_active', 'dsi_parameter'
    ];

    /**
     * Base dao constructor for dashboard_ite,.
     *
     */
    public function __construct()
    {
        parent::__construct('dashboard_item', 'dsi', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table dashboard_ite,.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dsi_title', 'dsi_code', 'dsi_route', 'dsi_path', 'dsi_description', 'dsi_color', 'dsi_active', 'dsi_parameter'
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
        $wheres = [];
        $wheres[] = '(dsi.dsi_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data that no exist in dashboard user.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function getItemNotExistInUserDashboard(array $wheres): array
    {
        $results = self::loadData($wheres);
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
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dsi.dsi_id, dsi.dsi_title, dsi.dsi_code, dsi.dsi_route, dsi.dsi_path,
                         dsi.dsi_description, dsi.dsi_order, dsi.dsi_color, dsi.dsi_grid_large, dsi.dsi_grid_medium,  
                         dsi.dsi_grid_small, dsi.dsi_grid_xsmall, dsi.dsi_height, 
                         dsi.dsi_module_id, dsi.dsi_active, dsi.dsi_parameter, sty.sty_name as dsi_module_name
                  FROM dashboard_item AS dsi LEFT OUTER JOIN
                       system_type AS sty ON sty.sty_id = dsi.dsi_module_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
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
        $query = 'SELECT count(DISTINCT (dsi.dsi_id)) AS total_rows
                  FROM dashboard_item AS dsi LEFT OUTER JOIN
                       system_type AS sty ON sty.sty_id = dsi.dsi_module_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function loadUserGroupDashboard($userGroupId): array
    {
        $wheres = [];
        $wheres[] = '(dsi.dsi_active = \'Y\')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = "SELECT dsi.dsi_id, dsi.dsi_title, dsi.dsi_code, dsi.dsi_description, dsi.dsi_path, dsi.dsi_parameter,
                         module.sty_id AS dsi_module_id, module.sty_name AS dsi_module_name, ugds.ugds_id,
                         (CASE WHEN (ugds.active IS NULL) THEN 'N' ELSE ugds.active END) AS ugds_active
                  FROM dashboard_item AS dsi 
                       LEFT OUTER JOIN (SELECT ugds_id, ugds_dsi_id, (CASE WHEN (ugds_deleted_on IS NULL) THEN 'Y' ELSE 'N' END) AS active
                          FROM user_group_dashboard_item 
                          WHERE (ugds_usg_id = " . $userGroupId . ')) AS ugds ON ugds.ugds_dsi_id = dsi.dsi_id
                       LEFT OUTER JOIN system_type AS module ON module.sty_id = dsi.dsi_module_id' . $strWhere;
        # Set Where condition.
        $query .= ' ORDER BY ugds_active DESC, module.sty_name, dsi.dsi_order';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
