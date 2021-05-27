<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Sales;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelAqua;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table sales_invoice.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesInvoiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'si_id',
        'si_ss_id',
        'si_number',
        'si_manual',
        'si_of_id',
        'si_so_id',
        'si_rb_id',
        'si_rel_id',
        'si_rel_of_id',
        'si_cp_id',
        'si_rel_reference',
        'si_date',
        'si_pt_id',
        'si_due_date',
        'si_approve_by',
        'si_approve_on',
        'si_receive_id',
        'si_receive_by',
        'si_receive_on',
        'si_pay_time',
        'si_paid_ref',
        'si_paid_by',
        'si_paid_on',
        'si_sia_id',
        'si_cur_id',
        'si_exchange_rate',
    ];

    /**
     * Base dao constructor for sales_invoice.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_invoice', 'si', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_invoice.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'si_number',
            'si_manual',
            'si_rel_reference',
            'si_date',
            'si_due_date',
            'si_approve_on',
            'si_receive_on',
            'si_pay_time',
            'si_paid_ref',
            'si_paid_on',
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
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(si.si_id = ' . $referenceValue . ')';
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
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(si.si_id = ' . $referenceValue . ')';
        $wheres[] = '(si.si_ss_id = ' . $systemSettingValue . ')';
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
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT si.si_id, si.si_ss_id, si.si_number, si.si_manual, si.si_of_id, io.of_name as si_invoice_office,
                        si.si_so_id, so.so_number as si_so_number, si.si_rb_id, rb.rb_number as si_rb_number,
                        bn.bn_name as si_rb_bank, si.si_rel_id, rel.rel_name as si_customer, si.si_rel_of_id, ro.of_name as si_cust_office,
                        cnt.cnt_name as si_cust_country, stt.stt_name as si_cust_state, cty.cty_name as si_cust_city, dtc.dtc_name as si_cust_district,
                        ro.of_address as si_cust_address, ro.of_postal_code as si_cust_postal, si.si_cp_id, cp.cp_name as si_pic_cust, cp.cp_email as si_pic_email,
                        si.si_rel_reference, si.si_date, si.si_pt_id, pt.pt_name as si_payment_terms, pt.pt_days as si_pt_days, si.si_due_date,
                        ua.us_name as si_approve_by, si.si_approve_on, si.si_pay_time, si.si_paid_ref, si.si_paid_on, up.us_name as si_paid_by,
                        sia.sia_id, sia.sia_reject_reason, sia.sia_created_on, sia.sia_deleted_on, urj.us_name as sia_deleted_by,
                        uc.us_name as si_created_by, si.si_created_on, si.si_deleted_reason, si.si_deleted_on, ud.us_name as si_deleted_by,
                        (CASE WHEN si.si_manual = \'Y\' THEN sid.sid_total ELSE jos.jos_total END) as si_total_amount,
                        si.si_receive_id, cpr.cp_name as si_receiver, ur.us_name as si_receive_by, si.si_receive_on,
                        rel.rel_phone as si_rel_phone, rb.rb_name as si_rb_name, rb.rb_branch as si_rb_branch,
                        ow.rel_email as si_email, ow.rel_vat as si_vat, si.si_cur_id, cur.cur_iso as si_currency_iso, si.si_exchange_rate
                    FROM sales_invoice as si INNER JOIN
                        office as io ON si.si_of_id = io.of_id INNER JOIN
                        relation as ow ON io.of_id = ow.rel_id INNER JOIN
                        currency as cur ON si.si_cur_id = cur.cur_id INNER JOIN
                        relation_bank as rb ON si.si_rb_id = rb.rb_id INNER JOIN
                        bank as bn ON rb.rb_bn_id = bn.bn_id INNER JOIN
                        payment_terms as pt ON si.si_pt_id = pt.pt_id INNER JOIN
                        relation as rel ON si.si_rel_id = rel.rel_id LEFT OUTER JOIN
                        sales_order as so ON si.si_so_id = so.so_id LEFT OUTER JOIN
                        office as ro ON si.si_rel_of_id = ro.of_id LEFT OUTER JOIN
                        country as cnt ON ro.of_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                        state as stt ON ro.of_stt_id = stt.stt_id LEFT OUTER JOIN
                        city as cty ON ro.of_cty_id = cty.cty_id LEFT OUTER JOIN
                        district as dtc ON ro.of_dtc_id = dtc.dtc_id LEFT OUTER JOIN
                        contact_person as cp ON si.si_cp_id = cp.cp_id LEFT OUTER JOIN
                        contact_person as cpr ON si.si_receive_id = cpr.cp_id LEFT OUTER JOIN
                        users as uc ON si.si_created_by = uc.us_id LEFT OUTER JOIN
                        users as ua ON si.si_approve_by = ua.us_id LEFT OUTER JOIN
                        users as ur ON si.si_receive_by = ur.us_id LEFT OUTER JOIN
                        users as up ON si.si_paid_by = up.us_id LEFT OUTER JOIN
                        users as ud ON si.si_deleted_by = ud.us_id LEFT OUTER JOIN
                        sales_invoice_approval as sia ON si.si_sia_id = sia.sia_id LEFT OUTER JOIN
                        users as urj ON sia.sia_deleted_by = urj.us_id LEFT OUTER JOIN
                        (SELECT sid_si_id, SUM(sid_total) as sid_total
                            FROM sales_invoice_detail
                            WHERE  (sid_deleted_on IS NULL)
                            GROUP BY sid_si_id) as sid ON si.si_id = sid.sid_si_id LEFT OUTER JOIN
                        (SELECT s.sid_si_id, SUM(j.jos_total) as jos_total
                            FROM sales_invoice_detail as s INNER JOIN
                                job_sales as j ON s.sid_jos_id = j.jos_id
                            WHERE (s.sid_deleted_on IS NULL)
                            GROUP BY s.sid_si_id) as jos ON si.si_id = jos.sid_si_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
            'is_received' => '',
            'is_approved' => '',
            'is_rejected' => '',
            'is_requested' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDark(Trans::getFinanceWord('canceled'));
        } else if ($data['is_paid'] === true) {
            $result = new LabelPrimary(Trans::getFinanceWord('paid'));
        } else if ($data['is_received'] === true) {
            $result = new LabelAqua(Trans::getFinanceWord('waitingPayment'));
        } else if ($data['is_approved'] === true) {
            $result = new LabelSuccess(Trans::getFinanceWord('waitingReceive'));
        } else if ($data['is_rejected'] === true) {
            $result = new LabelDanger(Trans::getFinanceWord('rejected'));
        } else if ($data['is_requested'] === true) {
            $result = new LabelWarning(Trans::getFinanceWord('waitingApproval'));
        } else {
            $result = new LabelGray(Trans::getFinanceWord('draft'));
        }

        return $result;
    }


}
