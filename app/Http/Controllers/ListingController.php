<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:03
 */

namespace App\Http\Controllers;


//use App\Frame\Exceptions\Message;
//use App\Frame\Formatter\Trans;
//use App\Model\Dao\PettyCash\CashAccountDao;
//use App\Model\Listing\Crm\Relation\Relation;
//use App\Model\Listing\CustomerService\SalesOrder;
//use App\Model\Listing\CustomerService\SalesOrderHistory;
//use App\Model\Listing\Fms\EquipmentFuel;
//use App\Model\Listing\Fms\Master\RenewalType;
//use App\Model\Listing\Fms\Master\ServiceTask;
//use App\Model\Listing\Fms\RenewalOrder;
//use App\Model\Listing\Fms\RenewalReminder;
//use App\Model\Listing\Fms\ServiceOrder;
//use App\Model\Listing\Fms\ServiceReminder;
//use App\Model\Listing\Finance\Purchase\JobDeposit;
//use App\Model\Listing\Job\Inklaring\JobInklaringExport;
//use App\Model\Listing\Job\Inklaring\JobInklaringExportContainer;
//use App\Model\Listing\Job\Inklaring\JobInklaringImport;
//use App\Model\Listing\Job\JoHistory;
//use App\Model\Listing\Job\Inklaring\JobInklaringImportContainer;
//use App\Model\Listing\Job\JobOrder;
//use App\Model\Listing\Job\Warehouse\JobInbound;
//use App\Model\Listing\Job\Warehouse\JobOutbound;
//use App\Model\Listing\Job\Warehouse\JobStockAdjustment;
//use App\Model\Listing\Job\Warehouse\JobStockMovement;
//use App\Model\Listing\Job\Warehouse\JobStockTransfer;
//use App\Model\Listing\Job\Warehouse\StockOpname;
//use App\Model\Listing\Master\Goods\Brand;
//use App\Model\Listing\Master\Finance\CostCode;
//use App\Model\Listing\Master\Equipment;
//use App\Model\Listing\PettyCash\CashAccount;
//use App\Model\Listing\Master\Finance\CostCodeGroup;
//use App\Model\Listing\Master\Finance\PaymentMethod;
//use App\Model\Listing\Master\Finance\PaymentTerms;
//use App\Model\Listing\Master\Finance\Tax;
//use App\Model\Listing\Master\Goods\GoodsCategory;
//use App\Model\Listing\Master\Goods\GoodsCauseDamage;
//use App\Model\Listing\Master\Goods\GoodsDamageType;
//use App\Model\Listing\Master\Quotation;
//use App\Model\Listing\Master\Unit;
//use App\Model\Listing\Master\Warehouse;
//use App\Model\Listing\Setting\Dashboard;
//use App\Model\Listing\Setting\ServiceTermDocument;
//use App\Model\Listing\Setting\SwitchSystem;
//use App\Model\Listing\Setting\SystemAction;
//use App\Model\Listing\System\Access\User;
//use App\Model\Listing\System\Access\UserGroup;
//use App\Model\Listing\Setting\SerialNumber;
//use App\Model\Listing\System\Container;
//use App\Model\Listing\System\Currency;
//use App\Model\Listing\System\CustomsClearanceType;
//use App\Model\Listing\System\CustomsDocumentType;
//use App\Model\Listing\System\DashboardItem;
//use App\Model\Listing\System\Document\Document;
//use App\Model\Listing\System\EquipmentGroup;
//use App\Model\Listing\System\Location\Port;
//use App\Model\Listing\System\Page\ApiAccess;
//use App\Model\Listing\System\Page\Menu;
//use App\Model\Listing\System\Page\Page;
//use App\Model\Listing\System\Page\PageCategory;
//use App\Model\Listing\System\Page\PageRight;
//use App\Model\Listing\System\Service\Service;
//use App\Model\Listing\System\Service\ServiceTerm;
//use App\Model\Listing\System\Service\SystemService;
//use App\Model\Listing\System\SystemSetting;
//use App\Model\Listing\System\Document\DocumentGroup;
//use App\Model\Listing\System\Document\DocumentType;
//use App\Model\Listing\System\Location\City;
//use App\Model\Listing\System\Location\Country;
//use App\Model\Listing\System\Location\District;
//use App\Model\Listing\System\Location\State;
//use App\Model\Listing\System\SerialCode;
//use App\Model\Listing\System\SystemTable;
//use App\Model\Listing\System\TransportModule;


class ListingController extends AbstractBaseController
{
    /**
     * Property to store the base path of the page.
     *
     * @var string $pageCategory
     */
    private static $pageCategory = 'Listing';


//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function jd()
//    {
//        $model = new JobDeposit(request()->all());
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function pageRight()
//    {
//        $model = new PageRight(request()->all());
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function salesInvoice()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Finance\Sales\SalesInvoice', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function purchaseInvoice()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Finance\Purchase\Invoice', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function cashMutation()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashMutation', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function cashAdvance()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashAdvance', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function topUp()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'PettyCash\CashTopUp', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function cashAccount()
//    {
//        $model = new CashAccount(request()->all());
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function paymentMethod()
//    {
//        $model = new PaymentMethod(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function paymentTerms()
//    {
//        $model = new PaymentTerms(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function costCodeGroup()
//    {
//        $model = new CostCodeGroup(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function joWhUnBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobUnBundling', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function joWhBundling()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Warehouse\JobBundling', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the detail of job order.
//     *
//     * @return mixed
//     */
//    public function joTrucking()
//    {
//        $model = $this->loadModel(self::$pageCategory, 'Job\Trucking\JoTrucking', request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function document()
//    {
//        $model = new Document(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function so()
//    {
//        $model = new SalesOrder(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function soHistory()
//    {
//        $model = new SalesOrderHistory(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function joHistory()
//    {
//        $model = new JoHistory(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function jobOrder()
//    {
//        $model = new JobOrder(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function apiAccess()
//    {
//        $model = new ApiAccess(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of service term document.
//     *
//     * @return mixed
//     */
//    public function serviceTermDocument()
//    {
//        $model = new ServiceTermDocument(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function goodsCauseDamage()
//    {
//        $model = new GoodsCauseDamage(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of stock adjustment type.
//     *
//     * @return mixed
//     */
//    public function stockAdjustmentType()
//    {
//        $model = new Warehouse\StockAdjustmentType(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of goods damage type.
//     *
//     * @return mixed
//     */
//    public function goodsDamageType()
//    {
//        $model = new GoodsDamageType(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the listing of warehouse stock adjustment.
//     *
//     * @return mixed
//     */
//    public function joWhStockAdjustment()
//    {
//        $model = new JobStockAdjustment(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhStockMovement()
//    {
//        $model = new JobStockMovement(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of warehouse stock movement.
//     *
//     * @return mixed
//     */
//    public function joWhStockTransfer()
//    {
//        $model = new JobStockTransfer(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//
//    /**
//     * The function to load the listing of warehouse Opname.
//     *
//     * @return mixed
//     */
//    public function joWhOpname()
//    {
//        $model = new StockOpname(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of warehouse Outbound.
//     *
//     * @return mixed
//     */
//    public function joWhOutbound()
//    {
//        $model = new JobOutbound(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of warehouse Inbound.
//     *
//     * @return mixed
//     */
//    public function joWhInbound()
//    {
//        $model = new JobInbound(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of active system action.
//     *
//     * @return mixed
//     */
//    public function systemAction()
//    {
//        $model = new SystemAction(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of active service customer.
//     *
//     * @return mixed
//     */
//    public function activeService()
//    {
//        $model = new \App\Model\Listing\Setting\SystemService(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of warehouse.
//     *
//     * @return mixed
//     */
//    public function warehouse()
//    {
//        $model = new Warehouse(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of Equipment.
//     *
//     * @return mixed
//     */
//    public function equipment()
//    {
//        $model = new Equipment(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of Equipment Group.
//     *
//     * @return mixed
//     */
//    public function equipmentGroup()
//    {
//        $model = new EquipmentGroup(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of Container.
//     *
//     * @return mixed
//     */
//    public function container()
//    {
//        $model = new Container(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of Transport Module.
//     *
//     * @return mixed
//     */
//    public function transportModule()
//    {
//        $model = new TransportModule(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of relation.
//     *
//     * @return mixed
//     */
//    public function relation()
//    {
//        $model = new Relation(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of users.
//     *
//     * @return mixed
//     */
//    public function user()
//    {
//        $model = new User(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of System Table.
//     *
//     * @return mixed
//     */
//    public function switchSystem()
//    {
//        $model = new SwitchSystem(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of System Table.
//     *
//     * @return mixed
//     */
//    public function systemTable()
//    {
//        $model = new SystemTable(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of document group.
//     *
//     * @return mixed
//     */
//    public function userGroup()
//    {
//        $model = new UserGroup(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of document group.
//     *
//     * @return mixed
//     */
//    public function documentGroup()
//    {
//        $model = new DocumentGroup(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of document type.
//     *
//     * @return mixed
//     */
//    public function documentType()
//    {
//        $model = new DocumentType(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of district.
//     *
//     * @return mixed
//     */
//    public function district()
//    {
//        $model = new District(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of city.
//     *
//     * @return mixed
//     */
//    public function city()
//    {
//        $model = new City(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of state.
//     *
//     * @return mixed
//     */
//    public function state()
//    {
//        $model = new State(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of country.
//     *
//     * @return mixed
//     */
//    public function country()
//    {
//        $model = new Country(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of menu.
//     *
//     * @return mixed
//     */
//    public function serialNumber()
//    {
//        $model = new SerialNumber(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of menu.
//     *
//     * @return mixed
//     */
//    public function serialCode()
//    {
//        $model = new SerialCode(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of menu.
//     *
//     * @return mixed
//     */
//    public function systemSetting()
//    {
//        $model = new SystemSetting(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of menu.
//     *
//     * @return mixed
//     */
//    public function menu()
//    {
//        $model = new Menu(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page Category.
//     *
//     * @return mixed
//     */
//    public function pageCategory()
//    {
//        $model = new PageCategory(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function page()
//    {
//        $model = new Page(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function service()
//    {
//        $model = new Service(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function serviceTerm()
//    {
//        $model = new ServiceTerm(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function systemService()
//    {
//        $model = new SystemService(request()->all());
//
//        return $this->doControlListing($model);
//    }
//
//    /**
//     * The function to load the listing of page.
//     *
//     * @return mixed
//     */
//    public function costCode()
//    {
//        $model = new CostCode(request()->all());
//
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//
//        return $this->doControlListing($model);
//    }
//
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
//    }
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
//        return $this->doControlListing($model);
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
//        return $this->doControlListing($model);
//    }
}
