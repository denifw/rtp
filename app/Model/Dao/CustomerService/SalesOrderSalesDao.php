<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use function count;

/**
 * Class to handle data access object for table sales_order.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrderSalesDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sosl_id', 'sosl_jo_id', 'sosl_cc_id', 'sosl_rel_id', 'sosl_description', 'sosl_rate',
        'sosl_quantity', 'sosl_uom_id', 'sosl_cur_id', 'sosl_exchange_rate', 'sosl_tax_id'
    ];

    /**
     * Base dao constructor for sales_order.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_sales', 'sosl', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sosl_description'
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
        $result = [];
        $where = [];
        $where[] = '(sosl.sosl_id = ' . $referenceValue . ')';
        $data = self::loadData($where);
        if (count($data) === 1) {
            $result = $data[0];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $soId     To store the reference value of the table.
     *
     * @return array
     */
    public static function getBySoId($soId): array
    {
        $where = [];
        $where[] = '(sosl.sosl_so_id = ' . $soId . ')';
        $where[] = '(sosl.sosl_deleted_on IS NULL)';
        return self::doPrepareSoSalesData(self::loadData($where));
    }
    /**
     * Function to get data by reference value
     *
     * @param array $data to store the data.
     *
     * @return array
     */
    public static function doPrepareSoSalesData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $rate = (float) $row['sosl_rate'] * (float) $row['sosl_quantity'] * (float) $row['sosl_exchange_rate'];
            $tax = ($rate * (float) $row['tax_percent']) / 100;
            $row['sosl_sub_total'] = $rate + $tax;
            $results[] = $row;
        }
        return $results;
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
        $query = "SELECT sosl.sosl_id, sosl.sosl_so_id, sosl.sosl_cc_id, sosl.sosl_rel_id, sosl.sosl_description, sosl.sosl_rate, sosl.sosl_quantity,
                            sosl.sosl_uom_id, sosl.sosl_cur_id, sosl.sosl_exchange_rate, sosl.sosl_tax_id,
                            cc.cc_code AS sosl_cc_code, uom.uom_code AS sosl_uom_code, cur.cur_iso AS sosl_cur_iso, 
                            tax.tax_name AS sosl_tax_name, rel.rel_name AS sosl_relation, tax.tax_percent
                    FROM sales_order_sales AS sosl INNER JOIN
                            cost_code AS cc ON cc.cc_id = sosl.sosl_cc_id INNER JOIN
                            unit AS uom ON uom.uom_id = sosl.sosl_uom_id INNER JOIN
                            currency AS cur ON cur.cur_id = sosl.sosl_cur_id INNER JOIN
                            (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t left OUTER join
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail 
                                where td_active = 'Y' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON tax.tax_id = sosl.sosl_tax_id INNER JOIN
                            relation AS rel ON rel.rel_id = sosl.sosl_rel_id" . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
