<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Job\Warehouse;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class StockReport extends AbstractStatisticModel
{

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockReport');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $whField = $this->Field->getSingleSelect('warehouse', 'warehouse', $this->getStringParameter('warehouse'));
        $whField->setHiddenField('wh_id', $this->getIntParameter('wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        # Goods Field
        $goodsField = $this->Field->getSingleSelect('goods', 'gd_name', $this->getStringParameter('gd_name'));
        $goodsField->setHiddenField('gd_id', $this->getIntParameter('gd_id'));
        $goodsField->addOptionalParameterById('gd_gdc_id', 'gd_gdc_id');
        $goodsField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->setEnableNewButton(false);
        # Condition
        if ($this->isValidParameter('gd_condition') === false) {
            $this->setParameter('gd_condition', 'G');
        }
        $conditionField = $this->Field->getRadioGroup('gd_condition', $this->getStringParameter('gd_condition'));
        $conditionField->addRadio(Trans::getWord('good'), 'G');
        $conditionField->addRadio(Trans::getWord('damage'), 'D');
        # Category Field
        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gdc_name', $this->getStringParameter('gdc_name'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setEnableNewButton(false);
        $goodsCategoryField->addOptionalParameterById('gd_rel_id', 'rel_id');
        $goodsCategoryField->addClearField('gd_name');
        $goodsCategoryField->addClearField('gd_id');


        # Add field into field set.
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === false) {
            $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
            $relationField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
            $relationField->addParameter('rel_ss_id', $this->User->getSsId());
            $relationField->setEnableNewButton(false);
            $relationField->addClearField('gdc_name');
            $relationField->addClearField('gd_gdc_id');
            $relationField->addClearField('gd_name');
            $relationField->addClearField('gd_id');
            $this->StatisticForm->addField(Trans::getWord('relation'), $relationField);
        } else {
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_id', $this->User->getRelId()));
            $this->StatisticForm->addHiddenField($this->Field->getHidden('rel_name', $this->User->Relation->getName()));
        }

        $this->StatisticForm->addField(Trans::getWord('category'), $goodsCategoryField);
        $this->StatisticForm->addField(Trans::getWord('goods'), $goodsField);
        $this->StatisticForm->addField(Trans::getWord('warehouse'), $whField);
        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('from_date', $this->getStringParameter('from_date')), true);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('until_date', $this->getStringParameter('until_date')));
        $this->StatisticForm->addField(Trans::getWord('condition'), $conditionField);
        $this->StatisticForm->setGridDimension(4);
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('from_date');
        $this->Validation->checkDate('from_date');
        if ($this->isValidParameter('from_date') === true && $this->isValidParameter('until_date') === true) {
            $this->Validation->checkDate('until_date', '', $this->getStringParameter('from_date'));
        }
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $portlet = $this->getResultPortlet();
        if ($this->getFormAction() === 'doExportXls') {
            $this->addDatas('Stock', $portlet);
        }
        $this->addContent('RslCtn', $portlet);
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultTable(): Table
    {
        $table = new Table('RslTbl');
        if ($this->getFormAction() === 'doExportXls') {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'good_origin' => Trans::getWord('initialGoodStock'),
                'damage_origin' => Trans::getWord('initialDamageStock'),
                'total_origin' => Trans::getWord('initialStock'),
                'total_wg_origin' => Trans::getWord('initialStockWeight') . ' (KG)',
                'total_vl_origin' => Trans::getWord('initialStockVolume') . ' (M3)',

                'in_good' => Trans::getWord('goodIn'),
                'in_damage' => Trans::getWord('damageIn'),
                'total_in' => Trans::getWord('totalIn'),
                'total_wg_in' => Trans::getWord('totalInWeight') . ' (KG)',
                'total_vl_in' => Trans::getWord('totalInVolume') . ' (M3)',

                'out_good' => Trans::getWord('goodOut'),
                'out_damage' => Trans::getWord('damageOut'),
                'total_out' => Trans::getWord('totalOut'),
                'total_wg_out' => Trans::getWord('totalOutWeight') . ' (KG)',
                'total_vl_out' => Trans::getWord('totalOutVolume') . ' (M3)',

                'mv_good' => Trans::getWord('movDamageToGood'),
                'wg_mv_good' => Trans::getWord('movDamageToGoodWeight') . ' (KG)',
                'vl_mv_good' => Trans::getWord('movDamageToGoodVolume') . ' (M3)',

                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'wg_mv_damage' => Trans::getWord('movGoodToDamageWeight') . ' (KG)',
                'vl_mv_damage' => Trans::getWord('movGoodToDamageVolume') . ' (M3)',

                'adj_good' => Trans::getWord('goodAdjustment'),
                'adj_damage' => Trans::getWord('damageAdjustment'),
                'total_adj' => Trans::getWord('totalAdjustment'),
                'total_wg_adj' => Trans::getWord('adjustmentWeight') . ' (KG)',
                'total_vl_adj' => Trans::getWord('adjustmentVolume') . ' (M3)',

                'good_last' => Trans::getWord('lastGoodStock'),
                'damage_last' => Trans::getWord('lastDamageStock'),
                'total_last' => Trans::getWord('lastStock'),
                'total_wg_last' => Trans::getWord('lastStockWeight') . ' (KG)',
                'total_vl_last' => Trans::getWord('lastStockVolume') . ' (M3)',
            ]);
            $table->setColumnType('good_origin', 'integer');
            $table->setColumnType('damage_origin', 'integer');
            $table->setColumnType('in_good', 'integer');
            $table->setColumnType('in_damage', 'integer');
            $table->setColumnType('out_good', 'integer');
            $table->setColumnType('out_damage', 'integer');
            $table->setColumnType('good_last', 'integer');
            $table->setColumnType('damage_last', 'integer');
            $table->setColumnType('adj_good', 'integer');
            $table->setColumnType('adj_damage', 'integer');

            $table->setFooterType('good_origin', 'SUM');
            $table->setFooterType('damage_origin', 'SUM');
            $table->setFooterType('in_good', 'SUM');
            $table->setFooterType('in_damage', 'SUM');
            $table->setFooterType('out_good', 'SUM');
            $table->setFooterType('out_damage', 'SUM');
            $table->setFooterType('good_last', 'SUM');
            $table->setFooterType('damage_last', 'SUM');
            $table->setFooterType('adj_good', 'SUM');
            $table->setFooterType('adj_damage', 'SUM');

            $table->setColumnType('total_wg_origin', 'float');
            $table->setColumnType('total_vl_origin', 'float');
            $table->setColumnType('total_wg_in', 'float');
            $table->setColumnType('total_vl_in', 'float');
            $table->setColumnType('total_wg_out', 'float');
            $table->setColumnType('total_vl_out', 'float');
            $table->setColumnType('total_wg_last', 'float');
            $table->setColumnType('total_vl_last', 'float');
            $table->setColumnType('wg_mv_good', 'float');
            $table->setColumnType('vl_mv_good', 'float');
            $table->setColumnType('wg_mv_damage', 'float');
            $table->setColumnType('vl_mv_damage', 'float');
            $table->setColumnType('total_wg_adj', 'float');
            $table->setColumnType('total_vl_adj', 'float');

            $table->setFooterType('total_wg_origin', 'SUM');
            $table->setFooterType('total_vl_origin', 'SUM');
            $table->setFooterType('total_wg_in', 'SUM');
            $table->setFooterType('total_vl_in', 'SUM');
            $table->setFooterType('total_wg_out', 'SUM');
            $table->setFooterType('total_vl_out', 'SUM');
            $table->setFooterType('total_wg_last', 'SUM');
            $table->setFooterType('total_vl_last', 'SUM');
            $table->setFooterType('wg_mv_good', 'SUM');
            $table->setFooterType('vl_mv_good', 'SUM');
            $table->setFooterType('wg_mv_damage', 'SUM');
            $table->setFooterType('vl_mv_damage', 'SUM');
            $table->setFooterType('total_wg_adj', 'SUM');
            $table->setFooterType('total_vl_adj', 'SUM');

        } else {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'total_origin' => Trans::getWord('initialStock'),
                'total_in' => mb_strtoupper(Trans::getWord('in')),
                'total_out' => mb_strtoupper(Trans::getWord('out')),
                'mv_good' => Trans::getWord('movDamageToGood'),
                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'total_adj' => Trans::getWord('adjustment'),
                'total_last' => Trans::getWord('lastStock'),
            ]);
        }
        $table->addColumnAttribute('gd_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_relation', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_sku', 'style', 'text-align: center;');
        $table->setColumnType('total_origin', 'integer');
        $table->setColumnType('total_in', 'integer');
        $table->setColumnType('total_out', 'integer');
        $table->setColumnType('total_last', 'integer');
        $table->setColumnType('mv_good', 'integer');
        $table->setColumnType('mv_damage', 'integer');
        $table->setColumnType('total_adj', 'integer');

        $table->setFooterType('total_origin', 'SUM');
        $table->setFooterType('total_in', 'SUM');
        $table->setFooterType('total_out', 'SUM');
        $table->setFooterType('total_last', 'SUM');
        $table->setFooterType('mv_good', 'SUM');
        $table->setFooterType('mv_damage', 'SUM');
        $table->setFooterType('total_adj', 'SUM');

        return $table;
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultGoodTable(): Table
    {
        $table = new Table('RslTbl');
        if ($this->getFormAction() === 'doExportXls') {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'good_origin' => Trans::getWord('initialStock'),
                'wg_good_origin' => Trans::getWord('initialStockWeight') . ' (KG)',
                'vl_good_origin' => Trans::getWord('initialStockVolume') . ' (M3)',

                'in_good' => Trans::getWord('in'),
                'wg_in_good' => Trans::getWord('inWeight') . ' (KG)',
                'vl_in_good' => Trans::getWord('inVolume') . ' (M3)',

                'out_good' => Trans::getWord('out'),
                'wg_out_good' => Trans::getWord('outWeight') . ' (KG)',
                'vl_out_good' => Trans::getWord('outVolume') . ' (M3)',

                'mv_good' => Trans::getWord('movDamageToGood'),
                'wg_mv_good' => Trans::getWord('movDamageToGoodWeight') . ' (KG)',
                'vl_mv_good' => Trans::getWord('movDamageToGoodVolume') . ' (M3)',

                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'wg_mv_damage' => Trans::getWord('movGoodToDamageWeight') . ' (KG)',
                'vl_mv_damage' => Trans::getWord('movGoodToDamageVolume') . ' (M3)',

                'adj_good' => Trans::getWord('adjustment'),
                'wg_adj_good' => Trans::getWord('adjustmentWeight') . ' (KG)',
                'vl_adj_good' => Trans::getWord('adjustmentVolume') . ' (M3)',

                'good_last' => Trans::getWord('lastStock'),
                'wg_good_last' => Trans::getWord('lastStockWeight') . ' (KG)',
                'vl_good_last' => Trans::getWord('lastStockVolume') . ' (M3)',
            ]);
            $table->setColumnType('wg_good_origin', 'float');
            $table->setColumnType('vl_good_origin', 'float');
            $table->setColumnType('wg_in_good', 'float');
            $table->setColumnType('vl_in_good', 'float');
            $table->setColumnType('wg_out_good', 'float');
            $table->setColumnType('vl_out_good', 'float');
            $table->setColumnType('wg_good_last', 'float');
            $table->setColumnType('vl_good_last', 'float');

            $table->setFooterType('wg_good_origin', 'SUM');
            $table->setFooterType('vl_good_origin', 'SUM');
            $table->setFooterType('wg_in_good', 'SUM');
            $table->setFooterType('vl_in_good', 'SUM');
            $table->setFooterType('wg_out_good', 'SUM');
            $table->setFooterType('vl_out_good', 'SUM');
            $table->setFooterType('wg_good_last', 'SUM');
            $table->setFooterType('vl_good_last', 'SUM');
            $table->setColumnType('wg_mv_good', 'float');
            $table->setColumnType('vl_mv_good', 'float');
            $table->setColumnType('wg_mv_damage', 'float');
            $table->setColumnType('vl_mv_damage', 'float');
            $table->setColumnType('wg_adj_good', 'float');
            $table->setColumnType('vl_adj_good', 'float');
            $table->setFooterType('wg_mv_good', 'SUM');
            $table->setFooterType('vl_mv_good', 'SUM');
            $table->setFooterType('wg_mv_damage', 'SUM');
            $table->setFooterType('vl_mv_damage', 'SUM');
            $table->setFooterType('wg_adj_good', 'SUM');
            $table->setFooterType('vl_adj_good', 'SUM');

        } else {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'good_origin' => Trans::getWord('initialStock'),
                'in_good' => mb_strtoupper(Trans::getWord('in')),
                'out_good' => mb_strtoupper(Trans::getWord('out')),
                'mv_good' => Trans::getWord('movDamageToGood'),
                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'adj_good' => Trans::getWord('adjustment'),
                'good_last' => Trans::getWord('lastStock'),
            ]);
        }
        $table->addColumnAttribute('gd_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_relation', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_sku', 'style', 'text-align: center;');

        $table->setColumnType('good_origin', 'integer');
        $table->setColumnType('in_good', 'integer');
        $table->setColumnType('out_good', 'integer');
        $table->setColumnType('good_last', 'integer');
        $table->setColumnType('adj_good', 'integer');

        $table->setFooterType('good_origin', 'SUM');
        $table->setFooterType('in_good', 'SUM');
        $table->setFooterType('out_good', 'SUM');
        $table->setFooterType('good_last', 'SUM');
        $table->setFooterType('adj_good', 'SUM');

        return $table;
    }

    /**
     * Function to get the report table.
     *
     * @return Table
     */
    protected function getResultDamageTable(): Table
    {
        $table = new Table('RslTbl');
        if ($this->getFormAction() === 'doExportXls') {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'damage_origin' => Trans::getWord('initialStock'),
                'wg_damage_origin' => Trans::getWord('initialStockWeight') . ' (KG)',
                'vl_damage_origin' => Trans::getWord('initialStockVolume') . ' (M3)',

                'in_damage' => Trans::getWord('in'),
                'wg_in_damage' => Trans::getWord('inWeight') . ' (KG)',
                'vl_in_damage' => Trans::getWord('inVolume') . ' (M3)',

                'out_damage' => Trans::getWord('out'),
                'wg_out_damage' => Trans::getWord('outWeight') . ' (KG)',
                'vl_out_damage' => Trans::getWord('outVolume') . ' (M3)',

                'mv_good' => Trans::getWord('movDamageToGood'),
                'wg_mv_good' => Trans::getWord('movDamageToGoodWeight') . ' (KG)',
                'vl_mv_good' => Trans::getWord('movDamageToGoodVolume') . ' (M3)',

                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'wg_mv_damage' => Trans::getWord('movGoodToDamageWeight') . ' (KG)',
                'vl_mv_damage' => Trans::getWord('movGoodToDamageVolume') . ' (M3)',

                'adj_damage' => Trans::getWord('adjustment'),
                'wg_adj_damage' => Trans::getWord('adjustmentWeight') . ' (KG)',
                'vl_adj_damage' => Trans::getWord('adjustmentVolume') . ' (M3)',

                'damage_last' => Trans::getWord('lastStock'),
                'wg_damage_last' => Trans::getWord('lastStockWeight') . ' (KG)',
                'vl_damage_last' => Trans::getWord('lastStockVolume') . ' (M3)',
            ]);
            $table->setColumnType('wg_damage_origin', 'float');
            $table->setColumnType('vl_damage_origin', 'float');
            $table->setColumnType('wg_in_damage', 'float');
            $table->setColumnType('vl_in_damage', 'float');
            $table->setColumnType('wg_out_damage', 'float');
            $table->setColumnType('vl_out_damage', 'float');
            $table->setColumnType('wg_damage_last', 'float');
            $table->setColumnType('vl_damage_last', 'float');

            $table->setColumnType('wg_mv_good', 'float');
            $table->setColumnType('vl_mv_good', 'float');
            $table->setColumnType('wg_mv_damage', 'float');
            $table->setColumnType('vl_mv_damage', 'float');
            $table->setColumnType('wg_adj_damage', 'float');
            $table->setColumnType('vl_adj_damage', 'float');

            $table->setFooterType('wg_damage_origin', 'SUM');
            $table->setFooterType('vl_damage_origin', 'SUM');
            $table->setFooterType('wg_in_damage', 'SUM');
            $table->setFooterType('vl_in_damage', 'SUM');
            $table->setFooterType('wg_out_damage', 'SUM');
            $table->setFooterType('vl_out_damage', 'SUM');
            $table->setFooterType('wg_damage_last', 'SUM');
            $table->setFooterType('vl_damage_last', 'SUM');

            $table->setFooterType('wg_mv_good', 'SUM');
            $table->setFooterType('vl_mv_good', 'SUM');
            $table->setFooterType('wg_mv_damage', 'SUM');
            $table->setFooterType('vl_mv_damage', 'SUM');
            $table->setFooterType('wg_adj_damage', 'SUM');
            $table->setFooterType('vl_adj_damage', 'SUM');

        } else {
            $table->setHeaderRow([
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom' => Trans::getWord('uom'),

                'damage_origin' => Trans::getWord('initialStock'),

                'in_damage' => mb_strtoupper(Trans::getWord('in')),

                'out_damage' => mb_strtoupper(Trans::getWord('out')),

                'mv_good' => Trans::getWord('movDamageToGood'),
                'mv_damage' => Trans::getWord('movGoodToDamage'),
                'adj_damage' => Trans::getWord('adjustment'),

                'damage_last' => Trans::getWord('lastStock'),
            ]);
        }
        $table->addColumnAttribute('gd_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_relation', 'style', 'text-align: center;');
        $table->addColumnAttribute('gd_sku', 'style', 'text-align: center;');

        $table->setColumnType('damage_origin', 'integer');
        $table->setColumnType('in_damage', 'integer');
        $table->setColumnType('out_damage', 'integer');
        $table->setColumnType('damage_last', 'integer');
        $table->setColumnType('mv_good', 'integer');
        $table->setColumnType('mv_damage', 'integer');
        $table->setColumnType('adj_damage', 'integer');
        $table->setFooterType('damage_origin', 'SUM');
        $table->setFooterType('in_damage', 'SUM');
        $table->setFooterType('out_damage', 'SUM');
        $table->setFooterType('damage_last', 'SUM');
        $table->setFooterType('mv_good', 'SUM');
        $table->setFooterType('mv_damage', 'SUM');
        $table->setFooterType('adj_damage', 'SUM');


        return $table;
    }

    /**
     * Function to get the report portlet.
     *
     * @return Portlet
     */
    protected function getResultPortlet(): Portlet
    {
        if ($this->isValidParameter('gd_condition') === true) {
            if ($this->getStringParameter('gd_condition', 'G') === 'D') {
                $table = $this->getResultDamageTable();
                $title = Trans::getWord('stockDamageReport');
            } else {
                $table = $this->getResultGoodTable();
                $title = Trans::getWord('stockGoodReport');
            }
        } else {
            $table = $this->getResultTable();
            $title = Trans::getWord('stockReport');
        }
        $portlet = new Portlet('RslPtl', $title);
        $table->addRows($this->doPrepareData($this->loadData()));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    protected function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $goods = $this->doStockCalculation($this->loadDataFormat($row), $row);
            if ($this->isValidData($goods) === true) {
                $results[] = $goods;
            }
        }
        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $goods To store the data.
     *
     * @return bool
     */
    private function isValidData(array $goods): bool
    {
        if ($this->isValidParameter('gd_condition')) {
            if ($this->getStringParameter('gd_condition') === 'D') {
                return $goods['damage_origin'] > 0
                    || $goods['sto_damage_origin'] > 0
                    || $goods['in_damage'] > 0
                    || $goods['sto_damage'] > 0
                    || $goods['out_damage'] > 0
                    || $goods['mv_good'] > 0
                    || $goods['mv_damage'] > 0
                    || $goods['adj_damage'] > 0;
            }
            return $goods['good_origin'] > 0
                || $goods['sto_good_origin'] > 0
                || $goods['in_good'] > 0
                || $goods['sto_good'] > 0
                || $goods['out_good'] > 0
                || $goods['mv_good'] > 0
                || $goods['mv_damage'] > 0
                || $goods['adj_good'] > 0;
        }
        return $goods['total_origin'] > 0
            || $goods['total_sto_origin'] > 0
            || $goods['total_in'] > 0
            || $goods['total_sto'] > 0
            || $goods['total_out'] > 0
            || $goods['mv_good'] > 0
            || $goods['mv_damage'] > 0
            || $goods['total_adj'] > 0;

    }


    /**
     * Get query to get the quotation data.
     *
     * @param array $goods To store the goods data.
     * @param array $row To store the data.
     *
     * @return array
     */
    private function doStockCalculation(array $goods, array $row): array
    {
        # Stock Awal
        $originStock = $this->doCalculateOriginStock($row);
        $goods['good_origin'] += $originStock['good'];
        $goods['vl_good_origin'] += $originStock['vl_good'];
        $goods['wg_good_origin'] += $originStock['wg_good'];
        $goods['damage_origin'] += $originStock['damage'];
        $goods['vl_damage_origin'] += $originStock['vl_damage'];
        $goods['wg_damage_origin'] += $originStock['wg_damage'];
        $goods['total_origin'] += $originStock['total'];
        $goods['total_vl_origin'] += $originStock['total_vl'];
        $goods['total_wg_origin'] += $originStock['total_wg'];

        # Stock Awal Staging Out
        $sto = $this->doCalculateOriginStagingOut($row);
        $goods['sto_good_origin'] += $sto['good'];
        $goods['vl_sto_good_origin'] += $sto['vl_good'];
        $goods['wg_sto_good_origin'] += $sto['wg_good'];
        $goods['sto_damage_origin'] += $sto['damage'];
        $goods['vl_sto_damage_origin'] += $sto['vl_damage'];
        $goods['wg_sto_damage_origin'] += $sto['wg_damage'];
        $goods['total_sto_origin'] += $sto['total'];
        $goods['total_vl_sto_origin'] += $sto['total_vl'];
        $goods['total_wg_sto_origin'] += $sto['total_wg'];
        # Current Inbound
        $goods['in_good'] += (float)$row['in_gd_b'];
        $goods['vl_in_good'] += (float)$row['in_gd_b_vl'];
        $goods['wg_in_good'] += (float)$row['in_gd_b_wg'];
        $goods['in_damage'] += (float)$row['in_dm_b'];
        $goods['vl_in_damage'] += (float)$row['in_dm_b_vl'];
        $goods['wg_in_damage'] += (float)$row['in_dm_b_wg'];
        $goods['total_in'] = $goods['in_good'] + $goods['in_damage'];
        $goods['total_vl_in'] = $goods['vl_in_good'] + $goods['vl_in_damage'];
        $goods['total_wg_in'] = $goods['wg_in_good'] + $goods['wg_in_damage'];

        # Current Staging Out
        $goods['sto_good'] += (float)$row['sto_gd_b'];
        $goods['vl_sto_good'] += (float)$row['sto_gd_b_vl'];
        $goods['wg_sto_good'] += (float)$row['sto_gd_b_wg'];
        $goods['sto_damage'] += (float)$row['sto_dm_b'];
        $goods['vl_sto_damage'] += (float)$row['sto_dm_b_vl'];
        $goods['wg_sto_damage'] += (float)$row['sto_dm_b_wg'];
        $goods['total_sto'] = $goods['sto_good'] + $goods['sto_damage'];
        $goods['total_vl_sto'] = $goods['vl_sto_good'] + $goods['vl_sto_damage'];
        $goods['total_wg_sto'] = $goods['wg_sto_good'] + $goods['wg_sto_damage'];


        # Current Outbound
        $goods['out_good'] += (float)$row['out_gd_b'];
        $goods['vl_out_good'] += (float)$row['out_gd_b_vl'];
        $goods['wg_out_good'] += (float)$row['out_gd_b_wg'];
        $goods['out_damage'] += (float)$row['out_dm_b'];
        $goods['vl_out_damage'] += (float)$row['out_dm_b_vl'];
        $goods['wg_out_damage'] += (float)$row['out_dm_b_wg'];
        $goods['total_out'] = $goods['out_good'] + $goods['out_damage'];
        $goods['total_vl_out'] = $goods['vl_out_good'] + $goods['vl_out_damage'];
        $goods['total_wg_out'] = $goods['wg_out_good'] + $goods['wg_out_damage'];

        # Restore Goods
        $goods['rtr_good'] += (float)$row['rt_gd_b'];
        $goods['vl_rtr_good'] += (float)$row['rt_gd_b_vl'];
        $goods['wg_rtr_good'] += (float)$row['rt_gd_b_wg'];
        $goods['rtr_damage'] += (float)$row['rt_dm_b'];
        $goods['vl_rtr_damage'] += (float)$row['rt_dm_b_vl'];
        $goods['wg_rtr_damage'] += (float)$row['rt_dm_b_wg'];
        $goods['total_rtr'] = $goods['rtr_good'] + $goods['rtr_damage'];
        $goods['total_vl_rtr'] = $goods['vl_rtr_good'] + $goods['vl_rtr_damage'];
        $goods['total_wg_rtr'] = $goods['wg_rtr_good'] + $goods['wg_rtr_damage'];

        # Current Movement
        $goods['mv_good'] += (float)$row['mv_gd_b'];
        $goods['vl_mv_good'] += (float)$row['mv_gd_b_vl'];
        $goods['wg_mv_good'] += (float)$row['mv_gd_b_wg'];
        $goods['mv_damage'] += (float)$row['mv_dm_b'];
        $goods['vl_mv_damage'] += (float)$row['mv_dm_b_vl'];
        $goods['wg_mv_damage'] += (float)$row['mv_dm_b_wg'];

        # Current Adjustment
        $goods['adj_good'] += (float)$row['ad_gd_b'];
        $goods['vl_adj_good'] += (float)$row['ad_gd_b_vl'];
        $goods['wg_adj_good'] += (float)$row['ad_gd_b_wg'];
        $goods['adj_damage'] += (float)$row['ad_dm_b'];
        $goods['vl_adj_damage'] += (float)$row['ad_dm_b_vl'];
        $goods['wg_adj_damage'] += (float)$row['ad_dm_b_wg'];
        $goods['total_adj'] = $goods['adj_good'] + $goods['adj_damage'];
        $goods['total_vl_adj'] = $goods['vl_adj_good'] + $goods['vl_adj_damage'];
        $goods['total_wg_adj'] = $goods['wg_adj_good'] + $goods['wg_adj_damage'];

        # Last Stock
        $goods['good_last'] = ($goods['good_origin'] + $goods['in_good'] + $goods['rtr_good'] + $goods['mv_good'] + $goods['adj_good']) -
            ($goods['sto_good'] + $goods['mv_damage']);
        $goods['vl_good_last'] = ($goods['vl_good_origin'] + $goods['vl_in_good'] + $goods['vl_rtr_good'] + $goods['vl_mv_good'] + $goods['vl_adj_good']) -
            ($goods['vl_sto_good'] + $goods['vl_mv_damage']);
        $goods['wg_good_last'] = ($goods['wg_good_origin'] + $goods['wg_in_good'] + $goods['wg_rtr_good'] + $goods['wg_mv_good'] + $goods['wg_adj_good']) -
            ($goods['wg_sto_good'] + $goods['wg_mv_damage']);
        $goods['damage_last'] = ($goods['damage_origin'] + $goods['in_damage'] + $goods['rtr_damage'] + $goods['mv_damage'] + $goods['adj_damage']) -
            ($goods['sto_damage'] + $goods['mv_good']);
        $goods['vl_damage_last'] = ($goods['vl_damage_origin'] + $goods['vl_in_damage'] + $goods['vl_rtr_damage'] + $goods['vl_mv_damage'] + $goods['vl_adj_damage']) -
            ($goods['vl_sto_damage'] + $goods['vl_mv_good']);
        $goods['wg_damage_last'] = ($goods['wg_damage_origin'] + $goods['wg_in_damage'] + $goods['wg_rtr_damage'] + $goods['wg_mv_damage'] + $goods['wg_adj_damage']) -
            ($goods['wg_sto_damage'] + $goods['wg_mv_good']);
        $goods['total_last'] = $goods['good_last'] + $goods['damage_last'];
        $goods['total_vl_last'] = $goods['vl_good_last'] + $goods['vl_damage_last'];
        $goods['total_wg_last'] = $goods['wg_good_last'] + $goods['wg_damage_last'];
        # Last Stock Staging Out
        $goods['sto_good_last'] = ($goods['sto_good_origin'] + $goods['sto_good']) - ($goods['out_good'] + $goods['rtr_good']);
        $goods['vl_sto_good_last'] = ($goods['vl_sto_good_origin'] + $goods['vl_sto_good']) - ($goods['vl_out_good'] + $goods['vl_rtr_good']);
        $goods['wg_sto_good_last'] = ($goods['wg_sto_good_origin'] + $goods['wg_sto_good']) - ($goods['wg_out_good'] + $goods['wg_rtr_good']);

        $goods['sto_damage_last'] = ($goods['sto_damage_origin'] + $goods['sto_damage']) - ($goods['out_damage'] + $goods['rtr_damage']);
        $goods['vl_sto_damage_last'] = ($goods['vl_sto_damage_origin'] + $goods['vl_sto_damage']) - ($goods['vl_out_damage'] + $goods['vl_rtr_damage']);
        $goods['wg_sto_damage_last'] = ($goods['wg_sto_damage_origin'] + $goods['wg_sto_damage']) - ($goods['wg_out_damage'] + $goods['wg_rtr_damage']);

        $goods['total_sto_last'] = $goods['sto_good_last'] + $goods['sto_damage_last'];
        $goods['total_vl_sto_last'] = $goods['vl_sto_good_last'] + $goods['vl_sto_damage_last'];
        $goods['total_wg_sto_last'] = $goods['wg_sto_good_last'] + $goods['wg_sto_damage_last'];
        return $goods;
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $row To store the data.
     *
     * @return array
     */
    private function doCalculateOriginStock(array $row): array
    {
        # When Inbound
        $good = (float)$row['in_gd_a'];
        $vlGood = (float)$row['in_gd_a_vl'];
        $wgGood = (float)$row['in_gd_a_wg'];
        $damage = (float)$row['in_dm_a'];
        $vlDamage = (float)$row['in_dm_a_vl'];
        $wgDamage = (float)$row['in_dm_a_wg'];
        # When Picking Goods decrees picked goods
        $good -= (float)$row['sto_gd_a'];
        $vlGood -= (float)$row['sto_gd_a_vl'];
        $wgGood -= (float)$row['sto_gd_a_wg'];
        $damage -= (float)$row['sto_dm_a'];
        $vlDamage -= (float)$row['sto_dm_a_vl'];
        $wgDamage -= (float)$row['sto_dm_a_wg'];
        # WHEN Outbound add return goods
        $good += (float)$row['rt_gd_a'];
        $vlGood += (float)$row['rt_gd_a_vl'];
        $wgGood += (float)$row['rt_gd_a_wg'];
        $damage += (float)$row['rt_dm_a'];
        $vlDamage += (float)$row['rt_dm_a_vl'];
        $wgDamage += (float)$row['rt_dm_a_wg'];
        # When movement from damage to good condition
        $mvg = (float)$row['mv_gd_a'];
        $mvgVl = (float)$row['mv_gd_a_vl'];
        $mvgWg = (float)$row['mv_gd_a_wg'];

        # When movement from damage to good condition
        $mvd = (float)$row['mv_dm_a'];
        $mvdVl = (float)$row['mv_dm_a_vl'];
        $mvdWg = (float)$row['mv_dm_a_wg'];

        $good += ($mvg - $mvd);
        $vlGood += ($mvgVl - $mvdVl);
        $wgGood += ($mvgWg - $mvdWg);
        $damage += ($mvd - $mvg);
        $vlDamage += ($mvdVl - $mvgVl);
        $wgDamage += ($mvdWg - $mvgWg);

        # When Adjustment
        $good += (float)$row['ad_gd_a'];
        $vlGood += (float)$row['ad_gd_a_vl'];
        $wgGood += (float)$row['ad_gd_a_wg'];
        $damage += (float)$row['ad_dm_a'];
        $vlDamage += (float)$row['ad_dm_a_vl'];
        $wgDamage += (float)$row['ad_dm_a_wg'];
        return [
            'good' => $good,
            'vl_good' => $vlGood,
            'wg_good' => $wgGood,
            'damage' => $damage,
            'vl_damage' => $vlDamage,
            'wg_damage' => $wgDamage,
            'total' => $good + $damage,
            'total_vl' => $vlGood + $vlDamage,
            'total_wg' => $wgGood + $wgDamage,
        ];
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $row To store the data.
     *
     * @return array
     */
    private function doCalculateOriginStagingOut(array $row): array
    {
        $good = (float)$row['sto_gd_a'];
        $vlGood = (float)$row['sto_gd_a_vl'];
        $wgGood = (float)$row['sto_gd_a_wg'];
        $damage = (float)$row['sto_dm_a'];
        $vlDamage = (float)$row['sto_dm_a_vl'];
        $wgDamage = (float)$row['sto_dm_a_wg'];

        $good -= ((float)$row['out_gd_a'] + (float)$row['rt_gd_a']);
        $vlGood -= ((float)$row['out_gd_a_vl'] + (float)$row['rt_gd_a_vl']);
        $wgGood -= ((float)$row['out_gd_a_wg'] + (float)$row['rt_gd_a_wg']);
        $damage -= ((float)$row['out_dm_a'] + (float)$row['rt_dm_a']);
        $vlDamage -= ((float)$row['out_dm_a_vl'] + (float)$row['rt_dm_a_vl']);
        $wgDamage -= ((float)$row['out_dm_a_wg'] + (float)$row['rt_dm_a_wg']);
        return [
            'good' => $good,
            'vl_good' => $vlGood,
            'wg_good' => $wgGood,
            'damage' => $damage,
            'vl_damage' => $vlDamage,
            'wg_damage' => $wgDamage,
            'total' => $good + $damage,
            'total_vl' => $vlGood + $vlDamage,
            'total_wg' => $wgGood + $wgDamage,
        ];
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $row To store the data.
     *
     * @return array
     */
    private function loadDataFormat(array $row): array
    {
        return [
            'gd_id' => $row['gd_id'],
            'gd_sku' => $row['gd_sku'],
            'gd_name' => $row['gd_name'],
            'gd_relation' => $row['rel_short_name'],
            'gd_brand' => $row['br_name'],
            'gd_category' => $row['gdc_name'],
            'gd_uom' => $row['uom_code'],
            # Origin stock
            'good_origin' => 0.0,
            'vl_good_origin' => 0.0,
            'wg_good_origin' => 0.0,
            'damage_origin' => 0.0,
            'vl_damage_origin' => 0.0,
            'wg_damage_origin' => 0.0,
            'total_origin' => 0.0,
            'total_vl_origin' => 0.0,
            'total_wg_origin' => 0.0,
            # Origin staging out
            'sto_good_origin' => 0.0,
            'vl_sto_good_origin' => 0.0,
            'wg_sto_good_origin' => 0.0,
            'sto_damage_origin' => 0.0,
            'vl_sto_damage_origin' => 0.0,
            'wg_sto_damage_origin' => 0.0,
            'total_sto_origin' => 0.0,
            'total_vl_sto_origin' => 0.0,
            'total_wg_sto_origin' => 0.0,
            # Current Inbound
            'in_good' => 0.0,
            'vl_in_good' => 0.0,
            'wg_in_good' => 0.0,
            'in_damage' => 0.0,
            'vl_in_damage' => 0.0,
            'wg_in_damage' => 0.0,
            'total_in' => 0.0,
            'total_vl_in' => 0.0,
            'total_wg_in' => 0.0,
            # Current Staging Out
            'sto_good' => 0.0,
            'vl_sto_good' => 0.0,
            'wg_sto_good' => 0.0,
            'sto_damage' => 0.0,
            'vl_sto_damage' => 0.0,
            'wg_sto_damage' => 0.0,
            'total_sto' => 0.0,
            'total_vl_sto' => 0.0,
            'total_wg_sto' => 0.0,
            # Current Outbound
            'out_good' => 0.0,
            'vl_out_good' => 0.0,
            'wg_out_good' => 0.0,
            'out_damage' => 0.0,
            'vl_out_damage' => 0.0,
            'wg_out_damage' => 0.0,
            'total_out' => 0.0,
            'total_vl_out' => 0.0,
            'total_wg_out' => 0.0,
            # Current Restore Outbound
            'rtr_good' => 0.0,
            'vl_rtr_good' => 0.0,
            'wg_rtr_good' => 0.0,
            'rtr_damage' => 0.0,
            'vl_rtr_damage' => 0.0,
            'wg_rtr_damage' => 0.0,
            'total_rtr' => 0.0,
            'total_vl_rtr' => 0.0,
            'total_wg_rtr' => 0.0,
            # Current Movement Damage to Good
            'mv_good' => 0.0,
            'vl_mv_good' => 0.0,
            'wg_mv_good' => 0.0,
            # Current Movement Good to Damage
            'mv_damage' => 0.0,
            'vl_mv_damage' => 0.0,
            'wg_mv_damage' => 0.0,
            # Current Adjustment
            'adj_good' => 0.0,
            'vl_adj_good' => 0.0,
            'wg_adj_good' => 0.0,
            'adj_damage' => 0.0,
            'vl_adj_damage' => 0.0,
            'wg_adj_damage' => 0.0,
            'total_adj' => 0.0,
            'total_vl_adj' => 0.0,
            'total_wg_adj' => 0.0,
            # Origin stock
            'good_last' => 0.0,
            'vl_good_last' => 0.0,
            'wg_good_last' => 0.0,
            'damage_last' => 0.0,
            'vl_damage_last' => 0.0,
            'wg_damage_last' => 0.0,
            'total_last' => 0.0,
            'total_vl_last' => 0.0,
            'total_wg_last' => 0.0,
            # Origin staging out
            'sto_good_last' => 0.0,
            'vl_sto_good_last' => 0.0,
            'wg_sto_good_last' => 0.0,
            'sto_damage_last' => 0.0,
            'vl_sto_damage_last' => 0.0,
            'wg_sto_damage_last' => 0.0,
            'total_sto_last' => 0.0,
            'total_vl_sto_last' => 0.0,
            'total_wg_sto_last' => 0.0,
        ];
    }


    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    protected function loadData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('gd.gd_ss_id', $this->User->getSsId());
        $wheres[] = '(gd.gd_deleted_on IS NULL)';
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('gd.gd_id', $this->getIntParameter('gd_id'));
        }
        if ($this->isValidParameter('gd_gdc_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('gd.gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        }
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('gd.gd_rel_id', $this->getIntParameter('rel_id'));
        }
        $wheres[] = '(j.gd_id IS NOT NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $unionAllQuery = [];
        $unionAllQuery[] = $this->getOriginGoodInboundQuery();
        $unionAllQuery[] = $this->getOriginDamageInboundQuery();
        $unionAllQuery[] = $this->getCurrentGoodInboundQuery();
        $unionAllQuery[] = $this->getCurrentDamageInboundQuery();

        $unionAllQuery[] = $this->getOriginGoodStagingQuery();
        $unionAllQuery[] = $this->getOriginDamageStagingQuery();
        $unionAllQuery[] = $this->getCurrentGoodStagingQuery();
        $unionAllQuery[] = $this->getCurrentDamageStagingQuery();

        $unionAllQuery[] = $this->getOriginGoodOutboundQuery();
        $unionAllQuery[] = $this->getOriginDamageOutboundQuery();
        $unionAllQuery[] = $this->getCurrentGoodOutboundQuery();
        $unionAllQuery[] = $this->getCurrentDamageOutboundQuery();

        $unionAllQuery[] = $this->getOriginMoveDamageToGoodQuery();
        $unionAllQuery[] = $this->getOriginMoveGoodToDamageQuery();
        $unionAllQuery[] = $this->getCurrentMoveDamageToGoodQuery();
        $unionAllQuery[] = $this->getCurrentMoveGoodToDamageQuery();

        $unionAllQuery[] = $this->getOriginGoodAdjustmentQuery();
        $unionAllQuery[] = $this->getOriginDamageAdjustmentQuery();
        $unionAllQuery[] = $this->getCurrentGoodAdjustmentQuery();
        $unionAllQuery[] = $this->getCurrentDamageAdjustmentQuery();

        $query = 'SELECT gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, rel.rel_short_name, j.gd_id as jo_gd_id, uom.uom_code,
                       SUM(in_gd_a * in_gd_a_gdu) as in_gd_a, SUM(in_gd_a * in_gd_a_vl) as in_gd_a_vl, SUM(in_gd_a * in_gd_a_wg) as in_gd_a_wg,
                       SUM(in_dm_a * in_dm_a_gdu) as in_dm_a, SUM(in_dm_a * in_dm_a_vl) as in_dm_a_vl, SUM(in_dm_a * in_dm_a_wg) as in_dm_a_wg,
                       SUM(in_gd_b * in_gd_b_gdu) as in_gd_b, SUM(in_gd_b * in_gd_b_vl) as in_gd_b_vl, SUM(in_gd_b * in_gd_b_wg) as in_gd_b_wg,
                       SUM(in_dm_b * in_dm_b_gdu) as in_dm_b, SUM(in_dm_b * in_dm_b_vl) as in_dm_b_vl, SUM(in_dm_b * in_dm_b_wg) as in_dm_b_wg,
                       SUM(sto_gd_a * sto_gd_a_gdu) as sto_gd_a, SUM(sto_gd_a * sto_gd_a_vl) as sto_gd_a_vl, SUM(sto_gd_a * sto_gd_a_wg) as sto_gd_a_wg,
                       SUM(sto_dm_a * sto_dm_a_gdu) as sto_dm_a, SUM(sto_dm_a * sto_dm_a_vl) as sto_dm_a_vl, SUM(sto_dm_a * sto_dm_a_wg) as sto_dm_a_wg,
                       SUM(sto_gd_b * sto_gd_b_gdu) as sto_gd_b, SUM(sto_gd_b * sto_gd_b_vl) as sto_gd_b_vl, SUM(sto_gd_b * sto_gd_b_wg) as sto_gd_b_wg,
                       SUM(sto_dm_b * sto_dm_b_gdu) as sto_dm_b, SUM(sto_dm_b * sto_dm_b_vl) as sto_dm_b_vl, SUM(sto_dm_b * sto_dm_b_wg) as sto_dm_b_wg,

                       SUM(out_gd_a * out_gd_a_gdu) as out_gd_a, SUM(out_gd_a * out_gd_a_vl) as out_gd_a_vl, SUM(out_gd_a * out_gd_a_wg) as out_gd_a_wg,
                       SUM(out_dm_a * out_dm_a_gdu) as out_dm_a, SUM(out_dm_a * out_dm_a_vl) as out_dm_a_vl, SUM(out_dm_a * out_dm_a_wg) as out_dm_a_wg,
                       SUM(out_gd_b * out_gd_b_gdu) as out_gd_b, SUM(out_gd_b * out_gd_b_vl) as out_gd_b_vl, SUM(out_gd_b * out_gd_b_wg) as out_gd_b_wg,
                       SUM(out_dm_b * out_dm_b_gdu) as out_dm_b, SUM(out_dm_b * out_dm_b_vl) as out_dm_b_vl, SUM(out_dm_b * out_dm_b_wg) as out_dm_b_wg,

                       SUM(out_gd_a_rt * out_gd_a_gdu) as rt_gd_a, SUM(out_gd_a_rt * out_gd_a_vl) as rt_gd_a_vl, SUM(out_gd_a_rt * out_gd_a_wg) as rt_gd_a_wg,
                       SUM(out_dm_a_rt * out_dm_a_gdu) as rt_dm_a, SUM(out_dm_a_rt * out_dm_a_vl) as rt_dm_a_vl, SUM(out_dm_a_rt * out_dm_a_wg) as rt_dm_a_wg,
                       SUM(out_gd_b_rt * out_gd_b_gdu) as rt_gd_b, SUM(out_gd_b_rt * out_gd_b_vl) as rt_gd_b_vl, SUM(out_gd_b_rt * out_gd_b_wg) as rt_gd_b_wg,
                       SUM(out_dm_b_rt * out_dm_b_gdu) as rt_dm_b, SUM(out_dm_b_rt * out_dm_b_vl) as rt_dm_b_vl, SUM(out_dm_b_rt * out_dm_b_wg) as rt_dm_b_wg,

                       SUM(mv_gd_a * mv_gd_a_gdu) as mv_gd_a, SUM(mv_gd_a * mv_gd_a_vl) as mv_gd_a_vl, SUM(mv_gd_a * mv_gd_a_wg) as mv_gd_a_wg,
                       SUM(mv_dm_a * mv_dm_a_gdu) as mv_dm_a, SUM(mv_dm_a * mv_dm_a_vl) as mv_dm_a_vl, SUM(mv_dm_a * mv_dm_a_wg) as mv_dm_a_wg,
                       SUM(mv_gd_b * mv_gd_b_gdu) as mv_gd_b, SUM(mv_gd_b * mv_gd_b_vl) as mv_gd_b_vl, SUM(mv_gd_b * mv_gd_b_wg) as mv_gd_b_wg,
                       SUM(mv_dm_b * mv_dm_b_gdu) as mv_dm_b, SUM(mv_dm_b * mv_dm_b_vl) as mv_dm_b_vl, SUM(mv_dm_b * mv_dm_b_wg) as mv_dm_b_wg,
                       SUM(ad_gd_a * ad_gd_a_gdu) as ad_gd_a, SUM(ad_gd_a * ad_gd_a_vl) as ad_gd_a_vl, SUM(ad_gd_a * ad_gd_a_wg) as ad_gd_a_wg,
                       SUM(ad_dm_a * ad_dm_a_gdu) as ad_dm_a, SUM(ad_dm_a * ad_dm_a_vl) as ad_dm_a_vl, SUM(ad_dm_a * ad_dm_a_wg) as ad_dm_a_wg,
                       SUM(ad_gd_b * ad_gd_b_gdu) as ad_gd_b, SUM(ad_gd_b * ad_gd_b_vl) as ad_gd_b_vl, SUM(ad_gd_b * ad_gd_b_wg) as ad_gd_b_wg,
                       SUM(ad_dm_b * ad_dm_b_gdu) as ad_dm_b, SUM(ad_dm_b * ad_dm_b_vl) as ad_dm_b_vl, SUM(ad_dm_b * ad_dm_b_wg) as ad_dm_b_wg
                FROM goods as gd INNER JOIN
                     brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                     goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                     unit as uom on uom.uom_id = gd.gd_uom_id INNER JOIN
                     relation as rel ON rel.rel_id = gd.gd_rel_id LEFT OUTER JOIN
                     (' . implode(' UNION ALL ', $unionAllQuery) . ') as j ON gd.gd_id = j.gd_id ';
        $query .= $strWhere;
        $query .= ' GROUP BY gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name, rel.rel_short_name, j.gd_id, uom.uom_code';
        $query .= ' ORDER BY rel.rel_short_name, br.br_name, gd.gd_sku, gdc.gdc_name, gd.gd_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getInboundConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jog.jog_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $wheres[] = '(ji.ji_deleted_on IS NULL)';
        $wheres[] = '(ji.ji_end_load_on IS NOT NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        $wheres[] = '(jir.jir_deleted_on IS NULL)';
        return $wheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginGoodInboundQuery(): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = '(jir.jir_gdt_id IS NULL)';
        $wheres[] = "(ji.ji_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jog.jog_gd_id as gd_id, SUM(jir.jir_quantity) as in_gd_a, gdu.gdu_qty_conversion as in_gd_a_gdu,
                     (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_gd_a_vl,
                     (CASE WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight ELSE jir.jir_weight END ) as in_gd_a_wg,
                     0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                     0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                     0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                     0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                     0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                     0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                     0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                     0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                     0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                     0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                     0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                     0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                     0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                     0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                     0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                     0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                     0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                     0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                     0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
              FROM job_inbound_receive as jir INNER JOIN
                   job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                   job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                   job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWheres . '
              GROUP BY jog.jog_gd_id, jir.jir_volume, jir.jir_weight, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginDamageInboundQuery(): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = '(jir.jir_gdt_id IS NOT NULL)';
        $wheres[] = "(ji.ji_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jog.jog_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       SUM(jir.jir_quantity) as in_dm_a, gdu.gdu_qty_conversion as in_dm_a_gdu,
                       (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_dm_a_vl,
                       (CASE WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight ELSE jir.jir_weight END ) as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
              FROM job_inbound_receive as jir INNER JOIN
                   job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                   job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                   job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWheres . '
              GROUP BY jog.jog_gd_id, jir.jir_volume, jir.jir_weight, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentGoodInboundQuery(): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = '(jir.jir_gdt_id IS NULL)';
        $wheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jog.jog_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       SUM(jir.jir_quantity) as in_gd_b, gdu.gdu_qty_conversion as in_gd_b_gdu,
                       (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_gd_b_vl,
                       (CASE WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight ELSE jir.jir_weight END ) as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
              FROM job_inbound_receive as jir INNER JOIN
                   job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                   job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                   job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWheres . '
              GROUP BY jog.jog_gd_id, jir.jir_volume, jir.jir_weight, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentDamageInboundQuery(): string
    {
        $wheres = $this->getInboundConditions();
        $wheres[] = '(jir.jir_gdt_id IS NOT NULL)';
        $wheres[] = "(ji.ji_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ji.ji_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jog.jog_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       SUM(jir.jir_quantity) as in_dm_b, gdu.gdu_qty_conversion as in_dm_b_gdu,
                       (CASE WHEN jir.jir_volume IS NULL THEN gdu.gdu_volume ELSE jir.jir_volume END) as in_dm_b_vl,
                       (CASE WHEN jir.jir_weight IS NULL THEN gdu.gdu_weight ELSE jir.jir_weight END ) as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
              FROM job_inbound_receive as jir INNER JOIN
                   job_goods as jog ON jir.jir_jog_id = jog.jog_id INNER JOIN
                   job_inbound as ji on jir.jir_ji_id = ji.ji_id INNER JOIN
                   goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                   job_order as jo ON jo.jo_id = ji.ji_jo_id ' . $strWheres . '
              GROUP BY jog.jog_gd_id, jir.jir_volume, jir.jir_weight, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getOutboundConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(job.job_deleted_on IS NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(job.job_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $wheres[] = '(jod.jod_jis_id IS NOT NULL)';
        return $wheres;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginGoodStagingQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(job.job_end_store_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                   0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                   0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                   0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                   SUM(jod.jod_quantity) as sto_gd_a, gdu.gdu_qty_conversion as sto_gd_a_gdu,
                   (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as sto_gd_a_vl,
                   (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as sto_gd_a_wg,
                   0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                   0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                   0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                   0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                   0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                   0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                   0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                   0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                   0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                   0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                   0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                   0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                   0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                   0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                   0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginDamageStagingQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(job.job_end_store_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       SUM(jod.jod_quantity) as sto_dm_a, gdu.gdu_qty_conversion as sto_dm_a_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as sto_dm_a_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentGoodStagingQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(job.job_end_store_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }

        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       SUM(jod.jod_quantity) as sto_gd_b, gdu.gdu_qty_conversion as sto_gd_b_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as sto_gd_b_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentDamageStagingQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_store_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(job.job_end_store_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(job.job_end_store_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       SUM(jod.jod_quantity) as sto_dm_b, gdu.gdu_qty_conversion as sto_dm_b_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as sto_dm_b_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginGoodOutboundQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_load_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(job.job_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       SUM(jod.jod_qty_loaded) as out_gd_a, SUM((jod.jod_quantity - jod.jod_qty_loaded)) as out_gd_a_rt, gdu.gdu_qty_conversion as out_gd_a_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_gd_a_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginDamageOutboundQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_load_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(job.job_end_load_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       SUM(jod.jod_qty_loaded) as out_dm_a, SUM((jod.jod_quantity - jod.jod_qty_loaded)) as out_dm_a_rt, gdu.gdu_qty_conversion as out_dm_a_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_dm_a_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentGoodOutboundQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_load_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(job.job_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       SUM(jod.jod_qty_loaded) as out_gd_b, SUM((jod.jod_quantity - jod.jod_qty_loaded)) as out_gd_b_rt, gdu.gdu_qty_conversion as out_gd_b_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_gd_b_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentDamageOutboundQuery(): string
    {
        $wheres = $this->getOutboundConditions();
        $wheres[] = '(job.job_end_load_on IS NOT NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(job.job_end_load_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(job.job_end_load_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jod.jod_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       SUM(jod.jod_qty_loaded) as out_dm_b, SUM((jod.jod_quantity - jod.jod_qty_loaded)) as out_dm_b_rt, gdu.gdu_qty_conversion as out_dm_b_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as out_dm_b_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_outbound_detail as jod INNER JOIN
                 job_outbound as job ON job.job_id = jod.jod_job_id INNER JOIN
                 job_order as jo on job.job_jo_id = jo.jo_id INNER JOIN
                 goods_unit as gdu on jod.jod_gdu_id = gdu.gdu_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ' . $strWheres . '
             GROUP BY jod.jod_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getMovementConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';

        $wheres[] = '(jm.jm_deleted_on IS NULL)';
        $wheres[] = '(jmd.jmd_deleted_on IS NULL)';
        $wheres[] = '(jm.jm_complete_on IS NOT NULL)';
        $wheres[] = '(jmd.jmd_jis_new_id IS NOT NULL)';
        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(jm.jm_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        return $wheres;
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginMoveDamageToGoodQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NULL)';
        $wheres[] = "(jm.jm_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                   0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                   0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                   0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                   0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                   0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                   0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                   0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                   0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                   0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                   0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                   0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                   SUM(jmd.jmd_quantity) as mv_gd_a, gdu.gdu_qty_conversion as mv_gd_a_gdu,
                   (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mv_gd_a_vl,
                   (CASE WHEN jmd.jmd_weight IS NULL THEN gdu.gdu_weight ELSE jmd.jmd_weight END ) as mv_gd_a_wg,
                   0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                   0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                   0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                   0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                   0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                   0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                   0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_movement_detail as jmd INNER JOIN
                 job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                 warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                 goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jmd.jmd_volume, jmd.jmd_weight, gdu.gdu_qty_conversion, gdu.gdu_volume, gdu.gdu_weight';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginMoveGoodToDamageQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NOT NULL)';
        $wheres[] = "(jm.jm_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                   0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                   0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                   0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                   0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                   0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                   0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                   0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                   0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                   0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                   0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                   0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                   0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                   SUM(jmd.jmd_quantity) as mv_dm_a, gdu.gdu_qty_conversion as mv_dm_a_gdu,
                   (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mv_dm_a_vl,
                   (CASE WHEN jmd.jmd_weight IS NULL THEN gdu.gdu_weight ELSE jmd.jmd_weight END ) as mv_dm_a_wg,
                   0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                   0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                   0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                   0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                   0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                   0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_movement_detail as jmd INNER JOIN
                 job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                 warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                 goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jmd.jmd_volume, jmd.jmd_weight, gdu.gdu_qty_conversion, gdu.gdu_volume, gdu.gdu_weight';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentMoveDamageToGoodQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NULL)';
        $wheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       SUM(jmd.jmd_quantity) as mv_gd_b, gdu.gdu_qty_conversion as mv_gd_b_gdu,
                       (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mv_gd_b_vl,
                       (CASE WHEN jmd.jmd_weight IS NULL THEN gdu.gdu_weight ELSE jmd.jmd_weight END ) as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_movement_detail as jmd INNER JOIN
                 job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                 warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                 goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jmd.jmd_volume, jmd.jmd_weight, gdu.gdu_qty_conversion, gdu.gdu_volume, gdu.gdu_weight';
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentMoveGoodToDamageQuery(): string
    {
        $wheres = $this->getMovementConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = '(jmd.jmd_gdt_id IS NOT NULL)';
        $wheres[] = "(jm.jm_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(jm.jm_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       SUM(jmd.jmd_quantity) as mv_dm_b, gdu.gdu_qty_conversion as mv_dm_b_gdu,
                       (CASE WHEN jmd.jmd_volume IS NULL THEN gdu.gdu_volume ELSE jmd.jmd_volume END) as mv_dm_b_vl,
                       (CASE WHEN jmd.jmd_weight IS NULL THEN gdu.gdu_weight ELSE jmd.jmd_weight END ) as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_movement_detail as jmd INNER JOIN
                 job_movement as jm ON jm.jm_id = jmd.jmd_jm_id INNER JOIN
                 warehouse as wh ON wh.wh_id = jm.jm_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = jm.jm_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id INNER JOIN
                 goods_unit as gdu ON jid.jid_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jmd.jmd_volume, jmd.jmd_weight, gdu.gdu_qty_conversion, gdu.gdu_volume, gdu.gdu_weight';
    }


    /**
     * Function to export data into excel file.
     *
     * @return array
     */
    private function getAdjustmentConditions(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        if ($this->isValidParameter('rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        $wheres[] = '(ja.ja_deleted_on IS NULL)';
        $wheres[] = '(ja.ja_complete_on IS NOT NULL)';
        $wheres[] = '(jad.jad_deleted_on IS NULL)';
        $wheres[] = '(jad.jad_jis_id IS NOT NULL)';

        if ($this->isValidParameter('wh_id') === true) {
            $wheres[] = '(ja.ja_wh_id = ' . $this->getIntParameter('wh_id') . ')';
        }
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(jid.jid_gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        return $wheres;
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginGoodAdjustmentQuery(): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(ja.ja_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                   0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                   0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                   0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                   0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                   0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                   0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                   0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                   0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                   0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                   0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                   0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                   0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                   0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                   0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                   0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                   SUM(jad.jad_quantity) as ad_gd_a, gdu.gdu_qty_conversion as ad_gd_a_gdu,
                   (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_gd_a_vl,
                   (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as ad_gd_a_wg,
                   0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                   0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                   0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getOriginDamageAdjustmentQuery(): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(ja.ja_complete_on < '" . $this->getStringParameter('from_date') . " 00:00:01')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       SUM(jad.jad_quantity) as ad_dm_a, gdu.gdu_qty_conversion as ad_dm_a_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_dm_a_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentGoodAdjustmentQuery(): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = '(jid.jid_gdt_id IS NULL)';
        $wheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                   0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                   0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                   0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                   0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                   0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                   0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                   0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                   0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                   0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                   0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                   0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                   0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                   0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                   0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                   0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                   0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                   0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                   SUM(jad.jad_quantity) as ad_gd_b, gdu.gdu_qty_conversion as ad_gd_b_gdu,
                   (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_gd_b_vl,
                   (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as ad_gd_b_wg,
                   0 as ad_dm_b, 0 as ad_dm_b_gdu, 0 as ad_dm_b_vl, 0 as ad_dm_b_wg
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }


    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCurrentDamageAdjustmentQuery(): string
    {
        $wheres = $this->getAdjustmentConditions();
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';
        $wheres[] = "(ja.ja_complete_on >= '" . $this->getStringParameter('from_date') . " 00:00:01')";
        if ($this->isValidParameter('until_date') === true) {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('until_date') . " 23:59:59')";
        } else {
            $wheres[] = "(ja.ja_complete_on <= '" . $this->getStringParameter('from_date') . " 23:59:59')";
        }
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT jid.jid_gd_id as gd_id, 0 as in_gd_a, 0 as in_gd_a_gdu, 0 as in_gd_a_vl, 0 as in_gd_a_wg,
                       0 as in_dm_a, 0 as in_dm_a_gdu, 0 as in_dm_a_vl, 0 as in_dm_a_wg,
                       0 as in_gd_b, 0 as in_gd_b_gdu, 0 as in_gd_b_vl, 0 as in_gd_b_wg,
                       0 as in_dm_b, 0 as in_dm_b_gdu, 0 as in_dm_b_vl, 0 as in_dm_b_wg,
                       0 as sto_gd_a, 0 as sto_gd_a_gdu, 0 as sto_gd_a_vl, 0 as sto_gd_a_wg,
                       0 as sto_dm_a, 0 as sto_dm_a_gdu, 0 as sto_dm_a_vl, 0 as sto_dm_a_wg,
                       0 as sto_gd_b, 0 as sto_gd_b_gdu, 0 as sto_gd_b_vl, 0 as sto_gd_b_wg,
                       0 as sto_dm_b, 0 as sto_dm_b_gdu, 0 as sto_dm_b_vl, 0 as sto_dm_b_wg,
                       0 as out_gd_a, 0 as out_gd_a_rt, 0 as out_gd_a_gdu, 0 as out_gd_a_vl, 0 as out_gd_a_wg,
                       0 as out_dm_a, 0 as out_dm_a_rt, 0 as out_dm_a_gdu, 0 as out_dm_a_vl, 0 as out_dm_a_wg,
                       0 as out_gd_b, 0 as out_gd_b_rt, 0 as out_gd_b_gdu, 0 as out_gd_b_vl, 0 as out_gd_b_wg,
                       0 as out_dm_b, 0 as out_dm_b_rt, 0 as out_dm_b_gdu, 0 as out_dm_b_vl, 0 as out_dm_b_wg,
                       0 as mv_gd_a, 0 as mv_gd_a_gdu, 0 as mv_gd_a_vl, 0 as mv_gd_a_wg,
                       0 as mv_dm_a, 0 as mv_dm_a_gdu, 0 as mv_dm_a_vl, 0 as mv_dm_a_wg,
                       0 as mv_gd_b, 0 as mv_gd_b_gdu, 0 as mv_gd_b_vl, 0 as mv_gd_b_wg,
                       0 as mv_dm_b, 0 as mv_dm_b_gdu, 0 as mv_dm_b_vl, 0 as mv_dm_b_wg,
                       0 as ad_gd_a, 0 as ad_gd_a_gdu, 0 as ad_gd_a_vl, 0 as ad_gd_a_wg,
                       0 as ad_dm_a, 0 as ad_dm_a_gdu, 0 as ad_dm_a_vl, 0 as ad_dm_a_wg,
                       0 as ad_gd_b, 0 as ad_gd_b_gdu, 0 as ad_gd_b_vl, 0 as ad_gd_b_wg,
                       SUM(jad.jad_quantity) as ad_dm_b, gdu.gdu_qty_conversion as ad_dm_b_gdu,
                       (CASE WHEN jid.jid_volume IS NULL THEN gdu.gdu_volume ELSE jid.jid_volume END) as ad_dm_b_vl,
                       (CASE WHEN jid.jid_weight IS NULL THEN gdu.gdu_weight ELSE jid.jid_weight END ) as ad_dm_b_wg
            FROM job_adjustment_detail as jad INNER JOIN
                 job_adjustment as ja ON ja.ja_id = jad.jad_ja_id INNER JOIN
                 warehouse as wh ON wh.wh_id = ja.ja_wh_id INNER JOIN
                 job_order as jo ON jo.jo_id = ja.ja_jo_id INNER JOIN
                 job_inbound_detail as jid ON jid.jid_id = jad.jad_jid_id INNER JOIN
                 goods_unit as gdu ON jad.jad_gdu_id = gdu.gdu_id ' . $strWheres . '
             GROUP BY jid.jid_gd_id, jid.jid_weight, jid.jid_volume, gdu.gdu_weight, gdu.gdu_volume, gdu.gdu_qty_conversion';
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        try {
            $excel = new Excel();
            foreach ($this->Datas as $key => $portlet) {
                if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                    $sheetName = StringFormatter::formatExcelSheetTitle(trim($key));
                    $excel->addSheet($sheetName, $sheetName);
                    $excel->setFileName($this->PageSetting->getPageDescription() . ' ' . $this->getDate() . '.xlsx');
                    $sheet = $excel->getSheet($sheetName, true);
                    $sheet->mergeCells('A1:E1');
                    $sheet->setCellValue('A1', Trans::getWord('stockReport'));
                    $sheet->getStyle('A1')->getFont()->setBold(true);

                    $sheet->mergeCells('A2:B2');
                    $sheet->setCellValue('A2', Trans::getWord('date'));
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->mergeCells('C2:E2');
                    $sheet->setCellValue('C2', $this->getDate());
                    $sheet->getStyle('C2')->getFont()->setBold(true);

                    $sheet->mergeCells('A3:B3');
                    $sheet->setCellValue('A3', Trans::getWord('warehouse'));
                    $sheet->getStyle('A3')->getFont()->setBold(true);
                    $sheet->mergeCells('C3:E3');
                    $sheet->setCellValue('C3', $this->getWarehouse());
                    $sheet->getStyle('C3')->getFont()->setBold(true);

                    $sheet->mergeCells('A4:B4');
                    $sheet->setCellValue('A4', Trans::getWord('relation'));
                    $sheet->getStyle('A4')->getFont()->setBold(true);
                    $sheet->mergeCells('C4:E4');
                    $sheet->setCellValue('C4', $this->getRelation());
                    $sheet->getStyle('C4')->getFont()->setBold(true);

                    $sheet->mergeCells('C5:E5');
                    $sheet->setCellValue('C5', $this->getGoodsCategory());
                    $sheet->getStyle('C5')->getFont()->setBold(true);
                    $sheet->mergeCells('A5:B5');
                    $sheet->setCellValue('A5', Trans::getWord('category'));
                    $sheet->getStyle('A5')->getFont()->setBold(true);

                    $sheet->mergeCells('C6:E6');
                    $sheet->setCellValue('C6', $this->getCondition());
                    $sheet->getStyle('C6')->getFont()->setBold(true);
                    $sheet->mergeCells('A6:B6');
                    $sheet->setCellValue('A6', Trans::getWord('condition'));
                    $sheet->getStyle('A6')->getFont()->setBold(true);
//                $sheet->freezePane('A6','A6');
                    $excel->doRowMovePointer($sheetName);
                    $excelTable = new ExcelTable($excel, $sheet);
                    $excelTable->setTable($portlet->Body[0]);
                    $excelTable->writeTable();
                    $excel->setActiveSheet($sheetName);
                }
            }
            $excel->createExcel();
        } catch (Exception $e) {
            $this->View->addErrorMessage('Failed to generate excel file.');
        }
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getDate(): string
    {
        $period = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd M Y');
        if ($this->isValidParameter('until_date') === true) {
            $period .= ' - ' . DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd M Y');
        }
        return $period;
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getWarehouse(): string
    {
        return $this->getStringParameter('warehouse', Trans::getWord('allWarehouse'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getRelation(): string
    {
        return $this->getStringParameter('rel_name', Trans::getWord('allRelation'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getGoodsCategory(): string
    {
        return $this->getStringParameter('gdc_name', Trans::getWord('allCategory'));
    }

    /**
     * Function to export data into excel file.
     *
     * @return string
     */
    private function getCondition(): string
    {
        $condition = $this->getStringParameter('gd_condition', 'A');
        if ($condition === 'G') {
            return Trans::getWord('good');
        }
        if ($condition === 'D') {
            return Trans::getWord('damage');
        }
        return Trans::getWord('allCondition');
    }
}
