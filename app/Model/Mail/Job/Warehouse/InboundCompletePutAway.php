<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Mail\Job\Warehouse;


use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Mail\Job\BaseJobMail;

/**
 * Mail for job inbound start unload
 *
 * @package    app
 * @subpackage Model\Mail\Job\Warehouse
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class InboundCompletePutAway extends BaseJobMail
{
    /**
     * Mail template constructor.
     *
     * @param array $parameters To store the receiver of the email.
     */
    public function __construct(array $parameters)
    {
        parent::__construct('mail.master_mail_template', 'inboundcompleteputaway', $parameters);
    }

    /**
     * Function prepare all data that needed.
     *
     * @return void
     */
    public function doPrepareData(): void
    {
        $this->Job = JobInboundDao::getByJoId($this->Parameters['jo_id']);
        $this->setSubject(Trans::getNotificationWord($this->NotificationCode . '.subject', '',
            [
                'jo_number' => $this->Job['jo_number'],
                'jo_customer' => $this->Job['jo_customer'],
            ]
        ));
        $this->setContent($this->createGreeting());
        $this->setContent($this->createJobInformation());
        $this->setContent($this->createUrl());
        $this->setContent($this->createSignature());
    }

    /**
     * Function to create job information
     *
     * @return string
     */
    protected function createJobInformation(): string
    {
        $text = Trans::getNotificationWord($this->NotificationCode . '.status', '', [
            'jo_status' => '<span class="label label-primary">' . Trans::getWord('putAwayCompleted') . '</span>'
        ]);
        $jobInformation = parent::createJobInformation();
        return '<br>' . $text . $jobInformation;
    }

    /**
     * Function to create job information
     *
     * @return string
     */
    private function createUrl(): string
    {
        return 'Please <a href="' . url('joWhInbound/view?jo_id=' . $this->Job['jo_id']) . '"> click here </a> for more information';
    }

}
