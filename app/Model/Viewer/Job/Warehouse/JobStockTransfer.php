<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Warehouse\JobStockTransferGoodsDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to handle the creation of detail JobStockTransfer page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobStockTransfer extends AbstractViewerModel
{
    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockTransfer', 'jtr_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateGoods') {
            $colVal = [
                'jtg_jtr_id' => $this->getDetailReferenceValue(),
                'jtg_gd_id' => $this->getIntParameter('jtg_gd_id'),
                'jtg_gdu_id' => $this->getIntParameter('jtg_gdu_id'),
                'jtg_quantity' => $this->getFloatParameter('jtg_quantity'),
                'jtg_production_number' => $this->getStringParameter('jtg_production_number')
            ];
            $jtgDao = new JobStockTransferGoodsDao();
            if ($this->isValidParameter('jtg_id') === true) {
                $jtgDao->doUpdateTransaction($this->getIntParameter('jtg_id'), $colVal);
            } else {
                $jtgDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoods') {
            $jtgDao = new JobStockTransferGoodsDao();
            $jtgDao->doDeleteTransaction($this->getIntParameter('jtg_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobStockTransferDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->overridePageTitle();
        # Load goods data.
        $this->loadGoodsData();
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getTransporterFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseOriginFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseDestinationFieldSet());
        $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
        if ($this->isValidParameter('jtr_publish_on') === true) {
            $jobOutboundData = JobOutboundDao::getByJoIdAndSystem($this->getIntParameter('jtr_job_jo_id'), $this->User->getSsId());
            $this->Tab->addPortlet('outbound', $this->getJobOutboundCustomerFieldSet($jobOutboundData));
            $this->Tab->addPortlet('outbound', $this->getJobOutboundFieldSet($jobOutboundData));
            $jobInboundData = JobInboundDao::getByJobOrderAndSystemSetting($this->getIntParameter('jtr_ji_jo_id'), $this->User->getSsId());
            $this->Tab->addPortlet('inbound', $this->getJobInboundCustomerFieldSet($jobInboundData));
            $this->Tab->addPortlet('inbound', $this->getJobInboundFieldSet($jobInboundData));
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateGoods') {
            $this->Validation->checkRequire('jtg_gd_id');
            $this->Validation->checkRequire('jtg_gdu_id');
            $this->Validation->checkFloat('jtg_quantity');
            $this->Validation->checkUnique('jtg_gd_id', 'job_stock_transfer_goods', [
                'jtg_id' => $this->getIntParameter('jtg_id')
            ], [
                'jtg_jtr_id' => $this->getDetailReferenceValue()
            ]);
            if ($this->isValidParameter('jtg_production_number')) {
                $this->Validation->checkRequire('jtg_production_number', 3, 255);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoods') {
            $this->Validation->checkRequire('jtg_id_del');
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
                'label' => Trans::getWhsWord('customer'),
                'value' => $this->getStringParameter('jtr_rel_name'),
            ],
            [
                'label' => Trans::getWhsWord('picCustomer'),
                'value' => $this->getStringParameter('jtr_pic_name'),
            ],
            [
                'label' => Trans::getWhsWord('manager'),
                'value' => $this->getStringParameter('jtr_us_name'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobGeneralPtl', Trans::getWhsWord('customer'));
        $portlet->addText($content);
        $portlet->addText($this->Field->getHidden('jtr_rel_id', $this->getIntParameter('jtr_rel_id')));
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the transporter Field Set.
     *
     * @return Portlet
     */
    private function getTransporterFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('transporter'),
                'value' => $this->getStringParameter('jtr_transporter_name'),
            ],
            [
                'label' => Trans::getWhsWord('driver'),
                'value' => $this->getStringParameter('jtr_driver'),
            ],
            [
                'label' => Trans::getWhsWord('driverPhone'),
                'value' => $this->getStringParameter('jtr_driver_phone'),
            ],
            [
                'label' => Trans::getWhsWord('truckNumber'),
                'value' => $this->getStringParameter('jtr_truck_plate'),
            ],
            [
                'label' => Trans::getWhsWord('containerNumber'),
                'value' => $this->getStringParameter('jtr_container_number'),
            ],
            [
                'label' => Trans::getWhsWord('sealNumber'),
                'value' => $this->getStringParameter('jtr_seal_number'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobTransporterPtl', Trans::getWhsWord('transporter'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the warehouse origin Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseOriginFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('warehouse'),
                'value' => $this->getStringParameter('jtr_who_name'),
            ],
            [
                'label' => Trans::getWhsWord('manager'),
                'value' => $this->getStringParameter('jtr_who_us_name'),
            ],
            [
                'label' => Trans::getWhsWord('planningDate'),
                'value' => $this->getStringParameter('jtr_who_date'),
            ],
            [
                'label' => Trans::getWhsWord('planningTime'),
                'value' => $this->getStringParameter('jtr_who_time'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobWhOriginPtl', Trans::getWhsWord('warehouseOrigin'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the warehouse destination Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseDestinationFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('warehouse'),
                'value' => $this->getStringParameter('jtr_whd_name'),
            ],
            [
                'label' => Trans::getWhsWord('manager'),
                'value' => $this->getStringParameter('jtr_whd_us_name'),
            ],
            [
                'label' => Trans::getWhsWord('planningDate'),
                'value' => $this->getStringParameter('jtr_whd_date'),
            ],
            [
                'label' => Trans::getWhsWord('planningTime'),
                'value' => $this->getStringParameter('jtr_whd_time'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobWhDestinationPtl', Trans::getWhsWord('warehouseDestination'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $modal = $this->getGoodsModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getGoodsDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('JtgTbl');
        $table->setHeaderRow([
            'jtg_sku' => Trans::getWhsWord('sku'),
            'jtg_goods' => Trans::getWhsWord('goods'),
            'jtg_production_number' => Trans::getWhsWord('productionNumber'),
            'jtg_quantity' => Trans::getWhsWord('qtyPlanning'),
            'jtg_unit' => Trans::getWhsWord('uom'),
        ]);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            $row['jtg_goods'] = $gdDao->formatFullName($row['jtg_gdc_name'], $row['jtg_br_name'], $row['jtg_gd_name']);
            $btnUpdate = new ModalButton('btnJtgEdtMdl' . $row['jtg_id'], '', $modal->getModalId());
            $btnUpdate->setEnableCallBack('jobStockTransferGoods', 'getStockTransferGoodsById');
            $btnUpdate->addParameter('jtg_id', $row['jtg_id']);
            $btnUpdate->setIcon(Icon::Pencil)->btnPrimary()->viewIconOnly();
            $row['jtg_action'] = $btnUpdate;
            $btnDelete = new ModalButton('btnJtgDelMdl' . $row['jtg_id'], '', $modalDelete->getModalId());
            $btnDelete->setEnableCallBack('jobStockTransferGoods', 'getStockTransferGoodsByIdForDelete');
            $btnDelete->addParameter('jtg_id', $row['jtg_id']);
            $btnDelete->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
            $row['jtg_action'] .= ' ' . $btnDelete;
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jtg_quantity', 'float');
        $table->setFooterType('jtg_quantity', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoJtgPtl', Trans::getWhsWord('goods'));
        if ($this->isValidParameter('jtr_publish_on') === false && $this->isValidParameter('jtr_deleted_on') === false) {
            $table->addColumnAtTheEnd('jtg_action', Trans::getWhsWord('action'));
            $table->addColumnAttribute('jtg_action', 'style', 'text-align: center;');
            $btnAddMdl = new ModalButton('btnJobJtgMdl', Trans::getWhsWord('addGoods'), $modal->getModalId());
            $btnAddMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnAddMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @param array $jobOutboundData
     *
     * @return Portlet
     */
    protected function getJobOutboundFieldSet(array $jobOutboundData): Portlet
    {
//        dd($jobOutboundData);
        $etaTime = '';
        if (empty($jobOutboundData['job_eta_date']) === false) {
            if (empty($jobOutboundData['job_eta_time']) === false) {
                $etaTime = DateTimeParser::format($jobOutboundData['job_eta_date'] . ' ' . $jobOutboundData['job_eta_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etaTime = DateTimeParser::format($jobOutboundData['job_eta_date'], 'Y-m-d', 'd M Y');
            }
        }
        $ataTime = '';
        if (empty($jobOutboundData['job_ata_date']) === false) {
            if (empty($jobOutboundData['job_ata_time']) === false) {
                $ataTime = DateTimeParser::format($jobOutboundData['job_ata_date'] . ' ' . $jobOutboundData['job_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $ataTime = DateTimeParser::format($jobOutboundData['job_ata_date'], 'Y-m-d', 'd M Y');
            }
        }
        $driver = $jobOutboundData['job_driver'];
        if (empty($jobOutboundData['job_driver_phone']) === false) {
            $driver .= ' / ' . $jobOutboundData['job_driver_phone'];
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('warehouse'),
                'value' => $jobOutboundData['job_warehouse'],
            ],
            [
                'label' => Trans::getWhsWord('eta'),
                'value' => $etaTime,
            ],
            [
                'label' => Trans::getWhsWord('ata'),
                'value' => $ataTime,
            ],
            [
                'label' => Trans::getWhsWord('consignee'),
                'value' => $jobOutboundData['job_consignee'],
            ],
            [
                'label' => Trans::getWhsWord('picConsignee'),
                'value' => $jobOutboundData['job_pic_consignee'],
            ],
            [
                'label' => Trans::getWhsWord('consigneeAddress'),
                'value' => $jobOutboundData['job_consignee_address'],
            ],
//            [
//                'label' => Trans::getWhsWord('transporter'),
//                'value' => $jobOutboundData['transporter'],
//            ],
            [
                'label' => Trans::getWhsWord('driver'),
                'value' => $driver,
            ],
            [
                'label' => Trans::getWhsWord('truckPlate'),
                'value' => $jobOutboundData['job_truck_number'],
            ],
            [
                'label' => Trans::getWhsWord('containerNumber'),
                'value' => $jobOutboundData['job_container_number'],
            ],
            [
                'label' => Trans::getWhsWord('sealNumber'),
                'value' => $jobOutboundData['job_seal_number'],
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobGeneralPtl', Trans::getWhsWord('jobDetail'));
        $portlet->addText($this->Field->getHidden('job_id', $jobOutboundData['job_id']));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);


        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @param array $jobOutboundData
     *
     * @return Portlet
     */
    protected function getJobOutboundCustomerFieldSet(array $jobOutboundData): Portlet
    {
        $currentAction = JobActionDao::getLastActiveActionByJobId($jobOutboundData['job_jo_id']);
        $joDao = new JobOrderDao();
        $data = [
            'is_deleted' => !empty($jobOutboundData['jo_deleted_on']),
            'is_hold' => !empty($jobOutboundData['joh_id']),
            'is_finish' => !empty($jobOutboundData['jo_finish_on']),
            'is_start' => !empty($jobOutboundData['jo_start_on']),
            'is_publish' => !empty($jobOutboundData['jo_publish_on']),
        ];
        if (empty($currentAction) === false) {
            $data = array_merge($data, [
                'jac_id' => $currentAction['jac_id'],
                'jae_style' => $currentAction['jac_style'],
                'jac_action' => $currentAction['jac_action'],
                'jae_description' => $currentAction['jae_description'],
                'jo_srt_id' => $currentAction['ac_srt_id'],
            ]);
        }
        $status = $joDao->generateStatus($data);
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('jobNumber'),
                'value' => $jobOutboundData['jo_number'],
            ],
            [
                'label' => Trans::getWhsWord('customer'),
                'value' => $jobOutboundData['jo_customer'],
            ],
            [
                'label' => Trans::getWhsWord('customerRef'),
                'value' => $jobOutboundData['jo_customer_ref'],
            ],
            [
                'label' => Trans::getWhsWord('picCustomer'),
                'value' => $jobOutboundData['jo_pic_customer'],
            ],
            [
                'label' => Trans::getWhsWord('orderOffice'),
                'value' => $jobOutboundData['jo_order_office'],
            ],
            [
                'label' => Trans::getWhsWord('invoiceOffice'),
                'value' => $jobOutboundData['jo_invoice_of'],
            ],
            [
                'label' => Trans::getWhsWord('orderDate'),
                'value' => DateTimeParser::format($jobOutboundData['jo_order_date'], 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWhsWord('jobManager'),
                'value' => $jobOutboundData['jo_manager'],
            ],
            [
                'label' => Trans::getWhsWord('status'),
                'value' => $status,
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWhsWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @param array $jobInboundData
     *
     * @return Portlet
     */
    protected function getJobInboundFieldSet(array $jobInboundData): Portlet
    {
        $etaTime = '';
        if (empty($jobInboundData['ji_eta_date']) === false) {
            if (empty($jobInboundData['ji_eta_time']) === false) {
                $etaTime = DateTimeParser::format($jobInboundData['ji_eta_date'] . ' ' . $jobInboundData['ji_eta_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etaTime = DateTimeParser::format($jobInboundData['ji_eta_date'], 'Y-m-d', 'd M Y');
            }
        }
        $ataTime = '';
        if (empty($jobInboundData['ji_ata_date']) === false) {
            if (empty($jobInboundData['ji_ata_time']) === false) {
                $ataTime = DateTimeParser::format($jobInboundData['ji_ata_date'] . ' ' . $jobInboundData['ji_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $ataTime = DateTimeParser::format($jobInboundData['ji_ata_date'], 'Y-m-d', 'd M Y');
            }
        }
        $driver = $jobInboundData['ji_driver'];
        if (empty($jobInboundData['ji_driver_phone']) === false) {
            $driver .= ' / ' . $jobInboundData['ji_driver_phone'];
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('warehouse'),
                'value' => $jobInboundData['ji_warehouse'],
            ],
            [
                'label' => Trans::getWhsWord('eta'),
                'value' => $etaTime,
            ],
            [
                'label' => Trans::getWhsWord('ata'),
                'value' => $ataTime,
            ],
            [
                'label' => Trans::getWhsWord('shipper'),
                'value' => $jobInboundData['ji_shipper'],
            ],
            [
                'label' => Trans::getWhsWord('picShipper'),
                'value' => $jobInboundData['ji_pic_shipper'],
            ],
            [
                'label' => Trans::getWhsWord('shipperAddress'),
                'value' => $jobInboundData['ji_shipper_address'],
            ],
            [
                'label' => Trans::getWhsWord('transporter'),
                'value' => $jobInboundData['ji_vendor'],
            ],
            [
                'label' => Trans::getWhsWord('driver'),
                'value' => $driver,
            ],
            [
                'label' => Trans::getWhsWord('truckPlate'),
                'value' => $jobInboundData['ji_truck_number'],
            ],
            [
                'label' => Trans::getWhsWord('containerNumber'),
                'value' => $jobInboundData['ji_container_number'],
            ],
            [
                'label' => Trans::getWhsWord('sealNumber'),
                'value' => $jobInboundData['ji_seal_number'],
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobInboundGeneralPtl', Trans::getWhsWord('jobDetail'));
        $portlet->addText($this->Field->getHidden('ji_id', $jobInboundData['ji_id']));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);


        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @param array $jobInboundData
     *
     * @return Portlet
     */
    protected function getJobInboundCustomerFieldSet(array $jobInboundData): Portlet
    {
        $currentAction = JobActionDao::getLastActiveActionByJobId($jobInboundData['ji_jo_id']);
        $joDao = new JobOrderDao();
        $data = [
            'is_deleted' => !empty($jobInboundData['jo_deleted_on']),
            'is_hold' => !empty($jobInboundData['joh_id']),
            'is_finish' => !empty($jobInboundData['jo_finish_on']),
            'is_start' => !empty($jobInboundData['jo_start_on']),
            'is_publish' => !empty($jobInboundData['jo_publish_on']),
        ];
        if (empty($currentAction) === false) {
            $data = array_merge($data, [
                'jac_id' => $currentAction['jac_id'],
                'jae_style' => $currentAction['jac_style'],
                'jac_action' => $currentAction['jac_action'],
                'jae_description' => $currentAction['jae_description'],
                'jo_srt_id' => $currentAction['ac_srt_id'],
            ]);
        }
        $status = $joDao->generateStatus($data);
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWhsWord('jobNumber'),
                'value' => $jobInboundData['jo_number'],
            ],
            [
                'label' => Trans::getWhsWord('customer'),
                'value' => $jobInboundData['jo_customer'],
            ],
            [
                'label' => Trans::getWhsWord('customerRef'),
                'value' => $jobInboundData['jo_customer_ref'],
            ],
            [
                'label' => Trans::getWhsWord('picCustomer'),
                'value' => $jobInboundData['jo_pic_customer'],
            ],
            [
                'label' => Trans::getWhsWord('orderOffice'),
                'value' => $jobInboundData['jo_order_office'],
            ],
            [
                'label' => Trans::getWhsWord('invoiceOffice'),
                'value' => $jobInboundData['jo_invoice_of'],
            ],
            [
                'label' => Trans::getWhsWord('orderDate'),
                'value' => DateTimeParser::format($jobInboundData['jo_order_date'], 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWhsWord('jobManager'),
                'value' => $jobInboundData['jo_manager'],
            ],
            [
                'label' => Trans::getWhsWord('status'),
                'value' => $status,
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobInboundCustomerPtl', Trans::getWhsWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

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
        $modal = new Modal('JoGdMdl', Trans::getWhsWord('goods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Unit Field
        $goodsField = $this->Field->getSingleSelectTable('goods', 'jtg_goods', $this->getParameterForModal('jtg_goods', $showModal), 'loadSingleSelectTableData');
        $goodsField->setHiddenField('jtg_gd_id', $this->getParameterForModal('jtg_gd_id', $showModal));
        $goodsField->setTableColumns([
            'gd_sku' => Trans::getWhsWord('sku'),
            'gd_gdc_name' => Trans::getWhsWord('category'),
            'gd_br_name' => Trans::getWhsWord('brand'),
            'gd_name' => Trans::getWhsWord('goods'),
        ]);
        $goodsField->setFilters([
            'gdc_name' => Trans::getWhsWord('category'),
            'br_name' => Trans::getWhsWord('brand'),
            'gd_name' => Trans::getWhsWord('goods'),
            'gd_sku' => Trans::getWhsWord('sku'),
        ]);
        $goodsField->setValueCode('gd_id');
        $goodsField->setLabelCode('gd_full_name');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameter('gd_rel_id', 594);
        $goodsField->addParameterById('gd_rel_id', 'jtr_rel_id', Trans::getWhsWord('customer'));
        $goodsField->addClearField('jtg_production_batch');
        $goodsField->addClearField('jtg_production_number');
        $goodsField->addClearField('jtg_unit');
        $goodsField->addClearField('jtg_gdu_id');
        $goodsField->setParentModal($modal->getModalId());
        $this->View->addModal($goodsField->getModal());
        $productionNumber = $this->Field->getSingleSelect('jobInboundDetail', 'jtg_production_batch', $this->getParameterForModal('jtg_production_batch', $showModal), 'loadAvailableLotNumber');
        $productionNumber->setHiddenField('jtg_production_number', $this->getParameterForModal('jtg_production_number', $showModal));
        $productionNumber->addParameterById('jid_gd_id', 'jtg_gd_id', Trans::getWhsWord('goods'));
        $productionNumber->setEnableNewButton(false);
        $productionNumber->setEnableDetailButton(false);
        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'jtg_unit', $this->getParameterForModal('jtg_unit', $showModal));
        $unitField->setHiddenField('jtg_gdu_id', $this->getParameterForModal('jtg_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'jtg_gd_id', Trans::getWhsWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);
        # Add field into field set.
        $fieldSet->addField(Trans::getWhsWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWhsWord('lotNumber'), $productionNumber);
        $fieldSet->addField(Trans::getWhsWord('quantity'), $this->Field->getNumber('jtg_quantity', $this->getParameterForModal('jtg_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWhsWord('uom'), $unitField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('jtg_id', $this->getParameterForModal('jtg_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    protected function getGoodsDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoGdDelMdl', Trans::getWhsWord('deleteGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWhsWord('brand'), $this->Field->getText('jtg_br_name_del', $this->getParameterForModal('jtg_br_name_del', $showModal)));
        $fieldSet->addField(Trans::getWhsWord('category'), $this->Field->getText('jtg_gdc_name_del', $this->getParameterForModal('jtg_gdc_name_del', $showModal)));
        $fieldSet->addField(Trans::getWhsWord('goods'), $this->Field->getText('jtg_gd_name_del', $this->getParameterForModal('jtg_gd_name_del', $showModal)));
        $fieldSet->addField(Trans::getWhsWord('productionNumber'), $this->Field->getText('jtg_production_number_del', $this->getParameterForModal('jtg_production_number_del', $showModal)));
        $fieldSet->addField(Trans::getWhsWord('quantity'), $this->Field->getNumber('jtg_quantity_del', $this->getParameterForModal('jtg_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWhsWord('uom'), $this->Field->getText('jtg_unit_del', $this->getParameterForModal('jtg_unit_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jtg_id_del', $this->getParameterForModal('jtg_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWhsWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to load goods data.
     *
     * @return void
     */
    private function loadGoodsData(): void
    {
        $wheres = [];
        $wheres[] = '(jtg.jtg_jtr_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jtg.jtg_deleted_on IS NULL)';
        $this->Goods = JobStockTransferGoodsDao::loadDataForStockTransfer($wheres);
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('jtr_number');
        $status = new LabelGray(Trans::getWhsWord('draft'));
        if ($this->isValidParameter('jtr_deleted_on')) {
            $status = new LabelDark(Trans::getWhsWord('deleted'));
            $this->View->addErrorMessage(Trans::getWhsWord('delete') . ' : ' . $this->getStringParameter('jtr_deleted_reason'));
        } elseif ($this->isValidParameter('jtr_end_in_on')) {
            $status = new LabelSuccess(Trans::getWhsWord('finish'));
        } elseif ($this->isValidParameter('jtr_end_in_on') === false && $this->isValidParameter('jtr_start_in_on') === true) {
            $status = new LabelPrimary(Trans::getWhsWord('inboundProcess'));
        } elseif ($this->isValidParameter('jtr_start_in_on') === false && $this->isValidParameter('jtr_end_out_on') === true) {
            $status = new LabelInfo(Trans::getWhsWord('delivery'));
        } elseif ($this->isValidParameter('jtr_end_out_on') === false && $this->isValidParameter('jtr_start_out_on') === true) {
            $status = new LabelWarning(Trans::getWhsWord('outboundProcess'));
        } elseif ($this->isValidParameter('jtr_start_out_on') === false && $this->isValidParameter('jtr_publish_on') === true) {
            $status = new LabelDanger(Trans::getWhsWord('publish'));
        }
        $this->View->setDescription($title . ' | ' . $status);

    }

}
