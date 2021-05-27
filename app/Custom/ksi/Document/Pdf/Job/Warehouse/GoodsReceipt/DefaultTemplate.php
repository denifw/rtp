<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\ksi\Document\Pdf\Job\Warehouse\GoodsReceipt;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends \App\Model\Document\Pdf\Job\Warehouse\GoodsReceipt\DefaultTemplate
{

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

}
