<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\Operation\Job;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table job_order.
 *
 * @package    app
 * @subpackage Model\Dao\Operation\Job
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobOrderDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jo_id',
        'jo_ss_id',
        'jo_number',
        'jo_name',
        'jo_rel_id',
        'jo_cp_id',
        'jo_srv_id',
        'jo_fee',
        'jo_value',
        'jo_estimation_start',
        'jo_estimation_end',
        'jo_address',
        'jo_dtc_id',
        'jo_reference',
        'jo_publish_by',
        'jo_publish_on',
        'jo_start_by',
        'jo_start_on',
        'jo_finish_on',
        'jo_finish_by',
        'jo_joa_id',
        'jo_us_id',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'jo_fee',
        'jo_value',
    ];

    /**
     * Base dao constructor for job_order.
     *
     */
    public function __construct()
    {
        parent::__construct('job_order', 'jo', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('jo.jo_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     * @param string $ssId To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem(string $referenceValue, string $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('jo.jo_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('jo.jo_ss_id', $ssId);
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
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_name, jo.jo_ss_id, jo.jo_rel_id, rel.rel_name as jo_relation,
                        jo.jo_cp_id, cp.cp_name as jo_contact_person, jo.jo_srv_id, srv.srv_name as jo_service,
                        jo.jo_fee, jo.jo_value, jo.jo_estimation_start, jo.jo_estimation_end, jo.jo_address, jo.jo_dtc_id,
                        dtc.dtc_name as jo_district, cty.cty_id as jo_cty_id, cty.cty_name as jo_city, stt.stt_id as jo_stt_id,
                        stt.stt_name as jo_state, cnt.cnt_id as jo_cnt_id, cnt.cnt_name as jo_country, jo.jo_reference,
                        jo.jo_created_on, uc.us_name as jo_created_by, jo.jo_publish_on, up.us_name as jo_publish_by,
                        jo.jo_start_on, us.us_name as jo_start_by, jo.jo_finish_on, uf.us_name as jo_finish_by, jo.jo_deleted_on,
                        jo.jo_deleted_reason, ud.us_name as jo_deleted_by, jo.jo_joa_id, joa.joa_created_on as jo_archive_on,
                        uca.us_name as jo_archive_by, srv.srv_code as jo_srv_code, jo.jo_us_id, um.us_name as jo_manager
                    FROM job_order as jo
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN service as srv ON srv.srv_id = jo.jo_srv_id
                        INNER JOIN district as dtc ON jo.jo_dtc_id = dtc.dtc_id
                        INNER JOIN city as cty ON dtc.dtc_cty_id = cty.cty_id
                        INNER JOIN state as stt ON cty.cty_stt_id = stt.stt_id
                        INNER JOIN country as cnt ON stt.stt_cnt_id = cnt.cnt_id
                        INNER JOIN users as uc ON jo.jo_created_by = uc.us_id
                        LEFT OUTER JOIN contact_person as cp ON jo.jo_cp_id = cp.cp_id
                        LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                        LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
                        LEFT OUTER JOIN users as us ON jo.jo_start_by = us.us_id
                        LEFT OUTER JOIN users as uf ON jo.jo_finish_by = uf.us_id
                        LEFT OUTER JOIN users as um ON jo.jo_us_id = um.us_id
                        LEFT OUTER JOIN job_archive as joa ON jo.jo_joa_id = joa.joa_id
                        LEFT OUTER JOIN users as uca ON joa.joa_created_by = uca.us_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_start_on DESC, jo.jo_publish_on DESC, jo.jo_created_on DESC, jo.jo_id';
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
        $query = 'SELECT count(DISTINCT (jo.jo_id)) AS total_rows
                        FROM job_order as jo
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN service as srv ON srv.srv_id = jo.jo_srv_id
                        INNER JOIN district as dtc ON jo.jo_dtc_id = dtc.dtc_id
                        INNER JOIN city as cty ON dtc.dtc_cty_id = cty.cty_id
                        INNER JOIN state as stt ON cty.cty_stt_id = stt.stt_id
                        INNER JOIN country as cnt ON stt.stt_cnt_id = cnt.cnt_id
                        INNER JOIN users as uc ON jo.jo_created_by = uc.us_id
                        LEFT OUTER JOIN contact_person as cp ON jo.jo_cp_id = cp.cp_id
                        LEFT OUTER JOIN users as ud ON jo.jo_deleted_by = ud.us_id
                        LEFT OUTER JOIN users as up ON jo.jo_publish_by = up.us_id
                        LEFT OUTER JOIN users as us ON jo.jo_start_by = us.us_id
                        LEFT OUTER JOIN users as uf ON jo.jo_finish_by = uf.us_id
                        LEFT OUTER JOIN users as um ON jo.jo_us_id = um.us_id
                        LEFT OUTER JOIN job_archive as joa ON jo.jo_joa_id = joa.joa_id
                        LEFT OUTER JOIN users as uca ON joa.joa_created_by = uca.us_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array|String $textColumn To store the text value of single select.
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, array $wheres = [], array $orders = []): array
    {
        $data = self::loadData($wheres, $orders, 20);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'jo_id');
    }


    /**
     * Function to get record for single select field.
     *
     * @param array $row To store the data.
     * @param string $prefix To store the prefix of data.
     *
     * @return string
     */
    public static function getStatus(array $row, string $prefix = 'jo'): string
    {
        $prefix .= '_';
        if (empty($row[$prefix . 'deleted_on']) === false) {
            $result = new LabelDanger(Trans::getWord('canceled'));
        } elseif (empty($row[$prefix . 'joa_id']) === false) {
            $result = new LabelDark(Trans::getWord('archived'));
        } elseif (empty($row[$prefix . 'finish_on']) === false) {
            $result = new LabelSuccess(Trans::getWord('finished'));
        } elseif (empty($row[$prefix . 'start_on']) === false) {
            $result = new LabelPrimary(Trans::getWord('inProgress'));
        } elseif (empty($row[$prefix . 'publish_on']) === false) {
            $result = new LabelInfo(Trans::getWord('published'));
        } else {
            $result = new LabelGray(Trans::getWord('draft'));
        }

        return $result;
    }


}
