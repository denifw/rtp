<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockMovement;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;
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
        parent::__construct(Trans::getWord('stockMovement') . '.pdf');
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

            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['jm_wh_id']);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('stockMovement')));
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
                'label' => Trans::getWord('date'),
                'value' => DateTimeParser::format($this->JobOrder['jm_date'], 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->JobOrder['jo_manager'],
            ],
            [
                'label' => Trans::getWord('remark'),
                'value' => $this->JobOrder['jm_remark'],
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
                'value' => $this->JobOrder['jm_wh_name'],
            ],
            [
                'label' => Trans::getWord('originStorage'),
                'value' => $this->JobOrder['jm_whs_name'],
            ],
            [
                'label' => Trans::getWord('destinationStorage'),
                'value' => $this->JobOrder['jm_destination_storage'],
            ],
            [
                'label' => Trans::getWord('address'),
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
    protected function getGoodsView(): string
    {
        $data = JobMovementDetailDao::loadDataByJmId($this->JobOrder['jm_id']);
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail') . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'jmd_gd_sku' => Trans::getWord('sku'),
            'jmd_gd_name' => Trans::getWord('goods'),
            'jmd_jid_lot_number' => Trans::getWord('lot'),
            'jmd_jid_serial_number' => Trans::getWord('serialNumber'),
            'jmd_jir_condition' => Trans::getWord('condition'),
            'jmd_quantity' => Trans::getWord('quantity'),
            'jmd_gdt_description' => Trans::getWord('damageType'),
            'jmd_gcd_description' => Trans::getWord('causeDamage'),
        ]);
        $tbl->addColumnAttribute('jmd_jir_condition', 'style', 'text-align: center;');

        $rows = [];
        $i = 1;
        $number = new NumberFormatter();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            if (empty($row['jmd_jid_gdt_id']) === false) {
                $row['jmd_jir_condition'] = Trans::getWord('damage');
            } else {
                $row['jmd_jir_condition'] = Trans::getWord('good');
            }
            $row['jmd_gd_name'] = $gdDao->formatFullName($row['jmd_gdc_name'], $row['jmd_br_name'], $row['jmd_gd_name']);
            $row['jmd_quantity'] = $number->doFormatFloat($row['jmd_quantity']) . ' ' . $row['jmd_gdu_uom'];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }

            $i++;
            $rows[] = $row;
        }
        $tbl->addRows($rows);
        $tbl->addColumnAttribute('jmd_jid_lot_number', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('jmd_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('jmd_jid_serial_number', 'style', 'text-align: center;');
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
                'label' => Trans::getWord('createdBy'),
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
        $this->JobOrder = JobMovementDao::getByJobIdAndSystem($this->getIntParameter('jo_id'), $this->User->getSsId());
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
