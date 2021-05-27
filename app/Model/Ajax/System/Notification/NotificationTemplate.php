<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System\Notification;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Notification\NotificationTemplateDao;

/**
 * Class to handle the ajax request fo pageNotificationTemplate.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Page
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class NotificationTemplate extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('nt_id') === true) {
            return NotificationTemplateDao::getByReference($this->getIntParameter('nt_id'));
        }

        return [];
    }

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('nt_id') === true) {
            $ntData= NotificationTemplateDao::getByReference($this->getIntParameter('nt_id'));
            if (empty($ntData) === false) {
                $keys = array_keys($ntData);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $ntData[$key];
                }
            }
        }

        return $result;
    }
}
