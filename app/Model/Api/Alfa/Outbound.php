<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 Matalogix
 */

namespace App\Model\Api\Alfa;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Model\Dao\Relation\RelationDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle Stock Card.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class Outbound extends JobOrder
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadJobData') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
        } elseif ($this->ActionName === 'loadJobGoods') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
        } elseif ($this->ActionName === 'startPicking') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'loadJobDetail') {
            $this->Validation->checkRequire('job_id');
            $this->Validation->checkInt('job_id');
            $this->Validation->checkRequire('jog_id');
            $this->Validation->checkInt('jog_id');
        } elseif ($this->ActionName === 'loadJidStock') {
            $this->Validation->checkRequire('jod_gd_id');
            $this->Validation->checkRequire('jod_job_wh_id');
            $this->Validation->checkRequire('jod_gdu_id');
        } elseif ($this->ActionName === 'insertOutboundDetail') {
            $this->Validation->checkRequire('jod_job_id');
            $this->Validation->checkRequire('jod_jid_id');
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_jog_id');
            $this->Validation->checkRequire('jod_quantity');
            $this->Validation->checkFloat('jod_quantity');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('jod_gd_id');
        } elseif ($this->ActionName === 'updateOutboundDetail') {
            $this->Validation->checkRequire('jod_id');
            $this->Validation->checkRequire('jod_qty_loaded');
            $this->Validation->checkFloat('jod_qty_loaded');
        } elseif ($this->ActionName === 'deleteOutboundDetail') {
            $this->Validation->checkRequire('jod_id');
        } elseif ($this->ActionName === 'verifyScanStorage') {
            $this->Validation->checkRequire('jod_job_wh_id');
            $this->Validation->checkRequire('jod_gd_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('storage');
        } elseif ($this->ActionName === 'verifySnStorage') {
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_gd_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('serial_number');
        } elseif ($this->ActionName === 'loadSuggestionPickPn') {
            $this->Validation->checkRequire('jod_job_wh_id');
            $this->Validation->checkRequire('jod_gd_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('jod_jog_id');
        } elseif ($this->ActionName === 'verifyPn') {
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_gd_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('packing_number');
        } elseif ($this->ActionName === 'insertJodByPacking') {
            $this->Validation->checkRequire('jod_job_id');
            $this->Validation->checkRequire('jod_packing_number');
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_jog_id');
            $this->Validation->checkRequire('jod_quantity');
            $this->Validation->checkFloat('jod_quantity');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('jod_gd_id');
        } elseif ($this->ActionName === 'completePicking') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'truckArrive') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
            $this->Validation->checkRequire('job_id');
            $this->Validation->checkInt('job_id');
            $this->Validation->checkRequire('jac_id');
            $this->Validation->checkInt('jac_id');
            $this->Validation->checkRequire('jo_srt_id');
            $this->Validation->checkRequire('action');
            $idVendor = null;
            if ($this->isValidParameter('job_vendor_id') === true) {
                $this->Validation->checkInt('job_vendor_id');
                $idVendor = $this->getIntParameter('job_vendor_id');
            }
            $this->Validation->checkRequire('job_vendor');
            $this->Validation->checkRequire('rel_short_name', 1, 25);
            $this->Validation->checkUnique('rel_short_name', 'relation', [
                'rel_id' => $idVendor,
            ], [
                'rel_ss_id' => $this->User->getSsId(),
            ]);
            $this->Validation->checkRequire('job_driver');
            $this->Validation->checkRequire('date');
            if ($this->isValidParameter('date')) {
                $this->Validation->checkDate('date', '', '', 'Y-m-d');
            }
            $this->Validation->checkRequire('time');
            if ($this->isValidParameter('time')) {
                $this->Validation->checkTime('time', 'H:i');
            }
        } elseif ($this->ActionName === 'startLoading') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'completeLoading') {
            $this->loadJobActionValidation();
        }
    }


    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {

        if ($this->ActionName === 'loadJobData') {
            $job = $this->loadJobData();
            $this->doPrepareStatusJobData($job);
            $this->doPrepareNextJobActionData($job);
            $this->addResultData('jobOutbound', $job);
            $actionWarning = '';
            if (empty($job['job_start_store_on']) === false && empty($job['job_end_store_on']) === true) {
                $warnings = $this->doValidateCompletePicking($job['job_id']);
                if (empty($warnings) === false) {
                    $actionWarning = $warnings[0];
                }
            }

            if (empty($job['job_start_load_on']) === false && empty($job['job_end_load_on']) === true) {
                $warnings = $this->doValidateCompleteLoading();
                if (empty($warnings) === false) {
                    $actionWarning = $warnings[0];
                }
            }
            $this->addResultData('actionWarning', $actionWarning);
        } elseif ($this->ActionName === 'loadJobGoods') {
            $wheres = [];
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jo_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $this->addResultData('goods', $this->loadJobGoods($wheres));
        } elseif ($this->ActionName === 'startPicking') {
            $data = $this->doStartPicking();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'loadJobDetail') {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $jog = $this->loadJobGoodsQuantity($wheres);
            $this->addResultData('jog', $jog);

            $data = $this->loadJobGoodsStorage();
            $this->addResultData('jodS', $data);
        } elseif ($this->ActionName === 'loadJidStock') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('jod_job_wh_id') . ')';
            if ($this->isValidParameter('jod_lot_number')) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $data = $this->loadJidStock($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0), $this->getIntParameter('jod_id', 0));
            $this->addResultData('jidS', $data);
        } elseif ($this->ActionName === 'insertOutboundDetail') {
            $data = $this->doInsertOutboundDetail();
            $this->addResultData('jodId', $data);
            $jidData = [];
            if ($this->getStringParameter('jod_gd_sn', 'N') === 'Y') {
                $wheres = [];
                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
                $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
                $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('jod_job_wh_id') . ')';
                if ($this->isValidParameter('jod_lot_number') === true) {
                    $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
                }
                $jidData = $this->loadJidStock($wheres, $this->getIntParameter('limit', 5), $this->getIntParameter('offset', 0), $data);
            }
            $this->addResultData('jidS', $jidData);
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jod_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $jog = $this->loadJobGoodsQuantity($wheres);
            $this->addResultData('jog', $jog);
        } elseif ($this->ActionName === 'updateOutboundDetail') {
            $this->doUpdateOutboundDetail();
            $this->addResultData('jodId', $this->getIntParameter('jod_id'));
        } elseif ($this->ActionName === 'deleteOutboundDetail') {
            $this->doDeleteOutboundDetail();
            $this->addResultData('jodId', $this->getIntParameter('jod_id'));
        } elseif ($this->ActionName === 'loadSuggestionPickSn') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('jod_job_wh_id') . ')';
            if ($this->isValidParameter('jod_lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $data = $this->loadJidStock($wheres, 5);
            $this->addResultData('jidS', $data);
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jod_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $jog = $this->loadJobGoodsQuantity($wheres);
            $this->addResultData('jog', $jog);
        } elseif ($this->ActionName === 'verifyScanStorage') {
            $storageId = $this->doVerifyStorage();
            $this->addResultData('whs_id', $storageId);
        } elseif ($this->ActionName === 'verifySnStorage') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jod_whs_id') . ')';
            if ($this->isValidParameter('jod_lot_number')) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('serial_number'));
            $data = $this->loadJidStock($wheres);
            $result = [];
            $jidId = '';
            if (count($data) === 1) {
                $result = $data[0];
                $jidId = $result['jid_id'];
            }
            $this->addResultData('jidId', $jidId);
            $this->addResultData('jid', $result);
        } elseif ($this->ActionName === 'loadSuggestionPickPn') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('jod_job_wh_id') . ')';
            if ($this->isValidParameter('jod_lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $data = $this->loadJidPnStock($wheres, 5);

            $this->addResultData('jidS', $this->doPrepareApiJidPnStock($data));
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jod_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $jog = $this->loadJobGoodsQuantity($wheres);
            $this->addResultData('jog', $jog);
        } elseif ($this->ActionName === 'verifyPn') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jod_whs_id') . ')';
            $wheres[] = "(jid.jid_packing_number = '" . $this->getStringParameter('packing_number') . "')";
            if ($this->isValidParameter('jod_lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $data = $this->loadJidPnStock($wheres);
            $result = [];
            $pn = '';
            if (count($data) === 1) {
                $result = $this->doPrepareApiJidPnStock($data)[0];
                $pn = $this->getStringParameter('packing_number');
            }

            $this->addResultData('packing_number', $pn);
            $this->addResultData('jid', $result);
        } elseif ($this->ActionName === 'insertJodByPacking') {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jod_whs_id') . ')';
            if ($this->isValidParameter('jod_lot_number')) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jod_lot_number') . "')";
            }
            $data = $this->loadJidStock(array_merge($wheres, [
                "(jid.jid_packing_number = '" . $this->getStringParameter('jod_packing_number') . "')",
            ]), 0, 0, 0, false);
            $this->doInsertJodByPacking($data);
            # Load Suggestion Data
            $suggestion = $this->loadJidPnStock($wheres, 5);
            $this->addResultData('jidS', $this->doPrepareApiJidPnStock($suggestion));
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jod_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $jog = $this->loadJobGoodsQuantity($wheres);
            $this->addResultData('jog', $jog);
        } elseif ($this->ActionName === 'completePicking') {
            $data = $this->doCompletePicking();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'truckArrive') {
            $data = $this->doUpdateTruckArrive();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'startLoading') {
            $data = $this->doStartLoading();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'completeLoading') {
            $data = $this->doCompleteLoading();
            $this->addResultData('jobEvent', $data);
        }
    }

    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function doStartLoading(): array
    {
        DB::beginTransaction();
        try {
            # Update actual time arrival job
            $jobColVal = [
                'job_start_load_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jobColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = [];
        }
        return $results;
    }

    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function doCompleteLoading(): array
    {
        DB::beginTransaction();
        try {
            $detailData = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('jwId'));
            $jisDao = new JobInboundStockDao();
            $jodDao = new JobOutboundDetailDao();
            foreach ($detailData as $row) {
                $colValJis = [
                    'jis_jid_id' => $row['jod_jid_id'],
                    'jis_quantity' => (float)$row['jod_qty_loaded'] * -1,
                ];
                if (empty($row['jod_jis_id']) === true) {
                    $jisDao->doApiInsertTransaction($colValJis, $this->User->getId());
                    $jodDao->doApiUpdateTransaction($row['jod_id'], [
                        'jod_jis_id' => $jisDao->getLastInsertId(),
                    ], $this->User->getId());
                } else {
                    $jisDao->doApiUpdateTransaction($row['jod_jis_id'], $colValJis, $this->User->getId());
                }
            }
            $date = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time') . ':' . date('s');
            # Start Job Outbound Transfer
            if ($this->isValidParameter('jo_jtr_id')) {
                $jrtDao = new JobStockTransferDao();
                $jrtDao->doApiUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_end_out_on' => $date,
                ], $this->User->getId());
            }
            # Update actual time arrival job
            $jobColVal = [
                'job_end_load_on' => $date,
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jobColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(2);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = [];
        }
        return $results;
    }


    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function doUpdateTruckArrive(): array
    {
        DB::beginTransaction();
        try {
            $idVendor = $this->getIntParameter('job_vendor_id', 0);
            if ($this->isValidParameter('job_vendor_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $relNumber = $sn->loadNumber('Relation', $this->User->Relation->getOfficeId());
                $colVal = [
                    'rel_ss_id' => $this->User->getSsId(),
                    'rel_number' => $relNumber,
                    'rel_name' => $this->getStringParameter('job_vendor'),
                    'rel_short_name' => $this->getStringParameter('rel_short_name'),
                    'rel_owner' => 'N',
                    'rel_active' => 'Y',
                ];
                $relDao = new RelationDao();
                $relDao->doApiInsertTransaction($colVal, $this->User->getId());
                $idVendor = $relDao->getLastInsertId();
            }

            # Update actual time arrival job
            $jobColVal = [
                'job_ata_time' => $this->getStringParameter('time'),
                'job_vendor_id' => $idVendor,
                'job_driver' => $this->getStringParameter('job_driver'),
                'job_driver_phone' => $this->getStringParameter('job_driver_phone'),
                'job_ata_date' => $this->getStringParameter('date'),
                'job_truck_number' => $this->getStringParameter('job_truck_number'),
                'job_container_number' => $this->getStringParameter('job_container_number'),
                'job_seal_number' => $this->getStringParameter('job_seal_number'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doApiUpdateTransaction($this->getIntParameter('job_id'), $jobColVal, $this->User->getId());

            $jacDao = new JobActionDao();
            $actionCode = $this->getStringParameter('action');
            if (mb_strtolower($actionCode) === 'arrive') {
                $jacId = $this->getIntParameter('jac_id');
            } else {
                $actionCode = 'Arrive';
                $jacId = $this->getJacIdForTruckArrive($this->getIntParameter('jo_id'));
            }
            $jacColVal = [
                'jac_start_by' => $this->User->getId(),
                'jac_start_on' => date('Y-m-d H:i:s'),
                'jac_start_date' => $this->getStringParameter('date'),
                'jac_start_time' => $this->getStringParameter('time'),
                'jac_end_by' => $this->User->getId(),
                'jac_end_on' => date('Y-m-d H:i:s'),
                'jac_end_date' => $this->getStringParameter('date'),
                'jac_end_time' => $this->getStringParameter('time'),
            ];
            $jacDao->doApiUpdateTransaction($jacId, $jacColVal, $this->User->getId());

            $key = $actionCode . $this->getIntParameter('jo_srt_id') . '.event';
            $event = Trans::getWord($key, 'action');
            $jaeColVal = [
                'jae_jac_id' => $jacId,
                'jae_description' => $event,
                'jae_date' => $this->getStringParameter('date'),
                'jae_time' => $this->getStringParameter('time'),
                'jae_remark' => '',
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doApiInsertTransaction($jaeColVal, $this->User->getId());
            $jaeColVal['jae_id'] = $jaeDao->getLastInsertId();
            DB::commit();
            $keyAction = $actionCode . $this->getIntParameter('jo_srt_id') . '.description';
            $action = Trans::getWord($keyAction, 'action');

            $time = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time');
            $jaeColVal['jae_time_on'] = DateTimeParser::format($time, 'Y-m-d H:i', 'H:i d M Y');
            $jaeColVal['jae_added_on'] = date('H:i d M Y');
            $jaeColVal['jae_action'] = $action;
            $jaeColVal['jae_added_by'] = $this->User->getName();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = [];
        }
        return $results;
    }

    /**
     * Function to get document action modal.
     *
     * @param int $joId To store the job number id.
     *
     * @return int
     */
    private function getJacIdForTruckArrive($joId): int
    {
        $wheres = [];
        $wheres[] = '(jac.jac_jo_id = ' . $joId . ')';
        $wheres[] = "(ac.ac_code = 'Arrive')";
        $strWhere = ' WHERE ' . implode(' AND  ', $wheres);
        $query = 'SELECT jac.jac_id, ac.ac_description, ac.ac_code
                    FROM job_action as jac INNER JOIN
                      action as ac ON jac.jac_ac_id = ac.ac_id ' . $strWhere;
        $sqlResults = DB::select($query);
        $result = 0;
        if (count($sqlResults) === 1) {
            $result = (int)DataParser::objectToArray($sqlResults[0])['jac_id'];
        }

        return $result;
    }


    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function doCompletePicking(): array
    {
        DB::beginTransaction();
        try {
            $jodDao = new JobOutboundDetailDao();
            $jisDao = new JobInboundStockDao();
            $jodData = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('jwId'));
            foreach ($jodData as $row) {
                if (empty($row['jod_jis_id']) === false) {
                    $jisId = $row['jod_jis_id'];
                    $jisDao->doApiUpdateTransaction($jisId, [
                        'jis_jid_id' => $row['jod_jid_id'],
                        'jis_quantity' => (float)$row['jod_quantity'] * -1,
                    ], $this->User->getId());
                } else {
                    $jisDao->doApiInsertTransaction([
                        'jis_jid_id' => $row['jod_jid_id'],
                        'jis_quantity' => (float)$row['jod_quantity'] * -1,
                    ], $this->User->getId());
                    $jisId = $jisDao->getLastInsertId();
                }
                $jodDao->doApiUpdateTransaction($row['jod_id'], [
                    'jod_jis_id' => $jisId,
                ], $this->User->getId());
            }

            # Update start store job.
            $jobColVal = [
                'job_end_store_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jobColVal, $this->User->getId());
            # Update job action
            $jaeColVal = $this->doUpdateJobActionEvent(2);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = [];
        }
        return $results;
    }


    /**
     * Function to get the goods load data.
     *
     * @return string
     */
    private function doVerifyStorage(): string
    {
        $result = '';
        $wheres = [];
        $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jod_gd_id') . ')';
        $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jod_gdu_id') . ')';
        if ($this->isValidParameter('jod_lot_number')) {
            $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter("jod_lot_number") . "')";
        }
        $wheres[] = '(jid.jid_id NOT IN (SELECT jod.jod_jid_id
                                            FROM job_outbound_detail as jod INNER JOIN
                                            job_outbound as job ON jod.jod_job_id = job.job_id INNER JOIN
                                            job_order as j ON j.jo_id = job.job_jo_id
                                            WHERE j.jo_deleted_on IS NULL AND jod.jod_jis_id IS NULL AND jod.jod_deleted_on IS NULL
                                            GROUP BY jod.jod_jid_id))';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = "(LOWER(whs.whs_name) = '" . mb_strtolower($this->getStringParameter('storage')) . "')";
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('jod_job_wh_id') . ')';
        $wheres[] = '(jis.stock > 0)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs.whs_id
                FROM job_inbound_detail as jid INNER JOIN
                warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN
                (SELECT jis_jid_id, SUM(jis_quantity) as stock
                    FROM job_inbound_stock
                    WHERE jis_deleted_on IS NULL
                    GROUP by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $query .= ' GROUP BY whs.whs_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0])['whs_id'];
        }

        return $result;
    }

    /**
     * Function to load total number of draft project.
     *
     * @return void
     */
    private function doDeleteOutboundDetail(): void
    {
        DB::beginTransaction();
        try {
            $jodId = $this->getIntParameter('jod_id');
            $data = JobOutboundDetailDao::getByReference($jodId);
            if (empty($data) === false && empty($data['jod_jis_id']) === false && (int)$data['jod_jis_id'] > 0) {
                $jisDao = new JobInboundStockDao();
                $jisDao->doApiDeleteTransaction($data['jod_jis_id'], $this->User->getId());
            }

            $jodDao = new JobOutboundDetailDao();
            $jodDao->doApiDeleteTransaction($jodId, $this->User->getId());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to load total number of draft project.
     *
     * @return void
     */
    private function doUpdateOutboundDetail(): void
    {
        DB::beginTransaction();
        try {
            $jodColVal = [
                'jod_qty_loaded' => $this->getFloatParameter('jod_qty_loaded'),
            ];
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doApiUpdateTransaction($this->getIntParameter('jod_id'), $jodColVal, $this->User->getId());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to load total number of draft project.
     *
     * @return string
     */
    private function doInsertOutboundDetail(): string
    {
        DB::beginTransaction();
        try {
            $jodColVal = [
                'jod_job_id' => $this->getIntParameter('jod_job_id'),
                'jod_jog_id' => $this->getIntParameter('jod_jog_id'),
                'jod_jid_id' => $this->getIntParameter('jod_jid_id'),
                'jod_whs_id' => $this->getIntParameter('jod_whs_id'),
                'jod_gdu_id' => $this->getIntParameter('jod_gdu_id'),
                'jod_gd_id' => $this->getIntParameter('jod_gd_id'),
                'jod_quantity' => $this->getFloatParameter('jod_quantity'),
                'jod_qty_loaded' => $this->getFloatParameter('jod_qty_loaded'),
                'jod_lot_number' => $this->getStringParameter('jod_lot_number'),
            ];
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doApiInsertTransaction($jodColVal, $this->User->getId());
            DB::commit();
            $results = $jodDao->getLastInsertId();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = '';
        }
        return $results;
    }

    /**
     * Function to load total number of draft project.
     *
     * @param array $data
     *
     * @return void
     */
    private function doInsertJodByPacking(array $data): void
    {
        if (empty($data) === false) {
            DB::beginTransaction();
            try {
                $jodDao = new JobOutboundDetailDao();
                foreach ($data as $row) {
                    $qtyLoaded = null;
                    if ($this->isValidParameter('jod_qty_loaded') === true) {
                        $qtyLoaded = $row['jid_stock'];
                    }
                    $jodColVal = [
                        'jod_job_id' => $this->getIntParameter('jod_job_id'),
                        'jod_jog_id' => $this->getIntParameter('jod_jog_id'),
                        'jod_jid_id' => $row['jid_id'],
                        'jod_whs_id' => $this->getIntParameter('jod_whs_id'),
                        'jod_gdu_id' => $this->getIntParameter('jod_gdu_id'),
                        'jod_gd_id' => $this->getIntParameter('jod_gd_id'),
                        'jod_quantity' => $row['jid_stock'],
                        'jod_qty_loaded' => $qtyLoaded,
                        'jod_lot_number' => $row['jid_lot_number'],
                    ];
                    $jodDao->doApiInsertTransaction($jodColVal, $this->User->getId());
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->setErrorCode('500');
            }
        }
    }

    /**
     * Function to load job goods storage.
     *
     * @param array $wheres To store the condition.
     * @param int $limit To store the condition.
     * @param int $offset To store the condition.
     * @param int $jodId To store the condition.
     * @param boolean $apiFormat To store the condition.
     *
     * @return array
     */
    private function loadJidStock(array $wheres = [], int $limit = 0, int $offset = 0, $jodId = 0, bool $apiFormat = true): array
    {
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        $wheres[] = '((jod.jod_used IS NULL) OR (jod.jod_used < jis.jis_stock))';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jo.jo_number as jid_inbound_number, jo.jo_start_on as jid_inbound_date, jid.jid_whs_id,
                        whs.whs_name as jid_whs_name, jid.jid_gdu_id, uom.uom_code as jid_uom, jid.jid_lot_number,
                        jid.jid_expired_date, jid.jid_packing_number, jid.jid_serial_number,
                        jid.jid_gdt_id, gdt.gdt_code as jid_gdt_code, gdt.gdt_description as jid_gdt_description,
                        jid.jid_gcd_id, gcd.gcd_code as jid_gcd_code, gcd.gcd_description as jid_gcd_description,
                        jis.jis_stock as jid_stock, jod.jod_used as jid_used, jid.jid_weight, jid.jid_volume
                FROM job_inbound_detail as jid INNER JOIN
                     job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                    job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                    goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                    unit as  uom on gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                    goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                    goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock
                  FROM job_inbound_stock
                  where (jis_deleted_on IS NULL)
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN
                (SELECT jod_jid_id, SUM(jod_quantity) as jod_used
                    FROM job_outbound_detail
                    WHERE (jod_jis_id IS NULL) AND (jod_deleted_on IS NULL) AND (jod_id <> ' . $jodId . ')
                    GROUP BY jod_jid_id) as jod ON jid.jid_id = jod.jod_jid_id ' . $strWhere;
        $query .= ' ORDER BY jid.jid_gdt_id DESC, jo.jo_start_on, whs.whs_name, jid.jid_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $number = new NumberFormatter($this->User);
            if ($apiFormat) {
                $data = DataParser::arrayObjectToArrayAPI($sqlResult);
            } else {
                $data = DataParser::arrayObjectToArray($sqlResult);
            }
            foreach ($data as $row) {
                if (empty($row['jid_inbound_date']) === false) {
                    $row['jid_inbound_date'] = DateTimeParser::format($row['jid_inbound_date'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $row['jid_inbound_date'] = '';
                }

                $stock = (float)$row['jid_stock'] - (float)$row['jid_used'];
                $row['jid_stock'] = $stock;
                $row['jid_stock_str'] = $number->doFormatFloat($stock);
                $row['jid_total_weight'] = $number->doFormatFloat($stock * (float)$row['jid_weight']);
                $row['jid_total_cbm'] = $number->doFormatFloat($stock * (float)$row['jid_volume']);
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Function to load job goods storage.
     *
     * @param array $wheres To store the condition.
     * @param int $limit To store the condition.
     * @param int $offset To store the condition.
     * @param boolean $apiFormat To store the condition.
     *
     * @return array
     */
    private function loadJidPnStock(array $wheres = [], int $limit = 0, int $offset = 0, $apiFormat = true): array
    {
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_packing_number IS NOT NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        $wheres[] = '((jod.jod_used IS NULL) OR (jod.jod_used < jis.jis_stock))';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jo.jo_id, jo.jo_number as jid_inbound_number, jo.jo_start_on as jid_inbound_date, jid.jid_whs_id,
                        whs.whs_name as jid_whs_name, jid.jid_gdu_id, uom.uom_code as jid_uom, jid.jid_lot_number, jid.jid_packing_number,
                        SUM((CASE WHEN jis.jis_stock IS NULL THEN 0 ELSE jis.jis_stock END) - (CASE WHEN jod.jod_used IS NULL THEN 0 ELSE jod.jod_used END)) as jid_stock,
                        SUM(((CASE WHEN jis.jis_stock IS NULL THEN 0 ELSE jis.jis_stock END) - (CASE WHEN jod.jod_used IS NULL THEN 0 ELSE jod.jod_used END)) * jid.jid_weight) as jid_weight,
                        SUM(((CASE WHEN jis.jis_stock IS NULL THEN 0 ELSE jis.jis_stock END) - (CASE WHEN jod.jod_used IS NULL THEN 0 ELSE jod.jod_used END)) * jid.jid_volume) as  jid_volume
                FROM job_inbound_detail as jid INNER JOIN
                     job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                    job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                    goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                    unit as  uom on gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock
                  FROM job_inbound_stock
                  where (jis_deleted_on IS NULL)
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN
                (SELECT jod_jid_id, SUM(jod_quantity) as jod_used
                    FROM job_outbound_detail
                    WHERE (jod_jis_id IS NULL) AND (jod_deleted_on IS NULL)
                    GROUP BY jod_jid_id) as jod ON jid.jid_id = jod.jod_jid_id ' . $strWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_number, jo.jo_start_on, jid.jid_whs_id,
                        whs.whs_name, jid.jid_gdu_id, uom.uom_code, jid.jid_lot_number, jid.jid_packing_number';
        $query .= ' ORDER BY jo.jo_start_on, whs.whs_name, jo.jo_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResult = DB::select($query);
        if ($apiFormat) {
            return DataParser::arrayObjectToArrayAPI($sqlResult);
        }
        return DataParser::arrayObjectToArray($sqlResult);
    }

    /**
     * Function to load job goods storage.
     *
     * @param array $data To store the condition.
     *
     * @return array
     */
    private function doPrepareApiJidPnStock(array $data): array
    {

        $result = [];
        if (empty($data) === false) {
            $number = new NumberFormatter($this->User);
            foreach ($data as $row) {
                if (empty($row['jid_inbound_date']) === false) {
                    $row['jid_inbound_date'] = DateTimeParser::format($row['jid_inbound_date'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $row['jid_inbound_date'] = '';
                }

                $stock = (float)$row['jid_stock'];
                $row['jid_stock'] = $stock;
                $row['jid_stock_str'] = $number->doFormatFloat($stock);
                $row['jid_total_weight'] = $number->doFormatFloat((float)$row['jid_weight']);
                $row['jid_total_cbm'] = $number->doFormatFloat((float)$row['jid_volume']);
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Function to load job goods storage.
     *
     * @return array
     */
    private function loadJobGoodsStorage(): array
    {
        $wheres = [];
        $wheres[] = '(jod.jod_jog_id = ' . $this->getIntParameter('jog_id') . ')';
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jod.jod_id, jod.jod_job_id, jod.jod_jog_id, jod.jod_jis_id, jod.jod_whs_id,
                        jod.jod_quantity, jod.jod_gdu_id, uom.uom_code, jod.jod_jid_id, jo.jo_number,
                        whs.whs_name, jo.jo_start_on, jod.jod_qty_loaded, jid.jid_weight, jid.jid_volume,
                        jid.jid_lot_number, jid.jid_expired_date, jid.jid_packing_number, jid.jid_serial_number,
                        jid.jid_gdt_id, gdt.gdt_code, gdt.gdt_description,
                        jid.jid_gcd_id, gcd.gcd_code, gcd.gcd_description, jod.jod_created_on
                        FROM job_outbound_detail as jod INNER JOIN
                            job_goods as jog ON jod.jod_jog_id = jog.jog_id INNER JOIN
                             goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                            warehouse_storage as whs ON jod.jod_whs_id = whs.whs_id INNER JOIN
                            goods_unit as gdu on gdu.gdu_id = jod.jod_gdu_id INNER JOIN
                            unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                            brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                            goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                              job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                                job_order as jo ON ji.ji_jo_id = jo.jo_id LEFT OUTER JOIN
                             goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                             goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id ' . $strWhere;
        $query .= ' ORDER BY jod.jod_created_on DESC, jod.jod_id';
        $limit = $this->getIntParameter('limit', 0);
        $offset = $this->getIntParameter('offset', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $number = new NumberFormatter($this->User);
            $data = DataParser::arrayObjectToArrayAPI($sqlResult);
            foreach ($data as $row) {
                $temp = [];
                $temp['jod_id'] = $row['jod_id'];
                $temp['jod_job_id'] = $row['jod_job_id'];
                $temp['jod_jog_id'] = $row['jod_jog_id'];
                $temp['jod_whs_id'] = $row['jod_whs_id'];
                $temp['jod_storage'] = $row['whs_name'];
                $temp['jod_gdu_id'] = $row['jod_gdu_id'];
                $temp['jod_uom'] = $row['uom_code'];
                $temp['jod_jid_id'] = $row['jod_jid_id'];
                $temp['jod_inbound'] = $row['jo_number'];
                $temp['jod_lot_number'] = $row['jid_lot_number'];
                $temp['jod_expired_date'] = $row['jid_expired_date'];
                $temp['jod_packing_number'] = $row['jid_packing_number'];
                $temp['jod_serial_number'] = $row['jid_serial_number'];
                $temp['jod_gdt_id'] = $row['jid_gdt_id'];
                $temp['jod_gdt_code'] = $row['gdt_code'];
                $temp['jod_gdt_description'] = $row['gdt_description'];
                $temp['jod_gcd_id'] = $row['jid_gcd_id'];
                $temp['jod_gcd_code'] = $row['gcd_code'];
                $temp['jod_gcd_description'] = $row['gcd_description'];
                if (empty($row['jo_start_on']) === false) {
                    $temp['jod_inbound_date'] = DateTimeParser::format($row['jo_start_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $temp['jod_inbound_date'] = '';
                }
                $quantity = (float)$row['jod_quantity'];
                $temp['jod_quantity'] = $quantity;
                $temp['jod_quantity_str'] = $number->doFormatFloat($quantity);
                $temp['jod_weight_picked'] = $number->doFormatFloat($quantity * (float)$row['jid_weight']);
                $temp['jod_cbm_picked'] = $number->doFormatFloat($quantity * (float)$row['jid_volume']);
                $qtyLoaded = (float)$row['jod_qty_loaded'];
                $temp['jod_qty_loaded'] = $qtyLoaded;
                $temp['jod_qty_loaded_str'] = $number->doFormatFloat($qtyLoaded);
                $temp['jod_weight_loaded'] = $number->doFormatFloat($qtyLoaded * (float)$row['jid_weight']);
                $temp['jod_cbm_loaded'] = $number->doFormatFloat($qtyLoaded * (float)$row['jid_volume']);
                $temp['jod_load_remaining'] = $number->doFormatFloat($quantity - $qtyLoaded);
                $temp['jod_load_remaining_str'] = $number->doFormatFloat($quantity - $qtyLoaded);
                $result[] = $temp;
            }
        }

        return $result;
    }

    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function doStartPicking(): array
    {
        DB::beginTransaction();
        try {
            $date = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time') . ':' . date('s');
            # Start SO if so exist and not started yet.
            if ($this->isValidParameter('jo_so_id') && $this->isValidParameter('jo_so_start_on') === false) {
                $soDao = new SalesOrderDao();
                $soDao->doApiUpdateTransaction($this->getIntParameter('jo_so_id'), [
                    'so_start_by' => $this->User->getId(),
                    'so_start_on' => $date,
                ], $this->User->getId());
            }
            # Start Job Outbound Transfer
            if ($this->isValidParameter('jo_jtr_id')) {
                $jrtDao = new JobStockTransferDao();
                $jrtDao->doApiUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_start_out_on' => $date,
                ], $this->User->getId());
            }
            # Start Job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => $date,
            ];
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), $joColVal, $this->User->getId());

            # Update actual time arrival job
            $jobColVal = [
                'job_start_store_on' => $date,
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jobColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $results = [];
        }
        return $results;
    }

    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function loadJobData(): array
    {
        $result = JobOutboundDao::getByJoIdAndSystem($this->getIntParameter('jo_id'), $this->User->getSsId());
        if (empty($result) === false) {
            $result['jo_officer'] = $this->isUserOfficer($this->getIntParameter('jo_id'), (int)$result['jo_manager_id'], $this->User->getId());
            $result['jo_so_id'] = $result['so_id'];
            $eta = '';
            if (empty($result['job_eta_date']) === false) {
                if (empty($result['job_eta_time']) === false) {
                    $eta = DateTimeParser::format($result['job_eta_date'] . ' ' . $result['job_eta_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $eta = DateTimeParser::format($result['job_eta_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['job_eta'] = $eta;
            $ata = '';
            if (empty($result['job_ata_date']) === false) {
                if (empty($result['job_ata_time']) === false) {
                    $ata = DateTimeParser::format($result['job_ata_date'] . ' ' . $result['job_ata_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $ata = DateTimeParser::format($result['job_ata_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['job_ata'] = $ata;
        }

        return DataParser::doFormatApiData($result);
    }

    /**
     * Function to load job goods.
     *
     * @param array $wheres to store the conditions.
     *
     * @return array
     */
    private function loadJobGoods(array $wheres = []): array
    {
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_name as jog_gd_name, gd.gd_sku as jog_gd_sku, gd.gd_barcode as jog_gd_barcode,
                        br.br_name as jog_gd_brand, gdc.gdc_name as jog_gd_category, jog.jog_gdu_id, uom.uom_code as jog_uom,
                        jog.jog_production_number, jog.jog_production_date,
                        jog.jog_quantity, (jog.jog_quantity * gdu.gdu_weight) as jog_weight, (jog.jog_quantity * gdu.gdu_volume) as jog_volume,
                        (CASE WHEN jod.qty_picked IS NULL THEN 0 ELSE jod.qty_picked END) as picked,
                        (CASE WHEN jod.weight_picked IS NULL THEN 0 ELSE jod.weight_picked END) as weight_picked,
                        (CASE WHEN jod.cbm_picked IS NULL THEN 0 ELSE jod.cbm_picked END) as cbm_picked,
                        (CASE WHEN jod.qty_loaded IS NULL THEN 0 ELSE jod.qty_loaded END) as loaded,
                        (CASE WHEN jod.weight_loaded IS NULL THEN 0 ELSE jod.weight_loaded END) as weight_loaded,
                        (CASE WHEN jod.cbm_loaded IS NULL THEN 0 ELSE jod.cbm_loaded END) as cbm_loaded,
                        gd.gd_sn as jog_gd_sn, gd.gd_tonnage as jog_gd_tonnage, gd.gd_cbm as jog_gd_cbm, gd.gd_multi_sn as jog_gd_multi_sn,
                        gd.gd_receive_sn as jog_gd_receive_sn, gd.gd_generate_sn as jog_gd_generate_sn,
                        gd.gd_packing as jog_gd_packing, gd.gd_expired as jog_gd_expired, gd.gd_min_tonnage as jog_gd_min_tonnage,
                        gd.gd_max_tonnage as jog_gd_max_tonnage,
                        gd.gd_min_cbm as jog_gd_min_cbm, gd.gd_max_cbm as jog_gd_max_cbm, gd.gd_tonnage_dm as jog_gd_tonnage_dm,
                         gd.gd_cbm_dm as jog_gd_cbm_dm
                FROM job_goods as jog INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                (SELECT jod_jog_id, SUM(jod_quantity) as qty_picked, SUM(jod_quantity * jid.jid_weight) as weight_picked, SUM(jod_quantity * jid.jid_volume) as cbm_picked,
                SUM(jod_qty_loaded) as qty_loaded, SUM(jod_qty_loaded * jid.jid_weight) as weight_loaded, SUM(jod_qty_loaded * jid.jid_volume) as cbm_loaded
                    FROM job_outbound_detail as j INNER JOIN
                        job_inbound_detail as jid ON j.jod_jid_id = jid.jid_id
                    WHERE (jod_deleted_on IS NULL)
                    GROUP BY jod_jog_id) as jod ON jog.jog_id = jod.jod_jog_id ' . $strWhere;
        $query .= ' ORDER BY gd.gd_sku, jog.jog_id';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $number = new NumberFormatter($this->User);
            $temp = DataParser::arrayObjectToArrayAPI($sqlResult);
            foreach ($temp as $row) {
                $quantity = (float)$row['jog_quantity'];
                $row['jog_quantity'] = $quantity;
                $row['jog_quantity_str'] = $number->doFormatFloat($quantity);
                $row['jog_weight_planning'] = $number->doFormatFloat((float)$row['jog_weight']);
                $row['jog_cbm_planning'] = $number->doFormatFloat((float)$row['jog_volume']);
                $picked = (float)$row['picked'];
                $row['jog_picked'] = $picked;
                $row['jog_picked_str'] = $number->doFormatFloat($picked);
                $row['jog_weight_picked'] = $number->doFormatFloat((float)$row['weight_picked']);
                $row['jog_cbm_picked'] = $number->doFormatFloat((float)$row['cbm_picked']);
                $row['jog_pick_remaining'] = $quantity - $picked;
                $row['jog_pick_remaining_str'] = $number->doFormatFloat($quantity - $picked);
                $loaded = (float)$row['loaded'];
                $row['jog_loaded'] = $loaded;
                $row['jog_loaded_str'] = $number->doFormatFloat($loaded);
                $row['jog_weight_loaded'] = $number->doFormatFloat((float)$row['weight_loaded']);
                $row['jog_cbm_loaded'] = $number->doFormatFloat((float)$row['cbm_loaded']);
                $row['jog_load_remaining'] = $picked - $loaded;
                $row['jog_load_remaining_str'] = $number->doFormatFloat($picked - $loaded);
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Function to load job goods.
     *
     * @param array $wheres to store the conditions.
     *
     * @return array
     */
    private function loadJobGoodsQuantity(array $wheres = []): array
    {
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_quantity,
                        (CASE WHEN jod.qty_picked IS NULL THEN 0 ELSE jod.qty_picked END) as picked,
                        (CASE WHEN jod.weight_picked IS NULL THEN 0 ELSE jod.weight_picked END) as weight_picked,
                        (CASE WHEN jod.cbm_picked IS NULL THEN 0 ELSE jod.cbm_picked END) as cbm_picked,
                        (CASE WHEN jod.qty_loaded IS NULL THEN 0 ELSE jod.qty_loaded END) as loaded,
                        (CASE WHEN jod.weight_loaded IS NULL THEN 0 ELSE jod.weight_loaded END) as weight_loaded,
                        (CASE WHEN jod.cbm_loaded IS NULL THEN 0 ELSE jod.cbm_loaded END) as cbm_loaded
                FROM job_goods as jog LEFT OUTER JOIN
                (SELECT jod_jog_id, SUM(jod_quantity) as qty_picked, SUM(jod_quantity * jid.jid_weight) as weight_picked, SUM(jod_quantity * jid.jid_volume) as cbm_picked,
                SUM(jod_qty_loaded) as qty_loaded, SUM(jod_qty_loaded * jid.jid_weight) as weight_loaded, SUM(jod_qty_loaded * jid.jid_volume) as cbm_loaded
                    FROM job_outbound_detail as j INNER JOIN
                        job_inbound_detail as jid ON j.jod_jid_id = jid.jid_id
                    WHERE (jod_deleted_on IS NULL)
                    GROUP BY jod_jog_id) as jod ON jog.jog_id = jod.jod_jog_id ' . $strWhere;
        $sqlResult = DB::select($query);
        $result = [
            'jog_picked' => 0,
            'jog_picked_str' => "",
            'jog_weight_picked' => "",
            'jog_cbm_picked' => "",
            'jog_pick_remaining' => 0,
            'jog_pick_remaining_str' => "",
            'jog_loaded' => 0,
            'jog_loaded_str' => "",
            'jog_weight_loaded' => "",
            'jog_cbm_loaded' => "",
            'jog_load_remaining' => 0,
            'jog_load_remaining_str' => "",
        ];
        if (count($sqlResult) === 1) {
            $number = new NumberFormatter($this->User);
            $row = DataParser::arrayObjectToArrayAPI($sqlResult)[0];
            $quantity = (float)$row['jog_quantity'];
            $picked = (float)$row['picked'];
            $result['jog_picked'] = $picked;
            $result['jog_picked_str'] = $number->doFormatFloat($picked);
            $result['jog_weight_picked'] = $number->doFormatFloat((float)$row['weight_picked']);
            $result['jog_cbm_picked'] = $number->doFormatFloat((float)$row['cbm_picked']);
            $result['jog_pick_remaining'] = $quantity - $picked;
            $result['jog_pick_remaining_str'] = $number->doFormatFloat($quantity - $picked);
            $loaded = (float)$row['loaded'];
            $result['jog_loaded'] = $loaded;
            $result['jog_loaded_str'] = $number->doFormatFloat($loaded);
            $result['jog_weight_loaded'] = $number->doFormatFloat((float)$row['weight_loaded']);
            $result['jog_cbm_loaded'] = $number->doFormatFloat((float)$row['cbm_loaded']);
            $result['jog_load_remaining'] = $picked - $loaded;
            $result['jog_load_remaining_str'] = $number->doFormatFloat($picked - $loaded);
        }

        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @param int $jobId TO store the job outbound reference
     *
     * @return array
     */
    private function doValidateCompletePicking($jobId): array
    {
        $result = [];
        $diffQty = JobOutboundDetailDao::getTotalDifferentQuantityUnloadWithPickingByJobOrderId($this->getIntParameter('jo_id'));
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getWord('outboundStorageNotMatch', 'message', '', [
                    'outbound' => $diffQty['qty_outbound'],
                    'taken' => $diffQty['qty_pick'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('outboundPickingEmpty', 'message');
        }
        if (empty($result) === true && $this->User->Settings->getNameSpace() === 'mol') {
            $valid = JobOutboundDetailDao::isValidAllInboundDetailIdByJobId($jobId);
            if ($valid === false) {
                $result[] = Trans::getWord('invalidSerialNumberOutbound', 'message');
            }
        }

        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompleteLoading(): array
    {
        $result = [];
        $diffQty = JobOutboundDetailDao::getTotalDifferentQuantityLoadingWithJobGoodsByJobOrderId($this->getIntParameter('jo_id'));
        if (empty($diffQty) === false) {
            if ((float)$diffQty['qty_planning'] !== (float)$diffQty['qty_loaded']) {
                $number = new NumberFormatter($this->User);
                $result[] = Trans::getWord('outboundLoadedNotMatch', 'message', '', [
                    'planning' => $number->doFormatFloat((float)$diffQty['qty_planning']),
                    'loaded' => $number->doFormatFloat((float)$diffQty['qty_loaded']),
                ]);
            }
        } else {
            $result[] = Trans::getWord('canNotCompleteActionForEmptyGoods', 'message');
        }

        return $result;
    }
}
