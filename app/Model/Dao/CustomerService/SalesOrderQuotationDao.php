<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table sales_order_quotation.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderQuotationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'soq_id', 'soq_so_id', 'soq_qt_id'
    ];

    /**
     * Base dao constructor for sales_order_quotation.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_quotation', 'soq', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order_quotation.
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
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soq.soq_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by so id
     *
     * @param int $soId To store the reference value of sales order.
     *
     * @return array
     */
    public static function loadDataBySoId(int $soId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soq.soq_so_id', $soId);
        $wheres[] = SqlHelper::generateNullCondition('soq.soq_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
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
        $query = 'SELECT soq.soq_id, soq.soq_so_id, soq.soq_qt_id,
                         qt.qt_number as soq_qt_number, rel.rel_name as soq_rel_name, dl.dl_number as soq_dl_number,
                         dl.dl_name as soq_deal_name, qt.qt_start_date as soq_start_date,
                        qt.qt_end_date as soq_end_date, qt.qt_commodity as soq_commodity
                  FROM sales_order_quotation as soq
                      INNER JOIN quotation as qt on qt.qt_id = soq.soq_qt_id
                      INNER JOIN relation as rel on rel.rel_id = qt.qt_rel_id
                      LEFT OUTER JOIN deal as dl on dl.dl_id = qt.qt_dl_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY qt.qt_number, soq.soq_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (soq_id)) AS total_rows
                  FROM sales_order_quotation as soq
                  INNER JOIN quotation as qt on qt.qt_id = soq.soq_qt_id' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'soq_qt_number', 'soq_id');
    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadAvailableQuotationForSo(array $wheres = []): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT qt.qt_id, qt.qt_number, qt.qt_start_date, qt.qt_end_date,
                           qt.qt_commodity, qt.qt_dl_id, dl.dl_number
                    FROM quotation as qt
                        LEFT OUTER JOIN deal as dl ON qt.qt_dl_id = dl.dl_id ' . $strWhere;
        $query .= ' ORDER BY qt.qt_number, qt.qt_id';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


}
