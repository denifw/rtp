<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Custom\wlog\Viewer\Job\Inklaring;

use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\JobGoodsDao;

/**
 *
 *
 * @package    app
 * @subpackage Custom
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobInklaring extends \App\Model\Viewer\Job\Inklaring\JobInklaring
{
    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doCompleteDrafting') {
            $data = SalesOrderDao::loadAllJobGoodsInbound($this->getIntParameter('jik_so_id'));
            if (empty($data) === false) {
                $numbers = [];
                if ($this->isValidParameter('so_customer_ref') === true) {
                    $numbers[] = $this->getStringParameter('so_customer_ref');
                }
                if ($this->isValidParameter('so_aju_ref') === true) {
                    $numbers[] = $this->getStringParameter('so_aju_ref');
                }
                $lotNumber = null;
                if (empty($numbers) === false) {
                    $lotNumber = implode(' - ', $numbers);
                }
                $jogDao = new JobGoodsDao();
                foreach ($data as $row) {
                    $jogColVal = [
                        'jog_production_number' => $lotNumber
                    ];
                    $jogDao->doUpdateTransaction($row['jog_id'], $jogColVal);
                }
            }
        }
        parent::doUpdate();
    }
}
