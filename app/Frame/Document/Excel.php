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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class to manage the excel data.
 *
 * @package    app
 * @subpackage Util\Document
 * @author     Deni Firdaus Waruwu <deni@lokasi.co.id>
 * @copyright  2017 lokasi.co.id
 */
class Excel
{

    /**
     * Original object from PhpExcel class.
     *
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet $PhpExcel
     */
    protected $PhpExcel;

    /**
     * Original object from ExcelProperties class.
     *
     * @var \App\Frame\Document\ExcelProperties $Properties
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
        $this->PhpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $this->Properties = new ExcelProperties();
    }

    /**
     * Set filename to be opened or created.
     *
     * @param string $fileName The filename.
     *
     * @return void
     */
    public function setFileName($fileName): void
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
            $this->FileName = 'Lokasi-' . uniqid('', true) . '.xlsx';
        }

        return $this->FileName;
    }

    /**
     * Function to add sheet to the document.
     *
     * @param string $id    The id for sheet.
     * @param string $title The title for sheet.
     *
     * @return void
     */
    public function addSheet($id, $title): void
    {
        if (\in_array($id, $this->Sheets, true) === false) {
            $this->Sheets[] = $id;
            $countSheet = \count($this->Sheets);
            try {
                if ($countSheet === 1) {
                    $this->PhpExcel->getActiveSheet()->setTitle($title);
                } else {
                    $this->PhpExcel->createSheet();
                    $this->PhpExcel->getSheet($countSheet - 1)->setTitle($title);
                }
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'DEBUG');
            }
        } else {
            Message::throwMessage('Sheet for id ' . $id . ' already exist.', 'DEBUG');
        }
    }

    /**
     * Get the coordinate from row and column number.
     *
     * @param integer $row    The selected row number.
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
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
    }

    /**
     * Function to set the active sheet
     *
     * @param string $id The id for sheet.
     *
     * @return void
     */
    public function setActiveSheet($id): void
    {
        if (\in_array($id, $this->Sheets, true) === true) {
            try {
                $index = array_search($id, $this->Sheets, true);
                $this->PhpExcel->setActiveSheetIndex($index);
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'DEBUG');
            }
        } else {
            Message::throwMessage('Sheet not found for id ' . $id . '.', 'DEBUG');
        }
    }

    /**
     * Function to get the active sheet
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getActiveSheet(): Worksheet
    {
        try {
            return $this->PhpExcel->getActiveSheet();
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'DEBUG');
        }

        return null;
    }

    /**
     * Function to move pointer to nex column
     *
     * @param  string $sheetId      To store the sheet id.
     * @param integer $numberOfRows To store the space for the next column.
     *
     * @return void
     */
    public function doRowMovePointer($sheetId, $numberOfRows = 2): void
    {
        $sheet = $this->getSheet($sheetId, true);
        $nextRow = $sheet->getHighestRow() + $numberOfRows;
        # get Column from string
        try {
            list($column, $row) = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($sheet->getActiveCell());
            $sheet->setSelectedCell($column . $nextRow);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }


    /**
     * Function to get the sheet
     *
     * @param string $id       To set the id of the sheet.
     * @param bool   $activate To set the trigger to activate the sheet.
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getSheet($id, $activate = false): Worksheet
    {
        $result = null;
        if (\in_array($id, $this->Sheets, true) === true) {
            try {
                $index = array_search($id, $this->Sheets, true);
                if ($activate === true) {
                    $this->setActiveSheet($id);
                    $result = $this->getActiveSheet();
                } else {
                    $result = $this->PhpExcel->getSheet($index);
                }
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'DEBUG');
            }
        } else {
            Message::throwMessage('Sheet not found for id ' . $id . '.', 'DEBUG');
        }

        return $result;
    }

    /**
     * Function to set the properties of the file.
     *
     * @param \App\Frame\Document\ExcelProperties $properties To store the file properties.
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
    private function doDownload($writerType): void
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
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
    }


    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param string $fileName The writer type to create the specified document.
     * @param string $sheetName The writer type to create the specified document.
     *
     * @return null|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function readSheetFile($fileName, $sheetName): ?Worksheet
    {
        $sheet = null;
        try {
            $reader = IOFactory::createReaderForFile($fileName);
            $spreadsheet = $reader->load($fileName);
            $sheet =  $spreadsheet->getSheetByName($sheetName);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }
        return $sheet;
    }


    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet The writer type to create the specified document.
     * @param string $indexRow The writer type to create the specified document.
     * @param array $columns The writer type to create the specified document.
     *
     * @return array
     */
    public function readSheetHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $indexRow, array $columns): array
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
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
        return $results;
    }

    /**
     * Create writer from PHP Excel IO Factory.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet The writer type to create the specified document.
     * @param string $startingRow The writer type to create the specified document.
     * @param array $columns The writer type to create the specified document.
     *
     * @return array
     */
    public function readAllSheetCells(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $startingRow, array $columns): array
    {
        $results = [];
        if ($sheet !== null && empty($columns) === false) {
            try {
                $next = true;
                do {
                    $row = [];
                    $isAllEmpty = true;
                    foreach ($columns as $key => $col) {
                        $val = '';

                        $cellName = $col . $startingRow;
                        $cell = $sheet->getCell($cellName, false);
                        if($cell !== null) {
                            $val = $cell->getValue();
                        }
                        if (empty($val) === false || trim($val) !== '') {
                            $isAllEmpty = false;
                        }
                        $row[$key] = $val;
                    }
                    $row['line_number'] = $startingRow;
                    if ($isAllEmpty === false) {
                        $results[] = $row;
                        $startingRow++;
                    } else {
                        $next = false;
                    }
                } while ($next === true);
            } catch (\Exception $e) {
                Message::throwMessage($e->getMessage(), 'ERROR');
            }
        }
        return $results;
    }
}
