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

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle Stock Card.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class StockMovement extends JobOrder
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadJobData') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
        } elseif ($this->ActionName === 'loadGoodsData') {
            $this->Validation->checkRequire('jm_id');
            $this->Validation->checkInt('jm_id');
        } elseif ($this->ActionName === 'doStart') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'doComplete') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'loadListJid') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkInt('jid_whs_id');
        } elseif ($this->ActionName === 'loadJidByModel') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jid_gd_barcode');
        } elseif ($this->ActionName === 'loadJidByPn') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jid_packing_number');
        } elseif ($this->ActionName === 'loadJidBySn') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jid_serial_number');
        } elseif ($this->ActionName === 'loadJidStock') {
            $this->Validation->checkRequire('jid_id');
        } elseif ($this->ActionName === 'doUpdateDetail') {
            $this->Validation->checkRequire('jmd_jm_id');
            $this->Validation->checkRequire('jmd_gdu_id');
            $this->Validation->checkRequire('jmd_quantity');
            if ($this->isValidParameter('jmd_jid_id') === false) {
                $this->Validation->checkRequire('jmd_jid_whs_id');
                $this->Validation->checkRequire('jmd_jid_gd_id');
                $this->Validation->checkRequire('jmd_jid_packing_number');
            }

            if ($this->isValidParameter('jmd_jid_stock') === true) {
                $this->Validation->checkFloat('jmd_quantity');
            }
            if ($this->isValidParameter('jmd_gdt_id') === true) {
                $this->Validation->checkRequire('jmd_gcd_id');
            }
            if ($this->isValidParameter('jmd_gcd_id') === true) {
                $this->Validation->checkRequire('jmd_gdt_id');
            }
            if ($this->isValidParameter('jmd_length') === true) {
                $this->Validation->checkFloat('jmd_length');
            }
            if ($this->isValidParameter('jmd_width') === true) {
                $this->Validation->checkFloat('jmd_width');
            }
            if ($this->isValidParameter('jmd_height') === true) {
                $this->Validation->checkFloat('jmd_height');
            }
            if ($this->isValidParameter('jmd_weight') === true) {
                $this->Validation->checkFloat('jmd_weight');
            }
        } elseif ($this->ActionName === 'doDeleteDetail') {
            $this->Validation->checkRequire('jmd_id');
            if ($this->isValidParameter('jmd_jid_packing_number') === true) {
                $this->Validation->checkRequire('jmd_jm_id');
                $this->Validation->checkRequire('jmd_jid_gd_id');
            }
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {

        if ($this->ActionName === 'loadJobData') {
            $data = $this->loadJobData();
            $warning = '';
            if (empty($data) === false) {
                $this->doPrepareStatusJobData($data);
                $this->doPrepareNextJobActionData($data);
                if (empty($data['jm_start_on']) === false && empty($data['jm_complete_on']) === true) {
                    $warning = $this->doValidateCompleteMovement($data['jm_id']);
                }
            }
            $this->addResultData('jobMovement', $data);
            $this->addResultData('actionWarning', $warning);
        } elseif ($this->ActionName === 'loadGoodsData') {
            $wheres = [];
            $wheres[] = '(jmd.jmd_jm_id = ' . $this->getIntParameter('jm_id') . ')';
            $data = $this->loadJobGoodsMovement($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            $this->addResultData('goods', $data);
        } elseif ($this->ActionName === 'doStart') {
            $data = $this->doStartMovement();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'doComplete') {
            $data = $this->doCompleteMovement();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'loadListJid') {
            $wheres = [];
            $wheres[] = "((gd.gd_sn = 'N') OR (gd.gd_sn IS NULL))";
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
            $data = $this->loadListJid($wheres);
            $this->addResultData('jidS', $data);
        } elseif ($this->ActionName === 'loadJidByModel') {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('gd.gd_barcode', $this->getStringParameter('jid_gd_barcode'));
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
            $data = $this->loadListJid($wheres, 1);
            $gdId = '';
            $goods = [];
            $jidS = [];
            if (count($data) === 1) {
                $row = $data[0];
                $gdId = $row['jid_gd_id'];
                $goods = [
                    'jid_gd_id' => $row['jid_gd_id'],
                    'jid_gd_sku' => $row['jid_gd_sku'],
                    'jid_gd_barcode' => $row['jid_gd_barcode'],
                    'jid_gd_name' => $row['jid_gd_name'],
                    'jid_gd_brand' => $row['jid_gd_brand'],
                    'jid_gd_category' => $row['jid_gd_category'],
                    'jid_gd_sn' => $row['jid_gd_sn'],
                    'jid_gd_generate_sn' => $row['jid_gd_generate_sn'],
                    'jid_gd_receive_sn' => $row['jid_gd_receive_sn'],
                    'jid_gd_multi_sn' => $row['jid_gd_multi_sn'],
                    'jid_gd_packing' => $row['jid_gd_packing'],
                    'jid_gd_expired' => $row['jid_gd_expired'],
                    'jid_gd_tonnage' => $row['jid_gd_tonnage'],
                    'jid_gd_tonnage_dm' => $row['jid_gd_tonnage_dm'],
                    'jid_gd_min_tonnage' => $row['jid_gd_min_tonnage'],
                    'jid_gd_max_tonnage' => $row['jid_gd_max_tonnage'],
                    'jid_gd_cbm' => $row['jid_gd_cbm'],
                    'jid_gd_cbm_dm' => $row['jid_gd_cbm_dm'],
                    'jid_gd_min_cbm' => $row['jid_gd_min_cbm'],
                    'jid_gd_max_cbm' => $row['jid_gd_max_cbm'],
                ];
                if ($row['jid_gd_sn'] !== 'Y') {
                    $jidS = $this->loadListJid($wheres);
                }
            }
            $this->addResultData('gd_id', $gdId);
            $this->addResultData('goods', $goods);
            $this->addResultData('jidS', $jidS);
        } elseif ($this->ActionName === 'loadJidByPn') {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('jid.jid_packing_number', $this->getStringParameter('jid_packing_number'));
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
            if ($this->getIntParameter('jid_gd_id', 0) !== 0) {
                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
            }
            $data = $this->loadListJid($wheres);
            $jidS = [];
            if (empty($data) === false) {
                $jid = [];
                $number = new NumberFormatter($this->User);
                foreach ($data as $row) {
                    if (empty($jid) === true) {
                        $row['jid_id'] = '';
                        $row['jid_serial_number'] = '';
                        $jid = $row;
                    } else {
                        $stock = (float)$jid['jid_stock'] + (float)$row['jid_stock'];
                        $jid['jid_stock'] = $stock;
                        $jid['jid_stock_str'] = $number->doFormatFloat($stock);
                    }
                }
                $jidS[] = $jid;
            }
            $this->addResultData('jidS', $jidS);
        } elseif ($this->ActionName === 'loadJidBySn') {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jid.jid_serial_number', $this->getStringParameter('jid_serial_number'));
            $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jid_whs_id') . ')';
            if ($this->getIntParameter('jid_gd_id', 0) !== 0) {
                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jid_gd_id') . ')';
            }
            $data = $this->loadListJid($wheres);
//            $jidS = [];
//            foreach ($data as $row) {
//                $jidS[] = $row;
//                $jidS[] = $row;
//            }
//            $this->addResultData('jidS', $jidS);
            $this->addResultData('jidS', $data);
//        } elseif ($this->ActionName === 'loadJidStock') {
//            $wheres = [];
//            $wheres[] = '(jid.jid_id = ' . $this->getIntParameter('jid_id') . ')';
//            $data = $this->loadListJid($wheres);
//            $jid = [];
//            if (count($data) === 1) {
//                $jid = $data[0];
//            }
//            $this->addResultData('jid', $jid);
        } elseif ($this->ActionName === 'doUpdateDetail') {
            $wheres = [];
            if ($this->isValidParameter('jmd_jid_id')) {
                $wheres[] = '(jid.jid_id = ' . $this->getIntParameter('jmd_jid_id') . ')';
            } else {
                $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('jmd_jid_whs_id') . ')';
                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jmd_jid_gd_id') . ')';
                $wheres[] = SqlHelper::generateStringCondition('jid.jid_packing_number', $this->getStringParameter('jmd_jid_packing_number'));
            }
            $data = $this->loadListJid($wheres, 0, 0, false);
            if (empty($data) === true) {
                Message::throwMessage("No data found!", 'WARNING');
            }
            $qtyMove = $this->getFloatParameter('jmd_quantity');
            $qtyStock = 0.0;
            foreach ($data as $row) {
                $qtyStock += (float)$row['jid_stock'];
            }
            if ($qtyMove > $qtyStock) {
                Message::throwMessage("Quantity movement must be lower or equals with " . $qtyStock, 'WARNING');
            }
            $jmdId = $this->doUpdateDetail($data);
            $this->addResultData('jmd_id', $jmdId);
        } elseif ($this->ActionName === 'doDeleteDetail') {
            $idS = [];
            if ($this->isValidParameter('jmd_jid_packing_number')) {
                $wheres = [];
                $wheres[] = '(jmd.jmd_jm_id = ' . $this->getIntParameter('jmd_jm_id') . ')';
                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('jmd_jid_gd_id') . ')';
                $wheres[] = "(jid.jid_packing_number = '" . $this->getStringParameter('jmd_jid_packing_number') . "')";
                $data = $this->loadJobGoodsMovement($wheres);
                foreach ($data as $row) {
                    $idS[] = $row['jmd_id'];
                }
            } else {
                $idS[] = $this->getIntParameter('jmd_id');
            }
            $this->doDeleteDetail($idS);
            $this->addResultData('jmd_id', $this->getIntParameter('jmd_id'));
//        } elseif ($this->ActionName === 'verifyScanModel') {
//            $gdId = '';
//            if ($this->isValidParameter('whs_id') && $this->isValidParameter('model')) {
//                $gdId = $this->loadJidByGoodBarcode();
//            }
//            $this->addResultData('gd_id', $gdId);
//        } elseif ($this->ActionName === 'verifyScanSn') {
//            $jidId = '';
//            $jid = [];
//            if ($this->isValidParameter('whs_id') && $this->isValidParameter('gd_id') && $this->isValidParameter('serial_number')) {
//                $wheres = [];
//                $wheres[] = "(gd.gd_sn = 'Y')";
//                $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('whs_id') . ')';
//                $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
//                $wheres[] = SqlHelper::generateLikeCondition('jid.jid_serial_number', $this->getStringParameter('serial_number'));
//                $data = $this->loadListJid($wheres);
//                if (count($data) === 1) {
//                    $jid = $data[0];
//                    $jidId = $jid['jid_id'];
//                }
//            }
//            $this->addResultData('jid_id', $jidId);
//            $this->addResultData('jid', $jid);
        }
    }

//
//    /**
//     * Function to load total number of draft project.
//     *
//     * @return string
//     */
//    private function loadJidByGoodBarcode(): string
//    {
//        $results = '';
//        $wheres = [];
//        $wheres[] = "(gd.gd_sn = 'Y')";
//        $wheres[] = '(jid.jid_whs_id = ' . $this->getIntParameter('whs_id') . ')';
//        $wheres[] = SqlHelper::generateLikeCondition('gd.gd_barcode', $this->getStringParameter('model'));
//        $wheres[] = '(jo.jo_deleted_on IS NULL)';
//        $wheres[] = '(jis.stock > 0)';
//        $wheres[] = '(jid.jid_deleted_on IS NULL)';
//        $query = 'SELECT jid.jid_gd_id, jis.stock as jid_stock, j.used as jid_used
//                    FROM job_inbound_detail as jid INNER JOIN
//                        job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
//                        job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
//                        goods as gd ON gd.gd_id = jid.jid_gd_id INNER JOIN
//                        (SELECT jis_jid_id, SUM(jis_quantity) as stock
//                            FROM job_inbound_stock
//                            WHERE (jis_deleted_on IS NULL)
//                            GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN
//                        (SELECT jmd.jmd_jid_id, SUM(jmd.jmd_quantity) as used
//                        FROM job_movement_detail as jmd INNER JOIN
//                            job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
//                            job_order as jo2 ON jo2.jo_id = jm.jm_jo_id
//                        WHERE (jo2.jo_deleted_on IS NULL) AND (jmd.jmd_deleted_on IS NULL)
//                        AND (jmd.jmd_jid_new_id IS NULL) AND (jmd.jmd_id <> ' . $this->getIntParameter('jmd_id', 0) . ')
//                        GROUP BY jmd.jmd_jid_id) as j ON jid.jid_id = j.jmd_jid_id ';
//        $query .= ' WHERE ' . implode(' AND ', $wheres);
//        $query .= ' ORDER BY jid.jid_gd_id';
//        $sqlResults = DB::select($query);
//        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
//        foreach ($data as $row) {
//            $stock = (float)$row['jid_stock'] - (float)$row['jid_used'];
//            if ($stock > 0) {
//                $results = $row['jid_gd_id'];
//            }
//        }
//        return $results;
//    }


    /**
     * Function to get the goods load data.
     *
     * @param array $data To store the data.
     *
     * @return int
     */
    private function doUpdateDetail(array $data): int
    {
        $results = 0;
        DB::beginTransaction();
        try {
            $qtyPlan = $this->getFloatParameter('jmd_quantity');
            $volume = null;
            if (($this->isValidParameter('jmd_length') === true) && ($this->isValidParameter('jmd_width') === true) && ($this->isValidParameter('jmd_height') === true)) {
                $volume = $this->getFloatParameter('jmd_length') * $this->getFloatParameter('jmd_width') * $this->getFloatParameter('jmd_height');
            }
            $jmdDao = new JobMovementDetailDao();
            foreach ($data as $row) {
                if ($qtyPlan > 0.0) {
                    $qty = (float)$row['jid_stock'];
                    if ($qty >= $qtyPlan) {
                        $qty = $qtyPlan;
                    }
                    $jmdColVal = [
                        'jmd_jm_id' => $this->getIntParameter('jmd_jm_id'),
                        'jmd_jid_id' => $row['jid_id'],
                        'jmd_gdu_id' => $this->getIntParameter('jmd_gdu_id'),
                        'jmd_quantity' => $qty,
                        'jmd_length' => $this->getFloatParameter('jmd_length'),
                        'jmd_width' => $this->getFloatParameter('jmd_width'),
                        'jmd_height' => $this->getFloatParameter('jmd_height'),
                        'jmd_volume' => $volume,
                        'jmd_weight' => $this->getFloatParameter('jmd_weight'),
                    ];
                    if ($this->getStringParameter('jmd_swt_condition', 'N') === 'Y') {
                        $jmdColVal['jmd_gdt_id'] = $this->getIntParameter('jmd_gdt_id');
                        $jmdColVal['jmd_gdt_remark'] = $this->getStringParameter('jmd_gdt_remark');
                        $jmdColVal['jmd_gcd_id'] = $this->getIntParameter('jmd_gcd_id');
                        $jmdColVal['jmd_gcd_remark'] = $this->getStringParameter('jmd_gcd_remark');
                    } else {
                        $jmdColVal['jmd_gdt_id'] = $row['jid_gdt_id'];
                        $jmdColVal['jmd_gdt_remark'] = $row['jid_gdt_remark'];
                        $jmdColVal['jmd_gcd_id'] = $row['jid_gcd_id'];
                        $jmdColVal['jmd_gcd_remark'] = $row['jid_gcd_remark'];
                    }
                    $qtyPlan -= $qty;
                    $jmdDao->doApiInsertTransaction($jmdColVal, $this->User->getId());
                }

            }
            $results = $jmdDao->getLastInsertId();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
            $this->setErrorMessage($e->getMessage());
        }
        return $results;
    }

    /**
     * Function to get the goods load data.
     *
     * @param array $idS To store deleted idS
     *
     * @return void
     */
    private function doDeleteDetail(array $idS): void
    {
        if (empty($idS) === false) {
            DB::beginTransaction();
            try {
                $jmdDao = new JobMovementDetailDao();
                foreach ($idS as $id) {
                    $jmdDao->doApiDeleteTransaction($id, $this->User->getId());
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->setErrorCode('500');
            }
        }
    }

    /**
     * Function to load total number of draft project.
     *
     * @param array $wheres    To store the where condition.
     * @param int   $limit     To store the limit result.
     * @param int   $offset    To store the offset result.
     * @param bool  $apiFormat To store the offset result.
     *
     * @return array
     */
    private function loadListJid(array $wheres = [], int $limit = 0, int $offset = 0, bool $apiFormat = true): array
    {
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jis.stock IS NOT NULL)';
        $wheres[] = '(jis.stock > 0)';
        $wheres[] = '((jis.stock > j.used) OR (j.used IS NULL))';
        $query = 'SELECT jid.jid_id, jid.jid_gd_id, gd.gd_sku as jid_gd_sku, gd.gd_barcode as jid_gd_barcode, gd.gd_name as jid_gd_name, gdc.gdc_name as jid_gd_category,
                        br.br_name as jid_gd_brand, gd.gd_sn as jid_gd_sn, gd.gd_generate_sn as jid_gd_generate_sn, 
                        gd.gd_receive_sn as jid_gd_receive_sn, gd.gd_multi_sn as jid_gd_multi_sn, gd.gd_packing as jid_gd_packing,
                        gd.gd_expired as jid_gd_expired, gd.gd_tonnage as jid_gd_tonnage, gd.gd_tonnage_dm as jid_gd_tonnage_dm,
                        gd.gd_min_tonnage as jid_gd_min_tonnage, gd.gd_max_tonnage as jid_gd_max_tonnage, gd.gd_cbm as jid_gd_cbm, 
                        gd.gd_cbm_dm as jid_gd_cbm_dm, gd.gd_min_cbm as jid_gd_min_cbm, gd.gd_max_cbm as jid_gd_max_cbm, 
                        jid.jid_lot_number, jid.jid_expired_date, jid.jid_packing_number, jid.jid_serial_number, jid.jid_gdt_id, 
                        gdt.gdt_code as jid_gdt_code, gdt.gdt_description as jid_gdt_description, jid.jid_gdu_id, 
                        uom.uom_code as jid_uom, jis.stock as jid_stock, j.used as jid_used,
                        jid.jid_gcd_id, gcd.gcd_code as jid_gcd_code, gcd.gcd_description as jid_gcd_description,
                        jid.jid_gdt_remark, jid.jid_gcd_remark
                    FROM job_inbound_detail as jid INNER JOIN
                        job_inbound as ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                        job_order as jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                        goods as gd ON gd.gd_id = jid.jid_gd_id INNER JOIN
                        brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                        goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                        goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id INNER JOIN
                        unit as uom ON uom.uom_id = gdu.gdu_uom_id INNER JOIN
                        (SELECT jis_jid_id, SUM(jis_quantity) as stock
                            FROM job_inbound_stock 
                            WHERE (jis_deleted_on IS NULL)
                            GROUP BY jis_jid_id) as jis ON jid.jid_id = jis.jis_jid_id LEFT OUTER JOIN
                        goods_damage_type as gdt ON jid.jid_gdt_id = gdt.gdt_id LEFT OUTER JOIN
                        goods_cause_damage as gcd ON jid.jid_gcd_id = gcd.gcd_id LEFT OUTER JOIN
                        (SELECT jmd.jmd_jid_id, SUM(jmd.jmd_quantity) as used
                        FROM job_movement_detail as jmd INNER JOIN
                            job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                            job_order as jo2 ON jo2.jo_id = jm.jm_jo_id
                        WHERE (jo2.jo_deleted_on IS NULL) AND (jmd.jmd_deleted_on IS NULL)
                        AND (jmd.jmd_jid_new_id IS NULL) AND (jmd.jmd_id <> ' . $this->getIntParameter('jmd_id', 0) . ')
                        GROUP BY jmd.jmd_jid_id) as j ON jid.jid_id = j.jmd_jid_id ';
        $query .= ' WHERE ' . implode(' AND ', $wheres);
        $query .= ' ORDER BY jid.jid_lot_number, gd.gd_sku, jid.jid_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        if ($apiFormat) {
            $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        } else {
            $data = DataParser::arrayObjectToArray($sqlResults);
        }
        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $stock = (float)$row['jid_stock'] - (float)$row['jid_used'];
            if ($stock > 0) {
                $row['jid_stock_str'] = $number->doFormatFloat($stock);
                $results[] = $row;
            }
        }
        return $results;
    }

    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doCompleteMovement(): array
    {
        DB::beginTransaction();
        try {
            $wheres = [];
            $wheres[] = '(jm.jm_id = ' . $this->getIntParameter('jwId') . ')';
            $wheres[] = '(jo.jo_deleted_on IS NULL)';
            $wheres[] = '(jm.jm_complete_on IS NULL)';
            $temp = JobMovementDao::loadSimpleDataData($wheres);
            if (empty($temp) === false) {
                $data = JobMovementDetailDao::loadDataByJmId($this->getIntParameter('jwId'));
                $jidDao = new JobInboundDetailDao();
                $jisDao = new JobInboundStockDao();
                $jmdDao = new JobMovementDetailDao();
                foreach ($data as $row) {
                    $jmdColVal = [];
                    # Decrease the stock for origin storage.
                    $jisColVal = [
                        'jis_jid_id' => $row['jmd_jid_id'],
                        'jis_quantity' => (float)$row['jmd_quantity'] * -1,
                    ];
                    $jisDao->doApiInsertTransaction($jisColVal, $this->User->getId());
                    $jmdColVal['jmd_jis_id'] = $jisDao->getLastInsertId();

                    # insert new job outbound detail for destination moved.
                    $jidColVal = [
                        'jid_ji_id' => $row['jmd_jid_ji_id'],
                        'jid_jir_id' => $row['jmd_jid_jir_id'],
                        'jid_whs_id' => $row['jmd_whs_id'],
                        'jid_quantity' => (float)$row['jmd_quantity'],
                        'jid_gd_id' => $row['jmd_gd_id'],
                        'jid_gdu_id' => $row['jmd_gdu_id'],
                        'jid_lot_number' => $row['jmd_jid_lot_number'],
                        'jid_serial_number' => $row['jmd_jid_serial_number'],
                        'jid_packing_number' => $row['jmd_jid_packing_number'],
                        'jid_expired_date' => $row['jmd_jid_expired_date'],
                        'jid_length' => $row['jmd_length'],
                        'jid_width' => $row['jmd_width'],
                        'jid_height' => $row['jmd_height'],
                        'jid_volume' => $row['jmd_volume'],
                        'jid_weight' => $row['jmd_weight'],
                        'jid_adjustment' => 'Y',
                        'jid_gdt_id' => $row['jmd_gdt_id'],
                        'jid_gdt_remark' => $row['jmd_gdt_remark'],
                        'jid_gcd_id' => $row['jmd_gcd_id'],
                        'jid_gcd_remark' => $row['jmd_gcd_remark'],
                    ];
                    $jidDao->doApiInsertTransaction($jidColVal, $this->User->getId());
                    $jmdColVal['jmd_jid_new_id'] = $jidDao->getLastInsertId();

                    # insert the job inbound stock for destination JID
                    $jisColVal = [
                        'jis_jid_id' => $jidDao->getLastInsertId(),
                        'jis_quantity' => (float)$row['jmd_quantity'],
                    ];
                    $jisDao->doApiInsertTransaction($jisColVal, $this->User->getId());
                    $jmdColVal['jmd_jis_new_id'] = $jisDao->getLastInsertId();

                    # Update current job movement detail.
                    $jmdDao->doApiUpdateTransaction($row['jmd_id'], $jmdColVal, $this->User->getId());
                }
                $jmColVal = [
                    'jm_complete_on' => date('Y-m-d H:i:s'),
                ];
                $jmDao = new JobMovementDao();
                $jmDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $jmColVal, $this->User->getId());

                # Update job action
                $jaeColVal = $this->doUpdateJobActionEvent(2);
                DB::commit();
                $results = $jaeColVal;
            } else {
                DB::rollBack();
                $results = [];
                $this->setErrorCode('403');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }


    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doStartMovement(): array
    {
        DB::beginTransaction();
        try {
            # Update actual time arrival job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), $joColVal, $this->User->getId());

            $jaeColVal = $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }


    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    private function loadJobData(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_id = ' . $this->getIntParameter('jo_id') . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
        $data = JobMovementDao::loadApiData($wheres, $this->User->getId());
        $result = [];
        if (count($data) === 1) {
            $result = $data[0];
            $time = '';
            if (empty($result['jm_date']) === false) {
                if (empty($result['jm_time']) === false) {
                    $time = DateTimeParser::format($result['jm_date'] . ' ' . $result['jm_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $time = DateTimeParser::format($result['jm_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['jm_time'] = $time;
            $startOn = '';
            if (empty($result['jo_start_on']) === false) {
                $startOn = DateTimeParser::format($result['jo_start_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            }
            $result['jm_start_on'] = $startOn;
            $completeOn = '';
            if (empty($result['jm_complete_on']) === false) {
                $completeOn = DateTimeParser::format($result['jm_complete_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            }
            $result['jm_complete_on'] = $completeOn;
        }

        return DataParser::doFormatApiData($result);
    }

    /**
     * Function to stock movement goods.
     *
     * @param array $wheres      To store the id of job movement.
     * @param int   $limit       To store the limit load.
     * @param int   $offset      To store the offset load.
     * @param bool  $isApiFormat To switch the data parser.
     *
     * @return array
     */
    private function loadJobGoodsMovement(array $wheres = [], int $limit = 0, int $offset = 0, bool $isApiFormat = true): array
    {
        $wheres[] = '(jmd.jmd_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jmd.jmd_id, jmd.jmd_jm_id, jmd.jmd_jid_id, jid.jid_ji_id as jmd_ji_id, 
                    jid.jid_jir_id as jmd_jid_jir_id, jid.jid_whs_id as jmd_jid_whs_id, jid.jid_quantity as jmd_jid_quantity,
                    jid.jid_gdt_id as jmd_jid_gdt_id, gdt1.gdt_code as jmd_jid_gdt_code, gdt1.gdt_description as jmd_jid_gdt_description,
                    jid.jid_gdt_remark as jmd_jid_gdt_remark, jid.jid_gcd_id as jmd_jid_gcd_id, gcd1.gcd_code as jmd_jid_gcd_code, 
                    gcd1.gcd_description as jmd_jid_gcd_description, jid.jid_gcd_remark as jmd_jid_gcd_remark, jid.jid_length as jmd_jid_length,
                    jid.jid_height as jmd_jid_height, jid.jid_width as jmd_jid_width, jid.jid_volume as jmd_jid_volume, jid.jid_weight as jmd_jid_weight,
                    jid.jid_serial_number as jmd_jid_serial_number, jid.jid_lot_number as jmd_jid_lot_number, jid.jid_packing_number as jmd_jid_packing_number, 
                    jid.jid_expired_date as jmd_jid_expired_date, jid.jid_gdu_id as jmd_jid_gdu_id, uom.uom_code as jmd_jid_uom,
                    jid.jid_gd_id as jmd_gd_id, gd.gd_sku as jmd_gd_sku, gd.gd_barcode as jmd_gd_barcode, gd.gd_name as jmd_gd_name, 
                    gd.gd_sn as jmd_gd_sn, gd.gd_generate_sn as jmd_gd_generate_sn, gd.gd_receive_sn as jmd_gd_receive_sn, gd.gd_multi_sn as jmd_gd_multi_sn,
                    gd.gd_packing as jmd_gd_packing, gd.gd_expired as jmd_gd_expired, gd.gd_tonnage as jmd_gd_tonnage, gd.gd_tonnage_dm as jmd_gd_tonnage_dm, 
                    gd.gd_min_tonnage as jmd_gd_min_tonnage, gd.gd_max_tonnage as jmd_gd_max_tonnage, gd.gd_cbm as jmd_gd_cbm, gd.gd_cbm_dm as jmd_gd_cbm_dm,
                    gd.gd_min_cbm as jmd_gd_min_cbm, gd.gd_max_cbm as jmd_gd_max_cbm, br.br_name as jmd_gd_brand, gdc.gdc_name as jmd_gd_category, 
                    jmd.jmd_quantity, jmd.jmd_gdt_id, gdt2.gdt_code as jmd_gdt_code, 
                    gdt2.gdt_description as jmd_gdt_description, jmd.jmd_gdt_remark, jmd.jmd_gcd_id, gcd2.gcd_code as jmd_gcd_code, 
                    gcd2.gcd_description as jmd_gcd_description, jmd.jmd_gcd_remark, jmd.jmd_length, jmd.jmd_height, jmd.jmd_width, jmd.jmd_weight, jmd.jmd_volume
                FROM job_movement_detail as jmd
                    INNER JOIN job_inbound_detail as jid ON jmd.jmd_jid_id = jid.jid_id
                    INNER JOIN goods as gd ON gd.gd_id = jid.jid_gd_id
                    INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                    INNER JOIN goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id
                    INNER JOIN goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id
                    INNER JOIN unit as uom ON gdu.gdu_uom_id = uom.uom_id
                    LEFT OUTER JOIN goods_damage_type as gdt1 ON jid.jid_gdt_id = gdt1.gdt_id
                    LEFT OUTER JOIN goods_cause_damage as gcd1 ON jid.jid_gcd_id = gcd1.gcd_id
                    LEFT OUTER JOIN goods_damage_type as gdt2 ON jmd.jmd_gdt_id = gdt2.gdt_id
                    LEFT OUTER JOIN goods_cause_damage as gcd2 ON jmd.jmd_gcd_id = gcd2.gcd_id ' . $strWheres;
        $query .= ' ORDER BY gd.gd_sku, jmd.jmd_id';
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        if ($isApiFormat) {
            $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        } else {
            $data = DataParser::arrayObjectToArray($sqlResults);
        }
        $result = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $qty = (float)$row['jmd_quantity'];
            $row['jmd_quantity_str'] = $number->doFormatFloat($qty);
            $weight = (float)$row['jmd_weight'];
            if ($weight <= 0.0) {
                $weight = (float)$row['jmd_jid_weight'];
            }
            $totalWeight = $qty * $weight;
            $row['jmd_total_weight'] = $number->doFormatFloat($totalWeight);

            $volume = (float)$row['jmd_volume'];
            if ($volume <= 0.0) {
                $volume = (float)$row['jmd_jid_volume'];
            }
            $totalVolume = $qty * $volume;
            $row['jmd_total_volume'] = $number->doFormatFloat($totalVolume);

            $result[] = $row;
        }
        return $result;
    }


    /**
     * Function to stock movement goods.
     *
     * @param int $jmId To store the job movment id.
     *
     * @return string
     */
    private function doValidateCompleteMovement($jmId): string
    {
        $warning = '';
        $isExistData = JobMovementDetailDao::isExistData($jmId);
        if ($isExistData === false) {
            $warning = Trans::getWord('movementGoodsValidation', 'message');
        }
        return $warning;
    }
}
