<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Custom\mol\Statistic\Job\Warehouse;

use App\Frame\Gui\Table;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class SerialNumberHistory extends \App\Model\Statistic\Job\Warehouse\SerialNumberHistory
{
    /**
     * Function to get the stock card table.
     *
     * @param string $htmlId     To store the title.
     *
     * @return Table
     */
    protected function getTableView(string $htmlId): Table
    {
        $table = parent::getTableView($htmlId);
        $table->removeColumn('jid_lot_number');
        $table->removeColumn('jid_packing_number');
        $table->removeColumn('total_weight');
        return $table;
    }

}
