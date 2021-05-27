<?php

namespace App\Rules;

use App\Frame\Formatter\DataParser;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckSnReceiveAndStoringInbound implements Rule
{

    /**
     * Property to store the sql query.
     *
     * @var int
     */
    private $JirId;
    /**
     * Create a new rule instance.
     *
     * @param int $jirId To store the goods Id.
     * @return void
     */
    public function __construct($jirId)
    {
        $this->JirId = $jirId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $wheres = [];
        $wheres[] = '(jir_id = ' . $this->JirId . ')';
        $wheres[] = '(jir_deleted_on IS NULL)';
        $wheres[] = '(jir_serial_number IS NOT NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'select jir_id, jir_serial_number 
                    from job_inbound_receive  ' . $strWhere;
        $sqlResult = DB::select($query);
        if(count($sqlResult) === 1) {
            $data = DataParser::objectToArray($sqlResult[0]);
            if($data['jir_serial_number'] !== $value) {
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.snReceiveStoring');
    }
}
