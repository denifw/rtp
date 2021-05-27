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
 * Class to handle data access object for table dashboard.
 *
 * @package    app
 * @subpackage Model\Dao\Setting
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class DashboardDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dsh_id', 'dsh_ss_id', 'dsh_us_id', 'dsh_name', 'dsh_description', 'dsh_order'
    ];

    /**
     * Base dao constructor for dashboard.
     *
     */
    public function __construct()
    {
        parent::__construct('dashboard', 'dsh', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table dashboard.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dsh_name', 'dsh_description'
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
     * @param int $referenceValue     To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(dsh.dsh_id = ' . $referenceValue . ')';
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
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(dsh.dsh_id = ' . $referenceValue . ')';
        $wheres[] = '(dsh.dsh_ss_id = ' . $systemSettingValue . ')';
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
        $wheres[] = '(dsh.dsh_id = ' . $referenceValue . ')';
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
        $query = 'SELECT dsh.dsh_id, dsh.dsh_ss_id, dsh.dsh_name, dsh.dsh_description, dsh.dsh_order
                  FROM   dashboard AS dsh' . $strWhere;
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
