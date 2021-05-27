<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job\Warehouse\Bundling;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobOfficerDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Detail\Job\BaseJobOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail JobPacking page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobBundling extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhBundling', 'jo_id');
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
        # Insert Job Goods
        $sn = new SerialNumber($this->User->getSsId());
        $snGoods = $sn->loadNumber('JobOrderGoods', $this->User->Relation->getOfficeId(), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
        $jogColVal = [
            'jog_serial_number' => $snGoods,
            'jog_jo_id' => $joId,
            'jog_gd_id' => $this->getIntParameter('jog_gd_id'),
            'jog_name' => $this->getStringParameter('jog_goods'),
            'jog_quantity' => $this->getFloatParameter('jog_quantity'),
            'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
        ];
        $jogDao = new JobGoodsDao();
        $jogDao->doInsertTransaction($jogColVal);
        # Insert Job Packing
        $colVal = [
            'jb_jo_id' => $joId,
            'jb_jog_id' => $jogDao->getLastInsertId(),
            'jb_wh_id' => $this->getIntParameter('jb_wh_id'),
            'jb_et_date' => $this->getStringParameter('jb_et_date'),
            'jb_et_time' => $this->getStringParameter('jb_et_time'),
        ];
        $jbDao = new JobBundlingDao();
        $jbDao->doInsertTransaction($colVal);
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
            $colVal = [
                'jb_wh_id' => $this->getIntParameter('jb_wh_id'),
                'jb_jog_id' => $this->getIntParameter('jb_jog_id'),
                'jb_et_date' => $this->getStringParameter('jb_et_date'),
                'jb_et_time' => $this->getStringParameter('jb_et_time'),
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $colVal);
            # Do Update Job Goods
            $jogColVal = [
                'jog_jo_id' => $this->getDetailReferenceValue(),
                'jog_gd_id' => $this->getIntParameter('jog_gd_id'),
                'jog_name' => $this->getStringParameter('jog_goods'),
                'jog_quantity' => $this->getFloatParameter('jog_quantity'),
                'jog_gdu_id' => $this->getIntParameter('jog_gdu_id'),
            ];
            $jogDao = new JobGoodsDao();
            $jogDao->doUpdateTransaction($this->getIntParameter('jb_jog_id'), $jogColVal);
        } else if ($this->getFormAction() === 'doDelete') {
            if ($this->isValidParameter('jb_outbound_id') === true) {
                $data = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('jb_outbound_id'));
                $jisDao = new JobInboundStockDao();
                $jodDao = new JobOutboundDetailDao();
                foreach ($data as $row) {
                    $jodDao->doDeleteTransaction($row['jod_id']);
                    if (empty($row['jod_jis_id']) === false) {
                        $jisDao->doDeleteTransaction($row['jod_jis_id']);
                    }
                }
            }
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
        return JobBundlingDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId(), $this->getIntParameter('jo_srt_id', 13));
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert()) {
            $this->setServiceIntoParameter($this->getDefaultRoute());
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        if ($this->isUpdate() === true) {
            # Load Current Action
            $this->CurrentAction = JobActionDao::getLastActiveActionByJobId($this->getDetailReferenceValue());

            # Override title page
            $this->overridePageTitle();
            # load officer data
            $this->Officers = JobOfficerDao::loadByJobOrderId($this->getDetailReferenceValue());
            # Show delete reason
            if ($this->isJobDeleted() === true) {
                $this->setDisableUpdate();
                $this->View->addErrorMessage(Trans::getWord('jobCanceledReason', 'message', '', ['user' => $this->getStringParameter('jo_deleted_by'), 'reason' => $this->getStringParameter('jo_deleted_reason')]));

            }
            # Show delete reason
            if ($this->isJobHold() === true) {
                $this->setDisableUpdate();
                $date = DateTimeParser::format($this->getStringParameter('joh_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
                $this->View->addWarningMessage(Trans::getWord('joHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('joh_reason')]));
            }
            if ($this->isValidParameter('jo_start_on') === true) {
                $this->Tab->addPortlet('general', $this->getJogMaterialFieldSet());
            } else {
                $this->Tab->addPortlet('general', $this->getMaterialFieldSet());
            }

            # Picking Process
            if ($this->isValidParameter('jb_start_pick_on') === true) {
                $this->Tab->addPortlet('pickingGoods', $this->getStorageFieldSet());
                if ($this->isValidParameter('jb_end_pick_on') === false) {
                    $this->Tab->setActiveTab('pickingGoods', true);
                } else {
                    $this->Tab->setActiveTab('general', true);
                }
            }

            # Bundling Process
            if ($this->isValidParameter('jb_start_pack_on') === true) {
                $this->Tab->addPortlet('bundling', $this->getBundlingFieldSet());
                if ($this->isValidParameter('jb_end_pack_on') === false) {
                    $this->Tab->setActiveTab('bundling', true);
                } else {
                    $this->Tab->setActiveTab('general', true);
                }
            }
            # Put Away Process
            if ($this->isValidParameter('jb_start_store_on') === true) {
                $this->Tab->addPortlet('putAway', $this->getPutAwayPortlet());
                if ($this->isValidParameter('jb_end_store_on') === false) {
                    $this->Tab->setActiveTab('putAway', true);
                } else {
                    $this->Tab->setActiveTab('general', true);
                }
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
            $this->Validation->checkRequire('jb_wh_id');
            $this->Validation->checkRequire('jog_gd_id');
            $this->Validation->checkRequire('jog_quantity');
            $this->Validation->checkFloat('jog_quantity');
            $this->Validation->checkRequire('jog_gdu_id');
            $this->Validation->checkRequire('jb_et_date');
            $this->Validation->checkDate('jb_et_date');
            $this->Validation->checkRequire('jb_et_time');
            $this->Validation->checkTime('jb_et_time', 'H:i');
            if ($this->isUpdate()) {
                $this->Validation->checkRequire('jb_jog_id');
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
        $fieldSet->setGridDimension(4);
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        if ($this->isUpdate() === true) {
            $relField->setReadOnly();
        }

        # Create Contact Field
        $picField = $this->Field->getSingleSelect('contactPerson', 'jo_pic', $this->getStringParameter('jo_pic'));
        $picField->setHiddenField('jo_pic_id', $this->getIntParameter('jo_pic_id'));
        $picField->addParameterById('cp_rel_id', 'jo_rel_id', Trans::getWord('customer'));
        $picField->setDetailReferenceCode('cp_id');

        # Create order Office Field
        $ofOrderField = $this->Field->getSingleSelect('office', 'jo_order_of', $this->getStringParameter('jo_order_of'));
        $ofOrderField->setHiddenField('jo_order_of_id', $this->getIntParameter('jo_order_of_id'));
        $ofOrderField->addParameter('of_rel_id', $this->User->getRelId());
        $ofOrderField->setEnableDetailButton(false);
        $ofOrderField->setEnableNewButton(false);

        # Create invoice Office Field
        $ofInvoiceField = $this->Field->getSingleSelect('office', 'jo_invoice_of', $this->getStringParameter('jo_invoice_of'));
        $ofInvoiceField->setHiddenField('jo_invoice_of_id', $this->getIntParameter('jo_invoice_of_id'));
        $ofInvoiceField->addParameter('of_rel_id', $this->User->getRelId());
        $ofInvoiceField->addParameter('of_invoice', 'Y');
        $ofInvoiceField->setEnableDetailButton(false);
        $ofInvoiceField->setEnableNewButton(false);

        # Create Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'jb_warehouse', $this->getStringParameter('jb_warehouse'));
        $whField->setHiddenField('jb_wh_id', $this->getIntParameter('jb_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        if ($this->isValidParameter('jo_start_on') === true) {
            $whField->setReadOnly();
        }
        # Create Contact Field
        $managerField = $this->Field->getSingleSelect('user', 'jo_manager', $this->getStringParameter('jo_manager'));
        $managerField->setHiddenField('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $managerField->addParameter('ss_id', $this->User->getSsId());
        $managerField->setEnableDetailButton(false);
        $managerField->setEnableNewButton(false);

        # Customer Reference Field
        $customerRefField = $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref'));
        if ($this->isValidParameter('jo_so_id')) {
            $customerRefField->setReadOnly();
        }


        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $picField);
        $fieldSet->addField(Trans::getWord('orderDate'), $this->Field->getCalendar('jo_order_date', $this->getStringParameter('jo_order_date')), true);
        if ($this->isValidParameter('jo_so_id')) {
            $soNumber = $this->Field->getText('so_number', $this->getStringParameter('so_number'));
            $soNumber->setReadOnly();
            $fieldSet->addField(Trans::getWord('soNumber'), $soNumber);

        }
        $fieldSet->addField(Trans::getWord('customerRef'), $customerRefField);
        if ($this->isUpdate()) {
            $fieldSet->addField(Trans::getWord('orderOffice'), $ofOrderField, true);
            $fieldSet->addField(Trans::getWord('invoiceOffice'), $ofInvoiceField, true);
        }
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('jobManager'), $managerField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('jo_srv_id', $this->getIntParameter('jo_srv_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_so_id', $this->getIntParameter('jo_so_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('joh_id', $this->getIntParameter('joh_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jb_inbound_id', $this->getIntParameter('jb_inbound_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jb_outbound_id', $this->getIntParameter('jb_outbound_id')));
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
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

        # Create Unit Field
        $unitField = $this->Field->getSingleSelect('goodsUnit', 'jog_unit', $this->getStringParameter('jog_unit'));
        $unitField->setHiddenField('jog_gdu_id', $this->getIntParameter('jog_gdu_id'));
        $unitField->addParameterById('gdu_gd_id', 'jog_gd_id', Trans::getWord('goods'));
        $unitField->setEnableNewButton(false);
        $unitField->setEnableDetailButton(false);

        $qtyField = $this->Field->getNumber('jog_quantity', $this->getFloatParameter('jog_quantity'));

        if ($this->isValidParameter('jo_start_on') === true) {
            $goodsField = $this->Field->getText('jog_goods', $this->getStringParameter('jog_goods'));
            $goodsField->setReadOnly();
            $fieldSet->addHiddenField($this->Field->getHidden('jog_gd_id', $this->getIntParameter('jog_gd_id')));
            $unitField->setReadOnly();
            $qtyField->setReadOnly();
        } else {
            # Create Unit Field
            $goodsField = $this->Field->getSingleSelectTable('goods', 'jog_goods', $this->getStringParameter('jog_goods'), 'loadSingleSelectTableData');
            $goodsField->setHiddenField('jog_gd_id', $this->getIntParameter('jog_gd_id'));
            $goodsField->setTableColumns([
                'gd_sku' => Trans::getWord('sku'),
                'gd_gdc_name' => Trans::getWord('category'),
                'gd_br_name' => Trans::getWord('brand'),
                'gd_name' => Trans::getWord('goods'),
            ]);
            $goodsField->setAutoCompleteFields([
                'jog_gd_sku' => 'gd_sku',
            ]);
            $goodsField->setFilters([
                'gdc_name' => Trans::getWord('category'),
                'br_name' => Trans::getWord('brand'),
                'gd_name' => Trans::getWord('goods'),
                'gd_sku' => Trans::getWord('sku'),
            ]);
            $goodsField->setValueCode('gd_id');
            $goodsField->setLabelCode('gd_full_name');
            $goodsField->addParameter('gd_ss_id', $this->User->getSsId());
            $goodsField->addParameter('gd_bundling', 'Y');
            $goodsField->addParameterById('gd_rel_id', 'jo_rel_id', Trans::getWord('customer'));
            $goodsField->addClearField('jog_unit');
            $goodsField->addClearField('jog_gdu_id');
            $this->View->addModal($goodsField->getModal());
        }


        $skuField = $this->Field->getText('jog_gd_sku', $this->getStringParameter('jog_gd_sku'));
        $skuField->setReadOnly();

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField, true);
        $fieldSet->addField(Trans::getWord('planningDate'), $this->Field->getCalendar('jb_et_date', $this->getStringParameter('jb_et_date')), true);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField, true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField, true);
        $fieldSet->addField(Trans::getWord('planningTime'), $this->Field->getTime('jb_et_time', $this->getStringParameter('jb_et_time')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addHiddenField($this->Field->getHidden('jb_jog_id', $this->getIntParameter('jb_jog_id')));
            $fieldSet->addHiddenField($this->Field->getHidden('jb_id', $this->getIntParameter('jb_id')));
        }
        # Create a portlet box.
        $portlet = new Portlet('JowGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getMaterialFieldSet(): Portlet
    {
        $table = new Table('JoGmTbl');
        $table->setHeaderRow([
            'gm_gd_sku' => Trans::getWord('sku'),
            'gm_goods' => Trans::getWord('goods'),
            'gm_quantity' => Trans::getWord('composition'),
            'gm_required' => Trans::getWord('qtyRequired'),
            'gm_available_stock' => Trans::getWord('availableStock'),
            'gm_uom_code' => Trans::getWord('uom'),
        ]);
        $data = GoodsMaterialDao::loadDataWithStock($this->getIntParameter('jog_gd_id'), $this->getIntParameter('jb_wh_id'));
        $rows = [];
        $gdDao = new GoodsDao();
        $i = 0;
        foreach ($data as $row) {
            $row['gm_goods'] = $gdDao->formatFullName($row['gm_gdc_name'], $row['gm_br_name'], $row['gm_gd_name']);
            $required = (float)$row['gm_quantity'] * $this->getFloatParameter('jog_quantity');
            $stock = (float)$row['gm_available_stock'];
            $row['gm_required'] = $required;
            if ($stock < $required) {
                $table->addCellAttribute('gm_available_stock', $i, 'style', 'background-color: red; text-align: right; color: white;');
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('JoGmPtl', Trans::getWord('billOfMaterials'));
        $table->setColumnType('gm_quantity', 'float');
        $table->setColumnType('gm_required', 'float');
        $table->setColumnType('gm_available_stock', 'float');
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
        if ($this->isAllowUpdate()) {
            if ($this->isValidParameter('jb_start_pack_on') === false && $this->PageSetting->checkPageRight('AllowDelete') === true && $this->isJobHold() === false) {
                $this->setEnableDeleteButton();
            }
            if ($this->isJobFinish() === false) {
                $this->setEnableHoldButton();
            }
        }
        if ($this->isJobHold() && $this->isSoHold() === false) {
            $this->setEnableUnHoldButton();
            $this->setDisableUpdate();
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
     * Function to get the material from job goods field Set.
     *
     * @return Portlet
     */
    private function getJogMaterialFieldSet(): Portlet
    {
        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_gd_sku' => Trans::getWord('sku'),
            'jog_name' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('qtyRequired'),
            'jog_unit' => Trans::getWord('uom'),
        ]);
        $data = $this->loadJogData();
        $table->addRows($data);
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('billOfMaterials'));
        $table->setColumnType('jog_quantity', 'float');
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the material from job goods field Set.
     *
     * @return array
     */
    private function loadJogData(): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jog.jog_id <> ' . $this->getIntParameter('jb_jog_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_gd_name,
                    br.br_name as jog_gd_brand, gdc.gdc_name as jog_gd_category, jog.jog_name, jog.jog_quantity,
                    uom.uom_code as jog_unit
                FROM job_goods as jog INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                unit as uom ON uom.uom_id = gdu.gdu_uom_id ';
        $query .= $strWheres;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $results = DataParser::arrayObjectToArray($sqlResults);
        }
        return $results;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        $table = new Table('JoJwdTbl');
        $table->setHeaderRow([
            'jod_storage' => Trans::getWord('storage'),
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
            'jod_jid_serial_number' => Trans::getWord('serialNumber'),
            'jod_quantity' => Trans::getWord('qtyPicking'),
            'jod_unit' => Trans::getWord('uom'),
        ]);
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('jb_outbound_id') . ')';
        if ($this->isJobDeleted() === false) {
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
        }
        $data = JobOutboundDetailDao::loadData($wheres);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jod_quantity', 'float');
        $table->setFooterType('jod_quantity', 'SUM');
        $table->addColumnAttribute('jod_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_gd_sku', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJbJodPtl', Trans::getWord('goodsTaken'));

        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the bundling Field Set.
     *
     * @return Portlet
     */
    protected function getBundlingFieldSet(): Portlet
    {
        $table = new Table('JoJbdTbl');
        $table->setHeaderRow([
            'jbd_user' => Trans::getWord('officer'),
            'jbd_lot_number' => Trans::getWord('lotNumber'),
            'jbd_serial_number' => Trans::getWord('serialNumber'),
            'jbd_quantity' => Trans::getWord('quantity'),
            'jbd_uom_code' => Trans::getWord('uom'),
            'jbd_status' => Trans::getWord('status'),
        ]);
        $wheres = [];
        $wheres[] = '(jbd.jbd_jb_id = ' . $this->getIntParameter('jb_id') . ')';
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
        $data = JobBundlingDetailDao::loadData($wheres, 30);
        $rows = [];
        foreach ($data as $row) {
            $status = new LabelWarning(Trans::getWord('inProgress'));
            if (empty($row['jbd_end_on']) === false) {
                $status = new LabelSuccess(Trans::getWord('complete'));
            }
            $row['jbd_status'] = $status;

            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jbd_quantity', 'float');
        $table->setFooterType('jbd_quantity', 'SUM');
        $table->addColumnAttribute('jbd_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_uom_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_status', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJbJbdPtl', Trans::getWord('bundling'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getPutAwayPortlet(): Portlet
    {
        $table = new Table('JoJidTbl');
        $table->setHeaderRow([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_gd_sku' => Trans::getWord('sku'),
            'jid_goods' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_quantity' => Trans::getWord('quantity'),
            'jid_uom' => Trans::getWord('uom'),
            'jid_total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'jid_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
        ]);
        $wheres = [];
        $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('jb_inbound_id') . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = "(jid.jid_adjustment = 'N')";

        $data = JobInboundDetailDao::loadData($wheres);
        $rows = JobInboundDetailDao::doPrepareInboundDetailData($data);
        $table->addRows($rows);
        $table->addColumnAttribute('jid_whs_name', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_gd_sku', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_serial_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_uom', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_condition', 'style', 'text-align: center');
        $table->setColumnType('jid_quantity', 'float');
        $table->setColumnType('jid_total_volume', 'float');
        $table->setColumnType('jid_total_weight', 'float');
        $table->setFooterType('jid_quantity', 'SUM');
        $table->setFooterType('jid_total_volume', 'SUM');
        $table->setFooterType('jid_total_weight', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoJidPtl', Trans::getWord('storage'));
        $portlet->addTable($table);

        return $portlet;
    }

}
