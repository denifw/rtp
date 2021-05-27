<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Setting;

use App\Frame\Formatter\Trans;
use App\Model\Dao\Setting\DashboardDao;
use App\Model\Dao\Setting\DashboardDetailDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\DashboardItemDao;

/**
 * Class to handle the creation of detail DashboardDetail page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class DashboardDetail extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'dashboardDetail', 'dsd_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $dsiData = DashboardItemDao::getByReference($this->getIntParameter('dsd_dsi_id'));
        $colVal = [
            'dsd_dsh_id' => $this->getIntParameter('dsh_id'),
            'dsd_dsi_id' => $this->getIntParameter('dsd_dsi_id'),
            'dsd_title' => $this->getStringParameter('dsd_title', $dsiData['dsi_title']),
            'dsd_grid_large' => $this->getIntParameter('dsd_grid_large', $dsiData['dsi_grid_large']),
            'dsd_grid_medium' => $this->getIntParameter('dsd_grid_medium', $dsiData['dsi_grid_medium']),
            'dsd_grid_small' => $this->getIntParameter('dsd_grid_small', $dsiData['dsi_grid_small']),
            'dsd_grid_xsmall' => $this->getIntParameter('dsd_grid_xsmall', $dsiData['dsi_grid_xsmall']),
            'dsd_height' => $this->getIntParameter('dsd_height', $dsiData['dsi_height']),
            'dsd_color' => $this->getStringParameter('dsd_color', $dsiData['dsi_color']),
            'dsd_order' => $this->getIntParameter('dsd_order', $dsiData['dsi_order']),
            'dsd_parameter' => $dsiData['dsi_parameter'],
        ];
        $dsdDao = new DashboardDetailDao();
        $dsdDao->doInsertTransaction($colVal);

        return $dsdDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $jsonData = $this->getParameterFilterByCode();
        $colVal = [
            'dsd_title' => $this->getStringParameter('dsd_title'),
            'dsd_grid_large' => $this->getIntParameter('dsd_grid_large', 3),
            'dsd_grid_medium' => $this->getIntParameter('dsd_grid_medium', 4),
            'dsd_grid_small' => $this->getIntParameter('dsd_grid_small', 6),
            'dsd_grid_xsmall' => $this->getIntParameter('dsd_grid_xsmall', 12),
            'dsd_order' => $this->getIntParameter('dsd_order'),
            'dsd_color' => $this->getStringParameter('dsd_color', '#000000'),
        ];
        if (empty($jsonData) === false) {
            $colVal['dsd_parameter'] = $jsonData;
        }
        $dsdDao = new DashboardDetailDao();
        $dsdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DashboardDetailDao::getByReferenceAndSystemAndUser($this->getDetailReferenceValue(), $this->User->getSsId(), $this->User->getId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isValidParameter('dsd_parameter') === true) {
            $this->setParameters(json_decode($this->getStringParameter('dsd_parameter'), true));
        }
        if (($this->isInsert() === true) && $this->isValidParameter('dsh_id') === true) {
            $dashboard = DashboardDao::getByReference($this->getIntParameter('dsh_id'));
            if (empty($dashboard) === false) {
                $this->setParameter('dsh_name', $dashboard['dsh_name']);
            } else {
                $this->setParameter('dsh_id', '');
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getLayoutFieldSet());
        $this->Tab->addContent('general', $this->getFormFilterFieldSet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('general', $this->getDashboardPreviewFieldSet());
        }
        $this->View->addContent('dsi_code', $this->Field->getHidden('dsi_code', $this->getStringParameter('dsi_code')));
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->isInsert() === true) {
            $this->Validation->checkRequire('dsh_id');
            $this->Validation->checkRequire('dsd_dsi_id');
        } elseif ($this->isUpdate() === true) {
            $this->Validation->checkRequire('dsd_title', 3, 255);
            $this->Validation->checkInt('dsd_grid_large');
            $this->Validation->checkInt('dsd_grid_medium');
            $this->Validation->checkInt('dsd_grid_small');
            $this->Validation->checkInt('dsd_grid_xsmall');
            $this->Validation->checkInt('dsd_order');
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
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $dshNameField = $this->Field->getText('dsh_name', $this->getStringParameter('dsh_name'));
        $dshNameField->setReadOnly();
        $dsiField = $this->Field->getSingleSelect('dashboardItem', 'dsi_title', $this->getStringParameter('dsi_title'), 'loadDashboardItemByUserGroup');
        $dsiField->setHiddenField('dsd_dsi_id', $this->getIntParameter('dsd_dsi_id'));
        $dsiField->addParameter('usg_ss_id', $this->User->getSsId());
        $dsiField->addParameter('ugd_ump_id', $this->User->getMappingId());
        $dsiField->setEnableNewButton(false);
        $dsiField->setEnableDetailButton(false);
        if ($this->isUpdate() === true) {
            $dsiField->setReadOnly();
        }
        $fieldSet->addField(Trans::getWord('dashboard'), $dshNameField);
        $fieldSet->addField(Trans::getWord('dashboardItem'), $dsiField, true);
        $fieldSet->addField(Trans::getWord('title'), $this->Field->getText('dsd_title', $this->getStringParameter('dsd_title')), true);
        # Create a portlet box.
        $portlet = new Portlet('DshItemPtl', Trans::getWord('dashboardItem'));
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
        $gridLargeField = $this->Field->getNumber('dsd_grid_large', $this->getStringParameter('dsd_grid_large', 3));
        $gridMediumField = $this->Field->getNumber('dsd_grid_medium', $this->getStringParameter('dsd_grid_medium', 4));
        $gridSmallField = $this->Field->getNumber('dsd_grid_small', $this->getStringParameter('dsd_grid_small', 6));
        $gridXSmallField = $this->Field->getNumber('dsd_grid_xsmall', $this->getStringParameter('dsd_grid_xsmall', 12));
        $colorField = $this->Field->getColor('dsd_color', $this->getStringParameter('dsd_color', '#000000'));
        $orderField = $this->Field->getNumber('dsd_order', $this->getStringParameter('dsd_order'));
        $fieldSet->addField(Trans::getWord('largeScreen'), $gridLargeField, true);
        $fieldSet->addField(Trans::getWord('mediumScreen'), $gridMediumField, true);
        $fieldSet->addField(Trans::getWord('smallScreen'), $gridSmallField, true);
        $fieldSet->addField(Trans::getWord('extraSmallScreen'), $gridXSmallField, true);
        $fieldSet->addField(Trans::getWord('color'), $colorField, true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $orderField, true);
        # Create a portlet box.
        $portlet = new Portlet('LayoutPtl', Trans::getWord('layout'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the layout Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getDashboardPreviewFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(3, 3, 3, 6);
        # Create a portlet box.
        $portlet = new Portlet('PreviewPtl', Trans::getWord('preview'));
        $pageRight = $this->PageSetting->loadPageRightsByIdPage(178);
        $pagePath = str_replace('/', '\\', $this->getStringParameter('dsi_path'));
        $model = 'App\\Model\\DashboardItem\\' . $pagePath;
        $id = str_replace(' ', '', $this->getStringParameter('dsi_code')) . $this->getDetailReferenceValue();
        /**
         * @var \App\Frame\Mvc\AbstractBaseDashboardItem $model
         */
        $model = new $model($id);
        $model->setRoute($this->getStringParameter('dsi_route'));
        $model->setParameters($this->loadData());
        $model->setPageRight($pageRight);
        if (empty($this->getStringParameter('dsd_parameter')) === false) {
            $model->addCallBackParameters(json_decode($this->getStringParameter('dsd_parameter'), true));
        }
        $model->setDisableDeleteButton();
        $model->setDisableEditButton();
        $model->setDisableReloadButton();
        $portlet->addText($model->doCreate());
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }


    /**
     * Function to get the form filter Field Set.
     *
     * @return string
     */
    private function getFormFilterFieldSet(): string
    {
        $code = $this->getStringParameter('dsi_code');
        $fieldSet = '';
        switch ($code) {
            # Widget
            case 'totalPlanningJob':
                $fieldSet = $this->getFormTotalPlanningJob();
                break;
            case 'totalPublishedJob':
                $fieldSet = $this->getFormTotalPublishedJob();
                break;
            case 'totalInProgressJob':
                $fieldSet = $this->getFormTotalInProgressJob();
                break;
            case 'totalCompleteJob':
                $fieldSet = $this->getFormTotalCompleteJob();
                break;
            # Widget Inklaring
//            case 'totalImport':
//                $fieldSet = $this->getFormTotalImport();
//                break;
//            case 'totalImportContainer':
//                $fieldSet = $this->getFormTotalImportContainer();
//                break;
//            case 'totalExport':
//                $fieldSet = $this->getFormTotalExport();
//                break;
//            case 'totalExportContainer':
//                $fieldSet = $this->getFormTotalExportContainer();
//                break;
            # Table
            case 'planningJobTable':
                $fieldSet = $this->getFormPlanningJob();
                break;
            case 'inProgressJobTable':
                $fieldSet = $this->getFormInProgressJob();
                break;
        }
        $portlet = '';
        if (empty($fieldSet) === false) {
            $portlet = new Portlet('FilterPtl', Trans::getWord('filter'));
            $portlet->addFieldSet($fieldSet);
            $portlet->setGridDimension(6, 6, 12);
        }

        return $portlet;
    }

    /**
     * Function to get parameter
     *
     * @return string
     */
    private function getParameterFilterByCode(): string
    {
        $code = $this->getStringParameter('dsi_code');
        $parameters = [];
        switch ($code) {
            # Widget
            case 'totalPlanningJob':
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
            case 'totalPublishedJob':
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
            case 'totalInProgressJob':
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
            case 'totalCompleteJob':
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
            # Widget Inklaring
//            case 'totalImport':
//                $parameters = [
//                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
//                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
//                    'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
//                    'jo_srt_name' => $this->getStringParameter('jo_srt_name'),
//                ];
//                break;
//            case 'totalImportContainer':
//                $parameters = [
//                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
//                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
//                    'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
//                    'jo_srt_name' => $this->getStringParameter('jo_srt_name'),
//                ];
//                break;
//            case 'totalExport':
//                $parameters = [
//                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
//                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
//                    'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
//                    'jo_srt_name' => $this->getStringParameter('jo_srt_name'),
//                ];
//                break;
//            case 'totalExportContainer':
//                $parameters = [
//                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
//                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
//                    'jo_srt_id' => $this->getIntParameter('jo_srt_id'),
//                    'jo_srt_name' => $this->getStringParameter('jo_srt_name'),
//                ];
//                break;
            # Table
            case 'inProgressJobTable';
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
            case 'planningJobTable';
                $parameters = [
                    'jo_srv_id' => $this->getIntParameter('jo_srv_id'),
                    'jo_srv_name' => $this->getStringParameter('jo_srv_name'),
                ];
                break;
        }
        $result = '';
        if (empty($parameters) === false) {
            $result = json_encode($parameters);
        }

        return $result;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormPlanningJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormInProgressJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalPlanningJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalPublishedJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalImport(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceTermField = $this->Field->getSingleSelect('serviceTerm', 'jo_srt_name', $this->getStringParameter('jo_srt_name'));
        $serviceTermField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $serviceTermField->addParameterById('srt_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $serviceTermField->setEnableNewButton(false);
        $serviceTermField->setEnableDetailButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalImportContainer(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceTermField = $this->Field->getSingleSelect('serviceTerm', 'jo_srt_name', $this->getStringParameter('jo_srt_name'));
        $serviceTermField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $serviceTermField->addParameterById('srt_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $serviceTermField->setEnableNewButton(false);
        $serviceTermField->setEnableDetailButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalExport(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceTermField = $this->Field->getSingleSelect('serviceTerm', 'jo_srt_name', $this->getStringParameter('jo_srt_name'));
        $serviceTermField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $serviceTermField->addParameterById('srt_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $serviceTermField->setEnableNewButton(false);
        $serviceTermField->setEnableDetailButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalExportContainer(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceTermField = $this->Field->getSingleSelect('serviceTerm', 'jo_srt_name', $this->getStringParameter('jo_srt_name'));
        $serviceTermField->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $serviceTermField->addParameterById('srt_srv_id', 'jo_srv_id', Trans::getWord('service'));
        $serviceTermField->setEnableNewButton(false);
        $serviceTermField->setEnableDetailButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $serviceTermField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalInProgressJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }

    /**
     * Function to get form filter.
     *
     * @return FieldSet
     */
    private function getFormTotalCompleteJob(): FieldSet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        # Add field to field set
        $serviceField = $this->Field->getSingleSelect('service', 'jo_srv_name', $this->getStringParameter('jo_srv_name'));
        $serviceField->setHiddenField('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $serviceField->addParameter('srv_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('service'), $serviceField);

        return $fieldSet;
    }
}
