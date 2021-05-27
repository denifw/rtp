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
 * Class to handle data access object for table job_sales.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobSalesDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jos_id', 'jos_jo_id', 'jos_cc_id', 'jos_rel_id', 'jos_description', 'jos_rate',
        'jos_quantity', 'jos_uom_id', 'jos_cur_id', 'jos_exchange_rate', 'jos_tax_id',
        'jos_total', 'jos_prd_id', 'jos_sid_id'
    ];

    /**
     * Base dao constructor for job_sales.
     *
     */
    public function __construct()
    {
        parent::__construct('job_sales', 'jos', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_sales.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jos_description',
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
        $wheres[] = SqlHelper::generateNumericCondition('jos.jos_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateNumericCondition('jos.jos_jo_id', $joId);
        $wheres[] = SqlHelper::generateNullCondition('jos.jos_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jos.jos_deleted_on');

        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
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
        $query = "SELECT jos.jos_id, jos.jos_jo_id, jos.jos_cc_id, jos.jos_rel_id, jos.jos_description, jos.jos_rate, jos.jos_quantity,
                            jos.jos_uom_id, jos.jos_cur_id, jos.jos_exchange_rate, jos.jos_tax_id,
                            cc.cc_code AS jos_cc_code, uom.uom_code AS jos_uom_code, cur.cur_iso AS jos_cur_iso,
                            tax.tax_name AS jos_tax_name, rel.rel_name AS jos_relation, jop.jop_id as jos_jop_id,
                            (CASE WHEN tax.tax_percent IS NULL THEN 0 ELSE tax.tax_percent END) as tax_percent,
                            jos.jos_total, jos.jos_sid_id, sid.sid_si_id as jos_si_id, si.si_number as jos_si_number,
                            jo.jo_number as jos_jo_number, srv.srv_name as jos_srv_name, srt.srt_name as jos_srt_name,
                            ccg.ccg_type as jos_type, jos.jos_prd_id, qt.qt_number as jos_quotation_number
                    FROM job_sales AS jos INNER JOIN
                            job_order AS jo ON jos.jos_jo_id = jo.jo_id INNER JOIN
                            service AS srv ON jo.jo_srv_id = srv.srv_id INNER JOIN
                            service_term AS srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                            cost_code AS cc ON cc.cc_id = jos.jos_cc_id INNER JOIN
                            cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                            unit AS uom ON uom.uom_id = jos.jos_uom_id INNER JOIN
                            currency AS cur ON cur.cur_id = jos.jos_cur_id INNER JOIN
                            relation AS rel ON rel.rel_id = jos.jos_rel_id LEFT OUTER JOIN
                            (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t LEFT OUTER JOIN
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON tax.tax_id = jos.jos_tax_id LEFT OUTER JOIN
                            sales_invoice_detail as sid ON jos.jos_sid_id = sid.sid_id LEFT OUTER JOIN
                            sales_invoice as si ON sid.sid_si_id = si.si_id LEFT OUTER JOIN
                            (SELECT jop_id, jop_jos_id
                                FROM job_purchase
                                    WHERE (jop_deleted_on IS NULL)) as jop ON jos.jos_id = jop.jop_jos_id
                            LEFT OUTER JOIN price_detail as prd ON jos.jos_prd_id = prd.prd_id
                            LEFT OUTER JOIN price as prc ON prd.prd_prc_id = prc.prc_id
                            LEFT OUTER JOIN quotation as qt ON prc.prc_qt_id = qt.qt_id" . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ccg.ccg_type DESC, jos.jos_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
