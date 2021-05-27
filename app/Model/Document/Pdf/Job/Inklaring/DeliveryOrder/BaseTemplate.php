<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Document\Pdf\Job\Inklaring\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Master\WarehouseDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 * Class to generate the delivery order report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class BaseTemplate extends AbstractBasePdf
{

    /**
     * Property to store the job detail.
     *
     * @var array $JobOrder
     */
    protected $JobOrder = [];


    /**
     * Property to store warehouse's data.
     *
     * @var array $Warehouse
     */
    protected $Warehouse = [];

    /**
     * Property to store container's data.
     *
     * @var array $Container
     */
    protected $Container = [];

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
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryOrder')));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getContainerView());
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
        $result = '<table class="table-info" style=" width: 100%; font-weight: bold;">';
        $result .= '<tr>';
        # General Job
        $result .= '<td style=" width: 33%;">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        # Shipper Job
        $result .= '<td style=" width: 33%;">';
        $result .= $this->getInklaringView();
        $result .= '</td>';
        # warehouse info
        $result .= '<td style=" width: 33%;">';
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
                'value' => $this->JobOrder['so_customer'],
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->JobOrder['so_customer_ref'],
            ],
            [
                'label' => Trans::getWord('blRef'),
                'value' => $this->JobOrder['so_bl_ref'],
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getInklaringView(): string
    {
        $data = [
            [
                'label' => Trans::getWord('consignee'),
                'value' => $this->JobOrder['so_consignee'],
            ],
            [
                'label' => Trans::getWord('shipper'),
                'value' => $this->JobOrder['so_shipper'],
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
                'value' => $this->JobOrder['so_warehouse_name'],
            ],
            [
                'label' => Trans::getWord('address'),
                'value' => $strAddress
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getContainerView(): string
    {
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('container') . '</p>';
        $tbl = new TablePdf('ctTbl');
        $tbl->setHeaderRow([
            'joc_container_info' => Trans::getWord('container'),
            'joc_arrive' => Trans::getWord('arrive'),
            'joc_start' => Trans::getWord('start'),
            'joc_out' => Trans::getWord('out'),
            'joc_goods' => Trans::getWord('goods'),
            'joc_notes' => Trans::getWord('notes'),
        ]);
        $rows = [];
        $arrayCheck = [];
        $i = 0;
        foreach ($this->Container as $row) {
            if (in_array($row['sog_soc_id'], $arrayCheck, true) === false) {
                $arrayCheck[$i] = $row['sog_soc_id'];
                $temp = [];
                $data = [
                    [
                        'label' => Trans::getWord('type'),
                        'value' => $row['sog_container_type'],
                    ],
                    [
                        'label' => Trans::getWord('containerNumber'),
                        'value' => $row['sog_container_number'],
                    ],
                    [
                        'label' => Trans::getWord('sealNumber'),
                        'value' => $row['sog_seal_number'],
                    ]
                ];
                $temp['joc_container_info'] = $this->createTableView($data, false);
                $dateTime = [
                    [
                        'label' => Trans::getWord('date'),
                        'value' => '',
                    ],
                    [
                        'label' => Trans::getWord('time'),
                        'value' => '_ _ : _ _',
                    ]
                ];
                $temp['joc_arrive'] = $this->createTableView($dateTime);
                $temp['joc_start'] = $this->createTableView($dateTime);
                $temp['joc_out'] = $this->createTableView($dateTime);
                $temp['joc_goods'] = $row['sog_name'];
                $temp['joc_notes'] = '';
                $rows[$i] = $temp;
                $i++;
            } else {
                $index = array_search($row['sog_soc_id'], $arrayCheck, true);
                $rows[$index]['joc_goods'] .= ', <br>' . $row['sog_name'];
            }
        }

        $tbl->addColumnAttribute('joc_notes', 'style', 'width: 150px');
        $tbl->addRows($rows);
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
                'label' => Trans::getWord('deliverBy'),
                'name' => '',
            ],
            [
                'label' => Trans::getWord('driver'),
                'name' => '',
            ],
            [
                'label' => Trans::getWord('receiver'),
                'name' => '',
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
        $jo = JobInklaringDao::getByJobId($this->getIntParameter('jo_id'));
        if (empty($jo) === false) {
            $so = SalesOrderDao::getByReference($jo['jik_so_id']);
            $this->JobOrder = array_merge($jo, $so);
            $this->Warehouse = WarehouseDao::getWarehouseAddress($this->JobOrder['so_wh_id']);
            $this->Container = SalesOrderGoodsDao::getBySoId($this->JobOrder['jik_so_id']);
        } else {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROR');
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
