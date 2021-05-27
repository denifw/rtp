<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckSerialNumberPrefix implements Rule
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
        if (empty($value) === false || mb_strlen($value) >= 2) {
            $prefix = mb_substr($value, 0, 2);
            $wheres = [];
            $wheres[] = '(jir.jir_id = ' . $this->JirId . ')';
            $wheres[] = "(gd.gd_sn = 'Y')";
            $wheres[] = "((gpf.gpf_prefix = '" . $prefix . "') OR (gd.gd_id NOT IN (SELECT gpf_gd_id FROM goods_prefix WHERE gpf_deleted_on IS NULL)))";
            $wheres[] = '(gpf.gpf_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'select gd.gd_id, gpf.gpf_id 
                    from goods as gd INNER JOIN
                        job_goods as jog ON jog.jog_gd_id = gd.gd_id INNER JOIN
                        job_inbound_receive as jir ON jir.jir_jog_id = jog.jog_id LEFT OUTER JOIN
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
