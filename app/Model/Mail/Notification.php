<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Model\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class to create
 *
 * @package    app
 * @subpackage Model\Mail
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Notification extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Attribute to set the subject.
     *
     * @var string $Subject
     */
    private $Subject;
    /**
     * Attribute to set the Title.
     *
     * @var string $Subject
     */
    private $Title;
    /**
     * Attribute to set the content.
     *
     * @var string $Content
     */
    private $Content;
    /**
     * Attribute to set the pre header data.
     *
     * @var string $PreHeader
     */
    private $PreHeader;
    /**
     * Attribute to set the receiver.
     *
     * @var array $Receiver
     */
    private $Receiver = [];

    /**
     * Function to set the title of the message.
     *
     * @param string $title To store the title of the email.
     *
     * @return void
     */
    public function setTitle($title): void
    {
        $this->Title = $title;
    }

    /**
     * Function to set the title of the message.
     *
     * @param string $subject To store the subject of the email.
     *
     * @return void
     */
    public function setSubject($subject): void
    {
        $this->Subject = $subject;
    }

    /**
     * Function to set the title of the message.
     *
     * @param string $preHeader To store the pre-header message.
     *
     * @return void
     */
    public function setPreHeader($preHeader): void
    {
        $this->PreHeader = $preHeader;
    }

    /**
     * Function to set the title of the message.
     *
     * @param string $content To store the content of the email.
     *
     * @return void
     */
    public function setContent($content): void
    {
        $this->Content = $content;
    }

    /**
     * Function to set the receiver for the email.
     *
     * @param array $receiver To store the receiver of the email.
     *
     * @return void
     */
    public function setReceiver($receiver): void
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
        return $this->view('mail.notification')->with([
            'receiver' => $this->Receiver['cp_name'],
            'mail_body' => $this->Content,
            'title' => $this->Title,
            'pre_header' => $this->PreHeader
        ])->subject($this->Subject);
    }
}
