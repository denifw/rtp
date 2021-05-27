<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\System\Service;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table service.
 *
 * @package    app
 * @subpackage Model\Dao\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class SystemServiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ssr_id',
        'ssr_ss_id',
        'ssr_srv_id',
        'ssr_srt_id',
        'ssr_active',
    ];

    /**
     * Base dao constructor for service.
     *
     */
    public function __construct()
    {
        parent::__construct('system_service', 'ssr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ssr_active',
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
        $wheres[] = '(ssr.ssr_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
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
        $wheres[] = '(ssr.ssr_id = ' . $referenceValue . ')';
        $wheres[] = '(ssr.ssr_ss_id = ' . $systemSettingValue . ')';
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
        $where[] = "(ssr.ssr_active = 'Y')";
        $where[] = '(ssr.ssr_deleted_on IS NULL)';

        return self::loadData($where);

    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ssr.ssr_id, ssr.ssr_ss_id, ssr.ssr_srv_id, ssr.ssr_active, srv.srv_name, srv.srv_code, ss.ss_relation,
                            ssr.ssr_srt_id, srt.srt_name, (CASE WHEN a.total_action IS NULL THEN 0 ELSE a.total_action END) as total_action,
                            srt.srt_route, srt.srt_name, srt.srt_color, srt.srt_image, srt.srt_order 
                        FROM system_service AS ssr 
                            INNER JOIN service AS srv ON srv.srv_id = ssr.ssr_srv_id 
                            INNER JOIN system_setting AS ss on ssr.ssr_ss_id = ss.ss_id
                            INNER JOIN service_term as srt ON srt.srt_id = ssr.ssr_srt_id
                             LEFT OUTER JOIN (SELECT sac.sac_ss_id, ac.ac_srt_id, count(ac_id) as total_action
                                                FROM system_action as sac 
                                                    INNER JOIN action as ac ON sac.sac_ac_id = ac.ac_id
                                                WHERE (sac.sac_deleted_on IS NULL)
                                                GROUP BY sac.sac_ss_id, ac.ac_srt_id) as a ON ssr.ssr_ss_id = a.sac_ss_id AND srt.srt_id = a.ac_srt_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ss.ss_relation, srv.srv_name, srt.srt_order, ssr.ssr_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all active service by system settings id.
     *
     * @param int $ssId To store the reference of system settings.
     * @return array
     */
    public static function loadActiveService(int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ssr.ssr_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ssr.ssr_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('ssr.ssr_ss_id', $ssId);
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srv.srv_id, srv.srv_name, srv.srv_image, srv.srv_code 
                        FROM service AS srv 
                            INNER JOIN system_service AS ssr ON srv.srv_id = ssr.ssr_srv_id ' . $strWhere;
        $query .= ' GROUP BY srv.srv_id, srv.srv_name, srv.srv_image, srv.srv_code';
        $query .= ' ORDER BY srv.srv_name, srv.srv_id';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
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
        $query = 'SELECT count(DISTINCT (ssr.ssr_id)) AS total_rows
                        FROM system_service AS ssr 
                            INNER JOIN service AS srv ON srv.srv_id = ssr.ssr_srv_id 
                            INNER JOIN system_setting AS ss on ssr.ssr_ss_id = ss.ss_id
                            INNER JOIN service_term as srt ON srt.srt_id = ssr.ssr_srt_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], int $limit = 20): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ssr.ssr_id, srv.srv_name 
                        FROM system_service AS ssr 
                            INNER JOIN service AS srv ON srv.srv_id = ssr.ssr_srv_id 
                            INNER JOIN system_setting AS ss on ssr.ssr_ss_id = ss.ss_id
                            INNER JOIN service_term as srt ON srt.srt_id = ssr.ssr_srt_id' . $strWhere;
        $query .= ' GROUP BY ssr.ssr_id, srv.srv_name';
        $query .= ' ORDER BY srv.srv_name, ssr.ssr_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET 0';
        }
        $result = DB::select($query);
        $data = DataParser::arrayObjectToArray($result);
        return parent::doPrepareSingleSelectData(
            $data,
            'srv_name',
            'ssr_id');
    }

}
