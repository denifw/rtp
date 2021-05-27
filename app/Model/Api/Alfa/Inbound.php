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
use App\Frame\Formatter\Trans;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundReceiveDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Helper\Job\Warehouse\InboundReceivePn;
use App\Model\Helper\Job\Warehouse\InboundReceiveSn;
use App\Model\Helper\Job\Warehouse\InboundStoringSn;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle Stock Card.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class Inbound extends JobOrder
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
        } else if ($this->ActionName === 'updateTruckArrival') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
            $this->Validation->checkRequire('ji_id');
            $this->Validation->checkInt('ji_id');
            $this->Validation->checkRequire('jac_id');
            $this->Validation->checkInt('jac_id');
            $this->Validation->checkRequire('jo_srt_id');
            $this->Validation->checkRequire('action');
            $idVendor = null;
            if ($this->isValidParameter('ji_vendor_id') === true) {
                $this->Validation->checkInt('ji_vendor_id');
                $idVendor = $this->getIntParameter('ji_vendor_id');
            }
            $this->Validation->checkRequire('ji_vendor');
            $this->Validation->checkRequire('rel_short_name', 1, 25);
            $this->Validation->checkUnique('rel_short_name', 'relation', [
                'rel_id' => $idVendor,
            ], [
                'rel_ss_id' => $this->User->getSsId(),
            ]);
            $this->Validation->checkRequire('ji_driver');
            $this->Validation->checkRequire('ji_truck_number');
            $this->Validation->checkRequire('date');
            if ($this->isValidParameter('date')) {
                $this->Validation->checkDate('date', '', '', 'Y-m-d');
            }
            $this->Validation->checkRequire('time');
            if ($this->isValidParameter('time')) {
                $this->Validation->checkTime('time', 'H:i');
            }
        } else if ($this->ActionName === 'startUnload') {
            $this->loadJobActionValidation();
        } else if ($this->ActionName === 'loadJobGoodsInbound') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
            $this->Validation->checkRequire('ji_id');
            $this->Validation->checkInt('ji_id');
        } else if ($this->ActionName === 'loadJobInboundReceive') {
            $this->Validation->checkRequire('jir_ji_id');
            $this->Validation->checkRequire('jir_jog_id');
        } else if ($this->ActionName === 'updateInboundReceive') {
            $this->Validation->checkRequire('jir_jog_id');
            $this->Validation->checkRequire('jir_quantity');
            $this->Validation->checkRequire('jir_ji_id');
            $this->Validation->checkRequire('jir_gd_id');
            $this->Validation->checkRequire('jir_gd_sn');
            $this->Validation->checkRequire('jir_gd_receive_sn');
            $this->Validation->checkRequire('jir_gd_packing');
            $this->Validation->checkRequire('jir_gd_expired');
            $this->Validation->checkRequire('jir_gd_tonnage');
            $this->Validation->checkRequire('jir_gd_tonnage_dm');
            $this->Validation->checkRequire('jir_gd_cbm');
            $this->Validation->checkRequire('jir_gd_cbm_dm');
            if ($this->getStringParameter('jir_gd_receive_sn', 'N') === 'Y') {
                $this->Validation->checkFloat('jir_quantity', 1, 1);
                $this->Validation->checkRequire('jir_serial_number', 3);
            } else {
                $this->Validation->checkFloat('jir_quantity', 1);
            }
            if ($this->getStringParameter('jir_gd_packing', 'N') === 'Y') {
                $this->Validation->checkRequire('jir_packing_number');
                if ($this->isValidParameter('jir_packing_number')) {
                    $jirPn = new InboundReceivePn();
                    $jirPn->SsId = $this->User->getSsId();
                    $jirPn->JirId = $this->getIntParameter('jir_id', 0);
                    $jirPn->WhId = $this->getIntParameter('jir_ji_wh_id', 0);
                    $jirPn->GdId = $this->getIntParameter('jir_gd_id', 0);
                    $jirPn->LotNumber = $this->getStringParameter('jir_lot_number', '');
                    $this->Validation->checkInboundReceivePn('jir_packing_number', $jirPn);
                }
            }

            if ($this->getStringParameter('jir_gd_expired', 'N') === 'Y') {
                $this->Validation->checkRequire('jir_expired_date');
            }
            if (($this->getStringParameter('jir_gd_tonnage', 'N') === 'Y') ||
                (($this->getStringParameter('jir_gd_tonnage_dm', 'N') === 'Y') && ($this->isValidParameter('jir_gdt_id') === true))) {
                $this->Validation->checkRequire('jir_weight');
                $this->Validation->checkFloat('jir_weight');
                if ($this->isValidParameter('jir_gd_min_tonnage')) {
                    $this->Validation->checkMinValue('jir_weight', $this->getFloatParameter('jir_gd_min_tonnage'));
                }
                if ($this->isValidParameter('jir_gd_max_tonnage')) {
                    $this->Validation->checkMaxValue('jir_weight', $this->getFloatParameter('jir_gd_max_tonnage'));
                }
            }
            if ($this->getStringParameter('jir_gd_cbm', 'N') === 'Y') {
                $this->Validation->checkRequire('jir_length');
                $this->Validation->checkRequire('jir_height');
                $this->Validation->checkRequire('jir_width');
            }
            if ($this->isValidParameter('jir_serial_number')) {
                $jir = new InboundReceiveSn();
                $jir->JirId = $this->getIntParameter('jir_id', 0);
                $jir->JiId = $this->getIntParameter('jir_ji_id', 0);
                $jir->WhId = $this->getIntParameter('jir_ji_wh_id', 0);
                $jir->GdId = $this->getIntParameter('jir_gd_id', 0);
                $this->Validation->checkInboundReceiveSn('jir_serial_number', $jir);
            }
            if ($this->isValidParameter('jir_gdt_id') === true) {
                $this->Validation->checkRequire('jir_stored');
                $this->Validation->checkRequire('jir_gcd_id');
            }
            if ($this->isValidParameter('jir_expired_date')) {
                $this->Validation->checkDate('jir_expired_date');
            }
        } else if ($this->ActionName === 'deleteInboundReceive') {
            $this->Validation->checkRequire('jir_id');
        } else if ($this->ActionName === 'verifyInboundReceiveSn') {
            $this->Validation->checkRequire('serial_number');
            if ($this->isValidParameter('serial_number')) {
                $jir = new InboundReceiveSn();
                $jir->JirId = $this->getIntParameter('jir_id', 0);
                $jir->JiId = $this->getIntParameter('jir_ji_id', 0);
                $jir->WhId = $this->getIntParameter('jir_ji_wh_id', 0);
                $jir->GdId = $this->getIntParameter('jir_gd_id', 0);
                $this->Validation->checkInboundReceiveSn('serial_number', $jir);
            }
        } else if ($this->ActionName === 'verifyInboundReceivePn') {
            $this->Validation->checkRequire('packing_number');
            $this->Validation->checkRequire('jir_gd_id');
            $this->Validation->checkRequire('jir_gdu_id');
            $this->Validation->checkRequire('jo_jtr_id');
            $this->Validation->checkRequire('jir_ji_id');
        } else if ($this->ActionName === 'insertJirByPn') {
            $this->Validation->checkRequire('jir_packing_number');
            $this->Validation->checkRequire('jir_gd_id');
            $this->Validation->checkRequire('jir_gdu_id');
            $this->Validation->checkRequire('jo_jtr_id');
            $this->Validation->checkRequire('jir_ji_id');
            $this->Validation->checkRequire('jir_jog_id');
        } else if ($this->ActionName === 'doEndUnload') {
            $this->loadJobActionValidation();
        } else if ($this->ActionName === 'startPutAway') {
            $this->loadJobActionValidation();
        } else if ($this->ActionName === 'loadJirForPutAway') {
            $this->Validation->checkRequire('jir_ji_id');
        } else if ($this->ActionName === 'loadJirStorage') {
            if ($this->isValidParameter('jir_id') === false) {
                $this->Validation->checkRequire('jir_jog_id');
                $this->Validation->checkRequire('jir_ji_id');
            }
        } else if ($this->ActionName === 'updateInboundDetail') {
            if ($this->isValidParameter('jid_jir_id') === false) {
                $this->Validation->checkRequire('jid_jir_jog_id');
                $this->Validation->checkRequire('jid_ji_id');
            }
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jid_quantity');
            if ($this->isValidParameter('jid_quantity')) {
                $this->Validation->checkFloat('jid_quantity');
            }
            $this->Validation->checkRequire('jid_gd_id');
            $this->Validation->checkRequire('jid_gd_sn');
            $this->Validation->checkRequire('jid_gd_receive_sn');
            $this->Validation->checkRequire('jid_gd_multi_sn');
            $this->Validation->checkRequire('jid_gdu_id');

            $this->Validation->checkUnique('jid_whs_id', 'job_inbound_detail', [
                'jid_id' => $this->getIntParameter('jid_id'),
            ], [
                'jid_ji_id' => $this->getIntParameter('jid_ji_id'),
                'jid_gd_id' => $this->getIntParameter('jid_gd_id'),
                'jid_gdu_id' => $this->getIntParameter('jid_gdu_id'),
                'jid_lot_number' => $this->getStringParameter('jid_lot_number'),
                'jid_expired_date' => $this->getStringParameter('jid_expired_date'),
                'jid_packing_number' => $this->getStringParameter('jid_packing_number'),
                'jid_serial_number' => $this->getStringParameter('jid_serial_number'),
                'jid_gdt_id' => $this->getIntParameter('jid_gdt_id'),
                'jid_gcd_id' => $this->getIntParameter('jid_gcd_id'),
                'jid_deleted_on' => null,
            ]);

            if ($this->getStringParameter('jid_gd_sn', 'N') === 'Y') {
                $this->Validation->checkRequire('jid_serial_number');
                $this->Validation->checkFloat('jid_quantity', 1, 1);
            } else {
                $this->Validation->checkFloat('jid_quantity', 1);
            }
            if ($this->isValidParameter('jid_serial_number') === true) {
                $data = new InboundStoringSn();
                $data->JidId = $this->getIntParameter('jid_id', 0);
                $data->JirId = $this->getIntParameter('jid_jir_id', 0);
                $data->JiId = $this->getIntParameter('jid_ji_id', 0);
                $data->WhId = $this->getIntParameter('jid_ji_wh_id', 0);
                $data->JogId = $this->getIntParameter('jid_jir_jog_id', 0);
                $data->GdId = $this->getIntParameter('jid_gd_id', 0);
                $data->GdOnReceiveSn = $this->getStringParameter('jid_gd_receive_sn', 'N');
                $data->LotNumber = $this->getStringParameter('jid_lot_number', '');
                $data->ExpiredDate = $this->getStringParameter('jid_expired_date', '');
                $data->PackingNumber = $this->getStringParameter('jid_packing_number', '');
                $data->GdtId = $this->getIntParameter('jid_gdt_id', 0);
                $data->GcdId = $this->getIntParameter('jid_gcd_id', 0);
                $this->Validation->checkInboundStoringSn('jid_serial_number', $data);
            }
        } else if ($this->ActionName === 'deleteInboundDetail') {
            $this->Validation->checkRequire('jid_id');
            if ($this->isValidParameter('jid_jir_id') === false) {
                $this->Validation->checkRequire('jid_gd_id');
                $this->Validation->checkRequire('jid_gdu_id');
                $this->Validation->checkRequire('jid_ji_id');
            }
        } else if ($this->ActionName === 'verifyStorage') {
            $this->Validation->checkRequire('jid_ji_id');
            $this->Validation->checkRequire('jid_ji_wh_id');
            $this->Validation->checkRequire('storage');
        } else if ($this->ActionName === 'verifySnInbound') {
            $this->Validation->checkRequire('serial_number');
            if ($this->isValidParameter('serial_number') === true) {
                $data = new InboundStoringSn();
                $data->JidId = $this->getIntParameter('jid_id', 0);
                $data->JirId = $this->getIntParameter('jid_jir_id', 0);
                $data->JiId = $this->getIntParameter('jid_ji_id', 0);
                $data->WhId = $this->getIntParameter('jid_ji_wh_id', 0);
                $data->JogId = $this->getIntParameter('jid_jir_jog_id', 0);
                $data->GdId = $this->getIntParameter('jid_gd_id', 0);
                $data->GdOnReceiveSn = $this->getStringParameter('jid_gd_receive_sn', 'N');
                $data->LotNumber = $this->getStringParameter('jid_lot_number', '');
                $data->ExpiredDate = $this->getStringParameter('jid_expired_date', '');
                $data->PackingNumber = $this->getStringParameter('jid_packing_number', '');
                $data->GdtId = $this->getIntParameter('jid_gdt_id', 0);
                $data->GcdId = $this->getIntParameter('jid_gcd_id', 0);
                $this->Validation->checkInboundStoringSn('serial_number', $data);
            }
        } else if ($this->ActionName === 'updateInboundDetailByPn') {
            $this->Validation->checkRequire('jid_ji_id');
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jid_jir_jog_id');
            $this->Validation->checkRequire('jid_packing_number');
        } else if ($this->ActionName === 'completePutAway') {
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
            $this->addResultData('jobInbound', $job);

            $actionWarning = '';
            if (empty($job['joh_id']) === true) {
                if (empty($job['ji_start_load_on']) === false && empty($job['ji_end_load_on']) === true) {
                    $warnings = JobInboundDao::doValidateCompleteLoading($this->getIntParameter('jo_id'));
                    if (empty($warnings) === false) {
                        $actionWarning = $warnings[0];
                    }
                }
                if (empty($job['ji_start_store_on']) === false && empty($job['ji_end_store_on']) === true) {
                    $warnings = JobInboundDao::doValidateCompleteStorage($job['ji_id'], $this->User->getAllData());
                    if (empty($warnings) === false) {
                        $actionWarning = $warnings[0];
                    }
                }
            }
            $this->addResultData('actionWarning', $actionWarning);
        } else if ($this->ActionName === 'updateTruckArrival') {
            $event = $this->doUpdateTruckArrival();
            $this->addResultData('jobEvent', $event);
        } else if ($this->ActionName === 'startUnload') {
            $data = $this->doStartUnload();
            $this->addResultData('jobEvent', $data);
        } else if ($this->ActionName === 'loadJobGoodsInbound') {
            $wheres = [];
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jo_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $goods = $this->loadJobGoods($this->getIntParameter('ji_id'), $wheres);
            $this->addResultData('goods', $goods);
        } else if ($this->ActionName === 'loadJobInboundReceive') {
            $wheres = [];
            $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
            $wheres[] = '(jir.jir_jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheres[] = '(jir.jir_deleted_on IS NULL)';

            $orders = [];
            if ($this->getIntParameter('jir_order_type', 0) === 1) {
                $orders = [
                    'jir.jir_stored DESC',
                    'jir.jir_packing_number',
                    'jir.jir_serial_number',
                    'jir.jir_id',
                ];
            }

            $data = $this->loadJobInboundReceiveData($wheres, $orders, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            $jirS = [];
            $number = new NumberFormatter($this->User);
            foreach ($data as $row) {
                $qty = (float)$row['jir_quantity'];
                $row['jir_quantity_str'] = $number->doFormatFloat($qty);
                $row['jir_total_weight'] = $number->doFormatFloat($qty * (float)$row['jir_weight']);
                $row['jir_total_volume'] = $number->doFormatFloat($qty * (float)$row['jir_volume']);
                $jirS[] = $row;
            }
            $this->addResultData('jirs', $jirS);
            $wheresJog = [];
            $wheresJog[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheresJog[] = '(jog.jog_deleted_on IS NULL)';
            $goods = $this->loadJobGoods($this->getIntParameter('jir_ji_id'), $wheresJog);
            $jog = [];
            if (count($goods) === 1) {
                $jog = $goods[0];
            }
            $this->addResultData('jog', $jog);
        } else if ($this->ActionName === 'updateInboundReceive') {
            $data = $this->doUpdateInboundReceive();
            $this->addResultData('jirId', $data);
            # Load JOG Data
            $wheresJog = [];
            $wheresJog[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheresJog[] = '(jog.jog_deleted_on IS NULL)';
            $goods = $this->loadJobGoods($this->getIntParameter('jir_ji_id'), $wheresJog);
            $jog = [];
            if (count($goods) === 1) {
                $jog = $goods[0];
            }
            $this->addResultData('jog', $jog);
        } else if ($this->ActionName === 'deleteInboundReceive') {
            $this->doDeleteInboundReceive();
            $this->addResultData('jirId', $this->getStringParameter('jir_id'));
        } else if ($this->ActionName === 'verifyInboundReceiveSn') {
            $this->addResultData('serial_number', $this->getStringParameter('serial_number'));
            $jidId = '';
            $jid = [];
            if($this->isValidParameter('jo_jtr_id')) {
                $wheres = [];
                $wheres[] = '(jtr.jtr_id = '.$this->getIntParameter('jo_jtr_id').')';
                $wheres[] = '(jid.jid_gd_id = '.$this->getIntParameter('jir_gd_id').')';
                $wheres[] = '(jid.jid_gdu_id = '.$this->getIntParameter('jir_gdu_id').')';
                $wheres[] = "(jid.jid_serial_number = '".$this->getStringParameter('serial_number')."')";
                $wheres[] = '(jid.jid_serial_number NOT IN (select jir_serial_number
                                                          from job_inbound_receive
                                                          WHERE (jir_deleted_on IS NULL) AND (jir_ji_id = '.$this->getIntParameter('jir_ji_id').')
                                                          GROUP BY jir_serial_number))';
                $data = $this->loadJobTransferGoods($wheres);
                if(count($data) === 1) {
                    $jid = $data[0];
                    $jidId = $jid['jtr_id'];
                }
            }
            $this->addResultData('jidId', $jidId);
            $this->addResultData('jid', $jid);
        } else if ($this->ActionName === 'verifyInboundReceivePn') {
            $wheres = [];
            $wheres[] = '(jtr.jtr_id = '.$this->getIntParameter('jo_jtr_id').')';
            $wheres[] = '(jid.jid_gd_id = '.$this->getIntParameter('jir_gd_id').')';
            $wheres[] = '(jid.jid_gdu_id = '.$this->getIntParameter('jir_gdu_id').')';
            $wheres[] = "(jid.jid_packing_number = '".$this->getStringParameter('packing_number')."')";
            $wheres[] = '(jid.jid_packing_number NOT IN (select jir_packing_number
                                                          from job_inbound_receive
                                                          WHERE (jir_deleted_on IS NULL) AND (jir_ji_id = '.$this->getIntParameter('jir_ji_id').')
                                                          GROUP BY jir_packing_number))';
            $data = $this->loadJobTransferGoods($wheres);
            $pn = '';
            $result = [
                'jid_quantity' => 0,
                'jid_quantity_str' => '',
                'jid_total_weight' => 0,
                'jid_total_weight_str' => '',
                'jid_total_volume' => 0,
                'jid_total_volume_str' => '',
            ];
            if(empty($data) === false) {
                $pn = $this->getStringParameter('packing_number');
                $number = new NumberFormatter($this->User);
                foreach ($data as $row) {
                    $qty = (float)$row['jid_quantity'];
                    $result['jid_quantity'] += $qty;
                    $result['jid_quantity_str'] = $number->doFormatFloat($result['jid_quantity']);
                    $result['jid_total_weight'] += ($qty * (float)$row['jid_weight']);
                    $result['jid_total_weight_str'] = $number->doFormatFloat($result['jid_total_weight']);
                    $result['jid_total_volume'] += ($qty * (float)$row['jid_volume']);
                    $result['jid_total_volume_str'] = $number->doFormatFloat($result['jid_total_volume']);
                }
            }
            $this->addResultData('packing_number', $pn);
            $this->addResultData('jid', $result);
        } else if ($this->ActionName === 'insertJirByPn') {
            $wheres = [];
            $wheres[] = '(jtr.jtr_id = '.$this->getIntParameter('jo_jtr_id').')';
            $wheres[] = '(jid.jid_gd_id = '.$this->getIntParameter('jir_gd_id').')';
            $wheres[] = '(jid.jid_gdu_id = '.$this->getIntParameter('jir_gdu_id').')';
            $wheres[] = "(jid.jid_packing_number = '".$this->getStringParameter('jir_packing_number')."')";
            $wheres[] = '(jid.jid_packing_number NOT IN (select jir_packing_number
                                                          from job_inbound_receive
                                                          WHERE (jir_deleted_on IS NULL) AND (jir_ji_id = '.$this->getIntParameter('jir_ji_id').')
                                                          GROUP BY jir_packing_number))';
            $data = $this->loadJobTransferGoods($wheres, false);
            if(empty($data) === false) {
                $this->doInsertJirbyPacking($data);
            }
            $wheresJog = [];
            $wheresJog[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheresJog[] = '(jog.jog_deleted_on IS NULL)';
            $goods = $this->loadJobGoods($this->getIntParameter('jir_ji_id'), $wheresJog);
            $jog = [];
            if (count($goods) === 1) {
                $jog = $goods[0];
            }
            $this->addResultData('jog', $jog);
        } else if ($this->ActionName === 'doEndUnload') {
            $data = $this->doUpdateCompleteUnload();
            $this->addResultData('jobEvent', $data);
        } else if ($this->ActionName === 'startPutAway') {
            $data = $this->doUpdateStartPutAway();
            $this->addResultData('jobEvent', $data);
        } else if ($this->ActionName === 'loadJirForPutAway') {
            $wheres = [];
            $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
            $data = $this->loadJirForPutAway($wheres);
            $this->addResultData('jirs', $this->doPrepareJobReceivedData($data));
        } else if ($this->ActionName === 'loadJirStorage') {
            $wheres = [];
            if ($this->isValidParameter('jir_id')) {
                $wheres[] = '(jir.jir_id = ' . $this->getIntParameter('jir_id') . ')';
            } else {
                $wheres[] = '(jir.jir_jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
                $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
                if ($this->isValidParameter('jir_lot_number')) {
                    $wheres[] = "(jir.jir_lot_number = '" . $this->getStringParameter('jir_lot_number') . "')";
                } else {
                    $wheres[] = '(jir.jir_lot_number IS NULL)';
                }
                if ($this->isValidParameter('jir_expired_date')) {
                    $wheres[] = "(jir.jir_expired_date = '" . $this->getStringParameter('jir_expired_date') . "')";
                } else {
                    $wheres[] = '(jir.jir_expired_date IS NULL)';
                }
                if ($this->isValidParameter('jir_packing_number')) {
                    $wheres[] = "(jir.jir_packing_number = '" . $this->getStringParameter('jir_packing_number') . "')";
                } else {
                    $wheres[] = '(jir.jir_packing_number IS NULL)';
                }
                if ($this->isValidParameter('jir_gdt_id')) {
                    $wheres[] = '(jir.jir_gdt_id = ' . $this->getIntParameter('jir_gdt_id') . ')';
                } else {
                    $wheres[] = '(jir.jir_gdt_id IS NULL)';
                }
                if ($this->isValidParameter('jir_gcd_id')) {
                    $wheres[] = '(jir.jir_gcd_id = ' . $this->getIntParameter('jir_gcd_id') . ')';
                } else {
                    $wheres[] = '(jir.jir_gcd_id IS NULL)';
                }
            }
            $wheres[] = '(jir.jir_deleted_on IS NULL)';
            $orders = [];
            if ($this->getIntParameter('jir_order_type', 0) === 1) {
                $orders = [
                    'whs.whs_name',
                    'jid.jid_lot_number',
                    'jid.jid_expired_date',
                    'jid.jid_packing_number',
                    'jid.jid_serial_number',
                    'jid.jid_id',
                ];
            }

            $offset = $this->getIntParameter('offset', 0);
            if ($offset === 0) {
                $this->addResultData('jir', $this->loadJirTotalStoredData($wheres));
            } else {
                $this->addResultData('jir', []);
            }
            $data = $this->loadJirStorageData($wheres, $orders, $this->getIntParameter('limit', 0), $offset);
            $this->addResultData('jidS', $data);
        } else if ($this->ActionName === 'updateInboundDetail') {
            $wheres = [];
            if ($this->isValidParameter('jid_jir_id')) {
                $wheres[] = '(jir.jir_id = ' . $this->getIntParameter('jid_jir_id') . ')';
            } else {
                $wheres[] = '(jir.jir_jog_id = ' . $this->getIntParameter('jid_jir_jog_id') . ')';
                $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jid_ji_id') . ')';
                if ($this->isValidParameter('jid_lot_number')) {
                    $wheres[] = "(jir.jir_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
                } else {
                    $wheres[] = '(jir.jir_lot_number IS NULL)';
                }
                if ($this->isValidParameter('jid_expired_date')) {
                    $wheres[] = "(jir.jir_expired_date = '" . $this->getStringParameter('jid_expired_date') . "')";
                } else {
                    $wheres[] = '(jir.jir_expired_date IS NULL)';
                }
                if ($this->isValidParameter('jid_packing_number')) {
                    $wheres[] = "(jir.jir_packing_number = '" . $this->getStringParameter('jid_packing_number') . "')";
                } else {
                    $wheres[] = '(jir.jir_packing_number IS NULL)';
                }
                if ($this->isValidParameter('jid_gdt_id')) {
                    $wheres[] = '(jir.jir_gdt_id = ' . $this->getIntParameter('jid_gdt_id') . ')';
                } else {
                    $wheres[] = '(jir.jir_gdt_id IS NULL)';
                }
                if ($this->isValidParameter('jid_gcd_id')) {
                    $wheres[] = '(jir.jir_gcd_id = ' . $this->getIntParameter('jid_gcd_id') . ')';
                } else {
                    $wheres[] = '(jir.jir_gcd_id IS NULL)';
                }
            }
            $jirS = [];
            if ($this->isValidParameter('jid_id') === false) {
                $jirS = $this->loadJirForPutAway(array_merge($wheres, ['((jid.qty_stored IS NULL) OR (jir.jir_quantity <> jid.qty_stored))']), false);
            }
            $this->doUpdateInboundDetail($jirS);
            $this->addResultData('jir', $this->loadJirTotalStoredData($wheres, $this->getIntParameter('jid_id', 0)));
        } else if ($this->ActionName === 'deleteInboundDetail') {
            $jidS = [];
            if ($this->isValidParameter('jid_jir_id') === false) {
                $jidS = $this->loadListJidIdForDelete();
            } else {
                $jidS[] = $this->getIntParameter('jid_id');
            }
            $this->doDeleteGoodsStorage($jidS);
            $this->addResultData('deletedIdS', $jidS);
        } else if ($this->ActionName === 'verifyStorage') {
            $storageId = $this->doVerifyStorage();
            $this->addResultData('whs_id', $storageId);
        } else if ($this->ActionName === 'verifySnInbound') {
            $this->addResultData('serial_number', $this->getStringParameter('serial_number'));
        } else if ($this->ActionName === 'updateInboundDetailByPn') {
            $wheres = [];
            $wheres[] = '(jir.jir_jog_id = ' . $this->getIntParameter('jid_jir_jog_id') . ')';
            $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jid_ji_id') . ')';
            if ($this->isValidParameter('jid_lot_number')) {
                $wheres[] = "(jir.jir_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
            } else {
                $wheres[] = '(jir.jir_lot_number IS NULL)';
            }
            if ($this->isValidParameter('jid_expired_date')) {
                $wheres[] = "(jir.jir_expired_date = '" . $this->getStringParameter('jid_expired_date') . "')";
            } else {
                $wheres[] = '(jir.jir_expired_date IS NULL)';
            }
            if ($this->isValidParameter('jid_packing_number')) {
                $wheres[] = "(jir.jir_packing_number = '" . $this->getStringParameter('jid_packing_number') . "')";
            } else {
                $wheres[] = '(jir.jir_packing_number IS NULL)';
            }
            if ($this->isValidParameter('jid_gdt_id')) {
                $wheres[] = '(jir.jir_gdt_id = ' . $this->getIntParameter('jid_gdt_id') . ')';
            } else {
                $wheres[] = '(jir.jir_gdt_id IS NULL)';
            }
            if ($this->isValidParameter('jid_gcd_id')) {
                $wheres[] = '(jir.jir_gcd_id = ' . $this->getIntParameter('jid_gcd_id') . ')';
            } else {
                $wheres[] = '(jir.jir_gcd_id IS NULL)';
            }
            $jirS = $this->loadJirForPutAway(array_merge($wheres, ['((jid.qty_stored IS NULL) OR (jir.jir_quantity <> jid.qty_stored))']), false);
            if (empty($jirS) === false) {
                $this->doUpdateInboundDetailByPn($jirS);
            }
            $this->addResultData('jir', $this->loadJirTotalStoredData($wheres));
        } else if ($this->ActionName === 'completePutAway') {
            $data = $this->doUpdateCompletePutAway();
            $this->addResultData('jobEvent', $data);
        }
    }

    /**
     * Function to load stock card data
     *
     * @param array $wheres    To store the conditions.
     * @param bool  $formatApi To format the sql results;
     *
     * @return array
     */
    private function loadJirForPutAway(array $wheres = [], bool $formatApi = true): array
    {
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $wheres[] = '(jir.jir_quantity > 0)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_barcode as jog_gd_barcode, gd.gd_name as jog_gd_name, br.br_name as jog_gd_brand,
                            gdc.gdc_name as jog_gd_category, jog.jog_gdu_id, uom.uom_code as jog_uom, jog.jog_quantity,
                            gd.gd_sn as jog_gd_sn, gd.gd_tonnage as jog_gd_tonnage, gd.gd_cbm as jog_gd_cbm, gd.gd_multi_sn as jog_gd_multi_sn,
                            gd.gd_receive_sn as jog_gd_receive_sn, gd.gd_generate_sn as jog_gd_generate_sn,
                            gd.gd_packing as jog_gd_packing, gd.gd_expired as jog_gd_expired, gd.gd_min_tonnage as jog_gd_min_tonnage,
                            gd.gd_max_tonnage as jog_gd_max_tonnage,
                            gd.gd_min_cbm as jog_gd_min_cbm, gd.gd_max_cbm as jog_gd_max_cbm, gd.gd_tonnage_dm as jog_gd_tonnage_dm,
                             gd.gd_cbm_dm as jog_gd_cbm_dm, jir.jir_id, jir.jir_ji_id, jir.jir_quantity, jir.jir_lot_number, jir.jir_packing_number,
                             jir.jir_serial_number, jir.jir_expired_date, jir.jir_weight, jir.jir_volume, jir.jir_length, jir.jir_height, jir.jir_width,
                        (CASE WHEN jid.qty_stored IS NULL THEN 0 ELSE jid.qty_stored END) as jir_qty_stored,
                        jir.jir_gdt_id, gdt.gdt_code as jir_gdt_code, gdt.gdt_description as jir_gdt_description, jir.jir_gdt_remark,
                        jir.jir_gcd_id, gcd.gcd_code as jir_gcd_code, gcd.gcd_description as jir_gcd_description, jir.jir_gcd_remark
                    FROM job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
                    goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                    goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                    brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                    goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id INNER JOIN
                    unit as uom ON uom.uom_id = gdu.gdu_uom_id LEFT OUTER JOIN
                    goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                    goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as qty_stored
                        FROM job_inbound_detail
                        WHERE (jid_deleted_on IS NULL)
                        GROUP BY jid_jir_id) as jid ON jir.jir_id = jid.jid_jir_id ' . $strWheres;
        $query .= ' ORDER BY gd.gd_sku, jir.jir_id';
        $sqlResults = DB::select($query);
        if ($formatApi) {
            return DataParser::arrayObjectToArrayAPI($sqlResults);
        }
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $data To store the data;
     *
     * @return array
     */
    private function doPrepareJobReceivedData(array $data): array
    {
        $lastResults = [];
        if (empty($data) === false) {
            $tempKey = [];
            $number = new NumberFormatter($this->User);
            $results = [];
            foreach ($data as $row) {
                $qty = (float)$row['jir_quantity'];
                $stored = (float)$row['jir_qty_stored'];
                $remaining = $qty - $stored;
                $weight = $qty * (float)$row['jir_weight'];
                $volume = $qty * (float)$row['jir_volume'];
                $key = $row['jog_id'] . $row['jir_lot_number'] . $row['jir_expired_date'] . $row['jir_packing_number'] . $row['jir_gdt_id'] . $row['jir_gcd_id'];
                if (in_array($key, $tempKey, true) === false) {
                    $jog = [
                        'jog_id' => $row['jog_id'],
                        'jog_gdu_id' => $row['jog_gdu_id'],
                        'jog_uom' => $row['jog_uom'],
                        'jog_quantity' => $row['jog_quantity'],
                        'jog_quantity_str' => $number->doFormatFloat($row['jog_quantity']),
                        'jog_gd_id' => $row['jog_gd_id'],
                        'jog_gd_sku' => $row['jog_gd_sku'],
                        'jog_gd_barcode' => $row['jog_gd_barcode'],
                        'jog_gd_name' => $row['jog_gd_name'],
                        'jog_gd_brand' => $row['jog_gd_brand'],
                        'jog_gd_category' => $row['jog_gd_category'],
                        'jog_gd_sn' => $row['jog_gd_sn'],
                        'jog_gd_generate_sn' => $row['jog_gd_generate_sn'],
                        'jog_gd_receive_sn' => $row['jog_gd_receive_sn'],
                        'jog_gd_multi_sn' => $row['jog_gd_multi_sn'],
                        'jog_gd_packing' => $row['jog_gd_packing'],
                        'jog_gd_expired' => $row['jog_gd_expired'],
                        'jog_gd_tonnage' => $row['jog_gd_tonnage'],
                        'jog_gd_tonnage_dm' => $row['jog_gd_tonnage_dm'],
                        'jog_gd_min_tonnage' => $row['jog_gd_min_tonnage'],
                        'jog_gd_max_tonnage' => $row['jog_gd_max_tonnage'],
                        'jog_gd_cbm' => $row['jog_gd_cbm'],
                        'jog_gd_cbm_dm' => $row['jog_gd_cbm_dm'],
                        'jog_gd_min_cbm' => $row['jog_gd_min_cbm'],
                        'jog_gd_max_cbm' => $row['jog_gd_max_cbm'],
                        'jir_id' => $row['jir_id'],
                        'jir_lot_number' => $row['jir_lot_number'],
                        'jir_expired_date' => $row['jir_expired_date'],
                        'jir_packing_number' => $row['jir_packing_number'],
                        'jir_quantity' => $qty,
                        'jir_qty_stored' => $stored,
                        'jir_qty_remaining' => $remaining,
                        'jir_weight' => $weight,
                        'jir_volume' => $volume,
                        'jir_gdt_id' => $row['jir_gdt_id'],
                        'jir_gdt_code' => $row['jir_gdt_code'],
                        'jir_gdt_description' => $row['jir_gdt_description'],
                        'jir_gdt_remark' => $row['jir_gdt_remark'],
                        'jir_gcd_id' => $row['jir_gcd_id'],
                        'jir_gcd_code' => $row['jir_gcd_code'],
                        'jir_gcd_description' => $row['jir_gcd_description'],
                        'jir_gcd_remark' => $row['jir_gcd_remark'],
                    ];
                    $results[] = $jog;
                    $tempKey[] = $key;
                } else {
                    $index = array_search($key, $tempKey, true);
                    $results[$index]['jir_id'] = "";
                    $results[$index]['jir_quantity'] += $qty;
                    $results[$index]['jir_qty_stored'] += $stored;
                    $results[$index]['jir_qty_remaining'] += $remaining;
                    $results[$index]['jir_weight'] += $weight;
                    $results[$index]['jir_volume'] += $volume;
                }
            }
            # format string quantity
            foreach ($results as $row) {
                $row['jir_quantity_str'] = $number->doFormatFloat($row['jir_quantity']);
                $row['jir_qty_stored_str'] = $number->doFormatFloat($row['jir_qty_stored']);
                $row['jir_qty_remaining_str'] = $number->doFormatFloat($row['jir_qty_remaining']);
                $row['jir_weight_str'] = $number->doFormatFloat($row['jir_weight']);
                $row['jir_volume_str'] = $number->doFormatFloat($row['jir_volume']);
                $lastResults[] = $row;
            }
        }
        return $lastResults;
    }

    /**
     * Function to load stock card data
     *
     * @param array $wheres To store the conditions.
     * @param array $orders To store the conditions.
     * @param int   $limit  To store the conditions.
     * @param int   $offset To store the conditions.
     *
     * @return array
     */
    private function loadJobInboundReceiveData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $strWheres = '';
        if (empty($wheres) === false) {
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jir.jir_id, jir.jir_ji_id, jir.jir_jog_id, jir.jir_lot_number, jir.jir_packing_number, jir.jir_serial_number,
                        jir.jir_expired_date, jir.jir_quantity, jir.jir_weight, jir.jir_length, jir.jir_height, jir.jir_width, jir.jir_volume,
                        jir.jir_gdt_id, gdt.gdt_code as jir_gdt_code, gdt.gdt_description as jir_gdt_description, jir.jir_gdt_remark,
                        jir.jir_gcd_id, gcd.gcd_code as jir_gcd_code, gcd.gcd_description as jir_gcd_description, jir.jir_gcd_remark,
                        jir.jir_stored, jir.jir_created_on
                    FROM job_inbound_receive as jir
                    LEFT OUTER JOIN goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id
                    LEFT OUTER JOIN goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id ' . $strWheres;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jir.jir_created_on DESC, jir.jir_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArrayAPI($sqlResults);
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doUpdateCompletePutAway(): array
    {
        DB::beginTransaction();
        try {
            $wheres = [];
            $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('jwId') . ')';
            $wheres[] = '(jid.jid_deleted_on IS NULL)';
            $wheres[] = '(ji.ji_end_store_on IS NULL)';
            $storage = JobInboundDetailDao::loadData($wheres);
            if (empty($storage) === false) {
                $jisDao = new JobInboundStockDao();
                foreach ($storage as $row) {
                    $jisColVal = [
                        'jis_jid_id' => $row['jid_id'],
                        'jis_quantity' => $row['jid_quantity'],
                    ];
                    $jisDao->doApiInsertTransaction($jisColVal, $this->User->getId());
                }

                $date = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time') . ':' . date('s');
                if ($this->isValidParameter('jo_jtr_id')) {
                    $jrtDao = new JobStockTransferDao();
                    $jrtDao->doApiUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                        'jtr_end_in_on' => $date,
                    ], $this->User->getId());
                }
                $jowColVal = [
                    'ji_end_store_on' => $date,
                ];
                $jiDao = new JobInboundDao();
                $jiDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jowColVal, $this->User->getId());
                # Update job action
                $jaeColVal = $this->doUpdateJobActionEvent(2);
                DB::commit();
                $results = $jaeColVal;
            } else {
                DB::rollBack();
                $results = [];
                $this->setErrorCode('403');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
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
        $wheres[] = '(ji.ji_id = ' . $this->getIntParameter('jid_ji_id') . ')';
        $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('jid_ji_wh_id') . ')';
        $wheres[] = "(LOWER(whs.whs_name) = '" . mb_strtolower($this->getStringParameter('storage')) . "')";
        $wheres[] = '(whs.whs_deleted_on IS NULL)';
        $wheres[] = "(whs.whs_active = 'Y')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs.whs_id
                FROM job_inbound as ji INNER JOIN
                    warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                    warehouse_storage as whs ON whs.whs_wh_id = wh.wh_id' . $strWheres;
        $query .= ' GROUP BY whs.whs_id';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0])['whs_id'];
        }

        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $jidS To stored the deleted id
     *
     * @return void
     */
    private function doDeleteGoodsStorage(array $jidS): void
    {
        if (empty($jidS) === false) {
            DB::beginTransaction();
            try {

                $jidDao = new JobInboundDetailDao();
                foreach ($jidS as $jidId) {
                    $jidDao->doApiDeleteTransaction($jidId, $this->User->getId());
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->setErrorCode('500');
            }
        }
    }


    /**
     * Function to get the goods load data.
     *
     * @param array $jirS To store the job_inbound_receive
     *
     * @return void
     */
    private function doUpdateInboundDetail(array $jirS): void
    {
        DB::beginTransaction();
        try {
            $jidDao = new JobInboundDetailDao();
            if ($this->isValidParameter('jid_id')) {
                $jidDao->doApiUpdateTransaction($this->getIntParameter('jid_id'), [
                    'jid_whs_id' => $this->getIntParameter('jid_whs_id'),
                    'jid_quantity' => $this->getFloatParameter('jid_quantity'),
                    'jid_serial_number' => $this->getStringParameter('jid_serial_number'),
                ], $this->User->getId());
            } else {
                $qty = $this->getFloatParameter('jid_quantity');
                foreach ($jirS as $jir) {
                    if ($qty > 0) {
                        $qtyReceive = (float)$jir['jir_quantity'];
                        if ($qty > $qtyReceive) {
                            $qtyStored = $qtyReceive;
                        } else {
                            $qtyStored = $qty;
                        }
                        $jidColVal = [
                            'jid_ji_id' => $jir['jir_ji_id'],
                            'jid_jir_id' => $jir['jir_id'],
                            'jid_whs_id' => $this->getIntParameter('jid_whs_id'),
                            'jid_gd_id' => $jir['jog_gd_id'],
                            'jid_gdu_id' => $jir['jog_gdu_id'],
                            'jid_quantity' => $qtyStored,
                            'jid_lot_number' => $jir['jir_lot_number'],
                            'jid_serial_number' => $this->getStringParameter('jid_serial_number'),
                            'jid_expired_date' => $jir['jir_expired_date'],
                            'jid_packing_number' => $jir['jir_packing_number'],
                            'jid_adjustment' => 'N',
                            'jid_gdt_id' => $jir['jir_gdt_id'],
                            'jid_gdt_remark' => $jir['jir_gdt_remark'],
                            'jid_gcd_id' => $jir['jir_gcd_id'],
                            'jid_gcd_remark' => $jir['jir_gcd_remark'],
                            'jid_length' => $jir['jir_length'],
                            'jid_width' => $jir['jir_width'],
                            'jid_height' => $jir['jir_height'],
                            'jid_volume' => $jir['jir_volume'],
                            'jid_weight' => $jir['jir_weight'],
                        ];
                        $jidDao->doApiInsertTransaction($jidColVal, $this->User->getId());
                        $qty -= $qtyStored;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $jirS To store the job_inbound_receive
     *
     * @return void
     */
    private function doUpdateInboundDetailByPn(array $jirS): void
    {
        DB::beginTransaction();
        try {
            $jidDao = new JobInboundDetailDao();
            foreach ($jirS as $jir) {
                $jidColVal = [
                    'jid_ji_id' => $jir['jir_ji_id'],
                    'jid_jir_id' => $jir['jir_id'],
                    'jid_whs_id' => $this->getIntParameter('jid_whs_id'),
                    'jid_gd_id' => $jir['jog_gd_id'],
                    'jid_gdu_id' => $jir['jog_gdu_id'],
                    'jid_quantity' => $jir['jir_quantity'],
                    'jid_lot_number' => $jir['jir_lot_number'],
                    'jid_serial_number' => $jir['jir_serial_number'],
                    'jid_expired_date' => $jir['jir_expired_date'],
                    'jid_packing_number' => $jir['jir_packing_number'],
                    'jid_adjustment' => 'N',
                    'jid_gdt_id' => $jir['jir_gdt_id'],
                    'jid_gdt_remark' => $jir['jir_gdt_remark'],
                    'jid_gcd_id' => $jir['jir_gcd_id'],
                    'jid_gcd_remark' => $jir['jir_gcd_remark'],
                    'jid_length' => $jir['jir_length'],
                    'jid_width' => $jir['jir_width'],
                    'jid_height' => $jir['jir_height'],
                    'jid_volume' => $jir['jir_volume'],
                    'jid_weight' => $jir['jir_weight'],
                ];
                $jidDao->doApiInsertTransaction($jidColVal, $this->User->getId());
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }


    /**
     * Function to get the goods load data.
     *
     * @param array $wheres To store the where condition.
     * @param array $orders To store the where condition.
     * @param int   $limit  To store the where condition.
     * @param int   $offset To store the where condition.
     *
     * @return array
     */
    private function loadJirStorageData(array $wheres = [], array $orders = [], int $limit = 0, int $offset = 0): array
    {
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = "(jid.jid_adjustment = 'N')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jid.jid_jir_id, jid.jid_whs_id, whs.whs_name as jid_storage,
                      jid.jid_quantity, jid.jid_gdu_id, uom.uom_code as jid_uom,
                      jid.jid_lot_number, jid.jid_weight, jid.jid_volume, jid.jid_serial_number, jid.jid_packing_number,
                      jid.jid_expired_date, jid.jid_created_on
                        FROM job_inbound_receive as jir
                            INNER JOIN job_inbound_detail as jid ON jir.jir_id = jid.jid_jir_id
                            INNER JOIN job_inbound as ji ON ji.ji_id = jid.jid_ji_id
                            INNER JOIN warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id
                            INNER JOIN goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id
                            INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id ' . $strWhere;
        if (empty($orders) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orders);
        } else {
            $query .= ' ORDER BY jid.jid_created_on DESC, jid.jid_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);

        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $number = new NumberFormatter($this->User);
        $result = [];
        foreach ($data as $row) {
            $qty = (float)$row['jid_quantity'];
            $row['jid_quantity_str'] = $number->doFormatFloat($row['jid_quantity']);
            $row['jid_total_weight'] = $number->doFormatFloat($qty * (float)$row['jid_weight']);
            $row['jid_total_volume'] = $number->doFormatFloat($qty * (float)$row['jid_volume']);
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $wheres To store the where condition.
     * @param int   $jidId  To store the exclude stored quantity.
     *
     * @return array
     */
    private function loadJirTotalStoredData(array $wheres = [], int $jidId = 0): array
    {
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jir.jir_id, jir.jir_quantity, (CASE WHEN jid.qty_stored IS NULL THEN 0 ELSE jid.qty_stored END) as jir_qty_stored
                        FROM job_inbound_receive as jir LEFT OUTER JOIN
                            (SELECT jid_jir_id, SUM(jid_quantity) as qty_stored
                                FROM job_inbound_detail
                                WHERE (jid_deleted_on IS NULL) AND (jid_id <> ' . $jidId . ')
                                GROUP BY jid_jir_id) as jid ON jir.jir_id = jid.jid_jir_id' . $strWhere;
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $result = [
            'jir_qty_stored' => 0,
            'jir_qty_stored_str' => '',
            'jir_qty_remaining' => 0,
            'jir_qty_remaining_str' => '',
        ];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $qty = (float)$row['jir_quantity'];
            $stored = (float)$row['jir_qty_stored'];
            $remaining = $qty - $stored;
            $result['jir_qty_stored'] += $stored;
            $result['jir_qty_stored_str'] = $number->doFormatFloat($result['jir_qty_stored']);
            $result['jir_qty_remaining'] += $remaining;
            $result['jir_qty_remaining_str'] = $number->doFormatFloat($result['jir_qty_remaining']);
        }
        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doUpdateStartPutAway(): array
    {
        DB::beginTransaction();
        try {
            # Update actual time arrival job
            $jiColVal = [
                'ji_start_store_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jiColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doUpdateCompleteUnload(): array
    {
        DB::beginTransaction();
        try {
            # Set unload date
            $jiColVal = [
                'ji_end_load_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jiColVal, $this->User->getId());

            # Update job action
            $jaeColVal = $this->doUpdateJobActionEvent(2);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }


    /**
     * Function to get the goods load data.
     *
     * @return void
     */
    private function doDeleteInboundReceive(): void
    {
        DB::beginTransaction();
        try {
            $jirDao = new JobInboundReceiveDao();
            $jirDao->doApiDeleteTransaction($this->getIntParameter('jir_id'), $this->User->getId());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to get the goods load data.
     *
     * @return string
     */
    private function doUpdateInboundReceive(): string
    {
        DB::beginTransaction();
        try {
            $volume = null;
            if (($this->isValidParameter('jir_length') === true) && ($this->isValidParameter('jir_height') === true) && ($this->isValidParameter('jir_width') === true)) {
                $volume = $this->getFloatParameter('jir_length') * $this->getFloatParameter('jir_height') * $this->getFloatParameter('jir_width');
            }
            # Do Update quantity actual
            $jirColVal = [
                'jir_ji_id' => $this->getIntParameter('jir_ji_id'),
                'jir_jog_id' => $this->getIntParameter('jir_jog_id'),
                'jir_quantity' => $this->getFloatParameter('jir_quantity'),
                'jir_qty_damage' => 0,
                'jir_serial_number' => $this->getStringParameter('jir_serial_number'),
                'jir_lot_number' => $this->getStringParameter('jir_lot_number'),
                'jir_packing_number' => $this->getStringParameter('jir_packing_number'),
                'jir_expired_date' => $this->getStringParameter('jir_expired_date'),
                'jir_gdt_id' => $this->getIntParameter('jir_gdt_id'),
                'jir_gdt_remark' => $this->getStringParameter('jir_gdt_remark'),
                'jir_gcd_id' => $this->getIntParameter('jir_gcd_id'),
                'jir_gcd_remark' => $this->getStringParameter('jir_gcd_remark'),
                'jir_stored' => $this->getStringParameter('jir_stored', 'Y'),
                'jir_length' => $this->getFloatParameter('jir_length'),
                'jir_width' => $this->getFloatParameter('jir_width'),
                'jir_height' => $this->getFloatParameter('jir_height'),
                'jir_volume' => $volume,
                'jir_weight' => $this->getFloatParameter('jir_weight'),
            ];
            $jirDao = new JobInboundReceiveDao();
            if ($this->isValidParameter('jir_id') === false) {
                $jirDao->doApiInsertTransaction($jirColVal, $this->User->getId());
            } else {
                $jirDao->doApiUpdateTransaction($this->getIntParameter('jir_id'), $jirColVal, $this->User->getId());
            }
            DB::commit();
            $results = $jirDao->getLastInsertId();
        } catch (\Exception $e) {
            DB::rollBack();
            $results = '';
            $this->setErrorCode('500');
        }

        return $results;
    }

    /**
     * Function to get the goods load data.
     * @param array $data to store the data.
     * @return void
     */
    private function doInsertJirbyPacking(array $data): void
    {
        if(empty($data) === false) {
            DB::beginTransaction();
            try {
                $jirDao = new JobInboundReceiveDao();
                foreach ($data as $row) {
                    # Do Update quantity actual
                    $jirColVal = [
                        'jir_ji_id' => $this->getIntParameter('jir_ji_id'),
                        'jir_jog_id' => $this->getIntParameter('jir_jog_id'),
                        'jir_quantity' => $row['jid_quantity'],
                        'jir_qty_damage' => 0,
                        'jir_serial_number' => $row['jid_serial_number'],
                        'jir_lot_number' => $row['jid_lot_number'],
                        'jir_packing_number' => $row['jid_packing_number'],
                        'jir_expired_date' => $row['jid_expired_date'],
                        'jir_gdt_id' => $row['jid_gdt_id'],
                        'jir_gdt_remark' => $row['jid_gdt_remark'],
                        'jir_gcd_id' => $row['jid_gcd_id'],
                        'jir_gcd_remark' => $row['jid_gcd_remark'],
                        'jir_stored' => 'Y',
                        'jir_length' => $row['jid_length'],
                        'jir_width' => $row['jid_width'],
                        'jir_height' => $row['jid_height'],
                        'jir_volume' => $row['jid_volume'],
                        'jir_weight' => $row['jid_weight'],
                    ];
                    $jirDao->doApiInsertTransaction($jirColVal, $this->User->getId());
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->setErrorCode('500');
            }

        }
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doStartUnload(): array
    {
        DB::beginTransaction();
        try {
            # Update actual time arrival job
            $jiColVal = [
                'ji_start_load_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jiColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doUpdateTruckArrival(): array
    {
        DB::beginTransaction();
        try {
            $date = $this->getStringParameter('date').' '.$this->getStringParameter('time').':'.date('s');
            # Update start Job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => $date,
            ];
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), $joColVal, $this->User->getId());
            if ($this->isValidParameter('so_id') && $this->isValidParameter('so_start_on') === false) {
                $soDao = new SalesOrderDao();
                $soDao->doApiUpdateTransaction($this->getIntParameter('so_id'), [
                    'so_start_by' => $this->User->getId(),
                    'so_start_on' => $date,
                ], $this->User->getId());
            }
            if ($this->isValidParameter('jo_jtr_id')) {
                $jrtDao = new JobStockTransferDao();
                $jrtDao->doApiUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_start_in_on' => $date,
                ], $this->User->getId());
            }
            # Insert Relation Vendor
            $idVendor = $this->getIntParameter('ji_vendor_id', 0);
            if ($this->isValidParameter('ji_vendor_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $relNumber = $sn->loadNumber('Relation', $this->User->Relation->getOfficeId());
                $colVal = [
                    'rel_ss_id' => $this->User->getSsId(),
                    'rel_number' => $relNumber,
                    'rel_name' => $this->getStringParameter('ji_vendor'),
                    'rel_short_name' => $this->getStringParameter('rel_short_name'),
                    'rel_owner' => 'N',
                    'rel_active' => 'Y',
                ];
                $relDao = new RelationDao();
                $relDao->doApiInsertTransaction($colVal, $this->User->getId());
                $idVendor = $relDao->getLastInsertId();
            }

            # Update actual time arrival job inbound
            $jiColVal = [
                'ji_vendor_id' => $idVendor,
                'ji_driver' => $this->getStringParameter('ji_driver'),
                'ji_driver_phone' => $this->getStringParameter('ji_driver_phone'),
                'ji_truck_number' => $this->getStringParameter('ji_truck_number'),
                'ji_container_number' => $this->getStringParameter('ji_container_number'),
                'ji_seal_number' => $this->getStringParameter('ji_seal_number'),
                'ji_ata_time' => $this->getStringParameter('time'),
                'ji_ata_date' => $this->getStringParameter('date'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doApiUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal, $this->User->getId());

            # Update Job Action
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
            $jacDao = new JobActionDao();
            $jacDao->doApiUpdateTransaction($this->getIntParameter('jac_id'), $jacColVal, $this->User->getId());

            # Insert Job Action Event.
            $key = $this->getStringParameter('action') . $this->getIntParameter('jo_srt_id') . '.event';
            $event = Trans::getWord($key, 'action');
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('jac_id'),
                'jae_description' => $event,
                'jae_date' => $this->getStringParameter('date'),
                'jae_time' => $this->getStringParameter('time'),
                'jae_remark' => '',
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doApiInsertTransaction($jaeColVal, $this->User->getId());
            $jaeColVal['jae_id'] = $jaeDao->getLastInsertId();
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ], $this->User->getId());
            DB::commit();
            $keyAction = $this->getStringParameter('action') . $this->getIntParameter('jo_srt_id') . '.description';
            $action = Trans::getWord($keyAction, 'action');
            $time = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time');
            $jaeColVal['jae_time_on'] = DateTimeParser::format($time, 'Y-m-d H:i', 'H:i d M Y');
            $jaeColVal['jae_added_on'] = date('H:i d M Y');
            $jaeColVal['jae_action'] = $action;
            $jaeColVal['jae_added_by'] = $this->User->getName();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }


    /**
     * Function to load total number of draft project.
     *
     *
     * @return array
     */
    private function loadJobData(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_id = ' . $this->getIntParameter('jo_id') . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT ji.ji_id, ji.ji_jo_id, ji.ji_wh_id, wh.wh_name as ji_warehouse, ji.ji_eta_date, ji.ji_eta_time,
                        ji.ji_ata_date, ji.ji_ata_time, ji.ji_rel_id, shipper.rel_name as ji_shipper, ji.ji_of_id,
                        o.of_name as ji_shipper_address, ji.ji_cp_id, pic2.cp_name as ji_pic_shipper, ji.ji_vendor_id,
                        transporter.rel_name AS ji_vendor, transporter.rel_short_name as ji_vendor_alias, ji.ji_truck_number, ji.ji_container_number, ji.ji_seal_number,
                        jo.jo_id, jo.jo_ref_id, j.jo_number as jo_reference, jo.jo_number, jo.jo_srv_id, srv.srv_name as jo_service,
                        jo.jo_srt_id, srt.srt_name as jo_service_term, jo.jo_order_date, jo.jo_rel_id, rel.rel_name as jo_customer,
                        jo.jo_pic_id, pic.cp_name as jo_pic, jo.jo_invoice_of_id, oi.of_name as jo_invoice_of,
                        jo.jo_order_of_id, oo.of_name as jo_order_of, jo.jo_manager_id, manager.us_name as jo_manager,
                        jo.jo_publish_on, jo.jo_deleted_on, jo.jo_deleted_reason, us.us_name as jo_deleted_by, jo.jo_start_on, jo.jo_finish_on,
                        u1.us_name as created_by, u2.us_name as published_by, u3.us_name as finished_by,
                        jo.jo_created_on, ji.ji_start_load_on, ji.ji_end_load_on, ji.ji_start_store_on, ji.ji_end_store_on,
                        pic3.cp_name as ji_vendor_pic, jo.jo_document_on, ji.ji_driver, ji.ji_driver_phone,
                        (CASE WHEN (joo.joo_id IS NULL) THEN 'N' ELSE 'Y' END) as officer, ji.ji_so_id as jo_so_id, so.so_id, so.so_number,
                        (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as jo_customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as jo_aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as jo_bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as jo_packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) AS jo_sppb_ref,
                            joh.joh_id, joh.joh_jo_id, joh.joh_reason, joh.joh_created_on,
                            so.so_soh_id, so.so_start_on, srt.srt_route as jo_route, jtr.jtr_id as jo_jtr_id, '' as jo_jb_id
                FROM job_inbound as ji
                    INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                    INNER JOIN service as srv ON jo.jo_srv_id = srv.srv_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    INNER JOIN office as oo ON jo.jo_order_of_id = oo.of_id
                    INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                    LEFT OUTER JOIN office as oi ON oi.of_id = jo.jo_invoice_of_id
                    LEFT OUTER JOIN relation as shipper ON ji.ji_rel_id = shipper.rel_id
                    LEFT OUTER JOIN relation as transporter ON ji.ji_vendor_id = transporter.rel_id
                    LEFT OUTER JOIN users as manager ON jo.jo_manager_id = manager.us_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN office as o ON ji.ji_of_id = o.of_id
                    LEFT OUTER JOIN contact_person as pic2 ON ji.ji_cp_id = pic2.cp_id
                    LEFT OUTER JOIN contact_person as pic3 ON ji.ji_pic_vendor = pic3.cp_id
                    LEFT OUTER JOIN job_order as j ON j.jo_ref_id = j.jo_id
                    LEFT OUTER JOIN users as us ON jo.jo_deleted_by = us.us_id
                    LEFT OUTER JOIN users as u1 ON jo.jo_created_by = u1.us_id
                    LEFT OUTER JOIN users as u2 ON jo.jo_publish_by = u2.us_id
                    LEFT OUTER JOIN users as u3 ON jo.jo_finish_by = u3.us_id
                    LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id
                    LEFT OUTER JOIN (SELECT joo_id, joo_jo_id, joo_us_id
                                        FROM job_officer
                                        WHERE (joo_deleted_on IS NULL) AND (joo_us_id = " . $this->User->getId() . ')
                                        GROUP BY joo_id, joo_jo_id, joo_us_id) as joo ON jo.jo_id = joo.joo_jo_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                    LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_ji_jo_id' . $strWhere;
        $sqlResult = DB::select($query);
        $result = [];
        if (count($sqlResult) === 1) {
            $result = DataParser::objectToArrayAPI($sqlResult[0]);
            $eta = '';
            if (empty($result['ji_eta_date']) === false) {
                if (empty($result['ji_eta_time']) === false) {
                    $eta = DateTimeParser::format($result['ji_eta_date'] . ' ' . $result['ji_eta_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $eta = DateTimeParser::format($result['ji_eta_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['jo_contract_ref'] = $result['jo_aju_ref'];
            $result['ji_eta'] = $eta;
            $ata = '';
            if (empty($result['ji_ata_date']) === false) {
                if (empty($result['ji_ata_time']) === false) {
                    $ata = DateTimeParser::format($result['ji_ata_date'] . ' ' . $result['ji_ata_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $ata = DateTimeParser::format($result['ji_ata_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['ji_ata'] = $ata;
        }
        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @param int   $jiId   To store the where condition.
     * @param array $wheres To store the where condition.
     *
     * @return array
     */
    private function loadJobGoods($jiId, array $wheres = []): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_barcode as jog_gd_barcode, gd.gd_name as jog_gd_name, br.br_name as jog_gd_brand,
                            gdc.gdc_name as jog_gd_category, jog.jog_gdu_id, uom.uom_code as jog_uom, jog.jog_quantity,
                            gd.gd_sn as jog_gd_sn, gd.gd_tonnage as jog_gd_tonnage, gd.gd_cbm as jog_gd_cbm, gd.gd_multi_sn as jog_gd_multi_sn,
                            gd.gd_receive_sn as jog_gd_receive_sn, gd.gd_generate_sn as jog_gd_generate_sn,
                            gd.gd_packing as jog_gd_packing, gd.gd_expired as jog_gd_expired, gd.gd_min_tonnage as jog_gd_min_tonnage,
                            gd.gd_max_tonnage as jog_gd_max_tonnage,
                            gd.gd_min_cbm as jog_gd_min_cbm, gd.gd_max_cbm as jog_gd_max_cbm, gd.gd_tonnage_dm as jog_gd_tonnage_dm,
                             gd.gd_cbm_dm as jog_gd_cbm_dm, jir.jir_id, jir.jir_quantity, jir.jir_lot_number, jir.jir_packing_number,
                            jir.jir_serial_number, jir.jir_gdt_id, jir.jir_weight, jir.jir_volume, jir.jir_stored
                        FROM job_goods as jog
                            INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                            INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                            INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                            INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                            INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
                            LEFT OUTER JOIN (SELECT jir_id, jir_jog_id, jir_quantity, jir_lot_number, jir_packing_number,
                                                    jir_serial_number, jir_gdt_id, jir_weight, jir_volume, jir_stored
                                                FROM job_inbound_receive
                                                WHERE (jir_deleted_on IS NULL)
                                                AND (jir_ji_id = " . $jiId . ")) as jir ON jog.jog_id = jir.jir_jog_id " . $strWhere;
        $query .= ' ORDER BY gd.gd_sku, jog.jog_id, jir.jir_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobGoodsReceive($data);
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $wheres To store the where condition.
     * @param bool $apiFormat To store the switch for data parser
     * @return array
     */
    private function loadJobTransferGoods(array $wheres = [], bool $apiFormat = true): array
    {
        $wheres[]= '(jod.jod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "select jtr.jtr_id, jid.jid_id, jid.jid_lot_number, jid.jid_expired_date, jid.jid_packing_number, jid.jid_serial_number,
                            jid.jid_quantity, jid.jid_weight, jid.jid_length, jid.jid_width, jid.jid_height, jid.jid_volume,
                            jid.jid_gdt_id, gdt.gdt_code as jid_gdt_code, gdt.gdt_description as jid_gdt_description, jid.jid_gdt_remark,
                            jid.jid_gcd_id, gcd.gcd_code as jid_gcd_code, gcd.gcd_description as jid_gcd_description, jid.jid_gcd_remark
                    FROM job_stock_transfer as jtr
                        INNER JOIN job_order as jo ON jtr.jtr_job_jo_id = jo.jo_id
                        INNER JOIN job_outbound as job ON jo.jo_id = job.job_jo_id
                        INNER JOIN job_outbound_detail as jod ON job.job_id = jod.jod_job_id
                        INNER JOIN job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id
                        LEFT OUTER JOIN goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id
                        LEFT OUTER JOIN goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id " . $strWhere;
        $sqlResults = DB::select($query);
        if ($apiFormat) {
            return DataParser::arrayObjectToArrayAPI($sqlResults);

        }
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $data To store the data;
     *
     * @return array
     */
    private function doPrepareJobGoodsReceive(array $data): array
    {
        $lastResults = [];
        if (empty($data) === false) {
            $tempJogId = [];
            $tempPn = [];
            $number = new NumberFormatter($this->User);
            $results = [];
            foreach ($data as $row) {
                $qtyPacking = 0;
                if (empty($row['jir_packing_number']) === false && in_array($row['jir_packing_number'], $tempPn, true) === false) {
                    $qtyPacking = 1;
                    $tempPn[] = $row['jir_packing_number'];
                }
                $qty = (float)$row['jir_quantity'];
                $qtyGood = 0;
                $qtyDamage = 0;
                $qtyReceived = 0;
                $qtyReturn = 0;
                $weight = 0;
                $volume = 0;
                if ($row['jir_stored'] === 'Y') {
                    if (empty($row['jir_gdt_id']) === false) {
                        $qtyDamage = $qty;
                    } else {
                        $qtyGood = $qty;
                    }
                    $qtyReceived = $qty;
                    $weight = $qty * (float)$row['jir_weight'];
                    $volume = $qty * (float)$row['jir_volume'];
                } else {
                    $qtyPacking = 0;
                    $qtyReturn = $qty;
                }
                if (in_array($row['jog_id'], $tempJogId, true) === false) {
                    $jog = [
                        'jog_id' => $row['jog_id'],
                        'jog_gdu_id' => $row['jog_gdu_id'],
                        'jog_uom' => $row['jog_uom'],
                        'jog_quantity' => $row['jog_quantity'],
                        'jog_quantity_str' => $number->doFormatFloat($row['jog_quantity']),

                        'jog_gd_id' => $row['jog_gd_id'],
                        'jog_gd_sku' => $row['jog_gd_sku'],
                        'jog_gd_barcode' => $row['jog_gd_barcode'],
                        'jog_gd_name' => $row['jog_gd_name'],
                        'jog_gd_brand' => $row['jog_gd_brand'],
                        'jog_gd_category' => $row['jog_gd_category'],
                        'jog_gd_sn' => $row['jog_gd_sn'],
                        'jog_gd_generate_sn' => $row['jog_gd_generate_sn'],
                        'jog_gd_receive_sn' => $row['jog_gd_receive_sn'],
                        'jog_gd_multi_sn' => $row['jog_gd_multi_sn'],
                        'jog_gd_packing' => $row['jog_gd_packing'],
                        'jog_gd_expired' => $row['jog_gd_expired'],
                        'jog_gd_tonnage' => $row['jog_gd_tonnage'],
                        'jog_gd_tonnage_dm' => $row['jog_gd_tonnage_dm'],
                        'jog_gd_min_tonnage' => $row['jog_gd_min_tonnage'],
                        'jog_gd_max_tonnage' => $row['jog_gd_max_tonnage'],
                        'jog_gd_cbm' => $row['jog_gd_cbm'],
                        'jog_gd_cbm_dm' => $row['jog_gd_cbm_dm'],
                        'jog_gd_min_cbm' => $row['jog_gd_min_cbm'],
                        'jog_gd_max_cbm' => $row['jog_gd_max_cbm'],

                        'qty_received' => $qtyReceived,
                        'qty_good' => $qtyGood,
                        'qty_damage' => $qtyDamage,
                        'qty_packing' => $qtyPacking,
                        'total_tonnage' => $weight,
                        'total_cbm' => $volume,
                        'qty_returned' => $qtyReturn,
                    ];
                    $results[] = $jog;
                    $tempJogId[] = $row['jog_id'];
                } else {
                    $index = array_search($row['jog_id'], $tempJogId, true);
                    $results[$index]['qty_received'] += $qtyReceived;
                    $results[$index]['qty_good'] += $qtyGood;
                    $results[$index]['qty_damage'] += $qtyDamage;
                    $results[$index]['qty_packing'] += $qtyPacking;
                    $results[$index]['total_tonnage'] += $weight;
                    $results[$index]['total_cbm'] += $volume;
                    $results[$index]['qty_returned'] += $qtyReturn;
                }
            }
            # format string quantity
            foreach ($results as $row) {
                $row['qty_received'] = $number->doFormatFloat($row['qty_received']);
                $row['qty_good'] = $number->doFormatFloat($row['qty_good']);
                $row['qty_damage'] = $number->doFormatFloat($row['qty_damage']);
                $row['qty_packing'] = $number->doFormatFloat($row['qty_packing']);
                $row['total_tonnage'] = $number->doFormatFloat($row['total_tonnage']);
                $row['total_cbm'] = $number->doFormatFloat($row['total_cbm']);
                $row['qty_returned'] = $number->doFormatFloat($row['qty_returned']);
                $lastResults[] = $row;
            }
        }
        return $lastResults;
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function loadListJidIdForDelete(): array
    {
        $wheres = [];
        $wheres[] = '(jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
        $wheres[] = '(jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
        $wheres[] = '(jid_ji_id = ' . $this->getIntParameter('jid_ji_id') . ')';
        if ($this->isValidParameter('jid_lot_number')) {
            $wheres[] = "(jid_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
        } else {
            $wheres[] = '(jid_lot_number IS NULL)';
        }
        if ($this->isValidParameter('jid_expired_date')) {
            $wheres[] = "(jid_expired_date = '" . $this->getStringParameter('jid_expired_date') . "')";
        } else {
            $wheres[] = '(jid_expired_date IS NULL)';
        }
        if ($this->isValidParameter('jid_packing_number')) {
            $wheres[] = "(jid_packing_number = '" . $this->getStringParameter('jid_packing_number') . "')";
        } else {
            $wheres[] = '(jid_packing_number IS NULL)';
        }
        if ($this->isValidParameter('jid_gdt_id')) {
            $wheres[] = '(jid_gdt_id = ' . $this->getIntParameter('jid_gdt_id') . ')';
        } else {
            $wheres[] = '(jid_gdt_id IS NULL)';
        }
        if ($this->isValidParameter('jid_gcd_id')) {
            $wheres[] = '(jid_gcd_id = ' . $this->getIntParameter('jid_gcd_id') . ')';
        } else {
            $wheres[] = '(jid_gcd_id IS NULL)';
        }
        $wheres[] = '(jid_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jid_id
                        FROM job_inbound_detail " . $strWhere;
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $results[] = $row['jid_id'];
        }
        return $results;
    }

}
