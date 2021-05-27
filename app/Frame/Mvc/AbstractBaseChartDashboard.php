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
abstract class AbstractBaseChartDashboard extends AbstractBaseDashboardItem
{
    /**
     * Property to store the chart object.
     *
     * @var \App\Frame\Chart\AbstractBaseChart $Chart
     */
    protected $Chart;

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
     * Property to enable refresh button
     *
     * @var bool $EnableReload
     */
    protected $EnableReload = true;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $pagePath To store the route.
     * @param string $id       The unique id from the chart.
     */
    public function __construct(string $route, string $id)
    {
        parent::__construct($route);
        $this->setId($id);
        $this->Portlet = new Portlet($this->Id . 'Ptl', '');
    }


    /**
     * Function to load the chart data.
     *
     * @return void
     */
    abstract public function loadTable(): void;

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    abstract public function loadChart(): void;

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
     * Function to set enable button reload
     *
     * @param bool $enable To store the trigger the enable button.
     *
     * @return void
     */
    public function setEnableReloadButton(bool $enable = true): void
    {
        $this->EnableReload = $enable;
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
            $btn->setIcon(Icon::Refresh)->btnSuccess()->pullRight();
            $btn->addAttribute('style', 'display: none;');
            $this->Portlet->addButton($btn);
        }
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
        $this->loadChart();
        $this->JsonResponses = $this->Chart->loadChart();
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

        $script .= 'var ' . $scriptVar . " = new App.Chart('" . $this->Id . "', '" . $this->getStringParameter('route') . "', 'chart');";
        if (empty($this->CallBackParameters) === false) {
            foreach ($this->CallBackParameters AS $key => $val) {
                $script .= $scriptVar . ".addParameter('" . $key . "', '" . $val . "');";
            }
        }
        if ($this->EnableReload === true) {
            $script .= $scriptVar . '.setReloadButton();';
        }
        $script .= $scriptVar . '.create();';
        $script .= '</script>';

        return $script;

    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $height To set the height number data.
     *
     * @return void
     */
    public function setHeight(int $height): void
    {
        if ($height !== null) {
            $this->Portlet->addHeaderAttribute('style', 'height: 34px');

            if ($height <= 64) {
                $height = 64;
            } else {
                $height -= 64;
            }
            $this->Portlet->addBodyAttribute('style', 'height: ' . $height . 'px');
        }
    }

}
