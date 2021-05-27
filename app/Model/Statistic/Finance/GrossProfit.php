<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Finance;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\JobOrderDao;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class GrossProfit extends AbstractStatisticModel
{
    /**
     * Property to store number object
     *
     * @param NumberFormatter $Number
     */
    private $Number;
    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $DtParser;

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'grossProfit');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Customer Field
        $customerField = $this->Field->getSingleSelect('relation', 'customer', $this->getStringParameter('customer'));
        $customerField->setHiddenField('customer_id', $this->getIntParameter('customer_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableDetailButton(false);
        $customerField->setEnableNewButton(false);

        # Create View Field
        $viewField = $this->Field->getRadioGroup('view_by', $this->getStringParameter('view_by', 'so'));
        $viewField->addRadios([
            'so' => Trans::getFinanceWord('salesOrder'),
            'jo' => Trans::getFinanceWord('jobOrder'),
        ]);

        $this->StatisticForm->addField(Trans::getFinanceWord('customer'), $customerField);
        $this->StatisticForm->addField(Trans::getFinanceWord('customerRef'), $this->Field->getText('customer_ref', $this->getStringParameter('customer_ref')));
        $this->StatisticForm->addField(Trans::getFinanceWord('dateFrom'), $this->Field->getCalendar('date_from', $this->getStringParameter('date_from')));
        $this->StatisticForm->addField(Trans::getFinanceWord('dateUntil'), $this->Field->getCalendar('date_until', $this->getStringParameter('date_until')));
        $this->StatisticForm->addField(Trans::getFinanceWord('internalRef'), $this->Field->getText('internal_ref', $this->getStringParameter('internal_ref')));
        $this->StatisticForm->addField(Trans::getFinanceWord('viewBy'), $viewField);
        $this->StatisticForm->addField(Trans::getFinanceWord('showReimburse'), $this->Field->getYesNo('reimburse', $this->getStringParameter('reimburse', 'N')));
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->Number = new NumberFormatter();
        $this->DtParser = new DateTimeParser();
        $this->addContent('result', $this->getResultPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('date_from');
        $this->Validation->checkRequire('date_until');
        $this->Validation->checkDate('date_from');
        if ($this->isValidParameter('date_from') === true) {
            $this->Validation->checkDate('date_until', '', $this->getStringParameter('date_from'));
        }
    }

    /**
     * Function to get the stock card table.
     *
     * @return Portlet
     */
    private function getResultPortlet(): Portlet
    {
        $isExport = false;
        if ($this->getFormAction() === 'doExportXls') {
            $isExport = true;
            $table = $this->getTableForExport();
        } else {
            $table = $this->getTable();
        }

        $isReimburse = false;
        if ($this->getStringParameter('reimburse', 'N') === 'Y') {
            $isReimburse = true;
        }
        if ($this->getStringParameter('view_by', 'so') === 'so') {
            $data = $this->doPrepareSoData();
        } else {
            $data = $this->doPrepareJoData();
        }

        $rows = [];
        if (empty($data) === false) {
            $i = 0;
            $total = [
                'so_customer' => '',
                'so_date' => '',
                'jo_srv_name' => '',
                'in_ref' => '',
                'customer_ref' => '',
                'jo_vendor' => '',
                'jos_amount' => 0.0,
                'jos_reimburse' => 0.0,
                'jos_invoiced' => 0.0,
                'jos_paid' => 0.0,
                'jop_amount' => 0.0,
                'jop_reimburse' => 0.0,
                'jop_invoiced' => 0.0,
                'jop_paid' => 0.0,
                'jo_margin' => 0.0,
                'jo_percentage' => 0.0,
            ];
            foreach ($data as $row) {
                $margin = $row['jos_amount'] - $row['jop_amount'];
                $percentage = 0.0;
                $row['jo_margin'] = $margin;
                if ($row['jos_amount'] !== 0.0) {
                    $percentage = ($margin / $row['jos_amount']) * 100;
                }
                $row['jo_percentage'] = $percentage;

                $row = $this->setAmount($row, $isReimburse);

                if ($isExport === false) {
                    $this->setTableStyle($table, $row, $i);
                    $total['jos_amount'] += $row['jos_amount'];
                    $total['jos_reimburse'] += $row['jos_reimburse'];
                    $total['jos_invoiced'] += $row['jos_invoiced'];
                    $total['jos_paid'] += $row['jos_paid'];
                    $total['jop_amount'] += $row['jop_amount'];
                    $total['jop_reimburse'] += $row['jop_reimburse'];
                    $total['jop_invoiced'] += $row['jop_invoiced'];
                    $total['jop_paid'] += $row['jop_paid'];
                    $total['jo_margin'] += $margin;
                }
                $i++;
                $rows[] = $row;
            }
            if ($isExport === false) {
                $total['jo_percentage'] = 0.0;
                if ($total['jos_amount'] !== 0.0) {
                    $total['jo_percentage'] = ($total['jo_margin'] / $total['jos_amount']) * 100;
                }
                $row = $this->setAmount($total, $isReimburse, 'font-weight: bold;');
                $rows[] = $row;
            }
        }
        $table->addRows($rows);
        $table->addColumnAtTheEnd('btn_action', Trans::getWord('action'));
        $table->addColumnAttribute('btn_action', 'style', 'text-align: center;');

        $portlet = new Portlet('ResPtl', 'Results');
        $portlet->addTable($table);
        $this->addDatas('ResPtl', $portlet);

        return $portlet;
    }

    /**
     * Function to get the stock card table.
     *
     * @param array $row To store the table
     * @param bool $showReimburse To trigger reimburse value
     * @param string $style To store table style.
     *
     * @return array
     */
    private function setAmount(array $row, bool $showReimburse, $style = ''): array
    {
        # Sales
        $sales = [];
        $sales[] = [
            'label' => Trans::getFinanceWord('revenue'),
            'value' => $this->Number->doFormatFloat($row['jos_amount']),
        ];
        if ($showReimburse) {
            $sales[] = [
                'label' => Trans::getFinanceWord('reimburse'),
                'value' => $this->Number->doFormatFloat($row['jos_reimburse']),
            ];
        }
        $sales[] = [
            'label' => Trans::getFinanceWord('invoiced'),
            'value' => $this->Number->doFormatFloat($row['jos_invoiced']),
        ];
        $sales[] = [
            'label' => Trans::getFinanceWord('paid'),
            'value' => $this->Number->doFormatFloat($row['jos_paid']),
        ];
        $row['sales'] = StringFormatter::generateKeyValueTableView($sales, 'label', 'value', false, $style);
        # Purchase
        $purchase = [];
        $purchase[] = [
            'label' => Trans::getFinanceWord('cogs'),
            'value' => $this->Number->doFormatFloat($row['jop_amount']),
        ];
        if ($showReimburse) {
            $purchase[] = [
                'label' => Trans::getFinanceWord('reimburse'),
                'value' => $this->Number->doFormatFloat($row['jop_reimburse']),
            ];
        }
        $purchase[] = [
            'label' => Trans::getFinanceWord('invoiced'),
            'value' => $this->Number->doFormatFloat($row['jop_invoiced']),
        ];
        $purchase[] = [
            'label' => Trans::getFinanceWord('paid'),
            'value' => $this->Number->doFormatFloat($row['jop_paid']),
        ];
        $row['purchase'] = StringFormatter::generateKeyValueTableView($purchase, 'label', 'value', false, $style);
        $row['margin'] = StringFormatter::generateTableView([
            $this->Number->doFormatFloat($row['jo_margin']),
            $this->Number->doFormatFloat($row['jo_percentage']) . '%',
        ], 'text-align: right;' . $style);

        return $row;
    }

    /**
     * Function to get the stock card table.
     *
     * @param Table $table To store the table
     * @param array $row To store the index
     * @param int $i To store the index
     *
     * @return void
     */
    private function setTableStyle(Table $table, array $row, $i): void
    {
        if ($row['jos_amount'] === 0.0) {
            $table->addCellAttribute('sales', $i, 'style', 'text-align: right; background-color: red; color: white;');
        } else {
            if (($row['jos_invoiced'] !== ($row['jos_amount'] + $row['jos_reimburse'])) || ($row['jos_paid'] !== $row['jos_invoiced'])) {
                $table->addCellAttribute('sales', $i, 'style', 'text-align: right; background-color: yellow;');
            }
        }
        if ($row['jop_amount'] === 0.0) {
            $table->addCellAttribute('purchase', $i, 'style', 'text-align: right; background-color: red; color: white;');
        } else {
            if (($row['jop_invoiced'] !== ($row['jop_amount'] + $row['jop_reimburse'])) || ($row['jop_paid'] !== $row['jop_invoiced'])) {
                $table->addCellAttribute('purchase', $i, 'style', 'text-align: right; background-color: yellow;');
            }
        }
        if ($row['jo_margin'] === 0.0) {
            $table->addCellAttribute('margin', $i, 'style', 'background-color: #34495E; color: white;');
        }
        if ($row['jo_margin'] < 0.0) {
            $table->addCellAttribute('margin', $i, 'style', 'background-color: red; color: white;');
        }
    }

    /**
     * Function to get the stock card table.
     *
     * @return Table
     */
    private function getTable(): Table
    {
        $table = new Table('ResTbl');
        $table->setHeaderRow([
            'so_customer' => Trans::getFinanceWord('customer'),
            'jo_srv_name' => Trans::getFinanceWord('service'),
            'in_ref' => Trans::getFinanceWord('internalRef'),
            'customer_ref' => Trans::getFinanceWord('customerRef'),
            'so_date' => Trans::getFinanceWord('date'),
            'sales' => Trans::getFinanceWord('sales'),
            'jo_vendor' => Trans::getFinanceWord('vendor'),
            'purchase' => Trans::getFinanceWord('purchase'),
            'margin' => Trans::getFinanceWord('margin'),
        ]);
        if ($this->getStringParameter('view_by', 'so') === 'so') {
            $table->removeColumn('jo_vendor');
            $table->removeColumn('so_date');
            $table->removeColumn('in_ref');
            $table->removeColumn('jo_srv_name');
            $table->addColumnAfter('so_customer', 'so_party', Trans::getFinanceWord('party'));
            $table->addColumnAfter('so_party', 'in_ref', Trans::getFinanceWord('soNumber'));
            $table->addColumnAfter('customer_ref', 'so_date', Trans::getFinanceWord('orderDate'));
        }
        return $table;
    }

    /**
     * Function to get the stock card table.
     *
     * @return Table
     */
    private function getTableForExport(): Table
    {
        $table = new Table('ResTbl');
        $table->setHeaderRow([
            'so_customer' => Trans::getFinanceWord('customer'),
            'so_date' => Trans::getFinanceWord('date'),
            'jo_srv_name' => Trans::getFinanceWord('service'),
            'in_ref' => Trans::getFinanceWord('internalRef'),
            'customer_ref' => Trans::getFinanceWord('customerRef'),
            'jos_amount' => Trans::getFinanceWord('revenue'),
            'jos_reimburse' => Trans::getFinanceWord('reimburse'),
            'jos_invoiced' => Trans::getFinanceWord('invoiced'),
            'jos_paid' => Trans::getFinanceWord('paid'),
            'jo_vendor' => Trans::getFinanceWord('vendor'),
            'jop_amount' => Trans::getFinanceWord('cogs'),
            'jop_reimburse' => Trans::getFinanceWord('reimburse'),
            'jop_invoiced' => Trans::getFinanceWord('invoiced'),
            'jop_paid' => Trans::getFinanceWord('paid'),
            'jo_margin' => Trans::getFinanceWord('margin'),
            'jo_percentage' => Trans::getFinanceWord('percentage') . ' (%)',
        ]);
        if ($this->getStringParameter('view_by', 'so') === 'so') {
            $table->removeColumn('jo_vendor');
            $table->removeColumn('so_date');
            $table->removeColumn('in_ref');
            $table->removeColumn('jo_srv_name');
            $table->addColumnAfter('so_customer', 'so_party', Trans::getFinanceWord('party'));
            $table->addColumnAfter('so_party', 'in_ref', Trans::getFinanceWord('soNumber'));
            $table->addColumnAfter('customer_ref', 'so_date', Trans::getFinanceWord('orderDate'));
        } else {
            $table->addColumnAttribute('jo_vendor', 'style', 'text-align: center;');
        }
        if ($this->getStringParameter('reimburse', 'N') === 'N') {
            $table->removeColumn('jos_reimburse');
            $table->removeColumn('jop_reimburse');
        } else {
            $table->setColumnType('jos_reimburse', 'float');
            $table->setColumnType('jop_reimburse', 'float');
            $table->setFooterType('jos_reimburse', 'SUM');
            $table->setFooterType('jop_reimburse', 'SUM');
        }
        $table->addColumnAttribute('so_customer', 'style', 'text-align: center;');
        $table->setColumnType('jos_amount', 'float');
        $table->setColumnType('jos_invoiced', 'float');
        $table->setColumnType('jos_paid', 'float');
        $table->setColumnType('jop_amount', 'float');
        $table->setColumnType('jop_invoiced', 'float');
        $table->setColumnType('jop_paid', 'float');
        $table->setColumnType('jo_margin', 'float');
        $table->setColumnType('jo_percentage', 'currency');

        $table->setFooterType('jos_amount', 'SUM');
        $table->setFooterType('jos_invoiced', 'SUM');
        $table->setFooterType('jos_paid', 'SUM');
        $table->setFooterType('jop_amount', 'SUM');
        $table->setFooterType('jop_invoiced', 'SUM');
        $table->setFooterType('jop_paid', 'SUM');
        $table->setFooterType('jo_margin', 'SUM');
        return $table;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function doPrepareJoData(): array
    {
        $results = [];
        $data = $this->loadData();
        $tempId = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            if (in_array($row['jo_id'], $tempId, true) === false) {
                $date = StringFormatter::generateKeyValueTableView([
                    [
                        'label' => Trans::getFinanceWord('order'),
                        'value' => $this->DtParser->formatDate($row['so_order_date']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('start'),
                        'value' => $this->DtParser->formatDateTime($row['jo_start_on'], 'Y-m-d H:i:s', 'd.M.Y'),
                    ],
                ]);
                $internalRef = StringFormatter::generateTableView([
                    $row['so_number'],
                    $row['jo_number'],
                ]);
                $jobUrl = $joDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id']);
                $btnJob = new HyperLink('BtnJoView' . $row['jo_id'], '', $jobUrl);
                $btnJob->viewAsButton();
                $btnJob->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
                $results[] = [
                    'so_customer' => $row['jo_customer'],
                    'so_date' => $date,
                    'jo_srv_name' => $row['jo_service'] . ' - ' . $row['jo_service_term'],
                    'in_ref' => $internalRef,
                    'customer_ref' => $joDao->concatReference($row, 'so'),
                    'jo_vendor' => $row['jo_vendor'],
                    'jos_amount' => 0.0,
                    'jos_reimburse' => 0.0,
                    'jos_invoiced' => 0.0,
                    'jos_paid' => 0.0,
                    'jop_amount' => 0.0,
                    'jop_reimburse' => 0.0,
                    'jop_invoiced' => 0.0,
                    'jop_paid' => 0.0,
                    'margin' => 0.0,
                    'percentage' => 0.0,
                    'btn_action' => $btnJob,
                ];
                $tempId[] = $row['jo_id'];
            }
            $index = array_search($row['jo_id'], $tempId, true);
            $temp = $results[$index];
            $amount = (float)$row['fn_total'];
            if ($row['fn_type'] === 'S') {
                if ($row['fn_category'] === 'S') {
                    $temp['jos_amount'] += $amount;
                } else {
                    $temp['jos_reimburse'] += $amount;
                }
                if ($row['fn_invoiced'] === 'Y') {
                    $temp['jos_invoiced'] += $amount;
                }
                if ($row['fn_paid'] === 'Y') {
                    $temp['jos_paid'] += $amount;
                }
            } else {
                if ($row['fn_category'] === 'P') {
                    $temp['jop_amount'] += $amount;
                } else {
                    $temp['jop_reimburse'] += $amount;
                }
                if ($row['fn_invoiced'] === 'Y') {
                    $temp['jop_invoiced'] += $amount;
                }
                if ($row['fn_paid'] === 'Y') {
                    $temp['jop_paid'] += $amount;
                }
            }
            $results[$index] = $temp;
        }
        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function doPrepareSoData(): array
    {
        $results = [];
        $data = $this->loadData();
        $tempId = [];
        $soDao = new SalesOrderDao();
        foreach ($data as $row) {
            if (in_array($row['so_id'], $tempId, true) === false) {
                $container = 'LCL';
                if ($row['so_container'] === 'Y') {
                    $container = 'Container';
                }
                $party = StringFormatter::generateTableView([
                    $this->Number->doFormatInteger($row['so_party']),
                    $container,
                ], 'text-align: right;');

                $url = url('/so/view?so_id=' . $row['so_id']);
                $btnSo = new HyperLink('BtnSoView' . $row['so_id'], '', $url);
                $btnSo->viewAsButton();
                $btnSo->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();

                $results[] = [
                    'so_customer' => $row['jo_customer'],
                    'customer_ref' => $soDao->concatReference($row),
                    'so_date' => $this->DtParser->formatDate($row['so_order_date']),
                    'so_party' => $party,
                    'jo_srv_name' => '',
                    'in_ref' => $row['so_number'],
                    'jos_amount' => 0.0,
                    'jos_reimburse' => 0.0,
                    'jos_invoiced' => 0.0,
                    'jos_paid' => 0.0,
                    'jop_amount' => 0.0,
                    'jop_reimburse' => 0.0,
                    'jop_invoiced' => 0.0,
                    'jop_paid' => 0.0,
                    'margin' => 0.0,
                    'percentage' => 0.0,
                    'btn_action' => $btnSo,
                ];
                $tempId[] = $row['so_id'];
            }
            $index = array_search($row['so_id'], $tempId, true);
            $temp = $results[$index];
            $amount = (float)$row['fn_total'];
            if ($row['fn_type'] === 'S') {
                if ($row['fn_category'] === 'S') {
                    $temp['jos_amount'] += $amount;
                } else {
                    $temp['jos_reimburse'] += $amount;
                }
                if ($row['fn_invoiced'] === 'Y') {
                    $temp['jos_invoiced'] += $amount;
                }
                if ($row['fn_paid'] === 'Y') {
                    $temp['jos_paid'] += $amount;
                }
            } else {
                if ($row['fn_category'] === 'P') {
                    $temp['jop_amount'] += $amount;
                } else {
                    $temp['jop_reimburse'] += $amount;
                }
                if ($row['fn_invoiced'] === 'Y') {
                    $temp['jop_invoiced'] += $amount;
                }
                if ($row['fn_paid'] === 'Y') {
                    $temp['jop_paid'] += $amount;
                }
            }
            $results[$index] = $temp;
        }

        return $results;
    }


    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $wheres = $this->getWhereCondition();
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $wheresSales = [];
        $wheresSales[] = SqlHelper::generateNullCondition('sid.sid_jos_id', false);
        $wheresSales[] = SqlHelper::generateNullCondition('si.si_approve_on', false);
        $wheresSales[] = SqlHelper::generateNullCondition('sid.sid_deleted_on');
        $wheresSales[] = SqlHelper::generateNullCondition('si.si_deleted_on');
        $wheresSales[] = SqlHelper::generateNullCondition('jos.jos_deleted_on');
        $wheresPurchase = [];
        $wheresPurchase[] = SqlHelper::generateNullCondition('jop.jop_deleted_on');
        $wheresPurchase[] = SqlHelper::generateNullCondition('pid.pid_deleted_on');
        $wheresPurchase[] = SqlHelper::generateNullCondition('pi.pi_deleted_on');
        $wheresPurchase[] = SqlHelper::generateNullCondition('pid.pid_jop_id', false);
        $wheresPurchase[] = SqlHelper::generateNullCondition('pi.pi_approve_on', false);
        if ($this->getStringParameter('reimburse', 'N') === 'N') {
            $wheresSales[] = "(ccg.ccg_type <> 'R')";
            $wheresPurchase[] = "(ccg.ccg_type <> 'R')";
        }
        # Sales Query
        $strWhereSales = ' WHERE ' . implode(' AND ', $wheresSales);
        $strWherePurchase = ' WHERE ' . implode(' AND ', $wheresPurchase);
        $inklaringSales = "SELECT so.so_id, so.so_number, soc.party as so_party, so.so_container, jo.jo_id, jo.jo_srt_id, rel.rel_name as jo_customer, so.so_order_date,
                               srv.srv_name as jo_service, srt.srt_name as jo_service_term,
                               so.so_customer_ref, so.so_aju_ref, so.so_bl_ref, so.so_packing_ref, so.so_sppb_ref,
                               'S' as fn_type, inv.jos_id as fn_id, inv.jos_total as fn_total,
                               (CASE WHEN inv.sid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                               (CASE WHEN inv.si_pay_time IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                               inv.ccg_type as fn_category, ven.rel_name as jo_vendor, us.us_name as jo_manager, jo.jo_start_on, jo.jo_number
                        FROM job_order as jo INNER JOIN
                             service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                             service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                             job_inklaring as jik ON jo.jo_id = jik.jik_jo_id INNER JOIN
                             sales_order as so ON so.so_id = jik.jik_so_id INNER JOIN
                             relation as rel ON rel.rel_id = so.so_rel_id LEFT OUTER JOIN
                             relation as ven ON jo.jo_vendor_id = ven.rel_id LEFT OUTER JOIN
                             users as us ON jo.jo_manager_id = us.us_id LEFT OUTER JOIN
                             (SELECT soc_so_id, COUNT(soc_id) as party
                                 FROM sales_order_container
                                 WHERE soc_deleted_on IS NULL
                                 GROUP BY soc_so_id) as soc ON soc.soc_so_id = so.so_id LEFT OUTER JOIN
                             (SELECT jos.jos_id, jos.jos_jo_id, jos.jos_total, ccg.ccg_type, sid.sid_id, si.si_pay_time
                              FROM job_sales as jos INNER JOIN
                                   cost_code as cc ON jos.jos_cc_id = cc.cc_id INNER JOIN
                                   cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id LEFT OUTER JOIN
                                   sales_invoice_detail as sid ON jos.jos_sid_id = sid.sid_id LEFT OUTER JOIN
                                  sales_invoice as si ON si.si_id = sid.sid_si_id " . $strWhereSales . " ) as inv ON jo.jo_id = inv.jos_jo_id" . $strWhere;
        # Purchase Field
        $inklaringPurchase = "SELECT so.so_id, so.so_number, soc.party as so_party, so.so_container, jo.jo_id, jo.jo_srt_id, rel.rel_name as jo_customer, so.so_order_date,
                                   srv.srv_name as jo_service, srt.srt_name as jo_service_term,
                                   so.so_customer_ref, so.so_aju_ref, so.so_bl_ref, so.so_packing_ref, so.so_sppb_ref,
                                   'P' as fn_type, inv.jop_id as fn_id, inv.jop_total as fn_total,
                                   (CASE WHEN inv.pid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                   (CASE WHEN inv.pi_paid_on IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                                   inv.ccg_type as fn_category, ven.rel_name as jo_vendor, us.us_name as jo_manager, jo.jo_start_on, jo.jo_number
                            FROM job_order as jo INNER JOIN
                                 service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                                 service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                                 job_inklaring as jik ON jo.jo_id = jik.jik_jo_id INNER JOIN
                                 sales_order as so ON so.so_id = jik.jik_so_id INNER JOIN
                                 relation as rel ON rel.rel_id = so.so_rel_id LEFT OUTER JOIN
                                 relation as ven ON jo.jo_vendor_id = ven.rel_id LEFT OUTER JOIN
                                 users as us ON jo.jo_manager_id = us.us_id LEFT OUTER JOIN
                                 (SELECT soc_so_id, COUNT(soc_id) as party
                                     FROM sales_order_container
                                     WHERE soc_deleted_on IS NULL
                                     GROUP BY soc_so_id) as soc ON soc.soc_so_id = so.so_id LEFT OUTER JOIN
                                 (SELECT jop.jop_id, jop.jop_jo_id, jop.jop_total, ccg.ccg_type, pid.pid_id, pi.pi_paid_on
                                  FROM job_purchase as jop INNER JOIN
                                       cost_code as cc ON jop.jop_cc_id = cc.cc_id INNER JOIN
                                       cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id LEFT OUTER JOIN
                                       purchase_invoice_detail as pid ON jop.jop_pid_id = pid.pid_id LEFT OUTER JOIN
                                      purchase_invoice as pi ON pi.pi_id = pid.pid_pi_id " . $strWherePurchase . " ) as inv ON jo.jo_id = inv.jop_jo_id " . $strWhere;
        $deliverySales = "SELECT so.so_id, so.so_number, soc.party as so_party, so.so_container, jo.jo_id, jo.jo_srt_id, rel.rel_name as jo_customer, so.so_order_date,
                               srv.srv_name as jo_service, srt.srt_name as jo_service_term,
                               so.so_customer_ref, so.so_aju_ref, so.so_bl_ref, so.so_packing_ref, so.so_sppb_ref,
                               'S' as fn_type, inv.jos_id as fn_id, inv.jos_total as fn_total,
                               (CASE WHEN inv.sid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                               (CASE WHEN inv.si_pay_time IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                               inv.ccg_type as fn_category, ven.rel_name as jo_vendor, us.us_name as jo_manager, jo.jo_start_on, jo.jo_number
                        FROM job_order as jo INNER JOIN
                             service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                             service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                             job_delivery as jdl ON jo.jo_id = jdl.jdl_jo_id INNER JOIN
                             sales_order as so ON so.so_id = jdl.jdl_so_id INNER JOIN
                             relation as rel ON rel.rel_id = so.so_rel_id LEFT OUTER JOIN
                             relation as ven ON jo.jo_vendor_id = ven.rel_id LEFT OUTER JOIN
                             users as us ON jo.jo_manager_id = us.us_id LEFT OUTER JOIN
                             (SELECT soc_so_id, COUNT(soc_id) as party
                                 FROM sales_order_container
                                 WHERE soc_deleted_on IS NULL
                                 GROUP BY soc_so_id) as soc ON soc.soc_so_id = so.so_id LEFT OUTER JOIN
                             (SELECT jos.jos_id, jos.jos_jo_id, jos.jos_total, ccg.ccg_type, sid.sid_id, si.si_pay_time
                              FROM job_sales as jos INNER JOIN
                                   cost_code as cc ON jos.jos_cc_id = cc.cc_id INNER JOIN
                                   cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id LEFT OUTER JOIN
                                   sales_invoice_detail as sid ON jos.jos_sid_id = sid.sid_id LEFT OUTER JOIN
                                  sales_invoice as si ON si.si_id = sid.sid_si_id " . $strWhereSales . " ) as inv ON jo.jo_id = inv.jos_jo_id" . $strWhere;
        # Purchase Field
        $deliveryPurchase = "SELECT so.so_id, so.so_number, soc.party as so_party, so.so_container, jo.jo_id, jo.jo_srt_id, rel.rel_name as jo_customer, so.so_order_date,
                                   srv.srv_name as jo_service, srt.srt_name as jo_service_term,
                                   so.so_customer_ref, so.so_aju_ref, so.so_bl_ref, so.so_packing_ref, so.so_sppb_ref,
                                   'P' as fn_type, inv.jop_id as fn_id, inv.jop_total as fn_total,
                                   (CASE WHEN inv.pid_id IS NULL THEN 'N' ELSE 'Y' END) as fn_invoiced,
                                   (CASE WHEN inv.pi_paid_on IS NULL THEN 'N' ELSE 'Y' END) as fn_paid,
                                   inv.ccg_type as fn_category, ven.rel_name as jo_vendor, us.us_name as jo_manager, jo.jo_start_on, jo.jo_number
                            FROM job_order as jo INNER JOIN
                                 service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                                 service_term as srt ON srt.srt_id = jo.jo_srt_id INNER JOIN
                                 job_delivery as jdl ON jo.jo_id = jdl.jdl_jo_id INNER JOIN
                                 sales_order as so ON so.so_id = jdl.jdl_so_id INNER JOIN
                                 relation as rel ON rel.rel_id = so.so_rel_id LEFT OUTER JOIN
                                 relation as ven ON jo.jo_vendor_id = ven.rel_id LEFT OUTER JOIN
                                 users as us ON jo.jo_manager_id = us.us_id LEFT OUTER JOIN
                                 (SELECT soc_so_id, COUNT(soc_id) as party
                                     FROM sales_order_container
                                     WHERE soc_deleted_on IS NULL
                                     GROUP BY soc_so_id) as soc ON soc.soc_so_id = so.so_id LEFT OUTER JOIN
                                 (SELECT jop.jop_id, jop.jop_jo_id, jop.jop_total, ccg.ccg_type, pid.pid_id, pi.pi_paid_on
                                  FROM job_purchase as jop INNER JOIN
                                       cost_code as cc ON jop.jop_cc_id = cc.cc_id INNER JOIN
                                       cost_code_group as ccg ON cc.cc_ccg_id = ccg.ccg_id LEFT OUTER JOIN
                                       purchase_invoice_detail as pid ON jop.jop_pid_id = pid.pid_id LEFT OUTER JOIN
                                      purchase_invoice as pi ON pi.pi_id = pid.pid_pi_id " . $strWherePurchase . " ) as inv ON jo.jo_id = inv.jop_jo_id " . $strWhere;
        $query = $inklaringSales . ' UNION ALL ' . $inklaringPurchase . ' UNION ALL ' . $deliverySales . ' UNION ALL ' . $deliveryPurchase;
        $query .= ' ORDER BY so_id DESC, jo_id DESC';
        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        if ($this->isValidParameter('customer_id')) {
            $wheres[] = '(so.so_rel_id = ' . $this->getIntParameter('customer_id') . ')';
        }
        if ($this->isValidParameter('customer_ref') === true) {
            $wheres[] = SqlHelper::generateOrLikeCondition([
                'so.so_customer_ref',
                'so.so_bl_ref',
                'so.so_packing_ref',
                'so.so_aju_ref',
                'so.so_sppb_ref',
            ], $this->getStringParameter('customer_ref'));
        }

        if ($this->isValidParameter('internal_ref') === true) {
            $wheres[] = SqlHelper::generateOrLikeCondition(['so.so_number', 'jo.jo_number'], $this->getStringParameter('internal_ref'));
        }

        if ($this->isValidParameter('date_from') === true) {
            if ($this->isValidParameter('date_until') === true) {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('date_from') . ' 00:01:00', '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('date_from'));
            }
        }
        if ($this->isValidParameter('date_until') === true) {
            if ($this->isValidParameter('date_from') === true) {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('date_until') . ' 23:59:00', '<=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('date_until'));
            }
        }
        $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNullCondition('so.so_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('jo.jo_deleted_on');
        return $wheres;
    }

}
