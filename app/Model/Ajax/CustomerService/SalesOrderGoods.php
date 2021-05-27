<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\CustomerService;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;

/**
 * Class to handle the ajax request fo SalesOrderGoods.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderGoods extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for SalesOrderGoods
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $results = [];
        if ($this->isValidParameter('sog_so_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('sog.sog_so_id', $this->getIntParameter('sog_so_id'));
//            $wheres[] = SqlHelper::generateOrLikeCondition([
//                'sog.sog_hs_code', 'sos.sos_name', 'sog.sog_packing_ref'
//            ], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');
            if ($this->isValidParameter('jik_id') === true) {
                $wheres[] = '(sog.sog_id NOT IN (SELECT jikr_sog_id
                                                FROM job_inklaring_release
                                                WHERE jikr_jik_id = ' . $this->getIntParameter('jik_id') . '
                                                    AND jikr_deleted_on IS NULL AND jikr_sog_id IS NOT NULL))';
            }
            if ($this->isValidParameter('sog_soc_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('sog.sog_soc_id', $this->getIntParameter('sog_soc_id'));
            }
            $data = SalesOrderGoodsDao::loadData($wheres);
            if (empty($data) === false) {
                $number = new NumberFormatter();
                foreach ($data as $row) {
                    $row['sog_quantity_number'] = $number->doFormatFloat($row['sog_quantity']);
                    $row['sog_gross_weight_number'] = $number->doFormatFloat($row['sog_gross_weight']);
                    $row['sog_net_weight_number'] = $number->doFormatFloat($row['sog_net_weight']);
                    $row['sog_cbm_number'] = $number->doFormatFloat($row['sog_cbm']);
                    $results[] = $row;
                }
            }
        }
        return $results;
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('sog_id') === true) {
            $data = SalesOrderGoodsDao::getByReference($this->getIntParameter('sog_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['sog_quantity_number'] = $number->doFormatFloat($data['sog_quantity']);
                $data['sog_length_number'] = $number->doFormatFloat($data['sog_length']);
                $data['sog_width_number'] = $number->doFormatFloat($data['sog_width']);
                $data['sog_height_number'] = $number->doFormatFloat($data['sog_height']);
                $data['sog_gross_weight_number'] = $number->doFormatFloat($data['sog_gross_weight']);
                $data['sog_net_weight_number'] = $number->doFormatFloat($data['sog_net_weight']);
                $data['sog_cbm_number'] = $number->doFormatFloat($data['sog_cbm']);
            }
            return $data;
        }
        return [];
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('sog_id') === true) {
            $data = SalesOrderGoodsDao::getByReference($this->getIntParameter('sog_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['sog_id'] = '';
                $data['sog_quantity_number'] = $number->doFormatFloat($data['sog_quantity']);
                $data['sog_length_number'] = $number->doFormatFloat($data['sog_length']);
                $data['sog_width_number'] = $number->doFormatFloat($data['sog_width']);
                $data['sog_height_number'] = $number->doFormatFloat($data['sog_height']);
                $data['sog_gross_weight_number'] = $number->doFormatFloat($data['sog_gross_weight']);
                $data['sog_net_weight_number'] = $number->doFormatFloat($data['sog_net_weight']);
                $data['sog_cbm_number'] = $number->doFormatFloat($data['sog_cbm']);
            }
        }

        return $data;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('sog_id') === true) {
            $data = SalesOrderGoodsDao::getByReference($this->getIntParameter('sog_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['sog_quantity_del_number'] = $number->doFormatFloat($data['sog_quantity']);
                $result['sog_length_del_number'] = $number->doFormatFloat($data['sog_length']);
                $result['sog_width_del_number'] = $number->doFormatFloat($data['sog_width']);
                $result['sog_height_del_number'] = $number->doFormatFloat($data['sog_height']);
                $result['sog_gross_weight_del_number'] = $number->doFormatFloat($data['sog_gross_weight']);
                $result['sog_net_weight_del_number'] = $number->doFormatFloat($data['sog_net_weight']);
                $result['sog_cbm_del_number'] = $number->doFormatFloat($data['sog_cbm']);
            }
        }

        return $result;
    }
}
