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
        'ba_bn_id',
        'ba_cur_id',
        'ba_account_number',
        'ba_account_name',
        'ba_bank_branch',
        'ba_main',
        'ba_receivable',
        'ba_payable',
        'ba_us_id',
        'ba_limit',
        'ba_block_by',
        'ba_block_on',
        'ba_block_reason',
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
     * Abstract function to load the seeder query for table bank_account.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ba_code',
            'ba_description',
            'ba_account_number',
            'ba_account_name',
            'ba_bank_branch',
            'ba_main',
            'ba_receivable',
            'ba_payable',
            'ba_block_on',
            'ba_block_reason',
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
     * @param UserSession $user To store the user data.
     *
     * @return array
     */
    public static function getByUser(UserSession $user): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ba.ba_ss_id', $user->getSsId());
        $wheres[] = SqlHelper::generateNumericCondition('ba.ba_us_id', $user->getId());
        $wheres[] = SqlHelper::generateNullCondition('ba.ba_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('ba.ba_block_on');
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(int $referenceValue, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ba.ba_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('ba.ba_ss_id', $ssId);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
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
        $query = 'SELECT ba.ba_id, ba.ba_code, ba.ba_description, ba.ba_bn_id, bn.bn_name as ba_bank_name, ba.ba_bank_branch,
                        ba.ba_account_number, ba.ba_account_name, ba.ba_cur_id, cur.cur_iso as ba_currency, ba.ba_main,
                        ba.ba_receivable, ba.ba_payable, ba.ba_us_id, us.us_name as ba_user, ba.ba_limit,
                        (CASE WHEN bab.balance IS NULL THEN 0.0 ELSE bab.balance END) as ba_balance,
                        ba.ba_created_on, uc.us_name as ba_created_by, ba.ba_block_on, ub.us_name as ba_block_by,
                        ba.ba_block_reason, ba.ba_deleted_on, ud.us_name as ba_deleted_by, ba.ba_deleted_reason
                FROM bank_account as ba
                    INNER JOIN bank as bn ON ba.ba_bn_id = bn.bn_id
                    INNER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                    INNER JOIN users as uc ON ba.ba_created_by = uc.us_id
                    LEFT OUTER JOIN users as us ON ba.ba_us_id = us.us_id
                    LEFT OUTER JOIN users as ub ON ba.ba_block_by = ub.us_id
                    LEFT OUTER JOIN users as ud ON ba.ba_deleted_by = ud.us_id
                    LEFT OUTER JOIN (SELECT bab_ba_id, SUM(bab_amount) as balance
                                        FROM bank_account_balance
                                        WHERE bab_deleted_on IS NULL
                                        GROUP BY bab_ba_id) as bab ON ba.ba_id = bab.bab_ba_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ba.ba_deleted_on DESC, ba.ba_block_on DESC, ba.ba_code, ba.ba_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
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
        $query = 'SELECT count(DISTINCT (ba.ba_id)) AS total_rows
                        FROM bank_account as ba
                    INNER JOIN bank as bn ON ba.ba_bn_id = bn.bn_id
                    INNER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                    INNER JOIN users as uc ON ba.ba_created_by = uc.us_id
                    LEFT OUTER JOIN users as us ON ba.ba_us_id = us.us_id
                    LEFT OUTER JOIN users as ub ON ba.ba_block_by = ub.us_id
                    LEFT OUTER JOIN users as ud ON ba.ba_deleted_by = ud.us_id' . $strWhere;

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
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ba_id');
    }
}
