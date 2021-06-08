<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\CityDao;

/**
 * Class to handle the ajax request fo City.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class City extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cty.cty_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('cty.cty_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('cty.cty_active', 'Y');
        if ($this->isValidParameter('cty_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_cnt_id', $this->getStringParameter('cty_cnt_id'));
        }
        if ($this->isValidParameter('cty_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_stt_id', $this->getStringParameter('cty_stt_id'));
        }
        return CityDao::loadSingleSelectData('cty_name', $wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadCompleteSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cty.cty_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('cty.cty_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('cty.cty_active', 'Y');
        if ($this->isValidParameter('cty_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_cnt_id', $this->getStringParameter('cty_cnt_id'));
        }
        if ($this->isValidParameter('cty_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_stt_id', $this->getStringParameter('cty_stt_id'));
        }
        return CityDao::loadSingleSelectData(['cty_name', 'stt_name', 'cnt_name'], $wheres);
    }
}
