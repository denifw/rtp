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
 * Class to handle data access object for table user_group_api_access.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserGroupApiAccessDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'uga_id',
        'uga_usg_id',
        'uga_aa_id'
    ];

    /**
     * Base dao constructor for user_group_api_access.
     *
     */
    public function __construct()
    {
        parent::__construct('user_group_api_access', 'uga', self::$Fields);
    }

}
