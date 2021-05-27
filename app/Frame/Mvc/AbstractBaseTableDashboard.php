<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;

use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;


/**
 * Class to manage the creation of chart.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseTableDashboard extends AbstractBaseDashboardItem
{
    /**
     * Property to store the table object.
     *
     * @var \App\Frame\Gui\Table $Table
     */
    protected $Table;

    /**
     * Property to store the portlet object.
     *
     * @var \App\Frame\Gui\Portlet $Portlet
     */
    protected $Portlet;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id)
    {
        parent::__construct();
        $this->setId($id);
        $this->Portlet = new Portlet($this->Id . 'Ptl', '');
        $this->setDisableDeleteButton();
        $this->setDisableEditButton();
    }


    /**
     * Function to load the chart data.
     *
     * @return void
     */
    abstract public function loadTable(): void;

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $large      To set the grid amount for a large screen.
     * @param integer $medium     To set the grid amount for a medium screen.
     * @param integer $small      To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->Portlet->setGridDimension($large, $medium, $small, $extraSmall);
    }


    /**
     * Function to set the chart title
     *
     * @param string $title To store the title of the chart.
     * @param string $icon  To store the icon of the portlet.
     *
     * @return void
     */
    public function setTitlePortlet(string $title, string $icon = ''): void
    {
        $this->Portlet->setTitle($title);
        $this->Portlet->setIcon($icon);
    }


    /**
     * Function to add the portlet attribute.
     *
     * @param integer $height To set the height number data.
     * @param string  $uom    To set unit of measure height.
     *
     * @return void
     */
    public function setHeight(int $height, string $uom = 'px'): void
    {
        if (empty($uom) === true) {
            $uom = 'px';
        }
        if ($height !== null) {
            $this->Portlet->addBodyAttribute('style', 'height:' . $height . $uom . ' !important; overflow: auto;');
        }
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    protected function loadContent(): void
    {
        if ($this->EnableReload === true) {
            $btn = new Button($this->Id . 'BtnReload', '');
            $btn->setIcon(Icon::Refresh)->btnSmall()->btnSuccess()->pullRight();
            $btn->addAttribute('style', 'display: none;');
            $this->Portlet->addButton($btn);
        }
        if ($this->EnableEdit === true) {
            $urlEdit = url('dashboardDetail/detail?dsh_id=' . $this->getIntParameter('dsd_dsh_id') . '&' . $this->getReferenceCode() . '=' . $this->getReferenceValue() . '&pv=1');
            $btnEdit = new HyperLink($this->Id . 'BtnEdit', '', $urlEdit, false);
            $btnEdit->viewAsButton();
            $btnEdit->setIcon(Icon::Pencil)->btnSmall()->btnWarning()->pullRight();
            $this->Portlet->addButton($btnEdit);
        }
        if ($this->EnableDelete === true) {
            $btnDel = new ModalButton($this->Id . 'BtnDel', '', $this->ModalDelete->getModalId());
            $btnDel->setIcon(Icon::Trash)->btnSmall()->btnDanger()->pullRight();
            $btnDel->setEnableCallBack('dashboardDetail', 'getByReferenceForDelete');
            $btnDel->addParameter($this->getReferenceCode(), $this->getReferenceValue());
            $this->Portlet->addButton($btnDel);
        }
        $this->Portlet->setChartTitleContainer($this->Id);
        $this->Portlet->addChartContainer($this->Id);
        $this->Content = $this->Portlet->createPortlet();
        $this->Content .= $this->loadJavaScript();
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    protected function loadJsonResponse(): void
    {
        $this->loadTable();
        $this->JsonResponses['data'] = $this->Table->createTable();
    }

    /**
     * Function to load the chart data.
     *
     * @return string
     */
    public function loadJavaScript(): string
    {
        $scriptVar = $this->Id . 'var';
        $script = '<script type="text/javascript">';

        $script .= 'var ' . $scriptVar . " = new App.Chart('" . $this->Id . "', '" . $this->Portlet->getPortletId() . "', '" . $this->getStringParameter('route') . "', 'table');";
        if (empty($this->CallBackParameters) === false) {
            foreach ($this->CallBackParameters AS $key => $val) {
                $script .= $scriptVar . ".addParameter('" . $key . "', '" . $val . "');";
            }
        }
        if ($this->EnableReload === true) {
            $script .= $scriptVar . '.setReloadButton();';
        }
        if ($this->AutoReloadTime > 0) {
            $script .= $scriptVar . '.setAutoReload(' . $this->AutoReloadTime . ');';
        }
        $script .= $scriptVar . '.create();';
        $script .= '</script>';

        return $script;

    }
}
