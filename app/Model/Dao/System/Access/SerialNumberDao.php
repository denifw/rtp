<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table serial_number.
 *
 * @package    app
 * @subpackage Model\Dao\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialNumberDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sn_id',
        'sn_sc_id',
        'sn_ss_id',
        'sn_of_id',
        'sn_relation',
        'sn_format',
        'sn_separator',
        'sn_prefix',
        'sn_yearly',
        'sn_monthly',
        'sn_length',
        'sn_increment',
        'sn_postfix',
        'sn_active',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'sn_length',
        'sn_increment',
    ];

    /**
     * Base dao constructor for serial_number.
     *
     */
    public function __construct()
    {
        parent::__construct('serial_number', 'sn', self::$Fields);
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('sn.sn_id', $referenceValue);

        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     * @param string $ssId To store the reference of system setting.
     *
     * @return array
     */
    public static function getByReferenceAndSystemSetting(string $referenceValue, string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('sn.sn_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('sn.sn_ss_id', $ssId);

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
     * @param array $orderBy To store the list condition query.
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
        $query = 'SELECT sn.sn_id, sn.sn_sc_id, sc.sc_code as sn_serial_code, sc.sc_description as sn_sc_description,
                            sn.sn_ss_id, sn.sn_relation, sn.sn_separator,
                        sn.sn_prefix, sn.sn_yearly, sn.sn_monthly, sn.sn_length, sn.sn_increment, sn.sn_postfix, sn.sn_active,
                        sn.sn_of_id, o.of_name as sn_office, sn.sn_format, sn.sn_deleted_on, sn.sn_deleted_reason,
                        ud.us_name as sn_deleted_by, ss.ss_relation as sn_system, ss.ss_rel_id as sn_rel_id
                        FROM serial_number as sn
                            INNER JOIN system_setting as ss ON sn.sn_ss_id = ss.ss_id
                            INNER JOIN serial_code as sc ON sn.sn_sc_id = sc.sc_id
                            LEFT OUTER JOIN office as o ON sn.sn_of_id = o.of_id
                            LEFT OUTER JOIN users as ud ON sn.sn_deleted_by = ud.us_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY ss.ss_relation, sn.sn_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);


        return DataParser::arrayObjectToArray($result);

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
        $query = 'SELECT count(DISTINCT (sn.sn_id)) AS total_rows
                   FROM serial_number as sn
                            INNER JOIN system_setting as ss ON sn.sn_ss_id = ss.ss_id
                            INNER JOIN serial_code as sc ON sn.sn_sc_id = sc.sc_id
                            LEFT OUTER JOIN office as o ON sn.sn_of_id = o.of_id
                            LEFT OUTER JOIN users as ud ON sn.sn_deleted_by = ud.us_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

}
