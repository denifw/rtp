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
 * Class to handle data access object for table job_deposit.
 *
 * @package    app
 * @subpackage Model\Dao\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDepositDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jd_id',
        'jd_number',
        'jd_rel_ref',
        'jd_paid_ref',
        'jd_settle_ref',
        'jd_ss_id',
        'jd_jo_id',
        'jd_rel_id',
        'jd_of_id',
        'jd_cp_id',
        'jd_cc_id',
        'jd_date',
        'jd_return_date',
        'jd_amount',
        'jd_approved_on',
        'jd_approved_by',
        'jd_paid_on',
        'jd_paid_by',
        'jd_pm_id',
        'jd_settle_on',
        'jd_settle_by',
        'jd_rb_rel',
        'jd_rb_paid',
        'jd_rb_return',
        'jd_return_on',
        'jd_return_by',
        'jd_deleted_reason',
        'jd_jda_id',
        'jd_invoice_of_id'
    ];

    /**
     * Base dao constructor for job_deposit.
     *
     */
    public function __construct()
    {
        parent::__construct('job_deposit', 'jd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_deposit.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jd_rel_ref',
            'jd_number',
            'jd_paid_ref',
            'jd_settle_ref',
            'jd_date',
            'jd_return_date',
            'jd_approved_on',
            'jd_paid_on',
            'jd_settle_on',
            'jd_return_on',
            'jd_deleted_reason',
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
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(jd.jd_id = ' . $referenceValue . ')';
        $wheres[] = '(jd.jd_ss_id = ' . $systemSettingValue . ')';
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
        $query = 'SELECT jd.jd_id, jd.jd_jo_id, jd.jd_rel_id, jd.jd_of_id, jd.jd_cp_id, jd.jd_cc_id,
                        jd.jd_date, jd.jd_return_date, jd.jd_amount, jd.jd_approved_on, ua.us_name as jd_approved_by,
                        jd.jd_paid_on, up.us_name as jd_paid_by, jd.jd_settle_on, us.us_name as jd_settle_by,
                        jd.jd_return_on, ur.us_name as jd_return_by, jd.jd_rb_paid, jd.jd_rb_return,
                        jd.jd_created_on, uc.us_name as jd_created_by, jd.jd_deleted_on, ud.us_name as jd_deleted_by,
                        jd.jd_deleted_reason, jo.jo_number as jd_jo_number, jo.jo_srv_id as jd_jo_srv_id, srv.srv_name as jd_jo_service,
                        jo.jo_srt_id as jd_jo_srt_id, srt.srt_name as jd_jo_service_term, rel.rel_name as jd_relation,
                        o.of_name as jd_rel_office, o.of_address as jd_of_address, o.of_postal_code as jd_postal_code,
                        dtc.dtc_name as jd_of_district, cty.cty_name as jd_of_city, stt.stt_name as jd_of_state, cnt.cnt_name as jd_of_country,
                        cp.cp_name as jd_pic, cc.cc_code as jd_cc_code, cc.cc_name as jd_cc_name, rbp.rb_number as jd_rb_number_paid,
                        rbp.rb_name as jd_rb_name_paid, rbp.rb_branch as jd_rb_branch_paid, bnp.bn_short_name as jd_bank_paid,
                        rbr.rb_number as jd_rb_number_return, rbr.rb_name as jd_rb_name_return, rbr.rb_branch as jd_rb_branch_return,
                        bnr.bn_short_name as jd_bank_return, (CASE WHEN jdd.jdd_total IS NULL THEN 0 ELSE jdd.jdd_total END) as jd_claim_amount,
                        jd.jd_number, jd.jd_paid_ref, jd.jd_settle_ref, jda.jda_id, jda.jda_reject_reason, jda.jda_deleted_on, uad.us_name as jda_deleted_by,
                        rbl.rb_number as jd_rb_number_rel, rbl.rb_name as jd_rb_name_rel, rbl.rb_branch as jd_rb_branch_rel, bnl.bn_short_name as jd_bank_rel,
                        jd.jd_invoice_of_id, io.of_name  as jd_invoice_office, pm.pm_name as jd_payment_method,
                        jd.jd_rel_ref, jd.jd_rb_rel, jo.jo_rel_id as jd_jo_rel_id
                    FROM job_deposit as jd
                        INNER JOIN job_order as jo ON jd.jd_jo_id = jo.jo_id
                        INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jd.jd_rel_id = rel.rel_id
                        INNER JOIN office as o ON jd.jd_of_id = o.of_id
                        INNER JOIN office as io ON jd.jd_invoice_of_id = io.of_id
                        INNER JOIN cost_code as cc ON jd.jd_cc_id = cc.cc_id
                        INNER JOIN cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id
                        LEFT OUTER JOIN users as uc ON jd.jd_created_by = uc.us_id
                        LEFT OUTER JOIN users as ua ON jd.jd_approved_by = ua.us_id
                        LEFT OUTER JOIN users as up ON jd.jd_paid_by = up.us_id
                        LEFT OUTER JOIN users as us ON jd.jd_settle_by = us.us_id
                        LEFT OUTER JOIN users as ur ON jd.jd_return_by = ur.us_id
                        LEFT OUTER JOIN users as ud ON jd.jd_deleted_by = ud.us_id
                        LEFT OUTER JOIN contact_person as cp ON jd.jd_cp_id = cp.cp_id
                        LEFT OUTER JOIN district as dtc ON o.of_dtc_id = dtc.dtc_id
                        LEFT OUTER JOIN city as cty ON o.of_cty_id = cty.cty_id
                        LEFT OUTER JOIN state as stt ON o.of_stt_id = stt.stt_id
                        LEFT OUTER JOIN country as cnt ON o.of_cnt_id = cnt.cnt_id
                        LEFT OUTER JOIN relation_bank as rbl ON jd.jd_rb_rel = rbl.rb_id
                        LEFT OUTER JOIN bank as bnl ON rbl.rb_bn_id = bnl.bn_id
                        LEFT OUTER JOIN relation_bank as rbp ON jd.jd_rb_paid = rbp.rb_id
                        LEFT OUTER JOIN bank as bnp ON rbp.rb_bn_id = bnp.bn_id
                        LEFT OUTER JOIN relation_bank as rbr ON jd.jd_rb_return = rbr.rb_id
                        LEFT OUTER JOIN bank as bnr ON rbr.rb_bn_id = bnr.bn_id
                        LEFT OUTER JOIN payment_method as pm ON jd.jd_pm_id = pm.pm_id
                        LEFT OUTER JOIN (SELECT jdd_jd_id, SUM(jdd_total) as jdd_total
                            FROM job_deposit_detail
                            WHERE jdd_deleted_on IS NULL
                            GROUP BY jdd_jd_id) as jdd ON jd.jd_id = jdd.jdd_jd_id
                        LEFT OUTER JOIN job_deposit_approval as jda ON jd.jd_jda_id = jda.jda_id
                        LEFT OUTER JOIN users as uad ON jda.jda_deleted_by = uad.us_id' . $strWhere;
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
            'is_return' => '',
            'is_settle' => '',
            'is_paid' => '',
            'is_approved' => '',
            'is_requested' => '',
            'is_rejected' => '',
        ];
         * */
        if ($data['is_deleted'] === true) {
            $result = new LabelDanger(Trans::getFinanceWord('deleted'));
        } else if ($data['is_return'] === true) {
            $result = new LabelSuccess(Trans::getFinanceWord('complete'));
        } else if ($data['is_settle'] === true) {
            $result = new LabelDark(Trans::getFinanceWord('waitingRefund'));
        } else if ($data['is_paid'] === true) {
            $result = new LabelAqua(Trans::getFinanceWord('waitingSettlement'));
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
