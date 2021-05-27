<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm\Quotation;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use App\Frame\Formatter\SqlHelper;

/**
 * Class to handle data access object for table quotation_terms.
 *
 * @package    app
 * @subpackage Model\Dao\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class QuotationTermsDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'qtm_id',
        'qtm_qt_id',
        'qtm_terms',
    ];

    /**
     * Base dao constructor for quotation_terms.
     *
     */
    public function __construct()
    {
        parent::__construct('quotation_terms', 'qtm', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table quotation_terms.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'qtm_terms',
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
    public static function getByReference($referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('qtm_id', $referenceValue);
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by quotation id
     *
     * @param int $qtId To store the reference quotation table.
     *
     * @return array
     */
    public static function getByQuotationId($qtId): array
    {
        $wheres = [];
        $wheres[] = '(qtm_deleted_on IS NULL)';
        $wheres[] = SqlHelper::generateNumericCondition('qtm_qt_id', $qtId);
        return self::loadData($wheres);
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT qtm_id, qtm_terms
                        FROM quotation_terms' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY qtm_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $row['qtm_terms'] = json_decode($row['qtm_terms'], true);
            $results[] = $row;
        }
        return $results;
    }
}
