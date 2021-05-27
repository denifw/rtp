<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Notification;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Notification\NotificationDao;
use App\Model\Dao\Notification\NotificationReceiverDao;

/**
 * Class to handle the ajax request fo Notification.
 *
 * @package    app
 * @subpackage Model\Ajax\Notification
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class Notification extends AbstractBaseAjaxModel
{

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadUnreadNotification(): array
    {
        $user = session('user');
        $wheres = [];
        $wheres[] = '(nf.nf_ss_id = ' . $user['ss_id'] . ')';
        $wheres[] = '(nfr.nfr_us_id = ' . $user['us_id'] . ')';
        $wheres[] = '(nfr.nfr_read_on IS NULL)';
        $orders[] = 'nfr.nfr_read_on';
        $orders[] = 'nf.nf_created_on DESC';
        $results = NotificationDao::loadData($wheres, $orders);
        $unRead = 0;
        $rows = [];
        if (empty($results) === false) {
            $nfrDao = new NotificationReceiverDao();
            $unRead = \count($results);
            foreach ($results AS $row) {
                if ($row['nfr_delivered'] === 'N') {
                    $rows[] = $this->doPrepareRowNotification($row);
                    $nfrDao->doUpdateTransaction($row['nfr_id'], ['nfr_delivered' => 'Y']);
                }
            }
        }
        $results['total_unread'] = $unRead;
        $results['new_rows'] = $rows;

        return $results;
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadListNotification(): array
    {
        $user = session('user');
        $wheres = [];
        $wheres[] = '(nf.nf_ss_id = ' . $user['ss_id'] . ')';
        $wheres[] = '(nfr.nfr_us_id = ' . $user['us_id'] . ')';
        $result = [];
        $rows = [];
        $unRead = 0;
        $orders[] = 'nfr.nfr_delivered';
        $orders[] = 'nfr.nfr_read_on DESC';
        $orders[] = 'nf.nf_created_on DESC';
        $results = NotificationDao::loadData($wheres, $orders, 10);
        if (empty($results) === false) {
            $nfrDao = new NotificationReceiverDao();
            foreach ($results AS $row) {
                $rows[] = $this->doPrepareRowNotification($row);
                if (empty($row['nfr_read_on']) === true) {
                    $unRead++;
                }
                if ($row['nfr_delivered'] === 'N') {
                    $nfrDao->doUpdateTransaction($row['nfr_id'], ['nfr_delivered' => 'Y']);
                }
            }
        }
        $result['rows'] = $rows;
        $result['total_unread'] = $unRead;

        return $result;
    }

    /**
     * Function to load page
     *
     * @param array $row To store the notification data.
     *
     * @return array
     */
    public function doPrepareRowNotification(array $row): array
    {
        $now = DateTimeParser::createDateTime();
        $created = \DateTime::createFromFormat('Y-m-d H:i:s', $row['nf_created_on']);
        $interval = $created->diff($now);
        $time = '';
        if ($interval->y > 0) {
            $time = 'long time ago';
        } elseif (empty($time) === true && $interval->m > 0) {
            $time = $this->doFormatTextTime($interval->m, 'month');
        } elseif ($interval->d > 0) {
            $time = $this->doFormatTextTime($interval->d, 'day');
        } elseif ($interval->h > 0) {
            $time = $this->doFormatTextTime($interval->h, 'hour');
        } elseif ($interval->i > 0) {
            $time = $this->doFormatTextTime($interval->i, 'minute');
        } else {
            $time = ' just now';
        }
        $messageParameter = [];
        if (empty($row['nf_message_parameter']) === false) {
            $messageParameter = json_decode($row['nf_message_parameter'], true);
        }
        $message = Trans::getNotificationWord($row['nt_code'] . '.message', '', $messageParameter);
        $read = 'N';
        if (empty($row['nfr_read_on']) === false) {
            $read = 'Y';
        }
        $result = [
            'nfr_id' => $row['nfr_id'],
            'nfr_read' => $read,
            'nfr_delivered' => $row['nfr_delivered'],
            'cp_name' => $row['cp_name'],
            'nf_url' => $row['nf_url'],
            'time' => $time,
            'nf_message' => $message,
        ];

        return $result;
    }

    /**
     * Function to load page
     *
     * @param int    $number To store the number of the time.
     * @param string $text   To store the type of the time.
     *
     * @return string
     */
    private function doFormatTextTime(int $number, string $text): string
    {
        if ($number > 1) {
            $text .= 's';
        }

        return $number . ' ' . $text . ' ago';
    }
}
