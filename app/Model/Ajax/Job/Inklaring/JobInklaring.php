<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job\Inklaring;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;

/**
 * Class to handle the ajax request fo JobInklaring.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Inklaring
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobInklaring extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobInklaring
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('jo_id') === true) {
            return JobInklaringDao::getByReference($this->getIntParameter('jo_id'));
        }
        return [];
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('jo_id') === true) {
            $data = JobInklaringDao::getByReference($this->getIntParameter('jo_id'));
            $dt = new DateTimeParser();
            if (empty($data) === false) {
                $data['jik_srt_id'] = $data['jo_srt_id'];
                $data['jik_service_term'] = $data['jo_service_term'];
                $data['jik_srv_id'] = $data['jo_srv_id'];
                $data['jik_service'] = $data['jo_service'];
                $data['jik_closing_time'] = $dt->formatTime($data['jik_closing_time']);
                $data['jik_departure_time'] = $dt->formatTime($data['jik_departure_time']);
                $data['jik_arrival_time'] = $dt->formatTime($data['jik_arrival_time']);
                $data['jik_id'] = '';
            }
        }

        return $data;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jo_id') === true) {
            $data = JobInklaringDao::getByReference($this->getIntParameter('jo_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }
}
