<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\System\Notification;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table notification_template.
 *
 * @package    app
 * @subpackage Model\Dao\System\Page
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class NotificationTemplateDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'nt_id', 'nt_code', 'nt_module', 'nt_description',
        'nt_message_fields', 'nt_mail_path'
    ];

    /**
     * Base dao constructor for notification_template.
     *
     */
    public function __construct()
    {
        parent::__construct('notification_template', 'nt', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table notification_template.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'nt_code', 'nt_module', 'nt_description',
            'nt_message_fields', 'nt_mail_path', 'nt_active'
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
        $wheres[] = '(nt_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        $result = [];
        if (count($data) === 1) {
            $result = $data[0];
            $result['nt_message_fields'] = implode(', ', json_decode($result['nt_message_fields'], true));
        }

        return $result;
    }

    /**
     * Function to get data by page id and code.
     *
     * @param string $code   To store the code of page notification template.
     * @param string $module   To store the code of module notification template.
     *
     * @return array
     */
    public static function getByCodeAndModule(string $code, string $module): array
    {
        $wheres = [];
        $wheres[] = '(nt.nt_code = \'' . $code . '\')';
        $wheres[] = '(nt.nt_module = \'' . $module . '\')';
        $wheres[] = '(nt.nt_deleted_on IS NULL)';
        $data = self::loadData($wheres);
        $result = [];
        if (count($data) === 1) {
            $result = $data[0];
            $result['nt_message_fields'] = json_decode($result['nt_message_fields'], true);
        }

        return $result;
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
        $query = 'SELECT nt.nt_id, nt.nt_code, nt.nt_module, nt.nt_description,
                          nt.nt_message_fields, nt.nt_mail_path,
                         nt.nt_active
                  FROM notification_template as nt' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (nt_id)) AS total_rows
                  FROM notification_template as nt' . $strWhere;

        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['total_rows'];
        }

        return $result;
    }

    /**
     * Function to get record for single select field.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orders To store the list sorting query.
     * @param int   $limit  To store the limit of the data.
     *
     * @return array
     */
    public static function loadSingleSelectData(array $wheres = [], array $orders = [], int $limit = 0): array
    {
        $data = self::loadData($wheres, $orders, $limit);

        return parent::doPrepareSingleSelectData($data, 'nt_code', 'nt_id');
    }


}
