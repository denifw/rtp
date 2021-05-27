<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Warehouse;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to handle the ajax request fo StockOpnameDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockOpnameDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('', $this->getStringParameter('search_key'));

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT
                    FROM ' . $strWhere;
        $query .= ' ORDER BY ';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, '', '');
    }


    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('sod_id') === true) {
            $data = StockOpnameDetailDao::getByReference($this->getIntParameter('sod_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['sod_quantity_number'] = $number->doFormatFloat($data['sod_quantity']);
                $data['sod_jid_stock_number'] = $number->doFormatFloat($data['sod_jid_stock']);

                return $data;
            }

        }

        return [];
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getByReferenceForUpdate(): array
    {
        if ($this->isValidParameter('sod_id') === true) {
            $data = StockOpnameDetailDao::getByReference($this->getIntParameter('sod_id'));
            if (empty($data) === false) {
                $gdDao = new GoodsDao();
                $data['sod_goods'] = $gdDao->formatFullName($data['sod_gd_category'], $data['sod_gd_brand'], $data['sod_gd_name']);
                if(empty($data['sod_gdt_id']) === false) {
                    $data['sod_gdt_description'] = $data['sod_gdt_code'] . ' '. $data['sod_gdt_description'];
                }
                $keys = array_keys($data);
                $result = [];
                foreach ($keys as $key) {
                    $result[$key.'_upd'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['sod_quantity_upd_number'] = $number->doFormatFloat($result['sod_quantity_upd']);
                $result['sod_qty_figure_upd_number'] = $number->doFormatFloat($result['sod_qty_figure_upd']);

                return $result;
            }

        }

        return [];
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('sod_id') === true) {
            $data = StockOpnameDetailDao::getByReference($this->getIntParameter('sod_id'));
            if (empty($data) === false) {
                $gdDao = new GoodsDao();
                $data['sod_goods'] = $gdDao->formatFullName($data['sod_gd_category'], $data['sod_gd_brand'], $data['sod_gd_name']);
                if(empty($data['sod_gdt_id']) === false) {
                    $data['sod_gdt_description'] = $data['sod_gdt_code'] . ' '. $data['sod_gdt_description'];
                }
                $keys = array_keys($data);
                $result = [];
                foreach ($keys as $key) {
                    $result[$key.'_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['sod_quantity_del_number'] = $number->doFormatFloat($result['sod_quantity_del']);
                $result['sod_qty_figure_del_number'] = $number->doFormatFloat($result['sod_qty_figure_del']);

                return $result;
            }
        }

        return $result;
    }

}
