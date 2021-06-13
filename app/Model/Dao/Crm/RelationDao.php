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
     * @param int $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = '(rel.rel_id = ' . $referenceValue . ')';
        $result = [];
        $results = self::loadData($wheres);
        if (count($results) === 1) {
            $result = $results[0];
        }
        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(rel.rel_id = ' . $referenceValue . ')';
        $wheres[] = '(rel.rel_ss_id  = ' . $systemSettingValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $ssId To store the system setting Id.
     * @param int $relId To store the relation Id.
     * @param string $name To store the contact name.
     * @param string $phoneNumber To store the contact phone number.
     *
     * @return array
     */
    public static function loadByCpNameAndNumber(int $ssId, int $relId, string $name, string $phoneNumber): array
    {
        $query = "SELECT rel.rel_id, off.of_id, cp.cp_id
                  FROM relation AS rel
                       INNER JOIN office AS off ON off.of_rel_id = rel.rel_id
                       INNER JOIN contact_person AS cp ON cp.cp_of_id = off.of_id
                  WHERE (rel.rel_ss_id = $ssId) AND (rel.rel_id = $relId) AND (cp.cp_name = '$name') AND (cp.cp_phone = '$phoneNumber')";
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $ssId To store the system setting Id.
     * @param string $name To store the contact name.
     *
     * @return array
     */
    public static function loadByName($ssId, string $name): array
    {
        $query = "SELECT rel.rel_id, off.of_id, cp.cp_id
                  FROM relation AS rel
                       INNER JOIN office AS off ON off.of_rel_id = rel.rel_id
                       INNER JOIN contact_person AS cp ON cp.cp_of_id = off.of_id
                  WHERE (rel.rel_ss_id = $ssId) AND (rel.rel_name = '$name')";
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
        }

        return $result;
    }


    /**
     * Function to get relation owner by system setting id
     *
     * @param int $ssId To store the system id.
     *
     * @return array
     */
    public static function getRelationOwnerBySystemId($ssId): array
    {
        $wheres = [];
        $wheres[] = "(rel_active = 'Y')";
        $wheres[] = "(rel_owner = 'Y')";
        $wheres[] = '(rel_ss_id = ' . $ssId . ')';
        $wheres[] = '(rel_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT rel_id, rel_name
                        FROM relation ' . $strWhere;
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], ['rel_id', 'rel_name']);
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
                         rel.rel_phone, rel.rel_vat, rel.rel_owner, rel.rel_remark, rel.rel_active,
                         rel.rel_manager_id, rel.rel_main_contact_id, rel.rel_ids_id, rel.rel_source_id,
                         rel.rel_established, rel.rel_size_id, rel.rel_employee, rel.rel_revenue, rel.rel_deleted_reason,
                         manager.us_name as rel_manager_name, mc.cp_name as rel_main_contact_name,
                         ids.ids_name as rel_ids_name, src.sty_name as rel_source_name,
                         sz.sty_name as rel_size
                  FROM   relation as rel
                         INNER JOIN system_setting as ss on rel.rel_ss_id = ss.ss_id
                         LEFT OUTER JOIN users as manager on manager.us_id = rel.rel_manager_id
                         LEFT OUTER JOIN contact_person as mc on mc.cp_id = rel.rel_main_contact_id
                         LEFT OUTER JOIN industry as ids on ids.ids_id = rel.rel_ids_id
                         LEFT OUTER JOIN system_type as src on src.sty_id = rel.rel_source_id
                         LEFT OUTER JOIN system_type as sz on sz.sty_id = rel.rel_size_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
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
        $query = 'SELECT count(DISTINCT (rel_id)) AS total_rows
                  FROM relation as rel
                  INNER JOIN system_setting as ss on rel.rel_ss_id = ss.ss_id
                  LEFT OUTER JOIN users as manager on manager.us_id = rel.rel_manager_id
                  LEFT OUTER JOIN contact_person as mc on mc.cp_id = rel.rel_main_contact_id
                  LEFT OUTER JOIN industry as ids on ids.ids_id = rel.rel_ids_id
                  LEFT OUTER JOIN system_type as src on src.sty_id = rel.rel_source_id
                  LEFT OUTER JOIN system_type as sz on sz.sty_id = rel.rel_size_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param int $id To store the reference value of the table.
     *
     * @return array
     */
    public static function loadSimpleDataById($id): array
    {
        $wheres = [];
        $wheres[] = '(rel_id = ' . $id . ')';
        $data = self::loadSimpleData($wheres);
        if (\count($data) === 1) {
            return $data[0];
        }

        return [];
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
    public static function loadSimpleData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT rel_id, rel_ss_id, rel_number, rel_name, rel_email, rel_website,
                      rel_phone, rel_vat, rel_owner, rel_active
                        FROM relation ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
