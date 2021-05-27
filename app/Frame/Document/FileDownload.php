<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Document;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\StringFormatter;
use App\Model\Dao\System\Document\DocumentDao;
use Illuminate\Support\Facades\File;

/**
 * Class to manage the file management system.
 *
 * @package    app
 * @subpackage Frame\Document
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class FileDownload
{
    /**
     * Property to store the based path of the file.
     *
     * @var string $Path
     */
    private $Path = 'app/public';

    /**
     * Attribute to set the notification.
     *
     * @var array $Attributes
     */
    private $Attributes = [];

    /**
     * Attribute to set the notification.
     *
     * @var array $Headers
     */
    private $Headers = [];

    /**
     * Basic constructor to start up the object that generates new excel files.
     *
     * @param int $documentId To store the id of the document.
     */
    public function __construct($documentId)
    {
        if (empty($documentId) === false && is_numeric($documentId) === true && $documentId > 0) {
            $this->Attributes = DocumentDao::loadCompleteDataByReference($documentId);
            if (empty($this->Attributes) === true) {
                Message::throwMessage('File Not found.', 'ERROR');
            }
        } else {
            Message::throwMessage('File Not found.', 'ERROR');
        }
    }

    /**
     * Set filename to be opened or created.
     *
     * @return void
     */
    public function loadFile(): void
    {
        $this->Path .= '/' . StringFormatter::replaceSpecialCharacter(strtolower($this->Attributes['ss_name_space']), '');
        $this->Path .= '/' . StringFormatter::replaceSpecialCharacter(strtolower($this->Attributes['dcg_code']), '');
        $this->Path .= '/' . StringFormatter::replaceSpecialCharacter(strtolower($this->Attributes['dct_code']), '');
        $this->Path .= '/' . $this->Attributes['doc_file_name'];

        if ($this->isFileExist($this->Path) === false) {
            Message::throwMessage('File Not found.', 'ERROR');
        }
        $this->prepareHeaders();
    }

    /**
     * Function to get the header file.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->Headers;
    }

    /**
     * Function to get the path file.
     *
     * @return string
     */
    public function getPath(): string
    {
        return storage_path($this->Path);
    }

    /**
     * Function to get the path file.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->Attributes['doc_file_name'];
    }

    /**
     * Set filename to be opened or created.
     *
     * @param string $path to store the path name.
     *
     * @return bool
     */
    private function isFileExist(string $path): bool
    {
        if (empty($path) === true) {
            return false;
        }

        return File::exists(storage_path($path));
    }

    /**
     * Function to prepare the header file.
     *
     * @return void
     */
    private function prepareHeaders(): void
    {
        $this->Headers['Content-Type'] = $this->getContentType();
//        $this->Headers['Content-Disposition'] = 'inline; filename="' . $this->Attributes['doc_file_name'] . '"';
    }

    /**
     * Set the content type of the file depending on the extension of the file.
     *
     * @return string
     */
    private function getContentType(): string
    {
        switch (mb_strtolower($this->Attributes['doc_file_type'])) {
            case 'pdf':
                $result = 'application/pdf';
                break;
            case 'exe':
                $result = 'application/octet-stream';
                break;
            case 'zip':
                $result = 'application/zip';
                break;
            case 'doc':
                $result = 'application/msword';
                break;
            case 'docx':
                $result = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'csv':
            case 'xls':
                $result = 'application/vnd.ms-excel';
                break;
            case 'xlsx':
                $result = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'ppt':
                $result = 'application/vnd.ms-powerpoint';
                break;
            case 'pptx':
                $result = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'gif':
                $result = 'image/gif';
                break;
            case 'png':
                $result = 'image/png';
                break;
            case 'jpeg':
                $result = 'image/jpeg';
                break;
            case 'jpg':
                $result = 'image/jpg';
                break;
            default:
                $result = 'application/force-download';
        }

        return $result;
    }

}
