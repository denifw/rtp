<?php

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Gui\Tabs;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to manage abstraction of the detail page.
 *
 * @package    app
 * @subpackage Frame\Mvc
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractDetailModel extends AbstractBaseLayout
{
    /**
     * Property to store the reference value.
     *
     * @var Tabs $Tab
     */
    protected $Tab;
    /**
     * Property to store the reference code.
     *
     * @var string
     */
    private $DetailReferenceCode = '';
    /**
     * Property to store the reference value.
     *
     * @var string
     */
    private $DetailReferenceValue = '';
    /**
     * Property to store the trigger to show insert button.
     *
     * @var boolean $EnableInsertButton
     */
    private $EnableInsertButton = true;
    /**
     * Property to store the trigger to show update button.
     *
     * @var boolean $EnableUpdateButton
     */
    private $EnableUpdateButton = true;
    /**
     * Property to store the trigger to show delete button.
     *
     * @var boolean $EnableDeleteButton
     */
    private $EnableDeleteButton = false;
    /**
     * Property to store the trigger to show delete button.
     *
     * @var boolean $EnableCopyButton
     */
    private $EnableCopyButton = false;
    /**
     * Property to store the trigger to show delete button.
     *
     * @var boolean $EnableViewButton
     */
    private $EnableViewButton = false;
    /**
     * Property to store the trigger to show delete button.
     *
     * @var boolean $EnableHoldButton
     */
    private $EnableHoldButton = false;

    /**
     * Property to store the trigger to show delete button.
     *
     * @var boolean $EnableUnHoldButton
     */
    private $EnableUnHoldButton = false;


    /**
     * Property to store the trigger to show close button.
     *
     * @var boolean $EnableCloseButton
     */
    private $EnableCloseButton = true;

    /**
     * Property to store the route name.
     *
     * @var string
     */
    private $RouteName;

    /**
     * Base detail model constructor.
     *
     * @param string $pageCategory To store the name page category.
     * @param string $nameSpace To store the name space of the page.
     * @param string $route To store the name space of the page.
     */
    public function __construct(string $pageCategory, string $nameSpace, string $route)
    {
        parent::__construct($pageCategory, $nameSpace, $route);
        $this->Tab = new Tabs('mainTab');
        $this->RouteName = $route;
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    abstract public function loadData(): array;

    /**
     * Abstract function to insert data into database.
     *
     * @return string
     */
    abstract protected function doInsert(): string;

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    abstract protected function doUpdate(): void;

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    abstract public function loadForm(): void;


    /**
     * Abstract function to load the default button of the page.
     *
     * @return void
     */
    abstract protected function loadDefaultButton(): void;

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->isUploadDocumentAction()) {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
            $this->Validation->checkRequire('doc_description', 2, 255);
        } elseif ($this->isDeleteDocumentAction()) {
            $this->Validation->checkRequire('doc_id_del');
        }
    }

    /**
     * Function to do the transaction of database.;
     *
     * @return void
     */
    public function doTransaction(): void
    {
        # todo implement user log <See file App/Frame/Bin/Code/user_log_transaction_db>

        $this->loadValidationRole();
        if ($this->isValidPostValues() === true) {
            DB::beginTransaction();
            try {
                if ($this->isUpdate() === true) {
                    $this->doUpdate();
                    $this->addSuccessMessage(Trans::getWord('successUpdate', 'message'));
                }
                if ($this->isInsert() === true) {
                    $lastInsertId = $this->doInsert();
                    $this->setDetailReferenceValue($lastInsertId);
                    $log['ul_ref_id'] = $lastInsertId;
                    $this->addSuccessMessage(Trans::getWord('successInsert', 'message'));
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                if ($this->isUpdate() === true) {
                    $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
                }
                if ($this->isInsert() === true) {
                    $this->addErrorMessage(Trans::getWord('failedInsert', 'message'));
                }
                $this->addErrorMessage($this->doPrepareSqlErrorMessage($e->getMessage()));
            }
        } else {
            # Set the error messages.
            if ($this->isInsert() === true) {
                $this->addErrorMessage(Trans::getWord('failedInsert', 'message'));
            }
            if ($this->isUpdate() === true) {
                $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
            }
        }
    }

    /**
     * Function to prepare sql message.
     *
     * @param string $error To store the error message of the sql.
     *
     * @return string
     */
    public function doPrepareSqlErrorMessage(string $error): string
    {
        $message = $error;
        if (strpos($error, 'duplicate') !== false) {
            $indexUniqueWord = strpos($error, '_unique');
            if ($indexUniqueWord !== false) {
                $message = substr($error, 0, $indexUniqueWord + 8) . '.';
                $message = str_replace('"', '', $message);
            }
        }

        return $message;
    }

    /**
     * Function to load all the view data and convert it to array.
     *
     * @return array
     */
    public function createView(): array
    {
        $this->loadDefaultButton();
        if ($this->isValidParameter($this->Tab->getFieldId()) === true) {
            $this->Tab->setActiveTab($this->getStringParameter($this->Tab->getFieldId()));
        }
        $this->View->addContent('tab_content', $this->Tab);
        $this->View->addContent('reference_value', $this->Field->getHidden($this->getDetailReferenceCode(), $this->getDetailReferenceValue()));

        return parent::createView();
    }

    /**
     * Function to get the detail reference value.
     *
     * @return string
     */
    public function getDetailReferenceValue(): ?string
    {
        if (empty($this->DetailReferenceValue) === true && empty($this->getDetailReferenceCode()) === false) {
            $this->DetailReferenceValue = $this->getStringParameter($this->getDetailReferenceCode());
        }
        return $this->DetailReferenceValue;
    }

    /**
     * Function to set the detail reference value.
     *
     * @param string $detailReferenceValue To store the last key value.
     *
     * @return void
     */
    public function setDetailReferenceValue(string $detailReferenceValue): void
    {
        if ($detailReferenceValue === null) {
            $detailReferenceValue = '';
        }
        $this->DetailReferenceValue = $detailReferenceValue;
    }

    /**
     * Function to get the detail reference value.
     *
     * @return string
     */
    public function getDetailReferenceCode(): string
    {
        return $this->DetailReferenceCode;
    }

    /**
     * Function to set enable close button.
     *
     * @param bool $enable To store the close button.
     *
     * @return void
     */
    protected function setEnableCloseButton(bool $enable = true): void
    {
        $this->EnableCloseButton = $enable;
    }

    /**
     * Function to check is the close button enable or not.
     *
     * @return bool
     */
    protected function isCloseButtonEnabled(): bool
    {
        return $this->EnableCloseButton;
    }

    /**
     * Function to check is the update button enable or not.
     *
     * @return bool
     */
    protected function isUpdateButtonEnabled(): bool
    {
        return $this->EnableUpdateButton;
    }

    /**
     * Function to check is the delete button enable or not.
     *
     * @return bool
     */
    protected function isDeleteButtonEnabled(): bool
    {
        return $this->EnableDeleteButton;
    }

    /**
     * Function to check is the insert button enable or not.
     *
     * @return bool
     */
    protected function isInsertButtonEnabled(): bool
    {
        return $this->EnableInsertButton;
    }

    /**
     * Function to set the detail reference value.
     *
     * @param string $detailReferenceCode To store the reference code.
     *
     * @return void
     */
    public function setDetailReferenceCode($detailReferenceCode): void
    {
        $this->DetailReferenceCode = $detailReferenceCode;
    }


    /**
     * Function to remove the active modal
     *
     * @return void
     */
    public function removeActiveModal(): void
    {
        $this->View->setActiveModal('');
    }

    /**
     * Function to check is it insert process or not.
     *
     * @return boolean
     */
    public function isInsert(): bool
    {
        $result = false;
        if (empty($this->getDetailReferenceValue()) === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to check is it update process or not.
     *
     * @return boolean
     */
    public function isUpdate(): bool
    {
        $result = false;
        if (empty($this->getDetailReferenceValue()) === false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to set disable insert.
     *
     * @param bool $disable To set disable value.
     *
     * @return void
     */
    protected function setDisableInsert(bool $disable = true): void
    {
        $this->EnableInsertButton = true;
        if ($disable === true) {
            $this->EnableInsertButton = false;
        }
    }

    /**
     * Function to set disable update.
     *
     * @param bool $disable To set disable value.
     *
     * @return void
     */
    protected function setDisableUpdate(bool $disable = true): void
    {
        $this->EnableUpdateButton = true;
        if ($disable === true) {
            $this->EnableUpdateButton = false;
        }
    }

    /**
     * Function to set delete update.
     *
     * @param bool $enable To set disable value.
     *
     * @return void
     */
    protected function setEnableDeleteButton(bool $enable = true): void
    {
        $this->EnableDeleteButton = $enable;
    }


    /**
     * Function to set enable close button.
     *
     * @param bool $enable To store the close button.
     *
     * @return void
     */
    protected function setEnableCopyButton(bool $enable = true): void
    {
        $this->EnableCopyButton = $enable;
    }

    /**
     * Function to set enable close button.
     *
     * @param bool $enable To store the close button.
     *
     * @return void
     */
    protected function setEnableViewButton(bool $enable = true): void
    {
        $this->EnableViewButton = $enable;
    }

    /**
     * Function to check is the close button enable or not.
     *
     * @return bool
     */
    protected function isCopyButtonEnabled(): bool
    {
        return $this->EnableCopyButton;
    }

    /**
     * Function to check is the close button enable or not.
     *
     * @return bool
     */
    protected function isViewButtonEnabled(): bool
    {
        return $this->EnableViewButton;
    }

    /**
     * Function to get the the default route.
     *
     * @return string
     */
    public function getDefaultRoute(): string
    {
        return $this->RouteName;
    }


    /**
     * Function to check is the close button enable or not.
     *
     * @return bool
     */
    protected function isHoldButtonEnabled(): bool
    {
        return $this->EnableHoldButton;
    }

    /**
     * Function to set enable close button.
     *
     * @param bool $enable To store the close button.
     *
     * @return void
     */
    protected function setEnableHoldButton(bool $enable = true): void
    {
        $this->EnableHoldButton = $enable;
    }


    /**
     * Function to check is the close button enable or not.
     *
     * @return bool
     */
    protected function isUnHoldButtonEnabled(): bool
    {
        return $this->EnableUnHoldButton;
    }

    /**
     * Function to set enable close button.
     *
     * @param bool $enable To store the close button.
     *
     * @return void
     */
    protected function setEnableUnHoldButton(bool $enable = true): void
    {
        $this->EnableUnHoldButton = $enable;
    }


    /**
     * Function to get the bank Field Set.
     *
     * @param string $docGroup To store the document group reference.
     * @param int $groupReference To store the document reference.
     * @param string $docType To store the document group reference.
     * @param int $typeReference To store the document reference.
     * @param bool $allowUpdate To set trigger to update document.
     *
     * @return Portlet
     */
    protected function getBaseDocumentPortlet(string $docGroup, $groupReference, string $docType = '', $typeReference = 0, bool $allowUpdate = true): Portlet
    {
        $docDeleteModal = $this->getBaseDocumentDeleteModal();
        if ($allowUpdate) {
            $this->View->addModal($docDeleteModal);
        }
        # Create table.
        $docTable = new Table('BaseDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
            'action' => Trans::getWord('delete'),
        ]);
        $wheres = [];
        $wheres[] = "(dcg.dcg_code = '" . $docGroup . "')";
        $wheres[] = '(doc.doc_group_reference = ' . $groupReference . ')';
        if (empty($docType) === false) {
            $wheres[] = "(dct.dct_code = '" . $docType . "')";
            if ($typeReference > 0) {
                $wheres[] = '(doc.doc_type_reference = ' . $typeReference . ')';
            }
        }
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnGnDocDown' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            if ($allowUpdate && (int)$row['doc_group_reference'] === $groupReference) {
                $btnDel = new ModalButton('btnGnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->setColumnType('doc_created_on', 'datetime');
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $docTable->addColumnAttribute('action', 'style', 'text-align: center');
        $portlet = new Portlet('GnDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        if ($allowUpdate) {
            # create modal.
            $docModal = $this->getBaseDocumentModal($docGroup);
            $this->View->addModal($docModal);
            $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
            $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnDocMdl);
        }

        return $portlet;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @param string $docGroup To store the document group reference.
     *
     * @return Modal
     */
    protected function getBaseDocumentModal(string $docGroup): Modal
    {
        $modal = new Modal('GnDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', $docGroup);
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);
        $dctFields->setAutoCompleteFields([
            'doc_description' => 'dct_description',
        ]);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('publicAccess'), $this->Field->getYesNo('doc_public', $this->getParameterForModal('doc_public', $showModal, 'Y')));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to check is delete action.
     *
     * @return bool
     */
    protected function isUploadDocumentAction(): bool
    {
        return $this->getFormAction() === 'doUploadDocument';
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    protected function getBaseDocumentDeleteModal(): Modal
    {
        $modal = new Modal('GnDocDelMdl', Trans::getWord('deleteDocument'));
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
     * Function to check is delete action.
     *
     * @return bool
     */
    protected function isDeleteDocumentAction(): bool
    {
        return $this->getFormAction() === 'doDeleteDocument';
    }


}
