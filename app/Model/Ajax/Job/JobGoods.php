<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobGoods.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobGoods extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadGoodsTrucking(): array
    {
        if ($this->isValidParameter('jog_jo_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jog_name', $this->getStringParameter('search_key'));
            $wheres[] = '(jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
            $wheres[] = '(jog_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jog_id, jog_name
                        FROM job_goods ' . $strWhere;
            $query .= ' ORDER BY jog_name, jog_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'jog_name', 'jog_id');
        }
        return [];
    }

    /**
     * Function to load the data for outbound
     *
     * @return array
     */
    public function getTruckingGoodsById(): array
    {
        $results = [];
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $data = JobGoodsDao::loadDataForTrucking($wheres);
            if (count($data) === 1) {
                $results = $data[0];
                $number = new NumberFormatter();
                $results['jog_quantity_number'] = $number->doFormatFloat($results['jog_quantity']);
                $results['jog_length_number'] = $number->doFormatFloat($results['jog_length']);
                $results['jog_width_number'] = $number->doFormatFloat($results['jog_width']);
                $results['jog_height_number'] = $number->doFormatFloat($results['jog_height']);
                $results['jog_weight_number'] = $number->doFormatFloat($results['jog_weight']);
                $results['jog_total_cbm_number'] = $number->doFormatFloat($results['jog_total_cbm']);
                $results['jog_total_tonnage_number'] = $number->doFormatFloat($results['jog_total_tonnage']);
            }
        }

        return $results;
    }

    /**
     * Function to load the data for outbound
     *
     * @return array
     */
    public function getTruckingGoodsByIdForDelete(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $results = [];
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $data = JobGoodsDao::loadDataForTrucking($wheres);
            if (count($data) === 1) {
                $row = $data[0];
                $keys = array_keys($row);
                foreach ($keys as $key) {
                    $results[$key . '_del'] = $row[$key];
                }
                $number = new NumberFormatter();
                $results['jog_quantity_del_number'] = $number->doFormatFloat($results['jog_quantity_del']);
            }
        }

        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jog_jo_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jog.jog_name', $this->getStringParameter('search_key'));
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT jog.jog_id, (jog.jog_serial_number || ' - ' || jog.jog_name || ' - ' || CAST(jog.jog_quantity as varchar(125)) || ' '|| uom.uom_code) as text
                        FROM job_goods as jog LEFT OUTER JOIN
                             unit as uom ON jog.jog_uom_id = uom.uom_id " . $strWhere;
            $query .= ' ORDER BY jog.jog_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'text', 'jog_id');
        }

        return [];
    }

    /**
     * Function to load the data for outbound
     *
     * @return array
     */
    public function loadDataForOutbound(): array
    {
        if ($this->isValidParameter('jog_jo_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
            if ($this->isValidParameter('jog_ignore_id') === true) {
                $wheres[] = '(jog.jog_id <> ' . $this->getIntParameter('jog_ignore_id') . ')';
            }
            if ($this->isValidParameter('jog_id') === true) {
                $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            }
            $wheres[] = '(jog.jog_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_gd_name, br.br_name as jog_br_name,
                        gdc.gdc_name as jog_gdc_name, jog.jog_production_number, jog.jog_quantity, jog.jog_gdu_id, uom.uom_code as jog_uom, jod.qty_used,
                        gd.gd_sn as jog_gd_sn
                        FROM job_goods as jog INNER JOIN
                            goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                            goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id INNER JOIN
                            brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                            goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                             unit as uom ON gdu.gdu_uom_id = uom.uom_id  LEFT OUTER JOIN
                             (SELECT jod_jog_id, SUM(jod_quantity) as qty_used
                                FROM job_outbound_detail
                                WHERE (jod_job_id = ' . $this->getIntParameter('job_id') . ') AND (jod_deleted_on IS NULL)
                                GROUP BY jod_jog_id) as jod ON jog.jog_id = jod.jod_jog_id ' . $strWhere;
            $query .= ' ORDER BY jog.jog_name, jog.jog_id';
            $query .= ' LIMIT 30 OFFSET 0';

            $results = [];
            $data = DB::select($query);
            if (empty($data) === false) {
                $tempResult = DataParser::arrayObjectToArray($data);
                $number = new NumberFormatter();
                $gdDao = new GoodsDao();
                foreach ($tempResult as $row) {
                    $row['jog_goods'] = $gdDao->formatFullName($row['jog_gdc_name'], $row['jog_br_name'], $row['jog_gd_name']);
                    $qty = (float)$row['jog_quantity'];
                    if (empty($row['qty_used']) === false) {
                        $qty -= (float)$row['qty_used'];
                    }
                    if ($qty > 0) {
                        $row['jog_quantity'] = $qty;
                        $row['jog_quantity_number'] = $number->doFormatFloat($qty);
                        $results[] = $row;
                    }
                }
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
    public function loadProductionNumbers(): array
    {
        $results = [];
        if ($this->isValidParameter('jog_gd_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jog_production_number', $this->getStringParameter('search_key'));
            $wheres[] = '(jog_gd_id = ' . $this->getIntParameter('jog_gd_id') . ')';
            $wheres[] = '(jog.jog_production_number IS NOT NULL)';
            $wheres[] = '(jir.jir_quantity > 0)';
            $wheres[] = '(jis.stock > 0)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jog.jog_production_number, jog.jog_production_date FROM job_goods as jog INNER JOIN
                            job_inbound_receive as jir ON jir.jir_jog_id = jog.jog_id INNER JOIN
                            job_inbound_detail as jid ON jid.jid_jir_id = jir.jir_id INNER JOIN
                            (SELECT jis_jid_id, SUM(jis_quantity) as stock FROM job_inbound_stock WHERE jis_deleted_on IS NULL GROUP BY jis_jid_id) as jis ON jis.jis_jid_id = jid.jid_id ' . $strWhere;
            $query .= ' GROUP BY jog.jog_production_number, jog.jog_production_date';
            $query .= ' ORDER BY jog.jog_production_date, jog.jog_production_number';
            $query .= ' LIMIT 30 OFFSET 0';
            $data = DB::select($query);
            if (empty($data) === false) {
                $tempResult = DataParser::arrayObjectToArray($data, [
                    'jog_production_number',
                    'jog_production_date',
                ]);
                foreach ($tempResult as $row) {
                    $time = '';
                    if (empty($row['jog_production_date']) === false) {
                        $time = ' - ' . DateTimeParser::format($row['jog_production_date'], 'Y-m-d', 'd.M.Y');
                    }
                    $results[] = [
                        'text' => $row['jog_production_number'] . $time,
                        'value' => $row['jog_production_number']
                    ];
                }
            }
        }

        # return the data.
        return $results;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectWarehouseData(): array
    {
        if ($this->isValidParameter('jog_jo_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jog.jog_name', $this->getStringParameter('search_key'));
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT jog.jog_id, (jog.jog_name || ' - ' || CAST(jwl.jwl_quantity as varchar(125)) || ' - ' || uom.uom_code) as text
                        FROM job_goods as jog LEFT OUTER JOIN
                             unit as uom ON jog.jog_uom_id = uom.uom_id LEFT OUTER JOIN
                              job_warehouse_load as jwl ON jog.jog_id = jwl.jwl_jog_id " . $strWhere;
            $query .= ' ORDER BY jog.jog_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'text', 'jog_id');
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadWhsForGenerateSerialNumber(): array
    {
        if ($this->isValidParameter('jog_jo_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jog.jog_name', $this->getStringParameter('search_key'));
            $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
            $wheres[] = '(jog.jog_deleted_on IS NULL)';
            $wheres[] = "(gd.gd_generate_sn = 'Y')";
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT jog.jog_id, (gd.gd_sku || ' - ' || jog.jog_name) as text
                        FROM job_goods as jog INNER JOIN
                             goods AS gd On gd.gd_id = jog.jog_gd_id" . $strWhere;
            $query .= ' ORDER BY jog.jog_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'text', 'jog_id');
        }

        return [];
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getDefaultGoodsById(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $result = JobGoodsDao::getByReference($this->getIntParameter('jog_id'));
            $number = new NumberFormatter();
            $result['jog_quantity_number'] = $number->doFormatFloat($result['jog_quantity']);
            $result['jog_length_number'] = $number->doFormatFloat($result['jog_length']);
            $result['jog_width_number'] = $number->doFormatFloat($result['jog_width']);
            $result['jog_height_number'] = $number->doFormatFloat($result['jog_height']);
            $result['jog_volume_number'] = $number->doFormatFloat($result['jog_volume']);
            $result['jog_gross_weight_number'] = $number->doFormatFloat($result['jog_gross_weight']);
            $result['jog_net_weight_number'] = $number->doFormatFloat($result['jog_net_weight']);

            return $result;
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getInboundGoodsById(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadDataForInbound($wheres);
            if (count($temp) === 1) {
                $result = $temp[0];
                $number = new NumberFormatter();
                if (empty($temp['jog_gd_id']) === false) {
                    $result['jog_goods'] = $result['jog_sku'] . ' | ' . $result['jog_goods'];
                }
                $result['jog_quantity_number'] = $number->doFormatFloat($result['jog_quantity']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getInboundGoodsByIdForDelete(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadDataForInbound($wheres);
            if (count($temp) === 1) {
                $result = [];
                $keys = array_keys($temp[0]);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[0][$key];
                }
                $number = new NumberFormatter();
                $result['jog_quantity_del_number'] = $number->doFormatFloat($result['jog_quantity_del']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getDefaultGoodsByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jog_id') === true) {
            $goods = JobGoodsDao::getByReference($this->getIntParameter('jog_id'));
            if (empty($goods) === false) {
                $keys = array_keys($goods);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $goods[$key];
                }
                $number = new NumberFormatter();
                $result['jog_quantity_del_number'] = $number->doFormatFloat($result['jog_quantity_del']);
                $result['jog_length_del_number'] = $number->doFormatFloat($result['jog_length_del']);
                $result['jog_width_del_number'] = $number->doFormatFloat($result['jog_width_del']);
                $result['jog_height_del_number'] = $number->doFormatFloat($result['jog_height_del']);
                $result['jog_volume_del_number'] = $number->doFormatFloat($result['jog_volume_del']);
                $result['jog_gross_weight_del_number'] = $number->doFormatFloat($result['jog_gross_weight_del']);
                $result['jog_net_weight_del_number'] = $number->doFormatFloat($result['jog_net_weight_del']);

                return $result;
            }
        }

        return $result;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getOutboundGoodsById(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadDataForOutbound($wheres);
            if (count($temp) === 1) {
                $result = $temp[0];
                $number = new NumberFormatter();
                if (empty($temp['jog_gd_id']) === false) {
                    $result['jog_goods'] = $result['jog_sku'] . ' | ' . $result['jog_goods'];
                }
                $result['jog_quantity_number'] = $number->doFormatFloat($result['jog_quantity']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getOutboundGoodsByIdForDelete(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadDataForOutbound($wheres);
            if (count($temp) === 1) {
                $result = [];
                $keys = array_keys($temp[0]);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[0][$key];
                }
                $number = new NumberFormatter();
                $result['jog_quantity_del_number'] = $number->doFormatFloat($result['jog_quantity_del']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadInboundForOutboundGoods(): array
    {
        $results = [];
        if ($this->isValidParameter('jo_ss_id_in') === true) {
            $wheres = [];
            $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('jo_ss_id_in') . ')';
            $wheres[] = '(jo.jo_srt_id = 1)';
            if ($this->isValidParameter('jo_rel_id_in') === true) {
                $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id_in') . ')';
            }
            if ($this->isValidParameter('jo_number_in') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number_in'));
            }
            if ($this->isValidParameter('jo_customer_ref_in') === true) {
                $soRef = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('jo_customer_ref_in'));
                $joRef = SqlHelper::generateLikeCondition('jo.jo_customer_ref', $this->getStringParameter('jo_customer_ref_in'));
                $wheres[] = '(' . $soRef . ' OR ' . $joRef . ')';
            }
            if ($this->isValidParameter('jo_aju_ref_in') === true) {
                $soRef = SqlHelper::generateLikeCondition('so.so_aju_ref', $this->getStringParameter('jo_aju_ref_in'));
                $joRef = SqlHelper::generateLikeCondition('jo.jo_aju_ref', $this->getStringParameter('jo_aju_ref_in'));
                $wheres[] = '(' . $soRef . ' OR ' . $joRef . ')';
            }
            if ($this->isValidParameter('jo_bl_ref_in') === true) {
                $soRef = SqlHelper::generateLikeCondition('so.so_bl_ref', $this->getStringParameter('jo_bl_ref_in'));
                $joRef = SqlHelper::generateLikeCondition('jo.jo_bl_ref', $this->getStringParameter('jo_bl_ref_in'));
                $wheres[] = '(' . $soRef . ' OR ' . $joRef . ')';
            }
            $data = JobGoodsDao::loadSimpleDataForInbound($wheres);
            if (empty($data) === false) {
                $temp = [];
                $jobIds = [];
                $tempResults = [];
                foreach ($data as $row) {
                    if (in_array($row['jo_id'], $jobIds, true) === false) {
                        $temp['jo_id_in'] = $row['jo_id'];
                        $temp['jo_number_in'] = $row['jo_number'];
                        $temp['jo_customer_ref_in'] = $row['jo_customer_ref'];
                        $temp['jo_bl_ref_in'] = $row['jo_bl_ref'];
                        $temp['jo_aju_ref_in'] = $row['jo_aju_ref'];
                        $temp['jo_goods'] = [];
                        $temp['jo_goods'][] = $row['jog_name'];
                        $jobIds[] = $row['jo_id'];
                        $tempResults[] = $temp;
                    } else {
                        $index = array_search($row['jo_id'], $jobIds, true);
                        $tempResults[$index]['jo_goods'][] .= $row['jog_name'];
                    }
                }

                foreach ($tempResults as $row) {
                    $arr = $row['jo_goods'];
                    $row['jo_goods_in'] = StringFormatter::generateTableView($arr);
                    $row['jo_goods_field_in'] = implode('; ', $arr);
                    unset($row['jo_goods']);
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
    public function getInklaringGoodsById(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadData($wheres);
            if (count($temp) === 1) {
                $result = $temp[0];
                $number = new NumberFormatter();
                $result['jog_quantity_number'] = $number->doFormatFloat($result['jog_quantity']);
                $result['jog_weight_number'] = $number->doFormatFloat($result['jog_weight']);
                $result['jog_volume_number'] = $number->doFormatFloat($result['jog_volume']);
                $result['jog_hscode'] = $result['jog_production_number'];

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getInklaringGoodsByIdForDelete(): array
    {
        if ($this->isValidParameter('jog_id') === true) {
            $wheres = [];
            $wheres[] = '(jog.jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $temp = JobGoodsDao::loadData($wheres);
            if (count($temp) === 1) {
                $result = [];
                $keys = array_keys($temp[0]);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[0][$key];
                }
                $number = new NumberFormatter();
                $result['jog_quantity_del_number'] = $number->doFormatFloat($result['jog_quantity_del']);
                $result['jog_weight_del_number'] = $number->doFormatFloat($result['jog_weight_del']);
                $result['jog_volume_del_number'] = $number->doFormatFloat($result['jog_volume_del']);
                $result['jog_hscode_del'] = $result['jog_production_number_del'];

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadPickuGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_name, jog.jog_quantity, jog.jog_uom_id,
                         jog.jog_weight, jog.jog_volume,  uom.uom_name as jog_unit, jikr.total_load
                  FROM job_goods as jog INNER JOIN
                       unit as uom ON uom.uom_id = jog.jog_uom_id LEFT OUTER JOIN
                    (SELECT jikr_jog_id, SUM(jikr_quantity) as total_load
                    FROM job_inklaring_release
                        WHERE  (jikr_deleted_on IS NULL)
                    GROUP BY jikr_jog_id) as jikr ON jikr.jikr_jog_id = jog.jog_id' . $strWhere;
        $query .= ' ORDER BY jog.jog_name';
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $tempResult = DataParser::arrayObjectToArray($data);
            $number = new NumberFormatter();
            foreach ($tempResult as $row) {
                $qty = (float)$row['jog_quantity'];
                if (empty($row['total_load']) === false) {
                    $qty -= (float)$row['total_load'];
                }
                if ($qty > 0) {
                    $row['jog_quantity'] = $qty;
                    $row['jog_quantity_number'] = $number->doFormatFloat($qty);

                    $results[] = $row;
                }
                $row['jog_weight_number'] = $number->doFormatFloat($row['jog_weight']);
                $row['jog_volume_number'] = $number->doFormatFloat($row['jog_volume']);
            }
        }

        return $results;
    }
}
