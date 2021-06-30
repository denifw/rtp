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
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table purchase_invoice.
 *
 * @package    app
 * @subpackage Model\Dao\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PurchaseInvoiceDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pi_id',
        'pi_ss_id',
        'pi_number',
        'pi_reference',
        'pi_rel_id',
        'pi_cp_id',
        'pi_date',
        'pi_due_date',
        'pi_notes',
        'pi_ba_id',
        'pi_bab_id',
        'pi_pay_date',
        'pi_paid_on',
        'pi_paid_by',
        'pi_verified_on',
        'pi_verified_by',
    ];

    /**
     * Base dao constructor for purchase_invoice.
     *
     */
    public function __construct()
    {
        parent::__construct('purchase_invoice', 'pi', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('pi.pi_id', $referenceValue);
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
        $wheres[] = SqlHelper::generateStringCondition('pi.pi_id', $referenceValue);
        $wheres[] = SqlHelper::generateStringCondition('pi.pi_ss_id', $ssId);
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
        $query = 'SELECT pi.pi_id, pi.pi_ss_id, pi.pi_number, pi.pi_reference, pi.pi_rel_id, rel.rel_name as pi_vendor,
                        pi.pi_cp_id, cp.cp_name as pi_pic_vendor, pi.pi_date, pi.pi_due_date, pi.pi_ba_id, ba.ba_description as pi_bank_account,
                        pi.pi_bab_id, pi.pi_notes, pi.pi_paid_on, up.us_name as pi_paid_by, pi.pi_verified_on, uv.us_name as pi_verified_by,
                        pi.pi_created_on, uc.us_name as pi_created_by, pi.pi_deleted_on, pi.pi_deleted_reason, ud.us_name as pi_deleted_by,
                        pid.total as pi_total, pi.pi_pay_date
                    FROM purchase_invoice as pi
                    INNER JOIN users as uc ON pi.pi_created_by = uc.us_id
                    LEFT OUTER JOIN relation as rel ON pi.pi_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as cp ON pi.pi_cp_id = cp.cp_id
                    LEFT OUTER JOIN bank_account as ba ON pi.pi_ba_id = ba.ba_id
                    LEFT OUTER JOIN users as up ON pi.pi_paid_by = up.us_id
                    LEFT OUTER JOIN users as uv ON pi.pi_verified_by = uv.us_id
                    LEFT OUTER JOIN users as ud ON pi.pi_deleted_by = ud.us_id
                    LEFT OUTER JOIN (SELECT pid_pi_id, SUM(pid_total) as total
                        FROM purchase_invoice_detail
                        WHERE (pid_deleted_on IS NULL)
                        GROUP BY pid_pi_id) as pid ON pi.pi_id = pid.pid_pi_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY pi.pi_deleted_on DESC, pi.pi_verified_on DESC, pi.pi_paid_on DESC, pi.pi_created_on, pi.pi_id';
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
        $query = 'SELECT count(DISTINCT (pi.pi_id)) AS total_rows
                        FROM purchase_invoice as pi
                    INNER JOIN users as uc ON pi.pi_created_by = uc.us_id
                    LEFT OUTER JOIN relation as rel ON pi.pi_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as cp ON pi.pi_cp_id = cp.cp_id
                    LEFT OUTER JOIN bank_account as ba ON pi.pi_ba_id = ba.ba_id
                    LEFT OUTER JOIN users as up ON pi.pi_paid_by = up.us_id
                    LEFT OUTER JOIN users as uv ON pi.pi_verified_by = uv.us_id
                    LEFT OUTER JOIN users as ud ON pi.pi_deleted_by = ud.us_id
                    LEFT OUTER JOIN (SELECT pid_pi_id, SUM(pid_total) as total
                        FROM purchase_invoice_detail
                        WHERE (pid_deleted_on IS NULL)
                        GROUP BY pid_pi_id) as pid ON pi.pi_id = pid.pid_pi_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'pi_id');
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
        if (empty($data['pi_deleted_on']) === false) {
            $result = new LabelDanger(Trans::getWord('deleted'));
        } elseif (empty($data['pi_verified_id']) === false) {
            $result = new LabelSuccess(Trans::getWord('verified'));
        } elseif (empty($data['pi_paid_on']) === false) {
            $result = new LabelPrimary(Trans::getWord('unVerified'));
        } else {
            $result = new LabelWarning(Trans::getWord('unPaid'));
        }

        return $result;
    }


}
