<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobInboundDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('jog.jog_name', $this->getStringParameter('search_key'));
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        if ($this->isValidParameter('jod_jog_id') === true) {
            $wheres[] = '(gd.gd_id IN (SELECT jog_gd_id from job_goods where (jog_id = ' . $this->getIntParameter('jod_jog_id') . ')))';
        }
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('jog_available_date') === true) {
            $wheres[] = "(jog.jog_available_date <= '" . $this->getStringParameter('jog_available_date') . "')";
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, (jog.jog_name || \' - \' || whs.whs_name || \' - \' || CAST(jis.jis_stock as varchar(125)) || \' - \' || uom.uom_code) as text
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN 
                job_order as jo ON jog.jog_jo_id = jo.jo_id INNER JOIN
                unit as uom ON jid.jid_uom_id = uom.uom_id INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  from job_inbound_stock 
                  where (jis_deleted_on IS NULL) 
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id' . $strWhere;
        $query .= ' ORDER BY jid.jid_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'text', 'jid_id');
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectDataForMovement(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('jog.jog_name', $this->getStringParameter('search_key'));
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        //        $wheres[] = '(jis.jis_stock > 0)';
        if ($this->isValidParameter('jid_whs_id') === true) {
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, gd.gd_sku, gd.gd_name, uom.uom_code, jis.jis_stock, jog.jog_production_number
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                unit as uom ON jid.jid_uom_id = uom.uom_id INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  from job_inbound_stock 
                  where (jis_deleted_on IS NULL) 
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id' . $strWhere;
        $query .= ' ORDER BY gd.gd_sku';
        $query .= ' LIMIT 30 OFFSET 0';

        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult, [
                'jid_id',
                'gd_sku',
                'gd_name',
                'uom_code',
                'jis_stock',
                'jog_production_number',
            ]);
            foreach ($data as $row) {
                $result[] = [
                    'text' => $row['gd_sku'] . ' - ' . $row['gd_name'] . ' - ' . $row['jis_stock'] . ' ' . $row['uom_code'],
                    'value' => $row['jid_id'],
                ];
            }
        }

        return $result;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForAdjustment(): array
    {
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('ss_id') . ')';
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('ja_gd_id') . ')';
        $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        if ($this->isValidParameter('jo_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('jo.jo_number', $this->getStringParameter('jo_number'));
        }
        if ($this->isValidParameter('whs_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('whs.whs_name', $this->getStringParameter('whs_name'));
        }
        if ($this->isValidParameter('serial_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('serial_number'));
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = 'SELECT jid.jid_id, whs.whs_name as jid_whs_name, jo.jo_number as jid_jo_number, uom.uom_code as jid_gdu_uom,
                      jid.jid_lot_number, jid.jid_serial_number, jid.jid_gdu_id, jid.jid_gdt_id, 
                      gdt.gdt_code as jid_gdt_code, gdt.gdt_description as jid_gdt_description, jid.jid_quantity,
                      jis.stock as jid_stock
                FROM job_inbound_detail as jid INNER JOIN
                warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN 
                goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                (SELECT jis_jid_id, SUM(jis_quantity) as stock
                    FROM job_inbound_stock
                    WHERE jis_deleted_on IS NULL
                    GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id ' . $strWhere;
        $query .= ' ORDER BY jid.jid_id';
        $query .= ' LIMIT 50 OFFSET 0';

        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult);
            $number = new NumberFormatter();
            foreach ($data as $row) {
                $row['jid_quantity_number'] = $number->doFormatFloat($row['jid_quantity']);
                $row['jid_stock_number'] = $number->doFormatFloat($row['jid_stock']);
                $results[] = $row;
            }
        }

        return $results;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForMovement(): array
    {
        $wheres = [];
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('ss_id') . ')';
        $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('whs_id') . ')';
        if ($this->isValidParameter('br_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('br.br_name', $this->getStringParameter('br_name'));
        }
        if ($this->isValidParameter('gdc_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gdc.gdc_name', $this->getStringParameter('gdc_name'));
        }
        if ($this->isValidParameter('gd_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gd.gd_name', $this->getStringParameter('gd_name'));
        }
        if ($this->isValidParameter('gd_sku') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gd.gd_sku', $this->getStringParameter('gd_sku'));
        }
        if ($this->isValidParameter('lot_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_lot_number', $this->getStringParameter('lot_number'));
        }
        if ($this->isValidParameter('serial_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('serial_number'));
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = 'SELECT jid.jid_id, gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, 
                      gdc.gdc_name as jid_gdc_name, uom.uom_code as jid_gdu_uom,
                      jis.jis_stock as jid_stock, jid.jid_gdu_id,
                      jid.jid_gdt_id, jid.jid_gdt_remark, jid.jid_gcd_id, jid.jid_gcd_remark, jid.jid_serial_number,
                      jid.jid_gdt_id, gdt.gdt_description as jid_gdt_description, jid.jid_gcd_id, gcd.gcd_description as jid_gcd_description, 
                      jid.jid_gdt_remark, jid.jid_gcd_remark, jid.jid_length, jid.jid_width, jid.jid_height, jid.jid_weight,
                      jid.jid_lot_number,  jmd.used as jid_used, gd.gd_tonnage as jid_gd_tonnage, gd.gd_cbm as jid_gd_cbm
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                job_order as jo ON ji.ji_jo_id = jo.jo_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  from job_inbound_stock
                  WHERE (jis_deleted_on IS NULL)
                  GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN 
                goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                (SELECT jmd_jid_id, SUM(jmd_quantity) as used
                    FROM job_movement_detail 
                    WHERE (jmd_deleted_on IS NULL) AND (jmd_jis_id IS NULL)
                    GROUP BY jmd_jid_id) as jmd ON jid.jid_id = jmd.jmd_jid_id ' . $strWhere;
        $query .= ' GROUP BY jid.jid_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, uom.uom_code,
                      jis.jis_stock, jid.jid_gdu_id, jid.jid_gdt_id, jid.jid_gdt_remark, 
                      jid.jid_gcd_id, jid.jid_gcd_remark, jid.jid_serial_number,
                      jid.jid_gdt_id, gdt.gdt_description, jid.jid_gcd_id, gcd.gcd_description, 
                      jid.jid_gdt_remark, jid.jid_gcd_remark, jid.jid_length, jid.jid_width, jid.jid_height, 
                      jid.jid_weight, jmd.used, gd.gd_tonnage, gd.gd_cbm';
        $query .= ' ORDER BY jid.jid_id';
        $query .= ' LIMIT 50 OFFSET 0';

        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult);
            $number = new NumberFormatter();
            $gdDao = new GoodsDao();
            foreach ($data as $row) {
                $qty = (float)$row['jid_stock'];
                $qtyUsed = (float)$row['jid_used'];
                $stock = $qty - $qtyUsed;
                if ($stock > 0) {
                    $row['jid_goods'] = $gdDao->formatFullName($row['jid_gdc_name'], $row['jid_br_name'], $row['jid_gd_name']);
                    $condition = Trans::getWord('good');
                    if (empty($row['jid_gdt_id']) === false) {
                        $condition = $row['jid_gdt_description'];
                    }
                    $row['jid_condition'] = $condition;
                    $row['jid_stock'] = $stock;
                    $row['jid_stock_number'] = $number->doFormatFloat($stock);
                    $row['jid_length_number'] = $number->doFormatFloat((float)$row['jid_length']);
                    $row['jid_width_number'] = $number->doFormatFloat((float)$row['jid_width']);
                    $row['jid_height_number'] = $number->doFormatFloat((float)$row['jid_height']);
                    $row['jid_weight_number'] = $number->doFormatFloat((float)$row['jid_weight']);
                    $results[] = $row;
                }
            }
        }

        return $results;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForOpname(): array
    {
        $wheres = [];
        $wheres[] = '(jid.jid_id NOT IN (SELECT sod_jid_id
                                        FROM stock_opname_detail WHERE (sod_deleted_on IS NULL) AND (sod_sop_id = ' . $this->getIntParameter('sop_id') . ')
                                        GROUP BY sod_jid_id))';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('ss_id') . ')';
        $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        if ($this->isValidParameter('br_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('br.br_name', $this->getStringParameter('br_name'));
        }
        if ($this->isValidParameter('gdc_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gdc.gdc_name', $this->getStringParameter('gdc_name'));
        }
        if ($this->isValidParameter('gd_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('gd.gd_name', $this->getStringParameter('gd_name'));
        }
        if ($this->isValidParameter('whs_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('whs.whs_name', $this->getStringParameter('whs_name'));
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $query = 'SELECT jid.jid_id, gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, 
                      gdc.gdc_name as jid_gdc_name, jo.jo_number as jid_jo_number, uom.uom_code as jid_jog_uom,
                      jis.jis_stock as jid_stock, jog.jog_production_number as jid_jog_production_number, jog.jog_uom_id as jid_jog_uom_id,
                      jid.jid_gdt_id, gdt.gdt_description as jid_gdt_description, jid.jid_gcd_id, gcd.gcd_description as  jid_gcd_description, 
                      jid.jid_whs_id, whs.whs_name as jid_whs_name
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                job_order as jo ON jog.jog_jo_id = jo.jo_id INNER JOIN 
                unit as uom ON jog.jog_uom_id = uom.uom_id INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN 
                goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  FROM job_inbound_stock 
                  WHERE (jis_deleted_on IS NULL)
                  GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id' . $strWhere;
        $query .= ' GROUP BY jid.jid_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, jo.jo_number, uom.uom_code,
                      jis.jis_stock, jog.jog_production_number, jog.jog_uom_id, jid.jid_gdt_id, gdt.gdt_description, 
                      jid.jid_gcd_id, gcd.gcd_description, jid.jid_whs_id, whs.whs_name';
        $query .= ' LIMIT 50 OFFSET 0';

        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $data = DataParser::arrayObjectToArray($sqlResult, [
                'jid_id', 'jid_gd_sku', 'jid_gd_name', 'jid_br_name', 'jid_gdc_name', 'jid_jo_number',
                'jid_jog_uom', 'jid_stock', 'jid_jog_production_number', 'jid_jog_uom_id', 'jid_gdt_id',
                'jid_gdt_description',
                'jid_gcd_id',
                'jid_gcd_description',
                'jid_whs_id',
                'jid_whs_name',
            ]);
            $number = new NumberFormatter();
            foreach ($data as $row) {
                $row['jid_stock_number'] = $number->doFormatFloat($row['jid_stock']);
                $results[] = $row;
            }
        }

        return $results;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForOutbound(): array
    {
        $results = [];
        if ($this->isValidParameter('jid_gd_id') === true) {
            $wheres = [];
            $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
            if ($this->getStringParameter('jid_damage', 'Y') === 'N') {
                $wheres[] = '(jid.jid_gdt_id IS NULL)';
            }
            $wheres[] = '(jid.jid_deleted_on IS NULL)';
            $wheres[] = '(jis.jis_stock > 0)';
            if ($this->isValidParameter('jid_lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
            }
            if ($this->isValidParameter('jid_whs_name') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('whs.whs_name', $this->getStringParameter('jid_whs_name'));
            }
            if ($this->isValidParameter('jid_serial_number') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('jid_serial_number'));
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_id, jid.jid_whs_id, whs.whs_name as jid_whs_name, jid.jid_lot_number, 
                uom.uom_code as jid_uom, gdt.gdt_description as jid_gdt_description, jis.jis_stock as jid_stock, 
                jod.jod_used, jid.jid_gdt_id, jid.jid_serial_number
            FROM job_inbound_detail as jid INNER JOIN
            warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
            goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
            unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
            goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
            (Select jis_jid_id, sum(jis_quantity) as jis_stock 
              FROM job_inbound_stock 
              where (jis_deleted_on IS NULL) 
              GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN
            (SELECT jod_jid_id, SUM(jod_quantity) as jod_used 
                FROM job_outbound_detail 
                WHERE (jod_jis_id IS NULL) AND (jod_deleted_on IS NULL)
                GROUP BY jod_jid_id) as jod ON jid.jid_id = jod.jod_jid_id ' . $strWhere;
            $query .= ' ORDER BY jid.jid_gdt_id DESC, jid.jid_lot_number, whs.whs_name, jid.jid_id';
            $query .= ' LIMIT 20 OFFSET 0';
            $results = [];
            $data = DB::select($query);
            if (empty($data) === false) {
                $tempResult = DataParser::arrayObjectToArray($data);
                $number = new NumberFormatter();
                foreach ($tempResult as $row) {
                    $qty = (float)$row['jid_stock'];
                    if (empty($row['jod_used']) === false) {
                        $qty -= (float)$row['jod_used'];
                    }
                    if ($qty > 0) {
                        $row['jid_stock'] = $qty;
                        $row['jid_stock_number'] = $number->doFormatFloat($qty);
                        $results[] = $row;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadJidNormalForOutbound(): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
        $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
        if ($this->getStringParameter('jid_damage', 'Y') === 'N') {
            $wheres[] = '(jid.jid_gdt_id IS NULL)';
        }
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        if ($this->isValidParameter('jid_lot_number') === true) {
            $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jid.jid_whs_id, whs.whs_name as jid_whs_name, jid.jid_lot_number, 
                uom.uom_code as jid_uom, gdt.gdt_description as jid_gdt_description, jis.jis_stock as jid_stock, 
                jod.jod_used, jid.jid_gdt_id
            FROM job_inbound_detail as jid INNER JOIN
            warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
            goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
            unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
            goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
            goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
            brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
            goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
            (Select jis_jid_id, sum(jis_quantity) as jis_stock 
              FROM job_inbound_stock 
              where (jis_deleted_on IS NULL) 
              GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN
            (SELECT jod_jid_id, SUM(jod_quantity) as jod_used 
                FROM job_outbound_detail 
                WHERE (jod_jis_id IS NULL) AND (jod_deleted_on IS NULL)
                GROUP BY jod_jid_id) as jod ON jid.jid_id = jod.jod_jid_id ' . $strWhere;
        $query .= ' ORDER BY jid.jid_gdt_id DESC, jid.jid_lot_number, whs.whs_name, jid.jid_id';
        $query .= ' LIMIT 50 OFFSET 0';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data);
            $number = new NumberFormatter();
            foreach ($tempResult as $row) {
                $qty = (float)$row['jid_stock'];
                if (empty($row['jod_used']) === false) {
                    $qty -= (float)$row['jod_used'];
                }
                if ($qty > 0) {
                    $row['jid_stock'] = $qty;
                    $row['jid_stock_number'] = $number->doFormatFloat($qty);
                    $results[] = $row;
                }
            }
        }
        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jid_id') === true) {
            $wheres = [];
            $wheres[] = '(jid.jid_id = ' . $this->getIntParameter('jid_id') . ')';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jid.jid_id, jid.jid_ji_id, jog.jog_serial_number as jid_jog_number, jid.jid_whs_id, whs.whs_name as jid_whs_name, 
                      jid.jid_quantity, jid.jid_gdt_id, gdt.gdt_description as jid_gdt_description, jid.jid_gdt_remark, 
                      jid.jid_gcd_id, gcd.gcd_description as jid_gcd_description, jid.jid_gcd_remark, jid.jid_jir_id,
                      jid.jid_gdu_id, uom.uom_code as jid_uom, jid.jid_adjustment, 
                      jid.jid_jir_id, jid.jid_serial_number, jid.jid_packing_number, jid.jid_lot_number, jid.jid_gd_id, 
                      gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, gdc.gdc_name as jid_gdc_name, 
                      jog.jog_volume as jid_jog_volume, jog.jog_weight as jid_jog_net_weight, 
                      j.total_stored as jid_jir_stored, jir.jir_quantity as jid_jir_quantity
                        FROM job_inbound_detail as jid  INNER JOIN
                         warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                         goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                         job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                         job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                         goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN 
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                         goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                         goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id  LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as total_stored 
                    FROM job_inbound_detail 
                    WHERE (jid_id <> ' . $this->getIntParameter('jid_id') . ') AND (jid_deleted_on IS NULL) GROUP BY jid_jir_id) as j ON jir.jir_id = j.jid_jir_id ' . $strWhere;
            $sqlResult = DB::select($query);
            $gdDao = new GoodsDao();
            if (\count($sqlResult) === 1) {
                $number = new NumberFormatter();
                $data = DataParser::objectToArray($sqlResult[0]);
                $data['jid_goods'] = $gdDao->formatFullName($data['jid_gdc_name'], $data['jid_br_name'], $data['jid_gd_name']);
                $keys = array_keys($data);
                $qty = (float)$data['jid_jir_quantity'];
                if (empty($data['jid_jir_stored']) === false) {
                    $qty -= (float)$data['jid_jir_stored'];
                }
                $row['jid_jir_quantity'] = $qty;
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $result['jid_quantity_del_number'] = $number->doFormatFloat($result['jid_quantity_del']);
            }
        }

        return $result;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('jid_id') === true) {
            $wheres = [];
            $wheres[] = '(jid.jid_id = ' . $this->getIntParameter('jid_id') . ')';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jid.jid_id, jid.jid_ji_id, jog.jog_serial_number as jid_jog_number, jid.jid_whs_id, whs.whs_name as jid_whs_name, 
                      jid.jid_quantity, jid.jid_gdt_id, gdt.gdt_description as jid_gdt_description, jid.jid_gdt_remark, 
                      jid.jid_gcd_id, gcd.gcd_description as jid_gcd_description, jid.jid_gcd_remark, jid.jid_jir_id,
                      jog.jog_gdu_id as jid_jog_gdu_id, uom.uom_code as jid_jog_uom, jid.jid_adjustment, 
                      jog.jog_name, jid.jid_jir_id, jog.jog_production_number as jid_jog_production_number, jog.jog_production_date as jid_jog_production_date,
                      gd.gd_id as jid_gd_id, gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, gdc.gdc_name as jid_gdc_name, 
                      jog.jog_weight as jid_weight, jog.jog_length as jid_length, 
                      jog.jog_width as jid_width, jog.jog_height as jid_height, 
                      j.total_stored as jid_jir_stored, jir.jir_quantity as jid_jir_quantity, 
                      jid.jid_serial_number, jid.jid_packing_number
                        FROM job_inbound_detail as jid  INNER JOIN
                         warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                         goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                         job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                         job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                         goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN 
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                         goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                         goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id  LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as total_stored 
                    FROM job_inbound_detail 
                    WHERE (jid_id <> ' . $this->getIntParameter('jid_id') . ') AND (jid_deleted_on IS NULL) GROUP BY jid_jir_id) as j ON jir.jir_id = j.jid_jir_id ' . $strWhere;
            $sqlResult = DB::select($query);
            if (\count($sqlResult) === 1) {
                $number = new NumberFormatter();
                $data = DataParser::objectToArray($sqlResult[0]);
                $qty = (float)$data['jid_jir_quantity'];
                if (empty($data['jid_jir_stored']) === false) {
                    $qty -= (float)$data['jid_jir_stored'];
                }
                $data['jid_jir_quantity'] = $qty;

                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_up'] = $data[$key];
                }

                $result['jid_serial_number'] = $result['jid_serial_number_up'];
                $result['jid_packing_number'] = $result['jid_packing_number_up'];
                $result['jid_jir_quantity_up_number'] = $number->doFormatFloat($qty);
                $result['jid_quantity_up_number'] = $number->doFormatFloat($result['jid_quantity_up']);
                $result['jid_width_up_number'] = $number->doFormatFloat($result['jid_width_up']);
                $result['jid_length_up_number'] = $number->doFormatFloat($result['jid_length_up']);
                $result['jid_height_up_number'] = $number->doFormatFloat($result['jid_height_up']);
                $result['jid_weight_up_number'] = $number->doFormatFloat($result['jid_weight_up']);
            }
        }

        return $result;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        $result = [];
        if ($this->isValidParameter('jid_id') === true) {
            $wheres = [];
            $wheres[] = '(jid.jid_id = ' . $this->getIntParameter('jid_id') . ')';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jid.jid_id, jid.jid_ji_id, jog.jog_serial_number as jid_jog_number, jid.jid_whs_id, whs.whs_name as jid_whs_name, 
                      jid.jid_quantity, jid.jid_gdt_id, gdt.gdt_description as jid_gdt_description, jid.jid_gdt_remark, 
                      jid.jid_gcd_id, gcd.gcd_description as jid_gcd_description, jid.jid_gcd_remark, jid.jid_jir_id,
                      jid.jid_gdu_id, uom.uom_code as jid_uom, jid.jid_adjustment, 
                      jid.jid_jir_id, jid.jid_lot_number, jid.jid_serial_number, jid.jid_packing_number,
                      gd.gd_sku as jid_gd_sku, gd.gd_name as jid_gd_name, br.br_name as jid_br_name, gdc.gdc_name as jid_gdc_name, 
                      jog.jog_weight as jid_weight, jog.jog_length as jid_length, 
                      jog.jog_width as jid_width, jog.jog_height as jid_height, 
                      j.total_stored as jid_jir_stored, jir.jir_quantity as jid_jir_quantity, 
                      jid.jid_gd_id, gd.gd_sn as jid_gd_sn
                        FROM job_inbound_detail as jid  INNER JOIN
                         warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                         goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id INNER JOIN
                         job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                         job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                         goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                         brand as br ON gd.gd_br_id = br.br_id INNER JOIN 
                         goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                         goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                         goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as total_stored 
                    FROM job_inbound_detail 
                    WHERE (jid_id <> ' . $this->getIntParameter('jid_id') . ') AND (jid_deleted_on IS NULL) GROUP BY jid_jir_id) as j ON jir.jir_id = j.jid_jir_id ' . $strWhere;
            $sqlResult = DB::select($query);
            $gdDao = new GoodsDao();
            if (\count($sqlResult) === 1) {
                $number = new NumberFormatter();
                $result = DataParser::objectToArray($sqlResult[0]);
                $qty = (float)$result['jid_jir_quantity'];
                if (empty($result['jid_jir_stored']) === false) {
                    $qty -= (float)$result['jid_jir_stored'];
                }
                $result['jid_goods'] = $gdDao->formatFullName($result['jid_gdc_name'], $result['jid_br_name'], $result['jid_gd_name']);
                $result['jid_jir_quantity'] = $qty;
                $result['jid_jir_quantity_number'] = $number->doFormatFloat($qty);
                $result['jid_quantity_number'] = $number->doFormatFloat($result['jid_quantity']);
                $result['jid_width_number'] = $number->doFormatFloat($result['jid_width']);
                $result['jid_length_number'] = $number->doFormatFloat($result['jid_length']);
                $result['jid_height_number'] = $number->doFormatFloat($result['jid_height']);
                $result['jid_weight_number'] = $number->doFormatFloat($result['jid_weight']);
            }
        }

        return $result;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadStorageStockForOutbound(): array
    {
        if ($this->isValidParameter('gd_id') === true) {
            if ($this->getStringParameter('jod_gd_sn', 'N') === 'Y') {
                $wheres = [];
                $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
                $wheres[] = '(jog.jog_gd_id = ' . $this->getIntParameter('gd_id') . ')';
                $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
                $wheres[] = '(jid.jid_deleted_on IS NULL)';
                $wheres[] = '(jis.jis_stock > 0)';
                if ($this->isValidParameter('jog_production_number') === true) {
                    $wheres[] = "(jog.jog_production_number = '" . $this->getStringParameter('jog_production_number') . "')";
                }

                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
                $query = 'SELECT whs.whs_id, jog.jog_production_number as whs_production_number, whs.whs_name, SUM(jis.jis_stock) as whs_stock, jod.jod_used, null as jid_id, 
                                gdt.gdt_id, gdt.gdt_description
                    FROM job_inbound_detail as jid INNER JOIN
                    job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                    job_goods as jog ON jir.jir_jog_id = jog.jog_id LEFT OUTER JOIN
                    goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                    (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                      FROM job_inbound_stock 
                      where (jis_deleted_on IS NULL) 
                      GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN 
                      (SELECT j.jod_whs_id, SUM(j.jod_quantity) as jod_used 
                        FROM job_outbound_detail as j INNER JOIN
                        job_goods as j3 ON j3.jog_id = j.jod_jog_id
                        WHERE (j.jod_job_id = ' . $this->getIntParameter('job_id') . ') AND (j3.jog_gd_id = ' . $this->getIntParameter('gd_id') . ') AND (j.jod_deleted_on IS NULL)
                        GROUP BY j.jod_whs_id) as jod ON jid.jid_whs_id = jod.jod_whs_id ' . $strWhere;
                $query .= ' GROUP BY whs.whs_id, whs.whs_name, jod.jod_used, jog.jog_production_number, gdt.gdt_id, gdt.gdt_description';
                $query .= ' ORDER BY whs.whs_name, whs.whs_id';
                $query .= ' LIMIT 50 OFFSET 0';
                $results = [];
                $data = DB::select($query);
                if (empty($data) === false) {
                    $tempResult = DataParser::arrayObjectToArray($data);
                    $number = new NumberFormatter();
                    foreach ($tempResult as $row) {
                        $conditions = Trans::getWord('good');
                        if (empty('gdt_id') === false) {
                            $condition = $row['gdt_description'];
                        }
                        $row['whs_condition'] = $conditions;
                        $qty = (float)$row['whs_stock'];
                        if (empty($row['jod_used']) === false) {
                            $qty -= (float)$row['jod_used'];
                        }
                        if ($qty > 0) {
                            $row['whs_stock'] = $qty;
                            $row['whs_stock_number'] = $number->doFormatFloat($qty);
                            $results[] = $row;
                        }
                    }
                }
                return $results;
            } else {
                return $this->loadStorageStockForOutboundWithJidId();
            }
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadStorageStockForOutboundWithJidId(): array
    {
        $wheres = [];
        $wheres[] = '(whs.whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = '(jog.jog_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.jis_stock > 0)';
        if ($this->isValidParameter('jog_production_number') === true) {
            $wheres[] = "(jog.jog_production_number = '" . $this->getStringParameter('jog_production_number') . "')";
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, whs.whs_id, whs.whs_name, SUM(jis.jis_stock) as whs_stock, jod.jod_used
                    FROM job_inbound_detail as jid INNER JOIN
                    job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                    warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id INNER JOIN
                    job_goods as jog ON jir.jir_jog_id = jog.jog_id LEFT OUTER JOIN
                    (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                      FROM job_inbound_stock 
                      where (jis_deleted_on IS NULL) 
                      GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id LEFT OUTER JOIN 
                      (SELECT j.jod_jid_id, SUM(j.jod_quantity) as jod_used 
                        FROM job_outbound_detail as j INNER JOIN
                        job_goods as j3 ON j3.jog_id = j.jod_jog_id
                        WHERE (j.jod_job_id = ' . $this->getIntParameter('job_id') . ') AND (j.jod_deleted_on IS NULL)
                        GROUP BY j.jod_jid_id) as jod ON jid.jid_id = jod.jod_jid_id ' . $strWhere;
        $query .= ' GROUP BY jid.jid_id, whs.whs_id, whs.whs_name, jod.jod_used';
        $query .= ' ORDER BY whs.whs_name, whs.whs_id';
        $query .= ' LIMIT 50 OFFSET 0';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data);
            $number = new NumberFormatter();
            foreach ($tempResult as $row) {
                $qty = (float)$row['whs_stock'];
                if (empty($row['jod_used']) === false) {
                    $qty -= (float)$row['jod_used'];
                }
                if ($qty > 0) {
                    $row['whs_stock'] = $qty;
                    $row['whs_stock_number'] = $number->doFormatFloat($qty);
                    $results[] = $row;
                }
            }
        }
        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadAvailableSerialNumberForOutbound(): array
    {
        if ($this->isValidParameter('jid_gd_id') === true) {
            $wheres = [];
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
            $wheres[] = '(jid.jid_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
            $wheres[] = '(jid.jid_deleted_on IS NULL)';
            $wheres[] = '(jid.jid_serial_number IS NOT NULL)';
            $wheres[] = '(jis.jis_stock > 0)';
            if ($this->isValidParameter('jod_id') === true) {
                $jodWheres = [];
                $jodWheres[] = '(jod_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
                $jodWheres[] = '(jod_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
                $jodWheres[] = '(jod_gdu_id = ' . $this->getIntParameter('jid_gdu_id') . ')';
                $jodWheres[] = '(jod_id <> ' . $this->getIntParameter('jod_id') . ')';
                $jodWheres[] = '(jod_deleted_on IS NULL)';
                $jodWheres[] = '(jod_jid_id IS NOT NULL)';
                $strJodWhere = ' WHERE ' . implode(' AND ', $jodWheres);
                $wheres[] = '(jid.jid_id NOT IN (SELECT jod_jid_id
                                                FROM job_outbound_detail ' . $strJodWhere . ' ))';
            }
            if ($this->isValidParameter('jid_lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('jid_lot_number') . "')";
            }

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_id, jid.jid_serial_number, jis.jis_stock
                FROM job_inbound_detail as jid LEFT OUTER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  FROM job_inbound_stock 
                  where (jis_deleted_on IS NULL) 
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id ' . $strWhere;
            $query .= ' GROUP BY jid.jid_id, jid.jid_serial_number, jis.jis_stock';
            $query .= ' ORDER BY jid.jid_serial_number, jid.jid_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResults = DB::select($query);
            $data = DataParser::arrayObjectToArray($sqlResults);
            $results = [];
            foreach ($data as $row) {
                $results[] = [
                    'text' => $row['jid_serial_number'],
                    'value' => $row['jid_id'],
                ];
            }
            return $results;
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadAvailableLotNumber(): array
    {
        if ($this->isValidParameter('jid_gd_id') === true) {
            $wheres = [];
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
            $wheres[] = '(jid.jid_deleted_on IS NULL)';
            $wheres[] = '(jid.jid_lot_number IS NOT NULL)';
            $wheres[] = '(jis.jis_stock > 0)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_lot_number, uom.uom_code, SUM(jis.jis_stock) as total_stock
                FROM job_inbound_detail as jid INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                unit as uom ON uom.uom_id = gdu.gdu_uom_id LEFT OUTER JOIN
                (Select jis_jid_id, sum(jis_quantity) as jis_stock 
                  FROM job_inbound_stock 
                  where (jis_deleted_on IS NULL) 
                  GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id ' . $strWhere;
            $query .= ' GROUP BY jid.jid_lot_number, uom.uom_code';
            $query .= ' ORDER BY jid.jid_lot_number';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResults = DB::select($query);
            $data = DataParser::arrayObjectToArray($sqlResults);
            $results = [];
            foreach ($data as $row) {
                $stock = (float)$row['total_stock'];
                if ($stock > 0) {
                    $results[] = [
                        'text' => $row['jid_lot_number'],
                        'value' => $row['jid_lot_number'],
                    ];
                }
            }
            return $results;
        }

        return [];
    }
}
