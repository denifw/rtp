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
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Viewer\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobStockAdjustment page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockAdjustment extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockAdjustment', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doActionStartAdjust') {
            # Update start Job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $jadColVal = [
                'jad_ja_id' => $this->getIntParameter('ja_id'),
                'jad_jid_id' => $this->getIntParameter('jad_jid_id'),
                'jad_quantity' => $this->getFloatParameter('jad_quantity'),
                'jad_gdu_id' => $this->getIntParameter('jad_gdu_id'),
                'jad_sat_id' => $this->getIntParameter('jad_sat_id'),
                'jad_remark' => $this->getStringParameter('jad_remark'),
            ];
            $jadDao = new JobAdjustmentDetailDao();
            if ($this->isValidParameter('jad_id') === true) {
                $jadDao->doUpdateTransaction($this->getIntParameter('jad_id'), $jadColVal);
            } else {
                $jadDao->doInsertTransaction($jadColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $jadDao = new JobAdjustmentDetailDao();
            $jadDao->doDeleteTransaction($this->getIntParameter('jad_id_del'));
        } elseif ($this->getFormAction() === 'doActionEndAdjust') {
            $details = JobAdjustmentDetailDao::loadDataByJaId($this->getIntParameter('ja_id'));
            $jadDao = new JobAdjustmentDetailDao();
            $jisDao = new JobInboundStockDao();
            foreach ($details as $row) {
                $jisColVal = [
                    'jis_jid_id' => $row['jad_jid_id'],
                    'jis_quantity' => $row['jad_quantity'],
                ];
                $jisDao->doInsertTransaction($jisColVal);
                $jadDao->doUpdateTransaction($row['jad_id'], [
                    'jad_jis_id' => $jisDao->getLastInsertId()
                ]);
            }
            $jaColVal = [
                'ja_complete_on' => date('Y-m-d H:i:s')
            ];
            $jaDao = new JobAdjustmentDao();
            $jaDao->doUpdateTransaction($this->getIntParameter('ja_id'), $jaColVal);

            $this->doUpdateJobAction(2);
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
        return JobAdjustmentDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJaHiddenData();
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getDetailFieldSet());
        # General tabs.
        if ($this->isValidParameter('jo_start_on') === true && $this->isValidParameter('ja_complete_on') === false) {
            $this->Tab->setActiveTab('general', true);
        }
        # include default portlet
        $this->includeAllDefaultPortlet();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doActionStartAdjust') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionEndAdjust') {
            $this->Validation->checkRequire('ja_id');
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('jad_jid_id');
            $this->Validation->checkRequire('jad_gdu_id');
            $this->Validation->checkRequire('jad_quantity');
            $this->Validation->checkFloat('jad_quantity');
            $this->Validation->checkRequire('jad_sat_id');
            $this->Validation->checkMaxLength('jad_remark', 255);
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('jad_id_del');
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('wh_name'),
            ],
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('jo_customer'),
            ],
            [
                'label' => Trans::getWord('goods'),
                'value' => $this->getStringParameter('ja_goods'),
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->getStringParameter('jo_manager'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getDetailFieldSet(): Portlet
    {
        $table = new Table('JoSopTbl');
        $table->setHeaderRow([
            'jad_jo_number' => Trans::getWord('inboundNumber'),
            'jad_inbound_on' => Trans::getWord('inboundDate'),
            'jad_whs_name' => Trans::getWord('storage'),
            'jad_lot_number' => Trans::getWord('lotNumber'),
            'jad_serial_number' => Trans::getWord('serialNumber'),
            'jad_quantity' => Trans::getWord('qtyAdjustment'),
            'jad_uom' => Trans::getWord('uom'),
            'jad_sat_description' => Trans::getWord('adjustmentType'),
            'jad_remark' => Trans::getWord('remark')
        ]);
        $data = JobAdjustmentDetailDao::loadDataByJaId($this->getIntParameter('ja_id'));
        $rows = [];
        foreach ($data as $row) {
            $row['jad_inbound_on'] = DateTimeParser::format($row['jad_inbound_on'], 'Y-m-d H:i:s', 'd.M.Y');
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jad_quantity', 'float');
        # Create a portlet box.
        $portlet = new Portlet('JoJadPtl', Trans::getWord('adjustmentDetail'));
        if ($this->isValidParameter('jo_start_on') === true && $this->isValidParameter('ja_complete_on') === false) {
            $modal = $this->getDetailModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getDetailDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'jobAdjustmentDetail', 'getByReference', ['jad_id']);
            $table->setDeleteActionByModal($modalDelete, 'jobAdjustmentDetail', 'getByReferenceForDelete', ['jad_id']);
            # add new button
            $btnCpMdl = new ModalButton('btnJoJadMdl', Trans::getWord('addGoods'), $modal->getModalId());
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
    protected function getDetailModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JadGdMdl', Trans::getWord('goods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create goods Field
        $goodsField = $this->Field->getSingleSelectTable('jobInboundDetail', 'jad_jo_number', $this->getParameterForModal('jad_jo_number', $showModal), 'loadDataForAdjustment');
        $goodsField->setHiddenField('jad_jid_id', $this->getParameterForModal('jad_jid_id', $showModal));
        $goodsField->setTableColumns([
            'jid_jo_number' => Trans::getWord('inboundNumber'),
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_quantity' => Trans::getWord('inboundQuantity'),
            'jid_stock' => Trans::getWord('currentStock'),
            'jid_gdu_uom' => Trans::getWord('uom'),
            'jid_gdt_description' => Trans::getWord('damageType'),
        ]);
        $goodsField->setFilters([
            'jo_number' => Trans::getWord('inboundNumber'),
            'lot_number' => Trans::getWord('lotNumber'),
            'whs_name' => Trans::getWord('storage'),
            'serial_number' => Trans::getWord('serialNumber')
        ]);
        $goodsField->setAutoCompleteFields([
            'jad_whs_name' => 'jid_whs_name',
            'jad_gdu_id' => 'jid_gdu_id',
            'jad_jid_quantity' => 'jid_quantity',
            'jad_jid_stock' => 'jid_stock',
            'jad_uom' => 'jid_gdu_uom',
            'jad_lot_number' => 'jid_lot_number',
            'jad_serial_number' => 'jid_serial_number',
            'jad_jid_gdt_description' => 'jid_gdt_description',
        ]);
        $goodsField->setValueCode('jid_id');
        $goodsField->setLabelCode('jid_jo_number');
        $goodsField->addParameter('wh_id', $this->getIntParameter('ja_wh_id'));
        $goodsField->addParameter('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $goodsField->addParameter('ja_id', $this->getIntParameter('ja_id'));
        $goodsField->addParameter('ja_gd_id', $this->getIntParameter('ja_gd_id'));
        $goodsField->addParameter('ss_id', $this->User->getSsId());
        $goodsField->setParentModal($modal->getModalId());
        $this->View->addModal($goodsField->getModal());

        # Create Unit Field
        $satField = $this->Field->getSingleSelect('stockAdjustmentType', 'jad_sat_description', $this->getParameterForModal('jad_sat_description', $showModal));
        $satField->setHiddenField('jad_sat_id', $this->getParameterForModal('jad_sat_id', $showModal));
        $satField->addParameter('sat_ss_id', $this->User->getSsId());
        $satField->setDetailReferenceCode('sat_id');
        $storageField = $this->Field->getText('jad_whs_name', $this->getParameterForModal('jad_whs_name', $showModal));
        $storageField->setReadOnly();
        $unitField = $this->Field->getText('jad_uom', $this->getParameterForModal('jad_uom', $showModal));
        $unitField->setReadOnly();
        $productionField = $this->Field->getText('jad_lot_number', $this->getParameterForModal('jad_lot_number', $showModal));
        $productionField->setReadOnly();
        $serialField = $this->Field->getText('jad_serial_number', $this->getParameterForModal('jad_serial_number', $showModal));
        $serialField->setReadOnly();
        $gdtField = $this->Field->getText('jad_jid_gdt_description', $this->getParameterForModal('jad_jid_gdt_description', $showModal));
        $gdtField->setReadOnly();
        $qtyInboundField = $this->Field->getText('jad_jid_quantity', $this->getParameterForModal('jad_jid_quantity', $showModal));
        $qtyInboundField->setReadOnly();
        $stockField = $this->Field->getText('jad_jid_stock', $this->getParameterForModal('jad_jid_stock', $showModal));
        $stockField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $storageField);
        $fieldSet->addField(Trans::getWord('inboundNumber'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('productionNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $serialField);
        $fieldSet->addField(Trans::getWord('inboundQuantity'), $qtyInboundField);
        $fieldSet->addField(Trans::getWord('currentStock'), $stockField);
        $fieldSet->addField(Trans::getWord('qtyAdjustment'), $this->Field->getNumber('jad_quantity', $this->getParameterForModal('jad_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('adjustmentType'), $satField, true);
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getText('jad_remark', $this->getParameterForModal('jad_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jad_gdu_id', $this->getParameterForModal('jad_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jad_id', $this->getParameterForModal('jad_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    protected function getDetailDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJadDelMdl', Trans::getWord('deleteGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $this->Field->getText('jad_whs_name_del', $this->getParameterForModal('jad_whs_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('inboundNumber'), $this->Field->getText('jad_jo_number_del', $this->getParameterForModal('jad_jo_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jad_lot_number_del', $this->getParameterForModal('jad_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jad_serial_number_del', $this->getParameterForModal('jad_serial_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('inboundQuantity'), $this->Field->getNumber('jad_jid_quantity_del', $this->getParameterForModal('jad_jid_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('currentStock'), $this->Field->getNumber('jad_jid_stock_del', $this->getParameterForModal('jad_jid_stock_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jad_uom_del', $this->getParameterForModal('jad_uom_del', $showModal)));
        $fieldSet->addField(Trans::getWord('qtyAdjustment'), $this->Field->getNumber('jad_quantity_del', $this->getParameterForModal('jad_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('adjustmentType'), $this->Field->getText('jad_sat_description_del', $this->getParameterForModal('jad_sat_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getText('jad_remark_del', $this->getParameterForModal('jad_remark_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jad_id_del', $this->getParameterForModal('jad_id_del', $showModal)));
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
        parent::loadDefaultButton();
        if ($this->isValidParameter('ja_complete_on') === true && $this->isJobDeleted() === false && $this->isJobFinish() === false) {
            $pdfButton = new PdfButton('JaPrint', Trans::getWord('printPdf'), 'stockadjustment');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJaHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('ja_id', $this->getIntParameter('ja_id'));
        $content .= $this->Field->getHidden('ja_wh_id', $this->getIntParameter('ja_wh_id'));
        $content .= $this->Field->getHidden('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $this->View->addContent('JaHdFld', $content);

    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
    }

}
