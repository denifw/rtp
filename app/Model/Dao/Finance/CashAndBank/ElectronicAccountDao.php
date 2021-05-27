<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Finance\CashAndBank;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table electronic_account.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicAccountDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ea_id',
        'ea_ss_id',
        'ea_code',
        'ea_description',
        'ea_cur_id',
        'ea_us_id',
        'ea_block_by',
        'ea_block_on',
        'ea_block_reason',
    ];

    /**
     * Base dao constructor for electronic_account.
     *
     */
    public function __construct()
    {
        parent::__construct('electronic_account', 'ea', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table electronic_account.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ea_code',
            'ea_description',
            'ea_block_on',
            'ea_block_reason',
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
    public static function getByReference(int $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ea.ea_id', $referenceValue);
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
    public static function getByReferenceAndSystem(int $referenceValue, int $ssId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ea.ea_id', $referenceValue);
        $wheres[] = SqlHelper::generateNumericCondition('ea.ea_ss_id', $ssId);
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
        $query = 'SELECT ea.ea_id, ea.ea_ss_id, ea.ea_code, ea.ea_description, ea.ea_us_id, us.us_name as ea_user,
                        ea.ea_created_on, uc.us_name as ea_created_by, ea.ea_deleted_on, ud.us_name as ea_deleted_by,
                        ea.ea_deleted_reason, ea.ea_block_on, ea.ea_block_reason, ub.us_name as ea_block_by,
                        (CASE when eb.balance IS NULL THEN 0.0 ELSE eb.balance END) as ea_balance,
                        ea.ea_cur_id, cur.cur_iso as ea_currency
                        FROM electronic_account as ea
                            INNER JOIN currency as cur ON ea.ea_cur_id = cur.cur_id
                            INNER JOIN users as uc ON ea.ea_created_by = uc.us_id
                            LEFT OUTER JOIN users as us ON ea.ea_us_id = us.us_id
                            LEFT OUTER JOIN users as ud ON ea.ea_deleted_by = ud.us_id
                            LEFT OUTER JOIN users as ub ON ea.ea_block_by = ub.us_id
                            LEFT OUTER JOIN (SELECT eb_ea_id, SUM(eb_amount) as balance
                                                FROM electronic_balance
                                                WHERE eb_deleted_on IS NULL
                                                GROUP BY eb_ea_id) as eb ON ea.ea_id = eb.eb_ea_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY ea.ea_deleted_on DESC, ea.ea_code, ea.ea_id';
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
        $query = 'SELECT count(DISTINCT (ea.ea_id)) AS total_rows
                    FROM electronic_account as ea
                            INNER JOIN currency as cur ON ea.ea_cur_id = cur.cur_id
                            INNER JOIN users as uc ON ea.ea_created_by = uc.us_id
                            LEFT OUTER JOIN users as us ON ea.ea_us_id = us.us_id
                            LEFT OUTER JOIN users as ud ON ea.ea_deleted_by = ud.us_id
                            LEFT OUTER JOIN users as ub ON ea.ea_block_by = ub.us_id ' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, $textColumn, 'ea_id');
    }


}
