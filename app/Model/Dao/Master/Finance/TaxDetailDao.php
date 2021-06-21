<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Dao\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table tax.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class TaxDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'td_id',
        'td_tax_id',
        'td_name',
        'td_percent',
    ];
    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'td_percent',
    ];

    /**
     * Base dao constructor for tax.
     *
     */
    public function __construct()
    {
        parent::__construct('tax_detail', 'td', self::$Fields);
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
        $wheres[] = SqlHelper::generateStringCondition('td_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by tax id
     *
     * @param string $taxId To store the Tax Id Value.
     *
     * @return array
     */
    public static function getByTaxId(string $taxId): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('td_tax_id', $taxId);
        $wheres[] = SqlHelper::generateNullCondition('td_deleted_on');
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list condition query.
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
        $query = 'SELECT td_id, td_name, td_percent, td_tax_id
                        FROM tax_detail ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY td_name, td_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record.
     *
     * @param string $taxId To store the limit of the data.
     * @param ?string $detailId To store the limit of the data.
     *
     * @return float
     */
    public static function getTotalPercentageByTaxId(string $taxId, ?string $detailId): float
    {
        $result = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('td_tax_id', $taxId);
        $wheres[] = SqlHelper::generateNullCondition('td_deleted_on');
        if (empty($detailId) === false) {
            $wheres[] = SqlHelper::generateStringCondition('td_id', $detailId, '<>');
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT td_tax_id, SUM(td_percent) as total_percent
                        FROM tax_detail ' . $strWhere;
        $query .= ' GROUP BY td_tax_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (float)DataParser::objectToArray($sqlResults[0])['total_percent'];
        }

        return $result;
    }
}
