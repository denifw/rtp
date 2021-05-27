<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\CustomerService;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use function count;

/**
 * Class to handle data access object for table sales_order.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrderHoldDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'soh_id',
        'soh_so_id',
        'soh_reason',
    ];

    /**
     * Base dao constructor for sales_order.
     *
     */
    public function __construct()
    {
        parent::__construct('sales_order_hold', 'soh', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table sales_order.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'soh_reason',
            ]);
    }


    /**
     * function to get all available fields
     *
     * @return array
     */
    public static function getFields(): array
    {
        return self::$Fields;
    }
}
