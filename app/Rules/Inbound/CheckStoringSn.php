<?php

namespace App\Rules\Inbound;

use App\Frame\Formatter\StringFormatter;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Helper\Job\Warehouse\InboundStoringSn;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckStoringSn implements Rule
{
    /**
     * Property to store the Jir ID.
     *
     * @var InboundStoringSn
     */
    public $Data;

    /**
     * Property to store the error message.
     *
     * @var string $Message
     */
    private $Message = '';

    /**
     * Create a new rule instance.
     *
     * @param InboundStoringSn $data To store the object helper
     *
     * @return void
     */
    public function __construct(InboundStoringSn $data)
    {
        $this->Data = $data;
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
        $inputs = [];
        if (empty($this->Data->SnDivider) === false) {
            $inputs = explode($this->Data->SnDivider, $value);
        } else {
            $inputs[] = $value;
        }
        # Validate Special Character
        $valid = StringFormatter::isContainsSpecialCharacter($inputs);
        if ($valid === false) {
            $this->Message = trans('validation.alfanumber');
            return false;
        }
        # Validate Serial Number Prefix
        $error = GoodsDao::isValidSnPrefix($this->Data->GdId, $inputs);
        if (empty($error) === false) {
            $this->Message = $error;
            return false;
        }
        if ($this->Data->GdOnReceiveSn === 'Y') {
            # Validate on receive sn
            $valid = $this->isEqualsWithReceive($inputs);
            if ($valid === false) {
                $this->Message = trans('validation.snReceiveStoring');
                return false;
            }

        } else {
            # Validate Duplicate Serial Number
            $valid = $this->isRegistered($inputs);
            if ($valid === false) {
                $this->Message = trans('validation.unique');
                return false;
            }
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
        return $this->Message;
    }


    /**
     * Function to validate serial number prefix.
     *
     * @param array $inputs To store the input.
     *
     * @return bool
     */
    private function isRegistered(array $inputs): bool
    {
        $orWheres = [];
        foreach ($inputs as $input) {
            $input = trim($input);
            if (empty($input) === false) {
                $orWheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $input);
            }
        }
        $wheres = [];
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        $wheres[] = '(jid.jid_id <> ' . $this->Data->JidId . ')';
        $wheres[] = '(gd.gd_id = ' . $this->Data->GdId . ')';
        $wheres[] = '(ji.ji_wh_id = ' . $this->Data->WhId . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '((jis.jid_stock > 0) OR (jid.jid_ji_id = ' . $this->Data->JiId . '))';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid_id
                        FROM job_inbound_detail as jid INNER JOIN
                        job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                        goods as gd ON gd.gd_id = jid.jid_gd_id LEFT OUTER JOIN
                        (SELECT jis_jid_id, SUM(jis_quantity) as jid_stock
                            FROM job_inbound_stock 
                            WHERE jis_deleted_on IS NULL
                            GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWhere;
        $sqlQuery = DB::select($query);

        return empty($sqlQuery);
    }


    /**
     * Function to validate serial number prefix.
     *
     * @param array $inputs To store the input.
     *
     * @return bool
     */
    private function isEqualsWithReceive(array $inputs): bool
    {
        $sn = [];
        foreach ($inputs as $input) {
            $input = trim($input);
            if (empty($input) === false) {
                $sn[] = $input;
            }
        }
        $wheres = [];
        $wheres[] = "(jir.jir_serial_number = '" . implode(', ', $sn) . "')";
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $wheres[] = '((jid.qty_stored = 0) OR (jid.qty_stored IS NULL))';
        if ($this->Data->JirId !== 0) {
            $wheres[] = '(jir.jir_id = ' . $this->Data->JirId . ')';
        } else {
            $wheres[] = '(jir.jir_jog_id = ' . $this->Data->JogId . ')';
            $wheres[] = '(jir.jir_ji_id = ' . $this->Data->JiId . ')';
            if (empty($this->Data->LotNumber) === false) {
                $wheres[] = "(jir.jir_lot_number = '" . $this->Data->LotNumber . "')";
            }
            if (empty($this->Data->ExpiredDate) === false) {
                $wheres[] = "(jir.jir_expired_date = '" . $this->Data->ExpiredDate . "')";
            }
            if (empty($this->Data->PackingNumber) === false) {
                $wheres[] = "(jir.jir_packing_number = '" . $this->Data->PackingNumber . "')";
            }
            if ($this->Data->GdtId !== 0) {
                $wheres[] = '(jir.jir_gdt_id = ' . $this->Data->GdtId . ')';
            }
            if ($this->Data->GcdId !== 0) {
                $wheres[] = '(jir.jir_gcd_id = ' . $this->Data->GcdId . ')';
            }
        }


        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jir_id 
                        FROM job_inbound_receive as jir LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as qty_stored 
                        FROM job_inbound_detail
                        WHERE (jid_deleted_on IS NULL) AND (jid_id <> ' . $this->Data->JidId . ')
                        GROUP BY jid_jir_id) as jid ON jir.jir_id = jid.jid_jir_id' . $strWheres;
        $query .= ' GROUP BY jir_id';
        $sqlQuery = DB::select($query);
        return !empty($sqlQuery);
    }


}
