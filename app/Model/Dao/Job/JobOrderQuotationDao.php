<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Job;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_order_quotation.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobOrderQuotationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'joq_id', 'joq_jo_id', 'joq_qt_id'
    ];

    /**
     * Base dao constructor for job_order_quotation.
     *
     */
    public function __construct()
    {
        parent::__construct('job_order_quotation', 'joq', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_order_quotation.
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
        $wheres[] = '(joq_id = ' . $referenceValue . ')';
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
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT joq.joq_id, joq.joq_jo_id, joq.joq_qt_id,
                         qt.qt_number as joq_qt_number, rel.rel_name as joq_rel_name,
                         dl.dl_name as joq_deal_name
                  FROM job_order_quotation AS joq
                  INNER JOIN quotation AS qt ON qt.qt_id = joq.joq_qt_id
                  INNER JOIN relation AS rel ON rel.rel_id = qt.qt_rel_id
                  LEFT OUTER JOIN deal as dl ON dl.dl_id = qt.qt_dl_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

}
