<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\Auth;

class AuthController extends Controller
{
    /**
     * The function to control user profile 
     *
     * @return mixed
     */
    public function loadUserMapping()
    {
        $model = new Auth('loadUserMapping', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function doLogin()
    {
        $model = new Auth('loginByUsernamePassword', request()->all());
        return $model->loadAuthResponse();
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function doLoginToken()
    {
        $model = new Auth('loginByToken', request()->all());
        return $model->loadResponse();
    }
}
