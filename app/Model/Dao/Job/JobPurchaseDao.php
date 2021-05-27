<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_purchase.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobPurchaseDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jop_id', 'jop_jo_id', 'jop_cc_id', 'jop_rel_id', 'jop_description', 'jop_rate',
        'jop_quantity', 'jop_uom_id', 'jop_cur_id', 'jop_exchange_rate', 'jop_tax_id',
        'jop_jos_id', 'jop_total', 'jop_doc_id', 'jop_prd_id', 'jop_pid_id'
    ];

    /**
     * Base dao constructor for job_purchase.
     *
     */
    public function __construct()
    {
        parent::__construct('job_purchase', 'jop', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_purchase.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jop_description',
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
        $wheres[] = '(jop.jop_id = ' . $referenceValue . ')';
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
    public static function getByJobId($joId): array
    {
        $wheres = [];
        $wheres[] = '(jop.jop_jo_id = ' . $joId . ')';
        $wheres[] = '(jop.jop_deleted_on IS NULL)';
        return self::loadData($wheres);
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = '(jop.jop_deleted_on IS NULL)';
        return self::loadData($where);
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
        $query = "SELECT jop.jop_id, jop.jop_jo_id, jo.jo_number as jop_jo_number, jop.jop_cc_id, jop.jop_rel_id, jop.jop_description, jop.jop_rate, jop.jop_quantity,
                         jop.jop_uom_id, jop.jop_cur_id, jop.jop_exchange_rate, jop.jop_tax_id,
                         cc.cc_code AS jop_cc_code, uom.uom_code AS jop_uom_code, cur.cur_iso AS jop_cur_iso,
                         tax.tax_name AS jop_tax_name, rel.rel_name AS jop_relation, tax.tax_percent,
                         jop.jop_jos_id, srv.srv_name as jop_jo_service, srt.srt_name as jop_jo_service_term, jop.jop_total,
                         jop.jop_pid_id, jop.jop_doc_id, pid.pid_pi_id as jop_pi_id, pi.pi_number as jop_pi_number,
                         ccg.ccg_type as jop_type, (CASE WHEN ccg.ccg_type = 'P' THEN 'Purchase' ELSE 'Reimburse' END) AS jop_type_name,
                        jop.jop_prd_id, qt.qt_number as jop_quotation_number, jo.jo_srt_id as jop_jo_srt_id,
                        jop.jop_cad_id, cad.cad_ca_id as jop_ca_id,  ca.ca_settlement_on as jop_ca_settlement_on
                        FROM job_purchase AS jop
                            INNER JOIN job_order as jo ON jo.jo_id = jop.jop_jo_id
                            INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                            INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                            INNER JOIN cost_code AS cc ON cc.cc_id = jop.jop_cc_id
                            INNER JOIN cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id
                            INNER JOIN unit AS uom ON uom.uom_id = jop.jop_uom_id
                            INNER JOIN currency AS cur ON cur.cur_id = jop.jop_cur_id
                            INNER JOIN relation AS rel ON rel.rel_id = jop.jop_rel_id
                            LEFT OUTER JOIN (SELECT t.tax_id, t.tax_name, (CASE WHEN tax_percent IS NULL THEN 0 ELSE tax_percent END) as tax_percent
                                                FROM tax as t LEFT OUTER JOIN
                                                    (SELECT td_tax_id, SUM(td_percent) as tax_percent
                                                    FROM tax_detail
                                                    WHERE td_active = 'Y' AND td_deleted_on IS NULL
                                                    GROUP BY td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jop.jop_tax_id = tax.tax_id
                            LEFT OUTER JOIN purchase_invoice_detail as pid ON jop.jop_pid_id = pid.pid_id
                            LEFT OUTER JOIN purchase_invoice as pi ON pi.pi_id = pid.pid_pi_id
                            LEFT OUTER JOIN price_detail as prd ON jop.jop_prd_id = prd.prd_id
                            LEFT OUTER JOIN price as prc ON prd.prd_prc_id = prc.prc_id
                            LEFT OUTER JOIN quotation as qt ON prc.prc_qt_id = qt.qt_id
                            LEFT OUTER JOIN cash_advance_detail as cad ON jop.jop_cad_id = cad.cad_id
                            LEFT OUTER JOIN cash_advance as ca ON cad.cad_ca_id = ca.ca_id" . $strWhere;
        $query .= ' ORDER BY ccg.ccg_type, jop.jop_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all record.
     *
     * @param int $joId To store the offset of the data to apply limit.
     * @param int $relId To store the offset of the data to apply limit.
     * @param int $currencyId To store the offset of the data to apply limit.
     *
     * @return float
     */
    public static function getTotalPurchaseJobCashAdvance(int $joId, int $relId, int $currencyId): float
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jop_jo_id', $joId);
        $wheres[] = SqlHelper::generateNumericCondition('jop_rel_id', $relId);
        $wheres[] = SqlHelper::generateNumericCondition('jop_cur_id', $currencyId);
        $wheres[] = SqlHelper::generateNullCondition('jop_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jop_pid_id');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = ' SELECT jop_jo_id, SUM(jop_total) as total
                        FROM job_purchase ' . $strWhere;
        $query .= ' GROUP BY jop_jo_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            return (float)DataParser::objectToArray($sqlResults[0])['total'];
        }
        return 0.0;
    }
}
