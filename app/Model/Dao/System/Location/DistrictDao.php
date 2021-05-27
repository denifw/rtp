<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Location;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table district.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class DistrictDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dtc_id',
        'dtc_cnt_id',
        'dtc_stt_id',
        'dtc_cty_id',
        'dtc_name',
        'dtc_iso',
        'dtc_active',
    ];

    /**
     * Base dao constructor for district.
     *
     */
    public function __construct()
    {
        parent::__construct('district', 'dtc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table district.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dtc_name',
            'dtc_iso',
            'dtc_active',
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
        $wheres[] = '(dtc.dtc_id = ' . $referenceValue . ')';
        $data = self::loadData(0, $wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId To store the system settings id.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, int $ssId): array
    {
        $wheres = [];
        $wheres[] = '(dtc.dtc_id = ' . $referenceValue . ')';
        $data = self::loadData($ssId, $wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all record.
     *
     * @param int   $ssId    To store the system setting id.
     * @param array $wheres  To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int   $limit   To store the limit of the data.
     * @param int   $offset  To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData($ssId, array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dtc.dtc_id, dtc.dtc_cnt_id, cnt.cnt_name as dtc_country, dtc.dtc_stt_id, stt.stt_name as dtc_state,
                        dtc.dtc_cty_id, cty.cty_name as dtc_city, dtc.dtc_name, dtc.dtc_iso, dtc.dtc_active, 
                        dtcc.dtcc_id, dtcc.dtcc_code
                    FROM district as dtc INNER JOIN
                        city as cty ON dtc.dtc_cty_id = cty.cty_id INNER JOIN
                        state as stt ON dtc.dtc_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON dtc.dtc_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                        (SELECT dtcc_id, dtcc_code, dtcc_dtc_id
                            FROM district_code
                            WHERE (dtcc_ss_id = ' . $ssId . ')) as dtcc ON dtc.dtc_id = dtcc.dtcc_dtc_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY cnt.cnt_name, stt.stt_name, cty.cty_name, dtc.dtc_name, dtc.dtc_id';
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
     * @param int $ssId To store the system settings ID.
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData($ssId, array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (dtc.dtc_id)) AS total_rows
                   FROM district as dtc INNER JOIN
                        city as cty ON dtc.dtc_cty_id = cty.cty_id INNER JOIN
                        state as stt ON dtc.dtc_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON dtc.dtc_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                        (SELECT dtcc_id, dtcc_code, dtcc_dtc_id
                            FROM district_code
                            WHERE (dtcc_ss_id = ' . $ssId . ')) as dtcc ON dtc.dtc_id = dtcc.dtcc_dtc_id' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


}
