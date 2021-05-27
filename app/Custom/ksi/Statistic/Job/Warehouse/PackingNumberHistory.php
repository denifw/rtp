<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Custom\ksi\Statistic\Job\Warehouse;

use App\Frame\Gui\Table;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class PackingNumberHistory extends \App\Model\Statistic\Job\Warehouse\PackingNumberHistory
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
        $table->removeColumn('total_volume');
        $table->removeColumn('remark');
        return $table;
    }

}
