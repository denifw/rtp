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
    Route::get('/home', 'DashboardController@home');
    Route::get('/logout', 'Auth\LoginController@doLogout');
    Route::get('/doSwitch', 'Auth\LoginController@doSwitch');
    Route::get('/seed', 'SeederController@index');
    Route::get('/download', 'DownloadController@doControl');
    # system table
    Route::match(['get', 'post'], '/st/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/SystemTable');
    });
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
    # Api Access
    Route::match(['get', 'post'], '/aa/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/ApiAccess');
    });
    # System Document
    # Document Group
    Route::match(['get', 'post'], '/dcg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentGroup');
    });
    # Document Type
    Route::match(['get', 'post'], '/dct/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentType');
    });
    # Document Template
    Route::match(['get', 'post'], '/dt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentTemplate');
    });
    # Document Template Type
    Route::match(['get', 'post'], '/dtt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentTemplateType');
    });
    # Document
    Route::match(['get', 'post'], '/doc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/Document');
    });
    # System - Master Country
    Route::match(['get', 'post'], '/cnt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/Country');
    });
    # System - Master State
    Route::match(['get', 'post'], '/stt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/State');
    });
    # System - Master City
    Route::match(['get', 'post'], '/cty/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/City');
    });
    # System - Master District
    Route::match(['get', 'post'], '/dtc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/District');
    });
    # System - Master - Bank
    Route::match(['get', 'post'], '/bn/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/Bank');
    });
    # System - Master - Currency
    Route::match(['get', 'post'], '/cur/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/Currency');
    });
    # System - Master - Languages
    Route::match(['get', 'post'], '/lg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/languages');
    });
    # System - Master - Serial Code
    Route::match(['get', 'post'], '/sc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/SerialCode');
    });
    # System - Master - Service
    Route::match(['get', 'post'], '/srv/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/Service');
    });
    # System - Master - Unit
    Route::match(['get', 'post'], '/uom/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/Unit');
    });
    # System - Access - System Setting
    Route::match(['get', 'post'], '/ss/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Access/SystemSetting');
    });
    # System - Access - Users
    Route::match(['get', 'post'], '/us/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Access/User');
    });
    # System - Access - UserGroup
    Route::match(['get', 'post'], '/usg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Access/UserGroup');
    });
    # System - Access - Serial Number
    Route::match(['get', 'post'], '/sn/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Access/SerialNumber');
    });
    # CRM - Relation
    Route::match(['get', 'post'], '/rel/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Relation');
    });
    # CRM - Office
    Route::match(['get', 'post'], '/of/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Office');
    });
    # CRM - Contact Person
    Route::match(['get', 'post'], '/cp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/ContactPerson');
    });
    # Master - Finance - Cost Code Group
    Route::match(['get', 'post'], '/ccg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/CostCodeGroup');
    });
    # Master - Finance - Cost Code
    Route::match(['get', 'post'], '/cc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/CostCode');
    });
    # Master - Finance - Payment Terms
    Route::match(['get', 'post'], '/pt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/PaymentTerms');
    });
    # Master - Finance - Payment Method
    Route::match(['get', 'post'], '/pm/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/PaymentMethod');
    });
    # Master - Finance - Tax
    Route::match(['get', 'post'], '/tax/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/Tax');
    });
    # Master - Finance - Tax Detail
    Route::match(['get', 'post'], '/td/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/TaxDetail');
    });
    # Master - Finance - Bank Account
    Route::match(['get', 'post'], '/ba/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/BankAccount');
    });
    # Master - Employee - Job Title
    Route::match(['get', 'post'], '/jt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Employee/JobTitle');
    });
    # Master - Employee - Employee
    Route::match(['get', 'post'], '/em/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Employee/Employee');
    });
    # Operation - Job
    Route::match(['get', 'post'], '/jo/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Operation/Job/JobOrder');
    });
    # Operation - Job Order Task
    Route::match(['get', 'post'], '/jot/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Operation/Job/JobOrderTask');
    });
    # Operation - Job Employee
    Route::match(['get', 'post'], '/jem/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Operation/Job/JobEmployee');
    });
    # Administration - Working Capital
    Route::match(['get', 'post'], '/wc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Administration/WorkingCapital');
    });
});
