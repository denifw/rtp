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
 * Class to control the creation of bar chart.
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Bar extends AbstractTableChart
{


    /**
     * Function to generate option chart.
     *
     * @return void
     */
    protected function generateOptionChart(): void
    {
        $this->doPrepareData();
        # Set Chart options
        $this->setChartOptions([
            'type' => 'bar'
        ]);
        # Set XAxes of the chart.
        $this->setXAxesOptions(
            [
                'type' => 'category',
                'labels' => ['rotation' => -45]
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
        # Set Tooltip of the chart.
        $this->setTooltipOptions(
            [
                'pointFormat' => '<span style="color:{point.color}">{point.name}</span> : <b>{point.y}</b><br/>'
            ]
        );
        $this->setLegendOptions(
            ['enabled' => true, 'align' => 'right', 'verticalAlign' => 'top', 'layout' => 'vertical', 'x' => 0, 'y' => 100]
        );

        $this->setSeriesOptions([[
            'name' => $this->XLabel,
            'colorByPoint' => true,
            'data' => $this->SeriesData
        ]]);
        if ($this->isDrillDownEnable() === true) {
            $this->setDrillDownOptions([
                'series' => $this->DrillDownData
            ]);
        }
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
        $yColumn = array_keys($this->YColumns)[0];
        $drillDownEnabled = $this->isDrillDownEnable();
        $drillDownData = [];
        foreach ($rows AS $row) {
            $xValue = $row[$this->XColumn];
            if (array_key_exists($xValue, $xAxesData) === false) {
                $xAxesData[$xValue] = 0;
            }
            $xAxesData[$xValue] += (float)$row[$yColumn];

            if ($drillDownEnabled === true) {
                if (array_key_exists($xValue, $drillDownData) === false) {
                    $drillDownData[$xValue] = [];
                }
                if (array_key_exists($row[$this->DrillDownColumn], $drillDownData[$xValue]) === false) {
                    $drillDownData[$xValue][$row[$this->DrillDownColumn]] = 0;
                }
                $drillDownData[$xValue][$row[$this->DrillDownColumn]] += (float)$row[$yColumn];
            }
        }

        # Prepared series data.
        foreach ($xAxesData as $name => $y) {
            $series = [
                'name' => $name,
                'y' => $y
            ];
            if ($drillDownEnabled === true) {
                $series['drilldown'] = $name;
            }
            $this->SeriesData[] = $series;
        }

        # Do prepare drill down data.
        if ($drillDownEnabled === true) {
            $xValues = array_keys($xAxesData);
            foreach ($xValues as $x) {
                $series = [];
                $data = $drillDownData[$x];
                foreach ($data as $key => $val) {
                    $series[] = [$key, $val];
                }
                $this->DrillDownData[] = [
                    'name' => $x,
                    'id' => $x,
                    'type' => $this->DrillDownType,
                    'data' => $series
                ];
            }
        }
    }

}
