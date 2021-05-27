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
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Viewer\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobStockMovement page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockMovement extends BaseJobOrder
{

    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    protected $Detail = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockMovement', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateMovementDetail') {
            $volume = null;
            if (($this->isValidParameter('jmd_length') === true) && ($this->isValidParameter('jmd_width') === true) && ($this->isValidParameter('jmd_height') === true)) {
                $volume = $this->getFloatParameter('jmd_length') * $this->getFloatParameter('jmd_width') * $this->getFloatParameter('jmd_height');
            }
            $jmdColVal = [
                'jmd_jm_id' => $this->getIntParameter('jm_id'),
                'jmd_jid_id' => $this->getIntParameter('jmd_jid_id'),
                'jmd_gdu_id' => $this->getIntParameter('jmd_gdu_id'),
                'jmd_quantity' => $this->getFloatParameter('jmd_quantity'),
                'jmd_length' => $this->getFloatParameter('jmd_length'),
                'jmd_width' => $this->getFloatParameter('jmd_width'),
                'jmd_height' => $this->getFloatParameter('jmd_height'),
                'jmd_volume' => $volume,
                'jmd_weight' => $this->getFloatParameter('jmd_weight'),
            ];
            if ($this->isValidParameter('jmd_gdt_id') === true) {
                $jmdColVal['jmd_gdt_id'] = $this->getIntParameter('jmd_gdt_id');
                $jmdColVal['jmd_gdt_remark'] = $this->getStringParameter('jmd_gdt_remark');
                $jmdColVal['jmd_gcd_id'] = $this->getIntParameter('jmd_gcd_id');
                $jmdColVal['jmd_gcd_remark'] = $this->getStringParameter('jmd_gcd_remark');
            }
            $jmdDao = new JobMovementDetailDao();
            if ($this->isValidParameter('jmd_id') === true) {
                $jmdDao->doUpdateTransaction($this->getIntParameter('jmd_id'), $jmdColVal);
            } else {
                $jmdDao->doInsertTransaction($jmdColVal);
            }
        } else if ($this->getFormAction() === 'doDeleteMovementDetail') {
            $jmdDao = new JobMovementDetailDao();
            $jmdDao->doDeleteTransaction($this->getIntParameter('jmd_id_del'));
        } else if ($this->getFormAction() === 'doActionStartMove') {
            # Update start Job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
        } else if ($this->getFormAction() === 'doActionEndMove') {
            # load good damage that will be stored.
            $data = JobMovementDetailDao::loadDataByJmId($this->getIntParameter('jm_id'));
            $jidDao = new JobInboundDetailDao();
            $jisDao = new JobInboundStockDao();
            $jmdDao = new JobMovementDetailDao();
            foreach ($data as $row) {
                $jmdColVal = [];
                # Decrease the stock for origin storage.
                $jisColVal = [
                    'jis_jid_id' => $row['jmd_jid_id'],
                    'jis_quantity' => (float)$row['jmd_quantity'] * -1,
                ];
                $jisDao->doInsertTransaction($jisColVal);
                $jmdColVal['jmd_jis_id'] = $jisDao->getLastInsertId();

                # insert new job outbound detail for destination moved.
                $jidColVal = [
                    'jid_ji_id' => $row['jmd_jid_ji_id'],
                    'jid_jir_id' => $row['jmd_jid_jir_id'],
                    'jid_whs_id' => $row['jmd_whs_id'],
                    'jid_quantity' => (float)$row['jmd_quantity'],
                    'jid_gd_id' => $row['jmd_gd_id'],
                    'jid_gdu_id' => $row['jmd_gdu_id'],
                    'jid_lot_number' => $row['jmd_jid_lot_number'],
                    'jid_serial_number' => $row['jmd_jid_serial_number'],
                    'jid_packing_number' => $row['jmd_jid_packing_number'],
                    'jid_length' => $row['jmd_length'],
                    'jid_width' => $row['jmd_width'],
                    'jid_height' => $row['jmd_height'],
                    'jid_volume' => $row['jmd_volume'],
                    'jid_weight' => $row['jmd_weight'],
                    'jid_adjustment' => 'Y',
                    'jid_gdt_id' => $row['jmd_gdt_id'],
                    'jid_gdt_remark' => $row['jmd_gdt_remark'],
                    'jid_gcd_id' => $row['jmd_gcd_id'],
                    'jid_gcd_remark' => $row['jmd_gcd_remark'],
                ];
                $jidDao->doInsertTransaction($jidColVal);
                $jmdColVal['jmd_jid_new_id'] = $jidDao->getLastInsertId();

                # insert the job inbound stock for destination JID
                $jisColVal = [
                    'jis_jid_id' => $jidDao->getLastInsertId(),
                    'jis_quantity' => (float)$row['jmd_quantity'],
                ];
                $jisDao->doInsertTransaction($jisColVal);
                $jmdColVal['jmd_jis_new_id'] = $jisDao->getLastInsertId();

                # Update current job movement detail.
                $jmdDao->doUpdateTransaction($row['jmd_id'], $jmdColVal);
            }
            $jmColVal = [
                'jm_complete_on' => date('Y-m-d H:i:s'),
            ];
            $jmDao = new JobMovementDao();
            $jmDao->doUpdateTransaction($this->getIntParameter('jm_id'), $jmColVal);

            # Update job Action
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
        return JobMovementDao::getByJobIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        # load detail data
        $this->Detail = JobMovementDetailDao::loadDataByJmId($this->getIntParameter('jm_id'));

        parent::loadForm();
        $this->setJmHiddenData();

        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        $this->Tab->addPortlet('general', $this->getCustomerFieldSet());
        $this->Tab->addPortlet('general', $this->getGoodsDetailFieldSet());
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
        if ($this->getFormAction() === 'doUpdateMovementDetail') {
            $this->Validation->checkRequire('jm_id');
            $this->Validation->checkRequire('jmd_jid_id');
            $this->Validation->checkRequire('jmd_gdu_id');
            $this->Validation->checkRequire('jmd_quantity');
            if ($this->isValidParameter('jmd_jid_stock') === true) {
                $this->Validation->checkFloat('jmd_quantity', 0.1, $this->getFloatParameter('jmd_jid_stock'));
            }
            $this->Validation->checkUnique('jmd_jid_id', 'job_movement_detail', [
                'jmd_id' => $this->getIntParameter('jmd_id'),
            ], [
                'jmd_jm_id' => $this->getIntParameter('jm_id'),
                'jmd_deleted_on' => null,
            ]);
            if ($this->isValidParameter('jmd_gdt_id') === true) {
                $this->Validation->checkRequire('jmd_gcd_id');
                if ($this->getStringParameter('jmd_gd_tonnage', 'N') === 'Y') {
                    $this->Validation->checkRequire('jmd_weight');
                }
                if ($this->getStringParameter('jmd_gd_cbm', 'N') === 'Y') {
                    $this->Validation->checkRequire('jmd_length');
                    $this->Validation->checkRequire('jmd_width');
                    $this->Validation->checkRequire('jmd_height');
                }
            }
            if ($this->isValidParameter('jmd_gcd_id') === true) {
                $this->Validation->checkRequire('jmd_gdt_id');
            }
            if ($this->isValidParameter('jmd_length') === true) {
                $this->Validation->checkFloat('jmd_length');
            }
            if ($this->isValidParameter('jmd_width') === true) {
                $this->Validation->checkFloat('jmd_width');
            }
            if ($this->isValidParameter('jmd_height') === true) {
                $this->Validation->checkFloat('jmd_height');
            }
            if ($this->isValidParameter('jmd_weight') === true) {
                $this->Validation->checkFloat('jmd_weight');
            }
        } else if ($this->getFormAction() === 'doDeleteMovementDetail') {
            $this->Validation->checkRequire('jmd_id_del');
        } else if ($this->getFormAction() === 'doActionStartMove') {
            $this->loadActionValidationRole();
        } else if ($this->getFormAction() === 'doActionEndMove') {
            $this->loadActionValidationRole();
        }
        parent::loadValidationRole();
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('jm_wh_name'),
            ],
            [
                'label' => Trans::getWord('originStorage'),
                'value' => $this->getStringParameter('jm_whs_name'),
            ],
            [
                'label' => Trans::getWord('destinationStorage'),
                'value' => $this->getStringParameter('jm_destination_storage'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JmGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getCustomerFieldSet(): Portlet
    {
        $time = '';
        if ($this->isValidParameter('jm_date') === true) {
            if ($this->isValidParameter('jm_time') === true) {
                $time = DateTimeParser::format($this->getStringParameter('jm_date') . ' ' . $this->getStringParameter('jm_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $time = DateTimeParser::format($this->getStringParameter('jm_date'), 'Y-m-d', 'd M Y');
            }
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('planningDate'),
                'value' => $time,
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->getStringParameter('jo_manager'),
            ],
            [
                'label' => Trans::getWord('remark'),
                'value' => $this->getStringParameter('jm_remark'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JmRemarkPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsDetailFieldSet(): Portlet
    {
        $table = new Table('JoJmdTbl');
        $table->setHeaderRow([
            'jmd_gd_sku' => Trans::getWord('sku'),
            'jmd_gd_name' => Trans::getWord('goods'),
            'jmd_jid_lot_number' => Trans::getWord('lotNumber'),
            'jmd_jid_serial_number' => Trans::getWord('serialNumber'),
            'jmd_jir_condition' => Trans::getWord('condition'),
            'jmd_quantity' => Trans::getWord('quantity'),
            'jmd_gdu_uom' => Trans::getWord('uom'),
            'jmd_gdt_code' => Trans::getWord('damageType'),
            'jmd_gcd_code' => Trans::getWord('causeDamage'),
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
        ]);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($this->Detail as $row) {
            $volume = (float)$row['jmd_jid_volume'];
            if (empty($row['jmd_volume']) === false) {
                $volume = (float)$row['jmd_volume'];
            }
            $weight = (float)$row['jmd_jid_weight'];
            if (empty($row['jmd_weight']) === false) {
                $weight = (float)$row['jmd_weight'];
            }
            $row['jmd_gd_name'] = $gdDao->formatFullName($row['jmd_gdc_name'], $row['jmd_br_name'], $row['jmd_gd_name']);
            if (empty($row['jmd_jid_gdt_id']) === false) {
                $row['jmd_jir_condition'] = new LabelDanger(Trans::getWord('damage'));
            } else {
                $row['jmd_jir_condition'] = new LabelSuccess(Trans::getWord('good'));
            }

            $row['total_weight'] = (float)$row['jmd_quantity'] * $weight;
            $row['total_volume'] = (float)$row['jmd_quantity'] * $volume;
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jmd_quantity', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setFooterType('jmd_quantity', 'SUM');
        $table->addColumnAttribute('jmd_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_jid_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_jir_condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gdu_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gdt_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gcd_code', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJmdPtl', Trans::getWord('goods'));
        if ($this->isJobDeleted() === false && $this->isValidParameter('jo_start_on') === true && $this->isValidParameter('jm_complete_on') === false) {
            $modal = $this->getGoodsDetailModal();
            $this->View->addModal($modal);
            $table->setUpdateActionByModal($modal, 'jobMovementDetail', 'getByReference', ['jmd_id']);

            $modalDelete = $this->getGoodsDetailDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jobMovementDetail', 'getByReferenceForDelete', ['jmd_id']);
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
     * @param bool $showModal To store the trigger to open modal when page reloaded.
     * @param string $modalId To store the modal id for fieldset.
     *
     * @return FieldSet
     */
    protected function getGoodsModalFieldSet(bool $showModal, string $modalId): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create goods Field
        $goodsField = $this->Field->getSingleSelectTable('jobInboundDetail', 'jmd_gd_name', $this->getParameterForModal('jmd_gd_name', $showModal), 'loadDataForMovement');
        $goodsField->setHiddenField('jmd_jid_id', $this->getParameterForModal('jmd_jid_id', $showModal));
        $goodsField->setTableColumns([
            'jid_gd_sku' => Trans::getWord('sku'),
            'jid_goods' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_stock' => Trans::getWord('currentStock'),
            'jid_gdu_uom' => Trans::getWord('uom'),
        ]);
        $goodsField->setFilters([
            'gd_sku' => Trans::getWord('sku'),
            'gd_name' => Trans::getWord('goods'),
            'lot_number' => Trans::getWord('lotNumber'),
            'serial_number' => Trans::getWord('serialNumber'),
        ]);
        $goodsField->setAutoCompleteFields([
            'jmd_gdu_id' => 'jid_gdu_id',
            'jmd_gd_sku' => 'jid_gd_sku',
            'jmd_gdu_uom' => 'jid_gdu_uom',
            'jmd_jid_stock' => 'jid_stock',
            'jmd_jid_stock_number' => 'jid_stock_number',
            'jmd_jid_lot_number' => 'jid_lot_number',
            'jmd_jid_serial_number' => 'jid_serial_number',
            'jmd_jid_gdt_id' => 'jid_gdt_id',
            'jmd_jid_gdt_remark' => 'jid_gdt_remark',
            'jmd_jid_gcd_id' => 'jid_gcd_id',
            'jmd_jid_gcd_remark' => 'jid_gcd_remark',
            'jmd_gd_tonnage' => 'jid_gd_tonnage',
            'jmd_gd_cbm' => 'jid_gd_cbm',
        ]);
        $goodsField->setValueCode('jid_id');
        $goodsField->setLabelCode('jid_goods');
        $goodsField->addParameter('whs_id', $this->getIntParameter('jm_whs_id'));
        $goodsField->addParameter('jm_id', $this->getIntParameter('jm_id'));
        $goodsField->addParameter('ss_id', $this->User->getSsId());
        $goodsField->setParentModal($modalId);
        $this->View->addModal($goodsField->getModal());

        # set readonly field.
        $skuField = $this->Field->getText('jmd_gd_sku', $this->getParameterForModal('jmd_gd_sku', $showModal));
        $skuField->setReadOnly();
        $unitField = $this->Field->getText('jmd_gdu_uom', $this->getParameterForModal('jmd_gdu_uom', $showModal));
        $unitField->setReadOnly();
        $stockField = $this->Field->getNumber('jmd_jid_stock', $this->getParameterForModal('jmd_jid_stock', $showModal));
        $stockField->setReadOnly();
        $productionField = $this->Field->getText('jmd_jid_lot_number', $this->getParameterForModal('jmd_jid_lot_number', $showModal));
        $productionField->setReadOnly();
        $serialField = $this->Field->getText('jmd_jid_serial_number', $this->getParameterForModal('jmd_jid_serial_number', $showModal));
        $serialField->setReadOnly();

        # Create damage type Field
        $damageTypeField = $this->Field->getSingleSelect('goodsDamageType', 'jmd_gdt_description', $this->getParameterForModal('jmd_gdt_description', $showModal));
        $damageTypeField->setHiddenField('jmd_gdt_id', $this->getParameterForModal('jmd_gdt_id', $showModal));
        $damageTypeField->addParameter('gdt_ss_id', $this->User->getSsId());
        $damageTypeField->setEnableDetailButton(false);
        $damageTypeField->setEnableNewButton(false);

        # Create damage type Field
        $damageCauseField = $this->Field->getSingleSelect('goodsCauseDamage', 'jmd_gcd_description', $this->getParameterForModal('jmd_gcd_description', $showModal));
        $damageCauseField->setHiddenField('jmd_gcd_id', $this->getParameterForModal('jmd_gcd_id', $showModal));
        $damageCauseField->addParameter('gcd_ss_id', $this->User->getSsId());
        $damageCauseField->setEnableDetailButton(false);
        $damageCauseField->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('currentStock'), $stockField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jmd_quantity', $this->getParameterForModal('jmd_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('lotNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $serialField);
        $fieldSet->addField(Trans::getWord('damageTypeRemark'), $this->Field->getText('jmd_gdt_remark', $this->getParameterForModal('jmd_gdt_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('causeDamage'), $damageCauseField);
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('jmd_length', $this->getParameterForModal('jmd_length', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamageRemark'), $this->Field->getText('jmd_gcd_remark', $this->getParameterForModal('jmd_gcd_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('jmd_height', $this->getParameterForModal('jmd_height', $showModal)));
        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('jmd_weight', $this->getParameterForModal('jmd_weight', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('jmd_width', $this->getParameterForModal('jmd_width', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_id', $this->getParameterForModal('jmd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_gdu_id', $this->getParameterForModal('jmd_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_jid_gdt_id', $this->getParameterForModal('jmd_jid_gdt_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_jid_gdt_remark', $this->getParameterForModal('jmd_jid_gdt_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_jid_gcd_id', $this->getParameterForModal('jmd_jid_gcd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_jid_gcd_remark', $this->getParameterForModal('jmd_jid_gcd_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_gd_tonnage', $this->getParameterForModal('jmd_gd_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_gd_cbm', $this->getParameterForModal('jmd_gd_cbm', $showModal)));
        return $fieldSet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getGoodsDetailModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JmJmdMdl', Trans::getWord('goods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateMovementDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateMovementDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $modal->addFieldSet($this->getGoodsModalFieldSet($showModal, $modal->getModalId()));

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
        $modal = new Modal('JmJmdDelMdl', Trans::getWord('deleteGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteMovementDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteMovementDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jmd_gd_name_del', $this->getParameterForModal('jmd_gd_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('brand'), $this->Field->getText('jmd_br_name_del', $this->getParameterForModal('jmd_br_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('category'), $this->Field->getText('jmd_gdc_name_del', $this->getParameterForModal('jmd_gdc_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jmd_jid_lot_number_del', $this->getParameterForModal('jmd_jid_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jmd_jid_serial_number_del', $this->getParameterForModal('jmd_jid_serial_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('currentStock'), $this->Field->getNumber('jmd_jid_stock_del', $this->getParameterForModal('jmd_jid_stock_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jmd_gdu_uom_del', $this->getParameterForModal('jmd_gdu_uom_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jmd_quantity_del', $this->getParameterForModal('jmd_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('damageType'), $this->Field->getText('jmd_gdt_description_del', $this->getParameterForModal('jmd_gdt_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamage'), $this->Field->getText('jmd_gcd_description_del', $this->getParameterForModal('jmd_gcd_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('damageTypeRemark'), $this->Field->getText('jmd_gdt_remark_del', $this->getParameterForModal('jmd_gdt_remark_del', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamageRemark'), $this->Field->getText('jmd_gcd_remark_del', $this->getParameterForModal('jmd_gcd_remark_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jmd_id_del', $this->getParameterForModal('jmd_id_del', $showModal)));
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
        if ($this->isValidParameter('jm_complete_on') === true) {
            $pdfButton = new PdfButton('JmPrint', Trans::getWord('printPdf'), 'stockmovement');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        # Keep this function empty
        # Override parent function so system will not load goods data.
    }
    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJmHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jm_id', $this->getIntParameter('jm_id'));
        $content .= $this->Field->getHidden('jm_wh_id', $this->getIntParameter('jm_wh_id'));
        $content .= $this->Field->getHidden('jm_whs_id', $this->getIntParameter('jm_whs_id'));
        $this->View->addContent('JmHdFld', $content);
    }
}
