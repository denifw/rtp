<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table bank.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class BankDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'bn_id',
        'bn_short_name',
        'bn_name',
        'bn_active',
    ];

    /**
     * Base dao constructor for bank.
     *
     */
    public function __construct()
    {
        parent::__construct('bank', 'bn', self::$Fields);
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
        $helper->addStringWhere('bn_id', $referenceValue);
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
            $helper->addOrderByString('bn_name, bn_id');
        }
        $query = 'SELECT bn_id, bn_short_name, bn_name, bn_active
                        FROM bank ' . $helper;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

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
        $query = 'SELECT count(DISTINCT (bn_id)) AS total_rows
                        FROM bank ' . $helper->getConditionForCountData();

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }
        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param SqlHelper $helper To store the list condition query.
     *
     * @return array
     */
    public static function loadSingleSelectData($textColumn, SqlHelper $helper): array
    {
        $helper->setLimit(20);
        $data = self::loadData($helper);

        return parent::doPrepareSingleSelectData($data, $textColumn, 'bn_id');
    }

}
