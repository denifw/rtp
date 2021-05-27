<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobNotificationReceiverDao;

/**
 * Class to handle the ajax request fo JobNotificationReceiver.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobNotificationReceiver extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jnr_id') === true) {
            $jnr = JobNotificationReceiverDao::getByReference($this->getIntParameter('jnr_id'));
            if (empty($jnr) === false) {
                $keys = array_keys($jnr);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $jnr[$key];
                }
            }
        }

        return $result;
    }
}
