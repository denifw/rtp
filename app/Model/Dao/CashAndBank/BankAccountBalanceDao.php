<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\CashAndBank;

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
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
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
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('bab_id', $referenceValue);
        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get total balance by bank account
     *
     * @param string $baId To store the system setting value.
     *
     * @return float
     */
    public static function getTotalBalanceAccount(string $baId): float
    {
        $balance = 0.0;
        $helper = new SqlHelper();
        $helper->addStringWhere('bab_ba_id', $baId);
        $helper->addNullWhere('bab_deleted_on');
        $helper->addGroupBy('bab_ba_id');
        $query = 'SELECT bab_ba_id, SUM(bab_amount) as balance
                    FROM bank_account_balance ' . $helper;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $balance = (float)DataParser::objectToArray($sqlResults[0])['balance'];
        }
        return $balance;
    }

    /**
     * Function to get all record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadData(SqlHelper $helper): array
    {
        if($helper->hasOrderBy() === false) {
            $helper->addOrderByString('bab_id DESC');
        }
        $query = 'SELECT bab_id, bab_ba_id, bab_amount
                        FROM bank_account_balance ' . $helper;
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
