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
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Icon;
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
 * @copyright  2019 Spada
 */
class StockMilestone extends AbstractStatisticModel
{

    /**
     * Property to store the data.
     *
     * @var array $Data
     */
    protected $Data = [];

    /**
     * Property to store the data.
     *
     * @var array $Warehouses
     */
    protected $Warehouses = [];

    /**
     * Property to store the data.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * Property to store the data.
     *
     * @var array $WarehouseIds
     */
    protected $WarehouseIds = [];

    /**
     * Property to store the data.
     *
     * @var array $FooterExcelData
     */
    protected $FooterExcelData = [];

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockMilestone');
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

        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'), 'loadCompleteGoodsSingleSelect');
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->setEnableNewButton(false);

        if ($this->isValidParameter('view_by') === false) {
            $this->setParameter('view_by', 'W');
        }
        $viewField = $this->Field->getRadioGroup('view_by', $this->getStringParameter('view_by'));
        $viewField->addRadios([
            'W' => Trans::getWord('warehouse'),
            'G' => Trans::getWord('goods'),
        ]);

        $srtField = $this->Field->getSelect('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $srtField->addOption('Inbound', 1);
        $srtField->addOption('Outbound', 2);
        $srtField->addOption('Movement', 5);
        $srtField->addOption('Adjustment', 4);

        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
            $relationField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
            $relationField->addParameter('rel_ss_id', $this->User->getSsId());
            $relationField->setEnableNewButton(false);

            $this->StatisticForm->addField(Trans::getWord('relation'), $relationField);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $this->User->getRelId()));
        }
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('from_date', $this->getStringParameter('from_date')), true);
        $this->StatisticForm->addField(Trans::getWord('serviceTerm'), $srtField);
        $this->StatisticForm->addField(Trans::getWord('lotNumber'), $this->Field->getText('jid_lot_number', $this->getStringParameter('jid_lot_number')));
        $this->StatisticForm->addField(Trans::getWord('viewBy'), $viewField);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('until_date', $this->getStringParameter('until_date')));
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('from_date');
        $this->Validation->checkDate('from_date', '', '', 'Y-m-d');
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $this->Validation->checkDate('until_date', '', $this->getStringParameter('from_date'), 'Y-m-d');
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
            if ($this->getStringParameter('view_by') === 'W') {
                $title = $this->Warehouses[$id];
                $this->addContent('Wh' . $id, $this->getDetailView($this->Data[$id], $id, $title, $title));
            } else {
                $temp = $this->Data[$id];
                $keys = array_keys($temp);
                foreach ($keys as $gdId) {
                    $title = $this->Warehouses[$id] . ' - ' . $this->Goods[$gdId];
                    $arrTitle = explode('-', $this->Goods[$gdId]);
                    $excelTitle = $this->Warehouses[$id] . '-' . trim($arrTitle[0]);
                    $this->addContent('Wh' . $id . '-' . $gdId, $this->getDetailView($this->Data[$id][$gdId], $id . '-' . $gdId, $title, $excelTitle));
                }
            }
        }
    }


    /**
     * Get query to get the quotation data.
     *
     * @return void
     */
    private function loadData(): void
    {
        $temp = $this->loadDatabaseRow($this->loadQuery());
        if ($this->getStringParameter('view_by') === 'W') {
            $this->doPrepareWarehouseData($temp);
        } else {
            $this->doPrepareGoodsData($temp);
        }
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
        $joWheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('rel_id') === true) {
            $joWheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $joWheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('jid_lot_number') === true) {
            $joWheres[] = StringFormatter::generateLikeQuery('jid.jid_lot_number', $this->getStringParameter('jid_lot_number'));
        }
        if ($this->isValidParameter('wh_id') === true) {
            $joWheres[] = '(wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        $srtId = $this->getIntParameter('jo_srt_id', 0);
        if ($srtId === 1) {
            $subQuery = $this->loadInboundQuery($joWheres);
        } else if ($srtId === 2) {
            $subQuery = $this->loadOutboundQuery($joWheres);
        } else if ($srtId === 4) {
            $subQuery = $this->loadAdjustmentQuery($joWheres);
        } else if ($srtId === 5) {
            $subQuery = $this->loadMovementQuery($joWheres);
        } else {
            $subQuery = $this->loadInboundQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadOutboundQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadMovementQuery($joWheres);
            $subQuery .= ' UNION ALL ' . $this->loadAdjustmentQuery($joWheres);
        }
        return 'SELECT jo_type, jo_id, jo_srt_id, jo_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                            container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number,
                            quantity, uom_code, gd_weight, gdt_id, damage_type, wh_id, gd_volume,
                            gd_id, gd_name, gd_sku, br_name, gdc_name, customer, shipper_address, shipper_district, shipper_city, remark, shipper_pic
                FROM (' . $subQuery . ') as j
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
        if ($this->isValidParameter('from_date') === true) {
            $jiWheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
        }
        if ($this->isValidParameter('until_date') === true) {
            $jiWheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            $jiWheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
        }
        $jiWheres[] = '(jid.jid_deleted_on IS NULL)';
        $jiWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jiWheres[] = "(jid.jid_adjustment = 'N')";
        $jiWheres[] = '(ji.ji_end_load_on IS NOT NULL)';

        $strJiWhere = ' WHERE ' . implode(' AND ', $jiWheres);
        $query = "SELECT 'IN' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            ji.ji_container_number as container_number, ji.ji_seal_number as seal_number, ji.ji_truck_number as truck_number,
                            wh.wh_name, whs.whs_name, ji.ji_end_load_on as load_on, shp.rel_name as shipper, jid.jid_lot_number,
                            SUM(jid.jid_quantity) as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer,
                            co.of_address as shipper_address, dtc.dtc_name as shipper_district, cty.cty_name as shipper_city ,'' as remark, ccp.cp_name AS shipper_pic
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
                           sales_order as so ON ji.ji_so_id = so.so_id LEFT OUTER JOIN
                            office as co ON ji.ji_of_id = co.of_id LEFT OUTER JOIN
                             city as cty ON co.of_cty_id = cty.cty_id LEFT OUTER JOIN
                             district as dtc ON co.of_dtc_id = dtc.dtc_id LEFT OUTER JOIN
                           contact_person AS ccp ON ccp.cp_id = ji.ji_cp_id " . $strJiWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, so.so_customer_ref, jo.jo_customer_ref,
                    so.so_aju_ref, jo.jo_aju_ref, so.so_bl_ref, jo.jo_bl_ref, so.so_packing_ref, jo.jo_packing_ref, so.so_sppb_ref, jo.jo_sppb_ref,
                    ji.ji_container_number, ji.ji_seal_number, ji.ji_truck_number,
                    wh.wh_name, whs.whs_name, ji.ji_end_load_on, shp.rel_name, jid.jid_lot_number,
                    uom.uom_code, jid.jid_weight, gdu.gdu_weight,
                    gdt.gdt_id, gdt.gdt_description, wh.wh_id, jid.jid_volume, gdu.gdu_volume,
                    gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name,
                            co.of_address, dtc.dtc_name, cty.cty_name, ccp.cp_name';

        return $query;
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
        if ($this->isValidParameter('from_date') === true) {
            $jobWheres[] = "(job.job_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
        }
        if ($this->isValidParameter('until_date') === true) {
            $jobWheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            $jobWheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
        }
        $jobWheres[] = '(jod.jod_deleted_on IS NULL)';
        $jobWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jobWheres[] = '(job.job_end_load_on IS NOT NULL)';
        $strJobWhere = ' WHERE ' . implode(' AND ', $jobWheres);
        $query = "SELECT 'OUT' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            job.job_container_number as container_number, job.job_seal_number as seal_number, job.job_truck_number as truck_number,
                            wh.wh_name, whs.whs_name, job.job_end_load_on as load_on, con.rel_name as shipper, jid.jid_lot_number,
                            SUM(jod.jod_qty_loaded) as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer,
                            co.of_address as shipper_address, dtc.dtc_name as shipper_district, cty.cty_name as shipper_city ,'' as remark, ccp.cp_name AS shipper_pic
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
                           sales_order as so ON job.job_so_id = so.so_id LEFT OUTER JOIN
                            office as co ON job.job_of_id = co.of_id LEFT OUTER JOIN
                             city as cty ON co.of_cty_id = cty.cty_id LEFT OUTER JOIN
                             district as dtc ON co.of_dtc_id = dtc.dtc_id LEFT OUTER JOIN
                           contact_person AS ccp ON ccp.cp_id = job.job_cp_id " . $strJobWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, so.so_customer_ref, jo.jo_customer_ref,
                        so.so_aju_ref, jo.jo_aju_ref,
                        so.so_bl_ref, jo.jo_bl_ref,
                        so.so_packing_ref, jo.jo_packing_ref,
                        so.so_sppb_ref, jo.jo_sppb_ref,
                        job.job_container_number, job.job_seal_number, job.job_truck_number,
                        wh.wh_name, whs.whs_name, job.job_end_load_on, con.rel_name, jid.jid_lot_number,
                        uom.uom_code, jid.jid_weight, gdu.gdu_weight,
                        gdt.gdt_id, gdt.gdt_description, wh.wh_id, jid.jid_volume, gdu.gdu_volume,
                        gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name,
                            co.of_address, dtc.dtc_name, cty.cty_name, ccp.cp_name';
        return $query;
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
        if ($this->isValidParameter('from_date') === true) {
            $jmWheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
        }
        if ($this->isValidParameter('until_date') === true) {
            $jmWheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            $jmWheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
        }
        $jmWheres[] = '(jmd.jmd_deleted_on IS NULL)';
        $jmWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jmWheres[] = '(jm.jm_complete_on IS NOT NULL)';
        $strJmWhere = ' WHERE ' . implode(' AND ', $jmWheres);
        return "SELECT 'MOV' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, '' as so_number, jo.jo_aju_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                            jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                            '' as container_number, '' as seal_number, '' as truck_number,
                            wh.wh_name, whs.whs_name || '=>' || whsm.whs_name as whs_name , jm.jm_complete_on as load_on, '' as shipper, jid.jid_lot_number,
                            jmd.jmd_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer,
                            '' as shipper_address, '' as shipper_district, '' as shipper_city,jm.jm_remark as remark, '' AS shipper_pic
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
                           goods_damage_type as gdt on jmd.jmd_gdt_id = gdt.gdt_id " . $strJmWhere;
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
        if ($this->isValidParameter('from_date') === true) {
            $jaWheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:01:00')";
        }
        if ($this->isValidParameter('until_date') === true) {
            $jaWheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:00')";
        } else {
            $jaWheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:00')";
        }
        $jaWheres[] = '(jad.jad_deleted_on IS NULL)';
        $jmWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jaWheres[] = '(ja.ja_complete_on IS NOT NULL)';
        $strJaWhere = ' WHERE ' . implode(' AND ', $jaWheres);
        return "SELECT 'ADJ' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, '' as so_number, jo.jo_aju_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                        jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                        '' as container_number, '' as seal_number, '' as truck_number,
                        wh.wh_name, whs.whs_name as whs_name , ja.ja_complete_on as load_on, '' as shipper, jid.jid_lot_number,
                        jad.jad_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                        jid.jid_gdt_id as gdt_id, sat.sat_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                        gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer,
                            '' as shipper_address, '' as shipper_district, '' as shipper_city ,sat.sat_description as remark, '' AS shipper_pic
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
    private function doPrepareWarehouseData(array $data): void
    {
        $joDao = new JobOrderDao();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $volume = (float)$row['quantity'] * (float)$row['gd_volume'];
            $weight = (float)$row['quantity'] * (float)$row['gd_weight'];
            $row['total_volume'] = $volume;
            $row['total_weight'] = $weight;
            $row['start_on'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'd.M.Y');
            $row['gd_full_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
            if (empty($row['gdt_id']) === true) {
                $row['condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['condition'] = new LabelDanger(Trans::getWord('damage'));
            }
            if (empty($row['shipper_district']) === false) {
                $row['shipper_address'] .= ' ' . $row['shipper_district'];
            }
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
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareGoodsData(array $data): void
    {
        $joDao = new JobOrderDao();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $volume = (float)$row['quantity'] * (float)$row['gd_volume'];
            $weight = (float)$row['quantity'] * (float)$row['gd_weight'];
            $row['gd_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
            $row['total_volume'] = $volume;
            $row['total_weight'] = $weight;
            $row['start_on'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'd.M.Y');
            if (empty($row['gdt_id']) === true) {
                $row['condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['condition'] = new LabelDanger(Trans::getWord('damage'));
            }
            if (empty($row['shipper_district']) === false) {
                $row['shipper_address'] .= ' ' . $row['shipper_district'];
            }
            $row['remark'] = $row['damage_type'];
            $row['jo_reference'] = $joDao->concatReference($row, '');
            if (in_array($row['wh_id'], $this->WarehouseIds, true) === false) {
                $this->WarehouseIds[] = $row['wh_id'];
                $this->Warehouses[$row['wh_id']] = $row['wh_name'];
                $this->Data[$row['wh_id']] = [];
            }
            if (array_key_exists($row['gd_id'], $this->Data[$row['wh_id']]) === false) {
                $this->Goods[$row['gd_id']] = $row['gd_sku'] . ' - ' . $row['gd_name'] . ' - ' . $row['customer'];
                $this->Data[$row['wh_id']][$row['gd_id']] = [];
            }
            $this->Data[$row['wh_id']][$row['gd_id']][] = $row;
        }
    }

    /**
     * Function to get the detail table.
     *
     * @param string $htmlId To store the title.
     *
     * @return Table
     */
    protected function getDetailTable($htmlId): Table
    {
        $table = new Table('Tbl' . $htmlId);
        $table->setHeaderRow([
            'start_on' => Trans::getWord('date'),
            'jo_number' => Trans::getWord('jobNumber'),
            'jid_lot_number' => Trans::getWord('lot'),
            'jo_reference' => Trans::getWord('reference'),
            'whs_name' => Trans::getWord('storage'),
            'quantity' => Trans::getWord('quantity'),
            'uom_code' => Trans::getWord('uom'),
            'total_volume' => Trans::getWord('volume') . ' (M3)',
            'total_weight' => Trans::getWord('weight') . ' (KG)',
            'shipper' => 'Ship. / Consig.',
            'condition' => Trans::getWord('condition'),
            'remark' => Trans::getWord('remark'),
        ]);
        if ($this->isValidParameter('jo_srt_id')) {
            $srtId = $this->getIntParameter('jo_srt_id');
            $isExportExls = $this->getFormAction() === 'doExportXls';
            if ($srtId === 1) {
                $table->renameColumn('shipper', Trans::getWord('shipper'));
                if ($isExportExls) {
                    $table->addColumnAfter('shipper', 'shipper_address', Trans::getWord('address'));
                    $table->addColumnAfter('shipper_address', 'shipper_city', Trans::getWord('city'));
                }
            } else if ($srtId === 2) {
                $table->renameColumn('shipper', Trans::getWord('consignee'));
                if ($isExportExls) {
                    $table->addColumnAfter('shipper', 'shipper_address', Trans::getWord('address'));
                    $table->addColumnAfter('shipper_address', 'shipper_city', Trans::getWord('city'));
                }
            } else {
                $table->removeColumn('shipper');
            }
        }
        if ($this->getStringParameter('view_by') === 'W') {
            $table->addColumnAfter('jo_number', 'customer', Trans::getWord('customer'));
            $table->addColumnAfter('customer', 'gd_sku', Trans::getWord('sku'));
            if ($this->getFormAction() === 'doExportXls') {
                $table->addColumnAfter('gd_sku', 'br_name', Trans::getWord('brand'));
                $table->addColumnAfter('br_name', 'gdc_name', Trans::getWord('category'));
                $table->addColumnAfter('gdc_name', 'gd_name', Trans::getWord('goods'));

            } else {
                $table->addColumnAfter('gd_sku', 'gd_full_name', Trans::getWord('goods'));
            }
            $table->addColumnAttribute('customer', 'style', 'text-align: center');
        }
        $table->setColumnType('quantity', 'integer');
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
        $footerData = [];
        $table = $this->getDetailTable($htmlId);
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
            } else if ($row['jo_type'] === 'OUT') {
                $out += (float)$row['quantity'];
                $outV += (float)$row['total_volume'];
                $outW += (float)$row['total_weight'];
            } else if ($row['jo_type'] === 'MOV') {
                $mov += (float)$row['quantity'];
                $movV += (float)$row['total_volume'];
                $movW += (float)$row['total_weight'];
            } else {
                $ad += (float)$row['quantity'];
                $adV += (float)$row['total_volume'];
                $adW += (float)$row['total_weight'];
            }
        }
        $table->addRows($data);
        $portlet = new Portlet('Ptl' . $htmlId, $title);
        $portlet->addTable($table);
        $this->addDatas($excelTitle, $portlet);
        $number = new NumberFormatter();
        $summary = '';
        $summary .= '<div class="col-xs-12">';
        $summary .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 pull-right">';
        $summary .= '<table width="100%" style="font-weight: bold;">';
        if ($this->isValidParameter('jo_srt_id') === false || $this->getIntParameter('jo_srt_id') === 1) {
            $summary .= '<tr>';
            $summary .= '<td width="30%">' . Trans::getWord('totalInbound') . '</td>';
            $summary .= '<td width="20%" style="text-align: right">' . $number->doFormatFloat($in) . ' Items</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($inW) . ' KG</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($inV) . ' M3</td>';
            $summary .= '</tr>';
            $footerData[] = [
                'description' => Trans::getWord('totalInbound'),
                'items' => $in,
                'weight' => $inW,
                'volume' => $inV,
            ];
        }
        if ($this->isValidParameter('jo_srt_id') === false || $this->getIntParameter('jo_srt_id') === 2) {
            $summary .= '<tr>';
            $summary .= '<td width="30%">' . Trans::getWord('totalOutbound') . '</td>';
            $summary .= '<td width="20%" style="text-align: right">' . $number->doFormatFloat($out) . ' Items</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($outW) . ' KG</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($outV) . ' M3</td>';
            $summary .= '</tr>';
            $footerData[] = [
                'description' => Trans::getWord('totalOutbound'),
                'items' => $out,
                'weight' => $outW,
                'volume' => $outV,
            ];
        }
        if ($this->isValidParameter('jo_srt_id') === false || $this->getIntParameter('jo_srt_id') === 5) {
            $summary .= '<tr>';
            $summary .= '<td width="30%">' . Trans::getWord('totalMovement') . '</td>';
            $summary .= '<td width="20%" style="text-align: right">' . $number->doFormatFloat($mov) . ' Items</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($movW) . ' KG</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($movV) . ' M3</td>';
            $summary .= '</tr>';
            $footerData[] = [
                'description' => Trans::getWord('totalMovement'),
                'items' => $mov,
                'weight' => $movW,
                'volume' => $movV,
            ];
        }
        if ($this->isValidParameter('jo_srt_id') === false || $this->getIntParameter('jo_srt_id') === 4) {
            $summary .= '<tr>';
            $summary .= '<td width="30%">' . Trans::getWord('totalAdjustment') . '</td>';
            $summary .= '<td width="20%" style="text-align: right">' . $number->doFormatFloat($ad) . ' Items</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($adW) . ' KG</td>';
            $summary .= '<td width="25%" style="text-align: right">' . $number->doFormatFloat($adV) . ' M3</td>';
            $summary .= '</tr>';
            $footerData[] = [
                'description' => Trans::getWord('totalAdjustment'),
                'items' => $ad,
                'weight' => $adW,
                'volume' => $adV,
            ];
        }
        $summary .= '</table>';
        $summary .= '</div>';
        $summary .= '</div>';
        $portlet->addText($summary);
        $tableFooter = new Table('Tbl' . $htmlId . 'Footer');
        $tableFooter->setHeaderRow([
            'description' => Trans::getWord('description'),
            'items' => Trans::getWord('items'),
            'weight' => Trans::getWord('weight') . ' (KG)',
            'volume' => Trans::getWord('volume') . ' (M3)',
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
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        parent::loadDefaultButton();
        $this->View->setNumberOfButton(4);
        if (empty($this->Data) === false) {
            $pdfButton = new PdfButton('SmPdf', Trans::getWord('printPdf'), 'stockmilestone');
            $pdfButton->setIcon(Icon::FilePdfO)->btnDanger()->pullRight()->btnMedium();
            $pdfButton->addParameter('ss_id', $this->User->getSsId());
            if ($this->isValidParameter('rel_id')) {
                $pdfButton->addParameter('rel_id', $this->getIntParameter('rel_id'));
            }
            if ($this->isValidParameter('gd_id')) {
                $pdfButton->addParameter('gd_id', $this->getIntParameter('gd_id'));
            }
            if ($this->isValidParameter('wh_id')) {
                $pdfButton->addParameter('wh_id', $this->getIntParameter('wh_id'));
            }
            if ($this->isValidParameter('from_date')) {
                $pdfButton->addParameter('from_date', $this->getStringParameter('from_date'));
            }
            if ($this->isValidParameter('until_date')) {
                $pdfButton->addParameter('until_date', $this->getStringParameter('until_date'));
            }
            if ($this->isValidParameter('jid_lot_number')) {
                $pdfButton->addParameter('jid_lot_number', $this->getStringParameter('jid_lot_number'));
            }
            if ($this->isValidParameter('view_by')) {
                $pdfButton->addParameter('view_by', $this->getStringParameter('view_by'));
            }
            if ($this->isValidParameter('jo_srt_id')) {
                $pdfButton->addParameter('jo_srt_id', $this->getIntParameter('jo_srt_id'));
            }
            $this->View->addButtonAfter($pdfButton, 'btnExportXls');
        }
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        $excel = new Excel();
        $periode = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd M Y');
        if ($this->isValidParameter('until_date') === true) {
            $periode .= ' - ' . DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd M Y');
        }

        foreach ($this->Datas as $key => $portlet) {
            if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                $sheetName = StringFormatter::formatExcelSheetTitle(trim($key));
                $excel->addSheet($sheetName, $sheetName);
                $excel->setFileName($this->PageSetting->getPageDescription() . ' ' . $periode . '.xlsx');
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
            }
        }
        $excel->createExcel();
    }
}
