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
 *
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class StackableColumn extends BasicChart
{

    /**
     * Property to store the column id of Y Axes.
     *
     * @var array $YAxesColumns
     */
    protected $YAxesColumns = [];

    /**
     * Property to store the column id of Y Axes.
     *
     * @var array $Categories
     */
    protected $Categories = [];

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $chartId    The Id to render the chart.
     * @param string $chartTitle The title for the chart if needed.
     */
    public function __construct($chartId = '', $chartTitle = '')
    {
        parent::__construct('column', $chartId);
        $this->setTitle($chartTitle);
    }

    /**
     * Function to set the column id for Y Axis from the table data.
     *
     * @param array $columnIds To store the column id of the x Axes.
     *
     * @return void
     */
    public function setYAxesColumns(array $columnIds): void
    {
        if ($this->Table !== null && empty($columnIds) === false) {
            if (empty($this->YAxesColumns) === true) {
                foreach ($columnIds as $id => $label) {
                    if (\in_array($id, $this->Table->getColumnIds(), true) === true) {
                        $this->YAxesColumns[$id] = $label;
                    } else {
                        Message::throwMessage('Column ' . $id . ' for Y axes does not exit in the table data.');
                    }
                }
            } else {
                Message::throwMessage('YAxes column already been set.');
            }
        } else {
            Message::throwMessage('Can not set YAxes column because chart table still null.');
        }
    }

    /**
     * Set custom options for chart.
     *
     * @return void
     */
    private function setOptionChart(): void
    {
        # Set XAxes of the chart.
        $this->setXAxes(
            [
                'categories' => $this->Categories,
                'labels' => ['rotation' => -45]
            ]
        );
        # Re-write the YAxis
        $this->setYAxes(
            [
                'title' => [
                    'text' => $this->YAxesLabel
                ],
                'stackLabels' => [
                    'enabled' => true,
                    'style' => [
                        'fontWeight' => 'bold',
                        'color' => "(Highcharts.theme && Highcharts.theme.textColor) || 'gray'"
                    ]
                ]
            ]
        );
        # Set Tooltip of the chart.
        $this->setTooltip(
            [
                'shared' => true,
                'pointFormat' => '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>'
            ]
        );
        $this->setLegend(
            ['enabled' => true, 'align' => 'right', 'verticalAlign' => 'top', 'layout' => 'vertical', 'x' => 0, 'y' => 100]
        );
        $this->setPlotOption([
            'column' => [
                'stacking' => 'normal',
                'dataLabels' => [
                    'enabled' => true,
                    'color' => "(Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'"
                ]
            ]
        ]);
    }

    /**
     * Function to do prepare the chart data base on the table data.
     *
     * @return void
     */
    public function doPrepareChartData(): void
    {
        if ($this->isCompleteChartData() === true) {
            $tableData = $this->Table->getRows();
            foreach ($tableData AS $row) {
                $seriesName = $this->doConvertSeriesNameByColumnType($row[$this->XAxesColumn], $this->XAxesColumnType);
                if (array_key_exists($seriesName, $this->ColumnSeriesData) === false) {
                    $this->ColumnSeriesData[$seriesName] = [];
                }
                foreach ($this->YAxesColumns as $id => $label) {
                    if (array_key_exists($id, $this->ColumnSeriesData[$seriesName]) === false) {
                        $this->ColumnSeriesData[$seriesName][$id] = 0;
                    }
                    $this->ColumnSeriesData[$seriesName][$id] += (float)$row[$id];
                }
            }
            $this->doGenerateSeriesData();
        }
    }


    /**
     * Generate the series data for the chart.
     *
     * @return void
     */
    protected function doGenerateSeriesData(): void
    {
        # Do get top data limit.
        foreach ($this->YAxesColumns as $id => $label) {
            $data = [];
            foreach ($this->ColumnSeriesData as $name => $val) {
                $data[] = $val[$id];
            }
            $this->addSeries([
                'name' => $label,
                'data' => $data
            ]);
        }

    }

    /**
     * Function to check is the chart data complete or not.
     *
     * @return boolean
     */
    protected function isCompleteChartData(): bool
    {
        if ($this->Table === null || empty($this->YAxesColumns) === true || empty($this->XAxesColumn) === true) {
            Message::throwMessage('Creation of Chart with id ' . $this->ChartId . ' failed, not all requirement data complete.');
        }

        return true;
    }


    /**
     * Function to check is the chart data complete or not.
     *
     * @return void
     */
    protected function loadCategories(): void
    {
        $tableData = $this->Table->getRows();
        foreach ($tableData AS $row) {
            $this->Categories[] = $row[$this->XAxesColumn];
        }
    }

    /**
     * Set options for chart.
     *
     * @return array
     */
    public function loadChart(): array
    {
        $this->loadCategories();
        $this->setOptionChart();
        $this->doPrepareChartData();

        return parent::loadChart();
    }
}
