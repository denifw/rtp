<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\System\Notification;

use App\Frame\Exceptions\Message;
use App\Frame\Mvc\AbstractBaseModel;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\Notification\NotificationDao;
use App\Model\Dao\Notification\NotificationReceiverDao;
use App\Model\Dao\Relation\ContactPersonDao;
use App\Frame\Formatter\Trans;
use App\Model\Dao\System\Notification\NotificationTemplateDao;
use App\Model\Queue\MailQueue;
use Illuminate\Support\Facades\Log;

/**
 * Class to manage the creation of notification.
 *
 * @package    app
 * @subpackage Frame\System\Notification
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
abstract class AbstractNotificationBuilder
{
    /**
     * Attribute to set the user data.
     *
     * @var array $User
     */
    public $User;
    /**
     * Attribute to set the notification code.
     *
     * @var string $NotificationCode
     */
    private $NotificationCode;
    /**
     * Attribute to set the notification code.
     *
     * @var string $NotificationCode
     */
    private $Module;
    /**
     * Attribute to set the notification.
     *
     * @var array $Notification
     */
    private $Notification = [];
    /**
     * Attribute to set the page notification.
     *
     * @var array $NotificationTemplate
     */
    private $NotificationTemplate = [];
    /**
     * Attribute to store receivers.
     *
     * @var array $Receiver
     */
    private $Receiver = [];

    /**
     * Attribute to set the page.
     *
     * @var \App\Frame\Mvc\AbstractBaseLayout $Page
     */
    protected $Page;

    /**
     * Attribute to set the error message.
     *
     * @var string $ErrorMessage
     */
    private $ErrorMessage = '';

    /**
     * Attribute to store the list of id users
     *
     * @var array $ReceiverIds
     */
    private $ReceiverIds;

    /**
     * The model of mail
     *
     * @var  \App\Model\Mail\AbstractMailModel $ModelMail
     */
    private $ModelMail;

    /**
     * Base model constructor.
     *
     * @param \App\Frame\Mvc\AbstractBaseModel $page             To store the name page object.
     * @param string                           $notificationCode To store the name of the notification.
     * @param string                           $module To store the name of the module notification.
     * @param array                            $receiverIds      To store the receiver id based on contact person.
     *
     */
    public function __construct(AbstractBaseModel $page, string $notificationCode, string $module, array $receiverIds)
    {
        $this->Page = $page;
        $this->NotificationCode = $notificationCode;
        $this->Module = $module;
        $this->ReceiverIds = $receiverIds;
        $this->User = new UserSession();
    }

    /**
     * Function to do notify user.
     *
     * @return bool
     */
    public function doNotify(): bool
    {
        $success = true;
        if ($this->Page === null) {
            $success = false;
            $this->ErrorMessage = Trans::getWord('invalidPageObject', 'message');
        } else {
            try {
                $this->loadData();
                $this->createNotification();
                $this->doSaveNotificationReceiver();
//                $this->doPushMailNotification();
            } catch (\Exception $e) {
                Log::error('ERROR Notification - ' . $e->getMessage());
                $success = false;
                $this->ErrorMessage = $e->getMessage();
            }
        }

        return $success;
    }

    /**
     * Function to do notify user.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->ErrorMessage;
    }

    /**
     * Function to load the data.
     *
     * @return void
     */
    private function loadData(): void
    {
        $this->NotificationTemplate = NotificationTemplateDao::getByCodeAndModule($this->NotificationCode, $this->Module);
        if (empty($this->NotificationTemplate) === true) {
            Message::throwMessage('No data found for page notification data.', 'ERROR');
        }
        if (empty($this->ReceiverIds) === false) {
            $this->Receiver = ContactPersonDao::loadDataCpForNotification($this->ReceiverIds);
        }
    }

    /**
     * Function to create notification.
     *
     * @return void
     */
    private function createNotification(): void
    {
        $url = $this->generateUrl();
        $messageParams = [];
        foreach ($this->NotificationTemplate['nt_message_fields'] AS $field) {
//            if ($this->Page->isValidParameter($field) === false) {
//                throw new DebugException('Invalid parameter ' . $field . ' for notification.');
//            }
            $messageParams[$field] = $this->Page->getStringParameter($field);
        }
        $colValNotification = [
            'nf_nt_id' => $this->NotificationTemplate['nt_id'],
            'nf_ss_id' => $this->User->getSsId(),
            'nf_url' => $url,
            'nf_url_key' => md5($url),
            'nf_message_parameter' => json_encode($messageParams),
        ];
        try {
            $nfDao = new NotificationDao();
            $nfDao->doInsertTransaction($colValNotification);
            $this->Notification = [
                'nf_id' => $nfDao->getLastInsertId(),
                'nf_ss_id' => $this->User->getSsId(),
                'nf_nt_id' => $this->NotificationTemplate['nt_id'],
                'nf_url' => $url,
                'nf_message_parameter' => $messageParams,
            ];
            $date = new \DateTime();
            $this->Notification['date'] = $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to store notification receiver.
     *
     * @return void
     */
    private function doSaveNotificationReceiver(): void
    {
        try {
            $nfrDao = new NotificationReceiverDao();
            foreach ($this->Receiver AS $row) {
                if ((empty($row['us_id']) === false) && ($row['us_id'] !== $this->User->getId())) {
                    $colVal = [
                        'nfr_nf_id' => $this->Notification['nf_id'],
                        'nfr_us_id' => $row['us_id'],
                        'nfr_delivered' => 'N',
                        'nfr_read_on' => null,
                        'nfr_read_by' => null,
                    ];
                    $nfrDao->doInsertTransaction($colVal);
                }
            }
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to push mail notification
     *
     * @return void
     */
    private function doPushMailNotification(): void
    {
        # load mail model class
        try {
            foreach ($this->Receiver as $receiver) {
                if (empty($receiver['cp_email']) === false && $receiver['us_id'] !== $this->User->getId()) {
                    $this->loadMailModel();
                    $this->ModelMail->setReceiver($receiver);
                    $this->ModelMail->doPrepareData();
                    $mailQueue = new MailQueue($receiver, $this->ModelMail);
                    $mailQueue->setDelay(3);
                    dispatch($mailQueue);
                }
            }
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to load mail model.
     *
     * @return void
     */
    private function loadMailModel(): void
    {
        $modelMailPath = $this->NotificationTemplate['nt_mail_path'];
        if (empty($modelMailPath) === false) {
            $modelMailPath = str_replace('/', '\\', $modelMailPath);
            $model = '\\App\\Model\\Mail\\' . $modelMailPath;
            if (class_exists($model) === true) {
                $this->ModelMail = new $model($this->Page->getAllParameters());
            } else {
                Message::throwMessage(Trans::getWord('mailModelNotFound', 'message') . $model);
            }
        } else {
            Message::throwMessage(Trans::getWord('mailModelNotFound', 'message'));
        }
    }

    /**
     * Function to generate url
     *
     * @return string
     */
    abstract protected function generateUrl(): string;
}
