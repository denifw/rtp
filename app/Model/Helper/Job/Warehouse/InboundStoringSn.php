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
class InboundStoringSn
{
    /**
     * Property to store the Jid ID.
     *
     * @var int
     */
    public $JidId;
    /**
     * Property to store the Jir ID.
     *
     * @var int
     */
    public $JirId;
    /**
     * Property to store the Job inbound ID.
     *
     * @var int
     */
    public $JiId;
    /**
     * Property to store the warehouse ID.
     *
     * @var int
     */
    public $WhId;
    /**
     * Property to store the Job Goods ID.
     *
     * @var int
     */
    public $JogId;
    /**
     * Property to store the Goods ID.
     *
     * @var int
     */
    public $GdId;
    /**
     * Property to store the goods receive sn.
     *
     * @var string
     */
    public $GdOnReceiveSn;
    /**
     * Property to store the Serial Number.
     *
     * @var string
     */
    public $SnDivider;
    /**
     * Property to store the Lot Number.
     *
     * @var string
     */
    public $LotNumber;

    /**
     * Property to store the Expired Date.
     *
     * @var string
     */
    public $ExpiredDate;
    /**
     * Property to store the Packing Number.
     *
     * @var string
     */
    public $PackingNumber;

    /**
     * Property to store the Gdt Id.
     *
     * @var int
     */
    public $GdtId;
    /**
     * Property to store the Gcd Id.
     *
     * @var int
     */
    public $GcdId;

    /**
     * InboundReceiveSn constructor.
     */
    public function __construct()
    {
        $this->JidId = 0;
        $this->JirId = 0;
        $this->JiId = 0;
        $this->WhId = 0;
        $this->JogId = 0;
        $this->GdId = 0;
        $this->GdOnReceiveSn = 'N';
        $this->SnDivider = ',';
        $this->LotNumber = '';
        $this->ExpiredDate = '';
        $this->PackingNumber = '';
        $this->GdtId = 0;
        $this->GcdId = 0;
    }


}
