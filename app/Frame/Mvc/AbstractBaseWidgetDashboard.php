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

/**
 * Class to manage the creation of chart.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseWidgetDashboard extends AbstractBaseDashboardItem
{

    /**
     * Property to store the template object.
     *
     * @var \App\Frame\Gui\Templates\AbstractTemplate $Template
     */
    protected $Template;


    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id)
    {
        parent::__construct();
        $this->setId($id);
        $this->setDisableDeleteButton();
        $this->setDisableEditButton();
    }

    /**
     * Function to load the template data.
     *
     * @return void
     */
    abstract public function loadTemplate(): void;

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $large To set the grid amount for a large screen.
     * @param integer $medium To set the grid amount for a medium screen.
     * @param integer $small To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->Template->setGridDimension($large, $medium, $small, $extraSmall);
    }


    /**
     * Function to add the portlet attribute.
     *
     * @param integer $height To set the height number data.
     * @param string $uom To set unit of measure height.
     *
     * @return void
     */
    public function setHeight(int $height, string $uom = 'px'): void
    {
        if (empty($uom) === true) {
            $uom = 'px';
        }
        if ($height !== null) {
            $this->Template->setHeight($height, $uom);
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
            $this->Template->addButton($btn);
        }
        if ($this->EnableEdit === true) {
            $urlEdit = url('dashboardDetail/detail?dsh_id=' . $this->getIntParameter('dsd_dsh_id') . '&' . $this->getReferenceCode() . '=' . $this->getReferenceValue() . '&pv=1');
            $btnEdit = new HyperLink($this->Id . 'BtnEdit', '', $urlEdit, false);
            $btnEdit->viewAsButton();
            $btnEdit->setIcon(Icon::Pencil)->btnSmall()->btnWarning()->pullRight();
            $this->Template->addButton($btnEdit);
        }
        if ($this->EnableDelete === true) {
            $btnDel = new ModalButton($this->Id . 'BtnDel', '', $this->ModalDelete->getModalId());
            $btnDel->setIcon(Icon::Trash)->btnSmall()->btnDanger()->pullRight();
            $btnDel->setEnableCallBack('dashboardDetail', 'getByReferenceForDelete');
            $btnDel->addParameter($this->getReferenceCode(), $this->getReferenceValue());
            $this->Template->addButton($btnDel);
        }
        $this->Content .= $this->Template->getContainer();
        $this->Content .= $this->loadJavaScript();
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    protected function loadJsonResponse(): void
    {
        $this->Template->Id = $this->Id;
        $this->loadTemplate();
        $this->JsonResponses['data'] = $this->Template->getContentBody();
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

        $script .= 'var ' . $scriptVar . " = new App.Chart('" . $this->Id . "', '" . $this->Id . "', '" . $this->getStringParameter('route') . "', 'widget');";
        if (empty($this->CallBackParameters) === false) {
            foreach ($this->CallBackParameters as $key => $val) {
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
