<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Document\Pdf\AbstractBasePdf;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends AbstractBasePdf
{

    /**
     * Property to store the job detail.
     *
     * @var array $JobOrder
     */
    protected $JobOrder = [];


    /**
     * Property to store the job detail.
     *
     * @var array $Consignee
     */
    protected $Consignee = [];

    /**
     * Property to store the job detail.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('deliveryOrder' ) . '.pdf');
    }

    /**
     * Function to set the content to pdf.
     *
     * @return void
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
            $this->Consignee = RelationDao::loadDataForDocumentHeader($this->JobOrder['job_rel_id'], (int)$this->JobOrder['job_of_id']);
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->JobOrder['job_id'] . ')';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $this->Goods = JobOutboundDetailDao::loadData($wheres);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryOrder' )));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
            $this->MPdf->WriteHTML($this->getReceiveView());
            $this->MPdf->WriteHTML($this->getSignature());
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getJobInformation(): string
    {
        $result = '<table width="100%" class="table-info" style="font-weight: bold">';
        $result .= '<tr>';
        # General Job
        $result .= '<td width="33%">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        # Shipper Job
        $result .= '<td width="33%">';
        $result .= $this->getShipperView();
        $result .= '</td>';
        # warehouse info
        $result .= '<td width="33%">';
        $result .= $this->getWarehouseView();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getGeneralView(): string
    {
        $strAddress = '';
        if (empty($this->Consignee) === false) {
            $address = [];
            $address[] = $this->Consignee['of_address'];
            if (empty($this->Consignee['dtc_name']) === false) {
                $address[] = $this->Consignee['dtc_name'];
                $address[] = $this->Consignee['cty_name'];
                $address[] = $this->Consignee['stt_name'];
                $address[] = $this->Consignee['cnt_name'];
            }
            if (empty($this->Consignee['of_postal_code']) === false) {
                $address[] = $this->Consignee['of_postal_code'];
            }

            $strAddress = $this->Consignee['rel_name'] . implode(', ', $address);
        }
        $data = [
            [
                'label' => Trans::getWord('shipTo' ),
                'value' => $strAddress,
            ],
            [
                'label' => Trans::getWord('billTo' ),
                'value' => $strAddress,
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getShipperView(): string
    {
        $data = [
            [
                'label' => Trans::getWord('transporter' ),
                'value' => $this->JobOrder['job_vendor'],
            ],
            [
                'label' => Trans::getWord('driver' ),
                'value' => $this->JobOrder['job_driver'],
            ],
            [
                'label' => Trans::getWord('truckPlate'),
                'value' => $this->JobOrder['job_truck_number'],
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->JobOrder['jo_manager'],
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getWarehouseView(): string
    {

        $data = [
            [
                'label' => Trans::getWord('jobNumber'),
                'value' => $this->JobOrder['jo_number'],
            ],
            [
                'label' => Trans::getWord('outboundDate' ),
                'value' => DateTimeParser::format($this->JobOrder['job_ata_date'], 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->JobOrder['job_warehouse'],
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getGoodsView(): string
    {
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail' ) . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'jod_goods' => Trans::getWord('goods'),
            'jod_quantity' => Trans::getWord('quantity'),
            'jod_total_weight' => Trans::getWord('weight' ) . ' (KG)',
            'jod_gdt_code' => Trans::getWord('condition'),
        ]);
        $rows = [];
        $i = 0;
        $number = new NumberFormatter();
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            if(empty($row['jid_gdt_id']) === true) {
                $row['jod_gdt_code'] = Trans::getWord('good');
            }
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
            $row['jod_quantity'] = $number->doFormatFloat($row['jod_qty_loaded']) . ' ' . $row['jod_unit'];
            $row['jod_total_weight'] = (float)$row['jod_qty_loaded'] * (float)$row['jod_weight'];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $rows[] = $row;
            $i++;
        }

        $tbl->addRows($rows);
        $tbl->setColumnType('jod_total_weight', 'float');
        $tbl->addColumnAttribute('jod_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('jod_gdt_code', 'style', 'text-align: center;');
        $result .= $tbl->createTable();

        return $result;
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getReceiveView(): string
    {
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('receiveStatus' ) . '</p>';
        $tbl = new TablePdf('goodsRcTbl');
        $tbl->setHeaderRow([
            'jod_goods' => Trans::getWord('goods'),
            'good' => Trans::getWord('qtyGoodReceived'),
            'damage' => Trans::getWord('qtyDamageReceived'),
            'description' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . Trans::getWord('notes') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        ]);
        $rows = [];
        $i = 0;
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
            $row['good'] = $row['jod_unit'];
            $row['damage'] = $row['jod_unit'];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $rows[] = $row;
            $i++;
        }
        $tbl->addRows($rows);
        $tbl->addColumnAttribute('good', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('damage', 'style', 'text-align: right;');
        $result .= $tbl->createTable();

        return $result;
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getSignature(): string
    {
        $data = [
            [
                'label' => Trans::getWord('receiver' ),
                'name' => '',
            ],
            [
                'label' => Trans::getWord('deliverBy' ),
                'name' => $this->JobOrder['job_driver'],
            ],
        ];
        $label = '';
        if (empty($this->Consignee['stt_name']) === false) {
            $label .= $this->Consignee['stt_name'] . ', ';
        }
        $label .= date('d M Y');

        $result = '<p class="pdf-date-label" style="font-weight: bold">' . $label . '</p>';
        $result .= $this->generateSignatureView($data);

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return void
     */
    protected function loadData(): void
    {
        if ($this->isValidParameter('jo_id') === false) {
            Message::throwMessage('Invalid parameter for jo_id.');
        }
        $this->JobOrder = JobOutboundDao::getByJoIdAndSystem($this->getIntParameter('jo_id'), $this->User->getSsId());
        if (empty($this->JobOrder) === true) {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROE');
        }
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }
}
