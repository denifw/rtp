<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockMilestone;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DefaultTemplate extends AbstractBasePdf
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
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('stockMilestone') . '.pdf');
    }

    /**
     * Function to set the content to pdf.
     *
     * @return void
     */
    public function loadContent(): void
    {
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $header = $this->getHeader();
            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('L', '', '', '1', '', 5, 5, $topMargin, 10, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');

            $content = '';
            $i = 1;
            foreach ($this->WarehouseIds as $id) {
                if ($this->getStringParameter('view_by') === 'W') {
                    $content .= '<p class="title-4" style="font-weight: bold"> ' . $i . '. ' . $this->Warehouses[$id] . '</p>';
                    $content .= $this->getDetailView($id);
                    $i++;
                } else {
                    $temp = $this->Data[$id];
                    $keys = array_keys($temp);
                    foreach ($keys as $gdId) {
                        $title = $this->Warehouses[$id] . ' - ' . $this->Goods[$gdId];
                        $content .= '<p class="title-4" style="font-weight: bold"> ' . $i . '. ' . $title . '</p>';
                        $content .= $this->getDetailView($id, $gdId);
                        $i++;
                    }
                }
            }
            $this->MPdf->WriteHTML($content);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to get the system setting header.
     *
     * @return string
     */

    private function getHeader(): string
    {
        $date = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd M Y');
        if ($this->isValidParameter('until_date') === true) {
            $date .= ' - ' . DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd M Y');
        }
        $header = '<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: middle; font-family: sans-serif; font-size: 18pt; color: #000088;">';
        $header .= '<tr>';
        $header .= '<td width="50%" style="text-align: left">' . Trans::getWord('stockMilestone') . '</td>';
        $header .= '<td width="50%" align="right"><span style="font-size:11pt;">' . $date . '</span></td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }

    /**
     * Function to get the stock card table.
     *
     * @param int $whId To store the warehouse id.
     * @param int $gdId To store the warehouse id.
     *
     * @return string
     */
    protected function getDetailView($whId, $gdId = 0): string
    {
        $table = new TablePdf('Tbl' . $whId . $gdId);
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
        $data = $this->Data[$whId];
        if ($this->getStringParameter('view_by') === 'W') {
            $table->addColumnAfter('jo_number', 'customer', Trans::getWord('customer'));
            $table->addColumnAfter('customer', 'gd_sku', Trans::getWord('sku'));
            $table->addColumnAfter('gd_sku', 'gd_name', Trans::getWord('goods'));
            $table->addColumnAttribute('customer', 'style', 'text-align: center');
        } else {
            $data = $this->Data[$whId][$gdId];
        }
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
        $i = 1;
        foreach ($data as $row) {
            if (($i % 2) === 0) {
                $table->addRowAttribute($i - 1, 'class', 'even');
            }
            $i++;
            if ($row['jo_type'] === 'IN') {
                $in += (float)$row['quantity'];
                $inW += (float)$row['total_weight'];
                $inV += (float)$row['total_volume'];
            } elseif ($row['jo_type'] === 'OUT') {
                $out += (float)$row['quantity'];
                $outW += (float)$row['total_weight'];
                $outV += (float)$row['total_volume'];
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
        $table->addRows($data);
        $table->setColumnType('quantity', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->addColumnAttribute('jo_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jo_type', 'style', 'text-align: center');
        $table->addColumnAttribute('whs_name', 'style', 'text-align: center');
        $table->addColumnAttribute('condition', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center');

        $content = $table->createTable();
        $number = new NumberFormatter();
        $data = [
            [
                'label' => Trans::getWord('totalInbound'),
                'qty' => $number->doFormatFloat($in) . ' Items',
                'weight' => $number->doFormatFloat($inW) . ' KG',
                'volume' => $number->doFormatFloat($inV) . ' M3',
            ],
            [
                'label' => Trans::getWord('totalOutbound'),
                'qty' => $number->doFormatFloat($out) . ' Items',
                'weight' => $number->doFormatFloat($outW) . ' KG',
                'volume' => $number->doFormatFloat($outV) . ' M3',
            ],
            [
                'label' => Trans::getWord('totalMovement'),
                'qty' => $number->doFormatFloat($mov) . ' Items',
                'weight' => $number->doFormatFloat($movW) . ' KG',
                'volume' => $number->doFormatFloat($movV) . ' M3',
            ],
            [
                'label' => Trans::getWord('totalAdjustment'),
                'qty' => $number->doFormatFloat($ad) . ' Items',
                'weight' => $number->doFormatFloat($adW) . ' KG',
                'volume' => $number->doFormatFloat($adV) . ' M3',
            ],
        ];
        $content .= $this->getSummaryInfo($data);

        return $content;
    }


    /**
     * Function to load the html content.
     *
     * @param array $data To store the data.
     *
     * @return string
     */
    protected function getSummaryInfo(array $data): string
    {
        $result = '<table width="100%" class="table-info" style="font-weight: bold">';
        $result .= '<tr>';
        # General Job
        $result .= '<td width="60%">&nbsp;</td>';
        # warehouse info
        $result .= '<td width="40%">';
        $result .= $this->getSummaryDetail($data);
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @param array $data To store the data.
     *
     * @return string
     */
    private function getSummaryDetail(array $data): string
    {
        $result = '<table width="100%" style="vertical-align: top; font-weight: bold">';

        foreach ($data as $row) {
            $result .= '<tr>';
            $result .= '<td width="30%">' . $row['label'] . '</td>';
            $result .= '<td width="20%" style="text-align: right">' . $row['qty'] . '</td>';
            $result .= '<td width="25%" style="text-align: right">' . $row['weight'] . '</td>';
            $result .= '<td width="25%" style="text-align: right">' . $row['volume'] . '</td>';
            $result .= '</tr>';
        }
        $result .= '</table>';

        return $result;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return void
     */
    protected function loadData(): void
    {
        $sqlResult = DB::select($this->loadQuery());
        $temp = DataParser::arrayObjectToArray($sqlResult);
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
            $joWheres[] = SqlHelper::generateLikeCondition('jid.jid_lot_number', $this->getStringParameter('jid_lot_number'));
        }
        if ($this->isValidParameter('wh_id') === true) {
            $joWheres[] = '(wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
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
                            container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number,
                            quantity, uom_code, gd_weight, gdt_id, damage_type, wh_id, gd_volume,
                            gd_id, gd_name, gd_sku, br_name, gdc_name, customer, shipper_address, shipper_district, shipper_city
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
                            co.of_address as shipper_address, dtc.dtc_name as shipper_district, cty.cty_name as shipper_city
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
                             district as dtc ON co.of_dtc_id = dtc.dtc_id " . $strJiWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, so.so_customer_ref, jo.jo_customer_ref,
                    so.so_aju_ref, jo.jo_aju_ref, so.so_bl_ref, jo.jo_bl_ref, so.so_packing_ref, jo.jo_packing_ref, so.so_sppb_ref, jo.jo_sppb_ref,
                    ji.ji_container_number, ji.ji_seal_number, ji.ji_truck_number,
                    wh.wh_name, whs.whs_name, ji.ji_end_load_on, shp.rel_name, jid.jid_lot_number,
                    uom.uom_code, jid.jid_weight, gdu.gdu_weight,
                    gdt.gdt_id, gdt.gdt_description, wh.wh_id, jid.jid_volume, gdu.gdu_volume,
                    gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name,
                            co.of_address, dtc.dtc_name, cty.cty_name';

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
                            co.of_address as shipper_address, dtc.dtc_name as shipper_district, cty.cty_name as shipper_city
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
                             district as dtc ON co.of_dtc_id = dtc.dtc_id " . $strJobWhere;
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
                            co.of_address, dtc.dtc_name, cty.cty_name';
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
                            '' as shipper_address, '' as shipper_district, '' as shipper_city
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
                            '' as shipper_address, '' as shipper_district, '' as shipper_city
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
            $row['gd_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
            $row['total_volume'] = $volume;
            $row['total_weight'] = $weight;
            $row['start_on'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'd.M.Y');
            if (empty($row['gdt_id']) === true) {
                $row['condition'] = Trans::getWord('good');
            } else {
                $row['condition'] = Trans::getWord('damage');
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
                $row['condition'] = Trans::getWord('good');
            } else {
                $row['condition'] = Trans::getWord('damage');
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
     * Function to load the html content.
     *
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }
}
