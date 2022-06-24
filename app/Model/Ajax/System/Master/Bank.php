<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\BankDao;

/**
 * Class to handle the ajax request fo Bank.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class Bank extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $helper = new SqlHelper();
        $helper->addOrLikeWhere(['bn_short_name', 'bn_name'], $this->getStringParameter('search_key'));
        $helper->addStringWhere('bn_active', 'Y');
        $helper->addNullWhere('bn_deleted_on');
        return BankDao::loadSingleSelectData('bn_name', $helper);
    }
}
