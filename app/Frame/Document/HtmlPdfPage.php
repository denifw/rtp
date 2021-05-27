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

/**
 * Class to generate PDF file from HTML.
 *
 * @package    app
 * @subpackage Frame\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class HtmlPdfPage
{
    /**
     * Property to store html header for ODD Number.
     *
     * @var string $HeaderOdd
     */
    private $HeaderOdd = '';
    /**
     * Property to store html header for Even Number.
     *
     * @var string $HeaderEven
     */
    private $HeaderEven = '';

    /**
     * Property to store the html content.
     *
     * @var string $Html
     */
    private $Content = '';

    /**
     * Property to store the html footer odd number.
     *
     * @var string $FooterOdd
     */
    private $FooterOdd = '';


    /**
     * Property to store the html footer even number.
     *
     * @var string $FooterEven
     */
    private $FooterEven = '';

    /**
     * Property to store the page orientation
     * P => Portrait.
     * L => Landscape.
     *
     * @var string $Orientation
     */
    private $Orientation = 'P';

    /**
     * Property to store the margin
     *
     * @var array $Margins
     */
    private $Margins = [];


    /**
     * Property to store the page size
     *
     * @var string $PageSize
     */
    private $PageSize = '';

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
     * HtmlPdf constructor.
     *
     * @param string $pageSize    To store the orientation page.
     * @param string $orientation To store the orientation page.
     */
    public function __construct(string $pageSize = 'A4', string $orientation = 'P')
    {
        $this->setOrientation($orientation);
        $this->setPageSize($pageSize);
    }

    /**
     * Function to add html content to pdf.
     *
     * @param string $htmlCode .
     *
     * @return void
     */
    public function addContent(string $htmlCode): void
    {
        $this->Content .= $htmlCode;
    }


    /**
     * Function to create html code.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * Function to set the page header.
     *
     * @param string $headerOdd  To store the header for odd number.
     * @param string $headerEven To store the header for even number.
     *
     * @return void
     */
    public function setHeader(string $headerOdd, string $headerEven): void
    {
        $this->HeaderOdd = $headerOdd;
        $this->HeaderEven = $headerEven;
    }

    /**
     * Function to get the page header odd.
     *
     * @return string
     */
    public function getHeaderOdd(): string
    {
        return $this->HeaderOdd;
    }

    /**
     * Function to get the page header even.
     *
     * @return string
     */
    public function getHeaderEven(): string
    {
        return $this->HeaderEven;
    }

    /**
     * Function to set the page footer.
     *
     * @param string $footerOdd  To store the footer for odd number.
     * @param string $footerEven To store the footer for even number.
     *
     * @return void
     */
    public function setFooter(string $footerOdd, string $footerEven): void
    {
        $this->FooterOdd = $footerOdd;
        $this->FooterEven = $footerEven;
    }

    /**
     * Function to get the page footer odd.
     *
     * @return string
     */
    public function getFooterOdd(): string
    {
        return $this->FooterOdd;
    }

    /**
     * Function to get the page footer even.
     *
     * @return string
     */
    public function getFooterEven(): string
    {
        return $this->FooterEven;
    }

    /**
     * Function to set the orientation page.
     *
     * @param string $orientation To store the orientation page.
     *
     * @return void
     */
    public function setOrientation(string $orientation): void
    {
        if (in_array($orientation, ['P', 'L'], true) === false) {
            Message::throwMessage('Invalid orientation value for pdf page.');
        }
        $this->Orientation = mb_strtoupper($orientation);
    }

    /**
     * Function to get the orientation page.
     *
     * @return string
     */
    public function getOrientation(): string
    {
        return $this->Orientation;
    }

    /**
     * Function to set the size page.
     *
     * @param string $size To store the size page.
     *
     * @return void
     */
    public function setPageSize(string $size): void
    {
        if (in_array($size, self::$AllowedPageSize, true) === false) {
            Message::throwMessage('Invalid page size value for pdf page.');
        }
        $this->PageSize = $size;
    }

    /**
     * Function to get the size page.
     *
     * @return string
     */
    public function getPageSize(): string
    {
        return $this->PageSize;
    }

    /**
     * Function to set margin.
     *
     * @param int $left   To store the left margin.
     * @param int $right  To store the right margin.
     * @param int $top    To store the top margin.
     * @param int $bottom To store the bottom margin.
     * @param int $header To store the header margin.
     * @param int $footer To store the footer margin.
     *
     * @return void
     */
    public function setMargin(int $left, int $right, int $top, int $bottom, int $header, int $footer): void
    {
        $this->Margins = [
            'l' => $left,
            'r' => $right,
            't' => $top,
            'b' => $bottom,
            'h' => $header,
            'f' => $footer,
        ];

    }

    /**
     * Function to get margins.
     *
     * @return array
     */
    public function getMargin(): array
    {
        return $this->Margins;
    }
}
