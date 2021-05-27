<?php
/**
 * Contains code written by the TIG Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Lokasi
 * @author    Deni Firdaus Waruwu <deni@lokasi.co.id>
 * @copyright 2017 lokasi.co.id
 */

namespace App\Frame\Document;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Table;
use App\Frame\System\Session\UserSession;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class to manage the table export to excel.
 *
 * @package    app
 * @subpackage Util\Document
 * @author     Deni Firdaus Waruwu <deni@lokasi.co.id>
 * @copyright  2017 lokasi.co.id
 */
class ExcelTable
{
    /**
     * The column type from the listing table.
     *
     * @var array $ColumnType
     */
    protected $ColumnType = [];

    /**
     * The current active sheet on witch the table will be placed.
     *
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ActiveSheet
     */
    private $ActiveSheet;

    /**
     * The complete Excel object to add the table to.
     *
     * @var \App\Frame\Document\Excel $Excel
     */
    private $Excel;

    /**
     * Set the footer array.
     *
     * @var array[] $Footer
     */
    private $Footer = [];

    /**
     * The grid from the excel file.
     *
     * @var array $Body
     */
    private $Body = [];

    /**
     * Set the header array.
     *
     * @var array[] $Header
     */
    private $Header = [];

    /**
     * The starting Position of the table to be added.
     *
     * @var string $StartingPoint
     */
    private $StartingPoint = '';

    /**
     * The current position of the active cell.
     *
     * @var string $CurrentPosition
     */
    private $CurrentPosition = '';

    /**
     * List of every unique Header Key name.
     *
     * @var array $HeaderKeys
     */
    private $HeaderKeys = [];

    /**
     * Does the table contains a first column with row Header information.
     *
     * @var boolean $HasRowHeader
     */
    private $HasRowHeader = false;

    /**
     * Does the table contains a last column with row calculations or row footer.
     *
     * @var boolean $HasRowFooter
     */
    private $HasRowFooter = false;

    /**
     * List of footer cells that can have a sort of calculation.
     *
     * @var array $FooterType
     */
    private $FooterType = [];

    /**
     * List of all available footer types the footer cell can have.
     *
     * @var array $FooterTypeList List of available types of the footer.
     */
    private $FooterTypeList = ['SUM', 'AVG'];

    /**
     * The constructor of the object.
     *
     * @param \App\Frame\Document\Excel                     $excel       The Excel.
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $activeSheet The active worksheet if provided to place the
     *                                                                   table on.
     */
    public function __construct(Excel $excel, Worksheet $activeSheet = null)
    {
        # Link the object with dependency injection.
        $this->Excel = $excel;
        # Set the Active worksheet.
        if ($activeSheet === null) {
            $activeSheet = $this->Excel->getActiveSheet();
        }
        $this->ActiveSheet = $activeSheet;
    }

    /**
     * Set the complete body of the excel table..
     *
     * @param array $body The complete grid.
     *
     * @return void
     */
    public function addGrid(array $body): void
    {
        foreach ($body as $row) {
            $this->addRow($row);
        }
    }

    /**
     * Function to set the table from html table.
     *
     * @param \App\Frame\Gui\Table $tableObject To store table object from html table.
     *
     * @return void
     */
    public function setTable(Table $tableObject): void
    {
        $this->setHeader($tableObject->getHeaderRow());
        $this->setRows($tableObject->getRows());
        $this->setColumnType($tableObject->getColumnType());
        $this->setFooterType($tableObject->getFooterType());
    }

    /**
     * Function to set the row data.
     *
     * @param array $rows To store the data for the excel content.
     *
     * @return void
     */
    public function setRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }


    /**
     * Add one row to the excel file.
     *
     * @param array $row Complete array for one row to add.
     *
     * @return void
     */
    public function addRow(array $row): void
    {
        $temp = [];
        foreach ($row as $key => $value) {
            if (array_key_exists($key, $this->Header) === true) {
                $val = $this->doConvertBrToNl($value);
                $val = preg_replace('/<[^>]*>/', '', $val);
                $temp[$key] = $val;
            }
        }
        $this->Body[] = $temp;

    }

    /**
     * Convert BR tags to nl.
     *
     * @param string $str The string to convert.
     *
     * @return string The converted values of the array.
     */
    public function doConvertBrToNl($str): string
    {
        return html_entity_decode(preg_replace('/\<br(\s*)?\/?\>/i', " \r\n", $str), ENT_QUOTES, 'UTF-8');;
    }

    /**
     * Enables the auto filter by default settings to the table that is provided.
     *
     * @return void
     */
    public function setAutoFilter(): void
    {
    }

    /**
     * Move the cell to Next row.
     *
     * @param integer $numberOfRows Number of rows to move down.
     *
     * @return void
     */
    public function doRowMovePointer(int $numberOfRows): void
    {
        # Move the pointer below the table.
        $nextRow = $this->ActiveSheet->getHighestRow() + $numberOfRows;
        # get Column from string
        try {
            [$column, $row] = Coordinate::coordinateFromString($this->getStartPosition());
            $this->ActiveSheet->setSelectedCell($column . $nextRow);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'DEBUG');
        }
    }

    /**
     * Set break on a cell.
     *
     * @param integer $row       The row number from the cell.
     * @param integer $column    The column from the cell.
     * @param integer $breakType Break type (type of PHPExcel_Worksheet::BREAK_*).
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function setBreak(int $row, int $column, int $breakType = Worksheet::BREAK_NONE): ?Worksheet
    {
        try {
            return $this->Excel->getActiveSheet()->setBreak($this->Excel->getCoordinate($row, $column), $breakType);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }

        return null;
    }

    /**
     * Set column types of the table.
     *
     * @param array $columnType Complete array for column type.
     *
     * @return void
     */
    public function setColumnType(array $columnType): void
    {
        # Set or the first row on the excel document will be a header.
        $this->ColumnType = $columnType;
    }

    /**
     * Set footer columns.
     *
     * @param array $columns Complete array for column names.
     *
     * @return void
     */
    public function setFooter(array $columns): void
    {
        # Set or the first row on the excel document will be a header.
        $this->Footer = $columns;
    }

    /**
     * The type of the table.
     *
     * @param string $columnId The column id.
     * @param string $type     The column type.
     *
     * @return void
     */
    public function addFooterType($columnId, $type): void
    {
        if (array_key_exists($columnId, $this->FooterType) === false) {
            # Change the property
            $this->FooterType[$columnId] = $type;
        } else {
            # Throw an PdfException in case the column has already an type
            Message::throwMessage('Footer Type already set.', 'DEBUG');
        }
    }

    /**
     * Process multiple footer types directly.
     *
     * @param array $footerIdType List of all the footer types.
     *
     * @return void
     */
    public function setFooterType(array $footerIdType): void
    {
        foreach ($footerIdType as $footerId => $type) {
            $this->addFooterType($footerId, $type);
        }
    }

    /**
     * Set header columns.
     *
     * @param array $columns Complete array for column names.
     *
     * @return void
     */
    public function setHeader(array $columns): void
    {
        $this->Header = $columns;
    }

    /**
     * Return the header row, if one is defined.
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->Header;
    }

    /**
     * Return the complete body of the excel table.
     *
     * @return array
     */
    public function getBody(): array
    {
        return $this->Body;
    }

    /**
     * Set the starting position of the table
     *
     * @param string $coordinateColumnRow The starting cell of the table ex: A1.
     *
     * @return void
     */
    public function setStartingPosition(string $coordinateColumnRow = 'A1'): void
    {
        $this->StartingPoint = $coordinateColumnRow;
        $this->ActiveSheet->setSelectedCell($coordinateColumnRow);
    }

    /**
     * Write the complete table.
     *
     * @return void
     */
    public function writeTable(): void
    {
        # Add header
        $this->createHeader();
        # Add body
        $this->createBody();
        # Add footer
        $this->createFooter();
        # Move the pointer no the beginning of the next row.
        $this->doRowMovePointer(2);
    }

    /**
     * Returns the active Sheet of the table.
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getActiveSheet(): Worksheet
    {
        return $this->ActiveSheet;
    }

    /**
     * Will return the current coordinate from where the start position is of the table.
     *
     * @return string Return position of current active cell ex: B2
     */
    public function getStartPosition(): string
    {
        if ($this->StartingPoint === '') {
            $this->StartingPoint = $this->ActiveSheet->getActiveCell();
        }

        return $this->StartingPoint;
    }

    /**
     * The number of rows the grid counts.
     *
     * @return integer
     */
    public function getNumberOfRows(): int
    {
        $totalAmountOfColumns = \count($this->Body);
        if ($this->hasColumnHeader() === true) {
            ++$totalAmountOfColumns;
        }
        if ($this->hasColumnFooter() === true) {
            ++$totalAmountOfColumns;
        }

        return $totalAmountOfColumns;
    }

    /**
     * Enables the row header when provided true.
     *
     * @param boolean $enabled When true, the first column of the table is considered a header column.
     *
     * @return void
     */
    public function hasRowHeader(bool $enabled): void
    {
        $this->HasRowHeader = $enabled;
    }

    /**
     * Returns true when first column of the table is row header.
     *
     * @return boolean
     */
    public function isRowHeaderEnabled(): bool
    {
        return $this->HasRowHeader;
    }

    /**
     * Enables the row footer, this means the last column of the table contains special data.
     *
     * @param boolean $enable When true , the last column of the table contains special data.
     *
     * @return void
     */
    public function hasRowFooter(bool $enable): void
    {
        $this->HasRowFooter = $enable;
    }

    /**
     * Returns true when the last column of the table contains special calculations or row footer data.
     *
     * @return boolean
     */
    public function isRowFooterEnabled(): bool
    {
        return $this->HasRowFooter;
    }

    /**
     * The number of data columns, this excludes the row header and row footer if they exists.
     *
     * @return integer
     */
    public function getNumberOfDataColumns(): int
    {
        $totalAmountOfColumns = \count($this->Header[0]);
        if ($this->isRowHeaderEnabled() === true) {
            $totalAmountOfColumns--;
        }
        if ($this->isRowFooterEnabled() === true) {
            $totalAmountOfColumns--;
        }

        return $totalAmountOfColumns;
    }

    /**
     * The number of rows that the grid has.this excludes the header and footer of the table.
     *
     * @return integer
     */
    public function getNumberOfDataRows(): int
    {
        return \count($this->Body);
    }

    /**
     * The total number of columns of the table, including the row header and row footer.
     *
     * @return integer
     */
    public function getNumberOfColumns(): int
    {
        return \count($this->HeaderKeys);
    }

    /**
     * Create the body of the table.
     *
     * @return void
     */
    private function createBody(): void
    {
        $user = new UserSession();
        $numberDecimal = 2;
        if ($user->isSet()) {
            $numberDecimal = $user->Settings->getDecimalNumber();
        }
        $decimalFormat = '#,##0.' . str_repeat('0', $numberDecimal);

        try {
            $startCell = $this->getCurrentPosition();
            [$startColumn, $startRow] = Coordinate::coordinateFromString($startCell);
            # Loop through Header row information
            foreach ($this->Body as $rowData) {
                $currentColumn = $startColumn;
                foreach ($this->HeaderKeys as $columnName) {
                    $cellValue = '';
                    if (array_key_exists($columnName, $rowData) === true) {
                        $cellValue = $rowData[$columnName];
                    }
                    # Set cell styles and settings.
                    if (array_key_exists($columnName, $this->ColumnType) === true && empty($cellValue) === false) {
                        if (\in_array($this->ColumnType[$columnName], [
                                'float',
                                'integer',
                                'exchange',
                                'currency',
                            ], true) === true) {
                            if ($this->ColumnType[$columnName] === 'integer') {
                                $this->ActiveSheet->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('0');
                            } else {
                                $cellValue = round($cellValue, $numberDecimal);
                                $this->ActiveSheet->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode($decimalFormat);
                            }
                        } elseif ($this->ColumnType[$columnName] === 'yesno') {
                            $cellValue = str_replace('&nbsp;', '', $cellValue);
                        }
                    }
                    # Set cell value
                    if ($this->ActiveSheet->getCell($currentColumn . $startRow) !== null) {
                        $this->ActiveSheet->getCell($currentColumn . $startRow)->setValue($cellValue);
                    }
                    # Go to next column to process.
                    ++$currentColumn;
                }
                # Go to next row if their is any.
                ++$startRow;
            }
            $this->setCurrentPosition($startColumn . $startRow);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
    }

    /**
     * Create the footer of the table.
     *
     * @return void
     */
    private function createFooter(): void
    {
        $user = new UserSession();
        $numberDecimal = 2;
        if ($user->isSet()) {
            $numberDecimal = $user->Settings->getDecimalNumber();
        }
        $decimalFormat = '#,##0.' . str_repeat('0', $numberDecimal);
        if (empty($this->FooterType) === false) {
            $this->doProcessFooterType();
            try {
                $startCell = $this->getCurrentPosition();
                [$startColumn, $startRow] = Coordinate::coordinateFromString($startCell);
                # Loop through Header row information
                $currentColumn = $startColumn;
                foreach ($this->HeaderKeys as $columnName) {
                    if (array_key_exists($columnName, $this->Footer) === true) {
                        $cellValue = $this->Footer[$columnName];
                        # Set cell type of the footer
                        if (array_key_exists($columnName, $this->ColumnType) === true) {
                            if (\in_array($this->ColumnType[$columnName], [
                                    'float',
                                    'integer',
                                    'exchange',
                                    'currency',
                                ], true) === true) {
                                if ($this->ColumnType[$columnName] === 'integer') {
                                    $this->ActiveSheet->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('0');
                                } else {
                                    $cellValue = round((float)$cellValue, $numberDecimal);
                                    $this->ActiveSheet->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode($decimalFormat);
                                }
                            } elseif ($this->ColumnType[$columnName] === 'yesno') {
                                $cellValue = str_replace('&nbsp;', '', $cellValue);
                            }
                        }

                        # Set cell value
                        if ($this->ActiveSheet->getCell($currentColumn . $startRow) !== null) {
                            $this->ActiveSheet->getCell($currentColumn . $startRow)->setValue($cellValue);
                        }
                        $this->ActiveSheet->getStyle($currentColumn . $startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('c8d6fb');
                        $this->ActiveSheet->getStyle($currentColumn . $startRow)->getFont()->setBold(true);
                    }
                    ++$currentColumn;
                }
                ++$startRow;
                $this->setCurrentPosition($startColumn . $startRow);
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
    }

    /**
     * Create the header of the table.
     *
     * @return void
     */
    private function createHeader(): void
    {
        try {
            $startCell = $this->getStartPosition();
            # Get the column and row position by index number.
            [$startColumn, $startRow] = Coordinate::coordinateFromString($startCell);
            # Loop through Header row information
            # Reset back to the first column when we start a new line.
            $currentColumn = $startColumn;
            foreach ($this->Header as $columnId => $cellValue) {
                $this->HeaderKeys[] = $columnId;
                # Set cell value
                if ($this->ActiveSheet->getCell($currentColumn . $startRow) !== null) {
                    $this->ActiveSheet->getCell($currentColumn . $startRow)->setValue($cellValue);
                }
                # Set the style of the header.
                $this->ActiveSheet->getStyle($currentColumn . $startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('c8d6fb');
                $this->ActiveSheet->getStyle($currentColumn . $startRow)->getFont()->setBold(true);
                $this->ActiveSheet->getColumnDimension($currentColumn)->setAutoSize(true);
                # Next column to process.
                ++$currentColumn;
            }
            # Move to the next row.
            ++$startRow;
            $this->setCurrentPosition($startColumn . $startRow);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
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
                if ((array_key_exists($columnId, $row) === true) && empty($row[$columnId]) === false && is_numeric($row[$columnId])) {
                    $value += $row[$columnId];
                    $numberValues++;
                }
            }
            $result = '';
            if ($footerType === 'SUM') {
                if (empty($value) === false) {
                    $result = $value;
                }
            } elseif ($footerType === 'AVG') {
                if (empty($value) === false) {
                    $result = $value / $numberValues;
                }
            }
            $this->Footer[$columnId] = $result;
        }
    }

    /**
     * Set the current pointer position of the selected cell.
     *
     * @param string $string Current position of current active cell ex: B2.
     *
     * @return void
     */
    private function setCurrentPosition($string): void
    {
        $this->CurrentPosition = $string;
    }

    /**
     * Set the current pointer position of the selected cell.
     *
     * @return string Current position of current active cell ex: B2
     */
    private function getCurrentPosition(): string
    {
        if ($this->CurrentPosition === '') {
            $this->CurrentPosition = $this->getStartPosition();
        }

        return $this->CurrentPosition;
    }

    /**
     * Returns boolean when the table has a footer enabled.
     *
     * @return boolean
     */
    private function hasColumnFooter(): bool
    {
        return $this->Footer !== [];
    }

    /**
     * Returns boolean when the table has a header enabled.
     *
     * @return boolean
     */
    private function hasColumnHeader(): bool
    {
        return $this->Header !== [];
    }
}
