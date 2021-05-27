<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Document\Pdf\Job\Delivery\Di;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 *
 *
 * @package    app
 * @subpackage Trucking/Di
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class BaseTemplate extends AbstractBasePdf
{

    /**
     * Property to store job order data.
     *
     * @var array
     */
    protected $JobOrder = [];

    /**
     * DeliveryInstruction constructor.
     *
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('deliveryInstruction') . '.pdf');
    }

    /**
     * Function load Content
     *
     */
    public function loadContent(): void
    {
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $header = $this->getDefaultHeader($this->User->getRelId());
            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryInstruction')));
            $this->MPdf->WriteHTML($this->getDIInformation());
            if ($this->JobOrder['jo_srt_route'] === 'joTruck') {
                $this->MPdf->WriteHTML($this->getLoadUnloadView('L'));
                $this->MPdf->WriteHTML($this->getLoadUnloadView('U'));
            } elseif ($this->JobOrder['jo_srt_route'] === 'joTruckImp') {
                $this->MPdf->WriteHTML($this->getDepoPickingView());
                $this->MPdf->WriteHTML($this->getLoadUnloadView('U'));
                $this->MPdf->WriteHTML($this->getDepoReturnView());
            } else {
                $this->MPdf->WriteHTML($this->getDepoPickingView());
                $this->MPdf->WriteHTML($this->getLoadUnloadView('L'));
                $this->MPdf->WriteHTML($this->getDepoReturnView());
            }
            $this->MPdf->WriteHTML($this->getSignature());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }

    }

    /**
     * Function getDIInformation
     *
     * @return string
     */
    protected function getDIInformation(): string
    {
        $result = '<table class="table-info" style="font-weight: bold; width: 100%">';
        $result .= '<tr>';
        $result .= '<td style="width: 33%;">';
        # Vendor
        $result .= $this->getVendorDelivery();
        $result .= '</td>';
        $result .= '<td style="width: 33%;">';
        # Customer
        $result .= $this->getGeneralView();
        $result .= '</td>';
        $result .= '<td  style="width: 33%;">';
        # Reference Delivery
        $result .= $this->getReferenceDeliveryInstruction();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function getGenerateView
     *
     * @return string
     */
    private function getGeneralView(): string
    {
        $data = [
            [
                'label' => Trans::getTruckingWord('jobNumber'),
                'value' => $this->JobOrder['jo_number'],
            ],
            [
                'label' => Trans::getTruckingWord('jobManager'),
                'value' => $this->JobOrder['jo_manager'],
            ],
            [
                'label' => Trans::getTruckingWord('customer'),
                'value' => $this->JobOrder['jo_customer'],
            ],
            [
                'label' => Trans::getTruckingWord('customerRef'),
                'value' => $this->JobOrder['jo_customer_ref'],
            ],
            [
                'label' => Trans::getTruckingWord('picCustomer'),
                'value' => $this->JobOrder['jo_pic'],
            ],
        ];
        return $this->createTableView($data, false);
    }

    /**
     * Function Vendor Delivery
     *
     * @return string
     */
    private function getVendorDelivery(): string
    {
        $data = [
            [
                'label' => Trans::getTruckingWord('vendor'),
                'value' => $this->JobOrder['jo_vendor'],
            ],
            [
                'label' => Trans::getTruckingWord('containerType'),
                'value' => $this->JobOrder['jt_ct_name'],
            ],
            [
                'label' => Trans::getTruckingWord('truckType'),
                'value' => $this->JobOrder['jt_eg_name'],
            ],
            [
                'label' => Trans::getTruckingWord('truckPlate'),
                'value' => $this->JobOrder['jt_eq_number'],
            ],
            [
                'label' => Trans::getTruckingWord('mainDriver'),
                'value' => $this->JobOrder['jt_first_driver'],
            ],
            [
                'label' => Trans::getTruckingWord('secondaryDriver'),
                'value' => $this->JobOrder['jt_second_driver'],
            ],
        ];
        return $this->createTableView($data, false);
    }

    /**
     * Function getReferenceDeliveryInstruction
     *
     * @return string
     */
    private function getReferenceDeliveryInstruction(): string
    {
        $data = [
            [
                'label' => Trans::getTruckingWord('blRef'),
                'value' => $this->JobOrder['jo_bl_ref'],
            ],
            [
                'label' => Trans::getTruckingWord('listRef'),
                'value' => $this->JobOrder['jo_packing_ref'],
            ],
            [
                'label' => Trans::getTruckingWord('ajuRef'),
                'value' => $this->JobOrder['jo_aju_ref'],
            ],
            [
                'label' => Trans::getTruckingWord('sppbRef'),
                'value' => $this->JobOrder['jo_sppb_ref'],
            ],
        ];
        if ($this->JobOrder['jo_srt_route'] !== 'joTruck') {
            $data[] = [
                'label' => Trans::getWord('containerNumber'),
                'value' => $this->JobOrder['jt_container_number'],
            ];
            $data[] = [
                'label' => Trans::getWord('sealNumber'),
                'value' => $this->JobOrder['jt_seal_number'],
            ];
        }
        return $this->createTableView($data);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getDepoPickingView(): string
    {
        $dateFormatter = new DateTimeParser();
        $office = OfficeDao::getByReference($this->JobOrder['jt_dp_id']);
        $address = $this->JobOrder['jt_dp_address'] . ', ' . $office['of_address'] . ', ' . $office['of_district'];
        $result = '<p class="title-4" style="font-weight: bold"> ' . Trans::getTruckingWord('depoPickUpContainer') . '</p>';
        $result .= $this->createTableView([
            [
                'label' => Trans::getWord('depoOwner'),
                'value' => $this->JobOrder['jt_rel_dp'],
            ],
            [
                'label' => Trans::getWord('address'),
                'value' => $address,
            ],
            [
                'label' => Trans::getWord('eta'),
                'value' => $dateFormatter->formatDateTime($this->JobOrder['jt_pick_date'] . ' ' . $this->JobOrder['jt_pick_time']),
            ],
        ], false, true, 'table-info');
        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getDepoReturnView(): string
    {
        $dateFormatter = new DateTimeParser();
        $office = OfficeDao::getByReference($this->JobOrder['jt_dr_id']);
        $address = $this->JobOrder['jt_dr_address'] . ', ' . $office['of_address'] . ', ' . $office['of_district'];
        $result = '<p class="title-4" style="font-weight: bold"> ' . Trans::getTruckingWord('depoReturnContainer') . '</p>';
        $result .= $this->createTableView([
            [
                'label' => Trans::getWord('depoOwner'),
                'value' => $this->JobOrder['jt_rel_dr'],
            ],
            [
                'label' => Trans::getWord('address'),
                'value' => $address,
            ],
            [
                'label' => Trans::getWord('eta'),
                'value' => $dateFormatter->formatDateTime($this->JobOrder['jt_return_date'] . ' ' . $this->JobOrder['jt_return_time']),
            ],
        ], false, true, 'table-info');
        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @param string $type To store the type of location.
     *
     * @return string
     */
    protected function getLoadUnloadView(string $type): string
    {
        $title = Trans::getTruckingWord('loadingAddress');
        if ($type === 'U') {
            $title = Trans::getTruckingWord('unloadingAddress');
        }
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . $title . '</p>';
        $tbl = new TablePdf('job' . $type . 'Tbl');
        $tbl->setHeaderRow([
            'jtd_relation' => Trans::getWord('relation'),
            'jtd_address' => Trans::getWord('address'),
            'jtd_reference' => Trans::getWord('reference'),
            'jtd_goods' => Trans::getWord('goods'),
            'jtd_quantity' => Trans::getWord('quantity'),
            'jtd_eta' => Trans::getWord('eta'),
        ]);
        $tbl->addRows($this->loadLoadingUnloadData($type));
        $result .= $tbl->createTable();
        return $result;
    }


    /**
     * Function to load loading and unloading location data.
     *
     * @param string $type To store the type of location.
     *
     * @return array
     */
    protected function loadLoadingUnloadData(string $type): array
    {
//        $data = JobTruckingDetailDao::getByJobIdAndType($this->JobOrder['jt_id'], $type);
//        $results = [];
//        $formatter = new StringFormatter();
//        $dt = new DateTimeParser();
//        $number = new NumberFormatter();
//        foreach ($data as $row) {
//            $row['jtd_address'] = $row['jtd_office'] . '<br/>' . $formatter->doFormatAddress($row);
//            $eta = '';
//            if (empty($row['jtd_eta_date']) === false) {
//                if (empty($row['jtd_eta_time']) === false) {
//                    $eta = $dt->formatDateTime($row['jtd_eta_date'] . ' ' . $row['jtd_eta_time']);
//                } else {
//                    $eta = $dt->formatDate($row['jtd_eta_date']);
//                }
//            }
//            $row['jtd_eta'] = $eta;
//            if (empty($row['jtd_pic']) === false) {
//                $row['jtd_relation'] .= '<br/>PIC : ' . $row['jtd_pic'];
//            }
//            $row['jtd_quantity'] = $number->doFormatFloat($row['jtd_quantity']);
//            if (empty($row['jtd_unit']) === false) {
//                $row['jtd_quantity'] .= ' ' . $row['jtd_unit'];
//            }
//            $results[] = $row;
//        }
//        return $results;
        return [];
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getSignature(): string
    {
        $relation = RelationDao::loadDataForDocumentHeader($this->User->getRelId());
        $label = '';
        $label .= $relation['stt_name'] . ', ' . date('d M Y');
        $result = '<p class="pdf-date-label" style="font-weight: bold;">' . $label . '</p>';
        $result .= '<table class="table-signature" style="font-weight: bold;">';
        $result .= '<tr>';
        $result .= '<td style="width: 33%"></td>';
        $result .= '<td style="width: 33%"></td>';
        $result .= '<td style="width: 33%">' . Trans::getWord('createdBy') . '</td>';
        $result .= '</tr>';
        $result .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $result .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $result .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $result .= '<tr>';
        $result .= '<td></td>';
        $result .= '<td></td>';
        $result .= '<td><u>' . $this->User->getName() . '</u></td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }

    /**
     * Function load data from Dao
     */
    private function loadData(): void
    {
        if ($this->isValidParameter('jo_id') === false) {
            Message::throwMessage(Trans::getWord('invalidRequestIdPdfTrucking', 'message'));
        } else {
            $this->JobOrder = [];
        }
    }
}
