<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Frame\Chart\TableChart;

/**
 * Grouped column chart
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class GroupedBar extends AbstractTableChart
{


    /**
     * Property to store the category of the chart.
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
        $this->doPrepareData();
        # set chart series
        $this->setChartOptions([
            'type' => 'bar'
        ]);
        $this->setXAxesOptions(
            [
                'categories' => $this->Categories
            ]
        );
        # Re-write the YAxis
        $this->setYAxesOptions(
            [
                'title' => [
                    'text' => $this->YTitle
                ]
            ]
        );
        $this->setTooltipOptions(
            [
                'headerFormat' => '<span style="font-size:10px">{point.key}</span><table>',
                'pointFormat' => '<tr><td style="color:{series.color};padding:0">{series.name}: </td><td style="padding:0"><b>{point.y:.,1f}</b></td></tr>',
                'footerFormat' => '</table>',
                'shared' => true,
                'useHTML' => true
            ]
        );
        $this->setLegendOptions(
            [
                'shadow' => false
            ]
        );
        # Re-write plot option
        $this->setPlotOptions(
            [
                'column' => [
                    'pointPadding' => 0.2,
                    'dataLabels' => 0
                ]
            ]
        );
        $this->setSeriesOptions($this->SeriesData);
    }
    /**
     * Function to do prepare the chart data base on the table data.
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
