<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckGoodsPrefixNumber implements Rule
{

    /**
     * Property to store the sql query.
     *
     * @var int
     */
    private $GdId;
    /**
     * Create a new rule instance.
     *
     * @param int $gdId To store the goods Id.
     * @return void
     */
    public function __construct($gdId)
    {
        $this->GdId = $gdId;
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
        if (empty($value) === false || mb_strlen($value) >= 2) {
            $prefix = mb_substr($value, 0, 2);
            $wheres = [];
            $wheres[] = '(gd.gd_id = ' . $this->GdId . ')';
            $wheres[] = "(gd.gd_sn = 'Y')";
            $wheres[] = "(gpf.gpf_prefix = '" . $prefix . "')";
            $wheres[] = '(gpf.gpf_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'select gd.gd_id, gpf.gpf_id 
                    from goods as gd INNER JOIN
                        goods_prefix as gpf on gd.gd_id = gpf.gpf_gd_id  ' . $strWhere;
            $sqlResult = DB::select($query);
            return !empty($sqlResult);
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
        return trans('validation.snPrefix');
    }
}
