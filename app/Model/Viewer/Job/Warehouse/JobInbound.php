<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundReceiveDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Helper\Job\Warehouse\InboundReceiveSn;
use App\Model\Viewer\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInbound extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhInbound', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doArriveTruck') {
            # Update start Job
            $this->doStartJobOrder();
            # Update actual time arrival job
            $jiColVal = [
                'ji_vendor_id' => $this->getIntParameter('ji_vendor_id'),
                'ji_driver' => $this->getStringParameter('ji_driver'),
                'ji_driver_phone' => $this->getStringParameter('ji_driver_phone'),
                'ji_truck_number' => $this->getStringParameter('ji_truck_number'),
                'ji_container_number' => $this->getStringParameter('ji_container_number'),
                'ji_seal_number' => $this->getStringParameter('ji_seal_number'),
                'ji_ata_time' => date('H:i'),
                'ji_ata_date' => date('Y-m-d'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal);
            if ($this->isValidParameter('jo_jtr_id') === true) {
                $jtrDao = new JobStockTransferDao();
                $jtrDao->doUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_start_in_on' => date('Y-m-d H:i:s'),
                ]);
            }
            # Update job Action
            $this->doUpdateJobAction();
            # Do notification
            $this->doGenerateNotificationReceiver('jobtruckarrive');
        } else if ($this->getFormAction() === 'doActionStartUnload') {
            $jiColVal = [
                'ji_start_load_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('inboundstartunload');
        } else if ($this->getFormAction() === 'doReceiveGoods') {
            $volume = null;
            if (($this->isValidParameter('jir_length') === true) && ($this->isValidParameter('jir_height') === true) && ($this->isValidParameter('jir_width') === true)) {
                $volume = $this->getFloatParameter('jir_length') * $this->getFloatParameter('jir_height') * $this->getFloatParameter('jir_width');
            }
            # Do Update quantity actual
            $jirColVal = [
                'jir_ji_id' => $this->getIntParameter('ji_id'),
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
                $jirDao->doInsertTransaction($jirColVal);
            } else {
                $jirDao->doUpdateTransaction($this->getIntParameter('jir_id'), $jirColVal);
            }
        } else if ($this->getFormAction() === 'doDeleteReceiveGoods') {
            $jirDao = new JobInboundReceiveDao();
            $jirDao->doDeleteTransaction($this->getIntParameter('jir_id_del'));
        } else if ($this->getFormAction() === 'doActionEndUnload') {
            $jiColVal = [
                'ji_end_load_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('inboundcompleteunload');
        } else if ($this->getFormAction() === 'doActionDocument') {
            # Update job Action
            $this->doUpdateJobAction();
        } else if ($this->getFormAction() === 'doActionStartPutAway') {
            $jiColVal = [
                'ji_start_store_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('inboundstartputaway');
        } else if ($this->getFormAction() === 'doUpdateStorage') {
            $volume = null;
            if (($this->isValidParameter('jid_length') === true) && ($this->isValidParameter('jid_height') === true) && ($this->isValidParameter('jid_width') === true)) {
                $volume = $this->getFloatParameter('jid_length') * $this->getFloatParameter('jid_height') * $this->getFloatParameter('jid_width');
            }
            $sn = '';
            if ($this->getStringParameter('jid_gd_sn', 'N') === 'Y') {
                $serials = explode(',', $this->getStringParameter('jid_serial_number'));
                $inputs = [];
                foreach ($serials as $data) {
                    $serial = trim($data);
                    if (empty($serial) === false) {
                        $inputs[] = $serial;
                    }
                }
                $sn = implode(', ', $inputs);
            }

            $jidColVal = [
                'jid_ji_id' => $this->getIntParameter('ji_id'),
                'jid_jir_id' => $this->getIntParameter('jid_jir_id'),
                'jid_whs_id' => $this->getIntParameter('jid_whs_id'),
                'jid_quantity' => $this->getFloatParameter('jid_quantity'),
                'jid_gd_id' => $this->getIntParameter('jid_gd_id'),
                'jid_gdu_id' => $this->getIntParameter('jid_gdu_id'),
                'jid_adjustment' => 'N',
                'jid_lot_number' => $this->getStringParameter('jid_lot_number'),
                'jid_serial_number' => $sn,
                'jid_packing_number' => $this->getStringParameter('jid_packing_number'),
                'jid_gdt_id' => $this->getIntParameter('jid_gdt_id'),
                'jid_gdt_remark' => $this->getStringParameter('jid_gdt_remark'),
                'jid_gcd_id' => $this->getIntParameter('jid_gcd_id'),
                'jid_gcd_remark' => $this->getStringParameter('jid_gcd_remark'),
                'jid_length' => $this->getFloatParameter('jid_length'),
                'jid_width' => $this->getFloatParameter('jid_width'),
                'jid_height' => $this->getFloatParameter('jid_height'),
                'jid_volume' => $volume,
                'jid_weight' => $this->getFloatParameter('jid_weight'),
            ];
            $jidDao = new JobInboundDetailDao();
            if ($this->isValidParameter('jid_id') === true) {
                $jidDao->doUpdateTransaction($this->getIntParameter('jid_id'), $jidColVal);
            } else {
                $jidDao->doInsertTransaction($jidColVal);
            }
        } else if ($this->getFormAction() === 'doDeleteStorage') {
            $jidDao = new JobInboundDetailDao();
            $jidDao->doDeleteTransaction($this->getIntParameter('jid_id_del'));
        } else if ($this->getFormAction() === 'doActionEndPutAway') {
            $wheres = [];
            $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('ji_id') . ')';
            $wheres[] = '(jid.jid_id NOT IN (SELECT jis_jid_id FROM job_inbound_stock WHERE jis_deleted_on IS NULL))';
            $wheres[] = '(jid.jid_deleted_on IS NULL)';

            $storage = JobInboundDetailDao::loadData($wheres);
            $jisDao = new JobInboundStockDao();
            foreach ($storage as $row) {
                $jisColVal = [
                    'jis_jid_id' => $row['jid_id'],
                    'jis_quantity' => $row['jid_quantity'],
                ];
                $jisDao->doInsertTransaction($jisColVal);
            }
            $jowColVal = [
                'ji_end_store_on' => date('Y-m-d H:i:s'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jowColVal);
            if ($this->isValidParameter('jo_jtr_id') === true) {
                $jtrDao = new JobStockTransferDao();
                $jtrDao->doUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_end_in_on' => date('Y-m-d H:i:s'),
                ]);
            }
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('inboundcompleteputaway');
        } else if ($this->getFormAction() === 'doUpdateGoodsInbound') {
            $jogColVal = [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_gd_id' => $this->getIntParameter('jog_gd_id'),
                'jog_name' => $this->getStringParameter('jog_goods'),
                'jog_quantity' => $this->getFloatParameter('jog_quantity'),
                'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
                'jog_production_number' => $this->getStringParameter('jog_production_number'),
                'jog_production_date' => $this->getStringParameter('jog_production_date'),
                'jog_available_date' => $this->getStringParameter('jo_order_date'),
                'jog_length' => $this->getFloatParameter('jog_length'),
                'jog_width' => $this->getFloatParameter('jog_width'),
                'jog_height' => $this->getFloatParameter('jog_height'),
                'jog_volume' => $this->getFloatParameter('jog_volume'),
                'jog_weight' => $this->getFloatParameter('jog_weight'),
            ];
            $jogDao = new JobGoodsDao();
            if ($this->isValidParameter('jog_id') === true) {
                $jogDao->doUpdateTransaction($this->getIntParameter('jog_id'), $jogColVal);
            } else {
                $sn = new SerialNumber($this->User->getSsId());
                $snGoods = $sn->loadNumber('JobOrderGoods', $this->getIntParameter('jo_order_of_id'), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
                $jogColVal['jog_serial_number'] = $snGoods;
                $jogDao->doInsertTransaction($jogColVal);
            }
        } else if ($this->getFormAction() === 'doDeleteGoodsInbound') {
            $jogDao = new JobGoodsDao();
            $jogDao->doDeleteTransaction($this->getIntParameter('jog_id_del'));
        } else if ($this->getFormAction() === 'doUpdateJobGoodsDamage') {
            $volume = null;
            if (($this->isValidParameter('jog_dm_length') === true) && ($this->isValidParameter('jog_dm_width') === true) && ($this->isValidParameter('jog_dm_height') === true)) {
                $volume = $this->getFloatParameter('jog_dm_length') * $this->getFloatParameter('jog_dm_width') * $this->getFloatParameter('jog_dm_height');
            }

            $jogDmColVal = [
                'jog_length' => $this->getFloatParameter('jog_dm_length'),
                'jog_width' => $this->getFloatParameter('jog_dm_width'),
                'jog_height' => $this->getFloatParameter('jog_dm_height'),
                'jog_net_weight' => $this->getFloatParameter('jog_dm_net_weight'),
                'jog_volume' => $volume,
            ];
            $jogDao = new JobGoodsDao();
            $jogDao->doUpdateTransaction($this->getIntParameter('jog_dm_id'), $jogDmColVal);
        } else {
            parent::doUpdate();
        }
    }


    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobInboundDao::getByJobOrderAndSystemSetting($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJiHiddenData();
        # form for unloading goods
        if ($this->isUnloadProcess() === true) {
            $modalReceive = $this->getGoodsReceiveModal();
            $this->View->addModal($modalReceive);
            $this->Tab->addPortlet('goodsReceived', $this->getFormGoodsReceivePortlet($modalReceive->getModalId()));
            $this->Tab->addPortlet('goodsReceived', $this->getGoodsReceivedPortlet($modalReceive));
            $this->Tab->setActiveTab('goodsReceived', true);
        }

        # general form
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getReferenceFieldSet());
        # Goods tab
        # form for goods field.
        $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
        if ($this->isValidParameter('ji_end_load_on') === true) {
            $this->Tab->addPortlet('goods', $this->getGoodsReceivedPortlet());
            $this->Tab->addPortlet('goods', $this->getGoodsReturnedPortlet());
        }

        if ($this->isValidParameter('ji_start_store_on') === true) {
            $this->Tab->addPortlet('storage', $this->getStorageFieldSet());
            if ($this->isValidParameter('ji_end_store_on') === false) {
                $this->Tab->setActiveTab('storage', true);
            }
        }
        # include default portlet
        $this->includeAllDefaultPortlet();
    }


    /**
     * function to check is unload started
     *
     * @return bool
     */
    protected function isUnloadProcess(): bool
    {
        return ($this->isValidParameter('ji_start_load_on') === true && $this->isValidParameter('ji_end_load_on') === false);
    }


    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doArriveTruck') {
            $this->Validation->checkRequire('ji_vendor_id');
            $this->Validation->checkRequire('ji_driver', 1, 255);
            $this->Validation->checkRequire('ji_truck_number', 4, 255);
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doActionStartUnload') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doDeleteReceiveGoods') {
            $this->Validation->checkRequire('jir_id_del');
        } else if ($this->getFormAction() === 'doReceiveGoods') {
            $this->Validation->checkRequire('jir_jog_id');
            $this->Validation->checkRequire('ji_id');
            $this->Validation->checkRequire('jir_quantity');
            $this->Validation->checkRequire('jir_condition');
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
            }
            if ($this->getStringParameter('jir_gd_expired', 'N') === 'Y') {
                $this->Validation->checkRequire('jir_expired_date');
            }
            if (($this->getStringParameter('jir_gd_tonnage', 'N') === 'Y') ||
                (($this->getStringParameter('jir_gd_tonnage_dm', 'N') === 'Y') && ($this->getStringParameter('jir_condition', 'N') === 'N'))) {
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
                $jir->JiId = $this->getIntParameter('ji_id', 0);
                $jir->WhId = $this->getIntParameter('ji_wh_id', 0);
                $jir->GdId = $this->getIntParameter('jir_gd_id', 0);
                $this->Validation->checkInboundReceiveSn('jir_serial_number', $jir);
            }
            if ($this->getStringParameter('jir_condition', 'Y') === 'N') {
                $this->Validation->checkRequire('jir_stored');
                $this->Validation->checkRequire('jir_gdt_id');
                $this->Validation->checkRequire('jir_gcd_id');
            }
            if ($this->isValidParameter('jir_expired_date')) {
                $this->Validation->checkDate('jir_expired_date');
            }
        } else if ($this->getFormAction() === 'doUpdateJobGoodsDamage') {
            $this->Validation->checkRequire('jog_dm_id');
            $this->Validation->checkRequire('jir_dm_id');
            if ($this->isValidParameter('jog_dm_length') === true) {
                $this->Validation->checkFloat('jog_dm_length', 0);
            }
            if ($this->isValidParameter('jog_dm_width') === true) {
                $this->Validation->checkFloat('jog_dm_width', 0);
            }
            if ($this->isValidParameter('jog_dm_height') === true) {
                $this->Validation->checkFloat('jog_dm_height', 0);
            }
            if ($this->isValidParameter('jog_dm_net_weight') === true) {
                $this->Validation->checkFloat('jog_dm_net_weight', 0);
            }
        } else if ($this->getFormAction() === 'doActionEndUnload') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doActionDocument') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doActionStartPutAway') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doUpdateStorage') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('ji_id');
            $this->Validation->checkRequire('jid_jir_id');
            $this->Validation->checkRequire('jid_gd_id');
            $this->Validation->checkRequire('jid_gdu_id');
            $this->Validation->checkRequire('jid_quantity');
            $this->Validation->checkRequire('jid_jir_quantity');
            if ($this->getStringParameter('jid_gd_sn', 'N') === 'Y') {
                $this->Validation->checkRequire('jid_serial_number');
                $this->Validation->checkFloat('jid_quantity', 1, 1);
                $this->Validation->checkSnReceiveAndStoringInbound('jid_serial_number', $this->getIntParameter('jid_jir_id'));
                if (($this->isValidParameter('ji_id') === true) && ($this->isValidParameter('jid_serial_number') === true) && ($this->isValidParameter('jid_gd_id') === true)) {
                    $this->Validation->checkInboundSerialNumber('jid_serial_number', $this->getIntParameter('ji_id'), $this->getIntParameter('jid_id', 0), $this->getIntParameter('jid_gd_id'));
                }
            } else {
                $this->Validation->checkFloat('jid_quantity', 1, $this->getFloatParameter('jid_jir_quantity', 0.0));
            }
        } else if ($this->getFormAction() === 'doDeleteStorage') {
            $this->Validation->checkRequire('jid_id_del');
        } else if ($this->getFormAction() === 'doActionEndPutAway') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doUpdateGoodsInbound') {
            $this->Validation->checkRequire('jog_gd_id');
            $this->Validation->checkRequire('jog_quantity');
            $this->Validation->checkRequire('jog_gdu_id');
            $this->Validation->checkFloat('jog_quantity', 0);
            $this->Validation->checkMaxLength('jog_production_number', 255);
            if ($this->isValidParameter('jog_production_date') === true) {
                $this->Validation->checkDate('jog_production_date');
            }
            $this->Validation->checkUnique('jog_gd_id', 'job_goods', [
                'jog_id' => $this->getIntParameter('jog_id'),
            ], [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_production_number' => $this->getStringParameter('jog_production_number'),
                'jog_uom_id' => $this->getIntParameter('jog_uom_id'),
                'jog_deleted_on' => null,
            ]);
        } else if ($this->getFormAction() === 'doDeleteGoodsInbound') {
            $this->Validation->checkRequire('jog_id_del');
        }
        parent::loadValidationRole();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getWarehouseFieldSet(): Portlet
    {
        $etaTime = '';
        if ($this->isValidParameter('ji_eta_date') === true) {
            if ($this->isValidParameter('ji_eta_time') === true) {
                $etaTime = DateTimeParser::format($this->getStringParameter('ji_eta_date') . ' ' . $this->getStringParameter('ji_eta_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etaTime = DateTimeParser::format($this->getStringParameter('ji_eta_date'), 'Y-m-d', 'd M Y');
            }
        }
        $ataTime = '';
        if ($this->isValidParameter('ji_ata_date') === true) {
            if ($this->isValidParameter('ji_ata_time') === true) {
                $ataTime = DateTimeParser::format($this->getStringParameter('ji_ata_date') . ' ' . $this->getStringParameter('ji_ata_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $ataTime = DateTimeParser::format($this->getStringParameter('ji_ata_date'), 'Y-m-d', 'd M Y');
            }
        }
        $driver = $this->getStringParameter('ji_driver');
        if ($this->isValidParameter('ji_driver_phone') === true) {
            $driver .= ' / ' . $this->getStringParameter('ji_driver_phone');
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('ji_warehouse'),
            ],
            [
                'label' => Trans::getWord('eta'),
                'value' => $etaTime,
            ],
            [
                'label' => Trans::getWord('ata'),
                'value' => $ataTime,
            ],
            [
                'label' => Trans::getWord('shipper'),
                'value' => $this->getStringParameter('ji_shipper'),
            ],
            [
                'label' => Trans::getWord('picShipper'),
                'value' => $this->getStringParameter('ji_pic_shipper'),
            ],
            [
                'label' => Trans::getWord('shipperAddress'),
                'value' => $this->getStringParameter('ji_shipper_address'),
            ],
            [
                'label' => Trans::getWord('transporter'),
                'value' => $this->getStringParameter('ji_vendor'),
            ],
            [
                'label' => Trans::getWord('driver'),
                'value' => $driver,
            ],
            [
                'label' => Trans::getWord('truckPlate'),
                'value' => $this->getStringParameter('ji_truck_number'),
            ],
            [
                'label' => Trans::getWord('containerNumber'),
                'value' => $this->getStringParameter('ji_container_number'),
            ],
            [
                'label' => Trans::getWord('sealNumber'),
                'value' => $this->getStringParameter('ji_seal_number'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JowGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('qtyPlanning'),
            'jog_unit' => Trans::getWord('uom'),
            'jog_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'jog_total_volume' => Trans::getWord('totalCbm'),
        ]);
        $table->addRows($this->Goods);
        if ($this->isValidParameter('ji_end_load_on') === true) {
            $table->addColumnAfter('jog_quantity', 'jog_qty_received', Trans::getWord('qtyReceived'));
            $table->setColumnType('jog_qty_received', 'float');
            $table->setFooterType('jog_qty_received', 'SUM');
            $table->addColumnAfter('jog_qty_received', 'jog_qty_good', Trans::getWord('qtyGood'));
            $table->setColumnType('jog_qty_good', 'float');
            $table->setFooterType('jog_qty_good', 'SUM');
            $table->addColumnAfter('jog_qty_good', 'jog_qty_damage', Trans::getWord('qtyDamage'));
            $table->setColumnType('jog_qty_damage', 'float');
            $table->setFooterType('jog_qty_damage', 'SUM');
            $table->addColumnAtTheEnd('jog_remarks', Trans::getWord('notes'));
        }
        $table->setColumnType('jog_quantity', 'float');
        $table->setColumnType('jog_total_volume', 'float');
        $table->setColumnType('jog_total_weight', 'float');
        $table->setFooterType('jog_quantity', 'SUM');
        $table->setFooterType('jog_total_volume', 'SUM');
        $table->setFooterType('jog_total_weight', 'SUM');
        $table->addColumnAttribute('jog_sku', 'style', 'text-align: center;');
        $table->addColumnAttribute('jog_unit', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('goods'));
        if ($this->isValidParameter('ji_start_load_on') === false && $this->isAllowUpdateAction()) {
            $modal = $this->getGoodsModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getGoodsDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'jobGoods', 'getInboundGoodsById', ['jog_id']);
            $table->setDeleteActionByModal($modalDelete, 'jobGoods', 'getInboundGoodsByIdForDelete', ['jog_id']);
            # add new button
            $btnCpMdl = new ModalButton('btnJoJogMdl', Trans::getWord('addGoods'), $modal->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getGoodsModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoGdMdl', Trans::getWord('goods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateGoodsInbound');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateGoodsInbound' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getGoodsModalField($showModal));

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @param bool $showModal to trigger value for modal action.
     *
     * @return FieldSet
     */
    protected function getGoodsModalField(bool $showModal): FieldSet
    {

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'jog_gdc_name', $this->getStringParameter('jog_gdc_name'));
        $goodsCategoryField->setHiddenField('jog_gdc_id', $this->getIntParameter('jog_gdc_id'));
        $goodsCategoryField->setDetailReferenceCode('gdc_id');
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsCategoryField->addClearField('jog_goods');
        $goodsCategoryField->addClearField('jog_gd_id');

        $brandField = $this->Field->getSingleSelect('brand', 'jog_br_name', $this->getStringParameter('jog_br_name'));
        $brandField->setHiddenField('jog_br_id', $this->getIntParameter('jog_br_id'));
        $brandField->addParameter('br_ss_id', $this->User->getSsId());
        $brandField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $brandField->setDetailReferenceCode('br_id');
        $brandField->addClearField('jog_goods');
        $brandField->addClearField('jog_gd_id');

        # Create Unit Field
        $goodsField = $this->Field->getSingleSelect('goods', 'jog_goods', $this->getParameterForModal('jog_goods', $showModal));
        $goodsField->setHiddenField('jog_gd_id', $this->getParameterForModal('jog_gd_id', $showModal));
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsField->addOptionalParameterById('gd_gdc_id', 'jog_gdc_id');
        $goodsField->addOptionalParameterById('gd_br_id', 'jog_br_id');
        $goodsField->addClearField('jog_unit');
        $goodsField->addClearField('jog_uom_id');
        $goodsField->setDetailReferenceCode('gd_id');

        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'jog_unit', $this->getParameterForModal('jog_unit', $showModal));
        $unitField->setHiddenField('jog_gdu_id', $this->getParameterForModal('jog_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('category'), $goodsCategoryField);
        $fieldSet->addField(Trans::getWord('brand'), $brandField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jog_quantity', $this->getParameterForModal('jog_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('productionNumber'), $this->Field->getText('jog_production_number', $this->getParameterForModal('jog_production_number', $showModal)));
        $fieldSet->addField(Trans::getWord('productionDate'), $this->Field->getCalendar('jog_production_date', $this->getParameterForModal('jog_production_date', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_id', $this->getParameterForModal('jog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_length', $this->getParameterForModal('jog_length', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_width', $this->getParameterForModal('jog_width', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_height', $this->getParameterForModal('jog_height', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_weight', $this->getParameterForModal('jog_weight', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_volume', $this->getParameterForModal('jog_volume', $showModal)));

        return $fieldSet;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    protected function getGoodsDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoGdDelMdl', Trans::getWord('deleteGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteGoodsInbound');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteGoodsInbound' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jog_name_del', $this->getParameterForModal('jog_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('sku'), $this->Field->getText('jog_sku_del', $this->getParameterForModal('jog_sku_del', $showModal)));
        $fieldSet->addField(Trans::getWord('brand'), $this->Field->getText('jog_br_name_del', $this->getParameterForModal('jog_br_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('category'), $this->Field->getText('jog_gdc_name_del', $this->getParameterForModal('jog_gdc_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('productionNumber'), $this->Field->getText('jog_production_number_del', $this->getParameterForModal('jog_production_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('productionDate'), $this->Field->getText('jog_production_date_del', $this->getParameterForModal('jog_production_date_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jog_quantity_del', $this->getParameterForModal('jog_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jog_unit_del', $this->getParameterForModal('jog_unit_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_id_del', $this->getParameterForModal('jog_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        if ($this->isValidParameter('ji_end_load_on') === true) {
            $wheres[] = '(jir.jir_deleted_on IS NULL)';
            $wheres[] = "(jir.jir_stored = 'Y')";
        }
        $temp = JobGoodsDao::loadDataForInbound($wheres);
        $this->Goods = JobGoodsDao::doPrepareDataForInbound($temp);
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        $table = new Table('JoJidTbl');
        $table->setHeaderRow([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_gd_sku' => Trans::getWord('sku'),
            'jid_goods' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_packing_number' => Trans::getWord('packingNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_quantity' => Trans::getWord('quantity'),
            'jid_uom' => Trans::getWord('uom'),
            'jid_total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'jid_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'jid_condition' => Trans::getWord('condition'),
            'jid_remarks' => Trans::getWord('notes'),
        ]);
        $wheres = [];
        $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('ji_id') . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = "(jid.jid_adjustment = 'N')";

        $data = JobInboundDetailDao::loadData($wheres);
        $rows = JobInboundDetailDao::doPrepareInboundDetailData($data);
        $table->addRows($rows);
        $table->addColumnAttribute('jid_whs_name', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_gd_sku', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_serial_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_uom', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_condition', 'style', 'text-align: center');
        $table->setColumnType('jid_quantity', 'float');
        $table->setColumnType('jid_total_volume', 'float');
        $table->setColumnType('jid_total_weight', 'float');
        $table->setFooterType('jid_quantity', 'SUM');
        $table->setFooterType('jid_total_volume', 'SUM');
        $table->setFooterType('jid_total_weight', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoJidPtl', Trans::getWord('storage'));
        if ($this->isValidParameter('ji_end_store_on') === false && $this->isAllowUpdateAction()) {
            $modal = $this->getStorageModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getStorageDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'jobInboundDetail', 'getByReference', ['jid_id']);
            $table->setDeleteActionByModal($modalDelete, 'jobInboundDetail', 'getByReferenceForDelete', ['jid_id']);
            $btnCpMdl = new ModalButton('btnJoJidMdl', Trans::getWord('addStorage'), $modal->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }

        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get storage modal.
     *
     * @param string $modalId To store the modal id.
     * @param bool $showModal To set trigger on load mode.
     *
     * @return FieldSet
     */
    protected function getStorageModalFieldSet(string $modalId, bool $showModal): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Goods Field
        $jirField = $this->Field->getSingleSelectTable('jobInboundReceive', 'jid_gd_name', $this->getParameterForModal('jid_gd_name', $showModal), 'loadPutAwayData');
        $jirField->setHiddenField('jid_jir_id', $this->getParameterForModal('jid_jir_id', $showModal));
        $jirField->setTableColumns([
            'jir_jog_number' => Trans::getWord('goodsId'),
            'jir_gd_sku' => Trans::getWord('sku'),
            'jir_goods' => Trans::getWord('goods'),
            'jir_quantity_number' => Trans::getWord('qtyReceived'),
            'jir_jog_uom' => Trans::getWord('uom'),
            'jir_lot_number' => Trans::getWord('lotNumber'),
            'jir_serial_number' => Trans::getWord('serialNumber'),
            'jir_gdt_description' => Trans::getWord('damageType'),
        ]);
        $jirField->setAutoCompleteFields([
            'jid_gd_id' => 'jir_gd_id',
            'jid_gd_sku' => 'jir_gd_sku',
            'jid_jog_number' => 'jir_jog_number',
            'jid_gdt_description' => 'jir_gdt_description',
            'jid_gcd_description' => 'jir_gcd_description',
            'jid_jir_quantity' => 'jir_quantity',
            'jid_jir_quantity_number' => 'jir_quantity_number',
            'jid_uom' => 'jir_jog_uom',
            'jid_gdt_id' => 'jir_gdt_id',
            'jid_gdt_remark' => 'jir_gdt_remark',
            'jid_gcd_id' => 'jir_gcd_id',
            'jid_gcd_remark' => 'jir_gcd_remark',
            'jid_gdu_id' => 'jir_jog_gdu_id',
            'jid_length' => 'jir_length',
            'jid_width' => 'jir_width',
            'jid_height' => 'jir_height',
            'jid_weight' => 'jir_weight',
            'jid_gd_sn' => 'jir_gd_sn',
            'jid_lot_number' => 'jir_lot_number',
            'jid_serial_number' => 'jir_serial_number',
            'jid_packing_number' => 'jir_packing_number',
            'jid_expired_date' => 'jir_expired_date',
        ]);
        $jirField->setValueCode('jir_id');
        $jirField->setLabelCode('jir_goods');
        $jirField->addParameter('jir_ji_id', $this->getIntParameter('ji_id'));
        $jirField->addOptionalParameterById('jid_id', 'jid_id');
        $jirField->setParentModal($modalId);
        $this->View->addModal($jirField->getModal());


        # Create Unit Field
        $whsField = $this->Field->getSingleSelect('warehouseStorage', 'jid_whs_name', $this->getParameterForModal('jid_whs_name', $showModal));
        $whsField->setHiddenField('jid_whs_id', $this->getParameterForModal('jid_whs_id', $showModal));
        $whsField->addParameter('whs_wh_id', $this->getIntParameter('ji_wh_id'));
        $whsField->setEnableNewButton(false);
        $whsField->setEnableDetailButton(false);

        $skuField = $this->Field->getText('jid_gd_sku', $this->getParameterForModal('jid_gd_sku', $showModal));
        $skuField->setReadOnly();
        $jogNumberField = $this->Field->getText('jid_jog_number', $this->getParameterForModal('jid_jog_number', $showModal));
        $jogNumberField->setReadOnly();
        $productionNumberField = $this->Field->getText('jid_jog_production_number', $this->getParameterForModal('jid_jog_production_number', $showModal));
        $productionNumberField->setReadOnly();
        $gdtField = $this->Field->getText('jid_gdt_description', $this->getParameterForModal('jid_gdt_description', $showModal));
        $gdtField->setReadOnly();
        $gcdField = $this->Field->getText('jid_gcd_description', $this->getParameterForModal('jid_cd_description', $showModal));
        $gcdField->setReadOnly();
        $jirQtyField = $this->Field->getNumber('jid_jir_quantity', $this->getParameterForModal('jid_jir_quantity', $showModal));
        $jirQtyField->setReadOnly();
        $uomField = $this->Field->getText('jid_uom', $this->getParameterForModal('jid_uom', $showModal));
        $uomField->setReadOnly();
        $requiredSnField = $this->Field->getText('jid_gd_sn', $this->getParameterForModal('jid_gd_sn', $showModal));
        $requiredSnField->setReadOnly();
        $lotField = $this->Field->getText('jid_lot_number', $this->getParameterForModal('jid_lot_number', $showModal));
        $lotField->setReadOnly();
        $packingField = $this->Field->getText('jid_packing_number', $this->getParameterForModal('jid_packing_number', $showModal));
        $packingField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $jirField, true);
        $fieldSet->addField(Trans::getWord('storage'), $whsField, true);
        $fieldSet->addField(Trans::getWord('qtyReceived'), $jirQtyField);
        $fieldSet->addField(Trans::getWord('qtyStore'), $this->Field->getNumber('jid_quantity', $this->getParameterForModal('jid_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jid_serial_number', $this->getParameterForModal('jid_serial_number', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $lotField);
        $fieldSet->addField(Trans::getWord('packingNumber'), $packingField);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumberField);
        $fieldSet->addField(Trans::getWord('damageType'), $gdtField);
        $fieldSet->addField(Trans::getWord('requiredUniqueSn'), $requiredSnField);
        $fieldSet->addHiddenField($this->Field->getHidden('jid_id', $this->getParameterForModal('jid_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gd_id', $this->getParameterForModal('jid_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gdt_id', $this->getParameterForModal('jid_gdt_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gdt_remark', $this->getParameterForModal('jid_gdt_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gcd_id', $this->getParameterForModal('jid_gcd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gcd_remark', $this->getParameterForModal('jid_gcd_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gdu_id', $this->getParameterForModal('jid_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_length', $this->getParameterForModal('jid_length', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_width', $this->getParameterForModal('jid_width', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_height', $this->getParameterForModal('jid_height', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_weight', $this->getParameterForModal('jid_weight', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_expired_date', $this->getParameterForModal('jid_expired_date', $showModal)));

        return $fieldSet;
    }

    /**
     * Function to get storage modal.
     *
     * @return Modal
     */
    protected function getStorageModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JiJidMdl', Trans::getWord('storage'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateStorage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getStorageModalFieldSet($modal->getModalId(), $showModal));

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    protected function getStorageDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JiJidDelMdl', Trans::getWord('deleteStorage'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteStorage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $this->Field->getText('jid_gd_sku_del', $this->getParameterForModal('jid_gd_sku_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jid_goods_del', $this->getParameterForModal('jid_goods_del', $showModal)));
        $fieldSet->addField(Trans::getWord('storage'), $this->Field->getText('jid_whs_name_del', $this->getParameterForModal('jid_whs_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jid_lot_number_del', $this->getParameterForModal('jid_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jid_serial_number_del', $this->getParameterForModal('jid_serial_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('packingNumber'), $this->Field->getText('jid_packing_number_del', $this->getParameterForModal('jid_packing_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getText('jid_quantity_del', $this->getParameterForModal('jid_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jid_uom_del', $this->getParameterForModal('jid_uom_del', $showModal)));
        $fieldSet->addField(Trans::getWord('damageType'), $this->Field->getText('jid_gdt_description_del', $this->getParameterForModal('jid_gdt_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_id_del', $this->getParameterForModal('jid_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->getStringParameter('jo_route') === 'joWhBundling' || $this->getStringParameter('jo_route') === 'joWhUnBundling') {
            $this->EnableAction = false;
        }
        if ($this->isValidParameter('ji_end_load_on') === true && $this->isAllowUpdateAction()) {
            $pdfButton = new PdfButton('JiPrint', Trans::getWord('printPdf'), 'goodreceipt');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get the goods loading field Set.
     *
     * @param string $modalId To store the modal ID
     *
     * @return Portlet
     */
    protected function getFormGoodsReceivePortlet(string $modalId): Portlet
    {
        $table = new Table('JoFrJirTbl');
        $table->setHeaderRow([
            'form_jir_jog_sku' => Trans::getWord('sku'),
            'form_jir_jog_goods' => Trans::getWord('goods'),
            'form_jir_jog_quantity' => Trans::getWord('qtyPlanning'),
            'form_jir_qty_received' => Trans::getWord('qtyReceived'),
            'form_jir_total_package' => Trans::getWord('totalPackaging'),
            'form_jir_qty_returned' => Trans::getWord('qtyReturned'),
            'form_jir_jog_unit' => Trans::getWord('uom'),
        ]);
        $data = JobInboundReceiveDao::loadJobGoodsReceive($this->getDetailReferenceValue(), $this->getIntParameter('ji_id'));
        $rows = [];
        $gdDao = new GoodsDao();
        $i = 0;
        foreach ($data as $row) {
            $i++;
            $temp = [
                'form_jir_jog_gd_id' => $row['jog_gd_id'],
                'form_jir_jog_sku' => $row['jog_gd_sku'],
                'form_jir_jog_goods' => $gdDao->formatFullName($row['jog_gd_category'], $row['jog_gd_brand'], $row['jog_gd_name']),
                'form_jir_jog_quantity' => $row['jog_quantity'],
                'form_jir_jog_unit' => $row['jog_uom'],
                'form_jir_qty_received' => $row['qty_received'],
                'form_jir_total_package' => $row['total_package'],
                'form_jir_qty_returned' => $row['qty_returned'],
            ];
            $btnProcess = new ModalButton('btnJirAddMdl' . $i, '', $modalId);
            $btnProcess->setIcon(Icon::CheckSquareO)->btnPrimary()->viewIconOnly();
            $btnProcess->setEnableCallBack('jobInboundReceive', 'getJobGoodsById');
            $btnProcess->addParameter('jir_jog_id', $row['jog_id']);
            $temp['form_jir_action'] = $btnProcess;
            $rows[] = $temp;

        }
        $table->addRows($rows);
        if ($this->isAllowUpdateAction()) {
            $table->addColumnAtTheEnd('form_jir_action', Trans::getWord('action'));
            $table->addColumnAttribute('form_jir_action', 'style', 'text-align: center;');
            $table->setHyperlinkColumn('form_jir_jog_sku', 'goods/detail', ['gd_id' => 'form_jir_jog_gd_id']);
        }
        $table->addColumnAttribute('form_jir_jog_sku', 'style', 'text-align: center;');
        $table->addColumnAttribute('form_jir_jog_unit', 'style', 'text-align: center;');
        $table->setColumnType('form_jir_jog_quantity', 'float');
        $table->setColumnType('form_jir_total_package', 'float');
        $table->setColumnType('form_jir_qty_received', 'float');
        $table->setColumnType('form_jir_qty_returned', 'float');
        $table->setFooterType('form_jir_jog_quantity', 'SUM');
        $table->setFooterType('form_jir_qty_received', 'SUM');
        $table->setFooterType('form_jir_total_package', 'SUM');
        $table->setFooterType('form_jir_qty_returned', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoFJirPtl', Trans::getWord('receiveGoods'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @param ?Modal $updateModal To store the update modal.
     *
     * @return Portlet
     */
    protected function getGoodsReceivedPortlet(Modal $updateModal = null): Portlet
    {
        $table = new Table('JoJirTbl');
        $table->setHeaderRow([
            'jir_gd_sku' => Trans::getWord('sku'),
            'jir_goods' => Trans::getWord('goods'),
            'jir_lot_number' => Trans::getWord('lotNumber'),
            'jir_packing_number' => Trans::getWord('packingNumber'),
            'jir_serial_number' => Trans::getWord('serialNumber'),
            'jir_quantity' => Trans::getWord('quantity'),
            'jir_uom_code' => Trans::getWord('uom'),
            'jir_total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'jir_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'jir_condition' => Trans::getWord('condition'),
            'jir_status' => Trans::getWord('status'),
        ]);
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('ji_id') . ')';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        if ($this->isValidParameter('ji_end_load_on') === true) {
            $wheres[] = "(jir.jir_stored = 'Y')";
            $orders = [
                'gd.gd_sku',
                'jir.jir_packing_number',
                'jir.jir_id',
            ];
        } else {
            $orders = [
                'jir.jir_created_on DESC',
            ];
        }

        $rows = [];
        $data = JobInboundReceiveDao::loadData($wheres, $orders);
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['jir_goods'] = $gdDao->formatFullName($row['jir_gd_category'], $row['jir_gd_brand'], $row['jir_gd_name']);
            $qty = (float)$row['jir_quantity'];
            if ($row['jir_stored'] === 'Y') {
                $row['jir_status'] = new LabelInfo(Trans::getWord('stored'));
            } else {
                $row['jir_status'] = new LabelDark(Trans::getWord('returned'));
            }
            if (empty($row['jir_gdt_id']) === true) {
                $row['jir_condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['jir_condition'] = $row['jir_gdt_code'] . ' ' . $row['jir_gdt_description'];
            }
            $row['jir_total_volume'] = $qty * (float)$row['jir_volume'];
            $row['jir_total_weight'] = $qty * (float)$row['jir_weight'];
            $rows[] = $row;
        }
        $table->addRows($rows);

        $table->addColumnAttribute('jir_condition', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_status', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_gd_sku', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_uom_code', 'style', 'text-align: center');
        $table->setColumnType('jir_quantity', 'float');
        $table->setColumnType('jir_total_volume', 'float');
        $table->setColumnType('jir_total_weight', 'float');
        $table->setFooterType('jir_total_volume', 'SUM');
        $table->setFooterType('jir_total_weight', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoTbJirPtl', Trans::getWord('goodsReceived'));
        if ($this->isValidParameter('ji_end_load_on') === false && $this->isAllowUpdateAction()) {
            if ($updateModal !== null) {
                $table->setUpdateActionByModal($updateModal, 'jobInboundReceive', 'gdtByIdForUpdate', ['jir_id']);
            }
            $modalDelete = $this->getGoodsReceiveDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jobInboundReceive', 'gdtByIdForDelete', ['jir_id']);
        }

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getGoodsReceiveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJirMdl', Trans::getWord('receiveGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReceiveGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doReceiveGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getGoodsReceiveFieldSet($showModal));

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @param bool $showModal To trigger modal.
     *
     * @return FieldSet
     */
    protected function getGoodsReceiveFieldSet(bool $showModal): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $jogNumber = $this->Field->getText('jir_jog_number', $this->getParameterForModal('jir_jog_number', $showModal));
        $jogNumber->setReadOnly();
        $skuField = $this->Field->getText('jir_gd_sku', $this->getParameterForModal('jir_gd_sku', $showModal));
        $skuField->setReadOnly();
        $goodsField = $this->Field->getText('jir_goods', $this->getParameterForModal('jir_goods', $showModal));
        $goodsField->setReadOnly();
        $unitField = $this->Field->getText('jir_uom_code', $this->getParameterForModal('jir_uom_code', $showModal));
        $unitField->setReadOnly();
        $condition = $this->Field->getRadioGroup('jir_condition', $this->getParameterForModal('jir_condition', $showModal));
        $condition->addRadios([
            'Y' => Trans::getWord('good'),
            'N' => Trans::getWord('damage'),
        ]);
        $stored = $this->Field->getRadioGroup('jir_stored', $this->getParameterForModal('jir_stored', $showModal));
        $stored->addRadios([
            'Y' => Trans::getWord('accept'),
            'N' => Trans::getWord('reject'),
        ]);

        # Create damage type Field
        $damageTypeField = $this->Field->getSingleSelect('goodsDamageType', 'jir_gdt_description', $this->getParameterForModal('jir_gdt_description', $showModal));
        $damageTypeField->setHiddenField('jir_gdt_id', $this->getParameterForModal('jir_gdt_id', $showModal));
        $damageTypeField->addParameter('gdt_ss_id', $this->User->getSsId());
        $damageTypeField->setEnableDetailButton(false);
        $damageTypeField->setEnableNewButton(false);

        # Create damage type Field
        $damageCauseField = $this->Field->getSingleSelect('goodsCauseDamage', 'jir_gcd_description', $this->getParameterForModal('jir_gcd_description', $showModal));
        $damageCauseField->setHiddenField('jir_gcd_id', $this->getParameterForModal('jir_gcd_id', $showModal));
        $damageCauseField->addParameter('gcd_ss_id', $this->User->getSsId());
        $damageCauseField->setEnableDetailButton(false);
        $damageCauseField->setEnableNewButton(false);
        # Create damage type Field
        $packingField = $this->Field->getText('jir_packing_number', $this->getParameterForModal('jir_packing_number', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumber);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jir_quantity', $this->getParameterForModal('jir_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jir_lot_number', $this->getParameterForModal('jir_lot_number', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jir_serial_number', $this->getParameterForModal('jir_serial_number', $showModal)));
        $fieldSet->addField(Trans::getWord('packingNumber'), $packingField);
        $fieldSet->addField(Trans::getWord('condition'), $condition, true);
        $fieldSet->addField(Trans::getWord('stored'), $stored);
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('causeDamage'), $damageCauseField);
        $fieldSet->addField(Trans::getWord('damageTypeRemark'), $this->Field->getText('jir_gdt_remark', $this->getParameterForModal('jir_gdt_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamageRemark'), $this->Field->getText('jir_gcd_remark', $this->getParameterForModal('jir_gcd_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('jir_weight', $this->getParameterForModal('jir_weight', $showModal)));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('jir_length', $this->getParameterForModal('jir_length', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('jir_width', $this->getParameterForModal('jir_width', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('jir_height', $this->getParameterForModal('jir_height', $showModal)));
        $fieldSet->addField(Trans::getWord('expiredDate'), $this->Field->getCalendar('jir_expired_date', $this->getParameterForModal('jir_expired_date', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_jog_id', $this->getParameterForModal('jir_jog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_id', $this->getParameterForModal('jir_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_id', $this->getParameterForModal('jir_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_tonnage', $this->getParameterForModal('jir_gd_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_cbm', $this->getParameterForModal('jir_gd_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_sn', $this->getParameterForModal('jir_gd_sn', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_receive_sn', $this->getParameterForModal('jir_gd_receive_sn', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_packing', $this->getParameterForModal('jir_gd_packing', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_tonnage_dm', $this->getParameterForModal('jir_gd_tonnage_dm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_min_tonnage', $this->getParameterForModal('jir_gd_min_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_max_tonnage', $this->getParameterForModal('jir_gd_max_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_cbm_dm', $this->getParameterForModal('jir_gd_cbm_dm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_min_cbm', $this->getParameterForModal('jir_gd_min_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_max_cbm', $this->getParameterForModal('jir_gd_max_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_gd_expired', $this->getParameterForModal('jir_gd_expired', $showModal)));

        return $fieldSet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getGoodsReceiveDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJirDelMdl', Trans::getWord('deleteReceiveGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteReceiveGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteReceiveGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $jogNumber = $this->Field->getText('jir_jog_number_del', $this->getParameterForModal('jir_jog_number_del', $showModal));
        $jogNumber->setReadOnly();
        $skuField = $this->Field->getText('jir_gd_sku_del', $this->getParameterForModal('jir_gd_sku_del', $showModal));
        $skuField->setReadOnly();
        $goodsField = $this->Field->getText('jir_goods_del', $this->getParameterForModal('jir_goods_del', $showModal));
        $goodsField->setReadOnly();
        $unitField = $this->Field->getText('jir_uom_code_del', $this->getParameterForModal('jir_uom_code_del', $showModal));
        $unitField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumber);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jir_quantity_del', $this->getParameterForModal('jir_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jir_lot_number_del', $this->getParameterForModal('jir_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jir_serial_number_del', $this->getParameterForModal('jir_serial_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('packingNumber'), $this->Field->getText('jir_packing_number_del', $this->getParameterForModal('jir_packing_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('condition'), $this->Field->getText('jir_condition_del', $this->getParameterForModal('jir_condition_del', $showModal)));
        $fieldSet->addField(Trans::getWord('stored'), $this->Field->getText('jir_stored_del', $this->getParameterForModal('jir_stored_del', $showModal)));
        $fieldSet->addField(Trans::getWord('damageType'), $this->Field->getText('jir_gdt_description_del', $this->getParameterForModal('jir_gdt_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamage'), $this->Field->getText('jir_gcd_description_del', $this->getParameterForModal('jir_gcd_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('damageTypeRemark'), $this->Field->getText('jir_gdt_remark_del', $this->getParameterForModal('jir_gdt_remark_del', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamageRemark'), $this->Field->getText('jir_gcd_remark_del', $this->getParameterForModal('jir_gcd_remark_del', $showModal)));
        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('jir_weight_del', $this->getParameterForModal('jir_weight_del', $showModal)));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('jir_length_del', $this->getParameterForModal('jir_length_del', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('jir_width_del', $this->getParameterForModal('jir_width_del', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('jir_height_del', $this->getParameterForModal('jir_height_del', $showModal)));
        $fieldSet->addField(Trans::getWord('expiredDate'), $this->Field->getText('jir_expired_date_del', $this->getParameterForModal('jir_expired_date_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jir_id_del', $this->getParameterForModal('jir_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsReturnedPortlet(): Portlet
    {
        $table = new Table('JoJirRtTbl');
        $table->setHeaderRow([
            'jir_gd_sku_rt' => Trans::getWord('sku'),
            'jir_goods_rt' => Trans::getWord('goods'),
            'jir_lot_number_rt' => Trans::getWord('lotNumber'),
            'jir_packing_number_rt' => Trans::getWord('packingNumber'),
            'jir_serial_number_rt' => Trans::getWord('serialNumber'),
            'jir_quantity_rt' => Trans::getWord('quantity'),
            'jir_uom_code_rt' => Trans::getWord('uom'),
            'jir_total_volume_rt' => Trans::getWord('totalVolume') . ' (M3)',
            'jir_total_weight_rt' => Trans::getWord('totalWeight') . ' (KG)',
            'jir_condition_rt' => Trans::getWord('condition'),
            'jir_status_rt' => Trans::getWord('status'),
        ]);
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('ji_id') . ')';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'N')";
        $orders = [
            'gd.gd_sku',
            'jir.jir_packing_number',
            'jir.jir_id',
        ];

        $rows = [];
        $data = JobInboundReceiveDao::loadData($wheres, $orders);
        $gdDao = new GoodsDao();
        if (empty($data) === false) {
            $keys = [];
            foreach ($data as $row) {
                $row['jir_goods'] = $gdDao->formatFullName($row['jir_gd_category'], $row['jir_gd_brand'], $row['jir_gd_name']);
                $qty = (float)$row['jir_quantity'];
                if ($row['jir_stored'] === 'Y') {
                    $row['jir_status'] = new LabelInfo(Trans::getWord('stored'));
                } else {
                    $row['jir_status'] = new LabelDark(Trans::getWord('returned'));
                }
                if (empty($row['jir_gdt_id']) === true) {
                    $row['jir_condition'] = new LabelSuccess(Trans::getWord('good'));
                } else {
                    $row['jir_condition'] = $row['jir_gdt_code'] . ' ' . $row['jir_gdt_description'];
                }
                $row['jir_total_volume'] = $qty * (float)$row['jir_volume'];
                $row['jir_total_weight'] = $qty * (float)$row['jir_weight'];
                if (empty($keys) === true) {
                    $keys = array_keys($row);
                }
                $temp = [];
                foreach ($keys as $key) {
                    $temp[$key . '_rt'] = $row[$key];
                }
                $rows[] = $temp;
            }
        }
        $table->addRows($rows);

        $table->addColumnAttribute('jir_condition_rt', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_status_rt', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_jog_number_rt', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_gd_sku_rt', 'style', 'text-align: center');
        $table->addColumnAttribute('jir_uom_code_rt', 'style', 'text-align: center');
        $table->setColumnType('jir_quantity_rt', 'float');
        $table->setColumnType('jir_total_volume_rt', 'float');
        $table->setColumnType('jir_total_weight_rt', 'float');
        $table->setFooterType('jir_total_volume_rt', 'SUM');
        $table->setFooterType('jir_total_weight_rt', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoTbJirRtPtl', Trans::getWord('goodsReturned'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJiHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('ji_id', $this->getIntParameter('ji_id'));
        $content .= $this->Field->getHidden('ji_wh_id', $this->getIntParameter('ji_wh_id'));
        $this->View->addContent('JiHdFld', $content);
    }

}
