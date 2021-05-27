<?php

namespace App\Rules\Bundling;

use App\Frame\Formatter\DataParser;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BundlingMaterialLotNumber implements Rule
{
    /**
     * Property to store the id for job goods id.
     *
     * @var int $JogId
     */
    private $JogId;
    /**
     * Property to store the id for bundling material id.
     *
     * @var int $JbmId
     */
    private $JbmId;
    /**
     * Property to store the id for job outbound id.
     *
     * @var int $JobId
     */
    private $JobId;
    /**
     * Property to store the id for job bundling id.
     *
     * @var int $JbId
     */
    private $JbId;

    /**
     * Create a new rule instance.
     *
     * @param integer $jogId To set the id of the job goods id.
     * @param integer $jobId To set the id of job outbound.
     * @param integer $jbId  To set the id of job bundling.
     * @param integer $jbmId To set the id of job bundling material.
     *
     * @return void
     */
    public function __construct($jogId, $jobId, $jbId, $jbmId)
    {
        $this->JogId = $jogId;
        $this->JobId = $jobId;
        $this->JbId = $jbId;
        $this->JbmId = $jbmId;
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
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->JobId . ')';
        $wheres[] = '(jod.jod_jog_id = ' . $this->JogId . ')';
        $wheres[] = "(jid.jid_lot_number = '" . $value . "')";
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_lot_number, SUM(jod.jod_quantity) as stock, (CASE WHEN jb.used IS NULL THEN 0 ELSE jb.used END) as total_used
                    FROM job_outbound_detail as jod INNER JOIN
                    job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id LEFT OUTER JOIN
                    (SELECT jbm_lot_number, SUM(jbm_quantity) as used
                        FROM job_bundling_detail as jbd INNER JOIN
                        job_bundling_material as jbm ON jbm.jbm_jbd_id = jbd.jbd_id 
                        WHERE (jbm.jbm_jog_id = ' . $this->JogId . ')
                        AND (jbd.jbd_jb_id = ' . $this->JbId . ') AND (jbm.jbm_deleted_on IS NULL) AND (jbd.jbd_deleted_on IS NULL)
                        AND (jbm.jbm_id <> ' . $this->JbmId . ') AND (jbm.jbm_lot_number IS NOT NULL)
                        GROUP BY jbm_lot_number) as jb ON jid.jid_lot_number = jb.jbm_lot_number ' . $strWhere;
        $query .= ' GROUP BY jid.jid_lot_number, jb.used';
        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $data = DataParser::objectToArray($sqlResult[0]);
            if ((float)$data['total_used'] < (float)$data['stock']) {
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
        return trans('validation.invalidLotNumber');
    }
}
