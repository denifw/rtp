<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Fms;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table renewal_order_cost.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrderCostDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rnc_id', 'rnc_rno_id', 'rnc_rnd_id', 'rnc_cc_id', 'rnc_rel_id', 'rnc_description',
        'rnc_rate', 'rnc_quantity', 'rnc_uom_id', 'rnc_tax_id', 'rnc_total'
    ];

    /**
     * Base dao constructor for renewal_order_cost.
     *
     */
    public function __construct()
    {
        parent::__construct('renewal_order_cost', 'rnc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table renewal_order_cost.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rnc_description',
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
        $wheres[] = '(rnc.rnc_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT rnc.rnc_id, rnc.rnc_rno_id, rnc.rnc_rnd_id, rnc.rnc_cc_id, rnc.rnc_rel_id,
                         rnc.rnc_description, rnc.rnc_rate, rnc.rnc_quantity, rnc.rnc_uom_id, rnc.rnc_tax_id, rnc.rnc_total,
                         cc.cc_code AS rnc_cc_code, rel.rel_name AS rnc_rel_name, uom.uom_name AS rnc_uom_name,
                         tax.tax_name AS rnc_tax_name, tax.tax_percent AS rnc_tax_percent, rnd.rnd_est_cost AS rnc_est_cost, rnt.rnt_name AS rnc_rnt_name
                  FROM   renewal_order_cost AS rnc INNER JOIN
                         cost_code AS cc ON cc.cc_id = rnc.rnc_cc_id INNER JOIN
                         relation AS rel ON rel.rel_id = rnc.rnc_rel_id INNER JOIN
                         unit AS uom ON uom.uom_id = rnc.rnc_uom_id INNER JOIN
                            (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t left OUTER join
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail 
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON tax.tax_id = rnc.rnc_tax_id LEFT OUTER JOIN
                         renewal_order_detail AS rnd ON rnd.rnd_id = rnc.rnc_rnd_id LEFT OUTER JOIN
                         renewal_type AS rnt ON rnt.rnt_id = rnd.rnd_rnt_id" . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get the detect are all renewal task's filled cost
     *
     * @param int $rnoId To store the renewal order reference
     *
     * @return array
     */
    public static function getTotalDifferentRenewalCost($rnoId): array
    {
        $rndWheres = [];
        $rndWheres[] = '(rnd_deleted_on IS NULL)';
        $rndWheres[] = '(rnd_rno_id = ' . $rnoId . ')';
        $strRndWhere = ' WHERE ' . implode(' AND ', $rndWheres);
        $rncWheres = [];
        $rncWheres[] = '(rnc_deleted_on IS NULL)';
        $rncWheres[] = '(rnc_rno_id = ' . $rnoId . ')';
        $strRnccWhere = ' WHERE ' . implode(' AND ', $rncWheres);
        $query = 'SELECT rnd.rnd_rno_id, rnd.total_rnd, rnc.rnc_rno_id, rnc.total_rnc, (rnd.total_rnd - rnc.total_rnc) as diff_qty
                  FROM (SELECT rnd_rno_id, count(rnd_id) as total_rnd
                        FROM renewal_order_detail ' . $strRndWhere . ' GROUP BY rnd_rno_id) as rnd
                  INNER JOIN
                     (SELECT rnc_rno_id, count(rnc_id) as total_rnc
                      FROM renewal_order_cost ' . $strRnccWhere . ' GROUP BY rnc_rno_id) as rnc ON rnc.rnc_rno_id = rnd.rnd_rno_id
                GROUP BY rnd.rnd_rno_id, rnd.total_rnd, rnc.rnc_rno_id, rnc.total_rnc ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            return DataParser::objectToArray($sqlResult[0]);
        }

        return [];

    }


}
