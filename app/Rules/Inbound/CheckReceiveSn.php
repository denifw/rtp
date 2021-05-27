<?php

namespace App\Rules\Inbound;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Helper\Job\Warehouse\InboundReceiveSn;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckReceiveSn implements Rule
{
    /**
     * Property to store the Jir ID.
     *
     * @var InboundReceiveSn
     */
    public $Jir;

    /**
     * Property to store the error message.
     *
     * @var string $Message
     */
    private $Message = '';

    /**
     * Create a new rule instance.
     *
     * @param InboundReceiveSn $jir To store the object helper
     *
     * @return void
     */
    public function __construct(InboundReceiveSn $jir)
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
        $inputs = [];
        if (empty($this->Jir->SnDivider) === false) {
            $inputs = explode($this->Jir->SnDivider, $value);
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
        $error = GoodsDao::isValidSnPrefix($this->Jir->GdId, $inputs);
        if (empty($error) === false) {
            $this->Message = $error;
            return false;
        }
        # Validate Duplicate Serial Number
        $valid = $this->isRegistered($inputs);
        if ($valid === false) {
            $this->Message = trans('validation.unique');
            return false;
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
                $orWheres[] = StringFormatter::generateLikeQuery('jir.jir_serial_number', $input);
            }
        }
        $wheres = [];
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $wheres[] = '(jir.jir_id <> ' . $this->Jir->JirId . ')';
        $wheres[] = '(jog.jog_gd_id = ' . $this->Jir->GdId . ')';
        $wheres[] = '(ji.ji_wh_id = ' . $this->Jir->WhId . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jir.jir_id, ji.ji_end_load_on, jid.jid_id, jis.jid_stock 
                        FROM job_inbound_receive as jir
                            INNER JOIN job_inbound as ji ON jir.jir_ji_id = ji.ji_id
                         INNER JOIN job_goods as jog ON jir.jir_jog_id = jog.jog_id
                         LEFT OUTER JOIN job_inbound_detail as jid ON jir.jir_id = jid.jid_jir_id
                         LEFT OUTER JOIN (SELECT jis_jid_id, SUM(jis_quantity) as jid_stock
                                                    FROM job_inbound_stock
                                                    WHERE jis_deleted_on IS NULL
                                                    GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $sqlResults = DB::select($query);
        $valid = true;
        if (empty($sqlResults) === false) {
            $data = DataParser::arrayObjectToArray($sqlResults);
            foreach ($data as $row) {
                if (empty($row['ji_end_load_on']) === true || (empty($row['ji_end_load_on']) === false && (float)$row['jid_stock'] > 0.0)) {
                    $valid = false;
                }
            }
        }
        return $valid;
    }
}
