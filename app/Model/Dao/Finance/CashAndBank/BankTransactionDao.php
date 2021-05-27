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

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table bank_transaction.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankTransactionDao extends AbstractBaseDao
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
        'bt_type',
        'bt_payer_ba_id',
        'bt_payer_bab_id',
        'bt_receiver_ba_id',
        'bt_receiver_bab_id',
        'bt_amount',
        'bt_notes',
        'bt_approve_by',
        'bt_approve_on',
        'bt_paid_by',
        'bt_paid_on',
        'bt_paid_ref',
        'bt_doc_id',
        'bt_receive_by',
        'bt_receive_on',
        'bt_synchronize_by',
        'bt_synchronize_on',
    ];

    /**
     * Base dao constructor for bank_transaction.
     *
     */
    public function __construct()
    {
        parent::__construct('bank_transaction', 'bt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table bank_transaction.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'bt_number',
            'bt_type',
            'bt_notes',
            'bt_approve_on',
            'bt_paid_on',
            'bt_paid_ref',
            'bt_receive_on',
            'bt_synchronize_on',
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
     * @param int $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('bt.bt_ss_id', $ssId);
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
        $query = 'SELECT bt.bt_id, bt.bt_ss_id, bt.bt_number, bt.bt_type, bt.bt_payer_ba_id, py.ba_code as bt_payer_code, py.ba_description as bt_payer,
                        bt.bt_payer_bab_id, bt.bt_receiver_ba_id, rc.ba_code as bt_receiver_code, rc.ba_description as bt_receiver, bt.bt_receiver_bab_id,
                        bt.bt_amount, bt.bt_notes, bt.bt_created_on, uc.us_name as bt_created_by, bt.bt_bta_id, bta.bta_created_on as bt_request_on,
                        urq.us_name as bt_request_by, bta.bta_deleted_on as bt_reject_on, urj.us_name as bt_reject_by, bta.bta_deleted_reason as bt_reject_reason,
                        bt.bt_approve_on, ua.us_name as bt_approve_by, bt.bt_paid_on, up.us_name as bt_paid_by, bt.bt_paid_ref, bt.bt_doc_id,
                        bt.bt_receive_on, ur.us_name as bt_receive_by, bt.bt_deleted_on, ud.us_name as bt_deleted_by, bt.bt_deleted_reason,
                        pycur.cur_iso as bt_payer_currency, rccur.cur_iso as bt_receiver_currency
                FROM bank_transaction as bt
                    INNER JOIN users as uc ON bt.bt_created_by = uc.us_id
                    LEFT OUTER JOIN bank_account as py ON bt.bt_payer_ba_id = py.ba_id
                    LEFT OUTER JOIN currency as pycur ON py.ba_cur_id = pycur.cur_id
                    LEFT OUTER JOIN bank_account as rc ON bt.bt_receiver_ba_id = rc.ba_id
                    LEFT OUTER JOIN currency as rccur ON rc.ba_cur_id = rccur.cur_id
                    LEFT OUTER JOIN users as ua ON bt.bt_approve_by = ua.us_id
                    LEFT OUTER JOIN users as up ON bt.bt_paid_by = up.us_id
                    LEFT OUTER JOIN users as ur ON bt.bt_receive_by = ur.us_id
                    LEFT OUTER JOIN users as us ON bt.bt_synchronize_by = us.us_id
                    LEFT OUTER JOIN users as ud ON bt.bt_deleted_by = ud.us_id
                    LEFT OUTER JOIN bank_transaction_approval as bta ON bt.bt_bta_id = bta.bta_id
                    LEFT OUTER JOIN users as urq ON bta.bta_created_by = urq.us_id
                    LEFT OUTER JOIN users as urj ON bta.bta_deleted_by = urj.us_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY bt.bt_deleted_on DESC, bt.bt_receive_on DESC, bt.bt_paid_on DESC, bt.bt_approve_on DESC, bt.bt_bta_id DESC, bt.bt_id';
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
        $query = 'SELECT count(DISTINCT (bt.bt_id)) AS total_rows
                FROM bank_transaction as bt
                    INNER JOIN users as uc ON bt.bt_created_by = uc.us_id
                    LEFT OUTER JOIN bank_account as py ON bt.bt_payer_ba_id = py.ba_id
                    LEFT OUTER JOIN currency as pycur ON py.ba_cur_id = pycur.cur_id
                    LEFT OUTER JOIN bank_account as rc ON bt.bt_receiver_ba_id = rc.ba_id
                    LEFT OUTER JOIN currency as rccur ON rc.ba_cur_id = rccur.cur_id
                    LEFT OUTER JOIN users as ua ON bt.bt_approve_by = ua.us_id
                    LEFT OUTER JOIN users as up ON bt.bt_paid_by = up.us_id
                    LEFT OUTER JOIN users as ur ON bt.bt_receive_by = ur.us_id
                    LEFT OUTER JOIN users as us ON bt.bt_synchronize_by = us.us_id
                    LEFT OUTER JOIN users as ud ON bt.bt_deleted_by = ud.us_id
                    LEFT OUTER JOIN bank_transaction_approval as bta ON bt.bt_bta_id = bta.bta_id
                    LEFT OUTER JOIN users as urq ON bta.bta_created_by = urq.us_id
                    LEFT OUTER JOIN users as urj ON bta.bta_deleted_by = urj.us_id ' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'bt_id');
    }

    /**
     * Function to generate the status
     *
     * @param array $data To store the status data.
     *
     * @return string
     */
    public function generateStatus(array $data): string
    {
        /*
         $data = [
            'is_deleted' => '',
            'is_paid' => '',
            'is_approved' => '',
            'is_requested' => '',
            'is_rejected' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDanger(Trans::getFinanceWord('deleted'));
        } else if ($data['is_receive'] === true) {
            $result = new LabelSuccess(Trans::getFinanceWord('complete'));
        } else if ($data['is_paid'] === true) {
            $result = new LabelInfo(Trans::getFinanceWord('waitingReceive'));
        } else if ($data['is_approved'] === true) {
            $result = new LabelPrimary(Trans::getFinanceWord('waitingPayment'));
        } else if ($data['is_requested'] === true) {
            if ($data['is_rejected'] === true) {
                $result = new LabelDark(Trans::getFinanceWord('rejected'));
            } else {
                $result = new LabelWarning(Trans::getFinanceWord('waitingApproval'));
            }
        } else {
            $result = new LabelGray(Trans::getWord('draft'));
        }

        return $result;
    }


}
