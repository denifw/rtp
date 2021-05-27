<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Document\Pdf\Crm\Quotation;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Crm\Quotation\PriceDao;
use App\Model\Dao\Crm\Quotation\PriceDetailDao;
use App\Model\Dao\Crm\Quotation\QuotationDao;
use App\Model\Dao\Crm\Quotation\QuotationServiceDao;
use App\Model\Dao\Crm\Quotation\QuotationTermsDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class BaseTemplate extends AbstractBasePdf
{
    /**
     * Property to store quotation data.
     *
     * @var array
     */
    protected $Data = [];


    public function __construct()
    {
        parent::__construct(Trans::getFinanceWord('quotation') . '.pdf');
    }

    /**
     * Function load Content
     */
    public function loadContent(): void
    {
        # Get data
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $header = $this->getHeaderLogo($this->User->getRelId());
            $footer = $this->getFooterAddress($this->User->getRelId(), $this->Data['qt_order_of_id']);
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 15, 15, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLHeader($header, 'O', true);
            $this->MPdf->SetHTMLFooter($footer, 'O');
            $this->MPdf->SetHTMLFooter($footer, 'E');

            $this->MPdf->WriteHTML($this->writeQuotationDetail());
            $this->MPdf->WriteHTML($this->writeCustomerDetail());
            $this->MPdf->WriteHTML($this->writeScopeOfService());
            $this->MPdf->WriteHTML($this->writePriceDescription());
            $this->MPdf->WriteHTML($this->writeTermCondition());
            $this->MPdf->WriteHTML($this->writeApproval());
            $this->MPdf->WriteHTML($this->writeSignature());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to write section for quotation detail.
     *
     * @return string
     */
    private function writeCustomerDetail(): string
    {
        $address = '';
        if (empty($this->Data['qt_of_id']) === false) {
            $office = OfficeDao::getByReference($this->Data['qt_of_id']);
            $address = $office['of_address_district'];
        }
        $data = [
            [
                'label' => Trans::getFinanceWord('customerName'),
                'value' => $this->Data['qt_relation'],
            ],
            [
                'label' => Trans::getFinanceWord('customerAddress'),
                'value' => $address,
            ],
            [
                'label' => Trans::getFinanceWord('personInCharge'),
                'value' => $this->Data['qt_pic_relation'],
            ],
        ];

        $table = $this->createTableView($data, true, true, 'font-12', false);
        $section = '<div style="width:70%; padding: 5px;">';
        $section .= $table;
        $section .= '</div>';
        return $section;
    }

    /**
     * Function to write section for quotation detail.
     *
     * @return string
     */
    private function writeQuotationDetail(): string
    {
        $section = '<div style="width: 48%; float: left; padding: 5px;">';
        $section .= $this->getQuotationDetailTable();
        $section .= '</div>';
        $section .= '<div class="font-16" style="width: 48%; font-weight: bold; float: right; text-align: center;">' . mb_strtoupper(Trans::getFinanceWord('quotation')) . '</div>';
        return $section;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getQuotationDetailTable(): string
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('quotationNumber'),
                'value' => $this->Data['qt_number'],
            ],
            [
                'label' => Trans::getFinanceWord('revision'),
                'value' => '',
            ],
            [
                'label' => Trans::getFinanceWord('issueDate'),
                'value' => DateTimeParser::format(date('Y-m-d'), 'Y-m-d', 'd M Y'),
            ],
        ];

        return $this->createTableView($data, true, true, 'font-12', false);
    }


    /**
     * Function to write scope of service section.
     *
     * @return string
     */
    protected function writeScopeOfService(): string
    {
        $services = QuotationServiceDao::getByQuotationId($this->Data['qt_id']);
        $countService = count($services);
        $textService = '';
        for ($i = 0; $i < $countService; $i++) {
            $row = $services[$i];
            if ($i === 0) {
                $textService = $row['qs_service'];
            } elseif ($i === ($countService - 1)) {
                $textService .= ' ' . Trans::getFinanceWord('and') . ' ' . $row['qs_service'];
            } else {
                $textService .= ', ' . $row['qs_service'];
            }
        }

        $section = '<p class="title-4" style="font-weight: bold">I. ' . Trans::getFinanceWord('scopeOfService') . '</p>';
        $data = [
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $textService,
            ],
            [
                'label' => Trans::getFinanceWord('commodity'),
                'value' => $this->Data['qt_commodity'],
            ],
            [
                'label' => Trans::getFinanceWord('specialRequirement'),
                'value' => $this->Data['qt_requirement'],
            ],
        ];

        $table = $this->createTableView($data, true, true, 'font-12', false);
        $section .= '<div style="width:70%; padding: 5px;">';
        $section .= $table;
        $section .= '</div>';
        return $section;
    }


    /**
     * Function to write price description section.
     *
     * @return string
     */
    protected function writePriceDescription(): string
    {
        $section = '<p class="title-4" style="font-weight: bold">II. ' . Trans::getFinanceWord('priceDescription') . '</p>';
        $tbl = new TablePdf('QtPrcTbl');
        $tbl->setHeaderRow([
            'prc_service' => Trans::getFinanceWord('service'),
            'prc_detail' => Trans::getFinanceWord('detail'),
            'prc_description' => Trans::getFinanceWord('description'),
            'prc_price' => Trans::getFinanceWord('price'),
            'prc_remark' => Trans::getFinanceWord('remark'),
        ]);
        $tbl->addRows($this->loadPriceData());
        $tbl->addTableAttribute('class', 'content-table font-12');
        $tbl->addTableAttribute('style', 'font-weight: normal;');
        $tbl->addColumnAttribute('prc_price', 'style', 'text-align: right;');
        $section .= $tbl->createTable();
        return $section;
    }

    /**
     * Function to prepare price data.
     *
     * @return array
     */
    private function loadPriceData(): array
    {
        $results = [];
        $data = PriceDetailDao::getByQuotationId($this->Data['qt_id']);
        $index = 0;
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['prc_service'] = $row['prc_srv_name'];
            if (empty($row['prc_srt_name']) === false) {
                $row['prc_service'] .= ' ' . $row['prc_srt_name'];

            }
            # Split between service
            $details = [];
            if ($row['prc_srv_code'] === 'warehouse') {
                $details[] = Trans::getFinanceWord('warehouse') . ' : ' . $row['prc_warehouse'];
            } elseif ($row['prc_srv_code'] === 'inklaring') {
                $details[] = Trans::getFinanceWord('module') . ' : ' . $row['prc_transport_module'];
                if ($row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('port') . ' : ' . $row['prc_pod_name'] . ' (' . $row['prc_pod_code'] . ') - ' . $row['prc_pod_country'];
                } else {
                    $details[] = Trans::getFinanceWord('port') . ' : ' . $row['prc_pol_name'] . ' (' . $row['prc_pol_code'] . ') - ' . $row['prc_pol_country'];
                }
                if (empty($row['prc_custom_clearance_type']) === false) {
                    $details[] = Trans::getFinanceWord('type') . ' : ' . $row['prc_custom_clearance_type'];
                }
            } elseif ($row['prc_srv_code'] === 'delivery') {
                $origin = $row['prc_origin_district'] . ', ' . $row['prc_origin_city'] . ', ' . $row['prc_origin_state'];
                if (empty($row['prc_origin_address']) === false) {
                    $origin = $row['prc_origin_address'] . ', ' . $origin;
                }
                $destination = $row['prc_destination_district'] . ', ' . $row['prc_destination_city'] . ', ' . $row['prc_destination_state'];
                if (empty($row['prc_destination_address']) === false) {
                    $destination = $row['prc_destination_address'] . ', ' . $destination;
                }
                if ($row['prc_srt_pol'] === 'Y' && $row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                    $details[] = Trans::getFinanceWord('module') . ' : ' . $row['prc_transport_module'];
                } elseif ($row['prc_srt_pol'] === 'Y' && $row['prc_srt_unload'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $destination;
                } elseif ($row['prc_srt_load'] === 'Y' && $row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $origin;
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                } else {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $origin;
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $destination;
                }
                $details[] = Trans::getFinanceWord('transport') . ' : ' . $row['prc_eg_name'];
            }
            if (empty($row['prc_container_type']) === false) {
                $details[] = Trans::getFinanceWord('containerType') . ' : ' . $row['prc_container_type'];
            }
            $row['prc_detail'] = implode('<br/>', $details);
            $description = [];
            $description[] = $row['prd_description'];
            $description[] = Trans::getFinanceWord('quantity') . ' : ' . $number->doFormatFloat($row['prd_quantity']) . ' ' . $row['prd_uom_code'];
            $row['prc_description'] = implode('<br/>', $description);
            $row['prc_price'] = $row['prd_currency'] . ' ' . $number->doFormatFloat($row['prd_total']);
            $row['prc_remark'] = $row['prd_remark'];

            # Create update button
            $route = PriceDao::getDetailRoute($row['prc_type'], $row['prc_srv_code']) . '?prc_id=' . $row['prc_id'];
            $btnEdit = new HyperLink('btnEdit' . $index, '', url($route));
            $btnEdit->setIcon(Icon::Edit)
                ->viewAsButton()
                ->viewIconOnly()
                ->btnMedium()
                ->btnPrimary();
            $row['prc_action'] = $btnEdit;
            $results[] = $row;
            $index++;
        }
        return $results;
    }


    /**
     * Function to write term condition section.
     *
     * @return string
     */
    protected function writeTermCondition(): string
    {
        $section = '<p class="title-4" style="font-weight: bold">III. ' . Trans::getFinanceWord('generalTermCondition') . '</p>';
        $terms = QuotationTermsDao::getByQuotationId($this->Data['qt_id']);

        if (empty($terms) === false) {
            $section .= '<ul style=" margin-top:3px;" class="font-12">';
            foreach ($terms as $row) {
                $section .= '<li style="margin-bottom: 1px;" class="font-12">' . StringFormatter::replaceNewLineToBr($row['qtm_terms']) . '</li>';
            }
            $section .= '</ul>';
        } else {
            $section .= '<div style="padding-left: 15px;">';
            $section .= '-';
            $section .= '</div>';
        }

        return $section;
    }

    /**
     * Function to write approval section.
     *
     * @return string
     */
    private function writeApproval(): string
    {
        $section = '<p class="title-4" style="font-weight: bold">IV. ' . Trans::getFinanceWord('confirmationAndApproval') . '</p>';
        $section .= '<div style="padding: 5px;" class="font-12">';
        $section .= Trans::getMessageWord('quotationConfirmationAndApproval');
        $section .= '</div>';
        return $section;
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function writeSignature(): string
    {
        $date = DateTimeParser::format(date('Y-m-d'), 'Y-m-d', 'd F Y');
        $picCustomer = $this->Data['qt_pic_relation'];
        if (empty($picCustomer)) {
            $picCustomer = "_______________________";
        }
        $rel = RelationDao::getByReference($this->Data['qt_ss_id']);
        $of = $this->getCpOfficeManager($this->Data['qt_us_id'],$this->Data['qt_id'] , $this->Data['qt_ss_id']);
        $section = '<p class="font-12" style="margin-bottom: 0; font-weight: normal; text-align: left">Jakarta ' . $date . '</p>';
        $section .= '<p></p>';
        $section .= '<div style="width: 48%; float: left; text-align: center;">';
        $section .= '<table>';
        $section .= '<tr>';
        $section .= '<td rowspan="3">';
        $section .= $this->getHeaderLogo($this->User->getRelId());
        $section .= '</td>';
        $section .= '<td>';
        $section .= $this->Data['qt_manager'];
        $section .= '</td>';
        $section .= '</tr>';
        $section .= '<tr>';
        $section .= '<td>';
        $section .= $of['cp_email'] . ' ' .$of['cp_phone'];
        $section .= '</td>';
        $section .= '</tr>';
        $section .= '<tr>';
        $section .= '<td>';
        $section .= $this->User->Relation->getName() . ' ' . $rel['rel_phone'];
        $section .= '</td>';
        $section .= '</tr>';
        $section .= '</table>';
        $section .= '</div>';
        $section .= '<div class="font-16" style="width: 48%; float: right; text-align: center;">';
        $section .= '<p class="font-12">Approval</p>';
        $section .= '<p></p>';
        $section .= '<p></p>';
        $section .= '<p>(' . $picCustomer . ')</p>';
        $section .= '</div>';
        return $section;
    }

    /**
     * function to retrieve data contact person
     * @param int $userId
     * @param int $qtId
     * @param int $ssId
     * @return array
     */
    private function getCpOfficeManager(int $userId, int $qtId, int $ssId): array
    {

        $wheres[] = '(us.us_id = ' . $userId . ')';
        $wheres[] = '(q.qt_id = ' . $qtId . ')';
        $wheres[] = '(ump.ump_ss_id = ' . $ssId . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cp.cp_name,cp.cp_email,cp.cp_phone,us.us_username
                    FROM users as us
                    INNER JOIN user_mapping as ump ON ump.ump_us_id = us.us_id
                    INNER JOIN contact_person as cp on ump.ump_cp_id = cp.cp_id
                    INNER JOIN office as o ON cp.cp_of_id = o.of_id
                    INNER JOIN quotation q on us.us_id = q.qt_us_id ' . $strWhere;
        $result = DB::select($query);
        return DataParser::objectToArray($result[0],[
            'cp_name',
            'cp_email',
            'cp_phone',
            'us_username'
        ]);
    }

    /**
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }

    /**
     * Function load data
     *
     * @return void
     */
    private function loadData(): void
    {
        if ($this->isValidParameter('qt_id') === false) {
            Message::throwMessage('Invalid parameter for reference value.', 'ERROR');
        } else {
            # Get data from dao
            $this->Data = QuotationDao::getByReferenceAndSystem($this->getIntParameter('qt_id'), $this->User->getSsId());
        }
    }
}
