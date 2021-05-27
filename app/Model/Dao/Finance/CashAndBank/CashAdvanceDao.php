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
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table cash_advance.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class CashAdvanceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ca_id',
        'ca_ss_id',
        'ca_number',
        'ca_reference',
        'ca_ba_id',
        'ca_ea_id',
        'ca_eb_id',
        'ca_ea_amount',
        'ca_cp_id',
        'ca_jo_id',
        'ca_date',
        'ca_amount',
        'ca_reserve_amount',
        'ca_actual_amount',
        'ca_return_amount',
        'ca_notes',
        'ca_receive_on',
        'ca_receive_by',
        'ca_receive_bab_id',
        'ca_settlement_by',
        'ca_settlement_on',
        'ca_settlement_bab_id',
        'ca_synchronize_id',
        'ca_synchronize_on',
        'ca_crc_id',
        'ca_crt_id',
        'ca_bt_id',
    ];

    /**
     * Base dao constructor for cash_advance.
     *
     */
    public function __construct()
    {
        parent::__construct('cash_advance', 'ca', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cash_advance.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ca_number',
            'ca_reference',
            'ca_date',
            'ca_notes',
            'ca_receive_on',
            'ca_settlement_on',
            'ca_synchronize_on',
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
        $wheres[] = SqlHelper::generateNumericCondition('ca.ca_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateNumericCondition('ca.ca_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('ca.ca_ss_id', $ssId);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }


    /**
     * Function to get data by reference value
     *
     * @param int $joId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobId(int $joId): array
    {
        $wheres = [];
        $wheres[] = '(ca.ca_jo_id = ' . $joId . ')';
        $wheres[] = '(ca.ca_deleted_on IS NULL)';
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
        $query = 'SELECT ca.ca_id, ca.ca_ss_id, ca.ca_number, ca.ca_ba_id, ba.ba_code as ca_ba_code, ba.ba_description as ca_ba_description,
                        ba.ba_us_id as ca_ba_us_id, ub.us_name as ca_ba_user, (CASE WHEN bab.bab_ba_id IS NULL THEN 0.0 ELSE bab.ba_balance END) as ca_ba_balance,
                        ba.ba_limit as ca_ba_limit, ca.ca_ea_id, ea.ea_code as ca_ea_code, ea.ea_description as ca_ea_description, ca.ca_ea_amount,
                        (CASE WHEN eb.eb_ea_id IS NULL THEN 0.0 ELSE eb.ea_balance END) as ca_ea_balance,
                        ca.ca_jo_id, jo.jo_number as ca_jo_number, srv.srv_id as ca_srv_id, srv.srv_code as ca_srv_code, srv.srv_name as ca_srv_name,
                        srt.srt_id as ca_srt_id, srt.srt_route as ca_srt_route, srt.srt_name as ca_srt_name, ca.ca_cp_id, cp.cp_name as ca_cp_name,
                        ca.ca_reference, ca.ca_date, ca.ca_amount, ca.ca_reserve_amount, ca.ca_actual_amount, ca.ca_return_amount, ca.ca_notes,
                        ca.ca_created_on, uc.us_name as ca_created_by, ca.ca_receive_on, ur.us_name as ca_receive_by,
                        ca.ca_settlement_on, us.us_name as ca_settlement_by, ca.ca_synchronize_on, usc.us_name as ca_synchronize_by,
                        ca.ca_deleted_on, ud.us_name as ca_deleted_by, ca.ca_deleted_reason, ca.ca_bt_id, bt.bt_number as ca_bt_number,
                        bt.bt_approve_on as ca_bt_approve_on, bt.bt_paid_on as ca_bt_paid_on, ba.ba_cur_id as ca_cur_id, cur.cur_iso as ca_currency,
                        bta.bta_id as ca_bta_id, bta.bta_deleted_on as ca_bta_reject_on, bta.bta_deleted_reason as ca_bta_reject_reason, ubtj.us_name as ca_bta_reject_by,
                        ca.ca_crc_id, crc.crc_deleted_on as ca_crc_reject_on, crc.crc_deleted_reason as ca_crc_reject_reason, urcr.us_name as ca_crc_reject_by,
                        ca.ca_crt_id, crt.crt_deleted_on as ca_crt_reject_on, crt.crt_deleted_reason as ca_crt_reject_reason, urtr.us_name as ca_crt_reject_by,
                        ca.ca_eb_id, ca.ca_receive_bab_id, ca.ca_settlement_bab_id
                    FROM cash_advance as ca
                    INNER JOIN bank_account as ba ON ca.ca_ba_id = ba.ba_id
                    INNER JOIN users as ub ON ba.ba_us_id = ub.us_id
                    INNER JOIN currency as cur ON ba.ba_cur_id = cur.cur_id
                    LEFT OUTER JOIN electronic_account as ea ON ca.ca_ea_id = ea.ea_id
                    LEFT OUTER JOIN contact_person as cp ON ca.ca_cp_id = cp.cp_id
                    LEFT OUTER JOIN job_order as jo ON ca.ca_jo_id = jo.jo_id
                    LEFT OUTER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                    LEFT OUTER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    LEFT OUTER JOIN users as ur ON ca.ca_receive_by = ur.us_id
                    LEFT OUTER JOIN users as us ON ca.ca_settlement_by = us.us_id
                    LEFT OUTER JOIN users as usc ON ca.ca_synchronize_by = usc.us_id
                    LEFT OUTER JOIN  users as uc ON ca.ca_created_by = uc.us_id
                    LEFT OUTER JOIN users as ud ON ca.ca_deleted_by = ud.us_id
                    LEFT OUTER JOIN cash_advance_received as crc ON ca.ca_crc_id = crc.crc_id
                    LEFT OUTER JOIN users as urcr ON crc.crc_deleted_by = urcr.us_id
                    LEFT OUTER JOIN cash_advance_returned as crt ON ca.ca_crt_id = crt.crt_id
                    LEFT OUTER JOIN users as urtr ON crt.crt_deleted_by = urtr.us_id
                    LEFT OUTER JOIN bank_transaction as bt ON ca.ca_bt_id = bt.bt_id
                    LEFT OUTER JOIN bank_transaction_approval as bta ON bt.bt_bta_id = bta.bta_id
                    LEFT OUTER JOIN users as ubtj ON bta.bta_deleted_by = ubtj.us_id
                    LEFT OUTER JOIN (SELECT bab_ba_id, SUM(bab_amount) as ba_balance
                                      FROM bank_account_balance
                                      WHERE (bab_deleted_on IS NULL)
                                      GROUP BY bab_ba_id) as bab ON ba.ba_id = bab.bab_ba_id
                    LEFT OUTER JOIN (SELECT eb_ea_id, SUM(eb_amount) as ea_balance
                                      FROM electronic_balance
                                      WHERE (eb_deleted_on IS NULL)
                                      GROUP BY eb_ea_id) as eb ON ea.ea_id = eb.eb_ea_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ca.ca_deleted_on DESC, ca.ca_settlement_on DESC, ca.ca_receive_on DESC, ca.ca_id DESC';
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
        $query = 'SELECT count(DISTINCT (ca.ca_id)) AS total_rows
                    FROM cash_advance as ca
                    INNER JOIN bank_account as ba ON ca.ca_ba_id = ba.ba_id
                    INNER JOIN users as ub ON ba.ba_us_id = ub.us_id
                    LEFT OUTER JOIN electronic_account as ea ON ca.ca_ea_id = ea.ea_id
                    LEFT OUTER JOIN contact_person as cp ON ca.ca_cp_id = cp.cp_id
                    LEFT OUTER JOIN job_order as jo ON ca.ca_jo_id = jo.jo_id
                    LEFT OUTER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                    LEFT OUTER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    LEFT OUTER JOIN users as ur ON ca.ca_receive_by = ur.us_id
                    LEFT OUTER JOIN users as us ON ca.ca_settlement_by = us.us_id
                    LEFT OUTER JOIN users as usc ON ca.ca_synchronize_by = usc.us_id
                    LEFT OUTER JOIN  users as uc ON ca.ca_created_by = uc.us_id
                    LEFT OUTER JOIN users as ud ON ca.ca_deleted_by = ud.us_id
                    LEFT OUTER JOIN cash_advance_received as crc ON ca.ca_crc_id = crc.crc_id
                    LEFT OUTER JOIN users as urcr ON crc.crc_deleted_by = urcr.us_id
                    LEFT OUTER JOIN cash_advance_returned as crt ON ca.ca_crt_id = crt.crt_id
                    LEFT OUTER JOIN users as urtr ON crt.crt_deleted_by = urtr.us_id
                    LEFT OUTER JOIN bank_transaction as bt ON ca.ca_bt_id = bt.bt_id
                    LEFT OUTER JOIN bank_transaction_approval as bta ON bt.bt_bta_id = bta.bta_id
                    LEFT OUTER JOIN users as ubtj ON bta.bta_deleted_by = ubtj.us_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ca_id');
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
            'is_completed' => '',
            'is_return_confirmed' => '',
            'is_return_rejected' => '',
            'is_waiting_return_confirm' => '',
            'is_waiting_return' => '',
            'is_receive_rejected' => '',
            'is_waiting_receive_confirm' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDark(Trans::getFinanceWord('canceled'));
        } else if ($data['is_completed'] === true) {
            $result = new LabelSuccess(Trans::getFinanceWord('completed'));
        } else if ($data['is_settlement_rejected'] === true) {
            $result = new LabelDanger(Trans::getFinanceWord('settlementRejected'));
        } else if ($data['is_waiting_settlement_confirm'] === true) {
            $result = new LabelWarning(Trans::getFinanceWord('waitingSettlementConfirmation'));
        } else if ($data['is_waiting_settlement'] === true) {
            if ($data['is_top_up_exist'] === true && $data['is_top_up_paid'] === false) {
                if ($data['is_top_up_approved'] === true) {
                    $result = new LabelPrimary(Trans::getFinanceWord('waitingTopUpPayment'));
                } else {
                    if ($data['is_top_up_rejected'] === true) {
                        $result = new LabelDark(Trans::getFinanceWord('requestTopUpRejected'));
                    } else {
                        $result = new LabelWarning(Trans::getFinanceWord('waitingTopUpApproval'));
                    }
                }
            } else {
                $result = new LabelPrimary(Trans::getFinanceWord('waitingSettlement'));
            }
        } else if ($data['is_receive_rejected'] === true) {
            $result = new LabelDanger(Trans::getFinanceWord('receiveRejected'));
        } else if ($data['is_waiting_receive_confirm'] === true) {
            $result = new LabelWarning(Trans::getFinanceWord('waitingReceiveConfirmation'));
        } else if ($data['is_top_up_exist'] === true && $data['is_top_up_paid'] === false) {
            if ($data['is_top_up_approved'] === true) {
                $result = new LabelPrimary(Trans::getFinanceWord('waitingTopUpPayment'));
            } else {
                if ($data['is_top_up_rejected'] === true) {
                    $result = new LabelDark(Trans::getFinanceWord('requestTopUpRejected'));
                } else {
                    $result = new LabelWarning(Trans::getFinanceWord('waitingTopUpApproval'));
                }
            }
        } else {
            $result = new LabelGray(Trans::getFinanceWord('draft'));
        }

        return $result;
    }


    /**
     * Function to get all total un settlement cash.
     *
     * @param int $baId To store cash account id.
     *
     * @return float
     */
    public static function getTotalUnSettlementCashByBankAccount(int $baId): float
    {
        $result = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ca_ba_id', $baId);
        $wheres[] = SqlHelper::generateNullCondition('ca_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('ca_settlement_on');
        $wheres[] = SqlHelper::generateNullCondition('ca_receive_on', false);
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = 'SELECT ca_ba_id, ca_amount, ca_reserve_amount
                FROM cash_advance ' . $strWhere;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                $result += (float)$row['ca_amount'] + (float)$row['ca_reserve_amount'];
            }
        }
        return $result;
    }

}
