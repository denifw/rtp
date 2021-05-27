<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_transfer_goods.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobStockTransferGoodsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jtg_id', 'jtg_jtr_id', 'jtg_gd_id', 'jtg_gdu_id', 'jtg_quantity', 'jtg_production_number'
    ];

    /**
     * Base dao constructor for job_transfer_goods.
     *
     */
    public function __construct()
    {
        parent::__construct('job_stock_transfer_goods', 'jtg', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_transfer_goods.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jtg_production_number'
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
        $wheres[] = '(jtg.jtg_id = ' . $referenceValue . ')';
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
        $query = 'SELECT  jtg.jtg_id, jtg.jtg_jtr_id, jtg.jtg_gd_id, jtg.jtg_gdu_id, jtg.jtg_quantity, jtg.jtg_production_number,
                          gd.gd_name, uom.uom_name AS gdu_name
                  FROM   job_stock_transfer_goods AS jtg INNER JOIN
                         goods AS gd ON gd.gd_id = jtg.jtg_gd_id INNER JOIN
                         goods_unit AS gdu ON gdu.gdu_id = jtg.jtg_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
    public static function loadDataForStockTransfer(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jtg.jtg_id, jtg.jtg_jtr_id, jtg.jtg_gd_id, jtg.jtg_gdu_id, jtg.jtg_quantity, 
                          jtg.jtg_production_number, jtg.jtg_production_number AS jtg_production_batch,
                          gd.gd_name AS jtg_gd_name,  gd.gd_sku AS jtg_sku, uom.uom_name AS jtg_unit, 
                          gdc.gdc_name AS jtg_gdc_name, br.br_name AS jtg_br_name
                  FROM   job_stock_transfer_goods AS jtg INNER JOIN
                         goods AS gd ON gd.gd_id = jtg.jtg_gd_id INNER JOIN
                         goods_unit AS gdu ON gdu.gdu_id = jtg.jtg_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                         goods_category AS gdc ON gdc.gdc_id = gd.gd_gdc_id LEFT OUTER JOIN
                         brand AS br ON br.br_id = gd.gd_br_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }



}
