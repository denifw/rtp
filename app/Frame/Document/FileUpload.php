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
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic;

/**
 * Class to manage the file management system.
 *
 * @package    app
 * @subpackage Frame\Document
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class FileUpload
{
    /**
     * Property to store the based path of the file.
     *
     * @var string $BasePath
     */
    private static $BasePath = 'app';

    /**
     * Original object from Uploaded File class.
     *
     * @var UploadedFile $File
     */
    protected $File;

    /**
     * Attribute to set the notification.
     *
     * @var array $Attributes
     */
    private $Attributes = [];


    /**
     * Basic constructor to start up the object that generates new excel files.
     *
     * @param string $documentId To store the id of the document.
     */
    public function __construct(string $documentId)
    {
        if (empty($documentId) === false) {
            $this->Attributes = DocumentDao::getByReference($documentId);
            if (empty($this->Attributes) === true) {
                Message::throwMessage('Invalid document ID for uploading file.');
            }
        } else {
            Message::throwMessage('Invalid document ID for uploading file.');
        }
    }

    /**
     * Set filename to be opened or created.
     *
     * @param UploadedFile $file The filename.
     *
     * @return void
     */
    public function upload(UploadedFile $file): void
    {
        if ($file === null) {
            Message::throwMessage('Invalid file object in file upload class.');
        } else {
            $this->File = $file;
            $path = 'public/';
            $path .= StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['ss_name_space']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dcg_code']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dct_code']));
            $success = $file->storeAs($path, $this->Attributes['doc_file_name']);
            if ($success === false) {
                Message::throwMessage('Failed to upload the file', 'ERROR');
            }
        }
    }

    /**
     * Set filename to be opened or created.
     *
     * @param string $strFile The filename.
     *
     * @return string
     */
    public function uploadBinaryFile(string $strFile): string
    {
        $path = '';
        if (empty($strFile) === true) {
            Message::throwMessage('Invalid file object in file upload class.');
        }
        try {
            $path = self::$BasePath . '/public/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['ss_name_space']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dcg_code']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dct_code']));
            $this->createDirectory($path);
            $fileName = storage_path($path) . '/' . $this->Attributes['doc_file_name'];
            if (file_put_contents($fileName, base64_decode($strFile, true)) === false) {
                Message::throwMessage('Failed to store file.', 'ERROR');
            }

        } catch (Exception $e) {
            Message::throwMessage($e->getMessage(), 'ERROR');
        }

        return $path;

    }

    /**
     * Upload thumbnail image.
     *
     * @param ?int $width The width of image.
     * @param ?int $height The width of image.
     *
     * @return void
     */
    public function uploadThumbnail(int $width = null, int $height = null): void
    {
        if (in_array($this->File->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'], true) === true) {
            $basePath = 'app/public/';
            $path = StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['ss_name_space']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dcg_code']));
            $path .= '/' . StringFormatter::replaceSpecialCharacter(mb_strtolower($this->Attributes['dct_code']));
            $path .= '/thumbs/';
            $this->createDirectory($basePath . $path);
            $img = ImageManagerStatic::make($this->File)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save('storage/' . $path . $this->Attributes['doc_file_name'], 100);
        }
    }


    /**
     * Set filename to be opened or created.
     *
     * @param string $path to store the path name.
     *
     * @return bool
     */
    private function isDirectoryExist(string $path): bool
    {
        if (empty($path) === true) {
            return false;
        }

        return File::exists(storage_path($path));
    }

    /**
     * Set filename to be opened or created.
     *
     * @param string $path to store the path name.
     *
     * @return void
     */
    private function createDirectory(string $path): void
    {
        try {
            if (empty($path) === false && $this->isDirectoryExist($path) === false) {
                File::makeDirectory(storage_path($path), 0755, true);
            }
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage() . ' - ' . $path);
        }
    }
}
