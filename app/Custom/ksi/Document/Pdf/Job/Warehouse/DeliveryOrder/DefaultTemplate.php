<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\ksi\Document\Pdf\Job\Warehouse\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use Exception;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends \App\Model\Document\Pdf\Job\Warehouse\DeliveryOrder\DefaultTemplate
{
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
            $this->Consignee = RelationDao::loadDataForDocumentHeader($this->JobOrder['job_rel_id'], (int)$this->JobOrder['job_of_id']);
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->JobOrder['job_id'] . ')';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $this->Goods = JobOutboundDetailDao::loadData($wheres);
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryOrder')));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
            $this->MPdf->WriteHTML($this->getReceiveView());
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
    protected function getGoodsView(): string
    {
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail') . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_quantity' => Trans::getWord('quantity'),
            'jod_total_weight' => Trans::getWord('totalWeight') . ' ' . '(KG)',
            'jod_packing_number' => Trans::getWord('packingNumber'),
            'jod_notes' => Trans::getWord('notes'),
        ]);
        $rows = [];
        $number = new NumberFormatter();
        $gdIds = [];
        $packingNumbers = [];
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            $qty = (float)$row['jod_qty_loaded'];
            $totalWeight = (float)$row['jod_weight'];
            $notes = '';
            if (empty($row['jid_gdt_id']) === false) {
                $notes = $number->doFormatFloat($qty) . ' ' . $row['jod_unit'] . ' ' . $row['jod_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jod_gcd_description'];
            }
            if (in_array($row['jod_gd_id'], $gdIds, true) === false) {
                $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
                $row['jod_notes'] = [];
                if (empty($notes) === false) {
                    $row['jod_notes'][] = $notes;
                }
                $row['jod_packing_number_list'] = [];
                if (empty($row['jod_packing_number']) === false && in_array($row['jod_packing_number'], $packingNumbers, true) === false) {
                    $row['jod_packing_number_list'][] = $row['jod_packing_number'];
                    $packingNumbers[] = $row['jod_packing_number'];
                }
                $row['jod_quantity'] = $qty;
                $row['jod_total_weight'] = $totalWeight;
                $rows[] = $row;
                $gdIds[] = $row['jod_gd_id'];
            } else {
                $index = array_search($row['jod_gd_id'], $gdIds, true);
                if (empty($notes) === false) {
                    $rows[$index]['jod_notes'][] = $notes;
                }
                if (empty($row['jod_packing_number']) === false && in_array($row['jod_packing_number'], $packingNumbers, true) === false) {
                    $rows[$index]['jod_packing_number_list'][] = $row['jod_packing_number'];
                    $packingNumbers[] = $row['jod_packing_number'];
                }
                $rows[$index]['jod_quantity'] += $qty;
                $rows[$index]['jod_total_weight'] += $totalWeight;
            }
        }
        $results = [];
        $i = 0;
        foreach ($rows as $row) {
            $results[] = [
                'jod_gd_sku' => $row['jod_gd_sku'],
                'jod_goods' => $row['jod_goods'],
                'jod_quantity' => $number->doFormatFloat($row['jod_quantity']) . ' ' . $row['jod_unit'],
                'jod_total_weight' => $row['jod_total_weight'],
                'jod_packing_number' => implode(', ', $row['jod_packing_number_list']),
                'jod_notes' => implode('<br />', $row['jod_notes']),
            ];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
        }
        $tbl->addRows($results);
        $tbl->setColumnType('jod_total_weight', 'float');
        $tbl->addColumnAttribute('jod_quantity', 'style', 'text-align: right;');
        $result .= $tbl->createTable();

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getReceiveView(): string
    {
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('receiveStatus') . '</p>';
        $tbl = new TablePdf('goodsRcTbl');
        $tbl->setHeaderRow([
            'jod_goods' => Trans::getWord('goods'),
            'good' => Trans::getWord('qtyGoodReceived'),
            'damage' => Trans::getWord('qtyDamageReceived'),
            'description' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . Trans::getWord('notes') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        ]);
        $rows = [];
        $i = 0;
        $gdIds = [];
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            if (in_array($row['jod_gd_id'], $gdIds, true) === false) {
                $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
                $row['good'] = $row['jod_unit'];
                $row['damage'] = $row['jod_unit'];
                if (($i % 2) === 0) {
                    $tbl->addRowAttribute($i - 1, 'class', 'even');
                }
                $gdIds[] = $row['jod_gd_id'];
                $rows[] = $row;
                $i++;
            }
        }
        $tbl->addRows($rows);
        $tbl->addColumnAttribute('good', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('damage', 'style', 'text-align: right;');
        $result .= $tbl->createTable();

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
        $address = $data['of_address'];
        if (empty($data['dtc_name']) === false) {
            $address .= ', ' . $data['dtc_name'];
            $address .= ', ' . $data['cty_name'];
            $address .= ', ' . $data['stt_name'];
            $address .= ', ' . $data['cnt_name'];
        }
        if (empty($data['of_postal_code']) === false) {
            $address .= ', ' . $data['of_postal_code'];
        }
        $contact = [];
        if (empty($data['rel_phone']) === false) {
            $contact[] = Trans::getWord('telp') . ':' . $data['rel_phone'];
        }
        if (empty($data['rel_email']) === false) {
            $contact[] = Trans::getWord('email') . ':' . $data['rel_email'];
        }
        if (empty($data['rel_website']) === false) {
            $contact[] = Trans::getWord('website') . ':' . $data['rel_website'];
        }
        $strContact = implode(', ', $contact);

        $header = '<table class="pdf-header"  style="font-weight: bold">';
        $header .= '<tr>';
        $header .= '<td class="head-logo"><img style="width: 150px" class="company-logo" alt="" src="' . $path . '" /></td>';
        $header .= '<td class="head-company">';
        $header .= '<table>';
        $header .= '<tr><td class="company-name">' . $data['rel_name'] . '</td></tr>';
        $header .= '<tr><td class="address">' . $address . '</td></tr>';
        $header .= '<tr><td  class="address">' . $strContact . '</td></tr>';
        $header .= '</table>';
        $header .= '</td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }
}
