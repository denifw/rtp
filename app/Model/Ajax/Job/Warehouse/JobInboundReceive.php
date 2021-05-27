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
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\Warehouse\JobInboundReceiveDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobInboundReceive.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInboundReceive extends AbstractBaseAjaxModel
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
        if ($this->isValidParameter('jir_ji_id') === true) {
            $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
        }
        $subWheresJid = '';
        if ($this->isValidParameter('jid_id') === true) {
            $subWheresJid = ' AND (jid_id <> ' . $this->getIntParameter('jid_id') . ')';
        }
        $wheres[] = '(jog.jog_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jir_id,jog.jog_serial_number,jog.jog_name,jir.jir_quantity, jid.total_stored
                    FROM job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jir.jir_jog_id = jog.jog_id  LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as total_stored 
                    FROM job_inbound_detail 
                    WHERE (jid_deleted_on IS NULL) ' . $subWheresJid . ' GROUP BY jid_jir_id) as jid ON jir.jir_id = jid.jid_jir_id ' . $strWhere;
        $query .= ' ORDER BY jog.jog_serial_number';
        $query .= ' LIMIT 30 OFFSET 0';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data, [
                'jir_id',
                'jog_serial_number',
                'jog_name',
                'jir_quantity',
                'total_stored',
            ]);
            foreach ($tempResult as $row) {
                $qty = (float)$row['jir_quantity'];
                if (empty($row['total_stored']) === false) {
                    $qty -= (float)$row['total_stored'];
                }
                if ($qty !== 0.0) {
                    $results[] = [
                        'text' => $row['jog_serial_number'] . ' - ' . $row['jog_name'] . ' - ' . $qty,
                        'value' => $row['jir_id'],
                    ];
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
    public function loadPutAwayData(): array
    {
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        $wheres[] = "(jir.jir_stored = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $subWheres = [];
        $subWheres[] = '(jid_deleted_on IS NULL)';
        if ($this->isValidParameter('jid_id') === true) {
            $subWheres[] = '(jid_id <> ' . $this->getIntParameter('jid_id') . ')';
        }
        $subWheresJid = ' WHERE ' . implode(' AND ', $subWheres);
        $query = 'SELECT jir.jir_id, jir.jir_quantity, gd.gd_id as jir_gd_id, gd.gd_sku as jir_gd_sku, gd.gd_name as jir_gd_name, br.br_name as jir_br_name,
                        gdc.gdc_name as jir_gdc_name, jog.jog_production_number as jir_jog_production_number, jog.jog_gdu_id as jir_jog_gdu_id, uom.uom_code as jir_jog_uom, 
                        jog.jog_serial_number as jir_jog_number, jir.jir_gdt_id, gdt.gdt_description as jir_gdt_description, jir.jir_gdt_remark,
                        jir.jir_gcd_id, gcd.gcd_description as jir_gcd_description, jir.jir_gcd_remark, jid.total_stored, 
                        jir.jir_length, jir.jir_width, jir.jir_height, jir.jir_weight, jir.jir_volume, 
                        gd.gd_sn as jir_gd_sn, gd.gd_packing AS jir_gd_packing, gd.gd_expired AS jir_gd_expired,
                        jir.jir_serial_number, jir.jir_lot_number, jir.jir_packing_number, jir.jir_expired_date
                    FROM job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                    goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                    brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                    goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                    goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                    unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                    goods_damage_type as gdt ON jir.jir_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                     goods_cause_damage as gcd ON jir.jir_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                    (SELECT jid_jir_id, SUM(jid_quantity) as total_stored 
                    FROM job_inbound_detail ' . $subWheresJid . ' 
                    GROUP BY jid_jir_id) as jid ON jir.jir_id = jid.jid_jir_id ' . $strWhere;
        $query .= ' ORDER BY gd.gd_sku, jir.jir_lot_number, jir.jir_packing_number, jir.jir_serial_number, jir.jir_id';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data);
            $number = new NumberFormatter();
            $gdDao = new GoodsDao();
            foreach ($tempResult as $row) {
                $row['jir_goods'] = $gdDao->formatFullName($row['jir_gdc_name'], $row['jir_br_name'], $row['jir_gd_name']);
                $qty = (float)$row['jir_quantity'];
                if (empty($row['total_stored']) === false) {
                    $qty -= (float)$row['total_stored'];
                }
                if ($qty > 0) {
                    $row['jir_quantity'] = $qty;
                    $row['jir_quantity_number'] = $number->doFormatFloat($qty);

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
    public function loadDamageData(): array
    {
        $wheres = [];
        $wheres[] = '(jir.jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $wheres[] = '(jir.jir_qty_damage > 0)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);

        $subWheres = [];
        $subWheres[] = '(jidm_deleted_on IS NULL)';
        if ($this->isValidParameter('jidm_id') === true) {
            $subWheres[] = '(jidm_id <> ' . $this->getIntParameter('jidm_id') . ')';
        }
        $strSubWhere = ' WHERE ' . implode(' AND ', $subWheres);


        $query = 'SELECT jir.jir_id, jir.jir_qty_damage, gd.gd_sku as jir_gd_sku, gd.gd_name as jir_gd_name, br.br_name as jir_br_name,
                        gdc.gdc_name as jir_gdc_name, jog.jog_production_number as jir_jog_production_number, uom.uom_code as jir_jog_uom, 
                        jog.jog_serial_number as jir_jog_number, jidm.total_used, gd.gd_tonnage as jir_gd_tonnage, 
                        gd.gd_cbm as jir_gd_cbm 
                    FROM job_inbound_receive as jir INNER JOIN
                    job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                    goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                    brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                    goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                    goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id INNER JOIN
                    unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                    (SELECT jidm_jir_id, SUM(jidm_quantity) as total_used 
                        FROM job_inbound_damage ' . $strSubWhere . ' 
                        GROUP BY jidm_jir_id) as jidm ON jir.jir_id = jidm.jidm_jir_id ' . $strWhere;
        $query .= ' ORDER BY jog.jog_serial_number, jir.jir_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $results = [];
        $data = DB::select($query);
        $gdDao = new GoodsDao();
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data);
            $number = new NumberFormatter();
            foreach ($tempResult as $row) {
                $row['jir_goods'] = $gdDao->formatFullName($row['jir_gdc_name'], $row['jir_br_name'], $row['jir_gd_name']);
                $qty = (float)$row['jir_qty_damage'];
                if (empty($row['total_used']) === false) {
                    $qty -= (float)$row['total_used'];
                }
                if ($qty > 0) {
                    $row['jir_qty_damage'] = $qty;
                    $row['jir_qty_damage_number'] = $number->doFormatFloat($qty);
                    $results[] = $row;
                }
            }
        }

        return $results;
    }

    /**
     * Function to load data by job goods id
     *
     * @return array
     */
    public function getByJobGoodsId(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jog.jog_id as jir_jog_id, jog.jog_serial_number as jir_jog_serial_number, jog.jog_gd_id as jir_jog_gd_id, 
                        gd.gd_sku as jir_jog_sku, gd.gd_name as jir_jog_goods, jog.jog_name as jir_jog_name, jog.jog_quantity as jir_jog_quantity,
                      jog.jog_gdu_id as jir_jog_gdu_id, uom.uom_code as jir_jog_unit, br.br_name as jir_jog_goods_brand, 
                      gdc.gdc_name as jir_jog_goods_category, jir.jir_id, jir_quantity, jir.jir_qty_damage 
                        FROM job_goods as jog LEFT OUTER JOIN
                        goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id LEFT OUTER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN 
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
                           job_inbound_receive as jir ON jog.jog_id = jir.jir_jog_id ' . $strWhere;
            $query .= ' GROUP BY jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku, gd.gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code, br.br_name, 
                      gdc.gdc_name, jir.jir_id, jir_quantity, jir.jir_qty_damage';
            $sqlResult = DB::select($query);
            if (\count($sqlResult) === 1) {
                $result = DataParser::objectToArray($sqlResult[0], [
                    'jir_jog_id', 'jir_jog_gd_id', 'jir_jog_sku', 'jir_jog_goods', 'jir_jog_name',
                    'jir_jog_quantity', 'jir_jog_uom_id', 'jir_jog_unit', 'jir_jog_serial_number',
                    'jir_jog_goods_brand', 'jir_jog_goods_category', 'jir_id', 'jir_quantity', 'jir_qty_damage',
                ]);
                $number = new NumberFormatter();
                $result['jir_jog_quantity_number'] = $number->doFormatFloat($result['jir_jog_quantity']);
                $result['jir_quantity_number'] = $number->doFormatFloat($result['jir_quantity']);
                $result['jir_qty_damage_number'] = $number->doFormatFloat($result['jir_qty_damage']);
            }
        }

        return $result;
    }


    /**
     * Function to load data by job goods id
     *
     * @return array
     */
    public function getByJobGoodsIdForDamage(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jog.jog_id as jog_dm_id, jog.jog_serial_number as jog_dm_serial_number, jog.jog_gd_id as jog_dm_gd_id, 
                        gd.gd_sku as jog_dm_sku, gd.gd_name as jog_dm_goods, jog.jog_name as jog_dm_name, jog.jog_quantity as jog_dm_quantity,
                      jog.jog_uom_id as jog_dm_uom_id, uom.uom_code as jog_dm_unit, br.br_name as jog_dm_goods_brand, 
                      gdc.gdc_name as jog_dm_goods_category, jir.jir_id as jir_dm_id, jog.jog_net_weight as jog_dm_net_weight, 
                      jog.jog_length as jog_dm_length, jog.jog_width as jog_dm_width, jog.jog_height as jog_dm_height
                        FROM job_goods as jog LEFT OUTER JOIN
                         unit as uom ON jog.jog_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN 
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id LEFT OUTER JOIN
                           job_inbound_receive as jir ON jog.jog_id = jir.jir_jog_id ' . $strWhere;
            $query .= ' GROUP BY jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku, gd.gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code, br.br_name, 
                      gdc.gdc_name, jir.jir_id, jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_net_weight';
            $sqlResult = DB::select($query);
            if (\count($sqlResult) === 1) {
                $result = DataParser::objectToArray($sqlResult[0], [
                    'jog_dm_id', 'jog_dm_gd_id', 'jog_dm_sku', 'jog_dm_goods', 'jog_dm_name',
                    'jog_dm_quantity', 'jog_dm_uom_id', 'jog_dm_unit', 'jog_dm_serial_number',
                    'jog_dm_goods_brand', 'jog_dm_goods_category', 'jir_dm_id',
                    'jog_dm_net_weight',
                    'jog_dm_length',
                    'jog_dm_width',
                    'jog_dm_height',
                ]);
                $number = new NumberFormatter();
                $result['jog_dm_quantity_number'] = $number->doFormatFloat($result['jog_dm_quantity']);
                $result['jog_dm_height_number'] = $number->doFormatFloat($result['jog_dm_height']);
                $result['jog_dm_width_number'] = $number->doFormatFloat($result['jog_dm_width']);
                $result['jog_dm_length_number'] = $number->doFormatFloat($result['jog_dm_length']);
                $result['jog_dm_net_weight_number'] = $number->doFormatFloat($result['jog_dm_net_weight']);
            }
        }

        return $result;
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getByReference(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_id') === true) {
            $wheres = [];
            $wheres[] = '(jir.jir_id = ' . $this->getIntParameter('jir_id') . ')';
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            $query = 'SELECT jog.jog_id as jir_jog_id, jog.jog_serial_number as jir_jog_serial_number, jog.jog_gd_id as jir_jog_gd_id, 
                        gd.gd_sku as jir_jog_sku, gd.gd_name as jir_jog_goods, jog.jog_name as jir_jog_name, jog.jog_quantity as jir_jog_quantity,
                      jog.jog_gdu_id as jir_jog_gdu_id, uom.uom_code as jir_jog_unit, br.br_name as jir_jog_goods_brand, 
                      gdc.gdc_name as jir_jog_goods_category, jir.jir_id, jir_quantity, jir.jir_qty_damage
                        FROM job_inbound_receive as jir INNER JOIN
                        job_goods as jog ON jog.jog_id = jir.jir_jog_id LEFT OUTER JOIN
                        goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id LEFT OUTER JOIN
                         unit as uom ON gdu.gdu_uom_id = uom.uom_id LEFT OUTER JOIN
                          goods as gd ON jog.jog_gd_id = gd.gd_id LEFT OUTER JOIN 
                          goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id LEFT OUTER JOIN
                           brand as br ON gd.gd_br_id = br.br_id ' . $strWhere;
            $query .= ' GROUP BY jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku, gd.gd_name, jog.jog_name, jog.jog_quantity,
                      jog.jog_uom_id, uom.uom_code, br.br_name, 
                      gdc.gdc_name, jir.jir_id, jir_quantity, jir.jir_qty_damage';
            $sqlResult = DB::select($query);
            if (\count($sqlResult) === 1) {
                $result = DataParser::objectToArray($sqlResult[0], [
                    'jir_jog_id', 'jir_jog_gd_id', 'jir_jog_sku', 'jir_jog_goods', 'jir_jog_name',
                    'jir_jog_quantity', 'jir_jog_uom_id', 'jir_jog_unit', 'jir_jog_serial_number',
                    'jir_jog_goods_brand', 'jir_jog_goods_category', 'jir_id', 'jir_quantity', 'jir_qty_damage',
                ]);
                $number = new NumberFormatter();
                $result['jir_jog_quantity_number'] = $number->doFormatFloat($result['jir_jog_quantity']);
                $result['jir_quantity_number'] = $number->doFormatFloat($result['jir_quantity']);
                $result['jir_qty_damage_number'] = $number->doFormatFloat($result['jir_qty_damage']);
            }
        }

        return $result;
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getJobGoodsById(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jir_jog_id') . ')';
            $data = JobGoodsDao::loadWarehouseData($wheres);
            if (count($data) === 1) {
                $row = $data[0];
                $result = [
                    'jir_jog_id' => $row['jog_id'],
                    'jir_gd_id' => $row['jog_gd_id'],
                    'jir_jog_number' => $row['jog_serial_number'],
                    'jir_gd_sku' => $row['jog_gd_sku'],
                    'jir_goods' => $row['jog_name'],
                    'jir_uom_code' => $row['jog_uom_code'],
                    'jir_gd_sn' => $row['jog_gd_sn'],
                    'jir_gd_multi_sn' => $row['jog_gd_multi_sn'],
                    'jir_gd_generate_sn' => $row['jog_gd_generate_sn'],
                    'jir_gd_receive_sn' => $row['jog_gd_receive_sn'],
                    'jir_gd_tonnage' => $row['jog_gd_tonnage'],
                    'jir_gd_tonnage_dm' => $row['jog_gd_tonnage_dm'],
                    'jir_gd_min_tonnage' => $row['jog_gd_min_tonnage'],
                    'jir_gd_max_tonnage' => $row['jog_gd_max_tonnage'],
                    'jir_gd_cbm' => $row['jog_gd_cbm'],
                    'jir_gd_cbm_dm' => $row['jog_gd_cbm_dm'],
                    'jir_gd_min_cbm' => $row['jog_gd_min_cbm'],
                    'jir_gd_max_cbm' => $row['jog_gd_max_cbm'],
                    'jir_gd_packing' => $row['jog_gd_packing'],
                    'jir_gd_expired' => $row['jog_gd_expired'],
                ];
                if ($row['jog_gd_sn'] === 'Y') {
                    $result['jir_quantity'] = 1;
                    $result['jir_quantity_number'] = '1';
                } else {
                    $result['jir_quantity'] = 0;
                    $result['jir_quantity_number'] = '';
                }
            }
        }

        return $result;
    }


    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function gdtByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_id') === true) {
            $wheres = [];
            $wheres[] = '(jir.jir_id = ' . $this->getIntParameter('jir_id') . ')';
            $data = JobInboundReceiveDao::loadData($wheres);
            if (count($data) === 1) {
                $gdDao = new GoodsDao();

                $result = $data[0];
                $result['jir_goods'] = $gdDao->formatFullName($result['jir_gd_category'], $result['jir_gd_brand'], $result['jir_gd_name']);
                $number = new NumberFormatter();
                $result['jir_quantity_number'] = $number->doFormatFloat($result['jir_quantity']);
                $result['jir_weight_number'] = $number->doFormatFloat($result['jir_weight']);
                $result['jir_length_number'] = $number->doFormatFloat($result['jir_length']);
                $result['jir_width_number'] = $number->doFormatFloat($result['jir_width']);
                $result['jir_height_number'] = $number->doFormatFloat($result['jir_height']);
                if (empty($result['jir_gdt_id']) === false) {
                    $result['jir_condition'] = 'N';
                } else {
                    $result['jir_condition'] = 'Y';
                }
            }
        }

        return $result;
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function gdtByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_id') === true) {
            $wheres = [];
            $wheres[] = '(jir.jir_id = ' . $this->getIntParameter('jir_id') . ')';
            $data = JobInboundReceiveDao::loadData($wheres);
            if (count($data) === 1) {
                $temp = $data[0];
                $keys = array_keys($temp);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[$key];
                }
                $gdDao = new GoodsDao();

                $result['jir_goods_del'] = $gdDao->formatFullName($result['jir_gd_category_del'], $result['jir_gd_brand_del'], $result['jir_gd_name_del']);
                $number = new NumberFormatter();
                $result['jir_quantity_del_number'] = $number->doFormatFloat($result['jir_quantity_del']);
                $result['jir_weight_del_number'] = $number->doFormatFloat($result['jir_weight_del']);
                $result['jir_length_del_number'] = $number->doFormatFloat($result['jir_length_del']);
                $result['jir_width_del_number'] = $number->doFormatFloat($result['jir_width_del']);
                $result['jir_height_del_number'] = $number->doFormatFloat($result['jir_height_del']);
                if (empty($result['jir_gdt_id_del']) === false) {
                    $result['jir_condition_del'] = Trans::getWord('damage');
                } else {
                    $result['jir_condition_del'] = Trans::getWord('good');
                }
                if ($result['jir_stored_del'] === 'Y') {
                    $result['jir_stored_del'] = Trans::getWord('stored');
                } else {
                    $result['jir_stored_del'] = Trans::getWord('returned');
                }
            }
        }

        return $result;
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getGenerateSnByGdId(): array
    {
        $result = [];
        if ($this->isValidParameter('jir_gd_id') === true) {
            $data = GoodsDao::getByReference($this->getIntParameter('jir_gd_id'));
            if (empty($data) === false) {
                $result['gn_sn_gd_id'] = $data['gd_id'];
                $result['gn_sn_gd_sku'] = $data['gd_sku'];
                $result['gn_sn_goods'] = $data['gd_name'];
                $result['gn_sn_quantity'] = 0;
                $result['gn_sn_quantity_number'] = '';
            }
        }

        return $result;
    }

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function loadPackingNumber(): array
    {
        $results = [];
        if ($this->isValidParameter('jir_ji_id') === true) {
            $wheres = [];
            $wheres[] = '(jir_ji_id = ' . $this->getIntParameter('jir_ji_id') . ')';
            $wheres[] = StringFormatter::generateLikeQuery('jir_packing_number', $this->getStringParameter('search_key'));
            $data = JobInboundReceiveDao::loadPackingNumber($wheres);
            $results[] = [
                'text' => '+ ' . Trans::getWord('createNew'),
                'value' => 'new',
            ];
            foreach ($data as $row) {
                $results[] = [
                    'text' => $row['jir_packing_number'],
                    'value' => $row['jir_packing_number'],
                ];
            }
        }

        return $results;
    }

}
