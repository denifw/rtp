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
 * Class to handle data access object for table page_right.
 *
 * @package    app
 * @subpackage Model\Dao\Pages
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class PageRightDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pr_id',
        'pr_pg_id',
        'pr_name',
        'pr_description',
        'pr_default',
        'pr_active',
    ];

    /**
     * Base dao constructor for page_right.
     *
     */
    public function __construct()
    {
        parent::__construct('page_right', 'pr', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('pr_id', $referenceValue);
        $data = self::loadAllData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }


    /**
     * Function to get data by reference value
     *
     * @param string $pgId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByPageId(string $pgId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('pr_pg_id', $pgId);
        return self::loadAllData($wheres);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadAllData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT pr_id, pr_pg_id, pr_name, pr_description, pr_default, pr_active,
                            pg.pg_title as pr_pg_title
                        FROM page_right as pr INNER JOIN
                        page as pg ON pg.pg_id = pr.pr_pg_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
