<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\User;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group_dashboard_item.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class UserGroupDashboardItemDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugds_id', 'ugds_dsi_id', 'ugds_usg_id'
    ];

    /**
     * Base dao constructor for user_group_dashboard_item.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_dashboard_item', 'ugds', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table user_group_dashboard_item.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
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
        $wheres[] = '(ugds.ugds_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(ugds_active = 'Y')";
        $where[] = '(ugds_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all data based on user group id.
     *
     * @param int $userGroupId To store the id of user group.
     *
     * @return array
     */
    public static function getByUserGroup(int $userGroupId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ugds.ugds_usg_id', $userGroupId);
        $wheres[] = SqlHelper::generateNullCondition('ugds.ugds_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('dsi.dsi_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('dsi.dsi_active', 'Y');
        return self::loadData($wheres);
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
        $query = 'SELECT ugds.ugds_id, ugds.ugds_dsi_id, ugds.ugds_usg_id,
                         usg.usg_name AS ugds_usg_name,
                         dsi.dsi_id, dsi.dsi_title, dsi.dsi_code, dsi.dsi_order,
                         dsi.dsi_grid_large, dsi.dsi_grid_medium, dsi.dsi_grid_small, dsi.dsi_grid_xsmall,
                         dsi.dsi_height, dsi.dsi_color, dsi.dsi_parameter,
                         sty.sty_id AS ugds_module_id, sty.sty_name AS ugds_module_name
                  FROM   user_group_dashboard_item AS ugds INNER JOIN
                         dashboard_item AS dsi ON dsi.dsi_id = ugds.ugds_dsi_id INNER JOIN
                         user_group AS usg ON usg.usg_id = ugds.ugds_usg_id INNER JOIN
                         system_type AS sty ON sty.sty_id = dsi.dsi_module_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
