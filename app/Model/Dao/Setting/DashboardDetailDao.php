<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Setting;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table dashboard_item.
 *
 * @package    app
 * @subpackage Model\Dao\Setting
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class DashboardDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dsd_id', 'dsd_dsh_id', 'dsd_dsi_id', 'dsd_title', 'dsd_height', 'dsd_grid_large',
        'dsd_grid_medium', 'dsd_grid_small', 'dsd_grid_xsmall', 'dsd_order', 'dsd_color', 'dsd_parameter'
    ];

    /**
     * Base dao constructor for dashboard_item.
     *
     */
    public function __construct()
    {
        parent::__construct('dashboard_detail', 'dsd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table dashboard_item.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dsd_title', 'dsd_color', 'dsd_parameter'
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
        $wheres[] = '(dsd.dsd_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     * @param int $userId To store the id user.
     *
     * @return array
     */
    public static function getByReferenceAndSystemAndUser($referenceValue, $systemSettingValue, $userId): array
    {
        $wheres = [];
        $wheres[] = '(dsd.dsd_id = ' . $referenceValue . ')';
        $wheres[] = '(dsh.dsh_ss_id = ' . $systemSettingValue . ')';
        $wheres[] = '(dsh.dsh_us_id = ' . $userId . ')';
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
     * @param array $orderList To store the list for sortir.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dsd.dsd_id, dsd.dsd_dsh_id, dsd.dsd_dsi_id, dsd.dsd_title, dsd.dsd_height, dsd.dsd_grid_large, 
                         dsd.dsd_grid_medium, dsd.dsd_grid_small, dsd.dsd_grid_xsmall, dsd.dsd_order, dsd.dsd_color, dsd.dsd_parameter,
                         dsh.dsh_name, dsi.dsi_title, dsi.dsi_code, dsi.dsi_route, dsi.dsi_path
                  FROM   dashboard_detail AS dsd INNER JOIN
                         dashboard AS dsh ON dsh.dsh_id = dsd.dsd_dsh_id INNER JOIN
                         dashboard_item AS dsi ON dsi.dsi_id = dsd.dsd_dsi_id' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
