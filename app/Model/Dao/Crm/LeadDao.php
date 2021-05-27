<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Dao\Crm;

use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table lead.
 *
 * @package    app
 * @subpackage Model\Dao\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class LeadDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ld_id', 'ld_number', 'ld_ss_id', 'ld_rel_id', 'ld_sty_id', 'ld_converted_on', 'ld_converted_by'
    ];

    /**
     * Base dao constructor for lead.
     *
     */
    public function __construct()
    {
        parent::__construct('lead', 'ld', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table lead.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'ld_number', 'ld_converted_on'
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
        $wheres[] = '(ld_id = ' . $referenceValue . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param int $referenceValue To store the reference value of the table.
     * @param int $ssId           To store the system setting value.
     *
     * @return array
     */
    public static function getByReferenceAndSystem($referenceValue, $ssId): array
    {
        $wheres = [];
        $wheres[] = '(ld_id = ' . $referenceValue . ')';
        $wheres[] = '(ld_ss_id = ' . $ssId . ')';
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
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
        $query = 'SELECT ld.ld_id, ld.ld_ss_id, ld.ld_number, ld.ld_rel_id, 
                         ld.ld_sty_id, ld.ld_converted_on, ld.ld_converted_by, 
                         sty.sty_name as ld_sty_name, sty.sty_label_type as ld_sty_label_type,
                         rel.rel_name, rel.rel_short_name, rel.rel_email, rel.rel_website,
                         rel.rel_phone, rel.rel_vat, rel.rel_owner, rel.rel_remark, rel.rel_active,
                         rel.rel_manager_id, rel.rel_main_contact_id, rel.rel_ids_id, rel.rel_source_id, 
                         rel.rel_established, rel.rel_size_id, rel.rel_employee, rel.rel_revenue, rel.rel_deleted_reason,
                         manager.us_name as rel_manager_name, mc.cp_name as rel_main_contact_name,
                         ids.ids_name as rel_ids_name, src.sty_name as rel_source_name,
                         sz.sty_name as rel_size
                  FROM lead as ld
                  INNER JOIN relation as rel on rel.rel_id = ld.ld_rel_id
                  INNER JOIN system_type as sty on sty.sty_id = ld.ld_sty_id
                  LEFT OUTER JOIN users as manager on manager.us_id = rel.rel_manager_id
                  LEFT OUTER JOIN contact_person as mc on mc.cp_id = rel.rel_main_contact_id
                  LEFT OUTER JOIN industry as ids on ids.ids_id = rel.rel_ids_id
                  LEFT OUTER JOIN system_type as src on src.sty_id = rel.rel_source_id
                  LEFT OUTER JOIN system_type as sz on sz.sty_id = rel.rel_size_id' . $strWhere;
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
        $query = 'SELECT count(DISTINCT (ld_id)) AS total_rows
                  FROM lead as ld
                  INNER JOIN relation as rel on rel.rel_id = ld.ld_rel_id
                     INNER JOIN system_type as sty on sty.sty_id = ld.ld_sty_id
                  LEFT OUTER JOIN users as manager on manager.us_id = rel.rel_manager_id
                  LEFT OUTER JOIN contact_person as mc on mc.cp_id = rel.rel_main_contact_id
                  LEFT OUTER JOIN industry as ids on ids.ids_id = rel.rel_ids_id
                  LEFT OUTER JOIN system_type as src on src.sty_id = rel.rel_source_id
                  LEFT OUTER JOIN system_type as sz on sz.sty_id = rel.rel_size_id' . $strWhere;

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

        return parent::doPrepareSingleSelectData($data, 'ld_number', 'ld_id');
    }


}
