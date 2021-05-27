<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Fms;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelGray;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Model\Dao\Fms\EquipmentFuelDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Fms\EquipmentMeterDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail EquipmentFuel page
 *
 * @package    app
 * @subpackage Model\Detail\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class EquipmentFuel extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'equipmentFuel', 'eqf_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'eqf_ss_id' => $this->User->getSsId(),
            'eqf_eq_id' => $this->getIntParameter('eqf_eq_id'),
            'eqf_date' => $this->getStringParameter('eqf_date'),
            'eqf_meter' => $this->getFloatParameter('eqf_meter'),
            'eqf_qty_fuel' => $this->getFloatParameter('eqf_qty_fuel'),
            'eqf_cost' => $this->getFloatParameter('eqf_cost'),
            'eqf_remark' => $this->getStringParameter('eqf_remark'),
        ];
        $eqfDao = new EquipmentFuelDao();
        $eqfDao->doInsertTransaction($colVal);

        return $eqfDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateDocument') {
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
        } elseif ($this->getFormAction() === 'doDeleteEquipmentFuel') {
            $colVal = [
                'eqf_deleted_reason' => $this->getStringParameter('eqf_deleted_reason'),
                'eqf_deleted_on' => date('Y-m-d H:i:s'),
                'eqf_deleted_by' => $this->User->getId(),
            ];
            $eqfDao = new EquipmentFuelDao();

            $eqfDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else {
            $colVal = [
                'eqf_eq_id' => $this->getIntParameter('eqf_eq_id'),
                'eqf_date' => $this->getStringParameter('eqf_date'),
                'eqf_meter' => $this->getFloatParameter('eqf_meter'),
                'eqf_qty_fuel' => $this->getFloatParameter('eqf_qty_fuel'),
                'eqf_cost' => $this->getFloatParameter('eqf_cost'),
                'eqf_remark' => $this->getStringParameter('eqf_remark'),
            ];
            $eqfDao = new EquipmentFuelDao();
            $eqfDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
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
        } elseif ($this->getFormAction() === 'doDeleteEquipmentFuel') {
            $this->Validation->checkRequire('eqf_deleted_reason', 3, 255);
        } else {
            $this->Validation->checkRequire('eqf_eq_id');
            $this->Validation->checkRequire('eqf_date');
            $this->Validation->checkFloat('eqf_meter');
            $this->Validation->checkFloat('eqf_qty_fuel');
            $this->Validation->checkFloat('eqf_cost');
            if ($this->isValidParameter('eqf_eq_id') && $this->isValidParameter('eqf_date') && $this->isValidParameter('eqf_meter')) {
                $idEqu = $this->getIntParameter('eqf_eq_id');
                $eqmDate = $this->getStringParameter('eqf_date');
                $minMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($idEqu, $eqmDate, 'min');
                $maxMeterData = EquipmentMeterDao::getMinMaxByIdEqAndDate($idEqu, $eqmDate, 'max');
                $this->Validation->checkFloat('eqf_meter', $minMeterData['eqm_meter'], $maxMeterData['eqm_meter']);
            }
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate()) {
            if (($this->isValidParameter('eqf_deleted_on') === false) && $this->isValidParameter('eqf_confirm_on') === false) {
                $modal = $this->getDeleteModal();
                $this->View->addModal($modal);
                $btnDel = new ModalButton('btnDelete', Trans::getFmsWord('delete'), $modal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            } else {
                $this->setDisableUpdate();
            }
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create field
        $eqField = $this->Field->getSingleSelect('equipment', 'eqf_eq_name', $this->getStringParameter('eqf_eq_name'), 'loadAutoCompleteData');
        $eqField->setHiddenField('eqf_eq_id', $this->getIntParameter('eqf_eq_id'));
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setAutoCompleteFields([
            'eqf_meter_info' => 'eq_meter_text',
        ]);
        $meterInfoField = $this->Field->getText('eqf_meter_info', $this->getStringParameter('eqf_meter_info'));
        $meterInfoField->setReadOnly();
        # Set read only fields
        if ($this->isUpdate()) {
            $eqField->setReadOnly();
        }
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('equipment'), $eqField, true);
        $fieldSet->addField(Trans::getFmsWord('recordDate'), $this->Field->getCalendar('eqf_date', $this->getStringParameter('eqf_date')), true);
        $fieldSet->addField(Trans::getFmsWord('meter'), $this->Field->getNumber('eqf_meter', $this->getFloatParameter('eqf_meter')), true);
        $fieldSet->addField(Trans::getFmsWord('meterInfo'), $meterInfoField);
        $fieldSet->addField(Trans::getFmsWord('fuel') . ' (Liter)', $this->Field->getNumber('eqf_qty_fuel', $this->getFloatParameter('eqf_qty_fuel')), true);
        $fieldSet->addField(Trans::getFmsWord('costPerLiter'), $this->Field->getNumber('eqf_cost', $this->getFloatParameter('eqf_cost')), true);
//        $fieldSet->addField(Trans::getFmsWord('fotoMeter'), $this->Field->getFile('eqf_foto_meter', $this->getFileParameter('eqf_foto_meter')));
//        $fieldSet->addField(Trans::getFmsWord('fotoReceipt'), $this->Field->getFile('eqf_foto_receipt', $this->getFileParameter('eqf_foto_receipt')));
        $fieldSet->addField(Trans::getFmsWord('remark'), $this->Field->getTextArea('eqf_remark', $this->getStringParameter('eqf_remark')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', Trans::getFmsWord('equipmentFuel'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(8, 12, 12);

        return $portlet;
    }

    /**
     * Function to get delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('EqfDelMdl', Trans::getFmsWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteEquipmentFuel');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteEquipmentFuel' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('reason'), $this->Field->getTextArea('eqf_deleted_reason', $this->getParameterForModal('eqf_deleted_reason', $showModal)), true);
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get document Field Set.
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
