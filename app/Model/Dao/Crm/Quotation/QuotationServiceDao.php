<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm\Quotation;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table quotation_service.
 *
 * @package    app
 * @subpackage Model\Dao\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class QuotationServiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'qs_id',
        'qs_qt_id',
        'qs_srv_id',
    ];

    /**
     * Base dao constructor for quotation_service.
     *
     */
    public function __construct()
    {
        parent::__construct('quotation_service', 'qs', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table quotation_service.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder();
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
        $wheres[] = SqlHelper::generateNumericCondition('qs_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by quotation id
     *
     * @param int $qtId To store the id of quotation table.
     *
     * @return array
     */
    public static function getByQuotationId($qtId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('qs.qs_qt_id', $qtId);
        $wheres[] = '(qs.qs_deleted_on IS NULL)';
        return self::loadData($wheres, ['qs.qs_srv_id', 'qs.qs_id']);
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
        $query = 'SELECT qs.qs_id, qs.qs_qt_id, qs.qs_srv_id, srv.srv_name as qs_service, srv.srv_code as qs_srv_code
                        FROM quotation_service as qs
                            INNER JOIN service as srv ON qs.qs_srv_id = srv.srv_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY srv.srv_id, qs.qs_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get data for check box input.
     *
     * @param int $ssId To store the reference of system setting table.
     * @param int $qtId To store the reference of quotation table.
     *
     * @return array
     */
    public static function loadDataForCheckBoxInput($ssId, $qtId = 0): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ssr.ssr_ss_id', $ssId);
        $wheres[] = '(ssr.ssr_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT srv.srv_id, srv.srv_name, qs.qs_id,
                       (CASE WHEN qs.qs_active IS NULL THEN 'N' ELSE qs.qs_active END) as qs_active
                FROM system_service as ssr
                     INNER JOIN service as srv ON ssr.ssr_srv_id = srv.srv_id
                     LEFT OUTER JOIN (SELECT qs_id, qs_srv_id, (CASE WHEN qs_deleted_on IS NULL THEN 'Y' ELSE 'N' END) as qs_active
                                      FROM quotation_service
                                      WHERE qs_qt_id = " . $qtId . ") as qs ON srv.srv_id = qs.qs_srv_id" . $strWhere;
        $query .= ' GROUP BY srv.srv_id, srv.srv_name, qs.qs_id, qs.qs_active';
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
