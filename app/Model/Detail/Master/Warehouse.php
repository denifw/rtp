<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Master\WarehouseDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\TableDatas;
use App\Model\Dao\Master\WarehouseStorageDao;
use App\Model\Dao\Relation\ContactPersonDao;

/**
 * Class to handle the creation of detail Warehouse page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Warehouse extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'warehouse', 'wh_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $volume = null;
        if (($this->isValidParameter('wh_length') === true) && ($this->isValidParameter('wh_height') === true) && ($this->isValidParameter('wh_width') === true)) {
            $volume = $this->getFloatParameter('wh_length') * $this->getFloatParameter('wh_height') * $this->getFloatParameter('wh_width');
        }
        $colVal = [
            'wh_ss_id' => $this->User->getSsId(),
            'wh_of_id' => $this->getIntParameter('wh_of_id'),
            'wh_name' => $this->getStringParameter('wh_name'),
            'wh_length' => $this->getFloatParameter('wh_length'),
            'wh_height' => $this->getFloatParameter('wh_height'),
            'wh_width' => $this->getFloatParameter('wh_width'),
            'wh_volume' => $volume,
            'wh_active' => 'Y',
        ];
        $whDao = new WarehouseDao();
        $whDao->doInsertTransaction($colVal);

        return $whDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateContact') {
            $cpColVal = [
                'cp_of_id' => $this->getIntParameter('wh_of_id'),
                'cp_name' => $this->getStringParameter('cp_name'),
                'cp_email' => $this->getStringParameter('cp_email'),
                'cp_phone' => $this->getStringParameter('cp_phone'),
                'cp_office_manager' => $this->getStringParameter('cp_office_manager', 'N'),
                'cp_active' => $this->getStringParameter('cp_active', 'Y'),
            ];
            $cpDao = new ContactPersonDao();
            if ($this->isValidParameter('cp_id') === false) {
                $sn = new SerialNumber($this->User->getSsId());
                $cpNumber = $sn->loadNumber('ContactPerson', $this->User->Relation->getOfficeId(), $this->User->getRelId());
                $cpColVal['cp_number'] = $cpNumber;
                $cpDao->doInsertTransaction($cpColVal);
            } else {
                $cpDao->doUpdateTransaction($this->getIntParameter('cp_id'), $cpColVal);
            }
        } elseif ($this->getFormAction() === 'doUpdateStorage') {
            $volume = null;
            if (($this->isValidParameter('whs_length') === true) && ($this->isValidParameter('whs_height') === true) && ($this->isValidParameter('whs_width') === true)) {
                $volume = $this->getFloatParameter('whs_length') * $this->getFloatParameter('whs_height') * $this->getFloatParameter('whs_width');
            }
            $whsColVal = [
                'whs_wh_id' => $this->getDetailReferenceValue(),
                'whs_name' => $this->getStringParameter('whs_name'),
                'whs_length' => $this->getFloatParameter('whs_length'),
                'whs_height' => $this->getFloatParameter('whs_height'),
                'whs_width' => $this->getFloatParameter('whs_width'),
                'whs_volume' => $volume,
                'whs_active' => $this->getStringParameter('whs_active', 'Y'),
            ];
            $whsDao = new WarehouseStorageDao();
            if ($this->isValidParameter('whs_id') === true) {
                $whsDao->doUpdateTransaction($this->getIntParameter('whs_id'), $whsColVal);
            } else {
                $whsDao->doInsertTransaction($whsColVal);
            }
        } else {
            $volume = null;
            if (($this->isValidParameter('wh_length') === true) && ($this->isValidParameter('wh_height') === true) && ($this->isValidParameter('wh_width') === true)) {
                $volume = $this->getFloatParameter('wh_length') * $this->getFloatParameter('wh_height') * $this->getFloatParameter('wh_width');
            }
            $colVal = [
                'wh_of_id' => $this->getIntParameter('wh_of_id'),
                'wh_name' => $this->getStringParameter('wh_name'),
                'wh_length' => $this->getFloatParameter('wh_length'),
                'wh_height' => $this->getFloatParameter('wh_height'),
                'wh_width' => $this->getFloatParameter('wh_width'),
                'wh_volume' => $volume,
                'wh_active' => $this->getStringParameter('wh_active'),
            ];
            $whDao = new WarehouseDao();
            $whDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return WarehouseDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('storage', $this->getStorageFieldSet());
            $this->Tab->addPortlet('contactPerson', $this->getContactFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateContact') {
            $this->Validation->checkRequire('wh_of_id');
            $this->Validation->checkRequire('cp_name', 2, 125);
            if ($this->isValidParameter('cp_email') === true) {
                $this->Validation->checkMaxLength('cp_email', 125);
                $this->Validation->checkEmail('cp_email');
            }
            $this->Validation->checkMaxLength('cp_phone', 25);

        } elseif ($this->getFormAction() === 'doUpdateStorage') {
            $this->Validation->checkRequire('whs_name', 2, 125);
            $this->Validation->checkUnique('whs_name', 'warehouse_storage', [
                'whs_id' => $this->getIntParameter('whs_id')], [
                'whs_wh_id' => $this->getDetailReferenceValue()
            ]);
//            $this->Validation->checkRequire('whs_length');
//            $this->Validation->checkRequire('whs_height');
//            $this->Validation->checkRequire('whs_width');
//            $this->Validation->checkRequire('whs_volume');
            if ($this->isValidParameter('whs_length') === true) {
                $this->Validation->checkFloat('whs_length', 0);
            }
            if ($this->isValidParameter('whs_height') === true) {
                $this->Validation->checkFloat('whs_height', 0);
            }
            if ($this->isValidParameter('whs_width') === true) {
                $this->Validation->checkFloat('whs_width', 0);
            }
        } else {
            $this->Validation->checkRequire('wh_name', 2, 125);
            $this->Validation->checkRequire('wh_of_id');
            if ($this->isValidParameter('wh_length') === true) {
                $this->Validation->checkFloat('wh_length', 0);
            }
            if ($this->isValidParameter('wh_height') === true) {
                $this->Validation->checkFloat('wh_height', 0);
            }
            if ($this->isValidParameter('wh_width') === true) {
                $this->Validation->checkFloat('wh_width', 0);
            }
//            $this->Validation->checkRequire('wh_length');
//            $this->Validation->checkRequire('wh_height');
//            $this->Validation->checkRequire('wh_width');
//            $this->Validation->checkRequire('wh_volume');
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $pdfButton = new PdfButton('WhPrint', Trans::getWord('printBarcode'), 'storagebarcode');
        $pdfButton->setIcon(Icon::Print)->btnPrimary()->pullRight()->btnMedium();
        $pdfButton->addParameter('wh_id', $this->getDetailReferenceValue());
        $this->View->addButtonAtTheBeginning($pdfButton);
        parent::loadDefaultButton();
    }

    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        $ofField = $this->Field->getSingleSelect('office', 'wh_office', $this->getStringParameter('wh_office'));
        $ofField->setHiddenField('wh_of_id', $this->getIntParameter('wh_of_id'));
        $ofField->addParameter('of_ss_id', $this->User->getSsId());
        $ofField->addParameterById('of_name', 'wh_name', Trans::getWord('name'));
        $ofField->setDetailReferenceCode('of_id');
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('wh_name', $this->getStringParameter('wh_name')), true);
        $fieldSet->addField(Trans::getWord('address'), $ofField, true);
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('wh_length', $this->getFloatParameter('wh_length')));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('wh_height', $this->getFloatParameter('wh_height')));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('wh_width', $this->getFloatParameter('wh_width')));
        if ($this->isUpdate() === true) {
            $volumeField = $this->Field->getNumber('wh_volume', $this->getFloatParameter('wh_volume'));
            $volumeField->setReadOnly();
            $fieldSet->addField(Trans::getWord('volume') . ' (M3)', $volumeField);
        }
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('wh_active', $this->getStringParameter('wh_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('WhGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the storage Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getStorageFieldSet(): Portlet
    {
        $modal = $this->getStorageModal();
        $this->View->addModal($modal);
        $table = new TableDatas('WhWhsTbl');
        $table->setRowsPerPage(50);
        $table->setHeaderRow([
            'whs_name' => Trans::getWord('name'),
            'whs_length' => Trans::getWord('length') . ' (M)',
            'whs_height' => Trans::getWord('height') . ' (M)',
            'whs_width' => Trans::getWord('width') . ' (M)',
            'whs_volume' => Trans::getWord('volume') . ' (M3)',
            'whs_active' => Trans::getWord('active'),
            'btn_barcode' => Trans::getWord('barcode'),
        ]);
        $results = WarehouseStorageDao::getByWarehouseId($this->getDetailReferenceValue());
        $data = [];
        foreach ($results as $row) {
            $barcodeButton = new Button('WhPrint', Trans::getWord('printBarcode'));
            $barcodeButton->setIcon(Icon::Print)->btnPrimary()->btnMedium();
            $barcodeButton->setPopup('documentPdf', [
                'path' => 'Warehouse/StorageBarcode',
                'wh_id' => $this->getDetailReferenceValue(),
                'whs_id' => $row['whs_id']
            ]);
            $row['btn_barcode'] = $barcodeButton;
            $data [] = $row;
        }
        $table->addRows($data);
        $table->setColumnType('whs_length', 'float');
        $table->setColumnType('whs_height', 'float');
        $table->setColumnType('whs_width', 'float');
        $table->setColumnType('whs_volume', 'float');
        $table->setColumnType('whs_active', 'yesno');
        $table->addColumnAttribute('btn_barcode', 'style', 'text-align: center');
        $table->setUpdateActionByModal($modal, 'warehouseStorage', 'getByReference', ['whs_id']);

        # Create a portlet box.
        $portlet = new Portlet('WhWhsPtl', Trans::getWord('storage'));
        $btnWhsMdl = new ModalButton('btnWhsMdl', Trans::getWord('addStorage'), $modal->getModalId());
        $btnWhsMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnWhsMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getStorageModal(): Modal
    {
        $modal = new Modal('WhWhsMdl', Trans::getWord('storage'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateStorage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storageName'), $this->Field->getText('whs_name', $this->getParameterForModal('whs_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('whs_length', $this->getParameterForModal('whs_length', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('whs_height', $this->getParameterForModal('whs_height', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('whs_width', $this->getParameterForModal('whs_width', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('whs_active', $this->getParameterForModal('whs_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('whs_id', $this->getParameterForModal('whs_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getContactFieldSet(): Portlet
    {
        $modal = $this->getContactModal();
        $this->View->addModal($modal);

        $table = new Table('RelCpTbl');
        $table->setHeaderRow([
            'cp_name' => Trans::getWord('name'),
            'cp_email' => Trans::getWord('email'),
            'cp_phone' => Trans::getWord('phone'),
            'cp_office_manager' => Trans::getWord('mainPic'),
            'cp_active' => Trans::getWord('active'),
        ]);
        $data = ContactPersonDao::getDataByOffice($this->getIntParameter('wh_of_id'));
        $table->addRows($data);
        $table->setColumnType('cp_active', 'yesno');
        $table->setColumnType('cp_office_manager', 'yesno');
        $table->setUpdateActionByModal($modal, 'contactPerson', 'getByReference', ['cp_id']);
        # Create a portlet box.
        $portlet = new Portlet('RelCPPtl', Trans::getWord('contactPerson'));
        $btnCpMdl = new ModalButton('btnCpMdl', Trans::getWord('addContact'), $modal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getContactModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('WhCpMdl', Trans::getWord('contactPerson'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateContact');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateContact' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getParameterForModal('cp_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getParameterForModal('cp_email', $showModal)), true);
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getParameterForModal('cp_phone', $showModal)));
        $fieldSet->addField(Trans::getWord('mainPic'), $this->Field->getYesNo('cp_office_manager', $this->getParameterForModal('cp_office_manager', $showModal)));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getParameterForModal('cp_active', $showModal)));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('cp_id', $this->getParameterForModal('cp_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


}
