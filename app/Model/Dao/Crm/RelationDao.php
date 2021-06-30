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
 * Class to handle data access object for table relation.
 *
 * @package    app
 * @subpackage Model\Dao\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class RelationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rel_id',
        'rel_ss_id',
        'rel_name',
        'rel_number',
        'rel_short_name',
        'rel_website',
        'rel_email',
        'rel_phone',
        'rel_vat',
        'rel_remark',
        'rel_of_id',
        'rel_cp_id',
        'rel_active',
    ];

    /**
     * Base dao constructor for relation.
     *
     */
    public function __construct()
    {
        parent::__construct('relation', 'rel', self::$Fields);
    }

    /**
     * Function to get data by reference value
     *
     * @param string $relId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $relId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('rel.rel_id', $relId);
        $results = self::loadData($wheres);
        $result = [];
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }


    /**
     * Function to get data by reference value
     *
     * @param string $relId To store the reference value of the table.
     * @param string $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $relId, string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('rel.rel_id', $relId);
        $wheres[] = SqlHelper::generateStringCondition('rel.rel_ss_id', $ssId);
        $results = self::loadData($wheres);
        $result = [];
        if (count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
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
        $query = 'SELECT rel.rel_id, rel.rel_ss_id, ss.ss_relation as rel_system,
                         rel.rel_number, rel.rel_name, rel.rel_short_name, rel.rel_email, rel.rel_website,
                         rel.rel_phone, rel.rel_vat, rel.rel_remark, rel.rel_active, rel.rel_of_id, o.of_name as rel_office,
                         rel.rel_cp_id, cp.cp_name as rel_pic, rel.rel_deleted_on, rel.rel_deleted_reason
                  FROM   relation as rel
                         INNER JOIN system_setting as ss on rel.rel_ss_id = ss.ss_id
                         LEFT OUTER JOIN office as o on rel.rel_of_id = o.of_id
                         LEFT OUTER JOIN contact_person as cp on rel.rel_cp_id = cp.cp_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY rel.rel_name, rel.rel_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (rel.rel_id)) AS total_rows
                  FROM relation as rel
                         INNER JOIN system_setting as ss on rel.rel_ss_id = ss.ss_id
                         LEFT OUTER JOIN office as o on rel.rel_of_id = o.of_id
                         LEFT OUTER JOIN contact_person as cp on rel.rel_cp_id = cp.cp_id' . $strWhere;

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
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'rel_id');
    }

    /**
     * Function to get all record.
     *
     * @param int $relId To store the id of the relation.
     * @param int $ofId To store the id of the office.
     *
     * @return array
     */
    public static function loadDataForDocumentHeader($relId, $ofId = 0): array
    {
        $wheres = [];
        $wheres[] = '(rel.rel_id = ' . $relId . ')';
        $wheres[] = '(o.of_deleted_on IS NULL)';
        $wheres[] = "(o.of_active = 'Y')";
        if ($ofId === 0) {
            $wheres[] = "(o.of_main = 'Y')";
        } else {
            $wheres[] = '(o.of_id = ' . $ofId . ')';
        }
        $docWheres = [];
        $docWheres[] = '(doc.doc_deleted_on IS NULL)';
        $docWheres[] = "(dcg.dcg_code = 'relation')";
        $docWheres[] = "(dct.dct_code = 'documentlogo')";

        $strDocWhere = ' WHERE ' . implode(' AND ', $docWheres);
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = "SELECT rel.rel_id, rel.rel_name, o.of_name, o.of_address, dtc.dtc_name, cnt.cnt_name, cty.cty_name, stt.stt_name, o.of_postal_code,
                        d.doc_id, d.doc_file_name, d.dct_code, d.dcg_code, d.doc_group_reference, d.doc_type_reference,
                        rel.rel_phone, rel.rel_email, rel.rel_website, d.ss_name_space, d.doc_created_on
                FROM office as o
                    INNER JOIN relation as rel ON o.of_rel_id = rel.rel_id
                    LEFT OUTER JOIN country as cnt ON o.of_cnt_id = cnt.cnt_id
                    LEFT OUTER JOIN state as stt ON o.of_stt_id = stt.stt_id
                    LEFT OUTER JOIN city as cty ON o.of_cty_id = cty.cty_id
                    LEFT OUTER JOIN district as dtc ON o.of_dtc_id = dtc.dtc_id
                    LEFT OUTER JOIN (SELECT doc.doc_id, doc.doc_file_name, dct.dct_code, dcg.dcg_code, doc.doc_group_reference, doc.doc_type_reference,
                                            ss.ss_name_space, doc.doc_created_on
                                        FROM document as doc
                                            INNER JOIN document_type AS dct ON doc.doc_dct_id = dct.dct_id
                                            INNER JOIN document_group AS dcg ON dct.dct_dcg_id = dcg.dcg_id
                                            INNER JOIN system_setting as ss ON doc.doc_ss_id = ss.ss_id
                                            " . $strDocWhere . ") as d ON rel.rel_id = d.doc_group_reference " . $strWhere;
        $query .= ' ORDER BY d.doc_created_on DESC, o.of_id';
        $query .= ' LIMIT 1 OFFSET 0';
        $result = DB::select($query);
        if (count($result) === 1) {
            return DataParser::objectToArray($result[0]);
        }

        return [];
    }


}
