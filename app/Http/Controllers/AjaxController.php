<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:03
 */

namespace App\Http\Controllers;


//use App\Model\Ajax\Crm\Relation\ContactPerson;
//use App\Model\Ajax\Crm\Relation\Office;
//use App\Model\Ajax\Crm\Relation\RelationBank;
//use App\Model\Ajax\CustomerService\SalesOrder;
//use App\Model\Ajax\CustomerService\SalesOrderDetail;
//use App\Model\Ajax\Finance\Purchase\JobDepositDetail;
//use App\Model\Ajax\Finance\Sales\SalesInvoiceDetail;
//use App\Model\Ajax\Fms\EquipmentMeter;
//use App\Model\Ajax\Fms\Master\RenewalType;
//use App\Model\Ajax\Fms\Master\ServiceTask;
//use App\Model\Ajax\Fms\OwnershipType;
//use App\Model\Ajax\Fms\RenewalOrderCost;
//use App\Model\Ajax\Fms\RenewalOrderDetail;
//use App\Model\Ajax\Fms\ServiceOrderCost;
//use App\Model\Ajax\Fms\ServiceOrderDetail;
//use App\Model\Ajax\Job\Inklaring\JobInklaringRelease;
//use App\Model\Ajax\Job\JobContainer;
//use App\Model\Ajax\Job\JobGoods;
//use App\Model\Ajax\Job\JobOrder;
//use App\Model\Ajax\Job\JobPurchase;
//use App\Model\Ajax\Job\JobSales;
//use App\Model\Ajax\Job\JobOfficer;
//use App\Model\Ajax\Job\Warehouse\Bundling\JobBundlingDetail;
//use App\Model\Ajax\Job\Warehouse\JobAdjustmentDetail;
//use App\Model\Ajax\Job\Warehouse\JobInboundDetail;
//use App\Model\Ajax\Job\Warehouse\JobInboundReceive;
//use App\Model\Ajax\Job\Warehouse\JobInboundDamage;
//use App\Model\Ajax\Job\Warehouse\JobMovementDetail;
//use App\Model\Ajax\Job\Warehouse\JobOutboundDetail;
//use App\Model\Ajax\Job\Warehouse\JobStockTransferGoods;
//use App\Model\Ajax\Job\Warehouse\StockOpname;
//use App\Model\Ajax\Job\Warehouse\StockOpnameDetail;
//use App\Model\Ajax\Master\Finance\Bank;
//use App\Model\Ajax\Master\Finance\PaymentMethod;
//use App\Model\Ajax\PettyCash\CashAccount;
//use App\Model\Ajax\Master\Goods\Brand;
//use App\Model\Ajax\Crm\Relation\Relation;
//use App\Model\Ajax\CustomerService\SalesOrderSales;
//use App\Model\Ajax\Job\Trucking\JobTruckingDetail;
//use App\Model\Ajax\Master\CostCodeService;
//use App\Model\Ajax\Master\Equipment;
//use App\Model\Ajax\Master\Finance\CostCodeGroup;
//use App\Model\Ajax\Master\Finance\CostCode;
//use App\Model\Ajax\Master\Finance\Tax;
//use App\Model\Ajax\Master\Finance\TaxDetail;
//use App\Model\Ajax\Master\Goods\Goods;
//use App\Model\Ajax\Master\Goods\GoodsCategory;
//use App\Model\Ajax\Master\Goods\GoodsCauseDamage;
//use App\Model\Ajax\Master\Goods\GoodsDamageType;
//use App\Model\Ajax\Master\Goods\GoodsMaterial;
//use App\Model\Ajax\Master\Goods\GoodsPrefix;
//use App\Model\Ajax\Master\Goods\GoodsUnit;
//use App\Model\Ajax\PettyCash\CashAdvance;
//use App\Model\Ajax\Setting\Action\SystemActionEvent;
//use App\Model\Ajax\Setting\Dashboard;
//use App\Model\Ajax\Setting\DashboardDetail;
//use App\Model\Ajax\Setting\DashboardItem;
//use App\Model\Ajax\System\CustomsDocumentType;
//use App\Model\Ajax\System\Document\Document;
//use App\Model\Ajax\System\EquipmentGroup;
//use App\Model\Ajax\Master\QuotationDetail;
//use App\Model\Ajax\Master\Unit;
//use App\Model\Ajax\Master\Warehouse;
//use App\Model\Ajax\Master\WarehouseStorage;
//use App\Model\Ajax\System\Container;
//use App\Model\Ajax\System\Currency;
//use App\Model\Ajax\System\Access\User;
//use App\Model\Ajax\System\Access\UserGroup;
//use App\Model\Ajax\System\Location\Port;
//use App\Model\Ajax\System\Page\Menu;
//use App\Model\Ajax\System\Page\Page;
//use App\Model\Ajax\System\Page\PageNotification;
//use App\Model\Ajax\System\Page\PageRight;
//use App\Model\Ajax\System\Service\Action;
//use App\Model\Ajax\System\Service\Service;
//use App\Model\Ajax\System\Service\ServiceTerm;
//use App\Model\Ajax\System\Service\SystemService;
//use App\Model\Ajax\System\SystemSetting;
//use App\Model\Ajax\System\Document\DocumentGroup;
//use App\Model\Ajax\System\Document\DocumentType;
//use App\Model\Ajax\System\Location\City;
//use App\Model\Ajax\System\Location\Country;
//use App\Model\Ajax\System\Location\District;
//use App\Model\Ajax\System\Location\State;
//use App\Model\Ajax\System\SerialCode;
//use App\Model\Ajax\System\SystemTable;
//use App\Model\Ajax\System\TransportModule;

class AjaxController extends AbstractBaseController
{

//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function jdd()
//    {
//        $model = new JobDepositDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function page()
//    {
//        $model = new Page(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function paymentMethod()
//    {
//        $model = new PaymentMethod(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function salesInvoiceDetail()
//    {
//        $model = new SalesInvoiceDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function so()
//    {
//        $model = new SalesOrder(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function cashAdvance()
//    {
//        $model = new CashAdvance(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function bank()
//    {
//        $model = new Bank(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function relationBank()
//    {
//        $model = new RelationBank(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job bundling detail.
//     *
//     * @return mixed
//     */
//    public function jobBundlingDetail()
//    {
//        $model = new JobBundlingDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function goodsMaterial()
//    {
//        $model = new GoodsMaterial(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page cash account.
//     *
//     * @return mixed
//     */
//    public function cashAccount()
//    {
//        $model = new CashAccount(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function soSales()
//    {
//        $model = new SalesOrderSales(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function taxDetail()
//    {
//        $model = new TaxDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function costCodeGroup()
//    {
//        $model = new CostCodeGroup(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function jobTruckingDetail()
//    {
//        $model = new JobTruckingDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function goodsPrefix()
//    {
//        $model = new GoodsPrefix(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function soDetail()
//    {
//        $model = new SalesOrderDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function document()
//    {
//        $model = new Document(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of stock opname detail.
//     *
//     * @return mixed
//     */
//    public function stockOpnameDetail()
//    {
//        $model = new StockOpnameDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function jobAdjustmentDetail()
//    {
//        $model = new JobAdjustmentDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function joWhOpname()
//    {
//        $model = new StockOpname(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function jobMovementDetail()
//    {
//        $model = new JobMovementDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function jobOutboundDetail()
//    {
//        $model = new JobOutboundDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of goods cause damage.
//     *
//     * @return mixed
//     */
//    public function goodsCauseDamage()
//    {
//        $model = new GoodsCauseDamage(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of stock adjustment type.
//     *
//     * @return mixed
//     */
//    public function stockAdjustmentType()
//    {
//        $model = new Warehouse\StockAdjustmentType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job warehouse damage.
//     *
//     * @return mixed
//     */
//    public function jobInboundDamage()
//    {
//        $model = new JobInboundDamage(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of job warehouse goods.
//     *
//     * @return mixed
//     */
//    public function jobInboundReceive()
//    {
//        $model = new JobInboundReceive(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of goods damage type.
//     *
//     * @return mixed
//     */
//    public function goodsDamageType()
//    {
//        $model = new GoodsDamageType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of system action event.
//     *
//     * @return mixed
//     */
//    public function jobOfficer()
//    {
//        $model = new JobOfficer(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of system action event.
//     *
//     * @return mixed
//     */
//    public function systemActionEvent()
//    {
//        $model = new SystemActionEvent(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of action.
//     *
//     * @return mixed
//     */
//    public function action()
//    {
//        $model = new Action(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of goods unit.
//     *
//     * @return mixed
//     */
//    public function jobInboundDetail()
//    {
//        $model = new JobInboundDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of goods unit.
//     *
//     * @return mixed
//     */
//    public function goods()
//    {
//        $model = new Goods(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of goods unit.
//     *
//     * @return mixed
//     */
//    public function jobGoods()
//    {
//        $model = new JobGoods(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of goods unit.
//     *
//     * @return mixed
//     */
//    public function goodsUnit()
//    {
//        $model = new GoodsUnit(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of warehouse storage.
//     *
//     * @return mixed
//     */
//    public function warehouseStorage()
//    {
//        $model = new WarehouseStorage(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of warehouse.
//     *
//     * @return mixed
//     */
//    public function warehouse()
//    {
//        $model = new Warehouse(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of equipment.
//     *
//     * @return mixed
//     */
//    public function equipment()
//    {
//        $model = new Equipment(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of container.
//     *
//     * @return mixed
//     */
//    public function container()
//    {
//        $model = new Container(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of user.
//     *
//     * @return mixed
//     */
//    public function equipmentGroup()
//    {
//        $model = new EquipmentGroup(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of user.
//     *
//     * @return mixed
//     */
//    public function user()
//    {
//        $model = new User(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of user group.
//     *
//     * @return mixed
//     */
//    public function userGroup()
//    {
//        $model = new UserGroup(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of contact person.
//     *
//     * @return mixed
//     */
//    public function contactPerson()
//    {
//        $model = new ContactPerson(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of document Group.
//     *
//     * @return mixed
//     */
//    public function office()
//    {
//        $model = new Office(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of document Group.
//     *
//     * @return mixed
//     */
//    public function documentGroup()
//    {
//        $model = new DocumentGroup(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of document type.
//     *
//     * @return mixed
//     */
//    public function documentType()
//    {
//        $model = new DocumentType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of district.
//     *
//     * @return mixed
//     */
//    public function systemTable()
//    {
//        $model = new SystemTable(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of district.
//     *
//     * @return mixed
//     */
//    public function district()
//    {
//        $model = new District(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of city.
//     *
//     * @return mixed
//     */
//    public function city()
//    {
//        $model = new City(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of state.
//     *
//     * @return mixed
//     */
//    public function state()
//    {
//        $model = new State(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of menu.
//     *
//     * @return mixed
//     */
//    public function country()
//    {
//        $model = new Country(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of menu.
//     *
//     * @return mixed
//     */
//    public function relation()
//    {
//        $model = new Relation(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of menu.
//     *
//     * @return mixed
//     */
//    public function serialCode()
//    {
//        $model = new SerialCode(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of menu.
//     *
//     * @return mixed
//     */
//    public function systemSetting()
//    {
//        $model = new SystemSetting(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of menu.
//     *
//     * @return mixed
//     */
//    public function menu()
//    {
//        $model = new Menu(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of page right.
//     *
//     * @return mixed
//     */
//    public function pageRight()
//    {
//        $model = new PageRight(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function pageNotification()
//    {
//        $model = new PageNotification(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function service()
//    {
//        $model = new Service(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function serviceTerm()
//    {
//        $model = new ServiceTerm(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function systemService()
//    {
//        $model = new SystemService(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function costCode()
//    {
//        $model = new CostCode(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function costCodeService()
//    {
//        $model = new CostCodeService(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of transport module.
//     *
//     * @return mixed
//     */
//    public function transportModule()
//    {
//        $model = new TransportModule(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function currency()
//    {
//        $model = new Currency(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function tax()
//    {
//        $model = new Tax(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function unit()
//    {
//        $model = new Unit(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function port()
//    {
//        $model = new Port(request()->all());
//
//        return $this->doControlAjax($model);
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
//        return $this->doControlAjax($model);
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
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function quotationDetail()
//    {
//        $model = new QuotationDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobSales()
//    {
//        $model = new JobSales(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobPurchase()
//    {
//        $model = new JobPurchase(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobContainer()
//    {
//        $model = new JobContainer(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobInklaringRelease()
//    {
//        $model = new JobInklaringRelease(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function customsDocumentType()
//    {
//        $model = new CustomsDocumentType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobOrder()
//    {
//        $model = new JobOrder(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function ownershipType()
//    {
//        $model = new OwnershipType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function serviceTask()
//    {
//        $model = new ServiceTask(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function renewalType()
//    {
//        $model = new RenewalType(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function equipmentMeter()
//    {
//        $model = new EquipmentMeter(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function serviceOrderDetail()
//    {
//        $model = new ServiceOrderDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function serviceOrderCost()
//    {
//        $model = new ServiceOrderCost(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function renewalOrderDetail()
//    {
//        $model = new RenewalOrderDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function renewalOrderCost()
//    {
//        $model = new RenewalOrderCost(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function dashboard()
//    {
//        $model = new Dashboard(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function dashboardItem()
//    {
//        $model = new DashboardItem(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function dashboardDetail()
//    {
//        $model = new DashboardDetail(request()->all());
//
//        return $this->doControlAjax($model);
//    }
//
//    /**
//     * The function to load the ajax of page notification.
//     *
//     * @return mixed
//     */
//    public function jobStockTransferGoods()
//    {
//        $model = new JobStockTransferGoods(request()->all());
//
//        return $this->doControlAjax($model);
//    }
}
