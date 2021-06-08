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
use App\Model\Dao\System\Master\StateDao;

/**
 * Class to handle the ajax request fo State.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class State extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateLikeCondition('stt.stt_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('stt.stt_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('stt.stt_active', 'Y');
        if ($this->isValidParameter('stt_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('stt.stt_cnt_id', $this->getStringParameter('stt_cnt_id'));
        }
        return StateDao::loadSingleSelectData('stt_name', $wheres);
    }
}
