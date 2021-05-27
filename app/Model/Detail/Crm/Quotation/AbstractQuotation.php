<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Crm\Quotation;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\Quotation\PriceDao;
use App\Model\Dao\Crm\Quotation\PriceDetailDao;
use App\Model\Dao\Crm\Quotation\QuotationDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\Quotation\QuotationServiceDao;
use App\Model\Dao\Crm\Quotation\QuotationSubmitDao;
use App\Model\Dao\Crm\Quotation\QuotationTermsDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\EquipmentGroupDao;
use App\Model\Dao\System\Service\ServiceDao;
use Exception;

/**
 * Class to handle the creation of detail Quotation page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
abstract class AbstractQuotation extends AbstractFormModel
{
    /**
     * Property to store detail price
     *
     * @var array $Details
     */
    private $Details = [];

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        if ($this->getStringParameter('qt_type') === 'S') {
            $serialCode = 'SalesQuotation';
        } elseif ($this->getStringParameter('qt_type') === 'P') {
            $serialCode = 'PurchaseQuotation';
        } else {
            $serialCode = 'Quotation';
        }
        $number = $sn->loadNumber($serialCode, $this->getIntParameter('qt_order_of_id'), $this->getIntParameter('qt_rel_id'));
        $colVal = [
            'qt_ss_id' => $this->User->getSsId(),
            'qt_number' => $number,
            'qt_type' => $this->getStringParameter('qt_type'),
            'qt_rel_id' => $this->getIntParameter('qt_rel_id'),
            'qt_of_id' => $this->getIntParameter('qt_of_id'),
            'qt_cp_id' => $this->getIntParameter('qt_cp_id'),
            'qt_dl_id' => $this->getIntParameter('qt_dl_id'),
            'qt_order_of_id' => $this->getIntParameter('qt_order_of_id'),
            'qt_us_id' => $this->getIntParameter('qt_us_id'),
            'qt_commodity' => $this->getStringParameter('qt_commodity'),
            'qt_requirement' => $this->getStringParameter('qt_requirement'),
            'qt_start_date' => $this->getStringParameter('qt_start_date'),
            'qt_end_date' => $this->getStringParameter('qt_end_date'),
        ];
        $qtDao = new QuotationDao();
        $qtDao->doInsertTransaction($colVal);
        $this->doUpdateQuotationService($qtDao->getLastInsertId());
        return $qtDao->getLastInsertId();
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
                'qt_type' => $this->getStringParameter('qt_type'),
                'qt_rel_id' => $this->getIntParameter('qt_rel_id'),
                'qt_of_id' => $this->getIntParameter('qt_of_id'),
                'qt_cp_id' => $this->getIntParameter('qt_cp_id'),
                'qt_dl_id' => $this->getIntParameter('qt_dl_id'),
                'qt_order_of_id' => $this->getIntParameter('qt_order_of_id'),
                'qt_us_id' => $this->getIntParameter('qt_us_id'),
                'qt_commodity' => $this->getStringParameter('qt_commodity'),
                'qt_requirement' => $this->getStringParameter('qt_requirement'),
                'qt_start_date' => $this->getStringParameter('qt_start_date'),
                'qt_end_date' => $this->getStringParameter('qt_end_date'),
            ];
            $qtDao = new QuotationDao();
            $qtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            $this->doUpdateQuotationService($this->getDetailReferenceValue());
        } elseif ($this->getFormAction() === 'doUpdateTerm') {
            $qtmColVal = [
                'qtm_qt_id' => $this->getDetailReferenceValue(),
                'qtm_terms' => json_encode($this->getStringParameter('qtm_terms')),
            ];
            $qtmDao = new QuotationTermsDao();
            if ($this->isValidParameter('qtm_id') === false) {
                $qtmDao->doInsertTransaction($qtmColVal);
            } else {
                $qtmDao->doUpdateTransaction($this->getIntParameter('qtm_id'), $qtmColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteTerm') {
            $qtmDao = new QuotationTermsDao();
            $qtmDao->doDeleteTransaction($this->getIntParameter('qtm_id_del'));
        } elseif ($this->getFormAction() === 'doSubmit') {
            $qtsDao = new QuotationSubmitDao();
            $qtsDao->doInsertTransaction([
                'qts_qt_id' => $this->getDetailReferenceValue(),
            ]);
            $qtDao = new QuotationDao();
            $qtDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'qt_qts_id' => $qtsDao->getLastInsertId(),
            ]);
        } elseif ($this->getFormAction() === 'doReject') {
            $qtsDao = new QuotationSubmitDao();
            $qtsDao->doDeleteTransaction($this->getIntParameter('qt_qts_id'), $this->getStringParameter('qts_deleted_reason'));
        } elseif ($this->getFormAction() === 'doApprove') {
            $qtDao = new QuotationDao();
            $colVal = [
                'qt_approve_on' => date('Y-m-d H:i:s'),
                'qt_approve_by' => $this->User->getId(),
            ];
            $qtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

        } elseif ($this->isDeleteAction()) {
            $qtDao = new QuotationDao();
            $qtDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
            # Do delete price.
            $prices = PriceDao::getByQuotationIdForDelete($this->getDetailReferenceValue());
            $prcDao = new PriceDao();
            foreach ($prices as $row) {
                $prcDao->doDeleteTransaction($row['prc_id'], $this->getReasonDeleteAction());
            }
        } elseif ($this->getFormAction() === 'doCopy') {
            $this->doCopy();
        } elseif ($this->isUploadDocumentAction()) {
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doUploadDocument($colVal, $file);
            }
        } elseif ($this->isDeleteDocumentAction()) {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doQtExportExcel') {
            $this->doExportXls();
        }
    }


    /**
     * Function for create table Price
     *
     * @return Table
     */
    private function getListTruckingPrice(): Table
    {
        $tbl = new Table('TblTrcList');
        $db = PriceDetailDao::getByQuotationId($this->getIntParameter('qt_id'));
        $eg = EquipmentGroupDao::loadData();
        $dbPrepared = $this->doPrepareEquipmentPrice($db);
        if ($this->isValidParameter('qt_id') === true) {
            $tbl->setHeaderRow($this->getHeaderXls());
            foreach ($eg as $ro) {
                $tbl->setColumnType($ro['eg_name'], 'currency');
            }
            $tbl->addRows($dbPrepared);
            return $tbl;
        }
        return $tbl;
    }

    /**
     * Function for header excel
     * @return array
     */
    private function getHeaderXls(): array
    {
        $db = PriceDetailDao::getByQuotationId($this->getIntParameter('qt_id'));
        $headers = [];
        if (empty($db) === false) {
            $headers['prc_origin_district'] = Trans::getFinanceWord('origin');
            $headers['prc_destination_district'] = Trans::getFinanceWord('destination');
            foreach ($db as $row) {
                $headers[$row['prc_eg_name']] = $row['prc_eg_name'];
            }
        }
        return $headers;
    }

    /**
     * Function do prepare data Equipment Trucking
     * @param array $db
     * @return array
     */
    private function doPrepareEquipmentPrice(array $db): array
    {
        $result = [];
        $i = 0;
        $or = '';
        $des = '';
        foreach ($db as $row) {
            // Condition not same with before index
            if ($or !== $row['prc_origin_district'] || $des !== $row['prc_destination_district']) {
                $i++;
                $or = $row['prc_origin_district'];
                $des = $row['prc_destination_district'];
                $result[$i] = [
                    'prc_origin_district' => $row['prc_origin_district'],
                    'prc_destination_district' => $row['prc_destination_district'],
                    $row['prc_eg_name'] => $row['prd_rate']
                ];
            } else {
                // Same condition
                $result[$i][$row['prc_eg_name']] = $row['prd_rate'];
            }
        }
        return $result;
    }

    /**
     * Function do export excel
     */
    private function doExportXls()
    {
        $srv = ServiceDao::loadData();
        $excel = new Excel();
        try {
            # addsheet prepare
            $excel->setFileName($this->PageSetting->getPageDescription() . '.xlsx');
            # Set title on excel
            foreach (array_reverse($srv) as $item) {
                $sheetName = StringFormatter::formatExcelSheetTitle($item['srv_code']);
                $excel->addSheet($sheetName, $sheetName);
                if ($sheetName === 'trucking') {
                    $tbl = $this->getListTruckingPrice();
                    $sheet = $excel->getSheet($sheetName, true);
                    $sheet->mergeCells('A1:E1');
                    $sheet->setCellValue('A1', Trans::getFinanceWord('truckingPrice'));
                    $sheet->getStyle('A1')->getFont()->setBold(false);
                    $sheet->mergeCells('A2:E2');
                    $sheet->setCellValue('A2', $this->loadData()['qt_relation']);
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $excel->doRowMovePointer($sheetName);

                    # Set table
                    $excelTable = new ExcelTable($excel, $sheet);
                    $excelTable->setTable($tbl);
                    $excelTable->writeTable();
                }
                $excel->setActiveSheet('trucking');
            }
        } catch (Exception $e) {
            $this->View->addErrorMessage('Failed to generate excel file.');
        }
        $excel->createExcel();
    }

    /**
     * Function to copy quotation.;
     *
     * @return void
     */
    protected function doCopy(): void
    {
        $sn = new SerialNumber($this->User->getSsId());
        if ($this->getStringParameter('qt_type') === 'S') {
            $serialCode = 'SalesQuotation';
        } elseif ($this->getStringParameter('qt_type') === 'P') {
            $serialCode = 'PurchaseQuotation';
        } else {
            $serialCode = 'Quotation';
        }
        $number = $sn->loadNumber($serialCode, $this->getIntParameter('qt_order_of_id_cp'), $this->getIntParameter('qt_rel_id_cp'));
        $colVal = [
            'qt_ss_id' => $this->User->getSsId(),
            'qt_number' => $number,
            'qt_type' => $this->getStringParameter('qt_type'),
            'qt_rel_id' => $this->getIntParameter('qt_rel_id_cp'),
            'qt_of_id' => $this->getIntParameter('qt_of_id_cp'),
            'qt_order_of_id' => $this->getIntParameter('qt_order_of_id_cp'),
            'qt_us_id' => $this->getIntParameter('qt_us_id_cp'),
            'qt_commodity' => $this->getStringParameter('qt_commodity'),
            'qt_requirement' => $this->getStringParameter('qt_requirement'),
            'qt_start_date' => $this->getStringParameter('qt_start_date_cp'),
            'qt_end_date' => $this->getStringParameter('qt_end_date_cp'),
        ];
        $qtDao = new QuotationDao();
        $qtDao->doInsertTransaction($colVal);
        $this->doCopyQuotationService($qtDao->getLastInsertId());

        # Copy Terms
        $qtmDao = new QuotationTermsDao();
        $terms = QuotationTermsDao::getByQuotationId($this->getDetailReferenceValue());
        foreach ($terms as $row) {
            $qtmDao->doInsertTransaction([
                'qtm_qt_id' => $qtDao->getLastInsertId(),
                'qtm_terms' => json_encode($row['qtm_terms']),
            ]);
        }

        # Do Copy Price and price detail
        $prices = PriceDetailDao::loadDefaultDataByQuotation($this->getDetailReferenceValue());
        $tempId = [];
        $prcDao = new PriceDao();
        $prdDao = new PriceDetailDao();
        foreach ($prices as $row) {
            if (array_key_exists($row['prc_id'], $tempId) === false) {
                $prcDao->doInsertTransaction([
                    'prc_ss_id' => $this->User->getSsId(),
                    'prc_qt_id' => $qtDao->getLastInsertId(),
                    'prc_code' => $row['prc_code'],
                    'prc_type' => $row['prc_type'],
                    'prc_rel_id' => $this->getStringParameter('qt_rel_id_cp'),
                    'prc_srv_id' => $row['prc_srv_id'],
                    'prc_srt_id' => $row['prc_srt_id'],
                    'prc_lead_time' => $row['prc_lead_time'],
                    'prc_ct_id' => $row['prc_ct_id'],
                    'prc_eg_id' => $row['prc_eg_id'],
                    'prc_dtc_origin' => $row['prc_dtc_origin'],
                    'prc_dtc_destination' => $row['prc_dtc_destination'],
                    'prc_wh_id' => $row['prc_wh_id'],
                    'prc_tm_id' => $row['prc_tm_id'],
                    'prc_cct_id' => $row['prc_cct_id'],
                    'prc_po_id' => $row['prc_po_id'],

                ]);
                $tempId[$row['prc_id']] = $prcDao->getLastInsertId();
            }
            # Do Copy Price Detail
            $prdDao->doInsertTransaction([
                'prd_prc_id' => $tempId[$row['prc_id']],
                'prd_cc_id' => $row['prd_cc_id'],
                'prd_description' => $row['prd_description'],
                'prd_quantity' => $row['prd_quantity'],
                'prd_uom_id' => $row['prd_uom_id'],
                'prd_rate' => $row['prd_rate'],
                'prd_minimum_rate' => $row['prd_minimum_rate'],
                'prd_cur_id' => $row['prd_cur_id'],
                'prd_exchange_rate' => $row['prd_exchange_rate'],
                'prd_tax_id' => $row['prd_tax_id'],
                'prd_total' => $row['prd_total'],
                'prd_remark' => $row['prd_remark'],
            ]);
        }
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param int $qtId To store the quotation reference value
     *
     * @return void
     */
    private function doUpdateQuotationService($qtId): void
    {
        $qsSrvIds = $this->getArrayParameter('qs_srv_id');
        $qsIds = $this->getArrayParameter('qs_id');
        $qsActives = $this->getArrayParameter('qs_active');
        if (count($qsIds) > 0) {
            $qsDao = new QuotationServiceDao();
            foreach ($qsIds as $key => $value) {
                if (array_key_exists($key, $qsActives) === true && $qsActives[$key] === 'Y') {
                    if (empty($value) === true) {
                        $colVal = [
                            'qs_qt_id' => $qtId,
                            'qs_srv_id' => $qsSrvIds[$key],
                        ];
                        $qsDao->doInsertTransaction($colVal);
                    } else {
                        $qsDao->doUndoDeleteTransaction($value);
                    }
                } else {
                    if (empty($value) === false) {
                        $qsDao->doDeleteTransaction($value);
                    }
                }
            }
        }
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param int $qtId To store the quotation reference value
     *
     * @return void
     */
    private function doCopyQuotationService($qtId): void
    {
        $qsSrvIds = $this->getArrayParameter('qs_srv_id');
        $qsIds = $this->getArrayParameter('qs_id');
        $qsActives = $this->getArrayParameter('qs_active');
        if (count($qsIds) > 0) {
            $qsDao = new QuotationServiceDao();
            foreach ($qsIds as $key => $value) {
                if (array_key_exists($key, $qsActives) === true && $qsActives[$key] === 'Y') {
                    $colVal = [
                        'qs_qt_id' => $qtId,
                        'qs_srv_id' => $qsSrvIds[$key],
                    ];
                    $qsDao->doInsertTransaction($colVal);
                }
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
        return QuotationDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getRelationPortlet());
        $this->Tab->addPortlet('general', $this->getScopePortlet());
        $this->Tab->addPortlet('general', $this->getDetailPortlet());
        if ($this->isUpdate() === true) {
            $this->overridePageTitle();
            $this->doPreparePrice();
            $this->Tab->addPortlet('general', $this->getTermsPortlet());
            $this->Tab->addPortlet('price', $this->getPricePortlet());
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('quotation', $this->getDetailReferenceValue()));
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetPortlet());
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
            $this->Validation->checkRequire('qt_rel_id');
            $this->Validation->checkRequire('qt_of_id');
            $this->Validation->checkRequire('qt_order_of_id');
            $this->Validation->checkRequire('qt_us_id');
            $this->Validation->checkRequire('qt_start_date');
            $this->Validation->checkRequire('qt_end_date');
            $this->Validation->checkMaxLength('qt_commodity', 256);
            $this->Validation->checkMaxLength('qt_requirement', 256);
            $this->Validation->checkRequireArray('qs_active', 1);
        } elseif ($this->getFormAction() === 'doUpdateTerm') {
            $this->Validation->checkRequire('qtm_terms', 2);
        } elseif ($this->getFormAction() === 'doDeleteTerm') {
            $this->Validation->checkRequire('qtm_id_del');
        } elseif ($this->getFormAction() === 'doReject') {
            $this->Validation->checkRequire('qt_qts_id');
            $this->Validation->checkRequire('qts_deleted_reason', 2, 256);
        } elseif ($this->getFormAction() === 'doCopy') {
            $this->Validation->checkRequire('qt_rel_id_cp');
            $this->Validation->checkRequire('qt_of_id_cp');
            $this->Validation->checkRequire('qt_order_of_id_cp');
            $this->Validation->checkRequire('qt_us_id_cp');
            $this->Validation->checkRequire('qt_start_date_cp');
            $this->Validation->checkRequire('qt_end_date_cp');
            $this->Validation->checkMaxLength('qt_commodity', 256);
            $this->Validation->checkMaxLength('qt_requirement', 256);
            $this->Validation->checkRequireArray('qs_active', 1);
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the customer Field Set.
     *
     * @return Portlet
     */
    private function getRelationPortlet(): Portlet
    {
        if ($this->getStringParameter('qt_type') === 'S') {
            $relation = Trans::getFinanceWord('customer');
            $office = Trans::getFinanceWord('customerOffice');
            $pic = Trans::getFinanceWord('picCustomer');
        } elseif ($this->getStringParameter('qt_type') === 'P') {
            $relation = Trans::getFinanceWord('vendor');
            $office = Trans::getFinanceWord('vendorOffice');
            $pic = Trans::getFinanceWord('picVendor');
        } else {
            $relation = Trans::getFinanceWord('relation');
            $office = Trans::getFinanceWord('relationOffice');
            $pic = Trans::getFinanceWord('picRelation');
        }
        # Instantiate Portlet Object
        $portlet = new Portlet('QtCusPtl', $relation);
        $portlet->setGridDimension(4, 4, 4, 12);

        # Create customs field.
        # Relation
        $relationField = $this->Field->getSingleSelect('relation', 'qt_relation', $this->getStringParameter('qt_relation'));
        $relationField->setHiddenField('qt_rel_id', $this->getIntParameter('qt_rel_id'));
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->addClearField('qt_office');
        $relationField->addClearField('qt_of_id');
        $relationField->addClearField('qt_pic_relation');
        $relationField->addClearField('qt_cp_id');
        # Office
        $officeField = $this->Field->getSingleSelect('office', 'qt_office', $this->getStringParameter('qt_office'));
        $officeField->setHiddenField('qt_of_id', $this->getIntParameter('qt_of_id'));
        $officeField->setDetailReferenceCode('of_id');
        $officeField->addParameterById('of_rel_id', 'qt_rel_id', $relation);
        $officeField->addClearField('qt_pic_relation');
        $officeField->addClearField('qt_cp_id');
        # Contact Person
        $cpField = $this->Field->getSingleSelect('contactPerson', 'qt_pic_relation', $this->getStringParameter('qt_pic_relation'));
        $cpField->setHiddenField('qt_cp_id', $this->getIntParameter('qt_cp_id'));
        $cpField->setDetailReferenceCode('cp_id');
        $cpField->addParameterById('cp_rel_id', 'qt_rel_id', $relation);
        $cpField->addParameterById('cp_of_id', 'qt_of_id', $office);
        # Deal
        $dealField = $this->Field->getSingleSelect('deal', 'qt_deal', $this->getStringParameter('qt_deal'));
        $dealField->setHiddenField('qt_dl_id', $this->getIntParameter('qt_dl_id'));
        $dealField->setEnableNewButton(false);
        $dealField->addParameterById('dl_rel_id', 'qt_rel_id', $relation);
        $dealField->addParameter('dl_ss_id', $this->User->getSsId());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add field into field set.
        $fieldSet->addField($relation, $relationField, true);
        $fieldSet->addField($office, $officeField, true);
        $fieldSet->addField($pic, $cpField);
        $fieldSet->addField(Trans::getFinanceWord('dealReference'), $dealField);

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the scope Field Set.
     *
     * @return Portlet
     */
    private function getScopePortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('QtScopePtl', Trans::getFinanceWord('scopeOfService'));
        $portlet->setGridDimension(4, 4, 4, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $data = QuotationServiceDao::loadDataForCheckBoxInput($this->User->getSsId(), $this->getDetailReferenceValue());
        $index = 0;
        $serviceField = $this->Field->getCheckBoxGroup('qs_active');
        foreach ($data as $row) {
            $fieldSet->addHiddenField($this->Field->getHidden('qs_id[' . $index . ']', $row['qs_id']));
            $fieldSet->addHiddenField($this->Field->getHidden('qs_srv_id[' . $index . ']', $row['srv_id']));
            $serviceField->addCheckBox($row['srv_name'], 'Y', $row['qs_active']);
            $index++;
        }


        # Add field into field set.
        $fieldSet->addField(Trans::getFinanceWord('service'), $serviceField, true);
        $fieldSet->addField(Trans::getFinanceWord('commodity'), $this->Field->getText('qt_commodity', $this->getStringParameter('qt_commodity')));
        $fieldSet->addField(Trans::getFinanceWord('requirement'), $this->Field->getText('qt_requirement', $this->getStringParameter('qt_requirement')));


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the detail Field Set.
     *
     * @return Portlet
     */
    private function getDetailPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('QtDetailPtl', Trans::getFinanceWord('detail'));
        $portlet->setGridDimension(4, 4, 4, 12);

        # Create customs field.
        # Office
        $officeField = $this->Field->getSingleSelect('office', 'qt_order_office', $this->getStringParameter('qt_order_office'));
        $officeField->setHiddenField('qt_order_of_id', $this->getIntParameter('qt_order_of_id'));
        $officeField->setEnableNewButton(false);
        $officeField->addParameter('of_rel_id', $this->User->getRelId());

        # Manager Field
        $usField = $this->Field->getSingleSelect('user', 'qt_manager', $this->getStringParameter('qt_manager'));
        $usField->setHiddenField('qt_us_id', $this->getIntParameter('qt_us_id'));
        $usField->setEnableNewButton(false);
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->addParameter('rel_id', $this->User->getRelId());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getFinanceWord('orderOffice'), $officeField, true);
        $fieldSet->addField(Trans::getFinanceWord('quotationManager'), $usField, true);
        $fieldSet->addField(Trans::getFinanceWord('startDate'), $this->Field->getCalendar('qt_start_date', $this->getStringParameter('qt_start_date')), true);
        $fieldSet->addField(Trans::getFinanceWord('endDate'), $this->Field->getCalendar('qt_end_date', $this->getStringParameter('qt_end_date')), true);
        $fieldSet->addHiddenField($this->Field->getHidden('qt_type', $this->getStringParameter('qt_type')));


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the price Field Set.
     *
     * @return Portlet
     */
    private function getPricePortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('QtPrcPtl', Trans::getFinanceWord('priceDetail'));
        $portlet->setGridDimension(12, 12, 12);

        $tbl = new Table('QtPrcTbl');
        $tbl->setHeaderRow([
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_service' => Trans::getFinanceWord('service'),
            'prc_detail' => Trans::getFinanceWord('detail'),
            'prc_description' => Trans::getFinanceWord('description'),
            'prd_tax' => Trans::getFinanceWord('tax'),
            'prd_total' => Trans::getFinanceWord('price'),
            'prd_minimum_rate' => Trans::getFinanceWord('minCharge'),
            'prc_action' => Trans::getFinanceWord('update'),
        ]);
        $tbl->addRows($this->Details);
        $tbl->addColumnAttribute('prd_minimum_rate', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('prd_total', 'style', 'text-align: right;');
        $tbl->addColumnAttribute('prc_container_type', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('prc_code', 'style', 'text-align: center;');
        $tbl->addColumnAttribute('prc_action', 'style', 'text-align: center;');

        $portlet->addTable($tbl);
        # Add Button
        if ($this->isAllowUpdate() === true) {
            $services = QuotationServiceDao::getByQuotationId($this->getDetailReferenceValue());
            $index = 0;
            foreach ($services as $row) {
                $route = PriceDao::getDetailRoute($this->getStringParameter('qt_type'), $row['qs_srv_code']);
                $route .= '?prc_qt_id=' . $this->getDetailReferenceValue();
                $btnAdd = new HyperLink('btbAddPrc' . $index, Trans::getFinanceWord('addPrice') . ' ' . $row['qs_service'], url($route));
                $btnAdd->viewAsButton();
                $btnAdd->setIcon(Icon::Plus);
                $btnAdd->btnMedium();
                $btnAdd->btnSuccess();
                $btnAdd->pullRight();
                $portlet->addButton($btnAdd);
                $index++;
            }

        }


        return $portlet;
    }

    /**
     * Function to prepare price data.
     *
     * @return void
     */
    private function doPreparePrice(): void
    {
        $this->Details = [];
        $data = PriceDetailDao::getByQuotationId($this->getDetailReferenceValue());
        $index = 0;
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['prc_service'] = $row['prc_srv_name'];
            if (empty($row['prc_srt_name']) === false) {
                $row['prc_service'] .= ' - ' . $row['prc_srt_name'];

            }
            # Split between service
            $details = [];
            if ($row['prc_srv_code'] === 'warehouse') {
                $details[] = Trans::getFinanceWord('warehouse') . ' : ' . $row['prc_warehouse'];
            } elseif ($row['prc_srv_code'] === 'inklaring') {
                $details[] = Trans::getFinanceWord('module') . ' : ' . $row['prc_transport_module'];
                if ($row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('port') . ' : ' . $row['prc_pod_name'] . ' (' . $row['prc_pod_code'] . ') - ' . $row['prc_pod_country'];
                } else {
                    $details[] = Trans::getFinanceWord('port') . ' : ' . $row['prc_pol_name'] . ' (' . $row['prc_pol_code'] . ') - ' . $row['prc_pol_country'];
                }
                if (empty($row['prc_custom_clearance_type']) === false) {
                    $details[] = Trans::getFinanceWord('type') . ' : ' . $row['prc_custom_clearance_type'];
                }
            } elseif ($row['prc_srv_code'] === 'delivery') {
                $origin = $row['prc_origin_district'] . ', ' . $row['prc_origin_city'] . ', ' . $row['prc_origin_state'];
                if (empty($row['prc_origin_address']) === false) {
                    $origin = $row['prc_origin_address'] . ', ' . $origin;
                }
                $destination = $row['prc_destination_district'] . ', ' . $row['prc_destination_city'] . ', ' . $row['prc_destination_state'];
                if (empty($row['prc_destination_address']) === false) {
                    $destination = $row['prc_destination_address'] . ', ' . $destination;
                }
                if ($row['prc_srt_pol'] === 'Y' && $row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                    $details[] = Trans::getFinanceWord('module') . ' : ' . $row['prc_transport_module'];
                } elseif ($row['prc_srt_pol'] === 'Y' && $row['prc_srt_unload'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $destination;
                } elseif ($row['prc_srt_load'] === 'Y' && $row['prc_srt_pod'] === 'Y') {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $origin;
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
                } else {
                    $details[] = Trans::getFinanceWord('from') . ' : ' . $origin;
                    $details[] = Trans::getFinanceWord('to') . ' : ' . $destination;
                }
                $details[] = Trans::getFinanceWord('transport') . ' : ' . $row['prc_eg_name'];
            }
            if (empty($row['prc_container_type']) === false) {
                $details[] = Trans::getFinanceWord('containerType') . ' : ' . $row['prc_container_type'];
            }
            $row['prc_detail'] = StringFormatter::generateTableView($details);
            $currency = $row['prd_currency'];
            if (empty($row['prd_currency_exchange']) === false) {
                $currency = $this->User->Settings->getCurrencyIso();
            }
            $rate = $currency . ' ' . $number->doFormatFloat($row['prd_rate']);
            $description = [];
            $description[] = Trans::getFinanceWord('coa') . ' : ' . $row['prd_cost_code'];
            $description[] = $row['prd_description'];
            $description[] = $number->doFormatFloat($row['prd_quantity']) . ' ' . $row['prd_uom_code'] . ' * ' . $rate;
            $row['prc_description'] = StringFormatter::generateTableView($description);
            $row['prd_total'] = $currency . ' ' . $number->doFormatFloat($row['prd_total']);
            if(empty($row['prd_minimum_rate']) === false) {
                $row['prd_minimum_rate'] = $currency . ' ' . $number->doFormatFloat($row['prd_minimum_rate']);
            }

            # Create update button
            $route = PriceDao::getDetailRoute($row['prc_type'], $row['prc_srv_code']) . '?prc_id=' . $row['prc_id'];
            $btnEdit = new HyperLink('btnEdit' . $index, '', url($route));
            $btnEdit->viewAsButton();
            $btnEdit->setIcon(Icon::Edit)
                ->viewAsButton()
                ->viewIconOnly()
                ->btnMedium()
                ->btnPrimary();
            $row['prc_action'] = $btnEdit;
            $this->Details[] = $row;
            $index++;
        }
    }

    /**
     * Function to get the term Field Set.
     *
     * @return Portlet
     */
    private function getTermsPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('QtTermsPtl', Trans::getFinanceWord('termAndCondition'));
        $portlet->setGridDimension(12, 12, 12);

        # Create Table
        $tbl = new Table('QtTermsTbl');
        $tbl->setHeaderRow([
            'qtm_terms' => Trans::getFinanceWord('termAndCondition'),
        ]);
        $data = QuotationTermsDao::getByQuotationId($this->getDetailReferenceValue());
        $tbl->addRows($data);
        if ($this->isAllowUpdate() === true) {
            $insertMdl = $this->getTermsInsertModal();
            $this->View->addModal($insertMdl);
            $deleteMdl = $this->getTermsDeleteModal();
            $this->View->addModal($deleteMdl);
            # Add table action
            $tbl->setUpdateActionByModal($insertMdl, 'qtm', 'getById', ['qtm_id']);
            $tbl->setDeleteActionByModal($deleteMdl, 'qtm', 'getByIdForDelete', ['qtm_id']);
            # Add portlet button.
            $btn = new ModalButton('AddQtmBtn', Trans::getFinanceWord('addTerm'), $insertMdl->getModalId());
            $btn->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btn);
        }
        $portlet->addTable($tbl);


        return $portlet;
    }

    /**
     * Function to get the modal form for adding terms.
     *
     * @return Modal
     */
    private function getTermsInsertModal(): Modal
    {
        $modal = new Modal('QtQtmMdl', Trans::getFinanceWord('addTermAndCondition'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateTerm');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateTerm' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        # Create Field Set.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getFinanceWord('termAndCondition'), $this->Field->getTextArea('qtm_terms', $this->getParameterForModal('qtm_terms', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('qtm_id', $this->getParameterForModal('qtm_id', $showModal)));
        $modal->addFieldSet($fieldSet);
        return $modal;
    }

    /**
     * Function to get the modal form for deleting terms.
     *
     * @return Modal
     */
    private function getTermsDeleteModal(): Modal
    {
        $modal = new Modal('QtQtmDelMdl', Trans::getFinanceWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteTerm');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteTerm' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        # Create Field Set.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getFinanceWord('termAndCondition'), $this->Field->getTextArea('qtm_terms_del', $this->getParameterForModal('qtm_terms_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('qtm_id_del', $this->getParameterForModal('qtm_id_del', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));
        $modal->addFieldSet($fieldSet);
        return $modal;
    }

    /**
     * Function to get the term Field Set.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->PageSetting->getPageDescription();
        if ($this->isValidParameter('qt_number') === true) {
            $title = $this->getStringParameter('qt_number');
        }
        $qtDao = new QuotationDao();
        $title .= ' | ' . $qtDao->getStatus($this->getAllParameters());
        $this->View->setDescription($title);

        # Show deleted message
        if ($this->isDeleted() === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', '', [
                'user' => $this->getStringParameter('qt_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('qt_deleted_on')),
                'reason' => $this->getStringParameter('qt_deleted_reason'),
            ]));
        }
        # Show rejected message
        if ($this->isRejected() === true) {
            $this->View->addWarningMessage(Trans::getMessageWord('quotationRejected', '', [
                'user' => $this->getStringParameter('qt_qts_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('qt_qts_deleted_on')),
                'reason' => $this->getStringParameter('qt_qts_deleted_reason'),
            ]));
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->PageSetting->checkPageRight('AllowExportXls') === true) {
            $btnXls = new SubmitButton('QtExportXls', Trans::getFinanceWord('exportXls'), 'doQtExportExcel', $this->getMainFormId());
            $btnXls->setIcon(Icon::Download)->setEnableLoading(false)->btnPrimary()->pullRight();
            $btnXls->setEnableLoading(false);
            $btnXls->addAttribute('class', 'btn btn-primary pull-right btn-sm');
            $this->View->addButton($btnXls);
        }

        if ($this->isUpdate() === true) {
            if ($this->isDeleted() === true) {
                $this->setEnableDeleteButton(false);
            } else {
                $this->setEnableDeleteButton();
                if ($this->isApproved() === true || !$this->isDraft()) {
                    $this->setDisableUpdate();
                    # Create copy button
                    $copyMdl = $this->getCopyModal();
                    $this->View->addModal($copyMdl);
                    $btnCopy = new ModalButton('QtCpBtn', Trans::getFinanceWord('copy'), $copyMdl->getModalId());
                    $btnCopy->setIcon(Icon::Copy)->btnDark()->pullRight();
                    $this->View->addButton($btnCopy);
                }
                # Create submit button
                if ($this->isApproved() === false && $this->isDraft() === true) {
                    $submitMdl = $this->getSubmitModal();
                    $this->View->addModal($submitMdl);
                    $btnSubmit = new ModalButton('QtSubmitBtn', Trans::getFinanceWord('submit'), $submitMdl->getModalId());
                    $btnSubmit->setIcon(Icon::LocationArrow)->btnMedium()->btnPrimary()->pullRight();
                    $this->View->addButton($btnSubmit);
                }
                # Create print button
                if ($this->isDraft() === false && $this->getStringParameter('qt_type', 'P') === 'S') {
                    $btnPrint = new PdfButton('printBtn', 'Print', 'quotation');
                    $btnPrint->setIcon(Icon::Tasks)->btnPrimary()->pullRight();
                    $btnPrint->addParameter('qt_id', $this->getDetailReferenceValue());
                    $this->View->addButton($btnPrint);
                }
                # Create approve button
                if ($this->isDraft() === false && $this->isApproved() === false) {
                    # Button Reject
                    $rejectMdl = $this->getRejectModal();
                    $this->View->addModal($rejectMdl);
                    $btnReject = new ModalButton('qtRejectBtn', Trans::getFinanceWord('reject'), $rejectMdl->getModalId());
                    $btnReject->setIcon(Icon::Remove)->btnDanger()->pullRight();
                    $this->View->addButton($btnReject);

                    # Button Approve
                    $approveMdl = $this->getApproveModal();
                    $this->View->addModal($approveMdl);
                    $btnApprove = new ModalButton('QtApproveBtn', Trans::getFinanceWord('approve'), $approveMdl->getModalId());
                    $btnApprove->setIcon(Icon::Check)->btnSuccess()->pullRight();
                    $this->View->addButton($btnApprove);
                }
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function to check is data can be updated or not.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return ($this->isValidParameter('qt_deleted_on') === false && $this->isValidParameter('qt_approve_on') === false &&
            ($this->isValidParameter('qt_qts_id') === false || ($this->isValidParameter('qt_qts_id') === true && $this->isValidParameter('qt_qts_deleted_on') === true)));
    }

    /**
     * Function to check is status draft or rejected.
     *
     * @return bool
     */
    private function isDraft(): bool
    {
        return !$this->isValidParameter('qt_qts_id') || ($this->isValidParameter('qt_qts_id') === true && $this->isValidParameter('qt_qts_deleted_on') === true);
    }

    /**
     * Function to check is status draft or rejected.
     *
     * @return bool
     */
    private function isRejected(): bool
    {
        return $this->isValidParameter('qt_qts_id') === true && $this->isValidParameter('qt_qts_deleted_on') === true;
    }

    /**
     * Function to check is status deleted.
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('qt_deleted_on');
    }

    /**
     * Function to check is status approved.
     *
     * @return bool
     */
    private function isApproved(): bool
    {
        return $this->isValidParameter('qt_approve_on');
    }

    /**
     * Function to get submit modal.
     *
     * @return Modal
     */
    private function getSubmitModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('QtSubmitMdl', Trans::getFinanceWord('submitConfirmation'));
        if (empty($this->Details) === true) {
            $modal->setTitle(Trans::getFinanceWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getWord('unableToSubmitQuotation', 'message');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getWord('submitQuotationConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doSubmit');
        }
        $modal->setBtnOkName(Trans::getFinanceWord('yesSubmit'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get approve modal.
     *
     * @return Modal
     */
    private function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('QtAppMdl', Trans::getFinanceWord('approveConfirmation'));
        $valid = PriceDetailDao::checkEmptyCostCodeForQuotation($this->getDetailReferenceValue());
        if ($valid === false) {
            $modal->setTitle(Trans::getFinanceWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getWord('unableToApproveQuotation', 'message');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getWord('approveQuotationConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doApprove');
        }
        $modal->setBtnOkName(Trans::getFinanceWord('yesApprove'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get reject modal.
     *
     * @return Modal
     */
    private function getRejectModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('qtRjtMdl', Trans::getFinanceWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReject');
        $showModal = false;
        if ($this->getFormAction() === 'doReject' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);

        # Add field into field set.
        $fieldSet->addField(Trans::getFinanceWord('reason'), $this->Field->getTextArea('qts_deleted_reason', $this->getParameterForModal('qts_deleted_reason', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('qt_qts_id', $this->getParameterForModal('qt_qts_id', $showModal)));

        $p = new Paragraph(Trans::getMessageWord('rejectQuotationConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFinanceWord('yesReject'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get copy modal.
     *
     * @return Modal
     */
    private function getCopyModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('qtCpMdl', Trans::getFinanceWord('copyQuotation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doCopy');
        $showModal = false;
        if ($this->getFormAction() === 'doCopy' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        # add custom field
        if ($this->getStringParameter('qt_type') === 'S') {
            $relation = Trans::getFinanceWord('customer');
            $office = Trans::getFinanceWord('customerOffice');
        } elseif ($this->getStringParameter('qt_type') === 'P') {
            $relation = Trans::getFinanceWord('vendor');
            $office = Trans::getFinanceWord('vendorOffice');
        } else {
            $relation = Trans::getFinanceWord('relation');
            $office = Trans::getFinanceWord('relationOffice');
        }
        # Relation
        $relationField = $this->Field->getSingleSelect('relation', 'qt_relation_cp', $this->getParameterForModal('qt_relation_cp', $showModal));
        $relationField->setHiddenField('qt_rel_id_cp', $this->getParameterForModal('qt_rel_id_cp', $showModal));
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->addClearField('qt_office_cp');
        $relationField->addClearField('qt_of_id_cp');
        # Office
        $officeField = $this->Field->getSingleSelect('office', 'qt_office_cp', $this->getParameterForModal('qt_office_cp', $showModal));
        $officeField->setHiddenField('qt_of_id_cp', $this->getParameterForModal('qt_of_id_cp', $showModal));
        $officeField->setDetailReferenceCode('of_id');
        $officeField->addParameterById('of_rel_id', 'qt_rel_id_cp', $relation);
        # Office
        $orderOfficeField = $this->Field->getSingleSelect('office', 'qt_order_office_cp', $this->getParameterForModal('qt_order_office_cp', $showModal));
        $orderOfficeField->setHiddenField('qt_order_of_id_cp', $this->getParameterForModal('qt_order_of_id_cp', $showModal));
        $orderOfficeField->setEnableNewButton(false);
        $orderOfficeField->addParameter('of_rel_id', $this->User->getRelId());

        # Manager Field
        $usField = $this->Field->getSingleSelect('user', 'qt_manager_cp', $this->getParameterForModal('qt_manager_cp', $showModal));
        $usField->setHiddenField('qt_us_id_cp', $this->getParameterForModal('qt_us_id_cp', $showModal));
        $usField->setEnableNewButton(false);
        $usField->addParameter('ss_id', $this->User->getSsId());
        $usField->addParameter('rel_id', $this->User->getRelId());


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);


        $fieldSet->addField($relation, $relationField, true);
        $fieldSet->addField($office, $officeField, true);
        $fieldSet->addField(Trans::getFinanceWord('orderOffice'), $orderOfficeField, true);
        $fieldSet->addField(Trans::getFinanceWord('quotationManager'), $usField, true);
        $fieldSet->addField(Trans::getFinanceWord('startDate'), $this->Field->getCalendar('qt_start_date_cp', $this->getParameterForModal('qt_start_date_cp', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('endDate'), $this->Field->getCalendar('qt_end_date_cp', $this->getParameterForModal('qt_end_date_cp', $showModal)), true);

        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getTimeSheetPortlet(): Portlet
    {
        $table = new Table('QtTsTbl');
        $table->setHeaderRow([
            'qt_action' => Trans::getWord('action'),
            'qt_time' => Trans::getWord('time'),
            'qt_user' => Trans::getWord('user'),
            'qt_remark' => Trans::getWord('remark'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->setColumnType('qt_time', 'datetime');
        # Create a portlet box.
        $portlet = new Portlet('QtTsPtl', Trans::getWord('timeSheet'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $data = QuotationSubmitDao::getByQuotationId($this->getDetailReferenceValue());
        if ($this->isValidParameter('qt_deleted_on') === true) {
            $result[] = [
                'qt_action' => Trans::getWord('deleted'),
                'qt_time' => $this->getStringParameter('qt_deleted_on'),
                'qt_user' => $this->getStringParameter('qt_deleted_by'),
                'qt_remark' => $this->getStringParameter('qt_deleted_reason'),
            ];
        }
        if ($this->isValidParameter('qt_approve_on') === true) {
            $result[] = [
                'qt_action' => Trans::getWord('approved'),
                'qt_time' => $this->getStringParameter('qt_approve_on'),
                'qt_user' => $this->getStringParameter('qt_approve_by'),
                'qt_remark' => '',
            ];
        }
        foreach ($data as $row) {
            if (empty($row['qts_deleted_on']) === false) {
                $result[] = [
                    'qt_action' => Trans::getFinanceWord('rejected'),
                    'qt_time' => $row['qts_deleted_on'],
                    'qt_user' => $row['qts_deleted_by'],
                    'qt_remark' => $row['qts_deleted_reason'],
                ];
            }
            $result[] = [
                'qt_action' => Trans::getFinanceWord('submitted'),
                'qt_time' => $row['qts_created_on'],
                'qt_user' => $row['qts_created_by'],
                'qt_remark' => '',
            ];
        }
        $result[] = [
            'qt_action' => Trans::getWord('created'),
            'qt_time' => $this->getStringParameter('qt_created_on'),
            'qt_user' => $this->getStringParameter('qt_created_by'),
            'qt_remark' => '',
        ];


        return $result;
    }
}
