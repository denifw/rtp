<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Job\Delivery;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\CardImage;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDetailDao;
use App\Model\Dao\Job\Delivery\LoadUnloadDeliveryDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Viewer\Job\BaseJobOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of view of job delivery page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
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
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doStartPickContainer') {
            $date = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00';

            # Update start Job
            $this->doStartJobOrder($date);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('pickup');
        } elseif ($this->getFormAction() === 'doArriveDepoPickUp') {
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_dp_ata' => $this->getStringParameter('dp_ar_date') . ' ' . $this->getStringParameter('dp_ar_time') . ':00',
            ]);
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('dp_jac_id'),
                'jae_description' => Trans::getTruckingWord('arriveAtDepo', '', ['depo' => $this->getStringParameter('dp_name')]),
                'jae_date' => $this->getStringParameter('dp_ar_date'),
                'jae_time' => $this->getStringParameter('dp_ar_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('arrivedepo');
        } elseif ($this->getFormAction() === 'doStartLoadContainer') {
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_dp_start' => $this->getStringParameter('jdl_ac_date') . ' ' . $this->getStringParameter('jdl_ac_time') . ':00',
            ]);
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('jdl_jac_id'),
                'jae_description' => Trans::getTruckingWord('startLiftOnContainer'),
                'jae_date' => $this->getStringParameter('jdl_ac_date'),
                'jae_time' => $this->getStringParameter('jdl_ac_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('loadcontainer');
        } elseif ($this->getFormAction() === 'doEndLoadContainer') {
            # Update job delivery
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_container_number' => $this->getStringParameter('jdl_container_number'),
                'jdl_seal_number' => $this->getStringParameter('jdl_seal_number'),
                'jdl_dp_end' => $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00',
            ]);
            # Update Job Delivery Container.
            if ($this->isValidParameter('jdld_soc_id') === true) {
                # Update sales order container
                $socDao = new SalesOrderContainerDao();
                $socDao->doUpdateTransaction($this->getIntParameter('jdld_soc_id'), [
                    'soc_container_number' => $this->getStringParameter('jdl_container_number'),
                    'soc_seal_number' => $this->getStringParameter('jdl_seal_number')
                ]);
                # update all job delivery by sales order container id.
                $listJdl = $this->loadJdlBySocForUpdateContainer();
                foreach ($listJdl as $row) {
                    $jdlDao->doUpdateTransaction($row['jdl_id'], [
                        'jdl_container_number' => $this->getStringParameter('jdl_container_number'),
                        'jdl_seal_number' => $this->getStringParameter('jdl_seal_number'),
                    ]);
                }
            }
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completeloadcontainer');
        } else if ($this->getFormAction() === 'doActionStartDelivery') {
            if ($this->isRoadJob() === true && $this->isContainerJob() === true) {
                $jdlDao = new JobDeliveryDao();
                $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                    'jdl_dp_atd' => $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00',
                ]);
            }
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('delivery');
        } elseif ($this->getFormAction() === 'doArriveLud') {
            $wheres = [];
            $wheres[] = '(lud.lud_deleted_on IS NULL)';
            $wheres[] = '(lud.lud_jdl_id = ' . $this->getIntParameter('jdl_id') . ')';
            $wheres[] = '(lud.lud_rel_id = ' . $this->getIntParameter('ar_rel_id') . ')';
            $wheres[] = '(lud.lud_of_id = ' . $this->getIntParameter('ar_of_id') . ')';
            $wheres[] = "(lud.lud_type = '" . $this->getStringParameter('ar_type') . "')";
            $ludData = LoadUnloadDeliveryDao::loadData($wheres);
            $ludDao = new LoadUnloadDeliveryDao();
            $office = '';
            foreach ($ludData as $row) {
                $office = $row['lud_office'];
                $ludDao->doUpdateTransaction($row['lud_id'], [
                    'lud_ata_on' => $this->getStringParameter('ar_date') . ' ' . $this->getStringParameter('ar_time') . ':00',
                ]);
            }
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('ar_jac_id'),
                'jae_description' => Trans::getTruckingWord('arriveAt') . ' ' . $office,
                'jae_date' => $this->getStringParameter('ar_date'),
                'jae_time' => $this->getStringParameter('ar_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('arrivelud');
        } elseif ($this->getFormAction() === 'doStartLoadUnload') {
            $wheres = [];
            $wheres[] = '(lud.lud_deleted_on IS NULL)';
            $wheres[] = '(lud.lud_ata_on IS NOT NULL)';
            $wheres[] = '(lud.lud_start_on IS NULL)';
            $wheres[] = '(lud.lud_jdl_id = ' . $this->getIntParameter('jdl_id') . ')';
            $wheres[] = "(lud.lud_type = '" . $this->getStringParameter('lud_type') . "')";
            $ludData = LoadUnloadDeliveryDao::loadData($wheres);
            $ludDao = new LoadUnloadDeliveryDao();
            $office = '';
            foreach ($ludData as $row) {
                $office = $row['lud_office'];
                $ludDao->doUpdateTransaction($row['lud_id'], [
                    'lud_start_on' => $this->getStringParameter('lud_date') . ' ' . $this->getStringParameter('lud_time') . ':00',
                ]);
            }
            $description = Trans::getTruckingWord('startLoadingAt', '', ['address' => $office]);
            if ($this->getStringParameter('lud_type', '') === 'D') {
                $description = Trans::getTruckingWord('startUnloadAt', '', ['address' => $office]);
            }
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('lud_jac_id'),
                'jae_description' => $description,
                'jae_date' => $this->getStringParameter('lud_date'),
                'jae_time' => $this->getStringParameter('lud_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('loadunload');
        } elseif ($this->getFormAction() === 'doUpdateLudQuantity') {
            $ludDao = new LoadUnloadDeliveryDao();
            $ludDao->doUpdateTransaction($this->getIntParameter('lud_id'), [
                'lud_pic_id' => $this->getIntParameter('lud_pic_id'),
                'lud_qty_good' => $this->getFloatParameter('lud_qty_good'),
                'lud_qty_damage' => $this->getFloatParameter('lud_qty_damage', 0),
            ]);
        } elseif ($this->getFormAction() === 'doEndLoadUnload') {
            $wheres = [];
            $wheres[] = '(lud.lud_deleted_on IS NULL)';
            $wheres[] = '(lud.lud_ata_on IS NOT NULL)';
            $wheres[] = '(lud.lud_start_on IS NOT NULL)';
            $wheres[] = '(lud.lud_end_on IS NULL)';
            $wheres[] = '(lud.lud_jdl_id = ' . $this->getIntParameter('jdl_id') . ')';
            $wheres[] = "(lud.lud_type = '" . $this->getStringParameter('lud_type') . "')";
            $ludData = LoadUnloadDeliveryDao::loadData($wheres);
            $ludDao = new LoadUnloadDeliveryDao();
            $office = '';
            foreach ($ludData as $row) {
                $office = $row['lud_office'];
                $ludDao->doUpdateTransaction($row['lud_id'], [
                    'lud_end_on' => $this->getStringParameter('lud_date') . ' ' . $this->getStringParameter('lud_time') . ':00',
                ]);
            }
            $description = Trans::getTruckingWord('loadingCompleteAt', '', ['address' => $office]);
            if ($this->getStringParameter('lud_type', '') === 'D') {
                $description = Trans::getTruckingWord('unloadCompleteAt', '', ['address' => $office]);
            }
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('lud_jac_id'),
                'jae_description' => $description,
                'jae_date' => $this->getStringParameter('lud_date'),
                'jae_time' => $this->getStringParameter('lud_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('completeloadunload');
        } elseif ($this->getFormAction() === 'doDepartLoadUnload') {
            $wheres = [];
            $wheres[] = '(lud.lud_deleted_on IS NULL)';
            $wheres[] = '(lud.lud_ata_on IS NOT NULL)';
            $wheres[] = '(lud.lud_start_on IS NOT NULL)';
            $wheres[] = '(lud.lud_end_on IS NOT NULL)';
            $wheres[] = '(lud.lud_atd_on IS NULL)';
            $wheres[] = '(lud.lud_jdl_id = ' . $this->getIntParameter('jdl_id') . ')';
            $wheres[] = "(lud.lud_type = '" . $this->getStringParameter('lud_type') . "')";
            $ludData = LoadUnloadDeliveryDao::loadData($wheres);
            $ludDao = new LoadUnloadDeliveryDao();
            $office = '';
            foreach ($ludData as $row) {
                $office = $row['lud_office'];
                $ludDao->doUpdateTransaction($row['lud_id'], [
                    'lud_atd_on' => $this->getStringParameter('lud_date') . ' ' . $this->getStringParameter('lud_time') . ':00',
                ]);
            }
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('lud_jac_id'),
                'jae_description' => Trans::getTruckingWord('departureFrom', '', ['address' => $office]),
                'jae_date' => $this->getStringParameter('lud_date'),
                'jae_time' => $this->getStringParameter('lud_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
        } elseif ($this->getFormAction() === 'doActionEndDelivery') {
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completedelivery');
        } elseif ($this->getFormAction() === 'doStartReturnContainer') {
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('returncontainer');
        } elseif ($this->getFormAction() === 'doArriveDepoReturn') {
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_dr_ata' => $this->getStringParameter('dp_ar_date') . ' ' . $this->getStringParameter('dp_ar_time') . ':00',
            ]);
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('dp_jac_id'),
                'jae_description' => Trans::getTruckingWord('arriveAtDepo', '', ['depo' => $this->getStringParameter('dp_name')]),
                'jae_date' => $this->getStringParameter('dp_ar_date'),
                'jae_time' => $this->getStringParameter('dp_ar_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('arrivedeporeturn');
        } elseif ($this->getFormAction() === 'doStartUnloadContainer') {
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_dr_start' => $this->getStringParameter('jdl_ac_date') . ' ' . $this->getStringParameter('jdl_ac_time') . ':00',
            ]);
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('jdl_jac_id'),
                'jae_description' => Trans::getTruckingWord('startLiftOffContainer'),
                'jae_date' => $this->getStringParameter('jdl_ac_date'),
                'jae_time' => $this->getStringParameter('jdl_ac_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ]);
            # Do notification
            $this->doGenerateNotificationReceiver('unloadcontainer');
        } elseif ($this->getFormAction() === 'doEndUnloadContainer') {
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                'jdl_dr_end' => $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00',
            ]);
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completeunloadcontainer');
        } elseif ($this->getFormAction() === 'doActionStartPool') {
            if ($this->isRoadJob() === true && $this->isContainerJob() === true) {
                $jdlDao = new JobDeliveryDao();
                $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                    'jdl_dr_atd' => $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00',
                ]);

            }
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('pool');
        } elseif ($this->getFormAction() === 'doActionEndPool') {
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('endpool');
        } else if ($this->getFormAction() === 'doActionStartPickUp') {
            if ($this->isRoadJob() === true && $this->isContainerJob() === true) {
                $jdlDao = new JobDeliveryDao();
                $jdlDao->doUpdateTransaction($this->getIntParameter('jdl_id'), [
                    'jdl_dp_atd' => $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00',
                ]);
            }
            if ($this->isRoadJob() === false || $this->isContainerJob() === false) {
                $date = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00';

                # Update start Job
                $this->doStartJobOrder($date);
            }
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('pickup');
        } elseif ($this->getFormAction() === 'doActionEndPickUp') {
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('endpickup');
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
        $this->Tab->addPortlet('general', $this->getSalesOrderPortlet());
        $this->Tab->addPortlet('general', $this->getReferencePortlet());
        $this->Tab->addPortlet('general', $this->getVendorPortlet());
        $this->Tab->addPortlet('general', $this->getDetailPortlet());
        if ($this->isConsolidateJob() === false) {
            if ($this->isRoadJob() === true) {
                $jdldData = JobDeliveryDetailDao::getByJobDeliveryId($this->getIntParameter('jdl_id'));
                if (count($jdldData) === 1) {
                    $jdld = $jdldData[0];
                    $this->setParameters($jdld);
                }
                $this->setHiddenDetailsField();
                if ($this->isContainerJob() === true) {
                    $this->Tab->addPortlet('general', $this->getContainerPortlet());
                }
                if ($this->isJobPublished() === true) {
                    $modal = $this->getLoadUnloadModal();
                    $this->View->addModal($modal);
                    if ($this->getStringParameter('jo_srt_load', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                        $this->Tab->addPortlet('deliveryOrder', $this->getLoadUnloadPortlet('O', $modal->getModalId()));
                    }
                    if ($this->getStringParameter('jo_srt_unload', 'N') === 'Y' || ($this->isRoadJob() === true && $this->isContainerJob() === false)) {
                        $this->Tab->addPortlet('deliveryOrder', $this->getLoadUnloadPortlet('D', $modal->getModalId()));
                    }
                    if ($this->isLoadUnloadProcess() === true) {
                        $this->Tab->setActiveTab('deliveryOrder', true);
                    }
                }
            } else {
                $this->Tab->addPortlet('deliveryOrder', $this->getMultiDeliveryPortlet());
            }
        }
        if ($this->isValidParameter('jdl_eq_doc_id') === true) {
            # load data
            $wheres = [];
            $wheres[] = '(doc.doc_id = ' . $this->getIntParameter('jdl_eq_doc_id') . ')';
            $wheres[] = '(doc.doc_deleted_on IS NULL)';
            $data = DocumentDao::loadData($wheres);
            if (count($data) === 1) {
                $this->Tab->addPortlet('general', $this->getImageEquipmentFieldSet($data[0]));
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
        if ($this->getFormAction() === 'doStartPickContainer') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doArriveDepoPickUp') {
            $this->Validation->checkRequire('dp_name');
            $this->Validation->checkRequire('dp_jac_id');
            $this->Validation->checkRequire('dp_ar_date');
            $this->Validation->checkRequire('dp_ar_time');
            $this->Validation->checkDate('dp_ar_date');
            $this->Validation->checkTime('dp_ar_time');
        } elseif ($this->getFormAction() === 'doStartLoadContainer') {
            $this->Validation->checkRequire('jdl_jac_id');
            $this->Validation->checkRequire('jdl_ac_date');
            $this->Validation->checkRequire('jdl_ac_time');
            $this->Validation->checkDate('jdl_ac_date');
            $this->Validation->checkTime('jdl_ac_time');
        } elseif ($this->getFormAction() === 'doEndLoadContainer') {
            $this->Validation->checkRequire('jdl_container_number');
            $this->Validation->checkMaxLength('jdl_seal_number', 255);
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartDelivery') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doArriveLud') {
            $this->Validation->checkRequire('ar_relation');
            $this->Validation->checkRequire('ar_rel_id');
            $this->Validation->checkRequire('ar_type');
            $this->Validation->checkRequire('ar_of_id');
            $this->Validation->checkRequire('ar_jac_id');
            $this->Validation->checkRequire('ar_date');
            $this->Validation->checkRequire('ar_time');
            $this->Validation->checkDate('ar_date');
            $this->Validation->checkTime('ar_time');
        } elseif ($this->getFormAction() === 'doStartLoadUnload') {
            $this->Validation->checkRequire('lud_type');
            $this->Validation->checkRequire('lud_jac_id');
            $this->Validation->checkRequire('lud_date');
            $this->Validation->checkRequire('lud_time');
            $this->Validation->checkDate('lud_date');
            $this->Validation->checkTime('lud_time');
        } elseif ($this->getFormAction() === 'doUpdateLudQuantity') {
            $this->Validation->checkRequire('lud_id');
            $this->Validation->checkRequire('lud_qty_good');
            $this->Validation->checkFloat('lud_qty_good');
            if ($this->isValidParameter('lud_qty_damage') === true) {
                $this->Validation->checkFloat('lud_qty_damage');
            }
        } elseif ($this->getFormAction() === 'doEndLoadUnload') {
            $this->Validation->checkRequire('lud_type');
            $this->Validation->checkRequire('lud_jac_id');
            $this->Validation->checkRequire('lud_date');
            $this->Validation->checkRequire('lud_time');
            $this->Validation->checkDate('lud_date');
            $this->Validation->checkTime('lud_time');
        } elseif ($this->getFormAction() === 'doDepartLoadUnload') {
            $this->Validation->checkRequire('lud_type');
            $this->Validation->checkRequire('lud_jac_id');
            $this->Validation->checkRequire('lud_date');
            $this->Validation->checkRequire('lud_time');
            $this->Validation->checkDate('lud_date');
            $this->Validation->checkTime('lud_time');
        } elseif ($this->getFormAction() === 'doActionEndDelivery') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doStartReturnContainer') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doArriveDepoReturn') {
            $this->Validation->checkRequire('dp_name');
            $this->Validation->checkRequire('dp_jac_id');
            $this->Validation->checkRequire('dp_ar_date');
            $this->Validation->checkRequire('dp_ar_time');
            $this->Validation->checkDate('dp_ar_date');
            $this->Validation->checkTime('dp_ar_time');
        } elseif ($this->getFormAction() === 'doStartUnloadContainer') {
            $this->Validation->checkRequire('jdl_jac_id');
            $this->Validation->checkRequire('jdl_ac_date');
            $this->Validation->checkRequire('jdl_ac_time');
            $this->Validation->checkDate('jdl_ac_date');
            $this->Validation->checkTime('jdl_ac_time');
        } elseif ($this->getFormAction() === 'doEndUnloadContainer') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartPool') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionEndPool') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartPickUp') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionEndPickUp') {
            $this->loadActionValidationRole();
        }
        parent::loadValidationRole();
    }

    /**
     * Function to get the Sales Order Portlet.
     *
     * @return Portlet
     */
    private function getSalesOrderPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getTruckingWord('soNumber'),
                'value' => $this->getStringParameter('so_number'),
            ],
            [
                'label' => Trans::getTruckingWord('customer'),
                'value' => $this->getStringParameter('jdl_so_customer'),
            ],
            [
                'label' => Trans::getTruckingWord('picCustomer'),
                'value' => $this->getStringParameter('jdl_so_pic_customer'),
            ],
            [
                'label' => Trans::getTruckingWord('serviceTerm'),
                'value' => $this->getStringParameter('jo_service_term'),
            ],
            [
                'label' => Trans::getTruckingWord('container'),
                'value' => new LabelYesNo($this->getStringParameter('jo_srt_container')),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getTruckingWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }


    /**
     * Function to get the Reference Portlet.
     *
     * @return Portlet
     */
    private function getReferencePortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getTruckingWord('customerRef'),
                'value' => $this->getStringParameter('jo_customer_ref'),
            ],
            [
                'label' => Trans::getTruckingWord('blRef'),
                'value' => $this->getStringParameter('jo_bl_ref'),
            ],
            [
                'label' => Trans::getTruckingWord('ajuRef'),
                'value' => $this->getStringParameter('jo_aju_ref'),
            ],
            [
                'label' => Trans::getTruckingWord('sppbRef'),
                'value' => $this->getStringParameter('jo_sppb_ref'),
            ],
            [
                'label' => Trans::getTruckingWord('packingListRef'),
                'value' => $this->getStringParameter('jo_packing_ref'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGReferencePtl', Trans::getTruckingWord('reference'));
        $portlet->addText($content);
        $portlet->setGridDimension(4, 4, 4);

        return $portlet;
    }

    /**
     * Function to get the detail portlet.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $departureTime = '';
        if ($this->isValidParameter('jdl_departure_date') === true) {
            if ($this->isValidParameter('jdl_departure_time') === true) {
                $departureTime = DateTimeParser::format($this->getStringParameter('jdl_departure_date') . ' ' . $this->getStringParameter('jdl_departure_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $departureTime = DateTimeParser::format($this->getStringParameter('jdl_departure_date'), 'Y-m-d', 'd M Y');
            }
        }
        $arrivalTime = '';
        if ($this->isValidParameter('jdl_arrival_date') === true) {
            if ($this->isValidParameter('jdl_arrival_time') === true) {
                $arrivalTime = DateTimeParser::format($this->getStringParameter('jdl_arrival_date') . ' ' . $this->getStringParameter('jdl_arrival_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $arrivalTime = DateTimeParser::format($this->getStringParameter('jdl_arrival_date'), 'Y-m-d', 'd M Y');
            }
        }
        $data = [];
        if ($this->isRoadJob() === true && $this->isContainerJob() === true && $this->isValidParameter('jdld_id') === true) {
            $data[] = [
                'label' => Trans::getTruckingWord('orderDelivery'),
                'value' => $this->getStringParameter('jdld_sdl_number'),
            ];
        }
        $data[] = [
            'label' => Trans::getTruckingWord('transportModule'),
            'value' => $this->getStringParameter('jdl_transport_module'),
        ];
        $data[] = [
            'label' => Trans::getTruckingWord('transportType'),
            'value' => $this->getStringParameter('jdl_equipment_group'),
        ];
        $data[] = [
            'label' => Trans::getTruckingWord('transport'),
            'value' => $this->getStringParameter('jdl_equipment'),
        ];
        if ($this->isRoadJob() === true) {
            $data[] = [
                'label' => Trans::getTruckingWord('truckPlate'),
                'value' => $this->getStringParameter('jdl_equipment_plate'),
            ];
            $data[] = [
                'label' => Trans::getTruckingWord('mainDriver'),
                'value' => $this->getStringParameter('jdl_first_driver'),
            ];
            $data[] = [
                'label' => Trans::getTruckingWord('secondaryDriver'),
                'value' => $this->getStringParameter('jdl_second_driver'),
            ];
        } else {
            $data[] = [
                'label' => Trans::getTruckingWord('transportNumber'),
                'value' => $this->getStringParameter('jdl_transport_number'),
            ];
        }
        $data[] = [
            'label' => Trans::getTruckingWord('departureTime'),
            'value' => $departureTime,
        ];
        $data[] = [
            'label' => Trans::getTruckingWord('arrivalTime'),
            'value' => $arrivalTime,
        ];
        if ($this->getStringParameter('jo_srt_pol', 'N') === 'Y') {
            $label = Trans::getTruckingWord('portOfLoading');
            if ($this->getStringParameter('jo_srt_pod', 'N') === 'N') {
                $label = Trans::getTruckingWord('portName');
            }
            $data[] = [
                'label' => $label,
                'value' => $this->getStringParameter('jdl_pol'),
            ];
        }
        if ($this->getStringParameter('jo_srt_pod', 'N') === 'Y') {
            $label = Trans::getTruckingWord('portOfDischarge');
            if ($this->getStringParameter('jo_srt_pol', 'N') === 'N') {
                $label = Trans::getTruckingWord('portName');
            }
            $data[] = [
                'label' => $label,
                'value' => $this->getStringParameter('jdl_pod'),
            ];
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JdlDetailPtl', Trans::getTruckingWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the detail portlet.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
        $dpOwnerLabel = Trans::getTruckingWord('depoPickUpOwner');
        $dpNameLabel = Trans::getTruckingWord('depoPickUp');
        $dpTimeLabel = Trans::getTruckingWord('pickUpTime');
        $drOwnerLabel = Trans::getTruckingWord('depoReturnOwner');
        $drNameLabel = Trans::getTruckingWord('depoReturn');
        $drTimeLabel = Trans::getTruckingWord('returnTime');
        $srtRoute = $this->getStringParameter('jo_srt_route');
        if ($srtRoute === 'dtpc') {
            $drOwnerLabel = Trans::getTruckingWord('yardOwner');
            $drNameLabel = Trans::getTruckingWord('yardName');
            $drTimeLabel = Trans::getTruckingWord('deliveryTime');
        } elseif ($srtRoute === 'ptdc') {
            $dpOwnerLabel = Trans::getTruckingWord('yardOwner');
            $dpNameLabel = Trans::getTruckingWord('yardName');
            $dpTimeLabel = Trans::getTruckingWord('pickUpTime');
        }
        $dt = new DateTimeParser();
        $pickTime = '';
        if ($this->isValidParameter('jdl_dp_ata') === true) {
            $pickTime = $dt->formatDateTime($this->getStringParameter('jdl_dp_ata'), 'Y-m-d H:i:s', 'H:i - d M Y');
        }
        $returnTime = '';
        if ($this->isValidParameter('jdl_dr_ata') === true) {
            $returnTime = $dt->formatDateTime($this->getStringParameter('jdl_dr_ata'), 'Y-m-d H:i:s', 'H:i - d M Y');
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getTruckingWord('containerType'),
                'value' => $this->getStringParameter('jdl_container_type')
            ],
            [
                'label' => Trans::getTruckingWord('containerNumber'),
                'value' => $this->getStringParameter('jdl_container_number')
            ],
            [
                'label' => Trans::getTruckingWord('sealNumber'),
                'value' => $this->getStringParameter('jdl_seal_number'),
            ],
            [
                'label' => $dpOwnerLabel,
                'value' => $this->getStringParameter('jdl_dp_owner')
            ],
            [
                'label' => $dpNameLabel,
                'value' => $this->getStringParameter('jdl_dp_name')
            ],
            [
                'label' => $dpTimeLabel,
                'value' => $pickTime,
            ],
            [
                'label' => $drOwnerLabel,
                'value' => $this->getStringParameter('jdl_dr_owner')
            ],
            [
                'label' => $drNameLabel,
                'value' => $this->getStringParameter('jdl_dr_name')
            ],
            [
                'label' => $drTimeLabel,
                'value' => $returnTime,
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JdlJdldPtl', Trans::getTruckingWord('container'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the icon field set.
     *
     * @param array $doc To store the document data.
     * @return Portlet
     */
    private function getImageEquipmentFieldSet(array $doc): Portlet
    {
        $portlet = new Portlet('EqImagePtl', Trans::getWord('transportImage'));
        $portlet->setGridDimension(4);

        $docDao = new DocumentDao();
        $path = $docDao->getDocumentPath($doc);
        $ca = new CardImage('EqIm');
        $ca->setHeight(200);
        $ca->setGridDimension(12, 12, 12);
        $ca->setData([
            'title' => '&nbsp;',
            'subtitle' => $doc['doc_description'],
            'img_path' => $path,
            'buttons' => [],
        ]);
        $portlet->addText($ca->createView());

        return $portlet;
    }

    /**
     * Function to get the Load unload portlet.
     *
     * @param string $type To store the type location, is it O or D.
     * @param string $modalId To store the id of modal for update.
     *
     *
     * @return Portlet
     */
    private function getLoadUnloadPortlet(string $type, string $modalId): Portlet
    {
        $table = new Table('JdldLudTbl' . $type);
        $table->setHeaderRow([
            'lud_relation' => Trans::getTruckingWord('relation'),
            'lud_address' => Trans::getTruckingWord('address'),
            'lud_pic' => Trans::getTruckingWord('pic'),
            'lud_reference' => Trans::getTruckingWord('reference'),
            'lud_sog_name' => Trans::getTruckingWord('goods'),
            'lud_quantity' => Trans::getTruckingWord('quantity'),
            'lud_time' => Trans::getTruckingWord('time'),
        ]);
        if ($this->isLoadUnloadProcess() === true) {
            $table->addColumnAtTheEnd('lud_action', Trans::getWord('action'));
            $table->addColumnAttribute('lud_action', 'style', 'text-align: center;');
        }
        $data = LoadUnloadDeliveryDao::getByJobDeliveryIdAndType($this->getIntParameter('jdl_id'), $type);
        $rows = [];
        $formatter = new StringFormatter();
        $number = new NumberFormatter();
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $row['lud_address'] = $formatter->doFormatAddress($row, 'lud');
            if (empty($row['lud_sog_hs_code']) === false) {
                $row['lud_sog_name'] = $row['lud_sog_hs_code'] . ' - ' . $row['lud_sog_name'];
            }

            #
            $uom = '';
            if (empty($row['lud_uom_code']) === false) {
                $uom = ' ' . $row['lud_uom_code'];
            }
            $rowQuantity = [
                [
                    'label' => Trans::getTruckingWord('plan'),
                    'value' => $number->doFormatFloat($row['lud_quantity']) . $uom,
                ],
            ];
            if (empty($row['lud_qty_good']) === false) {
                $rowQuantity[] = [
                    'label' => Trans::getTruckingWord('good'),
                    'value' => $number->doFormatFloat($row['lud_qty_good']) . $uom,
                ];
            }
            if (empty($row['lud_qty_damage']) === false) {
                $rowQuantity[] = [
                    'label' => Trans::getTruckingWord('damage'),
                    'value' => $number->doFormatFloat($row['lud_qty_damage']) . $uom,
                ];
            }

            $row['lud_quantity'] = StringFormatter::generateKeyValueTableView($rowQuantity);

            # Time
            $rowTime = [];

            if (empty($row['lud_ata_on']) === false) {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('ata'),
                    'value' => $dt->formatDateTime($row['lud_ata_on']),
                ];
            } else {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('ata'),
                    'value' => '',
                ];
            }
            if (empty($row['lud_start_on']) === false) {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('start'),
                    'value' => $dt->formatDateTime($row['lud_start_on']),
                ];
            } else {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('start'),
                    'value' => '',
                ];
            }
            if (empty($row['lud_end_on']) === false) {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('end'),
                    'value' => $dt->formatDateTime($row['lud_end_on']),
                ];
            } else {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('end'),
                    'value' => '',
                ];
            }
            if (empty($row['lud_atd_on']) === false) {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('atd'),
                    'value' => $dt->formatDateTime($row['lud_atd_on']),
                ];
            } else {
                $rowTime[] = [
                    'label' => Trans::getTruckingWord('atd'),
                    'value' => '',
                ];
            }
            $row['lud_time'] = StringFormatter::generateKeyValueTableView($rowTime);
            # add update action
            if ($this->isLoadUnloadProcess() === true && empty($row['lud_start_on']) === false && empty($row['lud_end_on']) === true) {
                $btnUpdate = new ModalButton('btnLudUp' . $row['lud_id'], '', $modalId);
                $btnUpdate->setIcon(Icon::Pencil)->btnWarning()->viewIconOnly();
                $btnUpdate->setEnableCallBack('lud', 'getById');
                $btnUpdate->addParameter('lud_id', $row['lud_id']);
                $row['lud_action'] = $btnUpdate;
            }
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
     * Function to check is this load or unload process
     *
     * @return bool
     */
    private function isLoadUnloadProcess(): bool
    {
        return empty($this->CurrentAction) === false && ($this->CurrentAction['jac_action'] === 'Unload' || $this->CurrentAction['jac_action'] === 'Loading');
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
        $content .= $this->Field->getHidden('jdl_consolidate', $this->getStringParameter('jdl_consolidate'));
        $content .= $this->Field->getHidden('jdl_tm_code', $this->getStringParameter('jdl_tm_code'));
        $this->View->addContent('JdlHdFls1', $content);

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
     * Function to get Goods modal.
     *
     * @return Modal
     */
    private function getLoadUnloadModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdlLudMdl', Trans::getTruckingWord('quantityActual'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateLudQuantity');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateLudQuantity' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $relField = $this->Field->getText('lud_relation', $this->getParameterForModal('lud_relation', $showModal));
        $relField->setReadOnly();

        # Create office
        $ofField = $this->Field->getText('lud_address', $this->getParameterForModal('lud_address', $showModal));
        $ofField->setReadOnly();

        # Create pic
        $picField = $this->Field->getSingleSelect('contactPerson', 'lud_pic', $this->getParameterForModal('lud_pic', $showModal));
        $picField->setHiddenField('lud_pic_id', $this->getParameterForModal('lud_pic_id', $showModal));
        $picField->addParameterById('cp_of_id', 'lud_of_id', Trans::getWord('address'));
        $picField->setDetailReferenceCode('cp_id');

        # Create goods
        $goodsField = $this->Field->getText('lud_sog_name', $this->getParameterForModal('lud_sog_name', $showModal));
        $goodsField->setReadOnly();

        $qtyField = $this->Field->getText('lud_quantity_number', $this->getParameterForModal('lud_quantity_number', $showModal));
        $qtyField->setReadOnly();

        $uomField = $this->Field->getText('lud_uom_code', $this->getParameterForModal('lud_uom_code', $showModal));
        $uomField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getTruckingWord('relation'), $relField);
        $fieldSet->addField(Trans::getTruckingWord('address'), $ofField);
        $fieldSet->addField(Trans::getTruckingWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getTruckingWord('pic'), $picField);
        $fieldSet->addField(Trans::getTruckingWord('quantity'), $qtyField);
        $fieldSet->addField(Trans::getTruckingWord('uom'), $uomField);
        $fieldSet->addField(Trans::getTruckingWord('qtyGood'), $this->Field->getNumber('lud_qty_good', $this->getParameterForModal('lud_qty_good', $showModal)), true);
        $fieldSet->addField(Trans::getTruckingWord('qtyDamage'), $this->Field->getNumber('lud_qty_damage', $this->getParameterForModal('lud_qty_damage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('lud_of_id', $this->getParameterForModal('lud_of_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('lud_id', $this->getParameterForModal('lud_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
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
        # Create a portlet box.
        $portlet = new Portlet('JoMtJdldPtl', Trans::getTruckingWord('salesOrderDelivery'));
        $portlet->addTable($table);

        return $portlet;
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
     * Function to load job delivery detail data.
     *
     * @return array
     */
    private function loadJdlBySocForUpdateContainer(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jdld.jdld_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_id', $this->getDetailReferenceValue(), '<>');
        $wheres[] = SqlHelper::generateNumericCondition('jdld.jdld_soc_id', $this->getIntParameter('jdld_soc_id'));
        $wheres[] = "(srt.srt_route IN ('dtpc', 'ptdc'))";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = ' SELECT jdl.jdl_id
                        FROM job_delivery_detail as jdld
                            INNER JOIN job_delivery as jdl ON jdl.jdl_id = jdld.jdld_jdl_id
                            INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id
                            INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id ' . $strWheres;
        $query .= ' GROUP BY jdl.jdl_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);

    }
}
