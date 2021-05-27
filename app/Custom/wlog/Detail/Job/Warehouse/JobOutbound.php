<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\wlog\Detail\Job\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobGoodsDao;

/**
 * Class to handle the creation of detail JoOutbound page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends \App\Model\Detail\Job\Warehouse\JobOutbound
{

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doInsertGoodsOutboundByInbound') {
            $wheres = [];
            $wheres[] = '(jo.jo_id = ' . $this->getIntParameter('jo_id_in') . ')';
            $data = JobGoodsDao::loadSimpleDataForInbound($wheres);
            $jogDao = new JobGoodsDao();
            $sn = new SerialNumber($this->User->getSsId());
            foreach ($data as $row) {
                $snGoods = $sn->loadNumber('JobOrderGoods', $this->getIntParameter('jo_order_of_id'), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
                $numbers = [];
                $numbers[] = $row['jo_customer_ref'];
                $numbers[] = $row['jo_aju_ref'];
                $lotNumber = implode(' - ', $numbers);
                $jogColVal = [
                    'jog_jo_id' => $this->getDetailReferenceValue(),
                    'jog_serial_number' => $snGoods,
                    'jog_gd_id' => $row['jog_gd_id'],
                    'jog_name' => $row['jog_name'],
                    'jog_quantity' => $row['jog_quantity'],
                    'jog_gdu_id' => $row['jog_gdu_id'],
                    'jog_production_number' => $lotNumber,
                ];
                $jogDao->doInsertTransaction($jogColVal);
            }
        }
        parent::doUpdate();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        parent::loadValidationRole();
        if ($this->getFormAction() === 'doUpdateGoodsOutbound') {
            $this->Validation->checkRequire('jog_production_number');
        } elseif ($this->getFormAction() === 'doInsertGoodsOutboundByInbound') {
            $this->Validation->checkRequire('jo_id_in');
        } elseif ($this->getFormAction() === 'doActionArrive') {
            $this->Validation->checkRequire('job_truck_number');
        }
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $modalInser = $this->getGoodsInsertModal();
        $this->View->addModal($modalInser);
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
        foreach ($this->Goods as $row) {
            $row['jog_goods'] = $row['jog_gdc_name'] . ' ' . $row['jog_br_name'] . ' ' . $row['jog_goods'];
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
        if ($this->isJobDeleted() === false && $this->isValidParameter('job_end_load_on') === false) {
            $table->addColumnAtTheEnd('jog_action', Trans::getWord('action'));
            $table->addColumnAttribute('jog_action', 'style', 'text-align: center;');

            $btnCpMdl = new ModalButton('btnJoJogMdl', Trans::getWord('addGoods'), $modalInser->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @param bool $showModal to store the trigger to open modal on reload.
     * @param string $modalId To store the id of the main modal.
     * @return FieldSet
     */
    protected function getGoodsModalField(bool $showModal, string $modalId): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # set readonly field.
        $goodsField = $this->Field->getText('jog_goods', $this->getParameterForModal('jog_goods', $showModal));
        $goodsField->setReadOnly();
        $skuField = $this->Field->getText('jog_sku', $this->getParameterForModal('jog_sku', $showModal));
        $skuField->setReadOnly();
        $brField = $this->Field->getText('jog_br_name', $this->getParameterForModal('jog_br_name', $showModal));
        $brField->setReadOnly();
        $gdcField = $this->Field->getText('jog_gdc_name', $this->getParameterForModal('jog_gdc_name', $showModal));
        $gdcField->setReadOnly();


        $productionNumber = $this->Field->getSingleSelect('jobGoods', 'jog_production_batch', $this->getParameterForModal('jog_production_batch', $showModal), 'loadProductionNumbers');
        $productionNumber->setHiddenField('jog_production_number', $this->getParameterForModal('jog_production_number', $showModal));
        $productionNumber->addParameterById('jog_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $productionNumber->setEnableNewButton(false);
        $productionNumber->setEnableDetailButton(false);
        $productionNumber->setReadOnly();

        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'jog_unit', $this->getParameterForModal('jog_unit', $showModal));
        $unitField->setHiddenField('jog_gdu_id', $this->getParameterForModal('jog_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);
        $unitField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('brand'), $brField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jog_quantity', $this->getParameterForModal('jog_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('category'), $gdcField);
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addField(Trans::getWord('productionNumber'), $productionNumber);
        $fieldSet->addHiddenField($this->Field->getHidden('jog_id', $this->getParameterForModal('jog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jog_gd_id', $this->getParameterForModal('jog_gd_id', $showModal)));

        return $fieldSet;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getGoodsInsertModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoGdInMdl', Trans::getWord('inboundGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertGoodsOutboundByInbound');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertGoodsOutboundByInbound' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Create Unit Field
        $inboundField = $this->Field->getSingleSelectTable('jobGoods', 'jo_number_in', $this->getParameterForModal('jo_number_in', $showModal), 'loadInboundForOutboundGoods');
        $inboundField->setHiddenField('jo_id_in', $this->getParameterForModal('jo_id_in', $showModal));
        $inboundField->setTableColumns([
            'jo_number_in' => Trans::getWord('jobNumber'),
            'jo_customer_ref_in' => Trans::getWord('customerRef'),
            'jo_bl_ref_in' => Trans::getWord('blRef'),
            'jo_aju_ref_in' => Trans::getWord('ajuRef'),
            'jo_goods_in' => Trans::getWord('goods'),
        ]);
        $inboundField->setAutoCompleteFields([
            'jo_customer_ref_in' => 'jo_customer_ref_in',
            'jo_bl_ref_in' => 'jo_bl_ref_in',
            'jo_aju_ref_in' => 'jo_aju_ref_in',
            'jo_goods_field_in' => 'jo_goods_field_in',
        ]);
        $inboundField->setFilters([
            'jo_number_in' => Trans::getWord('jobNumber'),
            'jo_customer_ref_in' => Trans::getWord('customerRef'),
            'jo_aju_ref_in' => Trans::getWord('ajuRef'),
        ]);
        $inboundField->setValueCode('jo_id_in');
        $inboundField->setLabelCode('jo_number_in');
        $inboundField->addParameter('jo_ss_id_in', $this->User->getSsId());
        $inboundField->addParameter('jo_bl_ref_in', $this->getStringParameter('jo_bl_ref'));
        $inboundField->addParameterById('jo_rel_id_in', 'jo_rel_id', Trans::getWord('customer'));
        $inboundField->setParentModal($modal->getModalId());
        $this->View->addModal($inboundField->getModal());
        # set readonly field.
        $customerRefField = $this->Field->getText('jo_customer_ref_in', $this->getParameterForModal('jo_customer_ref_in', $showModal));
        $customerRefField->setReadOnly();
        $blRefField = $this->Field->getText('jo_bl_ref_in', $this->getParameterForModal('jo_bl_ref_in', $showModal));
        $blRefField->setReadOnly();
        $ajuRefField = $this->Field->getText('jo_aju_ref_in', $this->getParameterForModal('jo_aju_ref_in', $showModal));
        $ajuRefField->setReadOnly();
        $goodsField = $this->Field->getTextArea('jo_goods_field_in', $this->getParameterForModal('jo_goods_field_in', $showModal));
        $goodsField->addAttribute('readonly', 'readonly');
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('jobNumber'), $inboundField, true);
        $fieldSet->addField(Trans::getWord('customerRef'), $customerRefField);
        $fieldSet->addField(Trans::getWord('blRef'), $blRefField);
        $fieldSet->addField(Trans::getWord('ajuRef'), $ajuRefField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
}
