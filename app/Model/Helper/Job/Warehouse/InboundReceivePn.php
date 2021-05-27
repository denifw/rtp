<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Helper\Job\Warehouse;

/**
 * Class to collect all inbound receive serial number data.
 *
 * @package    app
 * @subpackage Model\Helper\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class InboundReceivePn
{
    /**
     * Property to store Ss Id
     *
     * @var int
     */
    public $SsId;
    /**
     * Property to store the Jir ID.
     *
     * @var int
     */
    public $JirId;
    /**
     * Property to store the Warehouse ID.
     *
     * @var int
     */
    public $WhId;
    /**
     * Property to store the Goods ID.
     *
     * @var int
     */
    public $GdId;
    /**
     * Property to store the Serial Number.
     *
     * @var string
     */
    public $LotNumber;

    /**
     * InboundReceiveSn constructor.
     */
    public function __construct()
    {
        $this->SsId = 0;
        $this->JirId = 0;
        $this->WhId = 0;
        $this->GdId = 0;
        $this->LotNumber = '';
    }


}
