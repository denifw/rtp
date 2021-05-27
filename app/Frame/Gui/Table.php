<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2017 C-Book
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelInfo;
use App\Frame\Gui\Html\Labels\LabelWarning;

/**
 * Class  to manage the creation of tab.
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class Table
{
    /**
     * List of all available column types the table can have.
     *
     * Important is that all the elements must be in lower case.
     *
     * @var array $ColumnTypeList
     */
    private static $ColumnTypeList = ['currency', 'float', 'integer', 'yesno', 'date', 'time', 'datetime'];

    /**
     * List of all the body data.
     *
     * @var array $Body
     */
    protected $Body = [];
    /**
     * List of all the header name column.
     *
     * @var array $Header
     */
    protected $Header = [];
    /**
     * The names of all columns.
     *
     * @var array $ColumnIds
     */
    protected $ColumnIds = [];

    /**
     * Array of all attributes corresponding with the table.
     *
     * @var array $TableAttributes
     */
    private $TableAttributes;
    /**
     * List of all available footer types the footer cell can have.
     *
     * @var array $FooterTypeList
     */
    private $FooterTypeList = ['SUM', 'AVG'];
    /**
     * Attribute that is added for one special column.
     *
     * @var array $ColumnType
     */
    protected $ColumnType = [];
    /**
     * Attribute to store the view action.
     *
     * @var array $TableId
     */
    protected $TableId = '';
    /**
     * If property is set to true then there will be an extra column in the table where each row number.
     *
     * @var boolean $EnableLineNumber Default value is true
     */
    private $EnableLineNumber = true;
    /**
     * Attribute to store the start number of line.
     *
     * @var integer $OffsetLineNumber To store the start number for the line number.
     */
    private $OffsetLineNumber = 0;
    /**
     * Attribute that is added for one special column.
     *
     * @var array $ColumnAttributes
     */
    private $ColumnAttributes = [];
    /**
     * All attributes that are defined per cell.
     *
     * @var array $CellAttributes
     */
    private $CellAttributes;
    /**
     * List of all attributes that are variable for each row.
     *
     * @var array $RowAttributes
     */
    private $RowAttributes = [];
    /**
     * Property to store the number formatter object.
     *
     * @var \App\Frame\Formatter\NumberFormatter $NumberFormatter ;
     */
    private $NumberFormatter;

    /**
     * List of all cells in the footer row.
     *
     * @var array $Footer Array
     */
    private $Footer = [];

    /**
     * List of footer cell that can have a sort of calculation.
     *
     * @var array $FooterType
     */
    private $FooterType = [];

    /**
     * Attribute that is added for specific header cell.
     *
     * @var array $HeaderAttributes
     */
    protected $HeaderAttributes = [];


    /**
     * Attribute to store the hyperlink column.
     *
     * @var array $HyperlinkColumns
     */
    private $HyperlinkColumns = [];

    /**
     * Attribute to set header disable default false
     *
     * @var bool $DisableHeader
     */
    private $DisableHeader = false;


    /**
     * Attribute to store the view action.
     *
     * @var array $ViewAction
     */
    private $ViewAction = [];


    /**
     * Attribute to store the view action.
     *
     * @var array $EditAction
     */
    private $EditAction = [];

    /**
     * Attribute to store the copy action.
     *
     * @var array $CopyAction
     */
    private $CopyAction = [];


    /**
     * Attribute to store the view action.
     *
     * @var array $DeleteAction
     */
    private $DeleteAction = [];

    /**
     * Attribute to set footer disable default false
     *
     * @var bool $DisableFooter
     */
    private $DisableFooter = false;

    /**
     * Attribute to set footer disable default false
     *
     * @var bool $AllowEmpty
     */
    private $AllowEmpty = false;

    /**
     * Constructor for the table class.
     *
     * @param string $tableId To store the id of the table.
     */
    public function __construct(string $tableId)
    {
        $this->TableId = $tableId;
        $this->addTableAttribute('id', $tableId);
        $this->addTableAttribute('class', 'table table-bordered jambo_table');
        $this->NumberFormatter = new NumberFormatter();
    }

    /**
     * Add extra attributes to the table property.
     *
     * @param string $attributeName The attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    public function addTableAttribute($attributeName, $value): void
    {
        $this->TableAttributes[$attributeName] = $value;
    }

    /**
     * Function to convert the table to string.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->createTable();
    }

    /**
     * Will create the complete html table and return the result.
     *
     * All checks that are necessary to make a correct table will be set done here
     *
     * @return string The complete html table.
     */
    public function createTable(): string
    {
        $table = '';
        # Check to see if there are values, otherwise the table will be empty
        if (empty($this->Body) === false || $this->AllowEmpty === true) {
            # Create table
            # Open the divider.
            $table .= '<table ' . $this->getTableAttributes() . '> ';
            if ($this->DisableHeader === false) {
                $table .= $this->getHeader();
            }
            $table .= $this->getBody();
            if ($this->DisableFooter === false) {
                $table .= $this->getFooter();
            }
            $table .= '</table>';
            # Close the divider.
        } else {
            $table = '<span style="color: #FF1800">' . Trans::getWord('noDataFound', 'message') . '</span>';
        }

        return $table;
    }

    /**
     * Returns the complete string with all the table attributes that are added via the object or via internal use of
     * the class.
     *
     * @return string list with all table attributes
     */
    private function getTableAttributes(): string
    {
        $attr = '';
        # Gets complete list with all table attributes
        foreach ($this->TableAttributes as $key => $value) {
            # Concatenate to one string line
            $attr .= ' ' . $key . '="' . $value . '"';
        }

        return $attr;
    }

    /**
     * The complete creation of the header construction is made by this method.
     *
     * @return string
     */
    private function getHeader(): string
    {
        $result = '';
        if (empty($this->Header) === false) {
            # Declare variables
            $headerCell = '';
            $lineNumber = '';
            # Load the detail header of the table.
            foreach ($this->Header as $columnId => $value) {
                if (empty($value) === true) {
                    $this->addHeaderAttribute($columnId, 'style', 'display:none');
                    $this->addColumnAttribute($columnId, 'style', 'display:none');
                }
                if (array_key_exists($columnId, $this->HeaderAttributes) === true) {
                    $headerCell .= '<th' . $this->getHeaderAttributes($columnId) . '>' . $value . '</th>';
                } else {
                    $headerCell .= '<th>' . $value . '</th>';
                }
            }
            # Add additional column if the line numbers are set on true.
            if ($this->EnableLineNumber === true) {
                $lineNumber = '<th>#</th>';
            }

            $action = '';
            if (empty($this->ViewAction) === false || empty($this->EditAction) === false
                || empty($this->DeleteAction) === false || empty($this->CopyAction) === false) {
                $action = '<th>' . Trans::getWord('action') . '</th>';
            }

            $result = '<thead><tr>' . $lineNumber . $headerCell . $action . '</tr></thead>';
        } else {
            Message::throwMessage('There is no header set for the table.');
        }

        return $result;
    }

    /**
     * The complete creation of the footer construction is made by this method.
     *
     * @return string
     */
    private function getFooter(): string
    {
        $result = '';
        if (empty($this->FooterType) === false) {
            $this->doProcessFooterType();
            $result .= '<tr>';
            if ($this->EnableLineNumber === true) {
                $result .= '<td></td>';
            }
            foreach ($this->ColumnIds as $columnId) {
                if (\array_key_exists($columnId, $this->Footer) === true) {
                    $result .= '<td style = "text-align: right">' . $this->Footer[$columnId] . '</td>';
                } else {
                    $result .= '<td></td>';
                }
            }
            $result .= '</tr>';
        }

        return '<tfoot> ' . $result . '</tfoot>';
    }

    /**
     * Function to procees footer type
     *
     * @return void
     */
    private function doProcessFooterType(): void
    {
        foreach ($this->FooterType as $columnId => $footerType) {
            $value = 0;
            $numberValues = 0;
            foreach ($this->Body as $row) {
                if (array_key_exists($columnId, $row) === true) {
                    if (empty($row[$columnId]) === false && is_numeric($row[$columnId])) {
                        $value += $row[$columnId];
                        $numberValues++;
                    }
                }
            }
            $result = '';
            if ($footerType === 'SUM') {
                if (empty($value) === false) {
                    $result = $this->doProcessColumnType($this->ColumnType[$columnId], $value);
                }
            } else if ($footerType === 'AVG') {
                if (empty($value) === false) {
                    $value = $value / $numberValues;
                    $result = $this->doProcessColumnType($this->ColumnType[$columnId], $value);
                }
            }
            $this->Footer[$columnId] = $result;
        }
    }

    /**
     * Returns the complete string with all the attributes of specific header cell.
     *
     * @param string $columnId The column identifier.
     *
     * @return string
     */
    private function getHeaderAttributes($columnId): string
    {
        $attr = '';
        $attributes = $this->HeaderAttributes[$columnId];
        foreach ($attributes as $attributeName => $attributeValue) {
            $attr .= ' ' . $attributeName . '="' . $attributeValue . '"';
        }

        return $attr;
    }

    /**
     * Create the complete.
     *
     * @return string
     */
    private function getBody(): string
    {
        $rows = '';
        # Build up all rows for table
        $countBody = \count($this->Body);
        for ($i = 0; $i < $countBody; $i++) {
            $rows .= $this->getBodyRow($i);
        }

        return '<tbody id="' . $this->TableId . '_body">' . $rows . '</tbody>';
    }

    /**
     * Will create one complete row depending on the data that is given from the Body property.
     *
     * If the property for line numbers is set there will be an extra column added to the row
     *
     * @param integer $rowId Line number of the table.
     *
     * @return string complete correct HTML row with all cells included
     */
    private function getBodyRow($rowId): string
    {
        # Get the row span data.
        $rowNumber = '';
        # Add an extra column to row to display the row number
        if ($this->EnableLineNumber === true) {
            $rowNumber = '<td>' . ($rowId + (1 + $this->OffsetLineNumber)) . '</td>';
        }
        # Declare some vars
        $rowCells = '';
        $row = $this->Body[$rowId];
        foreach ($this->ColumnIds as $columnId) {
            $row['rowId'] = $rowId;
            $value = $this->getValueCellTable($row, $columnId);
            $value = $this->getHyperlinkCellTable($row, $columnId, $value);

            # Combine inner html with the cell attributes
            $rowCells .= '<td' . $this->getCellAttributes($columnId, $rowId) . $this->getColumnAttributes($columnId) . '>' . $value . '</td>';
        }
        # Return the complete line with all its attributes and all the column cells
        $row = '<tr' . $this->getRowAttributes($rowId) . '>' . $rowNumber . $rowCells . $this->getActionRow($row, $rowId) . '</tr>';

        return $row;
    }

    /**
     * Function to get the action
     *
     * If the property for line numbers is set there will be an extra column added to the row
     *
     * @param integer $rowId Line number of the table.
     * @param array $rowData To store the table row data.
     *
     * @return string complete correct HTML row with all cells included
     */
    private function getActionRow(array $rowData, $rowId): string
    {
        $result = '';
        if (empty($this->ViewAction) === false) {
            $this->ViewAction['icon'] = Icon::Eye;
            $this->ViewAction['class'] = 'btn btn-success btn-icon-only';
            $btnId = $this->TableId . 'vw' . $rowId;
            if ($this->ViewAction['type'] === 'modal') {
                $result .= $this->generateActionModalButton($btnId, $this->ViewAction, $rowData) . ' &nbsp;';
            }
            if ($this->ViewAction['type'] === 'link') {
                $result .= $this->generateActionHyperlink($btnId, $this->ViewAction, $rowData) . ' &nbsp;';
            }
        }
        if (empty($this->CopyAction) === false) {
            $this->CopyAction['icon'] = Icon::Copy;
            $this->CopyAction['class'] = 'btn btn-dark btn-icon-only';
            $btnId = $this->TableId . 'cp' . $rowId;
            if ($this->CopyAction['type'] === 'modal') {
                $result .= $this->generateActionModalButton($btnId, $this->CopyAction, $rowData) . ' &nbsp;';
            }
            if ($this->CopyAction['type'] === 'link') {
                $result .= $this->generateActionHyperlink($btnId, $this->CopyAction, $rowData) . ' &nbsp;';
            }
        }
        if (empty($this->EditAction) === false) {
            $this->EditAction['icon'] = Icon::Pencil;
            $this->EditAction['class'] = 'btn btn-primary btn-icon-only';
            $btnId = $this->TableId . 'ed' . $rowId;
            if ($this->EditAction['type'] === 'modal') {
                $result .= $this->generateActionModalButton($btnId, $this->EditAction, $rowData) . ' &nbsp;';
            }
            if ($this->EditAction['type'] === 'link') {
                $result .= $this->generateActionHyperlink($btnId, $this->EditAction, $rowData) . ' &nbsp;';
            }
        }

        if (empty($this->DeleteAction) === false) {
            $this->DeleteAction['icon'] = Icon::Trash;
            $this->DeleteAction['class'] = 'btn btn-danger btn-icon-only';
            $btnId = $this->TableId . 'dl' . $rowId;
            if ($this->DeleteAction['type'] === 'modal') {
                $result .= $this->generateActionModalButton($btnId, $this->DeleteAction, $rowData);
            }
            if ($this->DeleteAction['type'] === 'link') {
                $result .= $this->generateActionHyperlink($btnId, $this->DeleteAction, $rowData);
            }
        }
        if (empty($result) === false) {
            $result = '<td style="text-align: center">' . $result . '</td>';

        }

        return $result;
    }

    /**
     * Function to get the action
     *
     * If the property for line numbers is set there will be an extra column added to the row
     *
     * @param string $btnId Line number of the table.
     * @param array $buttonData To store the table row data.
     * @param array $rowData To store the table row data.
     *
     * @return string complete correct HTML row with all cells included
     */
    private function generateActionModalButton(string $btnId, array $buttonData, array $rowData): string
    {
        $btn = new ModalButton($btnId, '', $buttonData['modal']->getModalId());
        $btn->setIcon($buttonData['icon']);
        $btn->addAttribute('class', $buttonData['class']);
        $btn->setEnableCallBack($buttonData['route'], $buttonData['callbackFunction']);
        foreach ($buttonData['param'] as $key) {
            if (array_key_exists($key, $rowData) === true) {
                $btn->addParameter($key, $rowData[$key]);
            }
        }

        return $btn;
    }

    /**
     * Function to get the action
     *
     * If the property for line numbers is set there will be an extra column added to the row
     *
     * @param string $btnId Line number of the table.
     * @param array $buttonData To store the table row data.
     * @param array $rowData To store the table row data.
     *
     * @return string complete correct HTML row with all cells included
     */
    private function generateActionHyperlink(string $btnId, array $buttonData, array $rowData): string
    {
        if (array_key_exists('popup', $buttonData) === true) {
            $params = [];
            $paramsUrl = [];
            foreach ($buttonData['param'] as $key) {
                if (array_key_exists($key, $rowData) === true) {
                    $params[$key] = $rowData[$key];
                    $paramsUrl[] = $key . '=' . $rowData[$key];
                }
            }
            if ($buttonData['popup'] === true) {
                $btn = new Button($btnId, '');
                $btn->setIcon($buttonData['icon']);
                $btn->addAttribute('class', $buttonData['class']);
                $btn->setPopup($buttonData['route'], $params);
            } else {
                $url = $buttonData['route'];
                if (empty($paramsUrl) === false) {
                    $url .= '?' . implode('&', $paramsUrl);
                }
                $btn = new HyperLink($btnId, '', url($url));
                $btn->setIcon($buttonData['icon']);
                $btn->addAttribute('class', $buttonData['class']);
            }

            return $btn;
        }

        return '';

    }

    /**
     * Function to get the cell data.
     *
     * This is important so we need to centralize all the get data from tbl body to make a standard of the data
     * structure.
     *
     * @param array $row To store row data.
     * @param string $columnId To store the column name of the cell that we will get the data.
     *
     * @return mixed
     */
    public function getValueCellTable($row, $columnId)
    {
        $result = '';
        if (array_key_exists($columnId, $row) === true) {
            $result = $row[$columnId];
            if (array_key_exists($columnId, $this->ColumnType) === true && empty($result) === false) {
                $result = $this->doProcessColumnType($this->ColumnType[$columnId], $result);
            }
        }

        return $result;
    }

    /**
     * Function to get the cell data.
     *
     * This is important so we need to centralize all the get data from tbl body to make a standard of the data
     * structure.
     *
     * @param array $row To store row data.
     * @param string $columnId To store the column name of the cell that we will get the data.
     * @param string $label To store the cell value for the hyperlink.
     *
     * @return mixed
     */
    protected function getHyperlinkCellTable($row, $columnId, $label)
    {
        $result = $label;
        if (array_key_exists($columnId, $this->HyperlinkColumns) === true && empty($label) === false) {
            $paramIds = $this->HyperlinkColumns[$columnId]['params'];
            $param = '';
            $params = [];
            if (empty($paramIds) === false) {
                foreach ($paramIds as $key => $val) {
                    $params[] = $key . '=' . $this->getValueCellTable($row, $val);
                }
                $param = '?' . implode('&', $params);
            }
            $url = '/' . $this->HyperlinkColumns[$columnId]['route'] . $param;
            $link = new HyperLink($columnId . '[' . $row['rowId'] . ']', $label, $url);
            $result = $link;
        }

        return $result;
    }

    /**
     * Process all the column types that are provided for the given column Ids.
     *
     * @param string $type The store the column type.
     * @param string $value The attribute value.
     *
     * @return mixed
     */
    protected function doProcessColumnType($type, $value)
    {
        $result = '';
        if ($type === 'float') {
            $result = $this->NumberFormatter->doFormatFloat($value);
        } else if ($type === 'currency') {
            $result = $this->NumberFormatter->doFormatFloat($value, true);
        } else if ($type === 'integer') {
            $result = $this->NumberFormatter->doFormatInteger($value);
        } else if ($type === 'yesno') {
            if ($value === 'Y') {
                $result = new LabelInfo(Trans::getWord('yes'));
            } else if ($value === 'N') {
                $result = new LabelWarning(Trans::getWord('no'));
            }
        } else if ($type === 'date') {
            $result = DateTimeParser::format($value, 'Y-m-d', 'd.M.Y');
        } else if ($type === 'time') {
            $result = DateTimeParser::format($value, 'H:i:s', 'H:i');
        } else if ($type === 'datetime') {
            $result = DateTimeParser::format($value, 'Y-m-d H:i:s', 'H:i - d.M.Y');
        } else {
            $result = $value;
        }

        return $result;
    }

    /**
     * Returns the cell attributes if they are set.
     *
     * @param integer $columnId The column identifier for the cell.
     * @param integer $rowId The row identifier for the cell.
     *
     * @return string
     */
    private function getCellAttributes($columnId, $rowId): string
    {
        $attr = '';
        if (empty($this->CellAttributes[$columnId][$rowId]) === false) {
            $cell = $this->CellAttributes[$columnId][$rowId];
            foreach ($cell as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
        }

        # Return the complete attribute listing
        return $attr;
    }

    /**
     * Return the complete attribute listing.
     *
     * @param string $columnId The column identifier.
     *
     * @return string
     */
    private function getColumnAttributes($columnId): string
    {
        $attr = '';
        # Check if the column attribute is set
        if (array_key_exists($columnId, $this->ColumnAttributes) === true) {
            # get all attribute for one column
            $attributes = $this->ColumnAttributes[$columnId];
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
        }

        # Return the complete attribute listing
        return $attr;
    }

    /**
     * Returns the complete list of attributes.
     *
     * @param integer $rowId Row number.
     *
     * @return string List with all static row attributes
     */
    private function getRowAttributes($rowId): string
    {
        $attr = '';
        if (array_key_exists($rowId, $this->RowAttributes) === true) {
            $attributes = $this->RowAttributes[$rowId];
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '" ';
            }
        }

        return $attr;
    }

    /**
     * Function to set the offset of the line number.
     *
     * @param string $offsetNumber To store the offset of the line number.
     *
     * @return void
     */
    public function setStartRowNumber($offsetNumber): void
    {
        if (is_numeric($offsetNumber) === true) {
            $this->OffsetLineNumber = $offsetNumber;
        }
    }

    /**
     * Set the trigger to show line number in the table.
     *
     * @return void
     */
    public function setDisableLineNumber(): void
    {
        $this->EnableLineNumber = false;
    }

    /**
     * Set the trigger to show line number in the table.
     *
     * @return void
     */
    public function setEnableLineNumber(): void
    {
        $this->EnableLineNumber = true;
    }

    /**
     * Add a column to the table after a specific given column name, if the column name does not exist add the column
     * at the end of the table.
     *
     * @param string $afterColumn The unique column from the table, this is the identifying column, after this it will
     *                            be added.
     * @param string $uniqueId The unique name of the column.
     * @param string $columnName The description or translation of the column name.
     *
     * @return void
     */
    public function addColumnAfter($afterColumn, $uniqueId, $columnName): void
    {
        if (array_key_exists($afterColumn, $this->Header) === true) {
            $new = [];
            $this->ColumnIds = [];
            foreach ($this->Header as $k => $value) {
                $new[$k] = $value;
                $this->ColumnIds[] = $k;
                if ($k === $afterColumn) {
                    $new[$uniqueId] = $columnName;
                    $this->ColumnIds[] = $uniqueId;
                }
            }
            $this->Header = $new;
        } else {
            $this->addColumnAtTheEnd($uniqueId, $columnName);
        }
    }

    /**
     * Add a column to the table after a specific given column name, if the column name does not exist add the column
     * at the end of the table.
     *
     * @param string $uniqueId The unique name of the column.
     * @param string $columnName The description or translation of the column name.
     *
     * @return void
     */
    public function renameColumn($uniqueId, $columnName): void
    {
        if (array_key_exists($uniqueId, $this->Header) === true) {
            $this->Header[$uniqueId] = $columnName;
        } else {
            $this->addColumnAtTheEnd($uniqueId, $columnName);
        }
    }

    /**
     * Just add a column at the end of the array.
     *
     * @param string $uniqueId The unique name of the column.
     * @param string $columnName The description or translation of the column name.
     *
     * @return void
     */
    public function addColumnAtTheEnd($uniqueId, $columnName): void
    {
        $this->Header[$uniqueId] = $columnName;
        $this->ColumnIds[] = $uniqueId;
    }

    /**
     * Add a column at the beginning of the table.
     *
     * @param string $uniqueId The unique name of the column.
     * @param string $columnName The description or translation of the column name.
     *
     * @return void
     */
    public function addColumnAtTheBeginning($uniqueId, $columnName): void
    {
        # set the header if it is the first column.
        if ($this->Header === null) {
            $this->Header = [];
        }
        $this->Header = array_merge([$uniqueId => $columnName], $this->Header);
        array_unshift($this->ColumnIds, $uniqueId);
    }

    /**
     * Will add for each row extra attributes.
     *
     * All there attributes are special for each row and are given via the.
     * data array with the column id to witch its corresponds.
     *
     * @param string $rowId The id of the row.
     * @param string $attributeName The attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    public function addRowAttribute($rowId, $attributeName, $value): void
    {
        # check to see if column exists in body list
        $this->RowAttributes[$rowId][$attributeName] = $value;
    }

    /**
     * Add multiple rows to the table body this must be a three dimensional array.
     *
     * @param array $rowsData To store the list data for the table.
     *
     * @return void
     */
    public function addRows(array $rowsData = []): void
    {
        if (empty($rowsData) === false) {
            $this->Body = array_merge($this->Body, $rowsData);
        }
    }

    /**
     * Add a single row to the body array.
     *
     * @param array $rowData Add a single row to the body of the table.
     *
     * @return void
     */
    public function addRow(array $rowData): void
    {
        $this->Body[] = $rowData;
    }

    /**
     * Function to get all the rows in table.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->Body;
    }

    /**
     * Add extra attributes to the cell of the table to create.
     *
     * @param string $columnId The column identifier.
     * @param integer $rowId The row identifier.
     * @param string $attributeName The attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    public function addCellAttribute($columnId, $rowId, $attributeName, $value): void
    {
        $this->CellAttributes[$columnId][$rowId][$attributeName] = $value;
    }

    /**
     * Return the exact header names and the keys.
     *
     * @return array
     */
    public function getHeaderRow(): array
    {
        return $this->Header;
    }

    /**
     * Return the exact footer names and the keys.
     *
     * @return array
     */
    public function getFooterRow(): array
    {
        $this->doProcessFooterType();

        return $this->Footer;
    }

    /**
     * Return the exact footer names and the keys.
     *
     * @return array
     */
    public function getFooterType(): array
    {
        return $this->FooterType;
    }

    /**
     * Return the exact column type and the keys.
     *
     * @return array
     */
    public function getColumnType(): array
    {
        return $this->ColumnType;
    }

    /**
     * Specify the type of a certain column.
     *
     * @param string $columnId The column name.
     * @param string $type The type for the column.
     *
     *
     * @return void
     */
    public function setColumnType($columnId, $type): void
    {
        # Check if the column id exist in the Column id list.
        if (\in_array($columnId, $this->ColumnIds, true) === true) {
            $type = mb_strtolower($type);
            # List of valid positions where the text may be placed
            if (\in_array($type, self::$ColumnTypeList, true) === true) {
                if (array_key_exists($columnId, $this->ColumnType) === false) {
                    if (\in_array($type, ['float', 'integer', 'currency'], true) === true) {
                        $this->addColumnAttribute($columnId, 'style', 'text-align:right');
                    } else if (\in_array($type, ['yesno', 'date', 'time', 'datetime'], true) === true) {
                        $this->addColumnAttribute($columnId, 'style', 'text-align:center');
                    }
                    # Change the property
                    $this->ColumnType[$columnId] = $type;
                } else {
                    Message::throwMessage('Column type for column id ' . $columnId . ' has been set.');
                }
            } else {
                Message::throwMessage('Invalid Column type for column id ' . $columnId . '.');
            }
        }
    }

    /**
     * Add extra attributes to the columns of the table to create.
     *
     * @param string $columnId The column identifier.
     * @param string $attributeName The attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    public function addColumnAttribute($columnId, $attributeName, $value): void
    {
        $this->ColumnAttributes[$columnId][$attributeName] = $value;
    }

    /**
     * Set a specific footer type.
     *
     * @param string $columnId The column identifier.
     * @param string $type The footer type.
     *
     * @return void
     */
    public function setFooterType($columnId, $type): void
    {
        # Check if the column id exist in the header.
        if (\in_array($columnId, $this->ColumnIds, true) === true) {
            # List of valid positions where the text may be placed
            if (\in_array($type, $this->FooterTypeList, true) === true) {
                if (array_key_exists($columnId, $this->FooterType) === false) {
                    # Change the property
                    $this->FooterType[$columnId] = $type;
                } else {
                    Message::throwMessage('Footer type for column ' . $columnId . ' has been set.');
                }
            } else {
                Message::throwMessage('Invalid footer type for column ' . $columnId . '.');
            }
        }
    }

    /**
     * Remove added columns out of the table.
     *
     * @param string $columnId The unique name of the column.
     *
     * @return void
     */
    public function removeColumn(string $columnId): void
    {
        if (\is_string($columnId) === true && array_key_exists($columnId, $this->Header) === true) {
            # Unset the header row
            unset($this->Header[$columnId]);
            $key = array_search($columnId, $this->ColumnIds, true);
            # Unset the column rows
            unset($this->ColumnIds[$key]);
            # Reset the column ids to the correct values
            $this->ColumnIds = array_values($this->ColumnIds);
        }
    }

    /**
     * Set the footer row.
     *
     * @param array $footerData Footer row data.
     *
     * @return void
     */
    public function setFooterRow(array $footerData): void
    {
        if (\count($footerData) >= 1) {
            $this->Footer = $footerData;
        }
    }

    /**
     * Set disable header
     *
     * @return void
     */
    public function setDisableHeader(): void
    {
        $this->DisableHeader = true;
    }

    /**
     * Set disable footer
     *
     * @return void
     */
    public function setDisableFooter(): void
    {
        $this->DisableFooter = true;
    }

    /**
     * List of all the headers from the table.
     *
     * @param array $headerData Array of values with the names of the headers.
     *
     * @return void
     */
    public function setHeaderRow(array $headerData): void
    {
        if (empty($headerData) === false) {
            foreach ($headerData as $key => $value) {
                # Add basic field to the header.
                $this->addColumnAtTheEnd($key, $value);
            }
        }
    }

    /**
     * Add extra attributes to header cells.
     *
     * @param string $columnId The column identifier.
     * @param string $attributeName The attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    public function addHeaderAttribute($columnId, $attributeName, $value): void
    {
        $this->HeaderAttributes[$columnId][$attributeName] = $value;
    }

    /**
     * Set Hyperlink column for table row.
     *
     * @param string $columnId To store the column id for hyperlink.
     * @param string $routeName To store the route of the page.
     * @param array $keyValParams To store the parameter.
     *
     * @return void
     */
    public function setHyperlinkColumn(string $columnId, string $routeName, array $keyValParams = []): void
    {
        if (empty($routeName) === false && in_array($columnId, $this->ColumnIds, true) === true) {
            $this->HyperlinkColumns[$columnId] = [
                'route' => $routeName,
                'params' => $keyValParams,
            ];
        } else {
            Message::throwMessage('Invalid parameter to set the hyperlink column for table ' . $this->TableAttributes['id']);
        }
    }

    /**
     * Set action view by modal.
     *
     * @param \App\Frame\Gui\Modal $modal To store the path of the page.
     * @param string $callbackRoute To store the callback route.
     * @param string $callbackFunction To store the callback route.
     * @param array $params To store the parameter.
     *
     * @return void
     */
    public function setViewActionByModal(Modal $modal, string $callbackRoute, string $callbackFunction, array $params = []): void
    {
        if ($modal === null) {
            Message::throwMessage('Parameter modal can not be null.');
        }
        if (empty($callbackRoute) === true) {
            Message::throwMessage('Parameter callback route can not be null.');
        }
        if (empty($callbackFunction) === true) {
            Message::throwMessage('Parameter callback function can not be null.');
        }
        $this->ViewAction = [
            'type' => 'modal',
            'modal' => $modal,
            'route' => $callbackRoute,
            'callbackFunction' => $callbackFunction,
            'param' => $params,
        ];
    }

    /**
     * Set action view by hyperlink
     *
     * @param string $route To store the callback route.
     * @param array $params To store the parameter.
     * @param bool $isPopup To store the trigger is it a popup hyperlink or not.
     *
     * @return void
     */
    public function setViewActionByHyperlink(string $route, array $params = [], bool $isPopup = false): void
    {
        if (empty($route) === true) {
            Message::throwMessage('Parameter route can not be null.');
        }
        $this->ViewAction = [
            'type' => 'link',
            'route' => $route,
            'popup' => $isPopup,
            'param' => $params,
        ];
    }


    /**
     * Set action edit by modal.
     *
     * @param Modal $modal To store the path of the page.
     * @param string $callbackRoute To store the callback route.
     * @param string $callbackFunction To store the callback route.
     * @param array $params To store the parameter.
     *
     * @return void
     */
    public function setUpdateActionByModal(Modal $modal, string $callbackRoute, string $callbackFunction, array $params = []): void
    {
        if ($modal === null) {
            Message::throwMessage('Parameter modal can not be null.');
        }
        if (empty($callbackRoute) === true) {
            Message::throwMessage('Parameter callback route can not be null.');
        }
        if (empty($callbackFunction) === true) {
            Message::throwMessage('Parameter callback function can not be null.');
        }
        $this->EditAction = [
            'type' => 'modal',
            'modal' => $modal,
            'route' => $callbackRoute,
            'callbackFunction' => $callbackFunction,
            'param' => $params,
        ];
    }

    /**
     * Set action edit by hyperlink
     *
     * @param string $route To store the callback route.
     * @param array $params To store the parameter.
     * @param bool $isPopup To store the trigger is it a popup hyperlink or not.
     *
     * @return void
     */
    public function setUpdateActionByHyperlink(string $route, array $params = [], bool $isPopup = false): void
    {
        if (empty($route) === true) {
            Message::throwMessage('Parameter route can not be null.');
        }
        $this->EditAction = [
            'type' => 'link',
            'route' => $route,
            'popup' => $isPopup,
            'param' => $params,
        ];
    }

    /**
     * Set action copy by modal.
     *
     * @param Modal $modal To store the path of the page.
     * @param string $callbackRoute To store the callback route.
     * @param string $callbackFunction To store the callback route.
     * @param array $params To store the parameter.
     *
     * @return void
     */
    public function setCopyActionByModal(Modal $modal, string $callbackRoute, string $callbackFunction, array $params = []): void
    {
        if ($modal === null) {
            Message::throwMessage('Parameter modal can not be null.');
        }
        if (empty($callbackRoute) === true) {
            Message::throwMessage('Parameter callback route can not be null.');
        }
        if (empty($callbackFunction) === true) {
            Message::throwMessage('Parameter callback function can not be null.');
        }
        $this->CopyAction = [
            'type' => 'modal',
            'modal' => $modal,
            'route' => $callbackRoute,
            'callbackFunction' => $callbackFunction,
            'param' => $params,
        ];
    }

    /**
     * Set action edit by hyperlink
     *
     * @param string $route To store the callback route.
     * @param array $params To store the parameter.
     * @param bool $isPopup To store the trigger is it a popup hyperlink or not.
     *
     * @return void
     */
    public function setCopyActionByHyperlink(string $route, array $params = [], bool $isPopup = false): void
    {
        if (empty($route) === true) {
            Message::throwMessage('Parameter route can not be null.');
        }
        $this->CopyAction = [
            'type' => 'link',
            'route' => $route,
            'popup' => $isPopup,
            'param' => $params,
        ];
    }


    /**
     * Set action delete by modal.
     *
     * @param \App\Frame\Gui\Modal $modal To store the path of the page.
     * @param string $callbackRoute To store the callback route.
     * @param string $callbackFunction To store the callback route.
     * @param array $params To store the parameter.
     *
     * @return void
     */
    public function setDeleteActionByModal(Modal $modal, string $callbackRoute, string $callbackFunction, array $params = []): void
    {
        if ($modal === null) {
            Message::throwMessage('Parameter modal can not be null.');
        }
        if (empty($callbackRoute) === true) {
            Message::throwMessage('Parameter callback route can not be null.');
        }
        if (empty($callbackFunction) === true) {
            Message::throwMessage('Parameter callback function can not be null.');
        }
        $this->DeleteAction = [
            'type' => 'modal',
            'modal' => $modal,
            'route' => $callbackRoute,
            'callbackFunction' => $callbackFunction,
            'param' => $params,
        ];
    }

    /**
     * Set action Delete by hyperlink
     *
     * @param string $route To store the callback route.
     * @param array $params To store the parameter.
     * @param bool $isPopup To store the trigger is it a popup hyperlink or not.
     *
     * @return void
     */
    public function setDeleteActionByHyperlink(string $route, array $params = [], bool $isPopup = false): void
    {
        if (empty($route) === true) {
            Message::throwMessage('Parameter route can not be null.');
        }
        $this->DeleteAction = [
            'type' => 'link',
            'route' => $route,
            'popup' => $isPopup,
            'param' => $params,
        ];
    }

    /**
     * Return the list of all column ids.
     *
     * @return array
     */
    public function getColumnIds(): array
    {
        return $this->ColumnIds;
    }

    /**
     * Return the list of all column ids.
     *
     * @param bool $allow To store the trigger to allow empty data.
     *
     * @return void
     */
    public function setAllowEmpty(bool $allow = true): void
    {
        $this->AllowEmpty = $allow;
    }

}
