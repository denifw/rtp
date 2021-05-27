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
 * Class to handle data access object for table menu.
 *
 * @package    app
 * @subpackage Model\Dao\Pages
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class MenuDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'mn_id',
        'mn_name',
        'mn_parent',
        'mn_order',
        'mn_icon',
        'mn_active',
    ];

    /**
     * Base dao constructor for menu.
     *
     */
    public function __construct()
    {
        parent::__construct('menu', 'mn', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table menu.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'mn_name',
            'mn_icon',
            'mn_active',
            'mn_active',
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
        $query = 'SELECT mn.mn_id, mn.mn_name, mn.mn_parent, mn.mn_icon, mn.mn_active, mn.mn_order, m.mn_name as parent_menu
                        FROM menu as mn LEFT OUTER JOIN
                        menu as m ON mn.mn_parent = m.mn_id
                        WHERE (mn.mn_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], array_merge(self::$Fields, ['parent_menu']));
        }
        return $result;
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $wheres = [];
        $wheres[] = '(mn_deleted_on IS NULL)';
        $wheres[] = "(mn_active = 'Y')";

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
        $query = 'SELECT mn_id, mn_name, mn_active
                        FROM menu' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


}
