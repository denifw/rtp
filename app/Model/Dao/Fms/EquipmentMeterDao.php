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

use App\Frame\Exceptions\Message;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table equipment_meter.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class EquipmentMeterDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'eqm_id', 'eqm_eq_id', 'eqm_date', 'eqm_meter', 'eqm_source'
    ];

    /**
     * Base dao constructor for equipment_meter.
     *
     */
    public function __construct()
    {
        parent::__construct('equipment_meter', 'eqm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table equipment_meter.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'eqm_date',
            'eqm_source',
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
        $wheres[] = '(eqm.eqm_id = ' . $referenceValue . ')';
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
     * @param array $wheres    To store the list condition query.
     * @param array $orderList To store the list for sortir.
     * @param int   $limit     To store the limit of the data.
     * @param int   $offset    To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT eqm.eqm_id, eqm.eqm_eq_id, eqm.eqm_date, eqm.eqm_meter, eqm.eqm_source
                  FROM   equipment_meter AS eqm' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get min max meter
     *
     * @param int    $idEqu
     * @param string $eqmDate
     * @param string $type
     *
     * @return array
     */
    public static function getMinMaxByIdEqAndDate(int $idEqu, string $eqmDate, string $type): array
    {
        if (empty($idEqu) === true || empty($eqmDate) === true) {
            Message::throwMessage('Require Equipment Id and Date');
        } else {
            $result = [
                'eqm_meter' => null
            ];
            $query = '';
            if ($type === 'min') {
                $query = "SELECT eqm_eq_id, MAX(eqm_meter) AS eqm_meter
                      FROM   equipment_meter 
                      WHERE  eqm_eq_id = $idEqu AND 
                             eqm_date <=  '$eqmDate' AND eqm_deleted_on IS NULL
                      GROUP BY eqm_eq_id
                      LIMIT 1 OFFSET 0";
            } elseif ($type === 'max') {
                $query = "SELECT eqm_eq_id, MIN(eqm_meter) AS eqm_meter
                      FROM   equipment_meter 
                      WHERE  eqm_eq_id = $idEqu AND 
                             eqm_date >  '$eqmDate' AND eqm_deleted_on IS NULL
                      GROUP BY eqm_eq_id
                      LIMIT 1 OFFSET 0";
            } else {
                Message::throwMessage('Invalid type');
            }
            $sqlResults = DB::select($query);
            if (\count($sqlResults) === 1) {
                $result = DataParser::objectToArray($sqlResults[0]);
            }
        }

        return $result;
    }


}
