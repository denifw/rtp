<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\GoodsOut;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\WarehouseDao;
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
     * @var array $Warehouse
     */
    protected $Warehouse = [];

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
        parent::__construct(Trans::getWord('outboundOfGoods' ) . '.pdf');
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
            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['job_wh_id']);
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->JobOrder['job_id'] . ')';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $this->Goods = JobOutboundDetailDao::loadData($wheres);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('outboundOfGoods' )));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
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
        $result = '<table width="100%" class="table-info"  style="font-weight: bold">';
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
        $data = [];
        $data[] = [
            'label' => Trans::getWord('jobNumber'),
            'value' => $this->JobOrder['jo_number'],
        ];
        if(empty($this->JobOrder['so_id']) === false) {
            $data[] = [
                'label' => Trans::getWord('soNumber'),
                'value' => $this->JobOrder['so_number'],
            ];
        }
        $data[] = [
            'label' => Trans::getWord('customer'),
                'value' => $this->JobOrder['jo_customer'],
        ];
        $data[] = [
            'label' => Trans::getWord('customerRef'),
                'value' => $this->JobOrder['jo_customer_ref'],
        ];
        $data[] = [
            'label' => Trans::getWord('blRef'),
                'value' => $this->JobOrder['jo_bl_ref'],
        ];
        $data[] = [
            'label' => Trans::getWord('ajuRef'),
                'value' => $this->JobOrder['jo_aju_ref'],
        ];
        $data[] = [
            'label' => Trans::getWord('sppbRef'),
                'value' => $this->JobOrder['jo_sppb_ref'],
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
                'label' => Trans::getWord('eta'),
                'value' => DateTimeParser::format($this->JobOrder['job_eta_date']. ' '.$this->JobOrder['job_eta_time'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('consignee'),
                'value' => $this->JobOrder['job_consignee'],
            ],
            [
                'label' => Trans::getWord('picConsignee'),
                'value' => $this->JobOrder['job_pic_consignee'],
            ],
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
        $strAddress = '';
        if (empty($this->Warehouse) === false) {
            $address = [];
            $address[] = $this->Warehouse['wh_address'];
            if (empty($this->Warehouse['wh_district']) === false) {
                $address[] = $this->Warehouse['wh_district'];
                $address[] = $this->Warehouse['wh_city'];
                $address[] = $this->Warehouse['wh_state'];
                $address[] = $this->Warehouse['wh_country'];
            }
            if (empty($this->Warehouse['wh_postal_code']) === false) {
                $address[] = $this->Warehouse['wh_postal_code'];
            }

            $strAddress = implode(', ', $address);
        }
        $data = [
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->JobOrder['job_warehouse'],
            ],
            [
                'label' => Trans::getWord('address'),
                'value' => $strAddress,
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->JobOrder['jo_manager'],
            ],
            [
                'label' => Trans::getWord('ata'),
                'value' => DateTimeParser::format($this->JobOrder['job_ata_date']. ' '.$this->JobOrder['job_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('startLoad'),
                'value' => DateTimeParser::format($this->JobOrder['job_start_load_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('completeLoad'),
                'value' => DateTimeParser::format($this->JobOrder['job_end_load_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
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
            'jod_lot_number' => Trans::getWord('lot' ),
            'jod_storage' => Trans::getWord('storage'),
            'jod_quantity' => Trans::getWord('quantity'),
            'jod_total_weight' => Trans::getWord('weight' ) . ' (KG)',
            'jod_condition' => Trans::getWord('condition' ),
        ]);
        $rows = [];
        $i = 0;
        $gdDao = new GoodsDao();
        $number = new NumberFormatter();
        foreach ($this->Goods as $row) {
            if(empty($row['jid_gdt_id']) === true) {
                $row['jod_condition'] = Trans::getWord('good');
            } else {
                $row['jod_condition'] = $row['jod_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jod_gcd_description'];
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
        $tbl->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
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
    protected function getSignature(): string
    {
        $data = [
            [
                'label' => Trans::getWord('broughtBy' ),
                'name' => $this->JobOrder['job_driver'],
            ],
            [
                'label' => Trans::getWord('issuedBy' ),
                'name' => $this->JobOrder['jo_manager'],
            ],
            [
                'label' => Trans::getWord('createdBy' ),
                'name' => $this->User->getName(),
            ],
        ];
        $label = '';
        if (empty($this->Warehouse['wh_state']) === false) {
            $label .= $this->Warehouse['wh_state'] . ', ';
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
