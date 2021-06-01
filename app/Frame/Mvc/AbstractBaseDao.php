<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;


use App\Frame\Formatter\DataParser;
use App\Frame\System\Session\UserSession;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Abstract Class to handle the creation of the Dao.
 *
 * @package    app
 * @subpackage Frame\Mvc
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseDao extends Model
{
    /**
     * Property to store the prefix of the table.
     *
     * @var string
     */
    protected $TablePrefix = '';


    /**
     * Property to store the last insert id.
     *
     * @var string
     */
    protected $LastInsertId = '';

    /**
     * Property to store the incremental number.
     *
     * @var int
     */
    protected $Incremental = 0;

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [];

    /**
     * Base dao constructor.
     *
     * @param string $tableName To store the name of the table.
     * @param string $prefixTable To store the prefix of the table.
     * @param array $fields To store the field list for the table.
     */
    public function __construct(string $tableName = '', string $prefixTable = '', array $fields = [])
    {
        parent::__construct();
        $this->timestamps = false;
        $this->TablePrefix = $prefixTable;
        $this->table = $tableName;
        $this->primaryKey = $prefixTable . '_id';
        $this->initializeRandomIncremental();

        $fields = array_merge($fields, [
            $this->TablePrefix . '_created_on',
            $this->TablePrefix . '_created_by',
            $this->TablePrefix . '_updated_on',
            $this->TablePrefix . '_updated_by',
            $this->TablePrefix . '_deleted_on',
            $this->TablePrefix . '_deleted_by',
            $this->TablePrefix . '_deleted_reason',
        ]);
        $this->fillable = array_values(array_unique($fields));
    }

    /**
     * Function to initialize random incremental.
     *
     * @return void
     */
    private function initializeRandomIncremental(): void
    {
        try {
            $this->Incremental = random_int(0, 1000);
        } catch (Exception $e) {
            $this->Incremental = 0;
        }

    }

    /**
     * Abstract function to do insert transaction.
     *
     * @param array $fieldData To store the field value per column.
     *
     * @return void
     */
    public function doInsertTransaction(array $fieldData): void
    {
        $user = new UserSession();
        $this->Incremental++;
        $uidKey = microtime() . $this->TablePrefix . $user->getId() . $this->Incremental;
        $this->LastInsertId = Uuid::uuid3(Uuid::NAMESPACE_URL, $uidKey);
        $colValue = array_merge($fieldData, [
            $this->TablePrefix . '_created_on' => date('Y-m-d H:i:s'),
            $this->TablePrefix . '_created_by' => $user->getId(),
            $this->TablePrefix . '_id' => $this->LastInsertId,
        ]);
        DB::table($this->table)->insert($colValue);
    }

    /**
     * Abstract function to load the data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     * @param array $fieldData To store the field value per column.
     *
     * @return void
     */
    public function doUpdateTransaction(string $primaryKeyValue, array $fieldData): void
    {
        $user = new UserSession();
        $colValue = array_merge($fieldData, [
            $this->TablePrefix . '_updated_on' => date('Y-m-d H:i:s'),
            $this->TablePrefix . '_updated_by' => $user->getId(),
        ]);
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->update($colValue);
    }

    /**
     * Abstract function to load the data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     * @param string $deleteReason To store the message for deleted data.
     *
     * @return void
     */
    public function doDeleteTransaction(string $primaryKeyValue, string $deleteReason = ''): void
    {
        $user = new UserSession();
        $data = [
            $this->TablePrefix . '_deleted_on' => date('Y-m-d H:i:s'),
            $this->TablePrefix . '_deleted_by' => $user->getId(),
        ];
        if (empty($deleteReason) === false) {
            $data[$this->TablePrefix . '_deleted_reason'] = $deleteReason;
        }
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->update($data);
    }

    /**
     * Abstract function to load the data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     *
     * @return void
     */
    public function doHardDeleteTransaction(string $primaryKeyValue): void
    {
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->delete();
    }

    /**
     * Abstract function to undo delete data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     *
     * @return void
     */
    public function doUndoDeleteTransaction(string $primaryKeyValue): void
    {
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->update([
                $this->TablePrefix . '_deleted_on' => null,
                $this->TablePrefix . '_deleted_by' => null,
                $this->TablePrefix . '_deleted_reason' => null,
            ]);
    }


    /**
     * Function to set the user detail that do the un-delete.
     *
     * @return string
     */
    public function getLastInsertId(): string
    {
        return $this->LastInsertId;
    }


    /**
     * Abstract function to load the seeder query for table user_mapping.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        $result = [];
        $outFields = $this->getFillAbleFields();
        $data = $this->loadSeedData($outFields);
        if (empty($data) === false) {
            # add default string fields
            $numberFields = array_values(array_unique($this->NumericFields));

            # Generate Seeder
            $createdBy = Uuid::uuid3(Uuid::NAMESPACE_URL, 'us1');
            foreach ($data as $row) {
                $query = "DB::table('" . $this->getTable() . "')->insert([";
                foreach ($outFields as $field) {
                    if (in_array($field, $numberFields, true) === true) {
                        $val = $row[$field];
                    } else {
                        $val = "'" . $row[$field] . "'";
                    }
                    if (empty($val) === false && $val !== "''") {
                        $query .= "'" . $field . "' => " . $val . ', ';
                    }
                }
                $query .= "'" . $this->TablePrefix . "_created_by' => '" . $createdBy . "', ";
                $query .= "'" . $this->TablePrefix . "_created_on' => date('Y-m-d H:i:s')";
                $query .= ']);';
                $result[] = $query;
            }
        }

        return $result;
    }


    /**
     * Function to set the user detail that do the un-delete.
     *
     * @param array $numberFields To store the required fields.
     *
     * @return array
     * @deprecated Change to parent function.
     */
    protected function generateSeeder(array $numberFields = []): array
    {
        return $numberFields;
    }

    /**
     * Function to load out fields table.
     *
     * @return array
     */
    private function getFillAbleFields(): array
    {
        $result = [];
        $fields = $this->getFillable();
        $excludeFields = [
            $this->TablePrefix . '_created_on',
            $this->TablePrefix . '_created_by',
            $this->TablePrefix . '_updated_on',
            $this->TablePrefix . '_updated_by',
            $this->TablePrefix . '_deleted_on',
            $this->TablePrefix . '_deleted_by',
            $this->TablePrefix . '_deleted_reason',
        ];
        foreach ($fields as $field) {
            if (in_array($field, $excludeFields, true) === false) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Function to load all table data.
     *
     * @param array $outFields To store the out field data.
     *
     * @return array
     */
    private function loadSeedData(array $outFields): array
    {
        $query = ' SELECT ' . implode(', ', $outFields);
        $query .= ' FROM ' . $this->getTable();
        $query .= ' WHERE (' . $this->TablePrefix . '_deleted_on IS NULL)';
        $query .= ' ORDER BY ' . $this->TablePrefix . '_created_on, ' . $this->primaryKey;
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, $outFields);
        }

        return $result;
    }

    /**
     * Function to load data from database.
     *
     * @param array $data To store the query selection.
     * @param string|array $textColumn To store the column name that will be show as a text.
     * @param string $valueColumn To store the column name that will be show as a value.
     *
     * @return array
     */
    protected static function doPrepareSingleSelectData(array $data, $textColumn, string $valueColumn): array
    {
        $results = [];
        foreach ($data as $row) {
            if (is_array($textColumn) === true) {
                $text = [];
                foreach ($textColumn as $column) {
                    $text[] = $row[$column];
                }
                $row['text'] = implode(' - ', $text);
            } else {
                $row['text'] = $row[$textColumn];
            }
            $row['value'] = $row[$valueColumn];
            $results[] = $row;
        }
        # return the data.
        return $results;
    }

}
