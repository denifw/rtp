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

use App\Frame\Formatter\Trans;
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
class EmailRegistration extends Mailable
{
    use Queueable, SerializesModels;

    private $User;

    /**
     * Create a new message instance.
     *
     * @param array $user to set the receiver.
     *
     */
    public function __construct(array $user)
    {
        $this->User = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('mail.mail_registration')->with([
            'username' => $this->User['us_username'],
            'password' => $this->User['us_password'],
            'receiver' => $this->User['cp_name'],
            'title' => Trans::getWord('registrationInfo', 'mail'),
            'pre_header' => Trans::getWord('preHeaderRegistrationInfo', 'mail'),
        ])->subject(Trans::getWord('registrationInfo', 'mail'));
    }
}
