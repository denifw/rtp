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
 * Class to handle data access object for table bank_transaction_approval.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankTransactionApprovalDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'bta_id',
        'bta_bt_id',
    ];

    /**
     * Base dao constructor for bank_transaction_approval.
     *
     */
    public function __construct()
    {
        parent::__construct('bank_transaction_approval', 'bta', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table bank_transaction_approval.
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
     * @param int $btId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByTransactionId(int $btId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bta.bta_bt_id', $btId);
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
        $query = 'SELECT bta.bta_id, bta.bta_bt_id, bta.bta_created_on, uc.us_name as bta_created_by, bta.bta_deleted_on,
                            ud.us_name as bta_deleted_by, bta.bta_deleted_reason
                        FROM bank_transaction_approval as bta
                            INNER JOIN users as uc ON bta.bta_created_by = uc.us_id
                            INNER JOIN users as ud ON bta.bta_deleted_by = ud.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY bta.bta_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
