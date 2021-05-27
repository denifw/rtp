<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Model\Dao\System\DashboardItemDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail DashboardItem page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class DashboardItem extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'dashboardItem', 'dsi_id');
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
            'dsi_title' => $this->getStringParameter('dsi_title'),
            'dsi_code' => $this->getStringParameter('dsi_code'),
            'dsi_route' => $this->getStringParameter('dsi_route'),
            'dsi_path' => $this->getStringParameter('dsi_path'),
            'dsi_description' => $this->getStringParameter('dsi_description'),
            'dsi_order' => $this->getIntParameter('dsi_order'),
            'dsi_grid_large' => $this->getIntParameter('dsi_grid_large', 3),
            'dsi_grid_medium' => $this->getIntParameter('dsi_grid_medium', 4),
            'dsi_grid_small' => $this->getIntParameter('dsi_grid_small', 6),
            'dsi_grid_xsmall' => $this->getIntParameter('dsi_grid_xsmall', 12),
            'dsi_height' => $this->getIntParameter('dsi_height', 100),
            'dsi_color' => $this->getStringParameter('dsi_color', '#000000'),
            'dsi_active' => $this->getStringParameter('dsi_active', 'Y'),
            'dsi_module_id' => $this->getIntParameter('dsi_module_id'),
        ];
        $dsiDao = new DashboardItemDao();
        $dsiDao->doInsertTransaction($colVal);

        return $dsiDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dsi_title' => $this->getStringParameter('dsi_title'),
            'dsi_code' => $this->getStringParameter('dsi_code'),
            'dsi_route' => $this->getStringParameter('dsi_route'),
            'dsi_path' => $this->getStringParameter('dsi_path'),
            'dsi_description' => $this->getStringParameter('dsi_description'),
            'dsi_order' => $this->getIntParameter('dsi_order'),
            'dsi_grid_large' => $this->getIntParameter('dsi_grid_large', 3),
            'dsi_grid_medium' => $this->getIntParameter('dsi_grid_medium', 4),
            'dsi_grid_small' => $this->getIntParameter('dsi_grid_small', 6),
            'dsi_grid_xsmall' => $this->getIntParameter('dsi_grid_xsmall', 12),
            'dsi_height' => $this->getIntParameter('dsi_height', 100),
            'dsi_color' => $this->getStringParameter('dsi_color', '#000000'),
            'dsi_active' => $this->getStringParameter('dsi_active', 'Y'),
            'dsi_module_id' => $this->getIntParameter('dsi_module_id'),
        ];
        $dsiDao = new DashboardItemDao();
        $dsiDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DashboardItemDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getLayoutFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('dsi_title', 3, 255);
        $this->Validation->checkRequire('dsi_code', 3, 255);
        $this->Validation->checkRequire('dsi_route', 3, 255);
        $this->Validation->checkRequire('dsi_path', 3, 255);
        if ($this->isValidParameter('dsi_description') === true) {
            $this->Validation->checkRequire('dsi_description', 3, 255);
        }
        $this->Validation->checkInt('dsi_grid_large');
        $this->Validation->checkInt('dsi_grid_medium');
        $this->Validation->checkInt('dsi_grid_small');
        $this->Validation->checkInt('dsi_grid_xsmall');
        $this->Validation->checkInt('dsi_order');
        $this->Validation->checkInt('dsi_height');
        if ($this->isValidParameter('dsi_color') === true) {
            $this->Validation->checkRequire('dsi_color', 3, 32);
        }
        $this->Validation->checkUnique('dsi_code', 'dashboard_item', [
            'dsi_id' => $this->getDetailReferenceValue()
        ]);
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
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $moduleField = $this->Field->getSingleSelect('sty', 'dsi_module_name', $this->getStringParameter('dsi_module_name'));
        $moduleField->setHiddenField('dsi_module_id', $this->getIntParameter('dsi_module_id'));
        $moduleField->addParameter('sty_group', 'dashboardmodule');
        $moduleField->setEnableNewButton(false);
        $moduleField->setEnableDetailButton(false);
        $fieldSet->addField(Trans::getWord('title'), $this->Field->getText('dsi_title', $this->getStringParameter('dsi_title')), true);
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('dsi_code', $this->getStringParameter('dsi_code')), true);
        $fieldSet->addField(Trans::getWord('route'), $this->Field->getText('dsi_route', $this->getStringParameter('dsi_route')), true);
        $fieldSet->addField(Trans::getWord('path'), $this->Field->getText('dsi_path', $this->getStringParameter('dsi_path')), true);
        $fieldSet->addField(Trans::getWord('module'), $moduleField);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('dsi_description', $this->getStringParameter('dsi_description')));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('dsi_active', $this->getStringParameter('dsi_active')));
        # Create a portlet box.
        $portlet = new Portlet('DsiItemPtl', Trans::getWord('dashboardItem'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the layout Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getLayoutFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(3, 3, 3, 6);
        # Add field to field set
        $gridLargeField = $this->Field->getNumber('dsi_grid_large', $this->getIntParameter('dsi_grid_large', 3));
        $gridMediumField = $this->Field->getNumber('dsi_grid_medium', $this->getIntParameter('dsi_grid_medium', 4));
        $gridSmallField = $this->Field->getNumber('dsi_grid_small', $this->getIntParameter('dsi_grid_small', 6));
        $gridXSmallField = $this->Field->getNumber('dsi_grid_xsmall', $this->getIntParameter('dsi_grid_xsmall', 12));
        $heightField = $this->Field->getNumber('dsi_height', $this->getIntParameter('dsi_height', 100));
        $colorField = $this->Field->getColor('dsi_color', $this->getStringParameter('dsi_color', '#000000'));
        $orderField = $this->Field->getNumber('dsi_order', $this->getStringParameter('dsi_order'));
        $fieldSet->addField(Trans::getWord('largeScreen'), $gridLargeField, true);
        $fieldSet->addField(Trans::getWord('mediumScreen'), $gridMediumField, true);
        $fieldSet->addField(Trans::getWord('smallScreen'), $gridSmallField, true);
        $fieldSet->addField(Trans::getWord('extraSmallScreen'), $gridXSmallField, true);
        $fieldSet->addField(Trans::getWord('height'), $heightField, true);
        $fieldSet->addField(Trans::getWord('color'), $colorField);
        $fieldSet->addField(Trans::getWord('orderNumber'), $orderField, true);
        # Create a portlet box.
        $portlet = new Portlet('LayoutPtl', Trans::getWord('layout'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }


}
