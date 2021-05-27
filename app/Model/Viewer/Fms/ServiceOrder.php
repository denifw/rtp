<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Fms;

use App\Frame\Document\FileUpload;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\Button;
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
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\Fms\ServiceOrderCostDao;
use App\Model\Dao\Fms\ServiceOrderDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Fms\ServiceOrderDetailDao;
use App\Model\Dao\Fms\ServiceOrderRequestDao;
use App\Model\Dao\Fms\ServiceReminderDao;
use App\Model\Dao\Master\EquipmentDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail ServiceOrder page
 *
 * @package    app
 * @subpackage Model\Viewer\Fms
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrder extends AbstractViewerModel
{
    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    private $ServiceTasks = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceOrder', 'svo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateServiceTask') {
            $colVal = [
                'svd_svo_id' => $this->getDetailReferenceValue(),
                'svd_svt_id' => $this->getIntParameter('svd_svt_id'),
                'svd_est_cost' => $this->getFloatParameter('svd_est_cost'),
                'svd_remark' => $this->getStringParameter('svd_remark'),
            ];
            $svdDao = new ServiceOrderDetailDao();
            if ($this->isValidParameter('svd_id')) {
                $svdDao->doUpdateTransaction($this->getIntParameter('svd_id'), $colVal);
            } else {
                $svdDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteServiceTask') {
            $svdDao = new ServiceOrderDetailDao();
            $svdDao->doDeleteTransaction($this->getIntParameter('svd_id_del'));
        } elseif ($this->getFormAction() === 'doRequestServiceOrder') {
            $colVal = [
                'svr_svo_id' => $this->getDetailReferenceValue(),
            ];
            $svrDao = new ServiceOrderRequestDao();
            $svrDao->doInsertTransaction($colVal);
            # Update request id into mmaster table service order
            $colVal = [
                'svo_svr_id' => $svrDao->getLastInsertId(),
            ];
            $svoDao = new ServiceOrderDao();
            $svoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doApproveServiceOrder') {
            $colVal = [
                'svo_approved_on' => date('Y-m-d H:i:s'),
                'svo_approved_by' => $this->User->getId(),
            ];
            $svoDao = new ServiceOrderDao();
            $svoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doRejectServiceOrder') {
            $colVal = [
                'svr_svo_id' => $this->getDetailReferenceValue(),
                'svr_reject_reason' => $this->getStringParameter('reject_reason')
            ];
            $svrDao = new ServiceOrderRequestDao();
            $svrDao->doInsertTransaction($colVal);
            # Update request id into mmaster table service order
            $colVal = [
                'svo_svr_id' => $svrDao->getLastInsertId(),
            ];
            $svoDao = new ServiceOrderDao();
            $svoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doStartServiceOrder') {
            $colVal = [
                'svo_meter' => $this->getFloatParameter('svo_meter'),
                'svo_start_service_date' => $this->getStringParameter('svo_start_service_date'),
                'svo_start_service_time' => $this->getStringParameter('svo_start_service_time'),
                'svo_start_service_by' => $this->User->getId(),
            ];
            $svoDao = new ServiceOrderDao();
            $svoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            # Set equipment status
            $colValEq = [
                'eq_eqs_id' => 3,
                'eq_active' => 'N'
            ];
            $eqDao = new EquipmentDao();
            $eqDao->doUpdateTransaction($this->getIntParameter('svo_eq_id'), $colValEq);
            # Insert equipment meter history
            $colValMtr = [
                'eqm_eq_id' => $this->getIntParameter('svo_eq_id'),
                'eqm_meter' => $this->getFloatParameter('svo_meter'),
                'eqm_date' => $this->getStringParameter('svo_start_service_date'),
                'eqm_source' => Trans::getFmsWord('serviceEntry'),
            ];
            $eqmDao = new EquipmentMeterDao();
            $eqmDao->doInsertTransaction($colValMtr);
            $this->doUpdateServiceReminderNextDueDate();
        } elseif ($this->getFormAction() === 'doFinishServiceOrder') {
            $colVal = [
                'svo_finish_on' => date('Y-m-d H:i:s'),
                'svo_finish_by' => $this->User->getId(),
            ];
            $svoDao = new ServiceOrderDao();
            $svoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            # Set equipment status
            $colValEq = [
                'eq_eqs_id' => 1,
                'eq_active' => 'Y'
            ];
            $eqDao = new EquipmentDao();
            $eqDao->doUpdateTransaction($this->getIntParameter('svo_eq_id'), $colValEq);
        } elseif ($this->getFormAction() === 'doUpdateServiceTaskCost') {
            $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('svc_tax_id'));
            $rate = $this->getFloatParameter('svc_rate') * $this->getFloatParameter('svc_quantity');
            $taxAmount = ($rate * $taxPercent) / 100;
            $total = $rate + $taxAmount;
            $colVal = [
                'svc_svo_id' => $this->getDetailReferenceValue(),
                'svc_svd_id' => $this->getIntParameter('svc_svd_id'),
                'svc_cc_id' => $this->getIntParameter('svc_cc_id'),
                'svc_rel_id' => $this->getIntParameter('svc_rel_id'),
                'svc_rate' => $this->getFloatParameter('svc_rate'),
                'svc_quantity' => $this->getFloatParameter('svc_quantity'),
                'svc_uom_id' => $this->getIntParameter('svc_uom_id'),
                'svc_tax_id' => $this->getIntParameter('svc_tax_id'),
                'svc_description' => $this->getStringParameter('svc_description'),
                'svc_total' => $total
            ];
            $svcDao = new ServiceOrderCostDao();
            if ($this->isValidParameter('svc_id')) {
                $svcDao->doUpdateTransaction($this->getIntParameter('svc_id'), $colVal);
            } else {
                $svcDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteServiceTaskCost') {
            $svcDao = new ServiceOrderCostDao();
            $svcDao->doHardDeleteTransaction($this->getIntParameter('svc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isValidParameter('svo_deleted_on') === false) {
            if ($this->isValidParameter('svr_id') === false || $this->isValidParameter('svr_reject_reason') === true) {
                $modal = $this->getRequestModal();
                $this->View->addModal($modal);
                $btnReq = new ModalButton('btnRequest', Trans::getFmsWord('request'), $modal->getModalId());
                $btnReq->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnReq);
            } elseif ($this->isValidParameter('svr_id') === true && $this->isValidParameter('svo_approved_on') === false &&
                $this->isManager()) {
                # Modal button approve
                $approveModal = $this->getApproveModal();
                $this->View->addModal($approveModal);
                $btnApprove = new ModalButton('btnApprove', Trans::getFmsWord('approve'), $approveModal->getModalId());
                $btnApprove->setIcon(Icon::ThumbsUp)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnApprove);
                # Modal button Reject
                $rejectModal = $this->getRejectModal();
                $this->View->addModal($rejectModal);
                $btnReject = new ModalButton('btnReject', Trans::getFmsWord('reject'), $rejectModal->getModalId());
                $btnReject->setIcon(Icon::Times)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnReject);
            } elseif ($this->isValidParameter('svo_approved_on') === true && $this->isValidParameter('svo_start_service_date') === false) {
                $startModal = $this->getStartServiceModal();
                $this->View->addModal($startModal);
                $btnStart = new ModalButton('btnStartService', Trans::getFmsWord('startService'), $startModal->getModalId());
                $btnStart->setIcon(Icon::Cog)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnStart);
            } elseif ($this->isValidParameter('svo_start_service_date') === true && $this->isValidParameter('svo_finish_on') === false) {
                $finishModal = $this->getFinishModal();
                $this->View->addModal($finishModal);
                $btnFinish = new ModalButton('btnFinish', Trans::getFmsWord('finish'), $finishModal->getModalId());
                $btnFinish->setIcon(Icon::CheckSquareO)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnFinish);
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        # Load service task detail data
        $wheres = [];
        $wheres[] = '(svd.svd_svo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svd.svd_deleted_on IS NULL)';
        $this->ServiceTasks = ServiceOrderDetailDao::loadData($wheres);

        return ServiceOrderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isValidParameter('svo_approved_on') === true && $this->isValidParameter('svo_start_service_date') === false) {
            $numberFormatter = new NumberFormatter();
            $wheres[] = '(eqm.eqm_eq_id = ' . $this->getIntParameter('svo_eq_id') . ')';
            $orderList[] = 'eqm.eqm_date DESC';
            $meterData = EquipmentMeterDao::loadData($wheres, $orderList, 1);
            if (\count($meterData) === 1) {
                $textMeter = Trans::getFmsWord('hourMeter');
                if ($this->getStringParameter('eq_primary_meter') === 'km') {
                    $textMeter = Trans::getFmsWord('odometer');
                }
                $meterNumber = $numberFormatter->doFormatFloat($meterData[0]['eqm_meter']);
                $meterInfo = 'Last ' . $textMeter . ' update: ' . $meterNumber . ' On ' . DateTimeParser::format($meterData[0]['eqm_date'], 'Y-m-d', 'd M Y');
                $this->setParameter('svo_meter', $meterData[0]['eqm_meter']);
                $this->setParameter('svo_meter_number', $meterNumber);
                $this->setParameter('svo_meter_info', $meterInfo);
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getServiceTaskFieldSet());
        if ($this->isValidParameter('svo_start_service_date') === true) {
            $this->Tab->addPortlet('general', $this->getServiceTaskCostFieldSet());
        }
        $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
        $this->overridePageTitle();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateServiceTask') {
            $this->Validation->checkRequire('svd_svt_id');
            $this->Validation->checkFloat('svd_est_cost');
            if ($this->isValidParameter('svd_remark')) {
                $this->Validation->checkRequire('svd_remark', 3, 255);
            }
            $this->Validation->checkUnique('svd_svt_id', 'service_order_detail', [
                'svd_id' => $this->getIntParameter('svd_id'),
            ], [
                'svd_svo_id' => $this->getDetailReferenceValue()
            ]);
        } elseif ($this->getFormAction() === 'doDeleteServiceTask') {
            $this->Validation->checkRequire('svd_id_del');
        } elseif ($this->getFormAction() === 'doRejectServiceOrder') {
            $this->Validation->checkRequire('reject_reason', 3, 255);
        } elseif ($this->getFormAction() === 'doStartServiceOrder') {
            $this->Validation->checkRequire('svo_start_service_date');
            $this->Validation->checkRequire('svo_meter');
            if ($this->isValidParameter('svo_start_service_date')) {
                $idEqu = $this->getIntParameter('svo_eq_id');
                $eqmDate = $this->getStringParameter('svo_start_service_date');
                $minMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($idEqu, $eqmDate, 'min');
                $maxMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($idEqu, $eqmDate, 'max');
                $this->Validation->checkFloat('svo_meter', $minMeterData['eqm_meter'], $maxMeterData['eqm_meter']);
            }
        } elseif ($this->getFormAction() === 'doUpdateServiceTaskCost') {
            $this->Validation->checkRequire('svc_svd_id');
            $this->Validation->checkRequire('svc_cc_id');
            $this->Validation->checkRequire('svc_rel_id');
            $this->Validation->checkFloat('svc_rate');
            $this->Validation->checkFloat('svc_quantity');
            $this->Validation->checkRequire('svc_uom_id');
            $this->Validation->checkRequire('svc_tax_id');
            $this->Validation->checkRequire('svc_description', 3, 255);
            $this->Validation->checkUnique('svc_svd_id', 'service_order_cost', [
                'svc_id' => $this->getIntParameter('svc_id'),
            ]);
        } elseif ($this->getFormAction() === 'doDeleteServiceTaskCost') {
            $this->Validation->checkRequire('svc_id_del');
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkRequire('doc_description', 3, 255);
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
//        $numberFormatter = new NumberFormatter();
//        $textMeter = Trans::getFmsWord('hourMeter');
//        if ($this->getStringParameter('eq_primary_meter') === 'km') {
//            $textMeter = Trans::getFmsWord('odometer');
//        }
        $data = [
            [
                'label' => Trans::getFmsWord('equipment'),
                'value' => $this->getStringParameter('svo_eq_name'),
            ],
            [
                'label' => Trans::getFmsWord('vendor'),
                'value' => $this->getStringParameter('svo_vendor_name'),
            ],
//            [
//                'label' => $textMeter,
//                'value' => $numberFormatter->doFormatFloat($this->getStringParameter('svo_meter')) . ' ' . $this->getStringParameter('eq_primary_meter'),
//            ],
            [
                'label' => Trans::getFmsWord('orderDate'),
                'value' => DateTimeParser::format($this->getStringParameter('svo_order_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getFmsWord('planningDate'),
                'value' => DateTimeParser::format($this->getStringParameter('svo_planning_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getFmsWord('manager'),
                'value' => $this->getStringParameter('svo_manager_name'),
            ],
            [
                'label' => Trans::getFmsWord('requestBy'),
                'value' => $this->getStringParameter('svo_request_by_name'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        $fieldSet->addHiddenField($this->Field->getHidden('svo_eq_id', $this->getIntParameter('svo_eq_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('svo_order_date', $this->getStringParameter('svo_order_date')));
        # Create a portlet box.
        $portlet = new Portlet('SvoGeneralPtl', Trans::getFmsWord('serviceOrder'));
        $portlet->addText($content);
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12);

        return $portlet;
    }

    /**
     * Function to get the meter service task Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getServiceTaskFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('srvTskPtl', Trans::getFmsWord('serviceTask'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvTskTbl');
        $table->setHeaderRow([
            'svd_svt_name' => Trans::getFmsWord('task'),
            'svd_est_cost' => Trans::getFmsWord('estCost'),
            'svd_remark' => Trans::getFmsWord('remark'),
        ]);
        $wheres[] = '(svd_svo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svd_deleted_on IS NULL )';
        $serviceTaskData = ServiceOrderDetailDao::loadData($wheres);
        $table->addRows($serviceTaskData);
        # Add special table attribute
        $table->setColumnType('svd_est_cost', 'currency');
        # add new modal button
        if ($this->isValidParameter('svr_id') === false || $this->isValidParameter('svr_reject_reason') === true) {
            $modal = $this->getServiceTaskModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getServiceTaskDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'serviceOrderDetail', 'getByReference', ['svd_id']);
            $table->setDeleteActionByModal($modalDelete, 'serviceOrderDetail', 'getByReferenceForDelete', ['svd_id']);
            $btnSrvTskMdl = new ModalButton('btnSrvTskMdl', Trans::getFmsWord('addServiceTask'), $modal->getModalId());
            $btnSrvTskMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnSrvTskMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Service Task modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getServiceTaskModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SrvTskMdl', Trans::getFmsWord('serviceTask'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateServiceTask');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateServiceTask' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create single select service task
        $taskField = $this->Field->getSingleSelect('serviceTask', 'svd_svt_name', $this->getParameterForModal('svd_svt_name', $showModal));
        $taskField->setHiddenField('svd_svt_id', $this->getParameterForModal('svd_svt_id', $showModal));
        $taskField->addParameter('svt_ss_id', $this->User->getSsId());
        $estCostField = $this->Field->getNumber('svd_est_cost', $this->getParameterForModal('svd_est_cost', $showModal));
        $remarkField = $this->Field->getText('svd_remark', $this->getParameterForModal('svd_remark', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('task'), $taskField, true);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField, true);
        $fieldSet->addField(Trans::getFmsWord('remark'), $remarkField);
        $fieldSet->addHiddenField($this->Field->getHidden('svd_id', $this->getParameterForModal('svd_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get service task delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getServiceTaskDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SrvTskDelMdl', Trans::getFmsWord('serviceTask'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteServiceTask');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteServiceTask' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        # Create single select service task
        $taskField = $this->Field->getText('svd_svt_name_del', $this->getParameterForModal('svd_svt_name_del', $showModal));
        $taskField->setReadOnly();
        $estCostField = $this->Field->getNumber('svd_est_cost_del', $this->getParameterForModal('svd_est_cost_del', $showModal));
        $estCostField->setReadOnly();
        $remarkField = $this->Field->getText('svd_remark_del', $this->getParameterForModal('svd_remark_del', $showModal));
        $remarkField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('task'), $taskField);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('remark'), $remarkField);
        $fieldSet->addHiddenField($this->Field->getHidden('svd_id_del', $this->getParameterForModal('svd_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get request confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRequestModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('ReqSrvTskMdl', Trans::getFmsWord('requestConfirmation'));
        if (empty($this->ServiceTasks) === true) {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getFmsWord('unableRequestServiceOrder');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getFmsWord('requestServiceOrder');
            $modal->setFormSubmit($this->getMainFormId(), 'doRequestServiceOrder');
        }
        $modal->setBtnOkName(Trans::getFmsWord('yesRequest'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get approve confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('ApproveSrvTskMdl', Trans::getFmsWord('approveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doApproveServiceOrder');
        $modal->setBtnOkName(Trans::getFmsWord('yesApprove'));
        $p = new Paragraph(Trans::getFmsWord('approveServiceOrder'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get reject confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRejectModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('RejectSrvTskMdl', Trans::getFmsWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doRejectServiceOrder');
        $showModal = false;
        if ($this->getFormAction() === 'doRejectServiceOrder' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        # Add field into field set.
        $rejectField = $this->Field->getTextArea('reject_reason', $this->getParameterForModal('reject_reason', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('rejectReason'), $rejectField, true);
        $p = new Paragraph(Trans::getFmsWord('rejectConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFmsWord('yesReject'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the service task cost fieldSet.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getServiceTaskCostFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('srvTskCostPtl', Trans::getFmsWord('serviceTaskCost'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvTskCostTbl');
        $table->setHeaderRow([
            'svc_svt_name' => Trans::getFmsWord('task'),
            'svc_cc_code' => Trans::getFmsWord('costCode'),
            'svc_rel_name' => Trans::getFmsWord('relation'),
            'svc_description' => Trans::getFmsWord('description'),
            'svc_rate' => Trans::getFmsWord('rate'),
            'svc_quantity' => Trans::getFmsWord('qty'),
            'svc_uom_name' => Trans::getFmsWord('uom'),
            'svc_tax_code' => Trans::getFmsWord('tax'),
            'svc_total' => Trans::getFmsWord('total')
        ]);
        $wheres[] = '(svc_svo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svc_deleted_on IS NULL )';
        $serviceTaskCostData = ServiceOrderCostDao::loadData($wheres);
        $table->addRows($serviceTaskCostData);
        # Add special table attribute
        $table->setColumnType('svc_rate', 'currency');
        $table->setColumnType('svc_total', 'currency');
        $table->setColumnType('svc_quantity', 'float');
        # add new modal button
        if ($this->isValidParameter('svo_finish_on') === false) {
            $modal = $this->getServiceTaskCostModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getServiceTaskCostDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'serviceOrderCost', 'getByReference', ['svc_id']);
            $table->setDeleteActionByModal($modalDelete, 'serviceOrderCost', 'getByReferenceForDelete', ['svc_id']);
            $btnSrvTskCostMdl = new ModalButton('btnSrvTskCostMdl', Trans::getFmsWord('addCost'), $modal->getModalId());
            $btnSrvTskCostMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnSrvTskCostMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Service Task cost modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getServiceTaskCostModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SrvTskCostMdl', Trans::getFmsWord('serviceTaskCost'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateServiceTaskCost');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateServiceTaskCost' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Job Container Field
        $svdField = $this->Field->getSingleSelectTable('serviceOrderDetail', 'svc_svt_name', $this->getParameterForModal('svc_svt_name', $showModal), 'loadServiceOrderDetailData');
        $svdField->setHiddenField('svc_svd_id', $this->getParameterForModal('svc_svd_id', $showModal));
        $svdField->setTableColumns([
            'svd_svt_name' => Trans::getFmsWord('task'),
            'svd_est_cost' => Trans::getFmsWord('estCost'),
        ]);
        $svdField->setAutoCompleteFields([
            'svc_svt_name' => 'svd_svt_name',
            'svc_est_cost' => 'svd_est_cost',
        ]);
        $svdField->setValueCode('svd_id');
        $svdField->setLabelCode('svd_svt_name');
        $svdField->addParameter('svd_svo_id', $this->getIntParameter('svo_id'));
        $svdField->addOptionalParameterById('svc_id', 'svc_id');
        $svdField->setParentModal($modal->getModalId());
        $this->View->addModal($svdField->getModal());
        $estCostField = $this->Field->getText('svc_est_cost', $this->getParameterForModal('svc_est_cost', $showModal));
        $estCostField->setReadOnly();
        $costCodeField = $this->Field->getSingleSelect('costCode', 'svc_cc_code', $this->getParameterForModal('svc_cc_code', $showModal));
        $costCodeField->setHiddenField('svc_cc_id', $this->getParameterForModal('svc_cc_id', $showModal));
        $costCodeField->addParameter('cc_ss_id', $this->User->getSsId());
        $costCodeField->setEnableNewButton(false);
        $costCodeField->setEnableDetailButton(false);
        $relationField = $this->Field->getSingleSelect('relation', 'svc_rel_name', $this->getParameterForModal('svc_rel_name', $showModal));
        $relationField->setHiddenField('svc_rel_id', $this->getParameterForModal('svc_rel_id', $showModal));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $rateField = $this->Field->getNumber('svc_rate', $this->getParameterForModal('svc_rate', $showModal));
        $qtyField = $this->Field->getNumber('svc_quantity', $this->getParameterForModal('svc_quantity', $showModal));
        $uomField = $this->Field->getSingleSelect('unit', 'svc_uom_name', $this->getParameterForModal('svc_uom_name', $showModal));
        $uomField->setHiddenField('svc_uom_id', $this->getParameterForModal('svc_uom_id', $showModal));
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);
        $taxField = $this->Field->getSingleSelect('tax', 'svc_tax_name', $this->getParameterForModal('svc_tax_name', $showModal));
        $taxField->setHiddenField('svc_tax_id', $this->getParameterForModal('svc_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);
        $descriptionField = $this->Field->getText('svc_description', $this->getParameterForModal('svc_description', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('task'), $svdField, true);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFmsWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getFmsWord('rate'), $rateField, true);
        $fieldSet->addField(Trans::getFmsWord('qty'), $qtyField, true);
        $fieldSet->addField(Trans::getFmsWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFmsWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFmsWord('description'), $descriptionField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('svc_id', $this->getParameterForModal('svc_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get service task delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getServiceTaskCostDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SrvTskDelMdl', Trans::getFmsWord('serviceTask'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteServiceTaskCost');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteServiceTaskCost' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        # Create single select service task
        $svdField = $this->Field->getText('svc_svt_name_del', $this->getParameterForModal('svc_svt_name_del', $showModal));
        $svdField->setReadOnly();
        $estCostField = $this->Field->getNumber('svc_est_cost_del', $this->getParameterForModal('svc_est_cost_del', $showModal));
        $estCostField->setReadOnly();
        $costCodeField = $this->Field->getText('svc_cc_code_del', $this->getParameterForModal('svc_cc_code_del', $showModal));
        $costCodeField->setReadOnly();
        $relationField = $this->Field->getText('svc_rel_name_del', $this->getParameterForModal('svc_rel_name_del', $showModal));
        $relationField->setReadOnly();
        $rateField = $this->Field->getNumber('svc_rate_del', $this->getParameterForModal('svc_rate_del', $showModal));
        $rateField->setReadOnly();
        $qtyField = $this->Field->getNumber('svc_quantity_del', $this->getParameterForModal('svc_quantity_del', $showModal));
        $qtyField->setReadOnly();
        $uomField = $this->Field->getText('svc_uom_name_del', $this->getParameterForModal('svc_uom_name_del', $showModal));
        $uomField->setReadOnly();
        $taxField = $this->Field->getText('svc_tax_name_del', $this->getParameterForModal('svc_tax_name_del', $showModal));
        $taxField->setReadOnly();
        $descriptionField = $this->Field->getText('svc_description_del', $this->getParameterForModal('svc_description_del', $showModal));
        $descriptionField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('task'), $svdField);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('costCode'), $costCodeField);
        $fieldSet->addField(Trans::getFmsWord('relation'), $relationField);
        $fieldSet->addField(Trans::getFmsWord('rate'), $rateField);
        $fieldSet->addField(Trans::getFmsWord('qty'), $qtyField);
        $fieldSet->addField(Trans::getFmsWord('uom'), $uomField);
        $fieldSet->addField(Trans::getFmsWord('tax'), $taxField);
        $fieldSet->addField(Trans::getFmsWord('description'), $descriptionField);
        $fieldSet->addHiddenField($this->Field->getHidden('svc_id_del', $this->getParameterForModal('svc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get start service confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getStartServiceModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('StartSrvMdl', Trans::getFmsWord('startServiceConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doStartServiceOrder');
        $showModal = false;
        if ($this->getFormAction() === 'doStartServiceOrder' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $dateField = $this->Field->getCalendar('svo_start_service_date', $this->getParameterForModal('svo_start_service_date', $showModal));
        $timeField = $this->Field->getTime('svo_start_service_time', $this->getParameterForModal('svo_start_service_time', $showModal));
        $meterField = $this->Field->getNumber('svo_meter', $this->getParameterForModal('svo_meter', true));
        $meterInfoField = $this->Field->getText('svo_meter_info', $this->getStringParameter('svo_meter_info'));
        $meterInfoField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('startDate'), $dateField, true);
        $fieldSet->addField(Trans::getFmsWord('startTime'), $timeField);
        $fieldSet->addField(Trans::getFmsWord('meter'), $meterField, true);
        $fieldSet->addField(Trans::getFmsWord('meterInfo'), $meterInfoField);
        $p = new Paragraph(Trans::getFmsWord('startServiceConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFmsWord('yesStartService'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get finish confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getFinishModal(): Modal
    {
        $errors = $this->doValidateCompleteFillCost();
        # Create Fields.
        $modal = new Modal('FinishSrvTskMdl', Trans::getFmsWord('finishConfirmation'));
        if (empty($errors) === false) {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = implode(' <br/>', $errors);
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getFmsWord('finishServiceOrder');
            $modal->setFormSubmit($this->getMainFormId(), 'doFinishServiceOrder');
        }
        $modal->setBtnOkName(Trans::getFmsWord('yesFinish'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get document Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('SvoDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete')
        ]);
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'serviceorder')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = "(dct.dct_master = 'Y')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            if ((int)$row['doc_group_reference'] === $this->getDetailReferenceValue()) {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('SvoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        # create modal.
        $docModal = $this->getDocumentModal();
        $this->View->addModal($docModal);
        $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
        $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnDocMdl);

        return $portlet;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('SvoDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', 'serviceorder');
        $dctFields->addParameter('dct_master', 'Y');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('SvoDocDelMdl', Trans::getWord('deleteDocument'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to update service reminder next due date
     *
     * @return void
     */
    private function doUpdateServiceReminderNextDueDate(): void
    {
        $wheres = [];
        $wheres[] = '(svd.svd_svo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svd.svd_deleted_on IS NULL)';
        $serviceOrderDetailData = ServiceOrderDetailDao::loadData($wheres);
        $startServiceDate = DateTimeParser::format($this->getStringParameter('svo_start_service_date'), 'Y-m-d', 'Y-m-d');
        foreach ($serviceOrderDetailData AS $row) {
            $dateModify = null;
            $serviceReminderData = ServiceReminderDao::getByEqIdSvtId($this->getIntParameter('svo_eq_id'), $row['svd_svt_id']);
            if (empty($serviceReminderData) === false && empty($serviceReminderData['svrm_time_interval']) === false) {
                $nextDueDateModify = DateTimeParser::createDateTime($startServiceDate);
                $nextDueDateModify->modify('+' . $serviceReminderData['svrm_time_interval'] . ' ' . $serviceReminderData['svrm_time_interval_period']);
                $nextDue = $nextDueDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextDue);
                $nextThresholdDateModify->modify('-' . $serviceReminderData['svrm_time_threshold'] . ' ' . $serviceReminderData['svrm_time_threshold_period']);
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
                $colVal = [
                    'svrm_next_due_date' => $nextDue,
                    'svrm_next_due_date_threshold' => $nextThreshold
                ];
                $svrmDao = new ServiceReminderDao();
                $svrmDao->doUpdateTransaction($serviceReminderData['svrm_id'], $colVal);
            }
        }

    }

    /**
     * Function to check is user manager
     *
     * @return bool
     */
    private function isManager(): bool
    {
        $result = false;
        if ($this->getIntParameter('svo_manager_id') === $this->User->getId()) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to check are all service task's cost filled
     *
     * @return array
     */
    private function doValidateCompleteFillCost(): array
    {
        $result = [];
        $diffQty = ServiceOrderCostDao::getTotalDifferentServiceTaskCost($this->getDetailReferenceValue());
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getFmsWord('serviceCostNotMatch', '', [
                    'serviceTask' => $diffQty['total_svd'],
                    'serviceTaskCost' => $diffQty['total_svc'],
                ]);
            }
        } else {
            $result[] = Trans::getFmsWord('serviceCostEmpty');
        }

        return $result;
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('svo_number');
        $status = new LabelGray(Trans::getFmsWord('draft'));
        if ($this->isValidParameter('svo_deleted_on')) {
            $status = new LabelDark(Trans::getFmsWord('deleted'));
            $this->View->addWarningMessage(Trans::getWord('delete') . ' : ' . $this->getStringParameter('svo_deleted_reason'));
        } elseif ($this->isValidParameter('svo_finish_on')) {
            $status = new LabelSuccess(Trans::getFmsWord('finish'));
        } elseif ($this->isValidParameter('svo_finish_on') === false && $this->isValidParameter('svo_start_service_date') === true) {
            $status = new LabelPrimary(Trans::getFmsWord('onService'));
        } elseif ($this->isValidParameter('svo_start_service_date') === false && $this->isValidParameter('svo_approved_on') === true) {
            $status = new LabelInfo(Trans::getFmsWord('approved'));
        } elseif ($this->isValidParameter('svo_approved_on') === false && $this->isValidParameter('svr_id') === true) {
            if ($this->isValidParameter('svr_reject_reason')) {
                $status = new LabelDanger(Trans::getFmsWord('reject'));
                $this->View->addWarningMessage(Trans::getWord('reject') . ' : ' . $this->getStringParameter('svr_reject_reason'));
            } else {
                $status = new LabelWarning(Trans::getFmsWord('request'));
            }
        }
        $this->View->setDescription($title . ' | ' . $status);

    }
}
