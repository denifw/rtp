<?php
/**
 * Contains code written by the TIG Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Lokasi
 * @author    Deni Firdaus Waruwu <deni@lokasi.co.id>
 * @copyright 2017 lokasi.co.id
 */

namespace App\Http\Controllers;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\System\SystemSettings;
use App\Model\Dao\User\UserMappingDao;
use App\Model\Dao\User\UserTokenDao;

/**
 * Class to control the login of AbstractBaseAuth.
 *
 * @package    App
 * @subpackage Http\Controllers\Auth
 * @author     Deni Firdaus Waruwu <deni@lokasi.co.id>
 * @copyright  2017 lokasi.co.id
 */
abstract class AbstractBaseAuthController extends Controller
{

    /**
     * Function to check is the user already login or not.
     *
     * @return boolean
     */
    protected function isLogin(): bool
    {
        return (session('user') !== null);
    }

    /**
     * Function to generate the user token.
     *
     * @param array  $user      To set the user id.
     * @param string $tokenType To set the type of the token.
     * @param bool   $remember  To set the remember of the user.
     *
     * @return string
     */
    protected function generateToken($user, $tokenType, $remember = false): string
    {

        $userTokenDao = new UserTokenDao();
        $userToken = $userTokenDao->getUserTokenByType($user['us_id'], $user['ss_id'], $tokenType);
        if (empty($userToken) === false) {
            $userTokenDao->doDeleteTransaction($userToken['ut_id']);
        }
        $token = $userTokenDao->generateTokenByUserAndSystem($user['us_id'], $user['ss_id'], $tokenType);
        $colVal = [
            'ut_us_id' => $user['us_id'],
            'ut_ss_id' => $user['ss_id'],
            'ut_token' => $token,
            'ut_type' => $tokenType,
            'ut_expired_on' => $userTokenDao->getExpiredDate($tokenType, $remember)
        ];
        $userTokenDao->doInsertTransaction($colVal);

        return $token;

    }

    /**
     * Function to generate the user token.
     *
     * @param array $user To set the user id.
     *
     * @return void
     */
    protected function setSession(array $user): void
    {
        # Set remember user.
        if (empty($user) === false) {
            if ($user['us_system'] === 'N') {
                $user['systems'] = UserMappingDao::loadAllUserMappingData($user['us_id'], $user['ss_id']);
            } else {
                $user['systems'] = UserMappingDao::loadAllUserMappingDataForSystem($user['ss_id']);
            }

            $remember = false;
//            if (empty(request('remember')) === false) {
//                $remember = true;
//            }
            # Generate the token
            $token = $this->generateToken($user, 'LOGIN', $remember);
            $user['ut_token'] = $token;
            $user['ut_type'] = 'LOGIN';
            $user['remember'] = $remember;
            $settings = new SystemSettings();
            $settings->registerSystemSetting($user);
        } else {
            Message::throwMessage(Trans::getWord('failed', 'message'));
        }
    }

    /**
     * Function to generate the user token.
     *
     * @return void
     */
    protected function removeSession(): void
    {
        session()->flush();
        session()->regenerate();
    }
}
