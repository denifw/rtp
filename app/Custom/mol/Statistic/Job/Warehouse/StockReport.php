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
class StockReport extends \App\Model\Statistic\Job\Warehouse\StockReport
{

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultTable(): Table
    {
        $table = parent::getResultTable();
        if ($this->getFormAction() === 'doExportXls') {
            $table->removeColumn('total_wg_origin');
            $table->removeColumn('total_wg_in');
            $table->removeColumn('total_wg_out');
            $table->removeColumn('wg_mv_good');
            $table->removeColumn('wg_mv_damage');
            $table->removeColumn('adj_good');
            $table->removeColumn('adj_damage');
            $table->removeColumn('total_adj');
            $table->removeColumn('total_wg_adj');
            $table->removeColumn('total_vl_adj');
            $table->removeColumn('total_wg_last');
        } else {
            $table->removeColumn('gd_brand');
            $table->removeColumn('gd_name');
        }
        return $table;
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultGoodTable(): Table
    {
        $table = parent::getResultGoodTable();
        $table->removeColumn('adj_good');
        $table->renameColumn('mv_good', 'MOV NG3 to CK3');
        $table->renameColumn('mv_damage', 'MOV CK3 to NG3');
        if ($this->getFormAction() === 'doExportXls') {
            $table->renameColumn('vl_mv_good', 'MOV NG3 to CK3 Volume (M3)');
            $table->renameColumn('vl_mv_damage', 'MOV CK3 to NG3 Volume (M3)');
            $table->removeColumn('wg_good_origin');
            $table->removeColumn('wg_in_good');
            $table->removeColumn('wg_out_good');
            $table->removeColumn('wg_mv_good');
            $table->removeColumn('wg_mv_damage');
            $table->removeColumn('wg_adj_good');
            $table->removeColumn('vl_adj_good');
            $table->removeColumn('wg_good_last');
        } else {
            $table->removeColumn('gd_brand');
            $table->removeColumn('gd_name');
        }
        return $table;
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultDamageTable(): Table
    {
        $table = parent::getResultDamageTable();
        $table->removeColumn('adj_damage');
        $table->renameColumn('mv_good', 'MOV NG3 to CK3');
        $table->renameColumn('mv_damage', 'MOV CK3 to NG3');
        if ($this->getFormAction() === 'doExportXls') {
            $table->renameColumn('vl_mv_good', 'MOV NG3 to CK3 Volume (M3)');
            $table->renameColumn('vl_mv_damage', 'MOV CK3 to NG3 Volume (M3)');

            $table->removeColumn('wg_damage_origin');
            $table->removeColumn('wg_in_damage');
            $table->removeColumn('wg_out_damage');
            $table->removeColumn('wg_mv_good');
            $table->removeColumn('wg_mv_damage');
            $table->removeColumn('wg_adj_damage');
            $table->removeColumn('vl_adj_damage');
            $table->removeColumn('wg_damage_last');
        } else {
            $table->removeColumn('gd_brand');
            $table->removeColumn('gd_name');
        }
        return $table;
    }

}
