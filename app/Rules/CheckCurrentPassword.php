<?php

namespace App\Rules;

use App\Frame\Formatter\DataParser;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckCurrentPassword implements Rule
{
    /**
     * Property to store the user id.
     *
     * @var int $UserId
     */
    private $UserId = 0;

    /**
     * Create a new rule instance.
     *
     * @param int $userId To store the user id.
     *
     * @return void
     */
    public function __construct($userId)
    {
        if (empty($userId) === false && \is_int($userId) === true && $userId > 0) {
            $this->UserId = $userId;
        }
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

        $wheres[] = '(us_id = ' . $this->UserId . ')';
        $wheres[] = "(us_active = 'Y')";
        $wheres[] = "(us_confirm = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT us_password 
					FROM users ' . $strWhere;
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $arrData = DataParser::objectToArray($sqlResult[0], [
                'us_password',
            ]);
            if (Hash::check($value, $arrData['us_password']) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.currentPassword');
    }
}
