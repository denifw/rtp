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
use App\Model\Dao\Job\Warehouse\JobAdjustmentDetailDao;

/**
 * Class to handle the ajax request fo JobAdjustmentDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobAdjustmentDetail extends AbstractBaseAjaxModel
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
        if ($this->isValidParameter('jad_id') === true) {
            $result = JobAdjustmentDetailDao::getByReference($this->getIntParameter('jad_id'));
            $number = new NumberFormatter();
            $result['jad_quantity_number'] = $number->doFormatFloat($result['jad_quantity']);
            $result['jad_jid_quantity_number'] = $number->doFormatFloat($result['jad_jid_quantity']);
            $result['jad_jid_stock_number'] = $number->doFormatFloat($result['jad_jid_stock']);
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
        if ($this->isValidParameter('jad_id') === true) {
            $data = JobAdjustmentDetailDao::getByReference($this->getIntParameter('jad_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_del'] = $data[$key];
            }
            $number = new NumberFormatter();
            $result['jad_quantity_del_number'] = $number->doFormatFloat($result['jad_quantity_del']);
            $result['jad_jid_quantity_del_number'] = $number->doFormatFloat($result['jad_jid_quantity_del']);
            $result['jad_jid_stock_del_number'] = $number->doFormatFloat($result['jad_jid_stock_del']);
        }

        return $result;
    }
}
