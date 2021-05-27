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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table service.
 *
 * @package    app
 * @subpackage Model\Dao\System\Service
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'srv_id',
        'srv_name',
        'srv_code',
        'srv_active',
    ];

    /**
     * Base dao constructor for service.
     *
     */
    public function __construct()
    {
        parent::__construct('service', 'srv', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'srv_name',
            'srv_code',
            'srv_active',
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
        $wheres[] = "(srv_id = '$referenceValue')";
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $srvCode To store the srv code
     *
     * @return int
     */
    public static function getIdByCode(string $srvCode): int
    {
        $wheres = [];
        $wheres[] = "(srv_code = '" . $srvCode . "')";
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return (int)$data[0]['srv_id'];
        }

        return 0;
    }


    /**
     * Function to get trucking service
     *
     * @return array
     */
    public static function getServiceDelivery(): array
    {
        return self::getByCode('delivery');
    }

    /**
     * Function to get inklaring service
     *
     * @return array
     */
    public static function getServiceInklaring(): array
    {
        return self::getByCode('inklaring');
    }

    /**
     * Function to get warehouse service
     *
     * @return array
     */
    public static function getServiceWarehouse(): array
    {
        return self::getByCode('warehouse');
    }

    /**
     * Function to get data by reference value
     *
     * @param string $srvCode To store the srv code
     *
     * @return array
     */
    public static function getByCode(string $srvCode): array
    {
        $wheres = [];
        $wheres[] = "(srv_code = '" . $srvCode . "')";
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }


    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(srv_active = 'Y')";
        $where[] = '(srv_deleted_on IS NULL)';

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
        $query = 'SELECT srv_id, srv_name, srv_active, srv_code
                        FROM service' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

}
