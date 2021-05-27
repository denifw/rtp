<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Master;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
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
use App\Frame\Gui\Templates\CardImage;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Fms\EquipmentFuelDao;
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\Fms\RenewalOrderDao;
use App\Model\Dao\Fms\RenewalReminderDao;
use App\Model\Dao\Fms\ServiceOrderDao;
use App\Model\Dao\Fms\ServiceReminderDao;
use App\Model\Dao\Master\EquipmentDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\System\EquipmentGroupDao;
use App\Model\Dao\System\EquipmentStatusDao;
use App\Model\Dao\System\TransportModuleDao;

/**
 * Class to handle the creation of detail Equipment page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Equipment extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipment', 'eq_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $cbm = null;
        if ($this->isValidParameter('eq_length') && $this->isValidParameter('eq_height') && $this->isValidParameter('eq_width')) {
            $cbm = $this->getFloatParameter('eq_length') * $this->getFloatParameter('eq_height') * $this->getFloatParameter('eq_width');
        }
        $cbmCapacity = null;
        if ($this->isValidParameter('eq_lgh_capacity') && $this->isValidParameter('eq_wdh_capacity') && $this->isValidParameter('eq_hgh_capacity')) {
            $cbmCapacity = $this->getFloatParameter('eq_lgh_capacity') * $this->getFloatParameter('eq_wdh_capacity') * $this->getFloatParameter('eq_hgh_capacity');
        }
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('Equipment', $this->User->Relation->getOfficeId(), $this->getIntParameter('eq_rel_id'));
        $eqStatus = $this->getIntParameter('eq_eqs_id');
        $active = 'Y';
        if ($eqStatus === 3 || $eqStatus === 2) {
            $active = 'N';
        } elseif ($eqStatus === 1) {
            $active = 'Y';
        }
        $colVal = [
            'eq_number' => $number,
            'eq_ss_id' => $this->User->getSsId(),
            'eq_rel_id' => $this->getIntParameter('eq_rel_id'),
            'eq_eg_id' => $this->getIntParameter('eq_eg_id'),
            'eq_description' => $this->getStringParameter('eq_description'),
            'eq_length' => $this->getFloatParameter('eq_length'),
            'eq_width' => $this->getFloatParameter('eq_width'),
            'eq_height' => $this->getFloatParameter('eq_height'),
            'eq_volume' => $cbm,
            'eq_weight' => $this->getFloatParameter('eq_weight'),
            'eq_lgh_capacity' => $this->getFloatParameter('eq_lgh_capacity'),
            'eq_wdh_capacity' => $this->getFloatParameter('eq_wdh_capacity'),
            'eq_hgh_capacity' => $this->getFloatParameter('eq_hgh_capacity'),
            'eq_cbm_capacity' => $cbmCapacity,
            'eq_wgh_capacity' => $this->getFloatParameter('eq_wgh_capacity'),
            'eq_owt_id' => $this->getIntParameter('eq_owt_id'),
            'eq_manage_by_id' => $this->getIntParameter('eq_manage_by_id'),
            'eq_manager_id' => $this->getIntParameter('eq_manager_id'),
            'eq_sty_id' => $this->getIntParameter('eq_sty_id'),
            'eq_built_year' => $this->getIntParameter('eq_built_year'),
            'eq_color' => $this->getStringParameter('eq_color'),
            'eq_engine_capacity' => $this->getIntParameter('eq_engine_capacity'),
            'eq_fty_id' => $this->getIntParameter('eq_fty_id'),
            'eq_max_speed' => $this->getIntParameter('eq_max_speed'),
            'eq_fuel_consume' => $this->getFloatParameter('eq_fuel_consume'),
            'eq_license_plate' => $this->getStringParameter('eq_license_plate'),
            'eq_machine_number' => $this->getStringParameter('eq_machine_number'),
            'eq_chassis_number' => $this->getStringParameter('eq_chassis_number'),
            'eq_bpkb' => $this->getStringParameter('eq_bpkb'),
            'eq_stnk' => $this->getStringParameter('eq_stnk'),
            'eq_keur' => $this->getStringParameter('eq_keur'),
            'eq_picture' => $this->getStringParameter('eq_picture'),
            'eq_primary_meter' => $this->getStringParameter('eq_primary_meter'),
            'eq_eqs_id' => $this->getIntParameter('eq_eqs_id', 1),
            'eq_active' => $active,
            'eq_driver_id' => $this->getIntParameter('eq_driver_id'),
        ];
        $eqDao = new EquipmentDao();
        $eqDao->doInsertTransaction($colVal);

        return $eqDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {

        if ($this->getFormAction() === 'doUpdateMeter') {
            $colVal = [
                'eqm_eq_id' => $this->getDetailReferenceValue(),
                'eqm_date' => $this->getStringParameter('eqm_date'),
                'eqm_meter' => $this->getFloatParameter('eqm_meter'),
                'eqm_source' => Trans::getFmsWord('manuallyEntered'),
            ];
            $eqmDao = new EquipmentMeterDao();
            if ($this->isValidParameter('eqm_id') === true) {
                $eqmDao->doUpdateTransaction($this->getIntParameter('eqm_id'), $colVal);
            } else {
                $eqmDao->doInsertTransaction($colVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteMeter') {
            $eqmDao = new EquipmentMeterDao();
            $eqmDao->doDeleteTransaction($this->getIntParameter('eqm_id_del'));
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
        } elseif ($this->getFormAction() === 'doUploadImage') {
            # Upload Document.
            $file = $this->getFileParameter('eq_im_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('eq_im_dct'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('eq_im_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                if ($this->getStringParameter('eq_im_main','N') === 'Y') {
                    $eqDao = new EquipmentDao();
                    $eqDao->doUpdateTransaction($this->getDetailReferenceValue(),[
                        'eq_doc_id' => $docDao->getLastInsertId(),
                    ]);
                }
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteImage') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('eq_im_id_del'));
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } else {
            $cbm = null;
            if ($this->isValidParameter('eq_length') && $this->isValidParameter('eq_height') && $this->isValidParameter('eq_width')) {
                $cbm = $this->getFloatParameter('eq_length') * $this->getFloatParameter('eq_height') * $this->getFloatParameter('eq_width');
            }
            $cbmCapacity = null;
            if ($this->isValidParameter('eq_lgh_capacity') && $this->isValidParameter('eq_wdh_capacity') && $this->isValidParameter('eq_hgh_capacity')) {
                $cbmCapacity = $this->getFloatParameter('eq_lgh_capacity') * $this->getFloatParameter('eq_wdh_capacity') * $this->getFloatParameter('eq_hgh_capacity');
            }
            $eqStatus = $this->getIntParameter('eq_eqs_id');
            $active = 'Y';
            if ($eqStatus === 3 || $eqStatus === 2) {
                $active = 'N';
            } elseif ($eqStatus === 1) {
                $active = 'Y';
            }
            $colVal = [
                'eq_rel_id' => $this->getIntParameter('eq_rel_id'),
                'eq_eg_id' => $this->getIntParameter('eq_eg_id'),
                'eq_description' => $this->getStringParameter('eq_description'),
                'eq_length' => $this->getFloatParameter('eq_length'),
                'eq_width' => $this->getFloatParameter('eq_width'),
                'eq_height' => $this->getFloatParameter('eq_height'),
                'eq_volume' => $cbm,
                'eq_weight' => $this->getFloatParameter('eq_weight'),
                'eq_lgh_capacity' => $this->getFloatParameter('eq_lgh_capacity'),
                'eq_wdh_capacity' => $this->getFloatParameter('eq_wdh_capacity'),
                'eq_hgh_capacity' => $this->getFloatParameter('eq_hgh_capacity'),
                'eq_cbm_capacity' => $cbmCapacity,
                'eq_wgh_capacity' => $this->getFloatParameter('eq_wgh_capacity'),
                'eq_owt_id' => $this->getIntParameter('eq_owt_id'),
                'eq_manage_by_id' => $this->getIntParameter('eq_manage_by_id'),
                'eq_manager_id' => $this->getIntParameter('eq_manager_id'),
                'eq_sty_id' => $this->getIntParameter('eq_sty_id'),
                'eq_built_year' => $this->getIntParameter('eq_built_year'),
                'eq_color' => $this->getStringParameter('eq_color'),
                'eq_engine_capacity' => $this->getIntParameter('eq_engine_capacity'),
                'eq_fty_id' => $this->getIntParameter('eq_fty_id'),
                'eq_max_speed' => $this->getIntParameter('eq_max_speed'),
                'eq_fuel_consume' => $this->getFloatParameter('eq_fuel_consume'),
                'eq_license_plate' => $this->getStringParameter('eq_license_plate'),
                'eq_machine_number' => $this->getStringParameter('eq_machine_number'),
                'eq_chassis_number' => $this->getStringParameter('eq_chassis_number'),
                'eq_bpkb' => $this->getStringParameter('eq_bpkb'),
                'eq_stnk' => $this->getStringParameter('eq_stnk'),
                'eq_keur' => $this->getStringParameter('eq_keur'),
                'eq_picture' => $this->getStringParameter('eq_picture'),
                'eq_primary_meter' => $this->getStringParameter('eq_primary_meter'),
                'eq_eqs_id' => $this->getIntParameter('eq_eqs_id', 1),
                'eq_active' => $active,
                'eq_driver_id' => $this->getIntParameter('eq_driver_id'),
            ];
            $eqDao = new EquipmentDao();
            $eqDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EquipmentDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('eq_eg_id') === true) {
                $eg = EquipmentGroupDao::getByReference($this->getIntParameter('eq_eg_id'));
                if (empty($eg) === false) {
                    $this->setParameter('eq_group', $eg['eg_name']);
                    $this->setParameter('eq_tm_id', $eg['eg_tm_id']);
                    $this->setParameter('eq_transport_module', $eg['eg_module']);
                    $this->setParameter('eq_tm_code', $eg['eg_tm_code']);
                }
            }
            if ($this->isValidParameter('eq_tm_id') === true) {
                $tm = TransportModuleDao::getByReference($this->getIntParameter('eq_tm_id'));
                if (empty($tm) === false) {
                    $this->setParameter('eq_transport_module', $tm['tm_name']);
                    $this->setParameter('eq_tm_code', $tm['tm_code']);
                }
            }
            if ($this->isValidParameter('eq_rel_id') === true) {
                $rel = RelationDao::getByReference($this->getIntParameter('eq_rel_id'));
                if (empty($rel) === false) {
                    $this->setParameter('eq_owner', $rel['rel_name']);
                }
            }
            if ($this->isValidParameter('eq_manage_by_id') === true) {
                $rel = RelationDao::getByReference($this->getIntParameter('eq_manage_by_id'));
                if (empty($rel) === false) {
                    $this->setParameter('eq_rel_id', $this->getIntParameter('eq_manage_by_id'));
                    $this->setParameter('eq_owner', $rel['rel_name']);
                    $this->setParameter('eq_manage_by_name', $rel['rel_name']);
                }
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->Tab->addPortlet('general', $this->getSpecificationFieldSet());
            $this->Tab->addPortlet('general', $this->getIdentityFieldSet());
            $this->Tab->addPortlet('general', $this->getCapacityFieldSet());
            $this->Tab->addPortlet('reminder', $this->getServiceRemindersFieldSet());
            $this->Tab->addPortlet('reminder', $this->getRenewalRemindersFieldSet());
            $this->Tab->addPortlet('serviceHistory', $this->getServiceHistoryFieldSet());
            $this->Tab->addPortlet('renewalHistory', $this->getRenewalHistoryFieldSet());
            $this->Tab->addPortlet('meterHistory', $this->getMeterHistoryFieldSet());
            $this->Tab->addPortlet('fuelHistory', $this->getFuelHistoryFieldSet());
            $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
            $this->Tab->addPortlet('gallery', $this->getGalleryPortlet());
            $this->overridePageTitle();
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateMeter') {
            $this->Validation->checkRequire('eqm_date');
            $this->Validation->checkFloat('eqm_meter');
            if ($this->isValidParameter('eqm_date') && $this->isValidParameter('eqm_meter')) {
                $minMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($this->getDetailReferenceValue(), $this->getStringParameter('eqm_date'), 'min');
                $maxMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($this->getDetailReferenceValue(), $this->getStringParameter('eqm_date'), 'max');
                $this->Validation->checkFloat('eqm_meter', $minMeterData['eqm_meter'], $maxMeterData['eqm_meter']);
            }
        } elseif ($this->getFormAction() === 'doDeleteMeter') {
            $this->Validation->checkRequire('eqm_id_del');
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkRequire('doc_description', 3, 255);
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        } else {
            $this->Validation->checkRequire('eq_eg_id');
            $this->Validation->checkRequire('eq_tm_id');
            $this->Validation->checkRequire('eq_tm_code');
            $this->Validation->checkRequire('eq_rel_id');
            $this->Validation->checkRequire('eq_manage_by_id');
            $this->Validation->checkRequire('eq_description', 2, 255);
            if ($this->getStringParameter('eq_tm_code') === 'road') {
                $this->Validation->checkRequire('eq_license_plate', 3, 255);
            }
            if ($this->isValidParameter('eq_license_plate') === true) {
                $this->Validation->checkSpecialCharacter('eq_license_plate');
                $this->Validation->checkUnique('eq_license_plate', 'equipment', [
                    'eq_id' => $this->getDetailReferenceValue()
                ], [
                    'eq_ss_id' => $this->User->getSsId(),
                    'eq_eg_id' => $this->getIntParameter('eq_eg_id'),
                    'eq_deleted_on' => null,
                ]);
            }
            if ($this->isValidParameter('eq_length') === true) {
                $this->Validation->checkFloat('eq_length');
            }
            if ($this->isValidParameter('eq_width') === true) {
                $this->Validation->checkFloat('eq_width');
            }
            if ($this->isValidParameter('eq_height') === true) {
                $this->Validation->checkFloat('eq_height');
            }
            if ($this->isValidParameter('eq_weight') === true) {
                $this->Validation->checkFloat('eq_weight');
            }
            if ($this->isValidParameter('eq_lgh_capacity') === true) {
                $this->Validation->checkFloat('eq_lgh_capacity');
            }
            if ($this->isValidParameter('eq_wdh_capacity') === true) {
                $this->Validation->checkFloat('eq_wdh_capacity');
            }
            if ($this->isValidParameter('eq_hgh_capacity') === true) {
                $this->Validation->checkFloat('eq_hgh_capacity');
            }
            if ($this->isValidParameter('eq_wgh_capacity') === true) {
                $this->Validation->checkFloat('eq_wgh_capacity');
            }
            if ($this->isValidParameter('eq_built_year') === true) {
                $this->Validation->checkInt('eq_built_year', 1990);
            }
            if ($this->isValidParameter('eq_color') === true) {
                $this->Validation->checkRequire('eq_color', 3, 255);
            }
            if ($this->isValidParameter('eq_engine_capacity') === true) {
                $this->Validation->checkInt('eq_engine_capacity', 900);
            }
            if ($this->isValidParameter('eq_machine_number') === true) {
                $this->Validation->checkRequire('eq_machine_number', 3, 255);
            }
            if ($this->isValidParameter('eq_chassis_number') === true) {
                $this->Validation->checkRequire('eq_chassis_number', 3, 255);
            }
            if ($this->isValidParameter('eq_bpkb') === true) {
                $this->Validation->checkRequire('eq_bpkb', 3, 255);
            }
            if ($this->isValidParameter('eq_stnk') === true) {
                $this->Validation->checkRequire('eq_stnk', 3, 255);
            }
            if ($this->isValidParameter('eq_keur') === true) {
                $this->Validation->checkRequire('eq_keur', 3, 255);
            }
            if ($this->isValidParameter('eq_max_speed') === true) {
                $this->Validation->checkInt('eq_max_speed');
            }
            if ($this->isValidParameter('eq_fuel_consume') === true) {
                $this->Validation->checkFloat('eq_fuel_consume');
            }
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'eq_transport_module', $this->getStringParameter('eq_transport_module'));
        $tmField->setHiddenField('eq_tm_id', $this->getIntParameter('eq_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setEnableDetailButton(false);
        $tmField->setAutoCompleteFields([
            'eq_tm_code' => 'tm_code'
        ]);
        $tmField->addClearField('eq_group');
        $tmField->addClearField('eq_eg_id');
        # Equipment Group
        $egField = $this->Field->getSingleSelect('eg', 'eq_group', $this->getStringParameter('eq_group'));
        $egField->setHiddenField('eq_eg_id', $this->getIntParameter('eq_eg_id'));
        $egField->addParameter('eg_ss_id', $this->User->getSsId());
        $egField->setEnableNewButton(false);
        $egField->setEnableDetailButton(false);
        $egField->addParameterById('eg_tm_id', 'eq_tm_id', Trans::getWord('transportModule'));

        # Create radio grup primary meter
        if ($this->isValidParameter('eq_primary_meter') === false) {
            $this->setParameter('eq_primary_meter', 'km');
        }
        $primaryMeterField = $this->Field->getRadioGroup('eq_primary_meter', $this->getStringParameter('eq_primary_meter'));
        $primaryMeterField->addRadios([
            'km' => Trans::getFmsWord('kiloMeters'),
            'hours' => Trans::getFmsWord('hours')
        ]);
        # Create select option equipment status
        $wheres[] = '(eqs_active = \'Y\')';
        $statusData = EquipmentStatusDao::loadData($wheres);
        $statusField = $this->Field->getSelect('eq_eqs_id', $this->getIntParameter('eq_eqs_id'));
        $statusField->addOptions($statusData, 'eqs_name', 'eqs_id');

        # Create single select ownership type
        $owtField = $this->Field->getSingleSelect('ownershipType', 'eq_owt_name', $this->getStringParameter('eq_owt_name'));
        $owtField->setHiddenField('eq_owt_id', $this->getIntParameter('eq_owt_id'));
        $owtField->setEnableNewButton(false);
        $owtField->setEnableDetailButton(false);
        # Create single select owner
        $ownerField = $this->Field->getSingleSelect('relation', 'eq_owner', $this->getStringParameter('eq_owner'));
        $ownerField->setHiddenField('eq_rel_id', $this->getIntParameter('eq_rel_id'));
        $ownerField->addParameter('rel_ss_id', $this->User->getSsId());
        $ownerField->setEnableNewButton(false);
        $ownerField->setEnableDetailButton(false);
        # Create single select manage by
        $manageByField = $this->Field->getSingleSelect('relation', 'eq_manage_by_name', $this->getStringParameter('eq_manage_by_name'));
        $manageByField->setHiddenField('eq_manage_by_id', $this->getIntParameter('eq_manage_by_id'));
        $manageByField->addParameter('rel_ss_id', $this->User->getSsId());
        $manageByField->setEnableNewButton(false);
        $manageByField->setEnableDetailButton(false);
        $manageByField->addClearField('eq_driver');
        $manageByField->addClearField('eq_driver_id');
        # Create single select driver
        $driverField = $this->Field->getSingleSelect('contactPerson', 'eq_driver', $this->getStringParameter('eq_driver'));
        $driverField->setHiddenField('eq_driver_id', $this->getIntParameter('eq_driver_id'));
        $driverField->addParameterById('cp_rel_id', 'eq_manage_by_id', Trans::getFmsWord('manageBy'));
        $driverField->setDetailReferenceCode('cp_id');
        # Create single select manager
        $managerField = $this->Field->getSingleSelect('user', 'eq_manager_name', $this->getStringParameter('eq_manager_name'));
        $managerField->setHiddenField('eq_manager_id', $this->getIntParameter('eq_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableNewButton(false);
        $managerField->setEnableDetailButton(false);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(3, 3);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('eq_description', $this->getStringParameter('eq_description')), true);
        $fieldSet->addField(Trans::getFmsWord('licensePlate'), $this->Field->getText('eq_license_plate', $this->getStringParameter('eq_license_plate')));
        $fieldSet->addField(Trans::getWord('transportModule'), $tmField, true);
        $fieldSet->addField(Trans::getWord('transportType'), $egField, true);
        $fieldSet->addField(Trans::getFmsWord('owner'), $ownerField, true);
        $fieldSet->addField(Trans::getFmsWord('ownershipType'), $owtField);
        $fieldSet->addField(Trans::getFmsWord('manageBy'), $manageByField, true);
        $fieldSet->addField(Trans::getFmsWord('driver'), $driverField);
        $fieldSet->addField(Trans::getFmsWord('manager'), $managerField);
        $fieldSet->addField(Trans::getFmsWord('primaryMeter'), $primaryMeterField);
        $fieldSet->addField(Trans::getFmsWord('status'), $statusField);

        $fieldSet->addHiddenField($this->Field->getHidden('eq_tm_code', $this->getStringParameter('eq_tm_code')));
        # Create a portlet box.
        $portlet = new Portlet('EqGeneralPtl', Trans::getFmsWord('equipment'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getCapacityFieldSet(): Portlet
    {
        # Create Fields.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('length') . ' (M)', $this->Field->getNumber('eq_lgh_capacity', $this->getFloatParameter('eq_lgh_capacity')));
        $fieldSet->addField(Trans::getFmsWord('width') . ' (M)', $this->Field->getNumber('eq_wdh_capacity', $this->getFloatParameter('eq_wdh_capacity')));
        $fieldSet->addField(Trans::getFmsWord('height') . ' (M)', $this->Field->getNumber('eq_hgh_capacity', $this->getFloatParameter('eq_hgh_capacity')));
        $fieldSet->addField(Trans::getFmsWord('weight') . ' (KG)', $this->Field->getNumber('eq_wgh_capacity', $this->getFloatParameter('eq_wgh_capacity')));
        # Create a portlet box.
        $portlet = new Portlet('EqCapacityPtl', Trans::getFmsWord('capacity'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the specification Field Set.
     *
     * @return Portlet
     */
    private function getSpecificationFieldSet(): Portlet
    {
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);

        #Create Brand Equipment Field
        $brandField = $this->Field->getSingleSelect('sty', 'eq_sty_name', $this->getStringParameter('eq_sty_name'));
        $brandField->setHiddenField('eq_sty_id', $this->getIntParameter('eq_sty_id'));
        $brandField->addParameter('sty_group', 'brandequipment');
        $brandField->setEnableNewButton(false);
        $brandField->setEnableDetailButton(false);

        #Create Fuel Type Field
        $ftyField = $this->Field->getSingleSelect('sty', 'eq_fty_name', $this->getStringParameter('eq_fty_name'));
        $ftyField->setHiddenField('eq_fty_id', $this->getIntParameter('eq_fty_id'));
        $ftyField->addParameter('sty_group', 'fueltype');
        $ftyField->setEnableNewButton(false);
        $ftyField->setEnableDetailButton(false);

        # Add field to fieldset
        $fieldSet->addField(Trans::getFmsWord('brand'), $brandField);
        $fieldSet->addField(Trans::getFmsWord('fuelType'), $ftyField);
        $fieldSet->addField(Trans::getFmsWord('engineCapacity') . ' (CC)', $this->Field->getText('eq_engine_capacity', $this->getStringParameter('eq_engine_capacity')));
        $fieldSet->addField(Trans::getFmsWord('fuelConsume') . ' (KM) Per Liter', $this->Field->getNumber('eq_fuel_consume', $this->getFloatParameter('eq_fuel_consume')));
        $fieldSet->addField(Trans::getFmsWord('builtYear'), $this->Field->getText('eq_built_year', $this->getIntParameter('eq_built_year')));
        $fieldSet->addField(Trans::getFmsWord('color'), $this->Field->getText('eq_color', $this->getStringParameter('eq_color')));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('eq_length', $this->getFloatParameter('eq_length')));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('eq_width', $this->getFloatParameter('eq_width')));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('eq_height', $this->getFloatParameter('eq_height')));
        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('eq_weight', $this->getFloatParameter('eq_weight')));
        $fieldSet->addField(Trans::getWord('volume') . ' (M3)', $this->Field->getNumber('eq_volume', $this->getFloatParameter('eq_volume')));
        $fieldSet->addField(Trans::getFmsWord('maxSpeed') . ' (KM)', $this->Field->getText('eq_max_speed', $this->getStringParameter('eq_max_speed')));
//        $fieldSet->addField(Trans::getFmsWord('picture'), $this->Field->getFile('eq_picture', $this->getStringParameter('eq_picture')));
        # Create portlet box.
        $portlet = new Portlet('EqSpecificationPtl', Trans::getFmsWord('specification'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getGalleryPortlet(): Portlet
    {
        $portlet = new Portlet('EqGlrPtl', Trans::getWord('gallery'));
        $imUploadModal = $this->getGalleryModal();
        $this->View->addModal($imUploadModal);
        $imDeleteModal = $this->getGalleryDeleteModal();
        $this->View->addModal($imDeleteModal);

        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'equipment')";
        $wheres[] = "(dct.dct_code = 'image')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $docDao = new DocumentDao();
        $i = 0;
        foreach ($data as $row) {
            $i++;
            $path = $docDao->getDocumentPath($row);
            $ca = new CardImage('EqIm' . $i);
            $ca->setHeight(200);
            $btns = [];
            $btn = new Button('BtnIm' . $i, Trans::getWord('view'));
            $btn->setIcon(Icon::Eye)->btnPrimary()->btnSmall();
            $btn->addAttribute('onclick', "App.popup('" . $path . "')");
            $btns[] = $btn;
            $btnDel = new ModalButton('BtnDel' . $i, Trans::getWord('delete'), $imDeleteModal->getModalId());
            $btnDel->setIcon(Icon::Trash)->btnDanger()->btnSmall();
            $btnDel->addParameter('doc_id', $row['doc_id']);
            $btnDel->setEnableCallBack('document', 'getEquipmentImageForDelete');
            $btns[] = $btnDel;
            $ca->setData([
                'title' => '&nbsp;',
                'subtitle' => $row['doc_description'],
                'img_path' => $path,
                'buttons' => $btns,
            ]);
            $portlet->addText($ca->createView());
        }
        $btnDocMdl = new ModalButton('btnGlrMdl', Trans::getWord('upload'), $imUploadModal->getModalId());
        $btnDocMdl->setIcon(Icon::Plus)->pullRight()->pullRight();
        $portlet->addButton($btnDocMdl);

        return $portlet;
    }

    /**
     * Function to get the equipment identity Field Set.
     *
     * @return Portlet
     */
    private function getIdentityFieldSet(): Portlet
    {
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to fieldset
        $fieldSet->addField(Trans::getFmsWord('machineNumber'), $this->Field->getText('eq_machine_number', $this->getStringParameter('eq_machine_number')));
        $fieldSet->addField(Trans::getFmsWord('chassisNumber'), $this->Field->getText('eq_chassis_number', $this->getStringParameter('eq_chassis_number')));
        $fieldSet->addField(Trans::getFmsWord('bpkb'), $this->Field->getText('eq_bpkb', $this->getStringParameter('eq_bpkb')));
        $fieldSet->addField(Trans::getFmsWord('stnk'), $this->Field->getText('eq_stnk', $this->getStringParameter('eq_stnk')));
        $fieldSet->addField(Trans::getFmsWord('keur'), $this->Field->getText('eq_keur', $this->getStringParameter('eq_keur')));
        # Create portlet box.
        $portlet = new Portlet('EqIdentityPtl', Trans::getFmsWord('identity'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the service reminders Field Set.
     *
     * @return Portlet
     */
    private function getServiceRemindersFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqSrvRemindersPtl', Trans::getFmsWord('serviceReminder'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvRemindersTbl');
        $table->setHeaderRow([
            'svrm_svt_name' => Trans::getFmsWord('task'),
            'svrm_interval' => Trans::getFmsWord('schedule'),
            'svrm_next_due_date' => Trans::getFmsWord('nextDueDate'),
            'svrm_status' => Trans::getFmsWord('status'),
            'svrm_last_completed' => Trans::getFmsWord('lastCompleted')
        ]);
        $wheres[] = '(svrm.svrm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svrm.svrm_deleted_on IS NULL )';
        $serviceData = $this->doPrepareServiceRemindersData(ServiceReminderDao::loadCompleteData($wheres));
        $table->addRows($serviceData);
        # Add special table attribute
        $table->addColumnAttribute('svrm_last_completed', 'style', 'text-align: center');
        $table->addColumnAttribute('svrm_status', 'style', 'text-align: center');
        $table->setUpdateActionByHyperlink('serviceReminder/detail', ['svrm_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareServiceRemindersData(array $data): array
    {
        $results = [];
        $numberFormatter = new NumberFormatter();
        foreach ($data as $row) {
            $interval = 'Every ';
            if (empty($row['svrm_time_interval']) === false) {
                $interval .= '<i style="color: #1a3a95" class="' . Icon::Calendar . '" ></i> ' . $row['svrm_time_interval'] . ' ' . $row['svrm_time_interval_period'];
                if (empty($row['svrm_meter_interval']) === false) {
                    $interval .= ' or ';
                }
            }
            if (empty($row['svrm_meter_interval']) === false) {
                $interval .= '<i style="color: #1a3a95" class="' . Icon::Tachometer . '" ></i> ' . $numberFormatter->doFormatFloat($row['svrm_meter_interval']) . ' ' . $row['eq_primary_meter'];
            }
            $row['svrm_interval'] = $interval;
            $row['svrm_last_completed'] = DateTimeParser::format($row['svo_start_service_date'], 'Y-m-d', 'd M Y') . ' <br> ' . $numberFormatter->doFormatFloat($row['svo_meter']) . ' ' . $row['eq_primary_meter'];
            $meterDueText = '';
            $timesDueText = '';
            $svrmNextDueDate = '';
            $meterStatus = '';
            $timesStatus = '';
            $svrmStatus = '';
            # Calculate meter remaining
            if (empty($row['svrm_meter_remaining']) === false && empty($row['eqm_meter']) === false) {
                if ($row['svrm_meter_remaining'] > 0) {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'] . ' From now';
                } elseif ($row['svrm_meter_remaining'] < 0) {
                    $meterDueText = $numberFormatter->doFormatFloat(abs($row['svrm_meter_remaining'])) . ' ' . $row['eq_primary_meter'] . ' Ago';
                } else {
                    $meterDueText = $numberFormatter->doFormatFloat($row['svrm_meter_remaining']) . ' ' . $row['eq_primary_meter'];
                }
                # Set service reminder status compare by meter remaining and threshold
                if ($row['svrm_meter_remaining'] >= 0) {
                    if ($row['svrm_meter_threshold'] >= $row['svrm_meter_remaining']) {
                        $meterStatus = Trans::getFmsWord('comingSoon');
                    }
                } elseif ($row['svrm_meter_remaining'] < 0) {
                    $meterStatus = Trans::getFmsWord('overDue');
                }
            }
            # Calculate times remaining
            if (empty($row['svrm_time_interval']) === false) {
                $now = DateTimeParser::createDateTime(date('Y-m-d'));
                $nextDueDate = DateTimeParser::createDateTime($row['svrm_next_due_date']);
                $dateDiff = DateTimeParser::different($now, $nextDueDate);
                $timesDiffAgg = '';
                if (empty($dateDiff['y']) === false) {
                    $timesDiffAgg .= $dateDiff['y'] . ' Years ';
                }
                if (empty($dateDiff['m']) === false) {
                    $timesDiffAgg .= $dateDiff['m'] . ' Months ';
                }
                if (empty($dateDiff['d']) === false) {
                    $timesDiffAgg .= $dateDiff['d'] . ' Days ';
                }
                if ($now > $nextDueDate) {
                    $timesDueText .= $timesDiffAgg . ' Ago<br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                } elseif ($now < $nextDueDate) {
                    $timesDueText .= $timesDiffAgg . ' From Now <br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                } else {
                    $timesDueText .= $timesDiffAgg . ' <br> on ' . DateTimeParser::format($row['svrm_next_due_date'], 'Y-m-d', 'd M Y');
                }
                # Set service reminder status compare by time remaining and threshold
                $dateThreshold = DateTimeParser::createDateTime($row['svrm_next_due_date_threshold']);
                if ($nextDueDate >= $now) {
                    if ($now >= $dateThreshold) {
                        $timesStatus = Trans::getFmsWord('comingSoon');
                    }
                } else {
                    $timesStatus = Trans::getFmsWord('overDue');
                }
            }
            # Aggerate meter and times due date
            if (empty($meterDueText) === false) {
                $svrmNextDueDate .= $meterDueText;
                if (empty($timesDueText) === false) {
                    $svrmNextDueDate .= '<br>';
                }
            } elseif (empty($timesDueText) === true) {
                if ($row['eq_primary_meter'] === 'km') {
                    $svrmNextDueDate .= 'Odometer not set';
                } elseif ($row['eq_primary_meter'] === 'hours') {
                    $svrmNextDueDate .= 'Hours meter not set';
                }

            }
            if (empty($timesDueText) === false) {
                $svrmNextDueDate .= $timesDueText;
            }
            if (empty($meterStatus) === false || empty($timesStatus) === false) {
                if ($meterStatus === 'Coming Soon' || $timesStatus === 'Coming Soon') {
                    $svrmStatus = new LabelWarning(Trans::getFmsWord('comingSoon'));
                } else {
                    $svrmStatus = new LabelDanger(Trans::getFmsWord('overDue'));
                }
            }
            $row['svrm_next_due_date'] = $svrmNextDueDate;
            $row['svrm_status'] = $svrmStatus;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to get the renewal reminders Field Set.
     *
     * @return Portlet
     */
    private function getRenewalRemindersFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqRnRemindersPtl', Trans::getFmsWord('renewalReminder'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rnRemindersTbl');
        $table->setHeaderRow([
            'rnrm_rnt_name' => Trans::getFmsWord('renewalType'),
            'rnrm_expiry_date' => Trans::getFmsWord('expiryDate'),
            'rnrm_status' => Trans::getFmsWord('status'),
        ]);
        $wheres[] = '(rnrm.rnrm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rnrm.rnrm_deleted_on IS NULL )';
        $renewalData = $this->doPrepareRenewalRemindersData(RenewalReminderDao::loadData($wheres));
        $table->addRows($renewalData);
        # Add special table attribute
        $table->addColumnAttribute('rnrm_status', 'style', 'text-align: center');
        $table->setUpdateActionByHyperlink('renewalReminder/detail', ['rnrm_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare date
     *
     * @param array $data
     *
     * @return array
     */
    private function doPrepareRenewalRemindersData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $now = DateTimeParser::createDateTime(date('Y-m-d'));
            $expiryDate = DateTimeParser::createDateTime($row['rnrm_expiry_date']);
            $dateDiff = DateTimeParser::different($now, $expiryDate);
            $timesDiffAgg = '';
            $timesDueText = '';
            $timesStatus = '';
            if (empty($dateDiff['y']) === false) {
                $timesDiffAgg .= $dateDiff['y'] . ' Years ';
            }
            if (empty($dateDiff['m']) === false) {
                $timesDiffAgg .= $dateDiff['m'] . ' Months ';
            }
            if (empty($dateDiff['d']) === false) {
                $timesDiffAgg .= $dateDiff['d'] . ' Days ';
            }
            if ($now > $expiryDate) {
                $timesDueText .= $timesDiffAgg . ' Ago';
            } elseif ($now < $expiryDate) {
                $timesDueText .= $timesDiffAgg . ' From Now';
            } else {
                $timesDueText .= $timesDiffAgg . 'Today';
            }

            $row['rnrm_expiry_date'] = DateTimeParser::format($row['rnrm_expiry_date'], 'Y-m-d', 'd M Y') . '<br>' . $timesDueText;
            # Set service reminder status compare by time remaining and threshold
            $dateThreshold = DateTimeParser::createDateTime($row['rnrm_expiry_threshold_date']);
            if ($expiryDate >= $now) {
                if ($now >= $dateThreshold) {
                    $timesStatus = new LabelWarning(Trans::getFmsWord('comingSoon'));
                }
            } else {
                $timesStatus = new LabelDanger(Trans::getFmsWord('overDue'));
            }
            $row['rnrm_status'] = $timesStatus;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to get the service history Field Set.
     *
     * @return Portlet
     */
    private function getServiceHistoryFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqSrvHistoryPtl', Trans::getFmsWord('serviceHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('srvHistoryTbl');
        $table->setHeaderRow([
            'svo_number' => Trans::getFmsWord('number'),
            'svo_eq_name' => Trans::getFmsWord('equipment'),
            'svo_order_date' => Trans::getFmsWord('orderDate'),
            'svo_planning_date' => Trans::getFmsWord('planningDate'),
            'svo_meter' => Trans::getFmsWord('meter'),
            'svo_status' => Trans::getFmsWord('status')
        ]);
        $wheres[] = '(svo_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(svo_deleted_on IS NULL )';
        $orderList[] = 'svo.svo_finish_on DESC';
        $orderList[] = 'svo.svo_order_date DESC';
        $serviceData = [];
        $tempData = ServiceOrderDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $convertMeter = $numberFormat->doFormatFloat($row['svo_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['svo_order_date'] = DateTimeParser::format($row['svo_order_date'], 'Y-m-d', 'd M Y');
            $row['svo_planning_date'] = DateTimeParser::format($row['svo_planning_date'], 'Y-m-d', 'd M Y');
            $row['svo_meter'] = $convertMeter;
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['svo_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['svo_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['svo_finish_on']) === true && empty($row['svo_start_service_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onService'));
            } elseif (empty($row['svo_start_service_date']) === true && empty($row['svo_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['svo_approved_on']) === true && empty($row['svr_id']) === false) {
                if (empty($row['svr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['svo_status'] = $status;
            $serviceData[] = $row;
        }
        $table->addRows($serviceData);
        # Add special table attribute
        $table->addColumnAttribute('svo_meter', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_order_date', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_planning_date', 'style', 'text-align: center');
        $table->addColumnAttribute('svo_status', 'style', 'text-align: center');
        $table->setViewActionByHyperlink(url('serviceOrder/view'), ['svo_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the renewal history Field Set.
     *
     * @return Portlet
     */
    private function getRenewalHistoryFieldSet(): Portlet
    {
        # Create portlet box.
        $portlet = new Portlet('EqRnHistoryPtl', Trans::getFmsWord('renewalHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('rnHistoryTbl');
        $table->setHeaderRow([
            'rno_number' => Trans::getFmsWord('number'),
            'rno_eq_name' => Trans::getFmsWord('equipment'),
            'rno_order_date' => Trans::getFmsWord('orderDate'),
            'rno_planning_date' => Trans::getFmsWord('planningDate'),
            'rno_status' => Trans::getFmsWord('status')
        ]);
        $wheres[] = '(rno_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(rno_deleted_on IS NULL )';
        $orderList[] = 'rno.rno_finish_on DESC';
        $orderList[] = 'rno.rno_order_date DESC';
        $renewalData = [];
        $tempData = RenewalOrderDao::loadData($wheres, $orderList);
        foreach ($tempData as $row) {
            $status = new LabelGray(Trans::getFmsWord('draft'));
            if (empty($row['rno_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFmsWord('deleted'));
            } elseif (empty($row['rno_finish_on']) === false) {
                $status = new LabelSuccess(Trans::getFmsWord('finish'));
            } elseif (empty($row['rno_finish_on']) === true && empty($row['rno_start_renewal_date']) === false) {
                $status = new LabelPrimary(Trans::getFmsWord('onProgress'));
            } elseif (empty($row['rno_start_renewal_date']) === true && empty($row['rno_approved_on']) === false) {
                $status = new LabelInfo(Trans::getFmsWord('approved'));
            } elseif (empty($row['rno_approved_on']) === true && empty($row['rnr_id']) === false) {
                if (empty($row['rnr_reject_reason']) === true) {
                    $status = new LabelWarning(Trans::getFmsWord('request'));
                } else {
                    $status = new LabelDanger(Trans::getFmsWord('reject'));
                }
            }
            $row['rno_order_date'] = DateTimeParser::format($row['rno_order_date'], 'Y-m-d', 'd M Y');
            $row['rno_planning_date'] = DateTimeParser::format($row['rno_planning_date'], 'Y-m-d', 'd M Y');
            $row['rno_status'] = $status;
            $renewalData[] = $row;
        }
        $table->addRows($renewalData);
        # Add special settings to the table
        $table->addColumnAttribute('rno_order_date', 'style', 'text-align: center');
        $table->addColumnAttribute('rno_planning_date', 'style', 'text-align: center');
        $table->addColumnAttribute('rno_status', 'style', 'text-align: center');
        $table->setViewActionByHyperlink(url('renewalOrder/view'), ['rno_id']);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the meter history Field Set.
     *
     * @return Portlet
     */
    private function getMeterHistoryFieldSet(): Portlet
    {
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        # Create portlet box.
        $portlet = new Portlet('EqMtrHistoryyPtl', $textMeter . ' ' . Trans::getFmsWord('history'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('mtrHistoryTbl');
        $table->setHeaderRow([
            'eqm_date' => Trans::getFmsWord('date'),
            'eqm_meter_convert' => $textMeter,
            'eqm_source' => Trans::getFmsWord('source'),
        ]);
        $wheres[] = '(eqm.eqm_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(eqm.eqm_deleted_on IS NULL )';
        $orderList[] = 'eqm.eqm_date DESC';
        $orderList[] = 'eqm.eqm_meter DESC';
        $meterData = [];
        $tempData = EquipmentMeterDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $convertMeter = $numberFormat->doFormatFloat($row['eqm_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['eqm_date'] = DateTimeParser::format($row['eqm_date'], 'Y-m-d', 'd M Y');
            $row['eqm_meter_convert'] = $convertMeter;
            $meterData[] = $row;
        }
        $table->addRows($meterData);
        # Add special table attribute
        $table->addColumnAttribute('eqm_meter_convert', 'style', 'text-align: center');
        $table->addColumnAttribute('eqm_date', 'style', 'text-align: center');
        $table->addColumnAttribute('eqm_source', 'style', 'text-align: center');
        # add new modal button
        $modal = $this->getMeterHistoryModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getMeterHistoryDeleteModal();
        $this->View->addModal($modalDelete);
        $table->setUpdateActionByModal($modal, 'equipmentMeter', 'getByReference', ['eqm_id']);
        $table->setDeleteActionByModal($modalDelete, 'equipmentMeter', 'getByReferenceForDelete', ['eqm_id']);
        $btnMtrHisMdl = new ModalButton('btnMtrHisMdl', Trans::getFmsWord('update') . ' ' . $textMeter, $modal->getModalId());
        $btnMtrHisMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnMtrHisMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get Meter History modal.
     *
     * @return Modal
     */
    private function getMeterHistoryModal(): Modal
    {
        # Create Fields.
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        $modal = new Modal('MtrHisMdl', Trans::getFmsWord('update') . ' ' . $textMeter);
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateMeter');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateMeter' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField($textMeter, $this->Field->getNumber('eqm_meter', $this->getParameterForModal('eqm_meter', $showModal)), true);
        $fieldSet->addField(Trans::getFmsWord('date'), $this->Field->getCalendar('eqm_date', $this->getParameterForModal('eqm_date', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('eqm_id', $this->getParameterForModal('eqm_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Meter History delete modal.
     *
     * @return Modal
     */
    private function getMeterHistoryDeleteModal(): Modal
    {
        # Create Fields.
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        $modal = new Modal('MtrHisDelMdl', Trans::getFmsWord('delete') . ' ' . $textMeter);
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteMeter');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteMeter' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField($textMeter, $this->Field->getText('eqm_meter_del', $this->getParameterForModal('eqm_meter_del', $showModal)), true);
        $fieldSet->addField(Trans::getFmsWord('date'), $this->Field->getCalendar('eqm_date_del', $this->getParameterForModal('eqm_date_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('eqm_id_del', $this->getParameterForModal('eqm_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the fuel history Field Set.
     *
     * @return Portlet
     */
    private function getFuelHistoryFieldSet(): Portlet
    {
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        # Create portlet box.
        $portlet = new Portlet('EqFuelHistoryyPtl', Trans::getFmsWord('fuelHistory'));
        $portlet->setGridDimension(12, 12, 12);
        # Create Fieldset.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create table
        $table = new Table('fuelHistoryTbl');
        $table->setHeaderRow([
            'eqf_date' => Trans::getFmsWord('recordDate'),
            'eqf_meter_text' => $textMeter,
            'eqf_qty_fuel_text' => Trans::getFmsWord('fuel'),
            'eqf_cost' => Trans::getFmsWord('costPerLiter'),
            'eqf_total' => Trans::getFmsWord('total'),
        ]);
        $wheres[] = '(eqf.eqf_eq_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(eqf.eqf_deleted_on IS NULL )';
        $orderList[] = 'eqf.eqf_date DESC';
        $orderList[] = 'eqf.eqf_meter DESC';
        $meterData = [];
        $tempData = EquipmentFuelDao::loadData($wheres, $orderList);
        $numberFormat = new NumberFormatter();
        foreach ($tempData as $row) {
            $meterText = $numberFormat->doFormatFloat($row['eqf_meter']) . ' ' . $this->getStringParameter('eq_primary_meter');
            $row['eqf_date'] = DateTimeParser::format($row['eqf_date'], 'Y-m-d', 'd M Y');
            $row['eqf_meter_text'] = $meterText;
            $row['eqf_qty_fuel_text'] = $numberFormat->doFormatFloat($row['eqf_qty_fuel']) . ' L';
            $row['eqf_total'] = ($row['eqf_qty_fuel'] * $row['eqf_cost']);
            $meterData[] = $row;
        }
        $table->addRows($meterData);
        # Add special table attribute
        $table->addColumnAttribute('eqf_meter_text', 'style', 'text-align: center');
        $table->addColumnAttribute('eqf_qty_fuel_text', 'style', 'text-align: center');
        $table->addColumnAttribute('eqf_date', 'style', 'text-align: center');
        $table->setColumnType('eqf_cost', 'currency');
        $table->setColumnType('eqf_total', 'currency');
        # add button edit
        $table->setUpdateActionByHyperlink('equipmentFuel/detail', ['eqf_id']);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('EqDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete')
        ]);
        // $docTable->setDeleteActionByModal($docDeleteModal, 'document', 'getByReferenceForDelete', ['doc_id']);
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'equipment')";
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
        $portlet = new Portlet('EqFotoPtl', Trans::getWord('document'));
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
     * @return Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('EqDocMdl', Trans::getWord('documents'));
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
        $dctFields->addParameter('dcg_code', 'equipment');
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
     * @return Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('EqDocDelMdl', Trans::getWord('deleteDocument'));
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
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getGalleryModal(): Modal
    {
        $dct = DocumentTypeDao::getByCode('equipment', 'image');
        $modal = new Modal('EqGlrMdl', Trans::getWord('image'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadImage');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadImage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('image'), $this->Field->getFile('eq_im_file', ''), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('eq_im_description', $this->getParameterForModal('eq_im_description', $showModal)));
        $fieldSet->addField(Trans::getWord('mainImage'), $this->Field->getYesNo('eq_im_main', $this->getParameterForModal('eq_im_main', $showModal)), true);
        if (empty($dct) === false) {
            $fieldSet->addHiddenField($this->Field->getHidden('eq_im_dct', $dct['dct_id']));
        } else {
            $fieldSet->addHiddenField($this->Field->getHidden('eq_im_dct'));
        }
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getGalleryDeleteModal(): Modal
    {
        $modal = new Modal('EqGlrDelMdl', Trans::getWord('deleteImage'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteImage');

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('eq_im_description_del', $this->getParameterForModal('eq_im_description_del')));
        $fieldSet->addField(Trans::getWord('fileName'), $this->Field->getText('eq_im_name_del', $this->getParameterForModal('eq_im_name_del')));
        $fieldSet->addHiddenField($this->Field->getHidden('eq_im_id_del', $this->getParameterForModal('eq_im_id_del')));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('eq_number');
        $status = $this->getStringParameter('eq_eqs_name');
        if ($status === 'Not Available') {
            $status = new LabelDark($status);
        } elseif ($status === 'Available') {
            $status = new LabelSuccess($status);
        } elseif ($status === 'On Service') {
            $status = new LabelWarning($status);
        }
        $this->View->setDescription($title . ' | ' . $status);

    }

}
