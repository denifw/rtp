<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Statistic\Job\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 Spada
 */
class PackingNumberHistory extends AbstractStatisticModel
{

    /**
     * Property to store the data.
     *
     * @var array $Data
     */
    private $Data = [];

    /**
     * Property to store the data.
     *
     * @var array $Warehouses
     */
    private $Warehouses = [];

    /**
     * Property to store the data.
     *
     * @var array $WarehouseIds
     */
    private $WarehouseIds = [];

    /**
     * Property to store the data.
     *
     * @var array $FooterExcelData
     */
    private $FooterExcelData = [];

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'whPnHistory');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relId = '';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $relId = $this->User->getRelId();
        }
        $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $relId));

        $this->StatisticForm->addField(Trans::getWord('packingNumber'), $this->Field->getText('jid_packing_number', $this->getStringParameter('jid_packing_number')), true);
        $srtField = $this->Field->getSelect('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $srtField->addOption('Inbound', 1);
        $srtField->addOption('Outbound', 2);
        $srtField->addOption('Movement', 5);
        $srtField->addOption('Adjustment', 4);

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->setEnableNewButton(false);

        $whField = $this->Field->getSingleSelect('warehouse', 'warehouse', $this->getStringParameter('warehouse'));
        $whField->setHiddenField('wh_id', $this->getIntParameter('wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);


        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('from_date', $this->getStringParameter('from_date')), true);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('until_date', $this->getStringParameter('until_date')));
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('serviceTerm'), $srtField);
        $this->StatisticForm->setGridDimension(4);
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->isValidParameter('from_date') === true) {
            $this->Validation->checkDate('from_date', '', '', 'Y-m-d');
        }
        if ($this->isValidParameter('until_date') === true) {
            if ($this->isValidParameter('from_date') === true) {
                $this->Validation->checkDate('until_date', '', $this->getStringParameter('from_date'), 'Y-m-d');
            } else {
                $this->Validation->checkDate('until_date', '', '', 'Y-m-d');
            }
        }
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->loadData();
        foreach ($this->WarehouseIds as $id) {
            $title = $this->Warehouses[$id];
            $this->addContent('Wh' . $id, $this->getDetailView($this->Data[$id], $id, $title, $title));
        }
    }


    /**
     * Get query to get the quotation data.
     *
     * @return void
     */
    private function loadData(): void
    {
        $temp = [];
        if ($this->isValidParameter('jid_packing_number') === true || $this->isValidParameter('from_date') === true || $this->isValidParameter('until_date') === true) {
            $temp = $this->loadDatabaseRow($this->loadQuery());
        } else {
            $this->View->addErrorMessage(Trans::getWord('serialNumberHistoryStatistic', 'message'));
        }
        $this->doPrepareData($temp);
    }

    /**
     * Get query to get the quotation data.
     *
     * @return string
     */
    private function loadQuery(): string
    {
        $joWheres = [];
        $joWheres[] = '(jo.jo_deleted_on IS NULL)';
        $joWheres[] = "(gd.gd_packing = 'Y')";
        $joWheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $joWheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $joWheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('wh_id') === true) {
            $joWheres[] = '(wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('jid_packing_number') === true) {
            $joWheres[] = SqlHelper::generateStringCondition('jid.jid_packing_number', $this->getStringParameter('jid_packing_number'));
        }
        $srtId = $this->getIntParameter('jo_srt_id', 0);
        if ($srtId === 1) {
            $subQuery = $this->loadInboundQuery($joWheres);
        } elseif ($srtId === 2) {
            $subQuery = $this->loadOutboundQuery($joWheres);
        } elseif ($srtId === 4) {
            $subQuery = $this->loadAdjustmentQuery($joWheres);
        } elseif ($srtId === 5) {
            $subQuery = $this->loadMovementQuery($joWheres);
        } else {
            $subQuery = $this->loadInboundQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadOutboundQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadMovementQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadAdjustmentQuery($joWheres);
        }
        return 'SELECT jo_type, jo_id, jo_srt_id, jo_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                            container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number, jid_packing_number,
                            SUM(quantity) as quantity, uom_code, SUM(quantity * gd_weight) as total_weight, gdt_id, damage_type, wh_id,
                            SUM(quantity * gd_volume) as total_volume, gd_id, gd_name, gd_sku, br_name, gdc_name, customer, jid_expired_date
                FROM (' . $subQuery . ') as j
                GROUP BY jo_type, jo_id, jo_srt_id, jo_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                            container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number, jid_packing_number,
                            uom_code, gdt_id, damage_type, wh_id, gd_id, gd_name, gd_sku, br_name, gdc_name, customer, jid_expired_date
                ORDER BY j.wh_id, j.load_on DESC, j.jo_id';
    }

    /**
     * Get query inbound
     *
     * @param array $jiWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadInboundQuery(array $jiWheres): string
    {
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $jiWheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
            $jiWheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            if ($this->isValidParameter('from_date') === true) {
                $jiWheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
                $jiWheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
            }
            if ($this->isValidParameter('until_date') === true) {
                $jiWheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('until_date') . " 00:01:00')";
                $jiWheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
            }
        }
        $jiWheres[] = '(jid.jid_deleted_on IS NULL)';
        $jiWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jiWheres[] = "(jid.jid_adjustment = 'N')";
        $jiWheres[] = '(ji.ji_end_load_on IS NOT NULL)';

        $strJiWhere = ' WHERE ' . implode(' AND ', $jiWheres);
        return "SELECT 'IN' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            ji.ji_container_number as container_number, ji.ji_seal_number as seal_number, ji.ji_truck_number as truck_number,
                            wh.wh_name, whs.whs_name, ji.ji_end_load_on as load_on, shp.rel_name as shipper, jid.jid_lot_number, jid.jid_packing_number,
                            jid.jid_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_expired_date
                      FROM job_inbound_detail as jid INNER JOIN
                           job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                           goods_unit as gdu oN jid.jid_gdu_id = gdu.gdu_id INNER JOIN
                           job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                           goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                           goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                           warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                           relation as rel ON gd.gd_rel_id = rel.rel_id INNER JOIN
                           relation as shp ON ji.ji_rel_id = shp.rel_id INNER JOIN
                           warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN
                           goods_damage_type as gdt on jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                           sales_order as so ON ji.ji_so_id = so.so_id " . $strJiWhere;
    }

    /**
     * Get query outbound
     *
     * @param array $jobWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadOutboundQuery(array $jobWheres): string
    {
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $jobWheres[] = "(job.job_end_store_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
            $jobWheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            if ($this->isValidParameter('from_date') === true) {
                $jobWheres[] = "(job.job_end_store_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
                $jobWheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
            }
            if ($this->isValidParameter('until_date') === true) {
                $jobWheres[] = "(job.job_end_store_on >= '" . $this->getStringParameter('until_date') . " 00:01:00')";
                $jobWheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
            }
        }
        $jobWheres[] = '(jod.jod_deleted_on IS NULL)';
        $jobWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jobWheres[] = '(job.job_end_store_on IS NOT NULL)';
        $strJobWhere = ' WHERE ' . implode(' AND ', $jobWheres);
        return "SELECT 'OUT' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            job.job_container_number as container_number, job.job_seal_number as seal_number, job.job_truck_number as truck_number,
                            wh.wh_name, whs.whs_name, job.job_end_store_on as load_on, con.rel_name as shipper, jid.jid_lot_number, jid.jid_packing_number,
                            jod.jod_qty_loaded as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_expired_date
                      FROM job_outbound_detail as jod INNER JOIN
                           job_outbound as job ON jod.jod_job_id = job.job_id INNER JOIN
                           job_order as jo ON job.job_jo_id = jo.jo_id INNER JOIN
                           warehouse as wh ON job.job_wh_id = wh.wh_id INNER JOIN
                           job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id INNER JOIN
                           goods_unit as gdu ON jod.jod_gdu_id = gdu.gdu_id iNNER JOIN
                           goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                           goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                           relation as rel ON gd.gd_rel_id = rel.rel_id INNER JOIN
                           relation as con ON job.job_rel_id = con.rel_id INNER JOIN
                           warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN
                           goods_damage_type as gdt on jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                           sales_order as so ON job.job_so_id = so.so_id " . $strJobWhere;
    }

    /**
     * Get query movement
     *
     * @param array $jmWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadMovementQuery(array $jmWheres): string
    {
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $jmWheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
            $jmWheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            if ($this->isValidParameter('from_date') === true) {
                $jmWheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
                $jmWheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
            }
            if ($this->isValidParameter('until_date') === true) {
                $jmWheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('until_date') . " 00:01:00')";
                $jmWheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
            }
        }
        $jmWheres[] = '(jmd.jmd_deleted_on IS NULL)';
        $jmWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jmWheres[] = '(jm.jm_complete_on IS NOT NULL)';
        $strJmWhere = ' WHERE ' . implode(' AND ', $jmWheres);
        return "SELECT 'MOV' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, '' as so_number, jo.jo_aju_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                            jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                            '' as container_number, '' as seal_number, '' as truck_number,
                            wh.wh_name, whs.whs_name || '=>' || whsm.whs_name as whs_name , jm.jm_complete_on as load_on, '' as shipper, jid.jid_lot_number, jid.jid_packing_number,
                            jmd.jmd_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_expired_date
                      FROM job_movement_detail as jmd INNER JOIN
                           job_movement as jm ON jmd.jmd_jm_id = jm.jm_id INNER JOIN
                           job_order as jo ON jm.jm_jo_id = jo.jo_id INNER JOIN
                           warehouse as wh ON jm.jm_wh_id = wh.wh_id INNER JOIN
                           warehouse_storage as whs ON jm.jm_whs_id = whs.whs_id INNER JOIN
                           warehouse_storage as whsm ON jm.jm_new_whs_id = whsm.whs_id INNER JOIN
                           goods_unit as gdu ON jmd.jmd_gdu_id = gdu.gdu_id INNER JOIN
                           job_inbound_detail as jid ON jmd.jmd_jid_id = jid.jid_id INNER JOIN
                           goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                           unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                           goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           relation as rel ON gd.gd_rel_id = rel.rel_id LEFT OUTER JOIN
                           goods_damage_type as gdt on jmd.jmd_gdt_id = gdt.gdt_id" . $strJmWhere;
    }


    /**
     * Get query adjustment
     *
     * @param array $jaWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadAdjustmentQuery(array $jaWheres): string
    {
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $jaWheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
            $jaWheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            if ($this->isValidParameter('from_date') === true) {
                $jaWheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
                $jaWheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
            }
            if ($this->isValidParameter('until_date') === true) {
                $jaWheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('until_date') . " 00:01:00')";
                $jaWheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
            }
        }
        $jaWheres[] = '(jad.jad_deleted_on IS NULL)';
        $jmWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jaWheres[] = '(ja.ja_complete_on IS NOT NULL)';
        $strJaWhere = ' WHERE ' . implode(' AND ', $jaWheres);
        return "SELECT 'ADJ' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, '' as so_number, jo.jo_aju_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                        jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                        '' as container_number, '' as seal_number, '' as truck_number,
                        wh.wh_name, whs.whs_name as whs_name , ja.ja_complete_on as load_on, '' as shipper, jid.jid_lot_number, jid.jid_packing_number,
                        jad.jad_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                        jid.jid_gdt_id as gdt_id, sat.sat_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                        gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_expired_date
                      FROM job_adjustment_detail as jad INNER JOIN
                           stock_adjustment_type as sat ON jad.jad_sat_id = sat.sat_id INNER JOIN
                           job_adjustment as ja ON jad.jad_ja_id = ja.ja_id INNER JOIN
                           job_order as jo ON ja.ja_jo_id = jo.jo_id INNER JOIN
                           warehouse as wh ON ja.ja_wh_id = wh.wh_id INNER JOIN
                           goods_unit as gdu ON gdu.gdu_id = jad.jad_gdu_id INNER JOIN
                           job_inbound_detail as jid ON jad.jad_jid_id = jid.jid_id INNER JOIN
                           goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                           unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                           goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           relation as rel ON gd.gd_rel_id = rel.rel_id INNER JOIN
                           warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id " . $strJaWhere;
    }


    /**
     * Function to get the stock card table.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareData(array $data): void
    {
        $joDao = new JobOrderDao();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['start_on'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'd.M.Y');
            $row['gd_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
            if (empty($row['gdt_id']) === true) {
                $row['condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['condition'] = new LabelDanger(Trans::getWord('damage'));
            }
            $row['remark'] = $row['damage_type'];
            $row['jo_reference'] = $joDao->concatReference($row, '');
            if (in_array($row['wh_id'], $this->WarehouseIds, true) === false) {
                $this->WarehouseIds[] = $row['wh_id'];
                $this->Warehouses[$row['wh_id']] = $row['wh_name'];
                $this->Data[$row['wh_id']] = [];
            }
            $this->Data[$row['wh_id']][] = $row;
        }
    }

    /**
     * Function to get the stock card table.
     *
     * @param string $htmlId To store the title.
     *
     * @return Table
     */
    protected function getTableView(string $htmlId): Table
    {
        $table = new Table('Tbl' . $htmlId);
        $table->setHeaderRow([
            'start_on' => Trans::getWord('date'),
            'jo_number' => Trans::getWord('jobNumber'),
            'customer' => Trans::getWord('customer'),
            'gd_sku' => Trans::getWord('sku'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_packing_number' => Trans::getWord('packingNumber'),
            'jo_reference' => Trans::getWord('reference'),
            'whs_name' => Trans::getWord('storage'),
            'quantity' => Trans::getWord('quantity'),
            'uom_code' => Trans::getWord('uom'),
            'total_weight' => Trans::getWord('weight') . ' KG',
            'total_volume' => Trans::getWord('volume') . ' M3',
            'shipper' => 'Ship. / Consig.',
            'condition' => Trans::getWord('condition'),
            'remark' => Trans::getWord('remark'),
        ]);
        $table->addColumnAttribute('customer', 'style', 'text-align: center');
        $table->addColumnAttribute('gd_sku', 'style', 'text-align: center');
        $table->setColumnType('quantity', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->addColumnAttribute('jo_number', 'style', 'text-align: center');
        $table->addColumnAttribute('type', 'style', 'text-align: center');
        $table->addColumnAttribute('whs_name', 'style', 'text-align: center');
        $table->addColumnAttribute('condition', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center');
        return $table;
    }

    /**
     * Function to get the stock card table.
     *
     * @param array  $data       To store the data.
     * @param string $htmlId     To store the title.
     * @param string $title      To store the title.
     * @param string $excelTitle To store the title.
     *
     * @return Portlet
     */
    private function getDetailView(array $data, string $htmlId, string $title, string $excelTitle): Portlet
    {
        $in = 0;
        $inW = 0;
        $inV = 0;
        $out = 0;
        $outW = 0;
        $outV = 0;
        $mov = 0;
        $movW = 0;
        $movV = 0;
        $ad = 0;
        $adW = 0;
        $adV = 0;
        foreach ($data as $row) {
            if ($row['jo_type'] === 'IN') {
                $in += (float)$row['quantity'];
                $inW += (float)$row['total_weight'];
                $inV += (float)$row['total_volume'];
            } elseif ($row['jo_type'] === 'OUT') {
                $out += (float)$row['quantity'];
                $outV += (float)$row['total_volume'];
                $outW += (float)$row['total_weight'];
            } elseif ($row['jo_type'] === 'MOV') {
                $mov += (float)$row['quantity'];
                $movV += (float)$row['total_volume'];
                $movW += (float)$row['total_weight'];
            } else {
                $ad += (float)$row['quantity'];
                $adV += (float)$row['total_volume'];
                $adW += (float)$row['total_weight'];
            }
        }
        $table = $this->getTableView($htmlId);
        $table->addRows($data);
        $portlet = new Portlet('Ptl' . $htmlId, $title);
        $portlet->addTable($table);
        $this->addDatas($excelTitle, $portlet);
        $number = new NumberFormatter();
        $summary = '';
        $summary .= '<div class="col-xs-12">';
        $summary .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 pull-right">';
        $summary .= '<table style="font-weight: bold; width: 100%;">';
        $summary .= '<tr>';
        $summary .= '<td style="width: 30%" >' . Trans::getWord('totalInbound') . '</td>';
        $summary .= '<td style="width 20%; text-align: right">' . $number->doFormatFloat($in) . ' Items</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($inW) . ' KG</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($inV) . ' M3</td>';
        $summary .= '</tr>';
        $summary .= '<tr>';
        $summary .= '<td style="width: 30%">' . Trans::getWord('totalOutbound') . '</td>';
        $summary .= '<td style="width 20%; text-align: right">' . $number->doFormatFloat($out) . ' Items</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($outW) . ' KG</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($outV) . ' M3</td>';
        $summary .= '</tr>';
        $summary .= '<tr>';
        $summary .= '<td style="width: 30%">' . Trans::getWord('totalMovement') . '</td>';
        $summary .= '<td style="width 20%; text-align: right">' . $number->doFormatFloat($mov) . ' Items</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($movW) . ' KG</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($movV) . ' M3</td>';
        $summary .= '</tr>';
        $summary .= '<tr>';
        $summary .= '<td style="width: 30%">' . Trans::getWord('totalAdjustment') . '</td>';
        $summary .= '<td style="width 20%; text-align: right">' . $number->doFormatFloat($ad) . ' Items</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($adW) . ' KG</td>';
        $summary .= '<td style="width 25%; text-align: right">' . $number->doFormatFloat($adV) . ' M3</td>';
        $summary .= '</tr>';
        $summary .= '</table>';
        $summary .= '</div>';
        $summary .= '</div>';
        $portlet->addText($summary);
        $footerData[] = [
            'description' => Trans::getWord('totalInbound'),
            'items' => $in,
            'weight' => $inW,
            'volume' => $inV,
        ];
        $footerData[] = [
            'description' => Trans::getWord('totalOutbound'),
            'items' => $out,
            'weight' => $outW,
            'volume' => $outV,
        ];
        $footerData[] = [
            'description' => Trans::getWord('totalMovement'),
            'items' => $mov,
            'weight' => $movW,
            'volume' => $movV,
        ];
        $footerData[] = [
            'description' => Trans::getWord('totalAdjustment'),
            'items' => $ad,
            'weight' => $adW,
            'volume' => $adV,
        ];
        $tableFooter = new Table('Tbl' . $htmlId . 'Footer');
        $tableFooter->setHeaderRow([
            'description' => Trans::getWord('description'),
            'items' => Trans::getWord('items'),
            'weight' => Trans::getWord('weight') . ' (M3)',
            'volume' => Trans::getWord('volume') . ' (KG)',
        ]);
        $tableFooter->setDisableLineNumber();
        $tableFooter->setColumnType('items', 'float');
        $tableFooter->setColumnType('weight', 'float');
        $tableFooter->setColumnType('volume', 'float');
        $tableFooter->addRows($footerData);
        $this->FooterExcelData[$excelTitle] = $tableFooter;

        return $portlet;
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        $excel = new Excel();
        foreach ($this->Datas as $key => $portlet) {
            if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                $excel->setFileName($this->getXlsFileName());
                $sheetName = StringFormatter::formatExcelSheetTitle(trim($key));
                $excel->addSheet($sheetName, $sheetName);
                $sheet = $excel->getSheet($sheetName, true);
                $excelTable = new ExcelTable($excel, $sheet);
                $excelTable->setTable($portlet->Body[0]);
                $excelTable->writeTable();
                $excel->setActiveSheet($sheetName);
                if (empty($this->FooterExcelData[$key]) === false) {
                    $excel->doRowMovePointer($sheetName);
                    $footerTable = new ExcelTable($excel, $sheet);
                    $footerTable->setTable($this->FooterExcelData[$key]);
                    $footerTable->writeTable();
                }
                $excel->setActiveSheet($sheetName);
            }
        }
        $excel->createExcel();
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getXlsFileName(): string
    {
        $names = [];
        $names[] = 'SN History';
        if ($this->isValidParameter('gd_name') === true) {
            $name = explode(' - ', $this->getStringParameter('gd_name'))[0];
            $names[] = StringFormatter::replaceSpecialCharacter($name, '_');
        }
        if ($this->isValidParameter('jid_serial_number') === true) {
            $names[] = trim($this->getStringParameter('jid_serial_number'));
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $srtId = $this->getIntParameter('jo_srt_id');
            if ($srtId === 1) {
                $names[] = "IN";
            } elseif ($srtId === 2) {
                $names[] = "OUT";
            } elseif ($srtId === 4) {
                $names[] = "ADJ";
            } elseif ($srtId === 5) {
                $names[] = "MOV";
            }
        }
        if ($this->isValidParameter('from_date') === true) {
            $names[] = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd_m_y');
        }
        if ($this->isValidParameter('until_date') === true) {
            $names[] = DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd_m_y');
        }

        return implode(' - ', $names) . '.xlsx';
    }

}
