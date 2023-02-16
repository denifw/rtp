<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2023 Deni Firdaus Waruwu.
 */

namespace App\Model\Dao;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table rt_pintar.
 *
 * @package    app
 * @subpackage Model\Dao\
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2023 Deni Firdaus Waruwu.
 */
class RtPintarDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rtp_code',
        'rtp_description',
        'rtp_amount',
        'rtp_month',
        'rtp_year',
        'rtp_status',
        'rtp_status_text',
        'rtp_payment_time',
        'rtp_contact',
        'rtp_block',
        'rtp_number',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'rtp_amount',
        'rtp_month',
        'rtp_year',
    ];

    /**
     * Base dao constructor for rt_pintar.
     *
     */
    public function __construct()
    {
        parent::__construct('rt_pintar', 'rtp', self::$Fields);
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
        $helper = new SqlHelper();
        $helper->addStringWhere('rtp_id', $referenceValue);

        $data = self::loadData($helper);
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
        $helper = new SqlHelper();
        $helper->addStringWhere('rtp_id', $referenceValue);
        $helper->addStringWhere('rtp_ss_id', $ssId);

        $data = self::loadData($helper);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get all record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadData(SqlHelper $helper): array
    {
        if ($helper->hasOrderBy() === false) {
            $helper->addOrderBy('rtp_number, rtp_year, rtp_month, rtp_code');
        }

        $query = 'SELECT rtp_code, rtp_description, rtp_amount, rtp_month, rtp_year, rtp_status_text,
                        rtp_status, rtp_payment_time, rtp_contact, rtp_block, rtp_number
                        FROM rt_pintar ' . $helper;
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get total record.
     *
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(SqlHelper $helper): int
    {
        $result = 0;
        $query = 'SELECT count(DISTINCT (rtp_id)) AS total_rows
                        FROM rt_pintar' . $helper->getConditionForCountData();

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
     * @param SqlHelper $helper To store the list condition query.
     * @param array $numericFields To store the list sorting query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper, array $numericFields = []): array
    {
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'rtp_id', $numericFields);
    }

    /**
     * Function to get record for single select field.
     *
     * @return void
     */
    public function clearData(): void
    {
        $query = 'DELETE FROM rt_pintar WHERE rtp_code IS NOT NULL';
        DB::statement($query);
    }

}
