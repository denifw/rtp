<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:03
 */

namespace App\Http\Controllers;


//use App\Model\Viewer\Fms\Equipment;
//use App\Model\Viewer\Fms\EquipmentFuel;
//use App\Model\Viewer\Fms\RenewalOrder;
//use App\Model\Viewer\Fms\ServiceOrder;
//use App\Model\Viewer\Job\Inklaring\JobInklaringExport;
//use App\Model\Viewer\Job\Inklaring\JobInklaringExportContainer;
//use App\Model\Viewer\Job\Inklaring\JobInklaringImport;
//use App\Model\Viewer\Job\Warehouse\JobStockTransfer;
//use App\Model\Viewer\System\Page\Menu;
//use App\Model\Viewer\Setting\SystemAction;
//use App\Model\Viewer\Master\WarehouseStorage;
//use App\Model\Viewer\Job\Inklaring\JobInklaringImportContainer;
//use App\Model\Viewer\CustomerService\SalesOrder;
//use App\Model\Viewer\Job\Warehouse\JobStockAdjustment;

class ViewerController extends AbstractBaseController
{
    /**
     * Property to store the base path of the page.
     *
     * @var string $pageCategory
     */
    private static $pageCategory = 'Viewer';

//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function cashAccount()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashAccount', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joWhBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobBundling', request()->all());
//        return $this->doControlViewer($model);
//    }
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joWhUnBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobUnBundling', request()->all());
//        return $this->doControlViewer($model);
//    }
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joTruckImp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingImport', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joTruckExp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingExport', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joTruck()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingConventional', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function so()
//    {
//        $model = new SalesOrder(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function warehouseStorage()
//    {
//        $model = new WarehouseStorage(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function goods()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Master\Goods\Goods', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joWhStockAdjustment()
//    {
//        $model = new JobStockAdjustment(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhStockMovement()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobStockMovement', request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//
//    /**
//     * The function to load the view of warehouse Opname.
//     *
//     * @return mixed
//     */
//    public function joWhOpname()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\StockOpname', request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse Outbound.
//     *
//     * @return mixed
//     */
//    public function joWhOutbound()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobOutbound', request()->all());
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the view of warehouse Inbound.
//     *
//     * @return mixed
//     */
//    public function joWhInbound()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobInbound', request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of system action.
//     *
//     * @return mixed
//     */
//    public function systemAction()
//    {
//        $model = new SystemAction(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function menu()
//    {
//        $model = new Menu(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function joInklaringImportContainer()
//    {
//        $model = new JobInklaringImportContainer(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function joInklaringExportContainer()
//    {
//        $model = new JobInklaringExportContainer(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function joInklaringImport()
//    {
//        $model = new JobInklaringImport(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function joInklaringExport()
//    {
//        $model = new JobInklaringExport(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function serviceOrder()
//    {
//        $model = new ServiceOrder(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function equipment()
//    {
//        $model = new Equipment(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function equipmentFuel()
//    {
//        $model = new EquipmentFuel(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function renewalOrder()
//    {
//        $model = new RenewalOrder(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//
//    /**
//     * The function to load the detail of menu.
//     *
//     * @return mixed
//     */
//    public function joWhStockTransfer()
//    {
//        $model = new JobStockTransfer(request()->all());
//
//        return $this->doControlViewer($model);
//    }
//

}
