<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Warehouse\Bundling;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDetailDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobBundlingDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse\Bundling
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundlingDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        if ($this->isValidParameter('jbd_id') === true) {
            $data = JobBundlingDetailDao::getByReference($this->getIntParameter('jbd_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                return [
                    'jbd_id_del' => $data['jbd_id'],
                    'jbd_us_id_del' => $data['jbd_us_id'],
                    'jbd_user_del' => $data['jbd_user'],
                    'jbd_serial_number_del' => $data['jbd_serial_number'],
                    'jbd_quantity_del' => $number->doFormatFloat((float)$data['jbd_quantity']),
                ];
            }
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadPutAwayData(): array
    {
        if ($this->isValidParameter('jbd_jb_id') === true) {
            $wheres = [];
            $wheres[] = '(jbd_jb_id = ' . $this->getIntParameter('jbd_jb_id') . ')';
            $wheres[] = '(jbd_serial_number IS NOT NULL)';
            $wheres[] = '(jbd_deleted_on IS NULL)';
            $wheres[] = '(jbd_serial_number NOT IN (SELECT jid_serial_number
                                                    FROM job_inbound_detail
                                                    WHERE jid_ji_id = ' . $this->getIntParameter('jb_inbound_id', 0) . '
                                                    and jid_deleted_on is null and jid_serial_number is not null 
                                                    and jid_id <> ' . $this->getIntParameter('jid_id', 0) . '
                                                    GROUP BY jid_serial_number))';
            if ($this->isValidParameter('jbd_serial_number') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('jbd_serial_number', $this->getStringParameter('jbd_serial_number'));
            }
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jbd_id, jbd_serial_number, jbd_serial_number as jbd_sn_text, jbd_lot_number
                    FROM job_bundling_detail ' . $strWheres;
            $query .= ' ORDER BY jbd_serial_number, jbd_id';
            $sqlResults = DB::select($query);
            if (empty($sqlResults) === false) {
                return DataParser::arrayObjectToArray($sqlResults);
            }
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadUnBunlingSn(): array
    {
        if ($this->isValidParameter('jbd_jb_id') === true && $this->isValidParameter('jb_outbound_id') === true) {
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('jb_outbound_id') . ')';
            $wheres[] = '(jid.jid_serial_number IS NOT NULL)';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $wheres[] = '(jid.jid_serial_number NOT IN (SELECT jbd_serial_number
                                                    FROM job_bundling_detail
                                                    WHERE jbd_jb_id = ' . $this->getIntParameter('jbd_jb_id', 0) . '
                                                    and jbd_deleted_on is null and jbd_serial_number is not null 
                                                    and jbd_id <> ' . $this->getIntParameter('jbd_id', 0) . '
                                                    GROUP BY jbd_serial_number))';
            if ($this->isValidParameter('jid_serial_number') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('jid_serial_number'));
            }
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jod.jod_id, jid.jid_serial_number as jod_serial_number, jid.jid_lot_number as jod_lot_number
                    FROM job_outbound_detail as jod INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres;
            $query .= ' ORDER BY jid_serial_number, jod.jod_id';
            $sqlResults = DB::select($query);
            if (empty($sqlResults) === false) {
                return DataParser::arrayObjectToArray($sqlResults);
            }
        }
        return [];
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadPutAwaySnMaterial(): array
    {
        if ($this->isValidParameter('jbd_jb_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_gd_id = ' . $this->getIntParameter('jog_gd_id') . ')';
            $wheres[] = '(jog.jog_gdu_id = ' . $this->getIntParameter('jog_gdu_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $wheres[] = '(jbd.jbd_jb_id = ' . $this->getIntParameter('jbd_jb_id') . ')';
            $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
            $wheres[] = '(jbm.jbm_serial_number IS NOT NULL)';
            $wheres[] = '(jbm.jbm_deleted_on IS NULL)';
            $wheres[] = '(jbm.jbm_serial_number NOT IN (SELECT jid_serial_number
                                                    FROM job_inbound_detail
                                                    WHERE jid_ji_id = ' . $this->getIntParameter('jb_inbound_id', 0) . '
                                                    and jid_deleted_on is null and jid_serial_number is not null 
                                                    and jid_id <> ' . $this->getIntParameter('jid_id', 0) . '
                                                    GROUP BY jid_serial_number))';
            if ($this->isValidParameter('jbm_serial_number') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('jbm.jbm_serial_number', $this->getStringParameter('jbm_serial_number'));
            }
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jbm.jbm_id, jbm.jbm_serial_number, jbm.jbm_lot_number
                    FROM job_bundling_detail as jbd INNER JOIN
                     job_bundling_material as jbm ON jbd.jbd_id = jbm.jbm_jbd_id INNER JOIN
                     job_goods as jog ON jog.jog_id = jbm.jbm_jog_id ' . $strWheres;
            $query .= ' ORDER BY jbm.jbm_serial_number, jbm.jbm_id';
            $sqlResults = DB::select($query);
            if (empty($sqlResults) === false) {
                return DataParser::arrayObjectToArray($sqlResults);
            }
        }
        return [];
    }


}
