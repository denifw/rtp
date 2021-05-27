<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
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
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Job\Warehouse\JobStockTransferGoodsDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\WarehouseDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\System\Service\ServiceTermDao;

/**
 * Class to handle the creation of detail JobStockTransfer page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class JobStockTransfer extends AbstractFormModel
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
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'joWhStockTransfer', 'jtr_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('JobStockTransfer', $this->User->Relation->getOfficeId(), $this->User->getRelId());
        $colVal = [
            'jtr_number' => $number,
            'jtr_ss_id' => $this->User->getSsId(),
            'jtr_rel_id' => $this->getIntParameter('jtr_rel_id'),
            'jtr_customer_ref' => $this->getStringParameter('jtr_customer_ref'),
            'jtr_pic_id' => $this->getIntParameter('jtr_pic_id'),
            'jtr_who_id' => $this->getIntParameter('jtr_who_id'),
            'jtr_who_us_id' => $this->getIntParameter('jtr_who_us_id'),
            'jtr_who_date' => $this->getStringParameter('jtr_who_date'),
            'jtr_who_time' => $this->getStringParameter('jtr_who_time'),
            'jtr_whd_id' => $this->getIntParameter('jtr_whd_id'),
            'jtr_whd_us_id' => $this->getIntParameter('jtr_whd_us_id'),
            'jtr_whd_date' => $this->getStringParameter('jtr_whd_date'),
            'jtr_whd_time' => $this->getStringParameter('jtr_whd_time'),
            'jtr_transporter_id' => $this->getIntParameter('jtr_transporter_id'),
            'jtr_truck_plate' => $this->getStringParameter('jtr_truck_plate'),
            'jtr_container_number' => $this->getStringParameter('jtr_container_number'),
            'jtr_seal_number' => $this->getStringParameter('jtr_seal_number'),
            'jtr_driver' => $this->getStringParameter('jtr_driver'),
            'jtr_driver_phone' => $this->getStringParameter('jtr_driver_phone'),
        ];
        $jtrDao = new JobStockTransferDao();
        $jtrDao->doInsertTransaction($colVal);

        return $jtrDao->getLastInsertId();
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
                'jtg_production_number' => $this->getStringParameter('jtg_production_number'),
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
        } elseif ($this->getFormAction() === 'doPublishJob') {
            # Load goods
            $this->loadGoodsData();
            # Insert job outbound
            $jobOutId = $this->doInsertJobOutbound();
            # Insert job outbound
            $jobInId = $this->doInsertJobInbound();
            # Update Stock transfer (upate outbound id & inbound id)
            $colVal = [
                'jtr_publish_on' => date('Y-m-d H:i:s'),
                'jtr_publish_by' => $this->User->getId(),
                'jtr_job_jo_id' => $jobOutId,
                'jtr_ji_jo_id' => $jobInId,
            ];
            $jtrDao = new JobStockTransferDao();
            $jtrDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doDeleteJob') {
            $jtrDao = new JobStockTransferDao();
            $colVal = [
                'jtr_deleted_on' => date('Y-m-d H:i:s'),
                'jtr_deleted_by' => $this->User->getId(),
                'jtr_deleted_reason' => $this->getStringParameter('jtr_deleted_reason'),
            ];
            $jtrDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            # Delete job outbound
            if ($this->isValidParameter('jtr_job_jo_id') === true) {
                $joDelColVal = [
                    'jo_deleted_reason' => $this->getStringParameter('jtr_deleted_reason'),
                    'jo_deleted_by' => $this->User->getId(),
                    'jo_deleted_on' => date('Y-m-d H:i:s'),
                ];
                $joDao = new JobOrderDao();
                $joDao->doUpdateTransaction($this->getIntParameter('jtr_job_jo_id'), $joDelColVal);
            }
            # Delete job inbound
            if ($this->isValidParameter('jtr_ji_jo_id') === true) {
                $joDelColVal = [
                    'jo_deleted_reason' => $this->getStringParameter('jtr_deleted_reason'),
                    'jo_deleted_by' => $this->User->getId(),
                    'jo_deleted_on' => date('Y-m-d H:i:s'),
                ];
                $joDao = new JobOrderDao();
                $joDao->doUpdateTransaction($this->getIntParameter('jtr_ji_jo_id'), $joDelColVal);
            }
        } else {
            $colVal = [
                'jtr_customer_ref' => $this->getStringParameter('jtr_customer_ref'),
                'jtr_pic_id' => $this->getIntParameter('jtr_pic_id'),
                'jtr_who_id' => $this->getIntParameter('jtr_who_id'),
                'jtr_who_us_id' => $this->getIntParameter('jtr_who_us_id'),
                'jtr_who_date' => $this->getStringParameter('jtr_who_date'),
                'jtr_who_time' => $this->getStringParameter('jtr_who_time'),
                'jtr_whd_id' => $this->getIntParameter('jtr_whd_id'),
                'jtr_whd_us_id' => $this->getIntParameter('jtr_whd_us_id'),
                'jtr_whd_date' => $this->getStringParameter('jtr_whd_date'),
                'jtr_whd_time' => $this->getStringParameter('jtr_whd_time'),
                'jtr_transporter_id' => $this->getIntParameter('jtr_transporter_id'),
                'jtr_truck_plate' => $this->getStringParameter('jtr_truck_plate'),
                'jtr_container_number' => $this->getStringParameter('jtr_container_number'),
                'jtr_seal_number' => $this->getStringParameter('jtr_seal_number'),
                'jtr_driver' => $this->getStringParameter('jtr_driver'),
                'jtr_driver_phone' => $this->getStringParameter('jtr_driver_phone'),
            ];
            $jtrDao = new JobStockTransferDao();
            $jtrDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
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
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getTransporterFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseOriginFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseDestinationFieldSet());
        if ($this->isUpdate() === true) {
            $this->overridePageTitle();
            # Load goods data.
            $this->loadGoodsData();
            $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
            if ($this->isValidParameter('jtr_publish_on') === true) {
                $jobOutboundData = JobOutboundDao::getByReferenceAndSystem($this->getIntParameter('jtr_job_jo_id'), $this->User->getSsId());
                # set parameter job outbound status
                $this->setParameters([
                    'job_start_store_on' => $jobOutboundData['job_start_store_on'],
                    'job_end_store_on' => $jobOutboundData['job_end_store_on'],
                    'job_start_load_on' => $jobOutboundData['job_start_load_on'],
                    'job_end_load_on' => $jobOutboundData['job_end_load_on'],
                ]);
                $this->Tab->addPortlet('outbound', $this->getJobOutboundCustomerFieldSet($jobOutboundData));
                $this->Tab->addPortlet('outbound', $this->getJobOutboundFieldSet($jobOutboundData));
                $jobInboundData = JobInboundDao::getByReferenceAndSystemSetting($this->getIntParameter('jtr_ji_jo_id'), $this->User->getSsId());
                $this->Tab->addPortlet('inbound', $this->getJobInboundCustomerFieldSet($jobInboundData));
                $this->Tab->addPortlet('inbound', $this->getJobInboundFieldSet($jobInboundData));
            }
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
                'jtg_id' => $this->getIntParameter('jtg_id'),
            ], [
                'jtg_jtr_id' => $this->getDetailReferenceValue(),
            ]);
            if ($this->isValidParameter('jtg_production_number')) {
                $this->Validation->checkRequire('jtg_production_number', 3, 255);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoods') {
            $this->Validation->checkRequire('jtg_id_del');
        } elseif ($this->getFormAction() === 'doDeleteJob') {
            $this->Validation->checkRequire('jtr_deleted_reason', 3, 255);
        } else {
            $this->Validation->checkRequire('jtr_rel_id');
            $this->Validation->checkRequire('jtr_who_id');
            $this->Validation->checkRequire('jtr_who_us_id');
            $this->Validation->checkRequire('jtr_who_date');
            $this->Validation->checkRequire('jtr_who_time');
            $this->Validation->checkRequire('jtr_whd_id');
            $this->Validation->checkRequire('jtr_whd_us_id');
            $this->Validation->checkRequire('jtr_whd_date');
            $this->Validation->checkRequire('jtr_whd_time');
            $this->Validation->checkRequire('jtr_transporter_id');
            if ($this->isValidParameter('jtr_customer_ref')) {
                $this->Validation->checkRequire('jtr_customer_ref', 3, 255);
            }
            if ($this->isValidParameter('jtr_driver')) {
                $this->Validation->checkRequire('jtr_driver', 3, 255);
            }
            if ($this->isValidParameter('jtr_driver_phone')) {
                $this->Validation->checkRequire('jtr_driver_phone', 3, 255);
            }
            if ($this->isValidParameter('jtr_truck_plate')) {
                $this->Validation->checkRequire('jtr_truck_plate', 3, 255);
            }
            if ($this->isValidParameter('jtr_container_number')) {
                $this->Validation->checkRequire('jtr_container_number', 3, 255);
            }
            if ($this->isValidParameter('jtr_seal_number')) {
                $this->Validation->checkRequire('jtr_seal_number', 3, 255);
            }
            # Validate warehouse origin with warehouse destination, not allowed if both of them are same
            $this->Validation->checkDifferent('jtr_who_name', 'jtr_whd_name');

        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
            if ($this->isValidParameter('jtr_deleted_on') === false) {
                if ($this->isValidParameter('jtr_publish_on') === false && $this->PageSetting->checkPageRight('AllowPublish') === true) {
                    $modal = $this->getJobPublishModal();
                    $this->View->addModal($modal);
                    $btnPublish = new ModalButton('btnPubJo', Trans::getWhsWord('publish'), $modal->getModalId());
                    $btnPublish->setIcon(Icon::PaperPlane)->btnDanger()->pullRight()->btnMedium();
                    $this->View->addButtonAtTheBeginning($btnPublish);
                } elseif ($this->isValidParameter('jtr_publish_on') === true) {
                    $this->setDisableUpdate();
                }
                if ($this->isValidParameter('job_start_load_on') === false && $this->PageSetting->checkPageRight('AllowDelete') === true) {
                    $modalDel = $this->getJobDeleteModal();
                    $this->View->addModal($modalDel);
                    $btnDel = new ModalButton('btnDelete', Trans::getWhsWord('delete'), $modalDel->getModalId());
                    $btnDel->setIcon(Icon::Trash)->btnDanger()->pullRight()->btnMedium();
                    $this->View->addButtonAtTheBeginning($btnDel);
                }
            } else {
                $this->setDisableUpdate();
            }
            $btnView = new HyperLink('hplView', Trans::getWord('view'), url($this->getViewRoute() . '?' . $this->getDetailReferenceCode() . '=' . $this->getDetailReferenceValue()));
            $btnView->viewAsButton();
            $btnView->setIcon(Icon::Eye)->btnSuccess()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnView);
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        # Create Relation Field
        $customerField = $this->Field->getSingleSelect('relation', 'jtr_rel_name', $this->getStringParameter('jtr_rel_name'), 'loadGoodsOwnerData');
        $customerField->setHiddenField('jtr_rel_id', $this->getIntParameter('jtr_rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setDetailReferenceCode('rel_id');
        if ($this->isUpdate() === true) {
            $customerField->setReadOnly();
        }
        # Customer ref
        $customerRef = $this->Field->getText('jtr_customer_ref', $this->getStringParameter('jtr_customer_ref'));
        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'jtr_pic_name', $this->getStringParameter('jtr_pic_name'));
        $picField->setHiddenField('jtr_pic_id', $this->getIntParameter('jtr_pic_id'));
        $picField->addParameterById('cp_rel_id', 'jtr_rel_id', Trans::getWhsWord('customer'));
        $picField->setDetailReferenceCode('cp_id');
        # Add field to fieldset
        $fieldSet->addField(Trans::getWhsWord('customer'), $customerField, true);
        $fieldSet->addField(Trans::getWhsWord('picCustomer'), $picField);
        $fieldSet->addField(Trans::getWhsWord('customerRef'), $customerRef);
        $fieldSet->addHiddenField($this->Field->getHidden('jtr_job_jo_id', $this->getIntParameter('jtr_job_jo_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jtr_ji_jo_id', $this->getIntParameter('jtr_ji_jo_id')));
        # Create a portlet box.
        $portlet = new Portlet('JobGeneralPtl', Trans::getWhsWord('customer'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseOriginFieldSet(): Portlet
    {

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create Warehouse Origin Field
        $whoField = $this->Field->getSingleSelect('warehouse', 'jtr_who_name', $this->getStringParameter('jtr_who_name'));
        $whoField->setHiddenField('jtr_who_id', $this->getIntParameter('jtr_who_id'));
        $whoField->addParameter('wh_ss_id', $this->User->getSsId());
        $whoField->setEnableNewButton(false);
        $whoField->setDetailReferenceCode('wh_id');
        # Create job manager Origin Field
        $managerField = $this->Field->getSingleSelect('user', 'jtr_who_us_name', $this->getStringParameter('jtr_who_us_name'));
        $managerField->setHiddenField('jtr_who_us_id', $this->getIntParameter('jtr_who_us_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);
        $whoDateField = $this->Field->getCalendar('jtr_who_date', $this->getStringParameter('jtr_who_date'));
        $whoTimeField = $this->Field->getTime('jtr_who_time', $this->getStringParameter('jtr_who_time'));
        # Add field to fieldset
        $fieldSet->addField(Trans::getWhsWord('warehouse'), $whoField, true);
        $fieldSet->addField(Trans::getWhsWord('manager'), $managerField, true);
        $fieldSet->addField(Trans::getWhsWord('planningDate'), $whoDateField, true);
        $fieldSet->addField(Trans::getWhsWord('planningTime'), $whoTimeField, true);
        # Create a portlet box.
        $portlet = new Portlet('WhoPtl', Trans::getWhsWord('warehouseOrigin'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseDestinationFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create Warehouse Origin Field
        $whoField = $this->Field->getSingleSelect('warehouse', 'jtr_whd_name', $this->getStringParameter('jtr_whd_name'));
        $whoField->setHiddenField('jtr_whd_id', $this->getIntParameter('jtr_whd_id'));
        $whoField->addParameter('wh_ss_id', $this->User->getSsId());
        $whoField->setEnableNewButton(false);
        $whoField->setDetailReferenceCode('wh_id');
        # Create job manager Origin Field
        $managerField = $this->Field->getSingleSelect('user', 'jtr_whd_us_name', $this->getStringParameter('jtr_whd_us_name'));
        $managerField->setHiddenField('jtr_whd_us_id', $this->getIntParameter('jtr_whd_us_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);
        $whoDateField = $this->Field->getCalendar('jtr_whd_date', $this->getStringParameter('jtr_whd_date'));
        $whoTimeField = $this->Field->getTime('jtr_whd_time', $this->getStringParameter('jtr_whd_time'));
        # Add field to fieldset
        $fieldSet->addField(Trans::getWhsWord('warehouse'), $whoField, true);
        $fieldSet->addField(Trans::getWhsWord('manager'), $managerField, true);
        $fieldSet->addField(Trans::getWhsWord('planningDate'), $whoDateField, true);
        $fieldSet->addField(Trans::getWhsWord('planningTime'), $whoTimeField, true);
        # Create a portlet box.
        $portlet = new Portlet('WhdPtl', Trans::getWhsWord('warehouseDestination'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the Transporter Field Set.
     *
     * @return Portlet
     */
    private function getTransporterFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        $transporterField = $this->Field->getSingleSelect('relation', 'jtr_transporter_name', $this->getStringParameter('jtr_transporter_name'));
        $transporterField->setHiddenField('jtr_transporter_id', $this->getIntParameter('jtr_transporter_id'));
        $transporterField->addParameter('rel_ss_id', $this->User->getSsId());
        $transporterField->setDetailReferenceCode('rel_id');
        $driverField = $this->Field->getText('jtr_driver', $this->getStringParameter('jtr_driver'));
        $driverPhoneField = $this->Field->getText('jtr_driver_phone', $this->getStringParameter('jtr_driver_phone'));
        $truckPlateField = $this->Field->getText('jtr_truck_plate', $this->getStringParameter('jtr_truck_plate'));
        $containerNumberField = $this->Field->getText('jtr_container_number', $this->getStringParameter('jtr_container_number'));
        $sealNumberField = $this->Field->getText('jtr_seal_number', $this->getStringParameter('jtr_seal_number'));
        # Add field to fieldset
        $fieldSet->addField(Trans::getWhsWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWhsWord('transporter'), $transporterField, true);
        $fieldSet->addField(Trans::getWhsWord('driverPhone'), $driverPhoneField);
        $fieldSet->addField(Trans::getWhsWord('truckNumber'), $truckPlateField);
        $fieldSet->addField(Trans::getWhsWord('containerNumber'), $containerNumberField);
        $fieldSet->addField(Trans::getWhsWord('sealNumber'), $sealNumberField);
        # Create a portlet box.
        $portlet = new Portlet('TspPtl', Trans::getWhsWord('transporter'));
        $portlet->addFieldSet($fieldSet);
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
            [
                'label' => Trans::getWhsWord('transporter'),
                'value' => $jobOutboundData['transporter'],
            ],
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
                'value' => $jobOutboundData['jo_pic'],
            ],
            [
                'label' => Trans::getWhsWord('orderOffice'),
                'value' => $jobOutboundData['jo_order_of'],
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
        $portlet->addText($this->Field->getHidden('job_start_store_on', $this->getStringParameter('job_start_store_on')));
        $portlet->addText($this->Field->getHidden('job_end_store_on', $this->getStringParameter('job_end_store_on')));
        $portlet->addText($this->Field->getHidden('job_start_load_on', $this->getStringParameter('job_start_load_on')));
        $portlet->addText($this->Field->getHidden('job_end_load_on', $this->getStringParameter('job_end_load_on')));
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
                'value' => $jobInboundData['jo_pic'],
            ],
            [
                'label' => Trans::getWhsWord('orderOffice'),
                'value' => $jobInboundData['jo_order_of'],
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
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getJobPublishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoPubMdl', Trans::getWhsWord('publishConfirmation'));
        if (empty($this->Goods) === true) {
            $modal->setTitle(Trans::getWhsWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getWord('unablePublishJobOrder', 'message');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getWord('publishJobConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
        }
        $modal->setBtnOkName(Trans::getWhsWord('yesPublish'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get job delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getJobDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JobDelMdl', Trans::getWhsWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteJob');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteJob' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        # Add field into field set.
        $fieldSet->addField(Trans::getWhsWord('reason'), $this->Field->getTextArea('jtr_deleted_reason', $this->getParameterForModal('jtr_deleted_reason', $showModal)), true);
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
     * Function to do the insert job outbound;
     *
     * @return int Return job order outbound id.
     */
    protected function doInsertJobOutbound(): int
    {
        $srvData = ServiceTermDao::getByRoute('joWhOutbound');
        $whoOffData = WarehouseDao::getByReference($this->getIntParameter('jtr_who_id'));
        $whdOffData = WarehouseDao::getByReference($this->getIntParameter('jtr_whd_id'));
        $joId = 0;
        if (empty($srvData) === false && empty($whoOffData) === false && empty($whdOffData) === false) {
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('JobOrder', $whoOffData['wh_of_id'], $this->getIntParameter('jtr_rel_id'), $srvData['srt_srv_id'], $srvData['srt_id']);
            $joColVal = [
                'jo_number' => $number,
                'jo_ss_id' => $this->User->getSsId(),
                'jo_srv_id' => $srvData['srt_srv_id'],
                'jo_srt_id' => $srvData['srt_id'],
                'jo_order_date' => $this->getStringParameter('jtr_who_date'),
                'jo_rel_id' => $this->getIntParameter('jtr_rel_id'),
                'jo_customer_ref' => $this->getStringParameter('jtr_customer_ref'),
                'jo_pic_id' => $this->getIntParameter('jtr_pic_id'),
                'jo_order_of_id' => $whoOffData['wh_of_id'],
                'jo_manager_id' => $this->getIntParameter('jtr_who_us_id'),
                'jo_vendor_id' => $this->getIntParameter('jtr_rel_id'),
                'jo_publish_by' => $this->User->getId(),
                'jo_publish_on' => date('Y-m-d H:i:s'),
            ];
            # Insert Job order
            $jobDao = new JobOrderDao();
            $jobDao->doInsertTransaction($joColVal);
            $joId = $jobDao->getLastInsertId();
            $actions = SystemActionDao::getByServiceTermIdAndSystemId($srvData['srt_id'], $this->User->getSsId());
            $jacDao = new JobActionDao();
            $i = 1;
            foreach ($actions as $row) {
                $jacColVal = [
                    'jac_jo_id' => $joId,
                    'jac_ac_id' => $row['sac_ac_id'],
                    'jac_order' => $i,
                    'jac_active' => 'Y',
                ];
                $jacDao->doInsertTransaction($jacColVal);
                $i++;
            }
            # Insert job outbound
            $jobOutColVal = [
                'job_jo_id' => $joId,
                'job_wh_id' => $this->getIntParameter('jtr_who_id'),
                'job_eta_date' => $this->getStringParameter('jtr_who_date'),
                'job_eta_time' => $this->getStringParameter('jtr_who_time'),
                'job_rel_id' => $this->getIntParameter('jtr_rel_id'),
                'job_of_id' => $whdOffData['wh_of_id'],
                'job_vendor_id' => $this->getIntParameter('jtr_transporter_id'),
                'job_driver' => $this->getStringParameter('jtr_driver'),
                'job_driver_phone' => $this->getStringParameter('jtr_driver_phone'),
                'job_truck_number' => $this->getStringParameter('jtr_truck_plate'),
                'job_container_number' => $this->getStringParameter('jtr_container_number'),
                'job_seal_number' => $this->getStringParameter('jtr_seal_number'),
            ];
            $jobOutDao = new JobOutboundDao();
            $jobOutDao->doInsertTransaction($jobOutColVal);
            # Do insert job goods
            $gdDao = new GoodsDao();
            $jogDao = new JobGoodsDao();
            foreach ($this->Goods as $row) {
                $jtgGoods = $gdDao->formatFullName($row['jtg_gdc_name'], $row['jtg_br_name'], $row['jtg_gd_name']);
                $jogColVal = [
                    'jog_jo_id' => $joId,
                    'jog_gd_id' => $row['jtg_gd_id'],
                    'jog_name' => $jtgGoods,
                    'jog_quantity' => $row['jtg_quantity'],
                    'jog_gdu_id' => $row['jtg_gdu_id'],
                    'jog_production_number' => $row['jtg_production_number'],
                ];
                $snGoods = $sn->loadNumber('JobOrderGoods', $whoOffData['wh_of_id'], $this->getIntParameter('jtr_rel_id'), $srvData['srt_srv_id'], $srvData['srt_id']);
                $jogColVal['jog_serial_number'] = $snGoods;
                $jogDao->doInsertTransaction($jogColVal);
            }
        }

        return $joId;
    }

    /**
     * Function to do the insert job inbound;
     *
     * @return int Return job order inbound id.
     */
    protected function doInsertJobInbound(): int
    {
        $srvData = ServiceTermDao::getByRoute('joWhInbound');
        $whoOffData = WarehouseDao::getByReference($this->getIntParameter('jtr_who_id'));
        $whdOffData = WarehouseDao::getByReference($this->getIntParameter('jtr_whd_id'));
        $joId = 0;
        if (empty($srvData) === false && empty($whoOffData) === false && empty($whdOffData) === false) {
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('JobOrder', $whdOffData['wh_of_id'], $this->getIntParameter('jtr_rel_id'), $srvData['srt_srv_id'], $srvData['srt_id']);
            $joColVal = [
                'jo_number' => $number,
                'jo_ss_id' => $this->User->getSsId(),
                'jo_srv_id' => $srvData['srt_srv_id'],
                'jo_srt_id' => $srvData['srt_id'],
                'jo_order_date' => $this->getStringParameter('jtr_whd_date'),
                'jo_rel_id' => $this->getIntParameter('jtr_rel_id'),
                'jo_customer_ref' => $this->getStringParameter('jtr_customer_ref'),
                'jo_pic_id' => $this->getIntParameter('jtr_pic_id'),
                'jo_order_of_id' => $whdOffData['wh_of_id'],
                'jo_manager_id' => $this->getIntParameter('jtr_whd_us_id'),
                'jo_vendor_id' => $this->getIntParameter('jtr_rel_id'),
                'jo_publish_by' => $this->User->getId(),
                'jo_publish_on' => date('Y-m-d H:i:s'),
            ];
            # Insert Job order
            $jobDao = new JobOrderDao();
            $jobDao->doInsertTransaction($joColVal);
            $joId = $jobDao->getLastInsertId();
            $actions = SystemActionDao::getByServiceTermIdAndSystemId($srvData['srt_id'], $this->User->getSsId());
            $jacDao = new JobActionDao();
            $i = 1;
            foreach ($actions as $row) {
                $jacColVal = [
                    'jac_jo_id' => $joId,
                    'jac_ac_id' => $row['sac_ac_id'],
                    'jac_order' => $i,
                    'jac_active' => 'Y',
                ];
                $jacDao->doInsertTransaction($jacColVal);
                $i++;
            }
            # Insert job inbound
            $jiColVal = [
                'ji_jo_id' => $joId,
                'ji_wh_id' => $this->getIntParameter('jtr_whd_id'),
                'ji_eta_date' => $this->getStringParameter('jtr_whd_date'),
                'ji_eta_time' => $this->getStringParameter('jtr_whd_time'),
                'ji_rel_id' => $this->getIntParameter('jtr_rel_id'),
                'ji_of_id' => $whoOffData['wh_of_id'],
                'ji_vendor_id' => $this->getIntParameter('jtr_transporter_id'),
                'ji_driver' => $this->getStringParameter('jtr_driver'),
                'ji_driver_phone' => $this->getStringParameter('jtr_driver_phone'),
                'ji_truck_number' => $this->getStringParameter('jtr_truck_plate'),
                'ji_container_number' => $this->getStringParameter('jtr_container_number'),
                'ji_seal_number' => $this->getStringParameter('jtr_seal_number'),
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doInsertTransaction($jiColVal);
            # Do insert job goods
            $gdDao = new GoodsDao();
            $jogDao = new JobGoodsDao();
            foreach ($this->Goods as $row) {
                $jtgGoods = $gdDao->formatFullName($row['jtg_gdc_name'], $row['jtg_br_name'], $row['jtg_gd_name']);
                $jogColVal = [
                    'jog_jo_id' => $joId,
                    'jog_gd_id' => $row['jtg_gd_id'],
                    'jog_name' => $jtgGoods,
                    'jog_quantity' => $row['jtg_quantity'],
                    'jog_gdu_id' => $row['jtg_gdu_id'],
                    'jog_production_number' => $row['jtg_production_number'],
                ];
                $snGoods = $sn->loadNumber('JobOrderGoods', $whdOffData['wh_of_id'], $this->getIntParameter('jtr_rel_id'), $srvData['srt_srv_id'], $srvData['srt_id']);
                $jogColVal['jog_serial_number'] = $snGoods;
                $jogDao->doInsertTransaction($jogColVal);
            }
        }

        return $joId;
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
