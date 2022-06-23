<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\PaymentMethodDao;

/**
 * Class to handle the ajax request fo Bank.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PaymentMethod extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('pm_ss_id')) {
            $helper = new SqlHelper();
            $helper->addStringWhere('pm_ss_id', $this->getStringParameter('pm_ss_id'));
            $helper->addLikeWhere('pm_name', $this->getStringParameter('search_key'));
            $helper->addStringWhere('pm_active', 'Y');
            $helper->addNullWhere('pm_deleted_on');
            return PaymentMethodDao::loadSingleSelectData('pm_name', $helper);
        }
        return [];
    }
}
