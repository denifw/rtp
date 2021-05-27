<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Statistic\Job\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractStatisticModel;
use Exception;


/**
 * Class to control the system of JobFifo.
 *
 * @package    app
 * @subpackage Model\Listing\Job/Warehouse
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobFifo extends AbstractStatisticModel
{
    /**
     * JobFifo constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'whFifo');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $whField = $this->Field->getSingleSelect('warehouse', 'warehouse', $this->getStringParameter('warehouse'));
        $whField->setHiddenField('wh_id', $this->getIntParameter('wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $relField = $this->Field->getSingleSelect('relation', 'gd_relation', $this->getStringParameter('gd_relation'), 'loadGoodsOwnerData');
        $relField->setHiddenField('gd_rel_id', $this->getIntParameter('gd_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        $relField->addClearField('gd_name');
        $relField->addClearField('gd_id');

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_rel_id', 'gd_rel_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->setEnableNewButton(false);
        $goodsField->setEnableDetailButton(false);

        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $this->StatisticForm->addField(Trans::getWord('customer'), $relField);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('gd_rel_id', $this->User->getRelId()));
        }
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField, true);
        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('from_date', $this->getStringParameter('from_date')), true);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('until_date', $this->getStringParameter('until_date')), true);
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
    }

    /**
     * Function validation
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('gd_id');
        $this->Validation->checkRequire('from_date');
        $this->Validation->checkRequire('until_date');
        parent::loadValidationRole();
    }

    /**
     * Function load view
     */
    public function loadViews(): void
    {
        $portlet = $this->getResultPortlet();
        if ($this->getFormAction() === 'doExportXls') {
            $this->addDatas('FifoReport', $portlet);
        }
        $this->addContent('Content1', $portlet);
    }

    /**
     * Function to get results portlet
     *
     * @return Portlet
     */
    private function getResultPortlet(): Portlet
    {
        $table = new TableDatas('FifoTbl');
        if ($this->getFormAction() === 'doExportXls') {
            # Set Header when the action is to export xls

            $table->setHeaderRow([
                'gd_sku' => Trans::getWord('sku'),
                'gd_uom' => Trans::getWord('uom'),
                'gd_weight' => Trans::getWord('weight'),
                'gd_cbm' => Trans::getWord('cbm'),
                'date' => Trans::getWord('date'),
                'day' => Trans::getWord('day'),
                # inbound
                'in_qty' => Trans::getWord('inbound'),
                'in_total_cbm' => Trans::getWord('totalCBM'),
                'in_total_weight' => Trans::getWord('totalWeight'),
                # outbound
                'out_qty' => Trans::getWord('outbound'),
                'out_total_cbm' => Trans::getWord('totalCBM'),
                'out_total_weight' => Trans::getWord('totalWeight'),
                # adjustment
                'adj_qty' => Trans::getWord('adjustment'),
                'adj_total_cbm' => Trans::getWord('totalCBM'),
                'adj_total_weight' => Trans::getWord('totalWeight'),
                # balance
                'balance' => Trans::getWord('balance'),
                'bal_total_cbm' => Trans::getWord('totalCBM'),
                'bal_total_weight' => Trans::getWord('totalWeight'),
                'reference' => Trans::getWord('reference'),
                'remark' => Trans::getWord('remark'),
            ]);

            $table->addRows($this->doPrepareData($table));
            $table->setRowsPerPage(50);
            $table->setColumnType('gd_weight', 'float');
            $table->setColumnType('gd_cbm', 'float');
            # inbound
            $table->setColumnType('in_total_cbm', 'float');
            $table->setColumnType('in_total_weight', 'float');
            # outbound
            $table->setColumnType('out_total_cbm', 'float');
            $table->setColumnType('out_total_weight', 'float');
            # adjustment
            $table->setColumnType('adj_total_cbm', 'float');
            $table->setColumnType('adj_total_weight', 'float');
            # balance
            $table->setColumnType('bal_total_cbm', 'float');
            $table->setColumnType('bal_total_weight', 'float');
        } else {

            $table->setHeaderRow([
                'gd_sku' => Trans::getWord('sku'),
                'gd_uom' => Trans::getWord('uom'),
                'date' => Trans::getWord('date'),
                'day' => Trans::getWord('day'),
                'in_qty' => Trans::getWord('inbound'),
                'out_qty' => Trans::getWord('outbound'),
                'adj_qty' => Trans::getWord('adjustment'),
                'balance' => Trans::getWord('balance'),
                'reference' => Trans::getWord('reference'),
                'remark' => Trans::getWord('remark'),
            ]);

            $table->addRows($this->doPrepareData($table));
            $table->setRowsPerPage(50);
        }
        # inbound
        $table->setColumnType('in_qty', 'float');
        # outbound
        $table->setColumnType('out_qty', 'float');
        # adjustment
        $table->setColumnType('adj_qty', 'float');
        # balance
        $table->setColumnType('balance', 'float');

        $portlet = new Portlet('RslTblPtl', Trans::getWord('results'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function do prepare data before show
     * @param Table $table To store table object.
     * @return array
     */
    private function doPrepareData(Table $table): array
    {
        $results = [];
        $tempInDate = [];
        $data = $this->loadData();
        foreach ($data as $row) {
            $inboundDate = str_replace('-', '', $row['inbound_date']);
            $startDate = str_replace('-', '', $row['start_on']);
            if (array_key_exists($inboundDate, $results) === false) {
                $results[$inboundDate] = [
                    'index' => [$startDate],
                    'rows' => [$this->loadDataFormat($row)]
                ];
                $tempInDate[] = $inboundDate;
            } else {
                $tempResult = $results[$inboundDate];
                if (in_array($startDate, $tempResult['index'], true) === false) {
                    $tempResult['index'][] = $startDate;
                    $tempResult['rows'][] = $this->loadDataFormat($row);
                } else {
                    $index = array_search($startDate, $tempResult['index'], true);
                    $tempRow = $this->calculateQuantity($row, $tempResult['rows'][$index]);
                    $tempResult['rows'][$index] = $tempRow;
                }
                $results[$inboundDate] = $tempResult;
            }
        }
        $finalResults = [];
        $balance = 0.0;
        $balanceWeight = 0.0;
        $balanceCbm = 0.0;
        $lastMonth = '';
        $rowIndex = 0;
        foreach ($tempInDate as $key) {
            $rows = $results[$key]['rows'];
            $newInbound = true;
            foreach ($rows as $row) {
                if ($lastMonth !== $row['month']) {
                    if ($lastMonth !== '') {
                        # Add Row for Last date every month before the next month.
                        $lastDayOfMonth = date('Y-m-t', strtotime($lastMonth . '-01'));
                        $lastRowOfMonth = $this->loadDataFormat([
                            'gd_sku' => $row['gd_sku'],
                            'gd_uom' => $row['gd_uom'],
                            'gd_weight' => $row['gd_weight'],
                            'gd_cbm' => $row['gd_cbm'],
                            'start_on' => $lastDayOfMonth
                        ], false);
                        $lastRowOfMonth['balance'] = $balance;
                        $lastRowOfMonth['bal_total_weight'] = $balanceWeight;
                        $lastRowOfMonth['bal_total_cbm'] = $balanceCbm;
                        $lastRowOfMonth['reference'] = '';
                        $lastRowOfMonth['remark'] = Trans::getWord('monthlyChecking');
                        $finalResults[] = $lastRowOfMonth;
                        # Set row style
                        $table->addRowAttribute($rowIndex, 'style', 'background-color: #ffe6e6;');
                        $rowIndex++;
                    }
                    $lastMonth = $row['month'];
                }
                if ($rowIndex > 0 && $newInbound === true) {
                    # add empty row to separate the new inbound data.
                    $emptyRow = $this->loadDataFormat();
                    $emptyRow['reference'] = '';
                    $emptyRow['remark'] = '';
                    $finalResults[] = $emptyRow;
                    # Set row style
                    $table->addRowAttribute($rowIndex, 'style', 'background-color: #cccccc;');
                    $rowIndex++;
                }
                # Add row into final rows.
                $balance += $row['balance'];
                $balanceWeight += $row['bal_total_weight'];
                $balanceCbm += $row['bal_total_cbm'];
                $row['balance'] = $balance;
                $row['bal_total_weight'] = $balanceWeight;
                $row['bal_total_cbm'] = $balanceCbm;
                $row['reference'] = implode(', ', $row['reference']);
                # Format Remarks
                $remark = '';
                $number = new NumberFormatter($this->User);
                foreach ($row['remark'] as $pre => $val) {
                    $remark .= '(' . $val['prefix'] . ' - ' .
                        $number->doFormatFloat($val['quantity']) . ' ' . $val['uom'] . ' - ' .
                        $val['gdt_description'] . ') ';
                }
                $row['remark'] = $remark;
                $finalResults[] = $row;
                $rowIndex++;
                $newInbound = false;
            }
        }
        return $finalResults;
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $row To store the data.
     * @param bool $calculate To trigger calculation of quantity.
     *
     * @return array
     */
    private function loadDataFormat(array $row = [], bool $calculate = true): array
    {
        $strDate = '';
        $strMonth = '';
        $strDay = '';
        $balance = '';
        $sku = '';
        $uom = '';
        $weight = '';
        $cbm = '';
        if (empty($row) === false) {
            $balance = 0.0;
            $sku = $row['gd_sku'];
            $uom = $row['gd_uom'];
            $weight = $row['gd_weight'];
            $cbm = $row['gd_cbm'];
            $date = DateTimeParser::createFromFormat($row['start_on'], 'Y-m-d');
            if ($date !== null) {
                $strDate = $date->format('d.M.Y');
                $strMonth = $date->format('Y-m');
                $strDay = $date->format('D');
            }
        }

        $format = [
            'gd_sku' => $sku,
            'gd_uom' => $uom,
            'gd_weight' => $weight,
            'gd_cbm' => $cbm,
            'date' => $strDate,
            'month' => $strMonth,
            'day' => $strDay,
            'in_qty' => '',
            'in_total_cbm' => '',
            'in_total_weight' => '',
            'out_qty' => '',
            'out_total_cbm' => '',
            'out_total_weight' => '',
            'adj_qty' => '',
            'adj_total_cbm' => '',
            'adj_total_weight' => '',
            'balance' => $balance,
            'bal_total_cbm' => '',
            'bal_total_weight' => '',
            'reference' => [],
            'remark' => [],
        ];
        if ($calculate === false || empty($row) === true) {
            return $format;
        }

        return $this->calculateQuantity($row, $format);
    }

    /**
     * Get query to get inbound quantity.
     *
     * @param array $row To store the data.
     * @param array $results To store the data.
     *
     * @return array
     */
    private function calculateQuantity(array $row, array $results): array
    {
        $quantity = (float)$row['quantity'] * (float)$row['qty_conversion'];
        $weight = (float)$row['gd_weight'];
        $totalWeight = $weight * $quantity;
        $cbm = (float)$row['gd_cbm'];
        $totalCbm = $cbm * $quantity;
        $prefix = '';
        if ($row['service_term'] === 'joWhInbound') {
            $results['in_qty'] = (float)$results['in_qty'] + $quantity;
            $results['in_total_cbm'] = (float)$results['in_total_cbm'] + $totalCbm;
            $results['in_total_weight'] = (float)$results['in_total_weight'] + $totalWeight;
            $results['balance'] += $quantity;
            $results['bal_total_weight'] = (float)$results['bal_total_weight'] + $totalWeight;
            $results['bal_total_cbm'] = (float)$results['bal_total_cbm'] + $totalCbm;
            $prefix = 'IN';
        } elseif ($row['service_term'] === 'joWhOutbound') {
            $results['out_qty'] = (float)$results['out_qty'] + $quantity;
            $results['out_total_cbm'] = (float)$results['out_total_cbm'] + $totalCbm;
            $results['out_total_weight'] = (float)$results['out_total_weight'] + $totalWeight;
            $results['balance'] -= $quantity;
            $results['bal_total_weight'] = (float)$results['bal_total_weight'] - $totalWeight;
            $results['bal_total_cbm'] = (float)$results['bal_total_cbm'] - $totalCbm;
            $prefix = 'OUT';
        } elseif ($row['service_term'] === 'joWhStockAdjustment') {
            $results['adj_qty'] = (float)$results['adj_qty'] + $quantity;
            $results['adj_total_cbm'] = (float)$results['adj_total_cbm'] + $totalCbm;
            $results['adj_total_weight'] = (float)$results['adj_total_weight'] + $totalWeight;
            $results['bal_total_weight'] = (float)$results['bal_total_weight'] + $totalWeight;
            $results['bal_total_cbm'] = (float)$results['bal_total_cbm'] + $totalCbm;
            $results['balance'] += $quantity;
            $prefix = 'ADJ';
        }

        # prepare reference
        if (empty($row['jo_customer_ref']) === false) {
            $results['reference'][] = $row['jo_customer_ref'];
        }

        # prepare the remark for damage information
        if (empty($row['damage_code']) === false) {
            $key = $prefix . $row['damage_code'];
            if (array_key_exists($key, $results['remark']) === false) {
                $results['remark'][$key] = [
                    'prefix' => $prefix,
                    'quantity' => $quantity,
                    'uom' => $row['gd_uom'],
                    'gdt_code' => $row['damage_code'],
                    'gdt_description' => $row['damage_type'],
                ];
            } else {
                $results['remark'][$key]['quantity'] += $quantity;
            }
        }
        return $results;
    }

    /**
     * Function to load from data all
     * @return array
     */
    private function loadData(): array
    {
        $joWheres = [];
        $joWheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $this->User->getSsId());
        $joWheres [] = '(jo.jo_deleted_on is null)';
        $joWheres[] = '(ji.ji_deleted_on is null)';
        $joWheres [] = '(ji.ji_end_load_on is not null)';
        $joWheres[] = SqlHelper::generateStringCondition('ji.ji_start_load_on', $this->getStringParameter('from_date'), '>=');
        $joWheres[] = SqlHelper::generateStringCondition('ji.ji_start_load_on', $this->getStringParameter('until_date'), '<=');
        if ($this->isValidParameter('gd_id') === true) {
            $joWheres [] = SqlHelper::generateNumericCondition('gd.gd_id', $this->getIntParameter('gd_id'));
        }
        $query = $this->getJobInbound($joWheres);
        $query .= ' UNION ALL ' . $this->getJobOutbound($joWheres);
        $query .= ' UNION ALL ' . $this->getAdjustment($joWheres);
        $query .= ' ORDER BY ji_id, sort, start_on ';
        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get job inbound
     * @param array $wheres To store the where conditions
     * @return string
     */
    private function getJobInbound(array $wheres): string
    {
        $wheres[] = '(jir.jir_deleted_on is null)';
        $strWhereJid = ' WHERE ' . implode(' and ', $wheres);

        $query = "SELECT 1 as sort, srt.srt_route as service_term, (ji.ji_start_load_on::timestamp::date) as start_on, (ji.ji_start_load_on::timestamp::date) as inbound_date, ji.ji_id, gd.gd_id, gd.gd_name,
       jir.jir_gdt_id, jo.jo_customer_ref, gdt.gdt_code as damage_code, gdt.gdt_description as damage_type, SUM(jir.jir_quantity) as quantity,
       uom.uom_code as gd_uom, gdu.gdu_qty_conversion as qty_conversion,
       (case WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight else jir.jir_weight END) as gd_weight,
       (case WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume else  jir.jir_volume END) as gd_cbm, gd.gd_sku

       FROM job_inbound_receive as jir
         INNER JOIN job_inbound as ji ON jir.jir_ji_id = ji.ji_id
         INNER JOIN job_order as jo ON ji.ji_jo_id = jo.jo_id
         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
         INNER JOIN job_goods as jog ON jir.jir_jog_id = jog.jog_id
         INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
         INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
         INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
         LEFT OUTER JOIN goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id" . $strWhereJid;
        $query .= " GROUP BY srt.srt_route, (ji.ji_start_load_on::timestamp::date), ji.ji_id, gd.gd_id, gd.gd_name,
         uom.uom_code, jir.jir_gdt_id, gdt.gdt_code, gdt.gdt_description, jo.jo_customer_ref,
         gdu.gdu_qty_conversion, jir.jir_weight, gdu.gdu_weight, jir.jir_volume, gdu.gdu_volume, gd.gd_sku ";
        return $query;
    }

    /**
     * Function to Get job Outbound
     * @param array $wheres To store the where conditions
     * @return string
     */
    private function getJobOutbound(array $wheres): string
    {
        $wheres[] = '(jod.jod_deleted_on is null)';
        $wheres[] = '(jid.jid_deleted_on is null)';
        $wheres [] = '(job.job_end_load_on is not null)';
        $strWhereJod = ' WHERE ' . implode(' and ', $wheres);
        $query = "SELECT 2 as sort, srt.srt_route as service_term, (job.job_start_load_on::timestamp::date) as start_on, (ji.ji_start_load_on::timestamp::date) as inbound_date,
        ji.ji_id, gd.gd_id, gd.gd_name, jid.jid_gdt_id, jo.jo_customer_ref, gdt.gdt_code as damage_code, gdt.gdt_description as damage_type,
        SUM(jod.jod_quantity) as quantity, uom.uom_code as gd_uom, gdu.gdu_qty_conversion as qty_conversion,
        (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as gd_weight,
        (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE  jid.jid_volume END) as gd_cbm, gd.gd_sku
FROM job_outbound_detail as jod
         INNER JOIN job_outbound as job ON jod.jod_job_id = job.job_id
         INNER JOIN job_order as jo ON job.job_jo_id = jo.jo_id
         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
         INNER JOIN job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id
         INNER JOIN job_inbound as ji ON jid.jid_ji_id = ji.ji_id
         INNER JOIN goods as gd ON jid.jid_gd_id = gd.gd_id
         INNER JOIN goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id
         INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
         LEFT OUTER JOIN goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id " . $strWhereJod;
        $query .= " GROUP BY srt.srt_route, (job.job_start_load_on::timestamp::date), (ji.ji_start_load_on::timestamp::date), ji.ji_id, gd.gd_id, gd.gd_name,
         uom.uom_code, jid.jid_gdt_id, gdt.gdt_code, gdt.gdt_description, jo.jo_customer_ref,
         gdu.gdu_qty_conversion, jid.jid_weight, gdu.gdu_weight, jid.jid_volume, gdu.gdu_volume, gd.gd_sku ";
        return $query;
    }

    /**
     * Function to get adjustment
     * @param array $wheres To store the where conditions
     * @return string
     */
    private function getAdjustment(array $wheres): string
    {
        $query = "";
        $wheres [] = '(jid.jid_deleted_on is null)';
        $wheres [] = '(ja.ja_complete_on IS NOT NULL)';
        $wheres [] = '(jad.jad_deleted_on IS NULL)';
        $strWhereJad = ' WHERE ' . implode(' and ', $wheres);
        $query .= "SELECT 2 as sort,
       srt.srt_route as service_term,
       (jo.jo_start_on::timestamp::date) as start_on, (ji.ji_start_load_on::timestamp::date) as inbound_date,
       ji.ji_id, gd.gd_id, gd.gd_name,
       jid.jid_gdt_id,
       jo.jo_customer_ref,
       gdt.gdt_code as damage_code,
       gdt.gdt_description as damage_type,
       SUM(jad.jad_quantity) as quantity,
       uom.uom_code as gd_uom,
       gdu.gdu_qty_conversion as qty_conversion,
       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as gd_weight,
       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE  jid.jid_volume END) as gd_cbm, gd.gd_sku
FROM job_adjustment_detail as jad
         INNER JOIN job_adjustment as ja ON jad.jad_ja_id = ja.ja_id
         INNER JOIN job_order as jo ON ja.ja_jo_id = jo.jo_id
         INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
         INNER JOIN job_inbound_detail as jid ON jad.jad_jid_id = jid.jid_id
         INNER JOIN job_inbound as ji ON jid.jid_ji_id = ji.ji_id
         INNER JOIN goods as gd ON jid.jid_gd_id = gd.gd_id
         INNER JOIN goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id
         INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
         LEFT OUTER JOIN goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id " . $strWhereJad;
        $query .= " GROUP BY srt.srt_route, (jo.jo_start_on::timestamp::date), (ji.ji_start_load_on::timestamp::date), ji.ji_id, gd.gd_id, gd.gd_name,
         uom.uom_code, jid.jid_gdt_id, gdt.gdt_code, gdt.gdt_description, jo.jo_customer_ref,
         gdu.gdu_qty_conversion, jid.jid_weight, gdu.gdu_weight, jid.jid_volume, gdu.gdu_volume, gd.gd_sku ";
        return $query;
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        try {
            $excel = new Excel();
            $sku = explode(" -", $this->getStringParameter('gd_name'));
            foreach ($this->Datas as $key => $portlet) {
                if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                    $sheetName = StringFormatter::formatExcelSheetTitle($sku[0]);
                    $excel->setFileName($this->PageSetting->getPageDescription() . ' ' . $this->getPeriod() . '.xlsx');
                    $excel->addSheet($sheetName, $sheetName);
                    $sheet = $excel->getSheet($sheetName, true);
                    $sheet->mergeCells('A1:E1');
                    $sheet->setCellValue('A1', Trans::getWord('fifoReport'));
                    $sheet->getStyle('A1')->getFont()->setBold(true);

                    $sheet->mergeCells('A2:B2');
                    $sheet->setCellValue('A2', Trans::getWord('period'));
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->mergeCells('C2:E2');
                    $sheet->setCellValue('C2', $this->getPeriod());
                    $sheet->getStyle('C2')->getFont()->setBold(true);

                    $sheet->mergeCells('A3:B3');
                    $sheet->setCellValue('A3', Trans::getWord('warehouse'));
                    $sheet->getStyle('A3')->getFont()->setBold(true);
                    $sheet->mergeCells('C3:E3');
                    $sheet->setCellValue('C3', $this->getWarehouse());
                    $sheet->getStyle('C3')->getFont()->setBold(true);
                    $excel->doRowMovePointer($sheetName);
                    $sheet->setSelectedCellByColumnAndRow(1, 5);
                    $excelTable = new ExcelTable($excel, $sheet);
                    $excelTable->setTable($portlet->Body[0]);
                    $excelTable->writeTable();
                    $excel->setActiveSheet($sheetName);
                }
            }
            $excel->createExcel();
        } catch (Exception $e) {
            $this->View->addErrorMessage('Failed to generate excel file.');
        }
    }

    /**
     * @return string
     */
    private function getWarehouse(): string
    {
        if ($this->isValidParameter('warehouse') === true) {
            return $this->getStringParameter('warehouse');
        }
        return " - ";
    }

    /**
     * @return string
     */
    private function getPeriod(): string
    {
        $periodStart = $this->getStringParameter('from_date');
        $periodUntil = " - " . $this->getStringParameter('until_date');
        return $periodStart . $periodUntil;
    }


}
