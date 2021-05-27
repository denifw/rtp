<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockAdjustment;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDetailDao;
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
        parent::__construct(Trans::getWord('stockAdjustment' ) . '.pdf');
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

            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['ja_wh_id']);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('stockAdjustment' )));
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
                'label' => Trans::getWord('customer'),
                'value' => $this->JobOrder['jo_customer'],
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->JobOrder['jo_pic_customer'],
            ]
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
                'value' => $this->JobOrder['wh_name'],
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
        $data = JobAdjustmentDetailDao::loadDataByJaId($this->JobOrder['ja_id']);
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail' ) . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'jad_jo_number' => Trans::getWord('inboundNumber'),
            'jad_inbound_on' => Trans::getWord('inboundDate'),
            'jad_whs_name' => Trans::getWord('storage'),
            'jad_lot_number' => Trans::getWord('lotNumber'),
            'jad_serial_number' => Trans::getWord('serialNumber'),
            'jad_quantity' => Trans::getWord('qtyAdjustment'),
            'jad_sat_description' => Trans::getWord('adjustmentType'),
            'jad_remark' => Trans::getWord('remark')
        ]);

        $rows = [];
        $i = 1;
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['jad_inbound_on'] = DateTimeParser::format($row['jad_inbound_on'], 'Y-m-d H:i:s', 'd.M.Y');
            $row['jad_quantity'] = $number->doFormatFloat($row['jad_quantity']) . ' ' . $row['jad_uom'];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
            $rows[] = $row;
        }

        $tbl->addRows($rows);
        $tbl->addColumnAttribute('jad_jid_stock', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('jad_quantity', 'style', 'text-align: right;');
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
                'label' => Trans::getWord('jobManager'),
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
        $this->JobOrder = JobAdjustmentDao::getByJoIdAndSystem($this->getIntParameter('jo_id'), $this->User->getSsId());
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
