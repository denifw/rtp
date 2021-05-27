<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Goods;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Goods\GoodsPrefixDao;

/**
 * Class to handle the ajax request fo GoodsUnit.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsPrefix extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        $results = [];
        if ($this->isValidParameter('gpf_id') === true) {
            $results = GoodsPrefixDao::getByReference($this->getIntParameter('gpf_id'));
            if (empty($results) === false) {
                $number = new NumberFormatter();
                $results['gpf_length_number'] = $number->doFormatFloat($results['gpf_length']);
            }
        }

        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $results = [];
        if ($this->isValidParameter('gpf_id') === true) {
            $data = GoodsPrefixDao::getByReference($this->getIntParameter('gpf_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $results[$key . '_del'] = $data[$key];
                }
                $results['gpf_length_del_number'] = $number->doFormatFloat($results['gpf_length_del']);
            }
        }

        return $results;
    }
}
