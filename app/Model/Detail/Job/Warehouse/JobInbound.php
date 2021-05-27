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
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\Warehouse\JobInboundDamageDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobInbound page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
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
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $joId = parent::doInsert();
        $jiColVal = [
            'ji_jo_id' => $joId,
            'ji_wh_id' => $this->getIntParameter('ji_wh_id'),
            'ji_eta_date' => $this->getStringParameter('ji_eta_date'),
            'ji_eta_time' => $this->getStringParameter('ji_eta_time'),
            'ji_rel_id' => $this->getIntParameter('ji_rel_id'),
            'ji_of_id' => $this->getIntParameter('ji_of_id'),
            'ji_cp_id' => $this->getIntParameter('ji_cp_id'),
            'ji_vendor_id' => $this->getIntParameter('ji_vendor_id'),
            'ji_driver' => $this->getStringParameter('ji_driver'),
            'ji_driver_phone' => $this->getStringParameter('ji_driver_phone'),
            'ji_truck_number' => $this->getStringParameter('ji_truck_number'),
            'ji_container_number' => $this->getStringParameter('ji_container_number'),
            'ji_seal_number' => $this->getStringParameter('ji_seal_number'),
        ];
        $jiDao = new JobInboundDao();
        $jiDao->doInsertTransaction($jiColVal);

        return $joId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $tblSnGoods = null;
        if ($this->getFormAction() === null) {
            $jiColVal = [
                'ji_jo_id' => $this->getDetailReferenceValue(),
                'ji_eta_date' => $this->getStringParameter('ji_eta_date'),
                'ji_eta_time' => $this->getStringParameter('ji_eta_time'),
                'ji_rel_id' => $this->getIntParameter('ji_rel_id'),
                'ji_of_id' => $this->getIntParameter('ji_of_id'),
                'ji_cp_id' => $this->getIntParameter('ji_cp_id'),
                'ji_vendor_id' => $this->getIntParameter('ji_vendor_id'),
                'ji_driver' => $this->getStringParameter('ji_driver'),
                'ji_driver_phone' => $this->getStringParameter('ji_driver_phone'),
                'ji_truck_number' => $this->getStringParameter('ji_truck_number'),
                'ji_container_number' => $this->getStringParameter('ji_container_number'),
                'ji_seal_number' => $this->getStringParameter('ji_seal_number'),
            ];
            if ($this->isValidSoId() === false) {
                $jiColVal['ji_wh_id'] = $this->getIntParameter('ji_wh_id');
            }
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('ji_id'), $jiColVal);
        } else if ($this->getFormAction() === 'doUpdateGoodsInbound') {
            $volume = null;
            if (($this->isValidParameter('jog_length') === true) && ($this->isValidParameter('jog_width') === true) && ($this->isValidParameter('jog_height') === true)) {
                $volume = $this->getFloatParameter('jog_length') * $this->getFloatParameter('jog_width') * $this->getFloatParameter('jog_height');
            }
            $jogColVal = [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_sog_id' => $this->getIntParameter('jog_sog_id'),
                'jog_gd_id' => $this->getIntParameter('jog_gd_id'),
                'jog_name' => $this->getStringParameter('jog_goods'),
                'jog_quantity' => $this->getFloatParameter('jog_quantity'),
                'jog_uom_id' => $this->getIntParameter('jog_uom_id'),
                'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
                'jog_production_number' => $this->getStringParameter('jog_production_number'),
                'jog_production_date' => $this->getStringParameter('jog_production_date'),
                'jog_available_date' => $this->getStringParameter('jo_order_date'),
                'jog_length' => $this->getFloatParameter('jog_length'),
                'jog_width' => $this->getFloatParameter('jog_width'),
                'jog_height' => $this->getFloatParameter('jog_height'),
                'jog_volume' => $volume,
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
        } else if ($this->getFormAction() === 'doCopyData') {
            $amount = $this->getIntParameter('base_copy_amount');
            $jiDao = new JobInboundDao();
            $wheres = [];
            $wheres[] = '(jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
            $wheres[] = '(jog_deleted_on IS NULL)';
            $goods = JobGoodsDao::loadSimpleData($wheres);
            $jogDao = new JobGoodsDao();
            $sn = new SerialNumber($this->User->getSsId());
            for ($i = 0; $i < $amount; $i++) {
                $joId = $this->doInsertJobOrder();
                $jiColVal = [
                    'ji_jo_id' => $joId,
                    'ji_so_id' => $this->getSoId(),
                    'ji_wh_id' => $this->getIntParameter('ji_wh_id'),
                    'ji_eta_date' => $this->getStringParameter('ji_eta_date'),
                    'ji_eta_time' => $this->getStringParameter('ji_eta_time'),
                    'ji_rel_id' => $this->getIntParameter('ji_rel_id'),
                    'ji_of_id' => $this->getIntParameter('ji_of_id'),
                    'ji_cp_id' => $this->getIntParameter('ji_cp_id'),
                ];
                $jiDao->doInsertTransaction($jiColVal);
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
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isValidSoId() === true) {
            $this->Tab->addPortlet('general', $this->getSoFieldSet());
        } else {
            $this->Tab->addPortlet('general', $this->getReferenceFieldSet());
        }
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        if ($this->isUpdate() === true) {
            if ($this->isValidParameter('ji_end_store_on') === true) {
                $this->Tab->addPortlet('goods', $this->getStorageFieldSet());
            }
            $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
            if ($this->isValidParameter('ji_end_load_on') === true) {
                $wheres = [];
                $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('ji_id') . ')';
                $wheres[] = '(jidm.jidm_deleted_on IS NULL)';
                $wheres[] = "(jidm.jidm_stored = 'N')";
                $returnGoods = JobInboundDamageDao::loadData($wheres);
                if (empty($returnGoods) === false) {
                    $this->Tab->addPortlet('goods', $this->getGoodsReturnFieldSet($returnGoods));
                }
            }
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
            $this->Validation->checkRequire('ji_wh_id');
            $this->Validation->checkRequire('ji_eta_date');
            $this->Validation->checkDate('ji_eta_date');
            $this->Validation->checkRequire('ji_eta_time');
            $this->Validation->checkTime('ji_eta_time');
            $this->Validation->checkRequire('ji_rel_id');
            $this->Validation->checkMaxLength('ji_truck_number', 255);
            $this->Validation->checkMaxLength('ji_container_number', 255);
            $this->Validation->checkMaxLength('ji_seal_number', 255);
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('ji_id');
            }
            $this->Validation->checkMaxLength('ji_driver', 255);
            $this->Validation->checkMaxLength('ji_driver_phone', 255);
        } else if ($this->getFormAction() === 'doUpdateGoodsInbound') {
            $this->Validation->checkRequire('jog_gd_id');
            $this->Validation->checkRequire('jog_quantity');
            $this->Validation->checkRequire('jog_gdu_id');
            $this->Validation->checkFloat('jog_quantity', 0);
            $this->Validation->checkUnique('jog_gd_id', 'job_goods', [
                'jog_id' => $this->getIntParameter('jog_id'),
            ], [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
                'jog_deleted_on' => null,
            ]);
        } else if ($this->getFormAction() === 'doDeleteGoodsInbound') {
            $this->Validation->checkRequire('jog_id_del');
        } else if ($this->getFormAction() === 'doDeleteStorage') {
            $this->Validation->checkRequire('jwd_id_del');
        } else if ($this->getFormAction() === 'doUpdateStorage') {
            $this->Validation->checkRequire('jwd_whs_id');
            $this->Validation->checkRequire('jwd_jog_id');
            $this->Validation->checkRequire('ji_id');
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
        $whField = $this->Field->getSingleSelect('warehouse', 'ji_warehouse', $this->getStringParameter('ji_warehouse'));
        $whField->setHiddenField('ji_wh_id', $this->getIntParameter('ji_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        if ($this->isValidParameter('jo_start_on') === true || $this->isValidSoId() === true) {
            $whField->setReadOnly();
        }


        # Create Shipper or Consignee Field
        $shipperField = $this->Field->getSingleSelect('relation', 'ji_shipper', $this->getStringParameter('ji_shipper'));
        $shipperField->setHiddenField('ji_rel_id', $this->getIntParameter('ji_rel_id'));
        $shipperField->addParameter('rel_ss_id', $this->User->getSsId());
        $shipperField->addClearField('ji_shipper_address');
        $shipperField->addClearField('ji_of_id');
        $shipperField->addClearField('ji_pic_shipper');
        $shipperField->addClearField('ji_cp_id');
        $shipperField->setDetailReferenceCode('rel_id');

        # Create order Office Field
        $shipperAddressField = $this->Field->getSingleSelect('office', 'ji_shipper_address', $this->getStringParameter('ji_shipper_address'));
        $shipperAddressField->setHiddenField('ji_of_id', $this->getIntParameter('ji_of_id'));
        $shipperAddressField->addParameterById('of_rel_id', 'ji_rel_id', Trans::getWord('shipper'));
        $shipperAddressField->addClearField('ji_pic_shipper');
        $shipperAddressField->addClearField('ji_cp_id');
        $shipperAddressField->setEnableDetailButton(false);
        $shipperAddressField->setEnableNewButton(false);

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'ji_pic_shipper', $this->getStringParameter('ji_pic_shipper'));
        $picField->setHiddenField('ji_cp_id', $this->getIntParameter('ji_cp_id'));
        $picField->addParameterById('cp_rel_id', 'ji_rel_id', Trans::getWord('shipper'));
        $picField->addParameterById('cp_of_id', 'ji_of_id', Trans::getWord('shipperAddress'));
        $picField->setDetailReferenceCode('cp_id');


        # Create Shipper or Consignee Field
        $vendorField = $this->Field->getSingleSelect('relation', 'ji_vendor', $this->getStringParameter('ji_vendor'));
        $vendorField->setHiddenField('ji_vendor_id', $this->getIntParameter('ji_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');

        $truckNumberField = $this->Field->getText('ji_truck_number', $this->getStringParameter('ji_truck_number'));
        $driverField = $this->Field->getText('ji_driver', $this->getStringParameter('ji_driver'));

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('etaDate'), $this->Field->getCalendar('ji_eta_date', $this->getStringParameter('ji_eta_date')), true);
        $fieldSet->addField(Trans::getWord('etaTime'), $this->Field->getTime('ji_eta_time', $this->getStringParameter('ji_eta_time')), true);
        $fieldSet->addField(Trans::getWord('shipper'), $shipperField, true);
        $fieldSet->addField(Trans::getWord('shipperAddress'), $shipperAddressField);
        $fieldSet->addField(Trans::getWord('picShipper'), $picField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckNumberField);
        $fieldSet->addField(Trans::getWord('containerNumber'), $this->Field->getText('ji_container_number', $this->getStringParameter('ji_container_number')));
        $fieldSet->addField(Trans::getWord('sealNumber'), $this->Field->getText('ji_seal_number', $this->getStringParameter('ji_seal_number')));
        $fieldSet->addField(Trans::getWord('transporter'), $vendorField);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $this->Field->getText('ji_driver_phone', $this->getStringParameter('ji_driver_phone')));
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
        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('qtyPlanning'),
            'jog_unit' => Trans::getWord('uom'),
            'jog_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'jog_total_volume' => Trans::getWord('cbm'),
        ]);
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
        $table->setColumnType('jog_total_weight', 'float');
        $table->setColumnType('jog_total_volume', 'float');
        $table->setFooterType('jog_quantity', 'SUM');
        $table->setFooterType('jog_total_volume', 'SUM');
        $table->setFooterType('jog_total_weight', 'SUM');
        $table->addColumnAttribute('jog_sku', 'style', 'text-align: center;');
        $table->addColumnAttribute('jog_unit', 'style', 'text-align: center;');
        $table->addRows($this->Goods);
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('goods'));

        if ($this->isAllowUpdate() && $this->isValidParameter('ji_start_load_on') === false) {
            $modal = $this->getGoodsModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getGoodsDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'jobGoods', 'getInboundGoodsById', ['jog_id']);
            $table->setDeleteActionByModal($modalDelete, 'jobGoods', 'getInboundGoodsByIdForDelete', ['jog_id']);
            # add import button if the user has access to it.
            if ($this->PageSetting->checkPageRight('AllowImportXlsGoods') === true) {
                $modalUpload = $this->getGoodsUploadModal();
                $this->View->addModal($modalUpload);
                $btnUpMdl = new ModalButton('btnJogUpMdl', Trans::getWord('uploadXls'), $modalUpload->getModalId());
                $btnUpMdl->setIcon(Icon::Plus)->btnSuccess()->pullRight();
                $portlet->addButton($btnUpMdl);
            }
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
     * @param bool $showModal To trigger the value of parameter.
     * @return FieldSet
     */
    protected function getGoodsModalField(bool $showModal): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'jog_gdc_name', $this->getParameterForModal('jog_gdc_name', $showModal));
        $goodsCategoryField->setHiddenField('jog_gdc_id', $this->getParameterForModal('jog_gdc_id', $showModal));
        $goodsCategoryField->setDetailReferenceCode('gdc_id');
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsCategoryField->addClearField('jog_goods');
        $goodsCategoryField->addClearField('jog_gd_id');

        $brandField = $this->Field->getSingleSelect('brand', 'jog_br_name', $this->getParameterForModal('jog_br_name', $showModal));
        $brandField->setHiddenField('jog_br_id', $this->getParameterForModal('jog_br_id', $showModal));
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
        $fieldSet->addHiddenField($this->Field->getHidden('jog_sog_id', $this->getParameterForModal('jog_sog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_id', $this->getParameterForModal('jog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_length', $this->getParameterForModal('jog_length', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_width', $this->getParameterForModal('jog_width', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_height', $this->getParameterForModal('jog_height', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_weight', $this->getParameterForModal('jog_weight', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_volume', $this->getParameterForModal('jog_volume', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_sog_uom_id', $this->getParameterForModal('jog_sog_uom_id', $showModal)));

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
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        $table = new Table('JiJidTbl');
        $table->setHeaderRow([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_gd_sku' => Trans::getWord('sku'),
            'jid_goods' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
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
        $portlet = new Portlet('JiJidPtl', Trans::getWord('storage'));
        $portlet->addTable($table);

        return $portlet;
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
     * @param array $data To store the return goods damage data.
     *
     * @return Portlet
     */
    protected function getGoodsReturnFieldSet(array $data): Portlet
    {
        $table = new Table('JoJidmRetTbl');
        $table->setHeaderRow([
            'jidm_jog_number' => Trans::getWord('goodsId'),
            'jidm_gd_sku' => Trans::getWord('sku'),
            'jidm_gd_name' => Trans::getWord('goods'),
            'jidm_quantity' => Trans::getWord('qtyReturned'),
            'jidm_jog_uom' => Trans::getWord('uom'),
            'jidm_gdt' => Trans::getWord('damageType'),
            'jidm_gcd' => Trans::getWord('causeDamage'),
        ]);
        $rows = [];
        foreach ($data as $row) {
            $row['jidm_gd_name'] = $row['jidm_gdc_name'] . ' ' . $row['jidm_br_name'] . ' ' . $row['jidm_gd_name'];
            $row['jidm_gdt'] = $row['jidm_gdt_code'] . '<br />' . $row['jidm_gdt_description'];
            $row['jidm_gcd'] = $row['jidm_gcd_code'] . '<br >' . $row['jidm_gcd_description'];
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jidm_quantity_planning', 'float');
        $table->setColumnType('jidm_quantity', 'float');
        $table->addColumnAttribute('jidm_gdt', 'style', 'text-align: center;');
        $table->addColumnAttribute('jidm_gcd', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('jidmRetPtl', Trans::getWord('goodsReturned'));
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
        $this->EnableDelete = !$this->isValidParameter('ji_start_store_on');
        if ($this->isValidParameter('ji_end_load_on') === true && $this->isJobFinish() === false) {
            $pdfButton = new PdfButton('JiPrint', Trans::getWord('printPdf'), 'goodreceipt');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
        if($this->isJobPublished() === true) {
            $this->setEnableCopyButton();
        }
        parent::loadDefaultButton();
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
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $content .=$this->Field->getHidden('jo_service_term', $this->getStringParameter('jo_service_term'));
        $this->View->addContent('JiHdFld', $content);

    }

}
