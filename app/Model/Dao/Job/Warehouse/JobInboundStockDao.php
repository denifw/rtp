<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_inbound_stock.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundStockDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jis_id',
        'jis_jid_id',
        'jis_quantity',
    ];

    /**
     * Base dao constructor for job_inbound_stock.
     *
     */
    public function __construct()
    {
        parent::__construct('job_inbound_stock', 'jis', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_inbound_stock.
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
        $where = [];
        $where[] = '(jis_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $where = [];
        $where[] = '(jis_id = ' . $referenceValue . ')';
        $where[] = '(jis_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(jis_active = 'Y')";
        $where[] = '(jis_deleted_on IS NULL)';

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
        $query = 'SELECT jis_id
                        FROM job_inbound_stock' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);

    }


    /**
     * Function to get all record.
     *
     * @param int $jidId To store the offset of the data to apply limit.
     *
     * @return float
     */
    public static function getStockByJobInboundDetailId($jidId): float
    {
        $wheres = [];
        $wheres[] = '(jis_jid_id = ' . $jidId . ')';
        $strWhere = '(jis_deleted_on IS NULL)';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  from job_inbound_stock ' . $strWhere;
        $query .= ' GROUP BY jis_jid_id ';
        $result = DB::select($query);
        if (\count($result) === 1) {
            return (float)DataParser::objectToArray($result[0], ['jis_stock'])['jis_stock'];
        }

        return 0;

    }


}
