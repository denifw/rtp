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
 * Class to handle data access object for table page_category.
 *
 * @package    app
 * @subpackage Model\Dao\Pages
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class PageCategoryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pc_id',
        'pc_name',
        'pc_route',
        'pc_active',
    ];

    /**
     * Base dao constructor for page_category.
     *
     */
    public function __construct()
    {
        parent::__construct('page_category', 'pc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table page_category.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pc_name',
            'pc_route',
            'pc_active',
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
        $query = 'SELECT pc_id, pc_name, pc_route, pc_active
                        FROM page_category
                        WHERE (pc_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], self::$Fields);
        }

        return $result;
    }

    /**
     * Function to get all the active record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadActiveData(array $wheres = []): array
    {
        $wheres[] = "(pc_active = 'Y')";
        $wheres[] = '(pc_deleted_on IS NULL)';

        return self::loadAllData($wheres);
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
        $query = 'SELECT pc_id, pc_name, pc_active
                        FROM page_category' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


}