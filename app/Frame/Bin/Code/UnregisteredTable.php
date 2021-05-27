<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\Bin\Code;

use App\Frame\Formatter\DataParser;
use App\Frame\Gui\Table;
use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Code
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class UnregisteredTable
{

    /**
     * Function to check the data.
     *
     * @return void
     */
    public function doCheck(): void
    {
        $dbTables = $this->loadDatabaseTable();
        $sysTables = $this->loadSystemTable();
        $tbl = new Table('tbl');
        $tbl->setHeaderRow([
            'st_id' => 'ID',
            'st_name' => 'Name',
            'st_prefix' => 'Prefix',
            'st_path' => 'Path',
            'check' => 'Check',
            'db_name' => 'Name',
            'db_prefix' => 'Prefix',
            'path' => 'Path',
        ]);
        $i = 0;
        $rows = [];
        foreach ($dbTables as $key => $row) {
            if (array_key_exists($key, $sysTables) === false) {
                $rows[] = [
                    'st_id' => '',
                    'st_name' => '',
                    'st_prefix' => '',
                    'st_path' => '',
                    'check' => '',
                    'db_name' => $row['table'],
                    'db_prefix' => $row['prefix'],
                    'path' => '',
                ];
                $tbl->addCellAttribute('check', $i, 'style', 'background-color: red;');
                $i++;
            }
        }
        foreach ($sysTables as $key => $row) {
            $valid = true;
            $temp = [
                'st_id' => $row['st_id'],
                'st_name' => $row['st_name'],
                'st_prefix' => $row['st_prefix'],
                'st_path' => $row['st_path'],
                'check' => '',
                'db_name' => '',
                'db_prefix' => '',
                'path' => '',
            ];
            # Compare to db table
            if (array_key_exists($key, $dbTables) === false) {
                $valid = false;
                $tbl->addCellAttribute('check', $i, 'style', 'background-color: red;');
            } else {
                $db = $dbTables[$key];
                $temp['db_name'] = $db['table'];
                $temp['db_prefix'] = $db['prefix'];
                if ($db['prefix'] !== $row['st_prefix']) {
                    $valid = false;
                    $tbl->addCellAttribute('check', $i, 'style', 'background-color: red;');
                }
            }

            # Check Path Name
            $path = 'App\\Model\\Dao\\' . str_replace('/', '\\', $row['st_path']);
            $path .= '\\'.str_replace(' ', '', $row['st_name']).'Dao';
            if (class_exists($path) === false) {
                $valid = false;
            }
            if ($valid === false) {
                $rows[] = $temp;
                $tbl->addCellAttribute('check', $i, 'style', 'background-color: red;');
                $i++;
            }
        }
        $tbl->addRows($rows);
        echo $tbl->createTable();
    }


    /**
     * Function to load database table.
     *
     * @return array
     */
    private function loadDatabaseTable(): array
    {
        $query = "SELECT tc.table_name, kcu.column_name
                    FROM information_schema.table_constraints AS tc
                             INNER JOIN information_schema.key_column_usage AS kcu
                                        ON tc.constraint_name = kcu.constraint_name
                    where tc.constraint_type = 'PRIMARY KEY' and tc.table_name <> 'migrations'
                    GROUP BY tc.table_name, kcu.column_name
                    ORDER BY tc.table_name";
        $sqlResults = DB::select($query);
        $results = [];
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                $words = explode('_', $row['column_name']);
                $results[$row['table_name']] = [
                    'table' => $row['table_name'],
                    'prefix' => $words[0],
                ];
            }
        }
        return $results;
    }


    /**
     * Function to load system table.
     *
     * @return array
     */
    private function loadSystemTable(): array
    {
        $query = "SELECT st_id, st_name, st_prefix, st_path, st_active
                    FROM system_table ";
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $name = str_replace(' ', '_', mb_strtolower($row['st_name']));
            $results[$name] = $row;
        }
        return $results;
    }


}
