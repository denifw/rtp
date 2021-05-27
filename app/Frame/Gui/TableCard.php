<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;

/**
 * Class to build layout like card
 *
 * @package    app
 * @subpackage App\Frame\Gui
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class TableCard extends Table
{
    /**
     * Variable to store the template path.
     *
     * @var string $TemplatePath
     */
    private $TemplatePath = '';

    /**
     * Variable to store the template path.
     *
     * @var \App\Frame\Gui\Templates\AbstractTemplate $Card
     */
    private $Card;
    /**
     * Attribute to store all the grid column settings.
     *
     * @var array $GridColumns
     */
    protected $GridColumns = [];
    /**
     * Attribute to store all the inline style for the template.
     *
     * @var array $Height
     */
    protected $Height = [];

    /**
     * Will create the complete html table and return the result.
     *
     * All checks that are necessary to make a correct table will be set done here
     *
     * @return string The complete html table.
     */
    public function createTable(): string
    {
        $result = '';
        if (empty($this->TemplatePath) === false) {
            foreach ($this->Body AS $rowId => $row) {
                $row['rowId'] = $rowId;
                $data = $this->doPrepareData($row);
                $this->Card = new $this->TemplatePath();
                $this->Card->setData($data);
                if (empty($this->Height) === false) {
                    $this->Card->setHeight($this->Height['height'], $this->Height['uom']);
                }
                if (empty($this->GridColumns) === false) {
                    $this->Card->setGridDimension($this->GridColumns['lg'], $this->GridColumns['md'], $this->GridColumns['sm'], $this->GridColumns['xs']);
                }
                $result .= $this->Card->createView();
            }
        } else {
            Message::throwMessage('Invalid template path for table card system.');
        }

        return $result;
    }

    /**
     * Function to set title column.
     *
     * @param string $path To Store the template path.
     */
    public function setTemplatePath(string $path): void
    {
        if (empty($path) === true) {
            Message::throwMessage('Invalid template path for table card system.');
        }
        $this->TemplatePath = '\\App\\Frame\\Gui\\Templates\\' . str_replace('/', '\\', $path);
        if (class_exists($this->TemplatePath) === false) {
            Message::throwMessage('Invalid class template path ' . $this->TemplatePath . ' for table card system.');
        }
    }

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
    public function setCardGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->GridColumns = [
            'lg' => $large,
            'md' => $medium,
            'sm' => $small,
            'xs' => $extraSmall,
        ];
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $height To set the height number data.
     * @param string  $uom    To set unit of measure height.
     *
     * @return void
     */
    public function setCardHeight(int $height, string $uom = 'px'): void
    {
        if (empty($uom) === true) {
            $uom = 'px';
        }
        if ($height !== null) {
            $this->Height = [
                'height' => $height,
                'uom' => $uom,
            ];
        }
    }

    /**
     * Function to prepare the table data for the chart data.
     *
     * @param array $row To store the row data.
     *
     * @return array
     */
    private function doPrepareData(array $row): array
    {

        $result = [];
        foreach ($this->ColumnIds AS $column) {
            $value = $this->getValueCellTable($row, $column);
            $value = $this->getHyperlinkCellTable($row, $column, $value);
            $result[$column] = $value;
        }
        return $result;
    }

}
