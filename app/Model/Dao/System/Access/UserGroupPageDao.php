<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Mvc\AbstractBaseDao;

/**
 * Class to handle data access object for table user_group_page.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupPageDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugp_id',
        'ugp_usg_id',
        'ugp_pg_id',
    ];

    /**
     * Base dao constructor for user_group_page.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_page', 'ugp', self::$Fields);
    }
}
