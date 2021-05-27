<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Notification;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table notification.
 *
 * @package    app
 * @subpackage Model\Dao\System\Page
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class NotificationDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'nf_id', 'nf_ss_id', 'nf_nt_id',
        'nf_url', 'nf_url_key', 'nf_message_parameter',
    ];

    /**
     * Base dao constructor for notification.
     *
     */
    public function __construct()
    {
        parent::__construct('notification', 'nf', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table notification.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'nf_url',
            'nf_url_key',
            'nf_message_parameter',
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
        $wheres[] = '(nf_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function load unread notification.
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
        $query = 'SELECT nf.nf_id, nf.nf_ss_id, nf.nf_nt_id, nf.nf_url, nf.nf_url_key, nf.nf_message_parameter, nf.nf_created_on,
                         nfr.nfr_id, nfr.nfr_read_on, nfr.nfr_delivered,
                         nt.nt_id, nt.nt_code, cp.cp_name
                  FROM notification AS nf 
                  INNER JOIN notification_receiver AS nfr ON nfr.nfr_nf_id = nf.nf_id
                  INNER JOIN notification_template AS nt ON nt.nt_id = nf.nf_nt_id
                  INNER JOIN user_mapping AS ump ON ump.ump_us_id = nf.nf_created_by AND ump.ump_ss_id = nf.nf_ss_id
                  INNER JOIN contact_person AS cp ON cp.cp_id = ump.ump_cp_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get total record.
     *
     * @param array $wheres To store the list condition query.
     *
     * @return int
     */
    public static function loadTotalData(array $wheres = []): int
    {
        $result = 0;
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT count(DISTINCT (nf.nf_id)) AS total_rows
                  FROM notification AS nf 
                  INNER JOIN notification_receiver AS nfr ON nfr.nfr_nf_id = nf.nf_id
                  INNER JOIN notification_template AS nt ON nt.nt_id = nf.nf_nt_id
                  INNER JOIN user_mapping AS ump ON ump.ump_us_id = nf.nf_created_by AND ump.ump_ss_id = nf.nf_ss_id
                  INNER JOIN contact_person AS cp ON cp.cp_id = ump.ump_cp_id' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

}
