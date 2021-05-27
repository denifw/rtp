<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\Master\Finance;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table cost_code.
 *
 * @package    app
 * @subpackage Model\Dao\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class CostCodeGroupDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ccg_id',
        'ccg_ss_id',
        'ccg_code',
        'ccg_name',
        'ccg_srv_id',
        'ccg_type',
        'ccg_active',
    ];

    /**
     * Base dao constructor for cost_code.
     *
     */
    public function __construct()
    {
        parent::__construct('cost_code_group', 'ccg', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table cost_code.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ccg_code',
            'ccg_name',
            'ccg_type',
            'ccg_active',
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
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(ccg_ss_id = ' . $systemSettingValue . ')';
        $wheres[] = '(ccg_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (\count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get all the active record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadActiveData(array $wheres = []): array
    {
        $wheres[] = "(ccg_active = 'Y')";
        $wheres[] = '(ccg_deleted_on IS NULL)';

        return self::loadData($wheres);
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
        $query = "SELECT ccg.ccg_id, ccg.ccg_ss_id, ccg.ccg_code, ccg.ccg_name, ccg.ccg_active, 
                ccg.ccg_srv_id, srv.srv_name as ccg_service, ccg.ccg_type
                FROM cost_code_group as ccg LEFT OUTER JOIN
                service as srv ON ccg.ccg_srv_id = srv.srv_id " . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }


    /**
     * Function to get all the active record.
     *
     * @param string $type To store the type
     *
     * @return string
     */
    public static function getTypeName(string $type): string
    {
        if ($type === 'S') {
            return 'Sales';
        }
        if ($type === 'P') {
            return 'Purchase';
        }
        if ($type === 'D') {
            return 'Deposit';
        }
        return 'Reimburse';
    }

}
