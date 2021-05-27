<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\Master;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table unit.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class UnitDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'uom_id',
        'uom_name',
        'uom_code',
        'uom_active',
    ];

    /**
     * Base dao constructor for unit.
     *
     */
    public function __construct()
    {
        parent::__construct('unit', 'uom', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table unit.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'uom_name',
            'uom_code',
            'uom_active'
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
     * @param int $uomId To store the where statment.
     *
     * @return array
     */
    public static function getByReference($uomId): array
    {
        $wheres[] = '(uom_id = ' . $uomId . ')';
        $result = self::loadData($wheres);
        if (\count($result) === 1) {
            return $result[0];
        }

        return [];
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
        $wheres[] = "(uom_active = 'Y')";
        $wheres[] = '(uom_deleted_on IS NULL)';

        return self::loadData($wheres);

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
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT uom_id, uom_name, uom_code, uom_active
                        FROM unit ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
