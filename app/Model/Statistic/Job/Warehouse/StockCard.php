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

use App\Frame\Chart\TableChart\Column;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\ChartContainer;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractStatisticModel;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class StockCard extends AbstractStatisticModel
{

    /**
     * Property to store the data.
     *
     * @var array $Data
     */
    private $Data = [];
    /**
     * Property to portlet title.
     *
     * @var array $Title
     */
    private $Title;

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockCard');
        $this->setParameters($parameters);
        $this->Title = Trans::getWord('allWarehouse');
        if ($this->isValidParameter('warehouse') === true) {
            $this->Title = $this->getStringParameter('warehouse');
        }
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

        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gdc_name', $this->getStringParameter('gdc_name'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setEnableNewButton(false);
        $goodsCategoryField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsCategoryField->addClearField('gd_name');
        $goodsCategoryField->addClearField('gd_id');

        $brandField = $this->Field->getSingleSelect('brand', 'br_name', $this->getStringParameter('br_name'));
        $brandField->setHiddenField('gd_br_id', $this->getIntParameter('gd_br_id'));
        $brandField->addParameter('br_ss_id', $this->User->getSsId());
        $brandField->setEnableNewButton(false);
        $brandField->addClearField('gd_name');
        $brandField->addClearField('gd_id');
        $brandField->addOptionalParameterById('gd_rel_id', 'rel_id');

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_br_id', 'gd_br_id');
        $goodsField->addOptionalParameterById('gd_gdc_id', 'gd_gdc_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->setEnableNewButton(false);

        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
            $relationField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
            $relationField->addParameter('rel_ss_id', $this->User->getSsId());
            $relationField->setEnableNewButton(false);
            $relationField->addClearField('gdc_name');
            $relationField->addClearField('gd_gdc_id');
            $relationField->addClearField('br_name');
            $relationField->addClearField('gd_br_id');
            $relationField->addClearField('gd_name');
            $relationField->addClearField('gd_id');

            $this->StatisticForm->addField(Trans::getWord('relation'), $relationField);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $this->User->getRelId()));
        }
        $this->StatisticForm->addField(Trans::getWord('category'), $goodsCategoryField);
        $this->StatisticForm->addField(Trans::getWord('brand'), $brandField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);

        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
//        $this->StatisticForm->setGridDimension(4);
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->loadData();
        $chartData = $this->doPrepareChartData();
        $widgetData = $this->doPrepareWidgetData();
        $this->addContent('Content1', $this->getWidget($widgetData));
        $this->addContent('Content2', $this->getGoodStockChart($chartData) . $this->getDamageStockChart($chartData));
        $this->addContent('Content3', $this->getStockCardTablePortlet());
    }

    /**
     * Get query to get the quotation data.
     *
     * @return void
     */
    private function loadData(): void
    {
        $subWheres = ' WHERE (jid.jid_deleted_on IS NULL) AND (ji.ji_deleted_on IS NULL) AND (jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('wh_id')) {
            $subWheres .= ' AND (wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        # Set Select query;
        $query = 'SELECT  gd.gd_id, gd.gd_sku, gd.gd_name, rel.rel_short_name as rel_name, br.br_name, gdc.gdc_id, gdc.gdc_name, u.uom_name,
                  j.jis_stock as quantity, j.jid_gdt_id, j.weight, j.volume, j.wh_name, j.wh_id, j.gdu_qty_conversion,
			(CASE WHEN i.total IS NULL THEN 0 ELSE i.total END) as staging_in, (CASE WHEN o.total IS NULL THEN 0 ELSE o.total END) as staging_out
                  FROM goods AS gd INNER JOIN
                  brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                   goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                   unit as u ON gd.gd_uom_id = u.uom_id INNER JOIN
                  relation as rel ON gd.gd_rel_id = rel.rel_id LEFT OUTER JOIN
                  (SELECT jid.jid_gd_id, jid.jid_gdt_id, SUM(jis.jis_stock) AS jis_stock, wh.wh_name, wh.wh_id,
                  (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as weight,
                  (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as volume,
                  gdu.gdu_qty_conversion
                    FROM job_inbound_detail as jid INNER JOIN
                         job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                         job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                         warehouse as wh on ji.ji_wh_id = wh.wh_id INNER JOIN
                         goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIN
                      (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                       FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                       GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $subWheres . '
                       GROUP BY jid.jid_gd_id, jid.jid_gdt_id, wh.wh_name, wh.wh_id, jid.jid_weight, jid.jid_volume,
                       gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion) as j ON gd.gd_id = j.jid_gd_id LEFT OUTER JOIN
                       (select jog.jog_gd_id,  SUM(jir.jir_quantity) as total
               from job_inbound_receive as jir INNER JOIN
               job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
               job_inbound as ji ON ji.ji_id = jir.jir_ji_id INNER JOIN
               job_order as jo ON jo.jo_id = ji.ji_jo_id
               WHERE (jo.jo_deleted_on IS NULL) AND (ji.ji_deleted_on IS NULL)
               AND (ji.ji_end_store_on IS NULL) AND (ji.ji_end_load_on IS NOT NULL) AND (jir.jir_deleted_on IS NULL)
               group by jog.jog_gd_id) AS i ON gd.gd_id = i.jog_gd_id LEFT OUTER JOIN
               (select jog.jog_gd_id,  SUM(jod.jod_quantity) as total
               from job_outbound_detail as jod INNER JOIN
               job_goods as jog ON jog.jog_id = jod.jod_jog_id INNER JOIN
               job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
               job_order as jo ON jo.jo_id = job.job_jo_id
               WHERE (jo.jo_deleted_on IS NULL) AND (job.job_deleted_on is NULL)
               AND (job.job_end_store_on IS NOT NULL) AND (job.job_end_load_on IS NULL) AND (jod.jod_deleted_on is NULL)
               GROUP BY jog.jog_gd_id) AS o ON gd.gd_id = o.jog_gd_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        $query .= ' ORDER BY rel.rel_short_name, br.br_name, gd.gd_sku, gdc.gdc_name, gd.gd_id';
        $this->Data = $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the stock card table.
     *
     * @return Portlet
     */
    private function getStockCardTablePortlet(): Portlet
    {
        $title = Trans::getWord('stock') . ' ' . Trans::getWord('in') . ' ' . $this->Title;
        $portlet = new Portlet('StockCardPtl', $title);
        $portlet->addTable($this->getStockCardTable());
        $this->addDatas('StockCard', $portlet);

        return $portlet;
    }

    /**
     * Function to get the stock card table.
     *
     * @return Table
     */
    protected function getStockCardTable(): Table
    {
        $table = new TableDatas('StockCardTbl');
        $table->setHeaderRow([
            'rel_name' => Trans::getWord('relation'),
            'gd_sku' => Trans::getWord('sku'),
            'gd_full_name' => Trans::getWord('goods'),
            'goodQty' => Trans::getWord('goodItems'),
            'damageQty' => Trans::getWord('damageItems'),
            'staging_in' => Trans::getWord('stagingIn'),
            'staging_out' => Trans::getWord('stagingOut'),
            'uom_name' => Trans::getWord('uom'),
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'storage' => Trans::getWord('storage'),
        ]);
        $table->addRows($this->doPrepareStockCardTableData());
        $table->addColumnAttribute('rel_name', 'style', 'text-align: center');
        $table->setColumnType('goodQty', 'integer');
        $table->setColumnType('total_weight', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('damageQty', 'integer');
        $table->setColumnType('staging_in', 'integer');
        $table->setColumnType('staging_out', 'integer');
        $table->setFooterType('goodQty', 'SUM');
        $table->setFooterType('damageQty', 'SUM');
        $table->setFooterType('total_weight', 'SUM');
        $table->setFooterType('total_volume', 'SUM');
        $table->setFooterType('staging_in', 'SUM');
        $table->setFooterType('staging_out', 'SUM');
        $table->setViewActionByHyperlink(url('/goods/view'), ['gd_id', 'back_route']);
        $table->setRowsPerPage(25);
        return $table;
    }

    /**
     * Function to get the stock card table.
     *
     * @return array
     */
    private function doPrepareStockCardTableData(): array
    {

        $rows = [];
        $tempIds = [];
        $storageIds = [];
        $gdDao = new GoodsDao();
        foreach ($this->Data as $row) {
            $net = (float)$row['weight'];
            $cbm = (float)$row['volume'];
            $qty = (float)$row['quantity'];
            $stagingIn = (float)$row['staging_in'];
            $stagingOut = (float)$row['staging_out'];
            $qtyConversion = (float)$row['gdu_qty_conversion'];
            if (in_array($row['gd_id'], $tempIds, true) === false) {
                $tempIds[] = $row['gd_id'];
                $storageIds[$row['gd_id']] = [];
                $storageIds[$row['gd_id']][] = $row['wh_id'];
                $row['total_weight'] = ($qty + $stagingIn + $stagingOut) * $net;
                $row['total_volume'] = ($qty + $stagingIn + $stagingOut) * $cbm;
                if (empty($row['jid_gdt_id']) === false) {
                    $row['damageQty'] = $qty * $qtyConversion;
                    $row['goodQty'] = 0;
                } else {
                    $row['goodQty'] = $qty * $qtyConversion;
                    $row['damageQty'] = 0;
                }
                $row['back_route'] = 'stockCard';
                $row['storageNumber'] = 1;
                $row['storage'] = $row['storageNumber'] . ' Site';
                $row['gd_full_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
                $rows[] = $row;
            } else {
                $index = array_search($row['gd_id'], $tempIds, true);
                $rows[$index]['total_weight'] += ($qty * $net);
                $rows[$index]['total_volume'] += ($qty * $cbm);
                if (in_array($row['wh_id'], $storageIds[$row['gd_id']], true) === false) {
                    $storageIds[$row['gd_id']][] = $row['wh_id'];
                    $rows[$index]['storageNumber'] += 1;
                    $rows[$index]['storage'] = $rows[$index]['storageNumber'] . ' Sites';
                }
                if (empty($row['jid_gdt_id']) === false) {
                    $rows[$index]['damageQty'] += $qty * $qtyConversion;
                } else {
                    $rows[$index]['goodQty'] += $qty * $qtyConversion;
                }
            }
        }
        return $rows;
    }

    /**
     * Function to get the stock card table.
     *
     * @return array
     */
    private function doPrepareChartData(): array
    {
        $result = [];
        $tempIds = [];
        foreach ($this->Data as $row) {
            $qty = (float)$row['quantity'] * (float)$row['gdu_qty_conversion'];
            $item = [
                'wh_id' => $row['wh_id'],
                'wh_name' => $row['wh_name'],
                'quantity' => $qty,
            ];
            if (in_array($row['gd_id'], $tempIds, true) === false) {
                $tempIds[] = $row['gd_id'];
                $goods = [
                    'gd_id' => $row['gd_id'],
                    'gd_sku' => $row['gd_sku'],
                    'gd_name' => $row['gd_name'],
                    'gd_good_wh_ids' => [],
                    'gd_good_items' => [],
                    'gd_total_good' => 0,
                    'gd_damage_wh_ids' => [],
                    'gd_damage_items' => [],
                    'gd_total_damage' => 0,
                ];
                if (empty($row['jid_gdt_id']) === false) {
                    $goods['gd_damage_wh_ids'][] = $row['wh_id'];
                    $goods['gd_damage_items'][] = $item;
                    $goods['gd_total_damage'] += $qty;
                } else {
                    $goods['gd_good_wh_ids'][] = $row['wh_id'];
                    $goods['gd_good_items'][] = $item;
                    $goods['gd_total_good'] += $qty;
                }
                $result[] = $goods;
            } else {
                $index = array_search($row['gd_id'], $tempIds, true);
                if (empty($row['jid_gdt_id']) === false) {
                    $result[$index]['gd_total_damage'] += $qty;
                    if (in_array($row['wh_id'], $result[$index]['gd_damage_wh_ids'], true) === false) {
                        $result[$index]['gd_damage_wh_ids'][] = $row['wh_id'];
                        $result[$index]['gd_damage_items'][] = $item;
                    } else {
                        $whIndex = array_search($row['wh_id'], $result[$index]['gd_damage_wh_ids'], true);
                        $result[$index]['gd_damage_items'][$whIndex]['quantity'] += $qty;
                    }
                } else {
                    $result[$index]['gd_total_good'] += $qty;
                    if (in_array($row['wh_id'], $result[$index]['gd_good_wh_ids'], true) === false) {
                        $result[$index]['gd_good_wh_ids'][] = $row['wh_id'];
                        $result[$index]['gd_good_items'][] = $item;
                    } else {
                        $whIndex = array_search($row['wh_id'], $result[$index]['gd_good_wh_ids'], true);
                        $result[$index]['gd_good_items'][$whIndex]['quantity'] += $qty;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Function to get the stock card table.
     *
     * @return array
     */
    private function doPrepareWidgetData(): array
    {
        $result = [
            'damageQty' => 0,
            'goodQty' => 0,
        ];
        foreach ($this->Data as $row) {
            $qty = (float)$row['quantity'];
            $qtyConversion = (float)$row['gdu_qty_conversion'];
            if (empty($row['jid_gdt_id']) === false) {
                $result['damageQty'] += $qty * $qtyConversion;
            } else {
                $result['goodQty'] += $qty * $qtyConversion;
            }
        }
        return $result;
    }

    /**
     * Function to add stock widget
     *
     * @param array $widgetData To store widget data.
     *
     * @return string
     */
    private function getWidget(array $widgetData): string
    {
        $number = new NumberFormatter();
        $goodStock = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('totalGoodStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark-blue',
            'amount' => $number->doFormatFloat($widgetData['goodQty']),
            'uom' => 'Items',
            'url' => '',
        ];
        $goodStock->setData($data);
        $goodStock->setGridDimension(3, 3);

        # damage Stock
        $damageStock = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('totalDamageStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-danger',
            'amount' => $number->doFormatFloat($widgetData['damageQty']),
            'uom' => 'Items',
            'url' => '',
        ];
        $damageStock->setData($data);
        $damageStock->setGridDimension(3, 3);
        # Staging Inbound
        $stagingInbound = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('stagingInbound'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-teal-second',
            'amount' => $this->getTotalInboundStaging(),
            'uom' => 'Items',
            'url' => '',
        ];
        $stagingInbound->setData($data);
        $stagingInbound->setGridDimension(3, 3);

        # Staging Outbound
        $stagingOutbound = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('stagingOutbound'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-second',
            'amount' => $this->getTotalOutboundStaging(),
            'uom' => 'Items',
            'url' => '',
        ];
        $stagingOutbound->setData($data);
        $stagingOutbound->setGridDimension(3, 3);

        return $goodStock->createView() . $damageStock->createView() . $stagingInbound->createView() . $stagingOutbound->createView();
    }


    /**
     * Function to get the total staging inbound
     *
     * @return string
     */
    private function getTotalInboundStaging(): string
    {
        $result = '0';
        $number = new NumberFormatter();
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_end_store_on IS NULL)';
        $wheres[] = '(ji.ji_end_load_on IS NOT NULL)';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        } elseif ($this->isValidParameter('rel_id')) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('gd_id')) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }
        if ($this->isValidParameter('wh_id')) {
            $wheres[] = ' (ji.ji_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'select SUM(jir.jir_quantity) as total
                from job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
                    goods as gd ON gd.gd_id = jog.jog_gd_id INNER JOIN
                    job_inbound as ji ON ji.ji_id = jir.jir_ji_id INNER JOIN
                    job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $data = DataParser::objectToArray($sqlResults[0]);
            $result = $number->doFormatFloat((float)$data['total']);
        }

        return $result;
    }

    /**
     * Function to get the total staging outbound
     *
     * @return string
     */
    private function getTotalOutboundStaging(): string
    {
        $result = '0';
        $number = new NumberFormatter();
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(job.job_deleted_on is NULL)';
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(job.job_end_load_on IS NULL)';
        $wheres[] = '(jod.jod_deleted_on is NULL)';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        } elseif ($this->isValidParameter('rel_id')) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('gd_id')) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }
        if ($this->isValidParameter('wh_id')) {
            $wheres[] = '(job.job_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'select SUM(jod.jod_quantity) as total
                from job_outbound_detail as jod INNER JOIN
                    job_goods as jog ON jog.jog_id = jod.jod_jog_id INNER JOIN
                    goods as gd ON gd.gd_id = jog.jog_gd_id INNER JOIN
                    job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                    job_order as jo ON jo.jo_id = job.job_jo_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $data = DataParser::objectToArray($sqlResults[0]);
            $result = $number->doFormatFloat((float)$data['total']);
        }

        return $result;
    }

    /**
     * Function to get the stock card chart.
     *
     * @param array $chartData To store the chart data.
     *
     * @return Portlet
     */
    private function getDamageStockChart(array $chartData): Portlet
    {
        $table = new Table('StockDamageTbl');
        $table->setHeaderRow([
            'gd_name' => Trans::getWord('goods'),
            'wh_name' => Trans::getWord('warehouse'),
            'damageQty' => Trans::getWord('damageItems'),
        ]);
        $damageQty = array_column($chartData, 'gd_total_damage');
        array_multisort($damageQty, SORT_DESC, $chartData);
        $data = array_slice($chartData, 0, 10);
        $rows = [];
        foreach ($data as $row) {
            $items = $row['gd_damage_items'];
            foreach ($items as $item) {
                $rows[] = [
                    'gd_name' => $row['gd_name'],
                    'wh_name' => $item['wh_name'],
                    'damageQty' => $item['quantity'],
                ];
            }
        }
        $table->addRows($rows);

        $chart = new Column($table);
        $chart->setTitle(Trans::getWord('damageStock'));
        $chart->setYTitle(Trans::getWord('items'));
        $chart->setXColumn('gd_name', Trans::getWord('goods'));
        $chart->addYColumn('damageQty', '');
        $chart->setDrillDown('wh_name', 'pie');

        $container = new ChartContainer('ScDmgStcCc');
        $container->setChart($chart);
        $title = Trans::getWord('top') . ' 10 ' . Trans::getWord('damageStock') . ' ' . Trans::getWord('in') . ' ' . $this->Title;
        $portlet = new Portlet('ScDmgGrpPtl', $title);
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addText($container);

        return $portlet;
    }

    /**
     * Function to get the stock card chart.
     *
     * @param array $chartData To store the chart data.
     *
     * @return Portlet
     */
    private function getGoodStockChart(array $chartData): Portlet
    {
        $table = new Table('StockCardChartTbl');
        $table->setHeaderRow([
            'gd_name' => Trans::getWord('goods'),
            'wh_name' => Trans::getWord('warehouse'),
            'goodQty' => Trans::getWord('goodItems'),
        ]);
        $goodQty = array_column($chartData, 'gd_total_good');
        array_multisort($goodQty, SORT_DESC, $chartData);
        $data = array_slice($chartData, 0, 10);
        $rows = [];
        foreach ($data as $row) {
            $items = $row['gd_good_items'];
            foreach ($items as $item) {
                $rows[] = [
                    'gd_name' => $row['gd_name'],
                    'wh_name' => $item['wh_name'],
                    'goodQty' => $item['quantity'],
                ];
            }
        }
        $table->addRows($rows);

        $chart = new Column($table);
        $chart->setTitle(Trans::getWord('goodStock'));
        $chart->setYTitle(Trans::getWord('items'));
        $chart->setXColumn('gd_name', Trans::getWord('goods'));
        $chart->addYColumn('goodQty', '');
        $chart->setDrillDown('wh_name', 'pie');

        $container = new ChartContainer('ScGraphCc');
        $container->setChart($chart);
        $title = Trans::getWord('top') . ' 10 ' . Trans::getWord('goodStock') . ' ' . Trans::getWord('in') . ' ' . $this->Title;
        $portlet = new Portlet('ScGraphPtl', $title);
        $portlet->setGridDimension(6, 6, 12);
        $portlet->addText($container);

        return $portlet;
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        $wheres[] = '((j.jis_stock > 0) OR (i.total > 0) OR (o.total > 0))';
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(rel.rel_id = ' . $this->User->getRelId() . ')';
        } elseif ($this->isValidParameter('rel_id')) {
            $wheres[] = '(rel.rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }

        if ($this->isValidParameter('gd_id')) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        parent::loadDefaultButton();
        $this->View->setNumberOfButton(4);
        $pdfButton = new PdfButton('ScPdf', Trans::getWord('printPdf'), 'stockreport');
        $pdfButton->setIcon(Icon::FilePdfO)->btnDanger()->pullRight()->btnMedium();
        $pdfButton->addParameter('ss_id', $this->User->getSsId());
        if ($this->isValidParameter('rel_id')) {
            $pdfButton->addParameter('gd_rel_id', $this->getIntParameter('rel_id'));
        }
        if ($this->isValidParameter('gd_id')) {
            $pdfButton->addParameter('gd_id', $this->getIntParameter('gd_id'));
        }
        if ($this->isValidParameter('gd_br_id')) {
            $pdfButton->addParameter('gd_br_id', $this->getIntParameter('gd_br_id'));
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $pdfButton->addParameter('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        }
        if ($this->isValidParameter('wh_id')) {
            $pdfButton->addParameter('wh_id', $this->getIntParameter('wh_id'));
        }
        $this->View->addButtonAfter($pdfButton, 'btnExportXls');
    }
}
