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
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
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
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'tax', 'tax_id');
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
            'tax_ss_id' => $this->User->getSsId(),
            'tax_name' => $this->getStringParameter('tax_name'),
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
                'td_name' => $this->getStringParameter('td_name'),
                'td_percent' => $this->getFloatParameter('td_percent'),
                'td_active' => $this->getStringParameter('td_active', 'Y')
            ];
            $tdDao = new TaxDetailDao();
            if ($this->isValidParameter('td_id') === true) {
                $tdDao->doUpdateTransaction($this->getIntParameter('td_id'), $tdColVal);
            } else {
                $tdDao->doInsertTransaction($tdColVal);
            }
        } else {
            $colVal = [
                'tax_name' => $this->getStringParameter('tax_name'),
                'tax_active' => $this->getStringParameter('tax_active', 'Y'),
            ];
            $taxDao = new TaxDao();
            $taxDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
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
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->Tab->addPortlet('general', $this->getDetailFieldSet());
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
            $this->Validation->checkRequire('td_name');
            $this->Validation->checkUnique('td_name', 'tax_detail', [
                'td_id' => $this->getIntParameter('td_id')
            ], [
                'td_tax_id' => $this->getDetailReferenceValue()
            ]);
            $this->Validation->checkRequire('td_percent');
            $this->Validation->checkFloat('td_percent', -100, 100);
        } else {
            $this->Validation->checkRequire('tax_name');
            $this->Validation->checkUnique('tax_name', 'tax', [
                'tax_id' => $this->getDetailReferenceValue()
            ]);
        }
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
        $fieldSet->setGridDimension(6, 6, 6, 12);
        # Add field to field set
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('tax_name', $this->getStringParameter('tax_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('tax_active', $this->getStringParameter('tax_active')));
        # Create a portlet box.
        $portlet = new Portlet('taxGnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }

    /**
     * Function to get the storage Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getDetailFieldSet(): Portlet
    {
        $modal = $this->getDetailModal();
        $this->View->addModal($modal);
        $table = new Table('TaxTdTbl');
        $table->setHeaderRow([
            'td_name' => Trans::getWord('description'),
            'td_percent' => Trans::getFinanceWord('percentage'),
            'td_active' => Trans::getWord('active'),
        ]);
        $results = TaxDetailDao::getByTaxId($this->getDetailReferenceValue());
        $table->addRows($results);
        $table->setColumnType('td_percent', 'float');
        $table->setColumnType('td_active', 'yesno');
        $table->addColumnAttribute('td_name', 'style', 'text-align: center');
        $table->setUpdateActionByModal($modal, 'taxDetail', 'getByReference', ['td_id']);

        # Create a portlet box.
        $portlet = new Portlet('TaxTdPtl', Trans::getWord('detail'));
        $btnWhsMdl = new ModalButton('btnTaxTdMdl', Trans::getFinanceWord('addDetail'), $modal->getModalId());
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
    private function getDetailModal(): Modal
    {
        $modal = new Modal('TaxTdMdl', Trans::getFinanceWord('taxDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('td_name', $this->getParameterForModal('td_name', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('percentage') . ' (%)', $this->Field->getNumber('td_percent', $this->getParameterForModal('td_percent', $showModal)), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('td_active', $this->getParameterForModal('td_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('td_id', $this->getParameterForModal('td_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
}
