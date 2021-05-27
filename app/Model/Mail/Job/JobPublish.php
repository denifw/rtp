<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Mail\Job;


use App\Frame\Formatter\Trans;

/**
 * Mail for job inbound publish
 *
 * @package    app
 * @subpackage Model\Mail\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobPublish extends BaseJobMail
{
    /**
     * Mail template constructor.
     *
     * @param array $parameters To store the receiver of the email.
     */
    public function __construct(array $parameters)
    {
        parent::__construct('mail.master_mail_template', 'jobpublish', $parameters);
    }

    /**
     * Function to create job information
     *
     * @return string
     */
    protected function createJobInformation(): string
    {
        $text = Trans::getNotificationWord($this->NotificationCode . '.status', '', [
            'jo_status' => '<span class="label label-danger">' . Trans::getWord('published') . '</span>'
        ]);
        $jobInformation = parent::createJobInformation();
        return '<br>' . $text . $jobInformation;
    }


}
