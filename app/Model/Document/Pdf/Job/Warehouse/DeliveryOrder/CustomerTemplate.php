<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Document\Pdf\Job\Warehouse\DeliveryOrder;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Relation\RelationDao;
use Exception;

/**
 * Class to generate the stock report pdf.
 *
 * @package    app
 * @subpackage Model\Document\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class CustomerTemplate extends DefaultTemplate
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

            $this->Consignee = RelationDao::loadDataForDocumentHeader($this->JobOrder['job_rel_id'], (int)$this->JobOrder['job_of_id']);
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->JobOrder['job_id'] . ')';
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $this->Goods = JobOutboundDetailDao::loadData($wheres);

            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getWord('deliveryOrder' )));
            $this->MPdf->WriteHTML($this->getJobInformation());
            $this->MPdf->WriteHTML($this->getGoodsView());
            $this->MPdf->WriteHTML($this->getReceiveView());
            $this->MPdf->WriteHTML($this->getSignature());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

}
