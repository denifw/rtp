<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 Matalogix
 */

namespace App\Model\Api\Alfa;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseApi;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle Stock Card.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class StockCard extends AbstractBaseApi
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadGoodsStorage') {
            $this->Validation->checkRequire('gd_id');
            $this->Validation->checkInt('gd_id');
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {
        if ($this->ActionName === 'loadStockCard') {
            $data = $this->loadStockCardData();
            $this->addResultData('stocks', $data);
        } elseif ($this->ActionName === 'loadGoodsStorage') {
            $data = $this->loadGoodsStorage();
            $this->addResultData('storage', $data);
        }
    }

    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadStockCardData(): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        $wheres[] = '((d.jid_stock > 0) OR (g.jid_stock > 0))';
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        } else {
            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
            }
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        # Set Select query;
        $query = 'SELECT  gd.gd_id, gd.gd_name, gd.gd_sku, rel.rel_short_name as gd_relation, u.uom_code as gd_uom,
                            br.br_name as gd_br_name, gdc.gdc_name as gd_gdc_name,
                            g.jid_stock as qty_good, g.gdu_weight as weight_good, g.gdu_volume as volume_good,
                            d.jid_stock as qty_damage, d.gdu_weight as weight_damage, d.gdu_volume as volume_damage,
                            s.sites, g.gdu_qty_conversion as good_qty_conversion, d.gdu_qty_conversion as damage_qty_conversion
                    FROM goods AS gd INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                         unit as u on gd.gd_uom_id = u.uom_id INNER JOIN
                         relation as rel ON gd.gd_rel_id = rel.rel_id LEFT OUTER JOIN
                         (SELECT jid.jid_gd_id, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion, SUM(jis.jis_stock) as jid_stock
                          FROM job_inbound_detail as jid INNER JOIN
                               goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                               job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                               (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                                FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                                GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                          WHERE (jo.jo_deleted_on IS NULL) AND (jid.jid_deleted_on IS NULL) AND (jid.jid_gdt_id IS NULL)
                           GROUP BY jid.jid_gd_id, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion) as g ON gd.gd_id = g.jid_gd_id LEFT OUTER JOIN
                         (SELECT jid.jid_gd_id, (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END) as gdu_weight,
                                     (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as gdu_volume, gdu.gdu_qty_conversion,
                                     SUM(jis.jis_stock) as jid_stock
                          FROM job_inbound_detail as jid INNER JOIN
                               goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                               job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                               (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                                FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                                GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                          WHERE (jo.jo_deleted_on IS NULL) AND (jid.jid_deleted_on IS NULL) AND (jid.jid_gdt_id IS NOT NULL)
                           GROUP BY jid.jid_gd_id, gdu.gdu_weight, gdu.gdu_volume, jid.jid_weight, jid.jid_volume, gdu.gdu_qty_conversion) as d ON gd.gd_id = d.jid_gd_id LEFT OUTER JOIN
                         (SELECT jid.jid_gd_id, count(distinct (ji.ji_wh_id)) as sites
                          FROM job_inbound_detail as jid INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                               job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                               (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                                FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                                GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                          WHERE (jo.jo_deleted_on IS NULL) AND (jid.jid_deleted_on IS NULL) AND (jis.jis_stock > 0)
                          GROUP BY jid.jid_gd_id) as s ON gd.gd_id = s.jid_gd_id ';
        # Set Where condition.
        $query .= $strWhere;
        $query .= ' ORDER BY br.br_name,gdc.gdc_name,gd.gd_name, gd.gd_id ';
        $limit = $this->getIntParameter('limit', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $this->getIntParameter('offset', 0);
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareStockCardData($data);
    }

    /**
     * Function to do prepare stock card data.
     *
     * @param array $data To store the data.
     * @return array
     */
    private function doPrepareStockCardData(array $data): array
    {
        $results = [];
        if (empty($data) === false) {
            $number = new NumberFormatter();
            $gdDao  = new GoodsDao();
            foreach ($data as $row) {
                $qtyGood = (float) $row['qty_good'];
                $qtyDamage = (float) $row['qty_damage'];
                $net = ($qtyGood * (float) $row['weight_good']) + ($qtyDamage * (float) $row['weight_damage']);
                $cbm = ($qtyGood * (float) $row['volume_good']) + ($qtyDamage * (float) $row['volume_damage']);
                $results[] = [
                    'gd_id' => $row['gd_id'],
                    'gd_sku' => $row['gd_sku'],
                    'gd_name' => $gdDao->formatFullName($row['gd_gdc_name'], $row['gd_br_name'], $row['gd_name']),
                    'gd_relation' => $row['gd_relation'],
                    'gd_uom' => $row['gd_uom'],
                    'gd_qty_good' => $number->doFormatFloat($qtyGood * (float) $row['good_qty_conversion']),
                    'gd_qty_damage' => $number->doFormatFloat($qtyDamage * (float) $row['damage_qty_conversion']),
                    'total_weight' => $number->doFormatFloat($net),
                    'total_volume' => $number->doFormatFloat($cbm),
                    'gd_sites' => $number->doFormatInteger((int) $row['sites']),
                ];
            }
        }

        return $results;
    }



    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadGoodsStorage(): array
    {
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jis.stock <> 0)';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs.whs_id, wh.wh_name, whs.whs_name, jid.jid_gdt_id, SUM(jis.stock * gdu.gdu_qty_conversion) as total_stock, uom.uom_code,
                        jid.jid_lot_number, gdt.gdt_code, gdt.gdt_description, (jo.jo_start_on::timestamp::date) as start_on,
                        gcd.gcd_description
                FROM job_inbound_detail AS jid INNER JOIN
                    goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id INNER JOIn
                    job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                    job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                    warehouse as wh ON ji.ji_wh_id = wh.wh_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                    unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                    goods_damage_type as gdt  ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                    goods_cause_damage as gcd  ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                        (SELECT jis_jid_id, SUM(jis_quantity) as stock
                        FROM job_inbound_stock
                        WHERE (jis_deleted_on IS NULL)
                        GROUP BY jis_jid_id) jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
        $query .= ' GROUP BY whs.whs_id, wh.wh_name, whs.whs_name, jid.jid_gdt_id, uom.uom_code, jid.jid_lot_number, gdt.gdt_description, gdt.gdt_code, jo.jo_start_on, gcd.gcd_description';
        $query .= ' ORDER BY jid.jid_gdt_id DESC, jo.jo_start_on, wh.wh_name, whs.whs_name, whs.whs_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareStorageData($data);
    }

    /**
     * Function to prepare storage data.
     *
     * @param array $data To store the data.
     * @return array
     */
    private function doPrepareStorageData(array $data): array
    {
        $results = [];
        if (empty($data) === false) {
            $number = new NumberFormatter();
            $now = DateTimeParser::createDateTime();
            foreach ($data as $row) {
                $qty = (float) $row['total_stock'];
                $aging = 0;
                if (empty($row['start_on']) === false) {
                    $startOn = DateTimeParser::createDateTime($row['start_on']);
                    $diff = DateTimeParser::different($startOn, $now);
                    if (empty($diff) === false) {
                        $aging = $diff['days'];
                    }
                }
                $temp = [
                    'warehouse' => $row['wh_name'],
                    'storage' => $row['whs_name'],
                    'lot_number' => $row['jid_lot_number'],
                    'inbound_date' => DateTimeParser::format($row['start_on'], 'Y-m-d', 'd M Y'),
                    'aging_days' => $number->doFormatInteger($aging),
                    'quantity' => $number->doFormatFloat($qty),
                    'uom' => $row['uom_code'],
                    'damage_type' => $row['gdt_description'],
                    'cause_damage' => $row['gcd_description'],

                ];
                $results[] = DataParser::doFormatApiData($temp);
            }
        }

        return $results;
    }
}
