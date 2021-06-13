<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Http\Controllers\Auth;

use App\Frame\Formatter\Trans;
use App\Frame\System\Validation;
use App\Http\Controllers\AbstractBaseAuthController;
use App\Model\Dao\System\Access\UserMappingDao;
use App\Model\Dao\System\Access\UsersDao;
use App\Model\Dao\System\Access\UserTokenDao;

/**
 *
 *
 * @package    app
 * @subpackage Http\Controllers\Auth
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class EmailConfirmationController extends AbstractBaseAuthController
{
    /**
     * The default function of class EmailConfirmationController that will called when we don't specify the function name.
     *
     * @return mixed
     */
    public function index()
    {
        $token = request('token');
        if (empty($token) === true) {
            return view('errors.404');
        }
        $userTokenDao = new UserTokenDao();
        $userToken = $userTokenDao->getByToken($token);
        if (empty($userToken) === true) {
            return view('errors.404');
        }

        return view('auth.mail_confirmation')->with('token', $token);
    }

    /**
     * The function to create the password.
     *
     * @return mixed
     */
    public function doConfirm()
    {
        $validator = new Validation();
        $validator->setInputs(request()->all());
        $validator->checkRequire('token');
        $validator->checkRequire('us_password', 5);
        $validator->checkConfirmed('us_password');
        $validator->checkRequire('us_password_confirmation', 5);
        $validator->doValidation();
        if ($validator->isValidInputs() === false) {
            $error = $validator->getErrorMessage('us_password', Trans::getWord('password'));
            if (empty($error) === true) {
                $error = $validator->getErrorMessage('us_password_confirmation', Trans::getWord('passwordConfirmation'));
            }

            return view('auth.mail_confirmation')->with('token', request('token'))->withErrors([$error]);
        }
        $userTokenDao = new UserTokenDao();
        $userToken = $userTokenDao->getByToken(request('token'));
        if (empty($userToken) === true) {
            return view('errors.404');
        }

        $colVal = [
            'us_password' => bcrypt(request('us_password')),
            'us_confirm' => 'Y'
        ];
        $usDao = new UsersDao();
        $usDao->doUpdateTransaction($userToken['ut_us_id'], $colVal);

        $ump = UserMappingDao::getUnconfirmUserMapping($userToken['ut_us_id'], $userToken['ut_ss_id']);
        $umpDao = new UserMappingDao();
        $umpColVal = [
            'ump_confirm' => 'Y'
        ];
        $umpDao->doUpdateTransaction($ump['ump_id'], $umpColVal);
        $userTokenDao->doDeleteTransaction($userToken['ut_id']);

        return view('auth.message')->with([
            'message' => Trans::getWord('successEmailConfirmation', 'message')
        ]);
    }

    /**
     * The function to create the password.
     *
     * @return mixed
     */
    public function mappingUser()
    {
        $token = request('token');
        if (empty($token) === true) {
            return view('errors.404');
        }
        $userTokenDao = new UserTokenDao();
        $userToken = $userTokenDao->getByToken($token);
        if (empty($userToken) === true) {
            return view('errors.404');
        }
        $wheres = [];
        $wheres[] = '(ump.ump_us_id = ' . $userToken['ut_us_id'] . ')';
        $wheres[] = '(ump.ump_ss_id = ' . $userToken['ut_ss_id'] . ')';
        $wheres[] = "(ump.ump_confirm = 'N')";

        $ump = UserMappingDao::loadData($wheres);
        if (empty($ump) === true || count($ump) !== 1) {
            return view('errors.404');
        }
        $userTokenDao->doDeleteTransaction($userToken['ut_id']);
        $umpDao = new UserMappingDao();
        $umpDao->doUpdateTransaction($ump[0]['ump_id'], [
            'ump_confirm' => 'Y'
        ]);

        return view('auth.message')->with([
            'message' => Trans::getWord('successEmailConfirmation', 'message')
        ]);
    }
}
