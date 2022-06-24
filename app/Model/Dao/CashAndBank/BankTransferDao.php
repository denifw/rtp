<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\CashAndBank;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table bank_transfer.
 *
 * @package    app
 * @subpackage Model\Dao\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2022 Deni Firdaus Waruwu.
 */
class BankTransferDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'bt_id',
        'bt_ss_id',
        'bt_number',
        'bt_payer_ba_id',
        'bt_payer_bab_id',
        'bt_receiver_ba_id',
        'bt_receiver_bab_id',
        'bt_date',
        'bt_time',
        'bt_datetime',
        'bt_amount',
        'bt_exchange_rate',
        'bt_notes',
        'bt_doc_id',
        'bt_paid_on',
        'bt_paid_by',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'bt_amount',
        'bt_exchange_rate'
    ];

    /**
     * Base dao constructor for bank_transfer.
     *
     */
    public function __construct()
    {
        parent::__construct('bank_transfer', 'bt', self::$Fields);
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
        $helper->addStringWhere('bt_id', $referenceValue);

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
        $helper->addStringWhere('bt_id', $referenceValue);
        $helper->addStringWhere('bt_ss_id', $ssId);

        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $btId To store the system setting value.
     *
     * @return bool
     */
    public function isPaid(string $btId): bool
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('bt_id', $btId);
        $query = 'SELECT bt_id, bt_paid_on FROM bank_transfer ' . $helper;
        $data = DB::select($query);
        if (count($data) === 1) {
            $row = DataParser::objectToArray($data[0]);
            return !empty($row['bt_paid_on']);
        }
        return false;
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
            $helper->addOrderBy('bt.bt_deleted_on DESC, bt.bt_paid_on DESC, bt.bt_id DESC');
        }

        $query = 'SELECT bt.bt_id, bt.bt_ss_id, bt.bt_number, bt.bt_payer_ba_id, bap.ba_code as bt_payer_code, bap.ba_description as bt_payer_ba,
                           bap.ba_cur_id as bt_payer_cur_id, curp.cur_iso as bt_payer_currency, bt.bt_payer_bab_id, bap.ba_us_id as bt_payer_us_id,
                           bt.bt_receiver_ba_id, bar.ba_code as bt_receiver_code, bar.ba_description as bt_receiver_ba, bar.ba_cur_id as bt_receiver_cur_id,
                           curr.cur_iso as bt_receiver_currency, bt.bt_receiver_bab_id, bar.ba_us_id as bt_receiver_us_id, bt.bt_amount, bt.bt_exchange_rate,
                           bt.bt_date, bt.bt_time, bt.bt_datetime, bt.bt_notes, bt.bt_doc_id, bt.bt_created_on,
                           uc.us_name as bt_created_by, bt.bt_paid_on, up.us_name as bt_paid_by, bt.bt_deleted_on,
                           ud.us_name as bt_deleted_by, bt.bt_deleted_reason
                    FROM bank_transfer as bt
                    INNER JOIN bank_account as bap ON bt.bt_payer_ba_id = bap.ba_id
                    INNER JOIN currency as curp ON bap.ba_cur_id = curp.cur_id
                    INNER JOIN bank_account as bar ON bt.bt_receiver_ba_id = bar.ba_id
                    INNER JOIN currency as curr ON bar.ba_cur_id = curr.cur_id
                    INNER JOIN users as uc ON bt.bt_created_by = uc.us_id
                    LEFT OUTER JOIN users as up ON bt.bt_paid_by = up.us_id
                    LEFT OUTER JOIN users as ud ON bt.bt_deleted_by = ud.us_id ' . $helper;
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
        $query = 'SELECT count(DISTINCT (bt.bt_id)) AS total_rows
                        FROM bank_transfer as bt
                    INNER JOIN bank_account as bap ON bt.bt_payer_ba_id = bap.ba_id
                    INNER JOIN currency as curp ON bap.ba_cur_id = curp.cur_id
                    INNER JOIN bank_account as bar ON bt.bt_receiver_ba_id = bar.ba_id
                    INNER JOIN currency as curr ON bar.ba_cur_id = curr.cur_id
                    INNER JOIN users as uc ON bt.bt_created_by = uc.us_id
                    LEFT OUTER JOIN users as up ON bt.bt_paid_by = up.us_id
                    LEFT OUTER JOIN users as ud ON bt.bt_deleted_by = ud.us_id ' . $helper->getConditionForCountData();

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
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper): array
    {
        $numericFields = [
            'bt_amount',
            'bt_exchange_rate'
        ];
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'bt_id', $numericFields);
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $row To store the data.
     *
     * @return string
     */
    public function getStatus(array $row): string
    {
        if (empty($row['bt_deleted_on']) === false) {
            return new LabelDark(Trans::getWord('deleted'));
        }
        if (empty($row['bt_paid_on']) === false) {
            return new LabelSuccess(Trans::getWord('paid'));
        }
        return new LabelGray(Trans::getWord('draft'));
    }

}
