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
use App\Model\Dao\System\Master\CountryDao;

/**
 * Class to handle the ajax request fo Country.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Country extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateOrLikeCondition(['cnt_name', 'cnt_iso'], $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('cnt_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('cnt_active', 'Y');
        return CountryDao::loadSingleSelectData(['cnt_name', 'cnt_iso'], $wheres);
    }
}
