<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Setting;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table document_signature.
 *
 * @package    app
 * @subpackage Model\Dao\Setting
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentSignatureDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ds_id',
        'ds_ss_id',
        'ds_dt_id',
        'ds_cp_id',
        'ds_deleted_reason',
    ];

    /**
     * Base dao constructor for document_signature.
     *
     */
    public function __construct()
    {
        parent::__construct('document_signature', 'ds', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table document_signature.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ds_id',
            'ds_ss_id',
            'ds_dt_id',
            'ds_cp_id',
            'ds_deleted_reason',
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
        $wheres = [];
        $wheres[] = '(ds_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(ds.ds_id = ' . $referenceValue . ')';
        $wheres[] = '(ds.ds_ss_id = ' . $ssId . ')';
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
        $query = 'SELECT ds.ds_id, ds.ds_ss_id, ds.ds_deleted_reason, ds.ds_dt_id, ds.ds_cp_id,
                            dt.dt_description as ds_dt_description, cp.cp_name as ds_cp_name,
                            dtt.dtt_description as ds_dtt_description, dtt.dtt_id as ds_dtt_id, dt.dt_id as ds_dt_id,
                            cp.cp_id as ds_cp_id, u.us_name as ds_us_name
                    FROM document_signature as ds
                    INNER JOIN document_template as dt on ds.ds_dt_id = dt.dt_id
                    INNER JOIN contact_person as cp on ds.ds_cp_id = cp.cp_id
                    INNER JOIN document_template_type as dtt on dt.dt_dtt_id = dtt.dtt_id
                    LEFT JOIN  users as u on ds.ds_deleted_by = u.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
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
        $query = 'SELECT count(DISTINCT (ds.ds_id)) AS total_rows
                        FROM document_signature as ds
                    INNER JOIN document_template as dt on ds.ds_dt_id = dt.dt_id
                    INNER JOIN contact_person as cp on ds.ds_cp_id = cp.cp_id
                    INNER JOIN document_template_type as dtt on dt.dt_dtt_id = dtt.dtt_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int $limit To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'ds_dt_id', 'ds_id');
    }


}
