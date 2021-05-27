<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::group(['middleware' => ['auth:api']], function () {
# Auth
Route::post('/auth', 'Api\Alfa\AuthController@doLogin');

Route::group(['middleware' => ['checkApiToken']], function () {

    # Auth
    Route::post('/authByToken', 'Api\Alfa\AuthController@doLoginToken');
    Route::get('/loadUserMapping', 'Api\Alfa\AuthController@loadUserMapping');

    # Stock Card
    Route::get('/loadStockCard', 'Api\Alfa\HomeController@loadStockCard');
    Route::get('/loadGoodsStorage', 'Api\Alfa\HomeController@loadStorageStockCard');

    # Storage Overview
    Route::get('/loadStorageOverview', 'Api\Alfa\HomeController@loadStorageOverview');
    Route::get('/loadStorageGoods', 'Api\Alfa\HomeController@loadGoodsStorageOverview');

    # Master
    Route::get('/loadRelationGoods', 'Api\Alfa\MasterController@loadRelationGoods');
    Route::get('/loadGoods', 'Api\Alfa\MasterController@loadGoods');
    Route::get('/loadWarehouse', 'Api\Alfa\MasterController@loadWarehouse');
    Route::get('/loadRelation', 'Api\Alfa\MasterController@loadRelation');
    Route::get('/loadActionEvents', 'Api\Alfa\MasterController@loadActionEvents');
    Route::get('/loadGoodDamageType', 'Api\Alfa\MasterController@loadGoodDamageType');
    Route::get('/loadGoodCauseDamage', 'Api\Alfa\MasterController@loadGoodCauseDamage');
    Route::get('/loadWarehouseStorage', 'Api\Alfa\MasterController@loadWarehouseStorage');
    

    # Job Overview 
    Route::get('/loadJobOverview', 'Api\Alfa\HomeController@loadJobOverview');
    Route::get('/loadJobOverviewByTime', 'Api\Alfa\HomeController@loadJobOverviewByTime');
    Route::get('/loadListJobOverview', 'Api\Alfa\HomeController@loadListJobOverview'); 
    
    # Job Order
    Route::get('/loadProgressJobOverview', 'Api\Alfa\JobController@progressJobOverview');
    Route::get('/loadPlanningJobOverview', 'Api\Alfa\JobController@planningJobOverview');
    Route::get('/loadMyJobs', 'Api\Alfa\JobController@loadMyJobs');
    Route::get('/loadJobWorkSheet', 'Api\Alfa\JobController@loadJobWorkSheet');
    Route::get('/doUploadEventImage', 'Api\Alfa\JobController@doUploadEventImage');
    Route::get('/loadJobGoods', 'Api\Alfa\JobController@loadJobGoods');
    
    Route::post('/insertJobEvent', 'Api\Alfa\JobController@insertJobEvent');
    Route::post('/doUploadEventImage', 'Api\Alfa\JobController@doUploadEventImage');



    # Job Movement
    Route::get('/loadJobMovement', 'Api\Alfa\StockMovementController@loadJobData');
    Route::get('/loadJobGoodsMovement', 'Api\Alfa\StockMovementController@loadGoodsData');

    # Stock Opname
    Route::get('/loadStockOpname', 'Api\Alfa\StockOpnameController@loadJobData');
    Route::get('/loadStockOpnameGoods', 'Api\Alfa\StockOpnameController@loadGoodsData');

    # Job Inbound
    Route::get('/loadJobInbound', 'Api\Alfa\InboundController@loadJobData');
    Route::get('/loadJobInboundReceive', 'Api\Alfa\InboundController@loadJobInboundReceive');
    Route::get('/loadInboundReceiveDetail', 'Api\Alfa\InboundController@loadInboundReceiveDetail');
    Route::get('/loadJirForPutAway', 'Api\Alfa\InboundController@loadJirForPutAway');
    Route::get('/loadJirStorage', 'Api\Alfa\InboundController@loadJirStorage');
    Route::get('/loadJobGoodsInbound', 'Api\Alfa\InboundController@loadJobGoodsInbound');
    Route::get('/verifyStorageInbound', 'Api\Alfa\InboundController@verifyStorage');
    Route::get('/verifySnInbound', 'Api\Alfa\InboundController@verifySnInbound');
    Route::get('/loadRemainingInboundReceive', 'Api\Alfa\InboundController@loadRemainingInboundReceive');
    

    // Route::get('/verifySnInbound', 'Api\Alfa\InboundController@verifySnStorage');
    // Route::get('/verifySkuInbound', 'Api\Alfa\InboundController@verifySkuStorage');
    // Route::get('/loadInboundDamage', 'Api\Alfa\InboundController@loadGoodsDamageReceived');
    // Route::get('/loadJobGoodsInboundAfterReceive', 'Api\Alfa\InboundController@loadActualGoodsReceive');
    // Route::get('/loadInboundDetail', 'Api\Alfa\InboundController@loadListStorageGoods');
    // Route::get('/loadRemainingSnInbound', 'Api\Alfa\InboundController@loadRemainingSn');
    
    
    Route::post('/doInboundArrive', 'Api\Alfa\InboundController@updateTruckArrival');
    Route::post('/doStartUnloadingInbound', 'Api\Alfa\InboundController@startUnload');
    Route::post('/doGoodReceiveInbound', 'Api\Alfa\InboundController@receiveGoods');
    Route::post('/doRegisterInboundDamage', 'Api\Alfa\InboundController@registerGoodDamageReceive');
    Route::post('/doDeleteInboundDamage', 'Api\Alfa\InboundController@deleteGoodDamageReceive');
    Route::post('/doEndUnloadingInbound', 'Api\Alfa\InboundController@doEndUnload');
    Route::post('/doStartPutAwayInbound', 'Api\Alfa\InboundController@startPutAway');
    Route::post('/doUpdateInboundDetail', 'Api\Alfa\InboundController@updateGoodsStorage');
    Route::post('/doDeleteInboundDetail', 'Api\Alfa\InboundController@deleteGoodsStorage');
    // Route::post('/doUpdateSerialNumberInbound', 'Api\Alfa\InboundController@updateSerialNumber');
    Route::post('/doEndPutAwayInbound', 'Api\Alfa\InboundController@completePutAway');
    
        
    # Job Outbound
    Route::get('/loadJobOutbound', 'Api\Alfa\OutboundController@loadJobData');
    Route::get('/loadJobGoodsOutbound', 'Api\Alfa\OutboundController@loadJobGoods');
    Route::get('/loadJobOutboundDetail', 'Api\Alfa\OutboundController@loadJobDetail');
    Route::get('/loadJidStockForPicking', 'Api\Alfa\OutboundController@loadJidStock');
    Route::get('/verifyScanStorageOutbound', 'Api\Alfa\OutboundController@verifyScanStorage');
    
    // Route::get('/loadJidStorageForPicking', 'Api\Alfa\OutboundController@loadJidStorage');
    // Route::get('/loadRemainingSnOutbound', 'Api\Alfa\OutboundController@loadRemainingSn');
    // Route::get('/verifyStorageOutbound', 'Api\Alfa\OutboundController@verifyStorage');
    Route::get('/verifySnOutbound', 'Api\Alfa\OutboundController@verifySnStorage');

    
    Route::post('/doStartPickingOutbound', 'Api\Alfa\OutboundController@startPicking');
    Route::post('/doInsertOutboundDetail', 'Api\Alfa\OutboundController@insertOutboundDetail');
    Route::post('/doUpdateOutboundDetail', 'Api\Alfa\OutboundController@updateOutboundDetail');
    Route::post('/doDeleteJobOutboundDetail', 'Api\Alfa\OutboundController@deleteOutboundDetail');
    // Route::post('/doUpdateSerialNumberOutbound', 'Api\Alfa\OutboundController@updateSerialNumber');
    Route::post('/doEndPickingOutbound', 'Api\Alfa\OutboundController@completePicking');
    Route::post('/doOutboundArrive', 'Api\Alfa\OutboundController@truckdArrive');
    Route::post('/doStartLoadingOutbound', 'Api\Alfa\OutboundController@startLoading');
    Route::post('/doEndLoadingOutbound', 'Api\Alfa\OutboundController@completeLoading');
});
    
// });
