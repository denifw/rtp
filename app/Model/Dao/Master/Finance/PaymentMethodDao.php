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
class PaymentMethodDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'pm_id',
        'pm_ss_id',
        'pm_name',
        'pm_active',
    ];

    /**
     * Base dao constructor for tax.
     *
     */
    public function __construct()
    {
        parent::__construct('payment_method', 'pm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table tax.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'pm_name',
            'pm_active',
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
     * @param int $ssId To store the System Settings ID.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(pm_id = ' . $referenceValue . ')';
        $wheres[] = '(pm_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (\count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get all the active record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $wheres = [];
        $wheres[] = "(pm_active = 'Y')";
        $wheres[] = '(pm_deleted_on IS NULL)';
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
        $query = 'SELECT pm_id, pm_name, pm_active, pm_ss_id
                FROM payment_method ' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, self::$Fields);
    }
}
