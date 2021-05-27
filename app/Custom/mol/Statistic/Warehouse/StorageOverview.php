<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Custom\mol\Statistic\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class StorageOverview extends \App\Model\Statistic\Warehouse\StorageOverview
{

    /**
     * Function to get the stock card table.
     *
     * @par array $data To store list of goods.
     * @return Portlet
     */
    protected function getStockTable(array $data): Portlet
    {
        $table = new Table('StockTbl' . $data['id']);
        $table->setHeaderRow([
            'gd_sku' => Trans::getWord('sku'),
            'gd_brand' => Trans::getWord('brand'),
            'gd_category' => Trans::getWord('category'),
            'gd_name' => Trans::getWord('goods'),
            'qty_good' => Trans::getWord('qtyGood'),
            'qty_damage' => Trans::getWord('qtyDamage'),
            'gd_uom' => Trans::getWord('uom'),
        ]);
        $table->addRows($data['goods']);
        if ($this->getStringParameter('view_by', 'W') === 'W') {
            $table->addColumnAtTheBeginning('whs_name', Trans::getWord('storage'));
            $table->addColumnAttribute('whs_name', 'style', 'text-align: center;');
        }
        $table->setColumnType('qty_good', 'float');
        $table->setColumnType('qty_damage', 'float');
        $table->setFooterType('qty_good', 'SUM');
        $table->setFooterType('qty_damage', 'SUM');
        $table->addColumnAttribute('gd_uom', 'style', 'text-align: center;');
        $table->setViewActionByHyperlink(url('/warehouseStorage/view'), ['whs_id', 'back_route']);
        $portlet = new Portlet('StockPtl' . $data['id'], $data['title']); 
        $portlet->addTable($table);
        $this->addDatas('StorageOverview' . $data['id'], $portlet);

        return $portlet;
    }
}
