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
class SalesOrderServiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sos_id',
        'sos_so_id',
        'sos_srt_id',
    ];

    /**
     * Base dao constructor for sales_order.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_service', 'sos', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order.
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
        $result = [];
        $where = [];
        $where[] = '(sos.sos_id = ' . $referenceValue . ')';
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
        $where[] = '(sos.sos_so_id = ' . $soId . ')';
        $where[] = '(sos.sos_deleted_on IS NULL)';
        return self::loadData($where);
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
        $query = 'SELECT sos.sos_id, sos.sos_so_id, sos.sos_srt_id, srt.srt_name as sos_srt_name, 
                        srv.srv_id as sos_srv_id, srv.srv_name as sos_srv_name, srt.srt_order, srt.srt_route as sos_jo_route
                FROM sales_order_service as sos INNER JOIN
                    service_term as srt on sos.sos_srt_id = srt.srt_id INNER JOIN 
                    service as srv ON srv.srv_id = srt.srt_srv_id ' . $strWhere;
        $query .= ' ORDER BY srv.srv_id, srt.srt_order, sos.sos_srt_id, sos.sos_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }
}
