<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Document;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table document.
 *
 * @package    app
 * @subpackage Model\Dao\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'doc_id',
        'doc_dct_id',
        'doc_ss_id',
        'doc_group_reference',
        'doc_type_reference',
        'doc_file_name',
        'doc_description',
        'doc_file_size',
        'doc_file_type',
        'doc_public',
    ];

    /**
     * Base dao constructor for document.
     *
     */
    public function __construct()
    {
        parent::__construct('document', 'doc', self::$Fields);
    }

    /**
     * Function to get all the data by id.
     *
     * @param int $ssId To store the id of system setting.
     * @param string $code To store the id of system setting.
     *
     * @return array
     */
    public static function loadSystemSettingLogo($ssId, string $code): array
    {
        $wheres = [];
//        $wheres[] = '(dct.dct_code = ' . $code . ')';
        $wheres[] = SqlHelper::generateLikeCondition('dct.dct_code', $code);
        $wheres[] = '(doc_ss_id = ' . $ssId . ')';
        $wheres[] = '(doc_group_reference = ' . $ssId . ')';
        $wheres[] = '(doc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT doc_id, doc_file_name, doc_created_on, dct_code, dcg_code, ss.ss_name_space
                    FROM document as doc INNER JOIN
                    document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                     document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                      system_setting as ss ON doc.doc_ss_id = ss.ss_id ' . $strWhere;
        $query .= ' GROUP BY doc_id, doc_file_name, doc_created_on, dct_code, dcg_code, ss.ss_name_space ';
        $query .= ' ORDER BY doc_created_on DESC ';
        $query .= ' LIMIT 1 OFFSET 0 ';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
            $nameSpace = StringFormatter::replaceSpecialCharacter(strtolower($result['ss_name_space']), '');
            $dcg = StringFormatter::replaceSpecialCharacter(strtolower($result['dcg_code']), '');
            $dct = StringFormatter::replaceSpecialCharacter(strtolower($result['dct_code']), '');
            $result['path'] = $nameSpace . '/' . $dcg . '/' . $dct . '/' . $result['doc_file_name'];
        }

        return $result;

    }

    /**
     * Function to get all the data by id.
     *
     * @param int $ssId To store the id of system setting.
     * @param int $docId To store the id of document.
     *
     * @return array
     */
    public static function loadEquipmentImage($ssId, int $docId): array
    {
        $wheres = [];
//        $wheres[] = '(dct.dct_code = ' . $code . ')';
        $wheres[] = '(doc_ss_id = ' . $ssId . ')';
        $wheres[] = '(doc_id = ' . $docId . ')';
        $wheres[] = '(doc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT doc_id, doc_file_name, doc_created_on, dct_code, dcg_code, ss.ss_name_space
                    FROM document as doc INNER JOIN
                    document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                     document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                      system_setting as ss ON doc.doc_ss_id = ss.ss_id ' . $strWhere;
        $query .= ' GROUP BY doc_id, doc_file_name, doc_created_on, dct_code, dcg_code, ss.ss_name_space ';
        $query .= ' ORDER BY doc_created_on DESC ';
        $query .= ' LIMIT 1 OFFSET 0 ';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
            $nameSpace = StringFormatter::replaceSpecialCharacter(strtolower($result['ss_name_space']), '');
            $dcg = StringFormatter::replaceSpecialCharacter(strtolower($result['dcg_code']), '');
            $dct = StringFormatter::replaceSpecialCharacter(strtolower($result['dct_code']), '');
            $result['path'] = $nameSpace . '/' . $dcg . '/' . $dct . '/' . $result['doc_file_name'];
        }

        return $result;

    }

    /**
     * Function to get all the data by id.
     *
     * @param int $reference To store the reference of the document.
     *
     * @return array
     */
    public static function loadCompleteDataByReference($reference): array
    {
        $query = 'SELECT doc.doc_id, doc.doc_file_name, doc.doc_file_type, dct.dct_code, dcg.dcg_code, ss.ss_name_space
                        FROM document as doc INNER JOIN
                        document_type AS dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group AS dcg ON dct.dct_dcg_id = dcg.dcg_id INNER JOIN
                        system_setting as ss ON doc.doc_ss_id = ss.ss_id
                  WHERE (doc.doc_id = ' . $reference . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
        }

        return $result;

    }

    /**
     * Abstract function to load the seeder query for table document.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'doc_file_name',
            'doc_file_type',
            'doc_public',
        ]);
    }


    /**
     * Abstract function to do insert transaction.
     *
     * @param array $fieldData To store the field value per column.
     * @param UploadedFile $file The filename.
     *
     *
     * @return void
     */
    public function doUploadDocument(array $fieldData, UploadedFile $file): void
    {
        $this->doInsertTransaction($fieldData);
        $upload = new FileUpload($this->getLastInsertId());
        $upload->upload($file);
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
        $query = 'SELECT doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_dcg_id, dcg.dcg_code, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_description
                        FROM document as doc INNER JOIN
                        document_type as dct ON doc.doc_dct_id = dct.dct_id INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id
                        WHERE (doc.doc_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
        }

        return $result;
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(doc.doc_active = 'Y')";
        $where[] = '(doc.doc_deleted_on IS NULL)';

        return self::loadData($where);

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
        $query = 'SELECT doc.doc_id, doc.doc_dct_id, dct.dct_code, dct.dct_description, dct.dct_dcg_id, dcg.dcg_code, dcg.dcg_description, doc.doc_group_reference,
                    doc.doc_type_reference, doc.doc_file_name, doc.doc_file_size, doc.doc_file_type, doc.doc_public,
                    doc.doc_created_by, us.us_name as doc_creator, doc.doc_created_on,
                    doc.doc_description, ss.ss_name_space
                        FROM document as doc
                            INNER JOIN document_type as dct ON doc.doc_dct_id = dct.dct_id
                            INNER JOIN document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id
                            INNER JOIN users AS us ON us.us_id = doc.doc_created_by
                            INNER JOIN system_setting as ss ON doc.doc_ss_id = ss.ss_id' . $strWhere;
        $query .= ' ORDER BY doc.doc_created_on DESC, doc.doc_id ';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param string|array $groupCode To store the group code.
     * @param string|array $typeCode To store the type code.
     * @param int $groupReference To store the reference of the document.
     * @param int $typeReference To store the reference of the document.
     *
     * @return array
     */
    public static function getTotalByGroupAndType($groupCode, $typeCode, int $groupReference = 0, int $typeReference = 0): array
    {
        $docWheres = [];
        $docWheres[] = SqlHelper::generateNullCondition('doc_deleted_on');
        if ($groupReference > 0) {
            $docWheres[] = SqlHelper::generateNumericCondition('doc_group_reference', $groupReference);
        }
        if ($typeReference > 0) {
            $docWheres[] = SqlHelper::generateNumericCondition('doc_type_reference', $typeReference);
        }
        $strDocWhere = ' WHERE ' . implode(' AND ', $docWheres);
        # General Wheres
        $wheres = [];
        if (empty($groupCode) === false) {
            if (is_array($groupCode) === true) {
                $wheres[] = "(dcg.dcg_code IN ('" . implode("', '", $groupCode) . "'))";
            } else {
                $wheres[] = SqlHelper::generateStringCondition('dcg.dcg_code', $groupCode);
            }
        }
        if (empty($typeCode) === false) {
            if (is_array($typeCode) === true) {
                $wheres[] = "(dct.dct_code IN ('" . implode("', '", $typeCode) . "'))";
            } else {
                $wheres[] = SqlHelper::generateStringCondition('dct.dct_code', $typeCode);
            }
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dcg.dcg_id, dcg.dcg_code, dcg.dcg_description, dct.dct_id, dct.dct_code, dct.dct_description, doc.total_doc
                    FROM document_group as dcg
                    INNER JOIN document_type as dct ON dcg.dcg_id = dct.dct_dcg_id
                    LEFT OUTER JOIN(SELECT doc_dct_id, count(doc_id) as total_doc
                        FROM document
                        ' . $strDocWhere . '
                        GROUP BY doc_dct_id) as doc ON dct.dct_id = doc.doc_dct_id ' . $strWhere;
        $query .= ' ORDER BY dcg.dcg_description, dct.dct_description, dct.dct_id';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $groupReference To store the reference of the document.
     *
     * @return array
     */
    public static function loadDocumentForConfirmFuel(array $wheres, int $groupReference): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dct.dct_id, dct.dct_code, dct.dct_description, (CASE WHEN (doc.total_doc IS NULL) THEN 0 ELSE doc.total_doc END) as total
                   FROM document_type as dct INNER JOIN
                        document_group as dcg ON dcg.dcg_id = dct.dct_dcg_id LEFT JOIN LATERAL
                          (SELECT doc_dct_id, COUNT(doc_id) as total_doc
                          FROM document
                          WHERE (doc_group_reference = ' . $groupReference . ') AND (doc_deleted_on IS NULL)
                            AND (doc_dct_id = dct.dct_id)
                            GROUP BY doc_dct_id) as doc ON dct.dct_id = doc.doc_dct_id ' . $strWhere;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all record.
     *
     * @param array $doc To store the document file.
     *
     * @return string
     */
    public function getDocumentPath(array $doc): string
    {
        $paths = [];
        $paths[] = 'storage';
        if (empty($doc) === false) {
            if (array_key_exists('ss_name_space', $doc)) {
                $paths[] = StringFormatter::replaceSpecialCharacter(strtolower($doc['ss_name_space']), '');
            }
            if (array_key_exists('dcg_code', $doc)) {
                $paths[] = StringFormatter::replaceSpecialCharacter(strtolower($doc['dcg_code']), '');
            }
            if (array_key_exists('dct_code', $doc)) {
                $paths[] = StringFormatter::replaceSpecialCharacter(strtolower($doc['dct_code']), '');
            }
            if (array_key_exists('doc_file_name', $doc)) {
                $paths[] = $doc['doc_file_name'];
            }
        }
        return asset(implode('/', $paths));

    }

    /**
     * Function to get all record.
     *
     * @param string $groupCode To store the code for document group.
     * @param int $groupReference To store the reference for document group.
     * @param string $typeCode To store the code for document type.
     * @param int $typeReference To store the reference for document type.
     * @param bool $returnMultiple To trigger if we need to return 1 data or multiple data.
     *
     * @return array
     */
    public static function loadDataByCodeAndReference(string $groupCode, $groupReference = 0, string $typeCode = '', $typeReference = 0, bool $returnMultiple = true): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('dcg.dcg_code', mb_strtolower($groupCode));
        if ($groupReference > 0) {
            $wheres[] = SqlHelper::generateNumericCondition('doc.doc_group_reference', $groupReference);
        }
        if (empty($typeCode) === false) {
            $wheres[] = SqlHelper::generateStringCondition('dct.dct_code', mb_strtolower($typeCode));
        }
        if ($typeReference > 0) {
            $wheres[] = SqlHelper::generateNumericCondition('doc.doc_type_reference', $typeReference);
        }
        $limit = 0;
        if ($returnMultiple === false) {
            $limit = 1;
        }
        $data = self::loadData($wheres, $limit);
        if ($returnMultiple === true) {
            return $data;
        }
        if (count($data) > 0) {
            return $data[0];
        }
        return [];

    }


}
