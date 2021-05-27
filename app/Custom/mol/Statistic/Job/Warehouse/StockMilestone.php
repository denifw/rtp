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

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
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

        if ($this->getFormAction() === 'doExportXls') {
            $table->removeColumn('jo_reference');
            $table->addColumnAfter('jid_lot_number', 'customer_ref', Trans::getWord('customerRef'));
            $table->addColumnAfter('customer_ref', 'packing_ref', Trans::getWord('packingRef'));
            if ($this->getIntParameter('jo_srt_id', 0) === 1) {
                $table->addColumnAfter('shipper_city', 'truck_number', Trans::getWord('truckPlate'));
                $table->addColumnAfter('truck_number', 'container_number', Trans::getWord('containerNumber'));
                $table->addColumnAfter('container_number', 'seal_number', Trans::getWord('sealNumber'));
            }
            if ($this->getIntParameter('jo_srt_id', 0) === 2) {
                $table->addColumnAfter('shipper_city', 'truck_number', Trans::getWord('truckPlate'));
            }
        }
        $table->removeColumn('jid_lot_number');
        $table->removeColumn('total_weight');

        return $table;
    }

}
