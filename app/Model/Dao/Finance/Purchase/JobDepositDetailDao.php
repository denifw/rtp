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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_deposit_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDepositDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jdd_id',
        'jdd_jd_id',
        'jdd_jop_id',
        'jdd_cc_id',
        'jdd_description',
        'jdd_quantity',
        'jdd_rate',
        'jdd_uom_id',
        'jdd_exchange_rate',
        'jdd_cur_id',
        'jdd_tax_id',
        'jdd_total',
    ];

    /**
     * Base dao constructor for job_deposit_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_deposit_detail', 'jdd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_deposit_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
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
        $wheres[] = '(jdd.jdd_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jdId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJdId($jdId): array
    {
        $wheres = [];
        $wheres[] = '(jdd.jdd_jd_id = ' . $jdId . ')';
        $wheres[] = '(jdd.jdd_deleted_on IS NULL)';
        return self::loadData($wheres);
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
        $query = 'SELECT jdd.jdd_id, jdd.jdd_jd_id, jdd.jdd_jop_id, jdd.jdd_cc_id, jdd.jdd_description, jdd.jdd_rate, jdd.jdd_quantity,
                         jdd.jdd_uom_id, jdd.jdd_cur_id, jdd.jdd_exchange_rate, jdd.jdd_tax_id,
                         cc.cc_code AS jdd_cc_code, uom.uom_code AS jdd_uom_code, cur.cur_iso AS jdd_cur_iso, 
                         tax.tax_name AS jdd_tax_name, (CASE WHEN tax.tax_percent IS NULL THEN 0 ELSE tax.tax_percent END), jdd.jdd_total, ccg.ccg_type as jdd_type
                        FROM job_deposit_detail as jdd INNER JOIN
                        cost_code AS cc ON cc.cc_id = jdd.jdd_cc_id INNER JOIN
                             cost_code_group AS ccg ON cc.cc_ccg_id = ccg.ccg_id INNER JOIN
                             unit AS uom ON uom.uom_id = jdd.jdd_uom_id INNER JOIN
                             currency AS cur ON cur.cur_id = jdd.jdd_cur_id LEFT OUTER JOIN
                             (select t.tax_id, t.tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            from tax as t LEFT OUTER JOIN
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail 
                                where td_active = \'Y\' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id) AS tax ON jdd.jdd_tax_id = tax.tax_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
