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
 * Class to handle data access object for table renewal_order_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrderDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'rnd_id', 'rnd_rno_id', 'rnd_rnt_id', 'rnd_est_cost', 'rnd_remark',
    ];

    /**
     * Base dao constructor for renewal_order_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('renewal_order_detail', 'rnd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table renewal_order_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'rnd_remark',
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
        $wheres[] = '(rnd.rnd_id = ' . $referenceValue . ')';
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
        $query = 'SELECT rnd.rnd_id, rnd.rnd_rno_id, rnd.rnd_rnt_id, rnd.rnd_est_cost, rnd.rnd_remark,
                         rnt.rnt_name AS rnd_rnt_name
                  FROM   renewal_order_detail AS rnd INNER JOIN
                         renewal_type AS rnt ON rnt.rnt_id = rnd.rnd_rnt_id' . $strWhere;
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


}
