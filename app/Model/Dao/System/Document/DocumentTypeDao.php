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
        'dct_master',
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
     * Abstract function to load the seeder query for table document_type.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dct_code',
            'dct_table',
            'dct_description',
            'dct_value_field',
            'dct_text_field',
            'dct_master',
            'dct_active',
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
    public static function getByReference(int $referenceValue): array
    {
        $query = 'SELECT dct.dct_id, dct.dct_dcg_id, dct.dct_description, dcg.dcg_code as dct_group, dct.dct_code, dct.dct_table, dct.dct_value_field,
                    dct.dct_text_field, dct.dct_active, dct.dct_master
                        FROM document_type as dct INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id
                        WHERE (dct.dct_id = ' . $referenceValue . ')';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0], array_merge(self::$Fields, ['dct_group']));
        }

        return $result;
    }

    /**
     * Function to get data by reference value
     *
     * @param string $groupCode To store the reference value of the table.
     * @param string $typeCode  To store the reference value of the table.
     *
     * @return array
     */
    public static function getByCode(string $groupCode, string $typeCode): array
    {
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = '" . strtolower($groupCode) . "')";
        $wheres[] = "(dct.dct_code = '" . strtolower($typeCode) . "')";
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(dct.dct_active = 'Y')";
        $where[] = '(dct.dct_deleted_on IS NULL)';

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
        $query = 'SELECT dct.dct_id, dct.dct_dcg_id, dct.dct_description, dcg.dcg_code as dct_group, dct.dct_code, dct.dct_table, dct.dct_value_field,
                    dct.dct_text_field, dct.dct_active, dct.dct_master
                        FROM document_type as dct INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
