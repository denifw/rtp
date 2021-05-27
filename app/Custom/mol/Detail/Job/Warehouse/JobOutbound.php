<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\mol\Detail\Job\Warehouse;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Custom\wlog\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends \App\Model\Detail\Job\Warehouse\JobOutbound
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('jo_customer_ref');
            $this->Validation->checkUnique('jo_customer_ref', 'job_order', [
                'jo_id' => $this->getDetailReferenceValue()
            ], [
                'jo_deleted_on' => null
            ]);
        } else if ($this->getFormAction() === 'doUpdateGoodsOutbound') {
            $this->Validation->checkUnique('jog_gd_id', 'job_goods', [
                'jog_id' => $this->getIntParameter('jog_id')
            ], [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_production_number' => $this->getStringParameter('jog_production_number'),
                'jog_deleted_on' => null
            ]);
        }
        parent::loadValidationRole();
    }
}
