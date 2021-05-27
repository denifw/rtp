<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Job\Inklaring\Action;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\LabelTrueFalse;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\System\View;
use App\Model\Dao\Job\Inklaring\JobInklaringReleaseDao;
use App\Model\Dao\System\CustomsClearanceTypeDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

/**
 * Class inklaring action.
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Inklaring\Action
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 Spada
 */
class InklaringAction extends AbstractBaseJobAction
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
                case 'Drafting' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcDraftingMdl', 'doDrafting');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnDrafting', $modal->getModalId(), Icon::FileText));
                    } else {
                        $modal = $this->getDraftingCompleteModal('AcCompleteDraftingMdl', 'doCompleteDrafting', 1);
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnCompleteDrafting', $modal->getModalId(), Icon::CheckSquareO, 1));
                    }
                    break;
                case 'Register' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getRegisterModal('AcRegisterMdl', 'doRegister');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnRegister', $modal->getModalId(), Icon::CheckSquareO));
                    } else {
                        $modal = $this->getRegisterCompleteModal('AcCompleteRegisterMdl', 'doCompleteRegister', 1);
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnCompleteRegister', $modal->getModalId(), Icon::CheckSquareO, 1));
                    }
                    break;
                case 'PortRelease' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcPortReleaseMdl', 'doPortRelease');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnPortRelease', $modal->getModalId(), Icon::Exchange));
                    } else {
                        $modal = $this->getDefaultModal('AcCompletePortReleaseMdl', 'doCompletePortRelease', 1);
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnCompletePortRelease', $modal->getModalId(), Icon::CheckSquareO, 1));
                    }
                    break;
                case 'ReleaseGoods' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcReleaseMdl', 'doReleaseGoods');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnReleaseGoods', $modal->getModalId(), Icon::Truck));
                    } else {
                        if ($this->Model->getStringParameter('so_container', 'N') === 'Y') {
                            $p = new Paragraph(Trans::getMessageWord('inklaringContainerCompleteReleaseWarning'));
                            $p->setAsLabelLarge()->setAlignCenter();
                            $errors = $this->doValidateCompleteReleaseContainer();
                        } else {
                            $p = new Paragraph(Trans::getMessageWord('inklaringGoodsCompleteReleaseWarning'));
                            $p->setAsLabelLarge()->setAlignCenter();
                            $errors = $this->doValidateCompleteReleaseGoods();
                        }
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcCompleteReleaseGoodsMdl', 'doCompleteReleaseGoods', '1');
                        } else {
                            $modal = $this->getWarningModal('AcCompleteReleaseGoodsMdl', $p . $errors);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnCompleteReleaseGoods', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'Shipment' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcReleaseMdl', 'doReleaseGoods');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnShipment', $modal->getModalId(), Icon::Truck));
                    } else {
                        if ($this->Model->getStringParameter('so_container', 'N') === 'Y') {
                            $p = new Paragraph(Trans::getMessageWord('inklaringContainerCompleteShipmentWarning'));
                            $p->setAsLabelLarge()->setAlignCenter();
                            $errors = $this->doValidateCompleteReleaseContainer();
                        } else {
                            $p = new Paragraph(Trans::getMessageWord('inklaringGoodsCompleteShipmentWarning'));
                            $p->setAsLabelLarge()->setAlignCenter();
                            $errors = $this->doValidateCompleteReleaseGoods();
                        }
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcCompleteShipmentMdl', 'doCompleteReleaseGoods', '1');
                        } else {
                            $modal = $this->getWarningModal('AcCompleteShipmentMdl', $p . $errors);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnCompleteShipment', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'GatePass' :
                    $errors = $this->doValidateGateInGoods();
                    if (empty($errors) === true) {
                        $modal = $this->getGateInCompleteModal('AcCompleteGateInMdl', 'doGatePass');
                    } else {
                        $p = new Paragraph(Trans::getMessageWord('inklaringGatePassWarning'));
                        $p->setAsLabelLarge()->setAlignCenter();
                        $modal = $this->getWarningModal('AcCompleteGateInMdl', $p . $errors);
                    }
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnCompleteGateIn', $modal->getModalId(), Icon::CheckSquareO));
                    break;
                case 'Arrive':
                    $modal = $this->getDefaultModal('AcArriveMdl', 'doTransportArrive');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnArrive', $modal->getModalId(), Icon::Anchor));
                    break;
                case 'Departure':
                    $modal = $this->getDefaultModal('AcDepartureMdl', 'doTransportDeparture');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnDepart', $modal->getModalId(), Icon::Anchor));
                    break;
            }
        }
    }

    /**
     * Function to get drafting modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    private function getDraftingCompleteModal(string $modalId, string $modalSubmit, string $actionIndex = ''): Modal
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
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('ajuRef'), $this->Model->getField()->getText('so_aju_ref', $this->Model->getParameterForModal('so_aju_ref', true)), true);
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));
        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get register modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    private function getRegisterModal(string $modalId, string $modalSubmit, string $actionIndex = ''): Modal
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
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('manifestRef'), $this->Model->getField()->getText('so_manifest_ref', $this->Model->getParameterForModal('so_manifest_ref', true)), true);
        $fieldSet->addField(Trans::getWord('manifestDate'), $this->Model->getField()->getCalendar('so_manifest_date', $this->Model->getParameterForModal('so_manifest_date', true)), true);
        $fieldSet->addField(Trans::getWord('manifestPos'), $this->Model->getField()->getText('so_manifest_pos', $this->Model->getParameterForModal('so_manifest_pos', true)), true);
        $fieldSet->addField(Trans::getWord('manifestSubPos'), $this->Model->getField()->getText('so_manifest_sub_pos', $this->Model->getParameterForModal('so_manifest_sub_pos', true)));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));
        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get register complete modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    private function getRegisterCompleteModal(string $modalId, string $modalSubmit, string $actionIndex = ''): Modal
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
        # Create Field set
        # Custom Type
        $cctField = $this->Model->getField()->getSelect('so_cct_id', $this->Model->getParameterForModal('so_cct_id', true));
        $cctData = CustomsClearanceTypeDao::loadActiveData();
        $cctField->addOptions($cctData, 'cct_name', 'cct_id');

        $requireDoExpired = false;
        if ($this->isInklaringImport() === true) {
            $requireDoExpired = true;
        }
        $requiredCustomType = false;
        if ($this->Model->getStringParameter('so_plb', 'N') === 'N') {
            $requiredCustomType = true;
        }
        # Add field into fieldset
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('registerNumber'), $this->Model->getField()->getText('jik_register_number', $this->Model->getParameterForModal('jik_register_number', true)), true);
        $fieldSet->addField(Trans::getWord('registerDate'), $this->Model->getField()->getCalendar('jik_register_date', $this->Model->getParameterForModal('jik_register_date', true)), true);
        $fieldSet->addField(Trans::getWord('doRef'), $this->Model->getField()->getText('so_do_ref', $this->Model->getParameterForModal('so_do_ref', true)), true);
        $fieldSet->addField(Trans::getWord('doExpired'), $this->Model->getField()->getCalendar('so_do_expired', $this->Model->getParameterForModal('so_do_expired', true)), $requireDoExpired);
        $fieldSet->addField(Trans::getWord('sppbRef'), $this->Model->getField()->getText('so_sppb_ref', $this->Model->getParameterForModal('so_sppb_ref', true)), true);
        $fieldSet->addField(Trans::getWord('lineStatus'), $cctField, $requiredCustomType);
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image'), $this->Model->getField()->getFile('jac_image', ''));
        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));
        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to check are all container released.
     *
     * @return string
     */
    private function doValidateCompleteReleaseContainer(): string
    {
        $result = '';
        $data = JobInklaringReleaseDao::getUnReleaseContainer($this->Model->getIntParameter('jik_so_id'), $this->Model->getIntParameter('jik_id'));
        if (empty($data) === false) {
            $message = [];
            foreach ($data as $row) {
                $message[] = [
                    'label' => $row['soc_container_type'] . ' - ' . $row['soc_container_number'],
                    'value' => new LabelTrueFalse(false),
                ];
            }
            $result = StringFormatter::generateCustomTableView($message, 8, 8);
        }

        return $result;
    }

    /**
     * Function to check are all container released.
     *
     * @return string
     */
    private function doValidateCompleteReleaseGoods(): string
    {
        $result = '';
        $data = JobInklaringReleaseDao::getUnReleaseGoods($this->Model->getIntParameter('jik_so_id'), $this->Model->getIntParameter('jik_id'));
        if (empty($data) === false) {
            $message = [];
            $number = new NumberFormatter($this->Model->getUser());
            foreach ($data as $row) {
                $qtyPlanning = (float)$row['sog_quantity'];
                $qtyRelease = (float)$row['sog_qty_release'];
                if ($qtyPlanning !== $qtyRelease) {
                    $diff = $qtyRelease - $qtyPlanning;
                    $message[] = [
                        'label' => $row['sog_hs_code'] . ' - ' . $row['sog_name'],
                        'value' => $number->doFormatFloat($diff) . ' ' . $row['sog_uom'],
                    ];
                }
            }
            if (empty($message) === false) {
                $result = StringFormatter::generateCustomTableView($message, 8, 8);
            }
        }

        return $result;
    }

    /**
     * Function to check are all container released.
     *
     * @return string
     */
    private function doValidateGateInGoods(): string
    {
        $result = '';
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jikr.jikr_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jikr.jikr_gate_in_date');
        $wheres[] = SqlHelper::generateNumericCondition('jikr.jikr_jik_id', $this->Model->getIntParameter('jik_id'));
        $data = JobInklaringReleaseDao::loadData($wheres);
        if (empty($data) === false) {
            $message = [];
            if ($this->Model->getStringParameter('so_container', 'N') === 'Y') {
                foreach ($data as $row) {
                    $message[] = [
                        'label' => $row['jikr_container_type'] . ' - ' . $row['jikr_container_number'],
                        'value' => new LabelTrueFalse(false),
                    ];
                }
            } else {
                $number = new NumberFormatter();
                foreach ($data as $row) {
                    $message[] = [
                        'label' => $row['jikr_hs_code'] . ' - ' . $row['jikr_goods'] . ' - ' . $number->doFormatFloat($row['jikr_quantity']) . ' ' . $row['jikr_uom_code'],
                        'value' => new LabelTrueFalse(false),
                    ];
                }
            }
            $result = StringFormatter::generateCustomTableView($message, 8, 8);
        }

        return $result;
    }


    /**
     * Function to get register complete modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    private function getGateInCompleteModal(string $modalId, string $modalSubmit, string $actionIndex = ''): Modal
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
        # Custom Type
        $cctField = $this->Model->getField()->getSelect('so_cct_id', $this->Model->getParameterForModal('so_cct_id', true));
        $cctData = CustomsClearanceTypeDao::loadActiveData();
        $cctField->addOptions($cctData, 'cct_name', 'cct_id');
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(6);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));
        $fieldSet->addField(Trans::getWord('sppdRef'), $this->Model->getField()->getText('so_sppd_ref', $this->Model->getParameterForModal('so_sppd_ref', true)), true);
        $fieldSet->addField(Trans::getWord('sppdDate'), $this->Model->getField()->getCalendar('so_sppd_date', $this->Model->getParameterForModal('so_sppd_date', true)), true);
        $fieldSet->addField(Trans::getWord('lineStatus'), $cctField, true);
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

    /**
     * Function to check is it import or export container.
     *
     * @return bool
     */
    private function isInklaringImport(): bool
    {
        return $this->Model->getStringParameter('jo_srt_route') === 'jiic' || $this->Model->getStringParameter('jo_srt_route') === 'jii';
    }
}
