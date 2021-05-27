<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\CustomerService;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\CustomerService\SalesOrderQuotationDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;

/**
 * Class to handle the creation of detail BaseJobOrder page
 *
 * @package    app
 * @subpackage Model\Viewer\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrder extends AbstractViewerModel
{

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
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {

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
        $this->overridePageDescription();
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getServicePortlet());
        if ($this->isInklaring() || $this->isWarehouse()) {
            $this->Tab->addPortlet('general', $this->getDetailPortlet());
        }
        $this->Tab->addPortlet('general', $this->getRelationPortlet());
        if ($this->isInklaring() === true) {
            $this->Tab->addPortlet('general', $this->getPortPortlet());
        }
        if ($this->isContainer() === true && $this->isDelivery() === true && ($this->isLoad() === true || $this->isUnload() === true)) {
            $this->Tab->addPortlet('general', $this->getDepoPortlet());
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
            $this->View->addErrorMessage(Trans::getWord('soHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('soh_reason')]));
        }
        # Goods
        if ($this->isConsolidate() === false && ($this->isDelivery() === true || $this->isContainer() === true)) {
            $this->Tab->addPortlet('goods', $this->getContainerPortlet());
        }
        $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());

        # Delivery Address
        if ($this->isDelivery() === true) {
            if ($this->isLoad() === true) {
                $this->Tab->addPortlet('goods', $this->getLoadUnloadPortlet('O'));
            }
            if ($this->isUnload() === true) {
                $this->Tab->addPortlet('goods', $this->getLoadUnloadPortlet('D'));
            }
        }
        if ($this->isValidParameter('so_publish_on') === true) {
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
        $this->Tab->addPortlet('document', $this->getDocumentPortlet());
        $this->Tab->addPortlet('timeSheet', $this->getTimeSheetFieldSet());

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralPortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('so_customer'),
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->getStringParameter('so_pic_customer'),
            ],
            [
                'label' => Trans::getWord('orderOffice'),
                'value' => $this->getStringParameter('so_order_office'),
            ],
            [
                'label' => Trans::getWord('invoiceOffice'),
                'value' => $this->getStringParameter('so_invoice_office'),
            ],
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
        ]);
        # Create a portlet box.
        $portlet = new Portlet('SoGeneralPtl', Trans::getWord('customer'));
        $portlet->addText($content);
        $portlet->addText($this->Field->getHidden('so_order_of_id', $this->getIntParameter('jo_order_of_id')));
        $portlet->addText($this->Field->getHidden('so_rel_id', $this->getIntParameter('jo_rel_id')));
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the service portlet.
     *
     * @return Portlet
     */
    protected function getServicePortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('consolidate'),
                'value' => new LabelYesNo($this->getStringParameter('so_consolidate')),
            ],
            [
                'label' => Trans::getWord('container'),
                'value' => new LabelYesNo($this->getStringParameter('so_container')),
            ],
            [
                'label' => Trans::getWord('inklaring'),
                'value' => new LabelYesNo($this->getStringParameter('so_inklaring')),
            ],
            [
                'label' => Trans::getWord('delivery'),
                'value' => new LabelYesNo($this->getStringParameter('so_delivery')),
            ],
            [
                'label' => Trans::getWord('incoTerms'),
                'value' => $this->getStringParameter('so_inco_terms'),
            ],
            [
                'label' => Trans::getWord('multiPickUp'),
                'value' => new LabelYesNo($this->getStringParameter('so_multi_load')),
            ],
            [
                'label' => Trans::getWord('multiDrop'),
                'value' => new LabelYesNo($this->getStringParameter('so_multi_unload')),
            ],
            [
                'label' => Trans::getWord('warehouse'),
                'value' => new LabelYesNo($this->getStringParameter('so_warehouse')),
            ],
            [
                'label' => Trans::getWord('notes'),
                'value' => $this->getStringParameter('so_notes'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('SoSrvPtl', Trans::getWord('service'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getTimeSheetFieldSet(): Portlet
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
    private function getDocumentPortlet(): Portlet
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
        ]);
        # load data
        $data = SalesOrderDao::loadDocumentData($this->User->getSsId(), $this->getDetailReferenceValue(), $this->getStringParameter('so_number'));
        $results = [];
        foreach ($data as $row) {
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
        $portlet = new Portlet('SoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        return $portlet;
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
//    /**
//     * Function to check is so deleted or not.
//     *
//     * @return bool
//     */
//    private function isSoStarted(): bool
//    {
//
//        return $this->isValidParameter('so_start_on');
//    }
//
//    /**
//     * Function to check is so published or not.
//     *
//     * @return bool
//     */
//    private function isSoPublished(): bool
//    {
//
//        return $this->isValidParameter('so_publish_on');
//    }


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
        $data = JobOrderDao::loadDataBySoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
        $rows = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
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
            $url = '/' . $row['jo_srt_route'] . '/view?jo_id=' . $row['jo_id'];
            $btnUpdate = new HyperLink('btnJoEd' . $row['jo_id'], '', url($url));
            $btnUpdate->viewAsButton();
            $btnUpdate->setIcon(Icon::Eye)->btnMedium()->btnSuccess()->viewIconOnly();
            $row['jo_action'] = $btnUpdate;
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jo_status', 'style', 'text-align: center;');
        $table->addColumnAttribute('jo_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SoJoPtl', Trans::getWord('jobOrder'));
        $portlet->addTable($table);
        return $portlet;
    }

    /**
     * Function to get the detail portlet.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        $data = [];
        if ($this->isInklaring() === true) {
            $data = [
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
                    'label' => Trans::getWord('transportName'),
                    'value' => $this->getStringParameter('so_equipment'),
                ],
                [
                    'label' => Trans::getWord('transportNumber'),
                    'value' => $this->getStringParameter('so_transport_number'),
                ],
                [
                    'label' => Trans::getWord('plb'),
                    'value' => new LabelYesNo($this->getStringParameter('so_plb')),
                ],
            ];
        }
        if ($this->isWarehouse() === true || $this->isPlb() === true) {
            $data[] = [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('so_warehouse_name')
            ];
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('SoDtlPtl', Trans::getWord('soDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the Inklaring portlet.
     *
     * @return Portlet
     */
    private function getPortPortlet(): Portlet
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
        ]);
        # Create a portlet box.
        $portlet = new Portlet('SoPortPtl', Trans::getWord('port'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the Inklaring portlet.
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
        $data = [];
        if ($this->isLoad() === true) {
            $data[] = [
                'label' => Trans::getWord('ownerDepoPickUp'),
                'value' => $this->getStringParameter('so_dp_owner'),
            ];
            $data[] = [
                'label' => Trans::getWord('depoPickUp'),
                'value' => $this->getStringParameter('so_dp_name'),
            ];
            $data[] = [
                'label' => $yrOwnerLabel,
                'value' => $this->getStringParameter('so_yr_owner'),
            ];
            $data[] = [
                'label' => $yrLabel,
                'value' => $this->getStringParameter('so_yr_name'),
            ];
        }
        if ($this->isUnload() === true) {
            $data[] = [
                'label' => $ypOwnerLabel,
                'value' => $this->getStringParameter('so_yp_owner'),
            ];
            $data[] = [
                'label' => $ypLabel,
                'value' => $this->getStringParameter('so_yp_name'),
            ];
            $data[] = [
                'label' => Trans::getWord('ownerDepoReturn'),
                'value' => $this->getStringParameter('so_dr_owner'),
            ];
            $data[] = [
                'label' => Trans::getWord('depoReturn'),
                'value' => $this->getStringParameter('so_dr_name'),
            ];
        }
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('SoDepoPtl', $title);
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
     * Function to get the sales Field Set.
     *
     * @return Portlet
     */
    protected function getSalesFieldSet(): Portlet
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
    protected function getPurchaseFieldSet(): Portlet
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
     * @return Portlet
     */
    protected function getQuotationPortlet(): Portlet
    {
        $table = new Table('SoQtTbl');
        $table->setHeaderRow([
            'soq_qt_number' => Trans::getFinanceWord('number'),
            'soq_dl_number' => Trans::getFinanceWord('dealNumber'),
            'soq_commodity' => Trans::getFinanceWord('commodity'),
            'soq_period' => Trans::getFinanceWord('period'),
        ]);

        $data = SalesOrderQuotationDao::loadDataBySoId($this->getDetailReferenceValue());
        $rows = [];
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $row['soq_period'] = $dt->formatDate($row['soq_start_date']) . ' - ' . $dt->formatDate($row['soq_end_date']);
            $rows[] = $row;
        }
        $table->addRows($rows);

        $portlet = new Portlet('SoQtPtl', Trans::getWord('quotation'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getDepositPortlet(): Portlet
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
    protected function getFinanceMarginFieldSet(): Portlet
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
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
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
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getDetailReferenceValue());
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $rows = SalesOrderContainerDao::loadData($wheres);
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('SoSocPtl', Trans::getWord('party'));
        $portlet->addTable($table);

        return $portlet;

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
        $table->addRows($this->loadSalesOrderGoodsData());
        $portlet = new Portlet('SoSogPtl', Trans::getWord('goods'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do prepare goods data.
     *
     * @return array
     */
    private function loadSalesOrderGoodsData(): array
    {
        $results = [];
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

            $results[] = $row;
        }
        return $results;
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
        # Table
        $table = new Table('SoSdlTbl' . $type);
        $table->setHeaderRow([
            'sdl_relation' => Trans::getWord('relation'),
            'sdl_address' => Trans::getWord('address'),
            'sdl_pic' => Trans::getWord('pic'),
            'sdl_reference' => Trans::getWord('reference')
        ]);

        $title = Trans::getWord('originAddress');
        if ($type === 'D') {
            $title = Trans::getWord('destinationAddress');
        }
        $table->addRows($this->loadLoadUnloadData($type));
        # Create a portlet box.
        $portlet = new Portlet('SdlPtl' . $type, $title);

        if (($type === 'O' && $this->isMultiLoad()) || ($type === 'D' && $this->isMultiUnload() === true)) {
            $table->addColumnAfter('sdl_reference', 'sdl_sog_name', Trans::getWord('goods'));
            $table->addColumnAfter('sdl_sog_name', 'sdl_quantity', Trans::getWord('quantity'));
        }
        $portlet->addTable($table);

        return $portlet;
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
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeSalesInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeSales');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeePurchaseInformation(): bool
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
    protected function isAllowToSeeMarginInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeFinanceMargin');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeQuotationInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeQuotation');
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

}
