<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Warehouse;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\JobStockTransferGoodsDao;

/**
 * Class to handle the ajax request fo JobStockTransferGoods.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobStockTransferGoods extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getStockTransferGoodsById(): array
    {
        if ($this->isValidParameter('jtg_id') === true) {
            $wheres = [];
            $wheres[] = '(jtg.jtg_id = ' . $this->getIntParameter('jtg_id') . ')';
            $temp = JobStockTransferGoodsDao::loadDataForStockTransfer($wheres);
            if (\count($temp) === 1) {
                $result = $temp[0];
                $number = new NumberFormatter();
                $result['jtg_goods'] = $result['jtg_sku'] . ' | ' . $result['jtg_gd_name'];
                $result['jtg_quantity_number'] = $number->doFormatFloat($result['jtg_quantity']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getStockTransferGoodsByIdForDelete(): array
    {
        if ($this->isValidParameter('jtg_id') === true) {
            $wheres = [];
            $wheres[] = '(jtg.jtg_id = ' . $this->getIntParameter('jtg_id') . ')';
            $temp = JobStockTransferGoodsDao::loadDataForStockTransfer($wheres);
            if (\count($temp) === 1) {
                $result = [];
                $keys = array_keys($temp[0]);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[0][$key];
                }
                $number = new NumberFormatter();
                $result['jtg_quantity_del_number'] = $number->doFormatFloat($result['jtg_quantity_del']);

                return $result;
            }

            return [];
        }

        return [];
    }
}
