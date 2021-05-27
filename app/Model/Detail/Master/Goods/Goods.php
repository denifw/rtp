<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\Master\Goods;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Document\FileUpload;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\CardImage;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Dao\Master\Goods\GoodsNumberHistoryDao;
use App\Model\Dao\Master\Goods\GoodsPrefixDao;
use App\Model\Dao\Master\Goods\GoodsUnitDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail Goods page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Goods extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'goods', 'gd_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $generateSn = $this->getStringParameter('gd_generate_sn', 'N');
        $multiSn = $this->getStringParameter('gd_multi_sn', 'N');
        $receiveSn = $this->getStringParameter('gd_receive_sn', 'N');
        if ($this->getStringParameter('gd_sn', 'N') === 'N') {
            $generateSn = 'N';
            $multiSn = 'N';
            $receiveSn = 'N';
        }
        $tonnageDm = $this->getStringParameter('gd_tonnage_dm', 'N');
        $minTonnage = $this->getFloatParameter('gd_min_tonnage');
        $maxTonnage = $this->getFloatParameter('gd_max_tonnage');
        if ($this->getStringParameter('gd_tonnage', 'N') === 'N') {
            $minTonnage = null;
            $maxTonnage = null;
        } else {
            $tonnageDm = 'Y';
        }
        $cbmDm = $this->getStringParameter('gd_cbm_dm', 'N');
        $minCbm = $this->getFloatParameter('gd_min_cbm');
        $maxCbm = $this->getFloatParameter('gd_max_cbm');
        if ($this->getStringParameter('gd_cbm', 'N') === 'N') {
            $minCbm = null;
            $maxCbm = null;
        } else {
            $cbmDm = 'Y';
        }
        $colVal = [
            'gd_ss_id' => $this->User->getSsId(),
            'gd_rel_id' => $this->getIntParameter('gd_rel_id'),
            'gd_gdc_id' => $this->getIntParameter('gd_gdc_id'),
            'gd_br_id' => $this->getIntParameter('gd_br_id'),
            'gd_sku' => $this->getStringParameter('gd_sku'),
            'gd_barcode' => $this->getStringParameter('gd_barcode', $this->getStringParameter('gd_sku')),
            'gd_name' => $this->getStringParameter('gd_name'),
            'gd_remark' => $this->getStringParameter('gd_remark'),
            'gd_uom_id' => $this->getIntParameter('gd_uom_id'),

            'gd_sn' => $this->getStringParameter('gd_sn', 'N'),
            'gd_generate_sn' => $generateSn,
            'gd_receive_sn' => $receiveSn,
            'gd_multi_sn' => $multiSn,

            'gd_warranty' => $this->getStringParameter('gd_warranty', 'N'),
            'gd_bundling' => $this->getStringParameter('gd_bundling', 'N'),
            'gd_packing' => $this->getStringParameter('gd_packing', 'N'),
            'gd_expired' => $this->getStringParameter('gd_expired', 'N'),

            'gd_tonnage' => $this->getStringParameter('gd_tonnage', 'N'),
            'gd_tonnage_dm' => $tonnageDm,
            'gd_min_tonnage' => $minTonnage,
            'gd_max_tonnage' => $maxTonnage,

            'gd_cbm' => $this->getStringParameter('gd_cbm', 'N'),
            'gd_cbm_dm' => $cbmDm,
            'gd_min_cbm' => $minCbm,
            'gd_max_cbm' => $maxCbm,

            'gd_active' => $this->getStringParameter('gd_active', 'Y'),
        ];
        $goodsDao = new GoodsDao();
        $goodsDao->doInsertTransaction($colVal);
        $volume = null;
        if (($this->isValidParameter('gd_length') === true) && ($this->isValidParameter('gd_width') === true) && ($this->isValidParameter('gd_height') === true)) {
            $volume = $this->getFloatParameter('gd_length') * $this->getFloatParameter('gd_width') * $this->getFloatParameter('gd_height');
        }
        $gduColVal = [
            'gdu_gd_id' => $goodsDao->getLastInsertId(),
            'gdu_quantity' => 1,
            'gdu_uom_id' => $this->getIntParameter('gd_uom_id'),
            'gdu_qty_conversion' => 1,
            'gdu_length' => $this->getFloatParameter('gd_length'),
            'gdu_width' => $this->getFloatParameter('gd_width'),
            'gdu_height' => $this->getFloatParameter('gd_height'),
            'gdu_volume' => $volume,
            'gdu_weight' => $this->getFloatParameter('gd_weight'),
            'gdu_active' => 'Y',
        ];
        $gduDao = new GoodsUnitDao();
        $gduDao->doInsertTransaction($gduColVal);
        $this->setParameter('gd_gdu_id', $gduDao->getLastInsertId());

        return $goodsDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateUnit') {
            $volume = null;
            if (($this->isValidParameter('gdu_length') === true) && ($this->isValidParameter('gdu_width') === true) && ($this->isValidParameter('gdu_height') === true)) {
                $volume = $this->getFloatParameter('gdu_length') * $this->getFloatParameter('gdu_width') * $this->getFloatParameter('gdu_height');
            }
            $gduColVal = [
                'gdu_gd_id' => $this->getDetailReferenceValue(),
                'gdu_quantity' => $this->getFloatParameter('gdu_quantity'),
                'gdu_uom_id' => $this->getIntParameter('gdu_uom_id'),
                'gdu_qty_conversion' => $this->getFloatParameter('gdu_qty_conversion'),
                'gdu_length' => $this->getFloatParameter('gdu_length'),
                'gdu_width' => $this->getFloatParameter('gdu_width'),
                'gdu_height' => $this->getFloatParameter('gdu_height'),
                'gdu_volume' => $volume,
                'gdu_weight' => $this->getFloatParameter('gdu_weight'),
                'gdu_active' => $this->getStringParameter('gdu_active', 'Y'),
            ];
            $gduDao = new GoodsUnitDao();
            if ($this->isValidParameter('gdu_id') === true) {
                $gduDao->doUpdateTransaction($this->getIntParameter('gdu_id'), $gduColVal);
            } else {
                $gduDao->doInsertTransaction($gduColVal);
            }
        } elseif ($this->getFormAction() === 'doInsertGoodsPrefix') {
            $gpfColVal = [
                'gpf_gd_id' => $this->getDetailReferenceValue(),
                'gpf_prefix' => $this->getStringParameter('gpf_prefix'),
                'gpf_yearly' => $this->getStringParameter('gpf_yearly', 'Y'),
                'gpf_monthly' => $this->getStringParameter('gpf_monthly', 'Y'),
                'gpf_length' => $this->getFloatParameter('gpf_length', 5),
            ];
            $gpfDao = new GoodsPrefixDao();
            if ($this->isValidParameter('gpf_id')) {
                $gpfDao->doUpdateTransaction($this->getIntParameter('gpf_id'), $gpfColVal);
            } else {
                $gpfDao->doInsertTransaction($gpfColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoodsPrefix') {
            $gpfDao = new GoodsPrefixDao();
            $gpfDao->doDeleteTransaction($this->getIntParameter('gpf_id_del'));
        } elseif ($this->getFormAction() === 'doDeleteGoodsMaterial') {
            $gmDao = new GoodsMaterialDao();
            $gmDao->doDeleteTransaction($this->getIntParameter('gm_id_del'));
        } elseif ($this->getFormAction() === 'doInsertGoodsMaterial') {
            $gmColVal = [
                'gm_gd_id' => $this->getDetailReferenceValue(),
                'gm_goods_id' => $this->getIntParameter('gm_goods_id'),
                'gm_quantity' => $this->getFloatParameter('gm_quantity'),
                'gm_gdu_id' => $this->getIntParameter('gm_gdu_id'),
            ];
            $gmDao = new GoodsMaterialDao();
            if ($this->isValidParameter('gm_id') === true) {
                $gmDao->doUpdateTransaction($this->getIntParameter('gm_id'), $gmColVal);
            } else {
                $gmDao->doInsertTransaction($gmColVal);
            }

        } elseif ($this->getFormAction() === 'doUploadImage') {
            # Upload Document.
            $file = $this->getFileParameter('gd_im_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('gd_im_dct'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('gd_im_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                if ($this->getStringParameter('gd_im_main', 'N') === 'Y') {
                    $gdDao = new GoodsDao();
                    $gdDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                        'gd_doc_id' => $docDao->getLastInsertId(),
                    ]);
                }
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteImage') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('gd_im_id_del'));
        } else {
            $generateSn = $this->getStringParameter('gd_generate_sn', 'N');
            $multiSn = $this->getStringParameter('gd_multi_sn', 'N');
            $receiveSn = $this->getStringParameter('gd_receive_sn', 'N');
            if ($this->getStringParameter('gd_sn', 'N') === 'N') {
                $generateSn = 'N';
                $multiSn = 'N';
                $receiveSn = 'N';
            }
            $tonnageDm = $this->getStringParameter('gd_tonnage_dm', 'N');
            $minTonnage = $this->getFloatParameter('gd_min_tonnage');
            $maxTonnage = $this->getFloatParameter('gd_max_tonnage');
            if ($this->getStringParameter('gd_tonnage', 'N') === 'N') {
                $minTonnage = null;
                $maxTonnage = null;
            } else {
                $tonnageDm = 'Y';
            }
            $cbmDm = $this->getStringParameter('gd_cbm_dm', 'N');
            $minCbm = $this->getFloatParameter('gd_min_cbm');
            $maxCbm = $this->getFloatParameter('gd_max_cbm');
            if ($this->getStringParameter('gd_cbm', 'N') === 'N') {
                $minCbm = null;
                $maxCbm = null;
            } else {
                $cbmDm = 'Y';
            }
            $colVal = [
                'gd_rel_id' => $this->getIntParameter('gd_rel_id'),
                'gd_gdc_id' => $this->getIntParameter('gd_gdc_id'),
                'gd_br_id' => $this->getIntParameter('gd_br_id'),
                'gd_sku' => $this->getStringParameter('gd_sku'),
                'gd_barcode' => $this->getStringParameter('gd_barcode', $this->getStringParameter('gd_sku')),
                'gd_name' => $this->getStringParameter('gd_name'),
                'gd_remark' => $this->getStringParameter('gd_remark'),
                'gd_uom_id' => $this->getIntParameter('gd_uom_id'),
                'gd_sn' => $this->getStringParameter('gd_sn', 'N'),
                'gd_generate_sn' => $generateSn,
                'gd_receive_sn' => $receiveSn,
                'gd_multi_sn' => $multiSn,

                'gd_warranty' => $this->getStringParameter('gd_warranty', 'N'),
                'gd_bundling' => $this->getStringParameter('gd_bundling', 'N'),
                'gd_packing' => $this->getStringParameter('gd_packing', 'N'),
                'gd_expired' => $this->getStringParameter('gd_expired', 'N'),

                'gd_tonnage' => $this->getStringParameter('gd_tonnage', 'N'),
                'gd_tonnage_dm' => $tonnageDm,
                'gd_min_tonnage' => $minTonnage,
                'gd_max_tonnage' => $maxTonnage,

                'gd_cbm' => $this->getStringParameter('gd_cbm', 'N'),
                'gd_cbm_dm' => $cbmDm,
                'gd_min_cbm' => $minCbm,
                'gd_max_cbm' => $maxCbm,
                'gd_active' => $this->getStringParameter('gd_active', 'Y'),
            ];
            $goodsDao = new GoodsDao();
            $goodsDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            if ($this->isValidParameter('gd_gdu_id') === true) {
                $volume = null;
                if (($this->isValidParameter('gd_length') === true) && ($this->isValidParameter('gd_width') === true) && ($this->isValidParameter('gd_height') === true)) {
                    $volume = $this->getFloatParameter('gd_length') * $this->getFloatParameter('gd_width') * $this->getFloatParameter('gd_height');
                }
                $gduColVal = [
                    'gdu_gd_id' => $this->getDetailReferenceValue(),
                    'gdu_quantity' => 1,
                    'gdu_uom_id' => $this->getIntParameter('gd_uom_id'),
                    'gdu_qty_conversion' => 1,
                    'gdu_length' => $this->getFloatParameter('gd_length'),
                    'gdu_width' => $this->getFloatParameter('gd_width'),
                    'gdu_height' => $this->getFloatParameter('gd_height'),
                    'gdu_volume' => $volume,
                    'gdu_weight' => $this->getFloatParameter('gd_weight'),
                    'gdu_active' => 'Y',
                ];
                $gduDao = new GoodsUnitDao();
                $gduDao->doUpdateTransaction($this->getIntParameter('gd_gdu_id'), $gduColVal);
            }
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return GoodsDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true && $this->isValidParameter('gd_rel_id') === true) {
            $relation = RelationDao::loadSimpleDataById($this->getIntParameter('gd_rel_id'));
            if (empty($relation) === false) {
                $this->setParameter('gd_relation', $relation['rel_name']);
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getRemarkPortlet());
        $this->Tab->addPortlet('general', $this->getConfigPortlet());
        $this->setEnableViewButton();
        if ($this->isUpdate()) {

            # Add prefix Table
            if ($this->getStringParameter('gd_sn', 'N') === 'Y') {
                $this->Tab->addPortlet('serialNumber', $this->getSerialPrefixFieldSet());
                $this->Tab->addPortlet('serialNumber', $this->getSerialHistoryPortlet());
            }
            if ($this->getStringParameter('gd_generate_sn', 'N') === 'Y') {
                # Form generate Serial number serial number
                $this->Tab->addPortlet('serialNumber', $this->getGenerateSerialNumberPortlet());
            }
            if ($this->getStringParameter('gd_packing', 'N') === 'Y') {
                # Form generate Packing number serial number
                $this->Tab->addPortlet('serialNumber', $this->getGeneratePackingNumberPortlet());
            }
            # add Material portlet
            if ($this->getStringParameter('gd_bundling', 'N') === 'Y') {
                $this->Tab->addPortlet('materials', $this->getMaterialsFieldSet());
            }
            $this->Tab->addPortlet('gallery', $this->getGalleryPortlet());

            if ($this->getFormAction() === 'doGeneratePnGoods' && $this->isValidPostValues() === true) {
                $this->doGeneratePnGoods();
            }
            if ($this->getFormAction() === 'doGenerateSnGoods' && $this->isValidPostValues() === true) {
                $this->doGenerateSnGoods();
            }
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateUnit') {
            $this->Validation->checkRequire('gdu_uom_id');
            $this->Validation->checkRequire('gdu_quantity');
            $this->Validation->checkRequire('gdu_qty_conversion');
            $this->Validation->checkFloat('gdu_qty_conversion', 1);
            $this->Validation->checkFloat('gdu_quantity', 1);
            $this->Validation->checkUnique('gdu_uom_id', 'goods_unit', [
                'gdu_id' => $this->getIntParameter('gdu_id'),
            ], [
                'gdu_gd_id' => $this->getDetailReferenceValue(),
            ]);
            if ($this->isValidParameter('gdu_length') === true) {
                $this->Validation->checkFloat('gdu_length');
            }
            if ($this->isValidParameter('gdu_width') === true) {
                $this->Validation->checkFloat('gdu_width');
            }
            if ($this->isValidParameter('gdu_height') === true) {
                $this->Validation->checkFloat('gdu_height');
            }
            if ($this->isValidParameter('gdu_weight') === true) {
                $this->Validation->checkFloat('gdu_weight');
            }
        } elseif ($this->getFormAction() === 'doInsertGoodsPrefix') {
            $this->Validation->checkRequire('gpf_prefix', 2, 2);
            $this->Validation->checkUnique('gpf_prefix', 'goods_prefix', [
                'gpf_id' => $this->getIntParameter('gpf_id'),
            ], [
                'gpf_gd_id' => $this->getDetailReferenceValue(),
                'gpf_deleted_on' => null,
            ]);
            if ($this->isValidParameter('gpf_length')) {
                $this->Validation->checkInt('gpf_length', 1);
            }
        } elseif ($this->getFormAction() === 'doDeleteGoodsPrefix') {
            $this->Validation->checkRequire('gpf_id_del');
        } elseif ($this->getFormAction() === 'doDeleteGoodsMaterial') {
            $this->Validation->checkRequire('gm_id_del');
        } elseif ($this->getFormAction() === 'doInsertGoodsMaterial') {
            $this->Validation->checkRequire('gm_goods_id');
            $this->Validation->checkRequire('gm_quantity');
            $this->Validation->checkFloat('gm_quantity', 0);
            $this->Validation->checkRequire('gm_gdu_id');
            $this->Validation->checkUnique('gm_goods_id', 'goods_material', [
                'gm_id' => $this->getIntParameter('gm_id'),
            ], [
                'gm_gd_id' => $this->getDetailReferenceValue(),
                'gm_deleted_on' => null,
            ]);
        } elseif ($this->getFormAction() === 'doUploadImage') {
            $this->Validation->checkMaxLength('gd_im_description', 255);
            $this->Validation->checkRequire('gd_im_dct');
            $this->Validation->checkRequire('gd_im_file');
            $this->Validation->checkImage('gd_im_file');
        } elseif ($this->getFormAction() === 'doDeleteImage') {
            $this->Validation->checkRequire('gd_im_id_del');
        } elseif ($this->getFormAction() === 'doGeneratePnGoods') {
            $this->Validation->checkRequire('gn_pn_quantity');
            $this->Validation->checkInt('gn_pn_quantity', 1);
        } elseif ($this->getFormAction() === 'doGenerateSnGoods') {
            $this->Validation->checkRequire('gn_sn_quantity');
            $this->Validation->checkInt('gn_sn_quantity', 1);
        } else {
            $this->Validation->checkRequire('gd_uom_id');
            $this->Validation->checkRequire('gd_rel_id');
            $this->Validation->checkRequire('gd_gdc_id');
            $this->Validation->checkRequire('gd_br_id');
            $this->Validation->checkRequire('gd_sku');
            $this->Validation->checkRequire('gd_name');
            $this->Validation->checkUnique('gd_sku', 'goods', [
                'gd_id' => $this->getDetailReferenceValue(),
            ], [
                'gd_ss_id' => $this->User->getSsId(),
            ]);
            if ($this->isValidParameter('gd_barcode') === true) {
                $this->Validation->checkUnique('gd_barcode', 'goods', [
                    'gd_id' => $this->getDetailReferenceValue(),
                ], [
                    'gd_ss_id' => $this->User->getSsId(),
                ]);
            }
            $this->Validation->checkRequire('gd_sn');
            $this->Validation->checkRequire('gd_multi_sn');
            $this->Validation->checkRequire('gd_receive_sn');
            $this->Validation->checkRequire('gd_generate_sn');
            $this->Validation->checkRequire('gd_bundling');
            $this->Validation->checkRequire('gd_packing');
            $this->Validation->checkRequire('gd_expired');
            $this->Validation->checkRequire('gd_warranty');
            $this->Validation->checkRequire('gd_tonnage');
            $this->Validation->checkRequire('gd_tonnage_dm');
            $this->Validation->checkRequire('gd_cbm');
            $this->Validation->checkRequire('gd_cbm_dm');
            if ($this->isValidParameter('gd_length') === true) {
                $this->Validation->checkFloat('gd_length');
            }
            if ($this->isValidParameter('gd_width') === true) {
                $this->Validation->checkFloat('gd_width');
            }
            if ($this->isValidParameter('gd_height') === true) {
                $this->Validation->checkFloat('gd_height');
            }
            if ($this->isValidParameter('gd_weight') === true) {
                $this->Validation->checkFloat('gd_weight');
            }
            if ($this->isValidParameter('gd_min_tonnage') === true) {
                $this->Validation->checkFloat('gd_min_tonnage');
            }
            if ($this->isValidParameter('gd_max_tonnage') === true) {
                $this->Validation->checkFloat('gd_max_tonnage');
            }
            if ($this->isValidParameter('gd_min_cbm') === true) {
                $this->Validation->checkFloat('gd_min_cbm');
            }
            if ($this->isValidParameter('gd_max_cbm') === true) {
                $this->Validation->checkFloat('gd_max_cbm');
            }
            $this->Validation->checkMaxLength('gd_remark', 255);
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getRemarkPortlet(): Portlet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getTextArea('gd_remark', $this->getStringParameter('gd_remark'), 6));

        $portlet = new Portlet('gdRmkPtl', Trans::getWord('description'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 12, 12);

        return $portlet;

    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralPortlet(): Portlet
    {
        $portlet = new Portlet('gdInsertPtl', Trans::getWord('general'));
        $portlet->addFieldSet($this->getGeneralFieldSet());
        $portlet->setGridDimension(8, 12, 12);

        return $portlet;

    }

    /**
     * Function to get the general Field Set.
     *
     * @return FieldSet
     */
    protected function getGeneralFieldSet(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field to field set
        $relationField = $this->Field->getSingleSelect('relation', 'gd_relation', $this->getStringParameter('gd_relation'));
        $relationField->setHiddenField('gd_rel_id', $this->getIntParameter('gd_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setDetailReferenceCode('rel_id');
        if ($this->isValidParameter('gd_rel_id') === true) {
            $relationField->setReadOnly();
            $relationField->setEnableDetailButton(false);
        }

        # Add field unit
        $unitField = $this->Field->getSingleSelect('unit', 'gd_uom_name', $this->getStringParameter('gd_uom_name'));
        $unitField->setHiddenField('gd_uom_id', $this->getIntParameter('gd_uom_id'));
        $unitField->setDetailReferenceCode('uom_id');
        $unitField->setEnableDetailButton(false);
        $unitField->setEnableNewButton(false);

        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gd_category', $this->getStringParameter('gd_category'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setDetailReferenceCode('gdc_id');

        $brandField = $this->Field->getSingleSelect('brand', 'gd_brand', $this->getStringParameter('gd_brand'));
        $brandField->setHiddenField('gd_br_id', $this->getIntParameter('gd_br_id'));
        $brandField->addParameter('br_ss_id', $this->User->getSsId());
        $brandField->setDetailReferenceCode('br_id');

        $fieldSet->addField(Trans::getWord('relation'), $relationField, true);
        $fieldSet->addField(Trans::getWord('brand'), $brandField, true);
        $fieldSet->addField(Trans::getWord('sku'), $this->Field->getText('gd_sku', $this->getStringParameter('gd_sku')), true);
        $fieldSet->addField(Trans::getWord('category'), $goodsCategoryField, true);
        $fieldSet->addField(Trans::getWord('barcode'), $this->Field->getText('gd_barcode', $this->getStringParameter('gd_barcode')));
        $fieldSet->addField(Trans::getWord('defaultUnit'), $unitField, true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('gd_name', $this->getStringParameter('gd_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('gd_active', $this->getStringParameter('gd_active')));

        return $fieldSet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getConfigPortlet(): Portlet
    {
        $portlet = new Portlet('gdCfgPtl', Trans::getWord('configuration'));
        $portlet->addFieldSet($this->getConfigFieldSet());

        return $portlet;

    }


    /**
     * Function to get the general Field Set.
     *
     * @return FieldSet
     */
    protected function getConfigFieldSet(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension();
        $fieldSet->addField(Trans::getWord('requireSn'), $this->Field->getYesNo('gd_sn', $this->getStringParameter('gd_sn')), true);
        $fieldSet->addField(Trans::getWord('multiSn'), $this->Field->getYesNo('gd_multi_sn', $this->getStringParameter('gd_multi_sn')), true);
        $fieldSet->addField(Trans::getWord('generateSn'), $this->Field->getYesNo('gd_generate_sn', $this->getStringParameter('gd_generate_sn')), true);
        $fieldSet->addField(Trans::getWord('requiredSnOnReceive'), $this->Field->getYesNo('gd_receive_sn', $this->getStringParameter('gd_receive_sn')), true);

        $fieldSet->addField(Trans::getWord('enablePacking'), $this->Field->getYesNo('gd_packing', $this->getStringParameter('gd_packing')), true);
        $fieldSet->addField(Trans::getWord('enableBundling'), $this->Field->getYesNo('gd_bundling', $this->getStringParameter('gd_bundling')), true);
        $fieldSet->addField(Trans::getWord('requireExpiredDate'), $this->Field->getYesNo('gd_expired', $this->getStringParameter('gd_expired')), true);
        $fieldSet->addField(Trans::getWord('warranty'), $this->Field->getYesNo('gd_warranty', $this->getStringParameter('gd_warranty')), true);

        $fieldSet->addField(Trans::getWord('requireWeight'), $this->Field->getYesNo('gd_tonnage', $this->getStringParameter('gd_tonnage')), true);
        $fieldSet->addField(Trans::getWord('requireWeightOnDamage'), $this->Field->getYesNo('gd_tonnage_dm', $this->getStringParameter('gd_tonnage_dm')), true);
        $fieldSet->addField(Trans::getWord('requireCbm'), $this->Field->getYesNo('gd_cbm', $this->getStringParameter('gd_cbm')), true);
        $fieldSet->addField(Trans::getWord('requireCbmOnDamage'), $this->Field->getYesNo('gd_cbm_dm', $this->getStringParameter('gd_cbm_dm')), true);

        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('gd_weight', $this->getFloatParameter('gd_weight')));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('gd_length', $this->getFloatParameter('gd_length')));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('gd_width', $this->getFloatParameter('gd_width')));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('gd_height', $this->getFloatParameter('gd_height')));

        $fieldSet->addField(Trans::getWord('minWeight') . ' (KG)', $this->Field->getNumber('gd_min_tonnage', $this->getFloatParameter('gd_min_tonnage')));
        $fieldSet->addField(Trans::getWord('maxWeight') . ' (KG)', $this->Field->getNumber('gd_max_tonnage', $this->getFloatParameter('gd_max_tonnage')));
        $fieldSet->addField(Trans::getWord('minCbm') . ' (M3)', $this->Field->getNumber('gd_min_cbm', $this->getFloatParameter('gd_min_cbm')));
        $fieldSet->addField(Trans::getWord('maxCbm') . ' (M3)', $this->Field->getNumber('gd_max_cbm', $this->getFloatParameter('gd_max_cbm')));
        $fieldSet->addHiddenField($this->Field->getHidden('gd_gdu_id', $this->getIntParameter('gd_gdu_id')));

        return $fieldSet;
    }
//
//    /**
//     * Function to get the contact Field Set.
//     *
//     * @return Portlet
//     */
//    protected function getUnitConversionFieldSet(): Portlet
//    {
//        $modal = $this->getUnitModal();
//        $this->View->addModal($modal);
//
//        $table = new Table('GdGduTbl');
//        $table->setHeaderRow([
//            'gdu_uom' => Trans::getWord('uom'),
//            'gdu_based_unit' => Trans::getWord('defaultUnit'),
//            'gdu_length' => Trans::getWord('length') . ' (M)',
//            'gdu_width' => Trans::getWord('width') . ' (M)',
//            'gdu_height' => Trans::getWord('height') . ' (M)',
//            'gdu_volume' => Trans::getWord('volume') . ' (M3)',
//            'gdu_weight' => Trans::getWord('weight') . ' (KG)',
//            'gdu_default' => Trans::getWord('default'),
//            'gdu_active' => Trans::getWord('active'),
//        ]);
//        $data = $this->loadGoodsUnit();
//        $table->addRows($data);
//        $table->setColumnType('gdu_default', 'yesno');
//        $table->setColumnType('gdu_active', 'yesno');
//        $table->setColumnType('gdu_length', 'float');
//        $table->setColumnType('gdu_width', 'float');
//        $table->setColumnType('gdu_height', 'float');
//        $table->setColumnType('gdu_volume', 'float');
//        $table->setColumnType('gdu_weight', 'float');
//        $table->addColumnAttribute('gdu_uom', 'style', 'text-align: right;');
//        $table->addColumnAttribute('gdu_based_unit', 'style', 'text-align: right;');
//        $table->setUpdateActionByModal($modal, 'goodsUnit', 'getByReference', ['gdu_id']);
//
//        # Create Portlet
//        $portlet = new Portlet('GdGduPtl', Trans::getWord('unitConversion'));
//        # Create button Add
//        if ($this->getStringParameter('gd_bundling', 'N') === 'N') {
//            # Create a portlet box.
//            $btnGduMdl = new ModalButton('btnGduMdl', Trans::getWord('addConversion'), $modal->getModalId());
//            $btnGduMdl->addAttribute('class', 'btn-primary pull-right');
//            $btnGduMdl->setIcon('fa fa-plus');
//            $portlet->addButton($btnGduMdl);
//        }
//
//        $portlet->addTable($table);
//
//        return $portlet;
//    }
//
//
//    /**
//     * Function to load goods unit data
//     *
//     * @return array
//     */
//    private function loadGoodsUnit(): array
//    {
//        $results = [];
//        $data = GoodsUnitDao::getByGoodsId($this->getDetailReferenceValue());
//        $defaultUom = $this->getIntParameter('gd_uom_id', 0);
//        $number = new NumberFormatter();
//        foreach ($data as $row) {
//            $row['gdu_based_unit'] = $number->doFormatFloat($row['gdu_qty_conversion']) . ' ' . $this->getStringParameter('gd_unit');
//            $row['gdu_uom'] = $number->doFormatFloat($row['gdu_quantity']) . ' ' . $row['gdu_uom'];
//            if ($defaultUom === (int)$row['gdu_uom_id']) {
//                $row['gdu_default'] = 'Y';
//            } else {
//                $row['gdu_default'] = 'N';
//            }
//            $results[] = $row;
//        }
//
//        return $results;
//    }
//
//    /**
//     * Function to get operator modal.
//     *
//     * @return Modal
//     */
//    private function getUnitModal(): Modal
//    {
//        # Create Fields.
//
//        $modal = new Modal('GdGduMdl', Trans::getWord('unitConversion'));
//        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateUnit');
//        $showModal = false;
//        if ($this->getFormAction() === 'doUpdateUnit' && $this->isValidPostValues() === false) {
//            $modal->setShowOnLoad();
//            $showModal = true;
//        }
//        $fieldSet = new FieldSet($this->Validation);
//        $fieldSet->setGridDimension(6, 6);
//
//        # Create Unit Field
//        $unitBasedField = $this->Field->getSingleSelect('unit', 'gdu_uom', $this->getParameterForModal('gdu_uom', $showModal));
//        $unitBasedField->setHiddenField('gdu_uom_id', $this->getParameterForModal('gdu_uom_id', $showModal));
//        $unitBasedField->setEnableNewButton(false);
//        $unitBasedField->setEnableDetailButton(false);
//        # Create Unit Field
//        $baseUnitField = $this->Field->getText('gdu_based_unit', $this->getParameterForModal('gd_unit', true));
//        $baseUnitField->setReadOnly();
//        $quantityField = $this->Field->getText('gdu_quantity', '1');
//        $quantityField->setReadOnly();
//
//        # Add field into field set.
//        $fieldSet->addField(Trans::getWord('basedQuantity'), $quantityField, true);
//        $fieldSet->addField(Trans::getWord('uom'), $unitBasedField, true);
//        $fieldSet->addField(Trans::getWord('quantityConversion'), $this->Field->getNumber('gdu_qty_conversion', $this->getParameterForModal('gdu_qty_conversion', $showModal)), true);
//        $fieldSet->addField(Trans::getWord('defaultUnit'), $baseUnitField, true);
//        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('gdu_length', $this->getParameterForModal('gdu_length', $showModal)));
//        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('gdu_width', $this->getParameterForModal('gdu_width', $showModal)));
//        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('gdu_height', $this->getParameterForModal('gdu_height', $showModal)));
//        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('gdu_weight', $this->getParameterForModal('gdu_weight', $showModal)));
//        if ($this->getStringParameter('gd_bundling', 'N') === 'Y') {
//            $fieldSet->addHiddenField($this->Field->getHidden('gd_active', $this->getParameterForModal('gd_active', $showModal)));
//        } else {
//            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('gdu_active', $this->getParameterForModal('gdu_active', $showModal)));
//        }
//        $fieldSet->addHiddenField($this->Field->getHidden('gdu_id', $this->getParameterForModal('gdu_id', $showModal)));
//        $modal->addFieldSet($fieldSet);
//
//        return $modal;
//    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getSerialPrefixFieldSet(): Portlet
    {
        $modal = $this->getSnPrefixModal();
        $this->View->addModal($modal);

        $mdlDelete = $this->getSnPrefixDeleteModal();
        $this->View->addModal($mdlDelete);

        $table = new Table('GdGpfTbl');
        $table->setHeaderRow([
            'gpf_prefix' => Trans::getWord('prefix'),
            'gpf_yearly' => Trans::getWord('yearly'),
            'gpf_monthly' => Trans::getWord('monthly'),
            'gpf_length' => Trans::getWord('length'),
        ]);
        $data = GoodsPrefixDao::getByGoodsId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('gpf_yearly', 'yesno');
        $table->setColumnType('gpf_monthly', 'yesno');
        $table->setColumnType('gpf_length', 'integer');
        $table->setUpdateActionByModal($modal, 'goodsPrefix', 'getByReference', ['gpf_id']);
        $table->setDeleteActionByModal($mdlDelete, 'goodsPrefix', 'getByReferenceForDelete', ['gpf_id']);
        # Create a portlet box.
        $portlet = new Portlet('GdGpfPtl', Trans::getWord('serialNumberPrefix'));
        $btnGduMdl = new ModalButton('btnGpfMdl', Trans::getWord('addPrefix'), $modal->getModalId());
        $btnGduMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnGduMdl);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getSnPrefixModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('GdGpfMdl', Trans::getWord('serialNumberPrefix'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertGoodsPrefix');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertGoodsPrefix' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('prefix'), $this->Field->getText('gpf_prefix', $this->getParameterForModal('gpf_prefix', $showModal)), true);
        $fieldSet->addField(Trans::getWord('length'), $this->Field->getNumber('gpf_length', $this->getParameterForModal('gpf_length', $showModal)));
        $fieldSet->addField(Trans::getWord('yearly'), $this->Field->getYesNo('gpf_yearly', $this->getParameterForModal('gpf_yearly', $showModal)));
        $fieldSet->addField(Trans::getWord('monthly'), $this->Field->getYesNo('gpf_monthly', $this->getParameterForModal('gpf_monthly', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('gpf_id', $this->getParameterForModal('gpf_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getSnPrefixDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('GdGpfDelMdl', Trans::getWord('deleteSerialNumberPrefix'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteGoodsPrefix');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteGoodsPrefix' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('prefix'), $this->Field->getText('gpf_prefix_del', $this->getParameterForModal('gpf_prefix_del', $showModal)));
        $fieldSet->addField(Trans::getWord('length'), $this->Field->getNumber('gpf_length_del', $this->getParameterForModal('gpf_length_del', $showModal)));
        $fieldSet->addField(Trans::getWord('yearly'), $this->Field->getYesNo('gpf_yearly_del', $this->getParameterForModal('gpf_yearly_del', $showModal)));
        $fieldSet->addField(Trans::getWord('monthly'), $this->Field->getYesNo('gpf_monthly_del', $this->getParameterForModal('gpf_monthly_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('gpf_id_del', $this->getParameterForModal('gpf_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getMaterialsFieldSet(): Portlet
    {
        $modal = $this->getMaterialsModal();
        $this->View->addModal($modal);

        $mdlDelete = $this->getMaterialsDeleteModal();
        $this->View->addModal($mdlDelete);

        # Create a portlet box.
        $portlet = new Portlet('GdGpfPtl', Trans::getWord('billOfMaterials'));

        # Create table object.
        $table = new Table('GdGmTbl');
        $table->setHeaderRow([
            'gm_gd_sku' => Trans::getWord('sku'),
            'gm_br_name' => Trans::getWord('brand'),
            'gm_gdc_name' => Trans::getWord('category'),
            'gm_gd_name' => Trans::getWord('goods'),
            'gm_quantity' => Trans::getWord('quantity'),
            'gm_uom_code' => Trans::getWord('uom'),
        ]);
        $data = GoodsMaterialDao::getByGdId($this->getDetailReferenceValue());
        $table->addColumnAttribute('gm_gd_sku', 'style', 'text-align: center;');
        $table->setColumnType('gm_quantity', 'float');
        $table->addRows($data);
        $table->setUpdateActionByModal($modal, 'goodsMaterial', 'getByReference', ['gm_id']);
        $table->setDeleteActionByModal($mdlDelete, 'goodsMaterial', 'getByReferenceForDelete', ['gm_id']);
        $btnGmMdl = new ModalButton('btnGmMdl', Trans::getWord('addMaterial'), $modal->getModalId());
        $btnGmMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnGmMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getMaterialsModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('GdGmMdl', Trans::getWord('addMaterial'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertGoodsMaterial');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertGoodsMaterial' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        # Create Field
        $goodsField = $this->Field->getSingleSelectTable('goods', 'gm_gd_name', $this->getParameterForModal('gm_gd_name', $showModal), 'loadSingleSelectTableData');
        $goodsField->setHiddenField('gm_goods_id', $this->getParameterForModal('gm_goods_id'));
        $goodsField->setTableColumns([
            'gd_sku' => Trans::getWord('sku'),
            'gd_br_name' => Trans::getWord('brand'),
            'gd_gdc_name' => Trans::getWord('category'),
            'gd_name' => Trans::getWord('goods'),
        ]);
        $goodsField->setFilters([
            'gd_sku' => Trans::getWord('sku'),
            'br_name' => Trans::getWord('brand'),
            'gdc_name' => Trans::getWord('category'),
            'gd_name' => Trans::getWord('goods'),
        ]);
        $goodsField->setAutoCompleteFields([
            'gm_gd_sku' => 'gd_sku',
            'gm_br_name' => 'gd_br_name',
            'gm_gdc_name' => 'gd_gdc_name',
        ]);
        $goodsField->setValueCode('gd_id');
        $goodsField->setLabelCode('gd_name');
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameter('gd_ignore_id', $this->getDetailReferenceValue());
        $goodsField->addParameterById('gd_rel_id', 'gd_rel_id', Trans::getWord('relation'));
        $goodsField->setParentModal($modal->getModalId());
        $this->View->addModal($goodsField->getModal());

        # Create Sku Field
        $skuField = $this->Field->getText('gm_gd_sku', $this->getParameterForModal('gm_gd_sku', $showModal));
        $skuField->setReadOnly();
        # Brand Field
        $brandField = $this->Field->getText('gm_br_name', $this->getParameterForModal('gm_br_name', $showModal));
        $brandField->setReadOnly();
        # Category field
        $categoryField = $this->Field->getText('gm_gdc_name', $this->getParameterForModal('gm_gdc_name', $showModal));
        $categoryField->setReadOnly();
        #uomField
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'gm_uom_code', $this->getParameterForModal('gm_uom_code', $showModal));
        $unitField->setHiddenField('gm_gdu_id', $this->getParameterForModal('gm_gdu_id', $showModal));
        $unitField->addParameterById('gdu_gd_id', 'gm_goods_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('brand'), $brandField);
        $fieldSet->addField(Trans::getWord('category'), $categoryField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('gm_quantity', $this->getParameterForModal('gm_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('gm_id', $this->getParameterForModal('gm_id')));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getMaterialsDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('GdGmDelMdl', Trans::getWord('deleteMaterial'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteGoodsMaterial');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteGoodsMaterial' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $this->Field->getText('gm_gd_sku_del', $this->getParameterForModal('gm_gd_sku_del', $showModal)));
        $fieldSet->addField(Trans::getWord('brand'), $this->Field->getText('gm_br_name_del', $this->getParameterForModal('gm_br_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('gm_gd_name_del', $this->getParameterForModal('gm_gd_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('category'), $this->Field->getText('gm_gdc_name_del', $this->getParameterForModal('gm_gdc_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('gm_quantity_del', $this->getParameterForModal('gm_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('gm_uom_code_del', $this->getParameterForModal('gm_uom_code_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('gm_id_del', $this->getParameterForModal('gm_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        parent::loadDefaultButton();
        if ($this->isValidParameter('gd_barcode')) {
            $barcodeButton = new PdfButton('GdBrcdPrt', Trans::getWord('printBarcode'), 'goodsbarcode');
            $barcodeButton->setIcon(Icon::Print)->btnPrimary()->pullRight()->btnMedium();
            $barcodeButton->addParameter('gd_ss_id', $this->User->getSsId());
            $barcodeButton->addParameter('gd_id', $this->getDetailReferenceValue());
            $this->View->addButton($barcodeButton);
        }

    }


    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getGalleryPortlet(): Portlet
    {
        $portlet = new Portlet('GdGlrPtl', Trans::getWord('gallery'));
        $imUploadModal = $this->getGalleryModal();
        $this->View->addModal($imUploadModal);
        $imDeleteModal = $this->getGalleryDeleteModal();
        $this->View->addModal($imDeleteModal);

        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'goods')";
        $wheres[] = "(dct.dct_code = 'image')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $docDao = new DocumentDao();
        $i = 0;
        foreach ($data as $row) {
            $i++;
            $path = $docDao->getDocumentPath($row);
            $ca = new CardImage('GdIm' . $i);
            $ca->setHeight(200);
            $btns = [];
            $btn = new Button('BtnIm' . $i, Trans::getWord('view'));
            $btn->setIcon(Icon::Eye)->btnPrimary()->btnSmall();
            $btn->addAttribute('onclick', "App.popup('" . $path . "')");
            $btns[] = $btn;
            $btnDel = new ModalButton('BtnDel' . $i, Trans::getWord('delete'), $imDeleteModal->getModalId());
            $btnDel->setIcon(Icon::Trash)->btnDanger()->btnSmall();
            $btnDel->addParameter('doc_id', $row['doc_id']);
            $btnDel->setEnableCallBack('document', 'getGoodsImageForDelete');
            $btns[] = $btnDel;
            $ca->setData([
                'title' => '&nbsp;',
                'subtitle' => $row['doc_description'],
                'img_path' => $path,
                'buttons' => $btns,
            ]);
            $portlet->addText($ca->createView());
        }
        $btnDocMdl = new ModalButton('btnGlrMdl', Trans::getWord('upload'), $imUploadModal->getModalId());
        $btnDocMdl->setIcon(Icon::Plus)->pullRight()->pullRight();
        $portlet->addButton($btnDocMdl);

        return $portlet;
    }


    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getGalleryModal(): Modal
    {
        $dct = DocumentTypeDao::getByCode('goods', 'image');
        $modal = new Modal('GdGlrMdl', Trans::getWord('image'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadImage');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadImage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('image'), $this->Field->getFile('gd_im_file', ''), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('gd_im_description', $this->getParameterForModal('gd_im_description', $showModal)));
        $fieldSet->addField(Trans::getWord('mainImage'), $this->Field->getYesNo('gd_im_main', $this->getParameterForModal('gd_im_main', $showModal)), true);
        if (empty($dct) === false) {
            $fieldSet->addHiddenField($this->Field->getHidden('gd_im_dct', $dct['dct_id']));
        } else {
            $fieldSet->addHiddenField($this->Field->getHidden('gd_im_dct'));
        }
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getGalleryDeleteModal(): Modal
    {
        $modal = new Modal('GdGlrDelMdl', Trans::getWord('deleteImage'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteImage');

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('gd_im_description_del', $this->getParameterForModal('gd_im_description_del')));
        $fieldSet->addField(Trans::getWord('fileName'), $this->Field->getText('gd_im_name_del', $this->getParameterForModal('gd_im_name_del')));
        $fieldSet->addHiddenField($this->Field->getHidden('gd_im_id_del', $this->getParameterForModal('gd_im_id_del')));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getSerialHistoryPortlet(): Portlet
    {
        $table = new Table('GdGnhTbl');
        $table->setHeaderRow([
            'gnh_prefix' => Trans::getWord('prefix'),
            'gnh_year' => Trans::getWord('year'),
            'gnh_month' => Trans::getWord('month'),
            'gnh_number' => Trans::getWord('lastNumber'),
        ]);
        $data = GoodsNumberHistoryDao::getByGoodsId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('gnh_year', 'integer');
        $table->setColumnType('gnh_month', 'integer');
        $table->setColumnType('gnh_number', 'integer');
        # Create a portlet box.
        $portlet = new Portlet('GdGnhPtl', Trans::getWord('serialNumberHistory'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the form generate serial number portlet.
     *
     * @return Portlet
     */
    private function getGenerateSerialNumberPortlet(): Portlet
    {
        $portlet = new Portlet('GnSerialNumberPtl', Trans::getWord('generateSerialNumber'));
        $portlet->setGridDimension(6, 6);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Create single select good receive
        $goodField = $this->Field->getText('gn_sn_gd_name', $this->getStringParameter('gd_name'));
        $goodField->setReadOnly();
        $qtyField = $this->Field->getNumber('gn_sn_quantity', $this->getIntParameter('gn_sn_quantity'));
        $fieldSet->addField(Trans::getWord('goods'), $goodField, true);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField, true);
        # Create button export excel packing number
        $btnXls = new SubmitButton('btnGdSnXls', Trans::getWord('generateSerialNumber'), 'doGenerateSnGoods', $this->getMainFormId());
        $btnXls->setIcon(Icon::Download)->btnDark()->btnMedium()->center();
        $btnXls->setEnableLoading(false);
        $portlet->addFieldSet($fieldSet);
        $test = '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="text-align: center;">' . $btnXls . '</div>';
        $portlet->addText($test);

        return $portlet;
    }

    /**
     * Function to generate goods serial number.
     *
     * @return void
     */
    private function doGenerateSnGoods(): void
    {
        $tbl = null;
        DB::beginTransaction();
        try {
            $tbl = $this->generateTableSnGoods();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
        }
        if ($tbl !== null) {
            $excel = new Excel();
            $sheetName = StringFormatter::formatExcelSheetTitle($this->getStringParameter('gn_sn_gd_name'));
            $excel->addSheet($sheetName, $sheetName);
            $excel->setFileName('SN - ' . $this->getStringParameter('gd_sku') . ' - ' . $this->getStringParameter('gn_sn_gd_name') . '.xlsx');
            $sheet = $excel->getSheet($sheetName, true);
            $excelTable = new ExcelTable($excel, $sheet);
            $excelTable->setTable($tbl);
            $excelTable->writeTable();
            $excel->setActiveSheet($sheetName);
            $excel->createExcel();
        }

    }

    /**
     * Get query movement
     *
     * @return Table
     */
    private function generateTableSnGoods(): Table
    {
        $tbl = new Table('JoGnSnTbl');
        $tbl->setHeaderRow(
            [
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_name' => Trans::getWord('name'),
                'gd_serial_number' => Trans::getWord('serialNumber'),
            ]
        );
        $tbl->addRows(GoodsDao::generateSnGoodsData($this->getDetailReferenceValue(), $this->getIntParameter('gn_sn_quantity')));

        return $tbl;
    }

    /**
     * Function to get the form generate packing number portlet.
     *
     * @return Portlet
     */
    private function getGeneratePackingNumberPortlet(): Portlet
    {
        $portlet = new Portlet('GnPackingNumberPtl', Trans::getWord('generatePackingNumber'));
        $portlet->setGridDimension(6, 6);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Create single select good receive
        $goodField = $this->Field->getText('gn_pn_gd_name', $this->getStringParameter('gd_name'));
        $goodField->setReadOnly();
        $qtyField = $this->Field->getNumber('gn_pn_quantity', $this->getIntParameter('gn_pn_quantity'));
        $fieldSet->addField(Trans::getWord('goods'), $goodField, true);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField, true);
        # Create button export excel packing number
        $btnXls = new SubmitButton('btnGdPnXls', Trans::getWord('generatePackingNumber'), 'doGeneratePnGoods', $this->getMainFormId());
        $btnXls->setIcon(Icon::Download)->btnDark()->btnMedium()->center();
        $btnXls->setEnableLoading(false);
        $portlet->addFieldSet($fieldSet);
        $test = '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="text-align: center;">' . $btnXls . '</div>';
        $portlet->addText($test);

        return $portlet;
    }

    /**
     * Function to generate goods packing number.
     *
     * @return void
     */
    private function doGeneratePnGoods(): void
    {
        $tbl = null;
        DB::beginTransaction();
        try {
            $tbl = $this->generateTablePnGoods();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
        }
        if ($tbl !== null) {
            $excel = new Excel();
            $sheetName = StringFormatter::formatExcelSheetTitle($this->getStringParameter('gn_pn_gd_name'));
            $excel->addSheet($sheetName, $sheetName);
            $excel->setFileName('PN - ' . $this->getStringParameter('gd_sku') . ' - ' . $this->getStringParameter('gn_pn_gd_name') . '.xlsx');
            $sheet = $excel->getSheet($sheetName, true);
            $excelTable = new ExcelTable($excel, $sheet);
            $excelTable->setTable($tbl);
            $excelTable->writeTable();
            $excel->setActiveSheet($sheetName);
            $excel->createExcel();
        }
    }

    /**
     * Get query movement
     *
     * @return Table
     */
    private function generateTablePnGoods(): Table
    {
        $tbl = new Table('JoGnPnTbl');
        $tbl->setHeaderRow(
            [
                'gd_relation' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_name' => Trans::getWord('name'),
                'gd_packing_number' => Trans::getWord('packingNumber'),
            ]
        );
        $tbl->addRows(GoodsDao::generatePnGoodsData($this->getDetailReferenceValue(), $this->getIntParameter('gn_pn_quantity')));

        return $tbl;
    }

}
