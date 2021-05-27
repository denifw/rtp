<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Finance\CashAndBank;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table cash_advance_returned.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvanceReturnedDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'crt_id',
        'crt_ca_id',
    ];

    /**
     * Base dao constructor for cash_advance_returned.
     *
     */
    public function __construct()
    {
        parent::__construct('cash_advance_returned', 'crt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cash_advance_returned.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder();
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
     * @param int $caId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByCaId(int $caId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('crt.crt_ca_id', $caId);
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT crt.crt_id, crt.crt_ca_id, crt.crt_created_on, uc.us_name as crt_created_by, crt.crt_deleted_on,
                        crt.crt_deleted_reason, ud.us_name as crt_deleted_by
                    FROM cash_advance_returned as crt
                        INNER JOIN users as uc ON crt.crt_created_by = uc.us_id
                        LEFT OUTER JOIN users as ud ON crt.crt_deleted_by = ud.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY crt.crt_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
