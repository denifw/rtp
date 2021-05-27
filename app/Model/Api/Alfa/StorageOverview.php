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
use App\Frame\Formatter\NumberFormatter;
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
class StorageOverview extends AbstractBaseApi
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadStorageOverview') {
            $this->Validation->checkRequire('wh_id');
            $this->Validation->checkInt('wh_id');
        } elseif ($this->ActionName === 'loadGoodsStorageOverview') {
            $this->Validation->checkRequire('whs_id');
            $this->Validation->checkInt('whs_id');
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {
        if ($this->ActionName === 'loadStorageOverview') {
            $data = $this->loadStorageOverviewData();
            $this->addResultData('listStorage', $data);
        } elseif ($this->ActionName === 'loadGoodsStorageOverview') {
            $data = $this->loadGoodsStorage();
            $this->addResultData('listStorageGoods', $data);
        }
    }

    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadStorageOverviewData(): array
    {
        $wheres = [];
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = '(whs.whs_deleted_on IS NULL)';
        $wheres[] = "(whs.whs_active = 'Y')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);

        $subWheres = [];
        $subWheres[] = '(jid.jid_deleted_on IS NULL)';
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $subWheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $strSubWhere = ' WHERE '. implode(' AND ', $subWheres);
        # Set Select query;
        $query = 'SELECT  whs.whs_id, whs.whs_name, SUM(j.jid_stock) as total_stock
                    FROM warehouse_storage as whs INNER JOIN
                        (SELECT jid.jid_id, jid.jid_whs_id, jis.jid_stock
                        FROM job_inbound_detail as jid  INNER JOIN
                         goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                         (SELECT jis_jid_id, SUM(jis_quantity) as jid_stock
                          FROM job_inbound_stock
                          WHERE (jis_deleted_on IS NULL)
                          GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id '.$strSubWhere.') as j ON j.jid_whs_id = whs.whs_id ' . $strWheres;
        $query .= ' GROUP BY whs.whs_id, whs.whs_name';
        $query .= ' ORDER BY whs.whs_name, whs.whs_id';
        $limit = $this->getIntParameter('limit', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $this->getIntParameter('offset', 0);
        }

        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareStorageOverviewData($data);
    }

    /**
     * Function to do prepare stock card data.
     *
     * @param array $data To store the data.
     * @return array
     */
    private function doPrepareStorageOverviewData(array $data): array
    {
        $results = [];
        if (empty($data) === false) {
            $number = new NumberFormatter();
            foreach ($data as $row) {
                $stock = (float) $row['total_stock'];
                if ($stock !== 0.0) {
                    $row['whs_stock'] = $number->doFormatFloat($stock);
                    $results[] = $row;
                }
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
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        $wheres[] = '((d.jid_stock > 0) OR (g.jid_stock > 0))';
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $subWheres = [];
        $subWheres[] = '(jid.jid_deleted_on IS NULL)';
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $subWheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $strSubWhere = ' WHERE '. implode(' AND ', $subWheres);
        # Set Select query;
        $query = 'SELECT  gd.gd_id, gd.gd_name, gd.gd_sku, rel.rel_short_name as gd_relation, u.uom_code as gd_uom, 
                            br.br_name as gd_br_name, gdc.gdc_name as gd_gdc_name,
                            g.jid_stock as qty_good,
                            d.jid_stock as qty_damage
                    FROM goods AS gd INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                         unit as u on gd.gd_uom_id = u.uom_id INNER JOIN
                         relation as rel ON gd.gd_rel_id = rel.rel_id LEFT OUTER JOIN
                         (SELECT jid.jid_gd_id, SUM(jis.jis_stock * gdu.gdu_qty_conversion) as jid_stock
                          FROM job_inbound_detail as jid INNER JOIN
                               goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                               job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                               (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                                FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                                GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                          WHERE (jo.jo_deleted_on IS NULL) AND (jid.jid_deleted_on IS NULL) AND (jid.jid_gdt_id IS NULL) AND (jid.jid_whs_id = '.$this->getIntParameter('whs_id').')
                           GROUP BY jid.jid_gd_id) as g ON gd.gd_id = g.jid_gd_id LEFT OUTER JOIN
                         (SELECT jid.jid_gd_id, SUM(jis.jis_stock * gdu.gdu_qty_conversion) as jid_stock
                          FROM job_inbound_detail as jid INNER JOIN
                               goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                               job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                               job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                               (SELECT jis_jid_id, sum(jis_quantity) as jis_stock
                                FROM job_inbound_stock WHERE (jis_deleted_on IS NULL)
                                GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id
                          WHERE (jo.jo_deleted_on IS NULL) AND (jid.jid_deleted_on IS NULL) AND (jid.jid_gdt_id IS NOT NULL) AND (jid.jid_whs_id = '.$this->getIntParameter('whs_id').')
                           GROUP BY jid.jid_gd_id) as d ON gd.gd_id = d.jid_gd_id ';
        # Set Where condition.
        $query .= $strWhere;
        $query .= ' ORDER BY br.br_name,gdc.gdc_name,gd.gd_name, gd.gd_id ';
        $limit = $this->getIntParameter('limit', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $this->getIntParameter('offset', 0);
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareStorageGoodsData($data);
    }

    /**
     * Function to prepare storage data.
     *
     * @param array $data To store the data.
     * @return array
     */
    private function doPrepareStorageGoodsData(array $data): array
    {
        $results = [];
        if (empty($data) === false) {
            $number = new NumberFormatter();
            $gdDao = new GoodsDao();
            foreach ($data as $row) {
                $qtyGood = (float) $row['qty_good'];
                $qtyDamage = (float) $row['qty_damage'];
                $results[] = [
                    'gd_sku' => $row['gd_sku'],
                    'gd_name' => $gdDao->formatFullName($row['gd_gdc_name'], $row['gd_br_name'], $row['gd_name']),
                    'qty_good' => $number->doFormatFloat($qtyGood),
                    'qty_damage' => $number->doFormatFloat($qtyDamage),
                    'gd_relation' => $row['gd_relation'],
                    'gd_uom' => $row['gd_uom'],
                ];
            }
        }

        return $results;
    }
}
