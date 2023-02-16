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
use Illuminate\Support\Facades\Storage;

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
     * @var UploadedFile $File
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
     * @var Excel $Excel
     */
    private $Excel;

    /**
     * List of all the header name column.
     *
     * @var array $Header
     */
    private $Header = [];

    /**
     * List of all the header name column.
     *
     * @var array $Data
     */
    private $Data = [];

    /**
     * Property to store the result after store file.
     *
     * @var bool $IsSuccessStored
     */
    public $IsSuccessStored;

    /**
     * Property to store the upload template data.
     *
     * @var array $UptData
     */
    public $UptData = [];
    /**
     * Property to store the upload template data.
     *
     * @var array $MappingColumns
     */
    public $MappingColumns = [];
    /**
     * Function to parse data.
     * @param string $sheetName To store the index of the worksheet.
     * @param int $startRowHeader To store the index of the worksheet.
     * @param string $startColumnHeader To store the index of the worksheet.
     * @return void
     */
    public function doParseData(string $sheetName, int $startRowHeader = 1, string $startColumnHeader = 'A'): void
    {
        if ($this->IsSuccessStored === true) {
            $storagePath = storage_path('app/' . $this->BasePath . '/' . $this->FileName);
            $workSheet = $this->Excel->readSheetFileByName($storagePath, $sheetName);
            if ($workSheet !== null) {
                $this->Header = $this->Excel->readAllHeader($workSheet, $startRowHeader, $startColumnHeader);
                $startRowData = $startRowHeader + 1;
                $this->Data = $this->Excel->readAllSheetCells($workSheet, $startRowData, $this->Header);
                $this->deleteFile();
            } else {
                $this->deleteFile();
                Message::throwMessage('No data found for sheet name ' . $this->UptData['upt_sheet_name'] . '.');
            }
        }
    }


    /**
     * Basic constructor to start up the object that generates new parse excel.
     *
     * @param UploadedFile $file To store the file.
     */
    public function __construct(UploadedFile $file)
    {
        $this->File = $file;
        $this->FileName = 'upload_' . microtime() . '.' . $file->getClientOriginalExtension();
        $this->Excel = new Excel();
        $this->storeFile();
    }

    /**
     * Function to get sheet header excel.
     *
     * @return array
     */
    public function getSheetHeader(): array
    {
        return $this->Header;
    }

    /**
     * Function to get sheet header excel.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->Data;
    }

//    /**
//     * Function to read work sheet.
//     *
//     * @return void
//     */
//    public function readSheetFile(): void
//    {
//        if (empty($this->SheetName) === false) {
//            $storagePath = storage_path('app / ' . $this->BasePath . ' / ' . $this->FileName);
//            $result = $this->Excel->readSheetFile($storagePath, $this->SheetName);
//            if ($result !== null) {
//                $this->WorkSheet = $result;
//            } else {
//                Message::throwMessage('Work sheet not exist', 'ERROR');
//            }
//        } else {
//            Message::throwMessage('Sheet name is mandatory . ', 'ERROR');
//        }
//    }
//
//    /**
//     * Function to get all cells excel.
//     *
//     * @param string $startingRow .
//     *
//     * @return array
//     */
//    public function getAllSheetCells(string $startingRow): array
//    {
//        return $this->Excel->readAllSheetCells($this->WorkSheet, $startingRow, $this->Header);
//    }

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

    /**
     * Function to store file.
     *
     * @return void
     */
    private function deleteFile(): void
    {
        $storagePath = 'temp/' . $this->FileName;
        Storage::disk('public')->delete($storagePath);
    }

}
