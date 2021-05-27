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

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDao;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDetailDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JoStockAdjustment page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockAdjustment extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockAdjustment', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $joId = parent::doInsert();
        # insert job adjustment
        $jaColVal = [
            'ja_jo_id' => $joId,
            'ja_wh_id' => $this->getIntParameter('ja_wh_id'),
            'ja_gd_id' => $this->getIntParameter('ja_gd_id'),
        ];
        $jaDao = new JobAdjustmentDao();
        $jaDao->doInsertTransaction($jaColVal);

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
            # update job adjustment
            $jaColVal = [
                'ja_jo_id' => $this->getDetailReferenceValue(),
                'ja_wh_id' => $this->getIntParameter('ja_wh_id'),
                'ja_gd_id' => $this->getIntParameter('ja_gd_id'),
            ];
            $jaDao = new JobAdjustmentDao();
            $jaDao->doUpdateTransaction($this->getIntParameter('ja_id'), $jaColVal);
        }
        parent::doUpdate();
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobAdjustmentDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJaHiddenData();

        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('general', $this->getDetailFieldSet());
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
            $this->Validation->checkRequire('ja_wh_id');
            $this->Validation->checkRequire('ja_gd_id');
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('ja_id');
            }
        }
        parent::loadValidationRole();
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
        $fieldSet->setGridDimension(6, 6);
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');

        # Create Contact Field
        # Create Unit Field
        $goodsField = $this->Field->getSingleSelect('goods', 'ja_goods', $this->getStringParameter('ja_goods'));
        $goodsField->setHiddenField('ja_gd_id', $this->getIntParameter('ja_gd_id'));
        $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
        $goodsField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $goodsField->setEnableNewButton(false);
        $goodsField->setEnableDetailButton(false);

        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);

        # Warehouse field
        $whField = $this->Field->getSingleSelect('warehouse', 'wh_name', $this->getStringParameter('wh_name'));
        $whField->setHiddenField('ja_wh_id', $this->getIntParameter('ja_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableNewButton(false);
        $whField->setDetailReferenceCode('wh_id');

        if ($this->isValidParameter('jo_start_on') === true) {
            $relField->setReadOnly();
            $whField->setReadOnly();
            $goodsField->setReadOnly();
        }
        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField, true);
        # Create a portlet box.
        $portlet = new Portlet('JaGeneralPtl', Trans::getWord('jobDetail'));
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
    private function getDetailFieldSet(): Portlet
    {
        $table = new Table('JoSopTbl');
        $table->setHeaderRow([
            'jad_jo_number' => Trans::getWord('inboundNumber'),
            'jad_inbound_on' => Trans::getWord('inboundDate'),
            'jad_whs_name' => Trans::getWord('storage'),
            'jad_lot_number' => Trans::getWord('lotNumber'),
            'jad_serial_number' => Trans::getWord('serialNumber'),
            'jad_quantity' => Trans::getWord('qtyAdjustment'),
            'jad_uom' => Trans::getWord('uom'),
            'jad_sat_description' => Trans::getWord('adjustmentType'),
            'jad_remark' => Trans::getWord('remark')
        ]);
        $data = JobAdjustmentDetailDao::loadDataByJaId($this->getIntParameter('ja_id'));
        $rows = [];
        foreach ($data as $row) {
            $row['jad_inbound_on'] = DateTimeParser::format($row['jad_inbound_on'], 'Y-m-d H:i:s', 'd.M.Y');
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jad_jid_stock', 'float');
        $table->setColumnType('jad_quantity', 'float');
        # Create a portlet box.
        $portlet = new Portlet('JoJadPtl', Trans::getWord('adjustmentDetail'));

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
        $this->EnableDelete = !$this->isValidParameter('jm_complete_on');
        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJaHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('ja_id', $this->getIntParameter('ja_id'));
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $this->View->addContent('JaHdFld', $content);

    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
    }

}
