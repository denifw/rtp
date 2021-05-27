<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBT
 * @author    Ano SUrino <bong.anosurino@gmail.com>
 * @copyright 2020 MBT
 */

namespace App\Frame\Document;

use App\Frame\Exceptions\Message;
use Illuminate\Http\UploadedFile;

/**
 * Class to controll import excel to system
 *
 * @package    app
 * @subpackage Frame\Document
 * @author     Ano SUrino <bong.anosurino@gmail.com>
 * @copyright  2020 MBT
 */
class ParseExcel
{
    /**
     * Property to store the based path of the file.
     *
     * @var string $BasePath
     */
    private $BasePath = 'public/temp';

    /**
     * Original object from Uploaded File class.
     *
     * @var \Illuminate\Http\UploadedFile $File
     */
    private $File;

    /**
     * Property to store Filename.
     *
     * @var string $FileName The File Name
     */
    private $FileName;

    /**
     * The complete Excel object.
     *
     * @var \App\Frame\Document\Excel $Excel
     */
    private $Excel;

    /**
     * List of all the header name column.
     *
     * @var array $Header
     */
    private $Header = [];

    /**
     * Property to store excel sheet name.
     *
     * @var string $SheetName
     */
    private $SheetName;

    /**
     * Property to store Worksheet.
     *
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $WorkSheet
     */
    private $WorkSheet;

    /**
     * Property to store the result after store file.
     *
     * @var bool $IsSuccessStored
     */
    public $IsSuccessStored;

    /**
     * Basic constructor to start up the object that generates new parse excel.
     *
     * @param \Illuminate\Http\UploadedFile $file      To store the file.
     * @param string                        $fileName  To store the name of file.
     * @param string                        $sheetName To store excel sheet name.
     */
    public function __construct(UploadedFile $file, string $fileName, string $sheetName)
    {
        $this->File = $file;
        $this->FileName = $fileName;
        $this->Excel = new Excel();
        $this->SheetName = $sheetName;
        $this->storeFile();
        $this->readSheetFile();
    }

    /**
     * List of all the headers from excel.
     *
     * @param array $headerData Array of values with the names of the headers.
     *
     * @return void
     */
    public function setHeaderRow(array $headerData): void
    {
        if (empty($headerData) === false) {
            foreach ($headerData as $key => $value) {
                $this->Header[$key] = $value;
            }
        }
    }

    /**
     * Function to set excel sheet name to read.
     *
     * @param string $sheetName
     *
     * @return void
     */
    public function setSheetName(string $sheetName): void
    {
        if (empty($sheetName) === false) {
            $this->SheetName = $sheetName;
        } else {
            Message::throwMessage('Sheet name not set.', 'ERROR');
        }
    }

    /**
     * Function to get sheet header excel.
     *
     * @param string $indexRow .
     *
     * @return array
     */
    public function getSheetHeader(string $indexRow): array
    {
        return $this->Excel->readSheetHeader($this->WorkSheet, $indexRow, $this->Header);
    }

    /**
     * Function to read work sheet.
     *
     * @return void
     */
    public function readSheetFile(): void
    {
        if (empty($this->SheetName) === false) {
            $storagePath = storage_path('app/' . $this->BasePath . '/' . $this->FileName);
            $result = $this->Excel->readSheetFile($storagePath, $this->SheetName);
            if ($result !== null) {
                $this->WorkSheet = $result;
            } else {
                Message::throwMessage('Work sheet not exist', 'ERROR');
            }
        } else {
            Message::throwMessage('Sheet name is mandatory.', 'ERROR');
        }
    }

    /**
     * Function to get all cells excel.
     *
     * @param string $startingRow .
     *
     * @return array
     */
    public function getAllSheetCells(string $startingRow): array
    {
        return $this->Excel->readAllSheetCells($this->WorkSheet, $startingRow, $this->Header);
    }

    /**
     * Function to store file.
     *
     * @return void
     */
    private function storeFile(): void
    {
        if (empty($this->File) === false && empty($this->FileName) === false) {
            $success = $this->File->storeAs($this->BasePath, $this->FileName);
            if ($success === false) {
                Message::throwMessage('Failed to upload the file', 'ERROR');
            } else {
                $this->IsSuccessStored = true;
            }
        } else {
            Message::throwMessage('File and FileName are mandatory', 'ERROR');
        }
    }

}
