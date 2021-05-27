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
use App\Model\Dao\Job\Warehouse\JobInboundDamageDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to handle the ajax request fo JobInboundDamage.
 *
 * @package    app
 * @subpackage Model\Ajax\JobWarehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundDamage extends AbstractBaseAjaxModel
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
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        $result = [];
        if ($this->isValidParameter('jidm_id') === true) {
            $result = JobInboundDamageDao::getByReference($this->getIntParameter('jidm_id'));
            if (empty($result) === false) {
                $number = new NumberFormatter();
                $qty = (float)$result['jidm_jir_qty_damage'];
                if (empty($result['jidm_jir_damage_used']) === false) {
                    $qty -= (float)$result['jidm_jir_damage_used'];
                }

                $result['jidm_jir_qty_damage'] = $qty;
                $result['jidm_jir_qty_damage_number'] = $number->doFormatFloat($qty);
                $result['jidm_quantity_number'] = $number->doFormatFloat($result['jidm_quantity']);
                $result['jidm_length_number'] = $number->doFormatFloat($result['jidm_length']);
                $result['jidm_width_number'] = $number->doFormatFloat($result['jidm_width']);
                $result['jidm_height_number'] = $number->doFormatFloat($result['jidm_height']);
                $result['jidm_weight_number'] = $number->doFormatFloat($result['jidm_weight']);
            }
        }

        return $result;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jidm_id') === true) {

            $data = JobInboundDamageDao::getByReference($this->getIntParameter('jidm_id'));
            $gdDao = new GoodsDao();
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $qty = (float)$data['jidm_jir_qty_damage'];
                if (empty($data['jidm_jir_damage_used']) === false) {
                    $qty -= (float)$data['jidm_jir_damage_used'];
                }
                $result['jidm_jir_qty_damage'] = $qty;
                $data['jidm_gd_name'] = $gdDao->formatFullName($data['jidm_gdc_name'], $data['jidm_br_name'], $data['jidm_gd_name']);
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $result['jidm_jir_qty_damage_del_number'] = $number->doFormatFloat($result['jidm_jir_qty_damage_del']);
                $result['jidm_quantity_del_number'] = $number->doFormatFloat($result['jidm_quantity_del']);
                $result['jidm_length_del_number'] = $number->doFormatFloat($result['jidm_length_del']);
                $result['jidm_width_del_number'] = $number->doFormatFloat($result['jidm_width_del']);
                $result['jidm_height_del_number'] = $number->doFormatFloat($result['jidm_height_del']);
                $result['jidm_weight_del_number'] = $number->doFormatFloat($result['jidm_weight_del']);
            }

        }

        return $result;
    }
}
