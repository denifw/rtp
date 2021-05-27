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
class SerialNumberHistory extends \App\Model\Statistic\Job\Warehouse\SerialNumberHistory
{
    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        parent::loadSearchForm();
        $this->StatisticForm->removeField('gd_warranty');
        $packingNumberField = $this->Field->getText('jid_packing_number', $this->getStringParameter('jid_packing_number'));
        $this->StatisticForm->addFieldAfter('gd_name', Trans::getWord('packingNumber'), $packingNumberField);
    }

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
        $table->removeColumn('gd_sku');
        $table->removeColumn('gd_warranty');
        $table->removeColumn('total_volume');
        $table->removeColumn('remark');
        $table->addColumnAfter('customer', 'gd_sku', Trans::getWord('sku'));
        $table->addColumnAttribute('gd_sku', 'style', 'text-align: center');
        return $table;
    }

}
