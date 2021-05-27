<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobGoodsDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JoOutbound page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhOutbound', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $joId = parent::doInsert();
        $jobColVal = [
            'job_jo_id' => $joId,
            'job_wh_id' => $this->getIntParameter('job_wh_id'),
            'job_eta_date' => $this->getStringParameter('job_eta_date'),
            'job_eta_time' => $this->getStringParameter('job_eta_time'),
            'job_rel_id' => $this->getIntParameter('job_rel_id'),
            'job_of_id' => $this->getIntParameter('job_of_id'),
            'job_cp_id' => $this->getIntParameter('job_cp_id'),
            'job_vendor_id' => $this->getIntParameter('job_vendor_id'),
            'job_driver' => $this->getStringParameter('job_driver'),
            'job_driver_phone' => $this->getStringParameter('job_driver_phone'),
            'job_truck_number' => $this->getStringParameter('job_truck_number'),
            'job_container_number' => $this->getStringParameter('job_container_number'),
            'job_seal_number' => $this->getStringParameter('job_seal_number'),
        ];
        $jobDao = new JobOutboundDao();
        $jobDao->doInsertTransaction($jobColVal);

        return $joId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $jobColVal = [
                'job_jo_id' => $this->getDetailReferenceValue(),
                'job_eta_date' => $this->getStringParameter('job_eta_date'),
                'job_eta_time' => $this->getStringParameter('job_eta_time'),
                'job_rel_id' => $this->getIntParameter('job_rel_id'),
                'job_of_id' => $this->getIntParameter('job_of_id'),
                'job_cp_id' => $this->getIntParameter('job_cp_id'),
                'job_vendor_id' => $this->getIntParameter('job_vendor_id'),
                'job_driver' => $this->getStringParameter('job_driver'),
                'job_driver_phone' => $this->getStringParameter('job_driver_phone'),
                'job_truck_number' => $this->getStringParameter('job_truck_number'),
                'job_container_number' => $this->getStringParameter('job_container_number'),
                'job_seal_number' => $this->getStringParameter('job_seal_number'),
            ];
            if ($this->isValidSoId() === false) {
                $jobColVal['job_wh_id'] = $this->getIntParameter('job_wh_id');
            }
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);
        } else if ($this->getFormAction() === 'doUpdateGoodsOutbound') {
            $jogColVal = [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_ji_jo_id' => $this->getIntParameter('jog_ji_jo_id'),
                'jog_gd_id' => $this->getIntParameter('jog_gd_id'),
                'jog_name' => $this->getStringParameter('jog_goods'),
                'jog_quantity' => $this->getFloatParameter('jog_quantity'),
                'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
                'jog_production_number' => $this->getStringParameter('jog_production_number'),
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
        } else if ($this->getFormAction() === 'doDeleteJob') {
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $wheres[] = '(jod.jod_jis_id IS NOT NULL)';
            $details = JobOutboundDetailDao::loadData($wheres);
            if (empty($details) === false) {
                $jisDao = new JobInboundStockDao();
                foreach ($details as $row) {
                    $jisDao->doDeleteTransaction($row['jod_jis_id']);
                }
            }
        } else if ($this->getFormAction() === 'doCopyData') {
            $amount = $this->getIntParameter('base_copy_amount');
            $jobDao = new JobOutboundDao();
            $wheres = [];
            $wheres[] = '(jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
            $wheres[] = '(jog_deleted_on IS NULL)';
            $goods = JobGoodsDao::loadSimpleData($wheres);
            $jogDao = new JobGoodsDao();
            $sn = new SerialNumber($this->User->getSsId());
            for ($i = 0; $i < $amount; $i++) {
                $joId = $this->doInsertJobOrder();
                $jobColVal = [
                    'job_jo_id' => $joId,
                    'job_so_id' => $this->getSoId(),
                    'job_wh_id' => $this->getIntParameter('job_wh_id'),
                    'job_eta_date' => $this->getStringParameter('job_eta_date'),
                    'job_eta_time' => $this->getStringParameter('job_eta_time'),
                    'job_rel_id' => $this->getIntParameter('job_rel_id'),
                    'job_of_id' => $this->getIntParameter('job_of_id'),
                    'job_cp_id' => $this->getIntParameter('job_cp_id'),
                ];
                $jobDao->doInsertTransaction($jobColVal);
                foreach ($goods as $row) {
                    $snGoods = $sn->loadNumber('JobOrderGoods', $this->getIntParameter('jo_order_of_id'), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
                    $jogColVal = [
                        'jog_jo_id' => $joId,
                        'jog_serial_number' => $snGoods,
                        'jog_gd_id' => $row['jog_gd_id'],
                        'jog_name' => $row['jog_name'],
                        'jog_quantity' => $row['jog_quantity'],
                        'jog_uom_id' => $row['jog_uom_id'],
                        'jog_gdu_id' => $row['jog_gdu_id'],
                        'jog_production_number' => $row['jog_production_number'],
                        'jog_production_date' => $row['jog_production_date'],
                        'jog_available_date' => $row['jog_available_date'],
                        'jog_length' => $row['jog_length'],
                        'jog_width' => $row['jog_width'],
                        'jog_height' => $row['jog_height'],
                        'jog_volume' => $row['jog_volume'],
                        'jog_weight' => $row['jog_weight'],
                    ];
                    $jogDao->doInsertTransaction($jogColVal);
                }
            }
        } else if ($this->getFormAction() === 'doDelete') {
            $data = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('job_id'));
            $jisDao = new JobInboundStockDao();
            $jodDao = new JobOutboundDetailDao();
            foreach ($data as $row) {
                $jodDao->doDeleteTransaction($row['jod_id']);
                if (empty($row['jod_jis_id']) === false) {
                    $jisDao->doDeleteTransaction($row['jod_jis_id']);
                }
            }
        }
        parent::doUpdate();
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobOutboundDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJobHiddenData();
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getReferenceFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        if ($this->isUpdate() === true) {
            if ($this->isValidParameter('job_end_store_on') === true) {
                $this->Tab->addPortlet('goods', $this->getStorageFieldSet());
            }
            $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
            # include default portlet
            $this->includeAllDefaultPortlet();

        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('job_wh_id');
            $this->Validation->checkRequire('job_eta_date');
            $this->Validation->checkDate('job_eta_date');
            $this->Validation->checkRequire('job_eta_time');
            $this->Validation->checkTime('job_eta_time');
            if ($this->getIntParameter('jo_srt_id') !== 9) {
                $this->Validation->checkRequire('job_rel_id');
            }
            $this->Validation->checkMaxLength('job_truck_number', 255);
            $this->Validation->checkMaxLength('job_container_number', 255);
            $this->Validation->checkMaxLength('job_driver', 255);
            $this->Validation->checkMaxLength('job_driver_phone', 255);
            $this->Validation->checkMaxLength('job_seal_number', 255);
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('job_id');
            }
        } else if ($this->getFormAction() === 'doUpdateGoodsOutbound') {
            $this->Validation->checkRequire('jog_gd_id');
            $this->Validation->checkRequire('jog_gdu_id');
            $this->Validation->checkRequire('jog_quantity');
            $this->Validation->checkFloat('jog_quantity', 1);
        } else if ($this->getFormAction() === 'doDeleteGoodsOutbound') {
            $this->Validation->checkRequire('jog_id_del');
        } else if ($this->getFormAction() === 'doUpdateStorage') {
            $this->Validation->checkRequire('jwd_whs_id');
            $this->Validation->checkRequire('jwd_jog_id');
            $this->Validation->checkRequire('job_id');
            $this->Validation->checkRequire('jwd_uom_id');
            $this->Validation->checkRequire('jwd_quantity');
            $this->Validation->checkFloat('jwd_quantity');
        }
        parent::loadValidationRole();
    }


    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        # Create Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'job_warehouse', $this->getStringParameter('job_warehouse'));
        $whField->setHiddenField('job_wh_id', $this->getIntParameter('job_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        if ($this->isValidParameter('jo_publish_on') === true || $this->isValidSoId() === true) {
            $whField->setReadOnly();
        }


        # Create Shipper or Consignee Field
        $shipperField = $this->Field->getSingleSelect('relation', 'job_consignee', $this->getStringParameter('job_consignee'));
        $shipperField->setHiddenField('job_rel_id', $this->getIntParameter('job_rel_id'));
        $shipperField->addParameter('rel_ss_id', $this->User->getSsId());
        $shipperField->addClearField('job_consignee_address');
        $shipperField->addClearField('job_of_id');
        $shipperField->addClearField('job_pic_consignee');
        $shipperField->addClearField('job_cp_id');
        $shipperField->setDetailReferenceCode('rel_id');

        # Create order Office Field
        $shipperAddressField = $this->Field->getSingleSelect('office', 'job_consignee_address', $this->getStringParameter('job_consignee_address'));
        $shipperAddressField->setHiddenField('job_of_id', $this->getIntParameter('job_of_id'));
        $shipperAddressField->addParameterById('of_rel_id', 'job_rel_id', Trans::getWord('consignee'));
        $shipperAddressField->addClearField('job_pic_consignee');
        $shipperAddressField->addClearField('job_cp_id');
        $shipperAddressField->setEnableDetailButton(false);
        $shipperAddressField->setEnableNewButton(false);

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'job_pic_consignee', $this->getStringParameter('job_pic_consignee'));
        $picField->setHiddenField('job_cp_id', $this->getIntParameter('job_cp_id'));
        $picField->addParameterById('cp_rel_id', 'job_rel_id', Trans::getWord('consignee'));
        $picField->addParameterById('cp_of_id', 'job_of_id', Trans::getWord('consigneeAddress'));
        $picField->setDetailReferenceCode('cp_id');

        # Create Shipper or Consignee Field
        $vendorField = $this->Field->getSingleSelect('relation', 'job_vendor', $this->getStringParameter('job_vendor'));
        $vendorField->setHiddenField('job_vendor_id', $this->getIntParameter('job_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('job_vendor_pic');
        $vendorField->addClearField('job_pic_vendor');

        $driverField = $this->Field->getText('job_driver', $this->getStringParameter('job_driver'));
        $truckNumberField = $this->Field->getText('job_truck_number', $this->getStringParameter('job_truck_number'));
        if ($this->isValidParameter('jo_document_on') === true) {
            $vendorField->setReadOnly();
            // $truckNumberField->setReadOnly();
            $driverField->setReadOnly();
        }

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('etaDate'), $this->Field->getCalendar('job_eta_date', $this->getStringParameter('job_eta_date')), true);
        $fieldSet->addField(Trans::getWord('etaTime'), $this->Field->getTime('job_eta_time', $this->getStringParameter('job_eta_time')), true);
        $fieldSet->addField(Trans::getWord('consignee'), $shipperField, true);
        $fieldSet->addField(Trans::getWord('consigneeAddress'), $shipperAddressField);
        $fieldSet->addField(Trans::getWord('picConsignee'), $picField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckNumberField);
        $fieldSet->addField(Trans::getWord('containerNumber'), $this->Field->getText('job_container_number', $this->getStringParameter('job_container_number')));
        $fieldSet->addField(Trans::getWord('sealNumber'), $this->Field->getText('job_seal_number', $this->getStringParameter('job_seal_number')));
        $fieldSet->addField(Trans::getWord('transporter'), $vendorField);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $this->Field->getText('job_driver_phone', $this->getStringParameter('job_driver_phone')));
        # Create a portlet box.
        $portlet = new Portlet('JowGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $modalUpdate = $this->getGoodsModal();
        $this->View->addModal($modalUpdate);
        $modalDelete = $this->getGoodsDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_serial_number' => Trans::getWord('id'),
            'jog_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_production_number' => Trans::getWord('productionNumber'),
            'jog_quantity' => Trans::getWord('qtyPlanning'),
            'jog_unit' => Trans::getWord('uom'),
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
        ]);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            $row['jog_goods'] = $gdDao->formatFullName($row['jog_gdc_name'], $row['jog_br_name'], $row['jog_goods']);
            $volume = 0;
            if (empty($row['jog_gd_volume']) === false) {
                $volume = (float)$row['jog_gd_volume'];
            }
            $row['total_volume'] = $volume * (float)$row['jog_quantity'];

            $weight = 0;
            if (empty($row['jog_gd_weight']) === false) {
                $weight = (float)$row['jog_gd_weight'];
            }
            $row['total_weight'] = $weight * (float)$row['jog_quantity'];
            if (empty($row['jog_damage_id']) === false) {
                $row['jog_condition'] = new LabelDanger(Trans::getWord('damage'));
            } else {
                $row['jog_condition'] = new LabelSuccess(Trans::getWord('good'));
            }
            $btnUpdate = new ModalButton('btnJogEdtMdl' . $row['jog_id'], '', $modalUpdate->getModalId());
            $btnUpdate->setEnableCallBack('jobGoods', 'getOutboundGoodsById');
            $btnUpdate->addParameter('jog_id', $row['jog_id']);
            $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
            $row['jog_action'] = $btnUpdate;

            if ((float)$row['jog_qty_picking'] === 0.0) {
                $btnDelete = new ModalButton('btnJogDelMdl' . $row['jog_id'], '', $modalDelete->getModalId());
                $btnDelete->setEnableCallBack('jobGoods', 'getOutboundGoodsByIdForDelete');
                $btnDelete->addParameter('jog_id', $row['jog_id']);
                $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $row['jog_action'] .= ' ' . $btnDelete;
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jog_quantity', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->setFooterType('jog_quantity', 'SUM');
        $table->setFooterType('total_volume', 'SUM');
        $table->setFooterType('total_weight', 'SUM');
        # Create a portlet box.

        $portlet = new Portlet('JoJogPtl', Trans::getWord('goods'));
        if ($this->isValidParameter('job_end_load_on') === true) {
            $table->addColumnAfter('jog_quantity', 'jog_qty_loaded', Trans::getWord('qtyLoaded'));
            $table->setColumnType('jog_qty_loaded', 'float');
            $table->setFooterType('jog_qty_loaded', 'SUM');
        }
        if ($this->isAllowUpdate() && $this->isValidParameter('job_end_load_on') === false) {
            $table->addColumnAtTheEnd('jog_action', Trans::getWord('action'));
            $table->addColumnAttribute('jog_action', 'style', 'text-align: center;');

            if ($this->PageSetting->checkPageRight('AllowImportXlsGoods') === true) {
                $modalUpload = $this->getGoodsUploadModal();
                $this->View->addModal($modalUpload);
                $btnUpMdl = new ModalButton('btnJogUpMdl', Trans::getWord('uploadXls'), $modalUpload->getModalId());
                $btnUpMdl->setIcon(Icon::Plus)->btnSuccess()->pullRight();
                $portlet->addButton($btnUpMdl);

            }

            $btnCpMdl = new ModalButton('btnJoJogMdl', Trans::getWord('addGoods'), $modalUpdate->getModalId());
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
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateGoodsOutbound');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateGoodsOutbound' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getGoodsModalField($showModal, $modal->getModalId()));

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @param bool $showModal to store the trigger to open modal on reload.
     * @param string $modalId To store the id of the main modal.
     *
     * @return FieldSet
     */
    protected function getGoodsModalField(bool $showModal, string $modalId): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Unit Field
        $goodsField = $this->Field->getSingleSelectTable('goods', 'jog_goods', $this->getParameterForModal('jog_goods', $showModal), 'loadSingleSelectTableData');
        $goodsField->setHiddenField('jog_gd_id', $this->getParameterForModal('jog_gd_id', $showModal));
        $goodsField->setTableColumns([
            'gd_sku' => Trans::getWord('sku'),
            'gd_gdc_name' => Trans::getWord('category'),
            'gd_br_name' => Trans::getWord('brand'),
            'gd_name' => Trans::getWord('goods'),
        ]);
        $goodsField->setAutoCompleteFields([
            'jog_sku' => 'gd_sku',
            'jog_gdc_name' => 'gd_gdc_name',
            'jog_br_name' => 'gd_br_name',
        ]);
        $goodsField->setFilters([
            'gdc_name' => Trans::getWord('category'),
            'br_name' => Trans::getWord('brand'),
            'gd_name' => Trans::getWord('goods'),
            'gd_sku' => Trans::getWord('sku'),
        ]);
        $goodsField->setValueCode('gd_id');
        $goodsField->setLabelCode('gd_full_name');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsField->addClearField('jog_production_batch');
        $goodsField->addClearField('jog_production_number');
        $goodsField->addClearField('jog_unit');
        $goodsField->addClearField('jog_gdu_id');
        $goodsField->setParentModal($modalId);
        $this->View->addModal($goodsField->getModal());

        $productionNumber = $this->Field->getSingleSelect('jobInboundDetail', 'jog_production_batch', $this->getParameterForModal('jog_production_batch', $showModal), 'loadAvailableLotNumber');
        $productionNumber->setHiddenField('jog_production_number', $this->getParameterForModal('jog_production_number', $showModal));
        $productionNumber->addParameterById('jid_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $productionNumber->setEnableNewButton(false);
        $productionNumber->setEnableDetailButton(false);

        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'jog_unit', $this->getParameterForModal('jog_unit', $showModal));
        $unitField->setHiddenField('jog_gdu_id', $this->getParameterForModal('jog_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('lotNumber'), $productionNumber);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jog_quantity', $this->getParameterForModal('jog_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('jog_id', $this->getParameterForModal('jog_id', $showModal)));

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
        $fieldSet->addField(Trans::getWord('brand'), $this->Field->getText('jog_br_name_del', $this->getParameterForModal('jog_br_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('category'), $this->Field->getText('jog_gdc_name_del', $this->getParameterForModal('jog_gdc_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jog_name_del', $this->getParameterForModal('jog_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('productionNumber'), $this->Field->getText('jog_production_number_del', $this->getParameterForModal('jog_production_number_del', $showModal)));
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
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        $table = new Table('JoJwdTbl');
        $table->setHeaderRow([
            'jod_storage' => Trans::getWord('storage'),
            'jod_jog_number' => Trans::getWord('goodsId'),
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
            'jod_packing_number' => Trans::getWord('packingNumber'),
            'jod_jid_serial_number' => Trans::getWord('serialNumber'),
            'jod_quantity' => Trans::getWord('qtyPicking'),
            'jod_unit' => Trans::getWord('uom'),
            'jod_condition' => Trans::getWord('condition'),
        ]);
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $data = JobOutboundDetailDao::loadData($wheres);
        $rows = [];
        $i = 0;
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            if (empty($row['jid_gdt_id']) === true) {
                $row['jod_condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['jod_condition'] = $row['jod_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jod_gcd_description'];
            }
            $qtyLoaded = (float)$row['jod_qty_loaded'];
            if ($qtyLoaded > 0) {
                $row['jod_qty_return'] = (float)$row['jod_quantity'] - $qtyLoaded;
            }
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);

            if ($row['jod_gd_sn'] === 'Y' && empty($row['jod_jid_serial_number']) === true) {
                $table->addCellAttribute('jod_jid_serial_number', $i, 'style', 'background-color: red;');
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        $table->setColumnType('jod_quantity', 'float');
        $table->setFooterType('jod_quantity', 'SUM');
        $table->addColumnAttribute('jod_condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_gd_sku', 'style', 'text-align: center;');
        if ($this->isValidParameter('job_end_load_on') === true) {
            $table->addColumnAfter('jod_quantity', 'jod_qty_loaded', Trans::getWord('qtyLoaded'));
            $table->addColumnAfter('jod_qty_loaded', 'jod_qty_return', Trans::getWord('qtyReturned'));
            $table->setColumnType('jod_qty_loaded', 'float');
            $table->setColumnType('jod_qty_return', 'float');
            $table->setFooterType('jod_qty_loaded', 'SUM');
            $table->setFooterType('jod_qty_return', 'SUM');
        }
        # Create a portlet box.
        $portlet = new Portlet('JoJodPtl', Trans::getWord('goodsTaken'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {

        if ($this->isUpdate()) {
            if ($this->isValidParameter('job_end_load_on') === true && $this->isJobFinish() === false) {
                $pdfButton = new PdfButton('JiPrint', Trans::getWord('printPdf'), 'outboundgoods');
                $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
                $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
                $this->View->addButton($pdfButton);
            }
            # button DO
            if (($this->isValidParameter('job_end_load_on') === true && $this->isJobFinish() === false) ||
                ($this->isValidParameter('job_end_store_on') === true && $this->PageSetting->checkPageRight('AllowPrintDoAfterPicking') === true &&
                    $this->isJobFinish() === false)) {
                $pdfButton = new PdfButton('JiDoPrint', Trans::getWord('printDo'), 'deliveryorder');
                $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
                $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
                $this->View->addButton($pdfButton);
            }
            # Set Enable Delete
            $this->EnableDelete = !$this->isValidParameter('job_start_load_on');
        }
        if ($this->isJobPublished() === true) {
            $this->setEnableCopyButton();
        }
        parent::loadDefaultButton();
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
        if ($this->getStringParameter('jo_route') === 'joWhBundling') {
            $jbData = JobBundlingDao::getByJobOrder($this->getDetailReferenceValue());
            $wheres[] = '(jog.jog_id <> ' . $jbData['jb_jog_id'] . ')';
        }
        if ($this->getStringParameter('jo_route') === 'joWhUnBundling') {
            $jbData = JobBundlingDao::getByJobOrder($this->getDetailReferenceValue());
            $wheres[] = '(jog.jog_id = ' . $jbData['jb_jog_id'] . ')';
        }
        $this->Goods = JobGoodsDao::loadDataForOutbound($wheres);
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJobHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('job_id', $this->getIntParameter('job_id'));
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $content .=$this->Field->getHidden('jo_service_term', $this->getStringParameter('jo_service_term'));
        $this->View->addContent('JobHdFld', $content);
    }

}
