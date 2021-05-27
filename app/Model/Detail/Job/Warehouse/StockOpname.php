<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job\Warehouse;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\StockOpnameDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail StockOpname page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockOpname extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhOpname', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('JobOrder', $this->User->Relation->getOfficeId(), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
        $joColVal = [
            'jo_number' => $number,
            'jo_rel_id' => $this->getIntParameter('jo_rel_id'),
            'jo_customer_ref' => $this->getStringParameter('jo_customer_ref'),
            'jo_pic_id' => $this->getIntParameter('jo_pic_id'),
            'jo_ss_id' => $this->User->getSsId(),
            'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
            'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
            'jo_order_date' => date('Y-m-d'),
            'jo_order_of_id' => $this->User->Relation->getOfficeId(),
            'jo_manager_id' => $this->getIntParameter('jo_manager_id'),
        ];
        $jobDao = new JobOrderDao();
        $jobDao->doInsertTransaction($joColVal);

        $joId = $jobDao->getLastInsertId();
        $actions = SystemActionDao::getByServiceTermIdAndSystemId($this->getIntParameter('jo_srt_id'), $this->User->getSsId());
        $jacDao = new JobActionDao();
        $i = 1;
        foreach ($actions as $row) {
            $jacColVal = [
                'jac_jo_id' => $joId,
                'jac_ac_id' => $row['sac_ac_id'],
                'jac_order' => $i,
                'jac_active' => 'Y',
            ];
            $jacDao->doInsertTransaction($jacColVal);
            $i++;
        }
        $sopColVal = [
            'sop_jo_id' => $joId,
            'sop_wh_id' => $this->getIntParameter('sop_wh_id'),
            'sop_date' => $this->getStringParameter('sop_date'),
            'sop_time' => $this->getStringParameter('sop_time'),
            'sop_gd_id' => $this->getIntParameter('sop_gd_id'),
        ];
        $sopDao = new StockOpnameDao();
        $sopDao->doInsertTransaction($sopColVal);


        return $joId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $joColVal = [
                'jo_rel_id' => $this->getIntParameter('jo_rel_id'),
                'jo_customer_ref' => $this->getStringParameter('jo_customer_ref'),
                'jo_pic_id' => $this->getIntParameter('jo_pic_id'),
                'jo_manager_id' => $this->getIntParameter('jo_manager_id'),
            ];
            $jobDao = new JobOrderDao();
            $jobDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            $sopColVal = [
                'sop_jo_id' => $this->getDetailReferenceValue(),
                'sop_wh_id' => $this->getIntParameter('sop_wh_id'),
                'sop_date' => $this->getStringParameter('sop_date'),
                'sop_time' => $this->getStringParameter('sop_time'),
                'sop_gd_id' => $this->getIntParameter('sop_gd_id'),
            ];
            $sopDao = new StockOpnameDao();
            $sopDao->doUpdateTransaction($this->getIntParameter('sop_id'), $sopColVal);
        } else {
            parent::doUpdate();
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        $data = StockOpnameDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
        if (empty($data) === false) {
            $gdDao = new GoodsDao();
            $data['sop_goods'] = $gdDao->formatFullName($data['sop_gd_category'], $data['sop_gd_brand'], $data['sop_gd_name'], $data['sop_gd_sku']);
        }
        return $data;
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setSopHiddenData();

        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            if ($this->isValidParameter('sop_start_on') === true) {
                $this->Tab->addPortlet('general', $this->getStorageFieldSet());
            }
            # include default portlet
            $this->includeAllDefaultPortlet();

        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('jo_rel_id');
            $this->Validation->checkMaxLength('jo_customer_ref', 255);
            $this->Validation->checkRequire('jo_manager_id');
            $this->Validation->checkRequire('sop_wh_id');
            $this->Validation->checkRequire('sop_date');
            $this->Validation->checkRequire('sop_time');
            $this->Validation->checkDate('sop_date');
            $this->Validation->checkTime('sop_time');
            $this->Validation->checkRequire('sop_gd_id');
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('sop_id');
            }
        }
        if ($this->getFormAction() !== null) {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->isUpdate() === true) {
            $relField->setReadOnly();
        }

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'jo_pic_customer', $this->getStringParameter('jo_pic_customer'));
        $picField->setHiddenField('jo_pic_id', $this->getIntParameter('jo_pic_id'));
        $picField->addParameterById('cp_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $picField->setDetailReferenceCode('cp_id');

        # Create order Office Field
        $ofOrderField = $this->Field->getSingleSelect('office', 'jo_order_of', $this->getStringParameter('jo_order_of'));
        $ofOrderField->setHiddenField('jo_order_of_id', $this->getIntParameter('jo_order_of_id'));
        $ofOrderField->addParameter('of_rel_id', $this->User->getRelId());
        $ofOrderField->setEnableDetailButton(false);
        $ofOrderField->setEnableNewButton(false);
        $ofOrderField->addClearField('jo_manager');
        $ofOrderField->addClearField('jo_manager_id');

        # Create invoice Office Field
        $ofInvoiceField = $this->Field->getSingleSelect('office', 'jo_invoice_of', $this->getStringParameter('jo_invoice_of'));
        $ofInvoiceField->setHiddenField('jo_invoice_of_id', $this->getIntParameter('jo_invoice_of_id'));
        $ofInvoiceField->addParameter('of_rel_id', $this->User->getRelId());
        $ofInvoiceField->addParameter('of_invoice', 'Y');
        $ofInvoiceField->setEnableDetailButton(false);
        $ofInvoiceField->setEnableNewButton(false);

        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);

        $whField = $this->Field->getSingleSelect('warehouse', 'sop_warehouse', $this->getStringParameter('sop_warehouse'));
        $whField->setHiddenField('sop_wh_id', $this->getIntParameter('sop_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $goodsField = $this->Field->getSingleSelect('goods', 'sop_goods', $this->getStringParameter('sop_goods'), 'loadCompleteGoodsSingleSelect');
        $goodsField->setHiddenField('sop_gd_id', $this->getIntParameter('sop_gd_id'));
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsField->setEnableNewButton(false);

        if ($this->isValidParameter('jo_start_on') === true) {
            $whField->setReadOnly();
            $goodsField->setReadOnly();
        }

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('customerRef'), $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref')));
        $fieldSet->addField(Trans::getWord('picCustomer'), $picField);
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('planningDate'), $this->Field->getCalendar('sop_date', $this->getStringParameter('sop_date')), true);
        $fieldSet->addField(Trans::getWord('planningTime'), $this->Field->getTime('sop_time', $this->getStringParameter('sop_time')), true);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField, true);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getJoPublishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoPubMdl', Trans::getWord('publishConfirmation'));
        $text = Trans::getWord('publishJobConfirmation', 'message');
        $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
        $modal->setBtnOkName(Trans::getWord('yesPublish'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getStorageFieldSet(): Portlet
    {
        $table = new Table('JoSodTbl');
        $table->setHeaderRow([
            'sod_whs_name' => Trans::getWord('storage'),
            'sod_gd_sku' => Trans::getWord('sku'),
            'sod_goods' => Trans::getWord('goods'),
            'sod_production_number' => Trans::getWord('productionNumber'),
            'sod_serial_number' => Trans::getWord('serialNumber'),
            'sod_gdt_code' => Trans::getWord('damageType'),
            'sod_quantity' => Trans::getWord('currentStock'),
            'sod_gdu_uom' => Trans::getWord('uom'),
            'sod_qty_figure' => Trans::getWord('stockFigure'),
            'qty_diff' => Trans::getWord('diffQuantity'),
            'sod_remark' => Trans::getWord('remark'),
        ]);
        $data = StockOpnameDetailDao::getByStockOpnameId($this->getIntParameter('sop_id'), 200);
        $results = [];
        $i = 0;
        $gdDao = new GoodsDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['sod_goods'] = $gdDao->formatFullName($row['sod_gd_category'], $row['sod_gd_brand'], $row['sod_gd_name']);
            $diff = (float)$row['sod_qty_figure'] - (float)$row['sod_quantity'];
            $row['qty_diff'] = $number->doFormatFloat($diff);
            if ($diff > 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: orange; color: white; text-align: right;');
            } else if ($diff < 0) {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: red; color: white; text-align: right;');
            } else {
                $table->addCellAttribute('qty_diff', $i, 'style', 'background-color: green; color: white; text-align: right;');
            }
            if ($row['sod_qty_figure'] === null) {
                $table->addCellAttribute('sod_qty_figure', $i, 'style', 'background-color: #405467;');
            }
            $i++;
            $results[] = $row;
        }
        $table->addRows($results);
        $table->setColumnType('sod_quantity', 'float');
        $table->setColumnType('sod_qty_figure', 'float');
        $table->addColumnAttribute('sod_gdt_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_production_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('sod_gd_sku', 'style', 'text-align: center;');
        $table->setFooterType('sod_quantity', 'SUM');
        $table->setFooterType('sod_qty_figure', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoSodPtl', Trans::getWord('goods'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true && $this->isJobDeleted() === false) {

            if ($this->isValidParameter('sop_end_on') === true) {
                $pdfButton = new PdfButton('SoPrint', Trans::getWord('printPdf'), 'stockopname');
                $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
                $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
                $this->View->addButton($pdfButton);
            }
            if ($this->isValidParameter('sop_end_on') === false && $this->PageSetting->checkPageRight('AllowDelete') === true) {
                $this->setEnableDeleteButton();
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setSopHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('sop_id', $this->getIntParameter('sop_id'));
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $this->View->addContent('SopHdFld', $content);

    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        # Keep this function empty
    }

}
