<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Crm;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail ContactPerson page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ContactPerson extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'contactPerson', 'cp_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $ssId = $this->getIntParameter('cp_ss_id');
        if ($ssId <= 0) {
            $ssId = $this->User->getSsId();
        }
        $sn = new SerialNumber($ssId);
        $cpNumber = $sn->loadNumber('ContactPerson', $this->User->Relation->getOfficeId(), $this->getIntParameter('cp_rel_id'));
        $officeManager = 'N';
        if (ContactPersonDao::isOfficeHasManager($this->getIntParameter('cp_of_id')) === false) {
            $officeManager = 'Y';
        }
        $colVal = [
            'cp_number' => $cpNumber,
            'cp_of_id' => $this->getIntParameter('cp_of_id'),
            'cp_salutation_id' => $this->getIntParameter('cp_salutation_id'),
            'cp_name' => $this->getStringParameter('cp_name'),
            'cp_dpt_id' => $this->getIntParameter('cp_dpt_id'),
            'cp_jbt_id' => $this->getIntParameter('cp_jbt_id'),
            'cp_email' => $this->getStringParameter('cp_email'),
            'cp_phone' => $this->getStringParameter('cp_phone'),
            'cp_birthday' => $this->getStringParameter('cp_birthday'),
            'cp_office_manager' => $officeManager,
            'cp_active' => $this->getStringParameter('cp_active', 'Y'),
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($colVal);

        return $cpDao->getLastInsertId();
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
        } else {
            $colVal = [
                'cp_of_id' => $this->getIntParameter('cp_of_id'),
                'cp_salutation_id' => $this->getIntParameter('cp_salutation_id'),
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_dpt_id' => $this->getIntParameter('cp_dpt_id'),
                'cp_jbt_id' => $this->getIntParameter('cp_jbt_id'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_birthday' => $this->getStringParameter('cp_birthday'),
                'cp_active' => $this->getStringParameter('cp_active', 'Y'),
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ContactPersonDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('cp_of_id') === true) {
                $office = OfficeDao::getByReference($this->getIntParameter('cp_of_id'));
                if (empty($office) === false) {
                    $this->setParameter('cp_office', $office['of_name']);
                    $this->setParameter('cp_rel_id', $office['of_rel_id']);
                    $this->setParameter('cp_relation', $office['of_relation']);
                } else {
                    $this->setParameter('cp_of_id', '');
                }
            } elseif ($this->isValidParameter('cp_rel_id') === true) {
                $relation = RelationDao::getByReference($this->getIntParameter('cp_rel_id'));
                if (empty($relation) === false) {
                    $this->setParameter('cp_relation', $relation['rel_name']);
                } else {
                    $this->setParameter('cp_rel_id', '');
                }
            }
            $this->setParameter('cp_ss_id', $this->getIntParameter('ump_ss_id', 0));
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
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
            $this->Validation->checkFile('doc_file');
        } elseif ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        } else {
            $this->Validation->checkRequire('cp_rel_id');
            $this->Validation->checkRequire('cp_of_id');
            $this->Validation->checkRequire('cp_name', 2, 125);
            if ($this->isValidParameter('cp_email') === true) {
                $this->Validation->checkMaxLength('cp_email', 125);
                $this->Validation->checkEmail('cp_email');
            }
            if ($this->isValidParameter('cp_birthday') === true) {
                $this->Validation->checkDate('cp_birthday');
            }
            $this->Validation->checkMaxLength('cp_phone', 25);
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'cp_relation', $this->getStringParameter('cp_relation'));
        $relField->setHiddenField('cp_rel_id', $this->getIntParameter('cp_rel_id'));
        $relField->setDetailReferenceCode('rel_id');
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->isInsert() === true && $this->isValidParameter('cp_rel_id') === true) {
            $relField->setReadOnly();
        }
        # Create Office Field
        $officeField = $this->Field->getSingleSelect('office', 'cp_office', $this->getStringParameter('cp_office'));
        $officeField->setHiddenField('cp_of_id', $this->getIntParameter('cp_of_id'));
        $officeField->setDetailReferenceCode('of_id');
        $officeField->addParameterById('of_rel_id', 'cp_rel_id', Trans::getWord('relation'));
        $salutationField = $this->Field->getSingleSelect('sty', 'cp_salutation_name', $this->getStringParameter('cp_salutation_name'));
        $salutationField->setHiddenField('cp_salutation_id', $this->getIntParameter('cp_salutation_id'));
        $salutationField->addParameter('sty_group', 'relationsalutation');
        $salutationField->setEnableNewButton(false);
        $salutationField->setEnableDetailButton(false);
        $titleField = $this->Field->getSingleSelect('jbt', 'cp_jbt_name', $this->getStringParameter('cp_jbt_name'));
        $titleField->setHiddenField('cp_jbt_id', $this->getIntParameter('cp_jbt_id'));
        $titleField->addParameter('jbt_ss_id', $this->User->getSsId());
        $titleField->setDetailReferenceCode('jbt_id');
        $departmentField = $this->Field->getSingleSelect('dpt', 'cp_dpt_name', $this->getStringParameter('cp_dpt_name'));
        $departmentField->setHiddenField('cp_dpt_id', $this->getIntParameter('cp_dpt_id'));
        $departmentField->addParameter('dpt_ss_id', $this->User->getSsId());
        $departmentField->setDetailReferenceCode('dpt_id');
        if ($this->isInsert() === true && $this->isValidParameter('cp_of_id') === true) {
            $relField->setReadOnly();
            $officeField->setReadOnly();
        }
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('office'), $officeField, true);
        $fieldSet->addField(Trans::getCrmWord('salutation'), $salutationField);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')), true);
        $fieldSet->addField(Trans::getCrmWord('department'), $departmentField);
        $fieldSet->addField(Trans::getCrmWord('jobTitle'), $titleField);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getStringParameter('cp_email')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone')));
        $fieldSet->addField(Trans::getCrmWord('birthday'), $this->Field->getCalendar('cp_birthday', $this->getStringParameter('cp_birthday')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getStringParameter('cp_active')));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('cp_ss_id', $this->getIntParameter('cp_ss_id')));

        # Create a portlet box.
        $portlet = new Portlet('CpGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the bank Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    protected function getDocumentFieldSet(): Portlet
    {
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);

        # Create table.
        $docTable = new Table('RelDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete'),
        ]);
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = 'contactperson')";
        $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
            $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
            $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
            $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
            $btnDel->addParameter('doc_id', $row['doc_id']);
            $row['action'] = $btnDel;
            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('RelDocPtl', Trans::getWord('document'));
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
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('JoDocDelMdl', Trans::getWord('deleteDocument'));
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
     * Function to get the relation bank modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('JoDocMdl', Trans::getWord('documents'));
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
        $dctFields->addParameter('dcg_code', 'contactperson');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
}
