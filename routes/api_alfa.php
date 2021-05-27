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
    Route::get('/loadGoodUnit', 'Api\Alfa\MasterController@loadGoodUnit');
    

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
    Route::get('/loadListJidMovement', 'Api\Alfa\StockMovementController@loadListJid');
    Route::get('/loadJidMovementByModel', 'Api\Alfa\StockMovementController@loadJidByModel');
    Route::get('/loadJidMovementByPn', 'Api\Alfa\StockMovementController@loadJidByPn');
    Route::get('/loadJidMovementBySn', 'Api\Alfa\StockMovementController@loadJidBySn');
    Route::get('/loadJidStockForMovement', 'Api\Alfa\StockMovementController@loadJidStock');
    Route::get('/verifyScanModelMovement', 'Api\Alfa\StockMovementController@verifyScanModel');
    Route::get('/verifyScanSnMovement', 'Api\Alfa\StockMovementController@verifyScanSn');
     

    Route::post('/doStartStockMovement', 'Api\Alfa\StockMovementController@doStart');
    Route::post('/doEndStockMovement', 'Api\Alfa\StockMovementController@doComplete');
    Route::post('/doUpdateMovementDetail', 'Api\Alfa\StockMovementController@doUpdateDetail');
    Route::post('/doDeleteMovementDetail', 'Api\Alfa\StockMovementController@doDeleteDetail');
    
    # Stock Opname 
    Route::get('/loadStockOpname', 'Api\Alfa\StockOpnameController@loadJobData');
    Route::get('/loadStockOpnameGoods', 'Api\Alfa\StockOpnameController@loadGoodsData');
    Route::get('/loadStockOpnameDetail', 'Api\Alfa\StockOpnameController@loadStockOpnameDetail');
    Route::get('/verifyScanStorageOpname', 'Api\Alfa\StockOpnameController@verifyScanStorage');
    Route::get('/verifyScanModelOpname', 'Api\Alfa\StockOpnameController@verifyScanModel');
    Route::get('/verifyScanSnOpname', 'Api\Alfa\StockOpnameController@verifyScanSn');
    
    Route::post('/doStartStockOpname', 'Api\Alfa\StockOpnameController@doStart');
    Route::post('/doEndStockOpname', 'Api\Alfa\StockOpnameController@doEnd');
    Route::post('/doUpdateOpnameDetail', 'Api\Alfa\StockOpnameController@doUpdateOpnameDetail');
    Route::post('/doDeleteOpnameDetail', 'Api\Alfa\StockOpnameController@doDeleteOpnameDetail');
    
    # Job Inbound
    Route::get('/loadJobInbound', 'Api\Alfa\InboundController@loadJobData');
    Route::get('/loadJobGoodsInbound', 'Api\Alfa\InboundController@loadJobGoodsInbound');
    Route::get('/loadJobInboundReceive', 'Api\Alfa\InboundController@loadJobInboundReceive');
    Route::get('/verifyInboundReceiveSn', 'Api\Alfa\InboundController@verifyInboundReceiveSn');
    Route::get('/verifyInboundReceivePn', 'Api\Alfa\InboundController@verifyInboundReceivePn');

    Route::get('/loadJirForPutAway', 'Api\Alfa\InboundController@loadJirForPutAway');
    Route::get('/loadJirStorage', 'Api\Alfa\InboundController@loadJirStorage');
    Route::get('/verifyStorageInbound', 'Api\Alfa\InboundController@verifyStorage');
    Route::get('/verifySnInbound', 'Api\Alfa\InboundController@verifySnInbound');

    Route::post('/doInboundArrive', 'Api\Alfa\InboundController@updateTruckArrival');
    Route::post('/doStartUnloadingInbound', 'Api\Alfa\InboundController@startUnload');

    Route::post('/doUpdateInboundReceive', 'Api\Alfa\InboundController@updateInboundReceive');
    Route::post('/doDeleteInboundReceive', 'Api\Alfa\InboundController@deleteInboundReceive');
    Route::post('/doInsertJirByPn', 'Api\Alfa\InboundController@insertJirByPn');

    Route::post('/doRegisterInboundDamage', 'Api\Alfa\InboundController@registerGoodDamageReceive');
    Route::post('/doDeleteInboundDamage', 'Api\Alfa\InboundController@deleteGoodDamageReceive');
    Route::post('/doEndUnloadingInbound', 'Api\Alfa\InboundController@doEndUnload');
    Route::post('/doStartPutAwayInbound', 'Api\Alfa\InboundController@startPutAway');

    Route::post('/doUpdateInboundDetail', 'Api\Alfa\InboundController@updateInboundDetail');
    Route::post('/doUpdateInboundDetailByPn', 'Api\Alfa\InboundController@updateInboundDetailByPn');
    Route::post('/doDeleteInboundDetail', 'Api\Alfa\InboundController@deleteInboundDetail');
    Route::post('/doEndPutAwayInbound', 'Api\Alfa\InboundController@completePutAway');
    
        
    # Job Outbound 
    Route::get('/loadJobOutbound', 'Api\Alfa\OutboundController@loadJobData');
    Route::get('/loadJobGoodsOutbound', 'Api\Alfa\OutboundController@loadJobGoods');
    Route::get('/loadJobOutboundDetail', 'Api\Alfa\OutboundController@loadJobDetail');
    Route::get('/loadJidStockForPicking', 'Api\Alfa\OutboundController@loadJidStock');
    Route::get('/verifyScanStorageOutbound', 'Api\Alfa\OutboundController@verifyScanStorage');
    Route::get('/loadSuggestionPickSnOutbound', 'Api\Alfa\OutboundController@loadSuggestionPickSn');
    Route::get('/verifySnOutbound', 'Api\Alfa\OutboundController@verifySnStorage');
    Route::get('/loadSuggestionPickPnOutbound', 'Api\Alfa\OutboundController@loadSuggestionPickPn');
    Route::get('/verifyPnOutbound', 'Api\Alfa\OutboundController@verifyPn');

    
    Route::post('/doStartPickingOutbound', 'Api\Alfa\OutboundController@startPicking');
    Route::post('/doInsertOutboundDetail', 'Api\Alfa\OutboundController@insertOutboundDetail');
    Route::post('/doInsertJodByPacking', 'Api\Alfa\OutboundController@insertJodByPacking');
    Route::post('/doUpdateOutboundDetail', 'Api\Alfa\OutboundController@updateOutboundDetail');
    Route::post('/doDeleteJobOutboundDetail', 'Api\Alfa\OutboundController@deleteOutboundDetail');
    Route::post('/doEndPickingOutbound', 'Api\Alfa\OutboundController@completePicking');
    Route::post('/doOutboundArrive', 'Api\Alfa\OutboundController@truckArrive');
    Route::post('/doStartLoadingOutbound', 'Api\Alfa\OutboundController@startLoading');
    Route::post('/doEndLoadingOutbound', 'Api\Alfa\OutboundController@completeLoading');
});
    
// });
