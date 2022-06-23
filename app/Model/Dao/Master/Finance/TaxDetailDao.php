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
        $helper = new SqlHelper();
        $helper->addStringWhere('td.td_id', $referenceValue);
        $data = self::loadData($helper);
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
        $helper = new SqlHelper();
        $helper->addStringWhere('td.td_tax_id', $taxId);
        $helper->addNullWhere('td.td_deleted_on');
        return self::loadData($helper);
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
            $helper->addOrderByString('tax.tax_name, td.td_id');
        }
        $query = 'SELECT td.td_id, td.td_tax_id, td.td_child_tax_id, tax.tax_name as td_name, tax.tax_percent as td_percent
                        FROM tax_detail as td
                            INNER JOIN tax as tax ON td.td_child_tax_id = tax.tax_id' . $helper;
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

    /**
     * Function to get all record.
     *
     * @param string $taxId To store the limit of the data.
     *
     * @return float
     */
    public static function getTotalPercentageByTaxId(string $taxId): float
    {
        $result = 0.0;
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('td.td_tax_id', $taxId);
        $wheres[] = SqlHelper::generateNullCondition('td.td_deleted_on');
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT td.td_tax_id, SUM(tax.tax_percent) as total_percent
                        FROM tax_detail as td
                            INNER JOIN tax as tax ON td.td_child_tax_id = tax.tax_id ' . $strWhere;
        $query .= ' GROUP BY td.td_tax_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (float)DataParser::objectToArray($sqlResults[0])['total_percent'];
        }

        return $result;
    }
}
