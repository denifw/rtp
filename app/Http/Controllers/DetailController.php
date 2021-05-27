<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:03
 */

namespace App\Http\Controllers;


//use App\Model\Detail\Crm\Relation\ContactPerson;
//use App\Model\Detail\Crm\Relation\Office;
//use App\Model\Detail\Fms\EquipmentFuel;
//use App\Model\Detail\Fms\Master\RenewalType;
//use App\Model\Detail\Fms\Master\ServiceTask;
//use App\Model\Detail\Fms\RenewalOrder;
//use App\Model\Detail\Fms\RenewalReminder;
//use App\Model\Detail\Fms\ServiceOrder;
//use App\Model\Detail\Fms\ServiceReminder;
//use App\Model\Detail\Crm\Relation\RelationBank;
//use App\Model\Detail\Finance\Purchase\JobDeposit;
//use App\Model\Detail\Job\Inklaring\JobInklaringExport;
//use App\Model\Detail\Job\Inklaring\JobInklaringExportContainer;
//use App\Model\Detail\Job\Inklaring\JobInklaringImport;
//use App\Model\Detail\Job\JobOrder;
//use App\Model\Detail\Job\Inklaring\JobInklaringImportContainer;
//use App\Model\Detail\Job\Warehouse\JobStockAdjustment;
//use App\Model\Detail\Job\Warehouse\JobStockTransfer;
//use App\Model\Detail\Master\Goods\Brand;
//use App\Model\Detail\Master\Equipment;
//use App\Model\Detail\PettyCash\CashAccount;
//use App\Model\Detail\Master\Goods\GoodsCategory;
//use App\Model\Detail\Master\Goods\GoodsCauseDamage;
//use App\Model\Detail\Master\Goods\GoodsDamageType;
//use App\Model\Detail\Master\Warehouse;
//use App\Model\Detail\Setting\Dashboard;
//use App\Model\Detail\Setting\DashboardDetail;
//use App\Model\Detail\Setting\ServiceTermDocument;
//use App\Model\Detail\Setting\System;
//use App\Model\Detail\Setting\SystemAction;
//use App\Model\Detail\System\Access\User;
//use App\Model\Detail\Master\Finance\CostCode;
//use App\Model\Detail\Master\Finance\CostCodeGroup;
//use App\Model\Detail\Master\Finance\PaymentMethod;
//use App\Model\Detail\Master\Finance\PaymentTerms;
//use App\Model\Detail\Master\Finance\Tax;
//use App\Model\Detail\Master\Quotation;
//use App\Model\Detail\Master\Unit;
//use App\Model\Detail\System\Access\UserGroup;
//use App\Model\Detail\System\Container;
//use App\Model\Detail\System\Currency;
//use App\Model\Detail\System\CustomsClearanceType;
//use App\Model\Detail\System\CustomsDocumentType;
//use App\Model\Detail\System\DashboardItem;
//use App\Model\Detail\System\EquipmentGroup;
//use App\Model\Detail\System\Location\Port;
//use App\Model\Detail\System\Access\UserMapping;
//use App\Model\Detail\System\Page\ApiAccess;
//use App\Model\Detail\System\Page\Menu;
//use App\Model\Detail\System\Page\Page;
//use App\Model\Detail\System\Page\PageCategory;
//use App\Model\Detail\Setting\SerialNumber;
//use App\Model\Detail\System\Page\PageRight;
//use App\Model\Detail\System\Service\Service;
//use App\Model\Detail\System\Service\ServiceTerm;
//use App\Model\Detail\System\Service\SystemService;
//use App\Model\Detail\System\SystemSetting;
//use App\Model\Detail\System\Document\DocumentGroup;
//use App\Model\Detail\System\Document\DocumentType;
//use App\Model\Detail\System\Location\City;
//use App\Model\Detail\System\Location\Country;
//use App\Model\Detail\System\Location\District;
//use App\Model\Detail\System\Location\State;
//use App\Model\Detail\System\SerialCode;
//use App\Model\Detail\System\SystemTable;
//use App\Model\Detail\System\TransportModule;

class DetailController extends AbstractBaseController
{

    /**
     * Property to store the base path of the page.
     *
     * @var string $pageCategory
     */
    private static $pageCategory = 'Detail';

//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function relationBank()
//    {
//        $model = new RelationBank(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function jd()
//    {
//        $model = new JobDeposit(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function pageRight()
//    {
//        $model = new PageRight(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function salesInvoice()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Finance\Sales\SalesInvoice', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function purchaseInvoice()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Finance\Purchase\Invoice', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of cash advance
//     *
//     * @return mixed
//     */
//    public function cashAdvance()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashAdvance', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of petty cash.
//     *
//     * @return mixed
//     */
//    public function topUp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashTopUp', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function cashAccount()
//    {
//        $model = new CashAccount(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function paymentMethod()
//    {
//        $model = new PaymentMethod(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function paymentTerms()
//    {
//        $model = new PaymentTerms(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function costCodeGroup()
//    {
//        $model = new CostCodeGroup(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhUnBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobUnBundling', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobBundling', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joTruckImp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingImport', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joTruckExp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingExport', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joTruck()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\TruckingConventional', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joTrucking()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\JoTrucking', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function jobOrder()
//    {
//        $model = new JobOrder(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the service term document.
//     *
//     * @return mixed
//     */
//    public function apiAccess()
//    {
//        $model = new ApiAccess(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the service term document.
//     *
//     * @return mixed
//     */
//    public function system()
//    {
//        $model = new System(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the service term document.
//     *
//     * @return mixed
//     */
//    public function serviceTermDocument()
//    {
//        $model = new ServiceTermDocument(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function goodsCauseDamage()
//    {
//        $model = new GoodsCauseDamage(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of stock adjustment type.
//     *
//     * @return mixed
//     */
//    public function stockAdjustmentType()
//    {
//        $model = new Warehouse\StockAdjustmentType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of goods damage type.
//     *
//     * @return mixed
//     */
//    public function goodsDamageType()
//    {
//        $model = new GoodsDamageType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joWhStockAdjustment()
//    {
//        $model = new JobStockAdjustment(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhStockMovement()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobStockMovement', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhStockTransfer()
//    {
//        $model = new JobStockTransfer(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of warehouse Opname.
//     *
//     * @return mixed
//     */
//    public function joWhOpname()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\StockOpname', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse Outbound.
//     *
//     * @return mixed
//     */
//    public function joWhOutbound()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobOutbound', request()->all());
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse Inbound.
//     *
//     * @return mixed
//     */
//    public function joWhInbound()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobInbound', request()->all());
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of System Action.
//     *
//     * @return mixed
//     */
//    public function systemAction()
//    {
//        $model = new SystemAction(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function so()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'CustomerService\SalesOrder', request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of warehouse.
//     *
//     * @return mixed
//     */
//    public function warehouse()
//    {
//        $model = new Warehouse(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of equipment.
//     *
//     * @return mixed
//     */
//    public function equipment()
//    {
//        $model = new Equipment(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of equipment group.
//     *
//     * @return mixed
//     */
//    public function equipmentGroup()
//    {
//        $model = new EquipmentGroup(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of container.
//     *
//     * @return mixed
//     */
//    public function container()
//    {
//        $model = new Container(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of Transport Module.
//     *
//     * @return mixed
//     */
//    public function transportModule()
//    {
//        $model = new TransportModule(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of relation.
//     *
//     * @return mixed
//     */
//    public function relation()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Crm\Relation\Relation', request()->all());
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of user mapping.
//     *
//     * @return mixed
//     */
//    public function userMapping()
//    {
//        $model = new UserMapping(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of office.
//     *
//     * @return mixed
//     */
//    public function office()
//    {
//        $model = new Office(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of contact person.
//     *
//     * @return mixed
//     */
//    public function contactPerson()
//    {
//        $model = new ContactPerson(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of system table.
//     *
//     * @return mixed
//     */
//    public function user()
//    {
//        $model = new User(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of system table.
//     *
//     * @return mixed
//     */
//    public function systemTable()
//    {
//        $model = new SystemTable(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of document Group.
//     *
//     * @return mixed
//     */
//    public function userGroup()
//    {
//        $model = new UserGroup(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of document Group.
//     *
//     * @return mixed
//     */
//    public function documentGroup()
//    {
//        $model = new DocumentGroup(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of district.
//     *
//     * @return mixed
//     */
//    public function documentType()
//    {
//        $model = new DocumentType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of district.
//     *
//     * @return mixed
//     */
//    public function district()
//    {
//        $model = new District(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the detail of city.
//     *
//     * @return mixed
//     */
//    public function city()
//    {
//        $model = new City(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of state.
//     *
//     * @return mixed
//     */
//    public function state()
//    {
//        $model = new State(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of country.
//     *
//     * @return mixed
//     */
//    public function country()
//    {
//        $model = new Country(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of systemSetting.
//     *
//     * @return mixed
//     */
//    public function serialNumber()
//    {
//        $model = new SerialNumber(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of systemSetting.
//     *
//     * @return mixed
//     */
//    public function serialCode()
//    {
//        $model = new SerialCode(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of systemSetting.
//     *
//     * @return mixed
//     */
//    public function systemSetting()
//    {
//        $model = new SystemSetting(request()->all());
//
//        return $this->doControlDetail($model);
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
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page Category.
//     *
//     * @return mixed
//     */
//    public function pageCategory()
//    {
//        $model = new PageCategory(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page.
//     *
//     * @return mixed
//     */
//    public function page()
//    {
//        $model = new Page(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page.
//     *
//     * @return mixed
//     */
//    public function service()
//    {
//        $model = new Service(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page.
//     *
//     * @return mixed
//     */
//    public function serviceTerm()
//    {
//        $model = new ServiceTerm(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page.
//     *
//     * @return mixed
//     */
//    public function systemService()
//    {
//        $model = new SystemService(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the detail of page.
//     *
//     * @return mixed
//     */
//    public function costCode()
//    {
//        $model = new CostCode(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function unit()
//    {
//        $model = new Unit(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function currency()
//    {
//        $model = new Currency(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function tax()
//    {
//        $model = new Tax(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function port()
//    {
//        $model = new Port(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function brand()
//    {
//        $model = new Brand(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function goodsCategory()
//    {
//        $model = new GoodsCategory(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function goods()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Master\Goods\Goods', request()->all());
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function quotation()
//    {
//        $model = new Quotation(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function customsClearanceType()
//    {
//        $model = new CustomsClearanceType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function customsDocumentType()
//    {
//        $model = new CustomsDocumentType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function joInklaringImportContainer()
//    {
//        $model = new JobInklaringImportContainer(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function joInklaringExportContainer()
//    {
//        $model = new JobInklaringExportContainer(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function joInklaringImport()
//    {
//        $model = new JobInklaringImport(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function joInklaringExport()
//    {
//        $model = new JobInklaringExport(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function serviceOrder()
//    {
//        $model = new ServiceOrder(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function serviceReminder()
//    {
//        $model = new ServiceReminder(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function renewalOrder()
//    {
//        $model = new RenewalOrder(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function renewalReminder()
//    {
//        $model = new RenewalReminder(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function equipmentFuel()
//    {
//        $model = new EquipmentFuel(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function serviceTask()
//    {
//        $model = new ServiceTask(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function renewalType()
//    {
//        $model = new RenewalType(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function dashboard()
//    {
//        $model = new Dashboard(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function dashboardItem()
//    {
//        $model = new DashboardItem(request()->all());
//
//        return $this->doControlDetail($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function dashboardDetail()
//    {
//        $model = new DashboardDetail(request()->all());
//
//        return $this->doControlDetail($model);
//    }
}
