<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Frame\Bin\Code\Routes;

use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Bin\Code\Routes
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class CheckPageRoute
{
    /**
     * Property to store listing path of page.
     *
     * @var string $ListingPath ;
     * */
    private $ListingPath = 'App\\Model\\Listing\\';
    private $DetailPath = 'App\\Model\\Detail\\';
    private $ViewerPath = 'App\\Model\\Viewer\\';
    /**
     * Property to store list route.
     * This data we get by executing file get_route_list.php
     *
     * @var array $Routes ;
     * */
    private $FieldIds = ['listing_file', 'listing_db',
        'detail_file', 'detail_db', 'view_file', 'view_db'];
    /**
     * Property to store data form database.
     *
     * @var array $Database ;
     * */
    private $Database = [];
    /**
     * Property to store list route.
     * This data we get by executing file get_route_list.php
     *
     * @var array $Routes ;
     * */
    private $Routes = [
        ['route' => 'action', 'path' => 'System/Service/Action'],
        ['route' => 'activeService', 'path' => 'Setting/SystemService'],
        ['route' => 'apiAccess', 'path' => 'System/Page/ApiAccess'],
        ['route' => 'country', 'path' => 'System/Location/Country'],
        ['route' => 'state', 'path' => 'System/Location/State'],
        ['route' => 'city', 'path' => 'System/Location/City'],
        ['route' => 'district', 'path' => 'System/Location/District'],
        ['route' => 'port', 'path' => 'System/Location/Port'],
        ['route' => 'contactPerson', 'path' => 'Relation/ContactPerson'],
        ['route' => 'container', 'path' => 'System/Container'],
        ['route' => 'currency', 'path' => 'System/Currency'],
        ['route' => 'customsClearanceType', 'path' => 'System/CustomsClearanceType'],
        ['route' => 'customsDocumentType', 'path' => 'System/CustomsDocumentType'],
        ['route' => 'documentGroup', 'path' => 'System/Document/DocumentGroup'],
        ['route' => 'documentType', 'path' => 'System/Document/DocumentType'],
        ['route' => 'document', 'path' => 'System/Document/Document'],
        ['route' => 'equipment', 'path' => 'Master/Equipment'],
        ['route' => 'eg', 'path' => 'System/EquipmentGroup'],
        ['route' => 'brand', 'path' => 'Master/Goods/Brand'],
        ['route' => 'goodsCategory', 'path' => 'Master/Goods/GoodsCategory'],
        ['route' => 'goods', 'path' => 'Master/Goods/Goods'],
        ['route' => 'goodsUnit', 'path' => 'Master/Goods/GoodsUnit'],
        ['route' => 'goodsPrefix', 'path' => 'Master/Goods/GoodsPrefix'],
        ['route' => 'goodsMaterial', 'path' => 'Master/Goods/GoodsMaterial'],
        ['route' => 'goodsCauseDamage', 'path' => 'Master/Goods/GoodsCauseDamage'],
        ['route' => 'goodsDamageType', 'path' => 'Master/Goods/GoodsDamageType'],
        ['route' => 'joHistory', 'path' => 'Job/JoHistory'],
        ['route' => 'joInklaringExport', 'path' => 'Job/Inklaring/JobInklaringExport'],
        ['route' => 'joInklaringExportContainer', 'path' => 'Job/Inklaring/JobInklaringExportContainer'],
        ['route' => 'joInklaringImport', 'path' => 'Job/Inklaring/JobInklaringImport'],
        ['route' => 'joInklaringImportContainer', 'path' => 'Job/Inklaring/JobInklaringImportContainer'],
        ['route' => 'joWhInbound', 'path' => 'Job/Warehouse/JobInbound'],
        ['route' => 'joWhOpname', 'path' => 'Job/Warehouse/StockOpname'],
        ['route' => 'joWhOutbound', 'path' => 'Job/Warehouse/JobOutbound'],
        ['route' => 'joWhStockAdjustment', 'path' => 'Job/Warehouse/JobStockAdjustment'],
        ['route' => 'joWhStockMovement', 'path' => 'Job/Warehouse/JobStockMovement'],
        ['route' => 'joWhStockTransfer', 'path' => 'Job/Warehouse/JobStockTransfer'],
        ['route' => 'jobAdjustmentDetail', 'path' => 'Job/Warehouse/JobAdjustmentDetail'],
        ['route' => 'jobContainer', 'path' => 'Job/JobContainer'],
        ['route' => 'jobGoods', 'path' => 'Job/JobGoods'],
        ['route' => 'jobInboundDamage', 'path' => 'Job/Warehouse/JobInboundDamage'],
        ['route' => 'jobInboundDetail', 'path' => 'Job/Warehouse/JobInboundDetail'],
        ['route' => 'jobInboundReceive', 'path' => 'Job/Warehouse/JobInboundReceive'],
        ['route' => 'jobInklaringRelease', 'path' => 'Job/Inklaring/JobInklaringRelease'],
        ['route' => 'jobMovementDetail', 'path' => 'Job/Warehouse/JobMovementDetail'],
        ['route' => 'jobStockTransferGoods', 'path' => 'Job/Warehouse/JobStockTransferGoods'],
        ['route' => 'jobOfficer', 'path' => 'Job/JobOfficer'],
        ['route' => 'jobOrder', 'path' => 'Job/JobOrder'],
        ['route' => 'jobOutboundDetail', 'path' => 'Job/Warehouse/JobOutboundDetail'],
        ['route' => 'jobPurchase', 'path' => 'Job/JobPurchase'],
        ['route' => 'jobSales', 'path' => 'Job/JobSales'],
        ['route' => 'menu', 'path' => 'System/Page/Menu'],
        ['route' => 'office', 'path' => 'Relation/Office'],
        ['route' => 'page', 'path' => 'System/Page/Page'],
        ['route' => 'pageRight', 'path' => 'System/Page/PageRight'],
        ['route' => 'pageCategory', 'path' => 'System/Page/PageCategory'],
        ['route' => 'relation', 'path' => 'Relation/Relation'],
        ['route' => 'serialCode', 'path' => 'System/SerialCode'],
        ['route' => 'serialNumber', 'path' => 'Setting/SerialNumber'],
        ['route' => 'service', 'path' => 'System/Service/Service'],
        ['route' => 'serviceTerm', 'path' => 'System/Service/ServiceTerm'],
        ['route' => 'serviceTermDocument', 'path' => 'Setting/ServiceTermDocument'],
        ['route' => 'so', 'path' => 'CustomerService/SalesOrder'],
        ['route' => 'soHistory', 'path' => 'CustomerService/SalesOrderHistory'],
        ['route' => 'stockAdjustmentType', 'path' => 'Master/Warehouse/StockAdjustmentType'],
        ['route' => 'stockOpnameDetail', 'path' => 'Job/Warehouse/StockOpnameDetail'],
        ['route' => 'switchSystem', 'path' => 'Setting/SwitchSystem'],
        ['route' => 'system', 'path' => 'Setting/System'],
        ['route' => 'systemAction', 'path' => 'Setting/Action/SystemAction'],
        ['route' => 'systemActionEvent', 'path' => 'Setting/Action/SystemActionEvent'],
        ['route' => 'systemService', 'path' => 'System/Service/SystemService'],
        ['route' => 'systemSetting', 'path' => 'System/SystemSetting'],
        ['route' => 'systemTable', 'path' => 'System/SystemTable'],
        ['route' => 'transportModule', 'path' => 'System/TransportModule'],
        ['route' => 'unit', 'path' => 'Master/Unit'],
        ['route' => 'user', 'path' => 'User/User'],
        ['route' => 'userGroup', 'path' => 'User/UserGroup'],
        ['route' => 'userMapping', 'path' => 'User/UserMapping'],
        ['route' => 'warehouse', 'path' => 'Master/Warehouse'],
        ['route' => 'warehouseStorage', 'path' => 'Master/WarehouseStorage'],
        ['route' => 'joTrucking', 'path' => 'Job/Trucking/JoTrucking'],
        ['route' => 'joTruck', 'path' => 'Job/Trucking/TruckingConventional'],
        ['route' => 'joTruckExp', 'path' => 'Job/Trucking/TruckingExport'],
        ['route' => 'joTruckImp', 'path' => 'Job/Trucking/TruckingImport'],
        ['route' => 'jtd', 'path' => 'Job/Trucking/JobTruckingDetail'],
        ['route' => 'joWhBundling', 'path' => 'Job/Warehouse/Bundling/JobBundling'],
        ['route' => 'joWhUnBundling', 'path' => 'Job/Warehouse/Bundling/JobUnBundling'],
        ['route' => 'jbd', 'path' => 'Job/Warehouse/Bundling/JobBundlingDetail'],
        ['route' => 'jbd', 'path' => 'Job/Warehouse/Bundling/JobBundlingDetail'],
        ['route' => 'jobBundlingMaterial', 'path' => 'Job/Warehouse/Bundling/JobBundlingMaterial'],
        ['route' => 'costCodeGroup', 'path' => 'Master/Finance/CostCodeGroup'],
        ['route' => 'costCode', 'path' => 'Master/Finance/CostCode'],
        ['route' => 'tax', 'path' => 'Master/Finance/Tax'],
        ['route' => 'taxDetail', 'path' => 'Master/Finance/TaxDetail'],
        ['route' => 'paymentTerms', 'path' => 'Master/Finance/PaymentTerms'],
        ['route' => 'paymentMethod', 'path' => 'Master/Finance/PaymentMethod'],
        ['route' => 'soSales', 'path' => 'CustomerService/SalesOrderSales'],
        ['route' => 'bank', 'path' => 'Master/Finance/Bank'],
        ['route' => 'relationBank', 'path' => 'Relation/RelationBank'],
        ['route' => 'cashAccount', 'path' => 'PettyCash/CashAccount'],
        ['route' => 'topUp', 'path' => 'PettyCash\CashTopUp'],
        ['route' => 'cashAdvance', 'path' => 'PettyCash\CashAdvance'],
        ['route' => 'cashMutation', 'path' => 'PettyCash\CashMutation'],
        ['route' => 'purchaseInvoice', 'path' => 'Finance/Purchase/PurchaseInvoice'],
        ['route' => 'salesInvoice', 'path' => 'Finance/Sales/SalesInvoice'],
        ['route' => 'sid', 'path' => 'Finance/Sales/SalesInvoiceDetail'],
        ['route' => 'jd', 'path' => 'Finance/Purchase/JobDeposit'],
        ['route' => 'jdd', 'path' => 'Finance/Purchase/JobDepositDetail'],
        ['route' => 'serviceOrder', 'path' => 'Fms/ServiceOrder'],
        ['route' => 'serviceReminder', 'path' => 'Fms/ServiceReminder'],
        ['route' => 'serviceTask', 'path' => 'Fms/Master/ServiceTask'],
        ['route' => 'renewalType', 'path' => 'Fms/Master/RenewalType'],
        ['route' => 'equipmentUsage', 'path' => 'Fms/EquipmentUsage'],
        ['route' => 'equipmentFuel', 'path' => 'Fms/EquipmentFuel'],
        ['route' => 'renewalReminder', 'path' => 'Fms/RenewalReminder'],
        ['route' => 'renewalOrder', 'path' => 'Fms/RenewalOrder'],
        ['route' => 'ownershipType', 'path' => 'System/OwnershipType'],
        ['route' => 'equipmentMeter', 'path' => 'Fms/EquipmentMeter'],
        ['route' => 'serviceOrderDetail', 'path' => 'Fms/ServiceOrderDetail'],
        ['route' => 'serviceOrderCost', 'path' => 'Fms/ServiceOrderCost'],
        ['route' => 'renewalOrderDetail', 'path' => 'Fms/RenewalOrderDetail'],
        ['route' => 'renewalOrderCost', 'path' => 'Fms/RenewalOrderCost'],
        ['route' => 'dashboard', 'path' => 'Setting/Dashboard'],
        ['route' => 'dashboardDetail', 'path' => 'Setting/DashboardDetail'],
        ['route' => 'dashboardItem', 'path' => 'System/DashboardItem'],
        ['route' => 'bank', 'path' => 'Master/Finance/Bank'],
        ['route' => 'documentTemplateType', 'path' => 'System/Document/DocumentTemplateType'],
        ['route' => 'documentTemplate', 'path' => 'System/Document/DocumentTemplate'],
        ['route' => 'documentSignature', 'path' => 'Setting/DocumentSignature'],
        ['route' => 'rd', 'path' => 'Job/Trucking/RouteDelivery'],
        ['route' => 'sty', 'path' => 'System/SystemType'],
        ['route' => 'soi', 'path' => 'CustomerService/SalesOrderIssue'],
        ['route' => 'hpp', 'path' => 'Job/Trucking/HppCalculator'],
        ['route' => 'prc', 'path' => 'Crm/Price'],
        ['route' => 'prd', 'path' => 'Crm/PriceDetail'],
    ];

    /**
     * CheckPageRoute constructor.
     */
    public function __construct()
    {
        $this->loadDatabase();
        $this->doValidate();
    }


    public function getRoutes(): array
    {
        return $this->Routes;
    }

    public function printTable($showAll = true): void
    {
        $results = '<table width="100%" border="1">';
        $results .= '<thead>';
        $results .= '<tr>';
        $results .= '<th rowspan="2">Route</th>';
        $results .= '<th rowspan="2">Path</th>';
        $results .= '<th colspan="2">Listing</th>';
        $results .= '<th colspan="2">Detail</th>';
        $results .= '<th colspan="2">Viewer</th>';
        $results .= '</tr>';
        $results .= '<tr>';
        $results .= '<th>File</th>';
        $results .= '<th>DB</th>';
        $results .= '<th>File</th>';
        $results .= '<th>DB</th>';
        $results .= '<th>File</th>';
        $results .= '<th>DB</th>';
        $results .= '</tr>';
        $results .= '</thead>';
        $results .= '<tbody>';
        foreach ($this->Routes as $row) {
            $results .= $this->getTableRow($row, $showAll);
        }
        $results .= '</tbody>';
        $results .= '</table>';
        echo $results;
    }

    private function getTableRow($row, $showAll): string
    {
        $valid = true;
        if ($row['listing_file'] !== $row['listing_db'] ||
            $row['detail_file'] !== $row['detail_db'] || $row['view_file'] !== $row['view_db']) {
            $valid = false;
        }
        if ($showAll === false && $valid === true) {
            return '';
        }
        $result = '<td>' . $row['route'] . '</td>';
        $result .= '<td>' . $row['path'] . '</td>';
        foreach ($this->FieldIds as $field) {
            $text = '';
            $style = '';
            if (array_key_exists($field, $row) === true) {
                $val = $row[$field];
                if (is_bool($val) === true) {
                    $text = 'N';
                    $style = 'style="background-color: red; color: white; text-align: center;"';
                    if ($val === true) {
                        $text = 'Y';
                        $style = 'style="background-color: green; color: white; text-align: center;"';
                    }
                } else {
                    $text = $val;
                }
            }
            $result .= '<td ' . $style . '>' . $text . '</td>';
        }

        return '<tr>' . $result . '</tr>';
    }

    public function doValidate(): void
    {
        $lengthRoute = count($this->Routes);
        for ($i = 0; $i < $lengthRoute; $i++) {
            $path = str_replace('/', '\\', $this->Routes[$i]['path']);
            $this->Routes[$i]['listing_file'] = class_exists($this->ListingPath . $path);
            $this->Routes[$i]['detail_file'] = class_exists($this->DetailPath . $path);
            $this->Routes[$i]['view_file'] = class_exists($this->ViewerPath . $path);
            $this->Routes[$i]['listing_db'] = $this->checkDb($this->Routes[$i]['route'], 'listing');
            $this->Routes[$i]['detail_db'] = $this->checkDb($this->Routes[$i]['route'], 'detail');
            $this->Routes[$i]['view_db'] = $this->checkDb($this->Routes[$i]['route'], 'viewer');
        }
    }

    public function checkDb($route, $pc): bool
    {
        $result = false;
        if (array_key_exists($route, $this->Database) === true && array_key_exists($pc, $this->Database[$route]) === true) {
            return true;
        }
        return $result;
    }

    private function loadDatabase(): void
    {
        $query = 'select pg.pg_id, pg.pg_route, lower(pc.pc_name) as pg_cateory
                    from page as pg INNER JOIN
                        page_category as pc on pg.pg_pc_id = pc.pc_id
                        where pc.pc_id IN (2, 3, 4)
                    ORDER BY pg.pg_route, pg.pg_id';
        $sqlQuery = DB::select($query);
        $this->Database = [];
        foreach ($sqlQuery as $row) {
            if (array_key_exists($row->pg_route, $this->Database) === false) {
                $this->Database[$row->pg_route] = [];
            }
            $this->Database[$row->pg_route][$row->pg_cateory] = true;
        }
    }
}
