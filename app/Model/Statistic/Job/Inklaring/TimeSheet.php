<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Job\Inklaring;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use App\Model\Dao\Job\JobOrderDao;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class TimeSheet extends AbstractStatisticModel
{

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'inkTimeSheet');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'));
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $this->StatisticForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->StatisticForm->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->StatisticForm->addField(Trans::getWord('orderDateFrom'), $this->Field->getCalendar('order_date_from', $this->getStringParameter('order_date_from')));
        $this->StatisticForm->addField(Trans::getWord('orderDateUntil'), $this->Field->getCalendar('order_date_until', $this->getStringParameter('order_date_until')));
        $this->StatisticForm->addField(Trans::getWord('complete'), $this->Field->getYesNo('jo_complete', $this->getStringParameter('jo_complete', 'N')));
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $this->StatisticForm->addField(Trans::getWord('customer'), $relField);
        }

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getStringParameter('jo_complete', 'N') === 'Y') {
            $this->Validation->checkRequire('order_date_from');
            $this->Validation->checkRequire('order_date_until');
        }
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->addContent('TimeSheet', $this->getResultPortlet());
    }


    /**
     * Function to get the report portlet.
     *
     * @return Portlet
     */
    protected function getResultPortlet(): Portlet
    {
        $table = new Table('InkTsTbl');
        $header = [
            'jo_number' => Trans::getWord('jobNumber'),
            'so_customer' => Trans::getWord('customer'),
            'jo_reference' => Trans::getWord('reference'),
            'jik_transport' => Trans::getWord('transport'),
            'jik_ata' => Trans::getWord('arriveDepart'),
            'jac_drafting' => Trans::getWord('drafting'),
            'jac_register' => Trans::getWord('registration'),
            'jac_port' => Trans::getWord('portRelease'),
            'jac_goods' => Trans::getWord('goodsRelease'),
            'jo_action' => Trans::getWord('action'),
        ];
        if ($this->getFormAction() === 'doExportXls') {
            $header = [
                'jo_number' => Trans::getWord('jobNumber'),
                'so_customer' => Trans::getWord('customer'),
                'jo_reference' => Trans::getWord('reference'),
                'jik_transport' => Trans::getWord('transport'),
                'jik_ata' => Trans::getWord('arriveDepart'),
                'jac_drafting_start' => Trans::getWord('startDrafting'),
                'jac_drafting_end' => Trans::getWord('endDrafting'),
                'jac_drafting_duration' => Trans::getWord('draftingDuration'),
                'jac_drafting_status' => Trans::getWord('draftingStatus'),
                'jac_register_start' => Trans::getWord('startRegistration'),
                'jac_register_end' => Trans::getWord('endRegistration'),
                'jac_register_duration' => Trans::getWord('registrationDuration'),
                'jac_register_status' => Trans::getWord('registrationStatus'),
                'jac_port_start' => Trans::getWord('startPortRelease'),
                'jac_port_end' => Trans::getWord('endPortRelease'),
                'jac_port_duration' => Trans::getWord('portReleaseDuration'),
                'jac_port_status' => Trans::getWord('portReleaseStatus'),
                'jac_goods_start' => Trans::getWord('startGoodsRelease'),
                'jac_goods_end' => Trans::getWord('endGoodsRelease'),
                'jac_goods_duration' => Trans::getWord('goodsReleaseDuration'),
                'jac_goods_status' => Trans::getWord('goodsReleaseStatus'),
            ];
        }
        $table->setHeaderRow($header);
        $table->addRows($this->doPrepareData());
        $portlet = new Portlet('RslPtl', Trans::getWord('timeSheet'));
        $portlet->addTable($table);
        $this->addDatas('TimeSheet', $portlet);

        return $portlet;
    }

    /**
     * Function to prepare the data.
     *
     * @return array
     */
    protected function doPrepareData(): array
    {
        $results = [];
        $data = $this->loadData();
        $tempId = [];
        $joDao = new JobOrderDao();
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            if (in_array($row['jo_id'], $tempId, true) === false) {
                $ata = '';
                if($row['srt_route'] === 'jiec' || $row['srt_route'] === 'jie') {
                    if (empty($row['so_atd_date']) === false) {
                        if (empty($row['so_atd_time']) === false) {
                            $ata = $dt->formatDateTime($row['so_atd_date'] . ' ' . $row['so_atd_time']);
                        } else {
                            $ata = $dt->formatDate($row['so_atd_date']);
                        }
                    }
                } else {
                    if (empty($row['so_ata_date']) === false) {
                        if (empty($row['so_ata_time']) === false) {
                            $ata = $dt->formatDateTime($row['so_ata_date'] . ' ' . $row['so_ata_time']);
                        } else {
                            $ata = $dt->formatDate($row['so_ata_date']);
                        }
                    }
                }
                if (empty($ata) === true) {
                    $row['jik_ata'] = new LabelGray(Trans::getWord('waiting'));
                } else {
                    $row['jik_ata'] = $ata;
                }
                $row['jik_transport'] = StringFormatter::generateTableView([
                    $row['transport_name'], $row['so_transport_number']
                ]);
                $row['jo_reference'] = $joDao->concatReference($row, '');
                $btn = new HyperLink('BtnView' . $row['jo_id'], '', $joDao->getJobUrl('view', (int)$row['jo_srt_id'], $row['jo_id']), true);
                $btn->viewAsButton();
                $btn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
                $row['jo_action'] = $btn;
                $results[] = $this->doPivotTable($row);
                $tempId[] = $row['jo_id'];
            } else {
                $index = array_search($row['jo_id'], $tempId, true);
                $temp = $this->doPivotTable($row, $results[$index]);
                $results[$index] = $temp;
            }
        }

        return $results;
    }

    /**
     * Function to do pivot table.
     *
     * @param array $row To store temp result data.
     * @param array $result To store temp result data.
     *
     * @return array
     */
    private function doPivotTable(array $row, array $result = []): array
    {
        if (empty($result) === true) {
            $result = $row;
        }
        $action = $this->doCalculateTime($row);
        if ($row['ac_code'] === 'Drafting') {
            $result = $this->doMergeResults('drafting', $action, $result);
        }
        if ($row['ac_code'] === 'Register') {
            $result = $this->doMergeResults('register', $action, $result);
        }
        if ($row['ac_code'] === 'PortRelease') {
            $result = $this->doMergeResults('port', $action, $result);
        }
        if ($row['ac_code'] === 'ReleaseGoods' || $row['ac_code'] === 'Shipment') {
            $result = $this->doMergeResults('goods', $action, $result);
        }
        return $result;
    }

    /**
     * Function to do pivot table.
     *
     * @param string $code To store temp result data.
     * @param array $action To store temp result data.
     * @param array $result To store temp result data.
     *
     * @return array
     */
    private function doMergeResults(string $code, array $action, array $result): array
    {
        $result['jac_' . $code . '_start'] = $action['start'];
        $result['jac_' . $code . '_end'] = $action['end'];
        $result['jac_' . $code . '_duration'] = $action['duration'];
        $result['jac_' . $code . '_status'] = $action['status'];
        $result['jac_' . $code] = StringFormatter::generateKeyValueTableView([
            [
                'label' => Trans::getWord('start'),
                'value' => $action['start']
            ],
            [
                'label' => Trans::getWord('end'),
                'value' => $action['end']
            ],
            [
                'label' => Trans::getWord('duration'),
                'value' => $action['duration']
            ],
            [
                'label' => '',
                'value' => $action['status']
            ],
        ], 'label', 'value', true);
        return $result;
    }

    /**
     * Function to do pivot table.
     *
     * @param array $row To store temp result data.
     *
     * @return array
     */
    private function doCalculateTime(array $row): array
    {
        $dt = new DateTimeParser();
        $number = new NumberFormatter();
        $startDate = null;
        $start = '';
        $end = '';
        $duration = '';
        $status = new LabelGray(Trans::getWord('waiting'));
        if (empty($row['jac_start_date']) === false) {
            $start = $dt->formatDateTime($row['jac_start_date'] . ' ' . $row['jac_start_time']);
            $status = new LabelWarning(Trans::getWord('inProgress'));
        }
        if (empty($row['jac_end_date']) === false) {
            $end = $dt->formatDateTime($row['jac_end_date'] . ' ' . $row['jac_end_time']);
            $status = new LabelSuccess(Trans::getWord('complete'));
        }
        if (empty($row['jac_start_date']) === false && empty($row['jac_end_date']) === false) {
            $startDate = DateTimeParser::createFromFormat($row['jac_start_date'] . ' ' . $row['jac_start_time']);
            $endDate = DateTimeParser::createFromFormat($row['jac_end_date'] . ' ' . $row['jac_end_time']);
            $diff = DateTimeParser::different($endDate, $startDate);
            $day = $diff['days'];
            $hour = $diff['h'];
            $min = $diff['i'];
            $arr = [];
            if ($day > 0) {
                $arr[] = $number->doFormatInteger($day) . 'd';
            }
            if ($hour > 0 || $day > 0) {
                $arr[] = $number->doFormatInteger($hour) . 'h';
            }
            $arr[] = $number->doFormatInteger($min) . 'm';
            $duration = implode(' ', $arr);
        }
        return [
            'start' => $start,
            'end' => $end,
            'duration' => $duration,
            'status' => $status,
        ];
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function loadData(): array
    {

        $query = 'SELECT jo.jo_id, jo.jo_number, so.so_number, jo.jo_srt_id, srt.srt_name, ac.ac_code, jac.jac_order,
                        jac.jac_start_date, jac.jac_start_time, jac.jac_end_date, jac.jac_end_time,
                        rel.rel_name as so_customer, so.so_transport_name as transport_name, so.so_transport_number,
                        so.so_ata_date, so.so_ata_time, so.so_atd_date, so.so_atd_time,
                           so.so_customer_ref as customer_ref, so.so_aju_ref as aju_ref,
                           so.so_bl_ref as bl_ref, so.so_packing_ref as packing_ref,
                           so.so_sppb_ref as sppb_ref, srt.srt_route
                    FROM job_action as jac
                        INNER JOIN action as ac on jac.jac_ac_id = ac.ac_id
                        INNER JOIN job_order as jo ON jac.jac_jo_id = jo.jo_id
                        INNER JOIN service_term as srt ON ac.ac_srt_id = srt.srt_id
                        INNER JOIN job_inklaring as jik ON jo.jo_id = jik.jik_jo_id
                        INNER JOIN sales_order as so ON jik.jik_so_id = so.so_id
                        INNER JOIN relation as rel ON so.so_rel_id = rel.rel_id' . $this->getWhereConditions();
        $query .= ' ORDER BY jo.jo_id, jac.jac_order';
        $sqlResults = DB::select($query);
        $results = [];
        if (empty($sqlResults) === false) {
            $results = DataParser::arrayObjectToArray($sqlResults);
        }
        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return string
     */
    private function getWhereConditions(): string
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $this->User->getSsId());
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->getStringParameter('jo_complete', 'N') === 'N') {
            $wheres[] = '(jo.jo_finish_on IS NULL)';
        } else {
            $wheres[] = '(jo.jo_finish_on IS NOT NULL)';
        }
        $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
        if ($this->isValidParameter('jo_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number'));
        }
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_rel_id', $this->User->getRelId());
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_rel_id', $this->getIntParameter('jo_rel_id'));
        }
        if ($this->isValidParameter('jo_reference') === true) {
            $wheres[] = SqlHelper::generateOrLikeCondition([
                'so.so_number',
                'so.so_customer_ref',
                'so.so_bl_ref',
                'so.so_packing_ref',
                'so.so_aju_ref',
                'so.so_sppb_ref',
            ], $this->getStringParameter('jo_reference'));
        }
        if ($this->isValidParameter('order_date_from') === true) {
            $operator = '=';
            if ($this->isValidParameter('order_date_until') === true) {
                $operator = '>=';
            }
            $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_from'), $operator);
        }
        if ($this->isValidParameter('order_date_until') === true) {
            $operator = '=';
            if ($this->isValidParameter('order_date_from') === true) {
                $operator = '<=';
            }
            $wheres[] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('order_date_until'), $operator);
        }

        return ' WHERE ' . implode(' AND ', $wheres);
    }
}
