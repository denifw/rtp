<?php

namespace App\Rules\Bundling;

use App\Frame\Formatter\StringFormatter;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BundlingDetailSerialNumber implements Rule
{
    /**
     * Property to store the id for goods id.
     *
     * @var int $GdId
     */
    private $GdId;
    /**
     * Property to store the id for bundling detail id.
     *
     * @var int $JbdId
     */
    private $JbdId;
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
     * @param integer $gdId        To set the id of the current user.
     * @param integer $jbdId        To set the id of job bundling detail.
     * @param string $divider To store the multiple data divider
     * @return void
     */
    public function __construct($gdId, $jbdId, $divider)
    {
        $this->GdId = $gdId;
        $this->JbdId = $jbdId;
        $this->Divider = $divider;
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
        $wheres[] = '(jbd.jbd_id <> ' . $this->JbdId . ')';
        $wheres[] = '(jog.jog_gd_id = ' . $this->GdId . ')';
        $orWheres = [];
        foreach ($inputs as $input) {
            $input = trim($input);
            if (empty($input) === false) {
                $orWheres[] = StringFormatter::generateLikeQuery('jbd.jbd_serial_number', $input);
            }
        }
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
        $wheres[] = '(jbd.jbd_serial_number IS NOT NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jbd_id
                        FROM job_bundling_detail as jbd INNER JOIN
                        job_goods as jog ON jbd.jbd_jog_id = jog.jog_id ' . $strWhere;
        $sqlQuery = DB::select($query);

        return empty($sqlQuery);
    }
}
