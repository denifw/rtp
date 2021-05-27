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

use App\Frame\Chart\AbstractBaseChart;
use App\Frame\Exceptions\Message;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Chart
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractTableChart extends AbstractBaseChart
{
    /**
     * Property to store the object of table into the chart that contains all the series data for the chart.
     *
     * @var \App\Frame\Gui\Table $Table
     */
    protected $Table;
    /**
     * Property to store the x column
     *
     * @var string $XColumn
     */
    protected $XColumn = '';
    /**
     * Property to store the x label
     *
     * @var string $XLabel
     */
    protected $XLabel = 'Unknown';
    /**
     * Property to store the y columns
     *
     * @var array $YColumn
     */
    protected $YColumns = [];

    /**
     * Property to store the y title
     *
     * @var string $YTitle
     */
    protected $YTitle = 'Unknown';

    /**
     * Property to store the drill down column
     *
     * @var string $DrillDownColumn
     */
    protected $DrillDownColumn = '';

    /**
     * Property to store the drill down Type
     *
     * @var string $DrillDownType
     */
    protected $DrillDownType = '';

    /**
     * Property to store the drill down Type
     *
     * @var array $DrillDownType
     */
    static protected $DrillDownTypes = ['pie', 'bar', 'column'];

    /**
     * Property to store the series data.
     *
     * @var array $SeriesData
     */
    protected $SeriesData = [];

    /**
     * Property to store the drill down data.
     *
     * @var array $DrillDownData
     */
    protected $DrillDownData = [];

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
     * Function to generate option chart.
     *
     * @return void
     */
    abstract protected function generateOptionChart(): void;

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
     * @param string $yTitle To store the chart title.
     *
     * @return void
     */
    public function setYTitle(string $yTitle): void
    {
        if (empty($yTitle) === false) {
            $this->YTitle = $yTitle;
        }
    }

    /**
     * Function to set the x axes column.
     *
     * @param string $columnId To store the column for x axes.
     * @param string $label    To store the label for y axes.
     *
     * @return void
     */
    public function setXColumn(string $columnId, string $label = 'Unknown'): void
    {
        if (empty($columnId) === true) {
            Message::throwMessage('Not Allowed empty column id for X Axes column.');
        }
        if (in_array($columnId, $this->Table->getColumnIds(), true) === false) {
            Message::throwMessage('Column ' . $columnId . ' for X axes does not exit in the table data.');
        }
        $this->XColumn = $columnId;
        if (empty($label) === false) {
            $this->XLabel = $label;
        }
    }

    /**
     * Function to add the y axes column.
     *
     * @param string $columnId To store the column for y axes.
     * @param string $label    To store the label for y axes.
     *
     * @return void
     */
    public function addYColumn(string $columnId, string $label): void
    {
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
        $this->YColumns[$columnId] = $label;
    }

    /**
     * Function to set drill down chart.
     *
     * @param string $columnId      To store the column for y axes.
     * @param string $drillDownType To store the label for y axes.
     *
     * @return void
     */
    public function setDrillDown(string $columnId, string $drillDownType): void
    {
        if (empty($columnId) === true) {
            Message::throwMessage('Not Allowed empty column id for Y Axes column.');
        }
        if (array_key_exists($columnId, $this->YColumns) === true) {
            Message::throwMessage('Duplicate column id for Y axes.');
        }
        if (in_array($columnId, $this->Table->getColumnIds(), true) === false) {
            Message::throwMessage('Column ' . $columnId . ' for Y axes does not exit in the table data.');
        }
        if (empty($drillDownType) === true || in_array($drillDownType, self::$DrillDownTypes, true) === false) {
            Message::throwMessage('Not Allowed type of drill down chart. Allowed types are ' . implode(', ', self::$DrillDownTypes) . '.');
        }
        $this->DrillDownColumn = $columnId;
        $this->DrillDownType = $drillDownType;
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
        $this->generateOptionChart();

        return parent::loadChart();
    }


    /**
     * Function to check is drill down enable or not.
     *
     * @return bool
     */
    protected function isDrillDownEnable(): bool
    {
        return !empty($this->DrillDownColumn);
    }

    /**
     * Function to check is the chart data complete or not.
     *
     * @return void
     */
    private function doValidate(): void
    {
        if ($this->Table === null || empty($this->YColumns) === true || empty($this->XColumn) === true) {
            Message::throwMessage('Fail to create chart, not all requirement data complete.');
        }
    }


}
