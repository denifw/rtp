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
 * Class to handle data access object for table cash_advance_received.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvanceReceivedDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'crc_id',
        'crc_ca_id',
    ];

    /**
     * Base dao constructor for cash_advance_received.
     *
     */
    public function __construct()
    {
        parent::__construct('cash_advance_received', 'crc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cash_advance_received.
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
    public static function getByCaid(int $caId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('crc.crc_ca_id', $caId);
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
        $query = 'SELECT crc.crc_id, crc.crc_ca_id, crc.crc_created_on, uc.us_name as crc_created_by, crc.crc_deleted_on,
                        crc.crc_deleted_reason, ud.us_name as crc_deleted_by
                    FROM cash_advance_received as crc
                        INNER JOIN users as uc ON crc.crc_created_by = uc.us_id
                        LEFT OUTER JOIN users as ud ON crc.crc_deleted_by = ud.us_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY crc.crc_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
