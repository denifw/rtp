<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\System\Location;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table port.
 *
 * @package    app
 * @subpackage Model\Dao\System\Location
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class PortDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'po_id',
        'po_name',
        'po_cnt_id',
        'po_cty_id',
        'po_code',
        'po_tm_id',
        'po_active',
    ];

    /**
     * Base dao constructor for port.
     *
     */
    public function __construct()
    {
        parent::__construct('port', 'po', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table port.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'po_name',
            'po_code',
            'po_active',
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
        $query = 'SELECT po.po_id, po.po_cnt_id, po.po_cty_id, po.po_name, po.po_code, po_tm_id, po.po_active,
                         cnt.cnt_name as po_country, cty.cty_name as po_city, tm.tm_name as po_module
                        FROM port AS po INNER JOIN
                             transport_module AS tm On tm.tm_id = po.po_tm_id LEFT OUTER JOIN
                             country AS cnt ON cnt.cnt_id = po.po_cnt_id LEFT OUTER JOIN
                             city AS cty ON cty.cty_id = po.po_cty_id 
                        WHERE (po_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], \array_merge(self::$Fields, ['po_country', 'po_city', 'po_module']));
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
        $wheres[] = "(po.po_active = 'Y')";
        $wheres[] = '(po.po_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT po.po_id, po.po_cnt_id, po.po_cty_id, po.po_name, po.po_code, po_tm_id, po.po_active,
                         cnt.cnt_name as po_country, cty.cty_name as po_city, tm.tm_name as po_module
                        FROM port AS po INNER JOIN
                             transport_module AS tm On tm.tm_id = po.po_tm_id LEFT OUTER JOIN
                             country AS cnt ON cnt.cnt_id = po.po_cnt_id LEFT OUTER JOIN
                             city AS cty ON cty.cty_id = po.po_cty_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, \array_merge(self::$Fields, ['po_country', 'po_city', 'po_module']));

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
        $query = 'SELECT po.po_id, po.po_name, po.po_code, po.po_cnt_id, po.po_cty_id, po.po_tm_id, tm.tm_name as po_module, po.po_active,
                         cnt.cnt_name as po_country, cty,cty_name as po_city
                  FROM port AS po INNER JOIN
                         transport_module AS tm ON tm.tm_id = po.po_tm_id LEFT OUTER JOIN
                         country AS cnt ON cnt.cnt_id = po.po_cnt_id LEFT OUTER JOIN
                         city AS cty ON cty.cty_id = po.po_cty_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY po.po_name, po.po_id';
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
        $query = 'SELECT count(DISTINCT (po.po_id)) AS total_rows
                   FROM port AS po INNER JOIN
                         transport_module AS tm ON tm.tm_id = po.po_tm_id LEFT OUTER JOIN
                         country AS cnt ON cnt.cnt_id = po.po_cnt_id LEFT OUTER JOIN
                         city AS cty ON cty.cty_id = po.po_cty_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

}
