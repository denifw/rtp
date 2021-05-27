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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_action_event.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobActionEventDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jae_id',
        'jae_jac_id',
        'jae_description',
        'jae_sae_id',
        'jae_remark',
        'jae_active',
        'jae_date',
        'jae_time',
        'jae_doc_id',
    ];

    /**
     * Base dao constructor for job_action_event.
     *
     */
    public function __construct()
    {
        parent::__construct('job_action_event', 'jae', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_action_event.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jae_date',
            'jae_time',
            'jae_remark',
            'jae_active',
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
        $where[] = '(jae.jae_id = ' . $referenceValue . ')';

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
        $where[] = "(jae.jae_active = 'Y')";
        $where[] = '(jae.jae_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all active record.
     *
     * @param int $joId To store the job order reference
     *
     * @return array
     */
    public static function loadEventByJobId($joId): array
    {
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = "(jac.jac_active = 'Y')";
        $wheres[] = '(jac.jac_updated_on IS NOT NULL)';
        $wheres[] = '(jac.jac_deleted_on IS NULL)';
        $wheres[] = "(jae.jae_active = 'Y')";
        $wheres[] = '(jae.jae_deleted_on IS NULL)';

        $orders = [];
        $orders[] = 'jac.jac_updated_on DESC';
        $orders[] = 'jae.jae_created_on DESC';
        $orders[] = 'jae.jae_id';
        return self::loadData($wheres, $orders);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jae.jae_id, jae.jae_jac_id, jac.jac_ac_id, ac.ac_code as jae_action, jae.jae_description, (CASE WHEN jae.jae_remark IS NULL THEN jac.jac_remark ELSE jae.jae_remark END) as remark, jae.jae_created_by, 
                      us.us_name as jae_created_by, jae.jae_created_on, 
                      doc.doc_id, doc.doc_file_name, ss.ss_name_space, dct.dct_code, dcg.dcg_code, doc.doc_group_reference, doc.doc_type_reference, jae.jae_date, jae.jae_time
                FROM job_action_event as jae 
                    INNER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id 
                    INNER JOIN action as ac ON jac.jac_ac_id = ac.ac_id 
                    INNER JOIN users as us ON jae.jae_created_by = us.us_id 
                    LEFT OUTER JOIN document as doc ON jae.jae_doc_id = doc.doc_id
                    LEFT OUTER JOIN document_type as dct ON doc.doc_dct_id = dct.dct_id
                    LEFT OUTER JOIN document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id
                    LEFT OUTER JOIN system_setting as ss ON doc.doc_ss_id = ss.ss_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
