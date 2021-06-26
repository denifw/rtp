<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\Administration;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table working_capital.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class WorkingCapitalDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'wc_id',
        'wc_ss_id',
        'wc_ba_id',
        'wc_bab_id',
        'wc_type',
        'wc_date',
        'wc_time',
        'wc_transaction_on',
        'wc_amount',
        'wc_reference',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'wc_amount',
    ];

    /**
     * Base dao constructor for working_capital.
     *
     */
    public function __construct()
    {
        parent::__construct('working_capital', 'wc', self::$Fields);
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
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('wc_id', $referenceValue);
        $data = self::loadData($wheres);
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
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('wc_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('wc_ss_id', $ssId);
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
        $query = 'SELECT wc.wc_id, wc.wc_ss_id, wc.wc_ba_id, ba.ba_description as wc_bank_account,
                        cur.cur_iso as wc_currency, wc.wc_bab_id, wc.wc_type, wc.wc_date, wc.wc_amount,
                        wc.wc_created_on, uc.us_name as wc_created_by, wc.wc_deleted_on, wc.wc_deleted_reason,
                        ud.us_name as wc_deleted_by, wc.wc_reference, wc.wc_time, wc.wc_transaction_on
                    FROM working_capital as wc
                    INNER JOIN bank_account as ba ON ba.ba_id = wc.wc_ba_id
                    INNER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                    LEFT OUTER JOIN users as uc ON wc.wc_created_by = uc.us_id
                    LEFT OUTER JOIN users as ud ON wc.wc_deleted_by = ud.us_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
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
        $query = 'SELECT count(DISTINCT (wc.wc_id)) AS total_rows
                        FROM working_capital as wc
                    INNER JOIN bank_account as ba ON ba.ba_id = wc.wc_ba_id
                    INNER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                    LEFT OUTER JOIN users as uc ON wc.wc_created_by = uc.us_id
                    LEFT OUTER JOIN users as ud ON wc.wc_deleted_by = ud.us_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'wc_id');
    }


}
