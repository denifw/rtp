<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckEmptyQueryResult implements Rule
{
    /**
     * Property to store the sql query.
     *
     * @var string
     */
    private $Query;
    /**
     * Property to store the custom message.
     *
     * @var array
     */
    private $CustomMessage;

    /**
     * Create a new rule instance.
     *
     * @param string $query   To store the query for the validation.
     * @param string $message To store the custome message.
     *
     * @return void
     */
    public function __construct($query, $message = '')
    {
        $this->Query = $query;
        $this->CustomMessage = $message;
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
        $sqlResult = DB::select($this->Query);

        return empty($sqlResult);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (empty($this->CustomMessage) === false) {
            return $this->CustomMessage;
        }

        return trans('validation.unique');
    }
}
