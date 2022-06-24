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
use App\Frame\System\Session\UserSession;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table bank_account.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccountDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ba_id',
        'ba_ss_id',
        'ba_code',
        'ba_description',
        'ba_initial_balance',
        'ba_current_balance',
        'ba_bn_id',
        'ba_cur_id',
        'ba_account_number',
        'ba_account_name',
        'ba_bank_branch',
        'ba_main',
        'ba_receivable',
        'ba_payable',
        'ba_us_id',
        'ba_block_by',
        'ba_block_on',
        'ba_block_reason',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'ba_initial_balance',
        'ba_current_balance',
    ];

    /**
     * Base dao constructor for bank_account.
     *
     */
    public function __construct()
    {
        parent::__construct('bank_account', 'ba', self::$Fields);
    }

    /**
     * Function to get data by reference value
     *
     * @param UserSession $user To store the user data.
     *
     * @return array
     */
    public static function getByUser(UserSession $user): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('ba.ba_ss_id', $user->getSsId());
        $helper->addStringWhere('ba.ba_us_id', $user->getId());
        $helper->addNullWhere('ba.ba_deleted_on');
        $helper->addNullWhere('ba.ba_block_on');
        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     * @param string $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $referenceValue, string $ssId): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('ba.ba_ss_id', $ssId);
        $helper->addStringWhere('ba.ba_id', $referenceValue);
        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
        if ($helper->hasOrderBy() === false) {
            $helper->addOrderByString('ba.ba_deleted_on DESC, ba.ba_code, ba.ba_id');
        }
        $query = 'SELECT ba.ba_id, ba.ba_code, ba.ba_description, ba.ba_initial_balance, ba.ba_current_balance,
                           ba.ba_bn_id, bn.bn_short_name as ba_bn_short_name, bn.bn_name as ba_bank_name, ba.ba_bank_branch, ba.ba_bab_id,
                           ba.ba_account_number, ba.ba_account_name, ba.ba_cur_id, cur.cur_iso as ba_currency,
                           ba.ba_main, ba.ba_receivable, ba.ba_payable, ba.ba_us_id, us.us_name as ba_user,
                           ba.ba_created_on, uc.us_name as ba_created_by, ba.ba_block_on, ub.us_name as ba_block_by,
                           ba.ba_block_reason, ba.ba_deleted_on, ud.us_name as ba_deleted_by, ba.ba_deleted_reason
                    FROM bank_account as ba
                             INNER JOIN users as uc ON ba.ba_created_by = uc.us_id
                             LEFT OUTER JOIN bank as bn ON ba.ba_bn_id = bn.bn_id
                             LEFT OUTER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                             LEFT OUTER JOIN users as us ON ba.ba_us_id = us.us_id
                             LEFT OUTER JOIN users as ub ON ba.ba_block_by = ub.us_id
                             LEFT OUTER JOIN users as ud ON ba.ba_deleted_by = ud.us_id ' . $helper;
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get total record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(SqlHelper $helper): int
    {
        $result = 0;
        $query = 'SELECT count(DISTINCT (ba.ba_id)) AS total_rows
                        FROM bank_account as ba
                             INNER JOIN users as uc ON ba.ba_created_by = uc.us_id
                             LEFT OUTER JOIN bank as bn ON ba.ba_bn_id = bn.bn_id
                             LEFT OUTER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                             LEFT OUTER JOIN users as us ON ba.ba_us_id = us.us_id
                             LEFT OUTER JOIN users as ub ON ba.ba_block_by = ub.us_id
                             LEFT OUTER JOIN users as ud ON ba.ba_deleted_by = ud.us_id' . $helper->getConditionForCountData();

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array|String $textColumn To store the text value of single select.
     * @param SqlHelper $helper To store the list condition query.
     * @param array $numerics To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper, array $numerics = []): array
    {
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ba_id', $numerics);
    }
}
