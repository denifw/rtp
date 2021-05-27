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

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Model\Dao\Job\Warehouse\StockOpnameDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Viewer\Job\BaseJobOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail StockOpname page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockOpname extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhOpname', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {

        if ($this->getFormAction() === 'doActionStartOpname') {
            $this->doStartJobOrder();
            # Update start Opname
            $sopColVal = [
                'sop_start_on' => date('Y-m-d H:i:s'),
            ];
            $sopDao = new StockOpnameDao();
            $sopDao->doUpdateTransaction($this->getIntParameter('sop_id'), $sopColVal);

            # Generate Stock Opname Detail Data
            $data = $this->loadCurrentStorageStockData();
            $sodDao = new StockOpnameDetailDao();

            $sodDao->doInsertBatchTransaction($data);

            # Update job Action
            $this->doUpdateJobAction(1);
        } elseif ($this->getFormAction() === 'doInsertOpnameDetail') {
            $sodColVal = [
                'sod_sop_id' => $this->getIntParameter('sop_id'),
                'sod_whs_id' => $this->getIntParameter('sod_whs_id'),
                'sod_gd_id' => $this->getIntParameter('sod_gd_id'),
                'sod_gdt_id' => $this->getIntParameter('sod_gdt_id'),
                'sod_gdu_id' => $this->getIntParameter('sod_gdu_id'),
                'sod_quantity' => 0,
                'sod_qty_figure' => $this->getFloatParameter('sod_qty_figure'),
                'sod_production_number' => $this->getStringParameter('sod_production_number'),
                'sod_serial_number' => $this->getStringParameter('sod_serial_number'),
                'sod_remark' => $this->getStringParameter('sod_remark'),
            ];
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doInsertTransaction($sodColVal);
        } elseif ($this->getFormAction() === 'doUpdateOpnameDetail') {
            $sodColVal = [
                'sod_qty_figure' => $this->getFloatParameter('sod_qty_figure_upd'),
                'sod_remark' => $this->getStringParameter('sod_remark_upd'),
            ];
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doUpdateTransaction($this->getIntParameter('sod_id_upd'), $sodColVal);
        } elseif ($this->getFormAction() === 'doDeleteOpnameDetail') {
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doDeleteTransaction($this->getIntParameter('sod_id_del'));
        } elseif ($this->getFormAction() === 'doActionEndOpname') {
            # Update start Opname
            $sopColVal = [
                'sop_end_on' => date('Y-m-d H:i:s'),
            ];
            $sopDao = new StockOpnameDao();
            $sopDao->doUpdateTransaction($this->getIntParameter('sop_id'), $sopColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
        } elseif ($this->getFormAction() === 'doActionDocument') {
            # Update job Action
            $this->doUpdateJobAction();
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

        return StockOpnameDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setSopHiddenData();
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getCustomerFieldSet());
        # General tabs.
        if ($this->isValidParameter('sop_start_on') === true) {
            $this->Tab->addPortlet('general', $this->getStorageSummary());
            if ($this->isValidParameter('sop_end_on') === false) {
                $this->Tab->setActiveTab('general', true);
            }
            $this->Tab->addPortlet('detail', $this->getStorageFieldSet());
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
        if ($this->getFormAction() === 'doActionStartOpname') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doInsertOpnameDetail') {
            $this->Validation->checkRequire('sop_id');
            $this->Validation->checkRequire('sod_whs_id');
            $this->Validation->checkRequire('sod_gd_id');
            $this->Validation->checkRequire('sod_gdu_id');
            $this->Validation->checkRequire('sod_qty_figure');
            $this->Validation->checkFloat('sod_qty_figure');
            $this->Validation->checkMaxLength('sod_remark', 255);
            if ($this->getStringParameter('sod_gd_sn', 'N') === 'Y') {
                $this->Validation->checkFloat('sod_qty_figure', 1, 1);
                $this->Validation->checkRequire('sod_serial_number');
            }
            $this->Validation->checkUnique('sod_gd_id', 'stock_opname_detail', [
                'sod_id' => $this->getIntParameter('sod_id')
            ], [
                'sod_sop_id' => $this->getIntParameter('sop_id'),
                'sod_gd_id' => $this->getIntParameter('sod_gd_id'),
                'sod_whs_id' => $this->getIntParameter('sod_whs_id'),
                'sod_gdt_id' => $this->getIntParameter('sod_gdt_id'),
                'sod_gdu_id' => $this->getIntParameter('sod_gdu_id'),
                'sod_production_number' => $this->getStringParameter('sod_production_number'),
                'sod_serial_number' => $this->getStringParameter('sod_serial_number'),
                'sod_deleted_on' => null,
            ]);
        } elseif ($this->getFormAction() === 'doUpdateOpnameDetail') {
            $this->Validation->checkRequire('sod_id_upd');
            $this->Validation->checkRequire('sod_qty_figure_upd');
            $this->Validation->checkFloat('sod_qty_figure_upd');
            $this->Validation->checkMaxLength('sod_remark_upd', 255);
        } elseif ($this->getFormAction() === 'doDeleteOpnameDetail') {
            $this->Validation->checkRequire('sod_id_del');
        } elseif ($this->getFormAction() === 'doActionEndOpname') {
            $this->loadActionValidationRole();
        }
        parent::loadValidationRole();
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $planning = '';
        if ($this->isValidParameter('sop_date') === true) {
            if ($this->isValidParameter('sop_time') === true) {
                $planning = DateTimeParser::format($this->getStringParameter('sop_date') . ' ' . $this->getStringParameter('sop_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $planning = DateTimeParser::format($this->getStringParameter('sop_date'), 'Y-m-d', 'd M Y');
            }
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('sop_warehouse'),
            ],
            [
                'label' => Trans::getWord('planningDate'),
                'value' => $planning,
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->getStringParameter('jo_manager'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('detail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getCustomerFieldSet(): Portlet
    {
        $gdDao = new GoodsDao();
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('jo_customer'),
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->getStringParameter('jo_customer_ref'),
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->getStringParameter('jo_pic_customer'),
            ],
            [
                'label' => Trans::getWord('goods'),
                'value' => $gdDao->formatFullName($this->getStringParameter('sop_gd_category', ''), $this->getStringParameter('sop_gd_brand', ''), $this->getStringParameter('sop_gd_name', ''), $this->getStringParameter('sop_gd_sku', '')),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JoCustomerPtl', Trans::getWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        # Add Modal
        $modal = $this->getOpnameDetailProcessModal();
        $this->View->addModal($modal);

        $modalDelete = $this->getGoodsDetailDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new TableDatas('JoSodTbl');
        $table->setRowsPerPage(25);
        $table->setHeaderRow([
            'sod_whs_name' => Trans::getWord('storage'),
            'sod_production_number' => Trans::getWord('productionNumber'),
            'sod_serial_number' => Trans::getWord('serialNumber'),
            'sod_condition' => Trans::getWord('damageType'),
            'sod_quantity' => Trans::getWord('currentStock'),
            'sod_gdu_uom' => Trans::getWord('uom'),
            'sod_qty_figure' => Trans::getWord('stockFigure'),
            'qty_diff' => Trans::getWord('diffQuantity'),
            'sod_remark' => Trans::getWord('remark'),
        ]);

        $data = StockOpnameDetailDao::getByStockOpnameId($this->getIntParameter('sop_id'));
        $results = [];
        $i = 0;
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['sod_condition'] = $row['sod_gdt_code'] . '<br />' . $row['sod_gdt_description'];
            $diff = (float)$row['sod_qty_figure'] - (float)$row['sod_quantity'];
            $row['qty_diff'] = $number->doFormatFloat($diff);
            if ($diff > 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: orange; color: white; text-align: right;');
            } elseif ($diff < 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: red; color: white; text-align: right;');
            } else {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: green; color: white; text-align: right;');
            }
            if ($row['sod_qty_figure'] === null) {
                $table->addCellAttribute('sod_qty_figure', $i, 'style', 'background-color: #405467;');
            }
            $btnProcess = new ModalButton('btnProSod' . $row['sod_id'], '', $modal->getModalId());
            $btnProcess->setIcon(Icon::CheckSquareO)->btnSuccess()->viewIconOnly();
            $btnProcess->setEnableCallBack('stockOpnameDetail', 'getByReferenceForUpdate');
            $btnProcess->addParameter('sod_id', $row['sod_id']);
            $row['sod_action'] = $btnProcess;

            if ((float)$row['sod_quantity'] === 0.0) {
                $btnDel = new ModalButton('btnDelSod' . $row['sod_id'], '', $modalDelete->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('stockOpnameDetail', 'getByReferenceForDelete');
                $btnDel->addParameter('sod_id', $row['sod_id']);
                $row['sod_action'] .= ' ' . $btnDel;
            }

            $i++;
            $results[] = $row;
        }
        $table->addRows($results);
        $table->setColumnType('sod_quantity', 'float');
        $table->setColumnType('sod_qty_figure', 'float');
        $table->addColumnAttribute('sod_condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_production_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_whs_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_serial_number', 'style', 'text-align: center;');
        $table->setFooterType('sod_quantity', 'SUM');
        $table->setFooterType('sod_qty_figure', 'SUM');

        # Create a portlet box.
        $portlet = new Portlet('JoSodPtl', Trans::getWord('goods'));
        if ($this->isJobDeleted() === false && $this->isValidParameter('sop_end_on') === false) {
            $table->addColumnAtTheEnd('sod_action', Trans::getWord('action'));
            $table->addColumnAttribute('sod_action', 'style', 'text-align: center;');

            $modalInsert = $this->getOpnameDetailInsertModal();
            $this->View->addModal($modalInsert);
            # add new button
            $btnCpMdl = new ModalButton('btnJoSodMdl', Trans::getWord('addStockFigure'), $modalInsert->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get summary of opname stock.
     *
     * @return Portlet
     */
    protected function getStorageSummary(): Portlet
    {
        $table = new Table('JoSodSummaryTbl');
        $table->setHeaderRow([
            'whs_name' => Trans::getWord('storage'),
            'sod_production_number' => Trans::getWord('productionNumber'),
            'sod_quantity' => Trans::getWord('currentStock'),
            'sod_qty_figure' => Trans::getWord('stockFigure'),
            'qty_diff' => Trans::getWord('diffQuantity'),
            'sod_gdu_uom' => Trans::getWord('uom'),
        ]);
        $data = StockOpnameDetailDao::getSummaryByStockOpnameId($this->getIntParameter('sop_id'));
        $results = [];
        $i = 0;
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $diff = abs((float)($row['sod_qty_figure'] ?? 0) - (float)$row['sod_quantity']);
            $row['qty_diff'] = $number->doFormatFloat($diff);
            if ($diff > 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: orange; color: white; text-align: right;');
            } elseif ($diff < 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: red; color: white; text-align: right;');
            } else {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: green; color: white; text-align: right;');
            }
            if ($row['sod_qty_figure'] === null) {
                $table->addCellAttribute('sod_qty_figure', $i, 'style', 'background-color: #405467;');
            }

            $i++;
            $results[] = $row;
        }
        $table->addRows($results);
        $table->setColumnType('sod_quantity', 'float');
        $table->setColumnType('sod_qty_figure', 'float');
        $table->setColumnType('qty_diff', 'float');
        $table->addColumnAttribute('sod_production_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_whs_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_serial_number', 'style', 'text-align: center;');
        $table->setFooterType('sod_quantity', 'SUM');
        $table->setFooterType('sod_qty_figure', 'SUM');
        $table->setFooterType('qty_diff', 'SUM');

        # Create a portlet box.
        $portlet = new Portlet('JoSodPtl', Trans::getWord('goods'));

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getOpnameDetailInsertModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSodInsertMdl', Trans::getWord('stockFigure'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertOpnameDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertOpnameDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $storageField = $this->Field->getSingleSelect('warehouseStorage', 'sod_whs_name', $this->getParameterForModal('sod_whs_name', $showModal));
        $storageField->setHiddenField('sod_whs_id', $this->getParameterForModal('sod_whs_id', $showModal));
        $storageField->setEnableDetailButton(false);
        $storageField->setEnableNewButton(false);
        $storageField->addParameter('whs_wh_id', $this->getIntParameter('sop_wh_id', true));

        # Create Goods Field
        $gdField = $this->Field->getSingleSelectTable('goods', 'sod_goods', $this->getParameterForModal('sod_goods', $showModal), 'loadSingleSelectTableData');
        $gdField->setHiddenField('sod_gd_id', $this->getParameterForModal('sod_gd_id', $showModal));
        $gdField->setTableColumns([
            'gd_relation' => Trans::getWord('relation'),
            'gd_sku' => Trans::getWord('sku'),
            'gd_gdc_name' => Trans::getWord('category'),
            'gd_br_name' => Trans::getWord('brand'),
            'gd_name' => Trans::getWord('name'),
            'gd_required_sn' => Trans::getWord('requiredUniqueSn'),
        ]);
        $gdField->setFilters([
            'gd_relation' => Trans::getWord('relation'),
            'gd_sku' => Trans::getWord('sku'),
            'gd_gdc_name' => Trans::getWord('category'),
            'gd_br_name' => Trans::getWord('brand'),
            'gd_name' => Trans::getWord('name'),
        ]);

        $gdField->setAutoCompleteFields([
            'sod_gd_sku' => 'gd_sku',
            'sod_gd_sn' => 'gd_sn',
        ]);
        $gdField->setValueCode('gd_id');
        $gdField->setLabelCode('gd_full_name');

        $gdField->addParameter('gd_ss_id', $this->User->getSsId());
        $gdField->addParameter('gd_rel_id', $this->getIntParameter('jo_rel_id'));
        $gdField->addParameter('gd_id', $this->getIntParameter('sop_gd_id'));
        $gdField->setParentModal($modal->getModalId());
        $this->View->addModal($gdField->getModal());

        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'sod_gdu_uom', $this->getParameterForModal('sod_gdu_uom', $showModal));
        $unitField->setHiddenField('sod_gdu_id', $this->getParameterForModal('sod_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'sod_gd_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);

        # Create damage type Field
        $damageTypeField = $this->Field->getSingleSelect('goodsDamageType', 'sod_gdt_description', $this->getParameterForModal('sod_gdt_description', $showModal));
        $damageTypeField->setHiddenField('sod_gdt_id', $this->getParameterForModal('sod_gdt_id', $showModal));
        $damageTypeField->addParameter('gdt_ss_id', $this->User->getSsId());
        $damageTypeField->setEnableDetailButton(false);
        $damageTypeField->setEnableNewButton(false);

        $skuField = $this->Field->getText('sod_gd_sku', $this->getParameterForModal('sod_gd_sku', $showModal));
        $skuField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $storageField, true);
        $fieldSet->addField(Trans::getWord('goods'), $gdField, true);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('stockFigure'), $this->Field->getNumber('sod_qty_figure', $this->getParameterForModal('sod_qty_figure', $showModal)), true);
        $fieldSet->addField(Trans::getWord('productionNumber'), $this->Field->getText('sod_production_number', $this->getParameterForModal('sod_production_number', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('sod_serial_number', $this->getParameterForModal('sod_serial_number', $showModal)));
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getTextArea('sod_remark', $this->getParameterForModal('sod_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sod_gd_sn', $this->getParameterForModal('sod_gd_sn', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getOpnameDetailProcessModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSodProMdl', Trans::getWord('stockFigure'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateOpnameDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateOpnameDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);


        $storageField = $this->Field->getText('sod_whs_name_upd', $this->getParameterForModal('sod_whs_name_upd', $showModal));
        $storageField->setReadOnly();

        # Create goods Field
        $goodsField = $this->Field->getText('sod_goods_upd', $this->getParameterForModal('sod_goods_upd', $showModal));
        $goodsField->setReadOnly();

        # Create goods Field
        $skuField = $this->Field->getText('sod_gd_sku_upd', $this->getParameterForModal('sod_gd_sku_upd', $showModal));
        $skuField->setReadOnly();

        # Create Unit Field
        $unitField = $this->Field->getText('sod_gdu_uom_upd', $this->getParameterForModal('sod_gdu_uom_upd', $showModal));
        $unitField->setReadOnly();

        # Create damage type Field
        $damageTypeField = $this->Field->getText('sod_gdt_description_upd', $this->getParameterForModal('sod_gdt_description_upd', $showModal));
        $damageTypeField->setReadOnly();

        $qtyField = $this->Field->getNumber('sod_quantity_upd', $this->getParameterForModal('sod_quantity_upd', $showModal));
        $qtyField->setReadOnly();

        $productionField = $this->Field->getText('sod_production_number_upd', $this->getParameterForModal('sod_production_number_upd', $showModal));
        $productionField->setReadOnly();

        $serialField = $this->Field->getText('sod_serial_number_upd', $this->getParameterForModal('sod_serial_number_upd', $showModal));
        $serialField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $storageField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('currentStock'), $qtyField);
        $fieldSet->addField(Trans::getWord('stockFigure'), $this->Field->getNumber('sod_qty_figure_upd', $this->getParameterForModal('sod_qty_figure_upd', $showModal)), true);
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('productionNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $serialField);
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getTextArea('sod_remark_upd', $this->getParameterForModal('sod_remark_upd', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sod_id_upd', $this->getParameterForModal('sod_id_upd', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    protected function getGoodsDetailDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSodDelMdl', Trans::getWord('deleteStockFigure'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteOpnameDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteOpnameDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);


        $storageField = $this->Field->getText('sod_whs_name_del', $this->getParameterForModal('sod_whs_name_del', $showModal));
        $storageField->setReadOnly();

        # Create goods Field
        $goodsField = $this->Field->getText('sod_goods_del', $this->getParameterForModal('sod_goods_del', $showModal));
        $goodsField->setReadOnly();

        # Create goods Field
        $skuField = $this->Field->getText('sod_gd_sku_del', $this->getParameterForModal('sod_gd_sku_del', $showModal));
        $skuField->setReadOnly();

        # Create Unit Field
        $unitField = $this->Field->getText('sod_gdu_uom_del', $this->getParameterForModal('sod_gdu_uom_del', $showModal));
        $unitField->setReadOnly();

        # Create damage type Field
        $damageTypeField = $this->Field->getText('sod_gdt_description_del', $this->getParameterForModal('sod_gdt_description_del', $showModal));
        $damageTypeField->setReadOnly();

        $qtyField = $this->Field->getNumber('sod_quantity_del', $this->getParameterForModal('sod_quantity_del', $showModal));
        $qtyField->setReadOnly();

        $qtyFigureField = $this->Field->getNumber('sod_qty_figure_del', $this->getParameterForModal('sod_qty_figure_del', $showModal));
        $qtyFigureField->setReadOnly();

        $productionField = $this->Field->getText('sod_production_number_del', $this->getParameterForModal('sod_production_number_del', $showModal));
        $productionField->setReadOnly();

        $serialField = $this->Field->getText('sod_serial_number_del', $this->getParameterForModal('sod_serial_number_del', $showModal));
        $serialField->setReadOnly();

        $remarkField = $this->Field->getText('sod_remark_del', $this->getParameterForModal('sod_remark_del', $showModal));
        $remarkField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $storageField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('stockFigure'), $qtyFigureField);
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('productionNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $serialField);
        $fieldSet->addField(Trans::getWord('remark'), $remarkField);
        $fieldSet->addHiddenField($this->Field->getHidden('sod_id_del', $this->getParameterForModal('sod_id_del', $showModal)));
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
        if ($this->isValidParameter('sop_end_on') === true) {
            $pdfButton = new PdfButton('SoPrint', Trans::getWord('printPdf'), 'stockopname');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    protected function loadCurrentStorageStockData(): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.stock <> 0)';
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('sop_wh_id') . ')';
        $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        if ($this->isValidParameter('sop_gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('sop_gd_id') . ')';
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jid.jid_gdu_id, jid.jid_gd_id, jid.jid_lot_number,
                        jid.jid_whs_id, jid.jid_gdt_id, jis.stock, whs.whs_name, jid.jid_serial_number
                FROM job_inbound_detail AS jid INNER JOIN
                    goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN
                      (SELECT jis_jid_id, SUM(jis_quantity) as stock
                        FROM job_inbound_stock
                        WHERE (jis_deleted_on IS NULL)
                        GROUP BY jis_jid_id) jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $query .= ' GROUP BY jid.jid_id, jid.jid_gdu_id, jid.jid_gd_id, jid.jid_lot_number, jid.jid_whs_id, jid.jid_gdt_id,
                            jis.stock, whs.whs_name, jid.jid_serial_number';
        $query .= ' ORDER BY jid.jid_gdt_id DESC, whs.whs_name, jid.jid_gd_id, jid.jid_lot_number, jid.jid_serial_number, jid.jid_id';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult);
            foreach ($data as $row) {
                $result[] = [
                    'sod_sop_id' => $this->getIntParameter('sop_id'),
                    'sod_whs_id' => $row['jid_whs_id'],
                    'sod_gd_id' => $row['jid_gd_id'],
                    'sod_production_number' => $row['jid_lot_number'],
                    'sod_serial_number' => $row['jid_serial_number'],
                    'sod_quantity' => (float)$row['stock'],
                    'sod_gdu_id' => $row['jid_gdu_id'],
                    'sod_gdt_id' => $row['jid_gdt_id']
                ];
            }
        }
        return $result;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setSopHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('sop_id', $this->getIntParameter('sop_id'));
        $content .= $this->Field->getHidden('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $content .= $this->Field->getHidden('sop_wh_id', $this->getIntParameter('sop_wh_id'));
        $content .= $this->Field->getHidden('sop_gd_id', $this->getIntParameter('sop_gd_id'));
        $this->View->addContent('SopHdFld', $content);
    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        # Keep this function empty
    }
}
