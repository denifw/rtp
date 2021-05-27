<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\System\Location;

use App\Frame\Formatter\DataParser;
use App\Frame\Mvc\AbstractBaseDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table district_code.
 *
 * @package    app
 * @subpackage Model\Dao\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DistrictCodeDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dtcc_id',
        'dtcc_ss_id',
        'dtcc_dtc_id',
        'dtcc_code',
        'dtcc_deleted_reason',
    ];

    /**
     * Base dao constructor for district_code.
     *
     */
    public function __construct()
    {
        parent::__construct('district_code', 'dtcc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table district_code.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dtcc_code',
            'dtcc_deleted_reason',
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
     * @param int $ssId  To store the system setting value.
     * @param int $dtcId To store the reference value of district.
     *
     * @return array
     */
    public static function getBySystemAndDistrictId($ssId, $dtcId): array
    {
        $wheres = [];
        $wheres[] = '(dtcc_dtc_id = ' . $dtcId . ')';
        $wheres[] = '(dtcc_ss_id = ' . $ssId . ')';
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
        $query = 'SELECT dtcc_id, dtcc_dtc_id, dtcc_ss_id, dtcc_code
                    FROM district_code ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


}
