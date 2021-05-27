<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Warehouse\Action;


use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\System\View;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class OutboundAction extends AbstractBaseJobAction
{

    /**
     * Function to add layout for action to view
     *
     * @param View $View
     *
     * @return void
     */
    public function addActionView(View $View): void
    {
        $this->loadAction();
        if (empty($this->Action) === false) {
            switch ($this->Action['jac_action']) {
                case 'Picking' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcPickMdl', 'doActionStartPick');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnPick', $modal->getModalId(), Icon::Cubes));
                    } else {
                        $errors = $this->doValidateCompletePicking();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndPickMdl', 'doActionEndPick', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndPickMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndPickAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'Arrive' :
                    $modal = $this->getArriveModal('AcArriveMdl', 'doActionArrive');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnArrive', $modal->getModalId(), Icon::Truck));
                    break;
                case 'Document' :
                    $modal = $this->getDefaultModal('AcDocMdl', 'doActionDocument');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnDocAc', $modal->getModalId(), Icon::File));
                    break;
                case 'Load' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartLoadMdl', 'doActionStartLoad');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartLoadAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $errors = $this->doValidateCompleteLoading();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndLoadMdl', 'doActionEndLoad', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndLoadMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndLoadAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
            }
        }
    }

    /**
     * Function to get document action modal.
     *
     * @param string $modalId     To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    private function getArriveModal($modalId, $modalSubmit, $actionIndex = ''): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getWord('actionConfirmation'));
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('jac_date') === false) {
                $this->Model->setParameter('jac_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('jac_time') === false) {
                $this->Model->setParameter('jac_time', date('H:i'));
            }
            if ($this->Model->isValidParameter('job_ata_date') === false) {
                $this->Model->setParameter('job_ata_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('job_ata_time') === false) {
                $this->Model->setParameter('job_ata_time', date('H:i'));
            }
        }        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(12);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));

        # Create Shipper or Consignee Field
        $vendorField = $this->Model->getField()->getSingleSelect('relation', 'job_vendor', $this->Model->getStringParameter('job_vendor'));
        $vendorField->setHiddenField('job_vendor_id', $this->Model->getIntParameter('job_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('job_vendor_pic');
        $vendorField->addClearField('job_pic_vendor');


        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        $fieldSet->addField(Trans::getWord('driver'), $this->Model->getField()->getText('job_driver', $this->Model->getParameterForModal('job_driver', true)), true);
        $fieldSet->addField(Trans::getWord('transporter'), $vendorField, true);
        $fieldSet->addField(Trans::getWord('truckPlate'), $this->Model->getField()->getText('job_truck_number', $this->Model->getParameterForModal('job_truck_number', true)));
        $fieldSet->addField(Trans::getWord('driverPhone'), $this->Model->getField()->getText('job_driver_phone', $this->Model->getParameterForModal('job_driver_phone', true)));
        $fieldSet->addField(Trans::getWord('ataDate'), $this->Model->getField()->getCalendar('job_ata_date', $this->Model->getParameterForModal('job_ata_date', true)), true);
        $fieldSet->addField(Trans::getWord('ataTime'), $this->Model->getField()->getTime('job_ata_time', $this->Model->getParameterForModal('job_ata_time', true)), true);
        $fieldSet->addField(Trans::getWord('containerNumber'), $this->Model->getField()->getText('job_container_number', $this->Model->getParameterForModal('job_container_number', true)));
        $fieldSet->addField(Trans::getWord('sealNumber'), $this->Model->getField()->getText('job_seal_number', $this->Model->getParameterForModal('job_seal_number', true)));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));
        $fieldSet->setGridDimension(6, 6);
        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompletePicking(): array
    {

        $result = [];
        $diffQty = JobOutboundDetailDao::getTotalDifferentQuantityUnloadWithPickingByJobOrderId($this->Model->getDetailReferenceValue());
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getWord('outboundStorageNotMatch', 'message', '', [
                    'outbound' => $diffQty['qty_outbound'],
                    'taken' => $diffQty['qty_pick'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('outboundPickingEmpty', 'message');
        }
        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompleteLoading(): array
    {
        $result = [];
        $diffQty = JobOutboundDetailDao::getTotalDifferentQuantityLoadingWithJobGoodsByJobOrderId($this->Model->getDetailReferenceValue());
        if (empty($diffQty) === false) {
            if ((float)$diffQty['qty_planning'] !== (float)$diffQty['qty_loaded']) {
                $number = new NumberFormatter();
                $result[] = Trans::getWord('outboundLoadedNotMatch', 'message', '', [
                    'planning' => $number->doFormatFloat((float)$diffQty['qty_planning']),
                    'loaded' => $number->doFormatFloat((float)$diffQty['qty_loaded']),
                ]);
            }
        } else {
            $result[] = Trans::getWord('canNotCompleteActionForEmptyGoods', 'message');
        }

        return $result;
    }

}
