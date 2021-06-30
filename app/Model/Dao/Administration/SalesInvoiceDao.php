<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao\Administration;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table sales_invoice.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'si_id',
        'si_ss_id',
        'si_number',
        'si_rel_id',
        'si_of_id',
        'si_cp_id',
        'si_jo_id',
        'si_pt_id',
        'si_pm_id',
        'si_ba_id',
        'si_bab_id',
        'si_bab_id',
        'si_date',
        'si_submit_on',
        'si_submit_by',
        'si_due_date',
        'si_pay_date',
        'si_paid_on',
        'si_paid_by',
    ];

    /**
     * Base dao constructor for sales_invoice.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_invoice', 'si', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('si.si_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateStringCondition('si.si_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('si.si_ss_id', $ssId);
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
        $query = 'SELECT si.si_id, si.si_ss_id, si.si_number, si.si_rel_id, rel.rel_name as si_customer, si.si_of_id,
                        o.of_name as si_of_customer, si.si_cp_id, cp.cp_name as si_pic_customer, si.si_jo_id, jo.jo_number as si_jo_number,
                        jo.jo_name as si_jo_name, si.si_pt_id, pt.pt_name as si_payment_terms, pt.pt_days as si_pt_days,
                        si.si_pm_id, pm.pm_name as si_payment_method, si.si_ba_id, ba.ba_description as si_bank_account,
                        si.si_bab_id, si.si_date, si.si_submit_on, us.us_name as si_submit_by, si.si_due_date, si.si_pay_date,
                        si.si_paid_on, up.us_name as si_paid_by, si.si_created_on, uc.us_name as si_created_by, si.si_deleted_on,
                        si.si_deleted_reason, ud.us_name as si_deleted_by, sid.total as si_total
                    FROM sales_invoice as si
                    INNER JOIN relation as rel ON si.si_rel_id = rel.rel_id
                    INNER JOIN office as o ON si.si_of_id = o.of_id
                    INNER JOIN contact_person as cp ON si.si_cp_id = cp.cp_id
                    INNER JOIN payment_terms as pt ON si.si_pt_id = pt.pt_id
                    INNER JOIN users as uc ON si.si_created_by = uc.us_id
                    LEFT OUTER JOIN job_order as jo ON si.si_jo_id = jo.jo_id
                    LEFT OUTER JOIN payment_method as pm ON si.si_pm_id = pm.pm_id
                    LEFT OUTER JOIN bank_account as ba ON si.si_ba_id = ba.ba_id
                    LEFT OUTER JOIN users as us ON si.si_submit_by = us.us_id
                    LEFT OUTER JOIN users as up ON si.si_paid_by = up.us_id
                    LEFT OUTER JOIN users as ud ON si.si_deleted_by = ud.us_id
                    LEFT OUTER JOIN (SELECT sid_si_id, SUM(sid_total) as total
                        FROM sales_invoice_detail
                        WHERE (sid_deleted_on IS NULL)
                        GROUP BY sid_si_id) as sid ON si.si_id = sid.sid_si_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY si.si_deleted_on DESC, si.si_paid_on DESC, si.si_submit_on DESC, si.si_created_on DESC, si.si_id';
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
        $query = 'SELECT count(DISTINCT (si.si_id)) AS total_rows
                        FROM sales_invoice as si
                    INNER JOIN relation as rel ON si.si_rel_id = rel.rel_id
                    INNER JOIN office as o ON si.si_of_id = o.of_id
                    INNER JOIN contact_person as cp ON si.si_cp_id = cp.cp_id
                    INNER JOIN payment_terms as pt ON si.si_pt_id = pt.pt_id
                    INNER JOIN users as uc ON si.si_created_by = uc.us_id
                    LEFT OUTER JOIN job_order as jo ON si.si_jo_id = jo.jo_id
                    LEFT OUTER JOIN payment_method as pm ON si.si_pm_id = pm.pm_id
                    LEFT OUTER JOIN bank_account as ba ON si.si_ba_id = ba.ba_id
                    LEFT OUTER JOIN users as us ON si.si_submit_by = us.us_id
                    LEFT OUTER JOIN users as up ON si.si_paid_by = up.us_id
                    LEFT OUTER JOIN users as ud ON si.si_deleted_by = ud.us_id
                    LEFT OUTER JOIN (SELECT sid_si_id, SUM(sid_total) as total
                        FROM sales_invoice_detail
                        WHERE (sid_deleted_on IS NULL)
                        GROUP BY sid_si_id) as sid ON si.si_id = sid.sid_si_id ' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'si_id');
    }


    /**
     * Function to get record for single select field.
     *
     * @param array $data to store the data.
     *
     * @return string
     */
    public static function generateStatus(array $data): string
    {
        if (empty($data['si_deleted_on']) === false) {
            $result = new LabelDanger(Trans::getWord('deleted'));
        } elseif (empty($data['si_paid_on']) === false) {
            $result = new LabelSuccess(Trans::getWord('paid'));
        } elseif (empty($data['si_submit_on']) === false) {
            $result = new LabelPrimary(Trans::getWord('submitted'));
        } else {
            $result = new LabelGray(Trans::getWord('draft'));
        }

        return $result;
    }

}
