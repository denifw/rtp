<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table system_setting.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class SystemSettingDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ss_id',
        'ss_relation',
        'ss_decimal_number',
        'ss_decimal_separator',
        'ss_thousand_separator',
        'ss_lg_id',
        'ss_cur_id',
        'ss_logo_id',
        'ss_name_space',
        'ss_system',
        'ss_active',
        'ss_icon_id',
        'ss_rel_id',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'ss_decimal_number',
    ];

    /**
     * Base dao constructor for system_setting.
     *
     */
    public function __construct()
    {
        parent::__construct('system_setting', 'ss', self::$Fields);
    }


    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ss_id', $referenceValue);
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
     * @param array $orderBy To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ss.ss_id, ss.ss_relation, ss.ss_lg_id, lg.lg_locale as ss_language, ss.ss_cur_id, cur.cur_iso as ss_currency,
                        ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator, ss.ss_logo_id, ss.ss_icon_id,
                        ss.ss_name_space, ss.ss_api_key, ss.ss_rel_id, ss.ss_rel_id, ss.ss_system, ss.ss_active
                    FROM system_setting as ss
                        INNER JOIN languages as lg ON ss.ss_lg_id = lg.lg_id
                        INNER JOIN currency as cur ON ss.ss_cur_id = cur.cur_id
                        INNER JOIN relation as rel ON ss.ss_rel_id = rel.rel_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY ss.ss_system DESC, ss.ss_relation, ss.ss_id';
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
        $query = 'SELECT count(DISTINCT (ss.ss_id)) AS total_rows
                   FROM system_setting as ss
                        INNER JOIN languages as lg ON ss.ss_lg_id = lg.lg_id
                        INNER JOIN currency as cur ON ss.ss_cur_id = cur.cur_id
                        INNER JOIN relation as rel ON ss.ss_rel_id = rel.rel_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }


    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ss_id');
    }

}
