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

use App\Frame\Chart\AbstractBaseChart;
use App\Frame\Exceptions\Message;

/**
 * Class to create chart for fixed column.
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class FixedColumn extends AbstractBaseChart
{
    /**
     * Property to store the object of table into the chart that contains all the series data for the chart.
     *
     * @var \App\Frame\Gui\Table $Table
     */
    private $Table;
    /**
     * Property to store the x column
     *
     * @var string $XColumn
     */
    private $XColumn = '';
    /**
     * Property to store the y columns
     *
     * @var array $YColumn
     */
    private $YColumns = [];

    /**
     * Property to store the y title
     *
     * @var array $YTitles
     */
    private $YTitles = [];

    /**
     * Property to store the y title id
     *
     * @var array $YTitleIds
     */
    private $YTitleIds = [];


    /**
     * Property to store the series data.
     *
     * @var array $SeriesData
     */
    private $SeriesData = [];

    /**
     * Property to store the chart title
     *
     * @var string $Title
     */
    private $Title = 'Unknown';

    /**
     * Property to store the chart subtitle
     *
     * @var string $Subtitle
     */
    private $Subtitle = '';

    /**
     * Property to store the categories.
     *
     * @var array $Categories
     */
    private $Categories = [];

    /**
     * Basic constructor to start up the object that generates new chart files.
     * Within the constructor the id from the table will be placed in the attributes
     *
     * @param \App\Frame\Gui\Table $table To store the table data.
     */
    public function __construct(\App\Frame\Gui\Table $table)
    {
        parent::__construct();
        $this->Table = $table;
    }

    /**
     * Function to set the title of the chart.
     *
     * @param string $title To store the chart title.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        if (empty($title) === false) {
            $this->Title = $title;
        }
    }

    /**
     * Function to set the sub title of the chart.
     *
     * @param string $subtitle To store the chart title.
     *
     * @return void
     */
    public function setSubtitle(string $subtitle): void
    {
        $this->Subtitle = $subtitle;
    }

    /**
     * Function to set the Y title of the chart.
     *
     * @param string $titleId   To store the id of the title.
     * @param string $title     To store the chart title.
     * @param bool   $alignLeft To store the position of the title.
     *
     * @return void
     */
    public function addYTitle(string $titleId, string $title = 'Unknown', bool $alignLeft = true): void
    {
        if (empty($titleId) === true) {
            Message::throwMessage('Invalid title id for fixed column chart.');
        }
        if (in_array($titleId, $this->YTitleIds, true) === true) {
            Message::throwMessage('Title with id ' . $titleId . ' already exist on fixed column chart.');
        }
        $this->YTitleIds[] = $titleId;
        if (empty($title) === true) {
            $title = 'Unknown';
        }
        $this->YTitles[$titleId] = [
            'text' => $title,
            'opposite' => $alignLeft
        ];

    }

    /**
     * Function to set the x axes column.
     *
     * @param string $columnId To store the column for x axes.
     *
     * @return void
     */
    public function setXColumn(string $columnId): void
    {
        if (empty($columnId) === true) {
            Message::throwMessage('Not Allowed empty column id for X Axes column.');
        }
        if (in_array($columnId, $this->Table->getColumnIds(), true) === false) {
            Message::throwMessage('Column ' . $columnId . ' for X axes does not exit in the table data.');
        }
        $this->XColumn = $columnId;
    }

    /**
     * Function to add the y axes column.
     *
     * @param string $titleId  To store the id of the title.
     * @param string $columnId To store the column for y axes.
     * @param string $label    To store the label for y axes.
     * @param array  $rgbColor To store the color value in rgb mode. ex ;  R:255, G:255, B:255, Opacity : 1 => [255, 255, 255, 1].
     *
     * @return void
     */
    public function addYColumn(string $titleId, string $columnId, string $label, array $rgbColor = []): void
    {
        if (empty($titleId) === true) {
            Message::throwMessage('Invalid title id for fixed column chart.');
        }
        if (in_array($titleId, $this->YTitleIds, true) === false) {
            Message::throwMessage('Not found title id for ' . $titleId . ' inside fixed column chart.');
        }
        if (empty($columnId) === true) {
            Message::throwMessage('Not Allowed empty column id for Y Axes column.');
        }
        if (array_key_exists($columnId, $this->YColumns) === true) {
            Message::throwMessage('Duplicate column id for Y axes.');
        }
        if (in_array($columnId, $this->Table->getColumnIds(), true) === false) {
            Message::throwMessage('Column ' . $columnId . ' for Y axes does not exit in the table data.');
        }
        if (empty($label) === true) {
            $label = 'Unknown';
        }
        if (empty($rgbColor) === true && \count($rgbColor) !== 4) {
            Message::throwMessage('Invalid RGB color format for the fixed column chart on yColumn ' . $columnId);
        }
        if(array_key_exists($titleId, $this->YColumns) === false) {
            $this->YColumns[$titleId] = [];
        }
        $this->YColumns[$titleId][$columnId] = [
            'label' => $label,
            'color' => $rgbColor,
        ];
    }

    /**
     * Function to load the chart
     *
     * @return array
     */
    public function loadChart(): array
    {
        $this->doValidate();
        $this->setTitleOptions([
            'text' => $this->Title
        ]);
        if (empty($this->Subtitle) === false) {
            $this->setSubtitleOptions([
                'text' => $this->Subtitle
            ]);
        }
        $this->doPrepareData();
        $this->generateOptionChart();

        return parent::loadChart();
    }

    /**
     * Function to generate option chart.
     *
     * @return void
     */
    private function generateOptionChart(): void
    {
        # Set Chart options
        $this->setChartOptions([
            'type' => 'column'
        ]);
        $this->setXAxesOptions(
            [
                'categories' => array_values($this->Categories)
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
        # Re-write the YAxis
        $this->setYAxesOptions($this->loadYOptions());
        $this->setLegendOptions(
            [
                'shadow' => true
            ]
        );
        # Re-write plot option
        $this->setPlotOptions(
            [
                'column' => [
                    'grouping' => false,
                    'shadow' => false,
                    'borderWidth' => 0
                ]
            ]
        );

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
            foreach ($this->YTitleIds as $id) {
                $yColumns = $this->YColumns[$id];
                foreach ($yColumns as $column => $value) {
                    if (array_key_exists($column, $xAxesData[$xValue]) === false) {
                        $xAxesData[$xValue][$column] = 0;
                    }
                    $xAxesData[$xValue][$column] += (float)$row[$column];
                }
            }
        }


        $this->Categories = array_keys($xAxesData);

        $countTitle = \count($this->YTitleIds);
        $index = 0;
        foreach ($this->YTitleIds as $id) {
            $yColumns = $this->YColumns[$id];
            $indexColumn = 0;
            foreach ($yColumns as $column => $value) {
                $data = [];
                foreach ($xAxesData as $xValue => $option) {
                    $data[] = $option[$column];
                }
                $series = [
                    'name' => $value['label'],
                    'color' => 'rgba(' . $value['color'][0] . ',' . $value['color'][1] . ',' . $value['color'][2] . ',' . $value['color'][3] . ')',
                    'data' => $data,
                    'pointPadding' => $this->loadPadding($countTitle, $indexColumn),
                    'pointPlacement' => $this->loadPlacement($countTitle, $index)
                ];
                if ($index > 0) {
                    $series['yAxis'] = $index;
                }
                $this->SeriesData[] = $series;
                $indexColumn++;
            }
            $index++;
        }

    }

    /**
     * Function to check is the chart data complete or not.
     *
     * @return void
     */
    private function doValidate(): void
    {
        $valid = true;

        if ($this->Table === null || empty($this->YTitleIds) === true || empty($this->XColumn) === true) {
            $valid = false;
        }
        if (count(array_keys($this->YColumns)) !== \count($this->YTitleIds)) {
            $valid = false;
        }
        if ($valid === false) {
            Message::throwMessage('Fail to create fixed column chart, not all requirement data complete.');
        }
    }


    /**
     * Function to get the padding data.
     *
     * @param int $countTitle To store the amount of title.
     * @param int $index      To store the index of series.
     *
     * @return float
     */
    private function loadPadding($countTitle, $index): float
    {
        $padding = [
            ['padding' => 0, 'shadow' => 0.2],
            ['padding' => 0.3, 'shadow' => 0.4],
            ['padding' => 0.3, 'shadow' => 0.4],
            ['padding' => 0.4, 'shadow' => 0.45],
            ['padding' => 0.4, 'shadow' => 0.45],
        ];
        if ($index === 0) {
            $result = $padding[$countTitle - 1]['padding'];
        } else {
            $result = $padding[$countTitle - 1]['shadow'];
        }

        return $result;
    }


    /**
     * Function to get the placement data.
     *
     * @param int $countTitle To store the amount of title.
     * @param int $index      To store the index of series.
     *
     * @return float
     */
    private function loadPlacement($countTitle, $index): float
    {
        $padding = [
            ['placement' => 0, 'increment' => 0],
            ['placement' => -0.2, 'increment' => 0.4],
            ['placement' => -0.3, 'increment' => 0.3],
            ['placement' => -0.4, 'increment' => 0.2],
            ['placement' => -0.4, 'increment' => 0.2],
        ];
        $result = $padding[$countTitle - 1]['placement'] + ($padding[$countTitle - 1]['increment'] * $index);

        return $result;
    }


    /**
     * Function to load y options.
     *
     * @return array
     */
    private function loadYOptions(): array
    {
        $result = [];
        $index = 0;
        foreach ($this->YTitleIds as $id) {
            $data = [
                'title' => [
                    'text' => $this->YTitles[$id]['text']
                ]
            ];
            if ($index > 0) {
                $data['opposite'] = $this->YTitles[$id]['opposite'];
            }
            $result[] = $data;
            $index++;
        }

        return $result;
    }


}
