<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Makmur Berkat Teknologi
 */

namespace App\Model\Document\Pdf\Job\Warehouse\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 * Class to generate the delivery order size Pos pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Makmur Berkat Teknologi
 */
class PosTemplate extends AbstractBasePdf
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
        parent::__construct(Trans::getWord('deliveryOrder') . '.pdf');
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
            $orientation = 'P';
            $this->MPdf->_setPageSize('A6', $orientation);
            $this->MPdf->AddPage('', '', '', '1', '', 2, 2, 5, 1, 5, 5);
            $this->Consignee = RelationDao::loadDataForDocumentHeader($this->JobOrder['job_rel_id'], (int)$this->JobOrder['job_of_id']);
            $this->Goods = JobOutboundDetailDao::loadJobOutboundForPos($this->JobOrder['job_id']);
            $this->MPdf->WriteHTML($header);
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
            $this->MPdf->WriteHTML($footer);
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
        $result .= '<td width="100%">';
        $result .= $this->Consignee['rel_name'];
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '<tr>';
        $result .= '<td width="100%">';
        $result .= $this->JobOrder['jo_customer_ref'];
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '<hr style="margin-top: 2px;margin-bottom: 2px; color: #000000; font-weight: bold;">';
        $result .= '<tr>';
        $result .= '<td width="100%">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '<table width="50%">';
        $result .= '<tr>';
        $result .= '<td>';
        $result .= Trans::getWord('from');
        $result .= '</td>';
        $result .= '<td>';
        $result .= ':';
        $result .= '</td>';
        $result .= '<td>';
        $result .= $this->JobOrder['job_warehouse'];
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';
        $result .= '</table>';
        return $result;
    }

    /**
     * Function to get the system setting header.
     *
     * @param int $relId To store the title document.
     * @param int $ofId  To store the title document.
     *
     * @return string
     */

    protected function getDefaultHeader($relId, $ofId = 0): string
    {
        $data = RelationDao::loadDataForDocumentHeader($relId, $ofId);
        $path = asset('images/image-not-found.jpg');
        $docDao = new DocumentDao();
        if (empty($data['doc_id']) === false) {
            $path = $docDao->getDocumentPath($data);
        }
        $header = '<table width="100%"  style="font-weight: bold;">';
        $header .= '<tr>';
        $header .= '<td width="100%" style="text-align: center"><img style="margin-top: -15px" height="40px" alt="" src="' . $path . '" /></td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }

    /**
     * Function to get the system setting header.
     *
     * @return string
     */

    protected function getDefaultFooter(): string
    {
        $footer = '<table width="100%" class="table-info" style="font-weight: bold">';
        $footer .= '<tr>';
        $footer .= '<td style="text-align: center">'.Trans::getWord('greetingThank', 'message', '', [
            'relation' => $this->JobOrder['jo_customer']
            ]).'</td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        return $footer;
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
            $strAddress = $this->Consignee['of_name'] . '<br>' . implode(', ', $address);
        }
        $data = [
            [
                'label' => Trans::getWord('receiver'),
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
        $result = '';
        $result .= '<p style="font-weight: bold; font-size: 10px; margin: 0; font-family: Arial, Helvetica, sans-serif"> ' . Trans::getWord('items') . '</p>';
        $tbl = new Table('goodsTbl');
        $tbl->setHeaderRow([
            'jod_gd_name' => Trans::getWord('goods'),
            'jod_quantity' => Trans::getWord('quantity'),
        ]);
        $rows = [];
        $number = new NumberFormatter();
        foreach ($this->Goods as $row) {
            $row['jod_quantity'] = $number->doFormatFloat($row['jod_quantity']) . ' ' . $row['jod_unit'];
            $rows[] = $row;
        }
        $tbl->addRows($rows);
        $tbl->addTableAttribute('class', 'table-pos');
        $tbl->setDisableHeader();
        $tbl->setDisableLineNumber();
        $tbl->addColumnAttribute('jod_quantity', 'style', 'text-align: right;');
        $result .= $tbl->createTable();

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
        $this->JobOrder = JobOutboundDao::getByJoId($this->getIntParameter('jo_id'));
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
