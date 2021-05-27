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
 * Class to handle data access object for table notification_receiver.
 *
 * @package    app
 * @subpackage Model\Dao\System\Page
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class NotificationReceiverDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'nfr_id',
        'nfr_nf_id',
        'nfr_us_id',
        'nfr_delivered',
        'nfr_read_by',
        'nfr_read_on',
    ];

    /**
     * Base dao constructor for notification_receiver.
     *
     */
    public function __construct()
    {
        parent::__construct('notification_receiver', 'nfr', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table notification_receiver.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'nfr_read_by',
            'nfr_read_on',
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
        $wheres[] = '(nfr_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to update notification receiver.
     *
     * @param int $pageId To Store the id if the page.
     * @param int $userId To Store the id if the user.
     * @param int $systemSettingValue To store the system setting value.
     *
     * @return array
     */
    public static function getNotificationReceiverByUrlKeyAndUser($urlKey, $userId, $systemSettingValue): array
    {
        $wheres = [];
        $wheres[] = '(nfr.nfr_read_on IS NULL)';
        $wheres[] = '(nfr.nfr_us_id = ' . $userId . ')';
        $wheres[] = '(nf.nf_url_key = \'' . $urlKey . '\')';
        $wheres[] = '(nf.nf_ss_id = ' . $systemSettingValue . ')';

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
        $query = 'SELECT nfr.nfr_id, nfr.nfr_nf_id, nfr.nfr_us_id, nfr.nfr_delivered, 
                         nfr.nfr_read_by, nfr.nfr_read_on, nf.nf_id, nf.nf_url
                  FROM notification_receiver as nfr
                  INNER JOIN notification as nf on nf.nf_id = nfr.nfr_nf_id' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

}
