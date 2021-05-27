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

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobMovementDao;
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JoStockMovement page
 *
 * @package    app
 * @subpackage Model\Detail\Jo\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobStockMovement extends BaseJobOrder
{

    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    protected $Detail = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhStockMovement', 'jo_id');
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
        $number = $sn->loadNumber('JobOrder', $this->User->Relation->getOfficeId(), 0, $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
        $joColVal = [
            'jo_number' => $number,
            'jo_ss_id' => $this->User->getSsId(),
            'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
            'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
            'jo_order_date' => date('Y-m-d'),
            'jo_order_of_id' => $this->User->Relation->getOfficeId(),
            'jo_manager_id' => $this->getIntParameter('jo_manager_id')
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
        $jmColVal = [
            'jm_jo_id' => $joId,
            'jm_date' => $this->getStringParameter('jm_date'),
            'jm_time' => $this->getStringParameter('jm_time'),
            'jm_wh_id' => $this->getIntParameter('jm_wh_id'),
            'jm_whs_id' => $this->getIntParameter('jm_whs_id'),
            'jm_new_whs_id' => $this->getIntParameter('jm_new_whs_id'),
            'jm_remark' => $this->getStringParameter('jm_remark'),
        ];
        $jmDao = new JobMovementDao();
        $jmDao->doInsertTransaction($jmColVal);

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
                'jo_manager_id' => $this->getIntParameter('jo_manager_id'),
            ];
            $jobDao = new JobOrderDao();
            $jobDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            $jmColVal = [
                'jm_date' => $this->getStringParameter('jm_date'),
                'jm_time' => $this->getStringParameter('jm_time'),
                'jm_wh_id' => $this->getIntParameter('jm_wh_id'),
                'jm_whs_id' => $this->getIntParameter('jm_whs_id'),
                'jm_new_whs_id' => $this->getIntParameter('jm_new_whs_id'),
                'jm_remark' => $this->getStringParameter('jm_remark'),
            ];
            $jmDao = new JobMovementDao();
            $jmDao->doUpdateTransaction($this->getIntParameter('jm_id'), $jmColVal);
        }
        if ($this->getFormAction() !== null) {
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
        return JobMovementDao::getByJobIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJmHiddenData();
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        if ($this->isUpdate() === true) {
            # load detail data
            $this->Detail = JobMovementDetailDao::loadDataByJmId($this->getIntParameter('jm_id'));
            $this->Tab->addPortlet('general', $this->getGoodsFieldSet());
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
            $this->Validation->checkRequire('jo_manager_id');
            $this->Validation->checkRequire('jm_wh_id');
            $this->Validation->checkRequire('jm_whs_id');
            $this->Validation->checkRequire('jm_new_whs_id');
            $this->Validation->checkRequire('jm_date');
            $this->Validation->checkDate('jm_date');
            $this->Validation->checkRequire('jm_time');
            $this->Validation->checkTime('jm_time');
            $this->Validation->checkMaxLength('jm_remark', 255);
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('jm_id');
            }
        } elseif ($this->getFormAction() === 'doUpdateMovementDetail') {
            $this->Validation->checkRequire('jm_id');
            $this->Validation->checkRequire('jmd_jid_id');
            $this->Validation->checkRequire('jmd_gdu_id');
            $this->Validation->checkRequire('jmd_quantity');
            if ($this->isValidParameter('jmd_jid_stock') === true) {
                $this->Validation->checkFloat('jmd_quantity', 0.1, $this->getFloatParameter('jmd_jid_stock'));
            }
            if ($this->isValidParameter('jmd_gdt_id') === true) {
                $this->Validation->checkRequire('jmd_gcd_id');
            }
            if ($this->isValidParameter('jmd_gcd_id') === true) {
                $this->Validation->checkRequire('jmd_gdt_id');
            }
            $this->Validation->checkRequire('jmd_whs_id');
            $this->Validation->checkUnique('jmd_jid_id', 'job_movement_detail', [
                'jmd_id' => $this->getIntParameter('jmd_id')
            ], [
                'jmd_jm_id' => $this->getIntParameter('jm_id'),
                'jmd_whs_id' => $this->getIntParameter('jmd_whs_id'),
                'jmd_deleted_on' => null,
            ]);
        } elseif ($this->getFormAction() === 'doDeleteMovementDetail') {
            $this->Validation->checkRequire('jmd_id_del');
        }
        if ($this->getFormAction() !== null) {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseFieldSet(): Portlet
    {

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        # Create Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'jm_wh_name', $this->getStringParameter('jm_wh_name'));
        $whField->setHiddenField('jm_wh_id', $this->getIntParameter('jm_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->addClearField('jm_whs_name');
        $whField->addClearField('jm_whs_id');
        $whField->setEnableNewButton(false);
        $whField->setDetailReferenceCode('wh_id');
        # Create storage
        $storageField = $this->Field->getSingleSelect('warehouseStorage', 'jm_whs_name', $this->getStringParameter('jm_whs_name'));
        $storageField->setHiddenField('jm_whs_id', $this->getIntParameter('jm_whs_id'));
        $storageField->addParameterById('whs_wh_id', 'jm_wh_id', Trans::getWord('warehouse'));
        $storageField->setEnableNewButton(false);
        $storageField->setDetailReferenceCode('whs_id');

        # Create Storage Field
        $destinationField = $this->Field->getSingleSelect('warehouseStorage', 'jm_destination_storage', $this->getStringParameter('jm_destination_storage'));
        $destinationField->setHiddenField('jm_new_whs_id', $this->getIntParameter('jm_new_whs_id'));
        $destinationField->addParameterById('whs_wh_id', 'jm_wh_id', Trans::getWord('warehouse'));
        $destinationField->addParameterById('ignore_id', 'jm_whs_id', Trans::getWord('originStorage'));
        $destinationField->setEnableNewButton(false);
        $destinationField->setEnableDetailButton(false);


        if ($this->isValidParameter('jo_start_on') === true) {
            $whField->setReadOnly();
            $storageField->setReadOnly();
            $destinationField->setReadOnly();
        }

        # Create job manager Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);


        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('planningDate'), $this->Field->getCalendar('jm_date', $this->getStringParameter('jm_date')), true);
        $fieldSet->addField(Trans::getWord('planningTime'), $this->Field->getTime('jm_time', $this->getStringParameter('jm_time')), true);
        $fieldSet->addField(Trans::getWord('originStorage'), $storageField, true);
        $fieldSet->addField(Trans::getWord('destinationStorage'), $destinationField, true);
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField, true);
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getTextArea('jm_remark', $this->getStringParameter('jm_remark')));
        # Create a portlet box.
        $portlet = new Portlet('JmGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $table = new Table('JoJmdTbl');
        $table->setHeaderRow([
            'jmd_gd_sku' => Trans::getWord('sku'),
            'jmd_gd_name' => Trans::getWord('goods'),
            'jmd_jid_lot_number' => Trans::getWord('lotNumber'),
            'jmd_jid_serial_number' => Trans::getWord('serialNumber'),
            'jmd_jir_condition' => Trans::getWord('condition'),
            'jmd_quantity' => Trans::getWord('quantity'),
            'jmd_gdu_uom' => Trans::getWord('uom'),
            'jmd_gdt_code' => Trans::getWord('damageType'),
            'jmd_gcd_code' => Trans::getWord('causeDamage'),
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
        ]);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($this->Detail as $row) {
            $volume = (float)$row['jmd_jid_volume'];
            if (empty($row['jmd_volume']) === false) {
                $volume = (float)$row['jmd_volume'];
            }
            $weight = (float)$row['jmd_jid_weight'];
            if (empty($row['jmd_weight']) === false) {
                $weight = (float)$row['jmd_weight'];
            }
            $row['jmd_gd_name'] = $gdDao->formatFullName($row['jmd_gdc_name'], $row['jmd_br_name'], $row['jmd_gd_name']);
            if (empty($row['jmd_jid_gdt_id']) === false) {
                $row['jmd_jir_condition'] = new LabelDanger(Trans::getWord('damage'));
            } else {
                $row['jmd_jir_condition'] = new LabelSuccess(Trans::getWord('good'));
            }

            $row['total_weight'] = (float)$row['jmd_quantity'] * $weight;
            $row['total_volume'] = (float)$row['jmd_quantity'] * $volume;
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jmd_quantity', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setFooterType('jmd_quantity', 'SUM');
        $table->addColumnAttribute('jmd_jid_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_jir_condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gdu_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gdt_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jmd_gcd_code', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('goods'));
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
        if ($this->isUpdate() === true) {
            $this->EnableDelete = !$this->isValidParameter('jm_complete_on');
            if ($this->isValidParameter('jm_complete_on') === true && $this->isJobDeleted() === false) {
                $pdfButton = new PdfButton('JmPrint', Trans::getWord('printPdf'), 'stockmovement');
                $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
                $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
                $this->View->addButton($pdfButton);
            }
        }
        parent::loadDefaultButton();
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
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        # Keep this function empty to override parent fucntion.
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJmHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('jm_id', $this->getIntParameter('jm_id'));
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $this->View->addContent('JmHdFld', $content);

    }

}
