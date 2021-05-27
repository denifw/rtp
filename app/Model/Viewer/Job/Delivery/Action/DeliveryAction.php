<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Delivery\Action;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\System\View;
use App\Model\Dao\Job\Delivery\LoadUnloadDeliveryDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class DeliveryAction extends AbstractBaseJobAction
{

    /**
     * The field trigger true if all load unload location has been proceed
     *
     * @var $LudComplete
     */
    private $LudComplete = true;

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
                case 'PickUp':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStrPickUpMdl', 'doStartPickContainer');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStrPickUpAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        if ($this->Model->isValidParameter('jdl_dp_ata') === false) {
                            $this->getArriveDepoAction('doArriveDepoPickUp', $View, 'Picking', $this->Model->getStringParameter('jdl_dp_name'));
                        } elseif ($this->Model->isValidParameter('jdl_dp_ata') === true && $this->Model->isValidParameter('jdl_dp_start') === false) {
                            $modal = $this->getDefaultTruckingModal('AcStartLoadCtMdl', 'doStartLoadContainer', Trans::getTruckingWord('startLiftOnContainer'));
                            $View->addModal($modal);
                            $btn = new ModalButton('btnStartLoadCt', Trans::getTruckingWord('liftOnContainer'), $modal->getModalId());
                            $btn->setIcon(Icon::Cubes)->btnPrimary()->pullRight()->btnMedium();
                            $View->addButton($btn);
                        } elseif ($this->Model->isValidParameter('jdl_dp_start') === true && $this->Model->isValidParameter('jdl_dp_end') === false) {
                            $modal = $this->getLoadContainerModal('AcEndLoadCtMdl', 'doEndLoadContainer');
                            $View->addModal($modal);
                            $btn = new ModalButton('btnEndLoadCt', Trans::getTruckingWord('completeLiftOn'), $modal->getModalId());
                            $btn->setIcon(Icon::CheckSquareO)->btnPrimary()->pullRight()->btnMedium();
                            $View->addButton($btn);
                        }
                    }
                    break;
                case 'Loading':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStrPickUpMdl', 'doActionStartPickUp');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStrPickUpAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        if ($this->isRoadJob() === true) {
                            $address = $this->getCurrentAddress('O');
                            if (empty($address) === false) {
                                $this->loadActionLocation($address, $View);
                            } else {
                                if ($this->LudComplete === false) {
                                    $modal = $this->getArriveModal('O', 'AcArrLoadMdl', 'doArriveLud', $View);
                                    $View->addModal($modal);
                                    $btn = new ModalButton('btnArrLoad', Trans::getTruckingWord('arrive'), $modal->getModalId());
                                    $btn->setIcon(Icon::Truck)->btnPrimary()->pullRight()->btnMedium();
                                    $View->addButton($btn);
                                } else {
                                    $modal = $this->getDefaultModal('AcEndPickUpMdl', 'doActionEndPickUp', '1');
                                    $View->addModal($modal);
                                    $View->addButton($this->getDefaultButton('btnEndPickUpAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                                }
                            }
                        } else {
                            # When job is not road job
                            $modal = $this->getDefaultModal('AcEndPickUpMdl', 'doActionEndPickUp', '1');
                            $View->addModal($modal);
                            $View->addButton($this->getDefaultButton('btnEndPickUpAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                        }
                    }
                    break;
                case 'Unload':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStrDeliveryMdl', 'doActionStartDelivery');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStrDeliveryAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        if ($this->isRoadJob() === true) {
                            $address = $this->getCurrentAddress('D');
                            if (empty($address) === false) {
                                $this->loadActionLocation($address, $View);
                            } else {
                                if ($this->LudComplete === false) {
                                    $modal = $this->getArriveModal('D', 'AcArrUnLoadMdl', 'doArriveLud', $View);
                                    $View->addModal($modal);
                                    $btn = new ModalButton('btnArrUnLoad', Trans::getTruckingWord('arrive'), $modal->getModalId());
                                    $btn->setIcon(Icon::Truck)->btnPrimary()->pullRight()->btnMedium();
                                    $View->addButton($btn);
                                } else {
                                    $modal = $this->getDefaultModal('AcEndUnloadMdl', 'doActionEndDelivery', '1');
                                    $View->addModal($modal);
                                    $View->addButton($this->getDefaultButton('btnEndUnloadAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                                }
                            }
                        } else {
                            $modal = $this->getDefaultModal('AcEndUnloadMdl', 'doActionEndDelivery', '1');
                            $View->addModal($modal);
                            $View->addButton($this->getDefaultButton('btnEndUnloadAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                        }
                    }
                    break;
                case 'Return':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcExpMdl', 'doStartReturnContainer');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnExpAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        if ($this->Model->isValidParameter('jdl_dr_ata') === false) {
                            $this->getArriveDepoAction('doArriveDepoReturn', $View, 'Return', $this->Model->getStringParameter('jdl_dr_name'));
                        } elseif ($this->Model->isValidParameter('jdl_dr_ata') === true && $this->Model->isValidParameter('jdl_dr_start') === false) {
                            $modal = $this->getDefaultTruckingModal('AcStartLoadCtMdl', 'doStartUnloadContainer', Trans::getTruckingWord('startLiftOffContainer'));
                            $View->addModal($modal);
                            $btn = new ModalButton('btnStartLoadCt', Trans::getTruckingWord('liftOffContainer'), $modal->getModalId());
                            $btn->setIcon(Icon::Cubes)->btnPrimary()->pullRight()->btnMedium();
                            $View->addButton($btn);
                        } elseif ($this->Model->isValidParameter('jdl_dr_start') === true && $this->Model->isValidParameter('jdl_dr_end') === false) {
                            $modal = $this->getUnloadContainerModal('AcEndLoadCtMdl', 'doEndUnloadContainer');
                            $View->addModal($modal);
                            $btn = new ModalButton('btnEndLoadCt', Trans::getTruckingWord('completeLiftOff'), $modal->getModalId());
                            $btn->setIcon(Icon::CheckSquareO)->btnPrimary()->pullRight()->btnMedium();
                            $View->addButton($btn);
                        }
                    }
                    break;
                case 'Pool':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartReturnMdl', 'doActionStartPool');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartReturnAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $modal = $this->getDefaultModal('AcEndReturnMdl', 'doActionEndPool', '1');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndReturnAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
            }
        }
    }


    /**
     * Function to get document action modal.
     *
     * @param string $modalSubmit To store the id of the modal.
     * @param View $View To store the view object.
     * @param string $type To store the id of the modal.
     * @param string $depoName To store the id of the modal.
     *
     * @return void
     */
    private function getArriveDepoAction(string $modalSubmit, View $View, string $type, string $depoName): void
    {
        if (empty($depoName) === true) {
            $message = Trans::getTruckingWord('depoNameIsEmpty', '', ['type' => $type]);
            $modal = $this->getWarningModal('AcArrDepoMdl', $message);
        } else {
            $modal = $this->getArriveDepoModal('AcArrDepoMdl', $modalSubmit, $depoName);
        }
        $View->addModal($modal);

        $btn = new ModalButton('btnArrDepo', Trans::getTruckingWord('arrive'), $modal->getModalId());
        $btn->setIcon(Icon::Truck)->btnPrimary()->pullRight()->btnMedium();
        $View->addButton($btn);
    }


    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $depoName To store the id of the modal.
     *
     * @return Modal
     */
    private function getArriveDepoModal(string $modalId, string $modalSubmit, string $depoName): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getTruckingWord('arriveAtDepoConfirmation', '', ['depo' => $depoName]));
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('dp_ar_date') === false) {
                $this->Model->setParameter('dp_ar_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('dp_ar_time') === false) {
                $this->Model->setParameter('dp_ar_time', date('H:i'));
            }
        }

        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('dp_ar_date', $this->Model->getParameterForModal('dp_ar_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('dp_ar_time', $this->Model->getParameterForModal('dp_ar_time', true)), true);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('dp_name', $depoName));
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('dp_jac_id', $this->Action['jac_id']));
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $title To store the id of the modal.
     *
     * @return Modal
     */
    private function getDefaultTruckingModal(string $modalId, string $modalSubmit, string $title): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, $title);
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('jdl_ac_date') === false) {
                $this->Model->setParameter('jdl_ac_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('jdl_ac_time') === false) {
                $this->Model->setParameter('jdl_ac_time', date('H:i'));
            }
        }

        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jdl_ac_date', $this->Model->getParameterForModal('jdl_ac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jdl_ac_time', $this->Model->getParameterForModal('jdl_ac_time', true)), true);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('jdl_jac_id', $this->Action['jac_id']));
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     *
     * @return Modal
     */
    private function getLoadContainerModal(string $modalId, string $modalSubmit): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getTruckingWord('completeLiftOnContainer'));
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

        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getTruckingWord('containerNumber'), $this->Model->getField()->getText('jdl_container_number', $this->Model->getParameterForModal('jdl_container_number', true)), true);
        $fieldSet->addField(Trans::getTruckingWord('sealNumber'), $this->Model->getField()->getText('jdl_seal_number', $this->Model->getParameterForModal('jdl_seal_number', true)));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));

        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event1', 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to add layout for action to view
     *
     * @param string $type To store the type of location O -> origin, D -> Destination
     *
     * @return array
     */
    private function getCurrentAddress(string $type): array
    {
        $results = [];
        $address = LoadUnloadDeliveryDao::getByJobDeliveryIdAndType($this->Model->getIntParameter('jdl_id'), $type);
        $this->LudComplete = true;
        if (empty($address) === false) {
            foreach ($address as $row) {
                if (empty($row['lud_ata_on']) === false && empty($row['lud_atd_on']) === true) {
                    $results = $row;
                }
                if (empty($row['lud_end_on']) === true) {
                    $this->LudComplete = false;
                }
            }
        } else {
            $this->LudComplete = false;
        }
        return $results;
    }

    /**
     * Function to get document action modal.
     *
     * @param string $type To store the type of location O -> origin, D -> Destination
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param View $View To store the view object.
     *
     * @return Modal
     */
    private function getArriveModal(string $type, string $modalId, string $modalSubmit, View $View): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getTruckingWord('arriveConfirmation'));
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('ar_date') === false) {
                $this->Model->setParameter('ar_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('ar_time') === false) {
                $this->Model->setParameter('ar_time', date('H:i'));
            }
        }
        # Create Shipper or Consignee Field
        $relField = $this->Model->getField()->getSingleSelectTable('lud', 'ar_relation', $this->Model->getParameterForModal('ar_relation', true), 'loadUnArriveAddress');
        $relField->setHiddenField('ar_rel_id', $this->Model->getParameterForModal('ar_rel_id', true));
        $relField->setTableColumns([
            'lud_relation' => Trans::getTruckingWord('relation'),
            'lud_address' => Trans::getTruckingWord('address')
        ]);
        $relField->setAutoCompleteFields([
            'ar_address' => 'lud_address',
            'ar_of_id' => 'lud_of_id',
        ]);
        $relField->setValueCode('lud_rel_id');
        $relField->setLabelCode('lud_relation');
        $relField->addParameter('lud_jdl_id', $this->Model->getIntParameter('jdl_id'));
        $relField->addParameter('lud_type', $type);
        $relField->setParentModal($modal->getModalId());
        $View->addModal($relField->getModal());

        $addressField = $this->Model->getField()->getText('ar_address', $this->Model->getParameterForModal('ar_address', true));
        $addressField->setReadOnly();
        $officeField = $this->Model->getField()->getHidden('ar_of_id', $this->Model->getParameterForModal('ar_of_id', true));

        $locType = Trans::getTruckingWord('loading');
        if ($type === 'D') {
            $locType = Trans::getTruckingWord('unload');
        }
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addField(Trans::getTruckingWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('ar_date', $this->Model->getParameterForModal('ar_date', true)), true);
        $fieldSet->addField(Trans::getTruckingWord('address'), $addressField);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('ar_time', $this->Model->getParameterForModal('ar_time', true)), true);
        $fieldSet->addHiddenField($officeField);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('ar_jac_id', $this->Action['jac_id']));
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('ar_type', $type));
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('loc_type', $locType));
        # Add content Modal.
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get document action modal.
     *
     * @param array $address To store the id of the modal.
     * @param View $View To store the id of the modal.
     *
     * @return void
     */
    private function loadActionLocation(array $address, View $View): void
    {
        $endAction = 'doActionEndPickUp';
        $process = Trans::getTruckingWord('loading');
        if ($address['lud_type'] === 'D') {
            $process = Trans::getTruckingWord('unload');
            $endAction = 'doActionEndDelivery';
        }

        if (empty($address['lud_start_on']) === true) {
            # Start Load or Unload
            $modal = $this->getLoadUnloadModal(
                'AcStrJtdMdl',
                'doStartLoadUnload',
                $address['lud_type'],
                Trans::getTruckingWord('startLoadUnloadConfirmation', '', ['type' => $process])
            );
            $View->addModal($modal);
            $btn = new ModalButton('btnStrJtd', Trans::getTruckingWord('startLoadUnload', '', ['type' => $process]), $modal->getModalId());
            $btn->setIcon(Icon::Cubes)->btnPrimary()->pullRight()->btnMedium();
            $View->addButton($btn);
        } else if (empty($address['lud_end_on']) === true) {
            # Complete Load Or Unload
            $wheres = [];
            $wheres[] = '(lud.lud_deleted_on IS NULL)';
            $wheres[] = '(lud.lud_start_on IS NOT NULL)';
            $wheres[] = '(lud.lud_end_on IS NULL)';
            $wheres[] = '(lud.lud_qty_good IS NULL)';
            $wheres[] = '(lud.lud_qty_damage IS NULL)';
            $wheres[] = '(lud.lud_jdl_id = ' . $this->Model->getIntParameter('jdl_id') . ')';
            $wheres[] = "(lud.lud_type = '" . $address['lud_type'] . "')";
            $data = LoadUnloadDeliveryDao::loadData($wheres);
            if (empty($data) === true) {
                $modal = $this->getLoadUnloadModal(
                    'AcEndJtdMdl',
                    'doEndLoadUnload',
                    $address['lud_type'],
                    Trans::getTruckingWord('completeLoadUnloadConfirmation', '', ['type' => $process])
                );
            } else {
                $errors = [];
                foreach ($data as $row) {
                    $errors[] = Trans::getTruckingWord('pleaseUpdateActualQty', '', ['goods' => $row['lud_sog_name']]);
                }
                $message = implode(' <br/>', $errors);
                $modal = $this->getWarningModal('AcEndJtdMdl', $message);
            }

            $View->addModal($modal);
            $btn = new ModalButton('btnEndJtd', Trans::getTruckingWord('completeLoadUnload', '', ['type' => $process]), $modal->getModalId());
            $btn->setIcon(Icon::CheckSquareO)->btnPrimary()->pullRight()->btnMedium();
            $View->addButton($btn);
        } else {
            if ($this->LudComplete === false) {
                $modal = $this->getLoadUnloadModal(
                    'AcAtdJtdMdl',
                    'doDepartLoadUnload',
                    $address['lud_type'],
                    Trans::getTruckingWord('departureConfirmation')
                );
                $View->addModal($modal);
                $btn = new ModalButton('btnAtdJtd', Trans::getTruckingWord('nextLocation', '', ['type' => $process]), $modal->getModalId());
                $btn->setIcon(Icon::Truck)->btnPrimary()->pullRight()->btnMedium();
                $View->addButton($btn);
            } else {
                $modal = $this->getDefaultModal('AcEndPickUpMdl', $endAction, '1');
                $View->addModal($modal);
                $View->addButton($this->getDefaultButton('btnEndPickUpAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
            }
        }
    }

    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $type To store the type of location O -> origin, D -> Destination
     * @param string $title To store the title of the modal.
     *
     * @return Modal
     */
    private function getLoadUnloadModal(string $modalId, string $modalSubmit, string $type, $title = ''): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, $title);
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('lud_date') === false) {
                $this->Model->setParameter('lud_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('lud_time') === false) {
                $this->Model->setParameter('lud_time', date('H:i'));
            }
        }
        $locType = Trans::getTruckingWord('loading');
        if ($type === 'D') {
            $locType = Trans::getTruckingWord('unload');
        }
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(12);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('lud_jac_id', $this->Action['jac_id']));
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('lud_type', $type));
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('loc_type', $locType));

        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('lud_date', $this->Model->getParameterForModal('lud_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('lud_time', $this->Model->getParameterForModal('lud_time', true)), true);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     *
     * @return Modal
     */
    private function getUnloadContainerModal(string $modalId, string $modalSubmit): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getTruckingWord('completeLiftOffContainer'));
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

        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));

        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event1', 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to check is this transport module of road
     *
     * @return bool
     */
    private function isRoadJob(): bool
    {
        return $this->Model->getStringParameter('jdl_tm_code', '') === 'road';
    }
}
