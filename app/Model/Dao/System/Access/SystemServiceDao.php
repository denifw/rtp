<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\System\Access;

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
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ssr.ssr_id', $referenceValue);
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
     * @param string $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $referenceValue, string $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ssr.ssr_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('ssr.ssr_ss_id', $systemSettingValue);
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
        $query = 'SELECT ssr.ssr_id, ssr.ssr_ss_id, ssr.ssr_srv_id, ssr.ssr_active, srv.srv_name, srv.srv_code, ss.ss_relation
                        FROM system_service AS ssr
                            INNER JOIN service AS srv ON srv.srv_id = ssr.ssr_srv_id
                            INNER JOIN system_setting AS ss on ssr.ssr_ss_id = ss.ss_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ss.ss_relation, srv.srv_name, ssr.ssr_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);

    }

}
