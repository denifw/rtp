<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Master\Goods;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\CardImage;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\TableDatas;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Dao\Master\Goods\GoodsPrefixDao;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail Goods page
 *
 * @package    app
 * @subpackage Model\Viewer\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Goods extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goods', 'gd_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doExportSerialNumberXls') {
            $tbl = $this->loadTableSerialNumber();
            $excel = new Excel();
            $sheetName = StringFormatter::formatExcelSheetTitle('SN');
            $excel->addSheet($sheetName, $sheetName);
            $excel->setFileName(Trans::getWord('serialNumber') . '.xlsx');
            $sheet = $excel->getSheet($sheetName, true);
            $excelTable = new ExcelTable($excel, $sheet);
            $excelTable->setTable($tbl);
            $excelTable->writeTable();
            $excel->setActiveSheet($sheetName);
            $excel->createExcel();
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $data = GoodsDao::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {

        $this->Tab->addContent('general', $this->getWidget());
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getUomPortlet());
        $this->Tab->addPortlet('general', $this->getStorageFieldSet());
        # Configuration Tab
        $this->Tab->addPortlet('config', $this->getConfigSnPortlet());
        $this->Tab->addPortlet('config', $this->getConfigDimensionPortlet());
        if($this->getStringParameter('gd_sn', 'N') === 'Y') {
            $this->Tab->addPortlet('config', $this->getSerialPrefixPortlet());
        }
        # Gallery Tab
        $this->Tab->addPortlet('gallery', $this->getGalleryPortlet());

        # Material Tab
        if ($this->getStringParameter('gd_bundling', 'N') === 'Y') {
            $this->Tab->addPortlet('materials', $this->getMaterialsFieldSet());
        }
        #Staging Tab
        $this->Tab->addPortlet('staging', $this->getStagingFieldSet());
        # Milestone Tab
        $this->Tab->addPortlet('milestone', $this->getMilestoneFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('owner'),
                'value' => $this->getStringParameter('gd_relation'),
            ],
            [
                'label' => Trans::getWord('sku'),
                'value' => $this->getStringParameter('gd_sku'),
            ],
            [
                'label' => Trans::getWord('brand'),
                'value' => $this->getStringParameter('gd_brand'),
            ],
            [
                'label' => Trans::getWord('category'),
                'value' => $this->getStringParameter('gd_category'),
            ],
            [
                'label' => Trans::getWord('barcode'),
                'value' => $this->getStringParameter('gd_barcode'),
            ],
            [
                'label' => Trans::getWord('name'),
                'value' => $this->getStringParameter('gd_name'),
            ],
            [
                'label' => Trans::getWord('description'),
                'value' => $this->getStringParameter('gd_remark'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('GdGeneralPtl', Trans::getWord('general'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getUomPortlet(): Portlet
    {
        $number = new NumberFormatter();
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('uom'),
                'value' => $this->getStringParameter('gd_uom_name'),
            ],
            [
                'label' => Trans::getWord('weight'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_weight')) . ' KG',
            ],
            [
                'label' => Trans::getWord('length'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_length')) . ' M',
            ],
            [
                'label' => Trans::getWord('height'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_height')) . ' M',
            ],
            [
                'label' => Trans::getWord('width'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_width')) . ' M',
            ],
            [
                'label' => Trans::getWord('volume'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_volume')) . ' M3',
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('GdUomPtl', Trans::getWord('uom'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $stock = $this->getStockData();
        $number = new NumberFormatter();
        $goodStock = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('goodStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark-blue',
            'amount' => $number->doFormatAmount($stock['goodQty']),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $goodStock->setData($data);
        $goodStock->setGridDimension(6, 6);

        # damage Stock
        $damageStock = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('damageStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-danger',
            'amount' => $number->doFormatAmount($stock['damageQty']),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $damageStock->setData($data);
        $damageStock->setGridDimension(6, 6);

        # Staging Inbound
        $stagingInbound = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('stagingInbound'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-teal-second',
            'amount' => $this->getTotalInboundStaging(),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $stagingInbound->setData($data);
        $stagingInbound->setGridDimension(3, 3);

        #inbound Widget.
        $inbound = new NumberGeneral();
        $totalInbound = $this->loadTotalInboundThisMonth();
        $data = [
            'title' => Trans::getWord('totalInboundThisMonth'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-teal-fourth',
            'amount' => $number->doFormatAmount($totalInbound),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $inbound->setData($data);
        $inbound->setGridDimension(3, 3);


        # Staging Outbound
        $stagingOutbound = new NumberGeneral();
        $data = [
            'title' => Trans::getWord('stagingOutbound'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-second',
            'amount' => $this->getTotalOutboundStaging(),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $stagingOutbound->setData($data);
        $stagingOutbound->setGridDimension(3, 3);

        #outbound Widget.
        $outbound = new NumberGeneral();
        $totalOutbound = $this->loadTotalOutboundThisMonth();
        $data = [
            'title' => Trans::getWord('totalOutboundThisMonth'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-fourth',
            'amount' => $number->doFormatAmount($totalOutbound),
            'uom' => $this->getStringParameter('gd_unit'),
            'url' => '',
        ];
        $outbound->setData($data);
        $outbound->setGridDimension(3, 3);

        return $goodStock->createView() . $damageStock->createView() . $stagingInbound->createView() . $inbound->createView() . $stagingOutbound->createView() . $outbound->createView();
    }

    /**
     * Function to get the total staging inbound
     *
     * @return string
     */
    private function getTotalInboundStaging(): string
    {
        $result = '0';
        $number = new NumberFormatter();
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_end_store_on IS NULL)';
        $wheres[] = '(ji.ji_end_load_on IS NOT NULL)';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = '(gd.gd_id = ' . $this->getDetailReferenceValue() . ')';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'select SUM(jir.jir_quantity) as total
                from job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
                    goods as gd ON gd.gd_id = jog.jog_gd_id INNER JOIN
                    job_inbound as ji ON ji.ji_id = jir.jir_ji_id INNER JOIN
                    job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $data = DataParser::objectToArray($sqlResults[0]);
            $result = $number->doFormatFloat((float)$data['total']);
        }

        return $result;
    }

    /**
     * Function to get the total staging outbound
     *
     * @return string
     */
    private function getTotalOutboundStaging(): string
    {
        $result = '0';
        $number = new NumberFormatter();
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(job.job_deleted_on is NULL)';
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(job.job_end_load_on IS NULL)';
        $wheres[] = '(jod.jod_deleted_on is NULL)';
        $wheres[] = '(gd.gd_id = ' . $this->getDetailReferenceValue() . ')';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'select SUM(jod.jod_quantity) as total
                from job_outbound_detail as jod INNER JOIN
                    job_goods as jog ON jog.jog_id = jod.jod_jog_id INNER JOIN
                    goods as gd ON gd.gd_id = jog.jog_gd_id INNER JOIN
                    job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                    job_order as jo ON jo.jo_id = job.job_jo_id ' . $strWhere;
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $data = DataParser::objectToArray($sqlResults[0]);
            $result = $number->doFormatFloat((float)$data['total']);
        }

        return $result;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return array
     */
    private function getStockData(): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = 'SELECT  jid.jid_gd_id, SUM(jis.jis_stock) as jid_stock, jid.jid_gdt_id, gdu.gdu_qty_conversion
                  FROM job_inbound_detail as jid INNER JOIN
                  job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                  job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                  goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id LEFT OUTER JOIN
                   (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                       FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                       GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $query .= ' GROUP BY jid.jid_gd_id, jid.jid_gdt_id, gdu.gdu_qty_conversion';
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $data = $this->loadDatabaseRow($query);
            $goodQty = 0;
            $damageQty = 0;
            foreach ($data as $row) {
                $qty = (float)$row['jid_stock'];
                $qtyConversion = (float)$row['gdu_qty_conversion'];
                if (empty($row['jid_gdt_id']) === false) {
                    $damageQty += $qty * $qtyConversion;
                } else {
                    $goodQty += $qty * $qtyConversion;
                }
            }
            $result['goodQty'] = $goodQty;
            $result['damageQty'] = $damageQty;
        } else {
            $result['goodQty'] = 0;
            $result['damageQty'] = 0;
        }

        return $result;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->getStringParameter('gd_sn', 'N') === 'Y') {
            $btnXls = new SubmitButton('btnExportXls', Trans::getWord('serialNumber'), 'doExportSerialNumberXls', $this->getMainFormId());
            $btnXls->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $btnXls->setEnableLoading(false);
            $this->View->addButton($btnXls);
        }
        if ($this->isValidParameter('back_route') === true) {
            $this->setEnableCloseButton(false);
            parent::loadDefaultButton();
            if ($this->isPopupLayout() === true) {
                $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
                $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight()->btnMedium();
                $btnClose->addAttribute('onclick', 'App.closeWindow()');
                $this->View->addButton($btnClose);
            } else {
                $btnClose = new HyperLink('hplBack', Trans::getWord('close'), url('/' . $this->getStringParameter('back_route')));
                $btnClose->viewAsButton();
                $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnClose);
            }
        } else {
            parent::loadDefaultButton();
        }
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return float
     */
    private function loadTotalInboundThisMonth(): float
    {
        $month = DateTimeParser::createDateTime();
        $startFrom = $month->format('Y-m') . '-01';
        $startUntil = $month->format('Y-m-t');

        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = "(ji.ji_end_load_on >= '" . $startFrom . "')";
        $wheres[] = "(ji.ji_end_load_on <= '" . $startUntil . "')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sum(jid.jid_quantity * gdu.gdu_qty_conversion) as total
                    FROM job_inbound_detail as jid INNER JOIN
                        goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIN
                      job_inbound as ji on jid.jid_ji_id = ji.ji_id INNER JOIN
                      job_order as jo on ji.ji_jo_id = jo.jo_id' . $strWhere;
        $sqlResult = DB::select($query);
        $result = 0;
        if (empty($sqlResult) === false) {
            $result = (float)DataParser::objectToArray($sqlResult[0], ['total'])['total'];
        }

        return $result;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return float
     */
    private function loadTotalOutboundThisMonth(): float
    {
        $month = DateTimeParser::createDateTime();
        $startFrom = $month->format('Y-m') . '-01';
        $startUntil = $month->format('Y-m-t');

        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = "(job.job_end_load_on >= '" . $startFrom . "')";
        $wheres[] = "(job.job_end_load_on <= '" . $startUntil . "')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sum(jod.jod_qty_loaded * gdu.gdu_qty_conversion) as total
                        FROM job_outbound_detail as jod INNER JOIN
                            job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id INNER JOIN
                        goods_unit as gdu ON jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                          job_outbound as job on jod.jod_job_id = job.job_id INNER JOIN
                          job_order as jo on job.job_jo_id = jo.jo_id' . $strWhere;
        $sqlResult = DB::select($query);
        $result = 0;
        if (empty($sqlResult) === false) {
            $result = (float)DataParser::objectToArray($sqlResult[0], ['total'])['total'];
        }

        return $result;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getStorageFieldSet(): Portlet
    {
        $table = new TableDatas('GdWhsTbl');
        $table->setHeaderRow([
            'wh_name' => Trans::getWord('warehouse'),
            'whs_name' => Trans::getWord('storage'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_packing_number' => Trans::getWord('packingNumber'),
            'total_stock' => Trans::getWord('quantity'),
            'uom_code' => Trans::getWord('uom'),
            'inbound_date' => Trans::getWord('inboundDate'),
            'aging_days' => Trans::getWord('aging') . ' (' . Trans::getWord('days') . ')',
            'condition' => Trans::getWord('condition'),
            'gdt_code' => Trans::getWord('damageType'),
        ]);
        $table->addRows($this->loadStorageData());
        $table->setRowsPerPage(30);
        $table->setColumnType('aging_days', 'integer');
        $table->setColumnType('total_stock', 'float');
        $table->setFooterType('total_stock', 'SUM');
        $table->addColumnAttribute('whs_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('inbound_date', 'style', 'text-align: center;');
        $table->addColumnAttribute('condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('gdt_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jid_packing_number', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('GdStoragePtl', Trans::getWord('storage'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadStorageData(): array
    {
        $result = [];
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jis.stock <> 0)';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs.whs_id, wh.wh_name, whs.whs_name, jid.jid_gdt_id, SUM(jis.stock * gdu.gdu_qty_conversion) as total_stock, uom.uom_code,
                      jid.jid_lot_number, gdt.gdt_code, gdt.gdt_description, (ji.ji_start_load_on::timestamp::date) as start_on,
                      jid.jid_packing_number
                FROM job_inbound_detail AS jid INNER JOIN
                   goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIn
                    job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                    job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                    warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                     unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                     goods_damage_type as gdt  ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                      (SELECT jis_jid_id, SUM(jis_quantity) as stock
                        FROM job_inbound_stock
                        WHERE (jis_deleted_on IS NULL)
                        GROUP BY jis_jid_id) jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $query .= ' GROUP BY whs.whs_id, wh.wh_name, whs.whs_name, jid.jid_gdt_id, uom.uom_code, jid.jid_lot_number,
                            gdt.gdt_description, gdt.gdt_code, ji.ji_start_load_on, jid.jid_packing_number';
        $query .= ' ORDER BY jid.jid_gdt_id DESC, ji.ji_start_load_on, wh.wh_name, whs.whs_name, whs.whs_id';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult);
            $now = DateTimeParser::createDateTime();
            foreach ($data as $row) {
                $row['condition'] = new LabelSuccess(Trans::getWord('good'));
                if (empty($row['jid_gdt_id']) === false) {

                    $row['gdt_code'] .= ' - ' . $row['gdt_description'];
                    $row['condition'] = new LabelDanger(Trans::getWord('damage'));
                }
                $aging = 0;
                if (empty($row['start_on']) === false) {
                    $row['inbound_date'] = DateTimeParser::format($row['start_on'], 'Y-m-d', 'd M Y');
                    $startOn = DateTimeParser::createDateTime($row['start_on']);
                    $diff = DateTimeParser::different($startOn, $now);
                    if (empty($diff) === false) {
                        $aging = $diff['days'];
                    }
                }
                $row['aging_days'] = $aging;
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getMilestoneFieldSet(): Portlet
    {
        $table = new Table('GdMilestoneTbl');
        $table->setHeaderRow([
            'load_date' => Trans::getWord('loadUnloadDate'),
            'jo_number' => Trans::getWord('jobNumber'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_packing_number' => Trans::getWord('packingNumber'),
            'jo_reference' => Trans::getWord('reference'),
            'whs_name' => Trans::getWord('storage'),
            'shipper' => Trans::getWord('shipperConsignee'),
            'quantity' => Trans::getWord('quantity'),
            'uom_code' => Trans::getWord('uom'),
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'condition' => Trans::getWord('condition'),
            'jo_view' => Trans::getWord('view'),
        ]);
        $table->addRows($this->loadMilestoneData());
        $table->setColumnType('total_weight', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('quantity', 'float');
        $table->addColumnAttribute('uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('load_date', 'style', 'text-align: center;');
        $table->addColumnAttribute('whs_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jid_packing_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jo_view', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('GdMilestonePtl', Trans::getWord('milestone'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to load goods unit data
     *
     * @return array
     */
    private function loadMilestoneData(): array
    {
        $results = [];
        $sqlResults = DB::select($this->loadQuery());
        $data = DataParser::arrayObjectToArray($sqlResults);
        $joDao = new JobOrderDao();
        $i = 1;
        foreach ($data as $row) {
            $row['jo_reference'] = $joDao->concatReference($row, '');
            $row['whs_name'] = $row['wh_name'] . ' - ' . $row['whs_name'];
            $qty = (float)$row['quantity'];
            $row['quantity'] = $qty;
            $row['total_weight'] = $row['gd_weight'];
            $row['total_volume'] = $row['gd_volume'];
            $row['load_date'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'H:i d.M.Y');
            if (empty($row['gdt_id']) === true) {
                $row['condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['condition'] = new LabelDanger(Trans::getWord('damage'));
            }
            $btn = new HyperLink('btnViewJo' . $i, '', $joDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id']));
            $btn->viewAsButton();
            $btn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['jo_view'] = $btn;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return string
     */
    private function loadQuery(): string
    {
        $joWheres = [];
        $joWheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $subQuery = $this->loadInboundQuery($joWheres);
        $subQuery .= ' UNION ALL ' . $this->loadOutboundQuery($joWheres);
        $subQuery .= ' UNION ALL ' . $this->loadMovementQuery($joWheres);
        $subQuery .= ' UNION ALL ' . $this->loadAdjustmentQuery($joWheres);
        return 'SELECT jo_type, jo_id, jo_srt_id, jo_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                        container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number,
                        SUM(quantity) as quantity, uom_code, SUM(quantity * gd_weight) as gd_weight, gdt_id, damage_type, wh_id, SUM(quantity * gd_volume) as gd_volume,
                        gd_id, gd_name, gd_sku, br_name, gdc_name, customer, jid_packing_number
                FROM (' . $subQuery . ') as j
                GROUP BY jo_type, jo_id, jo_srt_id, jo_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                        container_number, seal_number, truck_number, wh_name, whs_name, load_on, shipper, jid_lot_number,
                        uom_code, gdt_id, damage_type, wh_id, gd_id, gd_name, gd_sku, br_name, gdc_name, customer, jid_packing_number
                ORDER BY j.load_on DESC, j.jo_id
                LIMIT 50 OFFSET 0';
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
                            wh.wh_name, whs.whs_name, ji.ji_end_load_on as load_on, shp.rel_short_name as shipper, jid.jid_lot_number,
                            jid.jid_quantity as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_packing_number
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
        $jobWheres[] = '(jod.jod_deleted_on IS NULL)';
        $jobWheres[] = '(jo.jo_deleted_on IS NULL)';
        $jobWheres[] = '(job.job_end_load_on IS NOT NULL)';
        $strJobWhere = ' WHERE ' . implode(' AND ', $jobWheres);
        return "SELECT 'OUT' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            job.job_container_number as container_number, job.job_seal_number as seal_number, job.job_truck_number as truck_number,
                            wh.wh_name, whs.whs_name, job.job_end_load_on as load_on, con.rel_short_name as shipper, jid.jid_lot_number,
                            jod.jod_qty_loaded as quantity, uom.uom_code, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight else jid.jid_weight END) as gd_weight,
                            gdt.gdt_id, gdt.gdt_description as damage_type, wh.wh_id, (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume else jid.jid_volume END) as gd_volume,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_packing_number
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
                           sales_order as so ON job.job_id = so.so_id " . $strJobWhere;
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
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_packing_number
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
                        gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer, jid.jid_packing_number
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
     * Get query movement
     *
     * @return Table
     */
    protected function loadTableSerialNumber(): Table
    {
        $tbl = new Table('GdSnTbl');
        $tbl->setHeaderRow(
            [
                'whs_name' => Trans::getWord('storage'),
                'jo_number' => Trans::getWord('inboundNumber'),
                'jid_serial_number' => Trans::getWord('serialNumber'),
                'jid_condition' => Trans::getWord('condition'),
                'gdt_code' => Trans::getWord('damageType'),
                'gcd_code' => Trans::getWord('causeDamage'),
            ]
        );
        $tbl->addRows($this->loadSerialNumberData());

        return $tbl;
    }

    /**
     * Get query movement
     *
     * @return array
     */
    private function loadSerialNumberData(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jis.jis_stock > 0)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jid.jid_id, jo.jo_number, jid.jid_serial_number, whs.whs_name, gdt.gdt_code, gcd.gcd_code, jid.jid_weight
                      FROM job_inbound_detail as jid INNER JOIN
                           job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                           warehouse_storage as whs ON whs.whs_id = jid.jid_whs_id INNER JOIN
                           job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                           (select jis_jid_id, sum(jis_quantity) as jis_stock
                            FROM job_inbound_stock
                            where jis_deleted_on is null
                            group by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN
                        goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                        goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id  " . $strWhere;
        $query .= ' ORDER BY whs.whs_name, jid.jid_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResults);
        $results = [];
        foreach ($data as $row) {
            $row['jid_condition'] = Trans::getWord('good');
            if (empty($row['gdt_code']) === false) {
                $row['jid_condition'] = Trans::getWord('damage');
            }
            $results[] = $row;
        }
        return $results;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getStagingFieldSet(): Portlet
    {
        $table = new Table('GdStagingTbl');
        $table->setHeaderRow([
            'date' => Trans::getWord('date'),
            'job_number' => Trans::getWord('jobNumber'),
            'reference' => Trans::getWord('reference'),
            'warehouse' => Trans::getWord('warehouse'),
            'relation' => Trans::getWord('shipperConsignee'),
            'qty' => Trans::getWord('quantity'),
            'uom' => Trans::getWord('uom'),
            'view' => Trans::getWord('view'),
        ]);
        $table->addRows($this->loadStagingData());
        $table->setColumnType('qty', 'float');
        $table->addColumnAttribute('uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('date', 'style', 'text-align: center;');
        $table->addColumnAttribute('warehouse', 'style', 'text-align: center;');
        $table->addColumnAttribute('lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('view', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('GdStagingPtl', Trans::getWord('staging'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to load goods unit data
     *
     * @return array
     */
    private function loadStagingData(): array
    {
        $results = [];
        $sqlResults = DB::select($this->loadStagingQuery());
        $data = DataParser::arrayObjectToArray($sqlResults);
        $joDao = new JobOrderDao();
        $i = 1;
        foreach ($data as $row) {
            $row['reference'] = $joDao->concatReference($row, '');
            $row['warehouse'] = $row['wh_name'];
            $qty = (float)$row['quantity'];
            $row['qty'] = $qty;
            $row['date'] = DateTimeParser::format($row['load_on'], 'Y-m-d H:i:s', 'H:i d.M.Y');
            $btn = new HyperLink('btnVJo' . $i, '', $joDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id']));
            $btn->viewAsButton();
            $btn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['view'] = $btn;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return string
     */
    private function loadStagingQuery(): string
    {
        $joWheres = [];
        $joWheres[] = '(jo.jo_deleted_on IS NULL)';
        $joWheres[] = '(gd.gd_id = ' . $this->getDetailReferenceValue() . ')';
        $subQuery = $this->loadInboundStagingQuery($joWheres);
        $subQuery .= ' UNION ALL ' . $this->loadOutboundStagingQuery($joWheres);
        return 'SELECT jo_type, jo_id, jo_srt_id, jo_number as job_number, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref,
                        container_number, seal_number, truck_number, wh_name, load_on, shipper as relation,
                        quantity, uom_code as uom, wh_id,
                        gd_id, gd_name, gd_sku, br_name, gdc_name, customer
                FROM (' . $subQuery . ') as j
                ORDER BY j.load_on DESC, j.jo_id
                LIMIT 50 OFFSET 0';
    }

    /**
     * Get query inbound
     *
     * @param array $jiWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadInboundStagingQuery(array $jiWheres): string
    {
        $jiWheres[] = '(ji.ji_deleted_on IS NULL)';
        $jiWheres[] = '(ji.ji_end_load_on IS NOT NULL)';
        $jiWheres[] = '(ji.ji_end_store_on IS NULL)';
        $jiWheres[] = '(jir.jir_deleted_on IS NULL)';
        $strJiWhere = ' WHERE ' . implode(' AND ', $jiWheres);
        $query = "SELECT 'IN' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            ji.ji_container_number as container_number, ji.ji_seal_number as seal_number, ji.ji_truck_number as truck_number,
                            wh.wh_name, ji.ji_end_load_on as load_on, shp.rel_name as shipper,
                            SUM(jir.jir_quantity) as quantity, uom.uom_code, wh.wh_id,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer
                      FROM job_inbound_receive as jir INNER JOIN
                           job_inbound as ji ON jir.jir_ji_id = ji.ji_id INNER JOIN
                           job_goods as jog ON jog.jog_id = jir.jir_jog_id INNER JOIN
                           job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                           goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                           goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                           brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                           goods_unit as gdu oN jog.jog_gdu_id = gdu.gdu_id INNER JOIN
                           unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                           warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                           relation as rel ON gd.gd_rel_id = rel.rel_id INNER JOIN
                           relation as shp ON ji.ji_rel_id = shp.rel_id LEFT OUTER JOIN
                           sales_order as so ON ji.ji_so_id = so.so_id " . $strJiWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, so.so_customer_ref, jo.jo_customer_ref,
                    so.so_aju_ref, jo.jo_aju_ref, so.so_bl_ref, jo.jo_bl_ref, so.so_packing_ref, jo.jo_packing_ref, so.so_sppb_ref, jo.jo_sppb_ref,
                    ji.ji_container_number, ji.ji_seal_number, ji.ji_truck_number,
                    wh.wh_name, ji.ji_end_load_on, shp.rel_name,
                    uom.uom_code, wh.wh_id,
                    gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name';
        return $query;
    }

    /**
     * Get query outbound
     *
     * @param array $jobWheres To store the default job wheres.
     *
     * @return string
     */
    private function loadOutboundStagingQuery(array $jobWheres): string
    {
        $jobWheres[] = '(job.job_deleted_on IS NULL)';
        $jobWheres[] = '(jod.jod_deleted_on IS NULL)';
        $jobWheres[] = '(job.job_end_store_on IS NOT NULL)';
        $jobWheres[] = '(job.job_end_load_on IS NULL)';
        $strJobWhere = ' WHERE ' . implode(' AND ', $jobWheres);
        $query = "SELECT 'OUT' as jo_type, jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            job.job_container_number as container_number, job.job_seal_number as seal_number, job.job_truck_number as truck_number,
                            wh.wh_name, job.job_end_store_on as load_on, con.rel_name as shipper,
                            SUM(jod.jod_qty_loaded) as quantity, uom.uom_code, wh.wh_id,
                            gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name as customer
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
                           relation as con ON job.job_rel_id = con.rel_id LEFT OUTER JOIN
                           sales_order as so ON job.job_so_id = so.so_id " . $strJobWhere;
        $query .= ' GROUP BY jo.jo_id, jo.jo_srt_id, jo.jo_number, so.so_number, so.so_customer_ref, jo.jo_customer_ref,
                        so.so_aju_ref, jo.jo_aju_ref,
                        so.so_bl_ref, jo.jo_bl_ref,
                        so.so_packing_ref, jo.jo_packing_ref,
                        so.so_sppb_ref, jo.jo_sppb_ref,
                        job.job_container_number, job.job_seal_number, job.job_truck_number,
                        wh.wh_name, job.job_end_store_on, con.rel_name,
                        uom.uom_code, wh.wh_id,
                        gd.gd_id, gd.gd_name, gd.gd_sku, br.br_name, gdc.gdc_name, rel.rel_short_name';
        return $query;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getMaterialsFieldSet(): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('GdGpfPtl', Trans::getWord('billOfMaterials'));

        # Create table object.
        $table = new Table('GdGmTbl');
        $table->setHeaderRow([
            'gm_gd_sku' => Trans::getWord('sku'),
            'gm_br_name' => Trans::getWord('brand'),
            'gm_gdc_name' => Trans::getWord('category'),
            'gm_gd_name' => Trans::getWord('goods'),
            'gm_quantity' => Trans::getWord('quantity'),
            'gm_uom_code' => Trans::getWord('uom'),
        ]);
        $data = GoodsMaterialDao::getByGdId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->addColumnAttribute('gm_gd_sku', 'style', 'text-align: center;');
        $table->setColumnType('gm_quantity', 'float');
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getGalleryPortlet(): Portlet
    {
        $portlet = new Portlet('GdGlrPtl', Trans::getWord('gallery'));
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'goods')";
        $wheres[] = "(dct.dct_code = 'image')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $docDao = new DocumentDao();
        $i = 0;
        foreach ($data as $row) {
            $i++;
            $path = $docDao->getDocumentPath($row);
            $ca = new CardImage('GdIm' . $i);
            $ca->setHeight(200);
            $btns = [];
            $btn = new Button('BtnIm' . $i, Trans::getWord('view'));
            $btn->setIcon(Icon::Eye)->btnPrimary()->btnSmall();
            $btn->addAttribute('onclick', "App.popup('" . $path . "')");
            $btns[] = $btn;
            $ca->setData([
                'title' => '&nbsp;',
                'subtitle' => $row['doc_description'],
                'img_path' => $path,
                'buttons' => $btns,
            ]);
            $portlet->addText($ca->createView());
        }

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getSerialPrefixPortlet(): Portlet
    {
        $table = new Table('GdGpfTbl');
        $table->setHeaderRow([
            'gpf_prefix' => Trans::getWord('prefix'),
            'gpf_yearly' => Trans::getWord('yearly'),
            'gpf_monthly' => Trans::getWord('monthly'),
            'gpf_length' => Trans::getWord('length'),
        ]);
        $data = GoodsPrefixDao::getByGoodsId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('gpf_yearly', 'yesno');
        $table->setColumnType('gpf_monthly', 'yesno');
        $table->setColumnType('gpf_length', 'integer');
        # Create a portlet box.
        $portlet = new Portlet('GdGpfPtl', Trans::getWord('serialNumberPrefix'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getConfigSnPortlet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('requireSn'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_sn', 'N')),
            ],
            [
                'label' => Trans::getWord('multiSn'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_multi_sn', 'N')),
            ],
            [
                'label' => Trans::getWord('requiredSnOnReceive'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_receive_sn', 'N')),
            ],
            [
                'label' => Trans::getWord('generateSn'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_generate_sn', 'N')),
            ],
            [
                'label' => Trans::getWord('bundlingEnabled'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_bundling', 'N')),
            ],
            [
                'label' => Trans::getWord('packingEnabled'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_packing', 'N')),
            ],
            [
                'label' => Trans::getWord('expiredEnabled'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_expired', 'N')),
            ],
            [
                'label' => Trans::getWord('warranty'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_warranty', 'N')),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('GdCfgPtl', Trans::getWord('basic'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getConfigDimensionPortlet(): Portlet
    {
        $number = new NumberFormatter();
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('requireWeight'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_tonnage', 'N')),
            ],
            [
                'label' => Trans::getWord('requireWeightOnDamage'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_tonnage_dm', 'N')),
            ],
            [
                'label' => Trans::getWord('minWeight'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_min_tonnage')) . ' KG',
            ],
            [
                'label' => Trans::getWord('maxWeight'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_max_tonnage')) . ' KG',
            ],
            [
                'label' => Trans::getWord('requireCbm'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_cbm', 'N')),
            ],
            [
                'label' => Trans::getWord('requireCbmOnDamage'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('gd_cbm_dm', 'N')),
            ],
            [
                'label' => Trans::getWord('minCbm'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_min_cbm')) . ' M3',
            ],
            [
                'label' => Trans::getWord('maxCbm'),
                'value' => $number->doFormatFloat($this->getFloatParameter('gd_max_cbm')) . ' M3',
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('GdCfgDmPtl', Trans::getWord('dimension'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
