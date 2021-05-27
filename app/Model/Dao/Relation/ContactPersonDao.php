<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\Relation;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table contact_person.
 *
 * @package    app
 * @subpackage Model\Dao\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class ContactPersonDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'cp_id',
        'cp_number',
        'cp_name',
        'cp_email',
        'cp_phone',
        'cp_of_id',
        'cp_office_manager',
        'cp_active',
        'cp_salutation_id',
        'cp_dpt_id',
        'cp_jbt_id',
        'cp_birthday',
        'cp_deleted_reason'
    ];

    /**
     * Base dao constructor for contact_person.
     *
     */
    public function __construct()
    {
        parent::__construct('contact_person', 'cp', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table contact_person.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'cp_number',
            'cp_name',
            'cp_email',
            'cp_phone',
            'cp_active',
            'cp_office_manager',
            'cp_birthday',
            'cp_deleted_reason',
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
        $wheres[] = '(cp.cp_id  = ' . $referenceValue . ')';
        $result = [];
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data by relation id
     *
     * @param int $relId To store the relation id.
     *
     * @return array
     */
    public static function getDataByRelation($relId): array
    {
        $wheres = [];
        $wheres[] = '(o.of_rel_id = ' . $relId . ')';

        return self::loadData($wheres);
    }

    /**
     * Function to get data by relation id
     *
     * @param int $ofId To store the office id.
     *
     * @return array
     */
    public static function getDataByOffice($ofId): array
    {
        $wheres = [];
        $wheres[] = '(o.of_id = ' . $ofId . ')';

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
        $query = 'SELECT cp.cp_id, cp.cp_number, cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_active, cp.cp_of_id, o.of_name as cp_office, 
                         cp.cp_office_manager, o.of_rel_id as cp_rel_id, rel.rel_name as cp_relation,
                         cp.cp_salutation_id, cp.cp_dpt_id, cp.cp_jbt_id, cp.cp_birthday, cp.cp_deleted_reason,
                         slt.sty_name as cp_salutation_name, jbt.jbt_name as cp_jbt_name, dpt.dpt_name as cp_dpt_name
                        FROM contact_person as cp 
                             INNER JOIN office as o ON cp.cp_of_id = o.of_id
                             INNER JOIN relation as rel ON o.of_rel_id = rel.rel_id
                             LEFT OUTER JOIN system_type as slt ON slt.sty_id = cp.cp_salutation_id
                             LEFT OUTER JOIN job_title as jbt ON jbt.jbt_id = cp.cp_jbt_id
                             LEFT OUTER JOIN department as dpt ON dpt.dpt_id = cp.cp_dpt_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to check is the office has manager.
     *
     * @param int $ofId To store the office id.
     *
     * @return bool
     */
    public static function isOfficeHasManager($ofId): bool
    {
        $wheres = [];
        $wheres[] = '(cp.cp_of_id = ' . $ofId . ')';
        $wheres[] = "(cp.cp_office_manager = 'Y')";
        $wheres[] = "(cp.cp_active = 'Y')";
        $result = self::loadData($wheres);

        return !empty($result);
    }

    /**
     * Function to get all record.
     *
     * @param array $receiverIds To store the receiver.
     *
     * @return array
     */
    public static function loadDataCpForNotification(array $receiverIds): array
    {
        $strWhere = '';
        $wheres = [];
        $wheres[] = '(cp.cp_id in (' . implode(',', $receiverIds) . '))';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT cp.cp_id, cp.cp_name, cp.cp_email, us.us_id, us.us_allow_mail, ump.ump_ss_id
                  FROM   contact_person AS cp
                         LEFT OUTER JOIN user_mapping AS ump ON ump.ump_cp_id = cp.cp_id
                         LEFT OUTER JOIN  users AS us ON us.us_id = ump.ump_us_id' . $strWhere;
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


}
