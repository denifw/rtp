<?php

namespace App\Http\Controllers\Auth;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\System\Validation;
use App\Http\Controllers\AbstractBaseAuthController;
use App\Model\Dao\System\Access\UsersDao;
use App\Model\Dao\System\Access\UserTokenDao;
use App\Model\Mail\PasswordReset;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends AbstractBaseAuthController
{
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

        return view('auth.forget');
    }

    /**
     * The function to verify the email and sending the email verification.
     *
     * @return mixed
     */
    public function doVerify()
    {
        $validator = new Validation();
        $validator->setInputs(request()->all());
        $validator->checkRequire('us_username', 3);
        $validator->checkEmail('us_username');
        $validator->doValidation();
        if ($validator->isValidInputs() === false) {
            return view('auth.forget')->withErrors([$validator->getErrorMessage('us_username', Trans::getWord('username'))])->with('us_username', request('us_username'));
        }
        $user = UsersDao::getByUsername(request('us_username'));
        if (empty($user) === false) {
            # Generate the token.
            $userTokenDao = new UserTokenDao();
            $userToken = $userTokenDao->getUserTokenByType($user['us_id'], '', 'FORGET_PASSWORD');
            if (empty($userToken) === false) {
                $dt = new DateTimeParser();
                return view('auth.forget')->withErrors([Trans::getWord('throttleForgotPassword', 'auth', '', ['time' => $dt->formatDateTime($userToken['ut_expired_on'])])]);
            }
            $token = $userTokenDao->generateTokenByUser($user['us_id'], 'FORGET_PASSWORD');
            $userToken = [
                'ut_us_id' => $user['us_id'],
                'ut_token' => $token,
                'ut_type' => 'FORGET_PASSWORD',
                'ut_expired_on' => $userTokenDao->getExpiredDate('FORGET_PASSWORD')
            ];
            $userTokenDao->doInsertTransaction($userToken);
            # Send the email
            $us = [];
            $us['name'] = $user['us_name'];
            $us['expired_date'] = $userToken['ut_expired_on'];
            Mail::to($user['us_username'])->send(new PasswordReset($us, $token));
            # Check if the email fail.
            $fails = Mail::failures();
            if (empty($fails) === false) {
                return view('auth.forget')->withErrors([Trans::getWord('unableToSentEmail', 'message', '', ['email' => $user['us_username']])]);
            }

            return view('auth.message')->with([
                'message' => Trans::getWord('successEmailReset', 'message', '', ['email' => $user['us_username']])]);
        }

        return view('auth.forget')->withErrors([Trans::getWord('invalidCredential', 'message')]);
    }

    /**
     * The function to show the reset form.
     *
     * @return mixed
     */
    public function reset()
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

        return view('auth.password_reset')->with('token', $token);
    }

    /**
     * The function to reset the password.
     *
     * @return mixed
     */
    public function doReset()
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

            return view('auth.password_reset')->with('token', request('token'))->withErrors([$error]);
        }
        $userTokenDao = new UserTokenDao();
        $userToken = $userTokenDao->getByToken(request('token'));
        if (empty($userToken) === true) {
            return view('errors.404');
        }
        $colVal = [
            'us_password' => bcrypt(request('us_password'))
        ];
        $us = new UsersDao();
        $us->doUpdateTransaction($userToken['ut_us_id'], $colVal);
        $userTokenDao->doDeleteTransaction($userToken['ut_id']);

        return view('auth.message')->with([
            'message' => Trans::getWord('successResetPassword', 'message')
        ]);
    }

}
