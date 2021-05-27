<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\mol\Listing\Master\Goods;

use App\Frame\Formatter\Trans;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Custom\wlog\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Goods extends \App\Model\Listing\Master\Goods\Goods
{
    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        parent::loadSearchForm();
        $this->ListingForm->removeField('gd_bundling');
        $this->ListingForm->addFieldAfter('gd_sn', Trans::getWord('multiSn'), $this->Field->getYesNo('gd_multi_sn', $this->getStringParameter('gd_multi_sn')));
    }
    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        parent::loadResultTable();
        # set header column table
        $this->ListingTable->removeColumn('br_name');
        $this->ListingTable->removeColumn('gd_bundling');
        $this->ListingTable->addColumnAfter('gd_uom_name', 'gd_height', Trans::getWord('height'). ' (M)');
        $this->ListingTable->addColumnAfter('gd_uom_name', 'gd_width', Trans::getWord('width'). ' (M)');
        $this->ListingTable->addColumnAfter('gd_uom_name', 'gd_length', Trans::getWord('length'). ' (M)');
        $this->ListingTable->addColumnAfter('gdu_sn', 'gd_multi_sn', Trans::getWord('multiSn'));
        $this->ListingTable->setColumnType('gd_height', 'float');
        $this->ListingTable->setColumnType('gd_width', 'float');
        $this->ListingTable->setColumnType('gd_length', 'float');
        $this->ListingTable->setColumnType('gd_multi_sn', 'yesno');
    }
}
