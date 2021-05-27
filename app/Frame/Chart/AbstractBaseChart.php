<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Chart;

use App\Frame\Exceptions\Message;

/**
 * The abstract class of the chart.
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseChart
{
    /**
     * Property to store the id of the chart data.
     *
     * @var array $ChartOptions
     */
    private $ChartOptions = [];

    /**
     * Property to store the title of the chart.
     *
     * @var array $TitleOptions
     */
    private $TitleOptions = [];

    /**
     * Property to store the sub title of the chart.
     *
     * @var array $SubtitleOptions
     */
    private $SubtitleOptions = [];

    /**
     * Property to store the series of the chart.
     *
     * @var array $SeriesOptions
     */
    private $SeriesOptions = [];

    /**
     * Property to store the y axes chart.
     *
     * @var array $YAxesOptions
     */
    private $YAxesOptions = [];

    /**
     * Property to store the X axes chart.
     *
     * @var array $XAxesOptions
     */
    private $XAxesOptions = [];

    /**
     * Property to store the tooltip
     *
     * @var array $TooltipOptions
     */
    private $TooltipOptions = [];

    /**
     * Property to store the plot options.
     *
     * @var array $PlotOptions
     */
    private $PlotOptions = [];

    /**
     * Property to store the legend data.
     *
     * @var array $LegendOptions
     */
    private $LegendOptions = [];

    /**
     * Property to store the color data.
     *
     * @var array $ColorOptions
     */
    private $ColorOptions;

    /**
     * Property to store the export data.
     *
     * @var array $ExportOptions
     */
    private $ExportOptions;

    /**
     * Property to store the export data.
     *
     * @var array $PaneOptions
     */
    private $PaneOptions = [];

    /**
     * Property to store the drill down data.
     *
     * @var array $DrillDownOptions
     */
    private $DrillDownOptions = [];
    /**
     * Property to store the credits data.
     *
     * @var array $CreditOptions
     */
    private $CreditOptions;

    /**
     * Basic constructor to start up the object that generates new chart files.
     *
     * Within the constructor the id from the table will be placed in the attributes
     *
     */
    public function __construct()
    {
        $this->CreditOptions = [
            'enabled' => false
        ];
        $this->ExportOptions = [
            'enabled' => false,
            'buttons' => [
                'contextButtons' => [
                    'enabled' => 'false',
                    'menuItems' => 'null'
                ]
            ]
        ];
        $this->ColorOptions = ['#AA8C30', '#5485BC', '#5C9384', '#981A37', '#FCB319', '#86A033', '#614931', '#00526F', '#594266', '#cb6828', '#aaaaab', '#a89375'];
    }

    /**
     * Function to set the table object.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setChartOptions(array $options): void
    {
        $this->ChartOptions = $options;
    }

    /**
     * Function to set the title data.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setTitleOptions(array $options): void
    {
        $this->TitleOptions = $options;
    }

    /**
     * Function to set the subtitle data.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setSubtitleOptions(array $options): void
    {
        $this->SubtitleOptions = $options;
    }

    /**
     * Function to set the color data.
     *
     * @param array $colors To store the list colors.
     *
     * @return void
     */
    public function setColorsOptions(array $colors = []): void
    {
        $this->ColorOptions = $colors;
    }


    /**
     * Function to set the plot options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setPlotOptions(array $options): void
    {
        $this->PlotOptions = $options;
    }

    /**
     * Function to set the tooltips options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setTooltipOptions(array $options): void
    {
        $this->TooltipOptions = $options;
    }

    /**
     * Function to set the x axes options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setXAxesOptions(array $options): void
    {
        $this->XAxesOptions = $options;
    }

    /**
     * Function to set the y axes options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setYAxesOptions(array $options): void
    {
        $this->YAxesOptions = $options;
    }

    /**
     * Function to set the pane options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setPaneOptions(array $options): void
    {
        $this->PaneOptions = $options;
    }

    /**
     * Function to set the series options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setSeriesOptions(array $options): void
    {
        $this->SeriesOptions = $options;
    }

    /**
     * Function to add the series options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function addSeriesOptions(array $options): void
    {
        $this->SeriesOptions[] = $options;
    }

    /**
     * Function to set drill down options.
     *
     * @param array $options TO store the options.
     *
     * @return void
     */
    public function setDrillDownOptions(array $options): void
    {
        $this->DrillDownOptions = $options;
    }

    /**
     * Function to set legend options.
     *
     * @param string | array $options TO store the options.
     *
     * @return void
     */
    public function setLegendOptions($options): void
    {
        $this->LegendOptions = $options;
    }

    /**
     * Function to load the chart
     *
     * @return array
     */
    public function loadChart(): array
    {
        $data = [];
        $data['chart'] = $this->ChartOptions;
        $data['title'] = $this->TitleOptions;
        $data['subtitle'] = $this->SubtitleOptions;
        $data['colors'] = $this->ColorOptions;
        $data['credits'] = $this->CreditOptions;
        $data['plotOptions'] = $this->PlotOptions;
        $data['tooltip'] = $this->TooltipOptions;
        $data['xAxis'] = $this->XAxesOptions;
        $data['yAxis'] = $this->YAxesOptions;
        $data['pane'] = $this->PaneOptions;
        $data['legend'] = $this->LegendOptions;
        $data['series'] = $this->SeriesOptions;
        $data['drilldown'] = $this->DrillDownOptions;
        $data['exporting'] = $this->ExportOptions;

        return $data;
    }

    /**
     * Function to load the chart
     *
     * @param string $containerId To store the container id.
     *
     * @return string
     */
    public function renderChart(string $containerId): string
    {
        $data = $this->loadChart();
        if (empty($containerId) === true) {
            Message::throwMessage('Invalid container id for the chart.');
        }
        $script = '';
        $script .= '<script type="text/javascript">';
        $script .= ' Highcharts.chart(' . $containerId . ', ' . json_encode(array_filter($data)) . ');';
        $script .= '</script>';

        return $script;
    }
}
