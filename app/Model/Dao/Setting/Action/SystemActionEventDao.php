<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Setting\Action;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table system_action_event.
 *
 * @package    app
 * @subpackage Model\Dao\Master\Action
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemActionEventDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sae_id',
        'sae_sac_id',
        'sae_description',
        'sae_order',
        'sae_active',
    ];

    /**
     * Base dao constructor for system_action_event.
     *
     */
    public function __construct()
    {
        parent::__construct('system_action_event', 'sae', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table system_action_event.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'sae_description',
            'sae_active',
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
        $where[] = '(sae.sae_id = ' . $referenceValue . ')';

        return self::loadData($where)[0];
    }

    /**
     * Function to get data by system action
     *
     * @param int $sacId To store the id of system action table.
     *
     * @return array
     */
    public static function getBySystemActionId($sacId): array
    {
        $where = [];
        $where[] = '(sae.sae_sac_id = ' . $sacId . ')';

        return self::loadData($where);
    }


    /**
     * Function to get all active record.
     *
     * @return array
     */
    public static function loadActiveData(): array
    {
        $where = [];
        $where[] = "(sae.sae_active = 'Y')";
        $where[] = '(sae.sae_deleted_on IS NULL)';

        return self::loadData($where);

    }

    /**
     * Function to get last order data.
     *
     * @param int $sacId To store the system action id.
     *
     * @return int
     */
    public static function getLastOrderData($sacId): int
    {
        $result = 0;
        $wheres = [];
        $wheres[] = '(sae_sac_id = ' . $sacId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sae_order
                      FROM system_action_event ' . $strWhere;
        $query .= ' ORDER BY sae_order DESC';
        $query .= ' LIMIT 1 OFFSET 0';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $result = DataParser::objectToArray($sqlResult[0], ['sae_order'])['sae_order'];
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
        $query = 'SELECT sae.sae_id, sae.sae_sac_id, ac.ac_id as sae_ac_id, ac.ac_description as sae_action, sae.sae_description, sae.sae_active, sae.sae_order
                        FROM system_action_event as sae INNER JOIN
                        system_action as sac ON sae.sae_sac_id = sac.sac_id INNER JOIN
                        action as ac ON sac.sac_ac_id = ac.ac_id ' . $strWhere;
        $query .= ' ORDER BY sac.sac_order, sae.sae_active DESC, sae.sae_order';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result, array_merge(self::$Fields, [
            'sae_ac_id',
            'sae_action',
        ]));

    }


}
