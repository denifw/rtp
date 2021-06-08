<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Mvc\AbstractBaseDao;

/**
 * Class to handle data access object for table serial_history.
 *
 * @package    app
 * @subpackage Model\Dao\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialHistoryDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'sh_id',
        'sh_sn_id',
        'sh_year',
        'sh_month',
        'sh_number',
    ];

    /**
     * Property to store the numeric fields.
     *
     * @var array
     */
    protected $NumericFields = [
        'sh_number',
    ];

    /**
     * Base dao constructor for serial_history.
     *
     */
    public function __construct()
    {
        parent::__construct('serial_history', 'sh', self::$Fields);
    }
}
