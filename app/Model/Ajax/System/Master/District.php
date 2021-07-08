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
use App\Model\Dao\System\Master\DistrictDao;

/**
 * Class to handle the ajax request fo District.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class District extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('dtc.dtc_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_active', 'Y');
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        }
        return DistrictDao::loadSingleSelectData('dtc_name', $wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectAutoComplete(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('dtc.dtc_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_active', 'Y');
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        }
        return DistrictDao::loadSingleSelectData('dtc_name', $wheres);

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
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('dtc.dtc_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_active', 'Y');
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        }
        return DistrictDao::loadSingleSelectData(['dtc_name', 'dtc_city', 'dtc_state', 'dtc_country'], $wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('dtc_state') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('stt.stt_name', $this->getStringParameter('dtc_state'));
        }
        if ($this->isValidParameter('dtc_city') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cty.cty_name', $this->getStringParameter('dtc_city'));
        }
        if ($this->isValidParameter('dtc_country') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cnt.cnt_name', $this->getStringParameter('dtc_country'));
        }
        if ($this->isValidParameter('dtc_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('dtc_name'));
        }
        return DistrictDao::loadData($wheres, [], 30);
    }

}
