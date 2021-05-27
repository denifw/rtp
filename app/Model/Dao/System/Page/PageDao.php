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
     * Base dao constructor for page.
     *
     */
    public function __construct()
    {
        parent::__construct('page', 'pg', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table page.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pg_title',
            'pg_description',
            'pg_route',
            'pg_icon',
            'pg_default',
            'pg_system',
            'pg_active',
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
        $query = 'SELECT pg.pg_id, pg.pg_title, pg.pg_description, pg.pg_route, pg.pg_mn_id, mn.mn_name, pg.pg_pc_id,
                        pg.pg_icon, pg.pg_icon, pg.pg_order, pg.pg_default, pg.pg_system, pg.pg_active 
                        FROM page as pg LEFT OUTER JOIN
                        menu as mn ON pg.pg_mn_id = mn.mn_id
                        WHERE (pg_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], array_merge(self::$Fields, ['mn_name']));
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadAllData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT pg.pg_id, pg.pg_title, pg.pg_description, pg.pg_route, pg.pg_mn_id, mn.mn_name, pg.pg_pc_id,
                        pg.pg_icon, pg.pg_order, pg.pg_default, pg.pg_system, pg.pg_active,
                        pc.pc_name, m2.mn_name as parent_menu
                        FROM page as pg INNER JOIN
                        page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                        menu as mn ON pg.pg_mn_id = mn.mn_id LEFT OUTER JOIN
                        menu as m2 ON mn.mn_parent = m2.mn_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
