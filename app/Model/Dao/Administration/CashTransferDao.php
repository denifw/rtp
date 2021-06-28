<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Administration;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table cash_transfer.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashTransferDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ct_id',
        'ct_ss_id',
        'ct_number',
        'ct_payer_ba_id',
        'ct_payer_bab_id',
        'ct_receiver_ba_id',
        'ct_receiver_bab_id',
        'ct_amount',
        'ct_currency_exchange',
        'ct_notes',
        'ct_doc_id',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'ct_amount',
        'ct_currency_exchange',
    ];

    /**
     * Base dao constructor for cash_transfer.
     *
     */
    public function __construct()
    {
        parent::__construct('cash_transfer', 'ct', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('ct.ct_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateStringCondition('ct.ct_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('ct.ct_ss_id', $ssId);
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
        $query = 'SELECT ct.ct_id, ct.ct_number, ct.ct_ss_id, ct.ct_payer_ba_id, bap.ba_description as ct_payer, ct.ct_payer_bab_id,
                        ct.ct_receiver_ba_id, bar.ba_description as ct_receiver, ct.ct_receiver_bab_id,
                        ct.ct_date, ct.ct_amount, ct.ct_currency_exchange, ct.ct_notes, ct.ct_doc_id,
                        ct.ct_created_on, uc.us_name as ct_created_by, ct.ct_updated_on, uu.us_name as ct_updated_by,
                        ct.ct_deleted_on, ct.ct_deleted_reason, ud.us_name as ct_deleted_by
                    FROM cash_transfer as ct
                    INNER JOIN bank_account as bap ON ct.ct_payer_ba_id = bap.ba_id
                    INNER JOIN bank_account as bar ON ct.ct_receiver_ba_id = bar.ba_id
                    INNER JOIN users as uc ON ct.ct_created_by = uc.us_id
                    LEFT OUTER JOIN users as uu ON ct.ct_updated_by = uu.us_id
                    LEFT OUTER JOIN users as ud ON ct.ct_deleted_by = ud.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ct.ct_number DESC, ct.ct_id';
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
        $query = 'SELECT count(DISTINCT (ct.ct_id)) AS total_rows
                        FROM cash_transfer as ct
                    INNER JOIN bank_account as bap ON ct.ct_payer_ba_id = bap.ba_id
                    INNER JOIN bank_account as bar ON ct.ct_receiver_ba_id = bar.ba_id
                    INNER JOIN users as uc ON ct.ct_created_by = uc.us_id
                    LEFT OUTER JOIN users as uu ON ct.ct_updated_by = uu.us_id
                    LEFT OUTER JOIN users as ud ON ct.ct_deleted_by = ud.us_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ct_id');
    }


}
