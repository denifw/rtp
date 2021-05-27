<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\CostCodeDao;

/**
 * Class to handle the ajax request fo CostCode.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class CostCode extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('cc_ss_id')) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('cc.cc_ss_id', $this->getIntParameter('cc_ss_id'));
            if ($this->isValidParameter('ccg_srv_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('ccg.ccg_srv_id', $this->getIntParameter('ccg_srv_id'));
            }
            if ($this->isValidParameter('ccg_type') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_type', $this->getStringParameter('ccg_type'));
            }
            $wheres[] = SqlHelper::generateOrLikeCondition(['cc.cc_code', 'cc.cc_name'], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNullCondition('cc.cc_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('cc.cc_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_active', 'Y');
            return CostCodeDao::loadSingleSelectData($wheres);
        }

        return [];
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadPurchaseData(): array
    {
        if ($this->isValidParameter('cc_ss_id')) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('cc.cc_ss_id', $this->getIntParameter('cc_ss_id'));
            if ($this->isValidParameter('ccg_srv_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('ccg.ccg_srv_id', $this->getIntParameter('ccg_srv_id'));
            }
            if ($this->isValidParameter('cc_group_code') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('ccg.ccg_code', $this->getStringParameter('cc_group_code'));
            }
            if ($this->isValidParameter('cc_group_name') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('ccg.ccg_name', $this->getStringParameter('cc_group_name'));
            }
            if ($this->isValidParameter('cc_code') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_code', $this->getStringParameter('cc_code'));
            }
            if ($this->isValidParameter('cc_name') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_name', $this->getStringParameter('cc_name'));
            }
            if ($this->isValidParameter('cc_service') === true) {
                if ($this->getStringParameter('cc_service', 'N') === 'Y') {
                    $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_srv_id', false);
                } else {
                    $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_srv_id');
                }
            }
            $wheres[] = "(ccg.ccg_type IN ('P', 'R'))";
            $wheres[] = SqlHelper::generateOrLikeCondition(['cc.cc_code', 'cc.cc_name'], $this->getStringParameter('search_key', ''));
            $wheres[] = SqlHelper::generateNullCondition('cc.cc_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('cc.cc_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_active', 'Y');
            return CostCodeDao::loadSingleSelectData($wheres);
        }

        return [];
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function getBySoId(): array
    {
        if ($this->isValidParameter('cc_ss_id') && $this->isValidParameter('so_id')) {
            $wheres = [];
            if ($this->isValidParameter('ccg_type') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_type', $this->getStringParameter('ccg_type'));
            }
            $wheres[] = SqlHelper::generateOrLikeCondition(['cc.cc_code', 'cc.cc_name'], $this->getStringParameter('search_key', ''));
            $wheres[] = '(ccg.ccg_srv_id IN (SELECT srt.srt_srv_id
                                                FROM sales_order_service as sos INNER JOIN
                                                service_term as srt ON srt.srt_id = sos.sos_srt_id
                                                WHERE sos.sos_deleted_on IS NULL and sos.sos_so_id = ' . $this->getIntParameter('so_id') . '
                                                GROUP BY srt.srt_srv_id))';
            $wheres[] = SqlHelper::generateNumericCondition('cc.cc_ss_id', $this->getIntParameter('cc_ss_id'));
            $wheres[] = SqlHelper::generateNullCondition('cc.cc_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('cc.cc_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_active', 'Y');
            return CostCodeDao::loadSingleSelectData($wheres);
        }

        return [];
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadPurchaseTable(): array
    {
        if ($this->isValidParameter('cc_ss_id')) {
            $wheres = [];
            if ($this->isValidParameter('ccg_srv_id') === true) {
                $wheres[] = '(ccg.ccg_srv_id = ' . $this->getIntParameter('ccg_srv_id') . ')';
            }
            if ($this->isValidParameter('cc_group') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('ccg.ccg_name', $this->getStringParameter('cc_group'));
            }
            if ($this->isValidParameter('cc_code') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_code', $this->getStringParameter('cc_code'));
            }
            if ($this->isValidParameter('cc_name') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_name', $this->getStringParameter('cc_name'));
            }
            $wheres[] = "(ccg.ccg_type IN ('P', 'R'))";
            $wheres[] = SqlHelper::generateNumericCondition('cc.cc_ss_id', $this->getIntParameter('cc_ss_id'));
            $wheres[] = SqlHelper::generateNullCondition('cc.cc_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('cc.cc_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_active', 'Y');
            return CostCodeDao::loadData($wheres, [], 20);
        }

        return [];
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectTable(): array
    {
        if ($this->isValidParameter('cc_ss_id')) {
            $wheres = [];
            if ($this->isValidParameter('ccg_srv_id') === true) {
                $wheres[] = '(ccg.ccg_srv_id = ' . $this->getIntParameter('ccg_srv_id') . ')';
            }
            if ($this->isValidParameter('cc_group') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('ccg.ccg_name', $this->getStringParameter('cc_group'));
            }
            if ($this->isValidParameter('cc_code') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_code', $this->getStringParameter('cc_code'));
            }
            if ($this->isValidParameter('cc_name') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('cc.cc_name', $this->getStringParameter('cc_name'));
            }
            if ($this->isValidParameter('cc_service') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('srv.srv_name', $this->getStringParameter('cc_service'));
            }
            if ($this->isValidParameter('ccg_type') === true) {
                $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_type', $this->getStringParameter('ccg_type'));
            }
            $wheres[] = SqlHelper::generateNumericCondition('cc.cc_ss_id', $this->getIntParameter('cc_ss_id'));
            $wheres[] = SqlHelper::generateNullCondition('cc.cc_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('cc.cc_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('ccg.ccg_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('ccg.ccg_active', 'Y');
            return CostCodeDao::loadData($wheres, [], 20);
        }

        return [];
    }

}
