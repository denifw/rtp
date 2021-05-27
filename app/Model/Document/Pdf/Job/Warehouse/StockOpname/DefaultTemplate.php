<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockOpname;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\StockOpnameDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\WarehouseDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

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
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('stockOpname') . '.pdf');
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
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 5;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');

            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['sop_wh_id']);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('stockOpname')));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
            $this->MPdf->WriteHTML($this->getSignature());
        } catch (Exception $e) {
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
        $result .= '<td width="50%">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        # warehouse info
        $result .= '<td width="50%">';
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
        $data = [
            [
                'label' => Trans::getWord('jobNumber'),
                'value' => $this->JobOrder['jo_number'],
            ],
            [
                'label' => Trans::getWord('opnameDate'),
                'value' => DateTimeParser::format($this->JobOrder['sop_start_on'], 'Y-m-d H:i:s', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->JobOrder['jo_customer'],
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->JobOrder['jo_customer_ref'],
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->JobOrder['jo_pic_customer'],
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
                'value' => $this->JobOrder['sop_warehouse'],
            ],
            [
                'label' => Trans::getWord('address'),
                'value' => $strAddress,
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
    protected function getGoodsView(): string
    {
        $data = StockOpnameDetailDao::getByStockOpnameId($this->JobOrder['sop_id']);
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail') . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'sod_whs_name' => Trans::getWord('storage'),
            'sod_gd_sku' => Trans::getWord('sku'),
            'sod_goods' => Trans::getWord('goods'),
            'sod_production_number' => Trans::getWord('productionNumber'),
            'sod_serial_number' => Trans::getWord('serialNumber'),
            'sod_gdt_description' => Trans::getWord('damageType'),
            'sod_quantity' => Trans::getWord('currentStock'),
            'sod_qty_figure' => Trans::getWord('stockFigure'),
            'qty_diff' => Trans::getWord('diffQuantity'),
            'sod_remark' => Trans::getWord('remark'),
        ]);
        // $tbl->addColumnAttribute('sod_gd_name', 'style', 'width: 20%');
        $results = [];
        $i = 0;
        $number = new NumberFormatter();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['sod_goods'] = $gdDao->formatFullName($row['sod_gd_category'], $row['sod_gd_brand'], $row['sod_gd_name']);
            $diff = (float)$row['sod_qty_figure'] - (float)$row['sod_quantity'];
            $row['qty_diff'] = $number->doFormatFloat($diff) . ' ' . $row['sod_gdu_uom'];

            $row['sod_qty_figure'] = $number->doFormatFloat($row['sod_qty_figure']) . ' ' . $row['sod_gdu_uom'];
            $row['sod_quantity'] = $number->doFormatFloat($row['sod_quantity']) . ' ' . $row['sod_gdu_uom'];
            if ($diff > 0) {
                $tbl->addCellAttribute('qty_diff', $i, 'style', 'background-color: orange; color: white; text-align: right;');
            } elseif ($diff < 0) {
                $tbl->addCellAttribute('qty_diff', $i, 'style', 'background-color: red; color: white; text-align: right;');
            } else {
                $tbl->addCellAttribute('qty_diff', $i, 'style', 'color: black; text-align: right;');
            }

            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i, 'class', 'even');
            }
            $i++;
            $results[] = $row;
        }

        $tbl->addRows($results);
        $tbl->addColumnAttribute('sod_qty_figure', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sod_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sod_production_number', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('sod_serial_number', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('sod_gd_sku', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('sod_gdt_description', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('sod_whs_name', 'style', 'text-align: center;');
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
                'label' => Trans::getWord('approveBy'),
                'name' => $this->JobOrder['jo_pic_customer'],
            ],
            [
                'label' => Trans::getWord('opnameBy'),
                'name' => $this->JobOrder['jo_manager'],
            ],
            [
                'label' => Trans::getWord('createdBy'),
                'name' => $this->User->getName(),
            ],
        ];
        $label = '';
        if (empty($this->Warehouse['wh_state']) === false) {
            $label .= $this->Warehouse['wh_state'] . ', ';
        }
        $label .= date('d M Y');

        $result = '<p class="pdf-date-label">' . $label . '</p>';
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
        $this->JobOrder = StockOpnameDao::getByJoIdAndSystem($this->getIntParameter('jo_id'), $this->User->getSsId());
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
