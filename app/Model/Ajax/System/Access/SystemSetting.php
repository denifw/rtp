<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Access\SystemSettingDao;

/**
 * Class to handle the ajax request fo SystemSetting.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemSetting extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ss_relation', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateStringCondition('ss_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ss_deleted_on');
        return SystemSettingDao::loadSingleSelectData('ss_relation', $wheres);
    }

}
