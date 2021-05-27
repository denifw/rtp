<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo SystemTable.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemTable extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('st_name', $this->getStringParameter('search_key'));
        $wheres[] = "(st_active = 'Y')";
        $wheres[] = '(st_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT st_id, st_name 
                        FROM system_table ' . $strWhere;
        $query .= ' ORDER BY st_name';
        $query .= ' LIMIT 30 OFFSET 0';

        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data, ['st_name']);
            foreach ($tempResult AS $row) {
                $results[] = [
                    'text' => $row['st_name'],
                    'value' => str_replace(' ', '_', mb_strtolower($row['st_name']))
                ];
            }
        }

        # return the data.
        return $results;
    }


    /**
     * Function to load table fields
     *
     * @return array
     */
    public function loadFieldsTable(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('column_name', $this->getStringParameter('search_key'));
        $wheres[] = "(table_schema = 'public')";
        $wheres[] = "(table_name = '" . $this->getStringParameter('table_name') . "')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT column_name, data_type, is_nullable
              FROM information_schema.columns ' . $strWhere;
        $query .= ' ORDER BY column_name';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data, ['column_name']);
            foreach ($tempResult AS $row) {
                $results[] = [
                    'text' => $row['column_name'],
                    'value' => $row['column_name']
                ];
            }
        }

        # return the data.
        return $results;
    }

}
