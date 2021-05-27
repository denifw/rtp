<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job\Warehouse\Bundling;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table job_bundling_detail.
 *
 * @package    app
 * @subpackage Model\Dao\Job\Warehouse\Bundling
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundlingDetailDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'jbd_id',
        'jbd_jb_id',
        'jbd_jog_id',
        'jbd_quantity',
        'jbd_lot_number',
        'jbd_serial_number',
        'jbd_us_id',
        'jbd_adjust_by',
        'jbd_start_on',
        'jbd_end_on',
    ];

    /**
     * Base dao constructor for job_bundling_detail.
     *
     */
    public function __construct()
    {
        parent::__construct('job_bundling_detail', 'jbd', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_bundling_detail.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'jbd_lot_number',
            'jbd_serial_number',
            'jbd_start_on',
            'jbd_end_on',
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
        $wheres[] = '(jbd.jbd_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jbId To store the reference value of the table.
     * @param int $usId To store the reference value of the table.
     *
     * @return array
     */
    public static function getInProgressBundle($jbId, $usId): array
    {
        $wheres = [];
        $wheres[] = '(jbd.jbd_jb_id = ' . $jbId . ')';
        $wheres[] = '(jbd.jbd_us_id = ' . $usId . ')';
        $wheres[] = '(jbd.jbd_start_on IS NOT NULL)';
        $wheres[] = '(jbd.jbd_end_on IS NULL)';
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }
        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $jbId To store the reference value of the table.
     *
     * @return array
     */
    public static function getByJobBundling($jbId): array
    {
        $wheres = [];
        $wheres[] = '(jbd.jbd_jb_id = ' . $jbId . ')';
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
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
        $query = 'SELECT jbd.jbd_id, jbd.jbd_jb_id, jbd.jbd_jog_id, jog.jog_gd_id as jbd_gd_id, 
                        gdu.gdu_id as jbd_gdu_id, uom.uom_code as jbd_uom_code, jbd.jbd_lot_number, 
                        jbd.jbd_serial_number, jbd.jbd_quantity, jbd.jbd_us_id, us.us_name as jbd_user,
                        jbd.jbd_start_on, jbd.jbd_end_on
                        FROM job_bundling_detail as jbd INNER JOIN
                        job_goods as jog on jog.jog_id = jbd.jbd_jog_id INNER JOIN
                        goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id INNER JOIN
                        unit as uom ON uom.uom_id = gdu.gdu_uom_id LEFT OUTER JOIN
                        users as us ON jbd.jbd_us_id = us.us_id' . $strWhere;
        $query .= ' ORDER BY us.us_name, jbd.jbd_end_on DESC, jbd.jbd_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);

    }


    /**
     * Function to get all record.
     *
     * @param int $jbId To store the limit of the data.
     *
     * @return float
     */
    public static function getTotalCompleteQuantity($jbId): float
    {
        $result = 0.0;
        $wheres[] = '(jbd_jb_id = ' . $jbId . ')';
        $wheres[] = '(jbd_end_on IS NOT NULL)';
        $wheres[] = '(jbd_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jbd_jb_id, SUM(jbd_quantity) as total
                        FROM job_bundling_detail ' . $strWhere;
        $query .= ' GROUP BY jbd_jb_id ';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (float)DataParser::objectToArray($sqlResults[0])['total'];
        }

        return $result;

    }


}
