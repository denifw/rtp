<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Setting;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table service_term_document.
 *
 * @package    app
 * @subpackage Model\Dao\System\Service
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceTermDocumentDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'std_id',
        'std_ss_id',
        'std_srt_id',
        'std_dct_id',
        'std_general',
    ];

    /**
     * Base dao constructor for service_term_document.
     *
     */
    public function __construct()
    {
        parent::__construct('service_term_document', 'std', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table service_term_document.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'std_general',
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
        $where[] = '(std_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $where = [];
        $where[] = '(std_id = ' . $referenceValue . ')';
        $where[] = '(std_ss_id = ' . $systemSettingValue . ')';

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
        $where[] = "(std_active = 'Y')";
        $where[] = '(std_deleted_on IS NULL)';

        return self::loadData($where);

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
        $query = 'SELECT std.std_id, std.std_srt_id, srt.srt_name, srv.srv_name, 
                    srv.srv_id, std.std_dct_id, dct.dct_code, dct.dct_description, std.std_general, 
                    std.std_deleted_on
                   FROM service_term_document as std INNER JOIN
                   service_term as srt ON std.std_srt_id = srt.srt_id INNER JOIN
                   service as srv ON srt.srt_srv_id = srv.srv_id INNER JOIN
                   document_type as dct ON std.std_dct_id = dct.dct_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all record.
     *
     * @param int $ssId           To store the reference of the document.
     * @param int $groupReference To store the reference of the document.
     * @param int $srtId          To store the service term id..
     *
     * @return array
     */
    public static function loadDocumentByGroupAndServiceTerm(int $ssId, int $groupReference, int $srtId): array
    {
        $wheres = [];
        $wheres[] = '(std.std_deleted_on IS NULL)';
        $wheres[] = '(dct.dct_deleted_on IS NULL)';
        $wheres[] = '(std.std_srt_id = ' . $srtId . ')';
        $wheres[] = '(std.std_ss_id = ' . $ssId . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $wheres[] = "(doc.doc_group_reference = " . $groupReference . ")";
        $wheres[] = "(dcg.dcg_code = 'joborder')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT std.std_id, dct.dct_id, dct.dct_code, dct.dct_description, dct.dct_master, COUNT(doc.doc_id) as total
                        FROM service_term_document as std 
                            INNER JOIN document_type as dct ON std.std_dct_id = dct.dct_id
                            INNER JOIN document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id 
                            LEFT OUTER JOIN document as doc ON dct.dct_id = doc.doc_dct_id ' . $strWhere;
        $query .= ' GROUP BY std.std_id, dct.dct_id, dct.dct_code, dct.dct_description, dct.dct_master';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
