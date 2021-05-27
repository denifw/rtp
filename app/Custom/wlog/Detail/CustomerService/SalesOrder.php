<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\wlog\Detail\CustomerService;

use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\JobGoodsDao;

/**
 * Class to handle the creation of detail JobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrder extends \App\Model\Detail\CustomerService\SalesOrder
{
    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        parent::doUpdate();
        if (empty($this->getFormAction()) === true) {
            $data = SalesOrderDao::loadAllJobGoodsInbound($this->getDetailReferenceValue());
            if (empty($data) === false) {
                $lotNumber = $this->loadLotNumberJobInboundGoods();
                $jogDao = new JobGoodsDao();
                foreach ($data as $row) {
                    $jogColVal = [
                        'jog_production_number' => $lotNumber
                    ];
                    $jogDao->doUpdateTransaction($row['jog_id'], $jogColVal);
                }
            }
        }

    }


    /**
     * Function to load default lot number for job inbound goods .;
     *
     * @return ?string
     */
    protected function loadLotNumberJobInboundGoods(): ?string
    {
        $numbers = [];
        if ($this->isValidParameter('so_customer_ref') === true) {
            $numbers[] = $this->getStringParameter('so_customer_ref');
        }
        if ($this->isValidParameter('so_aju_ref') === true) {
            $numbers[] = $this->getStringParameter('so_aju_ref');
        }
        if (empty($numbers) === false) {
            return implode(' - ', $numbers);
        }
        return null;
    }
}
