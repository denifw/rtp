<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Frame\Bin\SqlQuery\Uuid;

use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Bin\SqlQuery\Uuid
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class AddingUuid
{


    /**
     * Function to load database table.
     *
     * @return void
     */
    public function generateSql(): void
    {
        $data = $this->loadTable();
        $deleteColumn = $this->loadDeleteColumn();
        foreach ($data as $row) {
            $table = $row['table_name'];
            if ($table !== 'migrations') {
                echo '-- ========= START ' . $table . ' ================<br />';
                # generate uid field
                $this->generateUidColumn($table, $row['column_name']);

                # generate delete reason field
                $column = '';
                if (array_key_exists($table, $deleteColumn) === true) {
                    $column = $deleteColumn[$table];
                }
                $this->generateDeleteReasonColumn($table, $row['column_name'], $column);

                echo '-- ========= END ' . $table . ' ================<br /><br />';
            }
        }
    }

    /**
     * Function to load database table.
     *
     * @param string $table To store the table name
     * @param string $pKey  To store the table name
     *
     * @return void
     */
    public function generateUidColumn(string $table, string $pKey): void
    {
        $column = str_replace('_id', '_uid', $pKey);
        echo '-- ========= Adding UID ================<br />';
        echo '-- Adding Column<br />';
        echo 'ALTER TABLE public.' . $table . ' ADD COLUMN ' . $column . ' uuid;<br />';
        echo '-- Fill in Column<br />';
        echo "UPDATE " . $table . " SET " . $column . " = uuid_generate_v3(uuid_ns_url(), '" . $table . "_' || " . $pKey . ");<br />";
        echo '-- Alter Column<br />';
        echo "ALTER TABLE public." . $table . " ALTER COLUMN " . $column . " SET NOT NULL, ADD CONSTRAINT tbl_" . $column . "_unique UNIQUE (" . $column . ");<br />";
    }

    /**
     * Function to load database table.
     *
     * @param string $table  To store the table name
     * @param string $pKey   To store the table name
     * @param string $column To store the table name
     *
     * @return void
     */
    public function generateDeleteReasonColumn(string $table, string $pKey, string $column): void
    {
        echo '-- ========= Adding Delete Reason ================<br />';
        $prefixTable = explode('_', $pKey)[0];
        $newColumn = $prefixTable . '_deleted_reason';
        if (empty($column) === true) {
            echo '-- Adding Column<br />';
            echo 'ALTER TABLE public.' . $table . ' ADD COLUMN ' . $newColumn . ' varchar(255);<br />';

        } elseif ($column === $prefixTable . '_delete_reason') {
            echo 'ALTER TABLE public.' . $table . ' RENAME COLUMN ' . $prefixTable . '_delete_reason TO ' . $newColumn . ';<br />';

        }
    }

    /**
     * Function to load database table.
     *
     * @return array
     */
    public function loadTable(): array
    {
        $query = "SELECT tc.constraint_name, tc.table_name, kcu.column_name, tc.constraint_type
                    FROM information_schema.table_constraints AS tc
                             INNER JOIN information_schema.key_column_usage AS kcu
                                        ON tc.constraint_name = kcu.constraint_name
                    where tc.constraint_type = 'PRIMARY KEY'
                    ORDER BY tc.table_name";
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to load database table.
     *
     * @return array
     */
    public function loadDeleteColumn(): array
    {
        $query = "SELECT table_name, column_name
                    FROM information_schema.columns
                    WHERE (table_schema = 'public')
                    AND (column_name like '%delete_reason' OR column_name like '%deleted_reason')";
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            if (array_key_exists($row['table_name'], $results) === false) {
                $results[$row['table_name']] = $row['column_name'];
            } else {
                dd($row['table_name']);
            }
        }
        return $results;
    }


}
