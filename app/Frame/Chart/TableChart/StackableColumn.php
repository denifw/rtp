<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Chart\TableChart;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class StackableColumn extends AbstractTableChart
{

    /**
     * Property to store the categories.
     *
     * @var array $Categories
     */
    private $Categories = [];

    /**
     * Function to generate option chart.
     *
     * @return void
     */
    protected function generateOptionChart(): void
    {
        # Generate Prepare data.
        $this->doPrepareData();

        # Set Chart options
        $this->setChartOptions([
            'type' => 'column'
        ]);
        # Set XAxes of the chart.
        $this->setXAxesOptions(
            [
                'categories' => $this->Categories,
                'labels' => ['rotation' => -45]
            ]
        );

        # Re-write the YAxis
        $this->setYAxesOptions(
            [
                'title' => [
                    'text' => $this->YTitle
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
        $this->setTooltipOptions(
            [
                'shared' => true,
                'pointFormat' => '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>'
            ]
        );

        $this->setLegendOptions(
            ['enabled' => true, 'align' => 'right', 'verticalAlign' => 'top', 'layout' => 'vertical', 'x' => 0, 'y' => 100]
        );

        $this->setPlotOptions([
            'column' => [
                'stacking' => 'normal',
                'dataLabels' => [
                    'enabled' => true,
                    'color' => "(Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'"
                ]
            ]
        ]);

        $this->setSeriesOptions($this->SeriesData);
    }

    /**
     * Generate the series data for the chart.
     *
     * @return void
     */
    private function doPrepareData(): void
    {
        $rows = $this->Table->getRows();
        $xAxesData = [];
        foreach ($rows AS $row) {
            $xValue = $row[$this->XColumn];
            if (array_key_exists($xValue, $xAxesData) === false) {
                $xAxesData[$xValue] = [];
            }
            foreach ($this->YColumns as $column => $label) {
                if (array_key_exists($column, $xAxesData[$xValue]) === false) {
                    $xAxesData[$xValue][$column] = 0;
                }
                $xAxesData[$xValue][$column] += (float)$row[$column];
            }
        }


        $this->Categories = array_keys($xAxesData);
        # Do get top data limit.
        foreach ($this->YColumns as $column => $label) {
            $data = [];
            foreach ($xAxesData as $xValue => $option) {
                $data[] = $option[$column];
            }
            $this->SeriesData[] = [
                'name' => $label,
                'data' => $data
            ];
        }

    }
}
