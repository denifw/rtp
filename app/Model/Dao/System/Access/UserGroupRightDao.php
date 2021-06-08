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
 * Class to handle data access object for table user_group_right.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserGroupRightDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ugr_id',
        'ugr_usg_id',
        'ugr_pr_id',
    ];

    /**
     * Base dao constructor for user_group_right.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_right', 'ugr', self::$Fields);
    }
}
