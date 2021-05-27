<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\CustomerService;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;

/**
 * Class to handle the ajax request fo SalesOrderDelivery.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderDelivery extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for SalesOrderDelivery
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        # TODO Add additional wheres here.

        return SalesOrderDeliveryDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select for SalesOrderDelivery
     *
     * @return array
     */
    public function loadDataForJobDelivery(): array
    {
        if ($this->isValidParameter('so_ss_id') === false) {
            return [];
        }
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->getIntParameter('so_ss_id'));
        $wheres[] = SqlHelper::generateNullCondition('so.so_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('so.so_publish_on', false);
        $wheres[] = SqlHelper::generateNullCondition('sdl.sdl_deleted_on');
        if ($this->isValidParameter('sdl_so_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('sdl.sdl_so_id', $this->getIntParameter('sdl_so_id'));
        }
        if ($this->isValidParameter('sdl_eg_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('sdl.sdl_eg_id', $this->getIntParameter('sdl_eg_id'));
        }
        if ($this->isValidParameter('sdl_srt_load') === true) {
            $wheres[] = SqlHelper::generateStringCondition('srt.srt_load', $this->getStringParameter('sdl_srt_load'));
        }
        if ($this->isValidParameter('sdl_srt_unload') === true) {
            $wheres[] = SqlHelper::generateStringCondition('srt.srt_unload', $this->getStringParameter('sdl_srt_unload'));
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $wheres[] = '(sdl.sdl_id NOT IN (SELECT jdld.jdld_sdl_id
                                                FROM job_order as jo
                                                    INNER JOIN job_delivery as jdl ON jo.jo_id = jdl.jdl_jo_id
                                                    INNER JOIN job_delivery_detail as jdld ON jdl.jdl_id = jdld.jdld_jdl_id
                                            WHERE (jo.jo_srt_id = ' . $this->getIntParameter('jo_srt_id') . ')
                                            AND (jo.jo_ss_id = ' . $this->getIntParameter('so_ss_id') . ')
                                            AND (jo.jo_deleted_on IS NULL)
                                            AND (jdld.jdld_deleted_on IS NULL)))';
        }

        $data = SalesOrderDeliveryDao::loadSingleSelectData($wheres);
        $results = [];
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $row['sdl_pick_time'] = $dt->formatTime($row['sdl_pick_time']);
            $row['sdl_return_time'] = $dt->formatTime($row['sdl_return_time']);
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('sdl_id') === true) {
            return SalesOrderDeliveryDao::getByReference($this->getIntParameter('sdl_id'));
        }
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('sdl_id') === true) {
            $data = SalesOrderDeliveryDao::getByReference($this->getIntParameter('sdl_id'));
            $number = new NumberFormatter();
            if (empty($data) === false) {
                $keys = array_keys($data);
                $postFix = strtolower($data['sdl_type']);
                foreach ($keys as $key) {
                    $result[$key . $postFix] = $data[$key];
                }
                $result['sdl_quantity' . $postFix . '_number'] = $number->doFormatFloat($result['sdl_quantity' . $postFix]);
            }
        }
        return $result;
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $result = [];
        if ($this->isValidParameter('sdl_id') === true) {
            $data = SalesOrderDeliveryDao::getByReference($this->getIntParameter('sdl_id'));
            if (empty($data) === false) {
                $number = new NumberFormatter();
                $keys = array_keys($data);
                $postFix = strtolower($data['sdl_type']);
                foreach ($keys as $key) {
                    $result[$key . $postFix] = $data[$key];
                }
                $result['sdl_id' . $postFix] = '';
                $result['sdl_quantity' . $postFix . '_number'] = $number->doFormatFloat($result['sdl_quantity' . $postFix]);
            }
        }

        return $result;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('sdl_id') === true) {
            $data = SalesOrderDeliveryDao::getByReference($this->getIntParameter('sdl_id'));
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
