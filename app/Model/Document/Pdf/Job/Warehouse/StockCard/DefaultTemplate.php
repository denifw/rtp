<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockCard;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
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
     * Property to store the job detail.
     *
     * @var array $Data
     */
    protected $Data = [];
    /**
     * Property to store the job detail.
     *
     * @var array $Sites
     */
    protected $Sites = [];

    /**
     * AbstractBasePdf constructor.
     *
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('stockReport' ) . ' ' . date('d M Y') . '.pdf');
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
            $this->MPdf->AddPage('L', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $content = '';
            $i = 1;
            foreach ($this->Data as $wh => $goods) {
                $content .= '<p class="title-4" style="font-weight: bold"> ' . $i . '. ' . $this->Sites[$wh] . '</p>';
                $content .= $this->createGoodsTable($wh, $goods);
                $i++;
            }
            $this->MPdf->WriteHTML($content);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to load the html content.
     *
     * @return void
     */
    private function loadData(): void
    {
        $wheres = [];
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        $wheres[] = '(j.jis_stock > 0)';
        $wheres[] = '(gd.gd_ss_id = ' . $this->getIntParameter('ss_id') . ')';
        if ($this->isValidParameter('gd_rel_id')) {
            $wheres[] = '(rel.rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
        }
        if ($this->isValidParameter('gd_id')) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $subWheres = ' WHERE (jid.jid_deleted_on IS NULL) AND (ji.ji_deleted_on IS NULL) AND (jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('wh_id')) {
            $subWheres .= ' AND (wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        # Set Select query;
        $query = 'SELECT  gd.gd_id, gd.gd_sku, gd.gd_name, rel.rel_name, br.br_name, gdc.gdc_name, u.uom_name,
        j.jis_stock as quantity, j.jid_gdt_id, j.weight, j.volume, j.wh_name, j.wh_id, j.gdu_qty_conversion
        FROM goods AS gd INNER JOIN
        brand as br ON gd.gd_br_id = br.br_id INNER JOIN
         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
         unit as u ON gd.gd_uom_id = u.uom_id INNER JOIN
        relation as rel ON gd.gd_rel_id = rel.rel_id LEFT OUTER JOIN
        (SELECT jid.jid_gd_id, jid.jid_gdt_id, SUM(jis.jis_stock) AS jis_stock, wh.wh_name, wh.wh_id,
        (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as weight,
        (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as volume,
        gdu.gdu_qty_conversion
          FROM job_inbound_detail as jid INNER JOIN
               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
               job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
               warehouse as wh on ji.ji_wh_id = wh.wh_id INNER JOIN
               goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIN
            (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
             FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
             GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $subWheres . '
             GROUP BY jid.jid_gd_id, jid.jid_gdt_id, wh.wh_name, wh.wh_id, jid.jid_weight, jid.jid_volume,
             gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion) as j ON gd.gd_id = j.jid_gd_id';
        # Set Where condition.
        $query .= $strWhere;
        $query .= ' ORDER BY j.wh_id, gd.gd_rel_id, gd.gd_name, gd.gd_id';
        $sqlResult = DB::select($query);
        if (empty($sqlResult) === false) {
            $temp = DataParser::arrayObjectToArray($sqlResult);
            $this->doPrepareData($temp);
        }
    }

    /**
     * Function to load the html content.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareData(array $data): void
    {
        $gdIds = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $net = (float)$row['weight'];
            $cbm = (float)$row['volume'];
            $qty = (float)$row['quantity'];
            $qtyConversion = (float)$row['gdu_qty_conversion'];
            $whKey = 'wh_' . $row['wh_id'];
            $row['gd_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']);
            if (array_key_exists($whKey, $this->Data) === false) {
                $this->Sites[$whKey] = $row['wh_name'];
                $this->Data[$whKey] = [];
                $gdIds[$whKey] = [];
                $gdIds[$whKey][] = $row['gd_id'];
                $row['total_weight'] = $qty * $net;
                $row['total_volume'] = $qty * $cbm;
                if (empty($row['jid_gdt_id']) === false) {
                    $row['damageQty'] = $qty * $qtyConversion;
                    $row['goodQty'] = 0;
                } else {
                    $row['goodQty'] = $qty * $qtyConversion;
                    $row['damageQty'] = 0;
                }
                $this->Data[$whKey][] = $row;
            } else {
                if (in_array($row['gd_id'], $gdIds[$whKey], true) === false) {
                    $gdIds[$whKey][] = $row['gd_id'];
                    $row['total_weight'] = $qty * $net;
                    $row['total_volume'] = $qty * $cbm;
                        if (empty($row['jid_gdt_id']) === false) {
                        $row['damageQty'] = $qty * $qtyConversion;
                        $row['goodQty'] = 0;

                    } else {
                        $row['goodQty'] = $qty * $qtyConversion;
                        $row['damageQty'] = 0;
                    }
                    $this->Data[$whKey][] = $row;
                } else {
                    $index = array_search($row['gd_id'], $gdIds[$whKey], true);
                    $this->Data[$whKey][$index]['total_weight'] = $qty * $net;
                    $this->Data[$whKey][$index]['total_volume'] = $qty * $cbm;
                        if (empty($row['jid_gdt_id']) === false) {
                        $this->Data[$whKey][$index]['damageQty'] += $qty * $qtyConversion;
                    } else {
                        $this->Data[$whKey][$index]['goodQty'] += $qty * $qtyConversion;
                    }
                }
            }
        }
    }

    /**
     * Function to load the html content.
     *
     * @param string $whId To store the goods data.
     * @param array  $data To store the goods data.
     *
     * @return string
     */
    private function createGoodsTable(string $whId, array $data): string
    {
        $tbl = new TablePdf($whId . 'tbl');
        $tbl->setHeaderRow([
            'rel_name' => Trans::getWord('owner' ),
            'gd_name' => Trans::getWord('goods'),
            'goodQty' => Trans::getWord('goodItems' ),
            'uom_name' => Trans::getWord('uom'),
            'damageQty' => Trans::getWord('damageItems' ),
            'total_weight' => Trans::getWord('totalWeight' ) . ' (KG)',
            'total_volume' => Trans::getWord('totalVolume' ) . ' (M3)',
        ]);
        $count = count($data);
        for ($i = 1; $i <= $count; $i++) {
            if (($i % 2) === 0) {
                $tbl->addRowAttribute($i - 1, 'class', 'even');
            }
        }
        $tbl->addRows($data);
        $tbl->setColumnType('goodQty', 'float');
        $tbl->setColumnType('damageQty', 'float');
        $tbl->setColumnType('total_weight', 'float');
        $tbl->setColumnType('total_volume', 'float');


        return $tbl->createTable();
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


    /**
     * Function to get the system setting header.
     *
     * @return string
     */

    private function getHeader(): string
    {
        $header = '<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: middle; font-family: sans-serif; font-size: 18pt; color: #000088;">';
        $header .= '<tr>';
        $header .= '<td width="50%" style="text-align: left">' . Trans::getWord('stockReport' ) . '</td>';
        $header .= '<td width="50%" align="right"><span style="font-size:11pt;">' . date('d M Y') . '</span></td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }

}
