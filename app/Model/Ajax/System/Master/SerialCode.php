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
use App\Model\Dao\System\Master\SerialCodeDao;

/**
 * Class to handle the ajax request fo SerialCode.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialCode extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateLikeCondition('sc_description', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('sc_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('sc_active', 'Y');
        return SerialCodeDao::loadSingleSelectData('sc_description', $wheres);
    }

}
