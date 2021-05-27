<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Notification;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Notification\NotificationDao;
use App\Model\Dao\Notification\NotificationReceiverDao;

/**
 * Class to control the system of Notification.
 *
 * @package    app
 * @subpackage Model\Listing\Notification
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class Notification extends AbstractListingModel
{

    /**
     * Notification constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'notification');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'cp_name' => Trans::getWord('creator'),
                'nf_message' => Trans::getWord('message'),
                'nf_created_on' => Trans::getWord('createdOn'),
                'nfr_read' => Trans::getWord('read'),
                'url_link' => Trans::getWord('action'),
            ]
        );
        $listingData = $this->doPrepareData($this->loadData());
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('nfr_read', 'yesno');
        $this->ListingTable->addColumnAttribute('url_link', 'style', 'text-align:center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return NotificationDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return NotificationDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
    }

    /**
     * Function to do prepare notification data.
     *
     * @param array $data To store the notification data.
     *
     * @return array
     */
    public function doPrepareData(array $data): array
    {
        $results = [];
        $nfrDao = new NotificationReceiverDao();
        foreach ($data AS $row) {
            if ($row['nfr_delivered'] === 'N') {
                $nfrDao->doUpdateTransaction($row['nfr_id'], ['nfr_delivered' => 'Y']);
            }
            $button = new HyperLink('urlLink', '', $row['nf_url']);
            $button->addAttribute('class', 'btn btn-success btn-sm');
            $button->setIcon('fa fa-eye');
            if (empty($row['nf_message_parameter']) === false) {
                $messageParam = json_decode($row['nf_message_parameter'], true);
            }
            $read = 'N';
            if (empty($row['nfr_read_on']) === false) {
               $read = 'Y';
            }
            $results[] = [
                'cp_name' => $row['cp_name'],
                'nf_message' => Trans::getWord($row['nt_code'] . '.message', 'notification', '', $messageParam),
                'nf_created_on' => DateTimeParser::format($row['nf_created_on'], 'Y-m-d H:i:s', 'd M Y,  H:i:s'),
                'nfr_read' => $read,
                'url_link' => $button
            ];
        }

        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('nf_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNumericCondition('nfr_us_id', $this->User->getId());

        # return the list where condition.
        return $wheres;
    }
}
