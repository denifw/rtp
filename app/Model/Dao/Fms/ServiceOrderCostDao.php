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
 * Class to handle data access object for table service_order_cost.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrderCostDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'svc_id', 'svc_svo_id', 'svc_svd_id', 'svc_cc_id', 'svc_rel_id', 'svc_description', 'svc_rate',
        'svc_quantity', 'svc_uom_id', 'svc_tax_id', 'svc_total'
    ];

    /**
     * Base dao constructor for service_order_cost.
     *
     */
    public function __construct()
    {
        parent::__construct('service_order_cost', 'svc', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_order_cost.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'svc_description'
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
        $wheres[] = '(svc.svc_id = ' . $referenceValue . ')';
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
        $query = "SELECT svc.svc_id, svc.svc_svo_id, svc.svc_svd_id, svc.svc_cc_id, svc.svc_rel_id,
                         svc.svc_description, svc.svc_rate, svc.svc_quantity, svc.svc_uom_id, svc.svc_tax_id, svc.svc_total,
                         svt.svt_name AS svc_svt_name, cc.cc_code AS svc_cc_code, rel.rel_name AS svc_rel_name, uom.uom_name AS svc_uom_name,
                         tax.tax_name AS svc_tax_name, tax.tax_percent AS svc_tax_percent, svt.svt_name AS svc_svt_name, svd.svd_est_cost AS svc_est_cost
                  FROM service_order_cost AS svc LEFT OUTER JOIN
                       service_order_detail AS svd ON svd.svd_id = svc.svc_svd_id LEFT OUTER JOIN
                       service_task AS svt ON svt.svt_id = svd.svd_svt_id INNER JOIN
                       cost_code AS cc ON cc.cc_id = svc.svc_cc_id INNER JOIN
                       relation AS rel ON rel.rel_id = svc.svc_rel_id INNER JOIN
                       unit AS uom ON uom.uom_id = svc.svc_uom_id INNER JOIN
                            (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t left OUTER join
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail 
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON tax.tax_id = svc.svc_tax_id" . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get the detect are all service task's filled cost
     *
     * @param int $svoId To store the service order reference
     *
     * @return array
     */
    public static function getTotalDifferentServiceTaskCost($svoId): array
    {
        $svdWheres = [];
        $svdWheres[] = '(svd_deleted_on IS NULL)';
        $svdWheres[] = '(svd_svo_id = ' . $svoId . ')';
        $strSvdWhere = ' WHERE ' . implode(' AND ', $svdWheres);
        $svcWheres = [];
        $svcWheres[] = '(svc_deleted_on IS NULL)';
        $svcWheres[] = '(svc_svo_id = ' . $svoId . ')';
        $strSvcWhere = ' WHERE ' . implode(' AND ', $svcWheres);
        $query = 'SELECT svd.svd_svo_id, svd.total_svd, svc.svc_svo_id, svc.total_svc, (svd.total_svd - svc.total_svc) as diff_qty
                  FROM (SELECT svd_svo_id, count(svd_id) as total_svd
                        FROM service_order_detail ' . $strSvdWhere . ' GROUP BY svd_svo_id) as svd
                  INNER JOIN
                     (SELECT svc_svo_id, count(svc_id) as total_svc
                      FROM service_order_cost ' . $strSvcWhere . ' GROUP BY svc_svo_id) as svc ON svc.svc_svo_id = svd.svd_svo_id
                GROUP BY svd.svd_svo_id, svd.total_svd, svc.svc_svo_id, svc.total_svc ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            return DataParser::objectToArray($sqlResult[0]);
        }

        return [];

    }


}
