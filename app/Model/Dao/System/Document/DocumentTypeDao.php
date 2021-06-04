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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table document_type.
 *
 * @package    app
 * @subpackage Model\Dao\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentTypeDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dct_id',
        'dct_dcg_id',
        'dct_code',
        'dct_description',
        'dct_table',
        'dct_value_field',
        'dct_text_field',
        'dct_active',
    ];

    /**
     * Base dao constructor for document_type.
     *
     */
    public function __construct()
    {
        parent::__construct('document_type', 'dct', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('dct.dct_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $groupCode To store the reference value of the table.
     * @param string $typeCode To store the reference value of the table.
     *
     * @return array
     */
    public static function getByCode(string $groupCode, string $typeCode): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('dcg.dcg_code', strtolower($groupCode));
        $wheres[] = SqlHelper::generateStringCondition('dct.dct_code', strtolower($typeCode));
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list condition query.
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
        $query = 'SELECT dct.dct_id, dct.dct_dcg_id, dct.dct_description, dcg.dcg_code as dct_group, dct.dct_code, dct.dct_table, dct.dct_value_field,
                            dct.dct_text_field, dct.dct_active
                    FROM document_type as dct
                        INNER JOIN document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id ' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY  dcg.dcg_code, dct.dct_code, dct.dct_id';
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
        $query = 'SELECT count(DISTINCT (dct.dct_id)) AS total_rows
                       FROM document_type as dct
                        INNER JOIN document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id ' . $strWhere;
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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'dct_id');
    }


}
