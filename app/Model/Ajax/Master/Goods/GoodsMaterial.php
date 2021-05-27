<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Goods;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;

/**
 * Class to handle the ajax request fo GoodsMaterial.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class GoodsMaterial extends AbstractBaseAjaxModel
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
        # TODO Add additional wheres here.

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
        if ($this->isValidParameter('gm_id') === false) {
            return [];
        }
        $data = GoodsMaterialDao::getByReference($this->getIntParameter('gm_id'));
        if (empty($data) === false) {
            $number = new NumberFormatter();
            $data['gm_quantity_number'] = $number->doFormatFloat($data['gm_quantity']);
        }
        return $data;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        if ($this->isValidParameter('gm_id') === false) {
            return [];
        }
        $data = GoodsMaterialDao::getByReference($this->getIntParameter('gm_id'));
        $result = [];
        if (empty($data) === false) {
            $number = new NumberFormatter();
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_del'] = $data[$key];
            }
            $result['gm_quantity_del_number'] = $number->doFormatFloat($result['gm_quantity_del']);
        }
        return $result;
    }
}
