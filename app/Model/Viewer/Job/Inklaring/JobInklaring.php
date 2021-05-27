<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Job\Inklaring;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Inklaring\JobInklaringReleaseDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Viewer\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobInklaring page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobInklaring extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jik', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doDrafting') {
            $date = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time') . ':00';

            # Update start Job
            $this->doStartJobOrder($date);

            # Update Job Inklaring
            $jikColVal = [
                'jik_drafting_by' => $this->User->getId(),
                'jik_drafting_on' => $date,
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);

            # Update Sales Order Goods Position
            $this->doUpdateSalesGoodsPosition();
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('drafting');
        } elseif ($this->getFormAction() === 'doCompleteDrafting') {
            $soColVal = [
                'so_aju_ref' => $this->getStringParameter('so_aju_ref'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
            $jikColVal = [
                'jik_approve_by' => $this->User->getId(),
                'jik_approve_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completedrafting');
        } elseif ($this->getFormAction() === 'doRegister') {
            $soCalVal = [
                'so_manifest_ref' => $this->getStringParameter('so_manifest_ref'),
                'so_manifest_date' => $this->getStringParameter('so_manifest_date'),
                'so_manifest_pos' => $this->getStringParameter('so_manifest_pos'),
                'so_manifest_sub_pos' => $this->getStringParameter('so_manifest_sub_pos'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soCalVal);

            # Update Inklaring
            $jikColVal = [
                'jik_register_by' => $this->User->getId(),
                'jik_register_on' => date('Y-m-d H:i:s'),
            ];

            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('register');
        } elseif ($this->getFormAction() === 'doCompleteRegister') {
            # update so
            $soColVal = [
                'so_cct_id' => $this->getIntParameter('so_cct_id'),
                'so_do_ref' => $this->getStringParameter('so_do_ref'),
                'so_do_expired' => $this->getStringParameter('so_do_expired'),
                'so_sppb_ref' => $this->getStringParameter('so_sppb_ref'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
            # update inklaring job
            $jikColVal = [
                'jik_register_number' => $this->getStringParameter('jik_register_number'),
                'jik_register_date' => $this->getStringParameter('jik_register_date'),
                'jik_approve_pabean_by' => $this->User->getId(),
                'jik_approve_pabean_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completeregister');
        } elseif ($this->getFormAction() === 'doPortRelease') {
            $jikColVal = [
                'jik_port_release_by' => $this->User->getId(),
                'jik_port_release_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('portrelease');
        } elseif ($this->getFormAction() === 'doCompletePortRelease') {
            $jiColVal = [
                'jik_port_complete_by' => $this->User->getId(),
                'jik_port_complete_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jiColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completeportrelease');
        } elseif ($this->getFormAction() === 'doReleaseGoods') {
            $jikColVal = [
                'jik_release_by' => $this->User->getId(),
                'jik_release_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('goodsrelease');
        } elseif ($this->getFormAction() === 'doUpdateReleaseContainer') {
            $getInBy = null;
            if ($this->isValidParameter('jikr_gate_in_date') === true) {
                $getInBy = $this->User->getId();
            }
            $jikrColVal = [
                'jikr_jik_id' => $this->getIntParameter('jik_id'),
                'jikr_soc_id' => $this->getIntParameter('jikr_soc_id'),
                'jikr_quantity' => 1,
                'jikr_transporter_id' => $this->getIntParameter('jikr_transporter_id'),
                'jikr_truck_number' => $this->getStringParameter('jikr_truck_number'),
                'jikr_driver' => $this->getStringParameter('jikr_driver'),
                'jikr_driver_phone' => $this->getStringParameter('jikr_driver_phone'),
                'jikr_load_date' => $this->getStringParameter('jikr_load_date'),
                'jikr_load_time' => $this->getStringParameter('jikr_load_time'),
                'jikr_gate_in_date' => $this->getStringParameter('jikr_gate_in_date'),
                'jikr_gate_in_time' => $this->getStringParameter('jikr_gate_in_time'),
                'jikr_gate_in_by' => $getInBy,
            ];
            $jikDao = new JobInklaringReleaseDao();
            if ($this->isValidParameter('jikr_id') === false) {
                $jikDao->doInsertTransaction($jikrColVal);
            } else {
                $jikDao->doUpdateTransaction($this->getIntParameter('jikr_id'), $jikrColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteReleaseContainer') {
            $jikDao = new JobInklaringReleaseDao();
            $jikDao->doDeleteTransaction($this->getIntParameter('jikr_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateReleaseGoods') {
            $getInBy = null;
            if ($this->isValidParameter('jikr_gate_in_date') === true) {
                $getInBy = $this->User->getId();
            }
            $jikrColVal = [
                'jikr_jik_id' => $this->getIntParameter('jik_id'),
                'jikr_sog_id' => $this->getIntParameter('jikr_sog_id'),
                'jikr_quantity' => $this->getFloatParameter('jikr_quantity'),
                'jikr_transporter_id' => $this->getIntParameter('jikr_transporter_id'),
                'jikr_truck_number' => $this->getStringParameter('jikr_truck_number'),
                'jikr_driver' => $this->getStringParameter('jikr_driver'),
                'jikr_driver_phone' => $this->getStringParameter('jikr_driver_phone'),
                'jikr_load_date' => $this->getStringParameter('jikr_load_date'),
                'jikr_load_time' => $this->getStringParameter('jikr_load_time'),
                'jikr_gate_in_date' => $this->getStringParameter('jikr_gate_in_date'),
                'jikr_gate_in_time' => $this->getStringParameter('jikr_gate_in_time'),
                'jikr_gate_in_by' => $getInBy,
            ];
            $jikDao = new JobInklaringReleaseDao();
            if ($this->isValidParameter('jikr_id') === false) {
                $jikDao->doInsertTransaction($jikrColVal);
            } else {
                $jikDao->doUpdateTransaction($this->getIntParameter('jikr_id'), $jikrColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteReleaseGoods') {
            $jikDao = new JobInklaringReleaseDao();
            $jikDao->doDeleteTransaction($this->getIntParameter('jikr_id_del'));
        } elseif ($this->getFormAction() === 'doCompleteReleaseGoods') {
            $dateTime = date('Y-m-d H:i:s');
            $jikColVal = [
                'jik_complete_release_by' => $this->User->getId(),
                'jik_complete_release_on' => $dateTime,
            ];
            if ($this->getStringParameter('so_plb', 'N') === 'N') {
                $jikColVal['jik_gate_pass_by'] = $this->User->getId();
                $jikColVal['jik_gate_pass_on'] = $dateTime;
                # Update Job Action Gate In
                $jacData = JobActionDao::getByJoIdAndActionCode($this->getDetailReferenceValue(), 'GatePass');
                $jacColVal = [
                    'jac_start_by' => $this->User->getId(),
                    'jac_start_on' => $dateTime,
                    'jac_end_by' => $this->User->getId(),
                    'jac_end_on' => $dateTime
                ];
                $jacDao = new JobActionDao();
                $jacDao->doUpdateTransaction($jacData['jac_id'], $jacColVal);

                # Do Complete Sales goods Position
                $this->doCompleteSalesGoodsPosition();

            }
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('completegoodsrelease');
        } elseif ($this->getFormAction() === 'doGatePass') {
            # update so
            $soColVal = [
                'so_cct_id' => $this->getIntParameter('so_cct_id'),
                'so_sppd_ref' => $this->getStringParameter('so_sppd_ref'),
                'so_sppd_date' => $this->getStringParameter('so_sppd_date'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
            # Job inklaring
            $jikColVal = [
                'jik_gate_pass_by' => $this->User->getId(),
                'jik_gate_pass_on' => date('Y-m-d H:i:s'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);

            # Do Complete Sales goods Position
            $this->doCompleteSalesGoodsPosition();

            # Update job Action
            $this->doUpdateJobAction();
        } elseif ($this->getFormAction() === 'doTransportArrive') {
            # update so
            $soColVal = [
                'so_ata_date' => $this->getStringParameter('jac_date'),
                'so_ata_time' => $this->getStringParameter('jac_time'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
            # Update job Action
            $this->doUpdateJobAction();
            # Do notification
            $this->doGenerateNotificationReceiver('transportarrive');
        } elseif ($this->getFormAction() === 'doTransportDeparture') {
            # update so
            $soColVal = [
                'so_atd_date' => $this->getStringParameter('jac_date'),
                'so_atd_time' => $this->getStringParameter('jac_time'),
            ];
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), $soColVal);
            # Update job Action
            $this->doUpdateJobAction();
            # Do notification
            $this->doGenerateNotificationReceiver('transportdeparture');
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
        $data = JobInklaringDao::getByReferenceAndSystemSetting($this->getDetailReferenceValue(), $this->User->getSsId());
        if (empty($data) === false) {
            $soData = SalesOrderDao::getByReference($data['jik_so_id']);
            $data = array_merge($data, $soData);
        }
        return $data;
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->Tab->addPortlet('general', $this->getSalesOrderPortlet());
        $this->Tab->addPortlet('general', $this->getReferencePortlet());
        if ($this->isCustomerUser() === false) {
            $this->Tab->addPortlet('general', $this->getVendorPortlet());
        }
        $this->Tab->addPortlet('general', $this->getInklaringPortlet());
        $this->Tab->addPortlet('general', $this->getDetailPortlet());
        $this->Tab->addPortlet('general', $this->getRelationPortlet());
        if ($this->isValidParameter('jik_release_on') === true) {
            if ($this->getStringParameter('so_container', 'N') === 'Y') {
                $this->Tab->addPortlet('goods', $this->getReleaseContainerPortlet());
            } else {
                $this->Tab->addPortlet('goods', $this->getReleaseGoodsPortlet());
            }
        }
        if ($this->getStringParameter('so_container', 'N') === 'Y') {
            $this->Tab->addPortlet('goods', $this->getContainerPortlet());
        }
        $this->Tab->addPortlet('goods', $this->getGoodsPortlet());        # include default portlet
        if ($this->isValidParameter('jik_release_on') === true && ($this->isValidParameter('jik_complete_release_on') === false || $this->isValidParameter('jik_gate_pass_on') === false)) {
            $this->Tab->setActiveTab('goods');
        }
        $this->setJikHiddenData();
        $this->includeAllDefaultPortlet();

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doDrafting') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doCompleteDrafting') {
            $this->Validation->checkRequire('so_aju_ref', 3, 255);
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doRegister') {
            $this->Validation->checkRequire('jo_srt_route');
            $this->Validation->checkRequire('so_manifest_ref', 2, 255);
            $this->Validation->checkRequire('so_manifest_date');
            $this->Validation->checkDate('so_manifest_date');
            $this->Validation->checkRequire('so_manifest_pos', 2, 255);
            $this->Validation->checkMaxLength('so_manifest_sub_pos', 255);
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doCompleteRegister') {
            $this->Validation->checkRequire('so_sppb_ref', 3, 255);
            $this->Validation->checkRequire('jik_register_number', 3, 255);
            $this->Validation->checkRequire('jik_register_date');
            $this->Validation->checkDate('jik_register_date');
            $this->Validation->checkRequire('so_do_ref', 3, 255);
            if ($this->isInklaringImport() === true) {
                $this->Validation->checkRequire('so_do_expired');
            }
            if ($this->isValidParameter('so_do_expired') === true) {
                $this->Validation->checkDate('so_do_expired');
            }
            if ($this->getStringParameter('so_plb', 'N') === 'N') {
                $this->Validation->checkRequire('so_cct_id');
            }
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doPortRelease') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doCompletePortRelease') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doReleaseGoods') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doUpdateReleaseContainer') {
            $this->Validation->checkRequire('jikr_soc_id');
            $this->Validation->checkRequire('jikr_transporter_id');
            $this->Validation->checkRequire('jikr_truck_number', 3, 255);
            $this->Validation->checkRequire('jikr_load_date');
            $this->Validation->checkRequire('jikr_load_time');
            $this->Validation->checkDate('jikr_load_date');
            $this->Validation->checkTime('jikr_load_time');
            if ($this->isValidParameter('jikr_gate_in_date') === true) {
                $this->Validation->checkDate('jikr_gate_in_date');
            }
            if ($this->isValidParameter('jikr_gate_in_time') === true) {
                $this->Validation->checkTime('jikr_gate_in_time');
            }
        } elseif ($this->getFormAction() === 'doDeleteReleaseContainer') {
            $this->Validation->checkRequire('jikr_id_del');
        } elseif ($this->getFormAction() === 'doUpdateReleaseGoods') {
            $this->Validation->checkRequire('jikr_sog_id');
            $this->Validation->checkRequire('jikr_quantity');
            $this->Validation->checkFloat('jikr_quantity');
            $this->Validation->checkRequire('jikr_transporter_id');
            $this->Validation->checkRequire('jikr_truck_number', 3, 255);
            $this->Validation->checkRequire('jikr_load_date');
            $this->Validation->checkRequire('jikr_load_time');
            $this->Validation->checkDate('jikr_load_date');
            $this->Validation->checkTime('jikr_load_time');
            if ($this->isValidParameter('jikr_gate_in_date') === true) {
                $this->Validation->checkDate('jikr_gate_in_date');
            }
            if ($this->isValidParameter('jikr_gate_in_time') === true) {
                $this->Validation->checkTime('jikr_gate_in_time');
            }
        } elseif ($this->getFormAction() === 'doDeleteReleaseGoods') {
            $this->Validation->checkRequire('jikr_id_del');
        } elseif ($this->getFormAction() === 'doCompleteReleaseGoods') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doGatePass') {
            $this->Validation->checkDate('so_sppd_date');
            $this->Validation->checkRequire('so_sppd_ref', 3, 255);
            if ($this->getStringParameter('so_plb', 'N') === 'Y') {
                $this->Validation->checkRequire('so_cct_id');
            }
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doTransportArrive') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doTransportDeparture') {
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
                'label' => Trans::getWord('soNumber'),
                'value' => $this->getStringParameter('so_number'),
            ],
            [
                'label' => Trans::getWord('serviceTerm'),
                'value' => $this->getStringParameter('jo_service_term'),
            ],
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('so_customer'),
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->getStringParameter('so_pic_customer'),
            ],
            [
                'label' => Trans::getWord('orderDate'),
                'value' => DateTimeParser::format($this->getStringParameter('so_order_date'), 'Y-m-d', 'd M Y'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addText($content);
        if ($this->isCustomerUser() === true) {
            $portlet->setGridDimension(6, 6);
        } else {
            $portlet->setGridDimension(4, 4, 4);
        }

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
                'label' => Trans::getWord('customerRef'),
                'value' => $this->getStringParameter('so_customer_ref'),
            ],
            [
                'label' => Trans::getWord('blRef'),
                'value' => $this->getStringParameter('so_bl_ref'),
            ],
            [
                'label' => Trans::getWord('ajuRef'),
                'value' => $this->getStringParameter('so_aju_ref'),
            ],
            [
                'label' => Trans::getWord('sppbRef'),
                'value' => $this->getStringParameter('so_sppb_ref'),
            ],
            [
                'label' => Trans::getWord('packingListRef'),
                'value' => $this->getStringParameter('so_packing_ref'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGReferencePtl', Trans::getWord('reference'));
        $portlet->addText($content);
        if ($this->isCustomerUser() === true) {
            $portlet->setGridDimension(6, 6);
        } else {
            $portlet->setGridDimension(4, 4, 4);
        }

        return $portlet;
    }

    /**
     * Function to get the vendor Portlet.
     *
     * @return Portlet
     */
    protected function getVendorPortlet(): Portlet
    {
        $portlet = parent::getVendorPortlet();
        if ($this->isCustomerUser() === true) {
            $portlet->setGridDimension(6, 6);
        } else {
            $portlet->setGridDimension(4, 4, 4);
        }
        return $portlet;
    }

    /**
     * Function to get the Inklaring portlet.
     *
     * @return Portlet
     */
    private function getInklaringPortlet(): Portlet
    {
        $etd = '';
        if ($this->isValidParameter('so_departure_date') === true) {
            if ($this->isValidParameter('so_departure_time') === true) {
                $etd = DateTimeParser::format($this->getStringParameter('so_departure_date') . ' ' . $this->getStringParameter('so_departure_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etd = DateTimeParser::format($this->getStringParameter('so_departure_date'), 'Y-m-d', 'd M Y');
            }
        }
        $eta = '';
        if ($this->isValidParameter('so_arrival_date') === true) {
            if ($this->isValidParameter('so_arrival_time') === true) {
                $eta = DateTimeParser::format($this->getStringParameter('so_arrival_date') . ' ' . $this->getStringParameter('so_arrival_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $eta = DateTimeParser::format($this->getStringParameter('so_arrival_date'), 'Y-m-d', 'd M Y');
            }
        }
        $atd = '';
        if ($this->isValidParameter('so_atd_date') === true) {
            if ($this->isValidParameter('so_atd_time') === true) {
                $atd = DateTimeParser::format($this->getStringParameter('so_atd_date') . ' ' . $this->getStringParameter('so_atd_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $atd = DateTimeParser::format($this->getStringParameter('so_atd_date'), 'Y-m-d', 'd M Y');
            }
        }
        $ata = '';
        if ($this->isValidParameter('so_ata_date') === true) {
            if ($this->isValidParameter('so_ata_time') === true) {
                $ata = DateTimeParser::format($this->getStringParameter('so_ata_date') . ' ' . $this->getStringParameter('so_ata_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $ata = DateTimeParser::format($this->getStringParameter('so_ata_date'), 'Y-m-d', 'd M Y');
            }
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('documentType'),
                'value' => $this->getStringParameter('so_document_type'),
            ],
            [
                'label' => Trans::getWord('lineStatus'),
                'value' => $this->getStringParameter('so_custom_type'),
            ],
            [
                'label' => Trans::getWord('transportModule'),
                'value' => $this->getStringParameter('so_transport_module'),
            ],
            [
                'label' => Trans::getWord('portOfLoading'),
                'value' => $this->getStringParameter('so_pol') . ' - ' . $this->getStringParameter('so_pol_country'),
            ],
            [
                'label' => Trans::getWord('etdTime'),
                'value' => $etd,
            ],
            [
                'label' => Trans::getWord('atdTime'),
                'value' => $atd,
            ],
            [
                'label' => Trans::getWord('portOfDischarge'),
                'value' => $this->getStringParameter('so_pod') . ' - ' . $this->getStringParameter('so_pod_country'),
            ],
            [
                'label' => Trans::getWord('etaTime'),
                'value' => $eta,
            ],
            [
                'label' => Trans::getWord('ataTime'),
                'value' => $ata,
            ],
            [
                'label' => Trans::getWord('transportName'),
                'value' => $this->getStringParameter('so_transport_name'),
            ],
            [
                'label' => Trans::getWord('transportNumber'),
                'value' => $this->getStringParameter('so_transport_number'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JikPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the detail portlet.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $closingTime = '';
        if ($this->isValidParameter('jik_closing_date') === true) {
            if ($this->isValidParameter('jik_closing_time') === true) {
                $closingTime = DateTimeParser::format($this->getStringParameter('jik_closing_date') . ' ' . $this->getStringParameter('jik_closing_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $closingTime = DateTimeParser::format($this->getStringParameter('jik_closing_date'), 'Y-m-d', 'd M Y');
            }
        }
        $dt = new DateTimeParser();
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('closingTime'),
                'value' => $closingTime
            ],
            [
                'label' => Trans::getWord('plb'),
                'value' => new LabelYesNo($this->getStringParameter('so_plb'))
            ],
            [
                'label' => Trans::getWord('plbName'),
                'value' => $this->getStringParameter('so_warehouse_name')
            ],
            [
                'label' => Trans::getWord('doRef'),
                'value' => $this->getStringParameter('so_do_ref'),
            ],
            [
                'label' => Trans::getWord('doExpired'),
                'value' => $dt->formatDate($this->getStringParameter('so_do_expired'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('manifestRef'),
                'value' => $this->getStringParameter('so_manifest_ref'),
            ],
            [
                'label' => Trans::getWord('manifestDate'),
                'value' => $dt->formatDate($this->getStringParameter('so_manifest_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('manifestPos'),
                'value' => $this->getStringParameter('so_manifest_pos'),
            ],
            [
                'label' => Trans::getWord('manifestSubPos'),
                'value' => $this->getStringParameter('so_manifest_sub_pos'),
            ],
            [
                'label' => Trans::getWord('sppdRef'),
                'value' => $this->getStringParameter('so_sppd_ref'),
            ],
            [
                'label' => Trans::getWord('sppdDate'),
                'value' => $dt->formatDate($this->getStringParameter('so_sppd_date')),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JikDetailPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the Relation Portlet.
     *
     * @return Portlet
     */
    private function getRelationPortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('consignee'),
                'value' => $this->getStringParameter('so_consignee'),
            ],
            [
                'label' => Trans::getWord('consigneeAddress'),
                'value' => $this->getStringParameter('so_consignee_address'),
            ],
            [
                'label' => Trans::getWord('picConsignee'),
                'value' => $this->getStringParameter('so_pic_consignee'),
            ],
            [
                'label' => Trans::getWord('shipper'),
                'value' => $this->getStringParameter('so_shipper'),
            ],
            [
                'label' => Trans::getWord('shipperAddress'),
                'value' => $this->getStringParameter('so_shipper_address'),
            ],
            [
                'label' => Trans::getWord('picShipper'),
                'value' => $this->getStringParameter('so_pic_shipper'),
            ],
            [
                'label' => Trans::getWord('notifyParty'),
                'value' => $this->getStringParameter('so_notify'),
            ],
            [
                'label' => Trans::getWord('notifyPartyAddress'),
                'value' => $this->getStringParameter('so_notify_address'),
            ],
            [
                'label' => Trans::getWord('picNotifyParty'),
                'value' => $this->getStringParameter('so_pic_notify'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JikRelationPtl', Trans::getWord('relation'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the Container Portlet.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
        $table = new Table('SoContainerTbl');
        $table->setHeaderRow([
            'soc_number' => Trans::getWord('containerId'),
            'soc_container_type' => Trans::getWord('containerType'),
            'soc_container_number' => Trans::getWord('containerNumber'),
            'soc_seal_number' => Trans::getWord('sealNumber'),
        ]);
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getSoId());
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $data = SalesOrderContainerDao::loadData($wheres);
        if ($this->getStringParameter('so_plb', 'N') === 'Y' && $this->isValidParameter('jik_approve_pabean_on') === true) {
            $table->addColumnAfter('soc_seal_number', 'soc_print', Trans::getWord('do'));
            $table->addColumnAttribute('soc_print', 'style', 'text-align: center;');
            $rows = [];
            foreach ($data as $row) {
                $pdfButton = new PdfButton('JikSocPrint' . $row['soc_id'], Trans::getWord('printPdf'), 'deliveryorderinklaring');
                $pdfButton->setIcon(Icon::Download)->btnPrimary()->btnMedium();
                $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
                $pdfButton->addParameter('soc_id', $row['soc_id']);
                $row['soc_print'] = $pdfButton;

                $rows[] = $row;
            }
            $table->addRows($rows);
        } else {
            $table->addRows($data);
        }

        # Create a portlet box.
        $portlet = new Portlet('JikContainerPtl', Trans::getWord('containers'));
        $portlet->addTable($table);

        return $portlet;

    }


    /**
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    private function getGoodsPortlet(): Portlet
    {
        $table = new Table('JikSogTbl');
        $table->setHeaderRow([
            'sog_name' => Trans::getWord('description'),
            'sog_packing_ref' => Trans::getWord('packingRef'),
            'sog_quantity' => Trans::getWord('quantity'),
            'sog_uom' => Trans::getWord('uom'),
            'sog_gross_weight' => Trans::getWord('grossWeight') . ' (KG)',
            'sog_net_weight' => Trans::getWord('netWeight') . ' (KG)',
            'sog_dimension' => Trans::getWord('dimensionPerUnit') . ' (M)',
            'sog_cbm' => Trans::getWord('cbm'),
            'sog_notes' => Trans::getWord('notes'),
        ]);
        $table->setColumnType('sog_quantity', 'float');
        $table->setColumnType('sog_gross_weight', 'float');
        $table->setColumnType('sog_net_weight', 'float');
        $table->setColumnType('sog_cbm', 'float');
        $table->setFooterType('sog_gross_weight', 'SUM');
        $table->setFooterType('sog_net_weight', 'SUM');
        $table->setFooterType('sog_cbm', 'SUM');
        if ($this->getStringParameter('so_container', 'N') === 'Y') {
            $table->addColumnAtTheBeginning('sog_container_number', Trans::getWord('containerNumber'));
            $table->addColumnAtTheBeginning('sog_container_id', Trans::getWord('containerId'));
            $table->addColumnAtTheBeginning('sog_container_type', Trans::getWord('containerType'));
        }


        $table->addRows($this->loadSalesOrderGoodsData());
        # add new button

        $portlet = new Portlet('SoSogPtl', Trans::getWord('goods'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do load sales order goods data.
     *
     * @return array
     */
    private function loadSalesOrderGoodsData(): array
    {
        $results = [];
        $data = SalesOrderGoodsDao::getBySoId($this->getSoId());
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $dimensions = [];
            if (empty($row['sog_length']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('length'),
                    'value' => $number->doFormatFloat($row['sog_length']),
                ];
            }
            if (empty($row['sog_width']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('width'),
                    'value' => $number->doFormatFloat($row['sog_width']),
                ];
            }
            if (empty($row['sog_height']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('height'),
                    'value' => $number->doFormatFloat($row['sog_height']),
                ];
            }
            if (empty($row['sog_hs_code']) === false) {
                $row['sog_name'] = $row['sog_hs_code'] . ' - ' . $row['sog_name'];
            }
            $row['sog_dimension'] = StringFormatter::generateKeyValueTableView($dimensions);
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getReleaseContainerPortlet(): Portlet
    {
        $table = new Table('JoJikrTbl');
        $table->setHeaderRow([
            'jikr_container_type' => Trans::getWord('containerType'),
            'jikr_container_number' => Trans::getWord('containerNumber'),
            'jikr_seal_number' => Trans::getWord('sealNumber'),
            'jikr_transporter' => Trans::getWord('transporter'),
            'jikr_truck_number' => Trans::getWord('truckPlate'),
            'jikr_driver' => Trans::getWord('driver'),
            'jikr_release_on' => Trans::getWord('releasedOn'),
        ]);
        $showGateIn = false;
        if ($this->getStringParameter('so_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $showGateIn = true;
            $table->addColumnAfter('jikr_release_on', 'jikr_gate_in_on', Trans::getWord('gatePass'));
        }
        $data = JobInklaringReleaseDao::getByJobInklring($this->getIntParameter('jik_id'));
        $i = 0;
        $rows = [];
        $dt = new DateTimeParser();
        foreach ($data as $key => $row) {
            $row['jikr_release_on'] = $dt->formatDateTime($row['jikr_load_date'] . ' ' . $row['jikr_load_time']);
            if ($showGateIn === true) {
                if (empty($row['jikr_gate_in_date']) === false) {
                    $row['jikr_gate_in_on'] = $dt->formatDateTime($row['jikr_gate_in_date'] . ' ' . $row['jikr_gate_in_time']);
                } else {
                    $table->addCellAttribute('jikr_gate_in_on', $i, 'style', 'background-color:red; color:white');
                }
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        if ($this->isInklaringImport() === true) {
            $portletTitle = Trans::getWord('releasedContainer');
            $portletButton = Trans::getWord('release');
        } else {
            $portletTitle = Trans::getWord('shipmentContainer');
            $portletButton = Trans::getWord('shipment');
        }
        # Create a portlet box.
        $portlet = new Portlet('JoJikrPtl', $portletTitle);

        # Create modal
        $modal = $this->getReleaseContainerModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getReleaseContainerDeleteModal();
        $this->View->addModal($modalDelete);


        # Add portlet button
        if ($this->isAllowUpdateAction() === true) {
            $showUpdateButton = $showGateIn;
            if ($this->isValidParameter('jik_complete_release_on') === false) {
                $showUpdateButton = true;
                # Add add button
                $btnAddCntMdl = new ModalButton('btnJJikrAddMdl', $portletButton, $modal->getModalId());
                $btnAddCntMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
                $portlet->addButton($btnAddCntMdl);
                # Add delete button for table
                $table->setDeleteActionByModal($modalDelete, 'jikr', 'getByReferenceForDelete', ['jikr_id']);
            }
            if ($showUpdateButton === true) {
                $table->setUpdateActionByModal($modal, 'jikr', 'getByReference', ['jikr_id']);
            }
        }
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to get release container modal.
     *
     * @return Modal
     */
    protected function getReleaseContainerModal(): Modal
    {
        # Create Fields.
        if ($this->isInklaringImport() === true) {
            $title = Trans::getWord('releaseContainer');
        } else {
            $title = Trans::getWord('shipmentContainer');
        }
        $modal = new Modal('JikrAddMdl', $title);
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateReleaseContainer');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateReleaseContainer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        } else {
            if ($this->isValidParameter('jikr_load_date') === false) {
                $this->setParameter('jikr_load_date', date('Y-m-d'));
            }
            if ($this->isValidParameter('jikr_load_time') === false) {
                $this->setParameter('jikr_load_time', date('H:i'));
            }
            if ($this->isValidParameter('jikr_gate_in_date') === false) {
                $this->setParameter('jikr_gate_in_date', date('Y-m-d'));
            }
            if ($this->isValidParameter('jikr_gate_in_time') === false) {
                $this->setParameter('jikr_gate_in_time', date('H:i'));
            }

        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Sales Order Container Field
        $socField = $this->Field->getSingleSelect('soc', 'jikr_container_number', $this->getParameterForModal('jikr_container_number', $showModal));
        $socField->setHiddenField('jikr_soc_id', $this->getParameterForModal('jikr_soc_id', $showModal));
        $socField->addParameter('soc_so_id', $this->getSoId());
        $socField->addParameter('jik_id', $this->getIntParameter('jik_id'));
        $socField->setEnableNewButton(false);
        $socField->setEnableDetailButton(false);
        $socField->setAutoCompleteFields([
            'jikr_container_type' => 'soc_container_type',
            'jikr_seal_number' => 'soc_seal_number',
            'jikr_container_id' => 'soc_number',
        ]);
        # Container id
        $socIdField = $this->Field->getText('jikr_container_id', $this->getParameterForModal('jikr_container_id', $showModal));
        $socIdField->setReadOnly();
        # Container Type
        $typeField = $this->Field->getText('jikr_container_type', $this->getParameterForModal('jikr_container_type', $showModal));
        $typeField->setReadOnly();
        # Seal Number
        $sealField = $this->Field->getText('jikr_seal_number', $this->getParameterForModal('jikr_seal_number', $showModal));
        $sealField->setReadOnly();

        # Transporter
        $transporterField = $this->Field->getSingleSelect('relation', 'jikr_transporter', $this->getParameterForModal('jikr_transporter', $showModal));
        $transporterField->setHiddenField('jikr_transporter_id', $this->getParameterForModal('jikr_transporter_id'));
        $transporterField->addParameter('rel_ss_id', $this->User->getSsId());
        $transporterField->setDetailReferenceCode('rel_id');

        $truckPlateField = $this->Field->getText('jikr_truck_number', $this->getParameterForModal('jikr_truck_number', $showModal));
        $driverField = $this->Field->getText('jikr_driver', $this->getParameterForModal('jikr_driver', $showModal));
        $driverPhoneField = $this->Field->getText('jikr_driver_phone', $this->getParameterForModal('jikr_driver_phone', $showModal));
        $loadDate = $this->Field->getCalendar('jikr_load_date', $this->getParameterForModal('jikr_load_date', true));
        $loadTime = $this->Field->getTime('jikr_load_time', $this->getParameterForModal('jikr_load_time', true));
        if ($this->isValidParameter('jik_complete_release_on') === true) {
            $socField->setReadOnly();
            $transporterField->setReadOnly();
            $truckPlateField->setReadOnly();
            $driverField->setReadOnly();
            $driverPhoneField->setReadOnly();
            $loadDate->setReadOnly();
            $loadTime->setReadOnly();
        }
        if ($this->getStringParameter('so_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $fieldSet->addField(Trans::getWord('gateInDate'), $this->Field->getCalendar('jikr_gate_in_date', $this->getParameterForModal('jikr_gate_in_date', true)));
            $fieldSet->addField(Trans::getWord('gateInTime'), $this->Field->getTime('jikr_gate_in_time', $this->getParameterForModal('jikr_gate_in_time', true)));
        }
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('containerNumber'), $socField, true);
        $fieldSet->addField(Trans::getWord('transporter'), $transporterField, true);
        $fieldSet->addField(Trans::getWord('containerId'), $socIdField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckPlateField, true);
        $fieldSet->addField(Trans::getWord('containerType'), $typeField);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('sealNumber'), $sealField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $driverPhoneField);
        $fieldSet->addField(Trans::getWord('releaseDate'), $loadDate, true);
        $fieldSet->addField(Trans::getWord('releaseTime'), $loadTime, true);
        $fieldSet->addHiddenField($this->Field->getHidden('jikr_id', $this->getParameterForModal('jikr_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get release container modal.
     *
     * @return Modal
     */
    protected function getReleaseContainerDeleteModal(): Modal
    {
        # Create Fields.
        if ($this->isInklaringImport() === true) {
            $title = Trans::getWord('deleteReleasedContainer');
        } else {
            $title = Trans::getWord('deleteShipmentContainer');
        }
        $modal = new Modal('JikrDelMdl', $title);
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteReleaseContainer');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteReleaseContainer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Job Container Field
        $jocField = $this->Field->getText('jikr_container_number_del', $this->getParameterForModal('jikr_container_number_del', $showModal));
        $sealField = $this->Field->getText('jikr_seal_number_del', $this->getParameterForModal('jikr_seal_number_del', $showModal));
        $typeField = $this->Field->getText('jikr_container_type_del', $this->getParameterForModal('jikr_container_type_del', $showModal));
        $socIdField = $this->Field->getText('jikr_container_id_del', $this->getParameterForModal('jikr_container_id_del', $showModal));
        $transporterField = $this->Field->getText('jikr_transporter_del', $this->getParameterForModal('jikr_transporter_del', $showModal));
        $truckPlateField = $this->Field->getText('jikr_truck_number_del', $this->getParameterForModal('jikr_truck_number_del', $showModal));
        $driverField = $this->Field->getText('jikr_driver_del', $this->getParameterForModal('jikr_driver_del', $showModal));
        $driverPhoneField = $this->Field->getText('jikr_driver_phone_del', $this->getParameterForModal('jikr_driver_phone_del', $showModal));
        $loadDateField = $this->Field->getCalendar('jikr_load_date_del', $this->getParameterForModal('jikr_load_date_del', $showModal));
        $loadTimeField = $this->Field->getTime('jikr_load_time_del', $this->getParameterForModal('jikr_load_time_del', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('containerNumber'), $jocField);
        $fieldSet->addField(Trans::getWord('transporter'), $transporterField);
        $fieldSet->addField(Trans::getWord('containerId'), $socIdField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckPlateField);
        $fieldSet->addField(Trans::getWord('containerType'), $typeField);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('sealNumber'), $sealField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $driverPhoneField);
        $fieldSet->addField(Trans::getWord('loadDate'), $loadDateField);
        $fieldSet->addField(Trans::getWord('loadTime'), $loadTimeField);
        $fieldSet->addHiddenField($this->Field->getHidden('jikr_id_del', $this->getParameterForModal('jikr_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the release Goods Portlet.
     *
     * @return Portlet
     */
    private function getReleaseGoodsPortlet(): Portlet
    {
        $table = new Table('JoJikrTbl');
        $table->setHeaderRow([
            'jikr_hs_code' => Trans::getWord('hsCode'),
            'jikr_goods' => Trans::getWord('description'),
            'jikr_packing_ref' => Trans::getWord('packingRef'),
            'jikr_quantity' => Trans::getWord('quantity'),
            'jikr_uom_code' => Trans::getWord('uom'),
            'jikr_transporter' => Trans::getWord('transporter'),
            'jikr_truck_number' => Trans::getWord('truckPlate'),
            'jikr_driver' => Trans::getWord('driver'),
            'jikr_release_on' => Trans::getWord('releasedOn'),
        ]);
        $showGateIn = false;
        if ($this->getStringParameter('so_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $showGateIn = true;
            $table->addColumnAfter('jikr_release_on', 'jikr_gate_in_on', Trans::getWord('gatePass'));
        }
        $data = JobInklaringReleaseDao::getByJobInklring($this->getIntParameter('jik_id'));
        $i = 0;
        $rows = [];
        $dt = new DateTimeParser();
        foreach ($data as $key => $row) {
            $row['jikr_release_on'] = $dt->formatDateTime($row['jikr_load_date'] . ' ' . $row['jikr_load_time']);
            if ($showGateIn === true) {
                if (empty($row['jikr_gate_in_date']) === false) {
                    $row['jikr_gate_in_on'] = $dt->formatDateTime($row['jikr_gate_in_date'] . ' ' . $row['jikr_gate_in_time']);
                } else {
                    $table->addCellAttribute('jikr_gate_in_on', $i, 'style', 'background-color:red; color:white');
                }
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        # Create a portlet box.
        if ($this->isInklaringImport() === true) {
            $portletTitle = Trans::getWord('releasedGoods');
            $portletButton = Trans::getWord('release');
        } else {
            $portletTitle = Trans::getWord('shipmentGoods');
            $portletButton = Trans::getWord('shipment');
        }
        $portlet = new Portlet('JoJikrPtl', $portletTitle);

        # Create modal
        $modal = $this->getReleaseGoodsModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getReleaseGoodsDeleteModal();
        $this->View->addModal($modalDelete);


        # Add portlet button
        if ($this->isAllowUpdateAction() === true) {
            $showUpdateButton = $showGateIn;
            if ($this->isValidParameter('jik_complete_release_on') === false) {
                $showUpdateButton = true;
                # Add add button
                $btnAddCntMdl = new ModalButton('btnJJikrAddMdl', $portletButton, $modal->getModalId());
                $btnAddCntMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
                $portlet->addButton($btnAddCntMdl);
                # Add delete button for table
                $table->setDeleteActionByModal($modalDelete, 'jikr', 'getByReferenceForDelete', ['jikr_id']);
            }
            if ($showUpdateButton === true) {
                $table->setUpdateActionByModal($modal, 'jikr', 'getByReference', ['jikr_id']);
            }
        }
        $portlet->addTable($table);
        return $portlet;
    }


    /**
     * Function to get storage modal.
     *
     * @return Modal
     */
    protected function getReleaseGoodsModal(): Modal
    {
        # Create Fields.
        if ($this->isInklaringImport() === true) {
            $title = Trans::getWord('releaseGoods');
        } else {
            $title = Trans::getWord('shipmentGoods');
        }
        $modal = new Modal('JikrMdl', $title);
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateReleaseGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateReleaseGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        } else {
            if ($this->isValidParameter('jikr_load_date') === false) {
                $this->setParameter('jikr_load_date', date('Y-m-d'));
            }
            if ($this->isValidParameter('jikr_load_time') === false) {
                $this->setParameter('jikr_load_time', date('H:i'));
            }
            if ($this->isValidParameter('jikr_gate_in_date') === false) {
                $this->setParameter('jikr_gate_in_date', date('Y-m-d'));
            }
            if ($this->isValidParameter('jikr_gate_in_time') === false) {
                $this->setParameter('jikr_gate_in_time', date('H:i'));
            }
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Goods Field
        $sogField = $this->Field->getSingleSelectTable('sog', 'jikr_goods', $this->getParameterForModal('jikr_goods', $showModal));
        $sogField->setHiddenField('jikr_sog_id', $this->getParameterForModal('jikr_sog_id', $showModal));
        $sogField->setTableColumns([
            'sog_hs_code' => Trans::getWord('hsCode'),
            'sog_name' => Trans::getWord('description'),
            'sog_packing_ref' => Trans::getWord('packingRef'),
            'sog_quantity_number' => Trans::getWord('quantity'),
            'sog_uom' => Trans::getWord('uom')
        ]);
        $sogField->setAutoCompleteFields([
            'jikr_hs_code' => 'sog_hs_code',
            'jikr_goods_quantity' => 'sog_quantity',
            'jikr_goods_quantity_number' => 'sog_quantity_number',
            'jikr_uom_code' => 'sog_uom',
            'jikr_gross_weight' => 'sog_gross_weight',
            'jikr_gross_weight_number' => 'sog_gross_weight_number',
            'jikr_net_weight' => 'sog_net_weight',
            'jikr_net_weight_number' => 'sog_net_weight_number',
            'jikr_cbm' => 'sog_cbm',
            'jikr_cbm_number' => 'sog_cbm_number',
        ]);
        $sogField->setValueCode('sog_id');
        $sogField->setLabelCode('sog_name');
        $sogField->addParameter('sog_so_id', $this->getSoId());
        $sogField->addOptionalParameterById('jikr_id', 'jikr_id');
        $sogField->setParentModal($modal->getModalId());
        $this->View->addModal($sogField->getModal());

        $jogQtyField = $this->Field->getNumber('jikr_goods_quantity', $this->getParameterForModal('jikr_goods_quantity', $showModal));
        $jogQtyField->setReadOnly();
        $hsCodeField = $this->Field->getText('jikr_hs_code', $this->getParameterForModal('jikr_hs_code', $showModal));
        $hsCodeField->setReadOnly();
        $packingRefField = $this->Field->getText('jikr_packing_ref', $this->getParameterForModal('jikr_packing_ref', $showModal));
        $packingRefField->setReadOnly();
        $uomField = $this->Field->getText('jikr_uom_code', $this->getParameterForModal('jikr_uom_code', $showModal));
        $uomField->setReadOnly();
        $grossWeightField = $this->Field->getText('jikr_gross_weight', $this->getParameterForModal('jikr_gross_weight', $showModal));
        $grossWeightField->setReadOnly();
        $netWeightField = $this->Field->getText('jikr_net_weight', $this->getParameterForModal('jikr_net_weight', $showModal));
        $netWeightField->setReadOnly();
        $volumeField = $this->Field->getText('jikr_cbm', $this->getParameterForModal('jikr_cbm', $showModal));
        $volumeField->setReadOnly();
        # Transporter
        $transporterField = $this->Field->getSingleSelect('relation', 'jikr_transporter', $this->getParameterForModal('jikr_transporter', $showModal));
        $transporterField->setHiddenField('jikr_transporter_id', $this->getParameterForModal('jikr_transporter_id'));
        $transporterField->addParameter('rel_ss_id', $this->User->getSsId());
        $transporterField->setDetailReferenceCode('rel_id');

        $truckPlateField = $this->Field->getText('jikr_truck_number', $this->getParameterForModal('jikr_truck_number', $showModal));
        $driverField = $this->Field->getText('jikr_driver', $this->getParameterForModal('jikr_driver', $showModal));
        $driverPhoneField = $this->Field->getText('jikr_driver_phone', $this->getParameterForModal('jikr_driver_phone', $showModal));
        $loadDate = $this->Field->getCalendar('jikr_load_date', $this->getParameterForModal('jikr_load_date', true));
        $loadTime = $this->Field->getTime('jikr_load_time', $this->getParameterForModal('jikr_load_time', true));
        $qtyRelease = $this->Field->getNumber('jikr_quantity', $this->getParameterForModal('jikr_quantity', $showModal));
        if ($this->isValidParameter('jik_complete_release_on') === true) {
            $sogField->setReadOnly();
            $qtyRelease->setReadOnly();
            $transporterField->setReadOnly();
            $truckPlateField->setReadOnly();
            $driverField->setReadOnly();
            $driverPhoneField->setReadOnly();
            $loadDate->setReadOnly();
            $loadTime->setReadOnly();
        }
        # Add field into field set.
        if ($this->getStringParameter('so_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $fieldSet->addField(Trans::getWord('gateInDate'), $this->Field->getCalendar('jikr_gate_in_date', $this->getParameterForModal('jikr_gate_in_date', true)));
            $fieldSet->addField(Trans::getWord('gateInTime'), $this->Field->getTime('jikr_gate_in_time', $this->getParameterForModal('jikr_gate_in_time', true)));
        }
        $fieldSet->addField(Trans::getWord('hsCode'), $hsCodeField);
        $fieldSet->addField(Trans::getWord('goods'), $sogField, true);
        $fieldSet->addField(Trans::getWord('qtyPlanning'), $jogQtyField);
        $fieldSet->addField(Trans::getWord('qtyRelease'), $qtyRelease, true);
        $fieldSet->addField(Trans::getWord('transporter'), $transporterField, true);
        $fieldSet->addField(Trans::getWord('releaseDate'), $loadDate, true);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckPlateField, true);
        $fieldSet->addField(Trans::getWord('releaseTime'), $loadTime, true);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $driverPhoneField);
        $fieldSet->addField(Trans::getWord('packingRef'), $packingRefField);
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('grossWeight') . ' (KG)', $grossWeightField);
        $fieldSet->addField(Trans::getWord('netWeight') . ' (KG)', $netWeightField);
        $fieldSet->addField(Trans::getWord('cbm'), $volumeField);
        $fieldSet->addHiddenField($this->Field->getHidden('jikr_id', $this->getParameterForModal('jikr_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get release goods delete modal.
     *
     * @return Modal
     */
    protected function getReleaseGoodsDeleteModal(): Modal
    {
        # Create Fields.
        if ($this->isInklaringImport() === true) {
            $title = Trans::getWord('deleteReleasedGoods');
        } else {
            $title = Trans::getWord('deleteShipmentGoods');
        }
        $modal = new Modal('JikrDelMdl', $title);
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteReleaseGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteReleaseGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Job Container Field
        $hsCodeField = $this->Field->getText('jikr_hs_code_del', $this->getParameterForModal('jikr_hs_code_del', $showModal));
        $goodsField = $this->Field->getText('jikr_goods_del', $this->getParameterForModal('jikr_goods_del', $showModal));
        $quantityField = $this->Field->getNumber('jikr_quantity_del', $this->getParameterForModal('jikr_quantity_del', $showModal));
        $uomField = $this->Field->getText('jikr_uom_code_del', $this->getParameterForModal('jikr_uom_code_del', $showModal));
        $transporterField = $this->Field->getText('jikr_transporter_del', $this->getParameterForModal('jikr_transporter_del', $showModal));
        $truckPlateField = $this->Field->getText('jikr_truck_number_del', $this->getParameterForModal('jikr_truck_number_del', $showModal));
        $driverField = $this->Field->getText('jikr_driver_del', $this->getParameterForModal('jikr_driver_del', $showModal));
        $driverPhoneField = $this->Field->getText('jikr_driver_phone_del', $this->getParameterForModal('jikr_driver_phone_del', $showModal));
        $loadDateField = $this->Field->getCalendar('jikr_load_date_del', $this->getParameterForModal('jikr_load_date_del', $showModal));
        $loadTimeField = $this->Field->getTime('jikr_load_time_del', $this->getParameterForModal('jikr_load_time_del', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('hsCode'), $hsCodeField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('qtyRelease'), $quantityField);
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('transporter'), $transporterField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $truckPlateField);
        $fieldSet->addField(Trans::getWord('driver'), $driverField);
        $fieldSet->addField(Trans::getWord('driverPhone'), $driverPhoneField);
        $fieldSet->addField(Trans::getWord('loadDate'), $loadDateField);
        $fieldSet->addField(Trans::getWord('loadTime'), $loadTimeField);
        $fieldSet->addHiddenField($this->Field->getHidden('jikr_id_del', $this->getParameterForModal('jikr_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
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
        return $this->getStringParameter('jo_srt_route') === 'jiic' || $this->getStringParameter('jo_srt_route') === 'jii';
    }

    /**
     * Function to set so hidden data.
     *
     * @return void
     */
    private function setJikHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jik_id', $this->getIntParameter('jik_id'));
        $content .= $this->Field->getHidden('jik_so_id', $this->getIntParameter('jik_so_id'));
        $this->View->addContent('JikHdFld', $content);

    }

}
