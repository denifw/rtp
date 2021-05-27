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
use App\Frame\Formatter\DateTimeParser;
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
use App\Model\Dao\Fms\RenewalFulfillmentDao;
use App\Model\Dao\Fms\RenewalOrderCostDao;
use App\Model\Dao\Fms\RenewalOrderDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Fms\RenewalOrderDetailDao;
use App\Model\Dao\Fms\RenewalOrderRequestDao;
use App\Model\Dao\Fms\RenewalReminderDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail RenewalOrder page
 *
 * @package    app
 * @subpackage Model\Viewer\Fms
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrder extends AbstractViewerModel
{
    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    private $RenewalData = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'renewalOrder', 'rno_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateRenewal') {
            $colVal = [
                'rnd_rno_id' => $this->getDetailReferenceValue(),
                'rnd_rnt_id' => $this->getIntParameter('rnd_rnt_id'),
                'rnd_est_cost' => $this->getFloatParameter('rnd_est_cost'),
                'rnd_remark' => $this->getStringParameter('rnd_remark'),
            ];
            $rndDao = new RenewalOrderDetailDao();
            if ($this->isValidParameter('rnd_id')) {
                $rndDao->doUpdateTransaction($this->getIntParameter('rnd_id'), $colVal);
            } else {
                $rndDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteRenewal') {
            $rndDao = new RenewalOrderDetailDao();
            $rndDao->doHardDeleteTransaction($this->getIntParameter('rnd_id_del'));
        } elseif ($this->getFormAction() === 'doRequestRenewalOrder') {
            $colVal = [
                'rnr_rno_id' => $this->getDetailReferenceValue(),
            ];
            $rnrDao = new RenewalOrderRequestDao();
            $rnrDao->doInsertTransaction($colVal);
            $colVal = [
                'rno_rnr_id' => $rnrDao->getLastInsertId(),
            ];
            $rnoDao = new RenewalOrderDao();
            $rnoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doApproveRenewalOrder') {
            $colVal = [
                'rno_approved_on' => date('Y-m-d H:i:s'),
                'rno_approved_by' => $this->User->getId(),
            ];
            $rnoDao = new RenewalOrderDao();
            $rnoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doRejectRenewalOrder') {
            $colVal = [
                'rnr_rno_id' => $this->getDetailReferenceValue(),
                'rnr_reject_reason' => $this->getStringParameter('reject_reason'),
            ];
            $rnrDao = new RenewalOrderRequestDao();
            $rnrDao->doInsertTransaction($colVal);
            $colVal = [
                'rno_rnr_id' => $rnrDao->getLastInsertId(),
            ];
            $rnoDao = new RenewalOrderDao();
            $rnoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doStartRenewalOrder') {
            $colVal = [
                'rno_start_renewal_date' => $this->getStringParameter('rno_start_renewal_date'),
                'rno_start_renewal_time' => $this->getStringParameter('rno_start_renewal_time'),
                'rno_start_renewal_by' => $this->User->getId(),
            ];
            $rnoDao = new RenewalOrderDao();
            $rnoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            $this->doUpdateRenewalReminderNextExpiryDate();
        } elseif ($this->getFormAction() === 'doFinishRenewalOrder') {
            $colVal = [
                'rno_finish_on' => date('Y-m-d H:i:s'),
                'rno_finish_by' => $this->User->getId(),
            ];
            $rnoDao = new RenewalOrderDao();
            $rnoDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->getFormAction() === 'doUpdateRenewalCost') {
            $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('rnc_tax_id'));
            $rate = $this->getFloatParameter('rnc_rate') * $this->getFloatParameter('rnc_quantity');
            $taxAmount = ($rate * $taxPercent) / 100;
            $total = $rate + $taxAmount;
            $colVal = [
                'rnc_rno_id' => $this->getDetailReferenceValue(),
                'rnc_rnd_id' => $this->getIntParameter('rnc_rnd_id'),
                'rnc_cc_id' => $this->getIntParameter('rnc_cc_id'),
                'rnc_rel_id' => $this->getIntParameter('rnc_rel_id'),
                'rnc_rate' => $this->getFloatParameter('rnc_rate'),
                'rnc_quantity' => $this->getFloatParameter('rnc_quantity'),
                'rnc_uom_id' => $this->getIntParameter('rnc_uom_id'),
                'rnc_tax_id' => $this->getIntParameter('rnc_tax_id'),
                'rnc_description' => $this->getStringParameter('rnc_description'),
                'rnc_total' => $total,
            ];
            $rncDao = new RenewalOrderCostDao();
            if ($this->isValidParameter('rnc_id')) {
                $rncDao->doUpdateTransaction($this->getIntParameter('rnc_id'), $colVal);
            } else {
                $rncDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteRenewalCost') {
            $rncDao = new RenewalOrderCostDao();
            $rncDao->doHardDeleteTransaction($this->getIntParameter('rnc_id_del'));
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
        if ($this->isValidParameter('rno_deleted_on') === false) {
            if ($this->isValidParameter('rnr_id') === false || $this->isValidParameter('rnr_reject_reason') === true) {
                $modal = $this->getRequestModal();
                $this->View->addModal($modal);
                $btnReq = new ModalButton('btnRequest', Trans::getFmsWord('request'), $modal->getModalId());
                $btnReq->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnReq);
            } elseif ($this->isValidParameter('rnr_id') === true && $this->isValidParameter('rno_approved_on') === false &&
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
            } elseif ($this->isValidParameter('rno_approved_on') === true && $this->isValidParameter('rno_start_renewal_date') === false) {
                $startModal = $this->getStartRenewalModal();
                $this->View->addModal($startModal);
                $btnStart = new ModalButton('btnStartRenewal', Trans::getFmsWord('startRenewal'), $startModal->getModalId());
                $btnStart->setIcon(Icon::Cog)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButtonAtTheBeginning($btnStart);
            } elseif ($this->isValidParameter('rno_start_renewal_date') === true && $this->isValidParameter('rno_finish_on') === false) {
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
        # Load renewal order detail data
        $wheres = [];
        $wheres[] = '(rnd.rnd_rno_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnd.rnd_deleted_on IS NULL)';
        $this->RenewalData = RenewalOrderDetailDao::loadData($wheres);

        return RenewalOrderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getRenewalFieldSet());
        if ($this->isValidParameter('rno_start_renewal_date') === true) {
            $this->Tab->addPortlet('general', $this->getRenewalCostFieldSet());
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
        if ($this->getFormAction() === 'doUpdateRenewal') {
            $this->Validation->checkRequire('rnd_rnt_id');
            $this->Validation->checkFloat('rnd_est_cost');
            if ($this->isValidParameter('rnd_remark')) {
                $this->Validation->checkRequire('rnd_remark', 3, 255);
            }
            $this->Validation->checkUnique('rnd_rnt_id', 'renewal_order_detail', [
                'rnd_id' => $this->getIntParameter('rnd_id'),
            ], [
                'rnd_rno_id' => $this->getDetailReferenceValue(),
            ]);
        } elseif ($this->getFormAction() === 'doDeleteRenewal') {
            $this->Validation->checkRequire('rnd_id_del');
        } elseif ($this->getFormAction() === 'doRejectRenewalOrder') {
            $this->Validation->checkRequire('reject_reason', 3, 255);
        } elseif ($this->getFormAction() === 'doStartRenewalOrder') {
            $this->Validation->checkRequire('rno_start_renewal_date');
        } elseif ($this->getFormAction() === 'doUpdateRenewalCost') {
            $this->Validation->checkRequire('rnc_rnd_id');
            $this->Validation->checkRequire('rnc_cc_id');
            $this->Validation->checkRequire('rnc_rel_id');
            $this->Validation->checkFloat('rnc_rate');
            $this->Validation->checkFloat('rnc_quantity');
            $this->Validation->checkRequire('rnc_uom_id');
            $this->Validation->checkRequire('rnc_tax_id');
            $this->Validation->checkRequire('rnc_description', 3, 255);
            $this->Validation->checkUnique('rnc_rnd_id', 'renewal_order_cost', [
                'rnc_id' => $this->getIntParameter('rnc_id'),
            ]);
        } elseif ($this->getFormAction() === 'doDeleteRenewalCost') {
            $this->Validation->checkRequire('rnc_id_del');
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
    private function getGeneralFieldSet(): Portlet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        $data = [
            [
                'label' => Trans::getFmsWord('equipment'),
                'value' => $this->getStringParameter('rno_eq_name'),
            ],
            [
                'label' => Trans::getFmsWord('vendor'),
                'value' => $this->getStringParameter('rno_vendor_name'),
            ],
            [
                'label' => Trans::getFmsWord('orderDate'),
                'value' => DateTimeParser::format($this->getStringParameter('rno_order_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getFmsWord('planningDate'),
                'value' => DateTimeParser::format($this->getStringParameter('rno_planning_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getFmsWord('manager'),
                'value' => $this->getStringParameter('rno_manager_name'),
            ],
            [
                'label' => Trans::getFmsWord('requestBy'),
                'value' => $this->getStringParameter('rno_request_by_name'),
            ],
            [
                'label' => Trans::getFmsWord('processDate'),
                'value' => DateTimeParser::format($this->getStringParameter('rno_start_renewal_date'), 'Y-m-d', 'd M Y'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        $fieldSet->addHiddenField($this->Field->getHidden('rno_eq_id', $this->getIntParameter('rno_eq_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('rno_order_date', $this->getStringParameter('rno_order_date')));
        # Create a portlet box.
        $portlet = new Portlet('RnoGeneralPtl', Trans::getFmsWord('renewalOrder'));
        $portlet->addText($content);
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12);

        return $portlet;
    }

    /**
     * Function to get the renewal fieldSet.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRenewalFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('rndPtl', Trans::getFmsWord('renewalItems'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rndTbl');
        $table->setHeaderRow([
            'rnd_rnt_name' => Trans::getFmsWord('renewalType'),
            'rnd_est_cost' => Trans::getFmsWord('estCost'),
            'rnd_remark' => Trans::getFmsWord('remark'),
        ]);
        $wheres[] = '(rnd_rno_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnd_deleted_on IS NULL )';
        $renewalData = RenewalOrderDetailDao::loadData($wheres);
        $table->addRows($renewalData);
        # Add special table attribute
        $table->setColumnType('rnd_est_cost', 'currency');
        # add new modal button
        if ($this->isValidParameter('rnr_id') === false || $this->isValidParameter('rnr_reject_reason') === true) {
            $modal = $this->getRenewalModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getRenewalDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'renewalOrderDetail', 'getByReference', ['rnd_id']);
            $table->setDeleteActionByModal($modalDelete, 'renewalOrderDetail', 'getByReferenceForDelete', ['rnd_id']);
            $btnSrvTskMdl = new ModalButton('btnRndMdl', Trans::getFmsWord('addRenewal'), $modal->getModalId());
            $btnSrvTskMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnSrvTskMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Renewal modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRenewalModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('RndMdl', Trans::getFmsWord('renewal'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateRenewal');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateRenewal' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create single select renewal type
        $renewalField = $this->Field->getSingleSelect('renewalType', 'rnd_rnt_name', $this->getParameterForModal('rnd_rnt_name', $showModal));
        $renewalField->setHiddenField('rnd_rnt_id', $this->getParameterForModal('rnd_rnt_id', $showModal));
        $renewalField->addParameter('rnt_ss_id', $this->User->getSsId());
        $estCostField = $this->Field->getNumber('rnd_est_cost', $this->getParameterForModal('rnd_est_cost', $showModal));
        $remarkField = $this->Field->getText('rnd_remark', $this->getParameterForModal('rnd_remark', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('renewalType'), $renewalField, true);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField, true);
        $fieldSet->addField(Trans::getFmsWord('remark'), $remarkField);
        $fieldSet->addHiddenField($this->Field->getHidden('rnd_id', $this->getParameterForModal('rnd_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get renewal delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRenewalDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('RndDelMdl', Trans::getFmsWord('renewal'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteRenewal');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteRenewal' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        # Create single select renewal type
        $taskField = $this->Field->getText('rnd_rnt_name_del', $this->getParameterForModal('rnd_rnt_name_del', $showModal));
        $taskField->setReadOnly();
        $estCostField = $this->Field->getNumber('rnd_est_cost_del', $this->getParameterForModal('rnd_est_cost_del', $showModal));
        $estCostField->setReadOnly();
        $remarkField = $this->Field->getText('rnd_remark_del', $this->getParameterForModal('rnd_remark_del', $showModal));
        $remarkField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('task'), $taskField);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('remark'), $remarkField);
        $fieldSet->addHiddenField($this->Field->getHidden('rnd_id_del', $this->getParameterForModal('rnd_id_del', $showModal)));
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
        $modal = new Modal('ReqRndMdl', Trans::getFmsWord('requestConfirmation'));
        if (empty($this->RenewalData) === true) {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getFmsWord('unableRequestRenewalOrder');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getFmsWord('requestRenewalOrder');
            $modal->setFormSubmit($this->getMainFormId(), 'doRequestRenewalOrder');
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
        $modal = new Modal('ApproveRndMdl', Trans::getFmsWord('approveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doApproveRenewalOrder');
        $modal->setBtnOkName(Trans::getFmsWord('yesApprove'));
        $p = new Paragraph(Trans::getFmsWord('approveRenewalOrder'));
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
        $modal = new Modal('RejectRndMdl', Trans::getFmsWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doRejectRenewalOrder');
        $showModal = false;
        if ($this->getFormAction() === 'doRejectRenewalOrder' && $this->isValidPostValues() === false) {
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
     * Function to get the renewal cost fieldSet.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRenewalCostFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('RndCostPtl', Trans::getFmsWord('renewalCost'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rndCostTbl');
        $table->setHeaderRow([
            'rnc_rnt_name' => Trans::getFmsWord('renewal'),
            'rnc_cc_code' => Trans::getFmsWord('costCode'),
            'rnc_rel_name' => Trans::getFmsWord('relation'),
            'rnc_description' => Trans::getFmsWord('description'),
            'rnc_rate' => Trans::getFmsWord('rate'),
            'rnc_quantity' => Trans::getFmsWord('qty'),
            'rnc_uom_name' => Trans::getFmsWord('uom'),
            'rnc_tax_name' => Trans::getFmsWord('tax'),
            'rnc_total' => Trans::getFmsWord('total'),
        ]);
        $wheres[] = '(rnc_rno_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnc_deleted_on IS NULL )';
        $renewalCostData = RenewalOrderCostDao::loadData($wheres);
        $table->addRows($renewalCostData);
        # Add special table attribute
        $table->setColumnType('rnc_rate', 'currency');
        $table->setColumnType('rnc_total', 'currency');
        $table->setColumnType('rnc_quantity', 'float');
        # add new modal button
        if ($this->isValidParameter('rno_finish_on') === false) {
            $modal = $this->getRenewalCostModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getRenewalCostDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'renewalOrderCost', 'getByReference', ['rnc_id']);
            $table->setDeleteActionByModal($modalDelete, 'renewalOrderCost', 'getByReferenceForDelete', ['rnc_id']);
            $btnRndCostMdl = new ModalButton('btnRndCostMdl', Trans::getFmsWord('addCost'), $modal->getModalId());
            $btnRndCostMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnRndCostMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Renewal cost modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRenewalCostModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('RndCostMdl', Trans::getFmsWord('renewalCost'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateRenewalCost');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateRenewalCost' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Job Container Field
        $rndField = $this->Field->getSingleSelectTable('renewalOrderDetail', 'rnc_rnt_name', $this->getParameterForModal('rnc_rnt_name', $showModal), 'loadRenewalOrderDetailData');
        $rndField->setHiddenField('rnc_rnd_id', $this->getParameterForModal('rnc_rnd_id', $showModal));
        $rndField->setTableColumns([
            'rnc_rnt_name' => Trans::getFmsWord('renewal'),
            'rnd_est_cost' => Trans::getFmsWord('estCost'),
        ]);
        $rndField->setAutoCompleteFields([
            'rnc_rnt_name' => 'rnd_svt_name',
            'rnc_est_cost' => 'rnd_est_cost',
        ]);
        $rndField->setValueCode('rnd_id');
        $rndField->setLabelCode('rnd_rnt_name');
        $rndField->addParameter('rnd_rno_id', $this->getIntParameter('rno_id'));
        $rndField->addOptionalParameterById('rnc_id', 'rnc_id');
        $rndField->setParentModal($modal->getModalId());
        $this->View->addModal($rndField->getModal());
        $estCostField = $this->Field->getText('rnc_est_cost', $this->getParameterForModal('rnc_est_cost', $showModal));
        $estCostField->setReadOnly();
        $costCodeField = $this->Field->getSingleSelect('costCode', 'rnc_cc_code', $this->getParameterForModal('rnc_cc_code', $showModal));
        $costCodeField->setHiddenField('rnc_cc_id', $this->getParameterForModal('rnc_cc_id', $showModal));
        $costCodeField->addParameter('cc_ss_id', $this->User->getSsId());
        $costCodeField->addParameter('ccg_type', 'P');
        $costCodeField->setEnableNewButton(false);
        $costCodeField->setEnableDetailButton(false);
        $relationField = $this->Field->getSingleSelect('relation', 'rnc_rel_name', $this->getParameterForModal('rnc_rel_name', $showModal));
        $relationField->setHiddenField('rnc_rel_id', $this->getParameterForModal('rnc_rel_id', $showModal));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        $rateField = $this->Field->getNumber('rnc_rate', $this->getParameterForModal('rnc_rate', $showModal));
        $qtyField = $this->Field->getNumber('rnc_quantity', $this->getParameterForModal('rnc_quantity', $showModal));
        $uomField = $this->Field->getSingleSelect('unit', 'rnc_uom_name', $this->getParameterForModal('rnc_uom_name', $showModal));
        $uomField->setHiddenField('rnc_uom_id', $this->getParameterForModal('rnc_uom_id', $showModal));
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);
        $taxField = $this->Field->getSingleSelect('tax', 'rnc_tax_name', $this->getParameterForModal('rnc_tax_name', $showModal));
        $taxField->setHiddenField('rnc_tax_id', $this->getParameterForModal('rnc_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableDetailButton(false);
        $taxField->setEnableNewButton(false);
        $descriptionField = $this->Field->getText('rnc_description', $this->getParameterForModal('rnc_description', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('renewal'), $rndField, true);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFmsWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getFmsWord('rate'), $rateField, true);
        $fieldSet->addField(Trans::getFmsWord('qty'), $qtyField, true);
        $fieldSet->addField(Trans::getFmsWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFmsWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFmsWord('description'), $descriptionField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('rnc_id', $this->getParameterForModal('rnc_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get renewal delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getRenewalCostDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SrvTskDelMdl', Trans::getFmsWord('renewal'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteRenewalCost');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteRenewalCost' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $rndField = $this->Field->getText('rnc_rnt_name_del', $this->getParameterForModal('rnc_rnt_name_del', $showModal));
        $rndField->setReadOnly();
        $estCostField = $this->Field->getNumber('rnc_est_cost_del', $this->getParameterForModal('rnc_est_cost_del', $showModal));
        $estCostField->setReadOnly();
        $costCodeField = $this->Field->getText('rnc_cc_code_del', $this->getParameterForModal('rnc_cc_code_del', $showModal));
        $costCodeField->setReadOnly();
        $relationField = $this->Field->getText('rnc_rel_name_del', $this->getParameterForModal('rnc_rel_name_del', $showModal));
        $relationField->setReadOnly();
        $rateField = $this->Field->getNumber('rnc_rate_del', $this->getParameterForModal('rnc_rate_del', $showModal));
        $rateField->setReadOnly();
        $qtyField = $this->Field->getNumber('rnc_quantity_del', $this->getParameterForModal('rnc_quantity_del', $showModal));
        $qtyField->setReadOnly();
        $uomField = $this->Field->getText('rnc_uom_name_del', $this->getParameterForModal('rnc_uom_name_del', $showModal));
        $uomField->setReadOnly();
        $taxField = $this->Field->getText('rnc_tax_name_del', $this->getParameterForModal('rnc_tax_name_del', $showModal));
        $taxField->setReadOnly();
        $descriptionField = $this->Field->getText('rnc_description_del', $this->getParameterForModal('rnc_description_del', $showModal));
        $descriptionField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('renewal'), $rndField);
        $fieldSet->addField(Trans::getFmsWord('estCost'), $estCostField);
        $fieldSet->addField(Trans::getFmsWord('costCode'), $costCodeField);
        $fieldSet->addField(Trans::getFmsWord('relation'), $relationField);
        $fieldSet->addField(Trans::getFmsWord('rate'), $rateField);
        $fieldSet->addField(Trans::getFmsWord('qty'), $qtyField);
        $fieldSet->addField(Trans::getFmsWord('uom'), $uomField);
        $fieldSet->addField(Trans::getFmsWord('tax'), $taxField);
        $fieldSet->addField(Trans::getFmsWord('description'), $descriptionField);
        $fieldSet->addHiddenField($this->Field->getHidden('rnc_id_del', $this->getParameterForModal('rnc_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get start renewal confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getStartRenewalModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('StartRndMdl', Trans::getFmsWord('startRenewalConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doStartRenewalOrder');
        $showModal = false;
        if ($this->getFormAction() === 'doStartRenewalOrder' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $dateField = $this->Field->getCalendar('rno_start_renewal_date', $this->getParameterForModal('rno_start_renewal_date', $showModal));
        $timeField = $this->Field->getTime('rno_start_renewal_time', $this->getParameterForModal('rno_start_renewal_time', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getFmsWord('startDate'), $dateField, true);
        $fieldSet->addField(Trans::getFmsWord('startTime'), $timeField);
        $p = new Paragraph(Trans::getFmsWord('startRenewalConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFmsWord('yesStartRenewal'));
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
        $modal = new Modal('FinishRndMdl', Trans::getFmsWord('finishConfirmation'));
        if (empty($errors) === false) {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = implode(' <br/>', $errors);
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getFmsWord('finishRenewalOrder');
            $modal->setFormSubmit($this->getMainFormId(), 'doFinishRenewalOrder');
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
        $docTable = new Table('RnoDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete'),
        ]);
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'renewalorder')";
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
        $portlet = new Portlet('RnoDocPtl', Trans::getWord('document'));
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
        $modal = new Modal('RnoDocMdl', Trans::getWord('documents'));
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
        $dctFields->addParameter('dcg_code', 'renewalorder');
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
        $modal = new Modal('RnoDocDelMdl', Trans::getWord('deleteDocument'));
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
     * Function to check is user manager
     *
     * @return bool
     */
    private function isManager(): bool
    {
        $result = false;
        if ($this->getIntParameter('rno_manager_id') === $this->User->getId()) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to update renewal reminder next expiry date
     *
     * @return void
     */
    private function doUpdateRenewalReminderNextExpiryDate(): void
    {
        $wheres = [];
        $wheres[] = '(rnd.rnd_rno_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnd.rnd_deleted_on IS NULL)';
        $renewalOrderDetailData = RenewalOrderDetailDao::loadData($wheres);
        foreach ($renewalOrderDetailData as $row) {
            $dateModify = null;
            $renewalReminderData = RenewalReminderDao::getByEqIdRntId($this->getIntParameter('rno_eq_id'), $row['rnd_rnt_id']);
            if (empty($renewalReminderData) === false && empty($renewalReminderData['rnrm_interval']) === false) {
                $startRenewalDate = DateTimeParser::format($renewalReminderData['rnrm_expiry_date'], 'Y-m-d', 'Y-m-d');
                $nextExpiryDateModify = DateTimeParser::createDateTime($startRenewalDate);
                $nextExpiryDateModify->modify('+' . $renewalReminderData['rnrm_interval'] . ' ' . $renewalReminderData['rnrm_interval_period']);
                $nextExpiryDate = $nextExpiryDateModify->format('Y-m-d');
                # Next threshold date
                $nextThresholdDateModify = DateTimeParser::createDateTime($nextExpiryDate);
                $nextThresholdDateModify->modify('-' . $renewalReminderData['rnrm_threshold'] . ' ' . $renewalReminderData['rnrm_threshold_period']);
                $nextThreshold = $nextThresholdDateModify->format('Y-m-d');
                $colVal = [
                    'rnrm_expiry_date' => $nextExpiryDate,
                    'rnrm_expiry_threshold_date' => $nextThreshold,
                ];
                $rnrmDao = new RenewalReminderDao();
                $rnrmDao->doUpdateTransaction($renewalReminderData['rnrm_id'], $colVal);
                # Do inser renewal fulfillment
                $colValRni = [
                    'rnf_rno_id' => $this->getDetailReferenceValue(),
                    'rnf_rnrm_id' => $renewalReminderData['rnrm_id'],
                    'rnf_expiry_date' => $renewalReminderData['rnrm_expiry_date'],
                    'rnf_fulfillment_date' => $this->getStringParameter('rno_start_renewal_date'),
                ];
                $rniDao = new RenewalFulfillmentDao();
                $rniDao->doInsertTransaction($colValRni);
            }
        }

    }

    /**
     * Function to check are all renewal item's cost filled
     *
     * @return array
     */
    private function doValidateCompleteFillCost(): array
    {
        $result = [];
        $diffQty = RenewalOrderCostDao::getTotalDifferentRenewalCost($this->getDetailReferenceValue());
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getFmsWord('renewalCostNotMatch', '', [
                    'renewal' => $diffQty['total_rnd'],
                    'renewalCost' => $diffQty['total_rnc'],
                ]);
            }
        } else {
            $result[] = Trans::getFmsWord('renewalCostEmpty');
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
        $title = $this->getStringParameter('rno_number');
        $status = new LabelGray(Trans::getFmsWord('draft'));
        if ($this->isValidParameter('rno_deleted_on')) {
            $status = new LabelDark(Trans::getFmsWord('deleted'));
            $this->View->addWarningMessage(Trans::getWord('delete') . ' : ' . $this->getStringParameter('rno_deleted_reason'));
        } elseif ($this->isValidParameter('rno_finish_on')) {
            $status = new LabelSuccess(Trans::getFmsWord('finish'));
        } elseif ($this->isValidParameter('rno_finish_on') === false && $this->isValidParameter('rno_start_renewal_date') === true) {
            $status = new LabelPrimary(Trans::getFmsWord('onProgress'));
        } elseif ($this->isValidParameter('rno_start_renewal_date') === false && $this->isValidParameter('rno_approved_on') === true) {
            $status = new LabelInfo(Trans::getFmsWord('approved'));
        } elseif ($this->isValidParameter('rno_approved_on') === false && $this->isValidParameter('rnr_id') === true) {
            if ($this->isValidParameter('rnr_reject_reason')) {
                $status = new LabelDanger(Trans::getFmsWord('reject'));
                $this->View->addWarningMessage(Trans::getWord('reject') . ' : ' . $this->getStringParameter('rnr_reject_reason'));
            } else {
                $status = new LabelWarning(Trans::getFmsWord('request'));
            }
        }
        $this->View->setDescription($title . ' | ' . $status);

    }

}
