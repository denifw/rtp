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
 * Class to handle data access object for table office.
 *
 * @package    app
 * @subpackage Model\Dao\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class OfficeDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'of_id',
        'of_rel_id',
        'of_name',
        'of_invoice',
        'of_address',
        'of_cnt_id',
        'of_stt_id',
        'of_cty_id',
        'of_dtc_id',
        'of_postal_code',
        'of_longitude',
        'of_latitude',
        'of_cp_id',
        'of_active',
    ];

    /**
     * Base dao constructor for office.
     *
     */
    public function __construct()
    {
        parent::__construct('office', 'of', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('ofc.of_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by relation id
     *
     * @param string $relId To store the id of relation.
     *
     * @return array
     */
    public static function getDataByRelation(string $relId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ofc.of_rel_id', $relId);
        $wheres[] = SqlHelper::generateNullCondition('ofc.of_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT ofc.of_id, ofc.of_rel_id, ofc.of_name, ofc.of_invoice, ofc.of_address,
                        ofc.of_cnt_id, ofc.of_stt_id, ofc.of_cty_id, ofc.of_dtc_id, ofc.of_postal_code, ofc.of_longitude,
                        ofc.of_latitude, ofc.of_active, rel.rel_name as of_relation, cnt.cnt_name as of_country,
                        stt.stt_name as of_state, cty.cty_name as of_city, dtc.dtc_name as of_district,
                        ofc.of_cp_id, cp.cp_name as of_manager, (CASE WHEN ofc.of_id = rel.rel_of_id THEN 'Y' ELSE 'N' END) as of_rel_main
                    FROM office as ofc
                        INNER JOIN relation as rel ON ofc.of_rel_id = rel.rel_id
                        LEFT OUTER JOIN contact_person as cp ON ofc.of_cp_id = cp.cp_id
                        LEFT OUTER JOIN country as cnt ON ofc.of_cnt_id = cnt.cnt_id
                        LEFT OUTER JOIN state as stt ON ofc.of_stt_id = stt.stt_id
                        LEFT OUTER JOIN city as cty ON ofc.of_cty_id = cty.cty_id
                        LEFT OUTER JOIN district as dtc ON ofc.of_dtc_id = dtc.dtc_id" . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY ofc.of_name, ofc.of_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return self::doPrepareOfficeData($result);
    }

    /**
     * Function to prepare office data..
     *
     * @param array $sqlResults To store data from sql query.
     *
     * @return array
     */
    private static function doPrepareOfficeData(array $sqlResults): array
    {
        $results = [];
        foreach ($sqlResults as $row) {
            $data = DataParser::objectToArray($row);
            $address = $data['of_address'];
            $adDtc = $data['of_address'];
            $adCty = $data['of_address'];
            if (empty($data['of_district']) === false) {
                $address .= ', ' . $data['of_district'];
                $address .= ', ' . $data['of_city'];
                $address .= ', ' . $data['of_state'];
                $address .= ', ' . $data['of_country'];

                $adDtc .= ', ' . $data['of_district'];

                $adCty .= ', ' . $data['of_district'];
                $adCty .= ', ' . $data['of_city'];
            }
            if (empty($data['of_postal_code']) === false) {
                $address .= ', ' . $data['of_postal_code'];
            }
            $data['of_full_address'] = $address;
            $data['of_address_district'] = $adDtc;
            $data['of_address_city'] = $adCty;
            $results[] = $data;
        }
        return $results;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list order by query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadSimpleData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT of_id, of_name
                    FROM office ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY of_name, of_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param int $relId To store the relation id.
     * @return array
     */
    public static function loadInvoiceOffice($relId): array
    {
        $wheres = [];
        $wheres[] = '(of_rel_id = ' . $relId . ')';
        $wheres[] = '(of_deleted_on IS NULL)';
        $wheres[] = "(of_active = 'Y')";
        $wheres[] = "(of_invoice = 'Y')";
        return self::loadSimpleData($wheres, ['of_name', 'of_id']);
    }

    /**
     * Function to get all record.
     *
     * @param int $relId To store the relation id.
     * @return array
     */
    public static function loadOrderOffice($relId): array
    {
        $wheres = [];
        $wheres[] = '(of_rel_id = ' . $relId . ')';
        $wheres[] = '(of_deleted_on IS NULL)';
        $wheres[] = "(of_active = 'Y')";
        return self::loadSimpleData($wheres, ['of_name', 'of_id']);
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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'of_id');
    }
}
