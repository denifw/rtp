<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Finance\CashAndBank\CashPayment\CashReceive;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDetailDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 * Class to generate the cash advance.
 *
 * @package    app
 * @subpackage Model\Document\PettyCash
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends AbstractBasePdf
{

    /**
     * Property to store the data.
     *
     * @var array $Data
     */
    protected $Data = [];
    /**
     * Property to store number object
     *
     * @var NumberFormatter $Number
     */
    protected $Number;
    /**
     * Property to store date time parser object
     *
     * @var DateTimeParser $DtParser
     */
    protected $DtParser;
    /**
     * Property to store the data of job purchase;
     *
     * @var array $JobPurchase ;
     */
    protected $JobPurchase = [];

    /**
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getFinanceWord('cashPaymentReceipt') . '.pdf');
        $this->Number = new NumberFormatter();
        $this->DtParser = new DateTimeParser();

    }

    /**
     * Function to set the content to pdf.
     *
     * @return void
     */
    public function loadContent(): void
    {
        $this->loadData();
        $this->JobPurchase = JobPurchaseDao::getByJobId($this->Data['ca_jo_id']);
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

            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getFinanceWord('cashPaymentReceipt')));
            $this->MPdf->WriteHTML($this->getGeneralInformation());
            $this->MPdf->WriteHTML($this->getAmountView());
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
    protected function getGeneralInformation(): string
    {
        $result = '<table class="table-info" style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        # General Job
        $result .= '<td style="width: 50%">';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        # Shipper Job
        $result .= '<td style="width: 50%">';
        $result .= $this->getJobView();
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
        $eCardBalance = '-';
        if (empty($this->Data['ca_ea_id']) === false) {
            $eCardBalance = $this->Data['ca_currency'] . ' ' . $this->Number->doFormatFloat((float)$this->Data['ca_ea_balance']);
        }
        $data = [
            [
                'label' => Trans::getFinanceWord('caNumber'),
                'value' => $this->Data['ca_number'],
            ],
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->Data['ca_ba_code'] . ' - ' . $this->Data['ca_ba_description'],
            ],
            [
                'label' => Trans::getFinanceWord('eCardAccount'),
                'value' => $this->Data['ca_ea_code'] . ' - ' . $this->Data['ca_ea_description'],
            ],
            [
                'label' => Trans::getFinanceWord('eCardBalance'),
                'value' => $eCardBalance,
            ],
            [
                'label' => Trans::getFinanceWord('receiver'),
                'value' => $this->Data['ca_cp_name'],
            ],
            [
                'label' => Trans::getFinanceWord('planningDate'),
                'value' => $this->DtParser->formatDate($this->Data['ca_date']),
            ],
        ];

        return $this->createTableView($data, false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getJobView(): string
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('jo'),
                'value' => $this->Data['ca_jo_number'],
            ],
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $this->Data['ca_srv_name'] . ' - ' . $this->Data['ca_srt_name'],
            ],
        ];
        if ($this->Data['ca_srv_code'] === 'delivery') {
            $data = array_merge($data, $this->getDeliveryData());
        } elseif ($this->Data['ca_srv_code'] === 'inklaring') {
            $data = array_merge($data, $this->getInklaringData());
        }

        return $this->createTableView($data, false);
    }

    /**
     * Function to get job view portlet.
     *
     * @return array
     */
    private function getDeliveryData(): array
    {
        $data = [];
        $job = JobDeliveryDao::getByJobIdAndSystem($this->Data['ca_jo_id'], $this->User->getSsId());
        if (empty($job) === false) {
            $data = [
                [
                    'label' => Trans::getTruckingWord('transportModule'),
                    'value' => $job['jdl_transport_module'],
                ],
                [
                    'label' => Trans::getTruckingWord('transportType'),
                    'value' => $job['jdl_equipment_group'],
                ],
            ];
            if ($job['jdl_tm_code'] === 'road') {
                $data[] = [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $job['jdl_equipment_plate'],
                ];
            } else {
                $data[] = [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $job['jdl_equipment'] . ' ' . $job['jdl_transport_number'],
                ];
            }
        }
        return $data;
    }

    /**
     * Function to get job view portlet.
     *
     * @return array
     */
    private function getInklaringData(): array
    {
        $data = [];
        $job = JobInklaringDao::getByReferenceAndSystemSetting($this->Data['ca_jo_id'], $this->User->getSsId());
        if (empty($job) === false) {
            $so = SalesOrderDao::getByReferenceAndSystem($job['jik_so_id'], $this->User->getSsId());
            $data = [
                [
                    'label' => Trans::getTruckingWord('transportModule'),
                    'value' => $so['so_transport_module'],
                ],
                [
                    'label' => Trans::getTruckingWord('transport'),
                    'value' => $so['so_transport_name'] . ' - ' . $so['so_transport_number'],
                ],
                [
                    'label' => Trans::getWord('documentType'),
                    'value' => $so['so_document_type'],
                ],
                [
                    'label' => Trans::getWord('pol'),
                    'value' => $so['so_pol'] . ' - ' . $so['so_pol_country'],
                ],
                [
                    'label' => Trans::getWord('pod'),
                    'value' => $so['so_pod'] . ' - ' . $so['so_pod_country'],
                ],
            ];
        }
        return $data;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getAmountView(): string
    {
        $caAmount = CashAdvanceDetailDao::getTotalDetailByCa($this->Data['ca_id'], false);
        $reserved = (float)$this->Data['ca_reserve_amount'];
        $caAmount += $reserved;
        $result = '<p></p>';
        $result .= '<table style="font-weight: bold; width: 100%;">';
        $result .= '<tr>';
        $result .= '<td style="font-weight: bold; font-size: 14pt; text-align: left;">' . Trans::getFinanceWord('cashPayment') . '</td>';
        $result .= '<td style="font-weight: bold; font-size: 20pt; text-align: right;">' . $this->Data['ca_currency'] . ' ' . $this->Number->doFormatFloat($caAmount) . '</td>';
        $result .= '</tr>';
        $result .= '</table>';

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
                'label' => Trans::getFinanceWord('receiver'),
                'name' => $this->Data['ca_cp_name'],
            ],
            [
                'label' => Trans::getFinanceWord('payer'),
                'name' => $this->Data['ca_ba_user'],
            ],
        ];
        $label = Trans::getFinanceWord('printDate') . ' : ' . date('H:i d.M.Y');
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
        if ($this->isValidParameter('ca_id') === false) {
            Message::throwMessage('Invalid id for cash advance.');
        }
        $this->Data = CashAdvanceDao::getByReferenceAndSystem($this->getIntParameter('ca_id'), $this->User->getSsId());
        if (empty($this->Data) === true) {
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
