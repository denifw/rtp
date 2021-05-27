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
 * Class to handle data access object for table bank_account_balance.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccountBalanceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'bab_id',
        'bab_ba_id',
        'bab_amount',
    ];

    /**
     * Base dao constructor for bank_account_balance.
     *
     */
    public function __construct()
    {
        parent::__construct('bank_account_balance', 'bab', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table bank_account_balance.
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
     * @param int $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bab_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get total balance by bank account
     *
     * @param int $baId To store the system setting value.
     *
     * @return float
     */
    public static function getTotalBalanceAccount(int $baId): float
    {
        $balance = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bab_ba_id', $baId);
        $wheres[] = SqlHelper::generateNullCondition('bab_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT bab_ba_id, SUM(bab_amount) as balance
                    FROM bank_account_balance ' . $strWheres;
        $query .= ' GROUP BY bab_ba_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $balance = (float)DataParser::objectToArray($sqlResults[0])['balance'];
        }
        return $balance;
    }

    /**
     * Function to get total balance by bank account
     *
     * @param int $baId To store the system setting value.
     *
     * @return bool
     */
    public static function isBankAccountHasBalance(int $baId): bool
    {
        $result = false;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bab_ba_id', $baId);
        $wheres[] = SqlHelper::generateNullCondition('bab_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT bab_ba_id, count(bab_id) as total
                    FROM bank_account_balance ' . $strWheres;
        $query .= ' GROUP BY bab_ba_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $total = (int)DataParser::objectToArray($sqlResults[0])['total'];
            if ($total > 0) {
                $result = true;
            }
        }
        return $result;
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
        $query = 'SELECT bab_id, bab_ba_id, bab_amount
                        FROM bank_account_balance' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
