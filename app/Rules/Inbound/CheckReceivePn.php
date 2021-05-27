<?php

namespace App\Rules\Inbound;

use App\Model\Helper\Job\Warehouse\InboundReceivePn;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckReceivePn implements Rule
{
    /**
     * Property to store the Jir ID.
     *
     * @var InboundReceivePn
     */
    public $Jir;

    /**
     * Create a new rule instance.
     *
     * @param InboundReceivePn $jir To store the object helper
     *
     * @return void
     */
    public function __construct(InboundReceivePn $jir)
    {
        $this->Jir = $jir;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
//        $valid = $this->loadData($value);
//        if($valid)
        return $this->loadData($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.unique');;
    }


    /**
     * Function to validate serial number prefix.
     *
     * @param string $pn To store the input.
     *
     * @return bool
     */
    private function loadData(string $pn): bool
    {
        $wheres = [];
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $wheres[] = "(jir.jir_packing_number = '" . trim($pn) . "')";
        if (empty($this->Jir->LotNumber) === false) {
            $wheres[] = "(jir.jir_packing_number <> '" . $this->Jir->LotNumber . "')";
        }
        $wheres[] = '(jir.jir_id <> ' . $this->Jir->JirId . ')';
        $wheres[] = '(jog.jog_gd_id <> ' . $this->Jir->GdId . ')';
        $wheres[] = '(ji.ji_wh_id = ' . $this->Jir->WhId . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $this->Jir->SsId . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jir_id
                        FROM job_inbound_receive as jir
                            INNER JOIN job_inbound as ji ON jir.jir_ji_id = ji.ji_id
                            INNER JOIN job_goods as jog ON jir.jir_jog_id = jog.jog_id
                            INNER JOIN job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWheres;
        $query .= ' GROUP BY jir_id';
        $sqlQuery = DB::select($query);
        return empty($sqlQuery);
    }
}
