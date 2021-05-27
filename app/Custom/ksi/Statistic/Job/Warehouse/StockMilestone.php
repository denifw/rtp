<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Custom\ksi\Statistic\Job\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;

/**
 * Custom stock milestone for KSI
 *
 * @package    app
 * @subpackage Custom\ksi\Statistic\Job\Warehouse
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class StockMilestone extends \App\Model\Statistic\Job\Warehouse\StockMilestone
{
    /**
     * Function to get the detail table.
     *
     * @param string $htmlId To store the title.
     *
     * @return Table
     */
    protected function getDetailTable($htmlId): Table
    {
        $table = parent::getDetailTable($htmlId);
        $table->addColumnAfter('shipper', 'shipper_pic', Trans::getWord('pic'));
        $table->renameColumn('quantity', Trans::getWord('qty'));
        $table->removeColumn('remark');

        return $table;
    }
}
