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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Mail\AbstractMailModel;

/**
 * Class base job order mail model.
 *
 * @package    app
 * @subpackage Model\Mail\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BaseJobMail extends AbstractMailModel
{
    /**
     * Property to store job's data
     *
     * @var array $Job
     */
    protected $Job = [];

    /**
     * Property to store job id reference.
     *
     * @var int $JobId
     */
    protected $JobId;

    /**
     * Function prepare all data that needed.
     *
     * @return void
     */
    public function doPrepareData(): void
    {
        $this->JobId = $this->Parameters['jo_id'];
        $this->loadJobData();
        $this->setSubject(Trans::getNotificationWord($this->NotificationCode . '.subject', '',
            [
                'jo_number' => $this->Job['jo_number'],
                'jo_customer' => $this->Job['jo_customer'],
                'jo_service_term' => $this->Parameters['jo_service_term'],
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
        $data = [
            [
                'label' => Trans::getWord('number'),
                'value' => $this->Job['jo_number'],
            ],
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->Job['jo_customer'],
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->Job['jo_customer_ref'],
            ],
        ];

        return StringFormatter::generateCustomTableView($data);
    }

    /**
     * Function create greeting text
     *
     * @return string
     */
    protected function createGreeting(): string
    {
        return 'Hello, ' . $this->Receiver['cp_name'];
    }

    /**
     * Function to create job information
     *
     * @return string
     */
    private function createUrl(): string
    {
        $jobDao = new JobOrderDao();
        $url = $jobDao->getJobUrl('View', $this->Parameters['jo_srt_id'], $this->JobId);
        return 'Please <a href="' . url($url) . '"> click here </a> for more information';
    }

    /**
     * Function load job data.
     *
     * @return void
     */
    private function loadJobData(): void
    {
        if ($this->Parameters['jo_srv_code'] === 'warehouse') {
            switch ($this->Parameters['jo_srt_route']) {
                case 'joWhInbound':
                    $this->Job = JobInboundDao::getByJobOrderAndSystemSetting($this->JobId, $this->SystemSetting->getSsId());
                    break;
                case 'joWhOutbound':
                    $this->Job = JobOutboundDao::getByJoIdAndSystem($this->JobId, $this->SystemSetting->getSsId());
                    break;
            }
        } elseif ($this->Parameters['jo_srv_code'] === 'inklaring') {
            $this->Job = JobInklaringDao::getByReferenceAndSystemSetting($this->JobId, $this->SystemSetting->getSsId());
        } elseif ($this->Parameters['jo_srv_code'] === 'delivery') {
            $this->Job = JobDeliveryDao::getByJobIdAndSystem($this->JobId, $this->SystemSetting->getSsId());
        }
    }

}
