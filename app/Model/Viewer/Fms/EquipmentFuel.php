<?php
/**
 * Contains code written by the Spada Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Viewer\Fms;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Fms\EquipmentFuelDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail EquipmentFuel page
 *
 * @package    app
 * @subpackage Model\Viewer\Fms
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class EquipmentFuel extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'equipmentFuel', 'eqf_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUploadRequireDoc') {
            $listRow = $this->getArrayParameter('eqf_row_id');
            foreach ($listRow as $index) {
                $file = $this->getFileParameter('eqf_doc' . $index);
                if ($file !== null) {
                    $colVal = [
                        'doc_ss_id' => $this->User->getSsId(),
                        'doc_dct_id' => $this->getStringParameter('eqf_doc_type' . $index),
                        'doc_group_reference' => $this->getDetailReferenceValue(),
                        'doc_type_reference' => null,
                        'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                        'doc_description' => $this->getStringParameter('eqf_doc_description' . $index),
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
        } elseif ($this->getFormAction() === 'doConfirmEquipmentFuel') {
            $colVal = [
                'eqf_confirm_on' => date('Y-m-d H:i:s'),
                'eqf_confirm_by' => $this->User->getId()
            ];
            $eqfDao = new EquipmentFuelDao();
            $eqfDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
            $colValMeter = [
                'eqm_eq_id' => $this->getIntParameter('eqf_eq_id'),
                'eqm_date' => $this->getStringParameter('eqf_date'),
                'eqm_meter' => $this->getFloatParameter('eqf_meter'),
                'eqm_source' => Trans::getFmsWord('fuelEntry')
            ];
            $eqmDao = new EquipmentMeterDao();
            $eqmDao->doInsertTransaction($colValMeter);
        } elseif ($this->getFormAction() === 'doUpdateDocument') {
            # Upload Document.
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
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return EquipmentFuelDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->overridePageTitle();
            $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkRequire('doc_description', 3, 255);
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if (($this->isValidParameter('eqf_deleted_on') === false) && $this->isValidParameter('eqf_confirm_on') === false) {
            $modal = $this->getConfirmModal();
            $this->View->addModal($modal);
            $btnCon = new ModalButton('btnConfirm', Trans::getFmsWord('confirm'), $modal->getModalId());
            $btnCon->setIcon(Icon::ThumbsUp)->btnPrimary()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnCon);
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6);
        $numberFormatter = new NumberFormatter();
        $textMeter = Trans::getFmsWord('hourMeter');
        if ($this->getStringParameter('eq_primary_meter') === 'km') {
            $textMeter = Trans::getFmsWord('odometer');
        }
        $data = [
            [
                'label' => Trans::getFmsWord('equipment'),
                'value' => $this->getStringParameter('eqf_eq_name'),
            ],
            [
                'label' => Trans::getFmsWord('recordDate'),
                'value' => DateTimeParser::format($this->getStringParameter('eqf_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => $textMeter,
                'value' => $numberFormatter->doFormatFloat($this->getFloatParameter('eqf_meter')) . ' ' . $this->getStringParameter('eq_primary_meter'),
            ],
            [
                'label' => Trans::getFmsWord('qty'),
                'value' => $numberFormatter->doFormatFloat($this->getFloatParameter('eqf_qty_fuel')) . ' L',
            ],
            [
                'label' => Trans::getFmsWord('costPerLiter'),
                'value' => $numberFormatter->doFormatCurrency($this->getFloatParameter('eqf_cost')),
            ],
            [
                'label' => Trans::getFmsWord('total'),
                'value' => $numberFormatter->doFormatCurrency($this->getFloatParameter('eqf_qty_fuel') * $this->getFloatParameter('eqf_cost')),
            ],
            [
                'label' => Trans::getFmsWord('remark'),
                'value' => $this->getStringParameter('eqf_remark'),
            ],
        ];
        $content = $this->generateTableView($data);
        $fieldSet->addHiddenField($this->Field->getHidden('eqf_eq_id', $this->getIntParameter('eqf_eq_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('eqf_date', $this->getStringParameter('eqf_date')));
        $fieldSet->addHiddenField($this->Field->getHidden('eqf_meter', $this->getFloatParameter('eqf_meter')));
        # Create a portlet box.
        $portlet = new Portlet('EqfGeneralPtl', Trans::getFmsWord('equipmentFuel'));
        $portlet->addText($content);
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12);

        return $portlet;
    }

    /**
     * Function to get the bank Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
        # Create table.
        $docTable = new Table('EqDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete')
        ]);
        // $docTable->setDeleteActionByModal($docDeleteModal, 'document', 'getByReferenceForDelete', ['doc_id']);
        # load data
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'equipmentfuel')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = "(dct.dct_master = 'Y')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            if ((int)$row['doc_group_reference'] === $this->getDetailReferenceValue()) {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('EqFotoPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        # create modal.
        $docModal = $this->getDocumentModal();
        $this->View->addModal($docModal);
        $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
        $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnDocMdl);

        return $portlet;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('EqDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', 'equipmentfuel');
        $dctFields->addParameter('dct_master', 'Y');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('EqDocDelMdl', Trans::getWord('deleteDocument'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getConfirmModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('EqfConMdl', Trans::getFmsWord('requestConfirmation'));
        $errorMessage = '';
        $documentError = $this->doValidateRequiredDocument();
        $modalTitle = Trans::getFmsWord('warning');
        if (empty($documentError) === false) {
            $modalTitle = Trans::getFmsWord('missingRequiredDocument');
            $errorMessage = $documentError;
            $modal->setBtnOkName(Trans::getFmsWord('upload'));
            $modal->setFormSubmit($this->getMainFormId(), 'doUploadRequireDoc');
        }
        if (empty($errorMessage) === false) {
            $modal->setTitle($modalTitle);
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = $errorMessage;
        } else {
            $text = Trans::getFmsWord('requestConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doConfirmEquipmentFuel');
            $modal->setBtnOkName(Trans::getFmsWord('yesConfirm'));
        }

        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to validate document require.
     *
     * @return string
     */
    private function doValidateRequiredDocument(): string
    {
        $wheres = [];
        $wheres[] = '(dct.dct_code IN (\'receipt\', \'odometer\'))';
        $wheres[] = '(dcg.dcg_code = \'equipmentfuel\')';
        $docs = DocumentDao::loadDocumentForConfirmFuel($wheres, $this->getDetailReferenceValue());
        $complete = true;
        foreach ($docs as $row) {
            if ((int)$row['total'] === 0) {
                $complete = false;
            }
        }
        if ($complete === false) {
            $table = new Table('ValDocJobTbl');
            $table->setHeaderRow([
                'dct_code' => Trans::getFmsWord('description'),
                'dct_required' => Trans::getFmsWord('required'),
                'total' => Trans::getFmsWord('registered'),
                'eqf_doc' => Trans::getFmsWord('upload'),
                'eqf_doc_type' => '',
                'eqf_row_id' => '',
            ]);
            $rows = [];
            $i = 0;
            foreach ($docs as $row) {
                $required = 1;
                $row['dct_required'] = $required;
                if ((int)$row['total'] !== $required) {
                    $row['eqf_row_id'] = $this->Field->getHidden('eqf_row_id[' . $i . ']', $i);
                    $row['eqf_doc'] = $this->Field->getFile('eqf_doc' . $i . '', '');
                    $row['eqf_doc_type'] = $this->Field->getHidden('eqf_doc_type' . $i . '', $row['dct_id']) . $this->Field->getHidden('eqf_doc_description' . $i . '', $row['dct_description']);
                    $table->addCellAttribute('total', $i, 'style', 'background-color: red; color: white; font-weight: bold; text-align: right;');
                }
                $i++;
                $rows[] = $row;
            }
            $table->addRows($rows);
            $table->setColumnType('dct_required', 'integer');
            $table->setColumnType('total', 'integer');

            return $table->createTable();
        }

        return '';
    }

    /**
     * Function to override page's title
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $title = $this->getStringParameter('eqf_eq_name');
        $status = new LabelGray(Trans::getFmsWord('draft'));
        if ($this->isValidParameter('eqf_deleted_on')) {
            $status = new LabelDark(Trans::getFmsWord('deleted'));
            $this->View->addWarningMessage(Trans::getWord('delete') . ' : ' . $this->getStringParameter('eqf_deleted_reason'));
        } elseif ($this->isValidParameter('eqf_confirm_on')) {
            $status = new LabelSuccess(Trans::getFmsWord('confirm'));
        }
        $this->View->setDescription($title . ' | ' . $status);

    }
}
