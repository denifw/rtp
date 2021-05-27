<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Finance\Sales;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table sales_invoice_approval.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesInvoiceApprovalDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sia_id',
        'sia_si_id',
        'sia_reject_reason',
    ];

    /**
     * Base dao constructor for sales_invoice_approval.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_invoice_approval', 'sia', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_invoice_approval.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sia_reject_reason',
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
     * @param int $siId To store the reference value of the table.
     *
     * @return array
     */
    public static function getBySalesInvoice($siId): array
    {
        $wheres = [];
        $wheres[] = '(sia.sia_si_id = ' . $siId . ')';
        return self::loadData($wheres);
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
        $query = 'SELECT sia.sia_id, sia.sia_si_id, sia.sia_reject_reason, sia.sia_created_on, uc.us_name as sia_created_by,
                            sia.sia_deleted_on, ud.us_name as sia_deleted_by
                        FROM sales_invoice_approval as sia LEFT OUTER JOIN
                            users as uc ON sia.sia_created_by = uc.us_id LEFT OUTER JOIN
                            users as ud ON sia.sia_deleted_by = ud.us_id ' . $strWhere;
        $query .= ' ORDER BY sia.sia_deleted_ON DESC';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
