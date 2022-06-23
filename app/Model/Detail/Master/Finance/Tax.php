<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Master\Finance;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Ajax\Master\Finance\TaxDetail;
use App\Model\Dao\Master\Finance\TaxDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;

/**
 * Class to handle the creation of detail Tax page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */
class Tax extends AbstractFormModel
{
    /**
     * Property to store detail data.
     *
     * @var array $Details .
     */
    private $Details = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'tax', 'tax_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $percent = null;
        if ($this->getStringParameter('tax_group', 'N') === 'N') {
            $percent = $this->getFloatParameter('tax_percent');
        }
        $colVal = [
            'tax_ss_id' => $this->User->getSsId(),
            'tax_name' => $this->getStringParameter('tax_name'),
            'tax_group' => $this->getStringParameter('tax_group', 'N'),
            'tax_percent' => $percent,
            'tax_active' => $this->getStringParameter('tax_active', 'Y'),
        ];
        $taxDao = new TaxDao();
        $taxDao->doInsertTransaction($colVal);

        return $taxDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateDetail') {
            $tdColVal = [
                'td_tax_id' => $this->getDetailReferenceValue(),
                'td_child_tax_id' => $this->getStringParameter('td_child_tax_id'),
            ];
            $tdDao = new TaxDetailDao();
            if ($this->isValidParameter('td_id') === true) {
                $tdDao->doUpdateTransaction($this->getStringParameter('td_id'), $tdColVal);
            } else {
                $tdDao->doInsertTransaction($tdColVal);
            }
            $this->doUpdateTaxPercent();
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $tdDao = new TaxDetailDao();
            $tdId = $this->getStringParameter('td_id_del');
            $tdDao->doDeleteTransaction($tdId);
            $this->doUpdateTaxPercent();

        } else {
            $colVal = [
                'tax_name' => $this->getStringParameter('tax_name'),
                'tax_group' => $this->getStringParameter('tax_group'),
                'tax_active' => $this->getStringParameter('tax_active'),
            ];
            if ($this->getStringParameter('tax_group', 'N') === 'N') {
                $colVal['tax_percent'] = $this->getFloatParameter('tax_percent');
            }
            $taxDao = new TaxDao();
            $taxDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    private function doUpdateTaxPercent(): void
    {

        $totalPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getDetailReferenceValue());
        $taxDao = new TaxDao();
        $taxDao->doUpdateTransaction($this->getDetailReferenceValue(), [
            'tax_percent' => $totalPercent
        ]);

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return TaxDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isUpdate()) {
            if ($this->isGroupTax() === true) {
                $this->Details = TaxDetailDao::getByTaxId($this->getDetailReferenceValue());
            }
            $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
            if ($this->isGroupTax() === true) {
                $this->Tab->addPortlet('general', $this->getDetailFieldSet());
            }
        } else {
            $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateDetail') {
            $this->Validation->checkRequire('td_child_tax_id');
            $this->Validation->checkUnique('td_child_tax_id', 'tax_detail', [
                'td_id' => $this->getStringParameter('td_id')
            ], [
                'td_tax_id' => $this->getDetailReferenceValue(),
                'td_deleted_on' => null
            ]);
        } elseif ($this->getFormAction() === 'doDeleteDetail') {
            $this->Validation->checkRequire('td_id_del');
        } else {
            $this->Validation->checkRequire('tax_name');
            $this->Validation->checkUnique('tax_name', 'tax', [
                'tax_id' => $this->getDetailReferenceValue()
            ]);
            $this->Validation->checkRequire('tax_group');
            if ($this->getStringParameter('tax_group', 'Y') === 'N') {
                $this->Validation->checkRequire('tax_percent');
                $this->Validation->checkFloat('tax_percent');
            }
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $groupField = $this->Field->getYesNo('tax_group', $this->getStringParameter('tax_group'));
        $percentField = $this->Field->getText('tax_percent', $this->getStringParameter('tax_percent'));
        if (empty($this->Details) === false) {
            $groupField->setReadOnly();
        }
        # Add field to field set
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('tax_name', $this->getStringParameter('tax_name')), true);
        $fieldSet->addField(Trans::getWord('group'), $groupField, true);
        $fieldSet->addField(Trans::getWord('percentage'), $percentField);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('tax_active', $this->getStringParameter('tax_active')));
        # Create a portlet box.
        $portlet = new Portlet('taxGnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6);

        return $portlet;
    }

    /**
     * Function to get the storage Field Set.
     *
     * @return Portlet
     */
    private function getDetailFieldSet(): Portlet
    {
        $modal = $this->getDetailModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getDetailDeleteModal();
        $this->View->addModal($modalDelete);
        $table = new Table('TaxTdTbl');
        $table->setHeaderRow([
            'td_name' => Trans::getWord('description'),
            'td_percent' => Trans::getWord('percentage'),
        ]);
        $table->addRows($this->Details);
        $table->setColumnType('td_percent', 'float');
        $table->addColumnAttribute('td_name', 'style', 'text-align: center');
        $table->setDeleteActionByModal($modalDelete, 'td', 'getByIdForDelete', ['td_id']);

        # Create a portlet box.
        $portlet = new Portlet('TaxTdPtl', Trans::getWord('detail'));
        $btnWhsMdl = new ModalButton('btnTaxTdMdl', Trans::getWord('addDetail'), $modal->getModalId());
        $btnWhsMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnWhsMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getDetailModal(): Modal
    {
        $modal = new Modal('TaxTdMdl', Trans::getWord('taxDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $taxField = $this->Field->getSingleSelect('tax', 'td_name', $this->getParameterForModal('td_name', $showModal));
        $taxField->setHiddenField('td_child_tax_id', $this->getParameterForModal('td_child_tax_id', $showModal));
        $taxField->addParameter('tax_group', 'N');
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setEnableNewButton(false);
        $taxField->setAutoCompleteFields([
            'td_percent' => 'tax_percent'
        ]);

        $percentField = $this->Field->getNumber('td_percent', $this->getParameterForModal('td_percent', $showModal));
        $percentField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getWord('percentage'), $percentField);
        $fieldSet->addHiddenField($this->Field->getHidden('td_id', $this->getParameterForModal('td_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getDetailDeleteModal(): Modal
    {
        $modal = new Modal('TaxTdDelMdl', Trans::getWord('deleteDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('td_name_del', $this->getParameterForModal('td_name_del', $showModal)), true);
        $fieldSet->addField(Trans::getWord('percentage') . ' (%)', $this->Field->getNumber('td_percent_del', $this->getParameterForModal('td_percent_del', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('td_id_del', $this->getParameterForModal('td_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to check is tax group or not
     *
     * @return bool
     */
    private function isGroupTax(): bool
    {
        return $this->getStringParameter('tax_group', 'N') === 'Y';
    }
}
