<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', 'Auth\LoginController@index');
Route::post('/login', 'Auth\LoginController@doLogin');
Route::get('/checkTimeout', 'Auth\LoginController@checkSession');
Route::get('/password/forgot', 'Auth\ForgotPasswordController@index');
Route::post('/password/forgot', 'Auth\ForgotPasswordController@doVerify');
Route::get('/password/reset', 'Auth\ForgotPasswordController@reset');
Route::post('/password/reset', 'Auth\ForgotPasswordController@doReset');
Route::get('/confirmEmail', 'Auth\EmailConfirmationController@index');
Route::post('/confirmEmail', 'Auth\EmailConfirmationController@doConfirm');
Route::get('/password/successReset', 'Auth\ForgotPasswordController@successReset');
Route::get('/mappingUser', 'Auth\EmailConfirmationController@mappingUser');
Route::get('/test', 'TestController@test');
Route::group(['middleware' => ['app_auth']], static function () {

    Route::get('/', 'DashboardController@index');
    Route::get('/logout', 'Auth\LoginController@doLogout');
    Route::get('/doSwitch', 'Auth\LoginController@doSwitch');
    Route::get('/seed', 'SeederController@index');
    # menu
    Route::match(['get', 'post'], '/mn/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/Menu');
    });
    # pageCategory
    Route::match(['get', 'post'], '/pc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/PageCategory');
    });
    # page
    Route::match(['get', 'post'], '/pg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/Page');
    });
    # pageRight
    Route::match(['get', 'post'], '/pr/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/PageRight');
    });
});
