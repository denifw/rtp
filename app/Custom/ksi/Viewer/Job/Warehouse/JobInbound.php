<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\ksi\Viewer\Job\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Custom\wlog\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInbound extends \App\Model\Viewer\Job\Warehouse\JobInbound
{

    /**
     * Function to get operator modal.
     *
     * @param bool $showModal To trigger modal.
     *
     * @return FieldSet
     */
    protected function getGoodsReceiveFieldSet(bool $showModal): FieldSet
    {
        $fieldSet = parent::getGoodsReceiveFieldSet($showModal);
        $lotNumberField = $this->Field->getText('jir_lot_number', $this->getStringParameter('ji_ata_date'));
        $lotNumberField->setReadOnly();
        $fieldSet->removeField('jir_lot_number');
        $fieldSet->addFieldAfter('jir_quantity', Trans::getWord('lotNumber'), $lotNumberField);
        return $fieldSet;
    }
    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doArriveTruck') {
            $this->Validation->checkRequire('ji_vendor_id');
            $this->Validation->checkRequire('ji_driver', 1, 255);
            $this->loadActionValidationRole();
        } else {
            parent::loadValidationRole();
        }
    }
}
