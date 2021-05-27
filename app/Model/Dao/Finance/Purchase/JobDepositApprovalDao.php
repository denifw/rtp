<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Purchase;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_deposit_approval.
 *
 * @package    app
 * @subpackage Model\Dao\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDepositApprovalDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jda_id',
        'jda_jd_id',
        'jda_reject_reason',
    ];

    /**
     * Base dao constructor for job_deposit_approval.
     *
     */
    public function __construct()
    {
        parent::__construct('job_deposit_approval', 'jda', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_deposit_approval.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jda_reject_reason',
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
        $wheres[] = '(jda.jda_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jdId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJdId($jdId): array
    {
        $wheres = [];
        $wheres[] = '(jda.jda_jd_id = ' . $jdId . ')';
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
        $query = 'SELECT jda.jda_id, jda.jda_reject_reason, jda.jda_created_on, uc.us_name as jda_created_by,
                    jda.jda_deleted_on, ud.us_name as jda_deleted_by
                FROM job_deposit_approval as jda LEFT OUTER JOIN
                        users as uc ON jda.jda_created_by = uc.us_id LEFT OUTER JOIN
                        users as ud ON jda.jda_deleted_by = ud.us_id ' . $strWhere;
        $query .= ' ORDER BY jda.jda_deleted_on DESC';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
