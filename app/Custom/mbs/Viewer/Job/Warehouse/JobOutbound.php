<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\mbs\Viewer\Job\Warehouse;

use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Custom\wlog\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends \App\Model\Viewer\Job\Warehouse\JobOutbound
{
    
    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        parent::loadValidationRole();
        if ($this->getFormAction() === 'doActionArrive') {
            $this->Validation->checkRequire('job_truck_number');
        }
    }

}
