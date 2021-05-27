<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Job\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class WeightCbmReport extends AbstractStatisticModel
{

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'whWeighCbm');
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

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        } else {
            $goodsField->addParameter('gd_rel_id', $this->User->getRelId());
        }
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->setEnableNewButton(false);

        # Condition field
        $conditionField = $this->Field->getSelect('gd_condition', $this->getStringParameter('gd_condition'));
        $conditionField->addOption(Trans::getWord('allCondition'), 'A');
        $conditionField->addOption(Trans::getWord('good'), 'G');
        $conditionField->addOption(Trans::getWord('damage'), 'D');
        $conditionField->setPleaseSelect(false);

        # Add Field
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
            $relationField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
            $relationField->addParameter('rel_ss_id', $this->User->getSsId());
            $relationField->setEnableNewButton(false);
            $relationField->addClearField('gd_name');
            $relationField->addClearField('gd_id');

            $this->StatisticForm->addField(Trans::getWord('relation'), $relationField);
            $this->StatisticForm->setGridDimension(4);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $this->User->getRelId()));
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_name', $this->User->Relation->getName()));
            $this->StatisticForm->setGridDimension();
        }
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('from_date', $this->getStringParameter('from_date')), true);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('until_date', $this->getStringParameter('until_date')));
        $this->StatisticForm->addField(Trans::getWord('condition'), $conditionField);
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('from_date');
        $this->Validation->checkDate('from_date');
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $this->Validation->checkDate('until_date', '', $this->getStringParameter('from_date'));
        }
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $portlet = $this->getResultPortlet();
        if ($this->getFormAction() === 'doExportXls') {
            $this->addDatas('cbm', $portlet);
        }
        $this->addContent('RslCtn', $portlet);
    }

    /**
     * Function to get the report portlet.
     *
     * @return Portlet
     */
    protected function getResultPortlet(): Portlet
    {
        $table = $this->getResultTable();
        $portlet = new Portlet('RslPtl', Trans::getWord('results'));

        $data = $this->doPrepareData();
        if (empty($data) === false) {
            $index = count($data) - 2;
            $table->addRowAttribute($index, 'style', 'font-weight: bold');
            $table->addRowAttribute($index + 1, 'style', 'font-weight: bold');
        }
        $table->addRows($data);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultTable(): Table
    {
        $table = new Table('RslTbl');
        $table->setHeaderRow([
            'date' => Trans::getWord('date'),
            'in' => Trans::getWord('in') . ' (M3)',
            'out' => Trans::getWord('out') . ' (M3)',
            'mv_gd' => 'Move NG3 to CK3 (M3)',
            'mv_dm' => 'Move CK3 to NG3 (M3)',
            'total' => Trans::getWord('inventory') . ' (M3)',
        ]);
        $table->setColumnType('in', 'float');
        $table->setColumnType('out', 'float');
        $table->setColumnType('mv_gd', 'float');
        $table->setColumnType('mv_dm', 'float');
        $table->setColumnType('total', 'float');
        return $table;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    protected function doPrepareData(): array
    {
        $temp = $this->loadData();
        $data = [];
        foreach ($temp as $row) {
            $data[$row['period']] = $row;
        }
        $results = [];
        $dtParser = new DateTimeParser();
        $stock = 0.0;
        $i = 0;
        $total = [
            'in' => 0.0,
            'out' => 0.0,
            'mv_gd' => 0.0,
            'mv_dm' => 0.0,
            'adjustment' => 0.0,
            'total' => 0.0,
        ];
        $keys = $this->getRowKeys();
        $condition = $this->getStringParameter('gd_condition', 'A');
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) === true) {
                $row = $data[$key];
            } else {
                $row = [
                    'in_a_vl' => 0.0,
                    'in_b_vl' => 0.0,
                    'out_a_vl' => 0.0,
                    'out_b_vl' => 0.0,
                    'ad_a_vl' => 0.0,
                    'ad_b_vl' => 0.0,
                    'mg_a_vl' => 0.0,
                    'mg_b_vl' => 0.0,
                    'md_a_vl' => 0.0,
                    'md_b_vl' => 0.0,
                ];
            }
            if (empty($results) === true) {
                if ($condition === 'G') {
                    $stock = ((float)$row['in_a_vl'] + (float)$row['ad_a_vl'] + (float)$row['mg_a_vl']) - ((float)$row['md_a_vl'] + (float)$row['out_a_vl']);
                } elseif ($condition === 'D') {
                    $stock = ((float)$row['in_a_vl'] + (float)$row['ad_a_vl'] + (float)$row['md_a_vl']) - ((float)$row['mg_a_vl'] + (float)$row['out_a_vl']);
                } else {
                    $stock = ((float)$row['in_a_vl'] + (float)$row['ad_a_vl']) - (float)$row['out_a_vl'];
                }
                $temp = [
                    'date' => Trans::getWord('originCbm'),
                    'in' => '0',
                    'out' => '0',
                    'mv_gd' => '0',
                    'mv_dm' => '0',
                    'adjustment' => '0',
                    'total' => $stock,
                ];
            } else {
                if ($condition === 'G') {
                    $stock += (((float)$row['in_b_vl'] + (float)$row['ad_b_vl'] + (float)$row['mg_b_vl']) - ((float)$row['md_b_vl'] + (float)$row['out_b_vl']));
                } elseif ($condition === 'D') {
                    $stock += (((float)$row['in_b_vl'] + (float)$row['ad_b_vl'] + (float)$row['md_b_vl']) - ((float)$row['mg_b_vl'] + (float)$row['out_b_vl']));
                } else {
                    $stock += (((float)$row['in_b_vl'] + (float)$row['ad_b_vl']) - (float)$row['out_b_vl']);
                }
                $i++;
                $temp = [
                    'date' => $dtParser->formatDate($key),
                    'in' => $row['in_b_vl'],
                    'out' => $row['out_b_vl'],
                    'mv_gd' => $row['mg_b_vl'],
                    'mv_dm' => $row['md_b_vl'],
                    'adjustment' => $row['ad_b_vl'],
                    'total' => $stock,
                ];
                $total['in'] += $row['in_b_vl'];
                $total['out'] += $row['out_b_vl'];
                $total['mv_gd'] += $row['mg_b_vl'];
                $total['mv_dm'] += $row['md_b_vl'];
                $total['adjustment'] += $row['ad_b_vl'];
                $total['total'] += $stock;
            }
            $results[] = $temp;
        }
        if (empty($results) === false) {
            $results[] = [
                'date' => Trans::getWord('total'),
                'in' => $total['in'],
                'out' => $total['out'],
                'mv_gd' => $total['mv_gd'],
                'mv_dm' => $total['mv_dm'],
                'adjustment' => $total['adjustment'],
                'total' => $total['total'],
            ];
            $results[] = [
                'date' => Trans::getWord('average'),
                'in' => '',
                'out' => '',
                'mv_gd' => '',
                'mv_dm' => '',
                'adjustment' => '',
                'total' => $total['total'] / $i,
            ];
        }
        return $results;
    }


    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    protected function loadData(): array
    {
        $strDate = '2010-01-01';
        $date = DateTimeParser::createFromFormat($this->getStringParameter('from_date') . ' 00:00:01');
        if ($date !== null) {
            $date->modify('-1 day');
            $strDate = $date->format('Y-m-d');
        }
        $unionAllQuery = [];
        $unionAllQuery[] = $this->getOriginInboundQuery($strDate);
        $unionAllQuery[] = $this->getCurrentInboundQuery();
        $unionAllQuery[] = $this->getOriginOutboundQuery($strDate);
        $unionAllQuery[] = $this->getCurrentOutboundQuery();
        $unionAllQuery[] = $this->getOriginMoveDamageToGoodQuery($strDate);
        $unionAllQuery[] = $this->getOriginMoveGoodToDamageQuery($strDate);
        $unionAllQuery[] = $this->getMoveDamageToGoodQuery();
        $unionAllQuery[] = $this->getMoveGoodToDamageQuery();
        $unionAllQuery[] = $this->getOriginAdjustmentQuery($strDate);
        $unionAllQuery[] = $this->getCurrentAdjustmentQuery();

        $query = 'SELECT period, SUM(in_a * in_a_vl) as in_a_vl,SUM(in_b * in_b_vl) as in_b_vl,SUM(out_a * out_a_vl) as out_a_vl,
                       SUM(out_b * out_b_vl) as out_b_vl,SUM(mg_a * mg_a_vl) as mg_a_vl,SUM(mg_b * mg_b_vl) as mg_b_vl,
                       SUM(md_a * md_a_vl) as md_a_vl,SUM(md_b * md_b_vl) as md_b_vl, 
                       SUM(ad_a * ad_a_vl) as ad_a_vl,SUM(ad_b * ad_b_vl) as ad_b_vl
                FROM (' . implode(' UNION ALL ', $unionAllQuery) . ') as j ';
        $query .= ' GROUP BY period';
        $query .= ' ORDER BY period';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get the report table.
     *
     * @return array
     */
    protected function getRowKeys(): array
    {
        $results = [];
        $startDate = DateTimeParser::createFromFormat($this->getStringParameter('from_date') . ' 00:00:01');
        $days = 0;
        if ($this->isValidParameter('until_date')) {
            $endDate = DateTimeParser::createFromFormat($this->getStringParameter('until_date') . ' 00:00:01');
            if ($startDate !== null && $endDate !== null) {
                $diff = DateTimeParser::different($startDate, $endDate);
                if (empty($diff) === false) {
                    $days = (int)$diff['days'];
                }
            }
        }
        $days++;
        $startDate->modify('-1 days');
        for ($i = 0; $i <= $days; $i++) {
            $results[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }
        return $results;

    }

    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getInboundConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jog.jog_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_end_load_on IS NOT NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        $wheres[] = '(jir.jir_deleted_on IS NULL)';

        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition !== 'A') {
            if ($condition === 'G') {
                $wheres[] = '(jir.jir_gdt_id IS NULL)';
            } else {
                $wheres[] = '(jir.jir_gdt_id IS NOT NULL)';
            }
        }
        return $wheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @param string $period To store the period value
     *
     * @return string
     */
    private function getOriginInboundQuery(string $period): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = "(ji.ji_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return "SELECT '" . $period . "'::date as period, jir.jir_quantity as in_a, (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_inbound_receive as jir INNER JOIN
                     job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                     job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                     goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                     job_order as jo ON jo.jo_id = ji.ji_jo_id " . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentInboundQuery(): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT ji.ji_end_load_on::timestamp::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       jir.jir_quantity as in_b, (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_inbound_receive as jir INNER JOIN
                     job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                     job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                     goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                     job_order as jo ON jo.jo_id = ji.ji_jo_id' . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getOutboundConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(job.job_deleted_on IS NULL)';
        $wheres[] = '(job.job_end_load_on IS NOT NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(job.job_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $wheres[] = '(jod.jod_jis_id IS NOT NULL)';
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition !== 'A') {
            if ($condition === 'G') {
                $wheres[] = '(jid.jid_gdt_id IS NULL)';
            } else {
                $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
            }
        }
        return $wheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @param string $period To store the period value
     *
     * @return string
     */
    private function getOriginOutboundQuery(string $period): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = "(job.job_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return "SELECT '" . $period . "'::date as period, 0 as in_a, 0 as in_a_vl,
                       jod.jod_qty_loaded as out_a, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_outbound_detail as jod INNER JOIN
                     job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                     job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                     goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id " . $strWheres;
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentOutboundQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = "(job.job_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT job.job_end_load_on::timestamp::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       jod.jod_qty_loaded as out_b, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_outbound_detail as jod INNER JOIN
                     job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                     job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                     goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getMovementConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jm.jm_deleted_on IS NULL)';
        $wheres[] = '(jmd.jmd_deleted_on IS NULL)';
        $wheres[] = '(jm.jm_complete_on IS NOT NULL)';
        $wheres[] = '(jmd.jmd_jis_new_id IS NOT NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(jm.jm_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        return $wheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getMoveDamageToGoodQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NULL)';
        $wheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jm.jm_complete_on::timestamp::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       jmd.jmd_quantity as mg_b, (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_movement_detail as jmd INNER JOIN
                     job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                     warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                     job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                     goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getMoveGoodToDamageQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NOT NULL)';
        $wheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jm.jm_complete_on::timestamp::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       jmd.jmd_quantity as md_b, (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_movement_detail as jmd INNER JOIN
                     job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                     warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                     job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                     goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @param string $period To store the period value
     *
     * @return string
     */
    private function getOriginMoveDamageToGoodQuery(string $period): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NULL)';
        $wheres[] = "(jm.jm_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return "SELECT '" . $period . "'::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       jmd.jmd_quantity as mg_a, (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_movement_detail as jmd INNER JOIN
                     job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                     warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                     job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                     goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id" . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @param string $period To store the period value
     *
     * @return string
     */
    private function getOriginMoveGoodToDamageQuery(string $period): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NOT NULL)';
        $wheres[] = "(jm.jm_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return "SELECT '" . $period . "'::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       jmd.jmd_quantity as md_a, (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
                FROM job_movement_detail as jmd INNER JOIN
                     job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                     warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                     job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                     job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                     goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id" . $strWheres;
    }


    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getAdjustmentConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(ja.ja_deleted_on IS NULL)';
        $wheres[] = '(ja.ja_complete_on IS NOT NULL)';
        $wheres[] = '(jad.jad_deleted_on IS NULL)';
        $wheres[] = '(jad.jad_jis_id IS NOT NULL)';

        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(ja.ja_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition !== 'A') {
            if ($condition === 'G') {
                $wheres[] = '(jid.jid_gdt_id IS NULL)';
            } else {
                $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
            }
        }
        return $wheres;
    }


    /**
     * Function to export data into excel file.
     *
     * @param string $period To store the period value
     *
     * @return string
     */
    private function getOriginAdjustmentQuery(string $period): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = "(ja.ja_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return "SELECT '" . $period . "' as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       jad.jad_quantity as ad_a, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_a_vl,
                       0 as ad_b, 0 as ad_b_vl
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id" . $strWheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentAdjustmentQuery(): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT ja.ja_complete_on::timestamp::date as period, 0 as in_a, 0 as in_a_vl,
                       0 as out_a, 0 as out_a_vl,
                       0 as in_b, 0 as in_b_vl,
                       0 as out_b, 0 as out_b_vl,
                       0 as mg_a, 0 as mg_a_vl,
                       0 as mg_b, 0 as mg_b_vl,
                       0 as md_a, 0 as md_a_vl,
                       0 as md_b, 0 as md_b_vl,
                       0 as ad_a, 0 as ad_a_vl,
                       jad.jad_quantity as ad_b, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_b_vl
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id ' . $strWheres;
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
            foreach ($this->Datas as $key => $portlet) {
                if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                    $sheetName = StringFormatter::formatExcelSheetTitle(trim($key));
                    $excel->addSheet($sheetName, $sheetName);
                    $excel->setFileName($this->PageSetting->getPageDescription() . ' ' . $this->getDate() . '.xlsx');
                    $sheet = $excel->getSheet($sheetName, true);
                    $sheet->mergeCells('A1:E1');
                    $sheet->setCellValue('A1', Trans::getWord('cbmReport'));
                    $sheet->getStyle('A1')->getFont()->setBold(true);

                    $sheet->mergeCells('A2:B2');
                    $sheet->setCellValue('A2', Trans::getWord('date'));
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->mergeCells('C2:E2');
                    $sheet->setCellValue('C2', $this->getDate());
                    $sheet->getStyle('C2')->getFont()->setBold(true);

                    $sheet->mergeCells('A3:B3');
                    $sheet->setCellValue('A3', Trans::getWord('warehouse'));
                    $sheet->getStyle('A3')->getFont()->setBold(true);
                    $sheet->mergeCells('C3:E3');
                    $sheet->setCellValue('C3', $this->getWarehouse());
                    $sheet->getStyle('C3')->getFont()->setBold(true);

                    $sheet->mergeCells('A4:B4');
                    $sheet->setCellValue('A4', Trans::getWord('relation'));
                    $sheet->getStyle('A4')->getFont()->setBold(true);
                    $sheet->mergeCells('C4:E4');
                    $sheet->setCellValue('C4', $this->getRelation());
                    $sheet->getStyle('C4')->getFont()->setBold(true);

                    $sheet->mergeCells('C5:E5');
                    $sheet->setCellValue('C5', $this->getGoods());
                    $sheet->getStyle('C5')->getFont()->setBold(true);
                    $sheet->mergeCells('A5:B5');
                    $sheet->setCellValue('A5', Trans::getWord('goods'));
                    $sheet->getStyle('A5')->getFont()->setBold(true);

                    $sheet->mergeCells('C6:E6');
                    $sheet->setCellValue('C6', $this->getCondition());
                    $sheet->getStyle('C6')->getFont()->setBold(true);
                    $sheet->mergeCells('A6:B6');
                    $sheet->setCellValue('A6', Trans::getWord('condition'));
                    $sheet->getStyle('A6')->getFont()->setBold(true);

                    $excel->doRowMovePointer($sheetName);
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
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getDate(): string
    {
        $period = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd M Y');
        if ($this->isValidParameter('until_date') === true) {
            $period .= ' - ' . DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd M Y');
        }
        return $period;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getWarehouse(): string
    {
        return $this->getStringParameter('warehouse', Trans::getWord('allWarehouse'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getRelation(): string
    {
        return $this->getStringParameter('rel_name', Trans::getWord('allRelation'));
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getGoods(): string
    {
        return $this->getStringParameter('gd_name', Trans::getWord('allGoods'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCondition(): string
    {
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition === 'G') {
            return Trans::getWord('good');
        }
        if ($condition === 'D') {
            return Trans::getWord('damage');
        }
        return Trans::getWord('allCondition');
    }
}
