<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\CustomerService;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelTrueFalse;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\CustomerService\SalesGoodsPositionDao;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\CustomerService\SalesOrderQuotationDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderHoldDao;
use App\Model\Dao\CustomerService\SalesOrderIssueDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDetailDao;
use App\Model\Dao\Job\Delivery\LoadUnloadDeliveryDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobOrderHoldDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\System\CustomsClearanceTypeDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Service\SystemServiceDao;
use App\Model\Dao\System\TransportModuleDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail JobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrder extends AbstractFormModel
{
    /**
     * Property to store the sales order container data.
     *
     * @var array $SocData = [];
     */
    private $SocData = [];
    /**
     * Property to store the sales order goods data.
     *
     * @var array $SogData = [];
     */
    private $SogData = [];
    /**
     * Property to store the sales order loading address.
     *
     * @var array $LadingAddress = [];
     */
    private $LoadingAddress = [];
    /**
     * Property to store the sales order unload address.
     *
     * @var array $UnloadAddress = [];
     */
    private $UnloadAddress = [];

    /**
     * Property to store the list of job order.
     *
     * @var array $JobOrders = [];
     */
    private $JobOrders = [];

    /**
     * Property to store if exist inklaring job.
     *
     * @var bool $IsJobInklaringExist = false;
     */
    private $IsJobInklaringExist = false;
    /**
     * Property to store if exist delivery job.
     *
     * @var bool $IsJobDeliveryExist = false;
     */
    private $IsJobDeliveryExist = false;
    /**
     * Property to store if exist warehouse job.
     *
     * @var bool $IsJobWarehouseExist = false;
     */
    private $IsJobWarehouseExist = false;


    /**
     * Property to store the list of job order.
     *
     * @var array $Quotations = [];
     */
    private $Quotations = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'so', 'so_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $container = $this->getStringParameter('so_container');
        $consolidate = $this->getStringParameter('so_consolidate');
        $multiPickUp = $this->getStringParameter('so_multi_load');
        $multiDrop = $this->getStringParameter('so_multi_unload');
        if ($consolidate === 'Y') {
            $container = 'N';
            $multiPickUp = 'N';
            $multiDrop = 'N';
        }

        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('SalesOrder', $this->getIntParameter('so_order_of_id'), $this->getIntParameter('so_rel_id'));
        $colVal = [
            'so_number' => $number,
            'so_ss_id' => $this->User->getSsId(),
            'so_rel_id' => $this->getIntParameter('so_rel_id'),
            'so_pic_id' => $this->getIntParameter('so_pic_id'),
            'so_order_of_id' => $this->getIntParameter('so_order_of_id'),
            'so_invoice_of_id' => $this->getIntParameter('so_invoice_of_id'),
            'so_order_date' => date('Y-m-d'),
            'so_customer_ref' => $this->getStringParameter('so_customer_ref'),
            'so_bl_ref' => $this->getStringParameter('so_bl_ref'),
            'so_aju_ref' => $this->getStringParameter('so_aju_ref'),
            'so_sppb_ref' => $this->getStringParameter('so_sppb_ref'),
            'so_packing_ref' => $this->getStringParameter('so_packing_ref'),
            'so_container' => $container,
            'so_consolidate' => $consolidate,
            'so_sales_id' => $this->getIntParameter('so_sales_id'),
            'so_inklaring' => $this->getStringParameter('so_inklaring'),
            'so_delivery' => $this->getStringParameter('so_delivery'),
            'so_warehouse' => $this->getStringParameter('so_warehouse'),
            'so_ict_id' => $this->getIntParameter('so_ict_id'),
            'so_multi_load' => $multiPickUp,
            'so_multi_unload' => $multiDrop,
        ];
        $soDao = new SalesOrderDao();
        $soDao->doInsertTransaction($colVal);

        return $soDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $container = $this->getStringParameter('so_container');
            $consolidate = $this->getStringParameter('so_consolidate');
            $multiPickUp = $this->getStringParameter('so_multi_load');
            $multiDrop = $this->getStringParameter('so_multi_unload');
            if ($consolidate === 'Y') {
                $container = 'N';
                $multiPickUp = 'N';
                $multiDrop = 'N';
            }
            $colVal = [
                'so_rel_id' => $this->getIntParameter('so_rel_id'),
                'so_pic_id' => $this->getIntParameter('so_pic_id'),
                'so_order_of_id' => $this->getIntParameter('so_order_of_id'),
                'so_invoice_of_id' => $this->getIntParameter('so_invoice_of_id'),
                'so_customer_ref' => $this->getStringParameter('so_customer_ref'),
                'so_bl_ref' => $this->getStringParameter('so_bl_ref'),
                'so_aju_ref' => $this->getStringParameter('so_aju_ref'),
                'so_sppb_ref' => $this->getStringParameter('so_sppb_ref'),
                'so_packing_ref' => $this->getStringParameter('so_packing_ref'),
                'so_container' => $container,
                'so_consolidate' => $consolidate,
                'so_sales_id' => $this->getIntParameter('so_sales_id'),
                'so_notes' => $this->getStringParameter('so_notes'),
                'so_inklaring' => $this->getStringParameter('so_inklaring'),
                'so_delivery' => $this->getStringParameter('so_delivery'),
                'so_warehouse' => $this->getStringParameter('so_warehouse'),
                'so_ict_id' => $this->getIntParameter('so_ict_id'),
                'so_multi_load' => $multiPickUp,
                'so_multi_unload' => $multiDrop,
                'so_shipper_id' => $this->getintParameter('so_shipper_id'),
                'so_shipper_of_id' => $this->getintParameter('so_shipper_of_id'),
                'so_shipper_cp_id' => $this->getintParameter('so_shipper_cp_id'),
                'so_consignee_id' => $this->getintParameter('so_consignee_id'),
                'so_consignee_of_id' => $this->getintParameter('so_consignee_of_id'),
                'so_consignee_cp_id' => $this->getintParameter('so_consignee_cp_id'),
                'so_notify_id' => $this->getintParameter('so_notify_id'),
                'so_notify_of_id' => $this->getintParameter('so_notify_of_id'),
                'so_notify_cp_id' => $this->getintParameter('so_notify_cp_id'),
                'so_carrier_id' => $this->getintParameter('so_carrier_id'),
                'so_carrier_of_id' => $this->getintParameter('so_carrier_of_id'),
                'so_carrier_cp_id' => $this->getintParameter('so_carrier_cp_id'),
            ];
            if ($this->isInklaring() === true) {
                $colVal = array_merge($colVal, [
                    'so_cdt_id' => $this->getintParameter('so_cdt_id'),
                    'so_cct_id' => $this->getintParameter('so_cct_id'),
                    'so_tm_id' => $this->getintParameter('so_tm_id'),
                    'so_transport_name' => $this->getStringParameter('so_transport_name'),
                    'so_transport_number' => $this->getStringParameter('so_transport_number'),
                    'so_plb' => $this->getStringParameter('so_plb'),
                    'so_pol_id' => $this->getintParameter('so_pol_id'),
                    'so_departure_date' => $this->getStringParameter('so_departure_date'),
                    'so_departure_time' => $this->getStringParameter('so_departure_time'),
                    'so_pod_id' => $this->getintParameter('so_pod_id'),
                    'so_arrival_date' => $this->getStringParameter('so_arrival_date'),
                    'so_arrival_time' => $this->getStringParameter('so_arrival_time'),
                ]);
            } else {
                if ($this->isPol() === true) {
                    $colVal = array_merge($colVal, [
                        'so_pol_id' => $this->getintParameter('so_pol_id'),
                        'so_departure_date' => $this->getStringParameter('so_departure_date'),
                        'so_departure_time' => $this->getStringParameter('so_departure_time'),
                    ]);
                }
                if ($this->isPod() === true) {
                    $colVal = array_merge($colVal, [
                        'so_pod_id' => $this->getintParameter('so_pod_id'),
                        'so_arrival_date' => $this->getStringParameter('so_arrival_date'),
                        'so_arrival_time' => $this->getStringParameter('so_arrival_time'),
                    ]);
                }
            }
            if ($this->isInklaring() === true || $this->isWarehouse() === true) {
                $colVal = array_merge($colVal, [
                    'so_wh_id' => $this->getintParameter('so_wh_id'),
                ]);
            }
            if ($this->isDelivery() === true) {
                if ($this->isLoad() === true) {
                    $colVal = array_merge($colVal, [
                        'so_dp_id' => $this->getintParameter('so_dp_id'),
                        'so_yr_id' => $this->getintParameter('so_yr_id'),
                    ]);
                }
                if ($this->isUnload() === true) {
                    $colVal = array_merge($colVal, [
                        'so_dr_id' => $this->getintParameter('so_dr_id'),
                        'so_yp_id' => $this->getintParameter('so_yp_id'),
                    ]);
                }
            }

            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            $this->doUpdateJobOrderData();
        } elseif ($this->getFormAction() === 'doUpdateContainer') {
            $socColVal = [
                'soc_so_id' => $this->getDetailReferenceValue(),
                'soc_eg_id' => $this->getIntParameter('soc_eg_id'),
                'soc_ct_id' => $this->getIntParameter('soc_ct_id'),
                'soc_container_number' => $this->getStringParameter('soc_container_number'),
                'soc_seal_number' => $this->getStringParameter('soc_seal_number'),
            ];
            $socDao = new SalesOrderContainerDao();
            if ($this->isValidParameter('soc_id') === true) {
                # Do Update SOC data
                $socDao->doUpdateTransaction($this->getIntParameter('soc_id'), $socColVal);

                # Update job delivery container number where container is not empty
                $jdlData = JobDeliveryDao::loadJobDeliveryRoadBySocId($this->getIntParameter('soc_id'));
                if (empty($jdlData) === false) {
                    $jdlColVal = [
                        'jdl_eg_id' => $this->getIntParameter('soc_eg_id'),
                    ];
                    if ($this->isValidParameter('soc_ct_id') === true && $this->isValidParameter('soc_container_number') === true) {
                        $jdlColVal = array_merge($jdlColVal, [
                            'jdl_ct_id' => $this->getIntParameter('soc_ct_id'),
                            'jdl_container_number' => $this->getStringParameter('soc_container_number'),
                            'jdl_seal_number' => $this->getStringParameter('soc_seal_number'),
                        ]);
                    }
                    $jdlDao = new JobDeliveryDao();
                    foreach ($jdlData as $row) {
                        $jdlDao->doUpdateTransaction($row['jdl_id'], $jdlColVal);
                    }
                }

            } else {
                $sn = new SerialNumber($this->User->getSsId());
                $number = $sn->loadNumber('SOC', $this->getIntParameter('so_order_of_id'), $this->getIntParameter('so_rel_id'));
                $socColVal['soc_number'] = $number;
                $socDao->doInsertTransaction($socColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteContainer') {
            $socDao = new SalesOrderContainerDao();
            $socDao->doDeleteTransaction($this->getIntParameter('soc_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateGoods') {
            $cbm = null;
            if ($this->isValidParameter('sog_length') === true && $this->isValidParameter('sog_width') === true && $this->isValidParameter('sog_height') === true) {
                $cbm = $this->getFloatParameter('sog_length') * $this->getFloatParameter('sog_width') * $this->getFloatParameter('sog_height');
            }
            $sogColVal = [
                'sog_so_id' => $this->getDetailReferenceValue(),
                'sog_soc_id' => $this->getIntParameter('sog_soc_id'),
                'sog_hs_code' => $this->getStringParameter('sog_hs_code'),
                'sog_name' => $this->getStringParameter('sog_name'),
                'sog_quantity' => $this->getFloatParameter('sog_quantity'),
                'sog_uom_id' => $this->getIntParameter('sog_uom_id'),
                'sog_length' => $this->getFloatParameter('sog_length'),
                'sog_width' => $this->getFloatParameter('sog_width'),
                'sog_height' => $this->getFloatParameter('sog_height'),
                'sog_cbm' => $cbm,
                'sog_gross_weight' => $this->getFloatParameter('sog_gross_weight'),
                'sog_net_weight' => $this->getFloatParameter('sog_net_weight'),
                'sog_packing_ref' => $this->getStringParameter('sog_packing_ref'),
                'sog_dimension_unit' => $this->getStringParameter('sog_dimension_unit'),
                'sog_notes' => $this->getStringParameter('sog_notes'),
            ];
            $sogDao = new SalesOrderGoodsDao();
            if ($this->isValidParameter('sog_id') === true) {
                $sogDao->doUpdateTransaction($this->getIntParameter('sog_id'), $sogColVal);
            } else {
                $sn = new SerialNumber($this->User->getSsId());
                $number = $sn->loadNumber('SOG', $this->getIntParameter('so_order_of_id'), $this->getIntParameter('so_rel_id'));
                $sogColVal['sog_number'] = $number;
                $sogDao->doInsertTransaction($sogColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoods') {
            $sogDao = new SalesOrderGoodsDao();
            $sogDao->doDeleteTransaction($this->getIntParameter('sog_id_del'));
        } elseif ($this->getFormAction() === 'doUpdateLoadAddress') {
            $this->doUpdateDeliveryAddress('O');
        } elseif ($this->getFormAction() === 'doUpdateUnloadAddress') {
            $this->doUpdateDeliveryAddress('D');
        } elseif ($this->getFormAction() === 'doDeleteDelivery') {
            $sdlDao = new SalesOrderDeliveryDao();
            $sdlDao->doDeleteTransaction($this->getIntParameter('sdl_id_del'));
        } elseif ($this->getFormAction() === 'doPublishSo') {
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'so_publish_by' => $this->User->getId(),
                'so_publish_on' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($this->isDeleteAction() === true) {
            # Delete Sales Order
            $soDao = new SalesOrderDao();
            $soDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());

            # Delete Job Order
            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue());
            $jobDao = new JobOrderDao();
            foreach ($jobs as $row) {
                $jobDao->doDeleteTransaction($row['jo_id'], $this->getReasonDeleteAction());
            }
        } elseif ($this->isUploadDocumentAction() === true) {
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
                    'doc_public' => $this->getStringParameter('doc_public'),
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->isDeleteDocumentAction() === true) {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doHold') {
            $sohDao = new SalesOrderHoldDao();
            $sohColVal = [
                'soh_so_id' => $this->getDetailReferenceValue(),
                'soh_reason' => $this->getReasonHoldAction()
            ];
            $sohDao->doInsertTransaction($sohColVal);
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'so_soh_id' => $sohDao->getLastInsertId(),
            ]);

            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue(), [
                SqlHelper::generateNullCondition('jo_joh_id')
            ]);
            $johDao = new JobOrderHoldDao();
            $joDao = new JobOrderDao();
            foreach ($jobs as $row) {
                $johColVal = [
                    'joh_jo_id' => $row['jo_id'],
                    'joh_reason' => $this->getReasonHoldAction(),
                ];
                $johDao->doInsertTransaction($johColVal);
                $joDao->doUpdateTransaction($row['jo_id'], [
                    'jo_joh_id' => $johDao->getLastInsertId(),
                ]);
            }
        } elseif ($this->getFormAction() === 'doUnHold') {
            $sohDao = new SalesOrderHoldDao();
            $sohDao->doDeleteTransaction($this->getIntParameter('soh_id'));
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'so_soh_id' => null,
            ]);

            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue(), [
                SqlHelper::generateNullCondition('jo_joh_id', false)
            ]);
            $johDao = new JobOrderHoldDao();
            $joDao = new JobOrderDao();
            foreach ($jobs as $row) {
                $johDao->doDeleteTransaction($row['jo_joh_id']);
                $joDao->doUpdateTransaction($row['jo_id'], [
                    'jo_joh_id' => null,
                ]);
            }
        } elseif ($this->getFormAction() === 'doCompleteSo') {
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'so_finish_by' => $this->User->getId(),
                'so_finish_on' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($this->getFormAction() === 'doInsertQuotation') {
            $qtIds = $this->getArrayParameter('qt_id');
            $qtSelects = $this->getArrayParameter('qt_select');
            $soqDao = new SalesOrderQuotationDao();
            foreach ($qtIds as $key => $val) {
                if (array_key_exists($key, $qtSelects) === true && $qtSelects[$key] === 'Y') {
                    $soqDao->doInsertTransaction([
                        'soq_so_id' => $this->getDetailReferenceValue(),
                        'soq_qt_id' => $val,
                    ]);
                }
            }
        } elseif ($this->getFormAction() === 'doDeleteQuotation') {
            $soqDao = new SalesOrderQuotationDao();
            $soqDao->doDeleteTransaction($this->getIntParameter('soq_id_del'));
        } elseif ($this->getFormAction() === 'doCreateInklaring') {
            $srtId = $this->getIntParameter('jik_srt_id');
            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue(), [
                SqlHelper::generateNumericCondition('jo_srv_id', $this->getIntParameter('jik_srv_id')),
                SqlHelper::generateNumericCondition('jo_srt_id', $srtId),
            ]);
            if (empty($jobs) === true) {
                $joDao = new JobOrderDao();
                $sn = new SerialNumber($this->User->getSsId());
                $joId = $this->doInsertJobOrder($joDao, $sn, $this->getIntParameter('jik_srv_id'), $srtId);
                $jacDao = new JobActionDao();
                $this->doInsertJobAction($jacDao, $joId, $srtId);
                $jikColVal = [
                    'jik_jo_id' => $joId,
                    'jik_so_id' => $this->getDetailReferenceValue(),
                    'jik_closing_date' => $this->getStringParameter('jik_closing_date'),
                    'jik_closing_time' => $this->getStringParameter('jik_closing_time'),
                ];
                $jikDao = new JobInklaringDao();
                $jikDao->doInsertTransaction($jikColVal);
                $this->doInsertGoodsPositionByInklaring($joId);
            }
        } elseif ($this->getFormAction() === 'doCreateJobDelivery') {
            if ($this->getStringParameter('jdl_srt_route') === 'ptp' || $this->getStringParameter('jdl_srt_route') === 'ptpc') {
                $this->doInsertPortToPortJobDelivery();
            } else {
                $this->doInsertJobDelivery();
            }
        } elseif ($this->getFormAction() === 'doCreateJobWarehouse') {
            $this->doInsertJobWarehouse();
        }
    }

    /**
     * Function to do update sales order delivery.;
     *
     * @param string $type to store the type.
     *
     * @return void
     */
    private function doUpdateDeliveryAddress(string $type): void
    {
        $postFix = strtolower($type);
        $sdlColVal = [
            'sdl_so_id' => $this->getDetailReferenceValue(),
            'sdl_rel_id' => $this->getIntParameter('sdl_rel_id' . $postFix),
            'sdl_of_id' => $this->getIntParameter('sdl_of_id' . $postFix),
            'sdl_pic_id' => $this->getIntParameter('sdl_pic_id' . $postFix),
            'sdl_reference' => $this->getStringParameter('sdl_reference' . $postFix),
            'sdl_type' => $type,
        ];
        if (($type === 'O' && $this->isMultiLoad() === true) || ($type === 'D' && $this->isMultiUnload() === true)) {
            $sdlColVal = array_merge([
                'sdl_sog_id' => $this->getIntParameter('sdl_sog_id' . $postFix),
                'sdl_quantity' => $this->getFloatParameter('sdl_quantity' . $postFix),
            ], $sdlColVal);
        }
        $sdlDao = new SalesOrderDeliveryDao();
        if ($this->isValidParameter('sdl_id' . $postFix) === true) {
            $sdlDao->doUpdateTransaction($this->getIntParameter('sdl_id' . $postFix), $sdlColVal);
        } else {
            $sdlDao->doInsertTransaction($sdlColVal);
        }
    }

    /**
     * Function to do update job order data.;
     *
     * @return void
     */
    private function doUpdateJobOrderData(): void
    {
        if ($this->isWarehouse() === true && $this->isValidParameter('so_wh_id') === true) {
            # Update warehouse for all job warehouse
            $data = $this->loadJobWarehouseData();
            if (empty($data) === false) {
                $jiDao = new JobInboundDao();
                $jobDao = new JobOutboundDao();
                foreach ($data as $row) {
                    if ($row['jw_route'] === 'joWhOutbound') {
                        $jobDao->doUpdateTransaction($row['jw_id'], [
                            'job_wh_id' => $this->getIntParameter('so_wh_id')
                        ]);
                    } else {
                        $jiDao->doUpdateTransaction($row['jw_id'], [
                            'ji_wh_id' => $this->getIntParameter('so_wh_id')
                        ]);
                    }
                }
            }
        }

        if ($this->isDelivery() === true && $this->isContainer() === true) {
            # Update depo Information for job delivery
            $data = $this->loadJdlForUpdateDepo();
            if (empty($data) === false) {
                $jdlDao = new JobDeliveryDao();
                foreach ($data as $row) {
                    if ($row['srt_route'] === 'dtpc') {
                        $jdlDao->doUpdateTransaction($row['jdl_id'], [
                            'jdl_dp_id' => $this->getIntParameter('so_dp_id'),
                            'jdl_dr_id' => $this->getIntParameter('so_yr_id'),
                        ]);
                    } else {
                        $jdlDao->doUpdateTransaction($row['jdl_id'], [
                            'jdl_dp_id' => $this->getIntParameter('so_yp_id'),
                            'jdl_dr_id' => $this->getIntParameter('so_dr_id'),
                        ]);
                    }
                }
            }
        }
        if ($this->isDelivery() === true && $this->isContainer() === false) {
            # Update load unload delivery by warehouse port.
            $data = $this->loadLudForUpdateAddress();
            if (empty($data) === false) {
                $ludDao = new LoadUnloadDeliveryDao();
                foreach ($data as $row) {
                    if ($row['srt_route'] === 'dtp') {
                        $ludDao->doUpdateTransaction($row['lud_id'], [
                            'lud_rel_id' => $this->getIntParameter('so_yr_rel_id'),
                            'lud_of_id' => $this->getIntParameter('so_yr_id'),
                        ]);
                    } else {
                        $ludDao->doUpdateTransaction($row['lud_id'], [
                            'lud_rel_id' => $this->getIntParameter('so_yp_rel_id'),
                            'lud_of_id' => $this->getIntParameter('so_yp_id'),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Function to do the insert the job order.;
     *
     * @param JobOrderDao $joDao To store the id of service.
     * @param SerialNumber $sn To store the id of service.
     * @param int $srvId To store the id of service.
     * @param int $srtId To store the id of service term.
     *
     * @return int
     */
    private function doInsertJobOrder(JobOrderDao $joDao, SerialNumber $sn, int $srvId, int $srtId): int
    {
        $officeId = $this->getIntParameter('so_order_of_id', $this->User->Relation->getOfficeId());
        $number = $sn->loadNumber('JobOrder', $officeId, $this->getIntParameter('so_rel_id'), $srvId, $srtId);
        $joColVal = [
            'jo_number' => $number,
            'jo_ss_id' => $this->User->getSsId(),
            'jo_srv_id' => $srvId,
            'jo_srt_id' => $srtId,
            'jo_order_date' => date('Y-m-d'),
            'jo_rel_id' => $this->getIntParameter('so_rel_id'),
            'jo_pic_id' => $this->getIntParameter('so_pic_id'),
            'jo_order_of_id' => $this->getIntParameter('so_order_of_id'),
            'jo_invoice_of_id' => $this->getIntParameter('so_invoice_of_id'),
        ];
        $joDao->doInsertTransaction($joColVal);

        return $joDao->getLastInsertId();
    }

    /**
     * Function to do the insert the job order.;
     *
     * @param JobActionDao $jacDao To store the id of service.
     * @param int $joId To store the id of service term.
     * @param int $srtId To store the id of service term.
     *
     * @return void
     */
    private function doInsertJobAction(JobActionDao $jacDao, int $joId, int $srtId): void
    {
        $listActions = SystemActionDao::getByServiceTermIdAndSystemId($srtId, $this->User->getSsId());
        $i = 1;
        foreach ($listActions as $row) {
            $jacColVal = [
                'jac_jo_id' => $joId,
                'jac_ac_id' => $row['sac_ac_id'],
                'jac_order' => $i,
                'jac_active' => 'Y',
            ];
            $jacDao->doInsertTransaction($jacColVal);
            $i++;
        }
    }

    /**
     * Function to do the insert port to port delivery.;
     *
     * @return void
     */
    private function doInsertPortToPortJobDelivery(): void
    {        # Do Insert job delivery detail
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getDetailReferenceValue());
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $wheres[] = "(soc.soc_id NOT IN (SELECT jdld.jdld_soc_id
                                            FROM job_delivery_detail as jdld
                                                INNER JOIN job_delivery as jdl ON jdld.jdld_jdl_id = jdl.jdl_id
                                                INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                                                INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                                            WHERE jdld_deleted_on IS NULL AND jo.jo_deleted_on IS NULL
                                            AND srt.srt_route = '" . $this->getStringParameter('jdl_srt_route') . "'))";
        $socData = SalesOrderContainerDao::loadData($wheres);
        if (empty($socData) === false) {

            $srtId = $this->getIntParameter('jdl_srt_id');
            $joDao = new JobOrderDao();
            $sn = new SerialNumber($this->User->getSsId());
            $joId = $this->doInsertJobOrder($joDao, $sn, $this->getIntParameter('jdl_srv_id'), $srtId);
            $jacDao = new JobActionDao();
            $this->doInsertJobAction($jacDao, $joId, $srtId);
            $jdlColVal = [
                'jdl_jo_id' => $joId,
                'jdl_so_id' => $this->getDetailReferenceValue(),
                'jdl_consolidate' => $this->getStringParameter('so_consolidate', 'N'),
                'jdl_departure_date' => $this->getStringParameter('jdl_departure_date'),
                'jdl_departure_time' => $this->getStringParameter('jdl_departure_time'),
                'jdl_tm_id' => $this->getStringParameter('jdl_tm_id'),
                'jdl_pol_id' => $this->getStringParameter('jdl_pol_id'),
                'jdl_pod_id' => $this->getStringParameter('jdl_pod_id'),
            ];
            $jdlDao = new JobDeliveryDao();
            $jdlDao->doInsertTransaction($jdlColVal);

            $socIds = [];
            $jdldDao = new JobDeliveryDetailDao();
            foreach ($socData as $row) {
                $socIds[] = $row['soc_id'];
                $jdldColVal = [
                    'jdld_jdl_id' => $jdlDao->getLastInsertId(),
                    'jdld_soc_id' => $row['soc_id'],
                    'jdld_final_destination' => 'N'
                ];
                $jdldDao->doInsertTransaction($jdldColVal);
            }
            # Do Insert Sales Goods Position
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('sog.sog_so_id', $this->getDetailReferenceValue());
            $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
            $wheres[] = '(sog.sog_soc_id IN (' . implode(', ', $socIds) . '))';
            $sogData = SalesOrderGoodsDao::loadData($wheres);
            $sgpDao = new SalesGoodsPositionDao();
            foreach ($sogData as $row) {
                $sgpColVal = [
                    'sgp_sog_id' => $row['sog_id'],
                    'sgp_jo_id' => $joId
                ];
                $sgpDao->doInsertTransaction($sgpColVal);
            }
        }

    }

    /**
     * Function to do the insert port to port delivery.;
     *
     * @return void
     */
    private function doInsertJobDelivery(): void
    {
        # Load Soc Data
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('sog.sog_so_id', $this->getDetailReferenceValue());
        $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
        $wheres[] = "(sog.sog_soc_id NOT IN (SELECT jdld.jdld_soc_id
                                            FROM job_delivery_detail as jdld
                                                INNER JOIN job_delivery as jdl ON jdld.jdld_jdl_id = jdl.jdl_id
                                                INNER JOIN job_order as jo ON jdl.jdl_jo_id = jo.jo_id
                                                INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                                            WHERE jdld_deleted_on IS NULL AND jo.jo_deleted_on IS NULL
                                            AND srt.srt_route = '" . $this->getStringParameter('jdl_srt_route') . "'))";
        $sogData = SalesOrderGoodsDao::loadData($wheres);
        $socIds = [];
        $sgpDao = new SalesGoodsPositionDao();
        $tm = TransportModuleDao::getByCode('road');
        $joDao = new JobOrderDao();
        $sn = new SerialNumber($this->User->getSsId());
        $jacDao = new JobActionDao();
        $jdlDao = new JobDeliveryDao();
        $jdldDao = new JobDeliveryDetailDao();
        foreach ($sogData as $row) {
            if (array_key_exists($row['sog_soc_id'], $socIds) === false) {
                # Do Insert Job Order
                $srtId = $this->getIntParameter('jdl_srt_id');
                $joId = $this->doInsertJobOrder($joDao, $sn, $this->getIntParameter('jdl_srv_id'), $srtId);
                $this->doInsertJobAction($jacDao, $joId, $srtId);
                $jdlColVal = [
                    'jdl_jo_id' => $joId,
                    'jdl_so_id' => $this->getDetailReferenceValue(),
                    'jdl_consolidate' => $this->getStringParameter('so_consolidate', 'N'),
                    'jdl_tm_id' => $tm['tm_id'],
                    'jdl_eg_id' => $row['sog_eg_id'],
                    'jdl_departure_date' => $this->getStringParameter('jdl_departure_date'),
                    'jdl_departure_time' => $this->getStringParameter('jdl_departure_time'),
                ];
                if ($this->getStringParameter('jdl_srt_route') === 'dtpc') {
                    $jdlColVal['jdl_dp_id'] = $this->getIntParameter('so_dp_id');
                    $jdlColVal['jdl_pod_id'] = $this->getIntParameter('jdl_pod_id');
                    $jdlColVal['jdl_ct_id'] = $row['sog_ct_id'];
                    $jdlColVal['jdl_container_number'] = $row['sog_container_number'];
                    $jdlColVal['jdl_seal_number'] = $row['sog_seal_number'];
                    $jdlColVal['jdl_dr_id'] = $this->getIntParameter('so_yr_id');
                } elseif ($this->getStringParameter('jdl_srt_route') === 'ptdc') {
                    $jdlColVal['jdl_dp_id'] = $this->getIntParameter('so_yp_id');
                    $jdlColVal['jdl_dr_id'] = $this->getIntParameter('so_dr_id');
                    $jdlColVal['jdl_pol_id'] = $this->getIntParameter('jdl_pol_id');
                    $jdlColVal['jdl_ct_id'] = $row['sog_ct_id'];
                    $jdlColVal['jdl_container_number'] = $row['sog_container_number'];
                    $jdlColVal['jdl_seal_number'] = $row['sog_seal_number'];
                } elseif ($this->getStringParameter('jdl_srt_route') === 'dtp') {
                    $jdlColVal['jdl_pod_id'] = $this->getIntParameter('jdl_pod_id');
                } elseif ($this->getStringParameter('jdl_srt_route') === 'ptd') {
                    $jdlColVal['jdl_pol_id'] = $this->getIntParameter('jdl_pol_id');
                }
                $jdlDao->doInsertTransaction($jdlColVal);
                # Do Insert load unload delivery for non container job
                if ($this->getStringParameter('jdl_srt_route') === 'dtp') {
                    $this->doInsertLoadUnloadDelivery($jdlDao->getLastInsertId(), $row['sog_soc_id'], 'D');
                }
                if ($this->getStringParameter('jdl_srt_route') === 'ptd') {
                    $this->doInsertLoadUnloadDelivery($jdlDao->getLastInsertId(), $row['sog_soc_id'], 'O');
                }
                # Insert job delivery detail
                $jdldColVal = [
                    'jdld_jdl_id' => $jdlDao->getLastInsertId(),
                    'jdld_soc_id' => $row['sog_soc_id'],
                    'jdld_final_destination' => 'N'
                ];
                $jdldDao->doInsertTransaction($jdldColVal);

                $socIds[$row['sog_soc_id']] = $joId;
            } else {
                # Do Insert sales goods position
                $joId = $socIds[$row['sog_soc_id']];
            }
            # Do insert sales goods position
            $sgpColVal = [
                'sgp_sog_id' => $row['sog_id'],
                'sgp_jo_id' => $joId
            ];
            $sgpDao->doInsertTransaction($sgpColVal);

        }
    }

    /**
     * Function to do the insert port to port delivery.;
     *
     * @return void
     */
    private function doInsertJobWarehouse(): void
    {
        $joDao = new JobOrderDao();
        $sn = new SerialNumber($this->User->getSsId());
        $jacDao = new JobActionDao();
        $jiDao = new JobInboundDao();
        $jobDao = new JobOutboundDao();
        $srtId = $this->getIntParameter('jw_srt_id');
        $joId = $this->doInsertJobOrder($joDao, $sn, $this->getIntParameter('jw_srv_id'), $srtId);
        $this->doInsertJobAction($jacDao, $joId, $srtId);
        # Do Insert Job Warehouse
        if ($this->getStringParameter('jw_srt_route') === 'joWhOutbound') {
            # Insert Outbound
            $jobColVal = [
                'job_jo_id' => $joId,
                'job_so_id' => $this->getDetailReferenceValue(),
                'job_wh_id' => $this->getIntParameter('so_wh_id'),
                'job_eta_date' => $this->getStringParameter('jw_planning_date'),
                'job_eta_time' => $this->getStringParameter('jw_planning_time'),
                'job_rel_id' => $this->getIntParameter('so_consignee_id'),
                'job_of_id' => $this->getIntParameter('so_consignee_of_id'),
                'job_cp_id' => $this->getIntParameter('so_consignee_cp_id'),
            ];
            $jobDao->doInsertTransaction($jobColVal);
        } else {
            # Insert Inbound
            $jiColVal = [
                'ji_jo_id' => $joId,
                'ji_so_id' => $this->getDetailReferenceValue(),
                'ji_wh_id' => $this->getIntParameter('so_wh_id'),
                'ji_eta_date' => $this->getStringParameter('jw_planning_date'),
                'ji_eta_time' => $this->getStringParameter('jw_planning_time'),
                'ji_rel_id' => $this->getIntParameter('so_shipper_id'),
                'ji_of_id' => $this->getIntParameter('so_shipper_of_id'),
                'ji_cp_id' => $this->getIntParameter('so_shipper_cp_id'),
            ];
            $jiDao->doInsertTransaction($jiColVal);
        }
    }

    /**
     * Function to load default lot number for job inbound goods .;
     *
     * @return ?string
     */
    protected function loadLotNumberJobInboundGoods(): ?string
    {
        return null;
    }

    /**
     * Function to do the insert load unload delivery for non container job.;
     *
     * @param int $jdlId To store the id of job delivery.
     * @param int $socId To store the id of job delivery.
     * @param string $type to store the type of address.
     *
     * @return void
     */
    private function doInsertLoadUnloadDelivery(int $jdlId, int $socId, string $type): void
    {
        $warehouseRelId = $this->getIntParameter('so_yp_rel_id');
        $warehouseId = $this->getIntParameter('so_yp_id');
        if ($type === 'D') {
            $warehouseRelId = $this->getIntParameter('so_yr_rel_id');
            $warehouseId = $this->getIntParameter('jso_yr_id');
        }
        $sogData = SalesOrderGoodsDao::getBySocId($socId);
        $ludDao = new LoadUnloadDeliveryDao();
        foreach ($sogData as $sog) {
            $ludColVal = [
                'lud_jdl_id' => $jdlId,
                'lud_sog_id' => $sog['sog_id'],
                'lud_quantity' => $sog['sog_quantity'],
                'lud_rel_id' => $warehouseRelId,
                'lud_of_id' => $warehouseId,
                'lud_type' => $type,
            ];
            $ludDao->doInsertTransaction($ludColVal);
        }
    }

    /**
     * Function to do the insert the goods position by inklaring.;
     *
     * @param int $joId To store the id of job order.
     *
     * @return void
     */
    private function doInsertGoodsPositionByInklaring(int $joId): void
    {
        $sogData = SalesOrderGoodsDao::getBySoId($this->getDetailReferenceValue());
        $sgpDao = new SalesGoodsPositionDao();
        foreach ($sogData as $row) {
            $sgpColVal = [
                'sgp_sog_id' => $row['sog_id'],
                'sgp_jo_id' => $joId
            ];
            $sgpDao->doInsertTransaction($sgpColVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SalesOrderDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getReferencePortlet());
        } else {
            $this->overridePageDescription();
            # Load Pre-condition data
            $this->loadSalesOrderGoodsData();
            $this->loadQuotationData();
            $this->LoadingAddress = $this->loadLoadUnloadData('O');
            $this->UnloadAddress = $this->loadLoadUnloadData('D');
            $this->loadSalesOrderContainerData();
            if ($this->isSoPublished() === true) {
                $this->loadJobOrderData();
            }
            # Check is deleted.
            if ($this->isSoDeleted() === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('soCanceledReason', 'message', '', ['user' => $this->getStringParameter('so_deleted_by'), 'reason' => $this->getStringParameter('so_deleted_reason')]));
            }
            # Check is hold.
            if ($this->isSoHold() === true) {
                $this->setDisableUpdate();
                $date = DateTimeParser::format($this->getStringParameter('soh_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
                $this->View->addWarningMessage(Trans::getWord('soHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('soh_reason')]));
            }
            # Set Hidden Fields
            $this->setHiddenField();
            # General
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            $this->Tab->addPortlet('general', $this->getReferencePortlet());
            $this->Tab->addPortlet('general', $this->getRelationPortlet());
            $this->Tab->addPortlet('general', $this->getNotesPortlet());

            # Details
            if ($this->isWarehouse() === true || $this->isInklaring() === true) {
                $this->Tab->addPortlet('detail', $this->getDetailPortlet());
            }
            if ($this->isInklaring() === true) {
                $this->Tab->addPortlet('detail', $this->getPortPortlet());
            }
            if ($this->isDelivery() === true && ($this->isLoad() === true || $this->isUnload() === true)) {
                $this->Tab->addPortlet('detail', $this->getDepoPortlet());

            }


            # Goods
            if ($this->isConsolidate() === false && ($this->isDelivery() === true || $this->isContainer() === true)) {
                $this->Tab->addPortlet('goods', $this->getContainerPortlet());
            }
            $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());

            # Delivery Address
            if ($this->isDelivery() === true) {
                $deleteModal = $this->getLoadUnloadDeleteModal();
                $this->View->addModal($deleteModal);
                if ($this->isLoad() === true) {
                    $this->Tab->addPortlet('goods', $this->getLoadUnloadPortlet('O', $deleteModal));
                }
                if ($this->isUnload() === true) {
                    $this->Tab->addPortlet('goods', $this->getLoadUnloadPortlet('D', $deleteModal));
                }
            }
            # Job Order
            if ($this->isSoPublished() === true) {
                $this->Tab->addPortlet('jobOrder', $this->getJobPortlet());
            }
            if ($this->isAllowToSeeQuotationInformation()) {
                $this->Tab->addPortlet('finance', $this->getQuotationPortlet());
            }
            if ($this->isAllowToSeeSalesInformation()) {
                $this->Tab->addPortlet('finance', $this->getSalesFieldSet());
            }
            if ($this->isAllowToSeePurchaseInformation()) {
                $this->Tab->addPortlet('finance', $this->getPurchaseFieldSet());
                if ($this->isAllowToSeeDepositInformation()) {
                    $this->Tab->addPortlet('finance', $this->getDepositPortlet());
                }
            }
            if ($this->isAllowToSeeMarginInformation()) {
                $this->Tab->addPortlet('finance', $this->getFinanceMarginFieldSet());
            }
            $this->Tab->addPortlet('document', $this->getDocumentFPortlet());
            if ($this->isSoPublished() === true && $this->isSoFinish() === false) {
                $this->Tab->addPortlet('issues', $this->getReviewPortlet());
            }
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('so_rel_id');
            $this->Validation->checkRequire('so_order_of_id');
            $this->Validation->checkRequire('so_invoice_of_id');
            $this->Validation->checkRequire('so_consolidate');
            $this->Validation->checkRequire('so_container');
            $this->Validation->checkRequire('so_inklaring');
            $this->Validation->checkRequire('so_delivery');
            $this->Validation->checkRequire('so_warehouse');
            if ($this->getStringParameter('so_inklaring', 'N') === 'Y' || $this->getStringParameter('so_delivery', 'N') === 'Y') {
                $this->Validation->checkRequire('so_ict_id');
            }
            if ($this->getStringParameter('so_delivery', 'N') === 'Y') {
                $this->Validation->checkRequire('so_multi_load');
                $this->Validation->checkRequire('so_multi_unload');
            }
            $this->Validation->checkMaxLength('so_customer_ref', 255);
            $this->Validation->checkMaxLength('so_bl_ref', 255);
            $this->Validation->checkMaxLength('so_aju_ref', 255);
            $this->Validation->checkMaxLength('so_sppb_ref', 255);
            $this->Validation->checkMaxLength('so_packing_ref', 255);
            $this->Validation->checkMaxLength('so_notes', 255);
            if ($this->isUpdate() === true) {
                $this->loadUpdateValidation();
            }
        } elseif ($this->getFormAction() === 'doUpdateContainer') {
            if ($this->isDelivery() === true) {
                $this->Validation->checkRequire('soc_eg_id');
            }
            if ($this->isContainer() === true) {
                $this->Validation->checkRequire('soc_ct_id');
                if ($this->isValidParameter('soc_container_number') === true) {
                    $this->Validation->checkMaxLength('soc_container_number', 128);
                    $this->Validation->checkUnique('soc_container_number', 'sales_order_container',
                        [
                            'soc_id' => $this->getIntParameter('soc_id')
                        ],
                        [
                            'soc_so_id' => $this->getDetailReferenceValue(),
                            'soc_deleted_on' => null
                        ]);
                }
                if ($this->isValidParameter('soc_seal_number') === true) {
                    $this->Validation->checkMaxLength('soc_seal_number', 128);
                    $this->Validation->checkUnique('soc_seal_number', 'sales_order_container',
                        [
                            'soc_id' => $this->getIntParameter('soc_id')
                        ],
                        [
                            'soc_so_id' => $this->getDetailReferenceValue(),
                            'soc_deleted_on' => null
                        ]);
                }
            }
        } elseif ($this->getFormAction() === 'doDeleteContainer') {
            $this->Validation->checkRequire('soc_id_del');
        } elseif ($this->getFormAction() === 'doUpdateGoods') {
            $this->Validation->checkRequire('sog_name', 3);
            $this->Validation->checkMaxLength('sog_hs_code', 125);
            $this->Validation->checkMaxLength('sog_packing_ref', 256);
            $this->Validation->checkMaxLength('sog_notes', 256);
            $this->Validation->checkMaxLength('sog_packing_ref', 256);
            if ($this->isConsolidate() === false && ($this->isContainer() === true || $this->isDelivery() === true)) {
                $this->Validation->checkRequire('sog_soc_id');
            }
//            $this->Validation->checkUnique('sog_name', 'sales_order_goods', [
//                'sog_id' => $this->getIntParameter('sog_id')
//            ], [
//                'sog_so_id' => $this->getDetailReferenceValue(),
//                'sog_soc_id' => $this->getIntParameter('sog_soc_id'),
//                'sog_deleted_on' => null
//            ]);
            if ($this->isValidParameter('sog_length') === true) {
                $this->Validation->checkFloat('sog_length');
            }
            if ($this->isValidParameter('sog_height') === true) {
                $this->Validation->checkFloat('sog_height');
            }
            if ($this->isValidParameter('sog_width') === true) {
                $this->Validation->checkFloat('sog_width');
            }
            if ($this->isValidParameter('sog_gross_weight') === true) {
                $this->Validation->checkFloat('sog_gross_weight');
            }
            if ($this->isValidParameter('sog_net_weight') === true) {
                $this->Validation->checkFloat('sog_net_weight');
            }
            if (($this->isValidParameter('sog_length') === true && $this->isValidParameter('sog_width') === true && $this->isValidParameter('sog_height') === true) ||
                $this->isValidParameter('sog_gross_weight') === true || $this->isValidParameter('sog_net_weight') === true) {
                $this->Validation->checkRequire('sog_dimension_unit');
            }
        } elseif ($this->getFormAction() === 'doDeleteGoods') {
            $this->Validation->checkRequire('sog_id_del');
        } elseif ($this->getFormAction() === 'doUpdateLoadAddress') {
            $this->doValidateDeliveryAddress('O');
        } elseif ($this->getFormAction() === 'doUpdateUnloadAddress') {
            $this->doValidateDeliveryAddress('D');
        } elseif ($this->getFormAction() === 'doDeleteDelivery') {
            $this->Validation->checkRequire('sdl_id_del');
        } elseif ($this->getFormAction() === 'doHold') {
            $this->Validation->checkRequire('base_hold_reason', 3);
        } elseif ($this->getFormAction() === 'doUnHold') {
            $this->Validation->checkRequire('soh_id');
        } elseif ($this->getFormAction() === 'doInsertQuotation') {
            $this->Validation->checkRequireArray('qt_select', 1);
        } elseif ($this->getFormAction() === 'doDeleteQuotation') {
            $this->Validation->checkRequire('soq_id_del');
        } elseif ($this->getFormAction() === 'doCreateInklaring') {
            $this->Validation->checkRequire('jik_srv_id');
            $this->Validation->checkRequire('jik_srt_id');
            $this->Validation->checkRequire('jik_closing_date');
            $this->Validation->checkRequire('jik_closing_time');
            $this->Validation->checkDate('jik_closing_date');
            $this->Validation->checkTime('jik_closing_time');
        } elseif ($this->getFormAction() === 'doCreateJobDelivery') {
            $this->Validation->checkRequire('jdl_srv_id');
            $this->Validation->checkRequire('jdl_srt_id');
            $this->Validation->checkRequire('jdl_departure_date');
            $this->Validation->checkRequire('jdl_departure_time');
            $this->Validation->checkDate('jdl_departure_date');
            $this->Validation->checkTime('jdl_departure_time');
            $srtRoute = $this->getStringParameter('jdl_srt_route');
            if ($srtRoute === 'ptp' || $srtRoute === 'ptpc') {
                $this->Validation->checkRequire('jdl_tm_id');
            }
            if ($this->getStringParameter('jdl_srt_pol', 'N') === 'Y') {
                $this->Validation->checkRequire('jdl_pol_id');
            }
            if ($this->getStringParameter('jdl_srt_pod', 'N') === 'Y') {
                $this->Validation->checkRequire('jdl_pod_id');
            }
        } elseif ($this->getFormAction() === 'doCreateJobWarehouse') {
            $this->Validation->checkRequire('jw_srv_id');
            $this->Validation->checkRequire('jw_srt_id');
            $this->Validation->checkRequire('jw_srt_route');
            $this->Validation->checkRequire('jw_planning_date');
            $this->Validation->checkRequire('jw_planning_time');
            $this->Validation->checkDate('jw_planning_date');
            $this->Validation->checkTime('jw_planning_time');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to override page description.
     *
     * @return void
     */
    private function loadUpdateValidation(): void
    {
        if ($this->isValidParameter('so_departure_date') === true) {
            $this->Validation->checkDate('so_departure_date');
        }
        if ($this->isValidParameter('so_departure_time') === true) {
            $this->Validation->checkTime('so_departure_time');
        }
        if ($this->isValidParameter('so_arrival_date') === true) {
            $this->Validation->checkDate('so_arrival_date');
        }
        if ($this->isValidParameter('so_arrival_time') === true) {
            $this->Validation->checkTime('so_arrival_time');
        }
    }

    /**
     * Function to validate delivery address.
     *
     * @param string $type to store the address type.
     *
     * @return void
     */
    private function doValidateDeliveryAddress(string $type): void
    {
        $postFix = strtolower($type);
        $this->Validation->checkRequire('sdl_rel_id' . $postFix);
        $this->Validation->checkRequire('sdl_of_id' . $postFix);
        if (($type === 'O' && $this->isMultiLoad() === true) || ($type === 'D' && $this->isMultiUnload() === true)) {
            $this->Validation->checkRequire('sdl_sog_id' . $postFix);
        }
        if ($this->isValidParameter('sdl_quantity' . $postFix) === true) {
            $this->Validation->checkFloat('sdl_quantity' . $postFix, 1);
        }
    }

    /**
     * Function to override page description.
     *
     * @return void
     */
    private function overridePageDescription(): void
    {
        $soDao = new SalesOrderDao();
        $status = $soDao->generateStatus([
            'is_deleted' => $this->isValidParameter('so_deleted_on'),
            'is_hold' => $this->isValidParameter('soh_id'),
            'is_finish' => $this->isValidParameter('so_finish_on'),
            'is_in_progress' => $this->isValidParameter('so_start_on'),
            'is_publish' => $this->isValidParameter('so_publish_on'),
        ]);
        $this->View->setDescription('#' . $this->getStringParameter('so_number') . ' - ' . $status);
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
        $fieldSet->setGridDimension(6, 6);
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'so_customer', $this->getStringParameter('so_customer'));
        $relField->setHiddenField('so_rel_id', $this->getIntParameter('so_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'so_pic_customer', $this->getStringParameter('so_pic_customer'));
        $picField->setHiddenField('so_pic_id', $this->getIntParameter('so_pic_id'));
        $picField->addParameterById('cp_rel_id', 'so_rel_id', Trans::getWord('customer'));
        $picField->setDetailReferenceCode('cp_id');

        # Create order Office Field
        if ($this->isValidParameter('so_order_of_id') === false) {
            $this->setParameter('so_order_office', $this->User->Relation->getOfficeName());
            $this->setParameter('so_order_of_id', $this->User->Relation->getOfficeId());
        }
        $ofOrderField = $this->Field->getSingleSelect('office', 'so_order_office', $this->getStringParameter('so_order_office'));
        $ofOrderField->setHiddenField('so_order_of_id', $this->getIntParameter('so_order_of_id'));
        $ofOrderField->addParameter('of_rel_id', $this->User->getRelId());
        if ($this->PageSetting->checkPageRight('AllowInsertOrderOtherOffice') === false) {
            $ofOrderField->addParameter('of_id', $this->User->Relation->getOfficeId());
        }
        $ofOrderField->setEnableDetailButton(false);
        $ofOrderField->setEnableNewButton(false);
//        $ofOrderField->addClearField('so_sales_manager');
//        $ofOrderField->addClearField('so_sales_id');
        # Create invoice Office Field
        $ofInvoiceField = $this->Field->getSelect('so_invoice_of_id', $this->getIntParameter('so_invoice_of_id'));
        $ofInvoiceField->addOptions(OfficeDao::loadInvoiceOffice($this->User->getRelId()), 'of_name', 'of_id');
//        # Create Sales Manager Field
//        $salesField = $this->Field->getSingleSelect('contactPerson', 'so_sales_manager', $this->getStringParameter('so_sales_manager'));
//        $salesField->setHiddenField('so_sales_id', $this->getIntParameter('so_sales_id'));
//        $salesField->addParameterById('cp_of_id', 'so_order_of_id', Trans::getWord('orderOffice'));
//        $salesField->setDetailReferenceCode('cp_id');

        $containerField = $this->Field->getYesNo('so_container', $this->getStringParameter('so_container'));
        $consolidateField = $this->Field->getYesNo('so_consolidate', $this->getStringParameter('so_consolidate', 'N'));
        $consolidateField->setReadOnly(true);
        # Service Term
        if ($this->isUpdate() === true) {
            $relField->setReadOnly();
            $ofOrderField->setReadOnly();
            if (empty($this->SocData) === false || empty($this->SogData) === false) {
                $containerField->setReadOnly();
            }
        }

        $inklaringReadOnly = true;
        $deliveryReadOnly = true;
        $warehouseReadOnly = true;
        $ictReadOnly = true;
        $services = SystemServiceDao::loadActiveService($this->User->getSsId());
        foreach ($services as $row) {
            if ($row['srv_code'] === 'inklaring' && $this->IsJobInklaringExist === false) {
                $inklaringReadOnly = false;
            } else if ($row['srv_code'] === 'delivery' && $this->IsJobDeliveryExist === false) {
                $deliveryReadOnly = false;
            } else if ($row['srv_code'] === 'warehouse' && $this->IsJobWarehouseExist === false) {
                $warehouseReadOnly = false;
            }
        }
        if ($this->IsJobInklaringExist === false && $this->IsJobDeliveryExist === false) {
            $ictReadOnly = false;
        }
        if ($this->isInsert() === true) {
            if ($inklaringReadOnly === true) {
                $this->setParameter('so_inklaring', 'N');
            }
            if ($deliveryReadOnly === true) {
                $this->setParameter('so_delivery', 'N');
            }
            if ($warehouseReadOnly === true) {
                $this->setParameter('so_warehouse', 'N');
            }
        }

        $inklaringField = $this->Field->getYesNo('so_inklaring', $this->getStringParameter('so_inklaring'));
        $inklaringField->setReadOnly($inklaringReadOnly);
        $deliveryField = $this->Field->getYesNo('so_delivery', $this->getStringParameter('so_delivery'));
        $deliveryField->setReadOnly($deliveryReadOnly);
        $warehouseField = $this->Field->getYesNo('so_warehouse', $this->getStringParameter('so_warehouse'));
        $warehouseField->setReadOnly($warehouseReadOnly);
        $ictField = $this->Field->getSingleSelect('ict', 'so_inco_terms', $this->getStringParameter('so_inco_terms'));
        $ictField->setHiddenField('so_ict_id', $this->getIntParameter('so_ict_id'));
        $ictField->setEnableNewButton(false);
        $ictField->setEnableDetailButton(false);
        $ictField->setReadOnly($ictReadOnly);

        $multiLoadField = $this->Field->getYesNo('so_multi_load', $this->getStringParameter('so_multi_load'));
        $multiUnloadField = $this->Field->getYesNo('so_multi_unload', $this->getStringParameter('so_multi_unload'));

        if ($this->isConsolidate() === true || $this->IsJobDeliveryExist === true) {
            $containerField->setReadOnly();
            $multiUnloadField->setReadOnly();
            $multiLoadField->setReadOnly();
        }

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $picField);
        $fieldSet->addField(Trans::getWord('invoiceOffice'), $ofInvoiceField, true);
        $fieldSet->addField(Trans::getWord('orderOffice'), $ofOrderField, true);
        $fieldSet->addField(Trans::getWord('consolidate'), $consolidateField, true);
        $fieldSet->addField(Trans::getWord('container'), $containerField, true);
        $fieldSet->addField(Trans::getWord('inklaring'), $inklaringField, true);
        $fieldSet->addField(Trans::getWord('warehouse'), $warehouseField, true);
        $fieldSet->addField(Trans::getWord('delivery'), $deliveryField, true);
        $fieldSet->addField(Trans::getWord('incoTerms'), $ictField);
        $fieldSet->addField(Trans::getWord('multiPickUp'), $multiLoadField);
        $fieldSet->addField(Trans::getWord('multiDrop'), $multiUnloadField);
//        $fieldSet->addField(Trans::getWord('salesManager'), $salesField);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 8);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getReferencePortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('customerRef'), $this->Field->getText('so_customer_ref', $this->getStringParameter('so_customer_ref')));
        $fieldSet->addField(Trans::getWord('blRef'), $this->Field->getText('so_bl_ref', $this->getStringParameter('so_bl_ref')));
        $fieldSet->addField(Trans::getWord('ajuRef'), $this->Field->getText('so_aju_ref', $this->getStringParameter('so_aju_ref')));
        $fieldSet->addField(Trans::getWord('sppbRef'), $this->Field->getText('so_sppb_ref', $this->getStringParameter('so_sppb_ref')));
        $fieldSet->addField(Trans::getWord('packingListRef'), $this->Field->getText('so_packing_ref', $this->getStringParameter('so_packing_ref')));
        # Create a portlet box.
        $portlet = new Portlet('SoRefPtl', Trans::getWord('reference'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4);

        return $portlet;
    }

    /**
     * Function to get the Relation Portlet.
     *
     * @return Portlet
     */
    private function getRelationPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4, 4, 4);
        # Create Consignee or Consignee Field
        $consigneeField = $this->Field->getSingleSelect('relation', 'so_consignee', $this->getStringParameter('so_consignee'));
        $consigneeField->setHiddenField('so_consignee_id', $this->getIntParameter('so_consignee_id'));
        $consigneeField->addParameter('rel_ss_id', $this->User->getSsId());
        $consigneeField->setDetailReferenceCode('rel_id');
        $consigneeField->addClearField('so_consignee_address');
        $consigneeField->addClearField('so_consignee_of_id');
        $consigneeField->addClearField('so_pic_consignee');
        $consigneeField->addClearField('so_consignee_cp_id');
        # Create order Office Field
        $consigneeAddressField = $this->Field->getSingleSelect('office', 'so_consignee_address', $this->getStringParameter('so_consignee_address'));
        $consigneeAddressField->setHiddenField('so_consignee_of_id', $this->getIntParameter('so_consignee_of_id'));
        $consigneeAddressField->addParameterById('of_rel_id', 'so_consignee_id', Trans::getWord('consignee'));
        $consigneeAddressField->addClearField('so_pic_consignee');
        $consigneeAddressField->addClearField('so_consignee_cp_id');
        $consigneeAddressField->setDetailReferenceCode('of_id');
        # Create Contact Field
        $picConsigneeField = $this->Field->getSingleSelect('contactPerson', 'so_pic_consignee', $this->getStringParameter('so_pic_consignee'));
        $picConsigneeField->setHiddenField('so_consignee_cp_id', $this->getIntParameter('so_consignee_cp_id'));
        $picConsigneeField->addParameterById('cp_rel_id', 'so_consignee_id', Trans::getWord('consignee'));
        $picConsigneeField->addParameterById('cp_of_id', 'so_consignee_of_id', Trans::getWord('consigneeAddress'));
        $picConsigneeField->setDetailReferenceCode('cp_id');
        # Create Shipper or Consignee Field
        $shipperField = $this->Field->getSingleSelect('relation', 'so_shipper', $this->getStringParameter('so_shipper'));
        $shipperField->setHiddenField('so_shipper_id', $this->getIntParameter('so_shipper_id'));
        $shipperField->addParameter('rel_ss_id', $this->User->getSsId());
        $shipperField->setDetailReferenceCode('rel_id');
        $shipperField->addClearField('so_shipper_address');
        $shipperField->addClearField('so_shipper_of_id');
        $shipperField->addClearField('so_pic_shipper');
        $shipperField->addClearField('so_shipper_cp_id');
        # Create order Office Field
        $shipperAddressField = $this->Field->getSingleSelect('office', 'so_shipper_address', $this->getStringParameter('so_shipper_address'));
        $shipperAddressField->setHiddenField('so_shipper_of_id', $this->getIntParameter('so_shipper_of_id'));
        $shipperAddressField->addParameterById('of_rel_id', 'so_shipper_id', Trans::getWord('shipper'));
        $shipperAddressField->addClearField('so_pic_shipper');
        $shipperAddressField->addClearField('so_shipper_cp_id');
        $shipperAddressField->setDetailReferenceCode('of_id');
        # Create Contact Field
        $picShipperField = $this->Field->getSingleSelect('contactPerson', 'so_pic_shipper', $this->getStringParameter('so_pic_shipper'));
        $picShipperField->setHiddenField('so_shipper_cp_id', $this->getIntParameter('so_shipper_cp_id'));
        $picShipperField->addParameterById('cp_rel_id', 'so_shipper_id', Trans::getWord('shipper'));
        $picShipperField->addParameterById('cp_of_id', 'so_shipper_of_id', Trans::getWord('shipperAddress'));
        $picShipperField->setDetailReferenceCode('cp_id');
        # Create Notify or Consignee Field
        $notifyField = $this->Field->getSingleSelect('relation', 'so_notify', $this->getStringParameter('so_notify'));
        $notifyField->setHiddenField('so_notify_id', $this->getIntParameter('so_notify_id'));
        $notifyField->addParameter('rel_ss_id', $this->User->getSsId());
        $notifyField->setDetailReferenceCode('rel_id');
        $notifyField->addClearField('so_notify_address');
        $notifyField->addClearField('so_notify_of_id');
        $notifyField->addClearField('so_pic_notify');
        $notifyField->addClearField('so_notify_cp_id');
        # Create order Office Field
        $notifyAddressField = $this->Field->getSingleSelect('office', 'so_notify_address', $this->getStringParameter('so_notify_address'));
        $notifyAddressField->setHiddenField('so_notify_of_id', $this->getIntParameter('so_notify_of_id'));
        $notifyAddressField->addParameterById('of_rel_id', 'so_notify_id', Trans::getWord('notifyParty'));
        $notifyAddressField->addClearField('so_pic_notify');
        $notifyAddressField->addClearField('so_notify_cp_id');
        $notifyAddressField->setDetailReferenceCode('of_id');
        # Create Contact Field
        $picNotifyField = $this->Field->getSingleSelect('contactPerson', 'so_pic_notify', $this->getStringParameter('so_pic_notify'));
        $picNotifyField->setHiddenField('so_notify_cp_id', $this->getIntParameter('so_notify_cp_id'));
        $picNotifyField->addParameterById('cp_rel_id', 'so_notify_id', Trans::getWord('notifyParty'));
        $picNotifyField->addParameterById('cp_of_id', 'so_notify_of_id', Trans::getWord('notifyPartyAddress'));
        $picNotifyField->setDetailReferenceCode('cp_id');

        # Create Carrier Field
        $carrierField = $this->Field->getSingleSelect('relation', 'so_carrier', $this->getStringParameter('so_carrier'));
        $carrierField->setHiddenField('so_carrier_id', $this->getIntParameter('so_carrier_id'));
        $carrierField->addParameter('rel_ss_id', $this->User->getSsId());
        $carrierField->setDetailReferenceCode('rel_id');
        $carrierField->addClearField('so_carrier_address');
        $carrierField->addClearField('so_carrier_of_id');
        $carrierField->addClearField('so_pic_carrier');
        $carrierField->addClearField('so_carrier_cp_id');
        # Create order Office Field
        $carrierAddressField = $this->Field->getSingleSelect('office', 'so_carrier_address', $this->getStringParameter('so_carrier_address'));
        $carrierAddressField->setHiddenField('so_carrier_of_id', $this->getIntParameter('so_carrier_of_id'));
        $carrierAddressField->addParameterById('of_rel_id', 'so_carrier_id', Trans::getWord('carrier'));
        $carrierAddressField->addClearField('so_pic_notify');
        $carrierAddressField->addClearField('so_carrier_cp_id');
        $carrierAddressField->setDetailReferenceCode('of_id');
        # Create Contact Field
        $picCarrierField = $this->Field->getSingleSelect('contactPerson', 'so_pic_carrier', $this->getStringParameter('so_pic_carrier'));
        $picCarrierField->setHiddenField('so_carrier_cp_id', $this->getIntParameter('so_carrier_cp_id'));
        $picCarrierField->addParameterById('cp_rel_id', 'so_carrier_id', Trans::getWord('carrier'));
        $picCarrierField->addParameterById('cp_of_id', 'so_carrier_of_id', Trans::getWord('carrierAddress'));
        $picCarrierField->setDetailReferenceCode('cp_id');

        # Create Transport Module Field# Add field to fieldset
        $fieldSet->addField(Trans::getWord('consignee'), $consigneeField);
        $fieldSet->addField(Trans::getWord('consigneeAddress'), $consigneeAddressField);
        $fieldSet->addField(Trans::getWord('picConsignee'), $picConsigneeField);
        $fieldSet->addField(Trans::getWord('shipper'), $shipperField);
        $fieldSet->addField(Trans::getWord('shipperAddress'), $shipperAddressField);
        $fieldSet->addField(Trans::getWord('picShipper'), $picShipperField);
        $fieldSet->addField(Trans::getWord('carrier'), $carrierField);
        $fieldSet->addField(Trans::getWord('carrierAddress'), $carrierAddressField);
        $fieldSet->addField(Trans::getWord('picCarrier'), $picCarrierField);
        $fieldSet->addField(Trans::getWord('notifyParty'), $notifyField);
        $fieldSet->addField(Trans::getWord('notifyPartyAddress'), $notifyAddressField);
        $fieldSet->addField(Trans::getWord('picNotifyParty'), $picNotifyField);
        # Create a portlet box.
        $portlet = new Portlet('SoRelPtl', Trans::getWord('relation'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the detail portlet.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        # Create Transport Module Field
        $tmField = $this->Field->getSingleSelect('transportModule', 'so_transport_module', $this->getStringParameter('so_transport_module'));
        $tmField->setHiddenField('so_tm_id', $this->getIntParameter('so_tm_id'));
        $tmField->setEnableNewButton(false);
        # Custom Clearance Type
        $cctField = $this->Field->getSelect('so_cct_id', $this->getIntParameter('so_cct_id'));
        $cctData = CustomsClearanceTypeDao::loadActiveData();
        $cctField->addOptions($cctData, 'cct_name', 'cct_id');
        # Customs Document Type
        $cdtField = $this->Field->getSingleSelect('customsDocumentType', 'so_document_type', $this->getStringParameter('so_document_type'));
        $cdtField->setHiddenField('so_cdt_id', $this->getIntParameter('so_cdt_id'));
        $cdtField->setEnableDetailButton(false);
        $cdtField->setEnableNewButton(false);
        # Create Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'so_warehouse_name', $this->getStringParameter('so_warehouse_name'));
        $whField->setHiddenField('so_wh_id', $this->getIntParameter('so_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        # Create Equipment Field
        $eqField = $this->Field->getText('so_transport_name', $this->getStringParameter('so_transport_name'));

        # Create Transport Module Field# Add field to fieldset
        if ($this->isInklaring() === true) {
            $fieldSet->addField(Trans::getWord('plb'), $this->Field->getYesNo('so_plb', $this->getStringParameter('so_plb')));
            $fieldSet->addField(Trans::getWord('documentType'), $cdtField);
            $fieldSet->addField(Trans::getWord('transportModule'), $tmField);
            $fieldSet->addField(Trans::getWord('transportName'), $eqField);
            $fieldSet->addField(Trans::getWord('transportNumber'), $this->Field->getText('so_transport_number', $this->getStringParameter('so_transport_number')));
            $fieldSet->addField(Trans::getWord('lineStatus'), $cctField);
        }
        if ($this->isWarehouse() === true || $this->isInklaring() === true) {
            $fieldSet->addField(Trans::getWord('warehouseName'), $whField);
        }
        # Create a portlet box.
        $portlet = new Portlet('SoDetailPtl', Trans::getWord('jobDetail'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the port portlet.
     *
     * @return Portlet
     */
    private function getPortPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        # Create port Field
        $polField = $this->Field->getSingleSelect('port', 'so_pol', $this->getStringParameter('so_pol'));
        $polField->setHiddenField('so_pol_id', $this->getIntParameter('so_pol_id'));
        $polField->setEnableNewButton(false);
        $polField->addOptionalParameterById('po_tm_id', 'so_tm_id');
        $polField->setAutoCompleteFields(['so_pol_country' => 'po_country']);
        # Port of Destination
        $podField = $this->Field->getSingleSelect('port', 'so_pod', $this->getStringParameter('so_pod'));
        $podField->setHiddenField('so_pod_id', $this->getIntParameter('so_pod_id'));
        $podField->setEnableNewButton(false);
        $podField->addOptionalParameterById('po_tm_id', 'so_tm_id');
        $podField->setAutoCompleteFields(['so_pod_country' => 'po_country']);

        $polCountry = $this->Field->getText('so_pol_country', $this->getStringParameter('so_pol_country'));
        $polCountry->setReadOnly();
        $podCountry = $this->Field->getText('so_pod_country', $this->getStringParameter('so_pod_country'));
        $podCountry->setReadOnly();
        # Create Transport Module Field# Add field to fieldset
        $fieldSet->addField(Trans::getWord('portOfLoading'), $polField);
        $fieldSet->addField(Trans::getWord('etdDate'), $this->Field->getCalendar('so_departure_date', $this->getStringParameter('so_departure_date')));
        $fieldSet->addField(Trans::getWord('portOfDestination'), $podField);
        $fieldSet->addField(Trans::getWord('etaDate'), $this->Field->getCalendar('so_arrival_date', $this->getStringParameter('so_arrival_date')));
        $fieldSet->addField(Trans::getWord('polCountry'), $polCountry);
        $fieldSet->addField(Trans::getWord('etdTime'), $this->Field->getTime('so_departure_time', $this->getStringParameter('so_departure_time')));
        $fieldSet->addField(Trans::getWord('podCountry'), $podCountry);
        $fieldSet->addField(Trans::getWord('etaTime'), $this->Field->getTime('so_arrival_time', $this->getStringParameter('so_arrival_time')));
        $fieldSet->addField(Trans::getWord('atdDate'), $this->Field->getCalendar('so_atd_date', $this->getStringParameter('so_atd_date')));
        $fieldSet->addField(Trans::getWord('atdTime'), $this->Field->getTime('so_atd_time', $this->getStringParameter('so_atd_time')));
        $fieldSet->addField(Trans::getWord('ataDate'), $this->Field->getCalendar('so_ata_date', $this->getStringParameter('so_ata_date')));
        $fieldSet->addField(Trans::getWord('ataTime'), $this->Field->getTime('so_ata_time', $this->getStringParameter('so_ata_time')));
        # Create a portlet box.
        $portlet = new Portlet('SoPortPtl', Trans::getWord('port'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the Depo Portlet.
     *
     * @return Portlet
     */
    private function getDepoPortlet(): Portlet
    {
        $title = Trans::getWord('depoAndCyContainer');
        $ypOwnerLabel = Trans::getWord('ownerCyPickUp');
        $ypLabel = Trans::getWord('cyPickUp');
        $yrOwnerLabel = Trans::getWord('ownerCyDestination');
        $yrLabel = Trans::getWord('cyDestination');
        if ($this->isContainer() === false) {
            $title = Trans::getWord('warehousePort');
            $ypOwnerLabel = Trans::getWord('ownerWarehousePickUp');
            $ypLabel = Trans::getWord('warehousePickUp');
            $yrOwnerLabel = Trans::getWord('ownerWarehouseDestination');
            $yrLabel = Trans::getWord('warehouseDestination');
        }
        # Instantiate Portlet Object
        $portlet = new Portlet('SoDepoPtl', $title);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        # Create Depo Pickup Owner
        $dpOwnerField = $this->Field->getSingleSelect('relation', 'so_dp_owner', $this->getStringParameter('so_dp_owner'));
        $dpOwnerField->setHiddenField('so_dp_rel_id', $this->getIntParameter('so_dp_rel_id'));
        $dpOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $dpOwnerField->setDetailReferenceCode('rel_id');
        $dpOwnerField->addClearField('so_dp_id');
        $dpOwnerField->addClearField('so_dp_name');
        # Create depo pickup
        $dpField = $this->Field->getSingleSelect('office', 'so_dp_name', $this->getStringParameter('so_dp_name'));
        $dpField->setHiddenField('so_dp_id', $this->getIntParameter('so_dp_id'));
        $dpField->addParameterById('of_rel_id', 'so_dp_rel_id', Trans::getWord('ownerDepoPickUp'));
        $dpField->setDetailReferenceCode('of_id');

        # Create Depo Return Owner
        $drOwnerField = $this->Field->getSingleSelect('relation', 'so_dr_owner', $this->getStringParameter('so_dr_owner'));
        $drOwnerField->setHiddenField('so_dr_rel_id', $this->getIntParameter('so_dr_rel_id'));
        $drOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $drOwnerField->setDetailReferenceCode('rel_id');
        $drOwnerField->addClearField('so_dr_id');
        $drOwnerField->addClearField('so_dr_name');
        # Create depo return
        $drField = $this->Field->getSingleSelect('office', 'so_dr_name', $this->getStringParameter('so_dr_name'));
        $drField->setHiddenField('so_dr_id', $this->getIntParameter('so_dr_id'));
        $drField->addParameterById('of_rel_id', 'so_dr_rel_id', Trans::getWord('ownerDepoReturn'));
        $drField->setDetailReferenceCode('of_id');

        # Create yard Pickup Owner
        $ypOwnerField = $this->Field->getSingleSelect('relation', 'so_yp_owner', $this->getStringParameter('so_yp_owner'));
        $ypOwnerField->setHiddenField('so_yp_rel_id', $this->getIntParameter('so_yp_rel_id'));
        $ypOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $ypOwnerField->setDetailReferenceCode('rel_id');
        $ypOwnerField->addClearField('so_yp_id');
        $ypOwnerField->addClearField('so_yp_name');
        # Create yard pickup
        $ypField = $this->Field->getSingleSelect('office', 'so_yp_name', $this->getStringParameter('so_yp_name'));
        $ypField->setHiddenField('so_yp_id', $this->getIntParameter('so_yp_id'));
        $ypField->addParameterById('of_rel_id', 'so_yp_rel_id', $ypOwnerLabel);
        $ypField->setDetailReferenceCode('of_id');

        # Create Yard Return Owner
        $yrOwnerField = $this->Field->getSingleSelect('relation', 'so_yr_owner', $this->getStringParameter('so_yr_owner'));
        $yrOwnerField->setHiddenField('so_yr_rel_id', $this->getIntParameter('so_yr_rel_id'));
        $yrOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $yrOwnerField->setDetailReferenceCode('rel_id');
        $yrOwnerField->addClearField('so_yr_id');
        $yrOwnerField->addClearField('so_yr_name');
        # Create yard return
        $yrField = $this->Field->getSingleSelect('office', 'so_yr_name', $this->getStringParameter('so_yr_name'));
        $yrField->setHiddenField('so_yr_id', $this->getIntParameter('so_yr_id'));
        $yrField->addParameterById('of_rel_id', 'so_yr_rel_id', $yrOwnerLabel);
        $yrField->setDetailReferenceCode('of_id');

        # Add field to field set
        if ($this->isLoad() === true) {
            if ($this->isContainer() === true) {
                $fieldSet->addField(Trans::getWord('ownerDepoPickUp'), $dpOwnerField);
                $fieldSet->addField(Trans::getWord('depoPickUp'), $dpField);
            }
            $fieldSet->addField($yrOwnerLabel, $yrOwnerField);
            $fieldSet->addField($yrLabel, $yrField);
        }
        if ($this->isUnload() === true) {
            $fieldSet->addField($ypOwnerLabel, $ypOwnerField);
            $fieldSet->addField($ypLabel, $ypField);
            if ($this->isContainer() === true) {
                $fieldSet->addField(Trans::getWord('ownerDepoReturn'), $drOwnerField);
                $fieldSet->addField(Trans::getWord('depoReturn'), $drField);
            }
        }

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to load sales order container data.
     *
     * @return void
     */
    private function loadSalesOrderContainerData(): void
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getDetailReferenceValue());
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $this->SocData = SalesOrderContainerDao::loadData($wheres);
    }

    /**
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
        $allowUpdate = false;
        if ($this->IsJobDeliveryExist === false) {
            $allowUpdate = $this->isAllowUpdate();
        }
        $modal = $this->getContainerModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getContainerDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new Table('SoSocTbl');
        $header = [
            'soc_number' => Trans::getWord('id'),
        ];
        if ($this->isDelivery() === true) {
            $header['soc_equipment_group'] = Trans::getWord('truckType');
        }
        if ($this->isContainer() === true) {
            $header = array_merge($header, [
                'soc_container_type' => Trans::getWord('containerType'),
                'soc_container_number' => Trans::getWord('containerNumber'),
                'soc_seal_number' => Trans::getWord('sealNumber'),
            ]);
        }
        $table->setHeaderRow($header);
        if ($allowUpdate === true) {
            $table->addColumnAtTheEnd('soc_action', Trans::getWord('action'));
            $table->addColumnAttribute('soc_action', 'style', 'text-align: center;');
        }
        $rows = [];
        foreach ($this->SocData as $row) {
            if ($allowUpdate === true) {
                $row['soc_action'] = '';
                if ($this->isMultiLoadUnload() === false) {
                    $btnCp = new ModalButton('BtnSocCp' . $row['soc_id'], '', $modal->getModalId());
                    $btnCp->viewIconOnly()->btnDark()->setIcon(Icon::Copy);
                    $btnCp->setEnableCallBack('soc', 'getByIdForCopy');
                    $btnCp->addParameter('soc_id', $row['soc_id']);
                    $row['soc_action'] = $btnCp . ' ';
                }

                $btnUpdate = new ModalButton('BtnSocUp' . $row['soc_id'], '', $modal->getModalId());
                $btnUpdate->viewIconOnly()->btnPrimary()->setIcon(Icon::Pencil);
                $btnUpdate->setEnableCallBack('soc', 'getById');
                $btnUpdate->addParameter('soc_id', $row['soc_id']);
                $row['soc_action'] .= $btnUpdate;
                if ((int)$row['total_delivery'] === 0) {
                    $btnDel = new ModalButton('BtnSocDl' . $row['soc_id'], '', $modalDelete->getModalId());
                    $btnDel->viewIconOnly()->btnDanger()->setIcon(Icon::Trash);
                    $btnDel->setEnableCallBack('soc', 'getByIdForDelete');
                    $btnDel->addParameter('soc_id', $row['soc_id']);
                    $row['soc_action'] .= ' ' . $btnDel;
                }
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('SoSocPtl', Trans::getWord('party'));
        # add new button
        if (($this->isMultiLoadUnload() === false || empty($this->SocData) === true) && $allowUpdate === true) {
            $btnCpMdl = new ModalButton('btnSocMdl', Trans::getWord('addParty'), $modal->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }
        $portlet->addTable($table);

        return $portlet;

    }

    /**
     * Function to get Goods modal.
     *
     * @return Modal
     */
    private function getContainerModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSocMdl', Trans::getWord('party'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateContainer');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateContainer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Create Equipment group Field
        $egField = $this->Field->getSingleSelect('eg', 'soc_equipment_group', $this->getParameterForModal('soc_equipment_group', $showModal));
        $egField->setHiddenField('soc_eg_id', $this->getParameterForModal('soc_eg_id', $showModal));
        $egField->addParameter('eg_tm_code', 'road');
        $egField->addParameter('eg_container', $this->getStringParameter('so_container'));
        $egField->setEnableNewButton(false);

        # Create Unit Field
        $ctField = $this->Field->getSingleSelect('container', 'soc_container_type', $this->getParameterForModal('soc_container_type', $showModal));
        $ctField->setHiddenField('soc_ct_id', $this->getParameterForModal('soc_ct_id', $showModal));
        $ctField->setEnableNewButton(false);
        $ctField->setEnableDetailButton(false);

        $numberField = $this->Field->getText('soc_container_number', $this->getParameterForModal('soc_container_number', $showModal));
        $sealField = $this->Field->getText('soc_seal_number', $this->getParameterForModal('soc_seal_number', $showModal));
        # Add field into field set.
        if ($this->isDelivery() === true) {
            $fieldSet->addField(Trans::getWord('truckType'), $egField, true);
        }
        $containerRequired = false;
        if ($this->isContainer() === true) {
            $containerRequired = true;
        } else {
            $ctField->setReadOnly();
            $numberField->setReadOnly();
            $sealField->setReadOnly();
        }
        $fieldSet->addField(Trans::getWord('containerType'), $ctField, $containerRequired);
        $fieldSet->addField(Trans::getWord('containerNumber'), $numberField);
        $fieldSet->addField(Trans::getWord('sealNumber'), $sealField);
        $fieldSet->addHiddenField($this->Field->getHidden('soc_id', $this->getParameterForModal('soc_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    private function getContainerDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSocDelMdl', Trans::getWord('containerDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteContainer');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteContainer' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add field into field set.
        if ($this->isDelivery() === true) {
            $fieldSet->addField(Trans::getWord('truckType'), $this->Field->getText('soc_equipment_group_del', $this->getParameterForModal('soc_equipment_group_del', $showModal)));
        }
        $fieldSet->addField(Trans::getWord('containerType'), $this->Field->getText('soc_container_type_del', $this->getParameterForModal('soc_container_type_del', $showModal)));
        $fieldSet->addField(Trans::getWord('containerNumber'), $this->Field->getText('soc_container_number_del', $this->getParameterForModal('soc_container_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('sealNumber'), $this->Field->getText('soc_seal_number_del', $this->getParameterForModal('soc_seal_number', $showModal)));

        $fieldSet->addHiddenField($this->Field->getHidden('soc_id_del', $this->getParameterForModal('soc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getTruckingWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    private function getGoodsFieldSet(): Portlet
    {
        $table = new Table('SoSogTbl');
        $table->setHeaderRow([
            'sog_number' => Trans::getWord('number'),
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
        if ($this->isContainer() === true) {
            $table->addColumnAfter('sog_number', 'sog_container', Trans::getWord('container'));
        }
        if ($this->isDelivery() === true) {
            $table->addColumnAfter('sog_number', 'sog_equipment_group', Trans::getWord('truckType'));
        }


        $table->addRows($this->SogData);
        $portlet = new Portlet('SoSogPtl', Trans::getWord('goods'));
        if ($this->isAllowUpdate() === true && $this->IsJobDeliveryExist === false) {
            # Create a portlet box.
            $modal = $this->getGoodsModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getGoodsDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setUpdateActionByModal($modal, 'sog', 'getById', ['sog_id']);
            $table->setCopyActionByModal($modal, 'sog', 'getByIdForCopy', ['sog_id']);
            $table->setDeleteActionByModal($modalDelete, 'sog', 'getByIdForDelete', ['sog_id']);
            # add new button
            $btnSogMdl = new ModalButton('btnJoJogMdl', Trans::getTruckingWord('addGoods'), $modal->getModalId());
            $btnSogMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnSogMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare goods data.
     *
     * @return void
     */
    private function loadSalesOrderGoodsData(): void
    {
        $this->SogData = [];
        $data = SalesOrderGoodsDao::getBySoId($this->getDetailReferenceValue());
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
            $containers = [
//                [
//                    'label' => Trans::getWord('id'),
//                    'value' => $row['sog_soc_number'],
//                ],
                [
                    'label' => Trans::getWord('type'),
                    'value' => $row['sog_container_type'],
                ],
                [
                    'label' => Trans::getWord('number'),
                    'value' => $row['sog_container_number'],
                ],
                [
                    'label' => Trans::getWord('seal'),
                    'value' => $row['sog_seal_number'],
                ],
            ];
            $row['sog_container'] = StringFormatter::generateKeyValueTableView($containers);

            $this->SogData[] = $row;
        }
    }

    /**
     * Function to do load loading address.
     *
     * @param string $type To store the type of addresss.
     * @return array
     */
    private function loadLoadUnloadData(string $type): array
    {
        $data = SalesOrderDeliveryDao::getBySoIdAndType($this->getDetailReferenceValue(), $type);
        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['sdl_quantity'] = $number->doFormatFloat($row['sdl_quantity']);
            if (empty($row['sdl_uom']) === false) {
                $row['sdl_quantity'] .= ' ' . $row['sdl_uom'];
            }
            if (empty($row['sdl_sog_hs_code']) === false) {
                $row['sdl_sog_name'] = $row['sdl_sog_hs_code'] . ' - ' . $row['sdl_sog_name'];
            }
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to get Goods modal.
     *
     * @return Modal
     */
    private function getGoodsModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSogMdl', Trans::getWord('goodsDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Unit Field
        $label = Trans::getWord('container');
        if ($this->isContainer() === true) {
            $ctField = $this->Field->getSingleSelect('soc', 'sog_container_type', $this->getParameterForModal('sog_container_type', $showModal));
        } else {
            $label = Trans::getWord('truckType');
            $ctField = $this->Field->getSingleSelect('soc', 'sog_equipment_group', $this->getParameterForModal('sog_equipment_group', $showModal), 'loadSingleSelectTruckData');
        }
        $ctField->setHiddenField('sog_soc_id', $this->getParameterForModal('sog_soc_id', $showModal));
        $ctField->addParameter('soc_so_id', $this->getDetailReferenceValue());
        $ctField->setEnableNewButton(false);
        $ctField->setEnableDetailButton(false);
        $requiredContainer = false;
        if ($this->isDelivery() === true || $this->isContainer() === true) {
            $requiredContainer = true;
        } else {
            $ctField->setReadOnly();
        }
        # Unit field
        $unitField = $this->Field->getSingleSelect('unit', 'sog_uom', $this->getParameterForModal('sog_uom', $showModal));
        $unitField->setHiddenField('sog_uom_id', $this->getParameterForModal('sog_uom_id', $showModal));
        $unitField->setEnableDetailButton(false);
        $unitField->setEnableNewButton(false);
        # Create Dimension Type Field
        $dimensionType = $this->Field->getSelect('sog_dimension_unit', $this->getParameterForModal('sog_dimension_unit', $showModal));
        $dimensionType->addOption(Trans::getWord('perUnit'), 'Y');
        $dimensionType->addOption(Trans::getWord('total'), 'N');

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('hsCode'), $this->Field->getText('sog_hs_code', $this->getParameterForModal('sog_hs_code', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sog_name', $this->getParameterForModal('sog_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('sog_quantity', $this->getParameterForModal('sog_quantity', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('packingRef'), $this->Field->getText('sog_packing_ref', $this->getParameterForModal('sog_packing_ref', $showModal)));
        if ($this->isConsolidate() === false) {
            $fieldSet->addField($label, $ctField, $requiredContainer);
        }
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('sog_length', $this->getParameterForModal('sog_length', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('sog_width', $this->getParameterForModal('sog_width', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('sog_height', $this->getParameterForModal('sog_height', $showModal)));
        $fieldSet->addField(Trans::getWord('grossWeight') . ' (KG)', $this->Field->getNumber('sog_gross_weight', $this->getParameterForModal('sog_gross_weight', $showModal)));
        $fieldSet->addField(Trans::getWord('netWeight') . ' (KG)', $this->Field->getNumber('sog_net_weight', $this->getParameterForModal('sog_net_weight', $showModal)));
        $fieldSet->addField(Trans::getWord('dimensionType'), $dimensionType);
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('sog_notes', $this->getParameterForModal('sog_notes', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sog_id', $this->getParameterForModal('sog_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    private function getGoodsDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoSogDelMdl', Trans::getWord('goodsDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('hsCode'), $this->Field->getText('sog_hs_code_del', $this->getParameterForModal('sog_hs_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sog_name_del', $this->getParameterForModal('sog_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('packingRef'), $this->Field->getText('sog_packing_ref_del', $this->getParameterForModal('sog_packing_ref_del', $showModal)));
        if ($this->isContainer() === true) {
            $fieldSet->addField(Trans::getWord('containerType'), $this->Field->getText('sog_container_type_del', $this->getParameterForModal('sog_container_type_del', $showModal)));
            $fieldSet->addField(Trans::getWord('containerId'), $this->Field->getText('sog_container_id_del', $this->getParameterForModal('sog_container_id_del', $showModal)));
            $fieldSet->addField(Trans::getWord('containerNumber'), $this->Field->getText('sog_container_number_del', $this->getParameterForModal('sog_container_number_del', $showModal)));
        }
        if ($this->isContainer() === false && $this->isDelivery() === true) {
            $fieldSet->addField(Trans::getWord('truckType'), $this->Field->getText('sog_equipment_group_del', $this->getParameterForModal('sog_equipment_group_del', $showModal)));
        }
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('sog_quantity_del', $this->getParameterForModal('sog_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('sog_uom_del', $this->getParameterForModal('sog_uom_del', $showModal)));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('sog_length_del', $this->getParameterForModal('sog_length_del', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('sog_width_del', $this->getParameterForModal('sog_width_del', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('sog_height_del', $this->getParameterForModal('sog_height_del', $showModal)));
        $fieldSet->addField(Trans::getWord('grossWeight') . ' (KG)', $this->Field->getNumber('sog_gross_weight_del', $this->getParameterForModal('sog_gross_weight_del', $showModal)));
        $fieldSet->addField(Trans::getWord('netWeight') . ' (KG)', $this->Field->getNumber('sog_net_weight_del', $this->getParameterForModal('sog_net_weight_del', $showModal)));
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('sog_notes_del', $this->getParameterForModal('sog_notes_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sog_id_del', $this->getParameterForModal('sog_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getTruckingWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the notes Field Set.
     *
     * @return Portlet
     */
    private function getNotesPortlet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('notes'), $this->Field->getTextArea('so_notes', $this->getStringParameter('so_notes')));
        # Create a portlet box.
        $portlet = new Portlet('SoNotesPtl', Trans::getWord('notes'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getReviewPortlet(): Portlet
    {
        $table = new Table('CsInTbl');
        $table->setHeaderRow([
            'soi_number' => Trans::getWord('number'),
            'soi_srv_name' => Trans::getWord('service'),
            'soi_sty_name' => Trans::getWord('priority'),
            'soi_finish_on' => Trans::getWord('status'),
            'soi_action' => Trans::getWord('action'),
        ]);

        $table->addRows($this->doPrepareData($this->loadSoiData()));
        $table->addColumnAttribute('soi_sty_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('soi_finish_on', 'style', 'text-align: center;');
        $table->addColumnAttribute('soi_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SoSoiPtl', Trans::getWord('issues'));
        # Show Review Button
        if ($this->isSoPublished() === true && $this->isSoDeleted() === false) {
            $btnIssue = new HyperLink('btnSoi', Trans::getWord('issue'), url('soi/detail?so_id=' . $this->getIntParameter('so_id')));
            $btnIssue->viewAsButton();
            $btnIssue->setIcon(Icon::Comments)->btnInfo()->pullRight()->btnMedium();
            $portlet->addButton($btnIssue);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadSoiData(): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = '(soi.soi_so_id = ' . $this->getDetailReferenceValue() . ')';
        $data = SalesOrderIssueDao::loadData($wheres);
        foreach ($data as $row) {
            $btnView = new HyperLink('hplSoi', '', url('soi/view?soi_id=' . $row['soi_id']));
            $btnView->viewAsButton();
            $btnView->setIcon(Icon::Eye)->btnSuccess()->btnMedium();
            $row['soi_action'] = $btnView;
            $results[] = $row;
        }

        return $results;
    }


    /**
     * Function to get Goods modal.
     *
     * @param string $type To store the type of location
     * @return Modal
     */
    private function getLoadUnloadModal(string $type): Modal
    {
        $title = Trans::getWord('originAddress');
        $action = 'doUpdateLoadAddress';
        if ($type === 'D') {
            $title = Trans::getWord('destinationAddress');
            $action = 'doUpdateUnloadAddress';
        }
        # Create Fields.
        $modal = new Modal('SoSdlMdl' . $type, $title);
        $modal->setFormSubmit($this->getMainFormId(), $action);
        $showModal = false;
        if ($this->getFormAction() === $action && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $postFix = strtolower($type);
        # Create Relation
        $relField = $this->Field->getSingleSelect('relation', 'sdl_relation' . $postFix, $this->getParameterForModal('sdl_relation' . $postFix, $showModal));
        $relField->setHiddenField('sdl_rel_id' . $postFix, $this->getParameterForModal('sdl_rel_id' . $postFix, $showModal));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('sdl_address' . $postFix);
        $relField->addClearField('sdl_of_id' . $postFix);
        $relField->addClearField('sdl_pic' . $postFix);
        $relField->addClearField('sdl_pic_id' . $postFix);

        # Create office
        $ofField = $this->Field->getSingleSelect('office', 'sdl_office' . $postFix, $this->getParameterForModal('sdl_office' . $postFix, $showModal), 'loadOfficeAddress');
        $ofField->setHiddenField('sdl_of_id' . $postFix, $this->getParameterForModal('sdl_of_id' . $postFix, $showModal));
        $ofField->addParameterById('of_rel_id', 'sdl_rel_id' . $postFix, Trans::getWord('relation'));
        $ofField->setDetailReferenceCode('of_id');
        $ofField->addClearField('sdl_pic' . $postFix);
        $ofField->addClearField('sdl_pic_id' . $postFix);

        # Create pic
        $picField = $this->Field->getSingleSelect('contactPerson', 'sdl_pic' . $postFix, $this->getParameterForModal('sdl_pic' . $postFix, $showModal));
        $picField->setHiddenField('sdl_pic_id' . $postFix, $this->getParameterForModal('sdl_pic_id' . $postFix, $showModal));
        $picField->addParameterById('cp_of_id', 'sdl_of_id' . $postFix, Trans::getWord('address'));
        $picField->setDetailReferenceCode('cp_id');

        # Create Goods Field
        $sogField = $this->Field->getSingleSelectTable('sog', 'sdl_sog_name' . $postFix, $this->getParameterForModal('sdl_sog_name' . $postFix, $showModal));
        $sogField->setHiddenField('sdl_sog_id' . $postFix, $this->getParameterForModal('sdl_sog_id' . $postFix, $showModal));
        $sogField->setTableColumns([
            'sog_hs_code' => Trans::getWord('hsCode'),
            'sog_name' => Trans::getWord('description'),
            'sog_packing_ref' => Trans::getWord('packingRef'),
            'sog_quantity_number' => Trans::getWord('quantity'),
            'sog_uom' => Trans::getWord('uom')
        ]);
        $sogField->setAutoCompleteFields([
            'sdl_quantity' . $postFix => 'sog_quantity',
            'sdl_quantity_number' . $postFix => 'sog_quantity_number',
            'sdl_uom' . $postFix => 'sog_uom',
        ]);
        $sogField->setValueCode('sog_id');
        $sogField->setLabelCode('sog_name');
        $sogField->addParameter('sog_so_id', $this->getDetailReferenceValue());
        $sogField->setParentModal($modal->getModalId());
        $this->View->addModal($sogField->getModal());

        $uomField = $this->Field->getText('sdl_uom' . $postFix, $this->getParameterForModal('sdl_uom' . $postFix, $showModal));
        $uomField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('address'), $ofField, true);
        $fieldSet->addField(Trans::getWord('pic'), $picField);
        if (($type === 'O' && $this->isMultiLoad() === true) || ($type === 'D' && $this->isMultiUnload() === true)) {
            $fieldSet->addField(Trans::getWord('goods'), $sogField);
            $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('sdl_quantity' . $postFix, $this->getParameterForModal('sdl_quantity' . $postFix, $showModal)));
            $fieldSet->addField(Trans::getWord('uom'), $uomField);
        }
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('sdl_reference' . $postFix, $this->getParameterForModal('sdl_reference' . $postFix, $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sdl_id' . $postFix, $this->getParameterForModal('sdl_id' . $postFix, $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    private function getLoadUnloadDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SdlDelMdl', Trans::getWord('deleteAddress'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDelivery');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDelivery' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getText('sdl_relation_del', $this->getParameterForModal('sdl_relation_del', $showModal)));
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('sdl_address_del', $this->getParameterForModal('sdl_address_del', $showModal)));
        $fieldSet->addField(Trans::getWord('pic'), $this->Field->getText('sdl_pic_del', $this->getParameterForModal('sdl_pic_del', $showModal)));
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('sdl_reference_del', $this->getParameterForModal('sdl_reference_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('sdl_goods_del', $this->getParameterForModal('sdl_goods_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('sdl_quantity_del', $this->getParameterForModal('sdl_quantity_del', $showModal)));

        $fieldSet->addHiddenField($this->Field->getHidden('sdl_id_del', $this->getParameterForModal('sdl_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the Load unload portlet.
     *
     * @param string $type To store the type location, is it O or D.
     * @param Modal $deleteModal To store the id modal for adding address.
     *
     *
     * @return Portlet
     */
    private function getLoadUnloadPortlet(string $type, Modal $deleteModal): Portlet
    {
        # Table
        $table = new Table('SoSdlTbl' . $type);
        $table->setHeaderRow([
            'sdl_relation' => Trans::getWord('relation'),
            'sdl_address' => Trans::getWord('address'),
            'sdl_pic' => Trans::getWord('pic'),
            'sdl_reference' => Trans::getWord('reference')
        ]);

        $title = Trans::getWord('originAddress');
        if ($type === 'O') {
            $table->addRows($this->LoadingAddress);
        } else {
            $table->addRows($this->UnloadAddress);
            $title = Trans::getWord('destinationAddress');
        }
        # Create a portlet box.
        $portlet = new Portlet('SdlPtl' . $type, $title);
        $addModal = $this->getLoadUnloadModal($type);
        $this->View->addModal($addModal);

        if (($type === 'O' && ($this->isMultiLoad() === true || empty($this->LoadingAddress) === true)) || ($type === 'D' && ($this->isMultiUnload() === true || empty($this->UnloadAddress) === true))) {
            $table->addColumnAfter('sdl_reference', 'sdl_sog_name', Trans::getWord('goods'));
            $table->addColumnAfter('sdl_sog_name', 'sdl_quantity', Trans::getWord('quantity'));
            # add new button
            if ($this->isAllowUpdate() === true && $this->IsJobDeliveryExist === false) {
                $table->setCopyActionByModal($addModal, 'sdl', 'getByIdForCopy', ['sdl_id']);
                $btnCpMdl = new ModalButton('btnSdlMdl' . $type, Trans::getWord('addAddress'), $addModal->getModalId());
                $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
                $portlet->addButton($btnCpMdl);
            }

        }
        if ($this->isAllowUpdate() === true && $this->IsJobDeliveryExist === false) {
            $table->setUpdateActionByModal($addModal, 'sdl', 'getByIdForUpdate', ['sdl_id']);
            $table->setDeleteActionByModal($deleteModal, 'sdl', 'getByIdForDelete', ['sdl_id']);
        }
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getJobPortlet(): Portlet
    {
        $table = new Table('SoJoTbl');
        $table->setHeaderRow([
            'jo_number' => Trans::getWord('number'),
            'jo_service' => Trans::getWord('service'),
            'jo_service_term' => Trans::getWord('terms'),
            'jo_vendor' => Trans::getWord('vendor'),
            'jo_manager' => Trans::getWord('manager'),
            'jo_status' => Trans::getWord('status'),
            'jo_action' => Trans::getWord('action'),
        ]);

        $table->addRows($this->JobOrders);
        $table->addColumnAttribute('jo_status', 'style', 'text-align: center;');
        $table->addColumnAttribute('jo_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SoJoPtl', Trans::getWord('jobOrder'));
        if ($this->isAllowUpdate() === true) {
            if ($this->isWarehouse() === true) {
                $modal = $this->getWarehouseModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnJwSo', Trans::getWord('createWarehouse'), $modal->getModalId());
                $btnDel->setIcon(Icon::Plus)->btnDark()->pullRight()->btnMedium();
                $portlet->addButton($btnDel);
            }
            if ($this->isDelivery() === true && $this->isConsolidate() === false) {
                $modal = $this->getDeliveryModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnJdlSo', Trans::getWord('createDelivery'), $modal->getModalId());
                $btnDel->setIcon(Icon::Plus)->btnSuccess()->pullRight()->btnMedium();
                $portlet->addButton($btnDel);
            }
            if ($this->isInklaring() === true) {
                $modal = $this->getInklaringModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnJikSo', Trans::getWord('createInklaring'), $modal->getModalId());
                $btnDel->setIcon(Icon::Plus)->btnPrimary()->pullRight()->btnMedium();
                $portlet->addButton($btnDel);
            }
        }
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    private function loadJobOrderData(): void
    {
        $data = JobOrderDao::loadDataBySoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
        $this->JobOrders = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            if ($row['jo_srv_code'] === 'delivery') {
                $this->IsJobDeliveryExist = true;
            } elseif ($row['jo_srv_code'] === 'warehouse') {
                $this->IsJobWarehouseExist = true;
            } elseif ($row['jo_srv_code'] === 'inklaring') {
                $this->IsJobInklaringExist = true;
            }
            $row['jo_status'] = $joDao->generateStatus([
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_hold' => !empty($row['joh_id']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $url = '/' . $row['jo_srt_route'] . '/detail?jo_id=' . $row['jo_id'];
            $btnUpdate = new HyperLink('btnJoEd' . $row['jo_id'], '', url($url));
            $btnUpdate->viewAsButton();
            $btnUpdate->setIcon(Icon::Pencil)->btnMedium()->btnPrimary()->viewIconOnly();
            $row['jo_action'] = $btnUpdate;
            $this->JobOrders[] = $row;
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true && $this->isSoDeleted() === false) {
            if ($this->isSoPublished() === false) {
                if ($this->PageSetting->checkPageRight('AllowPublish') === true) {
                    $modal = $this->getSoPublishModal();
                    $this->View->addModal($modal);
                    $btnDel = new ModalButton('btnPubSo', Trans::getWord('publish'), $modal->getModalId());
                    $btnDel->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                    $this->View->addButton($btnDel);
                }
            } else {
                # Show Button finish
                if ($this->isAllowFinish() === true) {
                    $modal = $this->getSoFinishModal();
                    $this->View->addModal($modal);
                    $btnComplete = new ModalButton('btnFinishSo', Trans::getWord('finish'), $modal->getModalId());
                    $btnComplete->setIcon(Icon::CheckSquareO)->btnSuccess()->pullRight()->btnMedium();
                    $this->View->addButton($btnComplete);
                }
                # Show Button Hold
                if ($this->isSoHold()) {
                    $this->setEnableUnHoldButton();
                } else {
                    $this->setEnableHoldButton();
                }
            }
            $this->setEnableDeleteButton($this->isAllowDelete());
        }
        $this->setEnableViewButton();
        parent::loadDefaultButton();
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getSoPublishModal(): Modal
    {

        # Create Fields.
        $modal = new Modal('SoPubMdl', Trans::getWord('publishConfirmation'));
        $documentMessage = $this->checkPublishRequiredDocument();
        if (empty($this->SogData) === true) {
            $p = new Paragraph(Trans::getMessageWord('emptySogData'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } elseif (empty($documentMessage) === false) {
            $modal->setTitle(Trans::getWord('missingDocument'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $modal->setDisableBtnOk();
            $p = new Paragraph($documentMessage);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setDisableBtnOk();
        } elseif ($this->PageSetting->checkPageRight('AllowPublishWithoutQuotation') === false && empty($this->Quotations) === true) {
            $p = new Paragraph(Trans::getMessageWord('emptySoqData'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getWord('publishJobConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishSo');
            $modal->setBtnOkName(Trans::getWord('yesPublish'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        }

        return $modal;
    }

    /**
     * Function to get publish required document.
     *
     * @return string
     */
    private function checkPublishRequiredDocument(): string
    {
        if ($this->isInklaring() === false) {
            return '';
        }
        $data = DocumentDao::getTotalByGroupAndType('salesorder', ['bl', 'invoiceorigin', 'packinglist'], $this->getDetailReferenceValue());
        $valid = true;
        $number = new NumberFormatter($this->getUser());
        $message = [];
        foreach ($data as $row) {
            $total = (int)$row['total_doc'];
            $value = $number->doFormatInteger($total);
            if ($total === 0) {
                $valid = false;
                $value = new LabelDanger($total);
            }
            $message[] = [
                'label' => $row['dct_description'],
                'value' => $value,
            ];
        }
        if ($valid === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($message, 8, 8);
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getInklaringModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoJikMdl', Trans::getWord('createJobInklaring'));
        $requireFieldError = $this->checkInklaringRequiredFields();
        if (empty($requireFieldError) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredFields'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requireFieldError);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $modal->setFormSubmit($this->getMainFormId(), 'doCreateInklaring');
            $showModal = false;
            if ($this->getFormAction() === 'doCreateInklaring' && $this->isValidPostValues() === false) {
                $modal->setShowOnLoad();
                $showModal = true;
            }
            $fieldSet = new FieldSet($this->Validation);
            $fieldSet->setGridDimension(12, 12, 12);
            # Create Fields
            $srtField = $this->Field->getSingleSelect('serviceTerm', 'jik_service_term', $this->getParameterForModal('jik_service_term', $showModal));
            $srtField->setHiddenField('jik_srt_id', $this->getParameterForModal('jik_srt_id', $showModal));
            $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
            $srtField->addParameter('srv_code', 'inklaring');
            $srtField->addParameter('srt_container', $this->getStringParameter('so_container'));
            $srtField->setEnableNewButton(false);
            $srtField->setEnableDetailButton(false);
            $srtField->setAutoCompleteFields([
                'jik_srv_id' => 'srt_srv_id'
            ]);
            $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
            $fieldSet->addField(Trans::getWord('closingDate'), $this->Field->getCalendar('jik_closing_date', $this->getParameterForModal('jik_closing_date', $showModal)), true);
            $fieldSet->addField(Trans::getWord('closingTime'), $this->Field->getTime('jik_closing_time', $this->getParameterForModal('jik_closing_time', $showModal)), true);
            $fieldSet->addHiddenField($this->Field->getHidden('jik_srv_id', $this->getParameterForModal('jik_srv_id', $showModal)));
            $modal->addFieldSet($fieldSet);
        }

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getDeliveryModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoJdlMdl', Trans::getWord('createJobDelivery'));
        $errorRequired = $this->checkDeliveryRequiredFields();
        if (empty($errorRequired) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredFields'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($errorRequired);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $modal->setFormSubmit($this->getMainFormId(), 'doCreateJobDelivery');
            $showModal = false;
            if ($this->getFormAction() === 'doCreateJobDelivery' && $this->isValidPostValues() === false) {
                $modal->setShowOnLoad();
                $showModal = true;
            }
            $fieldSet = new FieldSet($this->Validation);
            $fieldSet->setGridDimension(6, 6);
            # Create Fields
            $srtField = $this->Field->getSingleSelect('serviceTerm', 'jdl_service_term', $this->getParameterForModal('jdl_service_term', $showModal));
            $srtField->setHiddenField('jdl_srt_id', $this->getParameterForModal('jdl_srt_id', $showModal));
            $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
            $srtField->addParameter('srv_code', 'delivery');
            $srtField->addParameter('srt_container', $this->getStringParameter('so_container'));
            $srtField->setEnableNewButton(false);
            $srtField->setEnableDetailButton(false);
            $srtField->setAutoCompleteFields([
                'jdl_srv_id' => 'srt_srv_id',
                'jdl_srt_route' => 'srt_route',
                'jdl_srt_pod' => 'srt_pod',
                'jdl_srt_pol' => 'srt_pol',
            ]);

            # Create Transport module
            # Create Transport Module Field
            $tmField = $this->Field->getSingleSelect('transportModule', 'jdl_transport_module', $this->getStringParameter('jdl_transport_module'), 'loadNonRoadData');
            $tmField->setHiddenField('jdl_tm_id', $this->getIntParameter('jdl_tm_id'));
            $tmField->setEnableNewButton(false);
            $tmField->addClearField('jdl_pol');
            $tmField->addClearField('jdl_pol_id');
            $tmField->addClearField('jdl_pod');
            $tmField->addClearField('jdl_pod_id');
            # POL
            $polField = $this->Field->getSingleSelect('port', 'jdl_pol', $this->getStringParameter('jdl_pol'));
            $polField->setHiddenField('jdl_pol_id', $this->getIntParameter('jdl_pol_id'));
            $polField->setEnableNewButton(false);
            $polField->addOptionalParameterById('po_tm_id', 'jdl_tm_id');
            # POD
            $podField = $this->Field->getSingleSelect('port', 'jdl_pod', $this->getStringParameter('jdl_pod'));
            $podField->setHiddenField('jdl_pod_id', $this->getIntParameter('jdl_pod_id'));
            $podField->setEnableNewButton(false);
            $podField->addOptionalParameterById('po_tm_id', 'jdl_tm_id');

            $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
            $polName = Trans::getWord('portOfLoading');
            $podName = Trans::getWord('portOfDischarge');
            $srtRoute = $this->getParameterForModal('jdl_srt_route', $showModal);
            if ($srtRoute === 'ptp' || $srtRoute === 'ptpc') {
                $fieldSet->addField(Trans::getWord('transportModule'), $tmField, true);
            } else {
                $polName = Trans::getWord('portName');
                $podName = Trans::getWord('portName');
            }
            if ($this->getParameterForModal('jdl_srt_pol', $showModal, 'N') === 'Y') {
                $fieldSet->addField($polName, $polField, true);
            }
            if ($this->getParameterForModal('jdl_srt_pod', $showModal, 'N') === 'Y') {
                $fieldSet->addField($podName, $podField, true);
            }

            $fieldSet->addField(Trans::getWord('etdDate'), $this->Field->getCalendar('jdl_departure_date', $this->getParameterForModal('jdl_departure_date', $showModal)), true);
            $fieldSet->addField(Trans::getWord('etdTime'), $this->Field->getTime('jdl_departure_time', $this->getParameterForModal('jdl_departure_time', $showModal)), true);
            $fieldSet->addHiddenField($this->Field->getHidden('jdl_srv_id', $this->getParameterForModal('jdl_srv_id', $showModal)));
            $fieldSet->addHiddenField($this->Field->getHidden('jdl_srt_route', $this->getParameterForModal('jdl_srt_route', $showModal)));
            $fieldSet->addHiddenField($this->Field->getHidden('jdl_srt_pol', $this->getParameterForModal('jdl_srt_pol', $showModal)));
            $fieldSet->addHiddenField($this->Field->getHidden('jdl_srt_pod', $this->getParameterForModal('jdl_srt_pod', $showModal)));
            $modal->addFieldSet($fieldSet);
        }
        return $modal;
    }


    /**
     * Function to get warehouse modal.
     *
     * @return Modal
     */
    private function getWarehouseModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoJwMdl', Trans::getWord('createJobWarehouse'));
        $requireFieldError = $this->checkWarehouseRequiredFields();
        if (empty($requireFieldError) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredFields'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requireFieldError);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $modal->setFormSubmit($this->getMainFormId(), 'doCreateJobWarehouse');
            $showModal = false;
            if ($this->getFormAction() === 'doCreateJobWarehouse' && $this->isValidPostValues() === false) {
                $modal->setShowOnLoad();
                $showModal = true;
            }
            $fieldSet = new FieldSet($this->Validation);
            $fieldSet->setGridDimension(6, 6);
            # Create Fields
            $srtField = $this->Field->getSingleSelect('serviceTerm', 'jw_service_term', $this->getParameterForModal('jw_service_term', $showModal), 'loadServiceTermInboundOutbound');
            $srtField->setHiddenField('jw_srt_id', $this->getParameterForModal('jw_srt_id', $showModal));
            $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
            $srtField->setEnableNewButton(false);
            $srtField->setEnableDetailButton(false);
            $srtField->setAutoCompleteFields([
                'jw_srv_id' => 'srt_srv_id',
                'jw_srt_route' => 'srt_route'
            ]);
            $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
            $fieldSet->addField(Trans::getWord('planningDate'), $this->Field->getCalendar('jw_planning_date', $this->getParameterForModal('jw_planning_date', $showModal)), true);
            $fieldSet->addField(Trans::getWord('planningTime'), $this->Field->getTime('jw_planning_time', $this->getParameterForModal('jw_planning_time', $showModal)), true);
            $fieldSet->addHiddenField($this->Field->getHidden('jw_srv_id', $this->getParameterForModal('jw_srv_id', $showModal)));
            $fieldSet->addHiddenField($this->Field->getHidden('jw_srt_route', $this->getParameterForModal('jw_srt_route', $showModal)));
            $modal->addFieldSet($fieldSet);
        }
        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getSoFinishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SoFinishMdl', Trans::getWord('finishConfirmation'));
        $text = Trans::getWord('finishSoConfirmation', 'message');
        $modal->setFormSubmit($this->getMainFormId(), 'doCompleteSo');
        $modal->setBtnOkName(Trans::getWord('yesFinish'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to check is so deleted or not.
     *
     * @return bool
     */
    private function isSoDeleted(): bool
    {

        return $this->isValidParameter('so_deleted_on');
    }

    /**
     * Function to check is so deleted or not.
     *
     * @return bool
     */
    private function isSoHold(): bool
    {
        return $this->isValidParameter('soh_id');
    }

    /**
     * Function to check is so deleted or not.
     *
     * @return bool
     */
    private function isSoStarted(): bool
    {

        return $this->isValidParameter('so_start_on');
    }

    /**
     * Function to check is so deleted or not.
     *
     * @return bool
     */
    private function isSoFinish(): bool
    {

        return $this->isValidParameter('so_finish_on');
    }

    /**
     * Function to check is so published or not.
     *
     * @return bool
     */
    private function isSoPublished(): bool
    {

        return $this->isValidParameter('so_publish_on');
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getTimeSheetFieldSet(): Portlet
    {
        $table = new Table('SoTsTbl');
        $table->setHeaderRow([
            'ts_action' => Trans::getWord('action'),
            'ts_remark' => Trans::getWord('remark'),
            'ts_time' => Trans::getWord('time'),
            'ts_creator' => Trans::getWord('reportedBy'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->addColumnAttribute('ts_time', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SoTsPtl', Trans::getWord('timeSheet'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        if ($this->isValidParameter('so_deleted_on') === true) {
            $result[] = [
                'ts_action' => Trans::getWord('canceled'),
                'ts_remark' => $this->getStringParameter('so_deleted_reason'),
                'ts_creator' => $this->getStringParameter('so_deleted_by'),
                'ts_time' => DateTimeParser::format($this->getStringParameter('so_deleted_on'), 'Y-m-d H:i:s', 'H:i - d M Y'),
            ];
        }
        if ($this->isValidParameter('so_finish_on') === true) {
            $result[] = [
                'ts_action' => Trans::getWord('finish'),
                'ts_remark' => '',
                'ts_creator' => $this->getStringParameter('so_finish_by'),
                'ts_time' => DateTimeParser::format($this->getStringParameter('so_finish_on'), 'Y-m-d H:i:s', 'H:i - d M Y'),
            ];
        }
        if ($this->isValidParameter('so_publish_on') === true) {
            $result[] = [
                'ts_action' => Trans::getWord('published'),
                'ts_remark' => '',
                'ts_creator' => $this->getStringParameter('so_publish_by'),
                'ts_time' => DateTimeParser::format($this->getStringParameter('so_publish_on'), 'Y-m-d H:i:s', 'H:i - d M Y'),
            ];
        }
        $result[] = [
            'ts_action' => Trans::getWord('created'),
            'ts_remark' => '',
            'ts_creator' => $this->getStringParameter('so_created_by'),
            'ts_time' => DateTimeParser::format($this->getStringParameter('so_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y'),
        ];

        return $result;
    }


    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    private function getDocumentFPortlet(): Portlet
    {
        $docDeleteModal = $this->getBaseDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        $docModal = $this->getBaseDocumentModal('salesorder');
        $this->View->addModal($docModal);
        # Create table.
        $docTable = new Table('SoDocTbl');
        $docTable->setHeaderRow([
            'doc_group_text' => Trans::getWord('group'),
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'doc_delete' => Trans::getWord('delete'),
        ]);
        # load data
        $data = SalesOrderDao::loadDocumentData($this->User->getSsId(), $this->getDetailReferenceValue(), $this->getStringParameter('so_number'));
        $results = [];
        foreach ($data as $row) {
            if ($row['dcg_code'] === 'salesorder') {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['doc_delete'] = $btnDel;
            }
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('doc_delete', 'style', 'text-align: center');
        $portlet = new Portlet('SoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        if ($this->isSoDeleted() === false && $this->isSoHold() === false) {
            # create modal.
            $btnDocMdl = new ModalButton('btnSoDocMdl', Trans::getWord('upload'), $docModal->getModalId());
            $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnDocMdl);
        }

        return $portlet;
    }


    /**
     * Abstract function to check is allow finish.
     *
     * @return bool
     */
    private function isAllowFinish(): bool
    {
        $result = false;
        if ($this->isSoStarted() === true && $this->isSoFinish() === false) {
            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue());
            if (empty($jobs) === false) {
                $result = true;
                foreach ($jobs as $row) {
                    if (empty($row['jo_finish_on']) === true) {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Abstract function to check is allow finish.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdate') && $this->isSoDeleted() === false && $this->isSoFinish() === false && $this->isSoHold() === false;
    }

    /**
     * Abstract function to check is allow delete.
     *
     * @return bool
     */
    private function isAllowDelete(): bool
    {
        $result = false;
        if ($this->PageSetting->checkPageRight('AllowDelete') === true) {
            $jobs = JobOrderDao::loadJoIdBySoId($this->getDetailReferenceValue(), [
                SqlHelper::generateNullCondition('jo_start_on', false)
            ]);
            $result = empty($jobs);
        }

        return $result;
    }

    /**
     * Function to get the sales Field Set.
     *
     * @return Portlet
     */
    private function getSalesFieldSet(): Portlet
    {
        $table = new Table('SoJosTbl');
        $table->setHeaderRow([
            'jos_jo_number' => Trans::getFinanceWord('joNumber'),
            'jos_jo_service' => Trans::getFinanceWord('service'),
            'jos_relation' => Trans::getFinanceWord('billTo'),
            'jos_description' => Trans::getFinanceWord('description'),
            'jos_quantity' => Trans::getFinanceWord('quantity'),
            'jos_rate' => Trans::getFinanceWord('rate'),
            'jos_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jos_tax_name' => Trans::getFinanceWord('tax'),
            'jos_total' => Trans::getFinanceWord('total'),
            'jos_type' => Trans::getFinanceWord('type'),
            'jos_invoice' => Trans::getFinanceWord('invoice'),
        ]);
        $wheres = [];
        $jik = '(jo.jo_id IN (SELECT jik_jo_id
                                    FROM job_inklaring
                                    WHERE jik_so_id = ' . $this->getDetailReferenceValue() . '))';
        $jdl = '(jo.jo_id IN (SELECT jdl_jo_id
                                    FROM job_delivery
                                    WHERE jdl_so_id = ' . $this->getDetailReferenceValue() . '))';
        $wheres[] = '(' . $jik . ' OR ' . $jdl . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jos.jos_deleted_on IS NULL)';
        $data = JobSalesDao::loadData($wheres, [
            'jos_jo_id', 'jos_type DESC'
        ]);
        $number = new NumberFormatter();
        $rows = [];
        $showBtnInvoice = false;
        $i = 0;
        foreach ($data as $row) {
            if ($row['jos_type'] === 'S') {
                $row['jos_type'] = new LabelPrimary(Trans::getFinanceWord('revenue'));
            } else {
                $row['jos_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $row['jos_jo_service'] = $row['jos_srv_name'] . ' - ' . $row['jos_srt_name'];
            $row['jos_description'] = $row['jos_cc_code'] . ' - ' . $row['jos_description'];
            $row['jos_quantity'] = $number->doFormatFloat((float)$row['jos_quantity']) . ' ' . $row['jos_uom_code'];
            $row['jos_rate'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_rate']);
            if (empty($row['jos_exchange_rate']) === false) {
                $row['jos_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_exchange_rate']);
                $row['jos_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_total']);
            } else {
                $row['jos_total'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_total']);
                $table->addCellAttribute('jos_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jos_tax_id']) === true) {
                $table->addCellAttribute('jos_tax_name', $i, 'style', 'background-color: red;');
            }

            if (empty($row['jos_sid_id']) === false) {
                $url = url('/salesInvoice/detail?si_id=' . $row['jos_si_id']);
                $siButton = new HyperLink('SoSiJosBtn' . $row['jos_id'], '', $url);
                $siButton->viewAsButton();
                $siButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $row['jos_invoice'] = $siButton;
            } else {
                $showBtnInvoice = true;
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jos_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_invoice', 'style', 'text-align: center;');

        $portlet = new Portlet('SoJosPtl', Trans::getWord('sales'));
        $portlet->addTable($table);
        if ($showBtnInvoice) {
            $url = url('/salesInvoice/detail');
            $caButton = new HyperLink('BtnSoSi', Trans::getFinanceWord('createInvoice'), $url);
            $caButton->viewAsButton();
            $caButton->setIcon(Icon::Plus)->btnSuccess()->pullRight()->btnMedium();
            $portlet->addButton($caButton);
        }

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    private function getPurchaseFieldSet(): Portlet
    {
        $table = new Table('SoJopTbl');
        $table->setHeaderRow([
            'jop_jo_number' => Trans::getFinanceWord('joNumber'),
            'jop_jo_service' => Trans::getFinanceWord('service'),
            'jop_relation' => Trans::getFinanceWord('billTo'),
            'jop_description' => Trans::getFinanceWord('description'),
            'jop_quantity' => Trans::getFinanceWord('quantity'),
            'jop_rate' => Trans::getFinanceWord('rate'),
            'jop_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jop_tax_name' => Trans::getFinanceWord('tax'),
            'jop_total' => Trans::getFinanceWord('total'),
            'jop_type' => Trans::getFinanceWord('type'),
            'jop_invoice' => Trans::getFinanceWord('invoice'),
        ]);
        $wheres = [];
        $jik = '(jo.jo_id IN (SELECT jik_jo_id
                                    FROM job_inklaring
                                    WHERE jik_so_id = ' . $this->getDetailReferenceValue() . '))';
        $jdl = '(jo.jo_id IN (SELECT jdl_jo_id
                                    FROM job_delivery
                                    WHERE jdl_so_id = ' . $this->getDetailReferenceValue() . '))';
        $wheres[] = '(' . $jik . ' OR ' . $jdl . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jop.jop_deleted_on IS NULL)';
        $data = JobPurchaseDao::loadData($wheres);
        $number = new NumberFormatter();
        $rows = [];
        $i = 0;
        foreach ($data as $row) {
            if ($row['jop_type'] === 'P') {
                $row['jop_type'] = new LabelPrimary(Trans::getFinanceWord('cogs'));
            } else {
                $row['jop_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $row['jop_jo_service'] .= ' - ' . $row['jop_jo_service_term'];
            $row['jop_description'] = $row['jop_cc_code'] . ' - ' . $row['jop_description'];
            $row['jop_quantity'] = $number->doFormatFloat((float)$row['jop_quantity']) . ' ' . $row['jop_uom_code'];
            $row['jop_rate'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_rate']);
            if (empty($row['jop_exchange_rate']) === false) {
                $row['jop_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_exchange_rate']);
                $row['jop_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_total']);
            } else {
                $row['jop_total'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_total']);
                $table->addCellAttribute('jop_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jop_tax_id']) === true) {
                $table->addCellAttribute('jop_tax_name', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jop_pi_id']) === false) {
                $url = url('/purchaseInvoice/detail?pi_id=' . $row['jop_pi_id']);
                $siButton = new HyperLink('SoPiJopBtn' . $row['jop_id'], '', $url);
                $siButton->viewAsButton();
                $siButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $row['jop_invoice'] = $siButton;
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jop_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_invoice', 'style', 'text-align: center;');

        $portlet = new Portlet('SoJopPtl', Trans::getWord('purchase'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the quotation portlet.
     *
     * @return void
     */
    private function loadQuotationData(): void
    {
        $data = SalesOrderQuotationDao::loadDataBySoId($this->getDetailReferenceValue());
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $row['soq_period'] = $dt->formatDate($row['soq_start_date']) . ' - ' . $dt->formatDate($row['soq_end_date']);
            $this->Quotations[] = $row;
        }
    }

    /**
     * Function to get the quotation portlet.
     *
     * @return Portlet
     */
    private function getQuotationPortlet(): Portlet
    {
        $modal = $this->getQuotationModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getQuotationDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('SoQtTbl');
        $table->setHeaderRow([
            'soq_qt_number' => Trans::getFinanceWord('number'),
            'soq_dl_number' => Trans::getFinanceWord('dealNumber'),
            'soq_commodity' => Trans::getFinanceWord('commodity'),
            'soq_period' => Trans::getFinanceWord('period'),
        ]);

        $table->addRows($this->Quotations);
        $table->setDeleteActionByModal($modalDelete, 'soq', 'getByReferenceForDelete', ['soq_id']);

        $btnQtMdl = new ModalButton('btnQtMdl', Trans::getWord('addQuotation'), $modal->getModalId());
        $btnQtMdl->addAttribute('class', 'btn-primary pull-right');
        $btnQtMdl->setIcon(Icon::Plus);

        $portlet = new Portlet('SoQtPtl', Trans::getWord('quotation'));
        $portlet->addTable($table);
        $portlet->addButton($btnQtMdl);

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    private function getDepositPortlet(): Portlet
    {
        $table = new Table('SoJdTbl');
        $table->setHeaderRow([
            'jd_number' => Trans::getFinanceWord('number'),
            'job_order' => Trans::getFinanceWord('jobOrder'),
            'jd_relation' => Trans::getFinanceWord('relation'),
            'jd_ref' => Trans::getFinanceWord('reference'),
            'jd_amount' => Trans::getFinanceWord('amount'),
            'jd_date' => Trans::getFinanceWord('date'),
            'jd_status' => Trans::getFinanceWord('status'),
            'jd_action' => Trans::getFinanceWord('view'),
        ]);
        $rows = [];
        $wheres = [];
        $wheres[] = '(jd.jd_deleted_on IS NULL)';
        $jik = '(jd.jd_jo_id IN (SELECT jik_jo_id
                                    FROM job_inklaring
                                    WHERE jik_so_id = ' . $this->getDetailReferenceValue() . '))';
        $jdl = '(jd.jd_jo_id IN (SELECT jdl_jo_id
                                    FROM job_delivery
                                    WHERE jdl_so_id = ' . $this->getDetailReferenceValue() . '))';
        $wheres[] = '(' . $jik . ' OR ' . $jdl . ')';

        $data = JobDepositDao::loadData($wheres);
        $dtParser = new DateTimeParser();
        $jdDao = new JobDepositDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $url = url('/jd/detail?jd_id=' . $row['jd_id']);
            $jdBtn = new HyperLink('JoJdBtn' . $row['jd_id'], '', $url);
            $jdBtn->viewAsButton();
            $jdBtn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['jd_action'] = $jdBtn;
            $row['job_order'] = StringFormatter::generateTableView([
                $row['jd_jo_number'],
                $row['jd_jo_service'],
            ], 'text-align: center;');
            $row['jd_ref'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('invoiceRef'),
                    'value' => $row['jd_rel_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('payment'),
                    'value' => $row['jd_paid_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('settlement'),
                    'value' => $row['jd_settle_ref'],
                ],
            ]);
            $row['jd_date'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $dtParser->formatDate($row['jd_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $dtParser->formatDate($row['jd_return_date']),
                ],
            ]);
            $refund = (float)$row['jd_amount'] - (float)$row['jd_claim_amount'];
            $row['jd_amount'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $number->doFormatFloat((float)$row['jd_amount']),
                ],
                [
                    'label' => Trans::getFinanceWord('claim'),
                    'value' => $number->doFormatFloat((float)$row['jd_claim_amount']),
                ],
                [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $number->doFormatFloat($refund),
                ],
            ]);
            $row['jd_status'] = $jdDao->generateStatus([
                'is_deleted' => !empty($row['jd_deleted_on']),
                'is_return' => !empty($row['jd_return_on']),
                'is_settle' => !empty($row['jd_settle_on']),
                'is_paid' => !empty($row['jd_paid_on']),
                'is_approved' => !empty($row['jd_approved_on']),
                'is_requested' => !empty($row['jda_id']),
                'is_rejected' => !empty($row['jda_deleted_on']),
            ]);
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jd_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jd_status', 'style', 'text-align: center;');
        $table->addColumnAttribute('jd_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SoJdPtl', Trans::getFinanceWord('deposit'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    private function getFinanceMarginFieldSet(): Portlet
    {
        $data = SalesOrderDao::loadFinanceMarginData($this->getDetailReferenceValue());
        $table = new Table('SoFinMgnTbl');
        $table->setHeaderRow($data['header']);
        $table->setDisableLineNumber();
        $table->addRows($data['rows']);
        $headerIds = array_keys($data['header']);
        foreach ($headerIds as $id) {
            if ($id !== 'fn_description') {
                $table->setColumnType($id, 'float');
            }
        }
        # Create a portlet box.
        $portlet = new Portlet('JoFinMgnPtl', Trans::getFinanceWord('grossMargin'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get modal for quotation
     *
     * @return Modal
     */
    private function getQuotationModal(): Modal
    {
        $modal = new Modal('SoQtModal', Trans::getFinanceWord('selectQuotation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertQuotation');
        if ($this->getFormAction() === 'doInsertQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        }
        $table = new Table('SoQtInsTbl');
        $table->setHeaderRow([
            'qt_id' => '',
            'qt_number' => Trans::getFinanceWord('number'),
            'qt_deal' => Trans::getFinanceWord('dealNumber'),
            'qt_period' => Trans::getFinanceWord('period'),
            'qt_commodity' => Trans::getFinanceWord('commodity'),
            'qt_select' => Trans::getFinanceWord('select'),
        ]);
        $data = $this->loadAvailableQuotationData();
        $table->addRows($data);
        $table->addColumnAttribute('qt_select', 'style', 'text-align: center;');

        if (empty($data) === true) {
            $modal->setDisableBtnOk();
        } else {
            $p = new Paragraph(Trans::getMessageWord('pleaseSelectOneOption'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        }
        # Create document type field.
        $modal->addTable($table);

        return $modal;
    }

    /**
     * Function to load available data
     *
     * @return array
     */
    private function loadAvailableQuotationData(): array
    {
        $srvCodes = [];
        if ($this->isInklaring()) {
            $srvCodes[] = 'inklaring';
        }
        if ($this->isWarehouse()) {
            $srvCodes[] = 'warehouse';
        }
        if ($this->isDelivery()) {
            $srvCodes[] = 'delivery';
        }
        $results = [];
        if (empty($srvCodes) === false) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNullCondition('qt.qt_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('qt.qt_approve_on', false);
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', 'S');
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_end_date', $this->getStringParameter('so_order_date'), '>=');
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getIntParameter('so_rel_id'));
            $wheres[] = '(qt.qt_id NOT IN (SELECT soq_qt_id
                                        FROM sales_order_quotation
                                        WHERE soq_deleted_on IS NULL AND soq_so_id = ' . $this->getDetailReferenceValue() . '))';
            $wheres[] = "(qt.qt_id IN (SELECT qs_qt_id
                                    FROM quotation_service as qs
                                    INNER JOIN service as srv ON qs.qs_srv_id = srv.srv_id
                                    WHERE (qs_deleted_on IS NULL) AND srv.srv_code IN ('" . implode("', '", $srvCodes) . "')))";
            $data = SalesOrderQuotationDao::loadAvailableQuotationForSo($wheres);
            $dt = new DateTimeParser();
            $i = 0;
            foreach ($data as $row) {
                $row['qt_id'] = $this->Field->getHidden('qt_id[' . $i . ']', $row['qt_id']);
                $row['qt_period'] = $dt->formatDate($row['qt_start_date']) . ' - ' . $dt->formatDate($row['qt_end_date']);
                $row['qt_select'] = $this->Field->getCheckBox('qt_select[' . $i . ']', 'Y');
                $results[] = $row;
                $i++;
            }
        }
        return $results;

    }

    /**
     * Function to get modal delete for quotation
     *
     * @return Modal
     */
    private function getQuotationDeleteModal(): Modal
    {
        $modal = new Modal('SoQtDelModal', Trans::getWord('deleteQuotation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteQuotation');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteQuotation' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->setTitle(Trans::getFinanceWord('deleteConfirmation'));
        $modal->addText('<p class="label-large" style="text-align: center">' . Trans::getMessageWord('deleteConfirmation') . '<p>');
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $qtField = $this->Field->getText('soq_qt_number_del', $this->getParameterForModal('soq_qt_number_del', $showModal));
        $qtField->setReadOnly();
        $dealField = $this->Field->getText('soq_dl_number_del', $this->getParameterForModal('soq_dl_number_del', $showModal));
        $dealField->setReadOnly();
        $commodityField = $this->Field->getText('soq_commodity_del', $this->getParameterForModal('soq_commodity_del', $showModal));
        $commodityField->setReadOnly();
        $expiredField = $this->Field->getText('soq_end_date_del', $this->getParameterForModal('soq_end_date_del', $showModal));
        $expiredField->setReadOnly();
        $fieldSet->addField(Trans::getFinanceWord('quotation'), $qtField);
        $fieldSet->addField(Trans::getFinanceWord('dealNumber'), $dealField);
        $fieldSet->addField(Trans::getFinanceWord('commodity'), $commodityField);
        $fieldSet->addField(Trans::getFinanceWord('expiredOn'), $expiredField);
        $fieldSet->addHiddenField($this->Field->getHidden('soq_id_del', $this->getParameterForModal('soq_id_del', $showModal)));
        # Create document type field.
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeSalesInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeSales');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeQuotationInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeQuotation');
    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeePurchaseInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeePurchase');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeDepositInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeDeposit');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeMarginInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeFinanceMargin');
    }

    /**
     * Function to do prepare data sales order issue.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];

        foreach ($data as $row) {
            $priority = mb_strtolower($row['soi_sty_name']);
            if ($priority === 'low') {
                $row['soi_sty_name'] = new LabelGray($row['soi_sty_name']);
            } else if ($priority === 'medium') {
                $row['soi_sty_name'] = new LabelPrimary($row['soi_sty_name']);
            } else {
                $row['soi_sty_name'] = new LabelDanger($row['soi_sty_name']);
            }

            $status = $row['soi_finish_on'];
            if ($status === NULL) {
                $row['soi_finish_on'] = new LabelPrimary('Open');
            } else {
                $row['soi_finish_on'] = new LabelSuccess('Close');
            }

            $results[] = $row;
        }

        return $results;
    }

    /**
     * Function to check is this inklaring active
     *
     * @return bool
     */
    private function isInklaring(): bool
    {
        return $this->getStringParameter('so_inklaring', 'N') === 'Y';
    }

    /**
     * Function to check is this PLB active
     *
     * @return bool
     */
    private function isPlb(): bool
    {
        return $this->getStringParameter('so_plb', 'N') === 'Y';
    }

    /**
     * Function to check is this delivery active
     *
     * @return bool
     */
    private function isDelivery(): bool
    {
        return $this->getStringParameter('so_delivery', 'N') === 'Y';
    }

    /**
     * Function to check is this delivery active
     *
     * @return bool
     */
    private function isWarehouse(): bool
    {
        return $this->getStringParameter('so_warehouse', 'N') === 'Y';
    }

    /**
     * Function to check is pol required
     *
     * @return bool
     */
    private function isPol(): bool
    {
        return $this->getStringParameter('so_ict_pol', 'N') === 'Y';
    }

    /**
     * Function to check is pod required
     *
     * @return bool
     */
    private function isPod(): bool
    {
        return $this->getStringParameter('so_ict_pod', 'N') === 'Y';
    }

    /**
     * Function to check is load required
     *
     * @return bool
     */
    private function isLoad(): bool
    {
        return $this->getStringParameter('so_ict_load', 'N') === 'Y';
    }

    /**
     * Function to check is unload required
     *
     * @return bool
     */
    private function isUnload(): bool
    {
        return $this->getStringParameter('so_ict_unload', 'N') === 'Y';
    }

    /**
     * Function to check is container
     *
     * @return bool
     */
    private function isContainer(): bool
    {
        return $this->getStringParameter('so_container', 'N') === 'Y';
    }

    /**
     * Function to check is consolidate
     *
     * @return bool
     */
    private function isConsolidate(): bool
    {
        return $this->getStringParameter('so_consolidate', 'N') === 'Y';
    }

    /**
     * Function to check is multi load
     *
     * @return bool
     */
    private function isMultiLoad(): bool
    {
        return $this->getStringParameter('so_multi_load', 'N') === 'Y';
    }

    /**
     * Function to check is multi unload
     *
     * @return bool
     */
    private function isMultiUnload(): bool
    {
        return $this->getStringParameter('so_multi_unload', 'N') === 'Y';
    }

    /**
     * Function to check is multi unload
     *
     * @return bool
     */
    private function isMultiLoadUnload(): bool
    {
        return $this->isDelivery() && ($this->isMultiLoad() || $this->isMultiUnload());
    }

    /**
     * Function to set hidden for job delivery detail fields.
     *
     * @return void
     */
    private function setHiddenField(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('so_ict_code', $this->getStringParameter('so_ict_code'));
        $content .= $this->Field->getHidden('so_ict_pol', $this->getStringParameter('so_ict_pol'));
        $content .= $this->Field->getHidden('so_ict_pod', $this->getStringParameter('so_ict_pod'));
        $content .= $this->Field->getHidden('so_ict_load', $this->getStringParameter('so_ict_load'));
        $content .= $this->Field->getHidden('so_ict_unload', $this->getStringParameter('so_ict_unload'));
        $content .= $this->Field->getHidden('soh_id', $this->getIntParameter('soh_id'));
        $this->View->addContent('SoHdn', $content);

    }


    /**
     * Function to validate fields require before publish.
     *
     * @return string
     */
    private function checkInklaringRequiredFields(): string
    {
        # SEt Required Fields
        $required = [];
        $required['so_cdt_id'] = 'documentType';
        $required['so_tm_id'] = 'transportModule';
        $required['so_transport_name'] = 'transportName';
        $required['so_transport_number'] = 'transportNumber';
        $required['so_plb'] = 'plb';
        if ($this->isPlb() === true) {
            $required['so_wh_id'] = 'warehouseName';
        }
        $required['so_pol_id'] = 'portOfLoading';
        $required['so_pod_id'] = 'portOfDischarge';
        $required['so_consignee'] = 'consignee';
        $required['so_consignee_address'] = 'consigneeAddress';
        $required['so_shipper'] = 'shipper';
        $required['so_shipper_address'] = 'shipperAddress';
        $required['so_notify'] = 'notifyParty';
        $required['so_notify_address'] = 'notifyPartyAddress';

        # Check Value
        $errors = $this->doValidateRequiredFields($required);
        # Check Value
        if (empty($this->SogData) === true) {
            $errors[] = [
                'label' => Trans::getWord('goods'),
                'value' => new LabelTrueFalse(false),
            ];
        }
        if (empty($errors) === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($errors, 8, 8);
    }

    /**
     * Function to validate fields require before publish.
     *
     * @param array $fields To store required fields.
     * @return array
     */
    private function doValidateRequiredFields(array $fields): array
    {
        $message = [];
        foreach ($fields as $key => $label) {
            if ($this->isValidParameter($key) === false) {
                $message[] = [
                    'label' => Trans::getWord($label),
                    'value' => new LabelTrueFalse(false),
                ];
            }
        }
        return $message;
    }

    /**
     * Function to validate fields require before publish.
     *
     * @return string
     */
    private function checkDeliveryRequiredFields(): string
    {
        $errors = [];
        # Check Party
        if (empty($this->SocData) === true) {
            $errors[] = [
                'label' => Trans::getWord('party'),
                'value' => new LabelTrueFalse(false),
            ];
        } else {
            foreach ($this->SocData as $row) {
                if (empty($row['soc_eg_id']) === true) {
                    $errors[] = [
                        'label' => Trans::getMessageWord('missingTruckTypeInSoc', '', ['socNumber' => $row['soc_number']]),
                        'value' => new LabelTrueFalse(false),
                    ];
                }
            }
        }
        # Check Goods
        if (empty($this->SogData) === true) {
            $errors[] = [
                'label' => Trans::getWord('goods'),
                'value' => new LabelTrueFalse(false),
            ];
        } else {
            foreach ($this->SogData as $row) {
                if (empty($row['sog_soc_id']) === true) {
                    $errors[] = [
                        'label' => Trans::getMessageWord('missingSocInSog', '', ['sogNumber' => $row['sog_number']]),
                        'value' => new LabelTrueFalse(false),
                    ];
                }
            }
        }
        if ($this->isLoad() === true && empty($this->LoadingAddress) === true) {
            $errors[] = [
                'label' => Trans::getWord('originAddress'),
                'value' => new LabelTrueFalse(false),
            ];
        }
        if ($this->isUnload() === true && empty($this->UnloadAddress) === true) {
            $errors[] = [
                'label' => Trans::getWord('destinationAddress'),
                'value' => new LabelTrueFalse(false),
            ];
        }
        if (empty($errors) === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($errors, 8, 8);
    }

    /**
     * Function to validate fields require before publish.
     *
     * @return string
     */
    private function checkWarehouseRequiredFields(): string
    {
        # SEt Required Fields
        $required = [];
        $required['so_wh_id'] = 'warehouseName';
        $required['so_consignee'] = 'consignee';
        $required['so_consignee_address'] = 'consigneeAddress';
        $required['so_shipper'] = 'shipper';
        $required['so_shipper_address'] = 'shipperAddress';

        $errors = $this->doValidateRequiredFields($required);
        if (empty($errors) === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($errors, 8, 8);
    }


    /**
     * Function to load job warehouse data.
     *
     * @return array
     */
    private function loadJobWarehouseData(): array
    {
        $inbound = 'SELECT ji.ji_id as jw_id, srt.srt_route as jw_route
                        FROM job_inbound as ji
                        INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        WHERE (jo.jo_deleted_on IS NULL) AND (ji.ji_so_id = ' . $this->getDetailReferenceValue() . ')';
        $outbound = 'SELECT job.job_id as jw_id, srt.srt_route as jw_route
                        FROM job_outbound as job
                        INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        WHERE (jo.jo_deleted_on IS NULL) AND (job.job_so_id = ' . $this->getDetailReferenceValue() . ')';
        $query = $inbound . ' UNION ALL ' . $outbound;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to load job warehouse data.
     *
     * @return array
     */
    private function loadLudForUpdateAddress(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('lud.lud_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_so_id', $this->getDetailReferenceValue());
        $dtp = "(srt.srt_route = 'dtp' AND lud.lud_type = 'D')";
        $ptd = "(srt.srt_route = 'ptd' AND lud.lud_type = 'O')";
        $wheres[] = '(' . $dtp . ' OR ' . $ptd . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = ' SELECT lud.lud_id, lud.lud_type, srt.srt_route
                    FROM load_unload_delivery as lud
                    INNER JOIN job_delivery as jdl ON jdl.jdl_id = lud.lud_jdl_id
                    INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id
                    INNER JOIN service_term as srt ON srt.srt_id = jo.jo_srt_id ' . $strWhere;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to load job warehouse data.
     *
     * @return array
     */
    private function loadJdlForUpdateDepo(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_so_id', $this->getDetailReferenceValue());
        $wheres[] = "(srt.srt_route = 'dtpc' OR srt.srt_route = 'ptdc')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = ' SELECT jdl.jdl_id, srt.srt_route
                    FROM job_delivery as jdl
                    INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id
                    INNER JOIN service_term as srt ON srt.srt_id = jo.jo_srt_id ' . $strWhere;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }
}
