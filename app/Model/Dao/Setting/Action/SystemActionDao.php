<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Setting\Action;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table system_action.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Action
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemActionDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sac_id',
        'sac_ss_id',
        'sac_srt_id',
        'sac_ac_id',
        'sac_order',
    ];

    /**
     * Base dao constructor for system_action.
     *
     */
    public function __construct()
    {
        parent::__construct('system_action', 'sac', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table system_action.
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
        $wheres[] = '(sac.sac_id = ' . $referenceValue . ')';

        return self::loadData($wheres)[0];
    }

    /**
     * Function to get by service term id.
     *
     * @param int $srtId To store the service term id.
     * @param int $ssId To store the service term id.
     *
     * @return array
     */
    public static function getByServiceTermIdAndSystemId($srtId, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(sac.sac_srt_id = ' . $srtId . ')';
        $wheres[] = '(sac.sac_ss_id = ' . $ssId . ')';
        $where[] = '(sac.sac_deleted_on IS NULL)';

        return self::loadData($wheres);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(sac.sac_id = ' . $referenceValue . ')';
        $wheres[] = '(sac.sac_ss_id = ' . $systemSettingValue . ')';

        return self::loadData($wheres)[0];
    }


    /**
     * Function to get last order data.
     *
     * @param int $systemSettingValue To store the system setting value.
     * @param int $srtId To store the service term id.
     *
     * @return int
     */
    public static function getLastOrderData($systemSettingValue, $srtId): int
    {
        $result = 0;
        $wheres = [];
        $wheres[] = '(sac_ss_id = ' . $systemSettingValue . ')';
        $wheres[] = '(sac_srt_id = ' . $srtId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sac_order
                      FROM system_action ' . $strWhere;
        $query .= ' ORDER BY sac_order DESC';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::objectToArray($sqlResult[0], ['sac_order'])['sac_order'];
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
        $where[] = '(sac.sac_deleted_on IS NULL)';

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
        $query = 'SELECT sac.sac_id, sac.sac_ac_id, ac.ac_description as sac_action, sac.sac_order,
                        sac.sac_srt_id, srt.srt_name as sac_service_term, srv.srv_id as sac_srv_id, srv.srv_name as sac_service,
                        ac.ac_order, ac.ac_id
                        FROM system_action as sac INNER JOIN
                        action as ac ON sac.sac_ac_id = ac.ac_id INNER JOIN
                        service_term as srt ON ac.ac_srt_id = srt.srt_id INNER JOIN
                         service as srv ON srt.srt_srv_id = srv.srv_id ' . $strWhere;
        $query .= ' ORDER BY sac.sac_srt_id, sac.sac_order, ac.ac_order, ac.ac_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, [
            'sac_action',
            'sac_service',
            'sac_srv_id',
            'sac_service_term',
        ]));
    }
}
