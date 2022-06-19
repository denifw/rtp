<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\Crm;

use App\Frame\Formatter\SqlHelper;
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
        'cp_active',
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
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('cp.cp_id', $referenceValue);
        $results = self::loadData($helper);
        if (count($results) === 1) {
            return $results[0];
        }

        return [];
    }

    /**
     * Function to get data by relation id
     *
     * @param string $relId To store the relation id.
     *
     * @return array
     */
    public static function getDataByRelation(string $relId): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('o.of_rel_id', $relId);
        $helper->addNullWhere('cp.cp_deleted_on');
        return self::loadData($helper);
    }

    /**
     * Function to get data by relation id
     *
     * @param string $ofId To store the office id.
     *
     * @return array
     */
    public static function getDataByOffice(string $ofId): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('cp.cp_of_id', $ofId);
        $helper->addNullWhere('cp.cp_deleted_on');
        return self::loadData($helper);
    }

    /**
     * Function to get all record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadData(SqlHelper $helper): array
    {
        if ($helper->hasOrderBy() === false) {
            $helper->addOrderByString('rel.rel_name, o.of_name, cp.cp_name, cp.cp_id');
        }
        $query = "SELECT cp.cp_id, cp.cp_number, cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_active, cp.cp_of_id, o.of_name as cp_office,
                         o.of_rel_id as cp_rel_id, rel.rel_name as cp_relation, cp.cp_deleted_on, cp.cp_deleted_reason,
                        (CASE WHEN cp.cp_id = o.of_cp_id THEN 'Y' ELSE 'N' END) as cp_of_manager
                        FROM contact_person as cp
                             INNER JOIN office as o ON cp.cp_of_id = o.of_id
                             INNER JOIN relation as rel ON o.of_rel_id = rel.rel_id " . $helper;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get total record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(SqlHelper $helper): int
    {
        $result = 0;
        $query = 'SELECT count(DISTINCT (cp.cp_id)) AS total_rows
                  FROM contact_person as cp
                             INNER JOIN office as o ON cp.cp_of_id = o.of_id
                             INNER JOIN relation as rel ON o.of_rel_id = rel.rel_id ' . $helper->getConditionForCountData();

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper): array
    {
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'cp_id');
    }
}
