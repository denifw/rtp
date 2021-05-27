<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Job\Inklaring;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Job\Inklaring\JobInklaringReleaseDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Detail\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobInklaring page
 *
 * @package    app
 * @subpackage Model\Detail\Job\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class JobInklaring extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'jik', 'jo_id');
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
        $jikColVal = [
            'jik_jo_id' => $joId,
            'jik_so_id' => $this->getIntParameter('jik_so_id'),
        ];
        $jikDao = new JobInklaringDao();
        $jikDao->doInsertTransaction($jikColVal);
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
            $jikColVal = [
                'jik_so_id' => $this->getIntParameter('jik_so_id'),
                'jik_closing_date' => $this->getStringParameter('jik_closing_date'),
                'jik_closing_time' => $this->getStringParameter('jik_closing_time'),
            ];
            $jikDao = new JobInklaringDao();
            $jikDao->doUpdateTransaction($this->getIntParameter('jik_id'), $jikColVal);
        } else if ($this->getFormAction() === 'doUploadRequireDoc') {
            $listRow = $this->getArrayParameter('jik_row_id');
            foreach ($listRow as $index) {
                $file = $this->getFileParameter('jik_doc' . $index);
                if ($file !== null) {
                    $colVal = [
                        'doc_ss_id' => $this->User->getSsId(),
                        'doc_dct_id' => $this->getIntParameter('jik_doc_type' . $index),
                        'doc_group_reference' => $this->getDetailReferenceValue(),
                        'doc_type_reference' => null,
                        'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                        'doc_description' => $this->getStringParameter('jik_doc_description' . $index),
                        'doc_file_size' => $file->getSize(),
                        'doc_file_type' => $file->getClientOriginalExtension(),
                        'doc_public' => 'Y',
                    ];
                    $docDao = new DocumentDao();
                    $docDao->doInsertTransaction($colVal);
                    $upload = new FileUpload($docDao->getLastInsertId());
                    $upload->upload($file);
                }
            }
        } else if ($this->getFormAction() === 'doCopyData') {
            $amount = $this->getIntParameter('base_copy_amount');
            $jikDao = new JobInklaringDao();
            $wheres = [];
            $wheres[] = '(jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
            $wheres[] = '(jog_deleted_on IS NULL)';
            $goods = JobGoodsDao::loadSimpleData($wheres);
            $jogDao = new JobGoodsDao();
            for ($i = 0; $i < $amount; $i++) {
                $joId = $this->doInsertJobOrder();
                $jikColVal = [
                    'jik_jo_id' => $joId,
                    'jik_cdt_id' => $this->getStringParameter('jik_cdt_id'),
                    'jik_planning_date' => $this->getStringParameter('jik_planning_date'),
                    'jik_consignee_id' => $this->getIntParameter('jik_consignee_id'),
                    'jik_of_consignee_id' => $this->getIntParameter('jik_of_consignee_id'),
                    'jik_pic_consignee_id' => $this->getIntParameter('jik_pic_consignee_id'),
                    'jik_shipper_id' => $this->getIntParameter('jik_shipper_id'),
                    'jik_of_shipper_id' => $this->getIntParameter('jik_of_shipper_id'),
                    'jik_pic_shipper_id' => $this->getIntParameter('jik_pic_shipper_id'),
                    'jik_notify_id' => $this->getIntParameter('jik_notify_id'),
                    'jik_of_notify_id' => $this->getIntParameter('jik_of_notify_id'),
                    'jik_pic_notify_id' => $this->getIntParameter('jik_pic_notify_id'),
                    'jik_pol_id' => $this->getIntParameter('jik_pol_id'),
                    'jik_pod_id' => $this->getIntParameter('jik_pod_id'),
                ];
                $jikDao->doInsertTransaction($jikColVal);
                foreach ($goods as $row) {
                    $jogColVal = [
                        'jog_jo_id' => $joId,
                        'jog_gd_id' => $row['jog_gd_id'],
                        'jog_name' => $row['jog_name'],
                        'jog_quantity' => $row['jog_quantity'],
                        'jog_uom_id' => $row['jog_uom_id'],
                        'jog_volume' => $row['jog_volume'],
                        'jog_weight' => $row['jog_weight'],
                    ];
                    $jogDao->doInsertTransaction($jogColVal);
                }
            }
        } else if ($this->getFormAction() === 'doExportTimeShtXls') {
            $this->doExportTimeShtXls();
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
        $data = JobInklaringDao::getByReferenceAndSystemSetting($this->getDetailReferenceValue(), $this->User->getSsId());
        if (empty($data) === false) {
            $soData = SalesOrderDao::getByReference($data['jik_so_id']);
            $data = array_merge($data, $soData);
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
        if ($this->isInsert() === true && $this->isValidParameter('jik_so_id') === true) {
            $soData = SalesOrderDao::getByReferenceAndSystem($this->getIntParameter('jik_so_id'), $this->User->getSsId());
            $this->setParameters($soData);
            if (empty($soData) === false) {
                $this->setParameter('jo_rel_id', $soData['so_rel_id']);
                $this->setParameter('jo_order_of_id', $soData['so_order_of_id']);
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('general', $this->getVendorFieldSet());
            $this->Tab->addPortlet('general', $this->getInklaringFieldSet());
            if ($this->isValidParameter('jik_release_on') === true) {
                if ($this->getStringParameter('so_container', 'N') === 'Y') {
                    $this->Tab->addPortlet('goods', $this->getReleaseContainerPortlet());
                } else {
                    $this->Tab->addPortlet('goods', $this->getReleaseGoodsPortlet());
                }
            }
            if ($this->getStringParameter('so_container', 'N') === 'Y') {
                $this->Tab->addPortlet('goods', $this->getContainerPortlet());
            }
            $this->Tab->addPortlet('goods', $this->getGoodsPortlet());
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
            $this->Validation->checkRequire('jik_so_id');
            $this->Validation->checkRequire('jo_order_of_id');
            if ($this->isUpdate() === true) {
                $this->Validation->checkRequire('jik_closing_date');
                $this->Validation->checkRequire('jik_closing_time');
                $this->Validation->checkDate('jik_closing_date');
                $this->Validation->checkTime('jik_closing_time');
            }
        } else if ($this->getFormAction() === 'doUpdateGoods') {
            $this->Validation->checkRequire('jog_name', 3, 255);
            if ($this->isValidParameter('jog_hscode') === true) {
                $this->Validation->checkMinLength('jog_hscode', 3);
                $this->Validation->checkMaxLength('jog_hscode', 255);
            }
            if ($this->isValidParameter('jog_quantity') === true) {
                $this->Validation->checkFloat('jog_quantity');
            }
            if ($this->isValidParameter('jog_weight') === true) {
                $this->Validation->checkFloat('jog_weight');
            }
            if ($this->isValidParameter('jog_volume') === true) {
                $this->Validation->checkFloat('jog_volume');
            }
        } else if ($this->getFormAction() === 'doDeleteGoods') {
            $this->Validation->checkRequire('jog_id_del');
        } else if ($this->getFormAction() === 'doUpdateContainer') {
            $this->Validation->checkRequire('joc_container_number', 3, 255);
            if ($this->isValidParameter('joc_seal_number') === true) {
                $this->Validation->checkRequire('joc_seal_number', 3, 255);
            }
            $this->Validation->checkRequire('joc_ct_id');
        } else if ($this->getFormAction() === 'doDeleteContainer') {
            $this->Validation->checkRequire('joc_id_del');
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
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        # So Reference
        $soField = $this->Field->getSingleSelectTable('so', 'so_number', $this->getStringParameter('so_number'));
        $soField->setHiddenField('jik_so_id', $this->getIntParameter('jik_so_id'));
        $soField->setTableColumns([
            'so_number' => Trans::getWord('number'),
            'so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('customerRef'),
        ]);
        $soField->setFilters([
            'so_number' => Trans::getWord('number'),
            'so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('customerRef'),
        ]);
        $soField->setAutoCompleteFields([
            'so_customer' => 'so_customer',
            'jo_rel_id' => 'so_rel_id',
            'so_customer_ref' => 'so_customer_ref',
            'so_bl_ref' => 'so_bl_ref',
            'so_aju_ref' => 'so_aju_ref',
            'so_sppb_ref' => 'so_sppb_ref',
            'so_packing_ref' => 'so_packing_ref',
            'so_container' => 'so_container',
            'jo_order_of_id' => 'so_order_of_id',
        ]);
        $soField->setValueCode('so_id');
        $soField->setLabelCode('so_number');
        $soField->addParameter('so_ss_id', $this->User->getSsId());
        $this->View->addModal($soField->getModal());
        if ($this->isInsert() === true && $this->isValidParameter('jik_so_id') === true) {
            $soField->setReadOnly();
        }

        $srtField = $this->Field->getSingleSelect('serviceTerm', 'jo_service_term', $this->getStringParameter('jo_service_term'));
        $srtField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $srtField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srtField->addParameter('srv_code', 'inklaring');
        $srtField->addParameterById('jik_so_id', 'jik_so_id', Trans::getWord('salesOrder'));
        $srtField->addOptionalParameterById('srt_container', 'so_container');
        $srtField->setEnableNewButton(false);
        $srtField->setEnableDetailButton(false);
        $srtField->setAutoCompleteFields([
            'jo_srv_id' => 'srt_srv_id'
        ]);
        if ($this->isUpdate() === true) {
            $soField->setReadOnly();
            $srtField->setReadOnly();
        }

        # Customer
        $customerField = $this->Field->getText('so_customer', $this->getStringParameter('so_customer'));
        $customerField->setReadOnly();
        $customerRefField = $this->Field->getText('so_customer_ref', $this->getStringParameter('so_customer_ref'));
        $customerRefField->setReadOnly();
        $blRefField = $this->Field->getText('so_bl_ref', $this->getStringParameter('so_bl_ref'));
        $blRefField->setReadOnly();
        $ajuRefField = $this->Field->getText('so_aju_ref', $this->getStringParameter('so_aju_ref'));
        $ajuRefField->setReadOnly();
        $sppbRefField = $this->Field->getText('so_sppb_ref', $this->getStringParameter('so_sppb_ref'));
        $sppbRefField->setReadOnly();
        $packingRefField = $this->Field->getText('so_packing_ref', $this->getStringParameter('so_packing_ref'));
        $packingRefField->setReadOnly();

        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('salesOrder'), $soField, true);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
        $fieldSet->addField(Trans::getWord('customer'), $customerField);
        $fieldSet->addField(Trans::getWord('customerRef'), $customerRefField);
        $fieldSet->addField(Trans::getWord('blRef'), $blRefField);
        $fieldSet->addField(Trans::getWord('ajuRef'), $ajuRefField);
        $fieldSet->addField(Trans::getWord('sppbRef'), $sppbRefField);
        $fieldSet->addField(Trans::getWord('packingRef'), $packingRefField);

        $fieldSet->addHiddenField($this->Field->getHidden('so_container', $this->getStringParameter('so_container')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_rel_id', $this->getIntParameter('jo_rel_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_customer', $this->getStringParameter('jo_customer')));
        $fieldSet->addHiddenField($this->Field->getHidden('jo_customer_ref', $this->getStringParameter('jo_customer_ref')));
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addFieldSet($fieldSet);

        $portlet->setGridDimension(8, 8, 8);

        return $portlet;
    }

    /**
     * Function to get the inklaring Field Set.
     *
     * @return Portlet
     */
    private function getInklaringFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('closingDate'), $this->Field->getCalendar('jik_closing_date', $this->getStringParameter('jik_closing_date')), true);
        $fieldSet->addField(Trans::getWord('closingTime'), $this->Field->getTime('jik_closing_time', $this->getStringParameter('jik_closing_time')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addHiddenField($this->Field->getHidden('jik_id', $this->getIntParameter('jik_id')));
        }
        # Create a portlet box.
        $portlet = new Portlet('JikPtl', Trans::getWord('jobDetail'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 8, 8);

        return $portlet;
    }

    /**
     * Function to get the goods Field Set.
     *
     * @return Portlet
     */
    private function getGoodsPortlet(): Portlet
    {
        $table = new Table('JikSogTbl');
        $table->setHeaderRow([
            'sog_name' => Trans::getWord('description'),
            'sog_packing_ref' => Trans::getWord('packingRef'),
            'sog_quantity' => Trans::getWord('quantity'),
            'sog_uom' => Trans::getWord('uom'),
            'sog_gross_weight' => Trans::getWord('grossWeight') . ' (KG)',
            'sog_net_weight' => Trans::getWord('netWeight') . ' (KG)',
            'sog_dimension' => Trans::getWord('dimensionPerUnit') . ' (M)',
            'sog_cbm' => Trans::getWord('cbm'),
            'sog_notes' => Trans::getWord('notes'),
        ]);
        $table->setColumnType('sog_quantity', 'float');
        $table->setColumnType('sog_gross_weight', 'float');
        $table->setColumnType('sog_net_weight', 'float');
        $table->setColumnType('sog_cbm', 'float');
        $table->setFooterType('sog_gross_weight', 'SUM');
        $table->setFooterType('sog_net_weight', 'SUM');
        $table->setFooterType('sog_cbm', 'SUM');
        if ($this->getStringParameter('so_container', 'N') === 'Y') {
            $table->addColumnAtTheBeginning('sog_container_number', Trans::getWord('containerNumber'));
            $table->addColumnAtTheBeginning('sog_container_id', Trans::getWord('containerId'));
            $table->addColumnAtTheBeginning('sog_container_type', Trans::getWord('containerType'));
        }


        $table->addRows($this->loadSalesOrderGoodsData());
        # add new button

        $portlet = new Portlet('SoSogPtl', Trans::getWord('goods'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to do load sales order goods data.
     *
     * @return array
     */
    private function loadSalesOrderGoodsData(): array
    {
        $results = [];
        $data = SalesOrderGoodsDao::getBySoId($this->getIntParameter('jik_so_id'));
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $dimensions = [];
            if (empty($row['sog_length']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('length'),
                    'value' => $number->doFormatFloat($row['sog_length']),
                ];
            }
            if (empty($row['sog_width']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('width'),
                    'value' => $number->doFormatFloat($row['sog_width']),
                ];
            }
            if (empty($row['sog_height']) === false) {
                $dimensions[] = [
                    'label' => Trans::getWord('height'),
                    'value' => $number->doFormatFloat($row['sog_height']),
                ];
            }
            if (empty($row['sog_hs_code']) === false) {
                $row['sog_name'] = $row['sog_hs_code'] . ' - ' . $row['sog_name'];
            }
            $row['sog_dimension'] = StringFormatter::generateKeyValueTableView($dimensions);
            $results[] = $row;
        }
        return $results;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    private function getReleaseContainerPortlet(): Portlet
    {
        $table = new Table('JoJikrTbl');
        $table->setHeaderRow([
            'jikr_container_type' => Trans::getWord('containerType'),
            'jikr_container_number' => Trans::getWord('containerNumber'),
            'jikr_seal_number' => Trans::getWord('sealNumber'),
            'jikr_transporter' => Trans::getWord('transporter'),
            'jikr_truck_number' => Trans::getWord('truckPlate'),
            'jikr_driver' => Trans::getWord('driver'),
            'jikr_release_on' => Trans::getWord('releasedOn'),
        ]);
        $showGateIn = false;
        if ($this->getStringParameter('jik_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $showGateIn = true;
            $table->addColumnAfter('jikr_release_on', 'jikr_gate_in_on', Trans::getWord('gatePass'));
        }
        $data = JobInklaringReleaseDao::getByJobInklring($this->getIntParameter('jik_id'));
        $i = 0;
        $rows = [];
        $dt = new DateTimeParser();
        foreach ($data as $key => $row) {
            $row['jikr_release_on'] = $dt->formatDateTime($row['jikr_load_date'] . ' ' . $row['jikr_load_time']);
            if ($showGateIn === true) {
                if (empty($row['jikr_gate_in_date']) === false) {
                    $row['jikr_gate_in_on'] = $dt->formatDateTime($row['jikr_gate_in_date'] . ' ' . $row['jikr_gate_in_time']);
                } else {
                    $table->addCellAttribute('jikr_gate_in_on', $i, 'style', 'background-color:red; color:white');
                }
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('JoJikrPtl', Trans::getWord('releasedContainer'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the Container Portlet.
     *
     * @return Portlet
     */
    private function getContainerPortlet(): Portlet
    {
        $table = new Table('SoContainerTbl');
        $table->setHeaderRow([
            'soc_number' => Trans::getWord('containerId'),
            'soc_container_type' => Trans::getWord('containerType'),
            'soc_container_number' => Trans::getWord('containerNumber'),
            'soc_seal_number' => Trans::getWord('sealNumber'),
        ]);
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getIntParameter('jik_so_id'));
        $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
        $data = SalesOrderContainerDao::loadData($wheres);
        $table->addRows($data);
        # Create a portlet box.
        $portlet = new Portlet('JikContainerPtl', Trans::getWord('containers'));
        $portlet->addTable($table);

        return $portlet;

    }

    private function doExportTimeShtXls(): void
    {
        $excel = new Excel();
        $excel->addSheet('timeSheet', Trans::getWord('timeSheet'));
        $excel->setFileName($this->PageSetting->getPageDescription() . '.xlsx');
        $sheet = $excel->getSheet('timeSheet', true);
        $portlet = parent::getTimeSheetFieldSet();
        $excelTable = new ExcelTable($excel, $sheet);
        $excelTable->setTable($portlet->Body[0]);
        $excelTable->writeTable();
        $excel->setActiveSheet('timeSheet');
        $excel->createExcel();
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $this->EnableDelete = !$this->isValidParameter('jik_approve_on');
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
        $requireFieldError = $this->checkRequiredPublishFields([
            'jo_manager_id' => 'jobManager',
            'jo_vendor_id' => 'vendor',
        ]);
        $requiredDoc = $this->checkPublishRequiredDocument();
        if ($this->isValidParameter('so_publish_on') === false) {
            $p = new Paragraph(Trans::getMessageWord('requiredPublishSo'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else if (empty($requireFieldError) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredFields'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requireFieldError);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else if (empty($requiredDoc) === false) {
            $p = new Paragraph(Trans::getMessageWord('missingRequiredSoDocument'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->addText($requiredDoc);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } elseif ($this->isAllowPublishWithoutFinanceData() === false && (empty($this->JobSales) === true || empty($this->JobPurchase) === true)) {
            $p = new Paragraph(Trans::getMessageWord('emptyJobFinanceData'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $p = new Paragraph(Trans::getMessageWord('publishJobConfirmation'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
            $modal->setBtnOkName(Trans::getWord('yesPublish'));
        }

        return $modal;
    }

    /**
     * Function to get publish required document.
     *
     * @return string
     */
    private function checkPublishRequiredDocument(): string
    {
        $data = DocumentDao::getTotalByGroupAndType('salesorder', ['bl', 'invoiceorigin', 'packinglist'], $this->getIntParameter('jik_so_id'));
        $valid = true;
        $number = new NumberFormatter($this->getUser());
        $message = [];
        foreach ($data as $row) {
            $total = (int)$row['total_doc'];
            $value = $number->doFormatInteger($total);
            if ($total === 0) {
                $valid = false;
                $value = new LabelDanger($total);
            }
            $message[] = [
                'label' => $row['dct_description'],
                'value' => $value,
            ];
        }
        if ($valid === true) {
            return '';
        }
        return StringFormatter::generateCustomTableView($message, 8, 8);
    }

    /**
     * Function to get the release Goods Portlet.
     *
     * @return Portlet
     */
    private function getReleaseGoodsPortlet(): Portlet
    {
        $table = new Table('JoJikrTbl');
        $table->setHeaderRow([
            'jikr_hs_code' => Trans::getWord('hsCode'),
            'jikr_goods' => Trans::getWord('description'),
            'jikr_packing_ref' => Trans::getWord('packingRef'),
            'jikr_quantity' => Trans::getWord('quantity'),
            'jikr_uom_code' => Trans::getWord('uom'),
            'jikr_transporter' => Trans::getWord('transporter'),
            'jikr_truck_number' => Trans::getWord('truckPlate'),
            'jikr_driver' => Trans::getWord('driver'),
            'jikr_release_on' => Trans::getWord('releasedOn'),
        ]);
        $showGateIn = false;
        if ($this->getStringParameter('jik_plb', 'N') === 'Y' && $this->isValidParameter('jik_complete_release_on') === true) {
            $showGateIn = true;
            $table->addColumnAfter('jikr_release_on', 'jikr_gate_in_on', Trans::getWord('gatePass'));
        }
        $data = JobInklaringReleaseDao::getByJobInklring($this->getIntParameter('jik_id'));
        $i = 0;
        $rows = [];
        $dt = new DateTimeParser();
        foreach ($data as $key => $row) {
            $row['jikr_release_on'] = $dt->formatDateTime($row['jikr_load_date'] . ' ' . $row['jikr_load_time']);
            if ($showGateIn === true) {
                if (empty($row['jikr_gate_in_date']) === false) {
                    $row['jikr_gate_in_on'] = $dt->formatDateTime($row['jikr_gate_in_date'] . ' ' . $row['jikr_gate_in_time']);
                } else {
                    $table->addCellAttribute('jikr_gate_in_on', $i, 'style', 'background-color:red; color:white');
                }
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('JoJikrPtl', Trans::getWord('releasedGoods'));
        $portlet->addTable($table);
        return $portlet;
    }


}
