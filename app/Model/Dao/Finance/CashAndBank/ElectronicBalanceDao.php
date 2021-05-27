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
 * Class to handle data access object for table electronic_balance.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicBalanceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'eb_id',
        'eb_ea_id',
        'eb_amount',
    ];

    /**
     * Base dao constructor for electronic_balance.
     *
     */
    public function __construct()
    {
        parent::__construct('electronic_balance', 'eb', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table electronic_balance.
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
     * Function to get total balance by bank account
     *
     * @param int $idUser To store the electronic account reference
     *
     * @return float
     */
    public static function getTotalBalanceUser(int $idUser): float
    {
        $balance = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ea.ea_us_id', $idUser);
        $wheres[] = SqlHelper::generateNullCondition('ea.ea_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('eb.eb_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ea.ea_us_id, SUM(eb.eb_amount) as balance
                    FROM electronic_balance as eb
                        INNER JOIN electronic_account as ea ON ea.ea_id = eb.eb_ea_id ' . $strWheres;
        $query .= ' GROUP BY ea.ea_us_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $balance = (float)DataParser::objectToArray($sqlResults[0])['balance'];
        }
        return $balance;
    }

    /**
     * Function to get total balance by bank account
     *
     * @param int $eaId To store the electronic account reference
     *
     * @return float
     */
    public static function getTotalBalanceAccount(int $eaId): float
    {
        $balance = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('eb_ea_id', $eaId);
        $wheres[] = SqlHelper::generateNullCondition('eb_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT eb_ea_id, SUM(eb_amount) as balance
                    FROM electronic_balance ' . $strWheres;
        $query .= ' GROUP BY eb_ea_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $balance = (float)DataParser::objectToArray($sqlResults[0])['balance'];
        }
        return $balance;
    }

    /**
     * Function to get total balance by bank account
     *
     * @param int $eaId To store the electronic account reference
     *
     * @return bool
     */
    public static function isAccountHasBalance(int $eaId): bool
    {
        $result = false;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('eb_ea_id', $eaId);
        $wheres[] = SqlHelper::generateNullCondition('eb_deleted_on');
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT eb_ea_id, count(eb_id) as total
                    FROM electronic_balance ' . $strWheres;
        $query .= ' GROUP BY eb_ea_id';
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
        $query = 'SELECT eb_id, eb_ea_id, eb_amount
                        FROM electronic_balance ' . $strWhere;
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
