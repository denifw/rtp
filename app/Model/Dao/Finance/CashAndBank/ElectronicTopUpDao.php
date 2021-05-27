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
 * Class to handle data access object for table electronic_top_up.
 *
 * @package    app
 * @subpackage Model\Dao\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicTopUpDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'et_id',
        'et_ea_id',
        'et_ba_id',
        'et_date',
        'et_amount',
        'et_notes',
        'et_doc_id',
        'et_bab_id',
        'et_eb_id',
    ];

    /**
     * Base dao constructor for electronic_top_up.
     *
     */
    public function __construct()
    {
        parent::__construct('electronic_top_up', 'et', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table electronic_top_up.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'et_date',
            'et_notes',
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
        $wheres[] = SqlHelper::generateNumericCondition('et.et_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $eaId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByAccount(int $eaId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('et.et_ea_id', $eaId);
        $wheres[] = SqlHelper::generateNullCondition('et.et_deleted_on');
        return self::loadData($wheres);
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
        $query = 'SELECT et.et_id, et.et_ea_id, et.et_ba_id, ba.ba_code as et_ba_code, ba.ba_description as et_ba_description,
                            et.et_amount, et.et_date, et.et_notes, et.et_doc_id, et.et_eb_id, et.et_bab_id,
                            et.et_created_on, et.et_created_by, uc.us_name as et_registered_by
                        FROM electronic_top_up as et
                           INNER JOIN bank_account as ba ON et.et_ba_id = ba.ba_id
                           INNER JOIN users as uc ON et.et_created_by = uc.us_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY et.et_deleted_on DESC, et.et_date DESC, et.et_id DESC';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }
}
