<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Service;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table action.
 *
 * @package    app
 * @subpackage Model\Dao\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ActionDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ac_id',
        'ac_srt_id',
        'ac_code',
        'ac_description',
        'ac_order',
        'ac_style',
    ];

    /**
     * Base dao constructor for action.
     *
     */
    public function __construct()
    {
        parent::__construct('action', 'ac', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table action.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ac_code',
            'ac_description',
            'ac_style',
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
        $where = [];
        $where[] = '(ac.ac_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by Service term
     *
     * @param int $srtId To store the service term id.
     *
     * @return array
     */
    public static function getByServiceTermId($srtId): array
    {
        $wheres = [];
        $wheres[] = '(ac.ac_srt_id = ' . $srtId . ')';
        $wheres[] = '(ac.ac_deleted_on IS NULL)';

        return self::loadData($wheres);
    }

    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(ac.ac_active = 'Y')";
        $where[] = '(ac.ac_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ac.ac_id, ac.ac_srt_id, srt.srt_name as ac_service_term, ac.ac_code, ac.ac_description, ac.ac_order, ac.ac_style
                        FROM action as ac INNER JOIN
                        service_term as srt ON ac.ac_srt_id = srt.srt_id ' . $strWhere;
        $query .= ' ORDER BY ac.ac_srt_id, ac.ac_order';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, ['ac_service_term']));

    }


}
