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
 * Class to handle data access object for table city.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class CityDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'cty_id',
        'cty_cnt_id',
        'cty_stt_id',
        'cty_name',
        'cty_iso',
        'cty_active',
    ];

    /**
     * Base dao constructor for city.
     *
     */
    public function __construct()
    {
        parent::__construct('city', 'cty', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table city.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'cty_name',
            'cty_iso',
            'cty_active',
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
        $query = 'SELECT cty.cty_id, cty.cty_name, cty.cty_iso, cty.cty_active, cty.cty_cnt_id, cnt.cnt_name as cty_country,
                          cty.cty_stt_id, stt.stt_name as cty_state
                        FROM city AS cty INNER JOIN
                        state as stt ON cty.cty_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON cty.cty_cnt_id = cnt.cnt_id
                        WHERE (cty_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], array_merge(self::$Fields, [
                'cty_country',
                'cty_state',
            ]));
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres  To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int   $limit   To store the limit of the data.
     * @param int   $offset  To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT cty.cty_id, cty.cty_name, cty.cty_iso, cty.cty_active, cty.cty_cnt_id, cnt.cnt_name as cty_country,
                          cty.cty_stt_id, stt.stt_name as cty_state
                        FROM city AS cty INNER JOIN
                        state as stt ON cty.cty_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON cty.cty_cnt_id = cnt.cnt_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY cnt.cnt_name, stt.stt_name, cty.cty_name, cty.cty_id';
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
        $query = 'SELECT count(DISTINCT (cty.cty_id)) AS total_rows
                   FROM city AS cty INNER JOIN
                        state as stt ON cty.cty_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON cty.cty_cnt_id = cnt.cnt_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


}
