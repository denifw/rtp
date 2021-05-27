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
 * Class to handle data access object for table service_order_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrderDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'svd_id', 'svd_svo_id', 'svd_svt_id', 'svd_est_cost', 'svd_act_cost', 'svd_remark'
    ];

    /**
     * Base dao constructor for service_order_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('service_order_detail', 'svd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_order_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'svd_active',
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
        $wheres[] = '(svd.svd_id = ' . $referenceValue . ')';
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
        $query = 'SELECT svd.svd_id, svd.svd_svo_id, svd.svd_svt_id, svd.svd_est_cost, svd.svd_remark,
                         svt.svt_name AS svd_svt_name
                  FROM   service_order_detail AS svd INNER JOIN
                         service_order AS svo ON svo.svo_id = svd.svd_svo_id INNER JOIN
                         service_task AS svt ON svt.svt_id = svd.svd_svt_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
