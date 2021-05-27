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
use App\Http\Controllers\StatisticController;
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
    Route::get('/documentPdf', 'DocumentController@doControlPdf');
    Route::get('/download', 'DownloadController@doControl');

    # route for document download.

    # ===== Start Dashboard ==========
    # home
    Route::get('/home', 'DashboardController@home');
    Route::post('/home', 'DashboardController@home');
    # ===== End Dashboard ==========

    # ===== Start Dashboard Item ==========
    # ------ totalDraftJob ------
    Route::get('/totalDraftJob', 'DashboardItemController@totalDraftJob');
    # ------ totalPublishedJob ------
    Route::get('/totalPublishedJob', 'DashboardItemController@totalPublishedJob');
    # ------ totalInProgressJob ------
    Route::get('/totalInProgressJob', 'DashboardItemController@totalInProgressJob');
    # ------ totalCompleteJob ------
    Route::get('/totalCompleteJob', 'DashboardItemController@totalCompleteJob');
    # ------ totalInboundItem ------
    Route::get('/totalInboundItem', 'DashboardItemController@totalInboundItem');
    # ------ totalOutboundItem ------
    Route::get('/totalOutboundItem', 'DashboardItemController@totalOutboundItem');
    # ------ totalGoodItem ------
    Route::get('/totalGoodItem', 'DashboardItemController@totalGoodItem');
    # ------ totalDamageItem ------
    Route::get('/totalDamageItem', 'DashboardItemController@totalDamageItem');
    # ------ warehouseArriveSoon ------
    Route::get('/warehouseArriveSoon', 'DashboardItemController@warehouseArriveSoon');
    # ------ inProgressJobTable ------
    Route::get('/inProgressJobTable', 'DashboardItemController@inProgressJobTable');
    # ------ planningJobTable ------
    Route::get('/planningJobTable', 'DashboardItemController@planningJobTable');
    # ------ autoReloadProgressJob ------
    Route::get('/autoReloadProgressJob', 'DashboardItemController@autoReloadProgressJob');
    # ------ autoReloadPlanningJob ------
    Route::get('/autoReloadPlanningJob', 'DashboardItemController@autoReloadPlanningJob');
    # ------ totalImportContainer ------
    Route::get('/totalImportContainer', 'DashboardItemController@totalImportContainer');
    # ------ totalExportContainer ------
    Route::get('/totalExportContainer', 'DashboardItemController@totalExportContainer');
    # ------ totalImport ------
    Route::get('/totalImport', 'DashboardItemController@totalImport');
    # ------ totalExport ------
    Route::get('/totalExport', 'DashboardItemController@totalExport');
    # ------ equipmentStatus ------
    Route::get('/equipmentStatus', 'DashboardItemController@equipmentStatus');
    # ------ equipmentCost ------
    Route::get('/equipmentCost', 'DashboardItemController@equipmentCost');
    # ------ equipmentReminder ------
    Route::get('/equipmentReminder', 'DashboardItemController@equipmentReminder');

    Route::get('/planningJobTrucking', 'DashboardItemController@planningJobTrucking');
    Route::get('/inProgressJobTrucking', 'DashboardItemController@inProgressJobTrucking');
    # ===== End Dashboard Item ==========


    # ====================================== Start Statistic ==========
    # stockCard
    Route::match(['get', 'post'], '/stockCard', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/StockCard');
    });
    # stockMilestone
    Route::match(['get', 'post'], '/stockMilestone', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/StockMilestone');
    });
    # storageOverview
    Route::match(['get', 'post'], '/storageOverview', static function () {
        $control = new StatisticController();
        return $control->doControl('Warehouse/StorageOverview');
    });
    # serialNumberHistory
    Route::match(['get', 'post'], '/serialNumberHistory', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/SerialNumberHistory');
    });
    # stockReport
    Route::match(['get', 'post'], '/stockReport', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/StockReport');
    });
    # whWeighCbm
    Route::match(['get', 'post'], '/whWeighCbm', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/WeightCbmReport');
    });
    # whPnHistory
    Route::match(['get', 'post'], '/whPnHistory', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/PackingNumberHistory');
    });
    # Stock Aging
    Route::match(['get', 'post'], '/stockAging', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/StockAging');
    });
    # grossProfit
    Route::match(['get', 'post'], '/grossProfit', static function () {
        $control = new StatisticController();
        return $control->doControl('Finance/GrossProfit');
    });
    # outstandingInvoice
    Route::match(['get', 'post'], '/outstandingInvoice', static function () {
        $control = new StatisticController();
        return $control->doControl('Finance/OutstandingInvoice');
    });
    # Inklaring Time Sheets
    Route::match(['get', 'post'], '/inkTimeSheet', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Inklaring/TimeSheet');
    });
    # Inklaring Report
    Route::match(['get', 'post'], '/jikReport', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Inklaring/JobReport');
    });

    # job fifo
    Route::match(['get', 'post'], '/whFifo', static function () {
        $control = new StatisticController();
        return $control->doControl('Job/Warehouse/JobFifo');
    });
    # =========================== End Statistic ==========


    # =========================== Start Page ==========
    # brand
    Route::match(['get', 'post'], '/action/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Service/Action');
    });
    # activeService
    Route::match(['get', 'post'], '/activeService/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/SystemService');
    });
    # apiAccess
    Route::match(['get', 'post'], '/apiAccess/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/ApiAccess');
    });
    # country
    Route::match(['get', 'post'], '/country/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Location/Country');
    });
    # state
    Route::match(['get', 'post'], '/state/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Location/State');
    });
    # city
    Route::match(['get', 'post'], '/city/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Location/City');
    });
    # district
    Route::match(['get', 'post'], '/district/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Location/District');
    });
    # port
    Route::match(['get', 'post'], '/port/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Location/Port');
    });
    # contactPerson
    Route::match(['get', 'post'], '/contactPerson/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Relation/ContactPerson');
    });
    # container
    Route::match(['get', 'post'], '/container/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Container');
    });
    # currency
    Route::match(['get', 'post'], '/currency/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Currency');
    });
    # customsClearanceType
    Route::match(['get', 'post'], '/customsClearanceType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/CustomsClearanceType');
    });
    # customsDocumentType
    Route::match(['get', 'post'], '/customsDocumentType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/CustomsDocumentType');
    });
    # documentGroup
    Route::match(['get', 'post'], '/documentGroup/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentGroup');
    });
    # documentType
    Route::match(['get', 'post'], '/documentType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentType');
    });
    # document
    Route::match(['get', 'post'], '/document/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/Document');
    });
    # equipment
    Route::match(['get', 'post'], '/equipment/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Equipment');
    });
    # equipmentGroup
    Route::match(['get', 'post'], '/eg/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/EquipmentGroup');
    });
    # ========================= Master/Goods ===================================
    # brand
    Route::match(['get', 'post'], '/brand/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/Brand');
    });
    # goodsCategory
    Route::match(['get', 'post'], '/goodsCategory/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsCategory');
    });
    # goods
    Route::match(['get', 'post'], '/goods/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/Goods');
    });
    # goodsUnit
    Route::match(['get', 'post'], '/goodsUnit/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsUnit');
    });
    # goodsPrefix
    Route::match(['get', 'post'], '/goodsPrefix/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsPrefix');
    });
    # goodsMaterial
    Route::match(['get', 'post'], '/goodsMaterial/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsMaterial');
    });
    # goodsCauseDamage
    Route::match(['get', 'post'], '/goodsCauseDamage/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsCauseDamage');
    });
    # goodsDamageType
    Route::match(['get', 'post'], '/goodsDamageType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Goods/GoodsDamageType');
    });
    # ========================= Master/Goods ===================================
    # joHistory
    Route::match(['get', 'post'], '/joHistory/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JoHistory');
    });
    # joWhInbound
    Route::match(['get', 'post'], '/joWhInbound/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobInbound');
    });
    # joWhOpname
    Route::match(['get', 'post'], '/joWhOpname/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/StockOpname');
    });
    # joWhOutbound
    Route::match(['get', 'post'], '/joWhOutbound/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobOutbound');
    });
    # joWhStockAdjustment
    Route::match(['get', 'post'], '/joWhStockAdjustment/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobStockAdjustment');
    });
    # joWhStockMovement
    Route::match(['get', 'post'], '/joWhStockMovement/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobStockMovement');
    });
    # joWhStockTransfer
    Route::match(['get', 'post'], '/joWhStockTransfer/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobStockTransfer');
    });
    # jobAdjustmentDetail
    Route::match(['get', 'post'], '/jobAdjustmentDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobAdjustmentDetail');
    });
    # jobContainer
    Route::match(['get', 'post'], '/jobContainer/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobContainer');
    });
    # jobGoods
    Route::match(['get', 'post'], '/jobGoods/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobGoods');
    });
    # jobInboundDamage
    Route::match(['get', 'post'], '/jobInboundDamage/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobInboundDamage');
    });
    # jobInboundDetail
    Route::match(['get', 'post'], '/jobInboundDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobInboundDetail');
    });
    # jobInboundReceive
    Route::match(['get', 'post'], '/jobInboundReceive/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobInboundReceive');
    });
    # jobInklaringRelease
    Route::match(['get', 'post'], '/jikr/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Inklaring/JobInklaringRelease');
    });
    # jobMovementDetail
    Route::match(['get', 'post'], '/jobMovementDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobMovementDetail');
    });
    # jobStockTransferGoods
    Route::match(['get', 'post'], '/jobStockTransferGoods/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobStockTransferGoods');
    });
    # jobOfficer
    Route::match(['get', 'post'], '/jobOfficer/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobOfficer');
    });
    # jobOrder
    Route::match(['get', 'post'], '/jobOrder/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobOrder');
    });
    # jobOutboundDetail
    Route::match(['get', 'post'], '/jobOutboundDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/JobOutboundDetail');
    });
    # jobPurchase
    Route::match(['get', 'post'], '/jobPurchase/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobPurchase');
    });
    # jobSales
    Route::match(['get', 'post'], '/jobSales/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobSales');
    });
    # menu
    Route::match(['get', 'post'], '/menu/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/Menu');
    });
    # office
    Route::match(['get', 'post'], '/office/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Relation/Office');
    });
    # page
    Route::match(['get', 'post'], '/page/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/Page');
    });
    # pageRight
    Route::match(['get', 'post'], '/pageRight/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/PageRight');
    });
    # pageCategory
    Route::match(['get', 'post'], '/pageCategory/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/PageCategory');
    });
    # relation
    Route::match(['get', 'post'], '/relation/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Relation/Relation');
    });
    # serialCode
    Route::match(['get', 'post'], '/serialCode/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/SerialCode');
    });
    # serialNumber
    Route::match(['get', 'post'], '/serialNumber/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/SerialNumber');
    });
    # service
    Route::match(['get', 'post'], '/service/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Service/Service');
    });
    # serviceTerm
    Route::match(['get', 'post'], '/serviceTerm/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Service/ServiceTerm');
    });
    # serviceTermDocument
    Route::match(['get', 'post'], '/serviceTermDocument/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/ServiceTermDocument');
    });
    # so
    Route::match(['get', 'post'], '/so/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrder');
    });
    # soHistory
    Route::match(['get', 'post'], '/soHistory/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderHistory');
    });
    # stockAdjustmentType
    Route::match(['get', 'post'], '/stockAdjustmentType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Warehouse/StockAdjustmentType');
    });
    # stockOpnameDetail
    Route::match(['get', 'post'], '/stockOpnameDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/StockOpnameDetail');
    });
    # switchSystem
    Route::match(['get', 'post'], '/switchSystem/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/SwitchSystem');
    });
    # system
    Route::match(['get', 'post'], '/system/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/System');
    });
    # systemAction
    Route::match(['get', 'post'], '/systemAction/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/Action/SystemAction');
    });
    # systemActionEvent
    Route::match(['get', 'post'], '/systemActionEvent/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/Action/SystemActionEvent');
    });
    # systemService
    Route::match(['get', 'post'], '/systemService/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Service/SystemService');
    });
    # systemSetting
    Route::match(['get', 'post'], '/systemSetting/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/SystemSetting');
    });
    # systemTable
    Route::match(['get', 'post'], '/systemTable/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/SystemTable');
    });
    # transportModule
    Route::match(['get', 'post'], '/transportModule/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/TransportModule');
    });
    # unit
    Route::match(['get', 'post'], '/unit/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Unit');
    });
    # user
    Route::match(['get', 'post'], '/user/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'User/User');
    });
    # userGroup
    Route::match(['get', 'post'], '/userGroup/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'User/UserGroup');
    });
    # userMapping
    Route::match(['get', 'post'], '/userMapping/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'User/UserMapping');
    });
    # warehouse
    Route::match(['get', 'post'], '/warehouse/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Warehouse');
    });
    # warehouseStorage
    Route::match(['get', 'post'], '/warehouseStorage/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/WarehouseStorage');
    });
    # joTrucking
    Route::match(['get', 'post'], '/joTrucking/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/JoTrucking');
    });
    # joTruck
    Route::match(['get', 'post'], '/joTruck/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/TruckingConventional');
    });
    # joTruckExp
    Route::match(['get', 'post'], '/joTruckExp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/TruckingExport');
    });
    # joTruckImp
    Route::match(['get', 'post'], '/joTruckImp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/TruckingImport');
    });
    # jtd
    Route::match(['get', 'post'], '/jtd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/JobTruckingDetail');
    });
    # joWhBundling
    Route::match(['get', 'post'], '/joWhBundling/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/Bundling/JobBundling');
    });
    # joWhUnBundling
    Route::match(['get', 'post'], '/joWhUnBundling/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/Bundling/JobUnBundling');
    });
    # jbd
    Route::match(['get', 'post'], '/jbd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/Bundling/JobBundlingDetail');
    });
    # jbd
    Route::match(['get', 'post'], '/jbd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/Bundling/JobBundlingDetail');
    });
    Route::match(['get', 'post'], '/jobBundlingMaterial/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Warehouse/Bundling/JobBundlingMaterial');
    });
    # costCodeGroup
    Route::match(['get', 'post'], '/costCodeGroup/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/CostCodeGroup');
    });
    # costCode
    Route::match(['get', 'post'], '/costCode/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/CostCode');
    });
    # tax
    Route::match(['get', 'post'], '/tax/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/Tax');
    });
    # taxDetail
    Route::match(['get', 'post'], '/taxDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/TaxDetail');
    });
    # paymentTerms
    Route::match(['get', 'post'], '/paymentTerms/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/PaymentTerms');
    });
    # paymentMethod
    Route::match(['get', 'post'], '/paymentMethod/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/PaymentMethod');
    });
    # soSales
    Route::match(['get', 'post'], '/soSales/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderSales');
    });
    # bank
    Route::match(['get', 'post'], '/bank/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/Bank');
    });
    # relationBank
    Route::match(['get', 'post'], '/relationBank/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Relation/RelationBank');
    });
    # purchaseInvoice
    Route::match(['get', 'post'], '/purchaseInvoice/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/Purchase/PurchaseInvoice');
    });
    # salesInvoice
    Route::match(['get', 'post'], '/salesInvoice/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/Sales/SalesInvoice');
    });
    # sid
    Route::match(['get', 'post'], '/sid/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/Sales/SalesInvoiceDetail');
    });
    # jd
    Route::match(['get', 'post'], '/jd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/Purchase/JobDeposit');
    });
    # jdd
    Route::match(['get', 'post'], '/jdd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/Purchase/JobDepositDetail');
    });
    # serviceOrder
    Route::match(['get', 'post'], '/serviceOrder/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/ServiceOrder');
    });
    # serviceReminder
    Route::match(['get', 'post'], '/serviceReminder/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/ServiceReminder');
    });
    # serviceTask
    Route::match(['get', 'post'], '/serviceTask/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/Master/ServiceTask');
    });
    # renewalType
    Route::match(['get', 'post'], '/renewalType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/Master/RenewalType');
    });
    # equipmentUsage
    Route::match(['get', 'post'], '/equipmentUsage/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/EquipmentUsage');
    });
    # equipmentFuel
    Route::match(['get', 'post'], '/equipmentFuel/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/EquipmentFuel');
    });
    # renewalReminder
    Route::match(['get', 'post'], '/renewalReminder/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/RenewalReminder');
    });
    # renewalOrder
    Route::match(['get', 'post'], '/renewalOrder/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/RenewalOrder');
    });
    # ownershipType
    Route::match(['get', 'post'], '/ownershipType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/OwnershipType');
    });
    # equipmentMeter
    Route::match(['get', 'post'], '/equipmentMeter/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/EquipmentMeter');
    });
    # serviceOrderDetail
    Route::match(['get', 'post'], '/serviceOrderDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/ServiceOrderDetail');
    });
    # serviceOrderCost
    Route::match(['get', 'post'], '/serviceOrderCost/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/ServiceOrderCost');
    });
    # renewalOrderDetail
    Route::match(['get', 'post'], '/renewalOrderDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/RenewalOrderDetail');
    });
    # renewalOrderCost
    Route::match(['get', 'post'], '/renewalOrderCost/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Fms/RenewalOrderCost');
    });
    # dashboard
    Route::match(['get', 'post'], '/dashboard/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/Dashboard');
    });
    # dashboardDetail
    Route::match(['get', 'post'], '/dashboardDetail/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/DashboardDetail');
    });
    # dashboardItem
    Route::match(['get', 'post'], '/dashboardItem/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/DashboardItem');
    });
    # bank
    Route::match(['get', 'post'], '/bank/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Finance/Bank');
    });
    # documentTemplateType
    Route::match(['get', 'post'], '/documentTemplateType/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentTemplateType');
    });
    # documentTemplate
    Route::match(['get', 'post'], '/documentTemplate/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Document/DocumentTemplate');
    });
    # documentSignature
    Route::match(['get', 'post'], '/documentSignature/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Setting/DocumentSignature');
    });
    # routeDelivery
    Route::match(['get', 'post'], '/rd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/RouteDelivery');
    });
    # systemType
    Route::match(['get', 'post'], '/sty/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/SystemType');
    });
    # salesOrderIssue
    Route::match(['get', 'post'], '/soi/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderIssue');
    });
    # salesOrderIssue
    Route::match(['get', 'post'], '/hpp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Trucking/HppCalculator');
    });
    # jobTitle
    Route::match(['get', 'post'], '/jbt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Crm/JobTitle');
    });
    # department
    Route::match(['get', 'post'], '/dpt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Crm/Department');
    });
    # industry
    Route::match(['get', 'post'], '/ids/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Master/Crm/Industry');
    });
    # deal
    Route::match(['get', 'post'], '/deal/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Deal');
    });
    # task
    Route::match(['get', 'post'], '/task/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Task');
    });
    # task participant
    Route::match(['get', 'post'], '/tp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/TaskParticipant');
    });
    # ticket
    Route::match(['get', 'post'], '/ticket/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Ticket');
    });


    # relation type
    Route::match(['get', 'post'], '/rty/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/RelationType');
    });

    # lead
    Route::match(['get', 'post'], '/lead/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Lead');
    });


    # price
    Route::match(['get', 'post'], '/prc/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/Price');
    });
    Route::match(['get', 'post'], '/prd/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/PriceDetail');
    });
    Route::match(['get', 'post'], '/prcSlsDl/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/PriceDelivery');
    });
    Route::match(['get', 'post'], '/prcPrcDl/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/CogsDelivery');
    });
    Route::match(['get', 'post'], '/prcSlsInk/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/PriceInklaring');
    });
    Route::match(['get', 'post'], '/prcPrcInk/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/CogsInklaring');
    });
    Route::match(['get', 'post'], '/prcSlsWh/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/PriceWarehouse');
    });
    Route::match(['get', 'post'], '/prcPrcWh/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/CogsWarehouse');
    });
    # Quotation
    Route::match(['get', 'post'], '/qt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/Quotation');
    });
    Route::match(['get', 'post'], '/slsQt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/SalesQuotation');
    });
    Route::match(['get', 'post'], '/prcQt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/PurchaseQuotation');
    });
    Route::match(['get', 'post'], '/qtm/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Crm/Quotation/QuotationTerms');
    });
    Route::match(['get', 'post'], '/soq/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderQuotation');
    });
    Route::match(['get', 'post'], '/joq/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/JobOrderQuotation');
    });
    Route::match(['get', 'post'], '/changePassword', static function () {
        $control = new PageController();
        return $control->doControl('detail', 'User/UserChangePassword');
    });
    # page notification
    Route::match(['get', 'post'], '/pnt/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Page/PageNotificationTemplate');
    });

    # joInklaring
    Route::match(['get', 'post'], '/jik/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Inklaring/JobInklaring');
    });
    # joInklaringExportContainer
    Route::match(['get', 'post'], '/joInklaringExportContainer/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Inklaring/JobInklaringExportContainer');
    });
    # joInklaringImport
    Route::match(['get', 'post'], '/joInklaringImport/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Inklaring/JobInklaringImport');
    });
    # joInklaringImportContainer
    Route::match(['get', 'post'], '/joInklaringImportContainer/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Inklaring/JobInklaringImportContainer');
    });

    # Sales Order Container
    Route::match(['get', 'post'], '/soc/{pc?}', static function ($pc = 'ajax') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderContainer');
    });
    # Sales Order Goods
    Route::match(['get', 'post'], '/sog/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderGoods');
    });
    # Inco Terms
    Route::match(['get', 'post'], '/ict/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'System/Master/IncoTerms');
    });
    # Sales Order Delivery
    Route::match(['get', 'post'], '/sdl/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'CustomerService/SalesOrderDelivery');
    });
    # Job Order Delivery
    Route::match(['get', 'post'], '/jdl/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Delivery/JobDelivery');
    });
    # Job Order Delivery Detail
    Route::match(['get', 'post'], '/jdld/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Delivery/JobDeliveryDetail');
    });
    # Load Unload Delivery
    Route::match(['get', 'post'], '/lud/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job/Delivery/LoadUnloadDelivery');
    });
    # notification
    Route::match(['get', 'post'], '/notification/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Notification\Notification');
    });
    # Job notification receiver
    Route::match(['get', 'post'], '/jnr/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Job\JobNotificationReceiver');
    });
    # BAnk Account
    Route::match(['get', 'post'], '/ba/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/BankAccount');
    });
    # topUp
    Route::match(['get', 'post'], '/topUp/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/TopUp');
    });
    # electronic Account
    Route::match(['get', 'post'], '/ea/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/ElectronicAccount');
    });
    # electronic top up
    Route::match(['get', 'post'], '/et/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/ElectronicTopUp');
    });
    # cashAdvance
    Route::match(['get', 'post'], '/ca/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/CashAdvance');
    });
    # cashAdvance Detail
    Route::match(['get', 'post'], '/cad/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/CashAdvanceDetail');
    });
    # Bank Account Mutation
    Route::match(['get', 'post'], '/baMutation/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/BankMutation');
    });
    # Bank Account Mutation
    Route::match(['get', 'post'], '/eaMutation/{pc?}', static function ($pc = 'listing') {
        $control = new PageController();
        return $control->doControl($pc, 'Finance/CashAndBank/ElectronicMutation');
    });

});
