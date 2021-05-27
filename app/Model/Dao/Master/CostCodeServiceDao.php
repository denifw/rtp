<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Master;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table cost_code_service.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class CostCodeServiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ccs_id',
        'ccs_cc_id',
        'ccs_srv_id',
        'ccs_active',
    ];

    /**
     * Base dao constructor for cost_code_service.
     *
     */
    public function __construct()
    {
        parent::__construct('cost_code_service', 'ccs', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cost_code_service.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ccs_active',
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
        $query = 'SELECT ccs.ccs_id, ccs.ccs_cc_id, ccs.ccs_srv_id, ccs.ccs_active, srv.srv_name
                        FROM cost_code_service AS ccs INNER JOIN
                             service AS srv ON srv.srv_id = ccs.ccs_srv_id
                        WHERE (ccs_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], \array_merge(self::$Fields, [
                'srv_name'
            ]));
        }

        return $result;
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(ccs_active = 'Y')";
        $where[] = '(ccs_deleted_on IS NULL)';

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
        $query = 'SELECT ccs.ccs_id, ccs.ccs_cc_id, ccs.ccs_srv_id, ccs.ccs_active, srv.srv_name
                        FROM cost_code_service AS ccs INNER JOIN
                             service AS srv ON srv.srv_id = ccs.ccs_srv_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, \array_merge(self::$Fields, [
            'srv_name'
        ]));

    }


}
