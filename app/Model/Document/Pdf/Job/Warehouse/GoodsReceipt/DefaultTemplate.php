<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\GoodsReceipt;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
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
     *
     * @param string $pageSize To store the page size of the pdf.
     *
     */
    public function __construct(string $pageSize = 'A4')
    {
        parent::__construct(Trans::getWord('goodsReceipt') . '.pdf', $pageSize);
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

            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['ji_wh_id']);

            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('goodsReceipt')));
            $this->MPdf->WriteHTML($this->getJobInformation());

            $this->MPdf->WriteHTML($this->getGoodsReceived());
            $this->MPdf->WriteHTML($this->getGoodsReturn());
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
        $result = '<table class="table-info"  style="width: 100%; font-weight: bold;">';
        $result .= '<tr>';
        # General Job
        $result .= '<td style="width:33%;">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        # Shipper Job
        $result .= '<td style="width:33%;">';
        $result .= $this->getShipperView();
        $result .= '</td>';
        # warehouse info
        $result .= '<td style="width:33%;">';
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
    protected function getGoodsReceived(): string
    {
        $goods = $this->loadGoodsReceiveData();
        $result = '';
        $result .= '<p class="title-4"  style="font-weight: bold;"> ' . Trans::getWord('goodsReceived') . '</p>';
        $tbl = new TablePdf('damageTbl');
        $tbl->setHeaderRow([
            'jog_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('qtyPlanning'),
            'jog_qty_received' => Trans::getWord('qtyReceived'),
            'jog_unit' => Trans::getWord('uom'),
            'jog_total_weight' => Trans::getWord('weight') . ' (KG)',
            'jog_remarks' => Trans::getWord('notes'),
        ]);
        $rows = [];
        $i = 0;
        foreach ($goods as $row) {
            $rows[] = $row;
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
        }
        $tbl->addRows($rows);
        $tbl->setColumnType('jog_quantity', 'float');
        $tbl->setColumnType('jog_qty_received', 'float');
        $tbl->setColumnType('jog_total_weight', 'float');
        $tbl->addColumnAttribute('jog_unit', 'style', 'text-align: center;');
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
                'label' => Trans::getWord('broughtBy'),
                'name' => $this->JobOrder['ji_driver'],
            ],
            [
                'label' => Trans::getWord('receiveBy'),
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

        $result = '<div class="pdf-date-label" style="font-weight: bold">' . $label . '</div>';
        $result .= $this->generateSignatureView($data);

        return $result;
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getGoodsReturn(): string
    {
        $returnGoods = $this->loadGoodsReturnData();
        $result = '';
        if (empty($returnGoods) === false) {
            $result .= '<p class="title-4"   style="font-weight: bold"> ' . Trans::getWord('goodsReturned') . '</p>';
            $tbl = new TablePdf('returnTbl');
            $tbl->setHeaderRow([
                'jog_sku_rt' => Trans::getWord('sku'),
                'jog_goods_rt' => Trans::getWord('goods'),
                'jog_qty_received_rt' => Trans::getWord('qtyReturned'),
                'jog_unit_rt' => Trans::getWord('uom'),
                'jog_total_weight_rt' => Trans::getWord('weight') . ' (KG)',
                'jog_remarks_rt' => Trans::getWord('notes'),
            ]);
            $rows = [];
            $i = 0;
            $keys = array_keys($returnGoods[0]);
            foreach ($returnGoods as $row) {
                $temp = [];
                foreach ($keys as $key) {
                    $temp[$key . '_rt'] = $row[$key];
                }
                $rows[] = $temp;
                if (($i % 2) === 0) {
                    $tbl->addRowAttribute($i - 1, 'class', 'even');
                }
                $i++;
            }
            $tbl->addRows($rows);
            $tbl->setColumnType('jog_quantity_rt', 'float');
            $tbl->setColumnType('jog_qty_received_rt', 'float');
            $tbl->setColumnType('jog_total_weight_rt', 'float');
            $tbl->addColumnAttribute('jog_unit_rt', 'style', 'text-align: center;');
            $result .= $tbl->createTable();
        }

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return array
     */
    protected function loadGoodsReceiveData(): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->JobOrder['jo_id'] . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $temp = JobGoodsDao::loadDataForInbound($wheres);
        return JobGoodsDao::doPrepareDataForInbound($temp);
    }

    /**
     * Function to load the html content.
     *
     * @return array
     */
    protected function loadGoodsReturnData(): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->JobOrder['jo_id'] . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'N')";
        $temp = JobGoodsDao::loadDataForInbound($wheres);
        return JobGoodsDao::doPrepareDataForInbound($temp);
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
                'label' => Trans::getWord('pic'),
                'value' => $this->JobOrder['jo_pic_customer'],
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->JobOrder['jo_customer_ref'],
            ],
            [
                'label' => Trans::getWord('blRef'),
                'value' => $this->JobOrder['jo_bl_ref'],
            ],
            [
                'label' => Trans::getWord('ajuRef'),
                'value' => $this->JobOrder['jo_aju_ref'],
            ],
            [
                'label' => Trans::getWord('sppbRef'),
                'value' => $this->JobOrder['jo_sppb_ref'],
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getShipperView(): string
    {
        $data = [
            [
                'label' => Trans::getWord('eta'),
                'value' => DateTimeParser::format($this->JobOrder['ji_eta_date'] . ' ' . $this->JobOrder['ji_eta_time'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('shipper'),
                'value' => $this->JobOrder['ji_shipper'],
            ],
            [
                'label' => Trans::getWord('picShipper'),
                'value' => $this->JobOrder['ji_pic_shipper'],
            ],
            [
                'label' => Trans::getWord('transporter'),
                'value' => $this->JobOrder['ji_vendor'],
            ],
            [
                'label' => Trans::getWord('driver'),
                'value' => $this->JobOrder['ji_driver'],
            ],
            [
                'label' => Trans::getWord('truckPlate'),
                'value' => $this->JobOrder['ji_truck_number'],
            ],
            [
                'label' => Trans::getWord('containerNumber'),
                'value' => $this->JobOrder['ji_container_number'],
            ],
            [
                'label' => Trans::getWord('sealNumber'),
                'value' => $this->JobOrder['ji_seal_number'],
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getWarehouseView(): string
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
                'value' => $this->JobOrder['ji_warehouse'],
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
                'value' => DateTimeParser::format($this->JobOrder['ji_ata_date'] . ' ' . $this->JobOrder['ji_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('startUnload'),
                'value' => DateTimeParser::format($this->JobOrder['ji_start_load_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
            [
                'label' => Trans::getWord('completeUnload'),
                'value' => DateTimeParser::format($this->JobOrder['ji_end_load_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
            ],
        ];

        return $this->createTableView($data, false);
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
        $this->JobOrder = JobInboundDao::getByJobOrderAndSystemSetting($this->getIntParameter('jo_id'), $this->User->getSsId());
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
