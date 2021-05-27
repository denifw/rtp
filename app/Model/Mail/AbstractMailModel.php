<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Mail;

use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use Illuminate\Mail\Mailable;

/**
 * Class to manage the creation of notification.
 *
 * @package    app
 * @subpackage Frame\System
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
abstract class AbstractMailModel extends Mailable
{
    /**
     * Attribute to set the content.
     *
     * @var string $Content
     */
    private $Content;
    /**
     * Attribute to set the content.
     *
     * @var string $Subject
     */
    private $Subject = '';

    /**
     * Attribute to set the receiver.
     *
     * @var array $Receiver
     */
    protected $Receiver = [];

    /**
     * The mail view path.
     *
     * @var string $mailViewPath
     */
    private $MailViewPath;

    /**
     * Object notification builder
     *
     * @var array $SystemSetting
     */
    protected $SystemSetting;

    /**
     * Attribute to set the notification.
     *
     * @var string $NotificationCode
     */
    protected $NotificationCode = '';
    /**
     * Attribute to set the notification.
     *
     * @var array $Parameters
     */
    protected $Parameters = [];


    /**
     * Abstract mai model constructor.
     *
     * @param string $mailViewPath     The mail view path.
     * @param string $notificationCode The mail view path.
     * @param array  $parameters       To store the receiver of the email.
     */
    public function __construct(string $mailViewPath, string $notificationCode, array $parameters)
    {
        $this->Parameters = $parameters;
        $this->MailViewPath = $mailViewPath;
        $this->NotificationCode = $notificationCode;
        $this->SystemSetting = new UserSession();
    }

    /**
     * Function prepare all data that needed.
     *
     * @return void
     */
    abstract public function doPrepareData(): void;


    /**
     * Function to set the title of the message.
     *
     * @param string $content To store the content of the email.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->Content .= $content;
    }

    /**
     * Function to set the receiver for the email.
     *
     * @param array $receiver To store the receiver of the email.
     *
     * @return void
     */
    public function setReceiver(array $receiver): void
    {
        $this->Receiver = $receiver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view($this->MailViewPath)->with($this->loadViewData())->subject($this->Subject);
    }

    /**
     * Build the message.
     *
     * @return array
     */
    public function loadViewData(): array
    {
        return [
            'ss_logo' => 'https://my.matalogix.com/storage/mbs/systemsetting/logo/mbs1563951086.png',
            'ss_name' => $this->SystemSetting->Relation->getName(),
            'receiver' => $this->Receiver['cp_name'],
            'mail_body' => $this->Content,
            'title' => $this->loadTitle(),
            'pre_header' => $this->loadPreHeader()
        ];
    }

    /**
     * Function get subject email.
     *
     * @return string
     */
    private function loadTitle(): string
    {
        return Trans::getNotificationWord($this->NotificationCode . '.title');
    }

    /**
     * Function get subject email.
     *
     * @return string
     */
    private function loadPreHeader(): string
    {
        return Trans::getNotificationWord($this->NotificationCode . '.preHeader');
    }

    /**
     * Function get subject email.
     *
     * @param string $subject To store the subject.
     *
     * @return void
     */
    protected function setSubject(string $subject): void
    {
        $this->Subject = $subject;
    }

    /**
     * Function create greeting text.
     *
     * @return string
     */
    protected function createSignature(): string
    {
        $result = '<p style="font-size: 13px; padding-top: 10px; color: #2B2B2B">Best Regard, 
                  <br><br><strong>' . $this->SystemSetting->Relation->getName(). '</strong></p>';

        return $result;
    }

}
