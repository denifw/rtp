<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Custom\ksi\Statistic\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;

/**
 * Model statistic Storage overview custom KSI.
 *
 * @package    app
 * @subpackage Custom\ksi\Statistic\Warehouse
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class StorageOverview extends \App\Model\Statistic\Warehouse\StorageOverview
{
    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        $data = $this->doPrepareDataXlsCustom();
        $table = new Table('StockTblXls');
        $table->setHeaderRow($this->getHeaderXlsCustom());
        $table->addRows($data);
        $excel = new Excel();
        $sheetName = Trans::getWhsWord('storageOverview');
        $excel->addSheet($sheetName, $sheetName);
        $excel->setFileName($this->PageSetting->getPageDescription() . '_' . date('Y_m_d') . '.xlsx');
        $sheet = $excel->getSheet($sheetName, true);
        $excelTable = new ExcelTable($excel, $sheet);
        $excelTable->setTable($table);
        $excelTable->writeTable();
        $excel->setActiveSheet($sheetName);
        $excel->createExcel();
    }

    /**
     * Function to get header excel custom KSI.
     *
     * @return array
     */
    private function getHeaderXlsCustom(): array
    {
        $headers = [];
        if (empty($this->Data) === false) {
            $headers['gd_sku'] = Trans::getWord('sku');
            $headers['goods'] = Trans::getWhsWord('goods');
            foreach ($this->Data AS $row) {
                $headers[$row['title']] = $row['title'];
            }
        }

        return $headers;
    }

    /**
     * Function do prepare data custom excel for KSI.
     *
     * @return array
     */
    private function doPrepareDataXlsCustom(): array
    {
        $results = [];
        $arrayGoods = [];
        $goodsWhIds = [];
        foreach ($this->Data AS $row) {
            $goods = $row['goods'];
            foreach ($goods AS $rowGoods) {
                if (in_array($rowGoods['gd_sku'], $arrayGoods, true) === false) {
                    $arrayGoods[] = $rowGoods['gd_sku'];
                    $goodsWhIds[$rowGoods['gd_sku']][] = $row['id'];
                    $results[] = [
                        'goods' => $rowGoods['gd_name'],
                        'gd_sku' => $rowGoods['gd_sku'],
                        $row['title'] => $rowGoods['qty_total']
                    ];
                } else {
                    $index = array_search($rowGoods['gd_sku'], $arrayGoods, true);
                    if (in_array($row['id'], $goodsWhIds[$rowGoods['gd_sku']], true) === false) {
                        $goodsWhIds[$rowGoods['gd_sku']][] = $row['id'];
                        $results[$index][$row['title']] = $rowGoods['qty_total'];
                    } else {
                        $results[$index][$row['title']] += $rowGoods['qty_total'];
                    }
                }
            }
        }

        return $results;
    }
}
