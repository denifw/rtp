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
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Goods\GoodsUnitDao;

/**
 * Class to handle the ajax request fo GoodsUnit.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsUnit extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('gdu_gd_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateOrLikeCondition(['uom.uom_code', 'uom.uom_name'], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNumericCondition('gdu.gdu_gd_id', $this->getIntParameter('gdu_gd_id'));
            if ($this->isValidParameter('gdu_uom_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('gdu.gdu_uom_id', $this->getIntParameter('gdu_uom_id'));
            }
            $wheres[] = SqlHelper::generateNullCondition('gdu.gdu_deleted_on');
            $data = GoodsUnitDao::loadData($wheres, 30);

            return $this->doPrepareSingleSelectData($data, 'gdu_full_uom', 'gdu_id');
        }

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
        if ($this->isValidParameter('gdu_id') === true) {
            $results = GoodsUnitDao::getByReference($this->getIntParameter('gdu_id'));
            if (empty($results) === false) {
                $number = new NumberFormatter();
                $results['gdu_qty_conversion_number'] = $number->doFormatFloat($results['gdu_qty_conversion']);
                $results['gdu_length_number'] = $number->doFormatFloat($results['gdu_length']);
                $results['gdu_width_number'] = $number->doFormatFloat($results['gdu_width']);
                $results['gdu_height_number'] = $number->doFormatFloat($results['gdu_height']);
                $results['gdu_weight_number'] = $number->doFormatFloat($results['gdu_weight']);
            }
        }

        return $results;
    }
}
