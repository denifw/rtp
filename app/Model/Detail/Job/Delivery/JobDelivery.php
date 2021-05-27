<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job\Delivery;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\LabelTrueFalse;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Model\Dao\CustomerService\SalesGoodsPositionDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDetailDao;
use App\Model\Dao\Job\Delivery\LoadUnloadDeliveryDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Detail\Job\BaseJobOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail JobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobDelivery extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jdl', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $joId = parent::doInsert();
        $jdlColVal = [
            'jdl_jo_id' => $joId,
            'jdl_so_id' => $this->getIntParameter('jdl_so_id'),
            'jdl_consolidate' => $this->getStringParameter('jdl_consolidate', 'N'),
            'jdl_departure_date' => $this->getStringParameter('jdl_departure_date'),
            'jdl_departure_time' => $this->getStringParameter('jdl_departure_time'),
            'jdl_tm_id' => $this->getIntParameter('jdl_tm_id'),
            'jdl_eg_id' => $this->getIntParameter('jdl_eg_id'),
        ];
        $jdlDao = new JobDeliveryDao();
        $jdlDao->doInsertTransaction($jdlColVal);

        return $joId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $jdlColVal = [
                'jdl_so_id' => $this->getIntParameter('jdl_so_id'),
                'jdl_consolidate' => $this->getStringParameter('jdl_consolidate', 'N'),
                'jdl_departure_date' => $this->getStringParameter('jdl_departure_date'),
                'jdl_departure_time' => $this->getStringParameter('jdl_departure_time'),
                'jdl_arrival_date' => $this->getStringParameter('jdl_arrival_date'),
                'jdl_arrival_time' => $this->getStringParameter('jdl_arrival_time'),
                'jdl_tm_id' => $this->getIntParameter('jdl_tm_id'),
                'jdl_eg_id' => $this->getIntParameter('jdl_eg_id'),
                'jdl_pol_id' => $this->getIntParameter('jdl_pol_id'),
                'jdl_pod_id' => $this->getIntParameter('jdl_pod_id'),
                'jdl_eq_id' => $this->getIntParameter('jdl_eq_id'),
                'jdl_first_cp_id' => $this->getIntParameter('jdl_first_cp_id'),
                'jdl_second_cp_id' => $this->getIntParameter('jdl_second_cp_id'),
                'jdl_transport_number' => $this->getStringParameter('jdl_transport_number'),
                'jdl_ct_id' => $this->getIntParameter('jdl_ct_id'),
                'jdl_container_number' => $this->getStringParameter('jdl_container_number'),
                'jdl_seal_number' => $this->getStringParameter('jdl_seal_number'),
                'jdl_dp_id' => $this->getIntParameter('jdl_dp_id'),
                'jdl_dp_date' => $this->getStringParameter('jdl_dp_date'),
                'jdl_dp_time' => $this->getStringParameter('jdl_dp_time'),
                'jdl_dr_id' => $this->getIntParameter('jdl_dr_id'),
                'jdl_dr_date' => $this->getStringParameter('jdl_dr_date'),
                'jdl_dr_time' => $this->getStringParameter('jdl_dr_time'),
            ];
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), $jdlColVal);
            # Update depo information on SO
            if ($this->isValidSoId() === true && $this->isContainerJob() === true) {
                $soColVal = [];
                if ($this->isLoad() === true) {
                    $soColVal = [
                        'so_dp_id' => $this->getIntParameter('jdl_dp_id'),
                        'so_yr_id' => $this->getIntParameter('jdl_dr_id'),
                    ];
                }
                if ($this->isUnload() === true) {
                    $soColVal = [
                        'so_yp_id' => $this->getIntParameter('jdl_dp_id'),
                        'so_dr_id' => $this->getIntParameter('jdl_dr_id'),
                    ];
                }
                if (empty($soColVal) === false) {
                    $soDao = new SalesOrderDao();
                    $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
                }

                $listJdl = $this->loadJobDeliveryForUpdateDepo();
                if (empty($listJdl) === false) {
                    foreach ($listJdl as $row) {
                        $jdlDao->doUpdateTransaction($row['jdl_id'], [
                            'jdl_dp_id' => $this->getIntParameter('jdl_dp_id'),
                            'jdl_dr_id' => $this->getIntParameter('jdl_dr_id'),
                        ]);
                    }
                }
            }
        } elseif ($this->getFormAction() === 'doDeleteJobDeliveryDetail') {
            $jdldId = $this->getIntParameter('jdld_id_del');
            $sgpData = JobDeliveryDetailDao::loadGoodsPositionByIdAndJoId($jdldId, $this->getDetailReferenceValue());
            $sgpDao = new SalesGoodsPositionDao();
            foreach ($sgpData as $row) {
                $sgpDao->doDeleteTransaction($row['sgp_id']);
            }
            # Delete Job Delivery Detail
            $jdldDao = new JobDeliveryDetailDao();
            $jdldDao->doDeleteTransaction($jdldId);
        } elseif ($this->getFormAction() === 'doPublishJob') {
            if ($this->isRoadJob() === true) {
                $sogData = SalesOrderGoodsDao::getBySocId($this->getIntParameter('jdld_soc_id'));
                $load = [];
                $unload = [];
                if ($this->getStringParameter('jo_srt_load', 'N') === 'Y') {
                    $load = SalesOrderDeliveryDao::getBySoIdAndType($this->getSoId(), 'O');
                }
                if ($this->getStringParameter('jo_srt_unload', 'N') === 'Y') {
                    $unload = SalesOrderDeliveryDao::getBySoIdAndType($this->getSoId(), 'D');
                }
                $locations = array_merge($load, $unload);
                $ludDao = new LoadUnloadDeliveryDao();
                foreach ($locations as $row) {
                    if (empty($row['sdl_sog_id']) === true) {
                        foreach ($sogData as $sog) {
                            $ludColVal = [
                                'lud_jdl_id' => $this->getIntParameter('jdl_id'),
                                'lud_sdl_id' => $row['sdl_id'],
                                'lud_sog_id' => $sog['sog_id'],
                                'lud_quantity' => $sog['sog_quantity'],
                                'lud_rel_id' => $row['sdl_rel_id'],
                                'lud_of_id' => $row['sdl_of_id'],
                                'lud_pic_id' => $row['sdl_pic_id'],
                                'lud_type' => $row['sdl_type'],
                                'lud_reference' => $row['sdl_reference'],
                            ];
                            $ludDao->doInsertTransaction($ludColVal);
                        }
                    } else {
                        $ludColVal = [
                            'lud_jdl_id' => $this->getIntParameter('jdl_id'),
                            'lud_sdl_id' => $row['sdl_id'],
                            'lud_sog_id' => $row['sdl_sog_id'],
                            'lud_quantity' => $row['sdl_quantity'],
                            'lud_rel_id' => $row['sdl_rel_id'],
                            'lud_of_id' => $row['sdl_of_id'],
                            'lud_pic_id' => $row['sdl_pic_id'],
                            'lud_type' => $row['sdl_type'],
                            'lud_reference' => $row['sdl_reference'],
                        ];
                        $ludDao->doInsertTransaction($ludColVal);
                    }
                }
            }
        } else if ($this->isDeleteAction() === true) {
            $joDao = new JobOrderDao();
            $joDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
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
        return JobDeliveryDao::getByJobIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setHiddenJobDeliveryField();
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getDeliveryPortlet());
        } else {
            $this->Tab->addPortlet('general', $this->getVendorFieldSet());
            $this->Tab->addPortlet('general', $this->getDeliveryPortlet());
            if ($this->isRoadJob() === true) {
                if ($this->isConsolidateJob() === false) {
                    # Show field for Single Sales Order Delivery
                    $jdldData = JobDeliveryDetailDao::getByJobDeliveryId($this->getIntParameter('jdl_id'));
                    if (count($jdldData) === 1) {
                        $jdld = $jdldData[0];
                        $this->setParameters($jdld);
                        $this->setJobDeliveryParameter($jdld);
                    }
                    $this->setHiddenDetailsField();
                    if ($this->isContainerJob() === true) {
                        $this->setHiddenContainerField();
                        $this->Tab->addPortlet('deliveryOrder', $this->getContainerPortlet());
                    }

                    if ($this->isJobPublished() === true) {
                        if ($this->getStringParameter('jo_srt_load', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                            $this->Tab->addPortlet('deliveryOrder', $this->getLoadUnloadPortlet('O'));
                        }
                        if ($this->getStringParameter('jo_srt_unload', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                            $this->Tab->addPortlet('deliveryOrder', $this->getLoadUnloadPortlet('D'));
                        }
                    } else {
                        if ($this->getStringParameter('jo_srt_load', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                            $this->Tab->addPortlet('deliveryOrder', $this->getSoLoadUnloadPortlet('O'));
                        }
                        if ($this->getStringParameter('jo_srt_unload', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                            $this->Tab->addPortlet('deliveryOrder', $this->getSoLoadUnloadPortlet('D'));
                        }
                    }
                } else {
                    #TODO Create consolidate function
                }

            } else {
                # Show field for Multi Sales Order Delivery
                if ($this->isConsolidateJob() === false) {
                    $this->Tab->addPortlet('deliveryOrder', $this->getMultiDeliveryPortlet());
                } else {
                    #TODO Create consolidate function
                }

            }
        }
        $this->includeAllDefaultPortlet();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            if ($this->getStringParameter('jdl_consolidate', 'N') === 'N') {
                $this->Validation->checkRequire('jdl_so_id');
            }
            $this->Validation->checkRequire('jdl_tm_id');
            $this->Validation->checkRequire('jdl_eg_id');
            $this->Validation->checkRequire('jdl_departure_date');
            $this->Validation->checkRequire('jdl_departure_time');
            $this->Validation->checkDate('jdl_departure_date');
            $this->Validation->checkTime('jdl_departure_time');
            if ($this->isValidParameter('jdl_arrival_date') === true) {
                $this->Validation->checkDate('jdl_arrival_date');
            }
            if ($this->isValidParameter('jdl_arrival_time') === true) {
                $this->Validation->checkTime('jdl_arrival_time');
            }
            if ($this->isValidParameter('jdl_first_cp_id') === true && $this->isValidParameter('jdl_second_cp_id') === true) {
                $this->Validation->checkDifferent('jdl_second_cp_id', 'jdl_first_cp_id');
            }
        } else if ($this->getFormAction() === 'doDeleteJobDeliveryDetail') {
            $this->Validation->checkRequire('jdld_id_del');
        }
        parent::loadValidationRole();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        # So Reference
        $soField = $this->Field->getSingleSelectTable('so', 'jdl_so_number', $this->getStringParameter('jdl_so_number'), 'loadActiveData');
        $soField->setHiddenField('jdl_so_id', $this->getIntParameter('jdl_so_id'));
        $soField->setTableColumns([
            'so_number' => Trans::getWord('number'),
            'so_customer' => Trans::getWord('customer'),
            'so_container_text' => Trans::getWord('container'),
            'so_customer_ref' => Trans::getWord('customerRef'),
        ]);
        $soField->setFilters([
            'so_number' => Trans::getWord('number'),
            'so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('customerRef'),
        ]);
        $soField->setAutoCompleteFields([
            'jdl_so_customer' => 'so_customer',
            'jo_customer_ref' => 'so_customer_ref',
            'jo_bl_ref' => 'so_bl_ref',
            'jo_aju_ref' => 'so_aju_ref',
            'jo_sppb_ref' => 'so_sppb_ref',
            'jo_packing_ref' => 'so_packing_ref',
            'jo_container' => 'so_container',
        ]);
        $soField->setValueCode('so_id');
        $soField->setLabelCode('so_number');
        $soField->addParameter('so_ss_id', $this->User->getSsId());
        $this->View->addModal($soField->getModal());


        $srtField = $this->Field->getSingleSelect('serviceTerm', 'jo_service_term', $this->getStringParameter('jo_service_term'));
        $srtField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srtField->addParameter('srv_code', 'delivery');
        $srtField->addParameterById('jdl_so_id', 'jdl_so_id', Trans::getWord('salesOrder'));
        $srtField->addOptionalParameterById('srt_container', 'jdl_container');
        $srtField->setEnableNewButton(false);
        $srtField->setEnableDetailButton(false);
        $srtField->setAutoCompleteFields([
            'jo_srv_id' => 'srt_srv_id'
        ]);
        if ($this->isUpdate() === true) {
            $soField->setReadOnly();
            $srtField->setReadOnly();
        }

        # Customer
        $customerField = $this->Field->getText('jdl_so_customer', $this->getStringParameter('jdl_so_customer'));
        $customerField->setReadOnly();
        $customerRefField = $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref'));
        $customerRefField->setReadOnly();
        $blRefField = $this->Field->getText('jo_bl_ref', $this->getStringParameter('jo_bl_ref'));
        $blRefField->setReadOnly();
        $ajuRefField = $this->Field->getText('jo_aju_ref', $this->getStringParameter('jo_aju_ref'));
        $ajuRefField->setReadOnly();
        $sppbRefField = $this->Field->getText('jo_sppb_ref', $this->getStringParameter('jo_sppb_ref'));
        $sppbRefField->setReadOnly();
        $packingRefField = $this->Field->getText('jo_packing_ref', $this->getStringParameter('jo_packing_ref'));
        $packingRefField->setReadOnly();

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('salesOrder'), $soField, true);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
        $fieldSet->addField(Trans::getWord('customer'), $customerField);
        $fieldSet->addField(Trans::getWord('customerRef'), $customerRefField);
        $fieldSet->addField(Trans::getWord('blRef'), $blRefField);
        $fieldSet->addField(Trans::getWord('ajuRef'), $ajuRefField);
        $fieldSet->addField(Trans::getWord('sppbRef'), $sppbRefField);
        $fieldSet->addField(Trans::getWord('packingRef'), $packingRefField);
        $fieldSet->addHiddenField($this->Field->getHidden('jo_customer', $this->getStringParameter('jo_customer')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_customer_ref', $this->getStringParameter('jo_customer_ref')));

        $fieldSet->addHiddenField($this->Field->getHidden('jdl_container', $this->getStringParameter('jdl_container')));
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addFieldSet($fieldSet);

        $portlet->setGridDimension(8, 8, 8);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getVendorFieldSet(): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('JoVendorPtl', Trans::getWord('vendor'));
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);
        # Create Relation Field
        $vendorField = $this->Field->getSingleSelect('relation', 'jo_vendor', $this->getStringParameter('jo_vendor'));
        $vendorField->setHiddenField('jo_vendor_id', $this->getIntParameter('jo_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setDetailReferenceCode('rel_id');
        $vendorField->addClearField('jo_pic_vendor');
        $vendorField->addClearField('jo_vendor_pic_id');
        $vendorField->addClearField('jdl_equipment');
        $vendorField->addClearField('jdl_eq_id');
        $vendorField->addClearField('jdl_first_driver');
        $vendorField->addClearField('jdl_first_cp_id');
        $vendorField->addClearField('jdl_second_driver');
        $vendorField->addClearField('jdl_second_cp_id');
        # Create Contact Field
        $picVendorField = $this->Field->getSingleSelect('contactPerson', 'jo_pic_vendor', $this->getStringParameter('jo_pic_vendor'));
        $picVendorField->setHiddenField('jo_vendor_pic_id', $this->getIntParameter('jo_vendor_pic_id'));
        $picVendorField->addParameterById('cp_rel_id', 'jo_vendor_id', Trans::getWord('vendor'));
        $picVendorField->setDetailReferenceCode('cp_id');

        if ($this->isJobPublished() === true) {
//            $managerField->setReadOnly();
            $vendorField->setReadOnly();
        }
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField);
        $fieldSet->addField(Trans::getWord('vendor'), $vendorField);
        $fieldSet->addField(Trans::getWord('picVendor'), $picVendorField);
        $fieldSet->addField(Trans::getWord('vendorReference'), $this->Field->getText('jo_vendor_ref', $this->getStringParameter('jo_vendor_ref')));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }

    /**
     * Function to get the delivery Field Set.
     *
     * @return Portlet
     */
    private function getDeliveryPortlet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);

        # Create Equipment Group Field
        $tmField = $this->Field->getSingleSelect('transportModule', 'jdl_transport_module', $this->getStringParameter('jdl_transport_module'));
        $tmField->setHiddenField('jdl_tm_id', $this->getIntParameter('jdl_tm_id'));
        $tmField->setEnableDetailButton(false);
        $tmField->setEnableNewButton(false);
        $tmField->setAutoCompleteFields([
            'jdl_tm_code' => 'tm_code'
        ]);
        $tmField->addClearField('jdl_equipment_group');
        $tmField->addClearField('jdl_eg_id');
        $tmField->addClearField('jdl_equipment');
        $tmField->addClearField('jdl_eq_id');
        $tmField->addClearField('jdl_pol');
        $tmField->addClearField('jdl_pol_id');
        $tmField->addClearField('jdl_pod');
        $tmField->addClearField('jdl_pod_id');
        if ($this->isUpdate() === true) {
            $tmField->setReadOnly();
        }

        # Create Equipment Group Field
        $egField = $this->Field->getSingleSelect('eg', 'jdl_equipment_group', $this->getStringParameter('jdl_equipment_group'));
        $egField->setHiddenField('jdl_eg_id', $this->getIntParameter('jdl_eg_id'));
        $egField->addParameterById('eg_tm_id', 'jdl_tm_id', Trans::getTruckingWord('transportModule'));
        $egField->setEnableDetailButton(false);
        $egField->setEnableNewButton(false);
        $egField->addClearField('jdl_equipment');
        $egField->addClearField('jdl_eq_id');
        if ($this->isRoadJob() === true) {
            $egField->setReadOnly();
        }

        $equipmentField = $this->Field->getSingleSelect('equipment', 'jdl_equipment', $this->getStringParameter('jdl_equipment'));
        $equipmentField->setHiddenField('jdl_eq_id', $this->getIntParameter('jdl_eq_id'));
        $equipmentField->addParameter('eq_ss_id', $this->User->getSsId());
        $equipmentField->addParameterById('eq_eg_id', 'jdl_eg_id', Trans::getTruckingWord('transportType'));
        $equipmentField->addParameterById('eq_manage_by_id', 'jo_vendor_id', Trans::getTruckingWord('vendor'));
        $equipmentField->setDetailReferenceCode('eq_id');
        $equipmentField->setAutoCompleteFields([
            'jdl_equipment_plate' => 'eq_license_plate',
            'jdl_first_cp_id' => 'eq_driver_id',
            'jdl_first_driver' => 'eq_driver',
        ]);

        # Create Contact Field
        $driverField = $this->Field->getSingleSelect('contactPerson', 'jdl_first_driver', $this->getStringParameter('jdl_first_driver'));
        $driverField->setHiddenField('jdl_first_cp_id', $this->getIntParameter('jdl_first_cp_id'));
        $driverField->addParameterById('cp_rel_id', 'jo_vendor_id', Trans::getTruckingWord('vendor'));
        $driverField->setDetailReferenceCode('cp_id');

        # Create Contact Field
        $driverTwoField = $this->Field->getSingleSelect('contactPerson', 'jdl_second_driver', $this->getStringParameter('jdl_second_driver'));
        $driverTwoField->setHiddenField('jdl_second_cp_id', $this->getIntParameter('jdl_second_cp_id'));
        $driverTwoField->addParameterById('cp_rel_id', 'jo_vendor_id', Trans::getTruckingWord('vendor'));
        $driverTwoField->setDetailReferenceCode('cp_id');

        # Create port Field
        $polField = $this->Field->getSingleSelect('port', 'jdl_pol', $this->getStringParameter('jdl_pol'));
        $polField->setHiddenField('jdl_pol_id', $this->getIntParameter('jdl_pol_id'));
        $polField->setEnableNewButton(false);
        # Port of Destination
        $podField = $this->Field->getSingleSelect('port', 'jdl_pod', $this->getStringParameter('jdl_pod'));
        $podField->setHiddenField('jdl_pod_id', $this->getIntParameter('jdl_pod_id'));
        $podField->setEnableNewButton(false);
        if ($this->isUpdate() === true && $this->isRoadJob() === false) {
            $polField->addParameterById('po_tm_id', 'jdl_tm_id', Trans::getWord('transportModule'));
            $podField->addParameterById('po_tm_id', 'jdl_tm_id', Trans::getWord('transportModule'));
        }
        $licenseField = $this->Field->getText('jdl_equipment_plate', $this->getStringParameter('jdl_equipment_plate'));
        $licenseField->setReadOnly();

        # Add field to fieldset
        $fieldSet->addField(Trans::getTruckingWord('transportModule'), $tmField, true);
        $fieldSet->addField(Trans::getTruckingWord('transportType'), $egField, true);
        $fieldSet->addField(Trans::getTruckingWord('etdDate'), $this->Field->getCalendar('jdl_departure_date', $this->getStringParameter('jdl_departure_date')), true);
        $fieldSet->addField(Trans::getTruckingWord('etdTime'), $this->Field->getTime('jdl_departure_time', $this->getStringParameter('jdl_departure_time')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getTruckingWord('transport'), $equipmentField);
            if ($this->isRoadJob() === true) {
                $fieldSet->addField(Trans::getTruckingWord('truckPlate'), $licenseField);
                $fieldSet->addField(Trans::getTruckingWord('mainDriver'), $driverField);
                $fieldSet->addField(Trans::getTruckingWord('secondaryDriver'), $driverTwoField);
            } else {
                $fieldSet->addField(Trans::getTruckingWord('transportNumber'), $this->Field->getText('jdl_transport_number', $this->getStringParameter('jdl_transport_number')));
            }
            $fieldSet->addField(Trans::getTruckingWord('etaDate'), $this->Field->getCalendar('jdl_arrival_date', $this->getStringParameter('jdl_arrival_date')));
            $fieldSet->addField(Trans::getTruckingWord('etaTime'), $this->Field->getTime('jdl_arrival_time', $this->getStringParameter('jdl_arrival_time')));
            if ($this->getStringParameter('jo_srt_pol', 'N') === 'Y') {
                if ($this->getStringParameter('jo_srt_pod', 'N') === 'Y') {
                    $fieldSet->addField(Trans::getTruckingWord('portOfLoading'), $polField);
                } else {
                    $fieldSet->addField(Trans::getTruckingWord('portName'), $polField);
                }
            }
            if ($this->getStringParameter('jo_srt_pod', 'N') === 'Y') {
                if ($this->getStringParameter('jo_srt_pol', 'N') === 'Y') {
                    $fieldSet->addField(Trans::getTruckingWord('portOfDischarge'), $podField);
                } else {
                    $fieldSet->addField(Trans::getTruckingWord('portName'), $podField);
                }
            }
        }


        $fieldSet->addHiddenField($this->Field->getHidden('jdl_consolidate', $this->getStringParameter('jdl_consolidate')));
        # Create a portlet box.
        $portlet = new Portlet('JdlDetailPtl', Trans::getTruckingWord('details'));
        if ($this->isInsert() === true) {
            $fieldSet->setGridDimension(12, 12, 12);
            $portlet->setGridDimension(4, 4, 4);
        }
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the Single Goods Delivery.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        # Sales Order Delivery Reference
        # Party Id
        $partyField = $this->Field->getText('jdld_soc_number', $this->getStringParameter('jdld_soc_number'));
        $partyField->setReadOnly();
        $containerTypeField = $this->Field->getText('jdld_container_type', $this->getStringParameter('jdld_container_type'));
        $containerTypeField->setReadOnly();
        $containerNumberField = $this->Field->getText('jdld_container_number', $this->getStringParameter('jdld_container_number'));
        $containerNumberField->setReadOnly();
        $sealNumberField = $this->Field->getText('jdld_seal_number', $this->getStringParameter('jdld_seal_number'));
        $sealNumberField->setReadOnly();

        # Create Depo Owner
        $dpOwnerField = $this->Field->getSingleSelect('relation', 'jdl_dp_owner', $this->getStringParameter('jdl_dp_owner'));
        $dpOwnerField->setHiddenField('jdl_dp_rel_id', $this->getIntParameter('jdl_dp_rel_id'));
        $dpOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $dpOwnerField->setDetailReferenceCode('rel_id');
        $dpOwnerField->addClearField('jdl_dp_name');
        $dpOwnerField->addClearField('jdl_dp_id');
        # Create depo name
        $dpField = $this->Field->getSingleSelect('office', 'jdl_dp_name', $this->getStringParameter('jdl_dp_name'));
        $dpField->setHiddenField('jdl_dp_id', $this->getIntParameter('jdl_dp_id'));
        $dpField->addParameterById('of_rel_id', 'jdl_dp_rel_id', Trans::getTruckingWord('owner'));
        $dpField->setDetailReferenceCode('of_id');

        # Create Depo Owner
        $drOwnerField = $this->Field->getSingleSelect('relation', 'jdl_dr_owner', $this->getStringParameter('jdl_dr_owner'));
        $drOwnerField->setHiddenField('jdl_dr_rel_id', $this->getIntParameter('jdl_dr_rel_id'));
        $drOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $drOwnerField->setDetailReferenceCode('rel_id');
        $drOwnerField->addClearField('jdl_dr_name');
        $drOwnerField->addClearField('jdl_dr_id');
        # Create depo name
        $drField = $this->Field->getSingleSelect('office', 'jdl_dr_name', $this->getStringParameter('jdl_dr_name'));
        $drField->setHiddenField('jdl_dr_id', $this->getIntParameter('jdl_dr_id'));
        $drField->addParameterById('of_rel_id', 'jdl_dr_rel_id', Trans::getTruckingWord('owner'));
        $drField->setDetailReferenceCode('of_id');

        # create actual date field
        $dpOwnerLabel = Trans::getTruckingWord('depoPickUpOwner');
        $dpNameLabel = Trans::getTruckingWord('depoPickUp');
        $drOwnerLabel = Trans::getTruckingWord('depoReturnOwner');
        $drNameLabel = Trans::getTruckingWord('depoReturn');
        $srtRoute = $this->getStringParameter('jo_srt_route');
        if ($srtRoute === 'dtpc') {
            $drOwnerLabel = Trans::getTruckingWord('yardOwner');
            $drNameLabel = Trans::getTruckingWord('yardName');
        } elseif ($srtRoute === 'ptdc') {
            $dpOwnerLabel = Trans::getTruckingWord('yardOwner');
            $dpNameLabel = Trans::getTruckingWord('yardName');
        } elseif ($srtRoute === 'dtp') {
            $drOwnerLabel = Trans::getTruckingWord('warehouseOwner');
            $drNameLabel = Trans::getTruckingWord('warehouseName');
        } elseif ($srtRoute === 'ptd') {
            $dpOwnerLabel = Trans::getTruckingWord('warehouseOwner');
            $dpNameLabel = Trans::getTruckingWord('warehouseName');
        }
        # Add field to fieldset
        $fieldSet->addField(Trans::getTruckingWord('partyId'), $partyField);
        $fieldSet->addField(Trans::getTruckingWord('containerType'), $containerTypeField);
        $fieldSet->addField(Trans::getTruckingWord('containerNumber'), $containerNumberField);
        $fieldSet->addField(Trans::getTruckingWord('sealNumber'), $sealNumberField);
        $fieldSet->addField($dpOwnerLabel, $dpOwnerField);
        $fieldSet->addField($dpNameLabel, $dpField);
        $fieldSet->addField($drOwnerLabel, $drOwnerField);
        $fieldSet->addField($drNameLabel, $drField);
        # Create a portlet box.
        $portlet = new Portlet('JoJdldPtl', Trans::getTruckingWord('container'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the Load unload portlet.
     *
     * @param string $type To store the type location, is it O or D.
     *
     *
     * @return Portlet
     */
    private function getLoadUnloadPortlet(string $type): Portlet
    {
        $table = new Table('JdldLudTbl' . $type);
        $table->setHeaderRow([
            'lud_relation' => Trans::getTruckingWord('relation'),
            'lud_address' => Trans::getTruckingWord('address'),
            'lud_pic' => Trans::getTruckingWord('pic'),
            'lud_reference' => Trans::getTruckingWord('reference'),
            'lud_sog_name' => Trans::getTruckingWord('goods'),
            'lud_quantity' => Trans::getTruckingWord('quantity'),
        ]);

        $data = LoadUnloadDeliveryDao::getByJobDeliveryIdAndType($this->getIntParameter('jdl_id'), $type);
        $rows = [];
        $formatter = new StringFormatter();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['lud_address'] = $formatter->doFormatAddress($row, 'lud');
            if (empty($row['lud_sog_hs_code']) === false) {
                $row['lud_sog_name'] = $row['lud_sog_hs_code'] . ' - ' . $row['lud_sog_name'];
            }
            $quantity = '';
            if (empty($row['lud_quantity']) === false) {
                $quantity = $number->doFormatFloat($row['lud_quantity']);
                if (empty($row['lud_uom_code']) === false) {
                    $quantity .= ' ' . $row['lud_uom_code'];
                }
            }
            $row['lud_quantity'] = $quantity;
            $rows[] = $row;
        }

        $table->addRows($rows);
        $title = Trans::getTruckingWord('loadingAddress');
        if ($type === 'D') {
            $title = Trans::getTruckingWord('unloadingAddress');
        }
        # Create a portlet box.
        $portlet = new Portlet('JdldLudPtl' . $type, $title);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the multi sales order delivery.
     *
     * @return Portlet
     */
    private function getMultiDeliveryPortlet(): Portlet
    {
        $table = new Table('JoJdlTbl');
        $table->setHeaderRow([
            'jdld_equipment_group' => Trans::getTruckingWord('truckType'),
            'goods_name' => Trans::getTruckingWord('goods'),
            'goods_gross_weight' => Trans::getTruckingWord('grossWeight') . ' (KG)',
            'goods_net_weight' => Trans::getTruckingWord('netWeight') . ' (KG)',
            'goods_cbm' => Trans::getTruckingWord('cbm'),
        ]);
        if ($this->isContainerJob() === true) {
            $table->addColumnAfter('jdld_equipment_group', 'jdld_container_type', Trans::getTruckingWord('containerType'));
            $table->addColumnAfter('jdld_container_type', 'jdld_container_number', Trans::getTruckingWord('containerNumber'));
            $table->addColumnAfter('jdld_container_number', 'jdld_seal_number', Trans::getTruckingWord('containerNumber'));
        }
        $table->addRows($this->loadJobDeliveryDetailData());

        $deleteModal = $this->getDeleteDeliveryDetailModal();
        $this->View->addModal($deleteModal);
        $table->setDeleteActionByModal($deleteModal, 'jdld', 'getByIdForDelete', ['jdld_id']);
        # Create a portlet box.
        $portlet = new Portlet('JoMtJdldPtl', Trans::getTruckingWord('salesOrderDelivery'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get delete modal for job delivery detail.
     *
     * @return Modal
     */
    private function getDeleteDeliveryDetailModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdlJdldDelMdl', Trans::getTruckingWord('deleteOrderDelivery'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteJobDeliveryDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteJobDeliveryDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Create Container Type
        $socNumberField = $this->Field->getText('jdld_soc_number_del', $this->getParameterForModal('jdld_soc_number_del', $showModal));
        $egField = $this->Field->getText('jdld_equipment_group_del', $this->getParameterForModal('jdld_equipment_group_del', $showModal));
        # Create Container Type
        $containerNumberField = $this->Field->getText('jdld_container_number_del', $this->getParameterForModal('jdld_container_number_del', $showModal));
        $typeField = $this->Field->getText('jdld_container_type_del', $this->getParameterForModal('jdld_container_type_del', $showModal));
        # Create Seal Number
        $sealNumberField = $this->Field->getText('jdld_seal_number_del', $this->getParameterForModal('jdld_seal_number_del', $showModal));
        # Add field to fieldset
        # Add field to fieldset
        $fieldSet->addField(Trans::getTruckingWord('transportId'), $socNumberField);
        $fieldSet->addField(Trans::getTruckingWord('transportType'), $egField);
        if ($this->isContainerJob() === true) {
            $fieldSet->addField(Trans::getTruckingWord('containerNumber'), $containerNumberField);
            $fieldSet->addField(Trans::getTruckingWord('containerType'), $typeField);
            $fieldSet->addField(Trans::getTruckingWord('sealNumber'), $sealNumberField);
        }
        $fieldSet->addHiddenField($this->Field->getHidden('jdld_id_del', $this->getParameterForModal('jdld_id_del', $showModal)));
        # Show confirmation message
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load job delivery detail data.
     *
     * @return array
     */
    private function loadJobDeliveryDetailData(): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_jdl_id', $this->getIntParameter('jdl_id'));
        $data = JobDeliveryDetailDao::loadGoodsDataContainer($wheres);
        if (empty($data) === false) {
            $tempIds = [];
            $temp = [];
            $number = new NumberFormatter($this->User);
            foreach ($data as $row) {
                $quantity = (float)$row['jdld_goods_quantity'];
                $grossWeight = (float)$row['jdld_goods_gross_weight'];
                $netWeight = (float)$row['jdld_goods_net_weight'];
                $cbm = (float)$row['jdld_goods_cbm'];
                if ($row['jdld_goods_dimension_unit'] === 'Y') {
                    $netWeight *= $quantity;
                    $grossWeight *= $quantity;
                    $cbm *= $quantity;
                }
                if (in_array($row['jdld_id'], $tempIds, true) === false) {
                    $row['goods'] = [];
                    $row['goods'][] = [
                        'label' => $row['jdld_goods'],
                        'value' => $number->doFormatFloat($quantity) . ' ' . $row['jdld_goods_uom'],
                    ];

                    $row['gross_weight'] = [$number->doFormatFloat($grossWeight)];
                    $row['net_weight'] = [$number->doFormatFloat($netWeight)];
                    $row['cbm'] = [$number->doFormatFloat($cbm)];
                    $tempIds[] = $row['jdld_id'];
                    $temp[] = $row;
                } else {
                    $index = array_search($row['jdld_id'], $tempIds, true);
                    $temp[$index]['goods'][] = [
                        'label' => $row['jdld_goods'],
                        'value' => $number->doFormatFloat($quantity) . $row['jdld_goods_uom'],
                    ];
                    $temp[$index]['gross_weight'][] = $number->doFormatFloat($grossWeight);
                    $temp[$index]['net_weight'][] = $number->doFormatFloat($netWeight);
                    $temp[$index]['cbm'][] = $number->doFormatFloat($cbm);
                }
            }
            foreach ($temp as $row) {
                $row['goods_name'] = StringFormatter::generateKeyValueTableView($row['goods']);
                $row['goods_gross_weight'] = StringFormatter::generateTableView($row['gross_weight']);
                $row['goods_net_weight'] = StringFormatter::generateTableView($row['net_weight']);
                $row['goods_cbm'] = StringFormatter::generateTableView($row['cbm']);
                $results[] = $row;
            }
        }
        return $results;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        # Set Permission delete, only allow delete when job is not started.
        $this->EnableDelete = !$this->isValidParameter('jt_start_on');
        parent::loadDefaultButton();

//        if ($this->isJobPublished() === true && $this->isJobDeleted() === false) {
//            $diButton = new PdfButton('JoDiPrt', 'DI', 'trdi');
//            $diButton->setIcon(Icon::FilePdfO)->btnDark()->pullRight()->btnMedium();
//            $diButton->addParameter('jo_id', $this->getDetailReferenceValue());
//            $this->View->addButtonAtTheBeginning($diButton);
//        }
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getJoPublishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoPubMdl', Trans::getWord('publishConfirmation'));
        $requireFieldError = $this->getRequiredFieldsForPublish();
        if (empty($requireFieldError) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredFields'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requireFieldError);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else if (JobDeliveryDetailDao::isJobDeliveryHasDetail($this->getIntParameter('jdl_id')) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingDeliveryOrderDetails'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requireFieldError);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } elseif ($this->isAllowPublishWithoutFinanceData() === false && (empty($this->JobSales) === true || empty($this->JobPurchase) === true)) {
            $p = new Paragraph(Trans::getMessageWord('emptyJobFinanceData'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $p = new Paragraph(Trans::getMessageWord('publishJobConfirmation'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
            $modal->setBtnOkName(Trans::getWord('yesPublish'));
        }

        return $modal;
    }


    /**
     * Function to get required fields for publish job
     *
     * @return string
     */
    private function getRequiredFieldsForPublish(): string
    {
        $dpNameLabel = 'depoPickUp';
        $drNameLabel = 'depoReturn';
        $srtRoute = $this->getStringParameter('jo_srt_route');
        if ($srtRoute === 'dtpc') {
            $drNameLabel = 'yardName';
        } elseif ($srtRoute === 'ptdc') {
            $dpNameLabel = 'yardName';
        }
        $required = [
            'jo_manager_id' => 'jobManager',
            'jo_vendor_id' => 'vendor',
//            'jdl_eq_id' => 'transportPlate',
        ];
        if ($this->isRoadJob() === true) {
//            $required['jdl_first_cp_id'] = 'mainDriver';
            if ($this->isContainerJob() === true) {
                $required['jdl_dp_id'] = $dpNameLabel;
                $required['jdl_dr_id'] = $drNameLabel;
            }
        } else {
            $required['jdl_transport_number'] = 'transportNumber';

        }
        if ($this->getStringParameter('jo_srt_pol', 'N') === 'Y') {
            $required['jdl_pol_id'] = 'portOfLoading';
        }
        if ($this->getStringParameter('jo_srt_pod', 'N') === 'Y') {
            $required['jdl_pod_id'] = 'portOfDischarge';
        }
        $errors = [];
        foreach ($required as $key => $label) {
            if ($this->isValidParameter($key) === false) {
                $errors[] = [
                    'label' => Trans::getWord($label),
                    'value' => new LabelTrueFalse(false),
                ];
            }
        }
        # Validate Load Unload Address
        if ($this->isRoadJob() === true && $this->isContainerJob() === false) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNullCondition('lud.lud_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('lud.lud_of_id');
            $wheres[] = SqlHelper::generateNumericCondition('lud.lud_jdl_id', $this->getIntParameter('jdl_id'));
            $data = LoadUnloadDeliveryDao::loadData($wheres);
            if (empty($data) === false) {
                $error = [];
                foreach ($data as $row) {
                    if ($row['lud_type'] === 'D') {
                        $error = [
                            'label' => Trans::getWord('warehouseDestination'),
                            'value' => new LabelTrueFalse(false),
                        ];
                    } else {
                        $error = [
                            'label' => Trans::getWord('warehousePickUp'),
                            'value' => new LabelTrueFalse(false),
                        ];
                    }
                }
                $errors[] = $error;
            }
        }
        if (empty($errors) === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($errors, 8, 8);
    }


    /**
     * Function to check is this transport module of road
     *
     * @return bool
     */
    private function isRoadJob(): bool
    {
        return $this->getStringParameter('jdl_tm_code', '') === 'road';
    }

    /**
     * Function to check is this consolidate job or not
     *
     * @return bool
     */
    private function isConsolidateJob(): bool
    {
        return $this->getStringParameter('jdl_consolidate', 'N') === 'Y';
    }

    /**
     * Function to check is this consolidate job or not
     *
     * @return bool
     */
    private function isLoad(): bool
    {
        return $this->getStringParameter('jo_srt_load', 'N') === 'Y';
    }

    /**
     * Function to check is this consolidate job or not
     *
     * @return bool
     */
    private function isUnload(): bool
    {
        return $this->getStringParameter('jo_srt_unload', 'N') === 'Y';
    }
//
//    /**
//     * Function to check is this transport module of sea
//     *
//     * @return bool
//     */
//    private function isSeaJob(): bool
//    {
//        return $this->getStringParameter('jdl_tm_code', '') === 'sea';
//    }
//
//    /**
//     * Function to check is this transport module of air
//     *
//     * @return bool
//     */
//    private function isAirJob(): bool
//    {
//        return $this->getStringParameter('jdl_tm_code', '') === 'air';
//    }
//
//    /**
//     * Function to check is this transport module of rail
//     *
//     * @return bool
//     */
//    private function isRailJob(): bool
//    {
//        return $this->getStringParameter('jdl_tm_code', '') === 'rail';
//    }


    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setHiddenJobDeliveryField(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jdl_id', $this->getIntParameter('jdl_id'));
        $content .= $this->Field->getHidden('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $content .= $this->Field->getHidden('jdl_consolidate', $this->getStringParameter('jdl_consolidate'));
        $content .= $this->Field->getHidden('jdl_tm_code', $this->getStringParameter('jdl_tm_code'));
        $this->View->addContent('JdlHdFls1', $content);

    }

    /**
     * Function to set hidden for container fields.
     *
     * @return void
     */
    private function setHiddenContainerField(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jdl_ct_id', $this->getIntParameter('jdl_ct_id'));
        $content .= $this->Field->getHidden('jdl_container_number', $this->getStringParameter('jdl_container_number'));
        $content .= $this->Field->getHidden('jdl_seal_number', $this->getStringParameter('jdl_seal_number'));
        $this->View->addContent('JdlHdFls2', $content);

    }

    /**
     * Function to set hidden for job delivery detail fields.
     *
     * @return void
     */
    private function setHiddenDetailsField(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jdld_id', $this->getIntParameter('jdld_id'));
        $content .= $this->Field->getHidden('jdld_soc_id', $this->getIntParameter('jdld_soc_id'));
        $this->View->addContent('JdlHdFls3', $content);

    }

    /**
     * Function to set parameter for job delivery detail.
     * @param array $data To store job detail delivery data
     * @return void
     */
    private function setJobDeliveryParameter(array $data): void
    {
        if ($this->isValidParameter('jdl_ct_id') === false) {
            $this->setParameter('jdl_ct_id', $data['jdld_ct_id']);
        }
        if ($this->isValidParameter('jdl_container_number') === false) {
            $this->setParameter('jdl_container_number', $data['jdld_container_number']);
        }
        if ($this->isValidParameter('jdl_container_type') === false) {
            $this->setParameter('jdl_container_type', $data['jdld_container_type']);
        }
        if ($this->isValidParameter('jdl_seal_number') === false) {
            $this->setParameter('jdl_seal_number', $data['jdld_seal_number']);
        }
    }

    /**
     * Function to load job delivery for update depo information.
     * @return array
     */
    private function loadJobDeliveryForUpdateDepo(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_so_id', $this->getSoId());
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $this->getDetailReferenceValue(), '<>');
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jdl.jdl_id
                    FROM job_delivery as jdl
                    INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id ' . $strWhere;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get the Load unload portlet.
     *
     * @param string $type To store the type location, is it O or D.
     *
     *
     * @return Portlet
     */
    private function getSoLoadUnloadPortlet(string $type): Portlet
    {
        $table = new Table('JdldSoLudTbl' . $type);
        $table->setHeaderRow([
            'sdl_relation' => Trans::getWord('relation'),
            'sdl_address' => Trans::getWord('address'),
            'sdl_pic' => Trans::getWord('pic'),
            'sdl_reference' => Trans::getWord('reference')
        ]);

        $data = SalesOrderDeliveryDao::getBySoIdAndType($this->getSoId(), $type);
        $table->addRows($data);
        $title = Trans::getTruckingWord('loadingAddress');
        if ($type === 'D') {
            $title = Trans::getTruckingWord('unloadingAddress');
        }
        # Create a portlet box.
        $portlet = new Portlet('JdldSoLudPtl' . $type, $title);
        $portlet->addTable($table);

        return $portlet;
    }


}
