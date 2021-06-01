<?php

namespace App\Http\Controllers\Auth;


use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use App\Frame\System\Validation;
use App\Http\Controllers\AbstractBaseAuthController;
use App\Model\Dao\User\UserMappingDao;
use App\Model\Dao\User\UsersDao;
use App\Model\Dao\User\UserTokenDao;

class LoginController extends AbstractBaseAuthController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    /**
     * The default function of class Home that will called when we don't specify the function name.
     *
     * @return mixed
     */
    public function index()
    {
        if ($this->isLogin() === true) {
            return redirect('/');
        }
        $errors = [];
        if (request('m') !== null) {
            $messageCode = (int)request('m');
            if ($messageCode === 1) {
                $errors[] = Trans::getWord('destroy_access', 'message');
            }
            if ($messageCode === 2) {
                $errors[] = Trans::getWord('expired_access', 'message');
            }
        }

        return view('auth.login')->withErrors($errors);
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function doLogin()
    {
        $validator = new Validation();
        $validator->setInputs(request()->all());
        $validator->checkRequire('us_username', 2);
        $validator->checkRequire('us_password', 2);
        $validator->doValidation();
        if ($validator->isValidInputs() === false) {
            $error = $validator->getErrorMessage('us_username', Trans::getWord('username'));
            if (empty($error) === true) {
                $error = $validator->getErrorMessage('us_password', Trans::getWord('password'));
            }

            return view('auth.login')->withErrors([$error])->with('us_username', request('us_username'));
        }
        try {
            $userDao = new UsersDao();
            $user = $userDao->getLoginData(request('us_username'), request('us_password'));
            if (empty($user) === true) {
                Message::throwMessage(Trans::getMessageWord('invalidUsernamePassword'));
            }
            if ($user['us_system'] === 'N') {
                $userSetting = UserMappingDao::loadUserMappingData($user['us_id']);
            } else {
                $userSetting = UserMappingDao::loadSystemMappingData();
            }
            if (empty($userSetting) === true) {
                Message::throwMessage(Trans::getMessageWord('invalidUsernamePassword'));
            }
            $user = array_merge($user, $userSetting);
            $this->setSession($user);

            return redirect('/');
        } catch (\Exception $e) {
            return view('auth.login')
                ->withErrors([$e->getMessage()])
                ->with('us_username', request('us_username'))
                ->with('us_password', request('us_password'));
        }

    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function doSwitch()
    {
        $validator = new Validation();
        $validator->setInputs(request()->all());
        $validator->checkRequire('ss_id');
        $validator->checkInt('ss_id');
        $validator->doValidation();
        if ($validator->isValidInputs() === false) {
            return view('errors.general', ['error_message' => Trans::getWord('pageNotFound', 'message'), 'back_url' => url('/')]);
        }

        try {
            $userSession = new UserSession();
            $user = UsersDao::getByReference($userSession->getId());
            if (empty($user) === true) {
                Message::throwMessage(Trans::getMessageWord('invalidUsernamePassword'));
            }
            if ($user['us_system'] === 'N') {
                $userSetting = UserMappingDao::loadUserMappingData($user['us_id'], request('ss_id'));
            } else {
                $userSetting = UserMappingDao::loadSystemMappingData(request('ss_id'));
            }
            if (empty($userSetting) === true) {
                Message::throwMessage(Trans::getWord('pageNotFound', 'message'));
            }
            $user = array_merge($user, $userSetting);
            $this->removeSession();
            $this->setSession($user);

            return redirect('/');

        } catch (\Exception $e) {
            return view('errors.general', ['error_message' => Trans::getWord('pageNotFound', 'message'), 'back_url' => url('/')]);
        }


    }

    /**
     * The function to process the logout.
     *
     * @return mixed
     */
    public function doLogout()
    {
        $user = new UserSession();
        if ($user->isSet()) {
            $userTokenDao = new UserTokenDao();
            $userToken = $userTokenDao->getUserTokenByType($user->getId(), $user->getSsId(), 'LOGIN');
            if (empty($userToken) === false) {
                $userTokenDao->doDeleteTransaction($userToken['ut_id']);
            }
            $this->removeSession();
        }

        return redirect('/login');
    }

    /**
     * The function to check session timeout.
     *
     * @return mixed
     */
    public function checkSession()
    {
        $valid = false;
        $user = new UserSession();
        if ($user->isSet()) {
            $utDao = new UserTokenDao();
            $access = $utDao->getUserToken($user->getId(), $user->getAuthToken());
            if (empty($access) === false && empty($utDao['ut_deleted_on']) === true) {
                $valid = true;
            }
        }

        return response()->json($valid);
    }

    /**
     * The function to process the logout.
     *
     * @return mixed
     */
    public function changePasswordForm()
    {
        return view('auth.changePassword');
    }

    /**
     * The function to process the logout.
     *
     * @return mixed
     */
//    public function doChangePassword() {
//        $messages = [
//            'same'     => 'The confirm password and new password must match.',
//            'required' => 'The :attribute is required.'
//        ];
//        $validator = Validator::make(request()->all(), [
//            'us_password'        => 'required',
//            'us_new_password'    => 'required',
//            'us_re_new_password' => 'required|same:us_new_password',
//        ], $messages);
//
//        if ($validator->fails()) {
//            return redirect('/changePassword')->withErrors($validator)->withInput();
//        }
//        $userDao = new UserDao();
//        $us = \session('user');
//
//        $user = $userDao->getLoginData($us['us_email'], request('us_password'));
//        if (empty($user) === true) {
//            return redirect('/changePassword')->withErrors(['Invalid current password.']);
//        }
//        $userDao->doUpdateTransaction($user['us_id'], [
//            'us_password' => bcrypt(request('us_new_password'))
//        ]);
//        $userTokenDao = new UserTokenDao();
//        $userToken = $userTokenDao->getUserTokenByType($us['us_id'], 'LOGIN');
//        if (empty($userToken) === false) {
//            $userTokenDao->doDeleteTransaction($userToken['ut_id']);
//        }
//        session()->flush();
//        session()->regenerate();
//
//        return redirect('/login');
//    }
}
