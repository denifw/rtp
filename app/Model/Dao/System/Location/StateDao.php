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
 * Class to handle data access object for table state.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class StateDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'stt_id',
        'stt_cnt_id',
        'stt_name',
        'stt_iso',
        'stt_active',
    ];

    /**
     * Base dao constructor for state.
     *
     */
    public function __construct()
    {
        parent::__construct('state', 'stt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table state.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'stt_name',
            'stt_iso',
            'stt_active',
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
        $query = 'SELECT stt.stt_id, stt.stt_name, stt.stt_iso, stt.stt_cnt_id, cnt.cnt_name as stt_country, stt.stt_active
                        FROM state as stt INNER JOIN
                        country as cnt ON stt.stt_cnt_id = cnt.cnt_id
                        WHERE (stt.stt_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], array_merge(self::$Fields, ['stt_country']));
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
        $query = 'SELECT stt.stt_id, stt.stt_name, stt.stt_iso, stt.stt_cnt_id, cnt.cnt_name as stt_country, stt.stt_active
                        FROM state as stt INNER JOIN
                        country as cnt ON stt.stt_cnt_id = cnt.cnt_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY cnt.cnt_name, stt.stt_name, stt.stt_id';
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
        $query = 'SELECT count(DISTINCT (stt.stt_id)) AS total_rows
                   FROM state as stt INNER JOIN
                        country as cnt ON stt.stt_cnt_id = cnt.cnt_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


}
