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
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseApi;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\WarehouseDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class Master extends AbstractBaseApi
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadWarehouseStorage') {
            $this->Validation->checkRequire('wh_id');
            $this->Validation->checkInt('wh_id');
        } elseif ($this->ActionName === 'loadGoodUnit') {
            $this->Validation->checkRequire('gdu_gd_id');
            $this->Validation->checkInt('gdu_gd_id');
        } elseif ($this->ActionName === 'loadActionEvents') {
            $this->Validation->checkRequire('ac_id');
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {
        if ($this->ActionName === 'loadRelationGoods') {
            $data = $this->loadRelationGoodsData();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadGoods') {
            $data = $this->loadGoodsData();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadWarehouse') {
            $data = $this->loadWarehouseData();
            $this->addResultData('listWarehouse', $data);
        } elseif ($this->ActionName === 'loadRelation') {
            $data = $this->loadRelation();
            $this->addResultData('relations', $data);
        } elseif ($this->ActionName === 'loadActionEvents') {
            $data = $this->loadActionEvents();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadGoodDamageType') {
            $data = $this->loadGoodDamageType();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadGoodCauseDamage') {
            $data = $this->loadGoodCauseDamage();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadWarehouseStorage') {
            $data = $this->loadWarehouseStorage();
            $this->addResultData('selectData', $data);
        } elseif ($this->ActionName === 'loadGoodUnit') {
            $data = $this->loadGoodsUnit();
            $this->addResultData('selectData', $data);
        }
    }


    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function loadGoodsUnit(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('uom.uom_code', $this->getStringParameter('qry', ''));
        $wheres[] = '(gdu.gdu_gd_id = ' . $this->getIntParameter('gdu_gd_id') . ')';
        $wheres[] = '(gdu.gdu_deleted_on IS NULL)';
        $wheres[] = "(gdu.gdu_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gdu.gdu_id, uom.uom_code
                        FROM goods_unit as gdu INNER JOIN
                        unit as uom on uom.uom_id = gdu.gdu_uom_id ' . $strWhere;
        $query .= ' ORDER BY uom.uom_code, gdu.gdu_id';
        $query .= ' LIMIT 30 OFFSET 0';

        $sqlResult = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResult);

        return $this->doPrepareSingleSelectData($data, 'gdu_id', 'uom_code');
    }


    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function loadWarehouseStorage(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('whs_name', $this->getStringParameter('qry', ''));
        $wheres[] = '(whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = '(whs_deleted_on IS NULL)';
        $wheres[] = "(whs_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs_id, whs_wh_id, whs_name, whs_width, whs_height, whs_length, whs_volume, whs_active
                        FROM warehouse_storage' . $strWhere;
        $query .= ' ORDER BY whs_name, whs_id';
        $query .= ' LIMIT 30 OFFSET 0';

        $sqlResult = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResult);

        return $this->doPrepareSingleSelectData($data, 'whs_id', 'whs_name');
    }


    /**
     * Function to load list event for action
     *
     *
     * @return array
     */
    private function loadGoodCauseDamage(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('gcd_description', $this->getStringParameter('qry', ''));
        $wheres[] = '(gcd_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = "(gcd_active = 'Y')";
        $wheres[] = '(gcd_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gcd_id, gcd_code, gcd_description, gcd_ss_id, gcd_active
                        FROM goods_cause_damage' . $strWhere;
        $query .= ' ORDER BY gcd_description, gcd_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResult = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResult);
        return $this->doPrepareSingleSelectData($data, 'gcd_id', 'gcd_description');
    }

    /**
     * Function to load list event for action
     *
     *
     * @return array
     */
    private function loadGoodDamageType(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('gdt_description', $this->getStringParameter('qry', ''));
        $wheres[] = '(gdt_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = "(gdt_active = 'Y')";
        $wheres[] = '(gdt_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gdt_id, gdt_code, gdt_ss_id, gdt_description, gdt_active
                        FROM goods_damage_type' . $strWhere;
        $query .= ' ORDER BY gdt_description, gdt_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResult = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResult);
        return $this->doPrepareSingleSelectData($data, 'gdt_id', 'gdt_description');
    }

    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function loadActionEvents(): array
    {
        $results = [];
        if ($this->isValidParameter('ac_id') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('sae.sae_description', $this->getStringParameter('qry', ''));
            $wheres[] = '(sac.sac_deleted_on IS NULL)';
            $wheres[] = '(sac.sac_ac_id = ' . $this->getIntParameter('ac_id', 0) . ')';
            $wheres[] = '(sac.sac_ss_id = ' . $this->User->getSsId() . ')';
            $wheres[] = '(sae.sae_deleted_on IS NULL)';
            $wheres[] = "(sae.sae_active = 'Y')";
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT sac.sac_id, sae.sae_id, sae.sae_description
                    FROM system_action as sac INNER JOIN 
                    system_action_event as sae ON sac.sac_id = sae.sae_sac_id ' . $strWhere;
            $query .= ' ORDER BY sae.sae_description, sae.sae_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResult = DB::select($query);
            $data = DataParser::arrayObjectToArray($sqlResult);
            $results = $this->doPrepareSingleSelectData($data, 'sae_id', 'sae_description');
        }
        return $results;
    }

    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function loadRelation(): array
    {
        $wheres = [];
        $strOrWheres = StringFormatter::generateOrLikeQuery($this->getStringParameter('qry', ''), ['rel_name', 'rel_short_name']);
        if (empty($strOrWheres) === false) {
            $wheres[] = $strOrWheres;
        }
        $wheres[] = '(rel_deleted_on IS NULL)';
        $wheres[] = '(rel_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = "(rel_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT rel_id, rel_name, rel_short_name
                FROM relation ' . $strWhere;
        $query .= ' ORDER BY rel_name, rel_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArrayAPI($sqlResult);
        }

        return $result;
    }

    /**
     * Function to load Warehouse data
     *
     * @return array
     */
    private function loadWarehouseData(): array
    {
        $wheres = [];
        $wheres[] = '(wh.wh_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(wh.wh_deleted_on IS NULL)';
        $wheres[] = "(wh.wh_active = 'Y')";
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(wh.wh_id IN (SELECT ji.ji_wh_id
                                            FROM job_order as jo INNER JOIN
                                                 job_inbound as ji ON jo.jo_id = ji.ji_jo_id
                                            WHERE (jo.jo_rel_id = ' . $this->User->getRelId() . ')
                                            GROUP BY ji.ji_wh_id))';
        }
        $data = WarehouseDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $temp = [
                'country' => $row['wh_country'],
                'state' => $row['wh_state'],
                'city' => $row['wh_city'],
                'district' => $row['wh_district'],
                'address' => $row['wh_address'],
                'postalCode' => $row['wh_postal_code'],
                'managedBy' => $row['ss_relation'],
                'office' => $row['wh_office'],
                'name' => $row['wh_name'],
                'id' => $row['wh_id'],

            ];
            $fullAddress = DataParser::doFormatAddress($temp);
            $temp['fullAddress'] = $fullAddress;
            $results[] = DataParser::doFormatApiData($temp);
        }

        return $results;
    }

    /**
     * Function to load relation goods data
     *
     * @return array
     */
    private function loadRelationGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->isValidParameter('qry') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('rel.rel_name', $this->getStringParameter('qry'));
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT rel.rel_id, rel.rel_name
                FROM relation as rel INNER JOIN 
                goods as gd ON rel.rel_id = gd.gd_rel_id ' . $strWhere;
        $query .= ' group BY rel.rel_id, rel.rel_name';
        $query .= ' ORDER BY rel.rel_name, rel.rel_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResult = DB::select($query);
        $data = DataParser::arrayObjectToArray($sqlResult);
        return $this->doPrepareSingleSelectData($data, 'rel_id', 'rel_name');
    }

    /**
     * Function to load goods data
     *
     * @return array
     */
    private function loadGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        if ($this->isValidParameter('qry') === true) {
            $strOrWheres = StringFormatter::generateOrLikeQuery($this->getStringParameter('qry', ''), [
                'gdc.gdc_name', 'br.br_name', 'gd.gd_name', 'gd.gd_sku'
            ]);
            if (empty($strOrWheres) === false) {
                $wheres[] = $strOrWheres;
            }
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(g.gd_rel_id = ' . $this->User->getRelId() . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT gd.gd_id, gd.gd_name, gd.gd_sku, gdc.gdc_name, br.br_name
                FROM goods as gd INNER JOIN
                    brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                    goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id ' . $strWhere;
        $query .= ' ORDER BY gdc.gdc_name, br.br_name, gd.gd_name, gd.gd_sku, gd.gd_id';
        $query .= ' LIMIT 30 OFFSET 0';
        $sqlResults = DB::select($query);
        $temp = DataParser::arrayObjectToArray($sqlResults);
        $data = [];
        $gdDao = new GoodsDao();
        foreach ($temp as $row) {
            $row['gd_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name'], $row['gd_sku']);
            $data[] = $row;
        }
        return $this->doPrepareSingleSelectData($data, 'gd_id', 'gd_name');
    }
}
