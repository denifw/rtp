<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf;

use App\Frame\Document\HtmlPdf;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\System\Document\DocumentDao;
use Exception;

/**
 *
 *
 * @package    app
 * @subpackage Model\Document\Pdf
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
abstract class AbstractBasePdf extends HtmlPdf
{

    /**
     * Property to store the user data.
     *
     * @var UserSession $User
     */
    protected $User;

    /**
     * AbstractBasePdf constructor.
     *
     * @param string $fileName To store the name of the pdf file.
     * @param string $pageSize To store the page size of the pdf.
     *
     */
    public function __construct(string $fileName, string $pageSize = 'A4')
    {
        parent::__construct($fileName, $pageSize);
        $this->loadStyleSheet($pageSize);
        $this->User = new UserSession();
    }


    /**
     * Function to set the content to pdf.
     *
     * @return void
     */

    abstract public function loadContent(): void;


    /**
     * Function to load the html content.
     *
     * @return string
     */

    abstract public function loadHtmlContent(): string;

    /**
     * Function to get the system setting header.
     *
     * @param int $relId To store the title document.
     * @param int $ofId  To store the title document.
     *
     * @return string
     */

    protected function getDefaultHeader($relId, $ofId = 0): string
    {
        $data = RelationDao::loadDataForDocumentHeader($relId, $ofId);
        $path = asset('images/image-not-found.jpg');
        $docDao = new DocumentDao();
        if (empty($data['doc_id']) === false) {
            $path = $docDao->getDocumentPath($data);
        }
        $address = $data['of_address'];
        if (empty($data['dtc_name']) === false) {
            $address .= ', ' . $data['dtc_name'];
            $address .= ', ' . $data['cty_name'];
            $address .= ', ' . $data['stt_name'];
            $address .= ', ' . $data['cnt_name'];
        }
        if (empty($data['of_postal_code']) === false) {
            $address .= ', ' . $data['of_postal_code'];
        }
        $contact = [];
        if (empty($data['rel_phone']) === false) {
            $contact[] = Trans::getWord('telp') . ':' . $data['rel_phone'];
        }
        if (empty($data['rel_email']) === false) {
            $contact[] = Trans::getWord('email') . ':' . $data['rel_email'];
        }
        if (empty($data['rel_website']) === false) {
            $contact[] = Trans::getWord('website') . ':' . $data['rel_website'];
        }
        $strContact = implode(', ', $contact);

        $header = '<table class="pdf-header"  style="font-weight: bold">';
        $header .= '<tr>';
        $header .= '<td class="head-logo"><img class="company-logo" alt="" src="' . $path . '" /></td>';
        $header .= '<td class="head-company">';
        $header .= '<table>';
        $header .= '<tr><td class="company-name">' . $data['rel_name'] . '</td></tr>';
        $header .= '<tr><td class="address">' . $address . '</td></tr>';
        $header .= '<tr><td  class="address">' . $strContact . '</td></tr>';
        $header .= '</table>';
        $header .= '</td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }


    /**
     * Function to get the system setting header.
     *
     * @param int $relId To store the title document.
     * @return string
     */

    protected function getHeaderLogo($relId): string
    {
        $data = DocumentDao::loadDataByCodeAndReference('relation', $relId, 'officiallogo', 0, false);
        if(empty($data) === true) {
            $data = DocumentDao::loadDataByCodeAndReference('relation', $relId, 'documentlogo', 0, false);
        }
        $path = asset('images/image-not-found.jpg');
        $docDao = new DocumentDao();
        if (empty($data) === false) {
            $path = $docDao->getDocumentPath($data);
        }
        $header = '<table class="pdf-header"  style="font-weight: bold; border-bottom: none;">';
        $header .= '<tr>';
        $header .= '<td style="text-align: left;"><img style="height: 50px; max-height: 100px;" alt="" src="' . $path . '" /></td>';
        $header .= '</tr>';
        $header .= '</table>';

        return $header;
    }


    /**
     * Function to get the system setting header.
     *
     * @return string
     */

    protected function getDefaultFooter(): string
    {
        $footer = '<table class="pdf-footer">';
        $footer .= '<tr>';
        $footer .= '<td class="page-no"><span style="">{PAGENO} ' . Trans::getWord('of') . ' {nbpg}</span></td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        return $footer;
    }

    /**
     * Function to get the system setting header.
     *
     * @param int $relId To store the title document.
     * @param int $ofId  To store the title document.
     *
     * @return string
     */

    protected function getFooterAddress($relId, $ofId = 0): string
    {
        if ($ofId > 0) {
            $data = OfficeDao::getByReference($ofId);
        } else {
            $data = OfficeDao::loadMainOfficeByRelation($relId);
        }
        $address = $data['of_address_district'].', <br />'.$data['of_city'] . ', ' . $data['of_state'] . ', ' . $data['of_country'].', '.$data['of_postal_code'];
        $footer = '<table class="pdf-footer" style="border-top: none;">';
        $footer .= '<tr>';
        $footer .= '<td class="page-no" style="text-align: center; color: black">' . $data['of_relation'] . '</td>';
        $footer .= '</tr>';
        $footer .= '<tr>';
        $footer .= '<td class="page-no" style="text-align: center; color: black">' . $address . '</td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        return $footer;
    }

    /**
     * Function to get the system setting header.
     *
     * @param array  $data  To store the data.
     * @param string $style To store the data.
     *
     * @return string
     */

    protected function generateSignatureView(array $data, string $style = 'font-weight: bold;'): string
    {
        if (empty($data) === true) {
            return '';
        }
        $count = count($data);
        $width = 100 / $count;
        $result = '<table class="table-signature" style="' . $style . '">';
        if (empty($style) === false) {
            $result = '<table class="table-signature" style="' . $style . '">';

        }
        $result .= '<tr>';
        foreach ($data as $row) {
            $result .= '<td width="' . $width . 'px">';
            $result .= $row['label'];
            $result .= '</td>';
        }
        $result .= '</tr>';
        $result .= '<tr><td colspan="' . $count . '">&nbsp;</td></tr>';
        $result .= '<tr><td colspan="' . $count . '">&nbsp;</td></tr>';
        $result .= '<tr><td colspan="' . $count . '">&nbsp;</td></tr>';
        $result .= '<tr>';
        foreach ($data as $row) {
            $result .= '<td width="' . $width . 'px">';
            $result .= '<u>' . $row['name'] . '</u>';
            $result .= '</td>';
        }
        $result .= '</tr>';
        $result .= '</table>';

        return $result;
    }

    /**
     * Function to load css style
     *
     * @param string $pageSize To store the data.
     *
     * @return void
     */

    private function loadStyleSheet(string $pageSize): void
    {
        try {
            if ($pageSize === 'A6') {
                $stylesheet = file_get_contents(asset('dist/css/pdf_style_a6.css'));
            } else {
                $stylesheet = file_get_contents(asset('dist/css/pdf_style.css'));
            }
            $this->MPdf->WriteHTML($stylesheet, 1);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }

    }

    /**
     * Function to get the system setting header.
     *
     * @param array  $data          To store the data.
     * @param bool   $allowEmpty    To store the data.
     * @param bool   $useSemiColumn To store the data.
     * @param string $class         To store the data.
     * @param bool   $boldStyle     To store the data.
     *
     * @return string
     */

    protected function createTableView(array $data, bool $allowEmpty = true, bool $useSemiColumn = true, $class = '', bool $boldStyle = true): string
    {
        if (empty($data) === true) {
            return '';
        }
        $style = 'vertical-align: top;';
        if ($boldStyle) {
            $style .= 'font-weight: bold;';
        }
        $result = '<table class="' . $class . '" style="' . $style . '">';

        foreach ($data as $row) {
            if ($allowEmpty === true || empty($row['value']) === false) {
                $result .= '<tr>';
                $result .= '<td>' . $row['label'] . '</td>';
                if ($useSemiColumn === true) {
                    $result .= '<td>:</td>';
                }
                $val = $row['value'];
                if (empty($val) === true) {
                    $val = '-';
                }
                $result .= '<td>' . $val . '</td>';
                $result .= '</tr>';
            }
        }
        $result .= '</table>';

        return $result;
    }


    /**
     * Function to load the html content.
     *
     * @param string $title To store the title
     * @param string $class To store the title
     * @param string $style To store the title
     *
     * @return string
     */
    protected function createDocumentTitle(string $title, string $class = 'title', string $style = 'font-weight: bold;'): string
    {
        $result = '';
        if (empty($title) === false) {
            $result = '<div class="' . $class . '"  style="' . $style . '">';
            $result .= '<span>' . $title . '</span>';
            $result .= '</div>';
        }

        return $result;
    }

}
