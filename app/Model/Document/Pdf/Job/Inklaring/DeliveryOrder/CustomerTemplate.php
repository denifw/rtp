<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Document\Pdf\Job\Inklaring\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use Exception;

/**
 * Class to generate the delivery order report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class CustomerTemplate extends BaseTemplate
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
            $header = $this->getDefaultHeader($this->JobOrder['jo_rel_id']);

            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryOrder')));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getContainerView());
            $this->MPdf->WriteHTML($this->getSignature());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }
}
