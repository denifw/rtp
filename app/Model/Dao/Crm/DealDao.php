<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table deal.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DealDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'dl_id', 'dl_ss_id', 'dl_number', 'dl_name', 'dl_rel_id', 'dl_pic_id', 'dl_manager_id',
        'dl_source_id', 'dl_amount', 'dl_close_date', 'dl_stage_id', 'dl_description', 'dl_deleted_reason', 'dl_sty_id'
    ];

    /**
     * Base dao constructor for deal.
     *
     */
    public function __construct()
    {
        parent::__construct('deal', 'dl', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table deal.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'dl_number', 'dl_name', 'dl_close_date', 'dl_description', 'dl_deleted_reason'
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
     * @param int $ssId           To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(dl.dl_id = ' . $referenceValue . ')';
        $wheres[] = '(dl.dl_ss_id = ' . $ssId . ')';
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
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dl.dl_id, dl.dl_number, dl.dl_name, dl.dl_rel_id, dl.dl_pic_id, dl.dl_manager_id,
                         dl.dl_source_id, dl.dl_amount, dl.dl_close_date, dl.dl_stage_id, dl.dl_description, dl.dl_sty_id,
                         rel.rel_name as dl_rel_name, manager.us_name as dl_manager_name,
                         stg.sty_name as dl_stage_name, pic.cp_name as dl_pic_name, src.sty_name as dl_source_name,
                         sty.sty_name as dl_sty_name
                  FROM   deal as dl
                         INNER JOIN relation as rel on rel.rel_id = dl.dl_rel_id
                         INNER JOIN users as manager on manager.us_id = dl.dl_manager_id
                         INNER JOIN system_type as stg on stg.sty_id = dl.dl_stage_id
                         LEFT OUTER JOIN contact_person as pic on pic.cp_id = dl.dl_pic_id
                         LEFT OUTER JOIN system_type as src on src.sty_id = dl.dl_source_id
                         LEFT OUTER JOIN system_type as sty on sty.sty_id = dl.dl_sty_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (dl.dl_id)) AS total_rows
                   FROM   deal as dl
                         INNER JOIN relation as rel on rel.rel_id = dl.dl_rel_id
                         INNER JOIN users as manager on manager.us_id = dl.dl_manager_id
                         INNER JOIN system_type as stg on stg.sty_id = dl.dl_stage_id
                         LEFT OUTER JOIN contact_person as pic on pic.cp_id = dl.dl_pic_id
                         LEFT OUTER JOIN system_type as src on src.sty_id = dl.dl_source_id
                         LEFT OUTER JOIN system_type as sty on sty.sty_id = dl.dl_sty_id' . $strWhere;

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
     * @param int   $limit  To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'dl_name', 'dl_id');
    }


}
