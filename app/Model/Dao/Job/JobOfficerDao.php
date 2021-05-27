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
 * Class to handle data access object for table job_officer.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOfficerDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'joo_id',
        'joo_jo_id',
        'joo_us_id',
    ];

    /**
     * Base dao constructor for job_officer.
     *
     */
    public function __construct()
    {
        parent::__construct('job_officer', 'joo', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_officer.
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
     * @param int $joId To store the reference value of the table.
     * @param int $usId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobOrderAndUser(int $joId, int $usId): array
    {
        $where = [];
        $where[] = SqlHelper::generateNumericCondition('joo.joo_jo_id', $joId);
        $where[] = SqlHelper::generateNumericCondition('joo.joo_us_id', $usId);
        $where[] = SqlHelper::generateNullCondition('joo.joo_deleted_on');
        $data = self::loadData($where);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
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
        $where[] = '(joo.joo_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = '(joo.joo_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get data by job id.
     *
     * @param int $joId To store the job id reference.
     * @param int $ssId To store the job id reference.
     *
     * @return array
     */
    public static function loadByJobOrderIdAndSystemSettings(int $joId, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('joo.joo_jo_id', $joId);
        $wheres[] = SqlHelper::generateNumericCondition('ump.ump_ss_id', $ssId);
        $wheres[] = SqlHelper::generateNullCondition('joo.joo_deleted_on');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT joo.joo_id, joo.joo_jo_id, joo.joo_us_id, us.us_name as joo_user,
                        us.us_username as joo_username, ump.ump_rel_id as joo_rel_id, rel.rel_name as joo_relation, ump_cp_id as joo_cp_id
                FROM job_officer as joo
                    INNER JOIN users as us ON joo.joo_us_id = us.us_id
                    INNER JOIN user_mapping as ump ON us.us_id = ump.ump_us_id
                    INNER JOIN relation as rel ON ump.ump_rel_id = rel.rel_id' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
        $query = 'SELECT joo.joo_id, joo.joo_jo_id, joo.joo_us_id, us.us_name as joo_user, us.us_username as joo_username
                        FROM job_officer as joo INNER JOIN
                        users as us ON joo.joo_us_id = us.us_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
