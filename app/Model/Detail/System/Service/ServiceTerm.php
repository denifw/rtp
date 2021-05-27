<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\System\Service;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Model\Dao\System\Service\ActionDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail ServiceTerm page
 *
 * @package    app
 * @subpackage Model\Detail\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class ServiceTerm extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'serviceTerm', 'srt_id');
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
            'srt_srv_id' => $this->getStringParameter('srt_srv_id'),
            'srt_name' => $this->getStringParameter('srt_name'),
            'srt_route' => $this->getStringParameter('srt_route'),
            'srt_container' => $this->getStringParameter('srt_container', 'N'),
            'srt_color' => $this->getStringParameter('srt_color'),
            'srt_image' => $this->getStringParameter('srt_image'),
            'srt_description' => $this->getStringParameter('srt_description'),
            'srt_order' => $this->getIntParameter('srt_order'),
            'srt_load' => $this->getStringParameter('srt_load', 'N'),
            'srt_unload' => $this->getStringParameter('srt_unload', 'N'),
            'srt_pol' => $this->getStringParameter('srt_pol', 'N'),
            'srt_pod' => $this->getStringParameter('srt_pod', 'N'),
            'srt_active' => $this->getStringParameter('srt_active', 'Y'),
        ];
        $srtDao = new ServiceTermDao();
        $srtDao->doInsertTransaction($colVal);

        return $srtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateAction') {
            $acColVal = [
                'ac_srt_id' => $this->getDetailReferenceValue(),
                'ac_code' => $this->getStringParameter('ac_code'),
                'ac_description' => $this->getStringParameter('ac_description'),
                'ac_style' => $this->getStringParameter('ac_style'),
                'ac_order' => $this->getIntParameter('ac_order'),
            ];
            $acDao = new ActionDao();
            if ($this->isValidParameter('ac_id') === true) {
                $acDao->doUpdateTransaction($this->getIntParameter('ac_id'), $acColVal);
            } else {
                $acDao->doInsertTransaction($acColVal);
            }

        } else {
            $colVal = [
                'srt_srv_id' => $this->getStringParameter('srt_srv_id'),
                'srt_name' => $this->getStringParameter('srt_name'),
                'srt_route' => $this->getStringParameter('srt_route'),
                'srt_container' => $this->getStringParameter('srt_container', 'N'),
                'srt_description' => $this->getStringParameter('srt_description'),
                'srt_color' => $this->getStringParameter('srt_color'),
                'srt_image' => $this->getStringParameter('srt_image'),
                'srt_order' => $this->getIntParameter('srt_order'),
                'srt_load' => $this->getStringParameter('srt_load', 'N'),
                'srt_unload' => $this->getStringParameter('srt_unload', 'N'),
                'srt_pol' => $this->getStringParameter('srt_pol', 'N'),
                'srt_pod' => $this->getStringParameter('srt_pod', 'N'),
                'srt_active' => $this->getStringParameter('srt_active', 'Y'),
            ];
            $srtDao = new ServiceTermDao();
            $srtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ServiceTermDao::getByReference($this->getDetailReferenceValue());
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
            $this->Tab->addPortlet('action', $this->getActionFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateAction') {
            $this->Validation->checkRequire('ac_code', 2, 125);
            $this->Validation->checkRequire('ac_description', 2, 255);
            $this->Validation->checkRequire('ac_order');
            $this->Validation->checkInt('ac_order');
            $this->Validation->checkUnique('ac_code', 'action', [
                'ac_id' => $this->getIntParameter('ac_id'),
            ], [
                'ac_srt_id' => $this->getDetailReferenceValue(),
            ]);
            $this->Validation->checkMaxLength('ac_style', 125);
        } else {
            $this->Validation->checkRequire('srt_srv_id');
            $this->Validation->checkRequire('srt_color');
            $this->Validation->checkRequire('srt_image');
            $this->Validation->checkRequire('srt_route');
            $this->Validation->checkRequire('srt_container');
            $this->Validation->checkSpecialCharacter('srt_route');
            $this->Validation->checkRequire('srt_name', 2, 125);
            $this->Validation->checkRequire('srt_description', 2, 255);
            $this->Validation->checkRequire('srt_order');
            $this->Validation->checkInt('srt_order', 1);
            $this->Validation->checkUnique('srt_route', 'service_term', [
                'srt_id' => $this->getDetailReferenceValue(),
            ]);
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
        $fieldSet->setGridDimension(6, 6);
        $serviceField = $this->Field->getSingleSelect('service', 'srt_service', $this->getStringParameter('srt_service'));
        $serviceField->setHiddenField('srt_srv_id', $this->getIntParameter('srt_srv_id'));
        $serviceField->setEnableNewButton(false);
        $serviceField->setEnableDetailButton(false);

        # Create color field set
        $colorField = $this->Field->getSelect('srt_color', $this->getStringParameter('srt_color'));
        $colorField->addOption('Red', '#FF0000');
        $colorField->addOption('Green', '#00FF00');
        $colorField->addOption('Blue', '#0000FF');

        # Add field to field set
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('srt_name', $this->getStringParameter('srt_name')), true);
        $fieldSet->addField(Trans::getWord('service'), $serviceField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('srt_description', $this->getStringParameter('srt_description')), true);
        $fieldSet->addField(Trans::getWord('route'), $this->Field->getText('srt_route', $this->getStringParameter('srt_route')), true);
        $fieldSet->addField(Trans::getWord('color'), $colorField, true);
        $fieldSet->addField(Trans::getWord('icon'), $this->Field->getText('srt_image', $this->getStringParameter('srt_image')), true);
        $fieldSet->addField(Trans::getWord('include') . ' ' . Trans::getWord('container'), $this->Field->getYesNo('srt_container', $this->getStringParameter('srt_container')), true);
        $fieldSet->addField(Trans::getWord('load'), $this->Field->getYesNo('srt_load', $this->getStringParameter('srt_load')), true);
        $fieldSet->addField(Trans::getWord('unload'), $this->Field->getYesNo('srt_unload', $this->getStringParameter('srt_unload')), true);
        $fieldSet->addField(Trans::getWord('pol'), $this->Field->getYesNo('srt_pol', $this->getStringParameter('srt_pol')), true);
        $fieldSet->addField(Trans::getWord('pod'), $this->Field->getYesNo('srt_pod', $this->getStringParameter('srt_pod')), true);
        $fieldSet->addField(Trans::getWord('order'), $this->Field->getNumber('srt_order', $this->getIntParameter('srt_order')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('srt_active', $this->getStringParameter('srt_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('serviceGeneralPortlet', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }


    /**
     * Function to get the action Field Set.
     *
     * @return Portlet
     */
    private function getActionFieldSet(): Portlet
    {
        $modal = $this->getActionModal();
        $this->View->addModal($modal);
        $table = new Table('SrtAcTbl');
        $table->setHeaderRow([
            'ac_code' => Trans::getWord('code'),
            'ac_description' => Trans::getWord('description'),
            'ac_style' => Trans::getWord('style'),
            'ac_order' => Trans::getWord('orderNumber'),
        ]);
        $data = ActionDao::getByServiceTermId($this->getDetailReferenceValue());
        $rows = [];
        foreach ($data as $row) {
            if (empty($row['ac_style']) === false) {
                $row['ac_style'] = new Label($row['ac_style'], $row['ac_style']);
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('ac_order', 'integer');
        $table->addColumnAttribute('ac_style', 'style', 'text-align: center');
        $table->setUpdateActionByModal($modal, 'action', 'getByReference', ['ac_id']);
        # Create a portlet box.
        $portlet = new Portlet('SrtAcPtl', Trans::getWord('action'));
        $btnCpMdl = new ModalButton('btnAcMdl', Trans::getWord('addAction'), $modal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get action modal.
     *
     * @return Modal
     */
    private function getActionModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('SrtAcMdl', Trans::getWord('action'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateAction');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateAction' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('ac_code', $this->getParameterForModal('ac_code', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('ac_description', $this->getParameterForModal('ac_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('style'), $this->Field->getText('ac_style', $this->getParameterForModal('ac_style', $showModal)));
        $fieldSet->addField(Trans::getWord('orderNumber'), $this->Field->getText('ac_order', $this->getParameterForModal('ac_order', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('ac_id', $this->getParameterForModal('ac_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
