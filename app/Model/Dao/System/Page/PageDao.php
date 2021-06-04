<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Page;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table page.
 *
 * @package    app
 * @subpackage Model\Dao\Pages
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class PageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pg_id',
        'pg_title',
        'pg_description',
        'pg_route',
        'pg_mn_id',
        'pg_pc_id',
        'pg_icon',
        'pg_order',
        'pg_default',
        'pg_system',
        'pg_active',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'pg_order',
    ];

    /**
     * Base dao constructor for page.
     *
     */
    public function __construct()
    {
        parent::__construct('page', 'pg', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('pg.pg_id', $referenceValue);
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
        $query = 'SELECT pg.pg_id, pg.pg_title, pg.pg_description, pg.pg_route, pg.pg_mn_id, mn.mn_name, pg.pg_pc_id,
                        pg.pg_icon, pg.pg_order, pg.pg_default, pg.pg_system, pg.pg_active,
                        pc.pc_name, pc.pc_code, m2.mn_name as parent_menu
                FROM page as pg
                    INNER JOIN page_category AS pc on pg.pg_pc_id = pc.pc_id
                    LEFT OUTER JOIN menu as mn ON pg.pg_mn_id = mn.mn_id
                    LEFT OUTER JOIN menu as m2 ON mn.mn_parent = m2.mn_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY pg.pg_title, pc.pc_name, mn.mn_order, pg.pg_id';
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
        $query = 'SELECT count(DISTINCT (pg.pg_id)) AS total_rows
                       FROM page as pg
                    INNER JOIN page_category AS pc on pg.pg_pc_id = pc.pc_id
                    LEFT OUTER JOIN menu as mn ON pg.pg_mn_id = mn.mn_id
                    LEFT OUTER JOIN menu as m2 ON mn.mn_parent = m2.mn_id' . $strWhere;
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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'pg_id');
    }


}
