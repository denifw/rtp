<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Warehouse\Action;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Field;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class InboundAction extends AbstractBaseJobAction
{
    /**
     * Function to add layout for action to view
     *
     * @param \App\Frame\System\View $View
     *
     * @return void
     */
    public function addActionView(\App\Frame\System\View $View): void
    {
        $this->loadAction();
        if (empty($this->Action) === false) {
            switch ($this->Action['jac_action']) {
                case 'Arrive':
                    $modal = $this->getArriveTruckModal('AcArriveMdl', 'doArriveTruck');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnArrive', $modal->getModalId(), Icon::Truck));
                    break;
                case 'Unload':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartUnloadMdl', 'doActionStartUnload');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartUnloadAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $errors = JobInboundDao::doValidateCompleteLoading($this->Model->getDetailReferenceValue());
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndUnloadMdl', 'doActionEndUnload', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndUnloadMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndUnloadAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'Document':
                    $modal = $this->getDefaultModal('AcDocMdl', 'doActionDocument');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnDocAc', $modal->getModalId(), Icon::File));
                    break;
                case 'PutAway':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartPutAwayMdl', 'doActionStartPutAway');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartPutAwayAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        $errors = $this->doValidateCompleteStorage();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndPutAwayMdl', 'doActionEndPutAway', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndPutAwayMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndPutAwayAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
            }
        }
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompleteStorage(): array
    {
        $result = [];
        $user = $this->Model->getUser();
        $diffQty = JobInboundDetailDao::getTotalDifferentQuantityLoadWithStoredByJobInboundId($this->Model->getIntParameter('ji_id'));
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getWord('inboundStorageNotMatch', 'message', '', [
                    'inbound' => $diffQty['qty_actual'],
                    'stored' => $diffQty['qty_stored'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('inboundStorageEmpty', 'message');
        }
        if (empty($result) === true && $user->Settings->getNameSpace() === 'mol') {
            $valid = JobInboundDetailDao::isValidAllSerialNumberByJiId($this->Model->getIntParameter('ji_id'));
            if ($valid === false) {
                $result[] = Trans::getWord('invalidSerialNumberInbound', 'message');
            }
        }

        return $result;
    }


    /**
     * Function to get document action modal.
     *
     * @param string $modalId     To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getArriveTruckModal($modalId, $modalSubmit, $actionIndex = ''): Modal
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
        }
        # Create Shipper or Consignee Field
        $field = new Field($this->Model->getValidation());
        $vendorField = $field->getSingleSelect('relation', 'ji_vendor', $this->Model->getParameterForModal('ji_vendor', true));
        $vendorField->setHiddenField('ji_vendor_id', $this->Model->getParameterForModal('ji_vendor_id', true));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('ji_vendor_pic');
        $vendorField->addClearField('ji_pic_vendor');
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('truckPlate'), $this->Model->getField()->getText('ji_truck_number', $this->Model->getParameterForModal('ji_truck_number', true)));
        $fieldSet->addField(Trans::getWord('transporter'), $vendorField, true);
        $fieldSet->addField(Trans::getWord('driver'), $this->Model->getField()->getText('ji_driver', $this->Model->getParameterForModal('ji_driver', true)), true);
        $fieldSet->addField(Trans::getWord('driverPhone'), $this->Model->getField()->getText('ji_driver_phone', $this->Model->getParameterForModal('ji_driver_phone', true)));
        $fieldSet->addField(Trans::getWord('containerNumber'), $this->Model->getField()->getText('ji_container_number', $this->Model->getParameterForModal('ji_container_number', true)));
        $fieldSet->addField(Trans::getWord('sealNumber'), $this->Model->getField()->getText('ji_seal_number', $this->Model->getParameterForModal('ji_seal_number', true)));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));

        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));

        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
}
