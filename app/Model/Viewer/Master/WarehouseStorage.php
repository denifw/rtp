<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Master;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Master\WarehouseStorageDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail WarehouseStorage page
 *
 * @package    app
 * @subpackage Model\Viewer\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class WarehouseStorage extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'warehouseStorage', 'whs_id');
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
            $excel->setFileName(Trans::getWord('serialNumber').'_'. $this->getStringParameter('whs_name', '') . '.xlsx');
            $sheet = $excel->getSheet($sheetName, true);
            $excelTable = new ExcelTable($excel, $sheet);
            $excelTable->setTable($tbl);
            $excelTable->writeTable();
            $excel->setActiveSheet($sheetName);
            if (empty($this->FooterExcelData['SN']) === false) {
                $excel->doRowMovePointer($sheetName);
                $footerTable = new ExcelTable($excel, $sheet);
                $footerTable->setTable($this->FooterExcelData['SN']);
                $footerTable->writeTable();
            }
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
        $wheres[] = '(whs.whs_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(wh.wh_ss_id = ' . $this->User->getSsId() . ')';
        $data = WarehouseStorageDao::loadData($wheres);
        if (\count($data) === 1) {
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
        $pageDescription = Trans::getWord('storage');
        $pageDescription .= ' ' . $this->getStringParameter('whs_name');
        $pageDescription .= ' - ' . $this->getStringParameter('wh_name');
        $this->View->setDescription($pageDescription);

        $data = $this->loadGoodsData();
        $this->Tab->addContent('general', $this->getWidget($data));
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet($data));
        $this->Tab->addContent('general', $this->Field->getHidden('whs_name', $this->getStringParameter('whs_name')));
    }


    /**
     * Function to add stock widget
     *
     * @param array $data To store the data.
     *
     * @return string
     */
    private function getWidget(array $data): string
    {
        $qtyDamage = 0;
        $qtyGood = 0;
        foreach ($data as $row) {
            if (empty($row['gdt_code']) === true) {
                $qtyGood += (float) $row['total_stock'];
            } else {
                $qtyDamage += (float) $row['total_stock'];
            }
        }
        $number = new NumberFormatter();
        $goodStock = new NumberGeneral();
        $good = [
            'title' => Trans::getWord('goodStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-teal-third',
            'amount' => $number->doFormatAmount($qtyGood),
            'uom' => Trans::getWord('items'),
            'url' => '',
        ];
        $goodStock->setData($good);
        $goodStock->setGridDimension(6, 6);


        # damage Stock
        $damageStock = new NumberGeneral();
        $damage = [
            'title' => Trans::getWord('damageStock'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-danger',
            'amount' => $number->doFormatAmount($qtyDamage),
            'uom' => Trans::getWord('items'),
            'url' => '',
        ];
        $damageStock->setData($damage);
        $damageStock->setGridDimension(6, 6);

        return $goodStock->createView() . $damageStock->createView();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        # TODO: Set the validation rule here.
    }


    /**
     * Function to get the general Field Set.
     *
     * @param array $data To store the data.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(array $data): Portlet
    {
        $table = new Table('WhsTbl');
        $table->setHeaderRow([
            'rel_short_name' => Trans::getWord('relation'),
            'gd_sku' => Trans::getWord('sku'),
            'gd_name' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'total_stock' => Trans::getWord('quantity'),
            'uom_code' => Trans::getWord('uom'),
            'inbound_date' => Trans::getWord('inboundDate'),
            'aging_days' => Trans::getWord('aging') . ' (' . Trans::getWord('days') . ')',
            'condition' => Trans::getWord('condition'),
            'gdt_code' => Trans::getWord('damageType'),
            'gcd_code' => Trans::getWord('causeDamage'),
        ]);
        $rows = [];
        $now = DateTimeParser::createDateTime();
        foreach ($data as $row) {
            $row['gd_name'] = $row['gdc_name'] . ' ' . $row['br_name'] . ' ' . $row['gd_name'];
            $row['condition'] = new LabelSuccess(Trans::getWord('good'));
            if (empty($row['gdt_code']) === false) {
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
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('aging_days', 'integer');
        $table->setColumnType('total_stock', 'float');
        $table->setFooterType('total_stock', 'SUM');
        $table->addColumnAttribute('inbound_date', 'style', 'text-align: center;');
        $table->addColumnAttribute('condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('gdt_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('gcd_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('GdStoragePtl', Trans::getWord('storage'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return array
     */
    private function loadGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(whs.whs_id = ' . $this->getIntParameter('whs_id') . ')';
        if ($this->PageSetting->checkPageRight('AllowSeeAllGoods') === false) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.stock <> 0)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = 'SELECT rel.rel_id, rel.rel_name, gd.gd_id, gd.gd_sku, rel.rel_short_name,
                      gd.gd_name, br.br_name, gdc.gdc_name, gdt.gdt_code, gcd.gcd_code,
                      jid.jid_lot_number, jo.start_on, uom.uom_code, SUM(jis.stock) as total_stock
                  FROM warehouse_storage as whs INNER JOIN
                  job_inbound_detail as jid ON whs.whs_id = jid.jid_whs_id INNER JOIN
                  job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                  job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                  (SELECT jo_id, (jo_start_on::timestamp::date) as start_on
                      FROM job_order
                          WHERE jo_start_on IS NOT NULL) as jo ON jog.jog_jo_id = jo.jo_id INNER JOIN
                  goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                  relation as rel ON gd.gd_rel_id = rel.rel_id INNER JOIN
                  brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                   goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                    unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                   (SELECT jis_jid_id, SUM(jis_quantity) as stock
                        FROM job_inbound_stock
                        WHERE (jis_deleted_on IS NULL)
                        GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN
                    goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                    goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id ';
        # Set Where condition.
        $query .= $strWhere;
        $query .= ' GROUP BY rel.rel_id, rel.rel_name, gdc.gdc_name, rel.rel_short_name, gd.gd_id, gd.gd_sku,
                      gd.gd_name, br.br_name, gdt.gdt_code, gcd.gcd_code,
                      jid.jid_lot_number, jo.start_on, uom.uom_code';
        $query .= ' ORDER BY gd.gd_sku, jo.start_on, gd.gd_id';

        $sqlResults = DB::select($query);

        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $btnXls = new SubmitButton('btnExportXls', Trans::getWord('serialNumber'), 'doExportSerialNumberXls', $this->getMainFormId());
        $btnXls->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
        $btnXls->setEnableLoading(false);
        $this->View->addButton($btnXls);
        $this->setEnableCloseButton(false);
        parent::loadDefaultButton();
        $btnClose = new HyperLink('hplBack', Trans::getWord('close'), url('/storageOverview'));
        $btnClose->viewAsButton();
        $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
        $this->View->addButton($btnClose);
    }

        /**
     * Get query movement
     *
     * @return \App\Fram\Gui\Table
     */
    private function loadTableSerialNumber(): Table
    {
        $tbl = new Table('GdSnTbl');
        $tbl->setHeaderRow(
            [
                'gdc_name' => Trans::getWord('category'),
                'gd_sku' => Trans::getWord('sku'),
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
     * @param array $jmWheres To store the default job wheres.
     *
     * @return array
     */
    private function loadSerialNumberData(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_serial_number IS NOT NULL)';
        $wheres[] = '(jid.jid_whs_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jis.jis_stock > 0)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jid.jid_id, jid.jid_serial_number, gd.gd_sku, gdc.gdc_name, gdt.gdt_code, gcd.gcd_code
                      FROM job_inbound_detail as jid INNER JOIN
                           job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                           goods as gd ON gd.gd_id = jid.jid_gd_id INNER JOIN
                           goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                           job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                           (select jis_jid_id, sum(jis_quantity) as jis_stock
                            FROM job_inbound_stock
                            where jis_deleted_on is null
                            group by jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN
                        goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                        goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id  " . $strWhere;
        $query .= ' ORDER BY gdc.gdc_name, gd.gd_sku, jid.jid_id';
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

}
