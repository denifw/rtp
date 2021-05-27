<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Job\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class StockAging extends AbstractStatisticModel
{

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockAging');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $whField = $this->Field->getSingleSelect('warehouse', 'warehouse', $this->getStringParameter('warehouse'));
        $whField->setHiddenField('wh_id', $this->getIntParameter('wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->addOptionalParameterById('gd_gdc_id', 'gd_gdc_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->setEnableNewButton(false);
        # Condition field
        $conditionFields = $this->Field->getSelect('gd_condition', $this->getStringParameter('gd_condition'));
        $conditionFields->addOption(Trans::getWord('allCondition'), 'A');
        $conditionFields->addOption(Trans::getWord('good'), 'G');
        $conditionFields->addOption(Trans::getWord('damage'), 'D');
        $conditionFields->setPleaseSelect(false);
        # Category Field
        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gdc_name', $this->getStringParameter('gdc_name'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setEnableNewButton(false);
        $goodsCategoryField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsCategoryField->addClearField('gd_name');
        $goodsCategoryField->addClearField('gd_id');

        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
            $relationField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
            $relationField->addParameter('rel_ss_id', $this->User->getSsId());
            $relationField->addClearField('gdc_name');
            $relationField->addClearField('gd_gdc_id');
            $relationField->addClearField('gd_name');
            $relationField->addClearField('gd_id');
            $relationField->setEnableNewButton(false);

            $this->StatisticForm->addField(Trans::getWord('relation'), $relationField);
            $this->StatisticForm->setGridDimension(4);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $this->User->getRelId()));
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_name', $this->User->Relation->getName()));
        }
        $this->StatisticForm->addField(Trans::getWord('category'), $goodsCategoryField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
        $this->StatisticForm->addField(Trans::getWord('condition'), $conditionFields);
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $portlet = $this->getResultPortlet();
        if ($this->getFormAction() === 'doExportXls') {
            $this->addDatas('Stock', $portlet);
        }
        $this->addContent('RslCtn', $portlet);
    }


    /**
     * Function to get the report portlet.
     *
     * @return Portlet
     */
    protected function getResultPortlet(): Portlet
    {
        $table = new Table('RslTbl');
        $table->setHeaderRow([
            'gd_relation' => Trans::getWord('relation'),
            'gd_sku' => Trans::getWord('sku'),
            'gd_category' => Trans::getWord('category'),
            'gd_uom' => Trans::getWord('uom'),
            'gd_stock' => Trans::getWord('totalQty'),
        ]);
        if ($this->getFormAction() === 'doExportXls') {
            $table->addColumnAfter('gd_sku', 'gd_brand', Trans::getWord('brand'));
            $table->addColumnAfter('gd_category', 'gd_name', Trans::getWord('goods'));
        }
        $index = 1;
        for ($i = 1; $i < 360; $i += 30) {
            $table->addColumnAtTheEnd('aging_' . $index, $i . '-' . ($i + 29) . ' ' . Trans::getWord('days'));
            $table->setColumnType('aging_' . $index, 'integer');
            $table->setFooterType('aging_' . $index, 'SUM');
            $index++;
        }
        $table->addColumnAfter('aging_' . $index, 'aging_' . ($index + 1), Trans::getWord('over') . ' 360 ' . Trans::getWord('days'));
        $table->setColumnType('aging_' . ($index + 1), 'integer');
        $table->setFooterType('aging_' . ($index + 1), 'SUM');
        $table->setColumnType('gd_stock', 'integer');
        $table->setFooterType('gd_stock', 'SUM');


        $table->setViewActionByHyperlink(url('/goods/view'), ['gd_id', 'back_route']);
        $table->addRows($this->doPrepareData());
        $portlet = new Portlet('RslPtl', Trans::getWord('stockAging'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to prepare the data.
     *
     * @return array
     */
    protected function doPrepareData(): array
    {
        $results = [];
        $data = $this->loadData();
        $tempGdId = [];
        $nowDate = DateTimeParser::createFromFormat(date('Y-m-d') . ' 01:00:00');
        foreach ($data as $row) {
            if (in_array($row['gd_id'], $tempGdId, true) === false) {
                $temp = [
                    'gd_relation' => $row['gd_relation'],
                    'gd_id' => $row['gd_id'],
                    'gd_sku' => $row['gd_sku'],
                    'gd_brand' => $row['gd_brand'],
                    'gd_category' => $row['gd_category'],
                    'gd_name' => $row['gd_name'],
                    'gd_uom' => $row['gd_uom'],
                    'gd_stock' => 0.0,
                    'back_route' => 'stockAging',
                ];
                $tempGdId[] = $row['gd_id'];
                $results[] = $this->doCalculateAging($temp, $nowDate, $row);
            } else {
                $index = array_search($row['gd_id'], $tempGdId, true);
                $temp = $results[$index];
                $results[$index] = $this->doCalculateAging($temp, $nowDate, $row);

            }
        }

        return $results;
    }

    /**
     * Function to calculate aging data.
     *
     * @param array $temp To store temp result data.
     * @param DateTime|null $nowDate To store the current time compare.
     * @param array $row To store row data from database.
     *
     * @return array
     */
    protected function doCalculateAging(array $temp, ?DateTime $nowDate, array $row): array
    {
        $quantity = (float)$row['gdu_qty_conversion'] * (float)$row['gd_stock'];
        $temp['gd_stock'] += $quantity;
        # Calculate aging
        $inboundDate = DateTimeParser::createFromFormat($row['start_on'] . ' 01:00:00');
        if ($nowDate !== null && $inboundDate !== null) {
            $diff = DateTimeParser::different($nowDate, $inboundDate);
            $aging = (int)$diff['days'];
            $indexData = $this->doFindIndex($aging);
            if (array_key_exists($indexData, $temp) === false) {
                $temp[$indexData] = $quantity;
            } else {
                $temp[$indexData] += $quantity;
            }
        }
        return $temp;
    }

    /**
     * Function to find index data.
     *
     * @param int $aging To store the amount of aging.
     *
     * @return string
     */
    private function doFindIndex(int $aging): string
    {
        $results = 'aging_';
        $index = (int)($aging / 30);
        $modula = $aging % 30;
        if ($modula > 0) {
            $index++;
        }
        if ($index > 12) {
            $index = 13;
        }
        return $results . $index;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $this->User->getSsId());
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.stock > 0)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ji.ji_wh_id', $this->getIntParameter('wh_id'));
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jid.jid_gd_id', $this->getIntParameter('gd_id'));
        }
        if ($this->isValidParameter('gd_gdc_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('gd.gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        }
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(rel.rel_id = ' . $this->User->getRelId() . ')';
        } elseif ($this->isValidParameter('rel_id')) {
            $wheres[] = SqlHelper::generateNumericCondition('gd.gd_rel_id', $this->getIntParameter('rel_id'));
        }
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition !== 'A') {
            if ($condition === 'G') {
                $wheres[] = '(jid.jid_gdt_id IS NULL)';
            } else {
                $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
            }
        }
        $query = 'SELECT (ji.ji_start_load_on::timestamp::date) as start_on, jid.jid_gd_id, gd.gd_id, gd.gd_sku, gd.gd_name, gd.gd_rel_id, br.br_name as gd_brand, gdc.gdc_name as gd_category, rel.rel_short_name as gd_relation, 
                        jid.jid_gdu_id, uom.uom_code as gd_uom, gdu.gdu_qty_conversion, SUM(jis.stock) as gd_stock
                FROM job_order as jo
                 INNER JOIN job_inbound as ji ON jo.jo_id = ji.ji_jo_id
                 INNER JOIN job_inbound_detail as jid ON ji.ji_id = jid.jid_ji_id
                 INNER JOIN goods as gd ON jid.jid_gd_id = gd.gd_id
                 INNER JOIN brand as br ON gd.gd_br_id = br.br_id
                 INNER JOIN goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id
                 INNER JOIN relation as rel ON gd.gd_rel_id = rel.rel_id
                 INNER JOIN goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id
                 INNER JOIN unit as uom ON uom.uom_id = gdu.gdu_uom_id
                    INNER JOIN (SELECT jis_jid_id, SUM(jis_quantity) as stock
                        FROM job_inbound_stock
                        WHERE jis_deleted_on IS NULL
                        GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id';
        $query .= ' WHERE ' . implode(' AND ', $wheres);

        $query .= ' GROUP BY ji.ji_start_load_on::timestamp::date, jid.jid_gd_id, gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, gd.gd_rel_id, rel.rel_short_name, 
                        jid.jid_gdu_id, uom.uom_code, gdu.gdu_qty_conversion';
        $query .= ' ORDER BY rel.rel_short_name, gd.gd_sku, gd.gd_id';
        $sqlResults = DB::select($query);
        $results = [];
        if (empty($sqlResults) === false) {
            $results = DataParser::arrayObjectToArray($sqlResults);
        }
        return $results;
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        try {
            $excel = new Excel();
            foreach ($this->Datas as $key => $portlet) {
                if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                    $sheetName = StringFormatter::formatExcelSheetTitle(trim($key));
                    $excel->addSheet($sheetName, $sheetName);
                    $excel->setFileName($this->PageSetting->getPageDescription() . ' ' . $this->getDate() . '.xlsx');
                    $sheet = $excel->getSheet($sheetName, true);
                    $sheet->mergeCells('A1:E1');
                    $sheet->setCellValue('A1', Trans::getWord('stockAging'));
                    $sheet->getStyle('A1')->getFont()->setBold(true);

                    $sheet->mergeCells('A2:B2');
                    $sheet->setCellValue('A2', Trans::getWord('date'));
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->mergeCells('C2:E2');
                    $sheet->setCellValue('C2', $this->getDate());
                    $sheet->getStyle('C2')->getFont()->setBold(true);

                    $sheet->mergeCells('A3:B3');
                    $sheet->setCellValue('A3', Trans::getWord('warehouse'));
                    $sheet->getStyle('A3')->getFont()->setBold(true);
                    $sheet->mergeCells('C3:E3');
                    $sheet->setCellValue('C3', $this->getWarehouse());
                    $sheet->getStyle('C3')->getFont()->setBold(true);

                    $sheet->mergeCells('A4:B4');
                    $sheet->setCellValue('A4', Trans::getWord('relation'));
                    $sheet->getStyle('A4')->getFont()->setBold(true);
                    $sheet->mergeCells('C4:E4');
                    $sheet->setCellValue('C4', $this->getRelation());
                    $sheet->getStyle('C4')->getFont()->setBold(true);

                    $sheet->mergeCells('C5:E5');
                    $sheet->setCellValue('C5', $this->getGoodsCategory());
                    $sheet->getStyle('C5')->getFont()->setBold(true);
                    $sheet->mergeCells('A5:B5');
                    $sheet->setCellValue('A5', Trans::getWord('category'));
                    $sheet->getStyle('A5')->getFont()->setBold(true);

                    $sheet->mergeCells('C6:E6');
                    $sheet->setCellValue('C6', $this->getCondition());
                    $sheet->getStyle('C6')->getFont()->setBold(true);
                    $sheet->mergeCells('A6:B6');
                    $sheet->setCellValue('A6', Trans::getWord('condition'));
                    $sheet->getStyle('A6')->getFont()->setBold(true);
//                $sheet->freezePane('A6','A6');
                    $excel->doRowMovePointer($sheetName);
                    $excelTable = new ExcelTable($excel, $sheet);
                    $excelTable->setTable($portlet->Body[0]);
                    $excelTable->writeTable();
                    $excel->setActiveSheet($sheetName);
                }
            }
            $excel->createExcel();
        } catch (Exception $e) {
            $this->View->addErrorMessage('Failed to generate excel file.');
        }
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getDate(): string
    {
        $date = DateTimeParser::createDateTime();
        return $date->format('d M Y');
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getWarehouse(): string
    {
        return $this->getStringParameter('warehouse', Trans::getWord('allWarehouse'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getRelation(): string
    {
        return $this->getStringParameter('rel_name', Trans::getWord('allRelation'));
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getGoodsCategory(): string
    {
        return $this->getStringParameter('gdc_name', Trans::getWord('allCategory'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCondition(): string
    {
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition === 'G') {
            return Trans::getWord('good');
        }
        if ($condition === 'D') {
            return Trans::getWord('damage');
        }
        return Trans::getWord('allCondition');
    }
}
