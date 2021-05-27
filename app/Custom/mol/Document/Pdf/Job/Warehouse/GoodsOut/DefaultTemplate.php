<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\mol\Document\Pdf\Job\Warehouse\GoodsOut;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends \App\Model\Document\Pdf\Job\Warehouse\GoodsOut\DefaultTemplate
{

    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getGoodsView(): string
    {
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->JobOrder['job_id'] . ')';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $data = JobOutboundDetailDao::loadData($wheres);
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getWord('goodsDetail') . '</p>';
        $tbl = new TablePdf('goodsTbl');
        $tbl->setHeaderRow([
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_quantity' => Trans::getWord('quantity'),
            'jod_serial_number' => Trans::getWord('serialNumber'),
            'jod_notes' => Trans::getWord('notes'),
        ]);
        $rows = [];
        $number = new NumberFormatter();
        $gdIds = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $qty = (float) $row['jod_qty_loaded'];
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
                $row['jod_serial_number'] = [];
                if (empty($row['jod_jid_serial_number']) === false) {
                    $row['jod_serial_number'][] = $row['jod_jid_serial_number'];
                }
                $row['jod_quantity'] = $qty;
                $rows[] = $row;
                $gdIds[] = $row['jod_gd_id'];
            } else {
                $index = array_search($row['jod_gd_id'], $gdIds, true);
                if (empty($notes) === false) {
                    $rows[$index]['jod_notes'][] = $notes;
                }
                if (empty($row['jod_jid_serial_number']) === false) {
                    $rows[$index]['jod_serial_number'][] = $row['jod_jid_serial_number'];
                }
                $rows[$index]['jod_quantity'] += $qty;
            }
        }
        $results = [];
        $i = 0;
        foreach ($rows as $row) {
            $results[] = [
                'jod_gd_sku' => $row['jod_gd_sku'],
                'jod_goods' => $row['jod_goods'],
                'jod_quantity' => $number->doFormatFloat($row['jod_quantity']) . ' ' . $row['jod_unit'],
                'jod_serial_number' => implode(', ', $row['jod_serial_number']),
                'jod_notes' => implode('<br />', $row['jod_notes']),
            ];
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
        }
        $tbl->addRows($results);
        $tbl->setColumnType('jod_total_weight', 'float');
        $tbl->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('jod_quantity', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('jod_gdt_code', 'style', 'text-align: center;');
        $result .= $tbl->createTable();

        return $result;
    }
}
