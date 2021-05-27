<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\System\Notification;

use App\Model\Dao\Job\JobOrderDao;

/**
 *
 *
 * @package    app
 * @subpackage Frame\System\Notification
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobNotificationBuilder extends AbstractNotificationBuilder
{
    /**
     * Function to generate url
     *
     * @return string
     */
    protected function generateUrl(): string
    {
        $joDao = new JobOrderDao();

        return $joDao->getJobUrl('view', $this->Page->getIntParameter('jo_srt_id'), $this->Page->getIntParameter('jo_id'));
    }
}
