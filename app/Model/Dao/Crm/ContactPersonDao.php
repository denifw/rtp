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
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('cp.cp_id', $referenceValue);
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
     * @param string $relId To store the relation id.
     *
     * @return array
     */
    public static function getDataByRelation(string $relId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('o.of_rel_id', $relId);
        return self::loadData($wheres);
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
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('cp.cp_of_id', $ofId);
        return self::loadData($wheres);
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
        $query = 'SELECT cp.cp_id, cp.cp_number, cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_active, cp.cp_of_id, o.of_name as cp_office,
                         o.of_rel_id as cp_rel_id, rel.rel_name as cp_relation, cp.cp_deleted_on, cp.cp_deleted_reason
                        FROM contact_person as cp
                             INNER JOIN office as o ON cp.cp_of_id = o.of_id
                             INNER JOIN relation as rel ON o.of_rel_id = rel.rel_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY rel.rel_name, o.of_name, cp.cp_name, cp.cp_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'cp_id');
    }
}
