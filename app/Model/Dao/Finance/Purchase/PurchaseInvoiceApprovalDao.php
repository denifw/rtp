<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Purchase;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table purchase_invoice_approval.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PurchaseInvoiceApprovalDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pia_id',
        'pia_pi_id',
        'pia_reject_reason',
    ];

    /**
     * Base dao constructor for purchase_invoice_approval.
     *
     */
    public function __construct()
    {
        parent::__construct('purchase_invoice_approval', 'pia', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table purchase_invoice_approval.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pia_reject_reason',
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
     * @param int $piId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByPiId($piId): array
    {
        $wheres = [];
        $wheres[] = '(pia.pia_pi_id = ' . $piId . ')';
        return self::loadData($wheres);
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue     To store the reference value of the table.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(pia_id = ' . $referenceValue . ')';
        $wheres[] = '(pia_ss_id = ' . $systemSettingValue . ')';
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
        $query = 'SELECT pia.pia_id, pia.pia_pi_id, pia.pia_created_on, uc.us_name as pia_created_by, pia.pia_reject_reason,
                            pia.pia_deleted_on, ud.us_name as pia_deleted_by
                        FROM purchase_invoice_approval as pia LEFT OUTER JOIN
                        users as uc ON pia.pia_created_by = uc.us_id LEFT OUTER JOIN
                        users as ud ON pia.pia_deleted_by = ud.us_id ' . $strWhere;
        $query .= ' ORDER BY pia.pia_deleted_on DESC';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
