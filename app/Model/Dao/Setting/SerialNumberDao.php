<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Setting;

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
        'sn_srv_id',
        'sn_srt_id',
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
     * Base dao constructor for serial_number.
     *
     */
    public function __construct()
    {
        parent::__construct('serial_number', 'sn', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table serial_number.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sn_relation',
            'sn_format',
            'sn_separator',
            'sn_prefix',
            'sn_yearly',
            'sn_monthly',
            'sn_postfix',
            'sn_active',
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
     * @param int $ssId To store the reference of system setting.
     *
     * @return array
     */
    public static function getByReferenceAndSystemSetting($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(sn.sn_id = ' . $referenceValue . ')';
        $wheres[] = '(sn.sn_ss_id = ' . $ssId . ')';

        return self::loadData($wheres)[0];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(sn.sn_active = 'Y')";
        $where[] = '(sn.sn_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT sn.sn_id, sn.sn_sc_id, sc.sc_code as sn_serial_code, sc.sc_description as sn_sc_description, sn.sn_ss_id, sn.sn_relation, sn.sn_separator,
                        sn.sn_prefix, sn.sn_yearly, sn.sn_monthly, sn.sn_length, sn.sn_increment, sn.sn_postfix, sn.sn_active,
                        sn.sn_of_id, o.of_name as sn_office, sn.sn_srv_id, sn.sn_srt_id, srv.srv_name as sn_service,
                        srt.srt_name as sn_service_term, sn.sn_format, sn.sn_deleted_on, sn.sn_deleted_reason,
                        ud.us_name as sn_deleted_by
                        FROM serial_number as sn
                            INNER JOIN serial_code as sc ON sn.sn_sc_id = sc.sc_id
                            LEFT OUTER JOIN service as srv ON sn.sn_srv_id = srv.srv_id
                            LEFT OUTER JOIN service_term as srt ON sn.sn_srt_id = srt.srt_id
                            LEFT OUTER JOIN office as o ON sn.sn_of_id = o.of_id
                            LEFT OUTER JOIN users as ud ON sn.sn_deleted_by = ud.us_id' . $strWhere;
        $query .= ' ORDER BY sn.sn_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);


        return DataParser::arrayObjectToArray($result);

    }


}
