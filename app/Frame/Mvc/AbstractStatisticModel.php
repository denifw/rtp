<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Frame\Mvc;

use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;

/**
 * Class abstract statistic model
 *
 * @package    app
 * @subpackage Frame\Mvc
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
abstract class AbstractStatisticModel extends AbstractBaseLayout
{
    /**
     * Attribute to store the object of the statistic form.
     *
     * @var \App\Frame\Gui\FieldSet $StatisticForm
     */
    protected $StatisticForm;

    /**
     * Property to store the trigger to show export xls button.
     *
     * @var boolean $EnableExportXls
     */
    private $EnableExportXls = true;

    /**
     * Property to store the trigger to show search button.
     *
     * @var boolean $EnableSearchButton
     */
    private $EnableSearchButton = false;

    /**
     * Property to store all data
     *
     * @var array $Datas
     */
    public $Datas = [];

    /**
     * Property to store the content data.
     *
     * @var array $Contents
     */
    private $Contents = [];

    /**
     * Property to store the content id.
     *
     * @var array $ContentIds
     */
    private $ContentIds = [];

    /**
     * Base listing model constructor.
     *
     * @param string $nameSpace To store the name space of the page.
     * @param string $route     To store the name space of the page.
     */
    public function __construct(string $nameSpace, string $route)
    {
        parent::__construct('Statistic', $nameSpace, $route);
        $this->StatisticForm = new FieldSet($this->Validation);
    }

    /**
     * Function add data.
     *
     * @param string  $id
     * @param Portlet $data
     */
    public function addDatas(string $id, Portlet $data): void
    {
        $this->Datas[$id] = $data;
    }

    /**
     * Function to create the statistic page.
     *
     * @return array
     */
    public function createView(): array
    {
        if ($this->StatisticForm->isFieldsExist() === true) {
            $StatisticForm = '<div class="col-12">';
            $StatisticForm .= $this->StatisticForm;
            $StatisticForm .= '</div>';
            $StatisticForm .= '<div class="clearfix"></div>';
            $this->View->addContent('search_form', $StatisticForm);
            $this->EnableSearchButton = true;
        }
        foreach ($this->ContentIds as $id) {
            $content = $this->Contents[$id];
            if (empty($content) === false) {
                $this->View->addContent($id, $content);
            }
        }
        # Add default button to the view.
        $this->loadDefaultButton();

        return parent::createView();
    }

    /**
     * Function to add content
     *
     * @param string $contentId To store the content id.
     * @param string $content   To store the content.
     *
     * @return void
     */
    protected function addContent(string $contentId, string $content): void
    {
        if (empty($contentId) === true) {
            Message::throwMessage('Not allowed empty content id for the statistic content.');
        }
        if (empty($content) === false) {
            if (in_array($contentId, $this->ContentIds, true) === false) {
                $this->ContentIds[] = $contentId;
            }
            if (array_key_exists($contentId, $this->Contents) === false) {
                $this->Contents[$contentId] = $content;
            } else {
                $this->Contents[$contentId] .= $content;
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
        $this->View->setNumberOfButton(3);
        if ($this->EnableSearchButton === true) {
            $btnSearch = new SubmitButton('btnSearch', Trans::getWord('search'), 'doSearch', $this->getMainFormId());
            $btnSearch->setIcon(Icon::Search)->btnInfo()->pullRight()->btnMedium();
            $this->View->addButton($btnSearch);
        }
        if ($this->EnableExportXls === true && $this->PageSetting->checkPageRight('AllowExportXls') === true) {
            $btnXls = new SubmitButton('btnExportXls', Trans::getWord('exportXls'), 'doExportXls', $this->getMainFormId());
            $btnXls->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $btnXls->setEnableLoading(false);
            $this->View->addButton($btnXls);
        }
        $btnReload = new Button('btnReload', Trans::getWord('reload'), 'button');
        $btnReload->setIcon(Icon::Refresh)->btnWarning()->pullRight()->btnMedium();
        $btnReload->addAttribute('onclick', 'App.reloadWindow()');
        $this->View->addButton($btnReload);
        if ($this->PopupLayout === true) {
            $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
            $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight()->btnMedium();
            $btnClose->addAttribute('onclick', 'App.closeWindow()');
            $this->View->addButton($btnClose);
        }
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    abstract public function loadSearchForm(): void;

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        $excel = new Excel();
        foreach ($this->Datas as $key => $portlet) {
            if (empty($portlet->Body) === false && ($portlet->Body[0] instanceof Table)) {
                $sheetName = StringFormatter::formatExcelSheetTitle(trim($portlet->Title));
                $excel->addSheet($sheetName, $sheetName);
                $excel->setFileName($this->PageSetting->getPageDescription() . '_' . date('Y_m_d') . '.xlsx');
                $sheet = $excel->getSheet($sheetName, true);
                $excelTable = new ExcelTable($excel, $sheet);
                $excelTable->setTable($portlet->Body[0]);
                $excelTable->writeTable();
                $excel->setActiveSheet($sheetName);
            }
        }
        $excel->createExcel();
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    abstract public function loadViews(): void;

    /**
     * Function to check is the validation rule exist or not.
     *
     * @return boolean
     */
    public function isValidationExist(): bool
    {
        return $this->Validation->isValidationExist();
    }

    /**
     * Function to enable new button.
     *
     * @param bool $disable To store the option value.
     *
     * @return void
     */
    protected function disableExportButton(bool $disable = true): void
    {
        $this->EnableExportXls = !$disable;
    }

    /**
     * Function to get the the default route.
     *
     * @return string
     */
    public function getDefaultRoute(): string
    {
        return '/';
    }
}
