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
 * Class to handle password reset.
 *
 * @package    app
 * @subpackage Model\Mail
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    private $token;
    private $User;

    /**
     * Create a new message instance.
     *
     * @param array  $user  to set the receiver.
     * @param string $token to set the token.
     *
     */
    public function __construct(array $user, string $token)
    {
        $this->User = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $expiredDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->User['expired_date']);
        $date = $expiredDate->format('d M Y') . ' ' . $expiredDate->format('H:i');

        return $this->view('mail.password_reset')->with([
            'token' => $this->token,
            'receiver' => $this->User['name'],
            'title' => Trans::getWord('passwordReset'),
            'pre_header' => Trans::getWord('preHeaderPasswordReset', 'mail', '', ['expired_date' => $date]),
        ])->subject(Trans::getWord('passwordReset'));
    }
}
