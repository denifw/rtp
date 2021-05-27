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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderContainerDao;

/**
 * Class to handle the ajax request fo SalesOrderContainer.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderContainer extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('soc_so_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getIntParameter('soc_so_id'));
            $wheres[] = SqlHelper::generateOrLikeCondition([
                'ct.ct_name', 'soc.soc_number', 'soc.soc_container_number'
            ], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
            if ($this->isValidParameter('jik_id') === true) {
                $wheres[] = '(soc.soc_id NOT IN (SELECT jikr_soc_id
                                                FROM job_inklaring_release
                                                WHERE jikr_jik_id = ' . $this->getIntParameter('jik_id') . '
                                                    AND jikr_deleted_on IS NULL AND jikr_soc_id IS NOT NULL))';
            }
            if ($this->isValidParameter('sdl_so_id') === true) {
                $wheres[] = '(soc.soc_id NOT IN (SELECT sdl_soc_id
                                                FROM sales_order_delivery
                                                WHERE sdl_so_id = ' . $this->getIntParameter('sdl_so_id') . '
                                                    AND sdl_deleted_on IS NULL AND sdl_soc_id IS NOT NULL))';
            }
            return SalesOrderContainerDao::loadSingleSelectData($wheres);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTruckData(): array
    {
        if ($this->isValidParameter('soc_so_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('soc.soc_so_id', $this->getIntParameter('soc_so_id'));
            $wheres[] = SqlHelper::generateOrLikeCondition([
                'ct.ct_name', 'soc.soc_number', 'soc.soc_container_number'
            ], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNullCondition('soc.soc_deleted_on');
            if ($this->isValidParameter('jik_id') === true) {
                $wheres[] = '(soc.soc_id NOT IN (SELECT jikr_soc_id
                                                FROM job_inklaring_release
                                                WHERE jikr_jik_id = ' . $this->getIntParameter('jik_id') . '
                                                    AND jikr_deleted_on IS NULL AND jikr_soc_id IS NOT NULL))';
            }
            if ($this->isValidParameter('sdl_so_id') === true) {
                $wheres[] = '(soc.soc_id NOT IN (SELECT sdl_soc_id
                                                FROM sales_order_delivery
                                                WHERE sdl_so_id = ' . $this->getIntParameter('sdl_so_id') . '
                                                    AND sdl_deleted_on IS NULL AND sdl_soc_id IS NOT NULL))';
            }
            return SalesOrderContainerDao::loadSingleSelectTruckData($wheres);
        }
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('soc_id') === true) {
            return SalesOrderContainerDao::getByReference($this->getIntParameter('soc_id'));
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
        if ($this->isValidParameter('soc_id') === true) {
            $data = SalesOrderContainerDao::getByReference($this->getIntParameter('soc_id'));
            if (empty($data) === false) {
                $data['soc_id'] = '';
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
        if ($this->isValidParameter('soc_id') === true) {
            $data = SalesOrderContainerDao::getByReference($this->getIntParameter('soc_id'));
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
