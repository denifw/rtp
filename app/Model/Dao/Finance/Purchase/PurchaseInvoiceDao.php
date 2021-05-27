<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Purchase;

use App\Frame\Formatter\SqlHelper;
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

/**
 * Class to handle data access object for table purchase_invoice.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PurchaseInvoiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pi_id',
        'pi_ss_id',
        'pi_number',
        'pi_srv_id',
        'pi_rel_id',
        'pi_rb_id',
        'pi_of_id',
        'pi_rel_of_id',
        'pi_cp_id',
        'pi_reference',
        'pi_rel_reference',
        'pi_doc_id',
        'pi_doc_tax_id',
        'pi_date',
        'pi_due_date',
        'pi_approve_by',
        'pi_approve_on',
        'pi_pay_date',
        'pi_paid_ref',
        'pi_paid_by',
        'pi_paid_on',
        'pi_paid_rb_id',
        'pi_ca_id',
        'pi_pia_id',
        'pi_cur_id',
        'pi_exchange_rate',
    ];

    /**
     * Base dao constructor for purchase_invoice.
     *
     */
    public function __construct()
    {
        parent::__construct('purchase_invoice', 'pi', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table purchase_invoice.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pi_number',
            'pi_rel_reference',
            'pi_date',
            'pi_due_date',
            'pi_paid_ref',
            'pi_approve_on',
            'pi_reference',
            'pi_pay_date',
            'pi_paid_on',
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
        $wheres[] = SqlHelper::generateNumericCondition('pi.pi_id', $referenceValue);
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
    public static function getByReferenceAndSystem(int $referenceValue, int $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('pi.pi_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('pi.pi_ss_id', $systemSettingValue);
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
        $query = 'SELECT pi.pi_id, pi.pi_number, pi.pi_rel_id, rel.rel_name as pi_vendor, pi.pi_rb_id, bn.bn_short_name as pi_rb_bank,
                        rb.rb_number as pi_rb_number, rb.rb_name as pi_rb_name, rb.rb_branch as pi_rb_branch, pi.pi_reference,
                        pi.pi_of_id, o.of_name as pi_invoice_office, pi.pi_rel_of_id, ro.of_name as pi_vendor_office,
                        o.of_address as pi_of_address, dtc.dtc_name as pi_of_district, pi.pi_srv_id, srv.srv_name as pi_service,
                        cty.cty_name as pi_of_city, stt.stt_name as pi_of_state, cnt.cnt_name as pi_of_country, o.of_postal_code as pi_postal_code,
                        pi.pi_cp_id, cp.cp_name as pi_contact_person, cp.cp_email as pi_cp_email, pi.pi_rel_reference,
                        pi.pi_date, pi.pi_due_date, pi.pi_approve_on, pi.pi_pay_date, pi.pi_paid_on, pi.pi_paid_rb_id, pi.pi_paid_ref,
                        bnp.bn_short_name as pi_rbp_bank, rbp.rb_number as pi_rbp_number, rbp.rb_name as pi_rbp_name, rbp.rb_branch as pi_rbp_branch,
                        pi.pi_ca_id, ca.ca_number as pi_ca_number, ca.ca_settlement as pi_ca_settlement,
                        pi.pi_deleted_on, pi.pi_deleted_reason, us.us_name as pi_deleted_by, pi.pi_doc_id,
                        pia.pia_id, pia.pia_created_on, pia.pia_reject_reason, pia.pia_deleted_on, urj.us_name as pia_deleted_by,
                        pid.pid_total as pi_total_amount, pi.pi_doc_tax_id, uc.us_name as pi_created_by,
                        ua.us_name as pi_approve_by, up.us_name as pi_paid_by, pi.pi_cur_id, cur.cur_iso as pi_currency_iso,
                        pi.pi_exchange_rate
                    FROM purchase_invoice as pi INNER JOIN
                        relation as rel ON pi.pi_rel_id = rel.rel_id INNER JOIN
                        service as srv ON pi.pi_srv_id = srv.srv_id INNER JOIN
                        currency as cur ON pi.pi_cur_id = cur.cur_id INNER JOIN
                        office as o ON pi.pi_of_id = o.of_id LEFT OUTER JOIN
                        relation_bank as rb ON pi.pi_rb_id = rb.rb_id LEFT OUTER JOIN
                        bank as bn ON rb.rb_bn_id = bn.bn_id LEFT OUTER JOIN
                        office as ro ON pi.pi_rel_of_id = ro.of_id LEFT OUTER JOIN
                        district as dtc ON ro.of_dtc_id = dtc.dtc_id LEFT OUTER JOIN
                        city as cty ON ro.of_cty_id = cty.cty_id LEFT OUTER JOIN
                        state as stt ON ro.of_stt_id = stt.stt_id LEFT OUTER JOIN
                        country as cnt ON ro.of_cnt_id = cnt.cnt_id LEFT OUTER JOIN
                        contact_person as cp ON pi.pi_cp_id = cp.cp_id LEFT OUTER JOIN
                        relation_bank as rbp ON pi.pi_paid_rb_id = rbp.rb_id LEFT OUTER JOIN
                        bank as bnp ON rbp.rb_bn_id = bnp.bn_id LEFT OUTER JOIN
                        cash_advance as ca ON pi.pi_ca_id = ca.ca_id LEFT OUTER JOIN
                        users as up ON pi.pi_paid_by = up.us_id LEFT OUTER JOIN
                        users as ua ON pi.pi_approve_by = ua.us_id LEFT OUTER JOIN
                        users as uc ON pi.pi_created_by = uc.us_id LEFT OUTER JOIN
                        users as us ON pi.pi_deleted_by = us.us_id LEFT OUTER JOIN
                            purchase_invoice_approval as pia ON pi.pi_pia_id = pia.pia_id LEFT OUTER JOIN
                            users as urj ON pia.pia_deleted_by = urj.us_id LEFT OUTER JOIN
                        (SELECT p.pid_pi_id, SUM(jop.jop_total) as pid_total
                            FROM purchase_invoice_detail as p INNER JOIN
                            job_purchase as jop ON p.pid_jop_id = jop.jop_id
                            WHERE (p.pid_deleted_on IS NULL)
                            GROUP BY p.pid_pi_id) as pid ON pi.pi_id = pid.pid_pi_id ' . $strWhere;
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
            'is_approved' => '',
            'is_rejected' => '',
            'is_requested' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDark(Trans::getFinanceWord('canceled'));
        } else if ($data['is_paid'] === true) {
            $result = new LabelPrimary(Trans::getFinanceWord('paid'));
        } else if ($data['is_approved'] === true) {
            $result = new LabelSuccess(Trans::getFinanceWord('waitingPayment'));
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
