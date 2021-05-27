<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm\Quotation;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table quotation.
 *
 * @package    app
 * @subpackage Model\Dao\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class QuotationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'qt_id',
        'qt_ss_id',
        'qt_number',
        'qt_type',
        'qt_rel_id',
        'qt_of_id',
        'qt_cp_id',
        'qt_dl_id',
        'qt_order_of_id',
        'qt_us_id',
        'qt_commodity',
        'qt_requirement',
        'qt_start_date',
        'qt_end_date',
        'qt_approve_on',
        'qt_approve_by',
        'qt_qts_id',
    ];

    /**
     * Base dao constructor for quotation.
     *
     */
    public function __construct()
    {
        parent::__construct('quotation', 'qt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table quotation.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'qt_number',
            'qt_type',
            'qt_commodity',
            'qt_requirement',
            'qt_start_date',
            'qt_end_date',
            'qt_approve_on',
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
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $ssId);
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
        $query = 'SELECT qt.qt_id, qt.qt_ss_id, qt.qt_number, qt.qt_type, qt.qt_rel_id, rel.rel_name as qt_relation,
                            qt.qt_of_id, ro.of_name as qt_office, qt.qt_cp_id, cp.cp_name as qt_pic_relation,
                            qt.qt_dl_id, dl.dl_number as qt_deal, dl.dl_name as qt_deal_name, qt.qt_order_of_id, oo.of_name as qt_order_office,
                            qt.qt_us_id, us.us_name as qt_manager, qt.qt_commodity, qt.qt_requirement, qt.qt_start_date,
                            qt.qt_end_date, qt.qt_approve_on, ua.us_name as qt_approve_by, qt.qt_created_on, uc.us_name as qt_created_by,
                            qt.qt_deleted_on, qt.qt_deleted_reason, ud.us_name as qt_deleted_by,
                            qt.qt_qts_id, qts.qts_created_on as qt_qts_created_on, uc2.us_name as qt_qts_created_by, qts.qts_deleted_on as qt_qts_deleted_on,
                            qts.qts_deleted_reason as qt_qts_deleted_reason, ud2.us_name as qt_qts_deleted_by
                        FROM quotation as qt
                            INNER JOIN relation as rel ON qt.qt_rel_id = rel.rel_id
                            INNER JOIN office as ro ON qt.qt_of_id = ro.of_id
                            INNER JOIN office as oo ON qt.qt_order_of_id = oo.of_id
                            INNER JOIN users as us ON qt.qt_us_id = us.us_id
                            LEFT OUTER JOIN contact_person as cp ON qt.qt_cp_id = cp.cp_id
                            LEFT OUTER JOIN deal as dl ON qt.qt_dl_id = dl.dl_id
                            LEFT OUTER JOIN users as ua ON qt.qt_approve_by = ua.us_id
                            LEFT OUTER JOIN users as uc ON qt.qt_created_by = uc.us_id
                            LEFT OUTER JOIN users as ud ON qt.qt_deleted_by = ud.us_id
                            LEFT OUTER JOIN quotation_submit as qts ON qt.qt_qts_id = qts.qts_id
                            LEFT OUTER JOIN users as uc2 ON qts.qts_created_by = uc2.us_id
                            LEFT OUTER JOIN users as ud2 ON qts.qts_deleted_by = ud2.us_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY qt.qt_id DESC';
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
        $query = 'SELECT count(DISTINCT (qt.qt_id)) AS total_rows
                        FROM quotation as qt
                            INNER JOIN relation as rel ON qt.qt_rel_id = rel.rel_id
                            INNER JOIN office as ro ON qt.qt_of_id = ro.of_id
                            INNER JOIN office as oo ON qt.qt_order_of_id = oo.of_id
                            INNER JOIN users as us ON qt.qt_us_id = us.us_id
                            LEFT OUTER JOIN contact_person as cp ON qt.qt_cp_id = cp.cp_id
                            LEFT OUTER JOIN deal as dl ON qt.qt_dl_id = dl.dl_id
                            LEFT OUTER JOIN quotation_submit as qts ON qt.qt_qts_id = qts.qts_id
                            LEFT OUTER JOIN users as uc2 ON qts.qts_created_by = uc2.us_id
                            LEFT OUTER JOIN users as ud2 ON qts.qts_deleted_by = ud2.us_id ' . $strWhere;

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
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT qt.qt_id, qt.qt_number, qt.qt_type, qt.qt_rel_id, rel.rel_name as qt_relation
                        FROM quotation as qt
                            INNER JOIN relation as rel ON qt.qt_rel_id = rel.rel_id
                             LEFT OUTER JOIN quotation_submit as qts ON qt.qt_id = qts.qts_qt_id ' . $strWhere;
        $query .= ' GROUP BY qt.qt_id, qt.qt_number, qt.qt_type, qt.qt_rel_id, rel.rel_name';
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY qt.qt_number, qt.qt_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET 0';
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);

        return parent::doPrepareSingleSelectData($data, 'qt_number', 'qt_id');
    }

    /**
     * Function to get quotation status
     *
     * @param array $data To store the parameter data.
     *
     * @return string
     */
    public function getStatus(array $data = []): string
    {
        if (array_key_exists('qt_deleted_on', $data) === true && empty($data['qt_deleted_on']) === false) {
            return new LabelDanger(Trans::getWord('deleted'));
        }
        $expired = false;
        if (array_key_exists('qt_end_date', $data) === true && empty($data['qt_end_date']) === false) {
            $today = DateTimeParser::createFromFormat(date('Y-m-d') . ' 23:50:00');
            $endDate = DateTimeParser::createFromFormat($data['qt_end_date'] . ' 23:50:00');
            $expired = ($today > $endDate);
        }
        $result = new LabelDanger(Trans::getWord('expired'));
        if ($expired === false) {
            if (array_key_exists('qt_approve_on', $data) === true && empty($data['qt_approve_on']) === false) {
                $result = new LabelSuccess(Trans::getWord('approved'));
            } elseif (array_key_exists('qt_qts_id', $data) === true && empty($data['qt_qts_id']) === false) {
                if (empty($data['qt_qts_deleted_on']) === false) {
                    $result = new LabelDark(Trans::getWord('rejected'));
                } else {
                    $result = new LabelPrimary(Trans::getWord('submitted'));
                }
            } else {
                $result = new LabelGray(Trans::getWord('draft'));
            }
        }

        return $result;
    }

}
