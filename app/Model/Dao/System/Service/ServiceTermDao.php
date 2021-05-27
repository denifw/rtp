<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Service;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table service_term.
 *
 * @package    app
 * @subpackage Model\Dao\System\Service
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceTermDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'srt_id',
        'srt_name',
        'srt_description',
        'srt_srv_id',
        'srt_container',
        'srt_color',
        'srt_image',
        'srt_route',
        'srt_active',
        'srt_order',
        'srt_load',
        'srt_unload',
        'srt_pol',
        'srt_pod',
    ];

    /**
     * Base dao constructor for service_term.
     *
     */
    public function __construct()
    {
        parent::__construct('service_term', 'srt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_term.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'srt_name',
            'srt_description',
            'srt_container',
            'srt_color',
            'srt_image',
            'srt_route',
            'srt_load',
            'srt_unload',
            'srt_pol',
            'srt_pod',
            'srt_active',
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
        $wheres[] = SqlHelper::generateNumericCondition('srt.srt_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $route to store the route parameter.
     *
     * @return array
     */
    public static function getByRoute(string $route): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLowerStringCondition('srt.srt_route', $route);
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
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT srt.srt_id, srt.srt_srv_id, srt.srt_name, srt.srt_description, srt.srt_container,
                        srt.srt_active, srv.srv_name as srt_service, srt.srt_color, srt.srt_image, srt.srt_route, srt.srt_order,
                        srt.srt_load, srt.srt_unload, srt.srt_pol, srt.srt_pod
                        FROM service_term AS srt INNER JOIN
                             service AS srv ON srv.srv_id = srt.srt_srv_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY srv.srv_id, srt.srt_order, srt.srt_name, srt.srt_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all record.
     *
     * @param int $srvId To store the service id.
     *
     * @return array
     */
    public static function getIdByService($srvId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('srt.srt_srv_id', $srvId);
        return self::loadData($wheres);

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
        $query = 'SELECT count(DISTINCT (srt.srt_id)) AS total_rows
                     FROM service_term AS srt INNER JOIN
                             service AS srv ON srv.srv_id = srt.srt_srv_id' . $strWhere;
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
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 30): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'srt_description', 'srt_id');
    }

}
