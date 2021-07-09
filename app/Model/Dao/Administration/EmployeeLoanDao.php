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

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table employee_loan.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class EmployeeLoanDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'el_id',
        'el_ss_id',
        'el_em_id',
        'el_number',
        'el_amount',
        'el_notes',
        'el_approve_by',
        'el_approve_on',
        'el_pay_date',
        'el_paid_by',
        'el_paid_on',
        'el_ba_id',
        'el_bab_id',
        'el_elb_id',
        'el_elr_id',
        'el_type'
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'el_amount',
    ];

    /**
     * Base dao constructor for employee_loan.
     *
     */
    public function __construct()
    {
        parent::__construct('employee_loan', 'el', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('el_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('el_ss_id', $ssId);
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
        $query = 'SELECT el.el_id, el.el_number, el.el_ss_id, el.el_em_id, em.em_name as el_employee, el.el_amount, el.el_notes,
                        el.el_arrove_on, ua.us_name as el_approve_by, el.el_pay_date, el.el_paid_on, up.us_name as el_paid_by,
                        el.el_ba_id, ba.ba_description as el_bank_account, el.el_bab_id, el.el_elb_id,
                        el.el_elr_id, elr.elr_created_on as el_request_on, urq.us_name as el_request_by, elr.elr_deleted_reason as el_reject_reason,
                        elr.elr_deleted_on as el_reject_on, urj.us_name as el_reject_by, el.el_deleted_on, el.el_deleted_reason,
                        ud.us_name as el_deleted_by, el.el_type
                    FROM employee_loan as el
                        INNER JOIN employee as em ON em.em_id = el.el_em_id
                        INNER JOIN users as uc ON el.el_created_by = uc.us_id
                        LEFT OUTER JOIN users as ua ON el.el_approve_by = ua.us_id
                        LEFT OUTER JOIN users as up ON el.el_paid_by = up.us_id
                        LEFT OUTER JOIN bank_account as ba ON el.el_ba_id = ba.ba_id
                        LEFT OUTER JOIN employee_loan_request as elr ON el.el_elr_id = elr.elr_id
                        LEFT OUTER JOIN users as urq ON elr.elr_created_by = urq.us_id
                        LEFT OUTER JOIN users as erj ON elr.elr_deleted_by = urj.us_id
                        LEFT OUTER JOIN users as ud ON el.el_deleted_by = ud.us_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (el.el_id)) AS total_rows
                    FROM employee_loan as el
                        INNER JOIN employee as em ON em.em_id = el.el_em_id
                        INNER JOIN users as uc ON el.el_created_by = uc.us_id
                        LEFT OUTER JOIN users as ua ON el.el_approve_by = ua.us_id
                        LEFT OUTER JOIN users as up ON el.el_paid_by = up.us_id
                        LEFT OUTER JOIN bank_account as ba ON el.el_ba_id = ba.ba_id
                        LEFT OUTER JOIN employee_loan_request as elr ON el.el_elr_id = elr.elr_id
                        LEFT OUTER JOIN users as urq ON elr.elr_created_by = urq.us_id
                        LEFT OUTER JOIN users as erj ON elr.elr_deleted_by = urj.us_id
                        LEFT OUTER JOIN users as ud ON el.el_deleted_by = ud.us_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'el_id');
    }


}
