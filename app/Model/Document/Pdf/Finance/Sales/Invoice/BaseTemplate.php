<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Finance\Sales\Invoice;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Finance\Sales\SalesInvoiceDao;
use App\Model\Dao\Finance\Sales\SalesInvoiceDetailDao;
use App\Model\Document\Pdf\AbstractBasePdf;

/**
 * Class to generate the cash advance.
 *
 * @package    app
 * @subpackage Model\Document\PettyCash
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class BaseTemplate extends AbstractBasePdf
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
     * Property to store the data of invoice detail;
     *
     * @var array $DetailData ;
     */
    protected $DetailData = [];
    /**
     * Property to store the data of invoice detail;
     *
     * @var array $DetailSales ;
     */
    protected $DetailSales = [];

    /**
     * Property to store the data of invoice detail;
     *
     * @var array $DetailReimburse ;
     */
    protected $DetailReimburse = [];

    /**
     * Property to store the data of invoice detail;
     *
     * @var array $TaxData ;
     */
    protected $TaxData = [];

    /**
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getFinanceWord('invoice') . '.pdf');
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
        if (empty($this->Data) === false) {
            $this->loadDetailData();
        }
        try {
            $this->setFileName('test');
            $this->MPdf->SetHeader();
            $header = $this->getDefaultHeader($this->User->getRelId(), (int)$this->Data['si_of_id']);

            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');

            $fileName = Trans::getFinanceWord('invoice');
            if (empty($this->Data['si_number']) === false) {
                $fileName .= '_'.StringFormatter::replaceSpecialCharacter($this->Data['si_number'], '_');
            }
            $this->setFileName($fileName);

            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getFinanceWord('invoice'), 'font-20',
                'font-weight: bold; font-family: Arial, Helvetica, sans-serif;text-align: center;margin-bottom: 15px;'));

            $this->MPdf->WriteHTML($this->getGeneralInformation());
            if (empty($this->DetailSales) === false) {
                $this->MPdf->WriteHTML($this->getDetailSalesView());
            }
            if (empty($this->DetailReimburse) === false) {
                $this->MPdf->WriteHTML($this->getDetailReimburseView());
            }
            $this->MPdf->WriteHTML($this->getSummaryView());
            $this->MPdf->WriteHTML($this->getPaymentView());
        } catch (\Exception $e) {
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
        $result = '<div class="col-lg">';
        # General Job
        $result .= '<div class="col-md pull-left" style="font-size: 5px;">';
        $result .= $this->getCustomerView();
        $result .= '</div>';
        # Shipper Job
        $result .= '<div class="col-md pull-right">';
        $result .= $this->getDetailView();
        $result .= '</div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getCustomerView(): string
    {
        $strFormatter = new StringFormatter();
        $address = $strFormatter->doFormatAddress([
            'address' => $this->Data['si_cust_address'],
            'district' => $this->Data['si_cust_district'],
            'city' => $this->Data['si_cust_city'],
            'state' => $this->Data['si_cust_state'],
            'country' => $this->Data['si_cust_country'],
            'postal_code' => $this->Data['si_cust_postal'],
        ]);

        $data = [
            [
                'label' => Trans::getFinanceWord('customer'),
                'value' => $this->Data['si_customer'],
            ],
            [
                'label' => Trans::getFinanceWord('address'),
                'value' => $address,
            ],
            [
                'label' => Trans::getFinanceWord('phone'),
                'value' => $this->Data['si_rel_phone'],
            ],
            [
                'label' => Trans::getFinanceWord('pic'),
                'value' => $this->Data['si_pic_cust'],
            ],
            [
                'label' => Trans::getFinanceWord('reference'),
                'value' => $this->Data['si_rel_reference'],
            ],
        ];

        return $this->createTableView($data, false, true, 'tbl-info font-12', false);
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getDetailView(): string
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('invoice'),
                'value' => $this->Data['si_number'],
            ],
            [
                'label' => Trans::getFinanceWord('reference'),
                'value' => $this->Data['si_so_number'],
            ],
            [
                'label' => Trans::getFinanceWord('invoiceDate'),
                'value' => $this->DtParser->formatDate($this->Data['si_date']),
            ],
            [
                'label' => Trans::getFinanceWord('dueDate'),
                'value' => $this->DtParser->formatDate($this->Data['si_due_date']),
            ],
        ];

        return $this->createTableView($data, false, true, 'tbl-info font-12', false);
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getDetailSalesView(): string
    {
        $result = '<p class="title-4"   style="font-weight: bold"> ' . Trans::getFinanceWord('details') . '</p>';
        $tbl = new TablePdf('SiSTbl');
        $tbl->setHeaderRow([
            'sid_description' => Trans::getFinanceWord('description'),
            'sid_quantity' => Trans::getFinanceWord('quantity'),
            'sid_rate' => Trans::getFinanceWord('rate'),
            'sid_tax_name' => Trans::getFinanceWord('tax'),
            'sid_sub_total' => Trans::getFinanceWord('total'),
        ]);
        $rows = [];
        $i = 0;
        foreach ($this->DetailSales as $row) {
            $total = (float)$row['sid_quantity'] * (float)$row['sid_rate'] * (float)$row['sid_exchange_rate'];
            $row['sid_quantity'] = $this->Number->doFormatFloat((float)$row['sid_quantity']) . ' ' . $row['sid_uom_code'];
            $row['sid_rate'] = $row['sid_cur_iso'] . ' ' . $this->Number->doFormatFloat((float)$row['sid_rate']);
            $row['sid_sub_total'] = $row['sid_cur_iso'] . ' ' . $this->Number->doFormatFloat($total);

            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
            $rows[] = $row;
        }
        $tbl->addRows($rows);
        $tbl->addColumnAttribute('sid_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_rate', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_sub_total', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_tax_name', 'style', 'text-align: center;');
        $result .= $tbl->createTable();

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getDetailReimburseView(): string
    {
        $result = '<p class="title-4"   style="font-weight: bold"> ' . Trans::getFinanceWord('reimbursement') . '</p>';
        $tbl = new TablePdf('SiRTbl');
        $tbl->setHeaderRow([
            'sid_description' => Trans::getFinanceWord('description'),
            'sid_quantity' => Trans::getFinanceWord('quantity'),
            'sid_rate' => Trans::getFinanceWord('rate'),
            'sid_tax_name' => Trans::getFinanceWord('tax'),
            'sid_sub_total' => Trans::getFinanceWord('total'),
        ]);
        $rows = [];
        $i = 0;
        foreach ($this->DetailReimburse as $row) {
            $total = (float)$row['sid_quantity'] * (float)$row['sid_rate'] * (float)$row['sid_exchange_rate'];
            $row['sid_quantity'] = $this->Number->doFormatFloat((float)$row['sid_quantity']) . ' ' . $row['sid_uom_code'];
            $row['sid_rate'] = $row['sid_cur_iso'] . ' ' . $this->Number->doFormatFloat((float)$row['sid_rate']);
            $row['sid_sub_total'] = $row['sid_cur_iso'] . ' ' . $this->Number->doFormatFloat($total);

            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
            $rows[] = $row;
        }
        $tbl->addRows($rows);
        $tbl->addColumnAttribute('sid_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_rate', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_sub_total', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('sid_tax_name', 'style', 'text-align: center;');
        $result .= $tbl->createTable();

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getPaymentView(): string
    {

        $result = '<p></p>';
        $result .= '<div class="col-lg" style="margin-top: 100px !important;">';
        $result .= '<div class="col-md pull-left">';
        $result .= $this->getPaymentInformation();
        $result .= '</div>';
        $result .= '<div class="col-md pull-right">';
        $result .= $this->getSignature();
        $result .= '</div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getSummaryView(): string
    {
        $subTotal = 0.0;
        $total = 0.0;
        foreach ($this->DetailData as $row) {
            $amount = (float)$row['sid_quantity'] * (float)$row['sid_rate'] * (float)$row['sid_exchange_rate'];
            $taxAmount = ((float)$row['sid_tax_percent'] * $amount) / 100;
            $subTotal += $amount;
            $total += ($amount + $taxAmount);
        }


        $table = '<table class="tbl-info font-12" style="width: 100%; border-collapse: collapse;">';
        $table .= '<tr>';
        $table .= '<td style="width: 30%; border: 1px solid #ddd;padding: 5px;">' . Trans::getFinanceWord('subTotal') . '</td>';
        $table .= '<td style="width: 70%;text-align: right; border: 1px solid #ddd;padding: 5px;">IDR ' . $this->Number->doFormatFloat($subTotal) . '</td>';
        $table .= '</tr>';
        foreach ($this->TaxData as $tax) {
            $table .= '<tr>';
            $table .= '<td style="border: 1px solid #ddd;padding: 5px;">' . $tax['tax_name'] . '</td>';
            if ($tax['tax_amount'] < 0) {
                $table .= '<td style="text-align: right; border: 1px solid #ddd;padding: 5px;">( IDR ' . $this->Number->doFormatFloat($tax['tax_amount'] * -1) . ' ) </td>';
            } else {
                $table .= '<td style="text-align: right; border: 1px solid #ddd;padding: 5px;">IDR ' . $this->Number->doFormatFloat($tax['tax_amount']) . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #ddd;padding: 5px;">' . Trans::getFinanceWord('total') . '</td>';
        $table .= '<td style="text-align: right; font-weight: bold; border: 1px solid #ddd;padding: 5px;">IDR ' . $this->Number->doFormatFloat($total) . '</td>';
        $table .= '</tr>';
        $table .= '</table>';

        $result = '<div style="width: 50%; float: right; margin-top: 5px;">';
        $result .= $table;
        $result .= '</div>';

        return $result;
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getPaymentInformation(): string
    {
        $result = '<p class="title-4" style="margin-bottom: 0 !important;"> ' . Trans::getFinanceWord('paymentDetail') . '</p>';
        $data = [
            [
                'label' => Trans::getFinanceWord('bankName'),
                'value' => $this->Data['si_rb_bank'],
            ],
            [
                'label' => Trans::getFinanceWord('bankBranch'),
                'value' => $this->Data['si_rb_branch'],
            ],
            [
                'label' => Trans::getFinanceWord('bankAccount'),
                'value' => $this->Data['si_rb_number'],
            ],
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->Data['si_rb_name'],
            ],
            [
                'label' => Trans::getFinanceWord('taxAccount'),
                'value' => $this->Data['si_vat'],
            ],
            [
                'label' => Trans::getFinanceWord('info'),
                'value' => $this->Data['si_email'],
            ],
        ];

        $result .= '<div style="border: 2px solid black;">';
        $result .= $this->createTableView($data, false, true, 'tbl-info font-12', false);
        $result .= '</div>';

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
                'label' => '',
                'name' => $this->User->getName(),
            ],
        ];
        $result = '<p></p>';
        $result .= $this->generateSignatureView($data, 'font-size: 12px !important');
        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return void
     */
    protected function loadData(): void
    {
        if ($this->isValidParameter('si_id') === false) {
            Message::throwMessage('Invalid parameter for creating pro format invoice.');
        }
        $this->Data = SalesInvoiceDao::getByReferenceAndSystem($this->getIntParameter('si_id'), $this->User->getSsId());
        if (empty($this->Data) === true) {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROE');
        }
    }

    /**
     * Function to load the html content.
     *
     * @return void
     */
    protected function loadDetailData(): void
    {
        $wheres = [];
        $wheres[] = '(sid.sid_si_id = ' . $this->Data['si_id'] . ')';
        $wheres[] = '(sid.sid_deleted_on IS NULL)';
        if ($this->Data['si_manual'] === 'Y') {
            $this->DetailData = SalesInvoiceDetailDao::loadManualData($wheres);
        } else {
            $this->DetailData = SalesInvoiceDetailDao::loadJosData($wheres);
        }
        $tempTaxId = [];
        foreach ($this->DetailData as $row) {
            if ($row['sid_cc_type'] === 'R') {
                $this->DetailReimburse[] = $row;
            } else {
                $this->DetailSales[] = $row;
            }
            $tax = (float)$row['sid_tax_percent'];
            if ($tax !== 0.0) {
                $subTotal = (float)$row['sid_quantity'] * (float)$row['sid_rate'] * (float)$row['sid_exchange_rate'];
                $taxAmount = ($tax * $subTotal) / 100;
                if (in_array($row['sid_tax_id'], $tempTaxId, true) === false) {
                    $tempTaxId[] = $row['sid_tax_id'];
                    $this->TaxData[] = [
                        'tax_name' => $row['sid_tax_name'],
                        'tax_amount' => $taxAmount,
                    ];
                } else {
                    $index = array_search($row['sid_tax_id'], $tempTaxId, true);
                    $this->TaxData[$index]['tax_amount'] += $taxAmount;
                }
            }

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
