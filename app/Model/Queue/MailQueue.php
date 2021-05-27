<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Queue;

use App\Model\Mail\AbstractMailModel;
use Illuminate\Support\Facades\Mail;

/**
 * Class mail queue
 *
 * @package    app
 * @subpackage Model\Mail
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class MailQueue extends AbstractQueue
{
    /**
     * The model of mail
     *
     * @var  \App\Model\Mail\AbstractMailModel $MailModel
     */
    private $MailModel;

    /**
     * Property to store data receiver.
     *
     * @var array $EmailReceiver
     */
    private $Receiver;

    /**
     * Create a new job instance.
     *
     * @param array             $receiver Data of receiver.
     * @param AbstractMailModel $model    The mail model.
     *
     * @return void
     */
    public function __construct(array $receiver, AbstractMailModel $model)
    {
        $this->Receiver = $receiver;
        $this->MailModel = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (empty($this->Receiver['cp_email']) === false) {
            Mail::to($this->Receiver['cp_email'])->send($this->MailModel);
        }
    }

}
