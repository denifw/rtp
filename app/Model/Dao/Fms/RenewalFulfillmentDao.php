<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Fms;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table renewal_fulfillment.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalFulfillmentDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rnf_id', 'rnf_rnrm_id', 'rnf_rno_id', 'rnf_expiry_date', 'rnf_fulfillment_date'
    ];

    /**
     * Base dao constructor for renewal_fulfillment.
     *
     */
    public function __construct()
    {
        parent::__construct('renewal_fulfillment', 'rnf', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table renewal_fulfillment.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rnf_expiry_date', 'rnf_fulfillment_date'
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
        $wheres[] = '(rnf.rnf_id = ' . $referenceValue . ')';
        $results = self::loadData($wheres);
        $result = [];
        if (\count($results) === 1) {
            $result = $results[0];
        }

        return $result;
    }


    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderList To store the list for sortir.
     * @param int   $limit  To store the limit of the data.
     * @param int   $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderList = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT rnf.rnf_id, rnf.rnf_rnrm_id, rnf.rnf_rno_id, rnf.rnf_expiry_date, 
                         rnf.rnf_fulfillment_date, rno.rno_number AS rnf_rno_number
                  FROM renewal_fulfillment AS rnf INNER JOIN
                       renewal_reminder AS rnrm ON rnrm.rnrm_id = rnf.rnf_rnrm_id INNER JOIN
                       renewal_order AS rno ON rno.rno_id = rnf.rnf_rno_id' . $strWhere;
        if (empty($orderList) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderList);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
