<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Frame\Document;

use App\Frame\Exceptions\Message;
use Mpdf\Mpdf;

/**
 * Class to generate PDF file from HTML.
 *
 * @package    app
 * @subpackage Frame\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class HtmlPdf
{
    /**
     * Property to store the object of MPdf.
     *
     * @var \Mpdf\Mpdf $MPdf
     */
    protected $MPdf;
    /**
     * Property to store the html content.
     *
     * @var array $OutputType
     */
    private static $OutputType = ['D', 'I'];

    /**
     * Property to store the name space of the model.
     *
     * @var array $Parameters
     */
    private $Parameters = [];

    /**
     * Property to store the page id.
     *
     * @var array $PageIds
     */
    private $PageIds = [];

    /**
     * Property to store the pages.
     *
     * @var array $Pages
     */
    private $Pages = [];

    /**
     * Property to store the pages.
     *
     * @var string $FileName
     */
    private $FileName;

    /**
     * Property to store the allowed page size
     *
     * @var array $AllowedPageSize
     */
    private static $AllowedPageSize = [
        'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10',
        'B0', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10',
        'C0', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10',
        '4A0', '2A0',
        'RA0', 'RA1', 'RA2', 'RA3', 'RA4',
        'SRA0', 'SRA1', 'SRA2', 'SRA3', 'SRA4',
        'Letter', 'Legal', 'Executive', 'Folio', 'Demy', 'Royal',
    ];

    /**
     * Property to store the allowed barcode type
     *
     * @var array $AllowedBarcodeType
     */
    private static $AllowedBarcodeType = [
        'EAN13', 'ISBN', 'ISSN', 'UPCA', 'UPCE', 'EAN8',
        'EAN13P5', 'ISBNP5', 'ISSNP5', 'UPCAP5', 'UPCEP5', 'EAN8P5',
        'IMB', 'RM4SCC', 'KIX', 'POSTNET', 'PLANET',
        'C128A', 'C128B', 'C128C',
        'EAN128A', 'EAN128B', 'EAN128C',
        'C39', 'C39+', 'C39E', 'C39E+',
        'S25', 'S25+', 'I25', 'I25+', 'I25B', 'I25B+',
        'C93', 'MSI', 'MSI+', 'CODABAR', 'CODE11',
    ];

    /**
     * HtmlPdf constructor.
     *
     * @param string $fileName To store the name of the pdf file.
     * @param string $pageSize To store the page size of the pdf.
     */
    public function __construct(string $fileName, string $pageSize = '')
    {
        if (empty($pageSize) === false && in_array($pageSize, self::$AllowedPageSize, true) === false) {
            Message::throwMessage('Invalid page size value for pdf page.');
        }
        $this->FileName = $fileName;
        try {
            $config = [
                'tempDir' => 'storage/app/temp/mpdf',
            ];
            $config['format'] = $pageSize;
            $this->MPdf = new Mpdf($config);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to add page to pdf.
     *
     * @param string                          $pageId To store the page id.
     * @param \App\Frame\Document\HtmlPdfPage $page   To store the page.
     *
     * @return void
     */
    public function addPage(string $pageId, HtmlPdfPage $page): void
    {
        if (empty($pageId) === false) {
            if (in_array($pageId, $this->PageIds, true) === true) {
                Message::throwMessage('Duplicate page for id ' . $pageId);
            }
            $this->PageIds[] = $pageId;
            $this->Pages[$pageId] = $page;
        }
    }


    /**
     * Function to add html content to pdf.
     *
     * @param string $outputType To set the output type.
     *                           D -> Download
     *                           I -> Inline
     *
     * @return void
     */
    public function createPdf(string $outputType = 'I'): void
    {
        $output = 'I';
        if (empty($outputType) === false) {
            $output = mb_strtoupper($outputType);
            if (in_array($output, self::$OutputType, true) === false) {
                Message::throwMessage('Invalid output type for HTML PDF.');
            }
        }
        try {
            $this->MPdf->Output($this->FileName, $output);
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to create html code.
     *
     * @return string
     */
    public function createHtml(): string
    {
        return '';
    }

    /**
     * @param string $code The code for generate barcode
     * @param int    $size The barcode's size
     * @param string $type The barcode type
     *
     * @return string
     */
    public function writeBarcode(string $code, int $size = 1, string $type = 'C39'): string
    {
        if (empty($code) === true) {
            Message::throwMessage('Code is require.');
        } else if (in_array($type, self::$AllowedBarcodeType, true) === false) {
            Message::throwMessage('Invalide barcode type');
        }

        return '<barcode code="' . $code . '" type="' . $type . '"  size="' . $size . '"  style="padding: 1.5mm;margin: 0;vertical-align: top;color: #000;"/>';
    }

    /**
     * Function to set post value from the request.
     *
     * @param array $parameters To store the list input from request.
     *
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        if (empty($parameters) === false) {
            $this->Parameters = array_merge($this->Parameters, $parameters);
        }
    }

    /**
     * Function to set parameter value by key.
     *
     * @param string $key   To store the key of the value
     * @param string $value To store the value
     *
     * @return void
     */
    public function setParameter($key, $value): void
    {
        if (empty($key) === false) {
            $this->Parameters[$key] = $value;
        }
    }

    /**
     * Function to set file name
     *
     * @param string $fileName To store the file name
     *
     * @return void
     */
    public function setFileName($fileName): void
    {
        if (empty($fileName) === false) {
            $this->FileName = $fileName . '.pdf';
        }
    }


    /**
     * Function to get array parameter
     *
     * @param string $key To store the key of the value
     *
     * @return array
     */
    public function getArrayParameter($key): array
    {
        $result = [];
        if (array_key_exists($key, $this->Parameters) === true && \is_array($this->Parameters[$key]) === true) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get float parameter value.
     *
     * @param string $key     To store the key of the value
     * @param float  $default To store the default value if the parameter is empty
     *
     * @return null|float
     */
    public function getFloatParameter($key, $default = null): ?float
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (float)$this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get parameter value.
     *
     * @param string  $key     To store the key of the value
     * @param integer $default To store the default value if the parameter is empty
     *
     * @return null|integer
     */
    public function getIntParameter($key, $default = null): ?int
    {

        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (int)$this->Parameters[$key];
        }

        return $result;
    }


    /**
     * Function to get string parameter value.
     *
     * @param string $key     To store the key of the value
     * @param string $default To store the default value if the parameter is empty
     *
     * @return string
     */
    public function getStringParameter($key, $default = null): ?string
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to check is the parameter has value or not.
     *
     * @param string $key To store the key of the value
     *
     * @return bool
     */
    public function isValidParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
            $result = true;
        }

        return $result;
    }


    /**
     * Function to check is the parameter has value or not.
     *
     * @param string $key To store the key of the value
     *
     * @return bool
     */
    public function isExistParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to get all parameter.
     *
     * @return array
     */
    public function getAllParameters(): array
    {
        return $this->Parameters;
    }

}
