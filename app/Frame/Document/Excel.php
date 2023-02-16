<?php

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */


namespace App\Frame\Document;

/**
 * Class to manage the layout creation.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\StringFormatter;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */
class Excel
{

    /**
     * Original object from PhpExcel class.
     *
     * @var Spreadsheet $PhpExcel
     */
    protected $PhpExcel;

    /**
     * Original object from ExcelProperties class.
     *
     * @var ExcelProperties $Properties
     */
    protected $Properties;

    /**
     * The property to hold the filename.
     *
     * @var string $FileName
     */
    private $FileName;

    /**
     * Property to store the sheet list.
     *
     * @var array $Sheets
     */
    private $Sheets = [];

    /**
     * Basic constructor to start up the object that generates new excel files.
     *
     * Within the constructor the id from the table will be placed in the attributes
     *
     */
    public function __construct()
    {
        $this->PhpExcel = new Spreadsheet();
        $this->Properties = new ExcelProperties();
    }

    /**
     * Set filename to be opened or created.
     *
     * @param string $fileName The filename.
     *
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->FileName = $fileName;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFileName(): string
    {
        # Create a unique name for the file if not provided.
        if (empty($this->FileName) === true) {
            $this->FileName = env('APP_NAME', 'Sys') . '-' . uniqid('', true) . '.xlsx';
        }

        return $this->FileName;
    }

    /**
     * Function to add sheet to the document.
     *
     * @param string $id The id for sheet.
     * @param string $title The title for sheet.
     *
     * @return void
     */
    public function addSheet(string $id, string $title): void
    {
        if (in_array($id, $this->Sheets, true) === false) {
            $this->Sheets[] = $id;
            $countSheet = count($this->Sheets);
            try {
                if ($countSheet === 1) {
                    $this->PhpExcel->getActiveSheet()->setTitle($title);
                } else {
                    $this->PhpExcel->createSheet();
                    $this->PhpExcel->getSheet($countSheet - 1)->setTitle($title);
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        } else {
            Message::throwMessage('Sheet for id ' . $id . ' already exist.');
        }
    }

    /**
     * Get the coordinate from row and column number.
     *
     * @param integer $row The selected row number.
     * @param integer $column The selected column number.
     *
     * @return string
     */
    public function getCoordinate($row = 1, $column = 0): string
    {
        $columnLetter = $this->getStringFromColumnIndex($column);

        return $columnLetter . $row;
    }

    /**
     * Get string value of column index from number.
     *
     * @param integer $column The column number.
     *
     * @return string
     */
    public function getStringFromColumnIndex($column = 0): string
    {
        return Coordinate::stringFromColumnIndex($column);
    }

    /**
     * Function to set the active sheet
     *
     * @param string $id The id for sheet.
     *
     * @return void
     */
    public function setActiveSheet(string $id): void
    {
        if (in_array($id, $this->Sheets, true) === true) {
            try {
                $index = array_search($id, $this->Sheets, true);
                $this->PhpExcel->setActiveSheetIndex($index);
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        } else {
            Message::throwMessage('Sheet not found for id ' . $id . '.');
        }
    }

    /**
     * Function to get the active sheet
     *
     * @return ?Worksheet
     */
    public function getActiveSheet(): ?Worksheet
    {
        try {
            return $this->PhpExcel->getActiveSheet();
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }

        return null;
    }

    /**
     * Function to move pointer to nex column
     *
     * @param string $sheetId To store the sheet id.
     * @param integer $numberOfRows To store the space for the next column.
     *
     * @return void
     */
    public function doRowMovePointer(string $sheetId, $numberOfRows = 2): void
    {
        $sheet = $this->getSheet($sheetId, true);
        $nextRow = $sheet->getHighestRow() + $numberOfRows;
        # get Column from string
        try {
            list($column, $row) = Coordinate::coordinateFromString($sheet->getActiveCell());
            $sheet->setSelectedCell($column . $nextRow);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }


    /**
     * Function to get the sheet
     *
     * @param string $id To set the id of the sheet.
     * @param bool $activate To set the trigger to activate the sheet.
     *
     * @return Worksheet
     */
    public function getSheet(string $id, $activate = false): Worksheet
    {
        $result = null;
        if (in_array($id, $this->Sheets, true) === true) {
            try {
                $index = array_search($id, $this->Sheets, true);
                if ($activate === true) {
                    $this->setActiveSheet($id);
                    $result = $this->getActiveSheet();
                } else {
                    $result = $this->PhpExcel->getSheet($index);
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        } else {
            Message::throwMessage('Sheet not found for id ' . $id . '.');
        }

        return $result;
    }

    /**
     * Function to set the properties of the file.
     *
     * @param ExcelProperties $properties To store the file properties.
     *
     * @return void
     */
    public function setProperties(ExcelProperties $properties): void
    {
        if ($properties !== null) {
            $this->Properties = $properties;
        }
    }

    /**
     * Create Excel file.
     *
     * @param string $type Specify how the excel should be returned (default html).
     *
     * @return void
     */
    public function createExcel($type = 'Xlsx'): void
    {
        $this->loadFileProperties();
        $this->doDownload($type);
    }

    /**
     * Set the excel properties
     *
     * @return void
     */
    private function loadFileProperties(): void
    {
        $this->PhpExcel->getProperties()->setCreator($this->Properties->getCreator())->setLastModifiedBy($this->Properties->getModifiedBy())->setTitle($this->Properties->getTitle())->setSubject($this->Properties->getSubject())->setDescription($this->Properties->getDescription())->setKeywords($this->Properties->getKeyword())->setCategory($this->Properties->getCategory());
    }

    /**
     * Download the document.
     *
     * @param string $writerType The writer type that will be used by PhpExcel.
     *
     * @return void
     */
    private function doDownload(string $writerType): void
    {
        # Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->getFileName() . '"');
        header('Cache-Control: max-age=0');
        # If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        # If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); # Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); # always modified
        header('Cache-Control: cache, must-revalidate'); # HTTP/1.1
        header('Pragma: public'); # HTTP/1.0
        $this->doSave($writerType);
    }

    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param string $writerType The writer type to create the specified document.
     *
     * @return void
     */
    private function doSave($writerType = 'Excel2007'): void
    {
        try {
            $objWriter = IOFactory::createWriter($this->PhpExcel, $writerType);
            $objWriter->save('php://output');
            exit;
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
    }


    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param string $fileName The writer type to create the specified document.
     * @param string $sheetName The writer type to create the specified document.
     *
     * @return null|Worksheet
     */
    public function readSheetFileByName(string $fileName, string $sheetName): ?Worksheet
    {
        $sheet = null;
        try {
            $reader = IOFactory::createReaderForFile($fileName);
            $spreadsheet = $reader->load($fileName);
            $sheet = $spreadsheet->getSheetByName($sheetName);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
        return $sheet;
    }

    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param string $fileName The writer type to create the specified document.
     * @param int $sheetIndex The writer type to create the specified document.
     *
     * @return null|Worksheet
     */
    public function readSheetFileByIndex(string $fileName, int $sheetIndex): ?Worksheet
    {
        $sheet = null;
        try {
            $reader = IOFactory::createReaderForFile($fileName);
            $spreadsheet = $reader->load($fileName);
            $sheet = $spreadsheet->getSheet($sheetIndex);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
        return $sheet;
    }


    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param Worksheet $sheet The writer type to create the specified document.
     * @param string $indexRow The writer type to create the specified document.
     * @param array $columns The writer type to create the specified document.
     *
     * @return array
     */
    public function readSheetHeader(Worksheet $sheet, string $indexRow, array $columns): array
    {
        $results = [];
        if ($sheet !== null && empty($columns) === false) {
            try {
                foreach ($columns as $col) {
                    $val = '';
                    $cellName = $col . $indexRow;
                    $cell = $sheet->getCell($cellName, false);
                    if ($cell !== null) {
                        $val = $cell->getValue();
                    }
                    $results[] = $val;
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
        return $results;
    }

    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param Worksheet $sheet The writer type to create the specified document.
     * @param int $indexRow The writer type to create the specified document.
     * @param string $indexColumn The writer type to create the specified document.
     *
     * @return array
     */
    public function readAllHeader(Worksheet $sheet, int $indexRow, string $indexColumn): array
    {
        $results = [];
        if ($sheet !== null) {
            try {
                $lastColumn = $sheet->getHighestColumn($indexRow);
                $lastColumn++;
                for ($column = $indexColumn; $column !== $lastColumn; $column++) {
                    $val = '';
                    $cellName = $column . $indexRow;
                    $cell = $sheet->getCell($cellName, false);
                    if ($cell !== null) {
                        $val = $cell->getValue();
                        $val = mb_strtolower(StringFormatter::replaceSpecialCharacter($val));
                    }
                    $results[$column] = $val;
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
        return $results;
    }

    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param Worksheet $sheet The writer type to create the specified document.
     * @param int $startingRow The writer type to create the specified document.
     * @param array $columns The writer type to create the specified document.
     *
     * @return array
     */
    public function readAllSheetCells(Worksheet $sheet, int $startingRow, array $columns): array
    {
        $results = [];
        if ($sheet !== null && empty($columns) === false) {
            try {
                $next = true;
                do {
                    $row = [];
                    $isAllEmpty = true;
                    foreach ($columns as $col => $header) {
                        $val = '';

                        $cellName = $col . $startingRow;
                        $cell = $sheet->getCell($cellName, false);
                        if ($cell !== null) {
                            $val = $cell->getValue();
                        }
                        if (empty($val) === false || trim($val) !== '') {
                            $isAllEmpty = false;
                        }
                        $row[$header] = $val;
                    }
                    $row['line_number'] = $startingRow;
                    if ($isAllEmpty === false) {
                        $results[] = $row;
                        $startingRow++;
                    } else {
                        $next = false;
                    }
                } while ($next === true);
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
        return $results;
    }
}
