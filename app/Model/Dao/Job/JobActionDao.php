<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_action.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobActionDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jac_id',
        'jac_jo_id',
        'jac_ac_id',
        'jac_start_by',
        'jac_start_on',
        'jac_end_by',
        'jac_end_on',
        'jac_order',
        'jac_remark',
        'jac_active',
        'jac_start_date',
        'jac_start_time',
        'jac_end_date',
        'jac_end_time',
    ];

    /**
     * Base dao constructor for job_action.
     *
     */
    public function __construct()
    {
        parent::__construct('job_action', 'jac', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_action.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jac_start_on',
            'jac_end_on',
            'jac_remark',
            'jac_active',
            'jac_start_date',
            'jac_start_time',
            'jac_end_date',
            'jac_end_time',
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
        $where = [];
        $where[] = '(jac.jac_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $joId To store the job id value of the table.
     * @param int $acId To store the action id value of the table.
     *
     * @return array
     */
    public static function getByJoIdAndActionId($joId, $acId): array
    {
        $where = [];
        $where[] = '(jac.jac_jo_id = ' . $joId . ')';
        $where[] = '(jac.jac_ac_id = ' . $acId . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $joId To store the job id value of the table.
     * @param string $actionCode To store the action id value of the table.
     *
     * @return array
     */
    public static function getByJoIdAndActionCode(int $joId, string $actionCode): array
    {
        $where = [];
        $where[] = SqlHelper::generateNumericCondition('jac.jac_jo_id', $joId);
        $where[] = SqlHelper::generateStringCondition('ac.ac_code', $actionCode);
        $data = self::loadData($where);
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
        $where[] = "(jac.jac_active = 'Y')";
        $where[] = '(jac.jac_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get the last action on job order
     *
     * @param int $joId To store the job reference.
     *
     * @return array
     */
    public static function getLastActiveActionByJobId($joId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = '(jac.jac_updated_on IS NOT NULL)';
        $wheres[] = "(jac.jac_active = 'Y')";
        $wheres[] = '(jae.jae_deleted_on IS NULL)';
        $wheres[] = "(jae.jae_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_code as jac_action, jac.jac_updated_on, jae.jae_id,
                      jae.jae_description, jae.jae_created_on, ac.ac_srt_id, ac.ac_style as jac_style,
                      jae.jae_date, jae.jae_time
                FROM job_action as jac INNER JOIN
                action as ac ON jac.jac_ac_id = ac.ac_id INNER JOIN
                job_action_event as jae on jac.jac_id = jae.jae_jac_id ' . $strWhere;
        $query .= ' GROUP BY jac.jac_id, jac.jac_ac_id, ac.ac_code, jac.jac_updated_on, jae.jae_id,
                    jae.jae_description, jae.jae_created_on, ac.ac_srt_id, ac.ac_style, jae.jae_date, jae.jae_time';
        $query .= ' ORDER BY jac.jac_updated_on DESC, jae.jae_id DESC';
        $query .= ' LIMIT 1 OFFSET 0';

        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0], [
                'jac_id',
                'jac_ac_id',
                'jac_action',
                'jac_updated_on',
                'jae_description',
                'ac_srt_id',
                'jac_style',
                'jae_date',
                'jae_time',
            ]);
        }

        return $result;

    }

    /**
     * Function to get the last action on job order
     *
     * @param int $joId To store the job reference.
     *
     * @return array
     */
    public static function getLastActiveActionByJobIdManually($joId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = "(jac.jac_active = 'Y')";
        $wheres[] = '(jae.jae_deleted_on IS NULL)';
        $wheres[] = "(jae.jae_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_code as jac_action, jac.jac_updated_on, jae.jae_id,
                      jae.jae_description, jae.jae_created_on, ac.ac_srt_id, ac.ac_style as jac_style,
                      jae.jae_date, jae.jae_time, jac.jac_end_date
                FROM job_action as jac INNER JOIN
                action as ac ON jac.jac_ac_id = ac.ac_id INNER JOIN
                job_action_event as jae on jac.jac_id = jae.jae_jac_id ' . $strWhere;
        $query .= ' GROUP BY jac.jac_id, jac.jac_ac_id, ac.ac_code, jac.jac_updated_on, jae.jae_id,
                    jae.jae_description, jae.jae_created_on, ac.ac_srt_id, ac.ac_style, jae.jae_date, jae.jae_time, jac.jac_end_date';
        $query .= ' ORDER BY jac.jac_order';

        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $temp = DataParser::arrayObjectToArray($sqlResult);
            foreach ($temp as $row) {
                if (empty($result) === true && empty($row['jac_end_date']) === true) {
                    $result = $row;
                }
            }
            if (empty($result) === true) {
                foreach ($temp as $row) {
                    if (empty($row['jac_end_date']) === false) {
                        $result = $row;
                    }
                }

            }
        }

        return $result;

    }

    /**
     * Function to get the next job action by job id
     *
     * @param int $joId To store the job reference.
     *
     * @return array
     */
    public static function loadNextActionByJobIdManually($joId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = '(jac.jac_end_on IS NULL)';
        $wheres[] = '(jac.jac_deleted_on IS NULL)';
        $wheres[] = "(jac.jac_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_code as jac_action, ac.ac_srt_id as jac_service_term, jac.jac_start_on,
                        jac.jac_end_on, ac.ac_style as jac_style, jac.jac_start_date, jac.jac_start_time, jac.jac_end_date, jac.jac_end_time
                FROM job_action as jac INNER JOIN
                action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        $query .= ' ORDER BY jac.jac_order';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0], [
                'jac_id',
                'jac_ac_id',
                'jac_service_term',
                'jac_action',
                'jac_start_on',
                'jac_end_on',
                'jac_style',
                'jac_start_date',
                'jac_start_time',
                'jac_end_date',
                'jac_end_time',
            ]);
        }

        return $result;

    }


    /**
     * Function to get the job action by job id
     *
     * @param int $joId To store the job reference.
     *
     * @return array
     */
    public static function loadDataByJobId($joId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = '(jac.jac_deleted_on IS NULL)';
        $wheres[] = "(jac.jac_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_code as jac_action, ac.ac_srt_id as jac_service_term, jac.jac_start_on, jac.jac_end_on,
                  jac.jac_start_date, jac.jac_start_time, jac.jac_end_date, jac.jac_end_time
                FROM job_action as jac INNER JOIN
                action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        $query .= ' ORDER BY jac.jac_order';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, [
                'jac_id',
                'jac_ac_id',
                'jac_service_term',
                'jac_action',
                'jac_start_on',
                'jac_end_on',
                'jac_start_date',
                'jac_start_time',
                'jac_end_date',
                'jac_end_time',
            ]);
        }

        return $result;

    }

    /**
     * Function to get the next job action by job id
     *
     * @param int $joId To store the job reference.
     *
     * @return array
     */
    public static function loadNextActionByJobId($joId): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = '(jac.jac_end_on IS NULL)';
        $wheres[] = '(jac.jac_deleted_on IS NULL)';
        $wheres[] = "(jac.jac_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_code as jac_action, ac.ac_srt_id as jac_service_term, jac.jac_start_on,
                        jac.jac_end_on, ac.ac_style as jac_style, jac.jac_start_date, jac.jac_start_time, jac.jac_end_date, jac.jac_end_time
                FROM job_action as jac INNER JOIN
                action as ac ON jac.jac_ac_id = ac.ac_id' . $strWhere;
        $query .= ' ORDER BY jac.jac_order';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0], [
                'jac_id',
                'jac_ac_id',
                'jac_service_term',
                'jac_action',
                'jac_start_on',
                'jac_end_on',
                'jac_style',
                'jac_start_date',
                'jac_start_time',
                'jac_end_date',
                'jac_end_time',
            ]);
        }

        return $result;

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
        $query = 'SELECT jac.jac_id, jac.jac_ac_id, ac.ac_description as jac_action, jac.jac_start_by,
                        u1.us_name as jac_start_by, jac.jac_start_on, jac.jac_end_by, u2.us_name as jac_end_by, jac.jac_end_on,
                        jac.jac_order, jac.jac_remark, jac.jac_active, jac.jac_start_date, jac.jac_start_time, jac.jac_end_date, jac.jac_end_time
                        FROM job_action as jac INNER JOIN
                        action as ac ON jac.jac_ac_id = ac.ac_id LEFT OUTER JOIN
                         users as u1 ON jac.jac_start_by = u1.us_id LEFT OUTER JOIN
                          users as u2 ON jac.jac_end_by = u2.us_id ' . $strWhere;
        $query .= ' ORDER BY jac.jac_order';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, [
            'jac_action',
            'jac_start_by',
            'jac_end_by',
        ]));

    }


}
