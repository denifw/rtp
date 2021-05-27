<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\StockMilestone;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use Exception;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class HeaderTemplate extends DefaultTemplate
{
    /**
     * Function to set the content to pdf.
     *
     * @return void
     */
    public function loadContent(): void
    {
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $header = $this->getDefaultHeader($this->User->getRelId());
            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('L', '', '', '1', '', 5, 5, $topMargin, 10, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');

            $content = '';
            $i = 1;
            foreach ($this->WarehouseIds as $id) {
                if ($this->getStringParameter('view_by') === 'W') {
                    $content .= '<p class="title-4" style="font-weight: bold"> ' . $i . '. ' . $this->Warehouses[$id] . '</p>';
                    $content .= $this->getDetailView($id);
                    $i++;
                } else {
                    $temp = $this->Data[$id];
                    $keys = array_keys($temp);
                    foreach ($keys as $gdId) {
                        $title = $this->Warehouses[$id] . ' - ' . $this->Goods[$gdId];
                        $content .= '<p class="title-4" style="font-weight: bold"> ' . $i . '. ' . $title . '</p>';
                        $content .= $this->getDetailView($id, $gdId);
                        $i++;
                    }
                }

            }
            $this->MPdf->WriteHTML($this->getTitle());
            $this->MPdf->WriteHTML($content);
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }


    /**
     * Function to load the html content.
     *
     * @return string
     */
    protected function getTitle(): string
    {
        $date = DateTimeParser::format($this->getStringParameter('from_date'), 'Y-m-d', 'd M Y');
        if ($this->isValidParameter('until_date') === true) {
            $date .= ' - ' . DateTimeParser::format($this->getStringParameter('until_date'), 'Y-m-d', 'd M Y');

        }
        $result = '<div class="title"  style="font-weight: bold">';
        $result .= '<h4 style="margin-bottom: -5px">' . Trans::getWord('stockMilestone') . ' ' . $date . '</h4>';
        $result .= '</div>';

        return $result;
    }

}
