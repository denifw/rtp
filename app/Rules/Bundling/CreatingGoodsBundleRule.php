<?php

namespace App\Rules\Bundling;

use App\Frame\Formatter\DataParser;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CreatingGoodsBundleRule implements Rule
{
    /**
     * Property to store the id for goods id.
     *
     * @var int $JbId
     */
    private $JbId;
    /**
     * Property to store the id for user id.
     *
     * @var int $UsId
     */
    private $UsId;

    /**
     * Property to store the id for user id.
     *
     * @var float $QuantityRequired
     */
    private $QuantityRequired;

    /**
     * Property to store the error message.
     *
     * @var string $Message
     */
    private $Message = '';

    /**
     * Create a new rule instance.
     *
     * @param int   $jbId             to store the id of job bundling.
     * @param float $quantityRequired to store the id of user.
     * @param int   $usId             to store the id of user.
     *
     * @return void
     */
    public function __construct($jbId, $quantityRequired, $usId)
    {
        $this->JbId = $jbId;
        $this->QuantityRequired = (float)$quantityRequired;
        $this->UsId = $usId;
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
        $query = 'SELECT jb_id, (CASE when jbd1.total IS NULL THEN 0 ELSE jbd1.total END) as jb_total,
                    (CASE when jbd2.jbd_id IS NULL THEN 0 ELSE jbd2.jbd_id END) as jb_own
                FROM job_bundling as jb LEFT OUTER JOIN
                (SELECT jbd_jb_id, SUM(jbd_quantity) as total
                 FROM job_bundling_detail
                 WHERE (jbd_deleted_on IS NULL)
                 GROUP by jbd_jb_id) as jbd1 ON jb.jb_id = jbd1.jbd_jb_id LEFT OUTER JOIN
                 (SELECT jbd_jb_id, jbd_id
                    FROM job_bundling_detail
                 WHERE (jbd_deleted_on IS NULL)
                 AND (jbd_start_on IS NOT NULL) AND (jbd_end_on IS NULL) 
                 AND ((jbd_us_id = ' . $this->UsId . ') OR (jbd_adjust_by = ' . $this->UsId . '))
                 GROUP BY jbd_jb_id, jbd_id) as jbd2 ON jb.jb_id = jbd2.jbd_jb_id ';
        $query .= ' WHERE (jb.jb_id = ' . $this->JbId . ')';
        $sqlResults = DB::select($query);
        if (count($sqlResults) !== 1) {
            $this->Message = trans('message.noDataFound');
            return false;
        }
        $data = DataParser::objectToArray($sqlResults[0]);
        $qty = (float)$data['jb_total'] + (float)$value;
        if ($qty > $this->QuantityRequired) {
            $this->Message = trans('validation.invalidCreatingBundle');
            return false;
        }
        if ((int)$data['jb_own'] > 0) {
            $this->Message = trans('message.validationOutstandingBundle');
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
}
