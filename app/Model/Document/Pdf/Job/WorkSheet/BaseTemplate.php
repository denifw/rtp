<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Document\Pdf\Job\WorkSheet;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 * Class to Control
 *
 * @package    app
 * @subpackage Model\Document\Pdf\Job\Inklaring
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BaseTemplate extends AbstractBasePdf
{
    /**
     * Property to store quotation data.
     *
     * @var array
     */
    protected $Data = [];


    public function __construct()
    {
        parent::__construct(Trans::getFinanceWord('workSheet') . '.pdf');
    }

    public function loadContent(): void
    {
        # Get data
        $this->loadData();
//        dd($this->Data);
        try {
            $this->setFileName(Trans::getFinanceWord('workSheet') . ' ' . $this->Data['jo_number']);
            $this->MPdf->SetHeader();
            $header = $this->getDefaultHeader($this->User->getRelId());
            $footer = $this->getFooterAddress($this->User->getRelId());
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 15, 15, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLHeader($header, 'O', true);
            $this->MPdf->SetHTMLFooter($footer, 'O');
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('workSheet')));

            if ($this->getStringParameter('jo_srv_code') === "inklaring") {
                $this->MPdf->WriteHTML($this->getInformationInklaring());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhInbound") {
                $this->MPdf->WriteHTML($this->getInformationInbound());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhOutbound") {
                $this->MPdf->WriteHTML($this->getInformationOutbound());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhStockMovement") {
                $this->MPdf->WriteHTML($this->getInformationStockMovement());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhOpname") {
                $this->MPdf->WriteHTML($this->getInformationOpname());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhStockAdjustment") {
                $this->MPdf->WriteHTML($this->getInformationStockAdjustment());
            }
            if ($this->getStringParameter('jo_srv_code') === "delivery") {
                $this->MPdf->WriteHTML($this->getInformationDelivery());
            }
            $this->MPdf->WriteHTML($this->getLoadTimeSheetView());
            $this->MPdf->WriteHTML($this->writeSignature());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    public function loadHtmlContent(): string
    {
        return '';
    }

    /**
     * Function to load the information inklaring.
     *
     * @return string
     */
    private function getInformationInklaring(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeInklaringDetail();
        $result .= '</td>';
        # Reference
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeReferenceDetail('so');
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the information inbound.
     *
     * @return string
     */
    private function getInformationInbound(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeJobOrderInboundDetail();
        $result .= '</td>';
        # Reference
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeJobOrder();
        $result .= '</td>';
        # Reference
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeReferenceDetail();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the information outbound.
     *
     * @return string
     */
    private function getInformationOutbound(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeJobOrderOutboundDetail();
        $result .= '</td>';
        # Customer
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeJobOrder();
        $result .= '</td>';
        # Reference
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeReferenceDetail();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the stock movement.
     *
     * @return string
     */
    private function getInformationStockMovement(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeStockMovementDetail();
        $result .= '</td>';
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeStockMovementJobDetail();
        $result .= '</td>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the information opname.
     *
     * @return string
     */
    private function getInformationOpname(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeOpnameDetail();
        $result .= '</td>';
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeOpnameCustomerDetail();
        $result .= '</td>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the information adjustment.
     *
     * @return string
     */
    private function getInformationStockAdjustment(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 33%;">';
        $result .= $this->writeStockAdjustmentDetail();
        $result .= '</td>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the information delivery.
     *
     * @return string
     */
    private function getInformationDelivery(): string
    {
        $result = '<table class="table-info"  style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job Order
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeDeliveryDetail();
        $result .= '</td>';
        # Reference
        $result .= '<td style="width: 50%;">';
        $result .= $this->writeReferenceDetail();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to get Inklaring.
     *
     * @return string
     */
    private function writeInklaringDetail(): string
    {
        if (empty($this->Data) === false) {
            $soData = SalesOrderDao::getByReference($this->Data['jik_so_id']);
            $this->Data = array_merge($this->Data, $soData);
        }
        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('orderDate'),
            'value' => DateTimeParser::format($this->Data['so_order_date'], 'Y-m-d', 'd M Y'),
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('vendor'),
            'value' => $this->Data['jo_vendor'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picVendor'),
            'value' => $this->Data['jo_pic_vendor'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('customer'),
            'value' => $this->Data['so_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picCustomer'),
            'value' => $this->Data['so_pic_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the Reference Inklaring.
     *
     * @param string $prefix
     *
     * @return string
     */
    private function writeReferenceDetail($prefix = 'jo'): string
    {
        if (empty($prefix) === false) {
            $prefix .= '_';
        }

        $data[] = [
            'label' => Trans::getFinanceWord('soNumber'),
            'value' => $this->Data['so_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('customerRef'),
            'value' => $this->Data[$prefix . 'customer_ref'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('blRef'),
            'value' => $this->Data[$prefix . 'bl_ref'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('ajuRef'),
            'value' => $this->Data[$prefix . 'aju_ref'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('sppbRef'),
            'value' => $this->Data[$prefix . 'sppb_ref'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get Inbound Detail.
     *
     * @return string
     */
    private function writeJobOrderInboundDetail(): string
    {

        $data[] = [
            'label' => Trans::getFinanceWord('warehouse'),
            'value' => $this->Data['ji_warehouse'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('shipper'),
            'value' => $this->Data['ji_shipper'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picShipper'),
            'value' => $this->Data['ji_pic_shipper'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('transporter'),
            'value' => $this->Data['ji_vendor'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the Customer.
     *
     * @return string
     */
    private function writeJobOrder(): string
    {

        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('orderOffice'),
            'value' => $this->Data['jo_order_office'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('orderDate'),
            'value' => DateTimeParser::format($this->Data['jo_order_date'], 'Y-m-d', 'd M Y'),
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get Outbound Detail.
     *
     * @return string
     */
    private function writeJobOrderOutboundDetail(): string
    {
        $data[] = [
            'label' => Trans::getFinanceWord('warehouse'),
            'value' => $this->Data['job_warehouse'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('consignee'),
            'value' => $this->Data['job_consignee'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picConsignee'),
            'value' => $this->Data['job_pic_consignee'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('transporter'),
            'value' => $this->Data['job_vendor'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the stock movement.
     *
     * @return string
     */
    private function writeStockMovementDetail(): string
    {
        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('warehouse'),
            'value' => $this->Data['jm_wh_name'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('originStorage'),
            'value' => $this->Data['jm_whs_name'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('destinationStorage'),
            'value' => $this->Data['jm_destination_storage'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the stock movement job.
     *
     * @return string
     */
    private function writeStockMovementJobDetail(): string
    {
        $planningDate = '';
        if (empty($this->Data['jm_date']) === false) {
            if (empty($this->Data['jm_time']) === false) {
                $planningDate = DateTimeParser::format($this->Data['jm_date'] . ' ' . $this->Data['jm_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $planningDate = DateTimeParser::format($this->Data['jm_date'], 'Y-m-d', 'd M Y');
            }
        }
        $data[] = [
            'label' => Trans::getFinanceWord('planningDate'),
            'value' => $planningDate,
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('remark'),
            'value' => $this->Data['jm_remark'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the opname.
     *
     * @return string
     */
    private function writeOpnameDetail(): string
    {
        $planningDate = '';
        if (empty($this->Data['sop_date']) === false) {
            if (empty($this->Data['sop_time']) === false) {
                $planningDate = DateTimeParser::format($this->Data['sop_date'] . ' ' . $this->Data['sop_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $planningDate = DateTimeParser::format($this->Data['sop_date'], 'Y-m-d', 'd M Y');
            }
        }

        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('warehouse'),
            'value' => $this->Data['sop_warehouse'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('planningDate'),
            'value' => $planningDate,
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the Customer opname.
     *
     * @return string
     */
    private function writeOpnameCustomerDetail(): string
    {
        $data[] = [
            'label' => Trans::getFinanceWord('customer'),
            'value' => $this->Data['jo_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('customerRef'),
            'value' => $this->Data['jo_customer_ref'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picCustomer'),
            'value' => $this->Data['jo_pic_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('goods'),
            'value' => $this->Data['sop_gd_brand'] . ' ' . $this->Data['sop_gd_category'] . ' ' . $this->Data['sop_gd_name'] . ' ' . $this->Data['sop_gd_sku'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get the stock adjustment.
     *
     * @return string
     */
    private function writeStockAdjustmentDetail(): string
    {
        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('warehouse'),
            'value' => $this->Data['wh_name'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('customer'),
            'value' => $this->Data['jo_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('goods'),
            'value' => $this->Data['ja_goods'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to get delivery.
     *
     * @return string
     */
    private function writeDeliveryDetail(): string
    {

        $data[] = [
            'label' => Trans::getFinanceWord('jobNumber'),
            'value' => $this->Data['jo_number'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('service'),
            'value' => $this->Data['jo_service'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('serviceTerm'),
            'value' => $this->Data['jo_service_term'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('jobManager'),
            'value' => $this->Data['jo_manager'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('vendor'),
            'value' => $this->Data['jo_vendor'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picVendor'),
            'value' => $this->Data['jo_pic_vendor'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('customer'),
            'value' => $this->Data['jdl_so_customer'],
        ];
        $data[] = [
            'label' => Trans::getFinanceWord('picCustomer'),
            'value' => $this->Data['jdl_so_pic_customer'],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load time sheet data.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $imageNotFoundPath = asset('images/image-not-found.jpg');
        $events = JobActionEventDao::loadEventByJobId($this->getIntParameter('jac_jo_id'));
        if (empty($this->Data['jo_finish_on']) === false) {
            $time = DateTimeParser::format($this->Data['jo_finish_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('finish'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->Data['jo_finish_by'],
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('finish') . '"/>',
            ];
        }
        if (empty($this->Data['jo_document_on']) === false) {
            $time = DateTimeParser::format($this->Data['jo_document_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('documentComplete'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->Data['jo_document_by'],
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('documentComplete') . '"/>',
            ];
        }
        $docDao = new DocumentDao();
        foreach ($events as $row) {
            $image = '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            if (empty($row['doc_id']) === false) {
                $path = $docDao->getDocumentPath($row);
                $image = '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $path . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            }
            $time = '';
            if (empty($row['jae_date']) === false) {
                if (empty($row['jae_time']) === false) {
                    $time = DateTimeParser::format($row['jae_date'] . ' ' . $row['jae_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $time = DateTimeParser::format($row['jae_date'], 'Y-m-d', 'd M Y');
                }
            }

            $result[] = [
                'jae_action' => Trans::getWord($row['jae_action'] . $this->Data['jo_srt_id'] . '.description', 'action'),
                'jae_event' => $row['jae_description'],
                'jae_remark' => $row['remark'],
                'jae_time' => $time,
                'jae_creator' => $row['jae_created_by'],
                'jae_created_on' => DateTimeParser::format($row['jae_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                'image' => $image,
            ];
        }

        if (empty($this->Data['jo_publish_on']) === false) {
            $time = DateTimeParser::format($this->Data['jo_publish_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('published'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->Data['jo_publish_by'],
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('published') . '"/>',
            ];
        }

        $time = DateTimeParser::format($this->Data['jo_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
        $result[] = [
            'jae_action' => Trans::getWord('draft'),
            'jae_event' => '',
            'jae_remark' => '',
            'jae_time' => $time,
            'jae_creator' => $this->Data['jo_created_by'],
            'jae_created_on' => $time,
            'image' => '<img style="text-align: center" height="42" width="42" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('created') . '"/>',
        ];

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getLoadTimeSheetView(): string
    {
        $title = Trans::getWord('timeSheet');
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . $title . '</p>';
        $tbl = new TablePdf('timeSheetTbl');
        $tbl->setHeaderRow([
            'jae_action' => Trans::getWord('action'),
            'jae_event' => Trans::getWord('event'),
            'jae_remark' => Trans::getWord('remark'),
            'jae_time' => Trans::getWord('time'),
            'jae_creator' => Trans::getWord('reportedBy'),
            'jae_created_on' => Trans::getWord('reportedOn'),
            'image' => Trans::getWord('image'),
        ]);
        $tbl->addRows($this->loadTimeSheetData());
        $result .= $tbl->createTable();
        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function writeSignature(): string
    {
        $data = [
            [
                'label' => Trans::getWord('manager'),
                'name' => $this->Data['jo_manager'],
            ],
        ];
        $label = date('d M Y');

        $result = '<p class="pdf-date-label" style="font-weight: bold">Jakarta ' . $label . '</p>';
        $result .= $this->generateSignatureView($data);

        return $result;
    }

    /**
     * Function load data
     *
     * @return void
     */
    private function loadData(): void
    {
        if ($this->isValidParameter('jac_jo_id') === false) {
            Message::throwMessage('Invalid parameter for reference value.', 'ERROR');
        } else {
            # Get data from dao
            if ($this->getStringParameter('jo_srv_code') === "inklaring") {
                $this->Data = JobInklaringDao::getByReferenceAndSystemSetting($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhInbound") {
                $this->Data = JobInboundDao::getByJobOrderAndSystemSetting($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhOutbound") {
                $this->Data = JobOutboundDao::getByJoIdAndSystem($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhStockMovement") {
                $this->Data = JobMovementDao::getByJobIdAndSystem($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhOpname") {
                $this->Data = StockOpnameDao::getByJoIdAndSystem($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srt_route') === "joWhStockAdjustment") {
                $this->Data = JobAdjustmentDao::getByJoIdAndSystem($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
            if ($this->getStringParameter('jo_srv_code') === "delivery") {
                $this->Data = JobDeliveryDao::getByJobIdAndSystem($this->getIntParameter('jac_jo_id'), $this->User->getSsId());
            }
        }
    }

}
