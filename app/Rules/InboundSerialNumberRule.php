<?php

namespace App\Rules;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class InboundSerialNumberRule implements Rule
{
    /**
     * Property to store the id for job inbound.
     *
     * @var int $jiId
     */
    private $JiId;
    /**
     * Property to store the id for job inbound detail.
     *
     * @var int $jidId
     */
    private $JidId;
    /**
     * Property to store the id for goods id.
     *
     * @var int $GdId
     */
    private $GdId;
    /**
     * Property to store the divider.
     *
     * @var string $Divider
     */
    private $Divider;

    /**
     * Property to store the error message.
     *
     * @var string $Message
     */
    private $Message = '';

    /**
     * Create a new rule instance.
     *
     * @param int    $jiId    To set the id of job inbound.
     * @param int    $jidId   To set the id of job inbound detail.
     * @param int    $gdId    To set the id of goods.
     * @param string $divider To store the multiple data divider
     *
     * @return void
     */
    public function __construct(int $jiId, int $jidId, int $gdId, string $divider)
    {
        $this->Divider = $divider;
        $this->JiId = $jiId;
        $this->JidId = $jidId;
        $this->GdId = $gdId;
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
        if (empty($this->Divider) === false) {
            $inputs = explode($this->Divider, $value);
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
        $error = GoodsDao::isValidSnPrefix($this->GdId, $inputs);
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
        $wheres = [];
        $wheres[] = '(jid.jid_id <> ' . $this->JidId . ')';
        $wheres[] = '(gd.gd_id = ' . $this->GdId . ')';
        $orWheres = [];
        foreach ($inputs as $input) {
            $input = trim($input);
            if (empty($input) === false) {
                $orWheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $input);
            }
        }
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '((jis.jid_stock > 0) OR (jid.jid_ji_id = ' . $this->JiId . '))';
        $wheres[] = "(gd.gd_sn = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid_id
                        FROM job_inbound_detail as jid INNER JOIN
                        goods as gd ON gd.gd_id = jid.jid_gd_id LEFT OUTER JOIN
                        (SELECT jis_jid_id, SUM(jis_quantity) as jid_stock
                            FROM job_inbound_stock 
                            WHERE jis_deleted_on IS NULL
                            GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWhere;
        $sqlQuery = DB::select($query);

        return empty($sqlQuery);
    }
}
