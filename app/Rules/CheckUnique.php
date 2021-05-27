<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckUnique implements Rule
{
    /**
     * Property to store the reference field.
     *
     * @var array
     */
    private $ReferenceFieldValue;
    /**
     * Property to store the table name.
     *
     * @var string
     */
    private $TableName;
    /**
     * Property to store the unique field.
     *
     * @var array
     */
    private $UniqueFields;

    /**
     * Create a new rule instance.
     *
     * @param string $tableName           To store the name of the table.
     * @param array  $referenceFieldValue To store the ignored field for the unique query.
     * @param array  $uniqueFields        To store the unique field value.
     *
     * @return void
     */
    public function __construct($tableName, $referenceFieldValue, $uniqueFields)
    {
        $this->TableName = $tableName;
        $this->ReferenceFieldValue = $referenceFieldValue;
        $this->UniqueFields = $uniqueFields;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $wheres = [];
        $whereValues = [];
        if (empty($this->ReferenceFieldValue) === false) {
            foreach ($this->ReferenceFieldValue AS $field => $val) {
                if ($val !== null) {
                    $wheres[] = '(' . $field . ' <> ?)';
                    $whereValues[] = $val;
                }
            }
        }
        if (empty($this->UniqueFields) === false) {
            foreach ($this->UniqueFields AS $field => $val) {
                if ($val !== null) {
                    $wheres[] = '(' . $field . ' = ?)';
                    $whereValues[] = $val;
                } else {
                    $wheres[] = '(' . $field . ' IS NULL)';
                }
            }
        }
        if ($value !== null) {
            $wheres[] = '(' . $attribute . ' = ?)';
            $whereValues[] = $value;
        } else {
            $wheres[] = '(' . $attribute . ' IS NULL)';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $sqlResult = DB::select('select ' . $attribute . ' FROM ' . $this->TableName . $strWhere, $whereValues);

        return empty($sqlResult);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.unique');
    }
}
