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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table renewal_reminder.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalReminderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rnrm_id', 'rnrm_ss_id', 'rnrm_eq_id', 'rnrm_rnt_id',
        'rnrm_interval', 'rnrm_interval_period',
        'rnrm_threshold', 'rnrm_threshold_period',
        'rnrm_expiry_date', 'rnrm_remark'
    ];

    /**
     * Base dao constructor for renewal_reminder.
     *
     */
    public function __construct()
    {
        parent::__construct('renewal_reminder', 'rnrm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table renewal_reminder.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rnrm_interval_period',
            'rnrm_threshold_period',
            'rnrm_expiry_date', 'rnrm_remark'
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
        $wheres[] = '(rnrm.rnrm_id = ' . $referenceValue . ')';
        $wheres[] = '(rnrm.rnrm_ss_id = ' . $systemSettingValue . ')';
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
        $query = 'SELECT rnrm.rnrm_id, rnrm.rnrm_ss_id, rnrm.rnrm_eq_id, rnrm.rnrm_rnt_id, rnrm.rnrm_interval,
                         rnrm.rnrm_interval_period, rnrm.rnrm_threshold, rnrm.rnrm_threshold_period,
                         rnrm.rnrm_expiry_date, rnrm.rnrm_expiry_threshold_date, rnrm.rnrm_remark,
                         rnt.rnt_name AS rnrm_rnt_name, eg.eg_name || \' - \' || eq.eq_description AS rnrm_eq_name,
                         rnf.rnf_id AS rnrm_rnf_id
                  FROM renewal_reminder AS rnrm INNER JOIN
                       equipment AS eq ON eq.eq_id = rnrm.rnrm_eq_id INNER JOIN
                       equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                       renewal_type AS rnt ON rnt.rnt_id = rnrm.rnrm_rnt_id LEFT OUTER JOIN
                       renewal_fulfillment AS rnf ON rnf.rnf_rnrm_id = rnrm.rnrm_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param int   $eqId  To store equipment id.
     * @param int   $rntId To store renewal type id.
     *
     * @return array
     */
    public static function getByEqIdRntId(int $eqId, int $rntId): array
    {
        $wheres = [];
        $wheres[] = '(rnrm.rnrm_eq_id = ' . $eqId . ')';
        $wheres[] = '(rnrm.rnrm_rnt_id = ' . $rntId. ')';
        $wheres[] = '(rnrm.rnrm_deleted_on IS NULL)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT   rnrm.rnrm_id, rnrm.rnrm_ss_id, rnrm.rnrm_eq_id, rnrm.rnrm_rnt_id, rnrm.rnrm_interval,
                           rnrm.rnrm_interval_period, rnrm.rnrm_threshold, rnrm.rnrm_threshold_period,
                           rnrm.rnrm_expiry_date, rnrm.rnrm_expiry_threshold_date, rnrm.rnrm_remark,
                           eq.eq_description AS rnrm_eq_description,
                           rnt.rnt_name AS rnrm_rnt_name, eg.eg_name || \' - \' || eq.eq_description AS rnrm_eq_name
                  FROM     renewal_reminder AS rnrm INNER JOIN
                           equipment AS eq ON eq.eq_id = rnrm.rnrm_eq_id INNER JOIN
                           equipment_group AS eg ON eg.eg_id = eq.eq_eg_id INNER JOIN
                           renewal_type AS rnt ON rnt.rnt_id = rnrm.rnrm_rnt_id' . $strWhere;
        $sqlResult = DB::select($query);
        $results = DataParser::arrayObjectToArray($sqlResult);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;

    }


}
