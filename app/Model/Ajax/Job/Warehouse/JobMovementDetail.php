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
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;

/**
 * Class to handle the ajax request fo JobMovementDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobMovementDetail extends AbstractBaseAjaxModel
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
        if ($this->isValidParameter('jmd_id') === true) {
            $data = JobMovementDetailDao::getByReference($this->getIntParameter('jmd_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $data['jmd_quantity_number'] = $number->doFormatFloat($data['jmd_quantity']);
                $data['jmd_jid_stock_number'] = $number->doFormatFloat($data['jmd_jid_stock']);
                $data['jmd_length_number'] = $number->doFormatFloat($data['jmd_length']);
                $data['jmd_height_number'] = $number->doFormatFloat($data['jmd_width']);
                $data['jmd_width_number'] = $number->doFormatFloat($data['jmd_height']);
                $data['jmd_weight_number'] = $number->doFormatFloat($data['jmd_weight']);

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
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jmd_id') === true) {
            $data = JobMovementDetailDao::getByReference($this->getIntParameter('jmd_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['jmd_quantity_del_number'] = $number->doFormatFloat($result['jmd_quantity_del']);
                $result['jmd_jid_stock_del_number'] = $number->doFormatFloat($result['jmd_jid_stock_del']);
            }

        }

        return $result;
    }

}
