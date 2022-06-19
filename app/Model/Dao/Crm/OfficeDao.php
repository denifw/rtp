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
        $helper = new SqlHelper();
        $helper->addStringWhere('ofc.of_id', $referenceValue);
        $data = self::loadData($helper);
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
        $helper = new SqlHelper();
        $helper->addStringWhere('ofc.of_rel_id', $relId);
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
            $helper->addOrderByString('ofc.of_name, ofc.of_id');
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
                        LEFT OUTER JOIN district as dtc ON ofc.of_dtc_id = dtc.dtc_id " . $helper;
        $results = DB::select($query);
        return DataParser::arrayObjectToArray($results);
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
        $query = 'SELECT count(DISTINCT (ofc.of_id)) AS total_rows
                  FROM office as ofc
                        INNER JOIN relation as rel ON ofc.of_rel_id = rel.rel_id
                        LEFT OUTER JOIN contact_person as cp ON ofc.of_cp_id = cp.cp_id
                        LEFT OUTER JOIN country as cnt ON ofc.of_cnt_id = cnt.cnt_id
                        LEFT OUTER JOIN state as stt ON ofc.of_stt_id = stt.stt_id
                        LEFT OUTER JOIN city as cty ON ofc.of_cty_id = cty.cty_id
                        LEFT OUTER JOIN district as dtc ON ofc.of_dtc_id = dtc.dtc_id ' . $helper->getConditionForCountData();

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'of_id');
    }
}
