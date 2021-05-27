<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table sales_order_issue.
 *
 * @package    app
 * @subpackage Model\Dao\CustomerService
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderIssueDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'soi_id',
        'soi_ss_id',
        'soi_number',
        'soi_rel_id',
        'soi_pic_id',
        'soi_srv_id',
        'soi_so_id',
        'soi_jo_id',
        'soi_subject',
        'soi_report_date',
        'soi_assign_id',
        'soi_priority_id',
        'soi_pic_field_id',
        'soi_description',
        'soi_solution',
        'soi_note',
        'soi_finish_by',
        'soi_finish_on',
        'soi_deleted_reason',
    ];

    /**
     * Base dao constructor for sales_order_issue.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_issue', 'soi', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order_issue.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'soi_subject',
            'soi_report_date',
            'soi_description',
            'soi_solution',
            'soi_note',
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
        $wheres[] = '(soi_id = ' . $referenceValue . ')';
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
     * @param int $ssId           To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(soi.soi_id = ' . $referenceValue . ')';
        $wheres[] = '(soi.soi_ss_id = ' . $ssId . ')';
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
        $query = 'SELECT soi.soi_id, soi.soi_ss_id, soi.soi_number, soi.soi_subject, soi.soi_report_date,
                            soi.soi_description, soi.soi_solution, soi.soi_note, soi.soi_rel_id, soi.soi_pic_id,
                            soi.soi_srv_id, soi.soi_so_id, soi.soi_jo_id, soi.soi_assign_id, soi.soi_priority_id,
                            soi.soi_pic_field_id, so.so_number as soi_so_number, srv.srv_name as soi_srv_name, srv.srv_name as soi_srv_name,
                            rel.rel_name as soi_rel_name, pic.cp_name as soi_pic_name, sty.sty_name as soi_sty_name,
                            us1.us_name as soi_assign_name, jo.jo_number as soi_jo_number, pic2.cp_name as soi_pic_field_name,
                            soi.soi_finish_on, us2.us_name as soi_us_name, jo.jo_srt_id as soi_jo_srt_id, 
                            ud.us_name as soi_deleted_by, soi.soi_deleted_reason, soi.soi_deleted_on, sty.sty_label_type as soi_sty_label
                    FROM sales_order_issue as soi
                        INNER JOIN relation as rel on soi.soi_rel_id = rel.rel_id
                        INNER JOIN sales_order as so on soi.soi_so_id = so.so_id
                        INNER JOIN service as srv on soi.soi_srv_id = srv.srv_id
                        INNER JOIN system_type as sty on soi.soi_priority_id = sty.sty_id
                        INNER JOIN users as us1 on soi.soi_assign_id = us1.us_id
                        LEFT OUTER JOIN contact_person as pic on soi.soi_pic_id = pic.cp_id
                        LEFT OUTER JOIN contact_person as pic2 on soi.soi_pic_field_id = pic2.cp_id
                        LEFT OUTER JOIN job_order as jo on soi.soi_jo_id = jo.jo_id
                        LEFT OUTER JOIN users as us2 on soi.soi_finish_by = us2.us_id
                        LEFT OUTER JOIN users as ud ON soi.soi_deleted_by = ud.us_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (soi.soi_id)) AS total_rows
                       FROM sales_order_issue as soi
                        INNER JOIN relation as rel on soi.soi_rel_id = rel.rel_id
                        INNER JOIN office as oo on rel.rel_id = oo.of_rel_id
                        LEFT JOIN contact_person as pic on oo.of_id = pic.cp_of_id
                        LEFT JOIN contact_person as pic2 on soi.soi_pic_field_id = pic2.cp_id
                        INNER JOIN service as srv on soi.soi_srv_id = srv.srv_id
                        INNER JOIN sales_order as so on soi.soi_so_id = so.so_id
                        INNER JOIN system_type as sty on soi.soi_priority_id = sty.sty_id
                        LEFT JOIN job_order as jo on soi.soi_jo_id = jo.jo_id
                        INNER JOIN users as us1 on soi.soi_assign_id = us1.us_id
                        LEFT JOIN users as us2 on soi.soi_finish_by = us2.us_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, 'soi_number', 'soi_id');
    }


}
