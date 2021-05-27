<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Deni Firdaus Waruwu<deni.fw@mbteknologi.com>
 * @copyright 2019 mbteknologi.com
 */

namespace App\Custom\mol\Statistic\Job\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Deni Firdaus Waruwu<deni.fw@mbteknologi.com>
 * @copyright  2019 mbteknologi.com
 */
class StockCard extends \App\Model\Statistic\Job\Warehouse\StockCard
{
    /**
     * Function to get the stock card table.
     *
     * @return Table
     */
    protected function getStockCardTable(): Table
    {
        $table = parent::getStockCardTable();
        $table->removeColumn('gd_full_name');
        $table->addColumnAfter('gd_sku', 'gdc_name', Trans::getWord('category'));
        if ($this->getFormAction() === 'doExportXls') {
            $table->addColumnAfter('gd_sku', 'br_name', Trans::getWord('brand'));
            $table->addColumnAfter('gdc_name', 'gd_name', Trans::getWord('goods'));
        }
        return $table;
    }
}
