<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm\Quotation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\Quotation\PriceDao;

/**
 * Class to handle the ajax request fo Price.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class Price extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Price
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        # TODO Add additional wheres here.

        return PriceDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('prc_id') === true) {
            return PriceDao::getByReference($this->getIntParameter('prc_id'));
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
        if ($this->isValidParameter('prc_id') === true) {
            $data = PriceDao::getByReference($this->getIntParameter('prc_id'));
            if (empty($data) === false) {
                $data['prc_id'] = '';
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
        if ($this->isValidParameter('prc_id') === true) {
            $data = PriceDao::getByReference($this->getIntParameter('prc_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function loadSingleSelectTable(): array
    {
        if ($this->isValidParameter('prc_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_ss_id', $this->getIntParameter('prc_ss_id'));
            if ($this->isValidParameter('prc_type') === true) {
                $wheres[] = SqlHelper::generateStringCondition('prc.prc_type', $this->getStringParameter('prc_type'));
                $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', $this->getStringParameter('prc_type'));
            }
            if ($this->isValidParameter('prc_qt_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('prc_qt_number'));
            }
            if ($this->isValidParameter('prc_code') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('prc.prc_code', $this->getStringParameter('prc_code'));
            }
            if ($this->isValidParameter('prc_relation') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('rel.rel_name', $this->getStringParameter('prc_relation'));
            }
            if ($this->isValidParameter('prc_order_date') === true) {
                $wheres[] = SqlHelper::generateStringCondition('qt.qt_end_date', $this->getStringParameter('prc_order_date'), '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('qt.qt_end_date', date('Y-m-d'), '>=');
            }
            if ($this->isValidParameter('prc_srv_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('prc.prc_srv_id', $this->getIntParameter('prc_srv_id'));
            }
            if ($this->isValidParameter('prc_srt_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('prc.prc_srt_id', $this->getIntParameter('prc_srt_id'));
            }
            if ($this->isValidParameter('prc_eg_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('prc.prc_eg_id', $this->getIntParameter('prc_eg_id'));
            }
            if ($this->isValidParameter('prc_tm_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('prc.prc_tm_id', $this->getIntParameter('prc_tm_id'));
            }
            $wheres[] = SqlHelper::generateNullCondition('prc.prc_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('qt.qt_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('qt.qt_approve_on', false);
            $data = PriceDao::loadData($wheres, [], 30);
            return PriceDao::doPrepareData($data);
        }

        return [];
    }
}
