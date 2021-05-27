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
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
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
class StockOpname extends JobOrder
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
            $this->Validation->checkRequire('sop_id');
            $this->Validation->checkInt('sop_id');
        } elseif ($this->ActionName === 'doStart') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'doEnd') {
            $this->loadJobActionValidation();
        } elseif ($this->ActionName === 'loadStockOpnameDetail') {
            $this->Validation->checkRequire('sop_id');
            $this->Validation->checkInt('sop_id');
        } elseif ($this->ActionName === 'doUpdateOpnameDetail') {
            $this->Validation->checkRequire('sod_qty_figure');
            $this->Validation->checkFloat('sod_qty_figure', 0.1);
            $this->Validation->checkMaxLength('sod_remark', 255);
            $this->Validation->checkMaxLength('sod_serial_number', 255);
            $this->Validation->checkMaxLength('sod_production_number', 255);
            if ($this->isValidParameter('sod_id') === false) {
                $this->Validation->checkRequire('sod_sop_id');
                $this->Validation->checkInt('sod_sop_id');
                $this->Validation->checkRequire('sod_gd_id');
                $this->Validation->checkInt('sod_gd_id');
                $this->Validation->checkRequire('sod_whs_id');
                $this->Validation->checkInt('sod_whs_id');
                $this->Validation->checkRequire('sod_gdu_id');
                $this->Validation->checkInt('sod_gdu_id');
                $this->Validation->checkUnique('sod_gd_id', 'stock_opname_detail', [
                    'sod_id' => $this->getIntParameter('sod_id')
                ], [
                    'sod_sop_id' => $this->getIntParameter('sod_sop_id'),
                    'sod_gd_id' => $this->getIntParameter('sod_gd_id'),
                    'sod_whs_id' => $this->getIntParameter('sod_whs_id'),
                    'sod_gdt_id' => $this->getIntParameter('sod_gdt_id'),
                    'sod_gdu_id' => $this->getIntParameter('sod_gdu_id'),
                    'sod_production_number' => $this->getStringParameter('sod_production_number'),
                    'sod_serial_number' => $this->getStringParameter('sod_serial_number'),
                    'sod_deleted_on' => null,
                ]);
            }
        } elseif ($this->ActionName === 'doDeleteOpnameDetail') {
            $this->Validation->checkRequire('sod_id');
            $this->Validation->checkInt('sod_id');
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
            $this->doPrepareStatusJobData($data);
            $this->doPrepareNextJobActionData($data);
            $warning = '';
            if (empty($data['sop_start_on']) === false && empty($data['sop_complete_on']) === true) {
                $warning = $this->validateCompleteSop($data['sop_id']);
            }
            $this->addResultData('stockOpname', $data);
            $this->addResultData('actionWarning', $warning);
        } elseif ($this->ActionName === 'loadGoodsData') {
            $data = $this->loadJobGoodsData();
            $this->addResultData('goods', $data);
        } elseif ($this->ActionName === 'doStart') {
            $data = $this->doStart();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'doEnd') {
            $data = $this->doComplete();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'loadStockOpnameDetail') {
            $wheres = [];
            $wheres[] = '(sod.sod_sop_id = ' . $this->getIntParameter('sop_id') . ')';
            $data = $this->loadStockOpnameDetail($wheres);
            $this->addResultData('sod_list', $data);
        } elseif ($this->ActionName === 'doUpdateOpnameDetail') {
            if ($this->isValidParameter('sod_id') === false) {
                $sodId = $this->doInsertOpnameDetail();
                $this->addResultData('sod_id', $sodId);
            } else {
                $this->doUpdateOpnameDetail();
                $this->addResultData('sod_id', $this->getIntParameter('sod_id'));
            }
        } elseif ($this->ActionName === 'doDeleteOpnameDetail') {
            $this->doDeleteOpnameDetail();
            $this->addResultData('sod_id', $this->getIntParameter('sod_id'));
        } elseif ($this->ActionName === 'verifyScanStorage') {
            $storageId = '';
            if ($this->isValidParameter('wh_id') && $this->isValidParameter('storage')) {
                $storageId = $this->doVerifyStorage();
            }
            $this->addResultData('whs_id', $storageId);
        } elseif ($this->ActionName === 'verifyScanModel') {
            $goods = [];
            if ($this->isValidParameter('gd_barcode') === true && $this->isValidParameter('sod_sop_id') === true && $this->isValidParameter('sod_whs_id') === true && $this->isValidParameter('gd_rel_id') === true) {
                $wheres = [];
                $wheres[] = "(LOWER(gd.gd_barcode) = '" . mb_strtolower($this->getStringParameter('gd_barcode')) . "')";
                $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
                $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
                $data = GoodsDao::loadData($wheres);
                if (\count($data) === 1) {
                    $goods = $data[0];
                }
            }
            if (empty($goods) === true) {
                $this->addResultData('gd_id', '');
            } else {
                $sods = [];
                if ($goods['gd_sn'] !== 'Y') {
                    $wheres = [];
                    $wheres[] = '(sod.sod_sop_id = ' . $this->getIntParameter('sod_sop_id') . ')';
                    $wheres[] = '(sod.sod_whs_id = ' . $this->getIntParameter('sod_whs_id') . ')';
                    $wheres[] = '(sod.sod_gd_id = ' . $goods['gd_id'] . ')';
                    $sods = $this->loadStockOpnameDetail($wheres);
                }
                $this->addResultData('gd_id', $goods['gd_id']);
                $this->addResultData('gd_sku', $goods['gd_sku']);
                $this->addResultData('gd_name', $goods['gd_name']);
                $this->addResultData('gd_sn', $goods['gd_sn']);
                $this->addResultData('sods', $sods);
            }
        } elseif ($this->ActionName === 'verifyScanSn') {
            $result = Trans::getWord('invalidSnInbound', 'message', '', ['sn' => $this->getStringParameter('sod_serial_number', '')]);
            $sod = [];
            $sodId = '';
            if ($this->isValidParameter('sod_gd_id') && $this->isValidParameter('sod_sop_id') && $this->isValidParameter('sod_whs_id') && $this->isValidParameter('sod_serial_number') && StringFormatter::isContainSpecialCharacter($this->getStringParameter('sod_serial_number')) == false) {
                $sn = $this->getStringParameter('sod_serial_number');
                if ($this->isValidSnPrefix()) {
                    $result = '';
                    $wheres = [];
                    $wheres[] = '(sod.sod_sop_id = ' . $this->getIntParameter('sod_sop_id') . ')';
                    $wheres[] = '(sod.sod_whs_id = ' . $this->getIntParameter('sod_whs_id') . ')';
                    $wheres[] = '(sod.sod_gd_id = ' . $this->getIntParameter('sod_gd_id') . ')';
                    $wheres[] = "(LOWER(sod.sod_serial_number) = '" . mb_strtolower($sn) . "')";
                    $data = $this->loadStockOpnameDetail($wheres);
                    if (\count($data) === 1) {
                        $sod = $data[0];
                        $sodId = $sod['sod_id'];
                    }
                } else {
                    if (strlen($sn) < 2) {
                        $prefix = mb_substr($sn, 0, 1);
                    } else {
                        $prefix = mb_substr($sn, 0, 2);
                    }
                    $result = Trans::getWord('invalidPrefixSnInbound', 'message', '', [
                        'prefix' => $prefix,
                        'sn' => $sn,
                    ]);
                }
            }
            $this->addResultData('sn_result', $result);
            $this->addResultData('sod', $sod);
            $this->addResultData('sod_id', $sodId);
        }
    }


    /**
     * Function to get the goods load data.
     *
     * @return void
     */
    private function doDeleteOpnameDetail(): void
    {
        DB::beginTransaction();
        try {
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doApiDeleteTransaction($this->getIntParameter('sod_id'), $this->User->getId());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to get the goods load data.
     *
     * @return string
     */
    private function doInsertOpnameDetail(): string
    {
        $result = '';
        DB::beginTransaction();
        try {
            $sodColVal = [
                'sod_sop_id' => $this->getIntParameter('sod_sop_id'),
                'sod_whs_id' => $this->getIntParameter('sod_whs_id'),
                'sod_gd_id' => $this->getIntParameter('sod_gd_id'),
                'sod_gdt_id' => $this->getIntParameter('sod_gdt_id'),
                'sod_gdu_id' => $this->getIntParameter('sod_gdu_id'),
                'sod_quantity' => 0,
                'sod_qty_figure' => $this->getFloatParameter('sod_qty_figure'),
                'sod_production_number' => $this->getStringParameter('sod_production_number'),
                'sod_serial_number' => $this->getStringParameter('sod_serial_number'),
                'sod_remark' => $this->getStringParameter('sod_remark'),
            ];
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doApiInsertTransaction($sodColVal, $this->User->getId());
            DB::commit();
            $result = $sodDao->getLastInsertId();
        } catch (\Exception $e) {
            DB::rollBack();
            $result = '';
            $this->setErrorCode('500');
        }
        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @return bool
     */
    private function isValidSnPrefix(): bool
    {
        $value = $this->getStringParameter('sod_serial_number');
        if (empty($value) === false || mb_strlen($value) >= 2) {
            $prefix = mb_substr($value, 0, 2);
            $wheres = [];
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('sod_gd_id') . ')';
            $wheres[] = "(gd.gd_sn = 'Y')";
            $wheres[] = "((gpf.gpf_prefix = '" . $prefix . "') OR (gd.gd_id NOT IN (SELECT gpf_gd_id FROM goods_prefix WHERE gpf_deleted_on IS NULL)))";
            $wheres[] = '(gpf.gpf_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'select gd.gd_id, gpf.gpf_id 
                    from goods as gd LEFT OUTER JOIN
                        goods_prefix as gpf on gd.gd_id = gpf.gpf_gd_id ' . $strWhere;
            $sqlResult = DB::select($query);

            return !empty($sqlResult);
        }
        return false;
    }


    /**
     * Function to get the goods load data.
     *
     * @return string
     */
    private function doVerifyStorage(): string
    {
        $result = '';
        $wheres = [];
        $wheres[] = '(whs_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        $wheres[] = "(LOWER(whs_name) = '" . mb_strtolower($this->getStringParameter('storage')) . "')";
        $wheres[] = '(whs_deleted_on IS NULL)';
        $wheres[] = "(whs_active = 'Y')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT whs_id
                FROM warehouse_storage ' . $strWheres;
        $query .= ' GROUP BY whs_id';
        $sqlResults = DB::select($query);
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0])['whs_id'];
        }

        return $result;
    }

    /**
     * Function to get the goods load data.
     *
     * @return void
     */
    private function doUpdateOpnameDetail(): void
    {
        DB::beginTransaction();
        try {
            $sodColVal = [
                'sod_qty_figure' => $this->getFloatParameter('sod_qty_figure'),
                'sod_remark' => $this->getStringParameter('sod_remark')
            ];
            $sodDao = new StockOpnameDetailDao();
            $sodDao->doApiUpdateTransaction($this->getIntParameter('sod_id'), $sodColVal, $this->User->getId());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to stock opname goods.
     *
     * @param array $wheres To store the where conditions
     * @return array
     */
    private function loadStockOpnameDetail(array $wheres = []): array
    {
        $wheres[] = '(sod.sod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sod.sod_id, sod.sod_whs_id, whs.whs_name, sod.sod_gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, 
                    sod.sod_gdu_id, uom.uom_code, sod.sod_production_number, sod.sod_serial_number, gd.gd_sn,
                    sod.sod_quantity, sod.sod_qty_figure, sod.sod_gdt_id, gdt.gdt_code, gdt.gdt_description, 
                    (CASE WHEN sod.sod_qty_figure IS NULL THEN 1 ELSE 2 END) as sod_sort, sod.sod_remark
                FROM stock_opname_detail as sod INNER JOIN
                warehouse_storage as whs ON whs.whs_id = sod.sod_whs_id INNER JOIN
                goods as gd ON gd.gd_id = sod.sod_gd_id INNER JOIN
                brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = sod.sod_gdu_id INNER JOIN
                unit as uom ON uom.uom_id = gdu.gdu_uom_id LEFT OUTER JOIN
                goods_damage_type as gdt ON sod.sod_gdt_id = gdt.gdt_id ' . $strWhere;
        $query .= ' ORDER BY sod_sort, whs.whs_name, gd.gd_sku, sod.sod_production_number, sod.sod_gdt_id, sod.sod_id';
        $limit = $this->getIntParameter('limit', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $this->getIntParameter('offset', 0);
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $number = new NumberFormatter();
        $gdDao = new GoodsDao();
        $results = [];
        foreach ($data as $row) {
            $qty = (float) $row['sod_quantity'];
            $qtyFigure = (float) $row['sod_qty_figure'];
            $qtyDiff = $qtyFigure - $qty;
            $results[] = [
                'sod_id' => $row['sod_id'],
                'sod_whs_id' => $row['sod_whs_id'],
                'sod_whs_name' => $row['whs_name'],
                'sod_gd_id' => $row['sod_gd_id'],
                'sod_gd_sku' => $row['gd_sku'],
                'sod_gd_sn' => $row['gd_sn'],
                'sod_gd_name' => $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']),
                'sod_gdu_id' => $row['sod_gdu_id'],
                'sod_uom' => $row['uom_code'],
                'sod_quantity' => $number->doFormatFloat($qty),
                'sod_qty_figure' => $qtyFigure,
                'sod_qty_figure_str' => $number->doFormatFloat($qtyFigure),
                'sod_qty_diff' => $number->doFormatFloat($qtyDiff),
                'sod_lot_number' => $row['sod_production_number'],
                'sod_serial_number' => $row['sod_serial_number'],
                'sod_gdt_id' => $row['sod_gdt_id'],
                'sod_gdt_code' => $row['gdt_code'],
                'sod_gdt_description' => $row['gdt_description'],
                'sod_remark' => $row['sod_remark'],
            ];
        }
        return $results;
    }


    /**
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doComplete(): array
    {
        $results = [];
        DB::beginTransaction();
        try {
            $sopColVal = [
                'sop_end_on' => date('Y-m-d H:i:s')
            ];
            $sopDao = new StockOpnameDao();
            $sopDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $sopColVal, $this->User->getId());

            # Update job action
            $jaeColVal = $this->doUpdateJobActionEvent(2);
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
     * Function to get the goods load data.
     *
     * @return array
     */
    private function doStart(): array
    {
        $results = [];
        DB::beginTransaction();
        try {
            # Update actual time arrival job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), $joColVal, $this->User->getId());

            $sopColVal = [
                'sop_start_on' => date('Y-m-d H:i:s'),
            ];
            $sopDao = new StockOpnameDao();
            $sopDao->doApiUpdateTransaction($this->getIntParameter('jwId'), $sopColVal, $this->User->getId());

            # Generate Stock Opname Detail Data
            $data = $this->loadCurrentStorageStockData();
            $sodDao = new StockOpnameDetailDao();
            foreach ($data as $colValSod) {
                $sodDao->doApiInsertTransaction($colValSod, $this->User->getId());
            }
            $jaeColVal =  $this->doUpdateJobActionEvent(1);
            DB::commit();
            $results = $jaeColVal;
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            dump($e->getMessage());
            exit;
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
        $data = StockOpnameDao::loadApiData($wheres, $this->User->getId());
        $result = [];
        if (\count($data) === 1) {
            $result = $data[0];
            $gdDao = new GoodsDao();
            $time = '';
            if (empty($result['sop_date']) === false) {
                if (empty($result['sop_time']) === false) {
                    $time = DateTimeParser::format($result['sop_date'] . ' ' . $result['sop_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                } else {
                    $time = DateTimeParser::format($result['sop_date'], 'Y-m-d', 'd M Y');
                }
            }
            $result['sop_time'] = $time;
            $result['sop_goods'] = $gdDao->formatFullName($result['sop_gd_category'], $result['sop_gd_brand'], $result['sop_gd_name']);
        }

        return DataParser::doFormatApiData($result);
    }

    /**
     * Function to stock opname goods.
     *
     * @return array
     */
    private function loadJobGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(sod.sod_sop_id = ' . $this->getIntParameter('sop_id') . ')';
        $wheres[] = '(sod.sod_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sod.sod_whs_id, whs.whs_name, sod.sod_gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, 
                    sod.sod_gdu_id, uom.uom_code, sod.sod_production_number, 
                    SUM(sod.sod_quantity) as sod_quantity, SUM(sod.sod_qty_figure) as sod_qty_figure, sod.sod_gdt_id, gdt.gdt_description
                FROM stock_opname_detail as sod INNER JOIN
                warehouse_storage as whs ON whs.whs_id = sod.sod_whs_id INNER JOIN
                goods as gd ON gd.gd_id = sod.sod_gd_id INNER JOIN
                brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = sod.sod_gdu_id INNER JOIN
                unit as uom ON uom.uom_id = gdu.gdu_uom_id LEFT OUTER JOIN
                goods_damage_type as gdt ON sod.sod_gdt_id = gdt.gdt_id ' . $strWhere;
        $query .= ' GROUP BY sod.sod_whs_id, whs.whs_name, sod.sod_gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, 
                        sod.sod_gdu_id, uom.uom_code, sod.sod_production_number, sod.sod_gdt_id, gdt.gdt_description ';
        $query .= ' ORDER BY sod.sod_gd_id, sod.sod_whs_id';
        $limit = $this->getIntParameter('limit', 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $this->getIntParameter('offset', 0);
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobGoodsData($data);
    }
    /**
     * Function to stock opname goods.
     *
     * @param array $data to store the data.
     * @return array
     */
    private function doPrepareJobGoodsData(array $data): array
    {
        $results = [];
        $number = new NumberFormatter();
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $qty = (float) $row['sod_quantity'];
            $qtyFigure = (float) $row['sod_qty_figure'];
            $qtyDiff = $qtyFigure - $qty;
            $results[] = [
                'sod_storage' => $row['whs_name'],
                'sod_sku' => $row['gd_sku'],
                'sod_goods' => $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name']),
                'sod_uom' => $row['uom_code'],
                'sod_quantity' => $qty,
                'sod_quantity_str' => $number->doFormatFloat($qty),
                'sod_qty_figure' => $qtyFigure,
                'sod_qty_figure_str' => $number->doFormatFloat($qtyFigure),
                'sod_qty_diff' => $qtyDiff,
                'sod_qty_diff_str' => $number->doFormatFloat($qtyDiff),
                'sod_lot_number' => $row['sod_production_number'],
                'sod_gdt_description' => $row['gdt_description']
            ];
        }
        return $results;
    }
    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadCurrentStorageStockData(): array
    {
        $result = [];
        $sop = $this->loadSopData($this->getIntParameter('jwId'));
        if (empty($sop) === false) {
            $wheres = [];
            $wheres[] = '(jid.jid_deleted_on IS NULL)';
            $wheres[] = '(jis.stock <> 0)';
            $wheres[] = '(whs.whs_wh_id = ' . $sop['sop_wh_id'] . ')';
            $wheres[] = '(gd.gd_rel_id = ' . $sop['jo_rel_id'] . ')';
            if (empty($sop['sop_gd_id']) === false) {
                $wheres[] = '(jid.jid_gd_id = ' . $sop['sop_gd_id'] . ')';
            }
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_id, jid.jid_gdu_id, jid.jid_gd_id, jid.jid_lot_number, 
                            jid.jid_whs_id, jid.jid_gdt_id, jis.stock, whs.whs_name, jid.jid_serial_number
                    FROM job_inbound_detail AS jid INNER JOIN
                        goods as gd ON jid.jid_gd_id = gd.gd_id INNER JOIN
                        warehouse_storage as whs ON jid.jid_whs_id = whs.whs_id LEFT OUTER JOIN
                          (SELECT jis_jid_id, SUM(jis_quantity) as stock 
                            FROM job_inbound_stock 
                            WHERE (jis_deleted_on IS NULL)
                            GROUP BY jis_jid_id) jis ON jid.jid_id = jis.jis_jid_id ' . $strWheres;
            $query .= ' GROUP BY jid.jid_id, jid.jid_gdu_id, jid.jid_gd_id, jid.jid_lot_number, jid.jid_whs_id, jid.jid_gdt_id, 
                                jis.stock, whs.whs_name, jid.jid_serial_number';
            $query .= ' ORDER BY jid.jid_gdt_id DESC, whs.whs_name, jid.jid_gd_id, jid.jid_lot_number, jid.jid_serial_number, jid.jid_id';
            $sqlResult = DB::select($query);
            if (empty($sqlResult) === false) {
                $data = DataParser::arrayObjectToArray($sqlResult);
                foreach ($data as $row) {
                    $result[] = [
                        'sod_sop_id' => $sop['sop_id'],
                        'sod_whs_id' => $row['jid_whs_id'],
                        'sod_gd_id' => $row['jid_gd_id'],
                        'sod_production_number' => $row['jid_lot_number'],
                        'sod_serial_number' => $row['jid_serial_number'],
                        'sod_quantity' => (float) $row['stock'],
                        'sod_gdu_id' => $row['jid_gdu_id'],
                        'sod_gdt_id' => $row['jid_gdt_id']
                    ];
                }
            }
        }
        return $result;
    }
    /**
     * Function to get the contact Field Set.
     *
     * @param int $sopId To store the sop id.
     * @return array
     */
    private function loadSopData($sopId): array
    {
        $results = [];
        $query = 'SELECT sop.sop_id, sop.sop_wh_id, sop.sop_gd_id, jo.jo_rel_id, jo.jo_id
                FROM stock_opname as sop INNER JOIN
                job_order as jo ON sop.sop_jo_id = jo.jo_id
                WHERE (sop.sop_id = ' . $sopId . ')';
        $sqlResults = DB::select($query);
        if (\count($sqlResults) === 1) {
            $results = DataParser::objectToArrayAPI($sqlResults[0]);
        }
        return $results;
    }
    /**
     * Function to get the contact Field Set.
     *
     * @param int $sopId To store the sop id.
     * @return string
     */
    private function validateCompleteSop($sopId): string
    {
        $results = '';
        $wheres = [];
        $wheres[] = '(sod_sop_id = ' . $sopId . ')';
        $wheres[] = '(sod_deleted_on IS NULL)';
        $wheres[] = '(sod_qty_figure IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sod_id
                FROM stock_opname_detail ' . $strWhere;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $results = Trans::getWord('pleaseUpdateOpnameActual', 'message');
        }
        return $results;
    }
}
