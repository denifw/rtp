<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Finance;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class SalesAging extends AbstractStatisticModel
{

    /**
     * Property to store the data.
     *
     * @var array $Data
     */
    private $Data = [];

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'salesAging');
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

        $storageField = $this->Field->getSingleSelect('warehouseStorage', 'whs_name', $this->getStringParameter('whs_name'));
        $storageField->setHiddenField('whs_id', $this->getIntParameter('whs_id'));
        $storageField->addParameterById('whs_wh_id', 'wh_id', Trans::getWord('warehouse'));
        $storageField->setEnableNewButton(false);
        if ($this->isValidParameter('view_by') === false) {
            $this->setParameter('view_by', 'W');
        }

        $viewField = $this->Field->getRadioGroup('view_by', $this->getStringParameter('view_by'));
        $viewField->addRadios([
            'W' => Trans::getWord('warehouse'),
            'S' => Trans::getWord('storage'),
        ]);


        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gdc_name', $this->getStringParameter('gdc_name'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setEnableNewButton(false);
        $goodsCategoryField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsCategoryField->addClearField('gd_name');
        $goodsCategoryField->addClearField('gd_id');

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_gdc_id', 'gd_gdc_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->setEnableNewButton(false);


        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField, true);
        $this->StatisticForm->addField(Trans::getWord('storage'), $storageField);
        $this->StatisticForm->addField(Trans::getWord('category'), $goodsCategoryField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('viewBy'), $viewField);
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->loadData();
        if (empty($this->Data) === false) {
            foreach ($this->Data as $row) {

                if ((float)$row['total'] !== '0') {
                    $this->addContent('stock' . $row['id'], $this->getStockTable($row));
                }
            }
        } else {
            $this->addContent('stock', $this->getEmptyPortlet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('wh_id');
    }


    /**
     * Get query to get the quotation data.
     *
     * @return void
     */
    private function loadData(): void
    {
        # Set Select query;
        $query = 'SELECT  wh.wh_id, wh.wh_name, whs.whs_id, whs.whs_name, gd.gd_id, gd.gd_sku,
                      gd.gd_name, gdc.gdc_name, br.br_name, uom.uom_code, jid.jid_gdt_id, SUM(jis.stock) as jid_stock
                  FROM warehouse_storage as whs INNER JOIN 
                  job_inbound_detail as jid ON whs.whs_id = jid.jid_whs_id INNER JOIN
                  goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                  unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                  warehouse as wh ON whs.whs_wh_id = wh.wh_id INNER JOIN 
                  goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                  goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                  brand as br on br.br_id = gd.gd_br_id INNER JOIN
                   (SELECT jis_jid_id, SUM(jis_quantity) as stock 
                        FROM job_inbound_stock
                        WHERE (jis_deleted_on IS NULL)
                        GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        $query .= ' GROUP BY wh.wh_id, wh.wh_name, whs.whs_id, whs.whs_name, gd.gd_id, gd.gd_sku,
                            gd.gd_name, gdc.gdc_name, br.br_name, uom.uom_code, jid.jid_gdt_id';
        $query .= ' ORDER BY wh.wh_name, whs.whs_name, whs.whs_id';
        $sqlResults = $this->loadDatabaseRow($query);
        if ($this->getStringParameter('view_by', 'W') === 'S') {
            $this->doPrepareStorageData($sqlResults);
        } else {
            $this->doPrepareWarehouseData($sqlResults);
        }
    }

    /**
     * Function to get the stock card table.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareWarehouseData(array $data): void
    {
        $tempId = [];
        $tempWhsGdId = [];
        foreach ($data as $row) {
            $goodsKey = $row['uom_code'] . '' . $row['gd_id'] . '' . $row['whs_id'];
            $goods = [
                'whs_id' => $row['whs_id'],
                'whs_name' => $row['whs_name'],
                'gd_sku' => $row['gd_sku'],
                'gd_name' => $row['br_name'] . ' ' . $row['gdc_name'] . ' ' . $row['gd_name'],
                'gd_uom' => $row['uom_code'],
            ];
            if (in_array($row['wh_id'], $tempId, true) === false) {
                if (empty($row['jid_gdt_id']) === true) {
                    $goods['qty_good'] = (float)$row['jid_stock'];
                    $goods['qty_damage'] = 0;
                    $goods['qty_total'] = (float)$row['jid_stock'];
                } else {
                    $goods['qty_good'] = 0;
                    $goods['qty_damage'] = (float)$row['jid_stock'];
                    $goods['qty_total'] = (float)$row['jid_stock'];
                }
                $tempWhsGdId[$row['wh_id']] = [];
                $tempWhsGdId[$row['wh_id']][] = $goodsKey;

                $data = [
                    'id' => $row['wh_id'],
                    'title' => $row['wh_name'],
                    'goods' => [],
                    'total' => (float)$row['jid_stock'],
                ];
                $data['goods'][] = $goods;
                $this->Data[] = $data;
                $tempId[] = $row['wh_id'];
            } else {
                $index = array_search($row['wh_id'], $tempId, true);
                if (in_array($goodsKey, $tempWhsGdId[$row['wh_id']], true) === false) {
                    if (empty($row['jid_gdt_id']) === true) {
                        $goods['qty_good'] = (float)$row['jid_stock'];
                        $goods['qty_damage'] = 0;
                        $goods['qty_total'] = (float)$row['jid_stock'];
                    } else {
                        $goods['qty_good'] = 0;
                        $goods['qty_damage'] = (float)$row['jid_stock'];
                        $goods['qty_total'] = (float)$row['jid_stock'];
                    }
                    $tempWhsGdId[$row['wh_id']][] = $goodsKey;
                    $this->Data[$index]['goods'][] = $goods;
                } else {
                    $indexGoods = array_search($goodsKey, $tempWhsGdId[$row['wh_id']], true);
                    if (empty($row['jid_gdt_id']) === true) {
                        $this->Data[$index]['goods'][$indexGoods]['qty_good'] += (float)$row['jid_stock'];
                        $this->Data[$index]['goods'][$indexGoods]['qty_total'] += (float)$row['jid_stock'];
                    } else {
                        $this->Data[$index]['goods'][$indexGoods]['qty_damage'] += (float)$row['jid_stock'];
                        $this->Data[$index]['goods'][$indexGoods]['qty_total'] += (float)$row['jid_stock'];
                    }
                }
                $this->Data[$index]['total'] += (float)$row['jid_stock'];
            }
        }
    }


    /**
     * Function to get the stock card table.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareStorageData(array $data): void
    {
        $tempId = [];
        $tempWhsGdId = [];
        foreach ($data as $row) {
            $goodsKey = $row['uom_code'] . '' . $row['gd_id'];
            $goods = [
                'gd_sku' => $row['gd_sku'],
                'gd_name' => $row['br_name'] . ' ' . $row['gdc_name'] . ' ' . $row['gd_name'],
                'gd_uom' => $row['uom_code'],
            ];
            if (in_array($row['whs_id'], $tempId, true) === false) {
                if (empty($row['jid_gdt_id']) === true) {
                    $goods['qty_good'] = (float)$row['jid_stock'];
                    $goods['qty_damage'] = 0;
                    $goods['qty_total'] = (float)$row['jid_stock'];
                } else {
                    $goods['qty_good'] = 0;
                    $goods['qty_damage'] = (float)$row['jid_stock'];
                    $goods['qty_total'] = (float)$row['jid_stock'];
                }
                $tempWhsGdId[$row['whs_id']] = [];
                $tempWhsGdId[$row['whs_id']][] = $goodsKey;

                $data = [
                    'whs_id' => $row['whs_id'],
                    'id' => $row['whs_id'],
                    'title' => $row['wh_name'] . " - " . $row['whs_name'],
                    'goods' => [],
                    'total' => (float)$row['jid_stock'],
                ];
                $data['goods'][] = $goods;
                $this->Data[] = $data;
                $tempId[] = $row['whs_id'];
            } else {
                $index = array_search($row['whs_id'], $tempId, true);
                if (in_array($goodsKey, $tempWhsGdId[$row['whs_id']], true) === false) {
                    if (empty($row['jid_gdt_id']) === true) {
                        $goods['qty_good'] = (float)$row['jid_stock'];
                        $goods['qty_damage'] = 0;
                        $goods['qty_total'] = (float)$row['jid_stock'];
                    } else {
                        $goods['qty_good'] = 0;
                        $goods['qty_damage'] = (float)$row['jid_stock'];
                        $goods['qty_total'] = (float)$row['jid_stock'];
                    }
                    $tempWhsGdId[$row['whs_id']][] = $goodsKey;
                    $this->Data[$index]['goods'][] = $goods;
                } else {
                    $indexGoods = array_search($goodsKey, $tempWhsGdId[$row['whs_id']], true);
                    if (empty($row['jid_gdt_id']) === true) {
                        $this->Data[$index]['goods'][$indexGoods]['qty_good'] += (float)$row['jid_stock'];
                        $this->Data[$index]['goods'][$indexGoods]['qty_total'] += (float)$row['jid_stock'];
                    } else {
                        $this->Data[$index]['goods'][$indexGoods]['qty_damage'] += (float)$row['jid_stock'];
                        $this->Data[$index]['goods'][$indexGoods]['qty_total'] += (float)$row['jid_stock'];
                    }
                }
                $this->Data[$index]['total'] += (float)$row['jid_stock'];
            }
        }
    }

    /**
     * Function to get the stock card table.
     *
     * @param array $data To store list of goods.
     *
     * @return Portlet
     */
    protected function getStockTable(array $data): Portlet
    {
        $table = new Table('StockTbl' . $data['id']);
        $table->setHeaderRow([
            'gd_sku' => Trans::getWord('sku'),
            'gd_name' => Trans::getWord('goods'),
            'qty_good' => Trans::getWord('qtyGood'),
            'qty_damage' => Trans::getWord('qtyDamage'),
            'gd_uom' => Trans::getWord('uom'),
        ]);
        $goods = $data['goods'];
        $rows = [];
        foreach ($goods as $row) {
            if ((float)$row['qty_total'] !== 0.0) {
                $rows[] = $row;
            }
        }
        $table->addRows($rows);
        if ($this->getStringParameter('view_by', 'W') === 'W') {
            $table->addColumnAtTheBeginning('whs_name', Trans::getWord('storage'));
            $table->addColumnAttribute('whs_name', 'style', 'text-align: center;');
        }
        $table->setColumnType('qty_good', 'float');
        $table->setColumnType('qty_damage', 'float');
        $table->setFooterType('qty_good', 'SUM');
        $table->setFooterType('qty_damage', 'SUM');
        $table->addColumnAttribute('gd_uom', 'style', 'text-align: center;');
        $portlet = new Portlet('StockPtl' . $data['id'], $data['title']);
        $portlet->addTable($table);
        $this->addDatas('StorageOverview' . $data['id'], $portlet);

        return $portlet;
    }

    /**
     * Function to get the stock card table.
     *
     * @return Portlet
     */
    private function getEmptyPortlet(): Portlet
    {
        $portlet = new Portlet('StockPtl', Trans::getWord('overview'));
        $portlet->addText(Trans::getWord('noDataFound', 'message'));
        $this->addDatas('StorageOverview', $portlet);

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
        if ($this->isValidParameter('wh_id')) {
            $wheres[] = '(wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('whs_id')) {
            $wheres[] = '(whs.whs_id = ' . $this->getIntParameter('whs_id') . ')';
        }
        if ($this->isValidParameter('gd_id')) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }

        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }

        if ($this->PageSetting->checkPageRight('AllowSeeAllGoods') === false) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }

        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.stock <> 0)';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
